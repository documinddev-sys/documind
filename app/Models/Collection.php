<?php

namespace App\Models;

use App\Helpers\Database;

class Collection extends BaseModel
{
    protected $table = 'collections';

    public function getUserCollections(int $user_id): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            "SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY created_at DESC"
        );
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getCollectionWithDocuments(int $collection_id, int $user_id): ?array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            "SELECT * FROM {$this->table} WHERE id = ? AND user_id = ?"
        );
        $stmt->execute([$collection_id, $user_id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function getCollectionDocuments(int $collection_id, int $limit = 50, int $offset = 0): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            "SELECT d.* FROM documents d
             JOIN collection_documents cd ON d.id = cd.document_id
             WHERE cd.collection_id = ? 
             ORDER BY cd.added_at DESC LIMIT $limit OFFSET $offset"
        );
        $stmt->execute([$collection_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function countCollectionDocuments(int $collection_id): int
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) as count FROM collection_documents WHERE collection_id = ?"
        );
        $stmt->execute([$collection_id]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }

    public function addDocumentToCollection(int $collection_id, int $document_id): bool
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare(
                "INSERT INTO collection_documents (collection_id, document_id) VALUES (?, ?)"
            );
            return $stmt->execute([$collection_id, $document_id]);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function removeDocumentFromCollection(int $collection_id, int $document_id): bool
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            "DELETE FROM collection_documents WHERE collection_id = ? AND document_id = ?"
        );
        return $stmt->execute([$collection_id, $document_id]);
    }
}
