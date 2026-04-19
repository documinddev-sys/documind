<?php

namespace App\Controllers;

use App\Services\AnalyticsService;

class AnalyticsController extends BaseController
{
    private $analyticsService;

    public function __construct()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }
        $this->analyticsService = new AnalyticsService();
    }

    public function index(): void
    {
        $userId = $_SESSION['user_id'];
        
        $stats = $this->analyticsService->getUserStats($userId);
        
        // Fetch real activity log density past 7 days
        $trends = $this->analyticsService->getUserActivityTrend($userId, 7);
        $trendMap = [];
        foreach ($trends as $t) {
            $trendMap[$t['date']] = $t['count'];
        }

        $activityDensity = [];
        for ($i=6; $i>=0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $displayDate = date('M d', strtotime("-$i days"));
            $activityDensity[] = [
                'day' => $displayDate,
                'count' => $trendMap[$date] ?? 0
            ];
        }

        $this->render('user.analytics', [
            'title' => 'My Analytics',
            'stats' => $stats,
            'activityDensity' => $activityDensity
        ]);
    }
}
