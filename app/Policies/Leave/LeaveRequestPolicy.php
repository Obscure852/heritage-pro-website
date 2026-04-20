<?php

namespace App\Policies\Leave;

use App\Models\Leave\LeaveRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Leave request authorization policy.
 *
 * Controls who can view, create, update, delete, and approve leave requests.
 */
class LeaveRequestPolicy {
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any leave requests.
     *
     * @param  User  $user
     * @return bool
     */
    public function viewAny(User $user): bool {
        // Roles: Administrator, Leave Admin, HR Admin, Leave View
        if ($user->hasAnyRoles(['Administrator', 'Leave Admin', 'HR Admin', 'Leave View'])) {
            return true;
        }

        // OR user has direct reports (is a manager)
        return User::where('reporting_to', $user->id)->exists();
    }

    /**
     * Determine whether the user can view a specific leave request.
     *
     * @param  User  $user
     * @param  LeaveRequest  $request
     * @return bool
     */
    public function view(User $user, LeaveRequest $request): bool {
        // Roles: Administrator, Leave Admin, HR Admin, Leave View
        if ($user->hasAnyRoles(['Administrator', 'Leave Admin', 'HR Admin', 'Leave View'])) {
            return true;
        }

        // OR owns the request
        if ($request->user_id === $user->id) {
            return true;
        }

        // OR is the approver (request owner reports to this user)
        return $request->user && $request->user->reporting_to === $user->id;
    }

    /**
     * Determine whether the user can create leave requests.
     *
     * All authenticated users can submit their own requests.
     * Actual balance validation happens in the service layer.
     *
     * @param  User  $user
     * @return bool
     */
    public function create(User $user): bool {
        return true;
    }

    /**
     * Determine whether the user can update a leave request.
     *
     * Only owner can edit, and only if request is pending.
     *
     * @param  User  $user
     * @param  LeaveRequest  $request
     * @return bool
     */
    public function update(User $user, LeaveRequest $request): bool {
        return $request->user_id === $user->id && $request->status === LeaveRequest::STATUS_PENDING;
    }

    /**
     * Determine whether the user can delete/cancel a leave request.
     *
     * Owner can cancel pending requests.
     * Admin/HR can cancel any pending request.
     *
     * @param  User  $user
     * @param  LeaveRequest  $request
     * @return bool
     */
    public function delete(User $user, LeaveRequest $request): bool {
        // Only pending requests can be cancelled
        if ($request->status !== LeaveRequest::STATUS_PENDING) {
            return false;
        }

        // Admin/HR can cancel any pending request
        if ($user->hasAnyRoles(['Administrator', 'Leave Admin', 'HR Admin'])) {
            return true;
        }

        // Owner can cancel their own pending request
        return $request->user_id === $user->id;
    }

    /**
     * Determine whether the user can approve or reject a leave request.
     *
     * @param  User  $user
     * @param  LeaveRequest  $request
     * @return bool
     */
    public function approve(User $user, LeaveRequest $request): bool {
        // Roles: Administrator, Leave Admin, HR Admin
        if ($user->hasAnyRoles(['Administrator', 'Leave Admin', 'HR Admin'])) {
            return true;
        }

        // OR is the designated approver (request owner reports to this user)
        return $request->user && $request->user->reporting_to === $user->id;
    }
}
