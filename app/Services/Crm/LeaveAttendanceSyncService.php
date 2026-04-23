<?php

namespace App\Services\Crm;

use App\Models\CrmAttendanceCode;
use App\Models\CrmAttendanceHoliday;
use App\Models\CrmAttendanceRecord;
use App\Models\CrmLeaveRequest;
use App\Models\CrmLeaveSetting;
use Illuminate\Support\Carbon;

class LeaveAttendanceSyncService
{
    public function __construct(
        private readonly AttendanceShiftResolver $shiftResolver,
    ) {
    }

    public function syncOnApproval(CrmLeaveRequest $request): void
    {
        $settings = CrmLeaveSetting::instance();

        if (! $settings->attendance_integration_enabled || ! $settings->auto_mark_attendance_on_approve) {
            return;
        }

        $user = $request->user;
        $leaveCode = CrmAttendanceCode::where('code', 'L')->first();
        $halfDayCode = CrmAttendanceCode::where('code', 'HD')->first();

        if (! $leaveCode) {
            return;
        }

        $current = $request->start_date->copy();
        $endDate = $request->end_date;

        while ($current->lte($endDate)) {
            if ($this->isCountableDay($user, $current)) {
                $isHalfDay = false;

                if ($current->isSameDay($request->start_date) && $request->start_half !== 'full') {
                    $isHalfDay = true;
                } elseif ($current->isSameDay($request->end_date) && $request->end_half !== 'full') {
                    $isHalfDay = true;
                }

                $code = ($isHalfDay && $halfDayCode) ? $halfDayCode : $leaveCode;

                CrmAttendanceRecord::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'date' => $current->toDateString(),
                    ],
                    [
                        'attendance_code_id' => $code->id,
                        'source' => 'leave_system',
                        'status' => 'active',
                        'leave_request_id' => $request->id,
                        'clock_in_note' => "Auto-generated: Leave #{$request->id} ({$request->leaveType->name})",
                    ]
                );
            }

            $current->addDay();
        }

        $request->update(['attendance_synced' => true]);
    }

    public function rollbackOnCancel(CrmLeaveRequest $request): void
    {
        $settings = CrmLeaveSetting::instance();

        if (! $settings->attendance_integration_enabled || ! $settings->auto_clear_attendance_on_cancel) {
            return;
        }

        if (! $request->attendance_synced) {
            return;
        }

        CrmAttendanceRecord::query()
            ->where('user_id', $request->user_id)
            ->where('leave_request_id', $request->id)
            ->where('source', 'leave_system')
            ->delete();

        $request->update(['attendance_synced' => false]);
    }

    private function isCountableDay($user, Carbon $date): bool
    {
        if (! $this->shiftResolver->isWorkingDay($user, $date)) {
            return false;
        }

        return ! CrmAttendanceHoliday::query()
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
    }
}
