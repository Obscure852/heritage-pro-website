<?php

namespace App\Http\Controllers;

use App\Services\NotificationAnalyticsService;
use Illuminate\Http\Request;

class NotificationAnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(NotificationAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Display the analytics dashboard
     */
    public function dashboard(Request $request)
    {
        $days = $request->get('days', 30);

        // Get all analytics data
        $successRate = $this->analyticsService->getSuccessRate($days);
        $costAnalytics = $this->analyticsService->getCostAnalytics();
        $topSenders = $this->analyticsService->getTopSenders(10, $days);
        $monthlyTrends = $this->analyticsService->getMonthlyTrends();
        $failureReasons = $this->analyticsService->getFailureReasons($days);

        return view('notifications.analytics.dashboard', compact(
            'successRate',
            'costAnalytics',
            'topSenders',
            'monthlyTrends',
            'failureReasons',
            'days'
        ));
    }

    /**
     * Get success rate data (AJAX)
     */
    public function getSuccessRate(Request $request)
    {
        $days = $request->get('days', 30);
        $data = $this->analyticsService->getSuccessRate($days);

        return response()->json($data);
    }

    /**
     * Get cost analytics data (AJAX)
     */
    public function getCostAnalytics(Request $request)
    {
        $termId = $request->get('term_id');
        $data = $this->analyticsService->getCostAnalytics($termId);

        return response()->json($data);
    }

    /**
     * Get usage statistics data (AJAX)
     */
    public function getUsageStats(Request $request)
    {
        $days = $request->get('days', 30);
        $groupBy = $request->get('group_by', 'day');

        $data = $this->analyticsService->getUsageStats($days, $groupBy);

        return response()->json($data);
    }

    /**
     * Get top senders data (AJAX)
     */
    public function getTopSenders(Request $request)
    {
        $limit = $request->get('limit', 10);
        $days = $request->get('days', 30);

        $data = $this->analyticsService->getTopSenders($limit, $days);

        return response()->json($data);
    }
}
