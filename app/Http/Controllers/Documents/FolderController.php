<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Http\Requests\Documents\StoreFolderRequest;
use App\Http\Requests\Documents\UpdateFolderRequest;
use App\Models\Document;
use App\Models\DocumentAudit;
use App\Models\DocumentFolder;
use App\Models\DocumentFolderPermission;
use App\Models\User;
use App\Policies\DocumentPolicy;
use App\Services\Documents\FolderPermissionService;
use App\Services\Documents\FolderService;
use App\Services\Documents\QuotaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class FolderController extends Controller {
    protected FolderService $folderService;
    protected FolderPermissionService $folderPermissionService;
    protected QuotaService $quotaService;

    public function __construct(FolderService $folderService, FolderPermissionService $folderPermissionService, QuotaService $quotaService) {
        $this->folderService = $folderService;
        $this->folderPermissionService = $folderPermissionService;
        $this->quotaService = $quotaService;
    }

    /**
     * Create a new folder.
     *
     * Validates input via StoreFolderRequest, checks authorization,
     * creates the folder, and computes the materialized path.
     */
    public function store(StoreFolderRequest $request): JsonResponse {
        $this->authorize('create', DocumentFolder::class);

        $validated = $request->validated();
        $user = $request->user();

        $parentFolder = null;
        $repositoryType = $validated['repository_type'] ?? DocumentFolder::REPOSITORY_PERSONAL;
        $parentId = $validated['parent_id'] ?? null;
        $accessScope = $validated['access_scope'] ?? 'private';
        $visibility = DocumentFolder::VISIBILITY_PRIVATE;

        if ($parentId !== null) {
            $parentFolder = DocumentFolder::findOrFail($parentId);

            if (!$user->can('view', $parentFolder)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to create folders in this location.',
                ], 403);
            }

            // Public folders are view-only for non-owners/admins in this scope.
            if (!$user->can('update', $parentFolder)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to create folders in this location.',
                ], 403);
            }

            $repositoryType = $parentFolder->repository_type;
            $visibility = $parentFolder->visibility;
        } else {
            if ($repositoryType === DocumentFolder::REPOSITORY_INSTITUTIONAL && !Gate::allows('manage-institutional-folders')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to create institutional folders.',
                ], 403);
            }

            $visibility = $repositoryType === DocumentFolder::REPOSITORY_PERSONAL
                ? ($accessScope === 'public' ? DocumentFolder::VISIBILITY_INTERNAL : DocumentFolder::VISIBILITY_PRIVATE)
                : DocumentFolder::VISIBILITY_INTERNAL;
        }

        try {
            $folder = DB::transaction(function () use ($validated, $user, $repositoryType, $visibility, $parentId) {
                $folder = DocumentFolder::create([
                    'name' => $validated['name'],
                    'description' => $validated['description'] ?? null,
                    'parent_id' => $parentId,
                    'owner_id' => $user->id,
                    'repository_type' => $repositoryType,
                    'visibility' => $visibility,
                    'depth' => 0,
                ]);

                $this->folderService->updatePathAndDepth($folder);

                return $folder;
            });

            return response()->json([
                'success' => true,
                'message' => 'Folder created successfully.',
                'folder' => [
                    'id' => $folder->id,
                    'name' => $folder->name,
                    'ulid' => $folder->ulid,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('FolderController: Failed to create folder', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create folder. Please try again.',
            ], 500);
        }
    }

    /**
     * Update a folder's name and description.
     */
    public function update(UpdateFolderRequest $request, DocumentFolder $folder): JsonResponse {
        $this->authorize('update', $folder);

        $validated = $request->validated();

        $folder->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? $folder->description,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Folder updated successfully.',
            'folder' => [
                'id' => $folder->id,
                'name' => $folder->name,
                'ulid' => $folder->ulid,
            ],
        ]);
    }

    /**
     * Update a folder access scope and cascade to all descendants.
     */
    public function updateAccess(Request $request, DocumentFolder $folder): JsonResponse {
        if (!$request->user()->can('update', $folder)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update this folder.',
            ], 403);
        }

        if ($folder->repository_type !== DocumentFolder::REPOSITORY_PERSONAL) {
            return response()->json([
                'success' => false,
                'message' => 'Access scope can only be changed for personal folders.',
            ], 422);
        }

        $validated = $request->validate([
            'access_scope' => ['required', 'string', Rule::in(['private', 'public'])],
        ]);

        $targetVisibility = $validated['access_scope'] === 'public'
            ? DocumentFolder::VISIBILITY_INTERNAL
            : DocumentFolder::VISIBILITY_PRIVATE;

        try {
            DB::transaction(function () use ($folder, $targetVisibility) {
                $this->folderService->updateSubtreeVisibility($folder, $targetVisibility);
            });

            return response()->json([
                'success' => true,
                'message' => $validated['access_scope'] === 'public'
                    ? 'Folder is now public to all staff.'
                    : 'Folder is now private.',
                'visibility' => $targetVisibility,
            ]);
        } catch (\Throwable $e) {
            Log::error('FolderController: Failed to update folder access scope', [
                'folder_id' => $folder->id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update folder access scope. Please try again.',
            ], 500);
        }
    }

    /**
     * Soft-delete a folder and cascade to children and documents.
     */
    public function destroy(DocumentFolder $folder, Request $request): JsonResponse {
        $this->authorize('delete', $folder);

        try {
            $folderName = $folder->name;
            $affectedOwnerIds = [];

            DB::transaction(function () use ($folder, &$affectedOwnerIds) {
                $folderIds = $folder->path
                    ? DocumentFolder::where('path', $folder->path)
                        ->orWhere('path', 'like', $folder->path . '/%')
                        ->pluck('id')
                    : collect([$folder->id]);
                $affectedOwnerIds = Document::whereIn('folder_id', $folderIds)
                    ->pluck('owner_id')
                    ->unique()
                    ->values()
                    ->all();

                $this->folderService->cascadeSoftDelete($folder);
            });

            if (!empty($affectedOwnerIds)) {
                User::whereIn('id', $affectedOwnerIds)
                    ->get()
                    ->each(fn (User $owner) => $this->quotaService->recalculate($owner));
            }

            return response()->json([
                'success' => true,
                'message' => "Folder '{$folderName}' and its contents have been moved to trash.",
            ]);
        } catch (\Exception $e) {
            Log::error('FolderController: Failed to delete folder', [
                'folder_id' => $folder->id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete folder. Please try again.',
            ], 500);
        }
    }

    /**
     * Bulk soft-delete folders selected from the document listing.
     */
    public function bulkDestroy(Request $request): JsonResponse {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => [
                'integer',
                Rule::exists('document_folders', 'id')->whereNull('deleted_at'),
            ],
        ]);

        $deleted = 0;
        $unauthorized = 0;
        $affectedOwnerIds = [];
        $folders = DocumentFolder::whereIn('id', $validated['ids'])
            ->orderBy('depth')
            ->get();
        $foldersToDelete = collect();

        foreach ($folders as $folder) {
            $coveredBySelectedAncestor = $foldersToDelete->contains(
                fn (DocumentFolder $selectedFolder) => $this->folderService->isDescendantOf($folder, $selectedFolder)
            );

            if ($coveredBySelectedAncestor) {
                continue;
            }

            if (!$request->user()->can('delete', $folder)) {
                $unauthorized++;
                continue;
            }

            $foldersToDelete->push($folder);
        }

        try {
            DB::transaction(function () use ($foldersToDelete, &$deleted, &$affectedOwnerIds) {
                foreach ($foldersToDelete as $folder) {
                    $folderIds = $folder->path
                        ? DocumentFolder::where('path', $folder->path)
                            ->orWhere('path', 'like', $folder->path . '/%')
                            ->pluck('id')
                        : collect([$folder->id]);

                    $affectedOwnerIds = array_merge(
                        $affectedOwnerIds,
                        Document::whereIn('folder_id', $folderIds)->pluck('owner_id')->all()
                    );

                    $this->folderService->cascadeSoftDelete($folder);
                    $deleted++;
                }
            });

            if (!empty($affectedOwnerIds)) {
                User::whereIn('id', array_values(array_unique($affectedOwnerIds)))
                    ->get()
                    ->each(fn (User $owner) => $this->quotaService->recalculate($owner));
            }

            $message = "{$deleted} folder(s) moved to trash.";
            if ($unauthorized > 0) {
                $message .= " {$unauthorized} folder(s) skipped (unauthorized).";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'deleted' => $deleted,
                'unauthorized' => $unauthorized,
            ]);
        } catch (\Throwable $e) {
            Log::error('FolderController: Failed to bulk delete folders', [
                'ids' => $validated['ids'],
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete selected folders. Please try again.',
            ], 500);
        }
    }

    /**
     * Move documents or folders to a target folder.
     *
     * Supports bulk moves: accepts an array of IDs and a target folder.
     */
    public function moveItems(Request $request): JsonResponse {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:document,folder'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            'target_folder_id' => ['nullable', 'integer', 'exists:document_folders,id'],
        ]);

        $targetFolderId = $validated['target_folder_id'] ?? null;
        $movedCount = 0;
        $unauthorizedCount = 0;
        $affectedOwnerIds = [];
        $targetFolder = $targetFolderId ? DocumentFolder::findOrFail($targetFolderId) : null;
        if ($targetFolder && !$this->canMoveIntoTarget($request->user(), $targetFolder)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to move items into the selected folder.',
            ], 403);
        }

        try {
            DB::transaction(function () use (
                $validated,
                $targetFolderId,
                $targetFolder,
                $request,
                &$movedCount,
                &$unauthorizedCount,
                &$affectedOwnerIds
            ) {
                if ($validated['type'] === 'document') {
                    // Get old folder IDs for document count updates
                    $documents = Document::whereIn('id', $validated['ids'])
                        ->select('id', 'folder_id', 'owner_id')
                        ->get();

                    $movable = $documents->filter(function (Document $document) use ($request) {
                        return $request->user()->can('update', $document);
                    });
                    $unauthorizedCount += ($documents->count() - $movable->count());

                    if ($movable->isEmpty()) {
                        return;
                    }

                    // Group by old folder for count decrements
                    $oldFolderCounts = $movable->groupBy('folder_id')
                        ->map(fn($group) => $group->count());

                    // Move documents to target folder
                    Document::whereIn('id', $movable->pluck('id'))->update(['folder_id' => $targetFolderId]);
                    $movedCount += $movable->count();
                    $affectedOwnerIds = array_merge($affectedOwnerIds, $movable->pluck('owner_id')->all());

                    // Decrement old folder counts
                    foreach ($oldFolderCounts as $folderId => $count) {
                        if ($folderId === null || $folderId === '') {
                            continue;
                        }

                        $this->folderService->decrementDocumentCount((int) $folderId, $count);
                    }

                    // Increment new folder count
                    if ($targetFolderId !== null) {
                        $this->folderService->incrementDocumentCount($targetFolderId, $movable->count());
                    }
                } else {
                    // Move folders
                    $folders = DocumentFolder::whereIn('id', $validated['ids'])->get();

                    foreach ($folders as $folder) {
                        if (!$request->user()->can('update', $folder)) {
                            $unauthorizedCount++;
                            continue;
                        }

                        if ($this->folderService->canMoveTo($folder, $targetFolderId)) {
                            $this->folderService->moveFolder($folder, $targetFolderId);
                            // Folder permissions auto-inherited from new parent via
                            // FolderPermissionService::getEffectiveFolderPermission ancestor walk
                            $movedCount++;
                        }
                    }
                }
            });

            if (!empty($affectedOwnerIds)) {
                User::whereIn('id', array_values(array_unique($affectedOwnerIds)))
                    ->get()
                    ->each(fn (User $owner) => $this->quotaService->recalculate($owner));
            }

            $message = "{$movedCount} item(s) moved successfully.";
            if ($unauthorizedCount > 0) {
                $message .= " {$unauthorizedCount} item(s) skipped (unauthorized).";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'moved' => $movedCount,
                'unauthorized' => $unauthorizedCount,
            ]);
        } catch (\Exception $e) {
            Log::error('FolderController: Failed to move items', [
                'type' => $validated['type'],
                'ids' => $validated['ids'],
                'target' => $targetFolderId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to move items. Please try again.',
            ], 500);
        }
    }

    /**
     * Return the full folder tree as JSON for use by move modals and sidebar.
     */
    public function tree(Request $request): JsonResponse {
        $this->authorize('viewAny', DocumentFolder::class);

        $tree = $this->folderService->buildFolderTree($request->user());

        return response()->json([
            'success' => true,
            'tree' => $tree,
        ]);
    }

    // ==================== Folder Permission Endpoints ====================

    /**
     * Get direct and inherited permissions for a folder.
     *
     * Returns both sets with is_inherited flag for UI display.
     * Only accessible by admins or the folder owner.
     */
    public function getFolderPermissions(DocumentFolder $folder): JsonResponse {
        $user = request()->user();

        // Only admin or folder owner can view permissions
        if (!DocumentPolicy::isAdmin($user) && $folder->owner_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view folder permissions.',
            ], 403);
        }

        $direct = $this->folderPermissionService->getFolderPermissions($folder)
            ->map(fn(DocumentFolderPermission $p) => [
                'type' => $p->permissionable_type,
                'id' => $p->permissionable_id,
                'permission' => $p->permission_level,
                'granted_by' => $p->grantedBy ? $p->grantedBy->full_name : null,
                'is_inherited' => false,
            ]);

        $inherited = $this->folderPermissionService->getInheritedPermissions($folder)
            ->map(fn(DocumentFolderPermission $p) => [
                'type' => $p->permissionable_type,
                'id' => $p->permissionable_id,
                'permission' => $p->permission_level,
                'granted_by' => $p->grantedBy ? $p->grantedBy->full_name : null,
                'is_inherited' => true,
            ]);

        return response()->json([
            'success' => true,
            'direct' => $direct->values(),
            'inherited' => $inherited->values(),
        ]);
    }

    /**
     * Set or update a permission on a folder.
     *
     * Only administrators can manage folder permissions.
     */
    public function setFolderPermission(Request $request, DocumentFolder $folder): JsonResponse {
        Gate::authorize('manage-institutional-folders');

        $validated = $request->validate([
            'permissionable_type' => ['required', 'string', 'in:user,role,department'],
            'permissionable_id' => ['required', 'string'],
            'permission' => ['required', 'string', 'in:view,upload,edit,manage'],
        ]);

        try {
            $permission = $this->folderPermissionService->setFolderPermission(
                $folder,
                $validated['permissionable_type'],
                $validated['permissionable_id'],
                $validated['permission'],
                $request->user()
            );

            return response()->json([
                'success' => true,
                'message' => 'Folder permission updated successfully.',
                'permission' => [
                    'type' => $permission->permissionable_type,
                    'id' => $permission->permissionable_id,
                    'permission' => $permission->permission_level,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('FolderController: Failed to set folder permission', [
                'folder_id' => $folder->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to set folder permission.',
            ], 500);
        }
    }

    /**
     * Remove a permission from a folder.
     *
     * Only administrators can remove folder permissions.
     */
    public function removeFolderPermission(Request $request, DocumentFolder $folder): JsonResponse {
        Gate::authorize('manage-institutional-folders');

        $validated = $request->validate([
            'permissionable_type' => ['required', 'string', 'in:user,role,department'],
            'permissionable_id' => ['required', 'string'],
        ]);

        try {
            $this->folderPermissionService->removeFolderPermission(
                $folder,
                $validated['permissionable_type'],
                $validated['permissionable_id']
            );

            return response()->json([
                'success' => true,
                'message' => 'Folder permission removed successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('FolderController: Failed to remove folder permission', [
                'folder_id' => $folder->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove folder permission.',
            ], 500);
        }
    }

    /**
     * Check whether the target folder accepts item moves from this user.
     */
    private function canMoveIntoTarget(User $user, ?DocumentFolder $targetFolder): bool {
        if ($targetFolder === null) {
            return true;
        }

        return $this->folderPermissionService->canUploadToFolder($targetFolder, $user);
    }
}
