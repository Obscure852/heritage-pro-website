<?php

namespace App\Policies;

use App\Models\DocumentFolder;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Gate;

class DocumentFolderPolicy {
    use HandlesAuthorization;

    /**
     * Roles with full administrative access to the document module.
     */
    public const ADMIN_ROLES = [
        'Administrator',
        'Documents Admin',
    ];

    /**
     * Check if user has admin-level document access.
     */
    public static function isAdmin(User $user): bool {
        return $user->hasAnyRoles(self::ADMIN_ROLES);
    }

    /**
     * Can the user view any folder listings?
     * Any authenticated user can browse folders.
     */
    public function viewAny(User $user): bool {
        return $user->status === 'Current';
    }

    /**
     * Can the user view a specific folder?
     * Owner, admin, or folder is marked internal/public.
     */
    public function view(User $user, DocumentFolder $folder): bool {
        // Admin can view all
        if (static::isAdmin($user)) {
            return true;
        }

        // Owner can view own folders
        if ($folder->owner_id === $user->id) {
            return true;
        }

        // Internal/public folders are visible to all authenticated staff.
        if (in_array($folder->visibility, [
            DocumentFolder::VISIBILITY_INTERNAL,
            DocumentFolder::VISIBILITY_PUBLIC,
        ], true)) {
            return true;
        }

        return false;
    }

    /**
     * Can the user create folders?
     * Any authenticated user can create personal folders.
     * Institutional folders require the manage-institutional-folders gate.
     */
    public function create(User $user): bool {
        return $user->status === 'Current';
    }

    /**
     * Can the user update a specific folder?
     * Owner or admin. Institutional folders require manage-institutional-folders gate.
     */
    public function update(User $user, DocumentFolder $folder): bool {
        // Admin can update any
        if (static::isAdmin($user)) {
            return true;
        }

        // Institutional folders require special gate
        if ($folder->repository_type === DocumentFolder::REPOSITORY_INSTITUTIONAL) {
            return Gate::allows('manage-institutional-folders');
        }

        // Owner can update own folders
        if ($folder->owner_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Can the user delete a specific folder?
     * Same rules as update.
     */
    public function delete(User $user, DocumentFolder $folder): bool {
        return $this->update($user, $folder);
    }

    /**
     * Can the user manage institutional folders?
     * Delegates to the manage-institutional-folders gate.
     */
    public function manageInstitutional(User $user): bool {
        return Gate::allows('manage-institutional-folders');
    }
}
