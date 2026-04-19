<?php

namespace App\Services;

use App\Models\DocumentShare;
use App\Models\Notification;
use App\Helpers\Database;

class ShareService
{
    private $shareModel;
    private $notificationModel;

    public function __construct()
    {
        $this->shareModel = new DocumentShare();
        $this->notificationModel = new Notification();
    }

    /**
     * Share a document with another user
     */
    public function shareDocument(int $document_id, int $owner_id, int $shared_with_id, string $permission = 'view'): bool
    {
        try {
            // Check if already shared
            if ($this->shareModel->isSharedWith($document_id, $shared_with_id)) {
                // Update permission
                return $this->updateSharePermission($document_id, $shared_with_id, $permission);
            }

            // Create share
            $this->shareModel->insert([
                'document_id' => $document_id,
                'owner_id' => $owner_id,
                'shared_with_id' => $shared_with_id,
                'permission' => $permission,
            ]);

            // Create notification
            $this->notificationModel->createNotification(
                $shared_with_id,
                'document_shared',
                'Document Shared',
                'A user shared a document with you',
                $owner_id,
                $document_id
            );

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Update share permission
     */
    public function updateSharePermission(int $document_id, int $user_id, string $permission): bool
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare(
                "UPDATE document_shares SET permission = ? WHERE document_id = ? AND shared_with_id = ?"
            );
            $stmt->execute([$permission, $document_id, $user_id]);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Unshare a document
     */
    public function unshareDocument(int $document_id, int $shared_with_id): bool
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare(
                "DELETE FROM document_shares WHERE document_id = ? AND shared_with_id = ?"
            );
            $stmt->execute([$document_id, $shared_with_id]);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if user can view document
     */
    public function canViewDocument(int $document_id, int $user_id): bool
    {
        return $this->shareModel->isSharedWith($document_id, $user_id);
    }

    /**
     * Check if user can edit document
     */
    public function canEditDocument(int $document_id, int $user_id): bool
    {
        $permission = $this->shareModel->checkPermission($document_id, $user_id);
        return $permission === 'edit';
    }

    /**
     * Check if user can comment on document
     */
    public function canCommentDocument(int $document_id, int $user_id): bool
    {
        $permission = $this->shareModel->checkPermission($document_id, $user_id);
        return in_array($permission, ['comment', 'edit']);
    }
}
