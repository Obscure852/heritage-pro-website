<?php

namespace App\Policies\Leave;

use App\Models\Leave\LeaveBalance;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Leave balance authorization policy.
 *
 * Controls who can view and adjust leave balances.
 */
class LeaveBalancePolicy {
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any leave balances (HR list).
     *
     * @param  User  $user
     * @return bool
     */
    public function viewAny(User $user): bool {
        return $user->hasAnyRoles(['Administrator', 'Leave Admin', 'HR Admin', 'Leave View']);
    }

    /**
     * Determine whether the user can view a specific leave balance.
     *
     * @param  User  $user
     * @param  LeaveBalance  $balance
     * @return bool
     */
    public function view(User $user, LeaveBalance $balance): bool {
        // Roles: Administrator, Leave Admin, HR Admin, Leave View
        if ($user->hasAnyRoles(['Administrator', 'Leave Admin', 'HR Admin', 'Leave View'])) {
            return true;
        }

        // OR owns the balance
        if ($balance->user_id === $user->id) {
            return true;
        }

        // OR is the user's manager (balance owner reports to this user)
        return $balance->user && $balance->user->reporting_to === $user->id;
    }

    /**
     * Determine whether the user can make adjustments to a leave balance.
     *
     * @param  User  $user
     * @param  LeaveBalance  $balance
     * @return bool
     */
    public function adjust(User $user, LeaveBalance $balance): bool {
        return $user->hasAnyRoles(['Administrator', 'Leave Admin', 'HR Admin']);
    }
}
