<?php

namespace App\Models;

use App\Helpers\Database;

class DocumentShare extends BaseModel
{
    protected $table = 'document_shares';

    public function getSharedDocuments(int $user_id, int $limit = 20, int $offset = 0): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            "SELECT d.*, u.name as owner_name, ds.permission 
             FROM {$this->table} ds
             JOIN documents d ON ds.document_id = d.id
             JOIN users u ON ds.owner_id = u.id
             WHERE ds.shared_with_id = ? 
             ORDER BY ds.shared_at DESC LIMIT $limit OFFSET $offset"
        );
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function countSharedDocuments(int $user_id): int
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) as count FROM {$this->table} WHERE shared_with_id = ?"
        );
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }

    public function getDocumentShares(int $document_id): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            "SELECT ds.*, u.name, u.email FROM {$this->table} ds
             JOIN users u ON ds.shared_with_id = u.id
             WHERE ds.document_id = ? ORDER BY ds.shared_at DESC"
        );
        $stmt->execute([$document_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function checkPermission(int $document_id, int $user_id): ?string
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            "SELECT permission FROM {$this->table} 
             WHERE document_id = ? AND shared_with_id = ? LIMIT 1"
        );
        $stmt->execute([$document_id, $user_id]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['permission'] ?? null;
    }

    public function isSharedWith(int $document_id, int $user_id): bool
    {
        return $this->checkPermission($document_id, $user_id) !== null;
    }
}
