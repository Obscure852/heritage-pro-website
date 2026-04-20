<?php

namespace App\Services\Leave;

use App\Events\Leave\LeaveRequestSubmitted;
use App\Models\Leave\LeaveAuditLog;
use App\Models\Leave\LeaveBalance;
use App\Models\Leave\LeaveRequest;
use App\Models\Leave\LeaveSetting;
use App\Models\Leave\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

/**
 * Service for managing leave requests.
 *
 * Handles leave request submission, validation, overlap detection, and lifecycle management.
 * Integrates with LeaveCalculationService for day calculations and LeaveBalanceService for balance checks.
 */
class LeaveRequestService {
    /**
     * @var LeaveCalculationService
     */
    protected LeaveCalculationService $leaveCalculationService;

    /**
     * @var LeaveBalanceService
     */
    protected LeaveBalanceService $leaveBalanceService;

    /**
     * Create a new service instance.
     *
     * @param LeaveCalculationService $leaveCalculationService
     * @param LeaveBalanceService $leaveBalanceService
     */
    public function __construct(
        LeaveCalculationService $leaveCalculationService,
        LeaveBalanceService $leaveBalanceService
    ) {
        $this->leaveCalculationService = $leaveCalculationService;
        $this->leaveBalanceService = $leaveBalanceService;
    }

    // ==================== REQUEST SUBMISSION ====================

    /**
     * Submit a new leave request.
     *
     * Creates a leave request with PENDING status after validating all business rules.
     * Updates the balance's pending field and sets submitted_at timestamp.
     *
     * @param User $user The user submitting the request
     * @param array $data Request data containing leave_type_id, start_date, end_date, reason, etc.
     * @return LeaveRequest The created leave request
     * @throws ValidationException If validation fails
     * @throws InvalidArgumentException If leave type not found
     */
    public function submit(User $user, array $data): LeaveRequest {
        return DB::transaction(function () use ($user, $data) {
            // Check idempotency key first if provided
            if (!empty($data['idempotency_key'])) {
                $existingRequest = $this->checkIdempotency($data['idempotency_key']);
                if ($existingRequest) {
                    return $existingRequest;
                }
            }

            // Get leave type
            $leaveType = LeaveType::find($data['leave_type_id']);
            if (!$leaveType) {
                throw new InvalidArgumentException('Leave type not found.');
            }

            // Calculate total days
            $startDate = Carbon::parse($data['start_date']);
            $endDate = Carbon::parse($data['end_date']);
            $startHalfDay = $data['start_half_day'] ?? null;
            $endHalfDay = $data['end_half_day'] ?? null;

            $totalDays = $this->leaveCalculationService->calculateLeaveDays(
                $startDate,
                $endDate,
                $startHalfDay,
                $endHalfDay
            );

            // Validate request
            $errors = $this->validateRequest($user, $data, null, $totalDays);
            if (!empty($errors)) {
                throw ValidationException::withMessages($errors);
            }

            // Get current leave year
            $leaveYear = $this->leaveBalanceService->getCurrentLeaveYear();

            // Get or create balance
            $balance = $this->leaveBalanceService->getOrCreateBalance($user, $leaveType, $leaveYear);

            // Generate ULID
            $ulid = (string) Str::ulid();

            // Create leave request
            $leaveRequest = LeaveRequest::create([
                'ulid' => $ulid,
                'user_id' => $user->id,
                'leave_type_id' => $leaveType->id,
                'leave_balance_id' => $balance->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'start_half_day' => $startHalfDay,
                'end_half_day' => $endHalfDay,
                'total_days' => $totalDays,
                'reason' => $data['reason'],
                'status' => LeaveRequest::STATUS_PENDING,
                'submitted_at' => now(),
                'idempotency_key' => $data['idempotency_key'] ?? null,
            ]);

            // Update balance pending field
            $balance->pending = (float) $balance->pending + $totalDays;
            $balance->save();

            // Log audit entry for request creation (AUDT-01)
            LeaveAuditLog::log(
                $leaveRequest,
                LeaveAuditLog::ACTION_CREATE,
                null,
                $leaveRequest->toArray(),
                'Leave request submitted'
            );

            // Dispatch event for notification listeners
            event(new LeaveRequestSubmitted($leaveRequest));

            return $leaveRequest->fresh(['leaveType', 'balance', 'user']);
        });
    }

    // ==================== VALIDATION ====================

    /**
     * Validate a leave request against all business rules.
     *
     * Returns an array of validation errors (empty if valid).
     * Validates: leave type active, gender restriction, balance, overlap, notice period,
     * maximum consecutive days, backdated requests, and attachment requirements.
     *
     * @param User $user The user submitting/updating the request
     * @param array $data Request data
     * @param LeaveRequest|null $existingRequest Existing request if updating (to exclude from overlap)
     * @param float|null $totalDays Pre-calculated total days (pass to avoid recalculating)
     * @return array Array of validation errors keyed by field name
     */
    public function validateRequest(
        User $user,
        array $data,
        ?LeaveRequest $existingRequest = null,
        ?float $totalDays = null
    ): array {
        $errors = [];

        // Get leave type
        $leaveType = LeaveType::find($data['leave_type_id']);
        if (!$leaveType) {
            $errors['leave_type_id'] = ['Leave type not found.'];
            return $errors;
        }

        // Parse dates
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);
        $startHalfDay = $data['start_half_day'] ?? null;
        $endHalfDay = $data['end_half_day'] ?? null;

        // Calculate total days if not provided
        if ($totalDays === null) {
            $totalDays = $this->leaveCalculationService->calculateLeaveDays(
                $startDate,
                $endDate,
                $startHalfDay,
                $endHalfDay
            );
        }

        // 1. Check leave type is active
        if (!$leaveType->is_active) {
            $errors['leave_type_id'] = ['This leave type is not active.'];
        }

        // 2. Check gender restriction using the helper method
        if (!$leaveType->isGenderEligible($user->gender)) {
            $errors['leave_type_id'] = ['This leave type is not available for your gender.'];
        }

        // 3. Check available balance (unless allow_negative_balance)
        $leaveYear = $this->leaveBalanceService->getCurrentLeaveYear();
        $balance = $this->leaveBalanceService->getBalanceForUser($user->id, $leaveType->id, $leaveYear);

        if (!$leaveType->allow_negative_balance) {
            $availableBalance = $balance ? $balance->available : 0;

            // If updating, add back the existing request's days to available
            if ($existingRequest && $existingRequest->status === LeaveRequest::STATUS_PENDING) {
                $availableBalance += (float) $existingRequest->total_days;
            }

            if ($totalDays > $availableBalance) {
                $errors['total_days'] = [
                    "Insufficient leave balance. Available: {$availableBalance} days, Requested: {$totalDays} days."
                ];
            }
        }

        // 4. Check no overlapping approved/pending requests
        $excludeRequestId = $existingRequest ? $existingRequest->id : null;
        if ($this->checkOverlap($user->id, $startDate, $endDate, $excludeRequestId)) {
            $errors['start_date'] = ['You already have a leave request for this period.'];
        }

        // 5. Check minimum notice period
        if ($leaveType->min_notice_days !== null && $leaveType->min_notice_days > 0) {
            $today = Carbon::today();
            $requiredStartDate = $today->copy()->addDays($leaveType->min_notice_days);

            if ($startDate->lt($requiredStartDate)) {
                $errors['start_date'] = [
                    "This leave type requires at least {$leaveType->min_notice_days} days advance notice."
                ];
            }
        }

        // 6. Check maximum consecutive days
        if ($leaveType->max_consecutive_days !== null && $leaveType->max_consecutive_days > 0) {
            if ($totalDays > $leaveType->max_consecutive_days) {
                $errors['total_days'] = [
                    "This leave type allows a maximum of {$leaveType->max_consecutive_days} consecutive days."
                ];
            }
        }

        // 7. Check backdated requests
        $today = Carbon::today();
        if ($startDate->lt($today)) {
            $allowBackdated = LeaveSetting::get('allow_backdated_requests', false);
            if (!$allowBackdated) {
                $errors['start_date'] = ['Backdated leave requests are not allowed.'];
            }
        }

        // 8. Check attachment requirement
        if ($leaveType->requires_attachment) {
            $attachmentThreshold = $leaveType->attachment_required_after_days;

            // If threshold is null, always require attachment
            // If threshold is set, require attachment only when total_days exceeds it
            $attachmentRequired = ($attachmentThreshold === null)
                || ($totalDays > $attachmentThreshold);

            if ($attachmentRequired && empty($data['attachments'])) {
                $thresholdText = $attachmentThreshold !== null
                    ? "for requests exceeding {$attachmentThreshold} days"
                    : '';
                $errors['attachments'] = [
                    "Attachment is required for this leave type {$thresholdText}."
                ];
            }
        }

        return $errors;
    }

    // ==================== OVERLAP DETECTION ====================

    /**
     * Check if a leave request overlaps with existing approved/pending requests.
     *
     * Two date ranges overlap if: start1 <= end2 AND end1 >= start2
     *
     * @param int $userId The user's ID
     * @param Carbon $startDate Start date of new request
     * @param Carbon $endDate End date of new request
     * @param int|null $excludeRequestId Request ID to exclude (for updates)
     * @return bool True if overlap exists
     */
    public function checkOverlap(
        int $userId,
        Carbon $startDate,
        Carbon $endDate,
        ?int $excludeRequestId = null
    ): bool {
        $query = LeaveRequest::forUser($userId)
            ->overlapping($startDate, $endDate)
            ->whereNotIn('status', [
                LeaveRequest::STATUS_CANCELLED,
                LeaveRequest::STATUS_REJECTED,
            ]);

        if ($excludeRequestId !== null) {
            $query->where('id', '!=', $excludeRequestId);
        }

        return $query->exists();
    }

    // ==================== IDEMPOTENCY ====================

    /**
     * Check if a request with the given idempotency key already exists.
     *
     * Used to prevent duplicate submissions during retries.
     *
     * @param string $idempotencyKey The idempotency key
     * @return LeaveRequest|null The existing request if found
     */
    public function checkIdempotency(string $idempotencyKey): ?LeaveRequest {
        return LeaveRequest::where('idempotency_key', $idempotencyKey)->first();
    }

    // ==================== REQUEST RETRIEVAL ====================

    /**
     * Get all leave requests for a user, optionally filtered by year.
     *
     * @param int $userId The user's ID
     * @param int|null $year Filter by leave year (null for all years)
     * @return Collection Collection of leave requests
     */
    public function getRequestsForUser(int $userId, ?int $year = null): Collection {
        $query = LeaveRequest::forUser($userId)
            ->with(['leaveType', 'approver', 'attachments'])
            ->orderByDesc('submitted_at');

        if ($year !== null) {
            // Filter by year based on start_date
            $query->whereYear('start_date', $year);
        }

        return $query->get();
    }

    // ==================== REQUEST UPDATE ====================

    /**
     * Update a pending leave request.
     *
     * Only allowed if the request status is PENDING.
     * Recalculates total_days if dates changed and adjusts pending balance.
     *
     * @param LeaveRequest $request The request to update
     * @param array $data Updated data
     * @return LeaveRequest The updated request
     * @throws InvalidArgumentException If request is not pending
     * @throws ValidationException If validation fails
     */
    public function updateRequest(LeaveRequest $request, array $data): LeaveRequest {
        if (!$request->isPending()) {
            throw new InvalidArgumentException('Only pending requests can be updated.');
        }

        return DB::transaction(function () use ($request, $data) {
            $user = $request->user;
            $originalTotalDays = (float) $request->total_days;

            // Capture old values for audit log (AUDT-01)
            $oldValues = $request->toArray();

            // Merge existing data with updates
            $updateData = [
                'leave_type_id' => $data['leave_type_id'] ?? $request->leave_type_id,
                'start_date' => $data['start_date'] ?? $request->start_date,
                'end_date' => $data['end_date'] ?? $request->end_date,
                'start_half_day' => array_key_exists('start_half_day', $data)
                    ? $data['start_half_day']
                    : $request->start_half_day,
                'end_half_day' => array_key_exists('end_half_day', $data)
                    ? $data['end_half_day']
                    : $request->end_half_day,
                'reason' => $data['reason'] ?? $request->reason,
                'attachments' => $data['attachments'] ?? [],
            ];

            // Calculate new total days
            $startDate = Carbon::parse($updateData['start_date']);
            $endDate = Carbon::parse($updateData['end_date']);
            $newTotalDays = $this->leaveCalculationService->calculateLeaveDays(
                $startDate,
                $endDate,
                $updateData['start_half_day'],
                $updateData['end_half_day']
            );

            // Validate with the existing request excluded from overlap check
            $errors = $this->validateRequest($user, $updateData, $request, $newTotalDays);
            if (!empty($errors)) {
                throw ValidationException::withMessages($errors);
            }

            // Update balance if total days changed
            if ($newTotalDays !== $originalTotalDays) {
                $balance = $request->balance;
                $difference = $newTotalDays - $originalTotalDays;
                $balance->pending = (float) $balance->pending + $difference;
                $balance->save();
            }

            // Check if leave type changed
            if ($updateData['leave_type_id'] !== $request->leave_type_id) {
                // Restore old balance pending
                $oldBalance = $request->balance;
                $oldBalance->pending = (float) $oldBalance->pending - $originalTotalDays;
                $oldBalance->save();

                // Get new balance
                $newLeaveType = LeaveType::find($updateData['leave_type_id']);
                $leaveYear = $this->leaveBalanceService->getCurrentLeaveYear();
                $newBalance = $this->leaveBalanceService->getOrCreateBalance($user, $newLeaveType, $leaveYear);

                // Update new balance pending
                $newBalance->pending = (float) $newBalance->pending + $newTotalDays;
                $newBalance->save();

                $request->leave_balance_id = $newBalance->id;
            }

            // Update request
            $request->leave_type_id = $updateData['leave_type_id'];
            $request->start_date = $startDate;
            $request->end_date = $endDate;
            $request->start_half_day = $updateData['start_half_day'];
            $request->end_half_day = $updateData['end_half_day'];
            $request->total_days = $newTotalDays;
            $request->reason = $updateData['reason'];
            $request->save();

            // Log audit entry for request update (AUDT-01)
            LeaveAuditLog::log(
                $request,
                LeaveAuditLog::ACTION_UPDATE,
                $oldValues,
                $request->fresh()->toArray(),
                'Leave request updated'
            );

            return $request->fresh(['leaveType', 'balance', 'user']);
        });
    }

    // ==================== REQUEST CANCELLATION ====================

    /**
     * Cancel a pending leave request.
     *
     * Sets status to CANCELLED, restores pending balance, and records cancellation details.
     *
     * @param LeaveRequest $request The request to cancel
     * @param int $cancelledBy User ID of person cancelling
     * @param string $reason Reason for cancellation
     * @return LeaveRequest The cancelled request
     * @throws InvalidArgumentException If request is not pending
     */
    public function cancelPendingRequest(
        LeaveRequest $request,
        int $cancelledBy,
        string $reason
    ): LeaveRequest {
        if (!$request->isPending()) {
            throw new InvalidArgumentException('Only pending requests can be cancelled.');
        }

        return DB::transaction(function () use ($request, $cancelledBy, $reason) {
            // Capture old values for audit log (AUDT-01)
            $oldValues = $request->toArray();

            // Restore pending balance
            $balance = $request->balance;
            $balance->pending = (float) $balance->pending - (float) $request->total_days;
            $balance->save();

            // Update request status
            $request->status = LeaveRequest::STATUS_CANCELLED;
            $request->cancelled_at = now();
            $request->cancelled_by = $cancelledBy;
            $request->cancellation_reason = $reason;
            $request->save();

            // Log audit entry for request cancellation (AUDT-01)
            LeaveAuditLog::log(
                $request,
                LeaveAuditLog::ACTION_CANCEL,
                $oldValues,
                $request->fresh()->toArray(),
                'Pending request cancelled by staff: ' . $reason
            );

            return $request->fresh(['leaveType', 'balance', 'user', 'cancelledBy']);
        });
    }
}
