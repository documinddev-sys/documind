<?php

namespace App\Models;

use App\Helpers\Database;

/**
 * Tracks per-user daily AI message consumption.
 *
 * Uses MySQL INSERT ... ON DUPLICATE KEY UPDATE for atomic increment.
 * No cron needed — the DATE column naturally partitions usage per day.
 */
class AiUsage extends BaseModel
{
    protected $table = 'ai_daily_usage';

    /**
     * Atomically check remaining quota AND increment if allowed.
     * Returns remaining messages AFTER this usage, or negative if limit hit.
     */
    public function tryConsumeMessage(int $userId, int $dailyLimit): int
    {
        $pdo = Database::getInstance();
        $today = date('Y-m-d');

        // Atomic upsert: insert with count=1 or increment if under limit
        $stmt = $pdo->prepare(
            "INSERT INTO {$this->table} (user_id, usage_date, message_count)
             VALUES (?, ?, 1)
             ON DUPLICATE KEY UPDATE
               message_count = IF(message_count < ?, message_count + 1, message_count)"
        );
        $stmt->execute([$userId, $today, $dailyLimit]);

        // Read current count
        $stmt = $pdo->prepare(
            "SELECT message_count FROM {$this->table}
             WHERE user_id = ? AND usage_date = ?"
        );
        $stmt->execute([$userId, $today]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $currentCount = (int)($row['message_count'] ?? 0);

        return $dailyLimit - $currentCount;
    }

    /**
     * Get today's usage count (read-only, for UI display)
     */
    public function getTodayUsage(int $userId): int
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            "SELECT message_count FROM {$this->table}
             WHERE user_id = ? AND usage_date = CURDATE()"
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int)($row['message_count'] ?? 0);
    }
}
