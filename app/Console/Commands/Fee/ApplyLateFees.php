<?php

namespace App\Console\Commands\Fee;

use App\Models\Fee\StudentInvoice;
use App\Services\Fee\InvoiceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ApplyLateFees extends Command
{
    protected $signature = 'fee:apply-late-fees
                            {--dry-run : Show what would be done without making changes}
                            {--year= : Limit to specific year}';

    protected $description = 'Apply late fees to overdue invoices based on configured settings';

    public function __construct(protected InvoiceService $invoiceService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $settings = $this->invoiceService->getLateFeeSettings();

        if (!$settings['enable_late_fees']) {
            $this->info('Late fees are disabled in settings.');
            return self::SUCCESS;
        }

        $this->info('Starting late fee application...');
        $this->info("Grace period: {$settings['late_fee_grace_period']} days");
        $this->info("Fee type: {$settings['late_fee_type']}");
        $this->info("Fee amount: {$settings['late_fee_amount']}");

        $isDryRun = $this->option('dry-run');
        $year = $this->option('year');

        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        // Get overdue invoices past grace period
        $gracePeriodDate = now()->subDays($settings['late_fee_grace_period']);

        $query = StudentInvoice::with('student')
            ->where('balance', '>', 0)
            ->whereIn('status', [
                StudentInvoice::STATUS_ISSUED,
                StudentInvoice::STATUS_PARTIAL,
                StudentInvoice::STATUS_OVERDUE,
            ])
            ->where('due_date', '<=', $gracePeriodDate)
            ->whereDoesntHave('lateFeeCharges', function ($q) {
                $q->whereDate('applied_date', today());
            });

        if ($year) {
            $query->where('year', $year);
        }

        $invoices = $query->get();

        $this->info("Found {$invoices->count()} invoices eligible for late fees.");

        $applied = 0;
        $skipped = 0;

        $this->withProgressBar($invoices, function ($invoice) use ($settings, $isDryRun, &$applied, &$skipped) {
            if (!$this->invoiceService->canApplyLateFee($invoice)) {
                $skipped++;
                return;
            }

            $amount = $this->invoiceService->calculateLateFeeAmount($invoice);
            $daysOverdue = now()->diffInDays($invoice->due_date);

            if ($isDryRun) {
                Log::info('DRY RUN: Would apply late fee', [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'student' => $invoice->student->full_name ?? 'N/A',
                    'amount' => $amount,
                    'days_overdue' => $daysOverdue,
                ]);
                $applied++;
                return;
            }

            try {
                $this->invoiceService->applyLateFee(
                    $invoice,
                    $amount,
                    $settings['late_fee_type'],
                    $daysOverdue
                );
                $applied++;
            } catch (\Exception $e) {
                Log::error('Failed to apply late fee', [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage(),
                ]);
                $skipped++;
            }
        });

        $this->newLine(2);
        $this->info("Late fee application complete.");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Applied', $applied],
                ['Skipped', $skipped],
                ['Total Processed', $invoices->count()],
            ]
        );

        Log::info('Late fee application completed', [
            'applied' => $applied,
            'skipped' => $skipped,
            'dry_run' => $isDryRun,
        ]);

        return self::SUCCESS;
    }
}
