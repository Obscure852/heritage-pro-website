<?php

namespace App\Policies\Fee;

use App\Models\Fee\FeePayment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Gate;

class FeePaymentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any payments.
     */
    public function viewAny(User $user): bool
    {
        return Gate::allows('collect-fees') || Gate::allows('view-fee-reports');
    }

    /**
     * Determine if the user can view the payment.
     */
    public function view(User $user, FeePayment $payment): bool
    {
        return Gate::allows('collect-fees') || Gate::allows('view-fee-reports');
    }

    /**
     * Determine if the user can create payments.
     */
    public function create(User $user): bool
    {
        return Gate::allows('collect-fees');
    }

    /**
     * Determine if the user can void the payment.
     * CRITICAL: Only Administrator/Bursar can void payments.
     */
    public function void(User $user, FeePayment $payment): bool
    {
        // Cannot void if already voided
        if ($payment->isVoided()) {
            return false;
        }

        // Only Administrator/Bursar can void payments (via void-payments gate)
        return Gate::allows('void-payments');
    }

    /**
     * Determine if the user can print receipt for the payment.
     */
    public function printReceipt(User $user, FeePayment $payment): bool
    {
        return Gate::allows('collect-fees');
    }
}
