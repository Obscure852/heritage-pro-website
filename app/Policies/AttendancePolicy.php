<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Klass;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttendancePolicy
{
    use HandlesAuthorization;

    /**
     * Roles that have full access to all attendance data
     */
    public const ADMIN_ROLES = [
        'Administrator',
        'Attendance Admin',
        'Academic Admin',
        'HOD',
        'Assessment Admin',
    ];

    /**
     * @deprecated Use ADMIN_ROLES constant instead
     */
    protected array $adminRoles = self::ADMIN_ROLES;

    /**
     * Check if a user has admin access to attendance module
     */
    public static function isAdmin(User $user): bool
    {
        return $user->hasAnyRoles(self::ADMIN_ROLES);
    }

    /**
     * Determine if the user can view attendance for a class.
     * Admins and Class Teachers can view any class; other users only their own.
     */
    public function viewAttendance(User $user, Klass $klass): bool
    {
        if ($user->hasAnyRoles($this->adminRoles)) {
            return true;
        }

        if ($user->hasAnyRoles(['Class Teacher'])) {
            return true;
        }

        return $klass->user_id === $user->id;
    }

    /**
     * Determine if the user can edit attendance for a class.
     */
    public function editAttendance(User $user, Klass $klass): bool
    {
        // Admins can edit any class
        if ($user->hasAnyRoles($this->adminRoles)) {
            return true;
        }

        // Class teacher can edit their own class
        return $klass->user_id === $user->id;
    }

    /**
     * Determine if the user can manage attendance settings (codes, holidays).
     */
    public function manageSettings(User $user): bool
    {
        return $user->hasAnyRoles($this->adminRoles);
    }

    /**
     * Legacy method - kept for backward compatibility
     * @deprecated Use editAttendance instead
     */
    public function canEditAttendance(User $user, Klass $klass): bool
    {
        return $this->editAttendance($user, $klass);
    }
}
