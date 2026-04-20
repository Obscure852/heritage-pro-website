<?php

namespace App\Policies\Activities;

use App\Models\Activities\Activity;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ActivityPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasAnyRoles(['Administrator'])) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRoles([
            'Activities Admin',
            'Activities Edit',
            'Activities View',
            'Activities Staff',
        ]);
    }

    public function view(User $user, Activity $activity): bool
    {
        if ($user->hasAnyRoles([
            'Activities Admin',
            'Activities Edit',
            'Activities View',
        ])) {
            return true;
        }

        if (!$user->hasAnyRoles(['Activities Staff'])) {
            return false;
        }

        return $activity->hasAssignedStaff($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRoles([
            'Activities Admin',
            'Activities Edit',
        ]);
    }

    public function update(User $user, Activity $activity): bool
    {
        return $this->create($user);
    }

    public function activate(User $user, Activity $activity): bool
    {
        return $this->create($user);
    }

    public function pause(User $user, Activity $activity): bool
    {
        return $this->create($user);
    }

    public function close(User $user, Activity $activity): bool
    {
        return $this->create($user);
    }

    public function archive(User $user, Activity $activity): bool
    {
        return $this->create($user);
    }

    public function manageStaff(User $user, Activity $activity): bool
    {
        return $this->update($user, $activity);
    }

    public function manageEligibility(User $user, Activity $activity): bool
    {
        return $this->update($user, $activity);
    }

    public function manageRoster(User $user, Activity $activity): bool
    {
        return $this->update($user, $activity);
    }

    public function manageSchedules(User $user, Activity $activity): bool
    {
        return $this->update($user, $activity);
    }

    public function manageSessions(User $user, Activity $activity): bool
    {
        if ($this->update($user, $activity)) {
            return true;
        }

        return $user->hasAnyRoles(['Activities Staff']) && $activity->hasAssignedStaff($user);
    }

    public function manageAttendance(User $user, Activity $activity): bool
    {
        if ($this->update($user, $activity)) {
            return true;
        }

        return $user->hasAnyRoles(['Activities Staff']) && $activity->hasAssignedStaff($user);
    }

    public function manageEvents(User $user, Activity $activity): bool
    {
        if ($this->update($user, $activity)) {
            return true;
        }

        return $user->hasAnyRoles(['Activities Staff']) && $activity->hasAssignedStaff($user);
    }

    public function manageResults(User $user, Activity $activity): bool
    {
        if ($this->update($user, $activity)) {
            return true;
        }

        return $user->hasAnyRoles(['Activities Staff']) && $activity->hasAssignedStaff($user);
    }

    public function manageFees(User $user, Activity $activity): bool
    {
        return $this->update($user, $activity);
    }

    public function reopenAttendance(User $user, Activity $activity): bool
    {
        return $this->update($user, $activity);
    }
}
