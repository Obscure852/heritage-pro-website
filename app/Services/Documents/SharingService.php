<?php

namespace App\Services\Documents;

use App\Models\Document;
use App\Models\DocumentAudit;
use App\Models\Role;
use App\Models\DocumentShare;
use App\Models\User;
use App\Policies\DocumentPolicy;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SharingService {
    /**
     * Permission level hierarchy mapping.
     */
    private const PERMISSION_LEVELS = [
        DocumentShare::PERMISSION_VIEW => 1,
        DocumentShare::PERMISSION_COMMENT => 2,
        DocumentShare::PERMISSION_EDIT => 3,
        DocumentShare::PERMISSION_MANAGE => 4,
    ];

    /**
     * Create or update a share for a document.
     *
     * Enforces: 50 user-share limit (SHR-09), escalation prevention (SHR-05),
     * and deduplication (updates existing share if same target).
     *
     * @throws ValidationException
     */
    public function createShare(Document $document, User $sharer, array $data): DocumentShare {
        return DB::transaction(function () use ($document, $sharer, $data) {
            $shareableType = $data['shareable_type'];
            $shareableId = (string) $data['shareable_id'];
            $permission = $data['permission'];
            $message = $data['message'] ?? null;

            if ($shareableType === DocumentShare::TYPE_USER) {
                if (!ctype_digit($shareableId) || !User::whereKey((int) $shareableId)->exists()) {
                    throw ValidationException::withMessages([
                        'shareable_id' => 'Selected user is invalid.',
                    ]);
                }
            } elseif ($shareableType === DocumentShare::TYPE_ROLE) {
                $roleExists = ctype_digit($shareableId)
                    ? Role::whereKey((int) $shareableId)->exists()
                    : Role::where('name', $shareableId)->exists();
                if (!$roleExists) {
                    throw ValidationException::withMessages([
                        'shareable_id' => 'Selected role is invalid.',
                    ]);
                }
            } elseif ($shareableType === DocumentShare::TYPE_DEPARTMENT) {
                if ($shareableId === '' || !User::where('department', $shareableId)->exists()) {
                    throw ValidationException::withMessages([
                        'shareable_id' => 'Selected department is invalid.',
                    ]);
                }
            }

            // Check for existing active share (same document + target) — update instead of duplicate
            $existingShare = DocumentShare::where('document_id', $document->id)
                ->where('shareable_type', $shareableType)
                ->where('shareable_id', $shareableId)
                ->where('is_active', true)
                ->whereNull('revoked_at')
                ->first();

            // Enforce 50-share limit for individual user shares (SHR-09)
            if ($shareableType === DocumentShare::TYPE_USER && !$existingShare) {
                $activeUserShares = DocumentShare::where('document_id', $document->id)
                    ->where('shareable_type', DocumentShare::TYPE_USER)
                    ->where('is_active', true)
                    ->whereNull('revoked_at')
                    ->count();

                if ($activeUserShares >= 50) {
                    throw ValidationException::withMessages([
                        'shareable_id' => 'Maximum of 50 individual user shares per document has been reached.',
                    ]);
                }
            }

            // Escalation prevention (SHR-05): cannot grant higher permission than own level
            // Exception: document owner and admins can grant any level
            $isOwner = $document->owner_id === $sharer->id;
            $isAdmin = DocumentPolicy::isAdmin($sharer);

            if (!$isOwner && !$isAdmin) {
                $sharerPermission = $this->getEffectivePermission($document, $sharer);
                $sharerLevel = $sharerPermission ? $this->permissionLevel($sharerPermission) : 0;
                $requestedLevel = $this->permissionLevel($permission);

                if ($requestedLevel > $sharerLevel) {
                    throw ValidationException::withMessages([
                        'permission' => 'You cannot grant a higher permission level than your own.',
                    ]);
                }
            }

            if ($existingShare) {
                $existingShare->update([
                    'permission_level' => $permission,
                    'message' => $message,
                ]);

                DocumentAudit::create([
                    'document_id' => $document->id,
                    'user_id' => $sharer->id,
                    'action' => DocumentAudit::ACTION_SHARED,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'metadata' => [
                        'shareable_type' => $shareableType,
                        'shareable_id' => $shareableId,
                        'permission' => $permission,
                        'updated_existing' => true,
                    ],
                ]);

                return $existingShare->fresh();
            }

            // Create new share
            $share = DocumentShare::create([
                'document_id' => $document->id,
                'shareable_type' => $shareableType,
                'shareable_id' => $shareableId,
                'permission_level' => $permission,
                'shared_by_user_id' => $sharer->id,
                'message' => $message,
                'is_active' => true,
            ]);

            DocumentAudit::create([
                'document_id' => $document->id,
                'user_id' => $sharer->id,
                'action' => DocumentAudit::ACTION_SHARED,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => [
                    'shareable_type' => $shareableType,
                    'shareable_id' => $shareableId,
                    'permission' => $permission,
                ],
            ]);

            return $share;
        });
    }

    /**
     * Soft-revoke a share by setting revoked_at and revoked_by_user_id.
     */
    public function revokeShare(DocumentShare $share, User $revoker): void {
        DB::transaction(function () use ($share, $revoker) {
            $share->update([
                'revoked_at' => now(),
                'revoked_by_user_id' => $revoker->id,
                'is_active' => false,
            ]);

            DocumentAudit::create([
                'document_id' => $share->document_id,
                'user_id' => $revoker->id,
                'action' => DocumentAudit::ACTION_UNSHARED,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => [
                    'share_id' => $share->id,
                    'shareable_type' => $share->shareable_type,
                    'shareable_id' => $share->shareable_id,
                    'permission' => $share->permission_level,
                ],
            ]);
        });
    }

    /**
     * Get the effective (highest) permission level a user has on a document.
     *
     * Priority: Owner/Admin -> Individual user share (SHR-06 override) -> highest of role/department shares.
     * Individual user share ALWAYS overrides role/department, even if lower (SHR-06).
     *
     * @return string|null Permission level constant, or null if no access
     */
    public function getEffectivePermission(Document $document, User $user): ?string {
        $roleIdentifiers = array_values(array_unique(array_map('strval', array_merge(
            $user->roles()->pluck('roles.id')->toArray(),
            $user->roles()->pluck('roles.name')->toArray()
        ))));

        // Owner always has manage
        if ($document->owner_id === $user->id) {
            return DocumentShare::PERMISSION_MANAGE;
        }

        // Admin always has manage
        if (DocumentPolicy::isAdmin($user)) {
            return DocumentShare::PERMISSION_MANAGE;
        }

        // Check individual user share first (SHR-06: individual ALWAYS overrides)
        $userShare = DocumentShare::where('document_id', $document->id)
            ->where('shareable_type', DocumentShare::TYPE_USER)
            ->where('shareable_id', (string) $user->id)
            ->where('is_active', true)
            ->whereNull('revoked_at')
            ->first();

        if ($userShare) {
            return $userShare->permission_level;
        }

        // Check role shares
        $roleShare = null;
        if (!empty($roleIdentifiers)) {
            $roleShare = DocumentShare::where('document_id', $document->id)
                ->where('shareable_type', DocumentShare::TYPE_ROLE)
                ->whereIn('shareable_id', $roleIdentifiers)
                ->where('is_active', true)
                ->whereNull('revoked_at')
                ->get();
        }

        // Check department share
        $departmentShare = null;
        if ($user->department) {
            $departmentShare = DocumentShare::where('document_id', $document->id)
                ->where('shareable_type', DocumentShare::TYPE_DEPARTMENT)
                ->where('shareable_id', (string) $user->department)
                ->where('is_active', true)
                ->whereNull('revoked_at')
                ->get();
        }

        // Find highest permission from role and department shares
        $allShares = collect();
        if ($roleShare) {
            $allShares = $allShares->merge($roleShare);
        }
        if ($departmentShare) {
            $allShares = $allShares->merge($departmentShare);
        }

        if ($allShares->isEmpty()) {
            return null;
        }

        // Return the highest permission level
        return $allShares->sortByDesc(function ($share) {
            return $this->permissionLevel($share->permission_level);
        })->first()->permission_level;
    }

    /**
     * Get all active (non-revoked) shares for a document, ordered by type then created_at.
     */
    public function getDocumentShares(Document $document): Collection {
        return DocumentShare::where('document_id', $document->id)
            ->where('is_active', true)
            ->whereNull('revoked_at')
            ->orderBy('shareable_type')
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Map permission name to numeric level for comparison.
     */
    public function permissionLevel(string $permission): int {
        return self::PERMISSION_LEVELS[$permission] ?? 0;
    }

    /**
     * Search users by name or email for autocomplete.
     */
    public function searchUsers(string $query, int $limit = 10): Collection {
        return User::where(function ($q) use ($query) {
                $q->where('firstname', 'like', "%{$query}%")
                  ->orWhere('lastname', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->select('id', 'firstname', 'lastname', 'email', 'department')
            ->limit($limit)
            ->get()
            ->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->full_name,
                'email' => $user->email,
                'department' => $user->department,
            ]);
    }

    /**
     * Return all available roles from the roles table.
     */
    public function getAvailableRoles(): Collection {
        return DB::table('roles')->select('id', 'name')->orderBy('name')->get();
    }

    /**
     * Return distinct departments from users.
     */
    public function getAvailableDepartments(): Collection {
        return User::whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');
    }
}
