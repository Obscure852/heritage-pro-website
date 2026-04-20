<?php

namespace App\Services\Fee;

use App\Jobs\Fee\GenerateStudentInvoiceJob;
use App\Models\Fee\DiscountType;
use App\Models\Fee\FeeAuditLog;
use App\Models\Fee\FeeBalanceCarryover;
use App\Models\Fee\FeeStructure;
use App\Models\Fee\FeeType;
use App\Models\Fee\LateFeeCharge;
use App\Models\Fee\StudentDiscount;
use App\Models\Fee\StudentInvoice;
use App\Models\Fee\StudentInvoiceItem;
use App\Models\Student;
use App\Models\StudentTerm;
use App\Models\User;
use Illuminate\Bus\Batch;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Service for managing student invoices.
 *
 * Invoices are generated once per student per year (annual invoice).
 */
class InvoiceService
{
    protected FeeStructureService $feeStructureService;
    protected BalanceService $balanceService;

    public function __construct(FeeStructureService $feeStructureService, BalanceService $balanceService)
    {
        $this->feeStructureService = $feeStructureService;
        $this->balanceService = $balanceService;
    }

    /**
     * Get an invoice by ID with all related data.
     *
     * @throws ModelNotFoundException
     */
    public function getInvoice(int $invoiceId): StudentInvoice
    {
        return StudentInvoice::with([
            'student',
            'items.feeStructure.feeType',
            'payments',
            'createdBy',
        ])->findOrFail($invoiceId);
    }

    /**
     * Get existing active invoice for student/year combination.
     */
    public function getStudentInvoiceForYear(int $studentId, int $year): ?StudentInvoice
    {
        return StudentInvoice::forStudent($studentId)
            ->forYear($year)
            ->active()
            ->first();
    }

    /**
     * Generate an annual invoice for a student.
     *
     * @throws \Exception If invoice already exists or student has no grade
     */
    public function generateInvoice(
        Student $student,
        int $year,
        int $gradeId,
        User $user,
        ?string $dueDate = null,
        ?string $notes = null
    ): StudentInvoice {
        Log::info('generateInvoice called', [
            'student_id' => $student->id,
            'year' => $year,
            'grade_id' => $gradeId,
        ]);

        return DB::transaction(function () use ($student, $year, $gradeId, $user, $dueDate, $notes) {
            // Check if invoice already exists for student/year
            $existingInvoice = $this->getStudentInvoiceForYear($student->id, $year);
            if ($existingInvoice) {
                throw new \Exception("Invoice already exists for this student and year (Invoice #: {$existingInvoice->invoice_number})");
            }

            // Get fee structures for grade/year
            $feeStructures = $this->feeStructureService->getFeeStructuresForGrade($gradeId, $year);

            Log::info('Fee structures retrieved', [
                'student_id' => $student->id,
                'grade_id' => $gradeId,
                'year' => $year,
                'fee_structures_count' => $feeStructures->count(),
                'fee_structure_ids' => $feeStructures->pluck('id')->toArray(),
            ]);

            if ($feeStructures->isEmpty()) {
                Log::error('No fee structures found', [
                    'student_id' => $student->id,
                    'grade_id' => $gradeId,
                    'year' => $year,
                ]);
                throw new \Exception('No fee structures defined for this grade and year');
            }

            // Get student's active discounts for the year
            $discounts = StudentDiscount::forStudent($student->id)
                ->forYear($year)
                ->active()
                ->with('discountType')
                ->get();

            // Calculate amounts
            $subtotalAmount = '0.00';
            $totalDiscountAmount = '0.00';
            $invoiceItems = [];

            foreach ($feeStructures as $feeStructure) {
                $amount = (string) $feeStructure->amount;
                $subtotalAmount = bcadd($subtotalAmount, $amount, 2);

                // Calculate discount for this item
                $itemDiscount = $this->calculateItemDiscount($feeStructure, $discounts);
                $totalDiscountAmount = bcadd($totalDiscountAmount, $itemDiscount, 2);

                $netAmount = bcsub($amount, $itemDiscount, 2);

                $invoiceItems[] = [
                    'fee_structure_id' => $feeStructure->id,
                    'description' => $feeStructure->feeType->name,
                    'amount' => $amount,
                    'discount_amount' => $itemDiscount,
                    'net_amount' => $netAmount,
                ];
            }

            $totalAmount = bcsub($subtotalAmount, $totalDiscountAmount, 2);

            // Create invoice
            Log::info('Creating invoice record', [
                'student_id' => $student->id,
                'year' => $year,
                'subtotal' => $subtotalAmount,
                'discount' => $totalDiscountAmount,
                'total' => $totalAmount,
                'items_count' => count($invoiceItems),
            ]);

            $invoice = StudentInvoice::create([
                'invoice_number' => StudentInvoice::generateInvoiceNumber($year),
                'student_id' => $student->id,
                'year' => $year,
                'subtotal_amount' => $subtotalAmount,
                'discount_amount' => $totalDiscountAmount,
                'total_amount' => $totalAmount,
                'amount_paid' => '0.00',
                'balance' => $totalAmount,
                'status' => StudentInvoice::STATUS_ISSUED,
                'issued_at' => now(),
                'due_date' => $dueDate ?? now()->addDays(30)->toDateString(),
                'notes' => $notes,
                'created_by' => $user->id,
            ]);

            Log::info('Invoice record created', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'student_id' => $student->id,
            ]);

            // Create invoice items
            foreach ($invoiceItems as $item) {
                StudentInvoiceItem::create([
                    'student_invoice_id' => $invoice->id,
                    'fee_structure_id' => $item['fee_structure_id'],
                    'item_type' => StudentInvoiceItem::TYPE_FEE,
                    'description' => $item['description'],
                    'amount' => $item['amount'],
                    'discount_amount' => $item['discount_amount'],
                    'net_amount' => $item['net_amount'],
                ]);
            }

            Log::info('Invoice items created', [
                'invoice_id' => $invoice->id,
                'items_created' => count($invoiceItems),
            ]);

            // Check for ALL previous years' balances and add carryovers if applicable
            $carryoverNote = '';
            $carryovers = $this->checkAndAddAllCarryovers($student, $year, $invoice, $user);
            if (!empty($carryovers)) {
                $carryoverAmounts = [];
                foreach ($carryovers as $fromYear => $amount) {
                    $carryoverAmounts[] = "{$fromYear}: {$amount}";
                }
                $carryoverNote = " | Carryovers: " . implode(', ', $carryoverAmounts);
            }

            // Log to audit trail
            FeeAuditLog::log(
                $invoice,
                FeeAuditLog::ACTION_ISSUE,
                null,
                $invoice->toArray(),
                "Invoice generated for student ID: {$student->id}, Year: {$year}{$carryoverNote}"
            );

            return $invoice->load(['items.feeStructure.feeType', 'student']);
        });
    }

    /**
     * Check for ALL previous years' balances and add carryovers to invoice.
     * Uses locking to prevent duplicate carryover creation from concurrent requests.
     *
     * @return array<int, string> Array of carryovers: [year => amount]
     */
    private function checkAndAddAllCarryovers(Student $student, int $year, StudentInvoice $invoice, User $user): array
    {
        // Lock student record to serialize carryover operations and prevent duplicates
        $student = Student::lockForUpdate()->find($student->id);

        $carryovers = [];

        // Get configurable lookback years from settings (default: 3)
        $lookbackYears = (int) settings('fee.carryover_lookback_years', 3);

        // Safety: clamp to actual data availability
        $earliestYear = \App\Models\Term::min('year') ?? $year;
        $startYear = max($year - $lookbackYears, (int) $earliestYear);

        // Loop through years with actual data
        for ($fromYear = $startYear; $fromYear < $year; $fromYear++) {
            // Skip if carryover already exists for this specific year range
            if (FeeBalanceCarryover::existsForStudentYearRange($student->id, $fromYear, $year)) {
                continue;
            }

            // Get balance for that specific year
            $balance = $this->balanceService->getStudentBalanceForYear($student->id, $fromYear);

            // If positive balance, add carryover
            if (bccomp($balance, '0.00', 2) > 0) {
                // Add carryover line item to invoice
                $this->addCarryoverToInvoice($invoice, $balance, $fromYear);

                // Create carryover record via BalanceService
                $this->balanceService->carryForwardBalance($student, $fromYear, $year, $user);

                $carryovers[$fromYear] = $balance;
            }
        }

        return $carryovers;
    }

    /**
     * Add a carryover line item to an invoice.
     */
    public function addCarryoverToInvoice(StudentInvoice $invoice, string $carryoverAmount, int $fromYear): void
    {
        // Create carryover line item with item_type and source_year
        StudentInvoiceItem::create([
            'student_invoice_id' => $invoice->id,
            'fee_structure_id' => null, // No fee structure for carryover
            'item_type' => StudentInvoiceItem::TYPE_CARRYOVER,
            'source_year' => $fromYear,
            'description' => "Balance carried forward from {$fromYear}",
            'amount' => $carryoverAmount,
            'discount_amount' => '0.00',
            'net_amount' => $carryoverAmount,
        ]);

        // Update invoice totals
        $invoice->subtotal_amount = bcadd((string) $invoice->subtotal_amount, $carryoverAmount, 2);
        $invoice->total_amount = bcadd((string) $invoice->total_amount, $carryoverAmount, 2);
        $invoice->balance = bcadd((string) $invoice->balance, $carryoverAmount, 2);
        $invoice->save();
    }

    /**
     * Calculate discount amount for a fee structure item.
     */
    private function calculateItemDiscount(FeeStructure $feeStructure, Collection $discounts): string
    {
        if ($discounts->isEmpty()) {
            return '0.00';
        }

        $amount = (string) $feeStructure->amount;
        $totalDiscount = '0.00';
        $feeCategory = $feeStructure->feeType->category;

        foreach ($discounts as $studentDiscount) {
            $discountType = $studentDiscount->discountType;

            // Check if discount applies to this fee type
            $appliesToThisFee = false;

            if ($discountType->applies_to === DiscountType::APPLIES_TO_ALL) {
                // Discount applies to all fees
                $appliesToThisFee = true;
            } elseif ($discountType->applies_to === DiscountType::APPLIES_TO_TUITION_ONLY) {
                // Discount only applies to tuition category fees
                $appliesToThisFee = ($feeCategory === FeeType::CATEGORY_TUITION);
            }

            if ($appliesToThisFee) {
                // Calculate discount: amount * (percentage / 100)
                $percentage = (string) $discountType->percentage;
                $discountAmount = bcmul($amount, bcdiv($percentage, '100', 4), 2);
                $totalDiscount = bcadd($totalDiscount, $discountAmount, 2);
            }
        }

        // Ensure discount doesn't exceed the item amount
        if (bccomp($totalDiscount, $amount, 2) > 0) {
            return $amount;
        }

        return $totalDiscount;
    }

    /**
     * Generate invoices for all students in a grade for a year.
     *
     * @return array{generated: int, skipped: int, errors: int, messages: array}
     */
    public function generateBulkInvoices(
        int $gradeId,
        int $year,
        User $user,
        ?string $dueDate = null
    ): array {
        Log::info('=== BULK INVOICE GENERATION STARTED ===', [
            'grade_id' => $gradeId,
            'year' => $year,
            'user_id' => $user->id,
            'due_date' => $dueDate,
        ]);

        $results = [
            'generated' => 0,
            'skipped' => 0,
            'errors' => 0,
            'messages' => [],
        ];

        // Get all students enrolled in the grade for the current academic year
        // We look for students in the most recent term of the year
        $studentTerms = StudentTerm::where('grade_id', $gradeId)
            ->whereHas('term', fn($q) => $q->where('year', $year))
            ->where('status', 'Current')
            ->with('student')
            ->get()
            ->unique('student_id'); // Ensure unique students

        Log::info('Students query executed', [
            'grade_id' => $gradeId,
            'year' => $year,
            'student_terms_count' => $studentTerms->count(),
            'student_ids' => $studentTerms->pluck('student_id')->toArray(),
        ]);

        if ($studentTerms->isEmpty()) {
            Log::warning('No students found for bulk invoice generation', [
                'grade_id' => $gradeId,
                'year' => $year,
                'query_conditions' => [
                    'status' => 'Current',
                    'term_year' => $year,
                ],
            ]);
        }

        foreach ($studentTerms as $studentTerm) {
            $student = $studentTerm->student;

            if (!$student) {
                Log::warning('Student record not found', ['student_term_id' => $studentTerm->id]);
                $results['errors']++;
                $results['messages'][] = "Student record not found for student_term ID: {$studentTerm->id}";
                continue;
            }

            Log::info('Processing student', [
                'student_id' => $student->id,
                'student_name' => $student->full_name,
            ]);

            try {
                // Check if invoice already exists
                $existingInvoice = $this->getStudentInvoiceForYear($student->id, $year);
                if ($existingInvoice) {
                    Log::info('Invoice already exists, skipping', [
                        'student_id' => $student->id,
                        'invoice_number' => $existingInvoice->invoice_number,
                    ]);
                    $results['skipped']++;
                    $results['messages'][] = "Skipped {$student->full_name}: Invoice already exists ({$existingInvoice->invoice_number})";
                    continue;
                }

                // Generate invoice
                Log::info('Generating invoice for student', ['student_id' => $student->id]);
                $this->generateInvoice($student, $year, $gradeId, $user, $dueDate);
                Log::info('Invoice generated successfully', ['student_id' => $student->id]);
                $results['generated']++;
            } catch (\Exception $e) {
                Log::error('Error generating invoice', [
                    'student_id' => $student->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $results['errors']++;
                $results['messages'][] = "Error for {$student->full_name}: {$e->getMessage()}";
            }
        }

        Log::info('=== BULK INVOICE GENERATION COMPLETED ===', $results);

        return $results;
    }

    /**
     * Recalculate an invoice with current discounts.
     * This re-applies any discounts that have been added/removed since invoice generation.
     *
     * @throws \Exception If invoice is cancelled or fully paid
     */
    public function recalculateInvoice(StudentInvoice $invoice, User $user): StudentInvoice
    {
        return DB::transaction(function () use ($invoice, $user) {
            // Check if invoice can be recalculated
            if ($invoice->isCancelled()) {
                throw new \Exception('Cannot recalculate a cancelled invoice.');
            }

            if ($invoice->isPaid()) {
                throw new \Exception('Cannot recalculate a fully paid invoice.');
            }

            $oldValues = $invoice->toArray();
            $amountPaid = (string) $invoice->amount_paid;

            // Get student's current active discounts for the year
            $discounts = StudentDiscount::forStudent($invoice->student_id)
                ->forYear($invoice->year)
                ->active()
                ->with('discountType')
                ->get();

            // Recalculate each invoice item (excluding carryover items)
            $subtotalAmount = '0.00';
            $totalDiscountAmount = '0.00';

            foreach ($invoice->items as $item) {
                // Skip carryover items - they should not be recalculated
                if ($item->item_type === StudentInvoiceItem::TYPE_CARRYOVER || !$item->fee_structure_id) {
                    $subtotalAmount = bcadd($subtotalAmount, (string) $item->amount, 2);
                    continue;
                }

                $feeStructure = $item->feeStructure;
                if (!$feeStructure) {
                    continue;
                }

                $amount = (string) $feeStructure->amount;
                $subtotalAmount = bcadd($subtotalAmount, $amount, 2);

                // Calculate new discount for this item
                $itemDiscount = $this->calculateItemDiscount($feeStructure, $discounts);
                $totalDiscountAmount = bcadd($totalDiscountAmount, $itemDiscount, 2);

                $netAmount = bcsub($amount, $itemDiscount, 2);

                // Update item
                $item->update([
                    'amount' => $amount,
                    'discount_amount' => $itemDiscount,
                    'net_amount' => $netAmount,
                ]);
            }

            $totalAmount = bcsub($subtotalAmount, $totalDiscountAmount, 2);
            $newBalance = bcsub($totalAmount, $amountPaid, 2);

            // Determine new status based on balance
            $newStatus = $invoice->status;
            if (bccomp($newBalance, '0.00', 2) <= 0) {
                $newStatus = StudentInvoice::STATUS_PAID;
            } elseif (bccomp($amountPaid, '0.00', 2) > 0) {
                $newStatus = StudentInvoice::STATUS_PARTIAL;
            } else {
                $newStatus = StudentInvoice::STATUS_ISSUED;
            }

            // Update invoice totals
            $invoice->update([
                'subtotal_amount' => $subtotalAmount,
                'discount_amount' => $totalDiscountAmount,
                'total_amount' => $totalAmount,
                'balance' => $newBalance,
                'status' => $newStatus,
            ]);

            // Log to audit trail
            FeeAuditLog::log(
                $invoice,
                FeeAuditLog::ACTION_UPDATE,
                $oldValues,
                $invoice->fresh()->toArray(),
                "Invoice recalculated with current discounts. Old discount: {$oldValues['discount_amount']}, New discount: {$totalDiscountAmount}"
            );

            return $invoice->fresh(['items.feeStructure.feeType', 'student']);
        });
    }

    /**
     * Cancel an invoice.
     *
     * @throws \Exception If invoice is already cancelled or has payments
     */
    public function cancelInvoice(StudentInvoice $invoice, User $user, string $reason): bool
    {
        return DB::transaction(function () use ($invoice, $user, $reason) {
            // Check if already cancelled
            if ($invoice->isCancelled()) {
                throw new \Exception('Invoice is already cancelled');
            }

            // Check if invoice has any payments
            if ($invoice->payments()->exists()) {
                throw new \Exception('Cannot cancel invoice with payments. Void payments first.');
            }

            $oldValues = $invoice->toArray();

            // Update status to cancelled
            $invoice->update([
                'status' => StudentInvoice::STATUS_CANCELLED,
            ]);

            // Log to audit trail
            FeeAuditLog::log(
                $invoice,
                FeeAuditLog::ACTION_CANCEL,
                $oldValues,
                $invoice->fresh()->toArray(),
                "Invoice cancelled. Reason: {$reason}"
            );

            return true;
        });
    }

    /**
     * Generate invoices for all students in a grade asynchronously using job batching.
     *
     * @return array{batch_id: string, batch_key: string, total_students: int}
     */
    public function generateBulkInvoicesAsync(
        int $gradeId,
        int $year,
        User $user,
        ?string $dueDate = null
    ): array {
        $batchKey = Str::uuid()->toString();

        Log::info('=== ASYNC BULK INVOICE GENERATION INITIATED ===', [
            'batch_key' => $batchKey,
            'grade_id' => $gradeId,
            'year' => $year,
            'user_id' => $user->id,
        ]);

        // Get all students enrolled in the grade for the year
        $studentTerms = StudentTerm::where('grade_id', $gradeId)
            ->whereHas('term', fn($q) => $q->where('year', $year))
            ->where('status', 'Current')
            ->with('student')
            ->get()
            ->unique('student_id');

        if ($studentTerms->isEmpty()) {
            Log::warning('No students found for async bulk invoice generation', [
                'grade_id' => $gradeId,
                'year' => $year,
            ]);

            return [
                'batch_id' => null,
                'batch_key' => $batchKey,
                'total_students' => 0,
            ];
        }

        // Initialize progress tracking
        Cache::put("bulk_invoice_progress:{$batchKey}", [
            'generated' => 0,
            'skipped' => 0,
            'errors' => 0,
            'messages' => [],
            'total' => $studentTerms->count(),
            'started_at' => now()->toDateTimeString(),
            'grade_id' => $gradeId,
            'year' => $year,
            'user_id' => $user->id,
        ], now()->addHours(2));

        // Create jobs for each student
        $jobs = [];
        foreach ($studentTerms as $studentTerm) {
            if ($studentTerm->student) {
                $jobs[] = new GenerateStudentInvoiceJob(
                    $studentTerm->student_id,
                    $gradeId,
                    $year,
                    $user->id,
                    $dueDate,
                    $batchKey
                );
            }
        }

        // Dispatch batch
        $batch = Bus::batch($jobs)
            ->name("Bulk Invoice Generation - Grade {$gradeId} - Year {$year}")
            ->allowFailures()
            ->finally(function (Batch $batch) use ($batchKey, $user) {
                $this->onBatchCompleted($batch, $batchKey, $user);
            })
            ->dispatch();

        // Store batch ID for status checking
        Cache::put("bulk_invoice_batch:{$batchKey}", $batch->id, now()->addHours(2));

        Log::info('Bulk invoice batch dispatched', [
            'batch_id' => $batch->id,
            'batch_key' => $batchKey,
            'total_jobs' => count($jobs),
        ]);

        return [
            'batch_id' => $batch->id,
            'batch_key' => $batchKey,
            'total_students' => count($jobs),
        ];
    }

    /**
     * Callback when batch completes.
     */
    private function onBatchCompleted(Batch $batch, string $batchKey, User $user): void
    {
        $progress = Cache::get("bulk_invoice_progress:{$batchKey}", []);
        $progress['completed_at'] = now()->toDateTimeString();
        $progress['batch_finished'] = true;

        Cache::put("bulk_invoice_progress:{$batchKey}", $progress, now()->addHours(2));

        Log::info('=== ASYNC BULK INVOICE GENERATION COMPLETED ===', [
            'batch_key' => $batchKey,
            'batch_id' => $batch->id,
            'generated' => $progress['generated'] ?? 0,
            'skipped' => $progress['skipped'] ?? 0,
            'errors' => $progress['errors'] ?? 0,
        ]);

        // TODO: Send notification to user (can be implemented with Laravel Notifications)
    }

    /**
     * Get the progress of a bulk invoice generation batch.
     */
    public function getBulkInvoiceProgress(string $batchKey): ?array
    {
        $progress = Cache::get("bulk_invoice_progress:{$batchKey}");

        if (!$progress) {
            return null;
        }

        // Get batch status from Laravel's batch system
        $batchId = Cache::get("bulk_invoice_batch:{$batchKey}");
        if ($batchId) {
            $batch = Bus::findBatch($batchId);
            if ($batch) {
                $progress['batch_progress'] = $batch->progress();
                $progress['batch_pending'] = $batch->pendingJobs;
                $progress['batch_failed'] = $batch->failedJobs;
                $progress['batch_cancelled'] = $batch->cancelled();
                $progress['batch_finished'] = $batch->finished();
            }
        }

        return $progress;
    }

    /**
     * Cancel a running bulk invoice generation batch.
     */
    public function cancelBulkInvoiceBatch(string $batchKey): bool
    {
        $batchId = Cache::get("bulk_invoice_batch:{$batchKey}");

        if (!$batchId) {
            return false;
        }

        $batch = Bus::findBatch($batchId);

        if ($batch && !$batch->finished()) {
            $batch->cancel();

            $progress = Cache::get("bulk_invoice_progress:{$batchKey}", []);
            $progress['cancelled_at'] = now()->toDateTimeString();
            $progress['batch_cancelled'] = true;
            Cache::put("bulk_invoice_progress:{$batchKey}", $progress, now()->addHours(2));

            Log::info('Bulk invoice batch cancelled', [
                'batch_key' => $batchKey,
                'batch_id' => $batchId,
            ]);

            return true;
        }

        return false;
    }

    /**
     * Apply a late fee to an invoice.
     */
    public function applyLateFee(StudentInvoice $invoice, string $amount, string $feeType, int $daysOverdue): LateFeeCharge
    {
        return DB::transaction(function () use ($invoice, $amount, $feeType, $daysOverdue) {
            // Create the late fee charge record
            $lateFee = LateFeeCharge::create([
                'student_invoice_id' => $invoice->id,
                'amount' => $amount,
                'fee_type' => $feeType,
                'applied_date' => now()->toDateString(),
                'days_overdue' => $daysOverdue,
            ]);

            // Add late fee as an invoice item
            StudentInvoiceItem::create([
                'student_invoice_id' => $invoice->id,
                'fee_structure_id' => null,
                'item_type' => 'late_fee',
                'description' => "Late fee ({$daysOverdue} days overdue)",
                'amount' => $amount,
                'discount_amount' => '0.00',
                'net_amount' => $amount,
            ]);

            // Update invoice totals
            $invoice->total_amount = bcadd((string) $invoice->total_amount, $amount, 2);
            $invoice->balance = bcadd((string) $invoice->balance, $amount, 2);
            $invoice->save();

            // Log to audit trail
            FeeAuditLog::log(
                $invoice,
                FeeAuditLog::ACTION_UPDATE,
                null,
                ['late_fee_amount' => $amount, 'days_overdue' => $daysOverdue],
                "Late fee of {$amount} applied ({$daysOverdue} days overdue)"
            );

            Log::info('Late fee applied to invoice', [
                'invoice_id' => $invoice->id,
                'late_fee_id' => $lateFee->id,
                'amount' => $amount,
                'days_overdue' => $daysOverdue,
            ]);

            return $lateFee;
        });
    }

    /**
     * Get late fee settings from configuration.
     */
    public function getLateFeeSettings(): array
    {
        return [
            'enable_late_fees' => (bool) settings('fee.enable_late_fees', false),
            'late_fee_grace_period' => (int) settings('fee.late_fee_grace_period', 7),
            'late_fee_type' => settings('fee.late_fee_type', 'fixed'),
            'late_fee_amount' => settings('fee.late_fee_amount', '50.00'),
            'late_fee_max_applications' => (int) settings('fee.late_fee_max_applications', 0), // 0 = unlimited
        ];
    }

    /**
     * Calculate late fee amount based on settings and invoice balance.
     */
    public function calculateLateFeeAmount(StudentInvoice $invoice): string
    {
        $settings = $this->getLateFeeSettings();
        $feeType = $settings['late_fee_type'];
        $feeAmount = $settings['late_fee_amount'];

        if ($feeType === LateFeeCharge::TYPE_PERCENTAGE) {
            return bcmul((string) $invoice->balance, bcdiv($feeAmount, '100', 4), 2);
        }

        return $feeAmount;
    }

    /**
     * Check if invoice is eligible for late fee application.
     */
    public function canApplyLateFee(StudentInvoice $invoice): bool
    {
        // Must have outstanding balance
        if (bccomp((string) $invoice->balance, '0', 2) <= 0) {
            return false;
        }

        // Must be overdue
        if (!$invoice->isOverdue()) {
            return false;
        }

        // Check if invoice is past grace period
        $settings = $this->getLateFeeSettings();
        $gracePeriod = $settings['late_fee_grace_period'];
        $daysOverdue = now()->diffInDays($invoice->due_date);

        if ($daysOverdue < $gracePeriod) {
            return false;
        }

        // Check max applications if set
        $maxApplications = $settings['late_fee_max_applications'];
        if ($maxApplications > 0) {
            $currentApplications = $invoice->lateFeeCharges()->where('waived', false)->count();
            if ($currentApplications >= $maxApplications) {
                return false;
            }
        }

        // Check if late fee already applied today
        if ($invoice->lateFeeCharges()->whereDate('applied_date', today())->exists()) {
            return false;
        }

        return true;
    }
}
