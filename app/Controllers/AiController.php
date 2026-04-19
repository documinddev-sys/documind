<?php

namespace App\Controllers;

use App\Models\Document;
use App\Models\AiChat;
use App\Models\AiUsage;
use App\Models\User;
use App\Services\AiService;
use App\Services\Logger;

class AiController extends BaseController
{
    private $documentModel;
    private $aiChatModel;
    private $logger;

    public function __construct()
    {
        $this->documentModel = new Document();
        $this->aiChatModel = new AiChat();
        $this->logger = new Logger();

        // Check authentication — AI requires login
        if (!isset($_SESSION['user_id'])) {
            if ($this->isJsonRequest()) {
                $this->json(['success' => false, 'error' => 'Login required to use AI chat', 'login_required' => true], 401);
            }
            $this->redirect('/login');
        }
    }

    /**
     * Chat interface for document
     */
    public function chat(int $documentId): void
    {
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'] ?? 'user';
        $document = $this->documentModel->findByIdWithVisibility($documentId, $userId, $userRole);

        if (!$document) {
            $this->abort(404);
        }

        // AI only for approved documents (or owner's own pending docs)
        if ($document['status'] !== 'approved' && $document['user_id'] !== $userId) {
            $this->abort(403);
        }

        $chatHistory = $this->aiChatModel->getChatHistory($documentId, $userId, limit: 50);
        $keywords = json_decode($document['keywords'] ?? '[]', true);

        // Get remaining AI messages for UI
        $remaining = 999;
        $dailyLimit = 20;
        if ($userRole !== 'admin') {
            $aiUsage = new AiUsage();
            $userRecord = (new User())->findById($userId);
            $dailyLimit = (int)($userRecord['daily_ai_limit'] ?? 20);
            $todayUsage = $aiUsage->getTodayUsage($userId);
            $remaining = max(0, $dailyLimit - $todayUsage);
        }

        $this->render('ai.chat', [
            'title' => 'Chat - ' . $document['original_name'],
            'document' => $document,
            'chatHistory' => $chatHistory,
            'keywords' => is_array($keywords) ? $keywords : [],
            'remaining' => $remaining,
            'dailyLimit' => $dailyLimit,
        ]);
    }

    /**
     * Get chat history
     */
    public function getChatHistory(int $documentId): void
    {
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'] ?? 'user';
        $document = $this->documentModel->findByIdWithVisibility($documentId, $userId, $userRole);

        if (!$document) {
            $this->json(['success' => false, 'error' => 'Document not found'], 404);
            return;
        }

        $history = $this->aiChatModel->getChatHistory($documentId, $userId, limit: 50);

        $this->json([
            'success' => true,
            'history' => $history,
        ]);
    }

    /**
     * Send message and get AI response — with rate limiting
     */
    public function sendMessage(): void
    {
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'] ?? 'user';
        $input = json_decode(file_get_contents('php://input'), true);

        $documentId = (int)($input['documentId'] ?? 0);
        $message = trim($input['message'] ?? '');

        if (!$documentId || empty($message)) {
            $this->json(['success' => false, 'error' => 'Invalid input'], 400);
            return;
        }

        $style = $input['style'] ?? 'balanced';
        $depth = $input['depth'] ?? 'standard';

        // Limit message length
        $message = mb_substr($message, 0, 2000);

        // Access check via unified visibility
        $document = $this->documentModel->findByIdWithVisibility($documentId, $userId, $userRole);
        if (!$document) {
            $this->json(['success' => false, 'error' => 'Document not found'], 404);
            return;
        }

        // AI only for approved documents
        if ($document['status'] !== 'approved' && $document['user_id'] !== $userId) {
            $this->json(['success' => false, 'error' => 'AI chat is only available for approved documents.'], 403);
            return;
        }

        // Rate limit check (skip for admins)
        $dailyLimit = 20;
        $remaining = 999;
        if ($userRole !== 'admin') {
            $aiUsage = new AiUsage();
            $userRecord = (new User())->findById($userId);
            $dailyLimit = (int)($userRecord['daily_ai_limit'] ?? 20);
            $remaining = $aiUsage->tryConsumeMessage($userId, $dailyLimit);

            if ($remaining < 0) {
                $this->json([
                    'success'       => false,
                    'error'         => "Daily AI message limit reached ($dailyLimit messages). Try again tomorrow.",
                    'limit_reached' => true,
                    'daily_limit'   => $dailyLimit,
                ], 429);
                return;
            }
        }

        try {
            // Save user message
            $this->aiChatModel->addMessage($documentId, $userId, 'user', $message);

            // Get recent chat history for context
            $history = $this->aiChatModel->getChatHistory($documentId, $userId, limit: 5);

            // Get AI response with robust environment variable lookup
            $apiKey = $_ENV['GEMINI_API_KEY'] ?? $_SERVER['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY');
            $model = $_ENV['GEMINI_MODEL'] ?? $_SERVER['GEMINI_MODEL'] ?? getenv('GEMINI_MODEL') ?: 'gemini-flash-latest';
            $aiService = new AiService($apiKey ?: '', $model);
            $aiResponse = $aiService->askQuestion(
                $document['full_text'],
                $message,
                array_slice($history, -4),
                $style,
                $depth
            );

            // Save AI response
            $this->aiChatModel->addMessage($documentId, $userId, 'assistant', $aiResponse);

            $this->logger->info("AI message: Doc=$documentId, User=$userId");

            $this->json([
                'success'   => true,
                'response'  => $aiResponse,
                'remaining' => max(0, $remaining),
            ]);

        } catch (\Exception $e) {
            $this->logger->error("AI message failed: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'AI service temporarily unavailable. Please try again.'], 500);
        }
    }

    /**
     * Clear chat history
     */
    public function clearHistory(int $documentId): void
    {
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'] ?? 'user';
        $document = $this->documentModel->findByIdWithVisibility($documentId, $userId, $userRole);

        if (!$document) {
            $this->json(['success' => false, 'error' => 'Document not found'], 404);
            return;
        }

        try {
            $success = $this->aiChatModel->clearChatHistory($documentId, $userId);

            if ($success) {
                $this->logger->info("Chat cleared: Doc=$documentId, User=$userId");
                $this->json(['success' => true, 'message' => 'Chat history cleared']);
            } else {
                $this->json(['success' => false, 'error' => 'Failed to clear history'], 500);
            }

        } catch (\Exception $e) {
            $this->logger->error("Clear history failed: " . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Check if the current request expects JSON
     */
    private function isJsonRequest(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        return str_contains($accept, 'application/json') || str_contains($contentType, 'application/json');
    }
}
