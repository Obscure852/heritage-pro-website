<?php

namespace App\Services\Documents;

use App\Mail\Documents\QuotaWarningMail;
use App\Models\Document;
use App\Models\DocumentFolder;
use App\Models\User;
use App\Models\UserDocumentQuota;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class QuotaService {
    protected DocumentSettingService $settingService;

    public function __construct(DocumentSettingService $settingService) {
        $this->settingService = $settingService;
    }

    /**
     * Get or create a quota record for the given user.
     *
     * Uses default quota from DocumentSettingService (DB overrides config), with warning threshold.
     */
    public function getOrCreateQuota(User $user): UserDocumentQuota {
        return UserDocumentQuota::firstOrCreate(
            ['user_id' => $user->id],
            [
                'quota_bytes' => $this->settingService->get('quotas.default_bytes', 524288000),
                'used_bytes' => 0,
                'warning_threshold_percent' => $this->settingService->get('quotas.warning_threshold_percent', 80),
                'is_unlimited' => false,
            ]
        );
    }

    /**
     * Check if the user can upload a file of the given size.
     *
     * Returns an array with 'allowed', 'reason', and 'usage_percent' keys.
     * - Over 110%: hard block (not allowed)
     * - 100-110%: soft warning (allowed with warning)
     * - Under 100%: allowed
     *
     * @return array{allowed: bool, reason: string|null, usage_percent: float}
     */
    public function canUpload(User $user, int $fileSizeBytes): array {
        $quota = $this->getOrCreateQuota($user);

        if ($quota->is_unlimited) {
            return ['allowed' => true, 'reason' => null, 'usage_percent' => 0.0];
        }

        $projected = $quota->used_bytes + $fileSizeBytes;
        $percent = $quota->quota_bytes > 0
            ? round(($projected / $quota->quota_bytes) * 100, 2)
            : 0.0;

        if ($percent > 110) {
            return [
                'allowed' => false,
                'reason' => 'Storage quota exceeded (over 110%). Contact an administrator to increase your quota.',
                'usage_percent' => $percent,
            ];
        }

        if ($percent > 100) {
            return [
                'allowed' => true,
                'reason' => 'over_quota_warning',
                'usage_percent' => $percent,
            ];
        }

        return ['allowed' => true, 'reason' => null, 'usage_percent' => $percent];
    }

    /**
     * Recalculate used_bytes from actual database records.
     *
     * Excludes archived documents (QTA-04) and documents in institutional folders (QTA-05).
     */
    public function recalculate(User $user): UserDocumentQuota {
        $quota = $this->getOrCreateQuota($user);

        // Get IDs of institutional folders to exclude
        $institutionalFolderIds = DocumentFolder::where('repository_type', 'institutional')
            ->pluck('id')
            ->toArray();

        $query = Document::where('owner_id', $user->id)
            ->where('status', '!=', Document::STATUS_ARCHIVED)
            ->whereNull('deleted_at');

        if (!empty($institutionalFolderIds)) {
            $query->where(function ($q) use ($institutionalFolderIds) {
                $q->whereNull('folder_id')
                  ->orWhereNotIn('folder_id', $institutionalFolderIds);
            });
        }

        $usedBytes = (int) $query->sum('size_bytes');

        $quota->update(['used_bytes' => $usedBytes]);

        return $quota->fresh();
    }

    /**
     * Increment used_bytes atomically after a successful upload.
     */
    public function incrementUsage(User $user, int $bytes): void {
        $quota = $this->getOrCreateQuota($user);
        $quota->increment('used_bytes', $bytes);
    }

    /**
     * Decrement used_bytes atomically after a document deletion.
     */
    public function decrementUsage(User $user, int $bytes): void {
        $quota = $this->getOrCreateQuota($user);
        // Prevent negative values
        $newUsed = max(0, $quota->used_bytes - $bytes);
        $quota->update(['used_bytes' => $newUsed]);
    }

    /**
     * Check if the user's quota is at or above the warning threshold.
     *
     * If so and warning hasn't been sent yet, send the warning email and set warning_sent_at.
     * If usage drops below threshold, reset warning_sent_at to null.
     */
    public function checkAndSendWarning(User $user): void {
        $quota = $this->getOrCreateQuota($user);

        if ($quota->is_unlimited) {
            return;
        }

        $usagePercent = $quota->usage_percent;

        if ($usagePercent >= $quota->warning_threshold_percent) {
            // Send warning if not already sent
            if ($quota->warning_sent_at === null) {
                Mail::to($user->email)->queue(new QuotaWarningMail($user, $quota));
                $quota->update(['warning_sent_at' => now()]);
            }
        } else {
            // Usage dropped below threshold, reset warning flag
            if ($quota->warning_sent_at !== null) {
                $quota->update(['warning_sent_at' => null]);
            }
        }
    }

    /**
     * Format bytes into a human-readable string (e.g., "245.3 MB").
     */
    public function formatBytes(int $bytes): string {
        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        $value = (float) $bytes;

        while ($value >= 1024 && $i < count($units) - 1) {
            $value /= 1024;
            $i++;
        }

        return round($value, 1) . ' ' . $units[$i];
    }
}
