<?php

namespace App\Console\Commands;

use App\Models\CrmLeaveRequest;
use App\Models\CrmLeaveSetting;
use App\Services\Crm\LeaveNotificationService;
use Illuminate\Console\Command;

class LeaveApprovalReminder extends Command
{
    protected $signature = 'leave:approval-reminder';

    protected $description = 'Send reminders to approvers with pending leave requests';

    public function handle(LeaveNotificationService $notificationService): int
    {
        $settings = CrmLeaveSetting::instance();
        $cutoff = now()->subHours($settings->approval_reminder_hours);

        $pendingRequests = CrmLeaveRequest::query()
            ->where('status', 'pending')
            ->where('submitted_at', '<=', $cutoff)
            ->whereNotNull('current_approver_id')
            ->with(['user', 'leaveType', 'currentApprover'])
            ->get();

        $count = 0;

        foreach ($pendingRequests as $request) {
            $notificationService->sendApprovalReminder($request);
            $count++;
        }

        $this->info("Sent {$count} approval reminder(s).");

        return self::SUCCESS;
    }
}
