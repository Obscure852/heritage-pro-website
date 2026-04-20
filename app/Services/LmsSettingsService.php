<?php

namespace App\Services;

use App\Models\SMSApiSetting;
use Illuminate\Support\Facades\Cache;

class LmsSettingsService
{
    /**
     * Cache duration in seconds (1 hour)
     */
    protected const CACHE_TTL = 3600;

    /**
     * Cache key for all LMS settings
     */
    protected const CACHE_KEY = 'lms_settings';

    /**
     * LMS settings category
     */
    protected const CATEGORY = 'lms';

    /**
     * Get a single LMS setting value
     */
    public static function get(string $key, $default = null)
    {
        $settings = self::all();
        return $settings[$key] ?? $default;
    }

    /**
     * Get all LMS settings as key-value pairs
     */
    public static function all(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return SMSApiSetting::where('category', self::CATEGORY)
                ->get()
                ->mapWithKeys(function ($setting) {
                    return [$setting->key => $setting->typed_value];
                })
                ->toArray();
        });
    }

    /**
     * Get all LMS settings as collection with full details
     */
    public static function allWithDetails(): \Illuminate\Support\Collection
    {
        return SMSApiSetting::where('category', self::CATEGORY)
            ->orderBy('display_order')
            ->get();
    }

    /**
     * Get settings by key prefix
     */
    public static function getByPrefix(string $prefix): \Illuminate\Support\Collection
    {
        return SMSApiSetting::where('category', self::CATEGORY)
            ->where('key', 'like', $prefix . '%')
            ->orderBy('display_order')
            ->get();
    }

    /**
     * Update a single setting
     */
    public static function update(string $key, $value): bool
    {
        $setting = SMSApiSetting::where('key', $key)
            ->where('category', self::CATEGORY)
            ->first();

        if ($setting && $setting->is_editable) {
            $preparedValue = self::prepareValueForStorage($value, $setting->type);
            $setting->update(['value' => $preparedValue]);
            self::clearCache();
            return true;
        }

        return false;
    }

    /**
     * Bulk update multiple settings
     */
    public static function bulkUpdate(array $settings): array
    {
        $success = 0;
        $failed = [];

        foreach ($settings as $key => $value) {
            if (self::update($key, $value)) {
                $success++;
            } else {
                $failed[] = $key;
            }
        }

        return [
            'success' => $success,
            'failed' => $failed
        ];
    }

    /**
     * Clear the LMS settings cache
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Prepare value for storage based on type
     */
    protected static function prepareValueForStorage($value, string $type): string
    {
        switch ($type) {
            case 'boolean':
            case 'bool':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
            case 'json':
            case 'array':
                return is_string($value) ? $value : json_encode($value);
            default:
                return (string) $value;
        }
    }

    // ==========================================
    // Convenience Methods for File Upload Limits
    // ==========================================

    public static function getScormMaxSizeMb(): int
    {
        return (int) self::get('lms_scorm_max_size_mb', 500);
    }

    public static function getH5pMaxSizeMb(): int
    {
        return (int) self::get('lms_h5p_max_size_mb', 500);
    }

    public static function getVideoMaxSizeMb(): int
    {
        return (int) self::get('lms_video_max_size_mb', 2048);
    }

    public static function getLibraryMaxSizeMb(): int
    {
        return (int) self::get('lms_library_max_size_mb', 500);
    }

    // ==========================================
    // Convenience Methods for Assignment Settings
    // ==========================================

    public static function getAssignmentMaxSizeMb(): int
    {
        return (int) self::get('lms_assignment_max_size_mb', 100);
    }

    public static function getAssignmentMaxFiles(): int
    {
        return (int) self::get('lms_assignment_max_files', 20);
    }

    public static function getAssignmentDefaultFileTypes(): array
    {
        $types = self::get('lms_assignment_default_types', 'pdf,doc,docx,ppt,pptx,xls,xlsx,txt,zip');
        return array_map('trim', explode(',', $types));
    }

    public static function getAssignmentLatePenaltyMax(): int
    {
        return (int) self::get('lms_assignment_late_penalty_max', 100);
    }

    public static function getAssignmentMaxPoints(): int
    {
        return (int) self::get('lms_assignment_max_points', 1000);
    }

    // ==========================================
    // Convenience Methods for Quiz Settings
    // ==========================================

    public static function getQuizTimeLimitMax(): int
    {
        return (int) self::get('lms_quiz_time_limit_max', 480);
    }

    public static function getQuizPointsMax(): int
    {
        return (int) self::get('lms_quiz_points_max', 100);
    }

    public static function getQuizPassingScoreDefault(): int
    {
        return (int) self::get('lms_quiz_passing_score_default', 50);
    }

    // ==========================================
    // Convenience Methods for Course & Grading
    // ==========================================

    public static function getCoursePassingGradeDefault(): int
    {
        return (int) self::get('lms_course_passing_grade_default', 60);
    }

    public static function getGradebookMethodDefault(): string
    {
        return (string) self::get('lms_gradebook_method_default', 'weighted');
    }

    public static function getGradebookPassingGradeDefault(): int
    {
        return (int) self::get('lms_gradebook_passing_grade_default', 50);
    }

    // ==========================================
    // Convenience Methods for Video Settings
    // ==========================================

    public static function getVideoSupportedFormats(): array
    {
        $formats = self::get('lms_video_supported_formats', 'mp4,mov,avi,mkv,webm,wmv,flv,m4v');
        return array_map('trim', explode(',', $formats));
    }

    public static function getVideoTranscodeFormatsDefault(): array
    {
        $formats = self::get('lms_video_transcode_formats_default', '720p,480p,360p');
        return array_map('trim', explode(',', $formats));
    }

    public static function getVideoCompletionThreshold(): int
    {
        return (int) self::get('lms_video_completion_threshold', 90);
    }

    // ==========================================
    // Convenience Methods for SCORM Settings
    // ==========================================

    public static function getScormSupportedVersions(): array
    {
        $versions = self::get('lms_scorm_supported_versions', '1.2,2004');
        return array_map('trim', explode(',', $versions));
    }

    public static function getScormMasteryScoreDefault(): int
    {
        return (int) self::get('lms_scorm_mastery_score_default', 70);
    }

    // ==========================================
    // Convenience Methods for LTI Settings
    // ==========================================

    public static function getLtiVersionDefault(): string
    {
        return (string) self::get('lms_lti_version_default', '1.3');
    }

    public static function getLtiPrivacyLevelDefault(): string
    {
        return (string) self::get('lms_lti_privacy_level_default', 'public');
    }

    public static function getLtiScoreMaxDefault(): int
    {
        return (int) self::get('lms_lti_score_max_default', 100);
    }

    // ==========================================
    // Convenience Methods for Gamification
    // ==========================================

    public static function getLeaderboardLimit(): int
    {
        return (int) self::get('lms_gamification_leaderboard_limit', 100);
    }

    public static function getActivityLimit(): int
    {
        return (int) self::get('lms_gamification_activity_limit', 50);
    }

    public static function isPointsEnabled(): bool
    {
        return (bool) self::get('lms_gamification_points_enabled', true);
    }

    public static function isBadgesEnabled(): bool
    {
        return (bool) self::get('lms_gamification_badges_enabled', true);
    }
}
