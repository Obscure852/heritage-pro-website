<?php

namespace App\Console\Commands\Fee;

use App\Jobs\Fee\SendPaymentReminderJob;
use App\Models\Fee\StudentInvoice;
use App\Services\Fee\PaymentPlanService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendOverdueNotifications extends Command
{
    protected $signature = 'fee:send-overdue-notifications
                            {--dry-run : Show what would be sent without sending}
                            {--year= : Limit to specific year}';

    protected $description = 'Send overdue payment notifications';

    public function __construct(protected PaymentPlanService $paymentPlanService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        if (!settings('fee.notify_on_overdue', true)) {
            $this->info('Overdue notifications are disabled in settings.');
            return self::SUCCESS;
        }

        $isDryRun = $this->option('dry-run');
        $year = $this->option('year');

        $this->info('Checking for overdue payments...');

        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No notifications will be sent');
        }

        // Get overdue reminder intervals from settings
        $intervals = json_decode(settings('fee.overdue_reminder_intervals', '[7, 14, 30]'), true) ?? [7, 14, 30];

        // 1. Send overdue invoice notifications
        $invoiceCount = $this->sendOverdueInvoiceNotifications($intervals, $year, $isDryRun);

        // 2. Update and notify overdue installments
        $installmentCount = $this->handleOverdueInstallments($isDryRun);

        // 3. Update invoice statuses to overdue
        $this->updateOverdueInvoiceStatuses($year);

        $this->newLine();
        $this->info('Overdue notification process complete.');
        $this->table(
            ['Type', 'Notifications Sent'],
            [
                ['Overdue Invoices', $invoiceCount],
                ['Overdue Installments', $installmentCount],
                ['Total', $invoiceCount + $installmentCount],
            ]
        );

        Log::info('Overdue notifications sent', [
            'invoice_notifications' => $invoiceCount,
            'installment_notifications' => $installmentCount,
            'dry_run' => $isDryRun,
        ]);

        return self::SUCCESS;
    }

    /**
     * Send notifications for overdue invoices at configured intervals.
     */
    protected function sendOverdueInvoiceNotifications(array $intervals, ?string $year, bool $isDryRun): int
    {
        $sent = 0;

        foreach ($intervals as $daysOverdue) {
            $targetDate = now()->subDays($daysOverdue)->toDateString();

            $query = StudentInvoice::with('student')
                ->where('balance', '>', 0)
                ->whereIn('status', [
                    StudentInvoice::STATUS_ISSUED,
                    StudentInvoice::STATUS_PARTIAL,
                    StudentInvoice::STATUS_OVERDUE,
                ])
                ->whereDate('due_date', $targetDate);

            if ($year) {
                $query->where('year', $year);
            }

            $invoices = $query->get();

            $this->info("Found {$invoices->count()} invoices {$daysOverdue} days overdue");

            foreach ($invoices as $invoice) {
                // Check cooldown (7 days between reminders)
                if (!$invoice->canSendReminder(7)) {
                    continue;
                }

                if ($isDryRun) {
                    $this->line("  Would send {$daysOverdue}-day overdue notice for #{$invoice->invoice_number}");
                    $sent++;
                    continue;
                }

                try {
                    SendPaymentReminderJob::dispatch($invoice, 'overdue', null, $daysOverdue);
                    $invoice->markReminderSent();
                    $sent++;
                    $this->line("  Sent {$daysOverdue}-day overdue notice for #{$invoice->invoice_number}");
                } catch (\Exception $e) {
                    Log::error('Failed to send overdue notification', [
                        'invoice_id' => $invoice->id,
                        'days_overdue' => $daysOverdue,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return $sent;
    }

    /**
     * Handle overdue installments - mark as overdue and send notifications.
     */
    protected function handleOverdueInstallments(bool $isDryRun): int
    {
        // Mark installments as overdue
        $markedOverdue = $this->paymentPlanService->markOverdueInstallments();
        $this->info("Marked {$markedOverdue} installments as overdue");

        // Get newly overdue installments for notification
        $overdueInstallments = $this->paymentPlanService->getOverdueInstallments();
        $sent = 0;

        foreach ($overdueInstallments as $installment) {
            $invoice = $installment->paymentPlan->invoice;

            if ($isDryRun) {
                $this->line("  Would send overdue notice for installment #{$installment->installment_number}");
                $sent++;
                continue;
            }

            try {
                $daysOverdue = now()->diffInDays($installment->due_date);
                SendPaymentReminderJob::dispatch($invoice, 'installment_overdue', $installment, $daysOverdue);
                $sent++;
            } catch (\Exception $e) {
                Log::error('Failed to send installment overdue notification', [
                    'installment_id' => $installment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $sent;
    }

    /**
     * Update invoice statuses to overdue where applicable.
     */
    protected function updateOverdueInvoiceStatuses(?string $year): void
    {
        $query = StudentInvoice::where('balance', '>', 0)
            ->whereIn('status', [
                StudentInvoice::STATUS_ISSUED,
                StudentInvoice::STATUS_PARTIAL,
            ])
            ->where('due_date', '<', today());

        if ($year) {
            $query->where('year', $year);
        }

        $updated = $query->update(['status' => StudentInvoice::STATUS_OVERDUE]);

        $this->info("Updated {$updated} invoices to overdue status");
    }
}
