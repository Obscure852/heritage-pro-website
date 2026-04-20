<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\Library\LibraryFine;
use App\Services\Library\DashboardService;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DashboardController extends Controller {
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService) {
        $this->middleware('auth');
        $this->dashboardService = $dashboardService;
    }

    /**
     * Display the library dashboard with today's stats, due-today list, collection summary,
     * overdue brackets, popular books, and recent activity feed.
     */
    public function index(): View {
        Gate::authorize('manage-library');

        $todayStats = $this->dashboardService->todayStats();
        $dueToday = $this->dashboardService->dueToday();
        $collectionSummary = $this->dashboardService->collectionSummary();
        $overdueData = $this->dashboardService->overdueByBracket();
        $popularBooks = $this->dashboardService->popularBooks();
        $recentActivity = $this->dashboardService->recentActivity();
        $unpaidFinesCount = LibraryFine::unpaid()->count();

        return view('library.dashboard.index', compact(
            'todayStats', 'dueToday', 'collectionSummary',
            'overdueData', 'popularBooks', 'recentActivity', 'unpaidFinesCount'
        ));
    }
}
