<?php

namespace App\Services\Documents;

use App\Models\DocumentFolder;
use App\Models\DocumentFolderPermission;
use App\Models\User;
use App\Policies\DocumentPolicy;
use Illuminate\Database\Eloquent\Collection;

class FolderPermissionService {
    protected FolderService $folderService;

    public function __construct(FolderService $folderService) {
        $this->folderService = $folderService;
    }

    /**
     * Resolve the effective permission for a user on a folder by walking the ancestor chain.
     *
     * Priority: admin > owner > direct permission on folder > closest ancestor permission.
     * Uses "inherit with override" semantics: a direct permission on a closer folder
     * overrides any inherited permission from a parent.
     */
    public function getEffectiveFolderPermission(DocumentFolder $folder, User $user): ?string {
        // Admins always get manage
        if (DocumentPolicy::isAdmin($user)) {
            return DocumentFolderPermission::PERMISSION_MANAGE;
        }

        // Folder owner always gets manage
        if ($folder->owner_id === $user->id) {
            return DocumentFolderPermission::PERMISSION_MANAGE;
        }

        // Check direct permission on THIS folder first (overrides inherited)
        $directPermission = $this->resolveUserPermissionOnFolder($folder, $user);
        if ($directPermission !== null) {
            return $directPermission;
        }

        // Walk UP the ancestor chain — closest ancestor wins
        $ancestors = $this->folderService->getAncestors($folder);

        // Reverse to walk from closest parent to root
        $ancestors = $ancestors->reverse();

        foreach ($ancestors as $ancestor) {
            $ancestorPermission = $this->resolveUserPermissionOnFolder($ancestor, $user);
            if ($ancestorPermission !== null) {
                return $ancestorPermission;
            }
        }

        return null;
    }

    /**
     * Create or update a folder permission.
     *
     * Uses updateOrCreate on folder_id + permissionable_type + permissionable_id.
     * Authorization (admin-only) is checked in the controller, not here.
     */
    public function setFolderPermission(
        DocumentFolder $folder,
        string $type,
        string $id,
        string $permission,
        User $granter
    ): DocumentFolderPermission {
        return DocumentFolderPermission::updateOrCreate(
            [
                'folder_id' => $folder->id,
                'permissionable_type' => $type,
                'permissionable_id' => $id,
            ],
            [
                'permission_level' => $permission,
                'granted_by_user_id' => $granter->id,
            ]
        );
    }

    /**
     * Delete a specific folder permission entry.
     */
    public function removeFolderPermission(DocumentFolder $folder, string $type, string $id): void {
        DocumentFolderPermission::where('folder_id', $folder->id)
            ->where('permissionable_type', $type)
            ->where('permissionable_id', $id)
            ->delete();
    }

    /**
     * Get all direct (non-inherited) permissions for a folder.
     */
    public function getFolderPermissions(DocumentFolder $folder): Collection {
        return DocumentFolderPermission::where('folder_id', $folder->id)
            ->with('grantedBy:id,firstname,lastname')
            ->get();
    }

    /**
     * Get permissions inherited from ancestors (for display).
     *
     * Walks ancestors from root to closest parent, collecting permissions.
     * Excludes any that are overridden at a closer level (same type+id).
     */
    public function getInheritedPermissions(DocumentFolder $folder): Collection {
        $ancestors = $this->folderService->getAncestors($folder);

        if ($ancestors->isEmpty()) {
            return new Collection();
        }

        // Batch-load all ancestor permissions in a single query
        $ancestorIds = $ancestors->pluck('id')->all();
        $allPermissions = DocumentFolderPermission::whereIn('folder_id', $ancestorIds)
            ->with('grantedBy:id,firstname,lastname')
            ->get()
            ->groupBy('folder_id');

        // Walk from closest parent to root so closest overrides
        $inherited = [];
        $reversed = $ancestors->reverse();

        foreach ($reversed as $ancestor) {
            $permissions = $allPermissions->get($ancestor->id, collect());

            foreach ($permissions as $perm) {
                $key = $perm->permissionable_type . ':' . $perm->permissionable_id;
                if (!isset($inherited[$key])) {
                    $inherited[$key] = $perm;
                }
            }
        }

        // Exclude any that have a direct override on this folder
        $directKeys = DocumentFolderPermission::where('folder_id', $folder->id)
            ->get()
            ->map(fn($p) => $p->permissionable_type . ':' . $p->permissionable_id)
            ->toArray();

        foreach ($directKeys as $key) {
            unset($inherited[$key]);
        }

        return new Collection(array_values($inherited));
    }

    /**
     * When a new subfolder is created, no action needed.
     *
     * Permissions are resolved dynamically via ancestor walk, not stored redundantly.
     * This is a no-op method documenting the design decision.
     */
    public function propagateToNewChild(DocumentFolder $parent, DocumentFolder $child): void {
        // No-op: permissions are resolved dynamically via getEffectiveFolderPermission ancestor walk.
        // Child folders automatically inherit parent permissions at query time.
    }

    /**
     * Map permission string to numeric level for comparison.
     *
     * view=1, upload=2, edit=3, manage=4. Higher is more permissive.
     */
    public function permissionLevel(string $permission): int {
        return match ($permission) {
            DocumentFolderPermission::PERMISSION_VIEW => 1,
            DocumentFolderPermission::PERMISSION_UPLOAD => 2,
            DocumentFolderPermission::PERMISSION_EDIT => 3,
            DocumentFolderPermission::PERMISSION_MANAGE => 4,
            default => 0,
        };
    }

    /**
     * Determine whether a user may upload documents into the given folder.
     */
    public function canUploadToFolder(DocumentFolder $folder, User $user): bool {
        if ($user->can('update', $folder)) {
            return true;
        }

        $effectivePermission = $this->getEffectiveFolderPermission($folder, $user);
        if ($effectivePermission === null) {
            return false;
        }

        return $this->permissionLevel($effectivePermission)
            >= $this->permissionLevel(DocumentFolderPermission::PERMISSION_UPLOAD);
    }

    /**
     * Resolve the highest permission a user has on a specific folder (direct only).
     *
     * Checks user-type, role-type, and department-type permissions,
     * returning the highest permission level found.
     */
    protected function resolveUserPermissionOnFolder(DocumentFolder $folder, User $user): ?string {
        $permissions = DocumentFolderPermission::where('folder_id', $folder->id)
            ->forUser($user)
            ->get();

        if ($permissions->isEmpty()) {
            return null;
        }

        // Return the highest permission level
        return $permissions->sortByDesc(fn($p) => $this->permissionLevel($p->permission_level))
            ->first()
            ->permission_level;
    }
}
