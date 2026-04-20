<?php

namespace App\Jobs\Fee;

use App\Models\Fee\FeeAuditLog;
use App\Models\Student;
use App\Models\User;
use App\Services\Fee\InvoiceService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Job to generate an invoice for a single student.
 *
 * Used as part of bulk invoice generation batches.
 */
class GenerateStudentInvoiceJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $studentId;
    public int $gradeId;
    public int $year;
    public int $userId;
    public ?string $dueDate;
    public string $batchKey;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 60;

    /**
     * Calculate the number of seconds to wait before retrying.
     */
    public function backoff(): array
    {
        return [5, 15, 30];
    }

    public function __construct(
        int $studentId,
        int $gradeId,
        int $year,
        int $userId,
        ?string $dueDate,
        string $batchKey
    ) {
        $this->studentId = $studentId;
        $this->gradeId = $gradeId;
        $this->year = $year;
        $this->userId = $userId;
        $this->dueDate = $dueDate;
        $this->batchKey = $batchKey;
    }

    public function handle(InvoiceService $invoiceService): void
    {
        // Check if batch was cancelled
        if ($this->batch()?->cancelled()) {
            return;
        }

        // Use distributed lock to prevent duplicate invoice generation
        $lockKey = "invoice_generation:{$this->studentId}:{$this->year}";
        $lock = Cache::lock($lockKey, 30);

        if (!$lock->get()) {
            Log::warning('Could not acquire lock for invoice generation', [
                'student_id' => $this->studentId,
                'year' => $this->year,
            ]);
            // Release to retry
            $this->release(5);
            return;
        }

        try {
            $student = Student::find($this->studentId);
            $user = User::find($this->userId);

            if (!$student || !$user) {
                $this->updateBatchProgress('error', "Student or user not found");
                return;
            }

            // Check if invoice already exists (double-check after lock)
            $existingInvoice = $invoiceService->getStudentInvoiceForYear($this->studentId, $this->year);
            if ($existingInvoice) {
                $this->updateBatchProgress('skipped', "Invoice already exists: {$existingInvoice->invoice_number}");
                return;
            }

            // Generate invoice
            $invoice = $invoiceService->generateInvoice(
                $student,
                $this->year,
                $this->gradeId,
                $user,
                $this->dueDate
            );

            $this->updateBatchProgress('generated', $invoice->invoice_number);

            Log::info('Invoice generated via queue', [
                'student_id' => $this->studentId,
                'invoice_number' => $invoice->invoice_number,
                'batch_key' => $this->batchKey,
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating invoice in queue', [
                'student_id' => $this->studentId,
                'error' => $e->getMessage(),
                'batch_key' => $this->batchKey,
            ]);

            $this->updateBatchProgress('error', $e->getMessage());

            // Don't throw to prevent batch failure, just log the error
        } finally {
            $lock->release();
        }
    }

    /**
     * Update batch progress in cache.
     */
    private function updateBatchProgress(string $status, string $message): void
    {
        $progressKey = "bulk_invoice_progress:{$this->batchKey}";
        $progress = Cache::get($progressKey, [
            'generated' => 0,
            'skipped' => 0,
            'errors' => 0,
            'messages' => [],
        ]);

        $progress[$status === 'generated' ? 'generated' : ($status === 'skipped' ? 'skipped' : 'errors')]++;

        // Keep only the last 50 messages to prevent memory issues
        if (count($progress['messages']) < 50) {
            $student = Student::find($this->studentId);
            $studentName = $student ? $student->full_name : "Student #{$this->studentId}";
            $progress['messages'][] = [
                'status' => $status,
                'student' => $studentName,
                'message' => $message,
                'time' => now()->toDateTimeString(),
            ];
        }

        Cache::put($progressKey, $progress, now()->addHours(2));
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Invoice generation job failed permanently', [
            'student_id' => $this->studentId,
            'year' => $this->year,
            'error' => $exception->getMessage(),
        ]);

        $this->updateBatchProgress('error', "Permanent failure: {$exception->getMessage()}");
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'invoice-generation',
            "student:{$this->studentId}",
            "year:{$this->year}",
            "batch:{$this->batchKey}",
        ];
    }
}
