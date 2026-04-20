<?php

namespace App\Http\Controllers\Leave;

use App\Exports\Leave\LeaveCarryOverExport;
use App\Exports\Leave\LeaveOutstandingExport;
use App\Exports\Leave\LeavePersonalHistoryExport;
use App\Exports\Leave\LeaveTeamSummaryExport;
use App\Exports\Leave\LeaveUtilizationExport;
use App\Helpers\TermHelper;
use App\Http\Controllers\Controller;
use App\Models\Leave\LeaveBalance;
use App\Models\Leave\LeaveType;
use App\Models\Term;
use App\Models\User;
use App\Services\Leave\LeaveBalanceService;
use App\Services\Leave\LeaveReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Controller for viewing leave reports.
 *
 * Provides HR interface for organization-wide leave reports (utilization,
 * outstanding balances, carry-over) and manager view for team summaries.
 */
class LeaveReportController extends Controller {
    /**
     * The leave report service instance.
     *
     * @var LeaveReportService
     */
    protected LeaveReportService $leaveReportService;

    /**
     * The leave balance service instance.
     *
     * @var LeaveBalanceService
     */
    protected LeaveBalanceService $leaveBalanceService;

    /**
     * Create a new controller instance.
     *
     * @param LeaveReportService $leaveReportService
     * @param LeaveBalanceService $leaveBalanceService
     */
    public function __construct(
        LeaveReportService $leaveReportService,
        LeaveBalanceService $leaveBalanceService
    ) {
        $this->middleware('auth');
        $this->leaveReportService = $leaveReportService;
        $this->leaveBalanceService = $leaveBalanceService;
    }

    /**
     * Get current year from TermHelper.
     *
     * @return int
     */
    protected function getCurrentYear(): int {
        $currentTerm = TermHelper::getCurrentTerm();
        return $currentTerm ? (int) $currentTerm->year : (int) date('Y');
    }

    /**
     * Get available years from Term table.
     *
     * @return array
     */
    protected function getYearsFromTerms(): array {
        return Term::distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();
    }

    /**
     * Display the organization utilization report.
     *
     * Shows organization-wide stats, leave type distribution, and monthly trend.
     * Implements RPTS-04 and RPTS-05.
     *
     * @param Request $request
     * @return View
     */
    public function utilization(Request $request): View {
        $currentYear = $this->getCurrentYear();
        $selectedYear = (int) $request->input('year', $currentYear);

        // Get organization stats
        $stats = $this->leaveReportService->getOrganizationStats($selectedYear);

        // Get leave type distribution
        $distribution = $this->leaveReportService->getLeaveTypeDistribution($selectedYear);

        // Get monthly usage trend
        $trend = $this->leaveReportService->getMonthlyUsageTrend($selectedYear);

        // Get available years from Term table
        $years = $this->getYearsFromTerms();

        return view('leave.reports.utilization', [
            'stats' => $stats,
            'distribution' => $distribution,
            'trend' => $trend,
            'years' => $years,
            'currentYear' => $currentYear,
            'selectedYear' => $selectedYear,
        ]);
    }

    /**
     * Display the outstanding balances report.
     *
     * Shows users with available balance > 0, filterable by year and leave type.
     * Implements RPTS-06.
     *
     * @param Request $request
     * @return View
     */
    public function outstanding(Request $request): View {
        $currentYear = $this->getCurrentYear();
        $selectedYear = (int) $request->input('year', $currentYear);
        $selectedLeaveTypeId = $request->input('leave_type_id');

        // Get outstanding balances
        $balances = $this->leaveReportService->getOutstandingBalances(
            $selectedYear,
            $selectedLeaveTypeId ? (int) $selectedLeaveTypeId : null
        );

        // Calculate summary stats
        $totalRecords = $balances->count();
        $totalOutstanding = $balances->sum('available');

        // Get leave types for filter
        $leaveTypes = LeaveType::active()
            ->orderBy('name')
            ->select('id', 'name', 'code')
            ->get();

        // Get available years from Term table
        $years = $this->getYearsFromTerms();

        // Paginate results manually (collection-based pagination)
        $page = $request->input('page', 1);
        $perPage = 25;
        $offset = ($page - 1) * $perPage;

        $paginatedBalances = new \Illuminate\Pagination\LengthAwarePaginator(
            $balances->slice($offset, $perPage)->values(),
            $balances->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('leave.reports.outstanding', [
            'balances' => $paginatedBalances,
            'leaveTypes' => $leaveTypes,
            'years' => $years,
            'currentYear' => $currentYear,
            'selectedYear' => $selectedYear,
            'selectedLeaveTypeId' => $selectedLeaveTypeId,
            'totalRecords' => $totalRecords,
            'totalOutstanding' => $totalOutstanding,
        ]);
    }

    /**
     * Display the carry-over report.
     *
     * Shows what was carried over between years and what was forfeited.
     * Implements RPTS-07.
     *
     * @param Request $request
     * @return View
     */
    public function carryover(Request $request): View {
        $currentYear = $this->getCurrentYear();

        // Defaults: from previous year to current year
        $selectedFromYear = (int) $request->input('from_year', $currentYear - 1);
        $selectedToYear = (int) $request->input('to_year', $currentYear);

        // Get carry-over data
        $carryoverData = $this->leaveReportService->getCarryOverReport($selectedFromYear, $selectedToYear);

        // Calculate summary stats
        $totalCarriedOver = $carryoverData->sum('carried_over');
        $totalForfeited = $carryoverData->sum('forfeited');

        // Get available years from Term table
        $years = $this->getYearsFromTerms();

        return view('leave.reports.carryover', [
            'carryoverData' => $carryoverData,
            'years' => $years,
            'currentYear' => $currentYear,
            'selectedFromYear' => $selectedFromYear,
            'selectedToYear' => $selectedToYear,
            'totalCarriedOver' => $totalCarriedOver,
            'totalForfeited' => $totalForfeited,
        ]);
    }

    /**
     * Display the team summary report.
     *
     * Shows leave summary for manager's direct reports.
     * Implements RPTS-03.
     *
     * @param Request $request
     * @return View
     */
    public function teamSummary(Request $request): View {
        $currentYear = $this->getCurrentYear();
        $selectedYear = (int) $request->input('year', $currentYear);
        $user = auth()->user();

        // Get team summary
        $teamSummary = $this->leaveReportService->getTeamSummary($user->id, $selectedYear);

        // Get direct reports for display
        $directReports = User::where('reporting_to', $user->id)
            ->where('status', 'Current')
            ->orderBy('name')
            ->select('id', 'name', 'department')
            ->get();

        // Get available years from Term table
        $years = $this->getYearsFromTerms();

        return view('leave.reports.team-summary', [
            'teamSummary' => $teamSummary,
            'directReports' => $directReports,
            'years' => $years,
            'currentYear' => $currentYear,
            'selectedYear' => $selectedYear,
        ]);
    }

    // ==================== EXPORT METHODS ====================

    /**
     * Export utilization report to Excel.
     *
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function exportUtilization(Request $request): BinaryFileResponse {
        $currentYear = $this->getCurrentYear();
        $selectedYear = (int) $request->input('year', $currentYear);

        // Get data for export
        $stats = $this->leaveReportService->getOrganizationStats($selectedYear);
        $distribution = $this->leaveReportService->getLeaveTypeDistribution($selectedYear);

        $filename = "leave-utilization-{$selectedYear}.xlsx";

        return Excel::download(
            new LeaveUtilizationExport($stats, $distribution, $selectedYear),
            $filename
        );
    }

    /**
     * Export outstanding balances report to Excel.
     *
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function exportOutstanding(Request $request): BinaryFileResponse {
        $currentYear = $this->getCurrentYear();
        $selectedYear = (int) $request->input('year', $currentYear);
        $selectedLeaveTypeId = $request->input('leave_type_id');

        // Get data for export
        $balances = $this->leaveReportService->getOutstandingBalances(
            $selectedYear,
            $selectedLeaveTypeId ? (int) $selectedLeaveTypeId : null
        );

        // Get leave type name for filename
        $leaveTypeName = null;
        if ($selectedLeaveTypeId) {
            $leaveType = LeaveType::find($selectedLeaveTypeId);
            $leaveTypeName = $leaveType ? $leaveType->name : null;
        }

        $filename = "outstanding-balances-{$selectedYear}.xlsx";
        if ($leaveTypeName) {
            $safeTypeName = preg_replace('/[^a-zA-Z0-9-_]/', '-', $leaveTypeName);
            $filename = "outstanding-balances-{$safeTypeName}-{$selectedYear}.xlsx";
        }

        return Excel::download(
            new LeaveOutstandingExport($balances, $selectedYear, $leaveTypeName),
            $filename
        );
    }

    /**
     * Export carry-over report to Excel.
     *
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function exportCarryover(Request $request): BinaryFileResponse {
        $currentYear = $this->getCurrentYear();
        $selectedFromYear = (int) $request->input('from_year', $currentYear - 1);
        $selectedToYear = (int) $request->input('to_year', $currentYear);

        // Get data for export
        $carryoverData = $this->leaveReportService->getCarryOverReport($selectedFromYear, $selectedToYear);

        $filename = "carryover-{$selectedFromYear}-to-{$selectedToYear}.xlsx";

        return Excel::download(
            new LeaveCarryOverExport($carryoverData, $selectedFromYear, $selectedToYear),
            $filename
        );
    }

    /**
     * Export team summary report to Excel.
     *
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function exportTeamSummary(Request $request): BinaryFileResponse {
        $currentYear = $this->getCurrentYear();
        $selectedYear = (int) $request->input('year', $currentYear);
        $user = auth()->user();

        // Get data for export
        $teamSummary = $this->leaveReportService->getTeamSummary($user->id, $selectedYear);
        $upcomingLeave = $teamSummary['upcoming_leave'] ?? collect();

        $filename = "team-summary-{$selectedYear}.xlsx";

        return Excel::download(
            new LeaveTeamSummaryExport($teamSummary, $upcomingLeave, $selectedYear),
            $filename
        );
    }

    /**
     * Export personal leave history to Excel.
     *
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function exportPersonalHistory(Request $request): BinaryFileResponse {
        $currentYear = $this->getCurrentYear();
        $selectedYear = (int) $request->input('year', $currentYear);
        $user = auth()->user();

        // Get data for export
        $requests = $this->leaveReportService->getPersonalHistory($user->id, $selectedYear);

        // Eager load approver relationship for export
        $requests->load('approver');

        $userName = $user->full_name ?? ($user->firstname . ' ' . $user->lastname);
        $filename = "my-leave-history-{$selectedYear}.xlsx";

        return Excel::download(
            new LeavePersonalHistoryExport($requests, $userName, $selectedYear),
            $filename
        );
    }
}
