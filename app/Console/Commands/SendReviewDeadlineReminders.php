<?php

namespace App\Console\Commands;

use App\Models\DocumentApproval;
use App\Services\Documents\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Artisan command to send reminder notifications for approaching document review deadlines.
 *
 * Queries pending/in-review approvals where the due date is within the next 2 days
 * or already past due, and sends a deadline reminder notification to each reviewer
 * (only once per approval, tracked via reminder_sent_at).
 */
class SendReviewDeadlineReminders extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'documents:send-review-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder notifications for approaching document review deadlines';

    /**
     * Execute the console command.
     */
    public function handle(): int {
        $this->info('Checking for approaching review deadlines...');

        $approvals = DocumentApproval::whereIn('status', [
                DocumentApproval::STATUS_PENDING,
                DocumentApproval::STATUS_IN_REVIEW,
            ])
            ->whereNotNull('due_date')
            ->where('due_date', '<=', now()->addDays(2)->endOfDay())
            ->whereNull('reminder_sent_at')
            ->with(['document', 'reviewer', 'document.owner'])
            ->get();

        if ($approvals->isEmpty()) {
            $this->info('No approaching deadlines found.');
            return Command::SUCCESS;
        }

        $notificationService = app(NotificationService::class);
        $sentCount = 0;

        foreach ($approvals as $approval) {
            try {
                $notificationService->notifyDeadlineApproaching($approval);
                $sentCount++;

                $this->info("Sent deadline reminder for document '{$approval->document->title}' to {$approval->reviewer->name}");
            } catch (\Throwable $e) {
                Log::error('Failed to send review deadline reminder', [
                    'approval_id' => $approval->id,
                    'document_id' => $approval->document_id,
                    'reviewer_id' => $approval->reviewer_id,
                    'error' => $e->getMessage(),
                ]);
                $this->error("Failed to send reminder for approval #{$approval->id}: {$e->getMessage()}");
            }
        }

        $this->info("Sent {$sentCount} deadline reminder(s).");

        return Command::SUCCESS;
    }
}
