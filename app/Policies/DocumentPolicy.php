<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DocumentShare;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\Document;

class DocumentPolicy {
    use HandlesAuthorization;

    /**
     * Roles with full administrative access to the document module.
     */
    public const ADMIN_ROLES = [
        'Administrator',
        'Documents Admin',
    ];

    /**
     * Roles that can approve/publish documents.
     */
    public const APPROVER_ROLES = [
        'Administrator',
        'Documents Admin',
        'HOD',
    ];

    public static function isAdmin(User $user): bool {
        return $user->hasAnyRoles(self::ADMIN_ROLES);
    }

    /**
     * Can user view document listings? (PRM-01: documents.view)
     */
    public function viewAny(User $user): bool {
        // All authenticated current staff can view document listings
        return $user->status === 'Current';
    }

    /**
     * Can user view a specific document?
     * Owner, shared users, admins, and internal visibility checks.
     */
    public function view(User $user, Document $document): bool {
        // Admin can view all
        if (static::isAdmin($user)) {
            return true;
        }
        // Owner can always view own documents
        if ($document->owner_id === $user->id) {
            return true;
        }
        // Published visibility checks
        if ($document->status === Document::STATUS_PUBLISHED) {
            if (in_array($document->visibility, [Document::VISIBILITY_INTERNAL, Document::VISIBILITY_PUBLIC], true)) {
                return true;
            }

            if (
                $document->visibility === Document::VISIBILITY_ROLES
                && $this->userMatchesPublishedRoles($user, $document)
            ) {
                return true;
            }
        }
        // Check shares — any active share grants view access (Phase 6)
        if ($this->hasActiveShare($user, $document)) {
            return true;
        }
        return false;
    }

    /**
     * Can user create documents? (PRM-02: documents.create)
     */
    public function create(User $user): bool {
        return $user->status === 'Current';
    }

    /**
     * Can user update a document? (PRM-03: documents.edit / documents.edit_any)
     */
    public function update(User $user, Document $document): bool {
        // Admin can edit any
        if (static::isAdmin($user)) {
            return true;
        }
        // Owner can edit own documents (if not locked)
        if ($document->owner_id === $user->id && !$document->is_locked) {
            return true;
        }
        // Users with edit or manage share permission can update
        $sharePermission = $this->getSharePermission($user, $document);
        if ($sharePermission && in_array($sharePermission, [DocumentShare::PERMISSION_EDIT, DocumentShare::PERMISSION_MANAGE])) {
            return !$document->is_locked;
        }
        return false;
    }

    /**
     * Can user delete a document? (PRM-04: documents.delete / documents.delete_any)
     */
    public function delete(User $user, Document $document): bool {
        // Admin can delete any
        if (static::isAdmin($user)) {
            return true;
        }
        // Owner can delete own documents (if not under legal hold)
        if ($document->owner_id === $user->id && !$document->legal_hold) {
            return true;
        }
        return false;
    }

    /**
     * Can user permanently delete (force delete)?
     * Only admins, and only if no legal hold.
     */
    public function forceDelete(User $user, Document $document): bool {
        return static::isAdmin($user) && !$document->legal_hold;
    }

    /**
     * Can user restore a soft-deleted document?
     */
    public function restore(User $user, Document $document): bool {
        if (static::isAdmin($user)) {
            return true;
        }
        return $document->owner_id === $user->id;
    }

    /**
     * Can user upload a new version of a document? (VER-08)
     *
     * Blocks version uploads when document is under review workflow.
     * Otherwise delegates to update() for standard authorization.
     */
    public function uploadVersion(User $user, Document $document): bool {
        if (!$document->supportsVersioning()) {
            return false;
        }

        // Block uploads during review workflow (VER-08)
        if (in_array($document->status, [Document::STATUS_PENDING_REVIEW, Document::STATUS_UNDER_REVIEW])) {
            return false;
        }

        return $this->update($user, $document);
    }

    /**
     * Can user publish or unpublish a document? (PUB-06)
     *
     * Only the document owner and admins can publish/unpublish.
     */
    public function publish(User $user, Document $document): bool {
        if (static::isAdmin($user)) {
            return true;
        }

        return $document->owner_id === $user->id;
    }

    /**
     * Can user review a document? (PRM-06: documents.approve)
     *
     * Must have an approver role and cannot review own documents (WFL-06).
     */
    public function review(User $user, Document $document): bool {
        // Must have approver role
        if (!$user->hasAnyRoles(self::APPROVER_ROLES)) {
            return false;
        }
        // Cannot review own documents (WFL-06)
        return $document->owner_id !== $user->id;
    }

    /**
     * Can user share a document? (PRM-05: documents.share)
     * Only owner, manage-level shared users, and admins.
     */
    public function share(User $user, Document $document): bool {
        // Admin can always share
        if (static::isAdmin($user)) {
            return true;
        }
        // Owner can always share
        if ($document->owner_id === $user->id) {
            return true;
        }
        // Users with manage permission can share
        $sharePermission = $this->getSharePermission($user, $document);
        return $sharePermission === DocumentShare::PERMISSION_MANAGE;
    }

    /**
     * Check if user has any active share on the document (any permission level).
     */
    private function hasActiveShare(User $user, Document $document): bool {
        $roleIdentifiers = $this->getRoleShareIdentifiers($user);

        // Direct user share
        $directShare = DocumentShare::where('document_id', $document->id)
            ->where('shareable_type', DocumentShare::TYPE_USER)
            ->where('shareable_id', (string) $user->id)
            ->where('is_active', true)
            ->whereNull('revoked_at')
            ->exists();

        if ($directShare) {
            return true;
        }

        // Role shares
        if (!empty($roleIdentifiers)) {
            $roleShare = DocumentShare::where('document_id', $document->id)
                ->where('shareable_type', DocumentShare::TYPE_ROLE)
                ->whereIn('shareable_id', $roleIdentifiers)
                ->where('is_active', true)
                ->whereNull('revoked_at')
                ->exists();

            if ($roleShare) {
                return true;
            }
        }

        // Department share
        if ($user->department) {
            $departmentShare = DocumentShare::where('document_id', $document->id)
                ->where('shareable_type', DocumentShare::TYPE_DEPARTMENT)
                ->where('shareable_id', (string) $user->department)
                ->where('is_active', true)
                ->whereNull('revoked_at')
                ->exists();

            if ($departmentShare) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the effective share permission level for a user on a document.
     * Returns null if no share, or the permission string.
     * Individual user share overrides role/department (SHR-06).
     */
    private function getSharePermission(User $user, Document $document): ?string {
        $roleIdentifiers = $this->getRoleShareIdentifiers($user);

        // Check individual user share first (SHR-06 override)
        $userShare = DocumentShare::where('document_id', $document->id)
            ->where('shareable_type', DocumentShare::TYPE_USER)
            ->where('shareable_id', (string) $user->id)
            ->where('is_active', true)
            ->whereNull('revoked_at')
            ->first();

        if ($userShare) {
            return $userShare->permission_level;
        }

        // Collect role and department shares, return highest
        $permissionLevels = [
            DocumentShare::PERMISSION_VIEW => 1,
            DocumentShare::PERMISSION_COMMENT => 2,
            DocumentShare::PERMISSION_EDIT => 3,
            DocumentShare::PERMISSION_MANAGE => 4,
        ];

        $highestLevel = 0;
        $highestPermission = null;

        // Role shares
        if (!empty($roleIdentifiers)) {
            $roleShares = DocumentShare::where('document_id', $document->id)
                ->where('shareable_type', DocumentShare::TYPE_ROLE)
                ->whereIn('shareable_id', $roleIdentifiers)
                ->where('is_active', true)
                ->whereNull('revoked_at')
                ->get();

            foreach ($roleShares as $share) {
                $level = $permissionLevels[$share->permission_level] ?? 0;
                if ($level > $highestLevel) {
                    $highestLevel = $level;
                    $highestPermission = $share->permission_level;
                }
            }
        }

        // Department share
        if ($user->department) {
            $deptShare = DocumentShare::where('document_id', $document->id)
                ->where('shareable_type', DocumentShare::TYPE_DEPARTMENT)
                ->where('shareable_id', (string) $user->department)
                ->where('is_active', true)
                ->whereNull('revoked_at')
                ->first();

            if ($deptShare) {
                $level = $permissionLevels[$deptShare->permission_level] ?? 0;
                if ($level > $highestLevel) {
                    $highestPermission = $deptShare->permission_level;
                }
            }
        }

        return $highestPermission;
    }

    /**
     * Return both role IDs and role names to support mixed legacy/new share data.
     *
     * @return array<int, string>
     */
    private function getRoleShareIdentifiers(User $user): array {
        static $cache = [];

        if (isset($cache[$user->id])) {
            return $cache[$user->id];
        }

        $roles = $user->roles()->select('roles.id', 'roles.name')->get();
        $ids = $roles->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        $names = $roles->pluck('name')->map(fn ($name) => (string) $name)->toArray();

        return $cache[$user->id] = array_values(array_unique(array_merge($ids, $names)));
    }

    /**
     * Check whether a user matches at least one configured published role.
     */
    private function userMatchesPublishedRoles(User $user, Document $document): bool {
        $publishedRoles = collect($document->published_roles ?? [])
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->map(fn ($value) => (string) $value)
            ->values();

        if ($publishedRoles->isEmpty()) {
            return false;
        }

        $roleIds = $user->roles()->pluck('roles.id')->map(fn ($id) => (string) $id);
        $roleNames = $user->roles()->pluck('roles.name')->map(fn ($name) => (string) $name);
        $userRoleIdentifiers = $roleIds->merge($roleNames)->unique()->values();

        return $publishedRoles->intersect($userRoleIdentifiers)->isNotEmpty();
    }
}
