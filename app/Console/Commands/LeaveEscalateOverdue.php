<?php

namespace App\Console\Commands;

use App\Services\Crm\LeaveApprovalService;
use Illuminate\Console\Command;

class LeaveEscalateOverdue extends Command
{
    protected $signature = 'leave:escalate-overdue';

    protected $description = 'Escalate pending leave requests that have exceeded the approval timeout';

    public function handle(LeaveApprovalService $approvalService): int
    {
        $escalated = $approvalService->escalateOverdueRequests();

        $this->info("Escalated {$escalated} overdue leave request(s).");

        return self::SUCCESS;
    }
}
