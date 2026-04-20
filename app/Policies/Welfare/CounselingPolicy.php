<?php

namespace App\Policies\Welfare;

use App\Models\User;
use App\Models\Welfare\CounselingSession;
use Illuminate\Auth\Access\HandlesAuthorization;

class CounselingPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any counseling sessions.
     * Counseling is Level 4 confidential - restricted access.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRoles([
            'Administrator',
            'School Counsellor',
        ]);
    }

    /**
     * Determine if the user can view the counseling session.
     */
    public function view(User $user, CounselingSession $session): bool
    {
        // Only admins and counsellors can view counseling sessions
        if ($user->hasAnyRoles(['Administrator', 'School Counsellor'])) {
            return true;
        }

        // Counsellor who conducted the session can always view
        if ($session->counsellor_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can create counseling sessions.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRoles([
            'Administrator',
            'School Counsellor',
        ]);
    }

    /**
     * Determine if the user can update the counseling session.
     */
    public function update(User $user, CounselingSession $session): bool
    {
        // Admins can always update
        if ($user->hasRoles('Administrator')) {
            return true;
        }

        // Only the counsellor who created it can update (within reason)
        if ($session->counsellor_id === $user->id) {
            // Can update scheduled sessions anytime
            if ($session->isScheduled()) {
                return true;
            }

            // Can update completed sessions within 7 days
            if ($session->isCompleted() && $session->updated_at->diffInDays(now()) <= 7) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the user can delete the counseling session.
     */
    public function delete(User $user, CounselingSession $session): bool
    {
        // Only admins can delete
        return $user->hasRoles('Administrator');
    }

    /**
     * Determine if the user can restore the counseling session.
     */
    public function restore(User $user, CounselingSession $session): bool
    {
        return $user->hasRoles('Administrator');
    }

    /**
     * Determine if the user can complete the session.
     */
    public function complete(User $user, CounselingSession $session): bool
    {
        if (!$session->isScheduled()) {
            return false;
        }

        // Only the assigned counsellor or admin can complete
        return $user->hasRoles('Administrator') || $session->counsellor_id === $user->id;
    }

    /**
     * Determine if the user can cancel the session.
     */
    public function cancel(User $user, CounselingSession $session): bool
    {
        if (!$session->isScheduled()) {
            return false;
        }

        return $user->hasRoles('Administrator') ||
            $user->hasRoles('School Counsellor') ||
            $session->counsellor_id === $user->id;
    }

    /**
     * Determine if the user can view session notes.
     * Session notes are encrypted and highly confidential.
     */
    public function viewNotes(User $user, CounselingSession $session): bool
    {
        // Only the counsellor who created it or admin can view notes
        return $user->hasRoles('Administrator') || $session->counsellor_id === $user->id;
    }

    /**
     * Determine if the user can view risk assessment.
     */
    public function viewRiskAssessment(User $user, CounselingSession $session): bool
    {
        // Only counsellors can view risk assessments
        return $user->hasAnyRoles(['Administrator', 'School Counsellor']);
    }

    /**
     * Determine if the user can schedule follow-up.
     */
    public function scheduleFollowUp(User $user, CounselingSession $session): bool
    {
        return $user->hasRoles('Administrator') ||
            $user->hasRoles('School Counsellor') ||
            $session->counsellor_id === $user->id;
    }

    /**
     * Determine if the user can export session data.
     */
    public function export(User $user, CounselingSession $session): bool
    {
        // Very restricted - only admins and the counsellor
        return $user->hasRoles('Administrator') || $session->counsellor_id === $user->id;
    }
}
