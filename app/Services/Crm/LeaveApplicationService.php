<?php

namespace App\Services\Crm;

use App\Models\CrmAttendanceHoliday;
use App\Models\CrmLeaveApprovalTrail;
use App\Models\CrmLeaveRequest;
use App\Models\CrmLeaveRequestAttachment;
use App\Models\CrmLeaveSetting;
use App\Models\CrmLeaveType;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaveApplicationService
{
    public function __construct(
        private readonly LeaveBalanceService $balanceService,
        private readonly AttendanceShiftResolver $shiftResolver,
    ) {
    }

    public function apply(User $user, array $data): CrmLeaveRequest
    {
        $leaveType = CrmLeaveType::findOrFail($data['leave_type_id']);
        $settings = CrmLeaveSetting::instance();

        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);
        $startHalf = $data['start_half'] ?? 'full';
        $endHalf = $data['end_half'] ?? 'full';

        $this->validateDates($user, $startDate, $endDate, $settings);
        $this->validateLeaveType($leaveType, $startHalf, $endHalf);
        $this->validateGenderRestriction($leaveType, $user);
        $this->validateOverlap($user, $startDate, $endDate);

        $totalDays = $this->calculateTotalDays($user, $startDate, $endDate, $startHalf, $endHalf);

        if ($totalDays <= 0) {
            throw ValidationException::withMessages([
                'start_date' => ['The selected dates contain no working days.'],
            ]);
        }

        $this->validateConsecutiveDays($leaveType, $totalDays);
        $this->validateNoticePeriod($leaveType, $startDate);

        $year = $this->balanceService->currentLeaveYear();
        if (! $this->balanceService->hasEnoughBalance($user, $leaveType, $totalDays, $year)) {
            throw ValidationException::withMessages([
                'leave_type_id' => ['Insufficient leave balance. You have ' . $this->balanceService->getOrCreateBalance($user, $leaveType, $year)->effective_available_days . ' days available.'],
            ]);
        }

        $approver = $this->resolveApprover($user);

        return DB::transaction(function () use ($user, $leaveType, $startDate, $endDate, $startHalf, $endHalf, $totalDays, $data, $approver, $year) {
            $request = CrmLeaveRequest::create([
                'user_id' => $user->id,
                'leave_type_id' => $leaveType->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'start_half' => $startHalf,
                'end_half' => $endHalf,
                'total_days' => $totalDays,
                'reason' => $data['reason'],
                'status' => 'pending',
                'submitted_at' => now(),
                'current_approver_id' => $approver->id,
                'escalation_level' => 1,
            ]);

            CrmLeaveApprovalTrail::create([
                'leave_request_id' => $request->id,
                'user_id' => $user->id,
                'action' => 'submitted',
                'level' => 0,
                'comment' => null,
            ]);

            $balance = $this->balanceService->getOrCreateBalance($user, $leaveType, $year);
            $this->balanceService->reservePendingDays($balance, $totalDays);

            if (! empty($data['attachments'])) {
                foreach ($data['attachments'] as $file) {
                    $path = $file->store('crm/leave-attachments', 'local');
                    CrmLeaveRequestAttachment::create([
                        'leave_request_id' => $request->id,
                        'file_path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'size_bytes' => $file->getSize(),
                    ]);
                }
            }

            return $request;
        });
    }

    public function cancel(CrmLeaveRequest $request, User $user, ?string $reason = null): CrmLeaveRequest
    {
        if (! $request->canBeCancelled()) {
            throw ValidationException::withMessages([
                'status' => ['This leave request cannot be cancelled.'],
            ]);
        }

        $wasApproved = $request->isApproved();

        return DB::transaction(function () use ($request, $user, $reason, $wasApproved) {
            $request->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
            ]);

            CrmLeaveApprovalTrail::create([
                'leave_request_id' => $request->id,
                'user_id' => $user->id,
                'action' => 'cancelled',
                'level' => $request->escalation_level,
                'comment' => $reason,
            ]);

            $leaveType = $request->leaveType;
            $balance = $this->balanceService->getOrCreateBalance(
                $request->user,
                $leaveType,
                $this->balanceService->currentLeaveYear()
            );

            if ($wasApproved) {
                $this->balanceService->reverseUsedDays($balance, (float) $request->total_days);
            } else {
                $this->balanceService->releasePendingDays($balance, (float) $request->total_days);
            }

            return $request->fresh();
        });
    }

    public function calculateTotalDays(User $user, Carbon $startDate, Carbon $endDate, string $startHalf = 'full', string $endHalf = 'full'): float
    {
        $totalDays = 0.0;
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            if ($this->isCountableDay($user, $current)) {
                $dayValue = 1.0;

                if ($current->isSameDay($startDate) && $startHalf !== 'full') {
                    $dayValue = 0.5;
                } elseif ($current->isSameDay($endDate) && $endHalf !== 'full') {
                    $dayValue = 0.5;
                }

                $totalDays += $dayValue;
            }

            $current->addDay();
        }

        return $totalDays;
    }

    private function isCountableDay(User $user, Carbon $date): bool
    {
        if (! $this->shiftResolver->isWorkingDay($user, $date)) {
            return false;
        }

        $isHoliday = CrmAttendanceHoliday::query()
            ->where('date', $date->toDateString())
            ->where('is_active', true)
            ->where(function ($q) use ($user) {
                $q->where('applies_to', 'all')
                    ->orWhere(function ($q2) use ($user) {
                        $q2->where('applies_to', 'department')
                            ->where('scope_id', $user->department_id);
                    })
                    ->orWhere(function ($q2) use ($user) {
                        $q2->where('applies_to', 'shift')
                            ->where('scope_id', $user->shift_id);
                    });
            })
            ->exists();

        return ! $isHoliday;
    }

    public function resolveApprover(User $user): User
    {
        if ($user->reports_to_user_id) {
            $supervisor = User::where('active', true)->find($user->reports_to_user_id);
            if ($supervisor) {
                return $supervisor;
            }
        }

        $admin = User::where('active', true)->where('role', 'admin')->where('id', '!=', $user->id)->first();

        if ($admin) {
            return $admin;
        }

        throw ValidationException::withMessages([
            'approver' => ['No available approver found. Please contact an administrator.'],
        ]);
    }

    private function validateDates(User $user, Carbon $startDate, Carbon $endDate, CrmLeaveSetting $settings): void
    {
        if ($endDate->lt($startDate)) {
            throw ValidationException::withMessages([
                'end_date' => ['End date must be on or after the start date.'],
            ]);
        }

        if (! $settings->allow_retroactive_leave && $startDate->lt(today())) {
            throw ValidationException::withMessages([
                'start_date' => ['Retroactive leave requests are not allowed.'],
            ]);
        }

        if ($settings->allow_retroactive_leave && $startDate->lt(today()->subDays($settings->retroactive_limit_days))) {
            throw ValidationException::withMessages([
                'start_date' => ["Leave cannot be requested more than {$settings->retroactive_limit_days} days in the past."],
            ]);
        }
    }

    private function validateLeaveType(CrmLeaveType $type, string $startHalf, string $endHalf): void
    {
        if (! $type->is_active) {
            throw ValidationException::withMessages([
                'leave_type_id' => ['This leave type is no longer available.'],
            ]);
        }

        if (! $type->allow_half_day && ($startHalf !== 'full' || $endHalf !== 'full')) {
            throw ValidationException::withMessages([
                'start_half' => ['Half-day leave is not allowed for this leave type.'],
            ]);
        }
    }

    private function validateOverlap(User $user, Carbon $startDate, Carbon $endDate): void
    {
        $overlapping = CrmLeaveRequest::query()
            ->forUser($user->id)
            ->overlapping($startDate, $endDate)
            ->exists();

        if ($overlapping) {
            throw ValidationException::withMessages([
                'start_date' => ['You already have a leave request that overlaps with these dates.'],
            ]);
        }
    }

    private function validateConsecutiveDays(CrmLeaveType $type, float $totalDays): void
    {
        if ($type->max_consecutive_days !== null && $totalDays > $type->max_consecutive_days) {
            throw ValidationException::withMessages([
                'end_date' => ["Maximum {$type->max_consecutive_days} consecutive days allowed for {$type->name}."],
            ]);
        }
    }

    private function validateNoticePeriod(CrmLeaveType $type, Carbon $startDate): void
    {
        if ($type->min_notice_days > 0 && $startDate->lt(today()->addDays($type->min_notice_days))) {
            throw ValidationException::withMessages([
                'start_date' => ["{$type->name} requires at least {$type->min_notice_days} days advance notice."],
            ]);
        }
    }

    private function validateGenderRestriction(CrmLeaveType $type, User $user): void
    {
        if (! $type->isAvailableForGender($user->gender)) {
            throw ValidationException::withMessages([
                'leave_type_id' => ["{$type->name} is not available for your profile."],
            ]);
        }
    }
}
