<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Document;
use App\Models\ActivityLog;
use App\Models\Notification;
use App\Services\AnalyticsService;
use App\Services\AiService;
use App\Services\Logger;
use App\Helpers\Database;

class AdminController extends BaseController
{
    private $userModel;
    private $documentModel;
    private $activityModel;
    private $analyticsService;
    private $logger;

    public function __construct()
    {
        // Check authentication AND admin role
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? null) !== 'admin') {
            $this->redirect('/dashboard');
        }

        $this->userModel = new User();
        $this->documentModel = new Document();
        $this->activityModel = new ActivityLog();
        $this->analyticsService = new AnalyticsService();
        $this->logger = new Logger();
    }

    /**
     * Admin dashboard
     */
    public function dashboard(): void
    {
        $stats = $this->analyticsService->getSystemStats();
        $recentActivity = $this->activityModel->getRecentActivity(10);
        $topUsers = $this->analyticsService->getMostActiveUsers(5);
        $pendingCount = $this->documentModel->countByStatus('pending');

        $this->render('admin.dashboard', [
            'title' => 'Admin Dashboard',
            'stats' => $stats,
            'recentActivity' => $recentActivity,
            'topUsers' => $topUsers,
            'pendingCount' => $pendingCount,
        ]);
    }

    /**
     * User management
     */
    public function users(int $page = 1): void
    {
        $limit = 20;
        $offset = ($page - 1) * $limit;
        $pdo = Database::getInstance();

        $stmt = $pdo->prepare(
            "SELECT * FROM users ORDER BY created_at DESC LIMIT $limit OFFSET $offset"
        );
        $stmt->execute();
        $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $total = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;
        $totalPages = ceil($total / $limit);

        $this->render('admin.users', [
            'title' => 'User Management',
            'users' => $users,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
        ]);
    }

    /**
     * View user details
     */
    public function viewUser(int $id): void
    {
        $user = $this->userModel->findById($id);
        if (!$user) {
            $this->abort(404);
        }

        $stats = $this->analyticsService->getUserStats($id);
        $activity = $this->activityModel->getUserActivityLog($id, 20);

        $this->render('admin.user-detail', [
            'title' => 'User: ' . $user['name'],
            'user' => $user,
            'stats' => $stats,
            'activity' => $activity,
        ]);
    }

    /**
     * Deactivate user
     */
    public function deactivateUser(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = (int)($input['user_id'] ?? 0);

        $user = $this->userModel->findById($id);
        if (!$user) {
            $this->json(['success' => false, 'error' => 'User not found'], 404);
            return;
        }

        // Prevent deactivating self
        if ($id === $_SESSION['user_id']) {
            $this->json(['success' => false, 'error' => 'Cannot deactivate yourself'], 400);
            return;
        }

        // Prevent deactivating the primary system admin (user ID 1)
        if ($id === 1) {
            $this->json(['success' => false, 'error' => 'Cannot deactivate the primary system administrator'], 403);
            return;
        }

        try {
            $this->userModel->update($id, ['is_active' => 0]);
            $this->activityModel->logActivity($_SESSION['user_id'], 'deactivate_user', 'user', $id);
            $this->logger->info("User deactivated: ID=$id");

            $this->json(['success' => true, 'message' => 'User deactivated']);
        } catch (\Exception $e) {
            $this->logger->error("Deactivate user failed: " . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Reactivate user
     */
    public function reactivateUser(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = (int)($input['user_id'] ?? 0);

        $user = $this->userModel->findById($id);
        if (!$user) {
            $this->json(['success' => false, 'error' => 'User not found'], 404);
            return;
        }

        try {
            $this->userModel->update($id, ['is_active' => 1]);
            $this->activityModel->logActivity($_SESSION['user_id'], 'reactivate_user', 'user', $id);
            $this->logger->info("User reactivated: ID=$id");

            $this->json(['success' => true, 'message' => 'User reactivated']);
        } catch (\Exception $e) {
            $this->logger->error("Reactivate user failed: " . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Promote a user to Admin role
     */
    public function makeAdmin(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = (int)($input['user_id'] ?? 0);

        $user = $this->userModel->findById($id);
        if (!$user) {
            $this->json(['success' => false, 'error' => 'User not found'], 404);
            return;
        }

        try {
            $this->userModel->update($id, ['role' => 'admin']);
            $this->activityModel->logActivity($_SESSION['user_id'], 'make_admin', 'user', $id);
            $this->logger->info("User promoted to admin: ID=$id by " . $_SESSION['user_id']);

            $this->json(['success' => true, 'message' => 'User promoted to Admin']);
        } catch (\Exception $e) {
            $this->logger->error("Make admin failed: " . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Document management with status filtering
     */
    public function documents(int $page = 1): void
    {
        $limit = 20;
        $offset = ($page - 1) * $limit;
        $statusFilter = $_GET['status'] ?? '';

        $documents = $this->documentModel->getAllDocumentsAdmin($limit, $offset, $statusFilter);
        $total = $this->documentModel->countAllDocumentsAdmin($statusFilter);
        $totalPages = max(1, ceil($total / $limit));

        // Count per status for tab badges
        $pendingCount = $this->documentModel->countByStatus('pending');
        $approvedCount = $this->documentModel->countByStatus('approved');
        $rejectedCount = $this->documentModel->countByStatus('rejected');

        $this->render('admin.documents', [
            'title' => 'Document Management',
            'documents' => $documents,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'statusFilter' => $statusFilter,
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
        ]);
    }

    /**
     * List documents pending admin review
     */
    public function pendingDocuments(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $documents = $this->documentModel->getDocumentsByStatus('pending', $limit, $offset);
        $total = $this->documentModel->countByStatus('pending');
        $totalPages = max(1, ceil($total / $limit));

        $this->render('admin.pending-documents', [
            'title' => 'Pending Approval',
            'documents' => $documents,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
        ]);
    }

    /**
     * Approve a document — triggers deferred AI processing
     */
    public function approveDocument(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $docId = (int)($input['document_id'] ?? 0);
        $makePublic = (bool)($input['is_public'] ?? true);

        $document = $this->documentModel->findById($docId);
        if (!$document) {
            $this->json(['success' => false, 'error' => 'Document not found'], 404);
            return;
        }
        if ($document['status'] === 'approved') {
            $this->json(['success' => false, 'error' => 'Document already approved'], 400);
            return;
        }

        // Run deferred AI processing
        $aiProcessed = 0;
        $summary = 'Document approved. AI analysis unavailable.';
        $keywords = '["document"]';

        if (!empty(trim($document['full_text'] ?? ''))) {
            try {
                $aiService = new AiService($_ENV['GEMINI_API_KEY']);
                $aiResults = $aiService->generateSummary($document['full_text']);
                $summary = $aiResults['summary'] ?? $summary;
                $keywords = json_encode($aiResults['keywords'] ?? ['document']);
                $aiProcessed = 1;
            } catch (\Exception $e) {
                $this->logger->warn("AI processing failed during approval for doc $docId: " . $e->getMessage());
            }
        }

        $this->documentModel->update($docId, [
            'status'       => 'approved',
            'is_public'    => $makePublic ? 1 : 0,
            'reviewed_by'  => $_SESSION['user_id'],
            'reviewed_at'  => date('Y-m-d H:i:s'),
            'summary'      => $summary,
            'keywords'     => $keywords,
            'ai_processed' => $aiProcessed,
        ]);

        $this->activityModel->logActivity(
            $_SESSION['user_id'], 'approve_document', 'document', $docId,
            "Approved: {$document['original_name']}"
        );

        // Notify document owner
        $notification = new Notification();
        $notification->insert([
            'user_id'             => $document['user_id'],
            'type'                => 'document_approved',
            'title'               => 'Document Approved',
            'message'             => "Your document \"{$document['original_name']}\" has been approved and is now available.",
            'related_user_id'     => $_SESSION['user_id'],
            'related_document_id' => $docId,
        ]);

        $this->json(['success' => true, 'message' => 'Document approved and processed']);
    }

    /**
     * Reject a document
     */
    public function rejectDocument(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $docId = (int)($input['document_id'] ?? 0);
        $reason = trim($input['reason'] ?? '');

        $document = $this->documentModel->findById($docId);
        if (!$document) {
            $this->json(['success' => false, 'error' => 'Document not found'], 404);
            return;
        }

        // Clear AI data on rejection
        $this->documentModel->update($docId, [
            'status'       => 'rejected',
            'is_public'    => 0,
            'reviewed_by'  => $_SESSION['user_id'],
            'reviewed_at'  => date('Y-m-d H:i:s'),
            'summary'      => '',
            'keywords'     => '[]',
            'ai_processed' => 0,
        ]);

        $this->activityModel->logActivity(
            $_SESSION['user_id'], 'reject_document', 'document', $docId,
            "Rejected: {$document['original_name']}" . ($reason ? " — $reason" : '')
        );

        // Notify owner
        $notification = new Notification();
        $notification->insert([
            'user_id'             => $document['user_id'],
            'type'                => 'document_rejected',
            'title'               => 'Document Not Approved',
            'message'             => "Your document \"{$document['original_name']}\" was not approved."
                                     . ($reason ? " Reason: $reason" : ''),
            'related_user_id'     => $_SESSION['user_id'],
            'related_document_id' => $docId,
        ]);

        $this->json(['success' => true, 'message' => 'Document rejected']);
    }

    /**
     * Toggle document public status
     */
    public function toggleDocumentPublic(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $docId = (int)($input['document_id'] ?? 0);

        $document = $this->documentModel->findById($docId);
        if (!$document) {
            $this->json(['success' => false, 'error' => 'Document not found'], 404);
            return;
        }

        if ($document['status'] !== 'approved') {
            $this->json(['success' => false, 'error' => 'Only approved documents can be made public'], 403);
            return;
        }

        $newPublicState = empty($document['is_public']) ? 1 : 0;
        
        $this->documentModel->update($docId, [
            'is_public' => $newPublicState
        ]);

        $this->activityModel->logActivity(
            $_SESSION['user_id'], 'toggle_document_public', 'document', $docId,
            "Document " . ($newPublicState ? "made public" : "removed from public library") . ": {$document['original_name']}"
        );

        $this->json([
            'success'   => true, 
            'is_public' => $newPublicState,
            'message'   => $newPublicState ? 'Document is now public' : 'Document removed from public library'
        ]);
    }

    /**
     * Update user quotas (upload limit, AI daily limit)
     */
    public function updateUserLimits(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $targetUserId = (int)($input['user_id'] ?? 0);
        $uploadLimit  = max(1, min(1000, (int)($input['upload_limit'] ?? 10)));
        $dailyAiLimit = max(1, min(500, (int)($input['daily_ai_limit'] ?? 20)));

        $user = $this->userModel->findById($targetUserId);
        if (!$user) {
            $this->json(['success' => false, 'error' => 'User not found'], 404);
            return;
        }

        $this->userModel->update($targetUserId, [
            'upload_limit'   => $uploadLimit,
            'daily_ai_limit' => $dailyAiLimit,
        ]);

        $this->activityModel->logActivity(
            $_SESSION['user_id'], 'update_user_limits', 'user', $targetUserId,
            "Set upload_limit=$uploadLimit, daily_ai_limit=$dailyAiLimit"
        );

        $this->json(['success' => true, 'message' => 'User limits updated']);
    }

    /**
     * Delete document (admin)
     */
    public function deleteDocument(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = (int)($input['document_id'] ?? 0);

        $document = $this->documentModel->findById($id);
        if (!$document) {
            $this->json(['success' => false, 'error' => 'Document not found'], 404);
            return;
        }

        try {
            // Delete file
            $filePath = __DIR__ . '/../../storage/uploads/' . $document['stored_name'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Delete from database
            $this->documentModel->delete($id);
            $this->activityModel->logActivity($_SESSION['user_id'], 'delete_document', 'document', $id);
            $this->logger->info("Document deleted by admin: ID=$id");

            $this->json(['success' => true, 'message' => 'Document deleted']);
        } catch (\Exception $e) {
            $this->logger->error("Delete document failed: " . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Activity log
     */
    public function activityLog(int $page = 1): void
    {
        $limit = 50;
        $offset = ($page - 1) * $limit;
        $pdo = Database::getInstance();

        $stmt = $pdo->prepare(
            "SELECT al.*, u.name, u.email FROM activity_logs al
             JOIN users u ON al.user_id = u.id
             ORDER BY al.created_at DESC LIMIT $limit OFFSET $offset"
        );
        $stmt->execute();
        $activities = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $stmt = $pdo->query("SELECT COUNT(*) as count FROM activity_logs");
        $total = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;
        $totalPages = ceil($total / $limit);

        $this->render('admin.activity-log', [
            'title' => 'Activity Log',
            'activities' => $activities,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
        ]);
    }
}
