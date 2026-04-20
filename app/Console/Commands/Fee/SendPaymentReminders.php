<?php

namespace App\Console\Commands\Fee;

use App\Jobs\Fee\SendPaymentReminderJob;
use App\Models\Fee\PaymentPlanInstallment;
use App\Models\Fee\StudentInvoice;
use App\Models\Fee\PaymentPlan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendPaymentReminders extends Command
{
    protected $signature = 'fee:send-reminders
                            {--dry-run : Show what would be sent without sending}
                            {--year= : Limit to specific year}';

    protected $description = 'Send payment reminder notifications for upcoming due dates';

    public function handle(): int
    {
        $reminderDaysBefore = (int) settings('fee.reminder_days_before', 3);
        $isDryRun = $this->option('dry-run');
        $year = $this->option('year');

        $this->info("Checking for payments due in {$reminderDaysBefore} days...");

        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No notifications will be sent');
        }

        $targetDate = now()->addDays($reminderDaysBefore)->toDateString();

        // 1. Invoice due date reminders
        $invoiceCount = $this->sendInvoiceReminders($targetDate, $year, $isDryRun);

        // 2. Installment due date reminders
        $installmentCount = $this->sendInstallmentReminders($targetDate, $isDryRun);

        $this->newLine();
        $this->info('Payment reminder process complete.');
        $this->table(
            ['Type', 'Reminders Sent'],
            [
                ['Invoice Due Dates', $invoiceCount],
                ['Installment Due Dates', $installmentCount],
                ['Total', $invoiceCount + $installmentCount],
            ]
        );

        Log::info('Payment reminders sent', [
            'invoice_reminders' => $invoiceCount,
            'installment_reminders' => $installmentCount,
            'target_date' => $targetDate,
            'dry_run' => $isDryRun,
        ]);

        return self::SUCCESS;
    }

    /**
     * Send reminders for invoices with due dates approaching.
     */
    protected function sendInvoiceReminders(string $targetDate, ?string $year, bool $isDryRun): int
    {
        $query = StudentInvoice::with('student')
            ->where('balance', '>', 0)
            ->whereIn('status', [
                StudentInvoice::STATUS_ISSUED,
                StudentInvoice::STATUS_PARTIAL,
            ])
            ->whereDate('due_date', $targetDate);

        if ($year) {
            $query->where('year', $year);
        }

        $invoices = $query->get();
        $sent = 0;

        $this->info("Found {$invoices->count()} invoices due on {$targetDate}");

        foreach ($invoices as $invoice) {
            // Check cooldown
            if (!$invoice->canSendReminder(3)) {
                $this->line("  Skipping invoice #{$invoice->invoice_number} - reminder sent recently");
                continue;
            }

            if ($isDryRun) {
                $this->line("  Would send reminder for invoice #{$invoice->invoice_number} to {$invoice->student->full_name}");
                $sent++;
                continue;
            }

            try {
                SendPaymentReminderJob::dispatch($invoice, 'invoice_due');
                $invoice->markReminderSent();
                $sent++;
                $this->line("  Sent reminder for invoice #{$invoice->invoice_number}");
            } catch (\Exception $e) {
                Log::error('Failed to send invoice reminder', [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $sent;
    }

    /**
     * Send reminders for installments with due dates approaching.
     */
    protected function sendInstallmentReminders(string $targetDate, bool $isDryRun): int
    {
        $installments = PaymentPlanInstallment::with(['paymentPlan.student', 'paymentPlan.invoice'])
            ->whereHas('paymentPlan', function ($query) {
                $query->where('status', PaymentPlan::STATUS_ACTIVE);
            })
            ->whereIn('status', [
                PaymentPlanInstallment::STATUS_PENDING,
                PaymentPlanInstallment::STATUS_PARTIAL,
            ])
            ->whereDate('due_date', $targetDate)
            ->get();

        $sent = 0;

        $this->info("Found {$installments->count()} installments due on {$targetDate}");

        foreach ($installments as $installment) {
            $invoice = $installment->paymentPlan->invoice;
            $student = $installment->paymentPlan->student;

            if ($isDryRun) {
                $this->line("  Would send installment #{$installment->installment_number} reminder to {$student->full_name}");
                $sent++;
                continue;
            }

            try {
                SendPaymentReminderJob::dispatch($invoice, 'installment_due', $installment);
                $sent++;
                $this->line("  Sent installment reminder #{$installment->installment_number}");
            } catch (\Exception $e) {
                Log::error('Failed to send installment reminder', [
                    'installment_id' => $installment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $sent;
    }
}
