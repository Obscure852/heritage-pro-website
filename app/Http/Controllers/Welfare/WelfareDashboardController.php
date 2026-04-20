<?php

namespace App\Http\Controllers\Welfare;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Welfare\WelfareAuditLog;
use App\Models\Welfare\WelfareType;
use App\Services\Welfare\WelfareReportingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class WelfareDashboardController extends Controller
{
    protected WelfareReportingService $reportingService;

    public function __construct(WelfareReportingService $reportingService)
    {
        $this->reportingService = $reportingService;
    }

    /**
     * Display the welfare dashboard.
     */
    public function index()
    {
        Gate::authorize('access-welfare');

        $dashboardData = $this->reportingService->getDashboardData();
        $welfareTypes = WelfareType::active()->get();

        return view('welfare.dashboard', compact('dashboardData', 'welfareTypes'));
    }

    /**
     * Display statistics page.
     */
    public function statistics()
    {
        Gate::authorize('access-welfare');

        $stats = $this->reportingService->getCaseStats();
        $trends = $this->reportingService->getTrends(12);

        return view('welfare.statistics', compact('stats', 'trends'));
    }

    /**
     * Display reports page.
     */
    public function reports(Request $request)
    {
        Gate::authorize('access-welfare');

        $termId = $request->input('term_id');
        $report = $this->reportingService->generateTermReport($termId);

        return view('welfare.reports', compact('report'));
    }

    /**
     * Export welfare data.
     */
    public function export(Request $request)
    {
        Gate::authorize('export-welfare');

        $filters = $request->only(['date_from', 'date_to', 'status', 'welfare_type_id']);
        $data = $this->reportingService->exportData($filters);

        // Return as download
        $filename = 'welfare_export_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'Case Number',
                'Student',
                'Type',
                'Status',
                'Priority',
                'Opened By',
                'Assigned To',
                'Opened At',
                'Closed At',
            ]);

            foreach ($data as $case) {
                fputcsv($file, [
                    $case->case_number,
                    $case->student->full_name ?? '',
                    $case->welfareType->name ?? '',
                    $case->status,
                    $case->priority,
                    $case->openedBy->full_name ?? '',
                    $case->assignedTo->full_name ?? '',
                    $case->opened_at?->format('Y-m-d H:i'),
                    $case->closed_at?->format('Y-m-d H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Display student welfare profile.
     */
    public function studentProfile(Student $student)
    {
        Gate::authorize('access-welfare');

        $summary = $this->reportingService->getStudentWelfareSummary($student->id);

        // Load recent cases
        $recentCases = $student->welfareCases()
            ->with(['welfareType', 'openedBy'])
            ->orderBy('opened_at', 'desc')
            ->limit(10)
            ->get();

        return view('welfare.student-profile', compact('student', 'summary', 'recentCases'));
    }

    /**
     * Display student welfare history.
     */
    public function studentHistory(Student $student)
    {
        Gate::authorize('access-welfare');

        $cases = $student->welfareCases()
            ->with(['welfareType', 'openedBy', 'assignedTo'])
            ->orderBy('opened_at', 'desc')
            ->paginate(20);

        return view('welfare.student-history', compact('student', 'cases'));
    }

    /**
     * Display audit log.
     */
    public function auditLog(Request $request)
    {
        Gate::authorize('view-welfare-audit');

        $query = WelfareAuditLog::with(['user', 'welfareCase.student']);

        // Apply filters
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(50);

        return view('welfare.audit-log', compact('logs'));
    }
}
