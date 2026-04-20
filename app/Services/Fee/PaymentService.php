<?php

namespace App\Services\Fee;

use App\Jobs\Fee\SendPaymentReminderJob;
use App\Models\Fee\FeeAuditLog;
use App\Models\Fee\FeePayment;
use App\Models\Fee\PaymentPlan;
use App\Models\Fee\PaymentPlanInstallment;
use App\Models\Fee\StudentInvoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

/**
 * Service for managing fee payments.
 *
 * Payments are recorded against annual invoices.
 */
class PaymentService
{
    /**
     * Get a payment by ID with all related data.
     *
     * @throws ModelNotFoundException
     */
    public function getPayment(int $paymentId): FeePayment
    {
        return FeePayment::with([
            'invoice.student',
            'receivedBy',
            'voidedBy',
        ])->findOrFail($paymentId);
    }

    /**
     * Get all payments for a student, optionally filtered by year.
     */
    public function getStudentPayments(int $studentId, ?int $year = null): Collection
    {
        $query = FeePayment::forStudent($studentId)
            ->notVoided();

        if ($year) {
            $query->forYear($year);
        }

        return $query->orderBy('payment_date', 'desc')
            ->with(['invoice', 'receivedBy'])
            ->get();
    }

    /**
     * Record a payment against an invoice.
     *
     * @throws \Exception If invoice is cancelled or already paid
     */
    public function recordPayment(StudentInvoice $invoice, User $user, array $data): FeePayment
    {
        return DB::transaction(function () use ($invoice, $user, $data) {
            // Lock the invoice to prevent race conditions on concurrent payments
            $invoice = StudentInvoice::lockForUpdate()->find($invoice->id);

            // Validate invoice can accept payment
            if ($invoice->isCancelled()) {
                throw new \Exception('Cannot record payment for a cancelled invoice');
            }

            if ($invoice->isPaid()) {
                throw new \Exception('Invoice is already fully paid');
            }

            // Validate amount doesn't exceed balance
            $amount = (string) $data['amount'];
            $balance = (string) $invoice->balance;

            if (bccomp($amount, $balance, 2) > 0) {
                throw new \Exception('Payment amount cannot exceed invoice balance');
            }

            // Generate receipt number using invoice year
            $year = $invoice->year;
            $receiptNumber = FeePayment::generateReceiptNumber($year);

            // Determine installment allocation
            $installmentId = $data['installment_id'] ?? null;
            $installment = null;

            // If no specific installment provided, check for active payment plan
            if (!$installmentId && $invoice->hasActivePaymentPlan()) {
                $plan = $invoice->activePaymentPlan;
                if ($plan) {
                    $installment = $plan->getNextDueInstallment();
                    $installmentId = $installment?->id;
                }
            } elseif ($installmentId) {
                $installment = PaymentPlanInstallment::find($installmentId);
            }

            // Create payment record
            $payment = FeePayment::create([
                'receipt_number' => $receiptNumber,
                'student_invoice_id' => $invoice->id,
                'payment_plan_installment_id' => $installmentId,
                'student_id' => $invoice->student_id,
                'year' => $year,
                'amount' => $amount,
                'payment_method' => $data['payment_method'],
                'payment_date' => $data['payment_date'] ?? now()->toDateString(),
                'reference_number' => $data['reference_number'] ?? null,
                'cheque_number' => $data['cheque_number'] ?? null,
                'bank_name' => $data['bank_name'] ?? null,
                'notes' => $data['notes'] ?? null,
                'received_by' => $user->id,
                'voided' => false,
            ]);

            // Update installment if applicable (lock to prevent race conditions)
            if ($installment) {
                $installment = PaymentPlanInstallment::lockForUpdate()->find($installment->id);
                $installment->recordPayment($amount);
            }

            // Update invoice amount_paid and recalculate balance
            $newAmountPaid = bcadd((string) $invoice->amount_paid, $amount, 2);
            $invoice->amount_paid = $newAmountPaid;
            $invoice->recalculateBalance();

            // Log to audit trail
            FeeAuditLog::log(
                $payment,
                FeeAuditLog::ACTION_CREATE,
                null,
                $payment->toArray(),
                "Payment recorded: Receipt #{$receiptNumber}, Amount: {$amount}, Invoice #{$invoice->invoice_number}"
            );

            // Send payment confirmation if enabled
            if (settings('fee.notify_on_payment', false)) {
                $this->dispatchPaymentConfirmation($payment);
            }

            return $payment->load(['invoice.student', 'receivedBy', 'installment']);
        });
    }

    /**
     * Dispatch payment confirmation notification.
     */
    protected function dispatchPaymentConfirmation(FeePayment $payment): void
    {
        try {
            SendPaymentReminderJob::sendConfirmation($payment);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to send payment confirmation', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Void a payment and restore the invoice balance.
     *
     * @throws \Exception If payment is already voided
     */
    public function voidPayment(FeePayment $payment, User $user, string $reason): bool
    {
        return DB::transaction(function () use ($payment, $user, $reason) {
            // Lock the payment and invoice to prevent race conditions
            $payment = FeePayment::lockForUpdate()->find($payment->id);
            $invoice = StudentInvoice::lockForUpdate()->find($payment->student_invoice_id);

            // Validate payment is not already voided
            if ($payment->isVoided()) {
                throw new \Exception('Payment has already been voided');
            }

            // Store old values for audit
            $oldValues = $payment->toArray();
            $paymentAmount = (string) $payment->amount;

            // Reverse installment allocation if applicable
            if ($payment->installment) {
                $payment->installment->reversePayment($paymentAmount);
            }

            // Void the payment
            $payment->void($user, $reason);

            // Restore invoice balance (using the already-locked invoice)
            if ($invoice) {
                $newAmountPaid = bcsub((string) $invoice->amount_paid, $paymentAmount, 2);
                $invoice->amount_paid = $newAmountPaid;
                $invoice->recalculateBalance();
            }

            // Log to audit trail
            FeeAuditLog::log(
                $payment,
                FeeAuditLog::ACTION_VOID,
                $oldValues,
                $payment->fresh()->toArray(),
                "Payment voided. Reason: {$reason}"
            );

            return true;
        });
    }

    /**
     * Get payments for a specific date range.
     */
    public function getPaymentsForDateRange(string $startDate, string $endDate, ?int $year = null): Collection
    {
        $query = FeePayment::notVoided()
            ->forDateRange($startDate, $endDate)
            ->with(['invoice.student', 'receivedBy']);

        if ($year !== null) {
            $query->forYear($year);
        }

        return $query->orderBy('payment_date', 'desc')->get();
    }
}
