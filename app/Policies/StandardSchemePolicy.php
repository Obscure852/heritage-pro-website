<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;
use App\Models\Schemes\StandardScheme;
use Illuminate\Auth\Access\HandlesAuthorization;

class StandardSchemePolicy {
    use HandlesAuthorization;

    private const ADMIN_ROLES = [
        'Administrator',
        'Academic Admin',
    ];

    private const MANAGE_ROLES = [
        'Administrator',
        'Academic Admin',
        'HOD',
        'Scheme Admin',
    ];

    private const VIEW_ROLES = [
        'Administrator',
        'Academic Admin',
        'HOD',
        'Scheme Admin',
        'Scheme View',
    ];

    /**
     * Can user view the standard scheme listing?
     */
    public function viewAny(User $user): bool {
        return $user->hasAnyRoles(self::VIEW_ROLES);
    }

    /**
     * Can user view a specific standard scheme?
     * Users with view roles, or contributors to this scheme.
     */
    public function view(User $user, StandardScheme $scheme): bool {
        if ($user->hasAnyRoles(self::VIEW_ROLES)) {
            return true;
        }

        return $scheme->contributors()->where('user_id', $user->id)->exists();
    }

    /**
     * Can user create a standard scheme?
     */
    public function create(User $user): bool {
        return $user->hasAnyRoles(self::MANAGE_ROLES);
    }

    /**
     * Can user copy a standard scheme into another term?
     */
    public function clone(User $user, StandardScheme $scheme): bool {
        if ($user->hasAnyRoles(self::ADMIN_ROLES) || $user->hasAnyRoles(['Scheme Admin'])) {
            return true;
        }

        return $user->hasAnyRoles(['HOD']) && $this->isHodForStandardScheme($user, $scheme);
    }

    /**
     * Can user update a standard scheme?
     * Must be in draft or revision_required status.
     */
    public function update(User $user, StandardScheme $scheme): bool {
        if (!$scheme->isEditable()) {
            return false;
        }

        if ($user->hasAnyRoles(self::ADMIN_ROLES) || $user->hasAnyRoles(['Scheme Admin'])) {
            return true;
        }

        if ($user->hasAnyRoles(['HOD']) && $this->isHodForStandardScheme($user, $scheme)) {
            return true;
        }

        // Panel lead can edit
        return $scheme->panel_lead_id === $user->id;
    }

    /**
     * Can user delete a standard scheme?
     * Only when status is draft.
     */
    public function delete(User $user, StandardScheme $scheme): bool {
        if ($scheme->status !== 'draft') {
            return false;
        }

        if ($user->hasAnyRoles(self::ADMIN_ROLES) || $user->hasAnyRoles(['Scheme Admin'])) {
            return true;
        }

        return false;
    }

    /**
     * Can user submit a standard scheme for review?
     */
    public function submit(User $user, StandardScheme $scheme): bool {
        if (!in_array($scheme->status, ['draft', 'revision_required'], true)) {
            return false;
        }

        if ($user->hasAnyRoles(self::ADMIN_ROLES) || $user->hasAnyRoles(['Scheme Admin'])) {
            return true;
        }

        if ($user->hasAnyRoles(['HOD']) && $this->isHodForStandardScheme($user, $scheme)) {
            return true;
        }

        return $scheme->panel_lead_id === $user->id;
    }

    /**
     * Can user review (approve/return) a standard scheme?
     */
    public function review(User $user, StandardScheme $scheme): bool {
        if (!in_array($scheme->status, ['submitted', 'under_review'], true)) {
            return false;
        }

        if ($user->hasAnyRoles(self::ADMIN_ROLES) || $user->hasAnyRoles(['Scheme Admin'])) {
            return true;
        }

        return $this->isHodForStandardScheme($user, $scheme);
    }

    /**
     * Can user publish a standard scheme?
     * Must be approved.
     */
    public function publish(User $user, StandardScheme $scheme): bool {
        return $user->hasAnyRoles(self::MANAGE_ROLES);
    }

    /**
     * Can user unpublish a standard scheme?
     */
    public function unpublish(User $user, StandardScheme $scheme): bool {
        if (!$scheme->isPublished()) {
            return false;
        }

        return $user->hasAnyRoles(self::MANAGE_ROLES);
    }

    /**
     * Check if user is HOD or assistant HOD for the standard scheme's department.
     */
    private function isHodForStandardScheme(User $user, StandardScheme $scheme): bool {
        $department = Department::find($scheme->department_id);

        if (!$department) {
            return false;
        }

        if (!is_null($department->department_head) && $department->department_head === $user->id) {
            return true;
        }

        if (!is_null($department->assistant) && $department->assistant === $user->id) {
            return true;
        }

        return false;
    }
}
