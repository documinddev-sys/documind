<?php

namespace App\Models;

use App\Helpers\Database;

class Document extends BaseModel
{
    protected $table = 'documents';

    /**
     * Find document by ID owned by a specific user (any status).
     */
    public function findByIdAndUser(int $id, int $userId): ?array
    {
        $stmt = $this->query(
            "SELECT * FROM {$this->table} WHERE id = ? AND user_id = ? LIMIT 1",
            [$id, $userId]
        );
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Centralised visibility check — single source of truth.
     *
     * Rules:
     *  - Owner always sees own docs (any status)
     *  - Admin always sees all docs (any status)
     *  - Shared recipients see doc IF status = 'approved'
     *  - Public visitors see doc IF status = 'approved' AND is_public = 1
     */
    public function findByIdWithVisibility(int $id, ?int $userId = null, string $userRole = 'user'): ?array
    {
        $pdo = Database::getInstance();

        // Admin: unrestricted
        if ($userRole === 'admin') {
            return $this->findById($id);
        }

        // Authenticated user: own doc OR approved+shared OR approved+public
        if ($userId !== null) {
            $stmt = $pdo->prepare(
                "SELECT d.* FROM {$this->table} d
                 LEFT JOIN document_shares ds ON d.id = ds.document_id AND ds.shared_with_id = ?
                 WHERE d.id = ?
                   AND (
                       d.user_id = ?
                       OR (d.status = 'approved' AND ds.shared_with_id IS NOT NULL)
                       OR (d.status = 'approved' AND d.is_public = 1)
                   )
                 LIMIT 1"
            );
            $stmt->execute([$userId, $id, $userId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        }

        // Unauthenticated visitor: approved + public only
        $stmt = $pdo->prepare(
            "SELECT * FROM {$this->table}
             WHERE id = ? AND status = 'approved' AND is_public = 1
             LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Legacy access check — wraps unified method for backward compatibility.
     */
    public function findByIdWithAccess(int $id, int $userId): ?array
    {
        return $this->findByIdWithVisibility($id, $userId, $_SESSION['user_role'] ?? 'user');
    }

    public function getUserDocuments(int $userId, int $limit = 20, int $offset = 0): array
    {
        $stmt = $this->query(
            "SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY upload_date DESC LIMIT $limit OFFSET $offset",
            [$userId]
        );
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function countUserDocuments(int $userId): int
    {
        $stmt = $this->query(
            "SELECT COUNT(*) as count FROM {$this->table} WHERE user_id = ?",
            [$userId]
        );
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int)($result['count'] ?? 0);
    }

    public function searchUserDocuments(int $userId, string $query, int $limit = 20, int $offset = 0): array
    {
        // Try FULLTEXT search first
        $stmt = $this->query(
            "SELECT * FROM {$this->table} 
             WHERE user_id = ? 
             AND MATCH(original_name, full_text) AGAINST(? IN BOOLEAN MODE)
             ORDER BY upload_date DESC LIMIT $limit OFFSET $offset",
            [$userId, $query]
        );

        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Fallback to LIKE if FULLTEXT returns no results
        if (empty($results) && strlen($query) >= 3) {
            $likeQuery = '%' . $query . '%';
            $stmt = $this->query(
                "SELECT * FROM {$this->table} 
                 WHERE user_id = ? 
                 AND (original_name LIKE ? OR keywords LIKE ? OR full_text LIKE ?)
                 ORDER BY upload_date DESC LIMIT $limit OFFSET $offset",
                [$userId, $likeQuery, $likeQuery, $likeQuery]
            );
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        return $results;
    }

    // ── Phase 5: Status & Public queries ──

    /**
     * Get documents by approval status with owner info (admin views).
     */
    public function getDocumentsByStatus(string $status, int $limit = 20, int $offset = 0): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            "SELECT d.*, u.name AS owner_name, u.email AS owner_email
             FROM {$this->table} d
             JOIN users u ON d.user_id = u.id
             WHERE d.status = ?
             ORDER BY d.upload_date DESC
             LIMIT $limit OFFSET $offset"
        );
        $stmt->execute([$status]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function countByStatus(string $status): int
    {
        $stmt = $this->query(
            "SELECT COUNT(*) AS count FROM {$this->table} WHERE status = ?",
            [$status]
        );
        return (int)($stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0);
    }

    /**
     * Public library: approved + public documents with relevance ranking.
     * Excludes full_text from SELECT for performance.
     */
    public function getApprovedPublicDocuments(int $limit = 12, int $offset = 0, string $search = '', string $typeFilter = ''): array
    {
        $pdo = Database::getInstance();
        $paramsWhere = [];
        $paramsOrder = [];
        $where = "d.status = 'approved' AND d.is_public = 1";
        $orderBy = "d.upload_date DESC";

        if (!empty($search)) {
            $where .= " AND MATCH(d.original_name, d.full_text) AGAINST(? IN BOOLEAN MODE)";
            $orderBy = "MATCH(d.original_name, d.full_text) AGAINST(? IN BOOLEAN MODE) DESC, d.upload_date DESC";
            $paramsWhere[] = $search;
            $paramsOrder[] = $search;
        }

        if (!empty($typeFilter) && in_array($typeFilter, ['pdf', 'docx'])) {
            $where .= " AND d.file_type = ?";
            $paramsWhere[] = $typeFilter;
        }

        $sql = "SELECT d.id, d.original_name, d.file_type, d.file_size, d.summary,
                       d.keywords, d.upload_date, d.view_count, u.name AS owner_name
                FROM {$this->table} d
                JOIN users u ON d.user_id = u.id
                WHERE $where
                ORDER BY $orderBy
                LIMIT $limit OFFSET $offset";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_merge($paramsWhere, $paramsOrder));
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function countApprovedPublic(string $search = '', string $typeFilter = ''): int
    {
        $pdo = Database::getInstance();
        $params = [];
        $where = "status = 'approved' AND is_public = 1";

        if (!empty($search)) {
            $where .= " AND MATCH(original_name, full_text) AGAINST(? IN BOOLEAN MODE)";
            $params[] = $search;
        }

        if (!empty($typeFilter) && in_array($typeFilter, ['pdf', 'docx'])) {
            $where .= " AND file_type = ?";
            $params[] = $typeFilter;
        }

        $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM {$this->table} WHERE $where");
        $stmt->execute($params);
        return (int)($stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0);
    }

    /**
     * Detect duplicate upload (same user + filename + file size).
     */
    public function findDuplicate(int $userId, string $originalName, int $fileSize): ?array
    {
        $stmt = $this->query(
            "SELECT id FROM {$this->table}
             WHERE user_id = ? AND original_name = ? AND file_size = ?
             LIMIT 1",
            [$userId, $originalName, $fileSize]
        );
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get all documents with owner info for admin (any status).
     */
    public function getAllDocumentsAdmin(int $limit = 20, int $offset = 0, string $statusFilter = ''): array
    {
        $pdo = Database::getInstance();
        $where = "1=1";
        $params = [];

        if (!empty($statusFilter) && in_array($statusFilter, ['pending', 'approved', 'rejected'])) {
            $where .= " AND d.status = ?";
            $params[] = $statusFilter;
        }

        $stmt = $pdo->prepare(
            "SELECT d.*, u.name AS owner_name FROM {$this->table} d
             JOIN users u ON d.user_id = u.id
             WHERE $where
             ORDER BY d.upload_date DESC
             LIMIT $limit OFFSET $offset"
        );
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function countAllDocumentsAdmin(string $statusFilter = ''): int
    {
        $pdo = Database::getInstance();
        $where = "1=1";
        $params = [];

        if (!empty($statusFilter) && in_array($statusFilter, ['pending', 'approved', 'rejected'])) {
            $where .= " AND status = ?";
            $params[] = $statusFilter;
        }

        $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM {$this->table} WHERE $where");
        $stmt->execute($params);
        return (int)($stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0);
    }
}
