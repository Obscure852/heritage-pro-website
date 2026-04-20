<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;
use App\Models\Schemes\SchemeOfWork;
use Illuminate\Auth\Access\HandlesAuthorization;

class SchemeOfWorkPolicy {
    use HandlesAuthorization;

    /**
     * Roles with full administrative access to the schemes module.
     */
    public const ADMIN_ROLES = [
        'Administrator',
        'Academic Admin',
    ];

    public static function isAdmin(User $user): bool {
        return $user->hasAnyRoles(self::ADMIN_ROLES);
    }

    /**
     * Can user view any scheme listing?
     * Teachers, HODs, Academic Admins, and Administrators.
     */
    public function viewAny(User $user): bool {
        return $user->hasAnyRoles(['Administrator', 'Academic Admin', 'HOD', 'Teacher']);
    }

    /**
     * Can user view a specific scheme?
     * Owners can always view their own schemes. Supervisors can view subordinate
     * schemes via reporting lines. HOD/admin roles can view all teacher schemes.
     */
    public function view(User $user, SchemeOfWork $scheme): bool {
        return SchemeOfWork::canUserView($user, $scheme);
    }

    /**
     * Can user create a scheme?
     * HODs, Academic Admins, and Administrators only.
     * Teachers receive schemes via standard scheme distribution.
     */
    public function create(User $user): bool {
        return $user->hasAnyRoles(['HOD', 'Academic Admin', 'Administrator']);
    }

    /**
     * Can user update a scheme?
     * Admins always. Owner only while the scheme remains in an editable state.
     */
    public function update(User $user, SchemeOfWork $scheme): bool {
        if (static::isAdmin($user)) {
            return true;
        }

        if ($scheme->teacher_id === $user->id && in_array($scheme->status, ['draft', 'revision_required', 'approved'], true)) {
            return true;
        }

        return false;
    }

    /**
     * Can user delete a scheme?
     * Admins always. Owner only if status is draft.
     */
    public function delete(User $user, SchemeOfWork $scheme): bool {
        if (static::isAdmin($user)) {
            return true;
        }

        return $scheme->teacher_id === $user->id && $scheme->status === 'draft';
    }

    /**
     * Can user clone a scheme into a new term?
     * Admins always. Owners only for their own schemes.
     */
    public function clone(User $user, SchemeOfWork $scheme): bool
    {
        if (static::isAdmin($user)) {
            return true;
        }

        return $scheme->teacher_id === $user->id;
    }

    /**
     * Can user submit a scheme for review?
     * Owner when status is draft or revision_required.
     * Admins can also submit on behalf of a teacher.
     */
    public function submit(User $user, SchemeOfWork $scheme): bool {
        if (!in_array($scheme->status, ['draft', 'revision_required'], true)) {
            return false;
        }

        if (static::isAdmin($user)) {
            return true;
        }

        return $scheme->teacher_id === $user->id;
    }

    /**
     * Can user perform a supervisor review on a scheme?
     * Admins always. Must be the teacher's direct supervisor and scheme must be submitted.
     */
    public function supervisorReview(User $user, SchemeOfWork $scheme): bool {
        if ($scheme->status !== 'submitted') {
            return false;
        }

        if (static::isAdmin($user)) {
            return true;
        }

        return $scheme->teacher && (int) $scheme->teacher->reporting_to === $user->id;
    }

    /**
     * Can user review (approve/reject) a scheme?
     * Admins always. HOD or assistant for the scheme's department.
     */
    public function review(User $user, SchemeOfWork $scheme): bool {
        if (!in_array($scheme->status, ['submitted', 'supervisor_reviewed', 'under_review'], true)) {
            return false;
        }

        if (static::isAdmin($user)) {
            return true;
        }

        return $this->isHodForScheme($user, $scheme);
    }

    /**
     * Can user publish or unpublish an approved scheme as the reference scheme
     * for its subject/grade/term context?
     */
    public function publishReference(User $user, SchemeOfWork $scheme): bool
    {
        if ($scheme->status !== 'approved') {
            return false;
        }

        if (static::isAdmin($user)) {
            return true;
        }

        return $this->isHodForScheme($user, $scheme);
    }

    /**
     * Resolve whether a user is the HOD or assistant HOD for the scheme's department.
     *
     * Chain: SchemeOfWork -> gradeSubject accessor -> department_id -> Department -> department_head / assistant.
     * Null-safe at every step — returns false (not 500) if any link in the chain is missing.
     */
    private function isHodForScheme(User $user, SchemeOfWork $scheme): bool {
        // Resolve GradeSubject via the XOR accessor
        $gradeSubject = $scheme->gradeSubject;

        if ($gradeSubject === null) {
            return false;
        }

        if (is_null($gradeSubject->department_id)) {
            return false;
        }

        static $departmentCache = [];
        $deptId = $gradeSubject->department_id;

        if (!array_key_exists($deptId, $departmentCache)) {
            $departmentCache[$deptId] = Department::find($deptId);
        }

        $department = $departmentCache[$deptId];

        if ($department === null) {
            return false;
        }

        // Both department_head and assistant have HOD review power (per locked decision)
        if (!is_null($department->department_head) && $department->department_head === $user->id) {
            return true;
        }

        if (!is_null($department->assistant) && $department->assistant === $user->id) {
            return true;
        }

        return false;
    }
}
