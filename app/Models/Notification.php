<?php

namespace App\Models;

use App\Helpers\Database;

class Notification extends BaseModel
{
    protected $table = 'notifications';

    public function getUserNotifications(int $user_id, int $limit = 20): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            "SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY created_at DESC LIMIT $limit"
        );
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getUnreadCount(int $user_id): int
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) as count FROM {$this->table} WHERE user_id = ? AND is_read = 0"
        );
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }

    public function markAsRead(int $notification_id, ?int $user_id = null): bool
    {
        if ($user_id) {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare(
                "UPDATE {$this->table} SET is_read = 1 WHERE id = ? AND user_id = ?"
            );
            return $stmt->execute([$notification_id, $user_id]);
        }
        return $this->update($notification_id, ['is_read' => 1]);
    }

    public function markAllAsRead(int $user_id): bool
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            "UPDATE {$this->table} SET is_read = 1 WHERE user_id = ?"
        );
        return $stmt->execute([$user_id]);
    }

    public function createNotification(int $user_id, string $type, string $title, string $message, ?int $related_user_id = null, ?int $related_document_id = null): int
    {
        return $this->insert([
            'user_id' => $user_id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'related_user_id' => $related_user_id,
            'related_document_id' => $related_document_id,
        ]);
    }
}
