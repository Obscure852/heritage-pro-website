<?php

namespace App\Policies\Welfare;

use App\Models\User;
use App\Models\Welfare\WelfareCase;
use Illuminate\Auth\Access\HandlesAuthorization;

class WelfareCasePolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any welfare cases.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRoles([
            'Administrator',
            'School Counsellor',
            'Welfare Admin',
            'Welfare View',
            'Nurse',
            'HOD',
            'Class Teacher',
        ]);
    }

    /**
     * Determine if the user can view the welfare case.
     */
    public function view(User $user, WelfareCase $case): bool
    {
        // Admins and welfare staff can view all
        if ($user->hasAnyRoles(['Administrator', 'School Counsellor', 'Welfare Admin', 'Welfare View'])) {
            return true;
        }

        // Nurses can view health-related cases
        if ($user->hasRoles('Nurse')) {
            return $case->welfareType->code === 'HEALTH';
        }

        // HODs and Class Teachers can view their assigned cases or students' cases
        if ($user->hasAnyRoles(['HOD', 'Class Teacher'])) {
            return $case->assigned_to === $user->id ||
                $case->opened_by === $user->id;
        }

        return false;
    }

    /**
     * Determine if the user can create welfare cases.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRoles([
            'Administrator',
            'School Counsellor',
            'Welfare Admin',
            'Nurse',
            'HOD',
            'Class Teacher',
        ]);
    }

    /**
     * Determine if the user can update the welfare case.
     */
    public function update(User $user, WelfareCase $case): bool
    {
        // Admins can always update
        if ($user->hasRoles('Administrator')) {
            return true;
        }

        // Case must be open for updates
        if ($case->isClosed()) {
            return false;
        }

        // Welfare staff can update
        if ($user->hasAnyRoles(['School Counsellor', 'Welfare Admin'])) {
            return true;
        }

        // Owner or assignee can update
        return $case->opened_by === $user->id || $case->assigned_to === $user->id;
    }

    /**
     * Determine if the user can delete the welfare case.
     */
    public function delete(User $user, WelfareCase $case): bool
    {
        // Only admins can delete
        return $user->hasRoles('Administrator');
    }

    /**
     * Determine if the user can restore the welfare case.
     */
    public function restore(User $user, WelfareCase $case): bool
    {
        return $user->hasRoles('Administrator');
    }

    /**
     * Determine if the user can permanently delete the welfare case.
     */
    public function forceDelete(User $user, WelfareCase $case): bool
    {
        return $user->hasRoles('Administrator');
    }

    /**
     * Determine if the user can assign the case.
     */
    public function assign(User $user, WelfareCase $case): bool
    {
        return $user->hasAnyRoles([
            'Administrator',
            'School Counsellor',
            'Welfare Admin',
        ]);
    }

    /**
     * Determine if the user can escalate the case.
     */
    public function escalate(User $user, WelfareCase $case): bool
    {
        if ($case->isClosed()) {
            return false;
        }

        return $user->hasAnyRoles([
            'Administrator',
            'School Counsellor',
            'Welfare Admin',
        ]) || $case->opened_by === $user->id || $case->assigned_to === $user->id;
    }

    /**
     * Determine if the user can approve the case.
     */
    public function approve(User $user, WelfareCase $case): bool
    {
        if (!$case->isPendingApproval()) {
            return false;
        }

        // Only senior staff can approve
        return $user->hasAnyRoles(['Administrator', 'School Counsellor']) ||
            in_array($user->position, ['School Head', 'Deputy School Head']);
    }

    /**
     * Determine if the user can close the case.
     */
    public function close(User $user, WelfareCase $case): bool
    {
        if ($case->isClosed()) {
            return false;
        }

        return $user->hasAnyRoles([
            'Administrator',
            'School Counsellor',
            'Welfare Admin',
        ]) || $case->assigned_to === $user->id;
    }

    /**
     * Determine if the user can reopen the case.
     */
    public function reopen(User $user, WelfareCase $case): bool
    {
        if (!$case->isClosed()) {
            return false;
        }

        return $user->hasAnyRoles([
            'Administrator',
            'School Counsellor',
        ]);
    }

    /**
     * Determine if the user can add notes to the case.
     */
    public function addNote(User $user, WelfareCase $case): bool
    {
        return $this->view($user, $case) && !$case->isClosed();
    }

    /**
     * Determine if the user can add attachments to the case.
     */
    public function addAttachment(User $user, WelfareCase $case): bool
    {
        return $this->view($user, $case) && !$case->isClosed();
    }

    /**
     * Determine if the user can view confidential content.
     */
    public function viewConfidential(User $user, WelfareCase $case): bool
    {
        // Check confidentiality level
        $level = $case->welfareType->confidentiality_level ?? 1;

        // Level 4 (highly confidential) - only counsellors and admins
        if ($level >= 4) {
            return $user->hasAnyRoles(['Administrator', 'School Counsellor']);
        }

        // Level 3 (confidential) - welfare staff
        if ($level >= 3) {
            return $user->hasAnyRoles(['Administrator', 'School Counsellor', 'Welfare Admin']);
        }

        // Lower levels - standard access
        return $this->view($user, $case);
    }

    /**
     * Determine if the user can export the case.
     */
    public function export(User $user, WelfareCase $case): bool
    {
        return $user->hasAnyRoles(['Administrator', 'School Counsellor']);
    }

    /**
     * Determine if the user can view audit logs for the case.
     */
    public function viewAudit(User $user, WelfareCase $case): bool
    {
        return $user->hasAnyRoles(['Administrator', 'School Counsellor', 'Welfare Admin']);
    }
}
