<?php

namespace App\Policies\Welfare;

use App\Models\User;
use App\Models\Welfare\DisciplinaryRecord;
use Illuminate\Auth\Access\HandlesAuthorization;

class DisciplinaryPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any disciplinary records.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRoles([
            'Administrator',
            'School Counsellor',
            'Welfare Admin',
            'Welfare View',
            'HOD',
            'Class Teacher',
        ]);
    }

    /**
     * Determine if the user can view the disciplinary record.
     */
    public function view(User $user, DisciplinaryRecord $record): bool
    {
        // Welfare staff can view all
        if ($user->hasAnyRoles(['Administrator', 'School Counsellor', 'Welfare Admin', 'Welfare View'])) {
            return true;
        }

        // Reporter can view their reported records
        if ($record->reported_by === $user->id) {
            return true;
        }

        // HODs and Class Teachers can view records for students in their classes
        if ($user->hasAnyRoles(['HOD', 'Class Teacher'])) {
            // Check if user is assigned to this case
            if ($record->welfareCase->assigned_to === $user->id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the user can create disciplinary records.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRoles([
            'Administrator',
            'School Counsellor',
            'Welfare Admin',
            'HOD',
            'Class Teacher',
            'Teacher',
        ]);
    }

    /**
     * Determine if the user can update the disciplinary record.
     */
    public function update(User $user, DisciplinaryRecord $record): bool
    {
        // Cannot update resolved records
        if ($record->isResolved()) {
            return $user->hasRoles('Administrator');
        }

        // Welfare staff can update
        if ($user->hasAnyRoles(['Administrator', 'School Counsellor', 'Welfare Admin'])) {
            return true;
        }

        // Reporter can update if still in reported status
        if ($record->reported_by === $user->id && $record->status === DisciplinaryRecord::STATUS_REPORTED) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can delete the disciplinary record.
     */
    public function delete(User $user, DisciplinaryRecord $record): bool
    {
        // Only admins can delete
        return $user->hasRoles('Administrator');
    }

    /**
     * Determine if the user can restore the disciplinary record.
     */
    public function restore(User $user, DisciplinaryRecord $record): bool
    {
        return $user->hasRoles('Administrator');
    }

    /**
     * Determine if the user can apply disciplinary action.
     */
    public function applyAction(User $user, DisciplinaryRecord $record): bool
    {
        if ($record->isResolved()) {
            return false;
        }

        // Only welfare staff and senior leaders can apply actions
        return $user->hasAnyRoles(['Administrator', 'School Counsellor', 'Welfare Admin']) ||
            in_array($user->position, ['School Head', 'Deputy School Head', 'HOD']);
    }

    /**
     * Determine if the user can apply severe actions (suspension, expulsion).
     */
    public function applySevereAction(User $user, DisciplinaryRecord $record): bool
    {
        if ($record->isResolved()) {
            return false;
        }

        // Only senior leadership can apply severe actions
        return $user->hasRoles('Administrator') ||
            in_array($user->position, ['School Head', 'Deputy School Head']);
    }

    /**
     * Determine if the user can resolve the record.
     */
    public function resolve(User $user, DisciplinaryRecord $record): bool
    {
        if ($record->isResolved()) {
            return false;
        }

        return $user->hasAnyRoles(['Administrator', 'School Counsellor', 'Welfare Admin']);
    }

    /**
     * Determine if the user can record parent notification.
     */
    public function notifyParent(User $user, DisciplinaryRecord $record): bool
    {
        if ($record->parent_notified) {
            return false;
        }

        return $user->hasAnyRoles([
            'Administrator',
            'School Counsellor',
            'Welfare Admin',
            'Class Teacher',
        ]);
    }

    /**
     * Determine if the user can view student disciplinary history.
     */
    public function viewStudentHistory(User $user): bool
    {
        return $user->hasAnyRoles([
            'Administrator',
            'School Counsellor',
            'Welfare Admin',
            'Welfare View',
            'HOD',
            'Class Teacher',
        ]);
    }

    /**
     * Determine if the user can export disciplinary records.
     */
    public function export(User $user): bool
    {
        return $user->hasAnyRoles(['Administrator', 'School Counsellor', 'Welfare Admin']);
    }

    /**
     * Determine if the user can view statistics.
     */
    public function viewStatistics(User $user): bool
    {
        return $user->hasAnyRoles([
            'Administrator',
            'School Counsellor',
            'Welfare Admin',
            'HOD',
        ]);
    }
}
