<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class TeacherThresholdPreference extends Model
{
    protected $fillable = [
        'user_id',
        'thresholds',
        'highlight_enabled',
    ];

    protected $casts = [
        'thresholds' => 'array',
        'highlight_enabled' => 'boolean',
    ];

    /**
     * Relationship to user (teacher)
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get effective settings for a teacher, merging with system defaults.
     * Returns an array with thresholds and highlight_enabled flag.
     */
    public static function getEffectiveSettings(int $userId, ?PassingThresholdSetting $systemSetting = null): array
    {
        $teacherPref = static::where('user_id', $userId)->first();

        // Default thresholds from system setting or constant
        $defaultThresholds = $systemSetting?->getSortedThresholds()
            ?? PassingThresholdSetting::DEFAULT_THRESHOLDS;

        // No teacher preference - use system defaults
        if (!$teacherPref) {
            return [
                'thresholds' => $defaultThresholds,
                'highlight_enabled' => true,
                'source' => 'system',
            ];
        }

        // Teacher disabled highlighting
        if (!$teacherPref->highlight_enabled) {
            return [
                'thresholds' => $defaultThresholds,
                'highlight_enabled' => false,
                'source' => 'teacher',
            ];
        }

        // Teacher has custom thresholds
        if (!empty($teacherPref->thresholds)) {
            $thresholds = $teacherPref->thresholds;
            usort($thresholds, fn($a, $b) => $a['max_percentage'] <=> $b['max_percentage']);

            return [
                'thresholds' => $thresholds,
                'highlight_enabled' => true,
                'source' => 'teacher',
            ];
        }

        // Teacher preference exists but no custom thresholds - use system defaults
        return [
            'thresholds' => $defaultThresholds,
            'highlight_enabled' => true,
            'source' => 'system',
        ];
    }

    /**
     * Update or create teacher preference with proper locking.
     */
    public static function upsertPreference(int $userId, array $data): self
    {
        return DB::transaction(function () use ($userId, $data) {
            // Use pessimistic locking to prevent race conditions
            $preference = static::where('user_id', $userId)->lockForUpdate()->first();

            if ($preference) {
                $preference->update($data);
                return $preference->fresh();
            }

            return static::create(array_merge(['user_id' => $userId], $data));
        });
    }

    /**
     * Reset teacher preference to system defaults
     */
    public function resetToDefaults(): self
    {
        $this->update([
            'thresholds' => null,
            'highlight_enabled' => true,
        ]);

        return $this->fresh();
    }
}
