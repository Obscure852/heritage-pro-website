<?php

namespace App\Console\Commands;

use App\Models\Leave\LeaveRequest;
use App\Models\Leave\LeaveSetting;
use App\Models\User;
use App\Services\Leave\LeaveNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Artisan command to send reminders to managers for pending leave requests (NOTF-06).
 *
 * Scheduled to run daily at 9 AM. Sends email reminders to managers who have
 * pending leave requests older than X hours (configurable via pending_approval_reminder_hours setting).
 */
class SendPendingApprovalReminders extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leave:send-pending-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminders to managers for pending leave requests';

    /**
     * @var LeaveNotificationService
     */
    protected LeaveNotificationService $notificationService;

    /**
     * Create a new command instance.
     *
     * @param LeaveNotificationService $notificationService
     */
    public function __construct(LeaveNotificationService $notificationService) {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int {
        // Get reminder hours setting (default: 24)
        $settingValue = LeaveSetting::get('pending_approval_reminder_hours', ['hours' => 24]);
        $reminderHours = is_array($settingValue) ? ($settingValue['hours'] ?? 24) : 24;

        // Calculate threshold time
        $threshold = Carbon::now()->subHours($reminderHours);

        $this->info("Looking for pending requests submitted before {$threshold->toDateTimeString()} ({$reminderHours} hours ago)...");

        // Find pending requests older than threshold
        $pendingRequests = LeaveRequest::pending()
            ->where('submitted_at', '<=', $threshold)
            ->with(['user', 'leaveType'])
            ->get();

        if ($pendingRequests->isEmpty()) {
            $this->info("No pending requests older than {$reminderHours} hours.");
            return Command::SUCCESS;
        }

        // Group by approver (reporting_to)
        $byManager = $pendingRequests->groupBy(function ($request) {
            return $request->user->reporting_to;
        });

        $count = 0;
        foreach ($byManager as $managerId => $requests) {
            if (!$managerId) {
                $this->warn("  - Skipped {$requests->count()} request(s) with no manager assigned");
                continue;
            }

            $manager = User::find($managerId);
            if (!$manager) {
                $this->warn("  - Manager ID {$managerId} not found, skipped {$requests->count()} request(s)");
                continue;
            }

            $this->notificationService->sendPendingApprovalReminder($manager, $requests);
            $this->line("  - Sent reminder to {$manager->name} for {$requests->count()} pending request(s)");
            $count++;
        }

        $this->info("Sent pending approval reminders to {$count} manager(s).");

        return Command::SUCCESS;
    }
}
