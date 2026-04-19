<?php

namespace App\Controllers;

use App\Models\Document;
use App\Models\DocumentShare;
use App\Models\User;
use App\Models\Notification;
use App\Services\ShareService;
use App\Services\Logger;

class ShareController extends BaseController
{
    private $documentModel;
    private $shareModel;
    private $userModel;
    private $shareService;
    private $logger;

    public function __construct()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }

        $this->documentModel = new Document();
        $this->shareModel = new DocumentShare();
        $this->userModel = new User();
        $this->shareService = new ShareService();
        $this->logger = new Logger();
    }

    /**
     * Get shares for a document
     */
    public function getShares(int $documentId): void
    {
        $userId = $_SESSION['user_id'];
        $document = $this->documentModel->findByIdAndUser($documentId, $userId);

        if (!$document) {
            $this->json(['success' => false, 'error' => 'Document not found'], 404);
        }

        $shares = $this->shareModel->getDocumentShares($documentId);

        $this->json([
            'success' => true,
            'shares' => $shares,
        ]);
    }

    /**
     * Share document with user
     */
    public function share(): void
    {
        $userId = $_SESSION['user_id'];
        $input = json_decode(file_get_contents('php://input'), true);

        $documentId = $input['document_id'] ?? null;
        $userEmail = $input['user_email'] ?? null;
        $permission = $input['permission'] ?? 'view';

        if (!$documentId || !$userEmail) {
            $this->json(['success' => false, 'error' => 'Missing required fields'], 400);
        }

        try {
            // Check if document belongs to user
            $document = $this->documentModel->findByIdAndUser($documentId, $userId);
            if (!$document) {
                $this->json(['success' => false, 'error' => 'Document not found'], 404);
            }

            // Find user by email
            $shareWithUser = $this->userModel->findByEmail($userEmail);
            if (!$shareWithUser) {
                $this->json(['success' => false, 'error' => 'User not found'], 404);
            }

            // Can't share with self
            if ($shareWithUser['id'] === $userId) {
                $this->json(['success' => false, 'error' => 'Cannot share with yourself'], 400);
            }

            // Share document
            $success = $this->shareService->shareDocument($documentId, $userId, $shareWithUser['id'], $permission);

            if ($success) {
                $this->logger->info("Document shared: DocID=$documentId, SharedWith=" . $shareWithUser['id']);
                $this->json(['success' => true, 'message' => 'Document shared successfully']);
            } else {
                $this->json(['success' => false, 'error' => 'Failed to share document'], 500);
            }
        } catch (\Exception $e) {
            $this->logger->error("Share failed: " . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update share permission
     */
    public function updatePermission(): void
    {
        $userId = $_SESSION['user_id'];
        $input = json_decode(file_get_contents('php://input'), true);

        $documentId = $input['document_id'] ?? null;
        $sharedWithId = $input['shared_with_id'] ?? null;
        $permission = $input['permission'] ?? null;

        if (!$documentId || !$sharedWithId || !$permission) {
            $this->json(['success' => false, 'error' => 'Missing required fields'], 400);
        }

        try {
            // Verify ownership
            $document = $this->documentModel->findByIdAndUser($documentId, $userId);
            if (!$document) {
                $this->json(['success' => false, 'error' => 'Document not found'], 404);
            }

            $success = $this->shareService->updateSharePermission($documentId, $sharedWithId, $permission);

            if ($success) {
                $this->logger->info("Share permission updated: DocID=$documentId, Permission=$permission");
                $this->json(['success' => true, 'message' => 'Permission updated']);
            } else {
                $this->json(['success' => false, 'error' => 'Failed to update permission'], 500);
            }
        } catch (\Exception $e) {
            $this->logger->error("Update permission failed: " . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Unshare document
     */
    public function unshare(): void
    {
        $userId = $_SESSION['user_id'];
        $input = json_decode(file_get_contents('php://input'), true);

        $documentId = $input['document_id'] ?? null;
        $sharedWithId = $input['shared_with_id'] ?? null;

        if (!$documentId || !$sharedWithId) {
            $this->json(['success' => false, 'error' => 'Missing required fields'], 400);
        }

        try {
            // Verify ownership
            $document = $this->documentModel->findByIdAndUser($documentId, $userId);
            if (!$document) {
                $this->json(['success' => false, 'error' => 'Document not found'], 404);
            }

            $success = $this->shareService->unshareDocument($documentId, $sharedWithId);

            if ($success) {
                $this->logger->info("Document unshared: DocID=$documentId, User=$sharedWithId");
                $this->json(['success' => true, 'message' => 'Document unshared']);
            } else {
                $this->json(['success' => false, 'error' => 'Failed to unshare document'], 500);
            }
        } catch (\Exception $e) {
            $this->logger->error("Unshare failed: " . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * List documents shared with user
     */
    public function sharedWithMe(int $page = 1): void
    {
        $userId = $_SESSION['user_id'];
        $limit = 12;
        $offset = ($page - 1) * $limit;

        $documents = $this->shareModel->getSharedDocuments($userId, $limit, $offset);
        $total = $this->shareModel->countSharedDocuments($userId);
        $totalPages = ceil($total / $limit);

        $this->render('user.shared-documents', [
            'title' => 'Shared with Me',
            'documents' => $documents,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
        ]);
    }
}
