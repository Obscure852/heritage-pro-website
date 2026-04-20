<?php

namespace App\Observers\Fee;

use App\Models\Fee\StudentInvoice;

/**
 * Observer for StudentInvoice model.
 *
 * Automatically recalculates the invoice balance when relevant fields change.
 * This ensures data integrity when amounts are modified directly.
 */
class StudentInvoiceObserver
{
    /**
     * Handle the StudentInvoice "updating" event.
     *
     * Automatically recalculate balance when total_amount, discount_amount,
     * or subtotal_amount changes.
     */
    public function updating(StudentInvoice $invoice): void
    {
        // Check if any amount fields have changed
        $amountFieldsChanged = $invoice->isDirty([
            'total_amount',
            'subtotal_amount',
            'discount_amount',
        ]);

        // Only recalculate if amount fields changed but not if we're already
        // updating balance/status (to avoid infinite loops)
        if ($amountFieldsChanged && !$invoice->isDirty('balance')) {
            $this->recalculateBalanceAndStatus($invoice);
        }
    }

    /**
     * Handle the StudentInvoice "saving" event.
     *
     * Ensures balance and status are set correctly before saving.
     */
    public function saving(StudentInvoice $invoice): void
    {
        // If this is a new invoice or total_amount changed, ensure balance is calculated
        if (!$invoice->exists || $invoice->isDirty('total_amount')) {
            // Only recalculate if balance wasn't explicitly set
            if (!$invoice->isDirty('balance')) {
                $this->recalculateBalanceAndStatus($invoice);
            }
        }
    }

    /**
     * Recalculate balance and update status based on amounts.
     *
     * Note: This modifies the model directly without saving,
     * as the caller (updating/saving) will handle the save.
     */
    protected function recalculateBalanceAndStatus(StudentInvoice $invoice): void
    {
        $totalAmount = (float) $invoice->total_amount;
        $amountPaid = (float) $invoice->amount_paid;

        // Calculate new balance
        $newBalance = $totalAmount - $amountPaid;

        // Ensure balance is never negative
        if ($newBalance < 0) {
            $newBalance = 0;
        }

        $invoice->balance = $newBalance;

        // Update status based on balance (only if invoice is not cancelled/draft)
        if (!in_array($invoice->status, [StudentInvoice::STATUS_CANCELLED, StudentInvoice::STATUS_DRAFT])) {
            if ($newBalance <= 0) {
                $invoice->status = StudentInvoice::STATUS_PAID;
            } elseif ($amountPaid > 0) {
                $invoice->status = StudentInvoice::STATUS_PARTIAL;
            }
            // Keep current status if balance > 0 and no payment made
        }
    }
}
