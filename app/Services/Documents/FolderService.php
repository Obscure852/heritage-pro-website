<?php

namespace App\Services\Documents;

use App\Models\Document;
use App\Models\DocumentFolder;
use App\Models\User;
use App\Policies\DocumentFolderPolicy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class FolderService {
    /**
     * Ensure a user has exactly one personal root folder.
     *
     * User-row locking serializes concurrent provisioning attempts for the same
     * account, which avoids duplicate "My Documents" roots during setup.
     */
    public function ensurePersonalRootFolder(User $user): DocumentFolder {
        return DB::transaction(function () use ($user) {
            User::query()
                ->whereKey($user->id)
                ->lockForUpdate()
                ->firstOrFail();

            $folder = DocumentFolder::query()
                ->where('owner_id', $user->id)
                ->where('name', 'My Documents')
                ->where('repository_type', DocumentFolder::REPOSITORY_PERSONAL)
                ->whereNull('parent_id')
                ->orderBy('id')
                ->first();

            if ($folder instanceof DocumentFolder) {
                if ($folder->path !== '/' . $folder->id || $folder->depth !== 0) {
                    $this->updatePathAndDepth($folder);
                }

                return $folder->fresh();
            }

            $folder = DocumentFolder::create([
                'name' => 'My Documents',
                'parent_id' => null,
                'owner_id' => $user->id,
                'repository_type' => DocumentFolder::REPOSITORY_PERSONAL,
                'visibility' => DocumentFolder::VISIBILITY_PRIVATE,
                'depth' => 0,
            ]);

            $this->updatePathAndDepth($folder);

            return $folder->fresh();
        });
    }

    /**
     * Compute and save the materialized path and depth for a folder.
     *
     * Root folders get path "/{id}" and depth 0.
     * Child folders get path "{parentPath}/{id}" and depth = parent depth + 1.
     */
    public function updatePathAndDepth(DocumentFolder $folder): void {
        if ($folder->parent_id === null) {
            $folder->path = '/' . $folder->id;
            $folder->depth = 0;
        } else {
            $parent = DocumentFolder::select('id', 'path', 'depth')->findOrFail($folder->parent_id);
            $folder->path = $parent->path . '/' . $folder->id;
            $folder->depth = $parent->depth + 1;
        }

        $folder->save();
    }

    /**
     * Update materialized paths for all descendants when a folder is moved.
     *
     * Uses a single UPDATE with REPLACE to rewrite path prefixes and recalculates
     * depth based on the number of path segments.
     */
    public function updateDescendantPaths(DocumentFolder $folder, string $oldPath, string $newPath): void {
        $descendants = DocumentFolder::where('path', 'like', $oldPath . '/%')->get();

        foreach ($descendants as $descendant) {
            $descendant->path = str_replace($oldPath, $newPath, $descendant->path);
            // Depth = number of '/' segments minus 1 (leading slash creates empty first segment)
            $descendant->depth = substr_count($descendant->path, '/') - 1;
            $descendant->save();
        }
    }

    /**
     * Get all ancestor folders from the materialized path.
     *
     * Parses the path string, extracts ancestor IDs, and returns them
     * ordered by their position in the hierarchy (root first).
     */
    public function getAncestors(DocumentFolder $folder): Collection {
        if ($folder->path === null || $folder->depth === 0) {
            return new Collection();
        }

        $segments = array_filter(explode('/', $folder->path));
        // Remove the last segment (the folder itself)
        array_pop($segments);

        if (empty($segments)) {
            return new Collection();
        }

        $ancestorIds = array_map('intval', $segments);

        return DocumentFolder::whereIn('id', $ancestorIds)
            ->orderByRaw($this->orderByIds($ancestorIds))
            ->get();
    }

    /**
     * Get all ancestor folders including the folder itself.
     */
    public function getAncestorsWithSelf(DocumentFolder $folder): Collection {
        if ($folder->path === null) {
            return new Collection([$folder]);
        }

        $segments = array_filter(explode('/', $folder->path));
        $allIds = array_map('intval', $segments);

        if (empty($allIds)) {
            return new Collection([$folder]);
        }

        return DocumentFolder::whereIn('id', $allIds)
            ->orderByRaw($this->orderByIds($allIds))
            ->get();
    }

    /**
     * Check if a folder is a descendant of another folder.
     */
    public function isDescendantOf(DocumentFolder $potentialDescendant, DocumentFolder $ancestor): bool {
        if ($potentialDescendant->path === null || $ancestor->path === null) {
            return false;
        }

        return str_starts_with($potentialDescendant->path . '/', $ancestor->path . '/');
    }

    /**
     * Validate whether a folder can be moved to a target parent.
     *
     * Prevents: moving to self, moving to own descendant (circular), target not found.
     * Allows: moving to root (null target).
     */
    public function canMoveTo(DocumentFolder $folder, ?int $targetParentId): bool {
        // Moving to root is always valid
        if ($targetParentId === null) {
            return true;
        }

        // Cannot move to self
        if ($targetParentId === $folder->id) {
            return false;
        }

        // Target must exist
        $target = DocumentFolder::find($targetParentId);
        if ($target === null) {
            return false;
        }

        // Cannot move to own descendant (circular reference)
        if ($this->isDescendantOf($target, $folder)) {
            return false;
        }

        return true;
    }

    /**
     * Move a folder to a new parent, updating all materialized paths.
     *
     * Wrapped in a transaction for atomicity.
     */
    public function moveFolder(DocumentFolder $folder, ?int $newParentId): void {
        DB::transaction(function () use ($folder, $newParentId) {
            $oldPath = $folder->path;
            $targetParent = null;

            if ($newParentId !== null) {
                $targetParent = DocumentFolder::select('id', 'repository_type', 'visibility')
                    ->findOrFail($newParentId);
            }

            $folder->parent_id = $newParentId;

            if ($targetParent !== null) {
                $folder->repository_type = $targetParent->repository_type;
                $folder->visibility = $targetParent->visibility;
            }

            $folder->save();

            $this->updatePathAndDepth($folder);

            $newPath = $folder->path;

            if ($oldPath !== $newPath) {
                $this->updateDescendantPaths($folder, $oldPath, $newPath);
            }

            if ($targetParent !== null) {
                $this->updateSubtreeRepositoryAndVisibility(
                    $folder,
                    $folder->repository_type,
                    $folder->visibility
                );
            }
        });
    }

    /**
     * Update visibility for a folder subtree (folder + descendants).
     */
    public function updateSubtreeVisibility(DocumentFolder $folder, string $visibility): void {
        if ($folder->path === null) {
            return;
        }

        DocumentFolder::where(function ($query) use ($folder) {
            $query->where('path', $folder->path)
                ->orWhere('path', 'like', $folder->path . '/%');
        })->update([
            'visibility' => $visibility,
            'updated_at' => now(),
        ]);
    }

    /**
     * Update repository type and visibility for a folder subtree (folder + descendants).
     */
    public function updateSubtreeRepositoryAndVisibility(
        DocumentFolder $folder,
        string $repositoryType,
        string $visibility
    ): void {
        if ($folder->path === null) {
            return;
        }

        DocumentFolder::where(function ($query) use ($folder) {
            $query->where('path', $folder->path)
                ->orWhere('path', 'like', $folder->path . '/%');
        })->update([
            'repository_type' => $repositoryType,
            'visibility' => $visibility,
            'updated_at' => now(),
        ]);
    }

    /**
     * Recursively soft-delete a folder and all its children and documents.
     *
     * Uses depth-first traversal within a transaction. Updates parent folder's
     * document_count after deletion.
     */
    public function cascadeSoftDelete(DocumentFolder $folder): void {
        DB::transaction(function () use ($folder) {
            // Depth-first: soft-delete all children recursively
            $children = DocumentFolder::where('parent_id', $folder->id)->get();
            foreach ($children as $child) {
                $this->cascadeSoftDelete($child);
            }

            // Soft-delete all documents in this folder
            $documentCount = Document::where('folder_id', $folder->id)->count();
            Document::where('folder_id', $folder->id)->delete();

            // Soft-delete the folder itself
            $folder->delete();

            // Update parent folder's document_count if applicable
            if ($folder->parent_id !== null) {
                $this->decrementDocumentCount($folder->parent_id, $documentCount);
            }
        });
    }

    /**
     * Build the full folder tree for a user, grouped by repository type.
     *
     * Loads all accessible folders in a single query and builds the tree
     * in O(n) using a map-based approach.
     *
     * @return array<string, array> Keyed by repository type, each containing nested tree.
     */
    public function buildFolderTree(User $user): array {
        $query = DocumentFolder::query();

        if (!DocumentFolderPolicy::isAdmin($user)) {
            $query->where(function ($visibilityQuery) use ($user) {
                $visibilityQuery->where('owner_id', $user->id)
                    ->orWhereIn('visibility', [
                        DocumentFolder::VISIBILITY_INTERNAL,
                        DocumentFolder::VISIBILITY_PUBLIC,
                    ]);
            });
        }

        $folders = $query
            ->with(['owner:id,firstname,lastname'])
            ->select([
                'id',
                'name',
                'parent_id',
                'owner_id',
                'repository_type',
                'visibility',
                'depth',
                'document_count',
                'path',
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // Build tree using map approach (O(n))
        $map = [];
        $tree = [];

        // First pass: create map of all folders as stdClass with children Collection
        foreach ($folders as $folder) {
            $map[$folder->id] = (object) [
                'id' => $folder->id,
                'name' => $folder->name,
                'parent_id' => $folder->parent_id,
                'owner_id' => $folder->owner_id,
                'repository_type' => $folder->repository_type,
                'visibility' => $folder->visibility,
                'depth' => $folder->depth,
                'document_count' => $folder->document_count,
                'owner_name' => trim((string) ($folder->owner?->full_name ?? '')),
                'children' => collect(),
            ];
        }

        // Second pass: build hierarchy
        foreach ($map as $id => $node) {
            if ($node->parent_id !== null && isset($map[$node->parent_id])) {
                $map[$node->parent_id]->children->push($node);
            } else {
                $tree[] = $node;
            }
        }

        // Group by repository type
        $grouped = [
            DocumentFolder::REPOSITORY_PERSONAL => [],
            'public' => [],
            DocumentFolder::REPOSITORY_INSTITUTIONAL => [],
            DocumentFolder::REPOSITORY_SHARED => [],
            DocumentFolder::REPOSITORY_DEPARTMENT => [],
        ];

        foreach ($tree as $rootNode) {
            if (
                $rootNode->repository_type === DocumentFolder::REPOSITORY_PERSONAL
                && (int) $rootNode->owner_id !== (int) $user->id
            ) {
                if (in_array($rootNode->visibility, [
                    DocumentFolder::VISIBILITY_INTERNAL,
                    DocumentFolder::VISIBILITY_PUBLIC,
                ], true)) {
                    $grouped['public'][] = $rootNode;
                }
                continue;
            }

            $type = $rootNode->repository_type;
            if (isset($grouped[$type])) {
                $grouped[$type][] = $rootNode;
            }
        }

        return $grouped;
    }

    /**
     * Increment the denormalized document count on a folder.
     */
    public function incrementDocumentCount(int $folderId, int $count = 1): void {
        DocumentFolder::where('id', $folderId)->increment('document_count', $count);
    }

    /**
     * Decrement the denormalized document count on a folder.
     */
    public function decrementDocumentCount(int $folderId, int $count = 1): void {
        DocumentFolder::where('id', $folderId)->decrement('document_count', $count);
    }

    /**
     * Build a cross-database ORDER BY expression that preserves the given ID sequence.
     *
     * MySQL supports FIELD(), but SQLite (used in tests) does not.
     * A CASE WHEN approach works on both.
     *
     * @param  int[]  $ids
     */
    private function orderByIds(array $ids): string {
        $cases = [];
        foreach ($ids as $position => $id) {
            $id = (int) $id;
            $cases[] = "WHEN id = {$id} THEN {$position}";
        }

        return 'CASE ' . implode(' ', $cases) . ' ELSE ' . count($ids) . ' END';
    }
}
