<?php

namespace App\Services\Documents;

use App\Models\Document;
use App\Models\DocumentAudit;
use App\Models\DocumentShare;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PublicLinkService {
    /**
     * Generate a public link for a published document.
     *
     * Creates a DocumentShare record of TYPE_PUBLIC_LINK with a unique 64-char access token,
     * optional password protection, configurable download permission, view limits, and expiry.
     *
     * Enforces:
     * - Document must be published (PUB-06)
     * - Maximum active links per document (PUB-05, default 5)
     *
     * @throws ValidationException
     */
    public function generateLink(Document $document, User $creator, array $data): DocumentShare {
        return DB::transaction(function () use ($document, $creator, $data) {
            // Verify document is published (PUB-06)
            if ($document->status !== Document::STATUS_PUBLISHED) {
                throw ValidationException::withMessages([
                    'document' => 'Public links can only be generated for published documents.',
                ]);
            }

            // Enforce max active links per document (PUB-05)
            $maxLinks = config('documents.public.max_links_per_document', 5);
            $activeCount = DocumentShare::where('document_id', $document->id)
                ->where('shareable_type', DocumentShare::TYPE_PUBLIC_LINK)
                ->whereNull('revoked_at')
                ->where('is_active', true)
                ->count();

            if ($activeCount >= $maxLinks) {
                throw ValidationException::withMessages([
                    'document' => "Maximum of {$maxLinks} active public links per document has been reached.",
                ]);
            }

            // Generate unique access token
            $token = Str::random(64);

            // Hash password if provided (PUB-02)
            $passwordHash = isset($data['password']) && $data['password']
                ? Hash::make($data['password'])
                : null;

            // Create the public link share record
            $share = DocumentShare::create([
                'document_id' => $document->id,
                'shareable_type' => DocumentShare::TYPE_PUBLIC_LINK,
                'shareable_id' => null,
                'permission_level' => DocumentShare::PERMISSION_VIEW,
                'shared_by_user_id' => $creator->id,
                'access_token' => $token,
                'password_hash' => $passwordHash,
                'allow_download' => $data['allow_download'] ?? false,
                'max_views' => $data['max_views'] ?? null,
                'view_count' => 0,
                'expires_at' => $data['expires_at'],
                'is_active' => true,
            ]);

            // Create audit record
            DocumentAudit::create([
                'document_id' => $document->id,
                'user_id' => $creator->id,
                'action' => DocumentAudit::ACTION_SHARED,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => [
                    'public_link' => true,
                    'token_prefix' => substr($token, 0, 8),
                    'has_password' => !is_null($passwordHash),
                    'allow_download' => $share->allow_download,
                    'max_views' => $share->max_views,
                    'expires_at' => $share->expires_at?->toIso8601String(),
                ],
            ]);

            return $share;
        });
    }

    /**
     * Get all active (non-revoked, active) public links for a document.
     */
    public function getActiveLinks(Document $document): Collection {
        return DocumentShare::where('document_id', $document->id)
            ->where('shareable_type', DocumentShare::TYPE_PUBLIC_LINK)
            ->whereNull('revoked_at')
            ->where('is_active', true)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Disable a public link without revoking it (can be re-enabled).
     */
    public function disableLink(DocumentShare $link, User $user): void {
        DB::transaction(function () use ($link, $user) {
            $link->update(['is_active' => false]);

            DocumentAudit::create([
                'document_id' => $link->document_id,
                'user_id' => $user->id,
                'action' => DocumentAudit::ACTION_SHARED,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => [
                    'public_link' => true,
                    'action' => 'disabled',
                    'share_id' => $link->id,
                    'token_prefix' => substr($link->access_token, 0, 8),
                ],
            ]);
        });
    }

    /**
     * Permanently revoke (soft-delete) a public link via SharingService.
     */
    public function deleteLink(DocumentShare $link, User $user): void {
        $sharingService = app(SharingService::class);
        $sharingService->revokeShare($link, $user);
    }

    /**
     * Resolve a public link by its access token.
     *
     * Returns null if the link is not found, inactive, expired, or view-exhausted.
     * Does NOT increment view count (call incrementViewCount separately).
     */
    public function resolveLink(string $token): ?DocumentShare {
        $link = DocumentShare::where('access_token', $token)
            ->where('shareable_type', DocumentShare::TYPE_PUBLIC_LINK)
            ->whereNull('revoked_at')
            ->whereHas('document', function ($query) {
                $query->where('status', Document::STATUS_PUBLISHED);
            })
            ->first();

        if (!$link) {
            return null;
        }

        // Check if link is active
        if (!$link->is_active) {
            return null;
        }

        // Check if link has expired
        if ($link->expires_at && $link->expires_at->isPast()) {
            return null;
        }

        // Check if view limit has been reached
        if ($link->max_views !== null && $link->view_count >= $link->max_views) {
            return null;
        }

        return $link;
    }

    /**
     * Verify a password against a public link's stored hash.
     */
    public function verifyPassword(DocumentShare $link, string $password): bool {
        if (!$link->password_hash) {
            return true;
        }

        return Hash::check($password, $link->password_hash);
    }

    /**
     * Check if an IP is locked out from a specific public link due to failed password attempts.
     *
     * @return bool True if the IP is locked out
     */
    public function checkLockout(string $ip, DocumentShare $link): bool {
        $maxAttempts = config('documents.public.max_password_attempts', 3);
        $attempts = Cache::get("public_link_lockout:{$link->id}:{$ip}", 0);

        return $attempts >= $maxAttempts;
    }

    /**
     * Record a failed password attempt for an IP on a specific public link.
     *
     * Sets a 15-minute TTL on the lockout counter. Returns the current attempt count.
     */
    public function recordFailedAttempt(string $ip, DocumentShare $link): int {
        $cacheKey = "public_link_lockout:{$link->id}:{$ip}";
        $current = Cache::get($cacheKey, 0);

        if ($current === 0) {
            // First attempt — set with 15-minute TTL (900 seconds)
            Cache::put($cacheKey, 1, 900);
            return 1;
        }

        // Increment existing counter (preserves TTL in most drivers)
        $newCount = Cache::increment($cacheKey);

        return $newCount;
    }

    /**
     * Increment the view count on a public link using pessimistic locking.
     */
    public function incrementViewCount(DocumentShare $link): void {
        DB::transaction(function () use ($link) {
            $locked = DocumentShare::where('id', $link->id)
                ->lockForUpdate()
                ->first();

            if ($locked) {
                $locked->increment('view_count');
            }
        });
    }
}
