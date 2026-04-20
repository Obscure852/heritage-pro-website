<?php

namespace App\Services\Leave;

use App\Models\Leave\LeaveBalance;
use App\Models\Leave\LeavePolicy;
use App\Models\Leave\LeaveRequest;
use App\Models\Leave\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service for generating leave report data.
 *
 * Provides data layer for all leave reports including organization stats,
 * type distribution, outstanding balances, carry-over, team summary, and personal history.
 * Consumed by LeaveReportController and export classes.
 */
class LeaveReportService {
    /**
     * @var LeaveBalanceService
     */
    public LeaveBalanceService $leaveBalanceService;

    /**
     * @var LeavePolicyService
     */
    protected LeavePolicyService $leavePolicyService;

    /**
     * Create a new service instance.
     *
     * @param LeaveBalanceService $leaveBalanceService
     * @param LeavePolicyService $leavePolicyService
     */
    public function __construct(LeaveBalanceService $leaveBalanceService, LeavePolicyService $leavePolicyService) {
        $this->leaveBalanceService = $leaveBalanceService;
        $this->leavePolicyService = $leavePolicyService;
    }

    // ==================== ORGANIZATION REPORTS ====================

    /**
     * Get organization-wide leave statistics for a year.
     *
     * Returns aggregate stats including total staff, entitlements, usage, and utilization rate.
     *
     * @param int $year The leave year
     * @return array Organization stats with keys: total_staff, total_entitled, total_used,
     *               total_pending, total_available, utilization_rate, upcoming_leave
     */
    public function getOrganizationStats(int $year): array {
        $balances = LeaveBalance::forYear($year)->get();

        if ($balances->isEmpty()) {
            return [
                'total_staff' => 0,
                'total_entitled' => 0.0,
                'total_used' => 0.0,
                'total_pending' => 0.0,
                'total_available' => 0.0,
                'utilization_rate' => 0.0,
                'upcoming_leave' => 0,
            ];
        }

        $totalStaff = $balances->pluck('user_id')->unique()->count();
        $totalEntitled = (float) $balances->sum('entitled');
        $totalUsed = (float) $balances->sum('used');
        $totalPending = (float) $balances->sum('pending');

        // Calculate total available: (entitled + carried_over + accrued + adjusted) - used - pending
        $totalAvailable = $balances->sum(function ($balance) {
            return $balance->available;
        });

        // Calculate utilization rate
        $utilizationRate = $totalEntitled > 0
            ? round(($totalUsed / $totalEntitled) * 100, 1)
            : 0.0;

        // Count upcoming approved leave (starting within next 30 days)
        $upcomingLeave = LeaveRequest::approved()
            ->where('start_date', '>=', Carbon::today())
            ->where('start_date', '<=', Carbon::today()->addDays(30))
            ->count();

        return [
            'total_staff' => $totalStaff,
            'total_entitled' => $totalEntitled,
            'total_used' => $totalUsed,
            'total_pending' => $totalPending,
            'total_available' => (float) $totalAvailable,
            'utilization_rate' => $utilizationRate,
            'upcoming_leave' => $upcomingLeave,
        ];
    }

    /**
     * Get leave distribution by type for a year.
     *
     * Returns per-type breakdown of entitlements, usage, and staff count.
     *
     * @param int $year The leave year
     * @return Collection Collection with per-type stats
     */
    public function getLeaveTypeDistribution(int $year): Collection {
        $balances = LeaveBalance::forYear($year)
            ->with('leaveType')
            ->get();

        if ($balances->isEmpty()) {
            return collect();
        }

        return $balances->groupBy('leave_type_id')->map(function ($typeBalances) {
            $leaveType = $typeBalances->first()->leaveType;
            $totalEntitled = (float) $typeBalances->sum('entitled');
            $totalUsed = (float) $typeBalances->sum('used');
            $totalPending = (float) $typeBalances->sum('pending');
            $totalAvailable = $typeBalances->sum(fn($b) => $b->available);

            return [
                'leave_type_id' => $leaveType->id,
                'leave_type_name' => $leaveType->name,
                'leave_type_code' => $leaveType->code,
                'color' => $leaveType->color,
                'total_entitled' => $totalEntitled,
                'total_used' => $totalUsed,
                'total_pending' => $totalPending,
                'total_available' => (float) $totalAvailable,
                'staff_count' => $typeBalances->pluck('user_id')->unique()->count(),
                'usage_percentage' => $totalEntitled > 0
                    ? round(($totalUsed / $totalEntitled) * 100, 1)
                    : 0.0,
            ];
        })->values();
    }

    /**
     * Get outstanding balances report.
     *
     * Returns users with available balance > 0, optionally filtered by leave type and department.
     *
     * @param int $year The leave year
     * @param int|null $leaveTypeId Optional leave type filter
     * @param string|null $department Optional department filter (string match)
     * @return Collection Collection of outstanding balances ordered by available DESC
     */
    public function getOutstandingBalances(int $year, ?int $leaveTypeId = null, ?string $department = null): Collection {
        $query = LeaveBalance::forYear($year)
            ->with(['user', 'leaveType']);

        if ($leaveTypeId !== null) {
            $query->forType($leaveTypeId);
        }

        $balances = $query->get();

        // Filter by department if provided
        if ($department !== null) {
            $balances = $balances->filter(function ($balance) use ($department) {
                return $balance->user && $balance->user->department === $department;
            });
        }

        // Filter to only those with available > 0 and map to report format
        return $balances->filter(fn($b) => $b->available > 0)
            ->map(function ($balance) {
                return [
                    'user_id' => $balance->user_id,
                    'user_name' => $balance->user ? $balance->user->full_name : 'Unknown',
                    'department' => $balance->user->department ?? null,
                    'leave_type_id' => $balance->leave_type_id,
                    'leave_type_name' => $balance->leaveType->name ?? 'Unknown',
                    'entitled' => (float) $balance->entitled,
                    'used' => (float) $balance->used,
                    'pending' => (float) $balance->pending,
                    'available' => (float) $balance->available,
                ];
            })
            ->sortByDesc('available')
            ->values();
    }

    /**
     * Get carry-over report between two years.
     *
     * Shows what was carried over from previous year and what was forfeited.
     *
     * @param int $fromYear Source year
     * @param int $toYear Target year
     * @return Collection Collection with carry-over details per user/type
     */
    public function getCarryOverReport(int $fromYear, int $toYear): Collection {
        // Get balances from both years with relationships
        $fromYearBalances = LeaveBalance::forYear($fromYear)
            ->with(['user', 'leaveType'])
            ->get()
            ->keyBy(fn($b) => "{$b->user_id}_{$b->leave_type_id}");

        $toYearBalances = LeaveBalance::forYear($toYear)
            ->with(['user', 'leaveType'])
            ->get()
            ->keyBy(fn($b) => "{$b->user_id}_{$b->leave_type_id}");

        // Get policies for toYear for carry_over_limit
        $policies = LeavePolicy::forYear($toYear)
            ->get()
            ->keyBy('leave_type_id');

        $report = collect();

        foreach ($fromYearBalances as $key => $fromBalance) {
            $toBalance = $toYearBalances->get($key);
            $policy = $policies->get($fromBalance->leave_type_id);

            $previousYearAvailable = (float) $fromBalance->available;
            $carryOverLimit = $policy ? (float) ($policy->carry_over_limit ?? 0) : 0;
            $carriedOver = $toBalance ? (float) $toBalance->carried_over : 0;
            $forfeited = max(0, $previousYearAvailable - $carriedOver);

            // Only include if there was something to carry over
            if ($previousYearAvailable > 0 || $carriedOver > 0) {
                $report->push([
                    'user_id' => $fromBalance->user_id,
                    'user_name' => $fromBalance->user ? $fromBalance->user->full_name : 'Unknown',
                    'leave_type_id' => $fromBalance->leave_type_id,
                    'leave_type_name' => $fromBalance->leaveType->name ?? 'Unknown',
                    'previous_year_balance' => $previousYearAvailable,
                    'carry_over_limit' => $carryOverLimit,
                    'carried_over' => $carriedOver,
                    'forfeited' => $forfeited,
                ]);
            }
        }

        return $report->sortBy('user_name')->values();
    }

    /**
     * Get team summary for a manager.
     *
     * Returns aggregate stats for manager's direct reports (users where reporting_to = managerId).
     *
     * @param int $managerId The manager's user ID
     * @param int $year The leave year
     * @return array Team summary with team_size, balances_by_type, pending_requests, upcoming_leave
     */
    public function getTeamSummary(int $managerId, int $year): array {
        // Get direct reports
        $teamMemberIds = User::where('reporting_to', $managerId)
            ->where('status', 'Current')
            ->pluck('id')
            ->toArray();

        if (empty($teamMemberIds)) {
            return [
                'team_size' => 0,
                'balances_by_type' => [],
                'pending_requests' => 0,
                'upcoming_leave' => collect(),
            ];
        }

        // Get all balances for team members
        $balances = LeaveBalance::forYear($year)
            ->whereIn('user_id', $teamMemberIds)
            ->with('leaveType')
            ->get();

        // Group by leave type and aggregate
        $balancesByType = $balances->groupBy('leave_type_id')->map(function ($typeBalances) {
            $leaveType = $typeBalances->first()->leaveType;
            return [
                'leave_type_id' => $leaveType->id,
                'leave_type_name' => $leaveType->name,
                'total_entitled' => (float) $typeBalances->sum('entitled'),
                'total_used' => (float) $typeBalances->sum('used'),
                'total_pending' => (float) $typeBalances->sum('pending'),
                'total_available' => $typeBalances->sum(fn($b) => $b->available),
            ];
        })->values()->toArray();

        // Count pending requests from team
        $pendingRequests = LeaveRequest::pending()
            ->whereIn('user_id', $teamMemberIds)
            ->count();

        // Get upcoming leave for team (approved, starting within 30 days)
        $upcomingLeave = LeaveRequest::approved()
            ->whereIn('user_id', $teamMemberIds)
            ->where('start_date', '>=', Carbon::today())
            ->where('start_date', '<=', Carbon::today()->addDays(30))
            ->with(['user', 'leaveType'])
            ->orderBy('start_date')
            ->get()
            ->map(function ($request) {
                return [
                    'user_id' => $request->user_id,
                    'user_name' => $request->user->full_name ?? 'Unknown',
                    'leave_type' => $request->leaveType->name ?? 'Unknown',
                    'start_date' => $request->start_date->format('Y-m-d'),
                    'end_date' => $request->end_date->format('Y-m-d'),
                    'total_days' => (float) $request->total_days,
                ];
            });

        return [
            'team_size' => count($teamMemberIds),
            'balances_by_type' => $balancesByType,
            'pending_requests' => $pendingRequests,
            'upcoming_leave' => $upcomingLeave,
        ];
    }

    /**
     * Get personal leave request history for a user.
     *
     * Returns all requests ordered by start_date DESC, optionally filtered by year.
     *
     * @param int $userId The user's ID
     * @param int|null $year Optional leave year filter
     * @return Collection Collection of user's leave requests
     */
    public function getPersonalHistory(int $userId, ?int $year = null): Collection {
        $query = LeaveRequest::forUser($userId)
            ->with('leaveType')
            ->orderByDesc('start_date');

        if ($year !== null) {
            // Filter by leave year - get the start month from settings
            $startMonth = $this->leavePolicyService->getLeaveYearStartMonth();
            $yearStart = Carbon::create($year, $startMonth, 1)->startOfMonth();
            $yearEnd = $yearStart->copy()->addYear()->subDay();

            $query->where('start_date', '>=', $yearStart)
                  ->where('start_date', '<=', $yearEnd);
        }

        return $query->get();
    }

    // ==================== DASHBOARD & UPCOMING ====================

    /**
     * Get dashboard statistics for a user.
     *
     * Returns personal summary for staff dashboard.
     *
     * @param int $userId The user's ID
     * @param int $year The leave year
     * @return array Dashboard stats with total_available, total_pending, total_used,
     *               upcoming_leave, recent_requests
     */
    public function getDashboardStats(int $userId, int $year): array {
        // Get user's balances for the year
        $balances = LeaveBalance::forUser($userId)
            ->forYear($year)
            ->with('leaveType')
            ->get();

        $totalAvailable = $balances->sum(fn($b) => $b->available);
        $totalPending = (float) $balances->sum('pending');
        $totalUsed = (float) $balances->sum('used');

        // Get upcoming approved leave (starting within 30 days)
        $upcomingLeave = LeaveRequest::forUser($userId)
            ->approved()
            ->where('start_date', '>=', Carbon::today())
            ->where('start_date', '<=', Carbon::today()->addDays(30))
            ->with('leaveType')
            ->orderBy('start_date')
            ->get();

        // Get last 5 requests
        $recentRequests = LeaveRequest::forUser($userId)
            ->with('leaveType')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return [
            'total_available' => (float) $totalAvailable,
            'total_pending' => $totalPending,
            'total_used' => $totalUsed,
            'upcoming_leave' => $upcomingLeave,
            'recent_requests' => $recentRequests,
        ];
    }

    /**
     * Get upcoming approved leave across the organization.
     *
     * Returns approved requests starting within specified days, optionally filtered by department.
     *
     * @param int $year The leave year (for context)
     * @param int $days Number of days to look ahead (default 30)
     * @param string|null $department Optional department filter
     * @return Collection Collection of upcoming leave requests
     */
    public function getUpcomingLeave(int $year, int $days = 30, ?string $department = null): Collection {
        $query = LeaveRequest::approved()
            ->where('start_date', '>=', Carbon::today())
            ->where('start_date', '<=', Carbon::today()->addDays($days))
            ->with(['user', 'leaveType'])
            ->orderBy('start_date');

        $requests = $query->get();

        // Filter by department if provided
        if ($department !== null) {
            $requests = $requests->filter(function ($request) use ($department) {
                return $request->user && $request->user->department === $department;
            });
        }

        return $requests->map(function ($request) {
            return [
                'user_id' => $request->user_id,
                'user_name' => $request->user->full_name ?? 'Unknown',
                'department' => $request->user->department ?? null,
                'leave_type_id' => $request->leave_type_id,
                'leave_type_name' => $request->leaveType->name ?? 'Unknown',
                'color' => $request->leaveType->color ?? null,
                'start_date' => $request->start_date->format('Y-m-d'),
                'end_date' => $request->end_date->format('Y-m-d'),
                'total_days' => (float) $request->total_days,
            ];
        })->values();
    }

    /**
     * Get monthly leave usage trend for a year.
     *
     * Returns array with 12 entries (Jan-Dec) showing total days taken per month.
     *
     * @param int $year The leave year
     * @return array Monthly usage trend with month names and totals
     */
    public function getMonthlyUsageTrend(int $year): array {
        // Get leave year date range
        $startMonth = $this->leavePolicyService->getLeaveYearStartMonth();
        $yearStart = Carbon::create($year, $startMonth, 1)->startOfMonth();
        $yearEnd = $yearStart->copy()->addYear()->subDay();

        // Get all approved requests in the leave year
        $requests = LeaveRequest::approved()
            ->where('start_date', '>=', $yearStart)
            ->where('start_date', '<=', $yearEnd)
            ->get();

        // Initialize all 12 months
        $months = [];
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        for ($i = 0; $i < 12; $i++) {
            $currentMonth = $yearStart->copy()->addMonths($i);
            $monthKey = $currentMonth->format('Y-m');
            $months[$monthKey] = [
                'month' => $monthNames[$currentMonth->month - 1],
                'month_number' => $currentMonth->month,
                'year' => $currentMonth->year,
                'total_days_taken' => 0.0,
            ];
        }

        // Aggregate days by start month
        foreach ($requests as $request) {
            $monthKey = $request->start_date->format('Y-m');
            if (isset($months[$monthKey])) {
                $months[$monthKey]['total_days_taken'] += (float) $request->total_days;
            }
        }

        return array_values($months);
    }

    /**
     * Get leave summary grouped by department.
     *
     * Returns aggregate stats per department.
     *
     * @param int $year The leave year
     * @return Collection Collection with department-level stats
     */
    public function getDepartmentSummary(int $year): Collection {
        $balances = LeaveBalance::forYear($year)
            ->with('user')
            ->get();

        if ($balances->isEmpty()) {
            return collect();
        }

        // Group by department
        return $balances->groupBy(function ($balance) {
            return $balance->user->department ?? 'Unassigned';
        })->map(function ($deptBalances, $department) {
            $totalEntitled = (float) $deptBalances->sum('entitled');
            $totalUsed = (float) $deptBalances->sum('used');
            $totalAvailable = $deptBalances->sum(fn($b) => $b->available);

            return [
                'department' => $department,
                'staff_count' => $deptBalances->pluck('user_id')->unique()->count(),
                'total_entitled' => $totalEntitled,
                'total_used' => $totalUsed,
                'total_available' => (float) $totalAvailable,
                'utilization_rate' => $totalEntitled > 0
                    ? round(($totalUsed / $totalEntitled) * 100, 1)
                    : 0.0,
            ];
        })->sortBy('department')->values();
    }
}
