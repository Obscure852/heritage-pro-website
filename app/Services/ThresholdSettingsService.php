<?php

namespace App\Services;

use App\Models\PassingThresholdSetting;
use App\Models\SchoolSetup;
use App\Models\TeacherThresholdPreference;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ThresholdSettingsService
{
    /**
     * Cache TTL in seconds (1 hour)
     */
    private const CACHE_TTL = 3600;

    /**
     * Cache key prefix
     */
    private const CACHE_PREFIX = 'threshold_settings';

    /**
     * Get the effective threshold settings for the current user.
     * Uses caching for performance and handles fallback chain.
     *
     * @param int|null $gradeId
     * @param int|null $gradeSubjectId
     * @param string|null $testType
     * @return array{thresholds: array, highlight_enabled: bool, source: string}
     */
    public function getEffectiveThreshold(
        ?int $gradeId = null,
        ?int $gradeSubjectId = null,
        ?string $testType = null
    ): array {
        $userId = Auth::id();
        $schoolType = $this->getSchoolType();

        // Build cache key from all relevant parameters
        $cacheKey = $this->buildCacheKey($userId, $schoolType, $gradeId, $gradeSubjectId, $testType);

        // Track cache key for non-Redis stores (so clearUserCache can find it later)
        if ($userId && !(Cache::getStore() instanceof \Illuminate\Cache\RedisStore)) {
            $this->trackUserCacheKey($userId, $cacheKey);
        }

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($schoolType, $gradeId, $gradeSubjectId, $testType, $userId) {
            // Get system setting using fallback chain
            $systemSetting = PassingThresholdSetting::getThreshold(
                $schoolType,
                $gradeId,
                $gradeSubjectId,
                $testType
            );

            // Merge with teacher preferences (teacher overrides system)
            if ($userId) {
                return TeacherThresholdPreference::getEffectiveSettings($userId, $systemSetting);
            }

            // No logged in user - return system defaults
            return [
                'thresholds' => $systemSetting?->getSortedThresholds()
                    ?? PassingThresholdSetting::DEFAULT_THRESHOLDS,
                'highlight_enabled' => true,
                'source' => 'system',
            ];
        });
    }

    /**
     * Update teacher preference with cache invalidation.
     *
     * @param int $userId
     * @param array $data
     * @return TeacherThresholdPreference
     */
    public function updateTeacherPreference(int $userId, array $data): TeacherThresholdPreference
    {
        // Clear cache for this user before update
        $this->clearUserCache($userId);

        try {
            $preference = TeacherThresholdPreference::upsertPreference($userId, $data);

            Log::info('Teacher threshold preference updated', [
                'user_id' => $userId,
                'highlight_enabled' => $data['highlight_enabled'] ?? true,
            ]);

            return $preference;
        } catch (\Exception $e) {
            Log::error('Failed to update teacher threshold preference', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update or create a system-wide threshold setting.
     *
     * @param array $criteria Scope criteria (school_type, grade_id, etc.)
     * @param array $data Setting data (thresholds, is_active)
     * @return PassingThresholdSetting
     */
    public function updateSystemSetting(array $criteria, array $data): PassingThresholdSetting
    {
        // Clear all cache when system settings change
        $this->clearAllCache();

        try {
            $setting = PassingThresholdSetting::upsertSetting($criteria, $data);

            Log::info('System threshold setting updated', [
                'criteria' => $criteria,
                'setting_id' => $setting->id,
            ]);

            return $setting;
        } catch (\Exception $e) {
            Log::error('Failed to update system threshold setting', [
                'criteria' => $criteria,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete a system setting.
     *
     * @param int $settingId
     * @return bool
     */
    public function deleteSystemSetting(int $settingId): bool
    {
        $this->clearAllCache();

        return DB::transaction(function () use ($settingId) {
            $setting = PassingThresholdSetting::findOrFail($settingId);
            return $setting->delete();
        });
    }

    /**
     * Reset teacher preference to system defaults.
     *
     * @param int $userId
     * @return TeacherThresholdPreference|null
     */
    public function resetTeacherPreference(int $userId): ?TeacherThresholdPreference
    {
        $this->clearUserCache($userId);

        $preference = TeacherThresholdPreference::where('user_id', $userId)->first();

        if ($preference) {
            return $preference->resetToDefaults();
        }

        return null;
    }

    /**
     * Get all system settings for admin management.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllSystemSettings()
    {
        return PassingThresholdSetting::with(['grade', 'gradeSubject.subject'])
            ->orderByRaw("
                CASE school_type
                    WHEN 'Junior' THEN 1
                    WHEN 'Senior' THEN 2
                    WHEN 'Primary' THEN 3
                    ELSE 4
                END
            ")
            ->orderBy('grade_id')
            ->orderBy('grade_subject_id')
            ->get();
    }

    /**
     * Get the current school type from setup.
     *
     * @return string
     */
    private function getSchoolType(): string
    {
        return Cache::remember('school_type_threshold', 86400, function () {
            return SchoolSetup::value('type') ?? 'Junior';
        });
    }

    /**
     * Build a cache key from parameters.
     *
     * @param int|null $userId
     * @param string $schoolType
     * @param int|null $gradeId
     * @param int|null $gradeSubjectId
     * @param string|null $testType
     * @return string
     */
    private function buildCacheKey(
        ?int $userId,
        string $schoolType,
        ?int $gradeId,
        ?int $gradeSubjectId,
        ?string $testType
    ): string {
        $parts = [
            self::CACHE_PREFIX,
            'u' . ($userId ?? 0),
            's' . $schoolType,
            'g' . ($gradeId ?? 0),
            'gs' . ($gradeSubjectId ?? 0),
            't' . ($testType ?? 'all'),
        ];

        return implode(':', $parts);
    }

    /**
     * Track a cache key for a user (for non-Redis stores).
     *
     * @param int $userId
     * @param string $cacheKey
     * @return void
     */
    private function trackUserCacheKey(int $userId, string $cacheKey): void
    {
        try {
            $userCacheKeysKey = self::CACHE_PREFIX . ':user_keys:' . $userId;
            $existingKeys = Cache::get($userCacheKeysKey, []);

            if (!in_array($cacheKey, $existingKeys)) {
                $existingKeys[] = $cacheKey;
                // Limit to last 50 keys to prevent unbounded growth
                if (count($existingKeys) > 50) {
                    $existingKeys = array_slice($existingKeys, -50);
                }
                Cache::put($userCacheKeysKey, $existingKeys, self::CACHE_TTL * 2);
            }

            // Also track in global all_keys for clearAllCache()
            $this->trackGlobalCacheKey($cacheKey);
        } catch (\Exception $e) {
            // Log but don't fail - cache tracking is best-effort
            Log::warning('Failed to track threshold cache key', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Track a cache key globally (for non-Redis stores).
     * Used by clearAllCache() to know which keys to clear.
     *
     * @param string $cacheKey
     * @return void
     */
    private function trackGlobalCacheKey(string $cacheKey): void
    {
        if (Cache::getStore() instanceof \Illuminate\Cache\RedisStore) {
            return; // Redis uses pattern matching, no tracking needed
        }

        try {
            $allKeysKey = self::CACHE_PREFIX . ':all_keys';
            $allKeys = Cache::get($allKeysKey, []);

            if (!in_array($cacheKey, $allKeys)) {
                $allKeys[] = $cacheKey;
                // Limit to last 500 keys globally
                if (count($allKeys) > 500) {
                    $allKeys = array_slice($allKeys, -500);
                }
                Cache::put($allKeysKey, $allKeys, 86400); // 24 hours
            }
        } catch (\Exception $e) {
            Log::warning('Failed to track global threshold cache key', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Clear cache for a specific user.
     *
     * @param int $userId
     * @return void
     */
    public function clearUserCache(int $userId): void
    {
        // Clear cache entries for this user
        // Since we can't easily iterate cache keys, we use tagged caching pattern
        // For now, we'll use a simpler approach with cache tags if available,
        // or flush by pattern if using Redis
        $pattern = self::CACHE_PREFIX . ':u' . $userId . ':*';

        // If using Redis, we can use pattern deletion
        if (Cache::getStore() instanceof \Illuminate\Cache\RedisStore) {
            $redis = Cache::getStore()->getRedis();
            $keys = $redis->keys(config('cache.prefix') . ':' . $pattern);
            if (!empty($keys)) {
                $redis->del($keys);
            }
        } else {
            // For file/array cache, we track user cache keys in a separate key
            $userCacheKeysKey = self::CACHE_PREFIX . ':user_keys:' . $userId;
            $userCacheKeys = Cache::get($userCacheKeysKey, []);

            foreach ($userCacheKeys as $key) {
                Cache::forget($key);
            }

            Cache::forget($userCacheKeysKey);
        }
    }

    /**
     * Clear all threshold-related cache.
     *
     * @return void
     */
    public function clearAllCache(): void
    {
        // For simplicity, we'll use cache tags if available
        // Otherwise, we need a more sophisticated approach

        if (Cache::getStore() instanceof \Illuminate\Cache\RedisStore) {
            $redis = Cache::getStore()->getRedis();
            $pattern = config('cache.prefix') . ':' . self::CACHE_PREFIX . ':*';
            $keys = $redis->keys($pattern);
            if (!empty($keys)) {
                $redis->del($keys);
            }
        } else {
            // For file/array cache, we track all cache keys
            $allKeysKey = self::CACHE_PREFIX . ':all_keys';
            $allKeys = Cache::get($allKeysKey, []);

            foreach ($allKeys as $key) {
                Cache::forget($key);
            }

            Cache::forget($allKeysKey);
        }

        // Also clear school type cache
        Cache::forget('school_type_threshold');
    }

    /**
     * Validate threshold array structure.
     *
     * @param array $thresholds
     * @return array{valid: bool, errors: array}
     */
    public function validateThresholds(array $thresholds): array
    {
        $errors = [];

        if (empty($thresholds)) {
            return ['valid' => false, 'errors' => ['At least one threshold is required']];
        }

        $seenPercentages = [];

        foreach ($thresholds as $index => $threshold) {
            $prefix = "Threshold " . ($index + 1);

            if (!isset($threshold['name']) || empty(trim($threshold['name']))) {
                $errors[] = "{$prefix}: Name is required";
            }

            if (!isset($threshold['max_percentage'])) {
                $errors[] = "{$prefix}: Max percentage is required";
            } elseif (!is_numeric($threshold['max_percentage'])) {
                $errors[] = "{$prefix}: Max percentage must be a number";
            } elseif ($threshold['max_percentage'] < 0 || $threshold['max_percentage'] > 100) {
                $errors[] = "{$prefix}: Max percentage must be between 0 and 100";
            } elseif (in_array($threshold['max_percentage'], $seenPercentages)) {
                $errors[] = "{$prefix}: Duplicate max percentage value";
            } else {
                $seenPercentages[] = $threshold['max_percentage'];
            }

            if (!isset($threshold['color']) || !preg_match('/^#[0-9A-Fa-f]{6}$/', $threshold['color'])) {
                $errors[] = "{$prefix}: Valid hex color is required (e.g., #ff0000)";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}
