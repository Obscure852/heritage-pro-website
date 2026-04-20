<?php

namespace App\Services\Leave;

use App\Models\Leave\LeaveBalance;
use App\Models\Leave\LeaveRequest;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Service for generating leave statements.
 *
 * Provides methods to generate statement data for PDF export
 * and to get available years for a user's leave history.
 */
class LeaveStatementService {
    /**
     * @var LeaveBalanceService
     */
    protected LeaveBalanceService $leaveBalanceService;

    /**
     * Create a new service instance.
     *
     * @param LeaveBalanceService $leaveBalanceService
     */
    public function __construct(LeaveBalanceService $leaveBalanceService) {
        $this->leaveBalanceService = $leaveBalanceService;
    }

    /**
     * Generate statement data for a user and year.
     *
     * Retrieves all balances and requests for the specified year,
     * calculates summary statistics, and groups requests by status.
     *
     * @param User $user The user to generate statement for
     * @param int $year The leave year
     * @return array Statement data including balances, requests, and summary
     */
    public function generateStatement(User $user, int $year): array {
        // Get all balances for the year
        $balances = $this->leaveBalanceService->getBalancesForUser($user->id, $year);

        // Get all requests for the year (all statuses for complete history)
        $requests = LeaveRequest::where('user_id', $user->id)
            ->whereYear('start_date', $year)
            ->with(['leaveType', 'approver'])
            ->orderBy('start_date')
            ->get();

        // Calculate summary stats
        $summary = [
            'total_entitled' => $balances->sum('entitled'),
            'total_carried' => $balances->sum('carried_over'),
            'total_accrued' => $balances->sum('accrued'),
            'total_adjusted' => $balances->sum('adjusted'),
            'total_used' => $balances->sum('used'),
            'total_pending' => $balances->sum('pending'),
            'total_available' => $balances->sum(fn($b) => $this->leaveBalanceService->calculateAvailable($b)),
        ];

        // Group requests by status
        $requestsByStatus = [
            'approved' => $requests->where('status', LeaveRequest::STATUS_APPROVED),
            'pending' => $requests->where('status', LeaveRequest::STATUS_PENDING),
            'rejected' => $requests->where('status', LeaveRequest::STATUS_REJECTED),
            'cancelled' => $requests->where('status', LeaveRequest::STATUS_CANCELLED),
        ];

        return [
            'user' => $user,
            'year' => $year,
            'balances' => $balances,
            'requests' => $requests,
            'requestsByStatus' => $requestsByStatus,
            'summary' => $summary,
            'generatedAt' => now(),
        ];
    }

    /**
     * Get available years for a user's leave history.
     *
     * Returns years where the user has leave requests,
     * sorted in descending order (most recent first).
     *
     * @param User $user The user to get years for
     * @return Collection Collection of years
     */
    public function getAvailableYears(User $user): Collection {
        return LeaveRequest::where('user_id', $user->id)
            ->selectRaw('YEAR(start_date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
    }
}
