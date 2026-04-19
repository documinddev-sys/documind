<?php

namespace App\Controllers;

use App\Models\Document;
use App\Models\ActivityLog;
use App\Services\AnalyticsService;

class DashboardController extends BaseController
{
    private $documentModel;
    private $activityModel;
    private $analyticsService;

    public function __construct()
    {
        // Check authentication
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/documind/public/login');
        }

        $this->documentModel = new Document();
        $this->activityModel = new ActivityLog();
        $this->analyticsService = new AnalyticsService();
    }

    /**
     * Show main user dashboard
     */
    public function index(): void
    {
        $userId = $_SESSION['user_id'];
        
        // Fetch real stats
        $stats = $this->analyticsService->getUserStats($userId);
        
        // Fetch 5 most recent documents
        $recentDocuments = $this->documentModel->getUserDocuments($userId, 5);
        
        // Fetch recent activity
        $recentActivity = $this->activityModel->getUserActivityLog($userId, 5);

        // Fetch storage limit (mocked for now, could be in config or user table)
        $storageLimitMb = 100; // 100MB limit
        $storageUsagePercent = ($stats['storage_mb'] / $storageLimitMb) * 100;

        $this->render('dashboard.index', [
            'title' => 'Dashboard',
            'stats' => $stats,
            'recentDocuments' => $recentDocuments,
            'recentActivity' => $recentActivity,
            'storageUsagePercent' => min(100, $storageUsagePercent),
            'storageLimitMb' => $storageLimitMb
        ]);
    }
}
