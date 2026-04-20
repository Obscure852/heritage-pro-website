<?php

namespace App\Policies;

use App\Models\KlassSubject;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\Klass;
use App\Models\OptionalSubject;

class KlassSubjectPolicy{
    use HandlesAuthorization;

    private const MARKBOOK_ADMIN_ROLES = [
        'Administrator',
        'Assessment Admin',
        'Academic Admin',
        'HOD',
    ];

    public function __construct(){
        //
    }

    public function enterMarks(User $user, KlassSubject $klassSubject){
        $isAssignedTeacher = $user->id === $klassSubject->user_id || $user->id === $klassSubject->assistant_user_id;
        $hasAdminRole = $user->hasAnyRoles(self::MARKBOOK_ADMIN_ROLES);
        $isSupervisor = $this->isDirectSupervisor($user, [$klassSubject->user_id, $klassSubject->assistant_user_id]);

        return $isAssignedTeacher || $hasAdminRole || $isSupervisor;
    }

    public function assessOptions(User $user, OptionalSubject $optionalSubject){
        $isTeacher = $user->id === $optionalSubject->user_id;
        $isAssistant = $user->id === $optionalSubject->assistant_user_id;
        $hasAdminRole = $user->hasAnyRoles(self::MARKBOOK_ADMIN_ROLES);
        $isSupervisor = $this->isDirectSupervisor($user, [$optionalSubject->user_id, $optionalSubject->assistant_user_id]);

        return $isTeacher || $isAssistant || $hasAdminRole || $isSupervisor;
    }

    public function classTeacherAccess(User $user, Klass $klass){
        return $user->id === $klass->user_id;
    }

    private function isDirectSupervisor(User $user, array $teacherIds): bool
    {
        $teacherIds = array_values(array_filter($teacherIds));

        if (empty($teacherIds)) {
            return false;
        }

        return User::whereIn('id', $teacherIds)
            ->where('reporting_to', $user->id)
            ->exists();
    }

}
