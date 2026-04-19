<?php
// Phase 5 - Deferred AI, upload limits, duplicate detection, approval status
namespace App\Controllers;

use App\Models\Document;
use App\Models\AiChat;
use App\Models\AiUsage;
use App\Models\User;
use App\Services\DocumentParser;
use App\Services\AiService;
use App\Services\Logger;
use App\Helpers\FileHelper;
use App\Helpers\Validator;

class DocumentController extends BaseController
{
    private $documentModel;
    private $aiChatModel;
    private $logger;

    public function __construct()
    {
        $this->documentModel = new Document();
        $this->aiChatModel = new AiChat();
        $this->logger = new Logger();

        // Check authentication
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/documind/public/login');
        }
    }

    /**
     * Show all user documents
     */
    public function index(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 12;
        $offset = ($page - 1) * $limit;
        $userId = $_SESSION['user_id'];

        $documents = $this->documentModel->getUserDocuments($userId, $limit, $offset);
        $total = $this->documentModel->countUserDocuments($userId);
        $totalPages = ceil($total / $limit);

        // Get upload limit info
        $userRecord = (new User())->findById($userId);
        $uploadLimit = (int)($userRecord['upload_limit'] ?? 10);
        $isAdmin = ($_SESSION['user_role'] ?? '') === 'admin';

        $this->render('documents.index', [
            'title' => 'My Documents',
            'documents' => $documents,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'uploadLimit' => $uploadLimit,
            'isAdmin' => $isAdmin,
        ]);
    }

    /**
     * Show upload form
     */
    public function showUpload(): void
    {
        $userId = $_SESSION['user_id'];
        $userRecord = (new User())->findById($userId);
        $uploadLimit = (int)($userRecord['upload_limit'] ?? 10);
        $currentCount = $this->documentModel->countUserDocuments($userId);
        $isAdmin = ($_SESSION['user_role'] ?? '') === 'admin';

        $this->render('documents.upload', [
            'title' => 'Upload Document',
            'maxSize' => $_ENV['UPLOAD_MAX_MB'] ?? 20,
            'uploadLimit' => $uploadLimit,
            'currentCount' => $currentCount,
            'remaining' => max(0, $uploadLimit - $currentCount),
            'isAdmin' => $isAdmin,
        ]);
    }

    /**
     * Get all user documents as JSON (for modals/selectors and grid)
     */
    public function listJson(): void
    {
        $userId = $_SESSION['user_id'];
        $page = max(0, (int)($_GET['page'] ?? 0));
        $limit = max(1, min((int)($_GET['per_page'] ?? 12), 100));
        $offset = $page * $limit;
        
        $filter = isset($_GET['filter']) ? trim($_GET['filter']) : '';
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        
        $pdo = \App\Helpers\Database::getInstance();
        $params = [$userId];
        $where = "user_id = ?";
        
        if ($filter === 'pdf' || $filter === 'docx') {
            $where .= " AND file_type = ?";
            $params[] = $filter;
        } elseif ($filter === 'ai') {
            $where .= " AND ai_processed = 1";
        }
        
        // Handle search
        if (!empty($search)) {
            $where .= " AND (original_name LIKE ? OR keywords LIKE ?)";
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }
        
        // Count for full pagination metadata if needed
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM documents WHERE $where");
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT * FROM documents WHERE $where ORDER BY upload_date DESC LIMIT $limit OFFSET $offset");
        $stmt->execute($params);
        $documents = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->json([
            'success' => true,
            'documents' => $documents,
            'total' => $total,
            'has_more' => ($offset + count($documents)) < $total
        ]);
    }

    /**
     * Handle document upload - deferred AI, with limits & duplicate check
     */
    public function upload(): void
    {
        // Increase limits for potentially heavy DOCX/PDF processing
        ini_set('memory_limit', '256M');
        set_time_limit(120);

        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'] ?? 'user';

        // Validate input
        if (empty($_FILES['document'])) {
            $this->json(['success' => false, 'error' => 'No file uploaded'], 400);
            return;
        }

        // Upload limit check for non-admin users
        if ($userRole !== 'admin') {
            $currentCount = $this->documentModel->countUserDocuments($userId);
            $userRecord = (new User())->findById($userId);
            $limit = (int)($userRecord['upload_limit'] ?? 10);
            if ($currentCount >= $limit) {
                $this->json([
                    'success' => false,
                    'error' => "Upload limit reached ($limit documents). Contact an administrator to increase your quota."
                ], 403);
                return;
            }
        }

        $file = $_FILES['document'];
        $errors = [];

        // Validate file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Upload error: ' . $this->getUploadError($file['error']);
        }

        // Check file type
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedMimes = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        
        // Add common DOCX fallbacks if extension is docx
        if ($extension === 'docx') {
            $allowedMimes[] = 'application/zip';
            $allowedMimes[] = 'application/octet-stream';
            $allowedMimes[] = 'application/x-zip-compressed';
        }

        $mimeType = FileHelper::getMimeType($file['tmp_name']);
        
        if (!in_array($mimeType, $allowedMimes)) {
            $errors[] = 'Only PDF and DOCX files are allowed';
        }

        // Check file size (20 MB limit)
        $maxBytes = ($_ENV['UPLOAD_MAX_MB'] ?? 20) * 1024 * 1024;
        if ($file['size'] > $maxBytes) {
            $errors[] = 'File too large (max ' . $_ENV['UPLOAD_MAX_MB'] . 'MB)';
        }

        if (!empty($errors)) {
            $this->json(['success' => false, 'errors' => $errors], 400);
            return;
        }

        // Duplicate detection
        $existing = $this->documentModel->findDuplicate($userId, $file['name'], $file['size']);
        if ($existing) {
            $this->json([
                'success' => false,
                'error' => 'A document with the same name and size already exists in your repository.'
            ], 409);
            return;
        }

        try {
            // Determine file type
            $fileType = $mimeType === 'application/pdf' ? 'pdf' : 'docx';

            // Generate secure filename
            $storedName = FileHelper::generateSecureName($file['name'], $fileType);
            $uploadPath = __DIR__ . '/../../storage/uploads/' . $storedName;

            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                throw new \Exception('Failed to move uploaded file');
            }

            // Extract text only (AI processing deferred until admin approval)
            $fullText = '';
            try {
                ob_start();
                $fullText = DocumentParser::extractText($uploadPath, $fileType);
                ob_end_clean();
            } catch (\Throwable $e) {
                if (ob_get_level()) ob_end_clean();
                $this->logger->warn("Text extraction failed: " . $e->getMessage());
                $fullText = '';
            }

            // Determine initial status: admin uploads auto-approve
            $isAdmin = ($userRole === 'admin');

            // For admin: run AI immediately since auto-approved
            $summary = '';
            $keywords = '[]';
            $aiProcessed = 0;

            if ($isAdmin && !empty(trim($fullText))) {
                try {
                    // AI analysis for admins with robust key and model lookup
                    $apiKey = $_ENV['GEMINI_API_KEY'] ?? $_SERVER['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY');
                    $model = $_ENV['GEMINI_MODEL'] ?? $_SERVER['GEMINI_MODEL'] ?? getenv('GEMINI_MODEL') ?: 'gemini-flash-latest';
                    $aiService = new AiService($apiKey ?: '', $model);
                    $aiResults = $aiService->generateSummary($fullText);
                    $summary = $aiResults['summary'] ?? '';
                    $keywords = json_encode($aiResults['keywords'] ?? ['document']);
                    $aiProcessed = 1;
                } catch (\Exception $e) {
                    $this->logger->warn("AI processing failed for admin upload: " . $e->getMessage());
                }
            }

            // Save document to database
            $documentId = $this->documentModel->insert([
                'user_id'       => $userId,
                'original_name' => $file['name'],
                'stored_name'   => $storedName,
                'file_type'     => $fileType,
                'file_size'     => $file['size'],
                'summary'       => $summary,
                'keywords'      => $keywords,
                'full_text'     => $fullText,
                'ai_processed'  => $aiProcessed,
                'status'        => $isAdmin ? 'approved' : 'pending',
                'is_public'     => $isAdmin ? 1 : 0,
            ]);

            $this->logger->info("Document uploaded: ID=$documentId, User=$userId, Status=" . ($isAdmin ? 'approved' : 'pending'));

            $message = $isAdmin
                ? 'Document uploaded and approved successfully'
                : 'Document uploaded successfully. It will be available after admin approval.';

            $this->json([
                'success' => true,
                'message' => $message,
                'documentId' => $documentId,
                'redirect' => '/documind/public/documents/' . $documentId,
                'status' => $isAdmin ? 'approved' : 'pending',
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Upload failed: " . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * View document details and chat
     */
    public function view(int $id): void
    {
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'] ?? 'user';
        $document = $this->documentModel->findByIdWithVisibility($id, $userId, $userRole);

        if (!$document) {
            $this->abort(404);
        }

        // Get chat history (last 5 messages)
        $chatHistory = $this->aiChatModel->getChatHistory($id, $userId, limit: 5);

        // Get remaining AI messages
        $remaining = 999;
        $dailyLimit = 20;
        if ($userRole !== 'admin') {
            $aiUsage = new AiUsage();
            $userRecord = (new User())->findById($userId);
            $dailyLimit = (int)($userRecord['daily_ai_limit'] ?? 20);
            $todayUsage = $aiUsage->getTodayUsage($userId);
            $remaining = max(0, $dailyLimit - $todayUsage);
        }

        $this->render('documents.view', [
            'title' => $document['original_name'],
            'document' => $document,
            'chatHistory' => $chatHistory,
            'keywords' => json_decode($document['keywords'] ?? '[]', true),
            'remaining' => $remaining,
            'dailyLimit' => $dailyLimit,
            'isOwner' => ($document['user_id'] == $userId),
        ]);
    }

    /**
     * Send message to AI about document — with rate limiting
     */
    public function askQuestion(): void
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

        $message = mb_substr($message, 0, 2000);

        $document = $this->documentModel->findByIdWithVisibility($documentId, $userId, $userRole);
        if (!$document) {
            $this->json(['success' => false, 'error' => 'Document not found'], 404);
            return;
        }

        // AI only for approved documents (or owner's own)
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
                ], 429);
                return;
            }
        }

        try {
            // Save user message
            $this->aiChatModel->addMessage($documentId, $userId, 'user', $message);

            // Get chat history for context
            $history = $this->aiChatModel->getChatHistory($documentId, $userId, limit: 5);

            // Get AI response with robust lookup
            $apiKey = $_ENV['GEMINI_API_KEY'] ?? $_SERVER['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY');
            $model = $_ENV['GEMINI_MODEL'] ?? $_SERVER['GEMINI_MODEL'] ?? getenv('GEMINI_MODEL') ?: 'gemini-flash-latest';
            $aiService = new AiService($apiKey ?: '', $model);
            $aiResponse = $aiService->askQuestion($document['full_text'], $message, $history);

            // Save AI response
            $this->aiChatModel->addMessage($documentId, $userId, 'assistant', $aiResponse);

            $this->logger->info("Question asked: Doc=$documentId, User=$userId");

            $this->json([
                'success'   => true,
                'response'  => $aiResponse,
                'remaining' => max(0, $remaining),
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Question failed: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'AI service temporarily unavailable. Please try again.'], 500);
        }
    }

    /**
     * Search documents
     */
    public function search(): void
    {
        $userId = $_SESSION['user_id'];
        $query = $_GET['q'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 12;
        $offset = ($page - 1) * $limit;

        if (empty($query)) {
            $this->redirect('/documind/public/documents');
        }

        $documents = $this->documentModel->searchUserDocuments($userId, $query, $limit, $offset);

        $this->render('documents.search', [
            'title' => 'Search Results for: ' . htmlspecialchars($query),
            'query' => $query,
            'documents' => $documents,
            'currentPage' => $page,
            'totalPages' => ceil(count($documents) / $limit),
        ]);
    }

    /**
     * Delete document
     */
    public function delete(int $id): void
    {
        $userId = $_SESSION['user_id'];
        $document = $this->documentModel->findByIdAndUser($id, $userId);

        if (!$document) {
            $this->json(['success' => false, 'error' => 'Document not found'], 404);
            return;
        }

        try {
            // Delete file from storage
            $filePath = __DIR__ . '/../../storage/uploads/' . $document['stored_name'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Delete from database (cascade will delete chat history)
            $this->documentModel->delete($id);

            $this->logger->info("Document deleted: ID=$id, User=$userId");

            $this->json(['success' => true, 'message' => 'Document deleted']);

        } catch (\Exception $e) {
            $this->logger->error("Delete failed: " . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Download document
     */
    public function download(int $id): void
    {
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'] ?? 'user';
        $document = $this->documentModel->findByIdWithVisibility($id, $userId, $userRole);

        if (!$document) {
            $this->abort(404);
        }

        $filePath = __DIR__ . '/../../storage/uploads/' . $document['stored_name'];

        if (!file_exists($filePath)) {
            $this->abort(404);
        }

        // Stream file
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $document['original_name'] . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    /**
     * Inline viewing of document (no forced download)
     */
    public function inline(int $id): void
    {
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'] ?? 'user';
        $document = $this->documentModel->findByIdWithVisibility($id, $userId, $userRole);

        if (!$document) {
            $this->abort(404);
        }

        $filePath = __DIR__ . '/../../storage/uploads/' . $document['stored_name'];

        if (!file_exists($filePath)) {
            $this->abort(404);
        }
        
        $mimeType = $document['file_type'] === 'pdf' 
            ? 'application/pdf' 
            : 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';

        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: inline; filename="' . $document['original_name'] . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    /**
     * Get upload error message
     */
    private function getUploadError(int $code): string
    {
        $errors = [
            UPLOAD_ERR_OK => 'No error',
            UPLOAD_ERR_INI_SIZE => 'File exceeds php.ini limit',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds form limit',
            UPLOAD_ERR_PARTIAL => 'File partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'No temp directory',
            UPLOAD_ERR_CANT_WRITE => 'Cannot write file',
            UPLOAD_ERR_EXTENSION => 'Upload stopped by extension',
        ];
        return $errors[$code] ?? 'Unknown error';
    }

    /**
     * Toggle favorite status
     */
    public function toggleFavorite(int $id): void
    {
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'] ?? 'user';
        $document = $this->documentModel->findByIdWithVisibility($id, $userId, $userRole);

        if (!$document) {
            $this->json(['success' => false, 'error' => 'Document not found'], 404);
            return;
        }

        try {
            $isFavorite = !($document['is_favorite'] ?? 0);
            $this->documentModel->update($id, ['is_favorite' => $isFavorite ? 1 : 0]);

            $this->json([
                'success' => true,
                'is_favorite' => $isFavorite,
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Toggle favorite failed: " . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Bulk share documents
     */
    public function bulkShare(): void
    {
        $userId = $_SESSION['user_id'];
        $input = json_decode(file_get_contents('php://input'), true);

        $docIds = $input['docIds'] ?? [];
        $userEmail = $input['email'] ?? '';
        $permission = $input['permission'] ?? 'viewer';

        if (empty($docIds) || empty($userEmail)) {
            $this->json(['success' => false, 'error' => 'Missing documents or recipient email'], 400);
            return;
        }

        $userModel = new User();
        $shareWithUser = $userModel->findByEmail($userEmail);

        if (!$shareWithUser) {
            $this->json(['success' => false, 'error' => 'Target user not found'], 404);
            return;
        }

        if ($shareWithUser['id'] === $userId) {
            $this->json(['success' => false, 'error' => 'You cannot share with yourself'], 400);
            return;
        }

        $shareService = new \App\Services\ShareService();
        $successCount = 0;

        foreach ($docIds as $documentId) {
            // Verify ownership
            $document = $this->documentModel->findByIdAndUser((int)$documentId, $userId);
            if ($document) {
                if ($shareService->shareDocument((int)$documentId, $userId, $shareWithUser['id'], $permission)) {
                    $successCount++;
                }
            }
        }

        if ($successCount > 0) {
            $this->logger->info("Bulk share: $successCount documents shared with User {$shareWithUser['id']}");
            $this->json([
                'success' => true,
                'message' => 'Successfully shared ' . $successCount . ' document(s) with ' . htmlspecialchars($userEmail)
            ]);
        } else {
            $this->json(['success' => false, 'error' => 'Failed to share documents or permission denied'], 403);
        }
    }

    /**
     * Bulk delete documents
     */
    public function bulkDelete(): void
    {
        $userId = $_SESSION['user_id'];
        $input = json_decode(file_get_contents('php://input'), true);
        $docIds = $input['document_ids'] ?? [];

        if (empty($docIds)) {
            $this->json(['success' => false, 'error' => 'No documents selected'], 400);
            return;
        }

        $successCount = 0;
        $errors = [];

        foreach ($docIds as $id) {
            $document = $this->documentModel->findByIdAndUser((int)$id, $userId);
            if ($document) {
                try {
                    // Delete file from storage
                    $filePath = __DIR__ . '/../../storage/uploads/' . $document['stored_name'];
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                    // Delete from database
                    $this->documentModel->delete($id);
                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = "Failed to delete ID $id: " . $e->getMessage();
                }
            }
        }

        if ($successCount > 0) {
            $this->logger->info("Bulk delete: $successCount documents deleted by User $userId");
            $this->json([
                'success' => true,
                'message' => "Successfully deleted $successCount document(s)"
            ]);
        } else {
            $this->json(['success' => false, 'error' => 'No documents could be deleted', 'details' => $errors], 500);
        }
    }
}
