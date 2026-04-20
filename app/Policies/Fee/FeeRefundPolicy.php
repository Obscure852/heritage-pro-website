<?php

namespace App\Policies\Fee;

use App\Models\Fee\FeeRefund;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Gate;

class FeeRefundPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any refunds.
     */
    public function viewAny(User $user): bool
    {
        return Gate::allows('view-refunds');
    }

    /**
     * Determine if the user can view the refund.
     */
    public function view(User $user, FeeRefund $refund): bool
    {
        return Gate::allows('view-refunds');
    }

    /**
     * Determine if the user can create refund requests.
     */
    public function create(User $user): bool
    {
        return Gate::allows('request-refunds');
    }

    /**
     * Determine if the user can approve refunds.
     */
    public function approve(User $user, ?FeeRefund $refund = null): bool
    {
        // If specific refund, check if it's pending
        if ($refund && !$refund->isPending()) {
            return false;
        }

        return Gate::allows('approve-refunds');
    }

    /**
     * Determine if the user can reject refunds.
     */
    public function reject(User $user, FeeRefund $refund): bool
    {
        // Can only reject pending refunds
        if (!$refund->isPending()) {
            return false;
        }

        return Gate::allows('approve-refunds');
    }

    /**
     * Determine if the user can process approved refunds.
     */
    public function process(User $user, FeeRefund $refund): bool
    {
        // Can only process approved refunds
        if (!$refund->isApproved()) {
            return false;
        }

        return Gate::allows('process-refunds');
    }

    /**
     * Determine if the user can print refund/credit note.
     */
    public function print(User $user, FeeRefund $refund): bool
    {
        return Gate::allows('view-refunds');
    }
}
