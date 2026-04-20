<?php

namespace App\Http\Controllers\Leave;

use App\Helpers\TermHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Leave\StoreLeaveBalanceAdjustmentRequest;
use App\Models\Leave\LeaveBalance;
use App\Models\Leave\LeaveType;
use App\Models\Term;
use App\Models\User;
use App\Services\Leave\LeaveAuditService;
use App\Services\Leave\LeaveBalanceService;
use App\Services\Leave\LeavePolicyService;
use App\Services\Leave\LeaveRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller for managing leave balances.
 *
 * Provides HR interface for viewing and adjusting staff leave balances,
 * and a self-service view for staff to see their own balances.
 * Also provides staff dashboard, policies views, and manager team balances view.
 */
class LeaveBalanceController extends Controller {
    /**
     * The leave balance service instance.
     *
     * @var LeaveBalanceService
     */
    protected LeaveBalanceService $leaveBalanceService;

    /**
     * The leave request service instance.
     *
     * @var LeaveRequestService
     */
    protected LeaveRequestService $leaveRequestService;

    /**
     * The leave policy service instance.
     *
     * @var LeavePolicyService
     */
    protected LeavePolicyService $leavePolicyService;

    /**
     * The leave audit service instance.
     *
     * @var LeaveAuditService
     */
    protected LeaveAuditService $leaveAuditService;

    /**
     * Create a new controller instance.
     *
     * @param LeaveBalanceService $leaveBalanceService
     * @param LeaveRequestService $leaveRequestService
     * @param LeavePolicyService $leavePolicyService
     * @param LeaveAuditService $leaveAuditService
     */
    public function __construct(
        LeaveBalanceService $leaveBalanceService,
        LeaveRequestService $leaveRequestService,
        LeavePolicyService $leavePolicyService,
        LeaveAuditService $leaveAuditService
    ) {
        $this->middleware('auth');
        $this->leaveBalanceService = $leaveBalanceService;
        $this->leaveRequestService = $leaveRequestService;
        $this->leavePolicyService = $leavePolicyService;
        $this->leaveAuditService = $leaveAuditService;
    }

    /**
     * Display a listing of leave balances for HR management.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View {
        // Get current term and use its year as default
        $currentTerm = TermHelper::getCurrentTerm();
        $currentYear = $currentTerm ? $currentTerm->year : date('Y');
        $selectedYear = $request->input('year', $currentYear);
        $selectedUserId = $request->input('user_id');
        $selectedLeaveTypeId = $request->input('leave_type_id');

        // Build query with filters
        $query = LeaveBalance::query()
            ->with(['user', 'leaveType'])
            ->where('leave_year', $selectedYear);

        if ($selectedUserId) {
            $query->where('user_id', $selectedUserId);
        }

        if ($selectedLeaveTypeId) {
            $query->where('leave_type_id', $selectedLeaveTypeId);
        }

        $balances = $query->orderBy('user_id')
            ->orderBy('leave_type_id')
            ->paginate(25)
            ->withQueryString();

        // Get filter options
        $users = User::where('status', 'Current')
            ->orderBy('firstname')
            ->orderBy('lastname')
            ->select('id', 'firstname', 'lastname')
            ->get();

        $leaveTypes = LeaveType::active()
            ->orderBy('name')
            ->select('id', 'name', 'code')
            ->get();

        // Get available years from terms table
        $years = Term::distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();

        // Ensure current year is in the list
        if (!in_array($currentYear, $years)) {
            array_unshift($years, $currentYear);
        }

        // Count stats for the selected year
        $totalBalances = LeaveBalance::where('leave_year', $selectedYear)->count();

        return view('leave.balances.index', [
            'balances' => $balances,
            'users' => $users,
            'leaveTypes' => $leaveTypes,
            'years' => $years,
            'currentYear' => $currentYear,
            'selectedYear' => $selectedYear,
            'selectedUserId' => $selectedUserId,
            'selectedLeaveTypeId' => $selectedLeaveTypeId,
            'totalBalances' => $totalBalances,
        ]);
    }

    /**
     * Display the specified leave balance with adjustment history and audit trail.
     *
     * @param LeaveBalance $balance
     * @return View
     */
    public function show(LeaveBalance $balance): View {
        $balance->load(['user', 'leaveType', 'adjustments.adjustedBy']);

        $adjustments = $this->leaveBalanceService->getAdjustmentHistory($balance);

        // Get audit history for this balance (AUDT-04)
        $auditLogs = $this->leaveAuditService->getAuditHistoryForBalance($balance);
        $auditService = $this->leaveAuditService;

        return view('leave.balances.show', [
            'balance' => $balance,
            'adjustments' => $adjustments,
            'auditLogs' => $auditLogs,
            'auditService' => $auditService,
        ]);
    }

    /**
     * Store a manual adjustment for a leave balance.
     *
     * @param StoreLeaveBalanceAdjustmentRequest $request
     * @param LeaveBalance $balance
     * @return JsonResponse
     */
    public function storeAdjustment(StoreLeaveBalanceAdjustmentRequest $request, LeaveBalance $balance): JsonResponse {
        try {
            $validated = $request->validated();

            $updatedBalance = $this->leaveBalanceService->adjustBalance(
                $balance,
                $validated['adjustment_type'],
                (float) $validated['days'],
                $validated['reason'],
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Balance adjustment saved successfully.',
                'available' => $updatedBalance->available,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving the adjustment.',
            ], 500);
        }
    }

    /**
     * Display the current user's own leave balances.
     *
     * @return View
     */
    public function myBalances(): View {
        $currentYear = $this->leaveBalanceService->getCurrentLeaveYear();
        $userId = auth()->id();

        $balances = $this->leaveBalanceService->getBalancesForUser($userId, $currentYear);

        return view('leave.balances.my-balances', [
            'balances' => $balances,
            'currentYear' => $currentYear,
        ]);
    }

    /**
     * Display the staff leave dashboard.
     *
     * Shows leave balance cards, stats summary, recent requests, and quick action links.
     * This is the main self-service entry point for staff.
     *
     * @return View
     */
    public function dashboard(): View {
        $currentYear = $this->leaveBalanceService->getCurrentLeaveYear();
        $userId = auth()->id();

        // Get balances for current user
        $balances = $this->leaveBalanceService->getBalancesForUser($userId, $currentYear);

        // Get recent leave requests (last 10)
        $recentRequests = $this->leaveRequestService->getRequestsForUser($userId)
            ->take(10);

        // Calculate stats
        $totalAvailable = $balances->sum(function ($balance) {
            return max(0, (float) $balance->available);
        });

        $totalPending = $balances->sum(function ($balance) {
            return (float) $balance->pending;
        });

        $totalUsed = $balances->sum(function ($balance) {
            return (float) $balance->used;
        });

        $stats = [
            'total_available' => $totalAvailable,
            'total_pending' => $totalPending,
            'total_used' => $totalUsed,
        ];

        return view('leave.balances.dashboard', [
            'balances' => $balances,
            'recentRequests' => $recentRequests,
            'stats' => $stats,
            'currentYear' => $currentYear,
        ]);
    }

    /**
     * Display leave policies for staff viewing.
     *
     * Shows all active leave types with their applicable policies and entitlements.
     *
     * @return View
     */
    public function policies(): View {
        $currentYear = $this->leaveBalanceService->getCurrentLeaveYear();

        // Get all active leave types with their policies
        $leaveTypes = LeaveType::with('policies')
            ->where('is_active', true)
            ->ordered()
            ->get();

        // For each leave type, get the applicable policy for current year
        $leaveTypesWithPolicies = $leaveTypes->map(function ($leaveType) use ($currentYear) {
            $policy = $this->leavePolicyService->getPolicyForTypeAndYear(
                $leaveType->id,
                $currentYear
            );

            return [
                'leave_type' => $leaveType,
                'policy' => $policy,
            ];
        });

        return view('leave.policies.index', [
            'leaveTypesWithPolicies' => $leaveTypesWithPolicies,
            'currentYear' => $currentYear,
        ]);
    }

    /**
     * Display team leave balances for managers.
     *
     * Shows leave balances for all direct reports (users where reporting_to = current user).
     * Protected by approve-leave-requests gate.
     *
     * @param Request $request
     * @return View
     */
    public function teamBalances(Request $request): View {
        $currentYear = $this->leaveBalanceService->getCurrentLeaveYear();
        $selectedYear = $request->input('year', $currentYear);
        $selectedLeaveTypeId = $request->input('leave_type_id');
        $manager = auth()->user();

        // Get direct report IDs
        $directReportIds = $manager->directReports()
            ->where('status', 'Current')
            ->pluck('id')
            ->toArray();

        // Get direct reports for filter dropdown
        $directReports = $manager->directReports()
            ->where('status', 'Current')
            ->orderBy('name')
            ->select('id', 'name')
            ->get();

        // Build query with filters - only for direct reports
        $query = LeaveBalance::query()
            ->with(['user', 'leaveType'])
            ->whereIn('user_id', $directReportIds)
            ->where('leave_year', $selectedYear);

        if ($selectedLeaveTypeId) {
            $query->where('leave_type_id', $selectedLeaveTypeId);
        }

        $balances = $query->orderBy('user_id')
            ->orderBy('leave_type_id')
            ->paginate(25)
            ->withQueryString();

        // Get leave types for filter dropdown
        $leaveTypes = LeaveType::active()
            ->orderBy('name')
            ->select('id', 'name', 'code')
            ->get();

        // Get available years (from existing balances for team)
        $years = LeaveBalance::whereIn('user_id', $directReportIds)
            ->distinct()
            ->orderByDesc('leave_year')
            ->pluck('leave_year')
            ->toArray();

        // Ensure current year is in the list
        if (!in_array($currentYear, $years)) {
            array_unshift($years, $currentYear);
        }

        // Calculate team stats
        $teamStats = [
            'total_members' => count($directReportIds),
            'total_available' => LeaveBalance::whereIn('user_id', $directReportIds)
                ->where('leave_year', $selectedYear)
                ->sum(\DB::raw('entitled + carried_over + accrued + adjusted - used - pending')),
            'total_pending' => LeaveBalance::whereIn('user_id', $directReportIds)
                ->where('leave_year', $selectedYear)
                ->sum('pending'),
        ];

        return view('leave.balances.team', [
            'balances' => $balances,
            'directReports' => $directReports,
            'leaveTypes' => $leaveTypes,
            'years' => $years,
            'currentYear' => $currentYear,
            'selectedYear' => $selectedYear,
            'selectedLeaveTypeId' => $selectedLeaveTypeId,
            'teamStats' => $teamStats,
        ]);
    }
}
