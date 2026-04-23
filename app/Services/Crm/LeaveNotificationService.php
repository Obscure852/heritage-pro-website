<?php

namespace App\Services\Crm;

use App\Models\CrmLeaveRequest;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class LeaveNotificationService
{
    public function notifySubmitted(CrmLeaveRequest $request): void
    {
        $approver = $request->currentApprover;

        if (! $approver) {
            return;
        }

        Log::info('Leave notification: Request submitted', [
            'leave_request_id' => $request->id,
            'applicant' => $request->user->name,
            'approver' => $approver->name,
            'type' => $request->leaveType->name,
            'dates' => $request->start_date->format('Y-m-d') . ' to ' . $request->end_date->format('Y-m-d'),
        ]);
    }

    public function notifyApproved(CrmLeaveRequest $request): void
    {
        Log::info('Leave notification: Request approved', [
            'leave_request_id' => $request->id,
            'applicant' => $request->user->name,
            'approved_by' => $request->approver?->name,
        ]);
    }

    public function notifyRejected(CrmLeaveRequest $request): void
    {
        Log::info('Leave notification: Request rejected', [
            'leave_request_id' => $request->id,
            'applicant' => $request->user->name,
            'rejected_by' => $request->rejector?->name,
            'reason' => $request->rejection_reason,
        ]);
    }

    public function notifyCancelled(CrmLeaveRequest $request): void
    {
        Log::info('Leave notification: Request cancelled', [
            'leave_request_id' => $request->id,
            'applicant' => $request->user->name,
        ]);
    }

    public function notifyEscalated(CrmLeaveRequest $request, User $newApprover): void
    {
        Log::info('Leave notification: Request escalated', [
            'leave_request_id' => $request->id,
            'applicant' => $request->user->name,
            'new_approver' => $newApprover->name,
            'escalation_level' => $request->escalation_level,
        ]);
    }

    public function sendApprovalReminder(CrmLeaveRequest $request): void
    {
        $approver = $request->currentApprover;

        if (! $approver) {
            return;
        }

        Log::info('Leave notification: Approval reminder sent', [
            'leave_request_id' => $request->id,
            'approver' => $approver->name,
            'submitted_at' => $request->submitted_at->format('Y-m-d H:i'),
        ]);
    }
}
