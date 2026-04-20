<?php

namespace App\Services\Fee;

use App\Models\Fee\FeeAuditLog;
use App\Models\Fee\PaymentPlan;
use App\Models\Fee\PaymentPlanInstallment;
use App\Models\Fee\StudentInvoice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentPlanService
{
    /**
     * Create a payment plan for an invoice.
     */
    public function createPaymentPlan(
        StudentInvoice $invoice,
        int $numberOfInstallments,
        string $frequency,
        Carbon $startDate,
        User $user,
        ?string $name = null,
        ?array $customInstallments = null
    ): PaymentPlan {
        return DB::transaction(function () use ($invoice, $numberOfInstallments, $frequency, $startDate, $user, $name, $customInstallments) {
            // Lock the invoice to prevent race conditions when checking for active plans
            $invoice = StudentInvoice::lockForUpdate()->find($invoice->id);

            // Check if invoice already has an active payment plan
            if ($invoice->hasActivePaymentPlan()) {
                throw new \Exception('Invoice already has an active payment plan.');
            }

            // Check if invoice has balance to be planned
            if (bccomp((string) $invoice->balance, '0', 2) <= 0) {
                throw new \Exception('Invoice has no outstanding balance for a payment plan.');
            }

            // Create the payment plan
            $plan = PaymentPlan::create([
                'student_invoice_id' => $invoice->id,
                'student_id' => $invoice->student_id,
                'year' => $invoice->year,
                'name' => $name ?? $this->generatePlanName($invoice, $frequency),
                'total_amount' => $invoice->balance,
                'number_of_installments' => $numberOfInstallments,
                'frequency' => $frequency,
                'start_date' => $startDate,
                'status' => PaymentPlan::STATUS_ACTIVE,
                'created_by' => $user->id,
            ]);

            // Create installments
            if ($frequency === PaymentPlan::FREQ_CUSTOM && $customInstallments) {
                $this->createCustomInstallments($plan, $customInstallments);
            } else {
                $this->createStandardInstallments($plan, $numberOfInstallments, $frequency, $startDate);
            }

            // Log to audit trail
            FeeAuditLog::log(
                $invoice,
                FeeAuditLog::ACTION_UPDATE,
                null,
                ['payment_plan_id' => $plan->id, 'installments' => $numberOfInstallments],
                "Payment plan created with {$numberOfInstallments} installments ({$frequency})"
            );

            Log::info('Payment plan created', [
                'plan_id' => $plan->id,
                'invoice_id' => $invoice->id,
                'installments' => $numberOfInstallments,
                'frequency' => $frequency,
            ]);

            return $plan->load('installments');
        });
    }

    /**
     * Create standard installments (equal amounts, regular intervals).
     */
    private function createStandardInstallments(PaymentPlan $plan, int $numberOfInstallments, string $frequency, Carbon $startDate): void
    {
        $totalAmount = (string) $plan->total_amount;
        $installmentAmount = bcdiv($totalAmount, (string) $numberOfInstallments, 2);

        // Handle rounding - last installment gets the remainder
        $allocatedAmount = bcmul($installmentAmount, (string) ($numberOfInstallments - 1), 2);
        $lastInstallmentAmount = bcsub($totalAmount, $allocatedAmount, 2);

        for ($i = 1; $i <= $numberOfInstallments; $i++) {
            $dueDate = $this->calculateDueDate($startDate, $i, $frequency);
            $amount = ($i === $numberOfInstallments) ? $lastInstallmentAmount : $installmentAmount;

            PaymentPlanInstallment::create([
                'payment_plan_id' => $plan->id,
                'installment_number' => $i,
                'amount' => $amount,
                'due_date' => $dueDate,
                'amount_paid' => '0.00',
                'status' => PaymentPlanInstallment::STATUS_PENDING,
            ]);
        }
    }

    /**
     * Create custom installments with specified amounts and dates.
     */
    private function createCustomInstallments(PaymentPlan $plan, array $customInstallments): void
    {
        $totalSpecified = '0.00';

        foreach ($customInstallments as $index => $installment) {
            $totalSpecified = bcadd($totalSpecified, $installment['amount'], 2);

            PaymentPlanInstallment::create([
                'payment_plan_id' => $plan->id,
                'installment_number' => $index + 1,
                'amount' => $installment['amount'],
                'due_date' => Carbon::parse($installment['due_date']),
                'amount_paid' => '0.00',
                'status' => PaymentPlanInstallment::STATUS_PENDING,
            ]);
        }

        // Validate total matches plan total
        if (bccomp($totalSpecified, (string) $plan->total_amount, 2) !== 0) {
            throw new \Exception('Custom installment amounts must equal the plan total.');
        }
    }

    /**
     * Calculate due date based on frequency.
     */
    private function calculateDueDate(Carbon $startDate, int $installmentNumber, string $frequency): Carbon
    {
        $date = $startDate->copy();

        switch ($frequency) {
            case PaymentPlan::FREQ_MONTHLY:
                return $date->addMonths($installmentNumber - 1);

            case PaymentPlan::FREQ_TERMLY:
                // Approximately 4 months between terms
                return $date->addMonths(($installmentNumber - 1) * 4);

            default:
                return $date->addMonths($installmentNumber - 1);
        }
    }

    /**
     * Generate a default plan name.
     */
    private function generatePlanName(StudentInvoice $invoice, string $frequency): string
    {
        $frequencyLabel = PaymentPlan::frequencies()[$frequency] ?? $frequency;
        return "{$invoice->year} {$frequencyLabel} Payment Plan";
    }

    /**
     * Preview a payment plan before creation.
     */
    public function previewPaymentPlan(
        StudentInvoice $invoice,
        int $numberOfInstallments,
        string $frequency,
        Carbon $startDate
    ): array {
        $totalAmount = (string) $invoice->balance;
        $installmentAmount = bcdiv($totalAmount, (string) $numberOfInstallments, 2);

        $allocatedAmount = bcmul($installmentAmount, (string) ($numberOfInstallments - 1), 2);
        $lastInstallmentAmount = bcsub($totalAmount, $allocatedAmount, 2);

        $installments = [];
        for ($i = 1; $i <= $numberOfInstallments; $i++) {
            $dueDate = $this->calculateDueDate($startDate, $i, $frequency);
            $amount = ($i === $numberOfInstallments) ? $lastInstallmentAmount : $installmentAmount;

            $installments[] = [
                'installment_number' => $i,
                'amount' => $amount,
                'due_date' => $dueDate->toDateString(),
                'due_date_formatted' => $dueDate->format('d M Y'),
            ];
        }

        return [
            'total_amount' => $totalAmount,
            'number_of_installments' => $numberOfInstallments,
            'frequency' => $frequency,
            'frequency_label' => PaymentPlan::frequencies()[$frequency] ?? $frequency,
            'start_date' => $startDate->toDateString(),
            'installments' => $installments,
        ];
    }

    /**
     * Cancel a payment plan.
     */
    public function cancelPaymentPlan(PaymentPlan $plan, User $user, string $reason): bool
    {
        return DB::transaction(function () use ($plan, $user, $reason) {
            if (!$plan->isActive()) {
                throw new \Exception('Only active payment plans can be cancelled.');
            }

            $plan->cancel($user, $reason);

            FeeAuditLog::log(
                $plan->invoice,
                FeeAuditLog::ACTION_UPDATE,
                ['plan_status' => PaymentPlan::STATUS_ACTIVE],
                ['plan_status' => PaymentPlan::STATUS_CANCELLED],
                "Payment plan cancelled. Reason: {$reason}"
            );

            Log::info('Payment plan cancelled', [
                'plan_id' => $plan->id,
                'user_id' => $user->id,
                'reason' => $reason,
            ]);

            return true;
        });
    }

    /**
     * Get the next installment due for a student.
     */
    public function getNextDueInstallment(int $studentId): ?PaymentPlanInstallment
    {
        return PaymentPlanInstallment::whereHas('paymentPlan', function ($query) use ($studentId) {
            $query->where('student_id', $studentId)
                  ->where('status', PaymentPlan::STATUS_ACTIVE);
        })
        ->whereIn('status', [
            PaymentPlanInstallment::STATUS_PENDING,
            PaymentPlanInstallment::STATUS_PARTIAL,
            PaymentPlanInstallment::STATUS_OVERDUE,
        ])
        ->orderBy('due_date')
        ->first();
    }

    /**
     * Get all overdue installments.
     */
    public function getOverdueInstallments(): \Illuminate\Database\Eloquent\Collection
    {
        return PaymentPlanInstallment::with(['paymentPlan.student', 'paymentPlan.invoice'])
            ->whereHas('paymentPlan', function ($query) {
                $query->where('status', PaymentPlan::STATUS_ACTIVE);
            })
            ->whereIn('status', [
                PaymentPlanInstallment::STATUS_PENDING,
                PaymentPlanInstallment::STATUS_PARTIAL,
            ])
            ->where('due_date', '<', today())
            ->get();
    }

    /**
     * Mark overdue installments.
     */
    public function markOverdueInstallments(): int
    {
        $overdueCount = 0;

        $installments = PaymentPlanInstallment::whereHas('paymentPlan', function ($query) {
            $query->where('status', PaymentPlan::STATUS_ACTIVE);
        })
        ->whereIn('status', [
            PaymentPlanInstallment::STATUS_PENDING,
            PaymentPlanInstallment::STATUS_PARTIAL,
        ])
        ->where('due_date', '<', today())
        ->get();

        foreach ($installments as $installment) {
            $installment->markAsOverdue();
            $overdueCount++;
        }

        return $overdueCount;
    }

    /**
     * Get installments due on a specific date.
     */
    public function getInstallmentsDueOn(Carbon $date): \Illuminate\Database\Eloquent\Collection
    {
        return PaymentPlanInstallment::with(['paymentPlan.student', 'paymentPlan.invoice'])
            ->whereHas('paymentPlan', function ($query) {
                $query->where('status', PaymentPlan::STATUS_ACTIVE);
            })
            ->whereIn('status', [
                PaymentPlanInstallment::STATUS_PENDING,
                PaymentPlanInstallment::STATUS_PARTIAL,
            ])
            ->whereDate('due_date', $date)
            ->get();
    }
}
