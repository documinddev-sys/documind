<?php

namespace App\Services;

use App\Models\User;
use App\Models\Document;
use App\Models\ActivityLog;
use App\Models\Notification;

class AnalyticsService
{
    private $userModel;
    private $documentModel;
    private $activityModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->documentModel = new Document();
        $this->activityModel = new ActivityLog();
    }

    /**
     * Get system statistics for admin dashboard
     */
    public function getSystemStats(): array
    {
        try {
            $pdo = \App\Helpers\Database::getInstance();
            
            // Total users
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
            $totalUsers = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;

            // Active users (last 30 days)
            $stmt = $pdo->query(
                "SELECT COUNT(DISTINCT user_id) as count FROM activity_logs 
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
            );
            $activeUsers = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;

            // Total documents
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM documents");
            $totalDocuments = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;

            // Total storage (sum of all document file sizes)
            $stmt = $pdo->query("SELECT SUM(file_size) as total FROM documents");
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $totalStorage = $result['total'] ?? 0;

            // Documents uploaded today
            $stmt = $pdo->query(
                "SELECT COUNT(*) as count FROM documents WHERE DATE(upload_date) = CURDATE()"
            );
            $uploadsToday = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;

            return [
                'total_users' => $totalUsers,
                'active_users' => $activeUsers,
                'total_documents' => $totalDocuments,
                'total_storage_mb' => round($totalStorage / 1024 / 1024, 2),
                'uploads_today' => $uploadsToday,
            ];
        } catch (\Exception $e) {
            return [
                'total_users' => 0,
                'active_users' => 0,
                'total_documents' => 0,
                'total_storage_mb' => 0,
                'uploads_today' => 0,
            ];
        }
    }

    /**
     * Get user statistics
     */
    public function getUserStats(int $user_id): array
    {
        try {
            $pdo = \App\Helpers\Database::getInstance();
            
            // User's documents
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM documents WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $totalDocuments = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;

            // User's storage
            $stmt = $pdo->prepare("SELECT SUM(file_size) as total FROM documents WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $totalStorageMb = round(($result['total'] ?? 0) / 1024 / 1024, 2);

            // Total views of user's documents
            $stmt = $pdo->prepare("SELECT SUM(view_count) as total FROM documents WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $totalViews = $result['total'] ?? 0;

            // Recent activity count
            $stmt = $pdo->prepare(
                "SELECT COUNT(*) as count FROM activity_logs 
                 WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
            );
            $stmt->execute([$user_id]);
            $recentActivity = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;

            return [
                'total_documents' => $totalDocuments,
                'storage_mb' => $totalStorageMb,
                'total_views' => $totalViews,
                'recent_activity' => $recentActivity,
            ];
        } catch (\Exception $e) {
            return [
                'total_documents' => 0,
                'storage_mb' => 0,
                'total_views' => 0,
                'recent_activity' => 0,
            ];
        }
    }

    /**
     * Get document statistics
     */
    public function getDocumentStats(int $document_id, int $user_id): array
    {
        try {
            $pdo = \App\Helpers\Database::getInstance();
            $stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ? AND user_id = ?");
            $stmt->execute([$document_id, $user_id]);
            $doc = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$doc) {
                return [];
            }

            return [
                'file_size_mb' => round($doc['file_size'] / 1024 / 1024, 2),
                'view_count' => $doc['view_count'],
                'last_accessed' => $doc['last_accessed'],
                'upload_date' => $doc['upload_date'],
                'ai_processed' => $doc['ai_processed'],
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get upload trends (last 7 days)
     */
    public function getUploadTrends(): array
    {
        try {
            $pdo = \App\Helpers\Database::getInstance();
            $stmt = $pdo->query(
                "SELECT DATE(upload_date) as date, COUNT(*) as count 
                 FROM documents 
                 WHERE upload_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                 GROUP BY DATE(upload_date)
                 ORDER BY date ASC"
            );
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get user-specific activity trends (last N days)
     */
    public function getUserActivityTrend(int $user_id, int $days = 7): array
    {
        try {
            $pdo = \App\Helpers\Database::getInstance();
            $stmt = $pdo->prepare(
                "SELECT DATE(created_at) as date, COUNT(*) as count 
                 FROM activity_logs 
                 WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                 GROUP BY DATE(created_at)
                 ORDER BY date ASC"
            );
            $stmt->execute([$user_id, $days]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get most active users
     */
    public function getMostActiveUsers(int $limit = 10): array
    {
        try {
            $pdo = \App\Helpers\Database::getInstance();
            $stmt = $pdo->query(
                "SELECT u.id, u.name, u.email, COUNT(al.id) as activity_count
                 FROM users u
                 LEFT JOIN activity_logs al ON u.id = al.user_id
                 WHERE al.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                 GROUP BY u.id
                 ORDER BY activity_count DESC
                 LIMIT $limit"
            );
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }
}
