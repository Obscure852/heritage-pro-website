<?php

namespace App\Services\Fee;

use App\Models\Fee\FeeAuditLog;
use App\Models\Fee\FeePayment;
use App\Models\Fee\FeeRefund;
use App\Models\Fee\StudentInvoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

/**
 * Service for managing fee refunds and credit notes.
 *
 * Handles refund requests, approvals, and processing.
 */
class RefundService
{
    /**
     * Get a refund by ID with all related data.
     *
     * @throws ModelNotFoundException
     */
    public function getRefund(int $refundId): FeeRefund
    {
        return FeeRefund::with([
            'invoice.student',
            'payment',
            'requestedBy',
            'approvedBy',
            'processedBy',
        ])->findOrFail($refundId);
    }

    /**
     * Get all refunds for a student, optionally filtered by year.
     */
    public function getStudentRefunds(int $studentId, ?int $year = null): Collection
    {
        $query = FeeRefund::forStudent($studentId);

        if ($year) {
            $query->forYear($year);
        }

        return $query->orderBy('created_at', 'desc')
            ->with(['invoice', 'payment', 'requestedBy'])
            ->get();
    }

    /**
     * Get pending refunds awaiting approval.
     */
    public function getPendingRefunds(?int $year = null): Collection
    {
        $query = FeeRefund::pending()
            ->with(['invoice.student', 'payment', 'requestedBy']);

        if ($year !== null) {
            $query->forYear($year);
        }

        return $query->orderBy('created_at', 'asc')->get();
    }

    /**
     * Get approved refunds awaiting processing.
     */
    public function getApprovedRefunds(?int $year = null): Collection
    {
        $query = FeeRefund::approved()
            ->with(['invoice.student', 'payment', 'approvedBy']);

        if ($year !== null) {
            $query->forYear($year);
        }

        return $query->orderBy('approved_at', 'asc')->get();
    }

    /**
     * Request a refund for a payment.
     *
     * @throws \Exception If payment cannot be refunded
     */
    public function requestRefund(FeePayment $payment, User $user, array $data): FeeRefund
    {
        return DB::transaction(function () use ($payment, $user, $data) {
            // Validate payment can be refunded
            if (!$payment->canBeRefunded()) {
                throw new \Exception('This payment cannot be refunded');
            }

            $amount = (string) $data['amount'];
            $refundableAmount = $payment->refundable_amount;

            // Validate amount doesn't exceed refundable amount
            if (bccomp($amount, $refundableAmount, 2) > 0) {
                throw new \Exception("Refund amount cannot exceed refundable amount of {$refundableAmount}");
            }

            // Determine refund type
            $refundType = FeeRefund::TYPE_PARTIAL;
            if (bccomp($amount, $payment->amount, 2) === 0) {
                $refundType = FeeRefund::TYPE_FULL;
            }

            // Override with credit note type if specified
            if (($data['refund_method'] ?? '') === FeeRefund::METHOD_CREDIT_TO_ACCOUNT) {
                $refundType = FeeRefund::TYPE_CREDIT_NOTE;
            }

            $invoice = $payment->invoice;
            $year = $invoice->year;
            $refundNumber = FeeRefund::generateRefundNumber($year);

            // Create refund record
            $refund = FeeRefund::create([
                'refund_number' => $refundNumber,
                'student_invoice_id' => $invoice->id,
                'student_id' => $invoice->student_id,
                'fee_payment_id' => $payment->id,
                'year' => $year,
                'amount' => $amount,
                'refund_type' => $refundType,
                'refund_method' => $data['refund_method'],
                'refund_date' => $data['refund_date'] ?? now()->toDateString(),
                'reference_number' => $data['reference_number'] ?? null,
                'reason' => $data['reason'],
                'status' => FeeRefund::STATUS_PENDING,
                'requested_by' => $user->id,
                'notes' => $data['notes'] ?? null,
            ]);

            // Log to audit trail
            FeeAuditLog::log(
                $refund,
                FeeAuditLog::ACTION_CREATE,
                null,
                $refund->toArray(),
                "Refund requested: #{$refundNumber}, Amount: {$amount}, Payment #{$payment->receipt_number}"
            );

            return $refund->load(['invoice.student', 'payment', 'requestedBy']);
        });
    }

    /**
     * Request a credit note for an invoice (not linked to a specific payment).
     *
     * @throws \Exception If invoice cannot have credit note
     */
    public function requestCreditNote(StudentInvoice $invoice, User $user, array $data): FeeRefund
    {
        return DB::transaction(function () use ($invoice, $user, $data) {
            // Validate invoice is not cancelled
            if ($invoice->isCancelled()) {
                throw new \Exception('Cannot issue credit note for a cancelled invoice');
            }

            $amount = (string) $data['amount'];
            $year = $invoice->year;
            $refundNumber = FeeRefund::generateRefundNumber($year);

            // Create credit note record
            $refund = FeeRefund::create([
                'refund_number' => $refundNumber,
                'student_invoice_id' => $invoice->id,
                'student_id' => $invoice->student_id,
                'fee_payment_id' => null,
                'year' => $year,
                'amount' => $amount,
                'refund_type' => FeeRefund::TYPE_CREDIT_NOTE,
                'refund_method' => FeeRefund::METHOD_CREDIT_TO_ACCOUNT,
                'refund_date' => $data['refund_date'] ?? now()->toDateString(),
                'reference_number' => $data['reference_number'] ?? null,
                'reason' => $data['reason'],
                'status' => FeeRefund::STATUS_PENDING,
                'requested_by' => $user->id,
                'notes' => $data['notes'] ?? null,
            ]);

            // Log to audit trail
            FeeAuditLog::log(
                $refund,
                FeeAuditLog::ACTION_CREATE,
                null,
                $refund->toArray(),
                "Credit note requested: #{$refundNumber}, Amount: {$amount}, Invoice #{$invoice->invoice_number}"
            );

            return $refund->load(['invoice.student', 'requestedBy']);
        });
    }

    /**
     * Approve a pending refund.
     *
     * @throws \Exception If refund is not pending
     */
    public function approveRefund(FeeRefund $refund, User $user): FeeRefund
    {
        return DB::transaction(function () use ($refund, $user) {
            if (!$refund->isPending()) {
                throw new \Exception('Only pending refunds can be approved');
            }

            $oldValues = $refund->toArray();

            $refund->update([
                'status' => FeeRefund::STATUS_APPROVED,
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            // Log to audit trail
            FeeAuditLog::log(
                $refund,
                FeeAuditLog::ACTION_UPDATE,
                $oldValues,
                $refund->fresh()->toArray(),
                "Refund approved: #{$refund->refund_number}"
            );

            return $refund->fresh(['invoice.student', 'payment', 'approvedBy']);
        });
    }

    /**
     * Reject a pending refund.
     *
     * @throws \Exception If refund is not pending
     */
    public function rejectRefund(FeeRefund $refund, User $user, string $reason): FeeRefund
    {
        return DB::transaction(function () use ($refund, $user, $reason) {
            if (!$refund->isPending()) {
                throw new \Exception('Only pending refunds can be rejected');
            }

            $oldValues = $refund->toArray();

            $refund->update([
                'status' => FeeRefund::STATUS_REJECTED,
                'rejection_reason' => $reason,
            ]);

            // Log to audit trail
            FeeAuditLog::log(
                $refund,
                FeeAuditLog::ACTION_UPDATE,
                $oldValues,
                $refund->fresh()->toArray(),
                "Refund rejected: #{$refund->refund_number}. Reason: {$reason}"
            );

            return $refund->fresh(['invoice.student', 'payment']);
        });
    }

    /**
     * Process an approved refund.
     *
     * @throws \Exception If refund is not approved
     */
    public function processRefund(FeeRefund $refund, User $user, ?array $data = []): FeeRefund
    {
        return DB::transaction(function () use ($refund, $user, $data) {
            // Lock refund and invoice to prevent race conditions
            $refund = FeeRefund::lockForUpdate()->find($refund->id);
            $invoice = StudentInvoice::lockForUpdate()->find($refund->student_invoice_id);

            if (!$refund->isApproved()) {
                throw new \Exception('Only approved refunds can be processed');
            }

            $oldValues = $refund->toArray();
            $amount = (string) $refund->amount;

            // Update refund status
            $refund->update([
                'status' => FeeRefund::STATUS_PROCESSED,
                'processed_by' => $user->id,
                'processed_at' => now(),
                'reference_number' => $data['reference_number'] ?? $refund->reference_number,
                'notes' => $data['notes'] ?? $refund->notes,
            ]);

            // If credit note, add credit to invoice
            if ($refund->isCreditNote()) {
                $invoice->addCredit($amount);

                FeeAuditLog::log(
                    $invoice,
                    FeeAuditLog::ACTION_UPDATE,
                    ['credit_balance' => $invoice->getOriginal('credit_balance')],
                    ['credit_balance' => $invoice->credit_balance],
                    "Credit added from refund #{$refund->refund_number}: {$amount}"
                );
            }

            // If linked to a payment, update invoice amount_paid and balance
            if ($refund->fee_payment_id && !$refund->isCreditNote()) {
                $newAmountPaid = bcsub((string) $invoice->amount_paid, $amount, 2);
                if (bccomp($newAmountPaid, '0', 2) < 0) {
                    $newAmountPaid = '0.00';
                }
                $invoice->amount_paid = $newAmountPaid;
                $invoice->recalculateBalance();

                FeeAuditLog::log(
                    $invoice,
                    FeeAuditLog::ACTION_UPDATE,
                    ['amount_paid' => $invoice->getOriginal('amount_paid'), 'balance' => $invoice->getOriginal('balance')],
                    ['amount_paid' => $invoice->amount_paid, 'balance' => $invoice->balance],
                    "Invoice balance adjusted for refund #{$refund->refund_number}"
                );
            }

            // Log refund processing
            FeeAuditLog::log(
                $refund,
                FeeAuditLog::ACTION_UPDATE,
                $oldValues,
                $refund->fresh()->toArray(),
                "Refund processed: #{$refund->refund_number}, Amount: {$amount}"
            );

            return $refund->fresh(['invoice.student', 'payment', 'processedBy']);
        });
    }

    /**
     * Get refunds for a specific date range.
     */
    public function getRefundsForDateRange(string $startDate, string $endDate, ?int $year = null): Collection
    {
        $query = FeeRefund::processed()
            ->whereBetween('refund_date', [$startDate, $endDate])
            ->with(['invoice.student', 'payment', 'processedBy']);

        if ($year !== null) {
            $query->forYear($year);
        }

        return $query->orderBy('refund_date', 'desc')->get();
    }

    /**
     * Get refund summary statistics for a period.
     */
    public function getRefundSummary(?int $year = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = FeeRefund::processed();

        if ($year !== null) {
            $query->forYear($year);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('refund_date', [$startDate, $endDate]);
        }

        $refunds = $query->get();

        return [
            'total_refunded' => $refunds->sum('amount'),
            'refund_count' => $refunds->count(),
            'by_type' => $refunds->groupBy('refund_type')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total' => $group->sum('amount'),
                ];
            }),
            'by_method' => $refunds->groupBy('refund_method')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total' => $group->sum('amount'),
                ];
            }),
        ];
    }
}
