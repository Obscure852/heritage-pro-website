<?php

namespace App\Policies\Welfare;

use App\Models\User;
use App\Models\Welfare\SafeguardingConcern;
use Illuminate\Auth\Access\HandlesAuthorization;

class SafeguardingPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any safeguarding concerns.
     * Safeguarding is highly confidential (Level 4).
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRoles([
            'Administrator',
            'School Counsellor',
            'Welfare Admin',
        ]);
    }

    /**
     * Determine if the user can view the safeguarding concern.
     */
    public function view(User $user, SafeguardingConcern $concern): bool
    {
        // Only designated safeguarding leads can view
        if ($user->hasAnyRoles(['Administrator', 'School Counsellor', 'Welfare Admin'])) {
            return true;
        }

        // Reporter can view their own reported concerns
        if ($concern->reported_by === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can create safeguarding concerns.
     * Anyone can report a safeguarding concern.
     */
    public function create(User $user): bool
    {
        // All staff can report safeguarding concerns
        return $user->hasAnyRoles([
            'Administrator',
            'School Counsellor',
            'Welfare Admin',
            'Welfare View',
            'Nurse',
            'HOD',
            'Class Teacher',
            'Teacher',
        ]);
    }

    /**
     * Determine if the user can update the safeguarding concern.
     */
    public function update(User $user, SafeguardingConcern $concern): bool
    {
        // Cannot update closed concerns
        if ($concern->isClosed()) {
            return false;
        }

        // Only designated safeguarding leads can update
        return $user->hasAnyRoles(['Administrator', 'School Counsellor', 'Welfare Admin']);
    }

    /**
     * Determine if the user can delete the safeguarding concern.
     */
    public function delete(User $user, SafeguardingConcern $concern): bool
    {
        // Safeguarding records should never be deleted - only admin in extreme cases
        return $user->hasRoles('Administrator');
    }

    /**
     * Determine if the user can restore the safeguarding concern.
     */
    public function restore(User $user, SafeguardingConcern $concern): bool
    {
        return $user->hasRoles('Administrator');
    }

    /**
     * Determine if the user can record immediate action.
     */
    public function recordImmediateAction(User $user, SafeguardingConcern $concern): bool
    {
        if ($concern->isClosed()) {
            return false;
        }

        return $user->hasAnyRoles(['Administrator', 'School Counsellor', 'Welfare Admin']);
    }

    /**
     * Determine if the user can notify authorities.
     */
    public function notifyAuthorities(User $user, SafeguardingConcern $concern): bool
    {
        if ($concern->isClosed() || $concern->authorities_notified) {
            return false;
        }

        // Only senior staff can notify authorities
        return $user->hasAnyRoles(['Administrator', 'School Counsellor']) ||
            in_array($user->position, ['School Head', 'Deputy School Head']);
    }

    /**
     * Determine if the user can notify parents.
     */
    public function notifyParents(User $user, SafeguardingConcern $concern): bool
    {
        if ($concern->isClosed() || $concern->parents_informed) {
            return false;
        }

        // Only designated leads can notify parents (sensitive decision)
        return $user->hasAnyRoles(['Administrator', 'School Counsellor']);
    }

    /**
     * Determine if the user can close the concern.
     */
    public function close(User $user, SafeguardingConcern $concern): bool
    {
        if ($concern->isClosed()) {
            return false;
        }

        // Only senior staff can close safeguarding concerns
        return $user->hasAnyRoles(['Administrator', 'School Counsellor']) ||
            in_array($user->position, ['School Head', 'Deputy School Head']);
    }

    /**
     * Determine if the user can view sensitive details.
     * Disclosure details are highly confidential.
     */
    public function viewSensitiveDetails(User $user, SafeguardingConcern $concern): bool
    {
        // Very restricted access
        return $user->hasAnyRoles(['Administrator', 'School Counsellor']);
    }

    /**
     * Determine if the user can export the concern.
     */
    public function export(User $user, SafeguardingConcern $concern): bool
    {
        // Very restricted - only for official reports
        return $user->hasRoles('Administrator') ||
            ($user->hasRoles('School Counsellor') && in_array($user->position, ['School Head', 'Deputy School Head']));
    }

    /**
     * Determine if the user can view audit trail.
     */
    public function viewAudit(User $user, SafeguardingConcern $concern): bool
    {
        return $user->hasAnyRoles(['Administrator', 'School Counsellor']);
    }
}
