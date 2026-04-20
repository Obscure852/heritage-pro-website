<?php

namespace App\Policies\Fee;

use App\Models\Fee\StudentInvoice;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Gate;

class StudentInvoicePolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any invoices.
     */
    public function viewAny(User $user): bool
    {
        return Gate::allows('collect-fees') || Gate::allows('view-fee-reports');
    }

    /**
     * Determine if the user can view the invoice.
     */
    public function view(User $user, StudentInvoice $invoice): bool
    {
        return Gate::allows('collect-fees') || Gate::allows('view-fee-reports');
    }

    /**
     * Determine if the user can create invoices.
     */
    public function create(User $user): bool
    {
        return Gate::allows('collect-fees');
    }

    /**
     * Determine if the user can update the invoice.
     */
    public function update(User $user, StudentInvoice $invoice): bool
    {
        // Cannot update if invoice is cancelled or paid
        if ($invoice->isCancelled() || $invoice->isPaid()) {
            return false;
        }

        return Gate::allows('collect-fees');
    }

    /**
     * Determine if the user can cancel the invoice.
     */
    public function cancel(User $user, StudentInvoice $invoice): bool
    {
        // Only setup admins can cancel invoices
        return Gate::allows('manage-fee-setup');
    }

    /**
     * Determine if the user can generate bulk invoices.
     */
    public function generateBulk(User $user): bool
    {
        return Gate::allows('collect-fees');
    }
}
