<?php

namespace App\Services\Leave;

use App\Events\Leave\LeaveRequestApproved;
use App\Events\Leave\LeaveRequestCancelled;
use App\Events\Leave\LeaveRequestRejected;
use App\Models\Leave\LeaveAuditLog;
use App\Models\Leave\LeaveBalance;
use App\Models\Leave\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Service for managing leave request approvals.
 *
 * Handles approval, rejection, and cancellation of leave requests
 * with proper balance updates and audit trail management.
 */
class LeaveApprovalService {
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

    // ==================== PUBLIC METHODS ====================

    /**
     * Approve a leave request.
     *
     * Validates the request is pending and approver is not the owner.
     * Updates status, approver info, timestamps, and balance (pending -> used).
     *
     * @param LeaveRequest $request The request to approve
     * @param User $approver The user approving the request
     * @param string|null $comments Optional approver comments
     * @return LeaveRequest The updated request
     * @throws InvalidArgumentException If request is not pending or self-approval attempted
     */
    public function approve(LeaveRequest $request, User $approver, ?string $comments = null): LeaveRequest {
        return DB::transaction(function () use ($request, $approver, $comments) {
            // Validate request is pending
            if ($request->status !== LeaveRequest::STATUS_PENDING) {
                throw new InvalidArgumentException(
                    "Cannot approve request: current status is '{$request->status}', expected 'pending'."
                );
            }

            // Block self-approval (APRV-07)
            if ($request->user_id === $approver->id) {
                throw new InvalidArgumentException('Cannot approve your own leave request.');
            }

            // Capture old values for audit log (AUDT-01)
            $oldValues = $request->toArray();

            // Get and lock balance for update
            $balance = LeaveBalance::where('id', $request->leave_balance_id)
                ->lockForUpdate()
                ->firstOrFail();

            // Update request status
            $request->status = LeaveRequest::STATUS_APPROVED;
            $request->approved_by = $approver->id;
            $request->approved_at = Carbon::now();
            $request->approver_comments = $comments;
            $request->save();

            // Update balance: pending -> used
            $this->updateBalanceOnApproval($balance, (float) $request->total_days);

            // Log audit entry for request approval (AUDT-01)
            LeaveAuditLog::log(
                $request,
                LeaveAuditLog::ACTION_APPROVE,
                $oldValues,
                $request->fresh()->toArray(),
                $comments ?? 'Request approved'
            );

            // Dispatch event for notification listeners
            event(new LeaveRequestApproved($request));

            return $request->fresh(['approver', 'user', 'leaveType', 'balance']);
        });
    }

    /**
     * Reject a leave request.
     *
     * Validates the request is pending and reason is provided.
     * Updates status and balance (pending reduced).
     *
     * @param LeaveRequest $request The request to reject
     * @param User $rejector The user rejecting the request
     * @param string $reason Required reason for rejection (APRV-03)
     * @return LeaveRequest The updated request
     * @throws InvalidArgumentException If request is not pending or reason is empty
     */
    public function reject(LeaveRequest $request, User $rejector, string $reason): LeaveRequest {
        return DB::transaction(function () use ($request, $rejector, $reason) {
            // Validate request is pending
            if ($request->status !== LeaveRequest::STATUS_PENDING) {
                throw new InvalidArgumentException(
                    "Cannot reject request: current status is '{$request->status}', expected 'pending'."
                );
            }

            // Validate reason is provided
            $reason = trim($reason);
            if (empty($reason)) {
                throw new InvalidArgumentException('A reason is required when rejecting a leave request.');
            }

            // Capture old values for audit log (AUDT-01)
            $oldValues = $request->toArray();

            // Get and lock balance for update
            $balance = LeaveBalance::where('id', $request->leave_balance_id)
                ->lockForUpdate()
                ->firstOrFail();

            // Update request status
            $request->status = LeaveRequest::STATUS_REJECTED;
            $request->approved_by = $rejector->id; // Tracks who rejected
            $request->approved_at = Carbon::now();
            $request->approver_comments = $reason;
            $request->save();

            // Update balance: reduce pending
            $this->updateBalanceOnRejection($balance, (float) $request->total_days);

            // Log audit entry for request rejection (AUDT-01)
            LeaveAuditLog::log(
                $request,
                LeaveAuditLog::ACTION_REJECT,
                $oldValues,
                $request->fresh()->toArray(),
                $reason
            );

            // Dispatch event for notification listeners
            event(new LeaveRequestRejected($request));

            return $request->fresh(['approver', 'user', 'leaveType', 'balance']);
        });
    }

    /**
     * Cancel a leave request.
     *
     * Can cancel pending or approved requests. For approved requests,
     * validates start_date is in the future (CANC-02).
     *
     * @param LeaveRequest $request The request to cancel
     * @param User $canceller The user cancelling the request
     * @param string $reason Required reason for cancellation
     * @return LeaveRequest The updated request
     * @throws InvalidArgumentException If request cannot be cancelled
     */
    public function cancel(LeaveRequest $request, User $canceller, string $reason): LeaveRequest {
        return DB::transaction(function () use ($request, $canceller, $reason) {
            // Validate request is APPROVED or PENDING
            $allowedStatuses = [LeaveRequest::STATUS_APPROVED, LeaveRequest::STATUS_PENDING];
            if (!in_array($request->status, $allowedStatuses)) {
                throw new InvalidArgumentException(
                    "Cannot cancel request: current status is '{$request->status}'. " .
                    "Only pending or approved requests can be cancelled."
                );
            }

            // For approved requests, validate start_date is in future (CANC-02)
            if ($request->status === LeaveRequest::STATUS_APPROVED) {
                $startDate = Carbon::parse($request->start_date);
                if (!$startDate->isFuture()) {
                    throw new InvalidArgumentException(
                        'Cannot cancel approved leave: leave has already started or is in the past.'
                    );
                }
            }

            // Validate reason is provided
            $reason = trim($reason);
            if (empty($reason)) {
                throw new InvalidArgumentException('A reason is required when cancelling a leave request.');
            }

            // Capture old values for audit log (AUDT-01)
            $oldValues = $request->toArray();

            // Get and lock balance for update
            $balance = LeaveBalance::where('id', $request->leave_balance_id)
                ->lockForUpdate()
                ->firstOrFail();

            // Store previous status for balance update and audit notes
            $previousStatus = $request->status;

            // Update request status
            $request->status = LeaveRequest::STATUS_CANCELLED;
            $request->cancelled_at = Carbon::now();
            $request->cancelled_by = $canceller->id;
            $request->cancellation_reason = $reason;
            $request->save();

            // Update balance based on previous status
            $this->updateBalanceOnCancellation($balance, (float) $request->total_days, $previousStatus);

            // Log audit entry for request cancellation (AUDT-01)
            $auditNote = $previousStatus === LeaveRequest::STATUS_APPROVED
                ? 'Approved leave cancelled: ' . $reason
                : 'Pending request cancelled: ' . $reason;
            LeaveAuditLog::log(
                $request,
                LeaveAuditLog::ACTION_CANCEL,
                $oldValues,
                $request->fresh()->toArray(),
                $auditNote
            );

            // Dispatch event for attendance sync removal (only if was approved)
            if ($previousStatus === LeaveRequest::STATUS_APPROVED) {
                event(new LeaveRequestCancelled($request, $previousStatus));
            }

            return $request->fresh(['cancelledBy', 'user', 'leaveType', 'balance']);
        });
    }

    /**
     * Get the designated approver for a staff member.
     *
     * Returns the user's direct supervisor (reporting_to).
     * Falls back to first HR Admin if no direct supervisor (APRV-06).
     *
     * @param User $staff The staff member
     * @return User|null The approver or null if none found
     */
    public function getApprover(User $staff): ?User {
        // Primary: return the reporting_to user
        if ($staff->reporting_to) {
            $supervisor = User::find($staff->reporting_to);
            if ($supervisor && $supervisor->status === 'Current') {
                return $supervisor;
            }
        }

        // Fallback: first user with HR Admin role (APRV-06)
        $hrAdmin = User::whereHas('roles', function ($query) {
            $query->where('name', 'HR Admin');
        })
            ->where('status', 'Current')
            ->where('id', '!=', $staff->id) // Can't be their own approver
            ->first();

        return $hrAdmin;
    }

    /**
     * Get pending leave requests for a specific approver.
     *
     * Returns requests where the owner reports to the approver,
     * plus all requests if approver has HR Admin role.
     *
     * @param User $approver The approver user
     * @return Collection Collection of pending LeaveRequest models
     */
    public function getPendingForApprover(User $approver): Collection {
        $query = LeaveRequest::pending()
            ->with(['user', 'leaveType', 'balance']);

        // Check if approver has HR Admin role (can approve all)
        $isHrAdmin = $approver->hasAnyRoles(['Administrator', 'Leave Admin', 'HR Admin']);

        if ($isHrAdmin) {
            // HR Admin sees all pending requests (except their own)
            $query->where('user_id', '!=', $approver->id);
        } else {
            // Regular manager only sees requests from their direct reports
            $query->forApprover($approver->id);
        }

        return $query->orderBy('submitted_at', 'asc')->get();
    }

    /**
     * Check if a user can approve a specific leave request.
     *
     * Delegates to LeaveRequestPolicy::approve.
     *
     * @param LeaveRequest $request The leave request
     * @param User $user The user to check
     * @return bool True if user can approve
     */
    public function canApprove(LeaveRequest $request, User $user): bool {
        // Cannot approve own request
        if ($request->user_id === $user->id) {
            return false;
        }

        // Must be pending
        if ($request->status !== LeaveRequest::STATUS_PENDING) {
            return false;
        }

        // Admin roles can approve any request
        if ($user->hasAnyRoles(['Administrator', 'Leave Admin', 'HR Admin'])) {
            return true;
        }

        // Check if user is the designated approver (request owner reports to this user)
        return $request->user && $request->user->reporting_to === $user->id;
    }

    /**
     * Check if a user can cancel a specific leave request.
     *
     * - Owner can cancel their own pending requests
     * - Owner can cancel their own approved requests if start_date > today
     * - Approver/Admin can cancel approved requests if start_date > today
     *
     * @param LeaveRequest $request The leave request
     * @param User $user The user to check
     * @return bool True if user can cancel
     */
    public function canCancel(LeaveRequest $request, User $user): bool {
        $isOwner = $request->user_id === $user->id;
        $isAdmin = $user->hasAnyRoles(['Administrator', 'Leave Admin', 'HR Admin']);
        $isApprover = $request->user && $request->user->reporting_to === $user->id;
        $startDateFuture = Carbon::parse($request->start_date)->isFuture();

        // Check by request status
        switch ($request->status) {
            case LeaveRequest::STATUS_PENDING:
                // Owner can cancel their own pending requests
                if ($isOwner) {
                    return true;
                }
                // Admin/Approver can cancel pending requests
                return $isAdmin || $isApprover;

            case LeaveRequest::STATUS_APPROVED:
                // Must be in the future
                if (!$startDateFuture) {
                    return false;
                }
                // Owner can cancel their own approved requests if in future
                if ($isOwner) {
                    return true;
                }
                // Admin/Approver can cancel approved requests if in future
                return $isAdmin || $isApprover;

            default:
                // Cannot cancel rejected, cancelled, or draft requests
                return false;
        }
    }

    // ==================== PRIVATE HELPER METHODS ====================

    /**
     * Update balance when a request is approved.
     *
     * Moves days from pending to used.
     *
     * @param LeaveBalance $balance The balance to update
     * @param float $days Number of days
     * @return void
     */
    private function updateBalanceOnApproval(LeaveBalance $balance, float $days): void {
        // Lock already acquired by caller, but re-lock for safety
        $balance = LeaveBalance::where('id', $balance->id)
            ->lockForUpdate()
            ->firstOrFail();

        $balance->pending = max(0, (float) $balance->pending - $days);
        $balance->used = (float) $balance->used + $days;
        $balance->save();
    }

    /**
     * Update balance when a request is rejected.
     *
     * Removes days from pending (balance restored).
     *
     * @param LeaveBalance $balance The balance to update
     * @param float $days Number of days
     * @return void
     */
    private function updateBalanceOnRejection(LeaveBalance $balance, float $days): void {
        // Lock already acquired by caller, but re-lock for safety
        $balance = LeaveBalance::where('id', $balance->id)
            ->lockForUpdate()
            ->firstOrFail();

        $balance->pending = max(0, (float) $balance->pending - $days);
        $balance->save();
    }

    /**
     * Update balance when a request is cancelled.
     *
     * If was approved: removes days from used (balance restored).
     * If was pending: removes days from pending (balance restored).
     *
     * @param LeaveBalance $balance The balance to update
     * @param float $days Number of days
     * @param string $previousStatus The status before cancellation
     * @return void
     */
    private function updateBalanceOnCancellation(LeaveBalance $balance, float $days, string $previousStatus): void {
        // Lock already acquired by caller, but re-lock for safety
        $balance = LeaveBalance::where('id', $balance->id)
            ->lockForUpdate()
            ->firstOrFail();

        if ($previousStatus === LeaveRequest::STATUS_APPROVED) {
            // Was approved: restore from used
            $balance->used = max(0, (float) $balance->used - $days);
        } elseif ($previousStatus === LeaveRequest::STATUS_PENDING) {
            // Was pending: restore from pending
            $balance->pending = max(0, (float) $balance->pending - $days);
        }

        $balance->save();
    }
}
