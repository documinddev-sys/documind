<?php

namespace App\Models;

use App\Helpers\Database;

class ActivityLog extends BaseModel
{
    protected $table = 'activity_logs';

    public function logActivity(int $user_id, string $action, string $entityType, ?int $entityId = null, ?string $description = null): int
    {
        return $this->insert([
            'user_id' => $user_id,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }

    public function getUserActivityLog(int $user_id, int $limit = 50): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            "SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY created_at DESC LIMIT $limit"
        );
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getSystemActivityLog(int $limit = 100): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            "SELECT al.*, u.name as user_name, u.email 
             FROM {$this->table} al
             JOIN users u ON al.user_id = u.id
             ORDER BY al.created_at DESC LIMIT $limit"
        );
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getRecentActivity(int $limit = 10): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            "SELECT al.*, u.name as user_name FROM {$this->table} al
             JOIN users u ON al.user_id = u.id
             ORDER BY al.created_at DESC LIMIT $limit"
        );
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
