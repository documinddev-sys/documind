<?php

namespace App\Models;

use App\Helpers\Database;

class AiChat extends BaseModel
{
    protected $table = 'ai_chats';

    public function getChatHistory(int $document_id, int $user_id, int $limit = 5): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            "SELECT * FROM {$this->table} 
             WHERE document_id = ? AND user_id = ? 
             ORDER BY created_at DESC LIMIT $limit"
        );
        $stmt->execute([$document_id, $user_id]);

        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        // Reverse to get chronological order
        return array_reverse($results);
    }

    public function addMessage(int $document_id, int $user_id, string $role, string $message, ?int $token_count = null): int
    {
        return $this->insert([
            'document_id' => $document_id,
            'user_id' => $user_id,
            'role' => $role,
            'message' => $message,
            'token_count' => $token_count,
        ]);
    }

    public function getDocumentChatCount(int $document_id): int
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) as count FROM {$this->table} WHERE document_id = ?"
        );
        $stmt->execute([$document_id]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }

    public function clearChatHistory(int $document_id, int $user_id): bool
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            "DELETE FROM {$this->table} WHERE document_id = ? AND user_id = ?"
        );
        return $stmt->execute([$document_id, $user_id]);
    }
}
