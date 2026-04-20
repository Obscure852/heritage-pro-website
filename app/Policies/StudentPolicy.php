<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StudentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any students.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRoles([
            'Administrator',
            'HOD',
            'Academic Admin',
            'Academic Edit',
            'Students Admin',
            'Class Teacher',
            'Students Edit',
            'Students View',
            'Students Health',
            'Teacher'
        ]);
    }

    /**
     * Determine whether the user can view the student.
     */
    public function view(User $user, Student $student): bool
    {
        return $user->hasAnyRoles([
            'Administrator',
            'HOD',
            'Academic Admin',
            'Academic Edit',
            'Students Admin',
            'Class Teacher',
            'Students Edit',
            'Students View',
            'Students Health',
            'Teacher'
        ]);
    }

    /**
     * Determine whether the user can create students.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRoles([
            'Administrator',
            'HOD',
            'Academic Admin',
            'Academic Edit',
            'Students Admin',
            'Students Edit'
        ]);
    }

    /**
     * Determine whether the user can update the student.
     */
    public function update(User $user, Student $student): bool
    {
        return $user->hasAnyRoles([
            'Administrator',
            'HOD',
            'Academic Admin',
            'Academic Edit',
            'Students Admin',
            'Students Edit'
        ]);
    }

    /**
     * Determine whether the user can delete the student.
     */
    public function delete(User $user, Student $student): bool
    {
        return $user->hasAnyRoles([
            'Administrator',
            'Students Admin'
        ]);
    }

    /**
     * Determine whether the user can bulk delete students.
     */
    public function deleteMultiple(User $user): bool
    {
        return $user->hasAnyRoles([
            'Administrator',
            'Students Admin'
        ]);
    }

    /**
     * Determine whether the user can manage student health records.
     */
    public function manageHealth(User $user, Student $student): bool
    {
        return $user->hasAnyRoles([
            'Administrator',
            'Students Health'
        ]);
    }

    /**
     * Determine whether the user can allocate students to classes.
     */
    public function allocateClass(User $user): bool
    {
        return $user->hasAnyRoles([
            'Administrator',
            'HOD',
            'Academic Admin',
            'Academic Edit',
            'Students Admin',
            'Students Edit'
        ]);
    }

    /**
     * Determine whether the user can allocate students to houses.
     */
    public function allocateHouse(User $user): bool
    {
        return $user->hasAnyRoles([
            'Administrator',
            'Houses Admin',
            'Houses Edit',
            'Students Admin'
        ]);
    }

    /**
     * Determine whether the user can import students.
     */
    public function import(User $user): bool
    {
        return $user->hasAnyRoles([
            'Administrator',
            'Students Admin',
            'Data Importing'
        ]);
    }

    /**
     * Determine whether the user can export students.
     */
    public function export(User $user): bool
    {
        return $user->hasAnyRoles([
            'Administrator',
            'Students Admin',
            'Students View',
            'Academic Admin'
        ]);
    }
}
