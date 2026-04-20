<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PassingThresholdSetting extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'school_type',
        'grade_id',
        'grade_subject_id',
        'test_type',
        'thresholds',
        'is_active',
    ];

    protected $casts = [
        'thresholds' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Default thresholds used when no settings exist
     */
    public const DEFAULT_THRESHOLDS = [
        ['name' => 'failing', 'max_percentage' => 39, 'color' => '#fee2e2'],
        ['name' => 'warning', 'max_percentage' => 49, 'color' => '#fef3c7'],
        ['name' => 'caution', 'max_percentage' => 59, 'color' => '#fefce8'],
    ];

    /**
     * Relationship to grade
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    /**
     * Relationship to grade subject
     */
    public function gradeSubject(): BelongsTo
    {
        return $this->belongsTo(GradeSubject::class);
    }

    /**
     * Scope for active settings
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the most specific applicable threshold setting using fallback chain.
     * Uses database-level ordering for efficiency and race-condition safety.
     *
     * Precedence (most specific first):
     * 1. Subject-specific (grade_subject_id set)
     * 2. Grade + Test Type (grade_id + test_type set)
     * 3. Grade only (grade_id set)
     * 4. School Type (school_type set)
     * 5. Global default (all null)
     */
    public static function getThreshold(
        ?string $schoolType = null,
        ?int $gradeId = null,
        ?int $gradeSubjectId = null,
        ?string $testType = null
    ): ?self {
        return static::active()
            ->where(function ($query) use ($schoolType, $gradeId, $gradeSubjectId, $testType) {
                // Match any of these scopes
                $query->where(function ($q) use ($gradeSubjectId) {
                    // Most specific: Subject-specific
                    $q->whereNotNull('grade_subject_id')
                      ->where('grade_subject_id', $gradeSubjectId);
                })
                ->orWhere(function ($q) use ($gradeId, $testType) {
                    // Grade + Test Type
                    $q->whereNotNull('grade_id')
                      ->whereNotNull('test_type')
                      ->where('grade_id', $gradeId)
                      ->where('test_type', $testType);
                })
                ->orWhere(function ($q) use ($gradeId) {
                    // Grade only
                    $q->whereNotNull('grade_id')
                      ->whereNull('test_type')
                      ->whereNull('grade_subject_id')
                      ->where('grade_id', $gradeId);
                })
                ->orWhere(function ($q) use ($schoolType) {
                    // School Type default
                    $q->whereNotNull('school_type')
                      ->whereNull('grade_id')
                      ->whereNull('grade_subject_id')
                      ->whereNull('test_type')
                      ->where('school_type', $schoolType);
                })
                ->orWhere(function ($q) {
                    // Global default
                    $q->whereNull('school_type')
                      ->whereNull('grade_id')
                      ->whereNull('grade_subject_id')
                      ->whereNull('test_type');
                });
            })
            ->orderByRaw('
                CASE
                    WHEN grade_subject_id IS NOT NULL THEN 1
                    WHEN grade_id IS NOT NULL AND test_type IS NOT NULL THEN 2
                    WHEN grade_id IS NOT NULL THEN 3
                    WHEN school_type IS NOT NULL THEN 4
                    ELSE 5
                END
            ')
            ->first();
    }

    /**
     * Create or update a threshold setting with proper locking to prevent race conditions.
     */
    public static function upsertSetting(array $criteria, array $data): self
    {
        return DB::transaction(function () use ($criteria, $data) {
            // Use pessimistic locking to prevent race conditions
            $setting = static::where($criteria)->lockForUpdate()->first();

            if ($setting) {
                $setting->update($data);
                return $setting->fresh();
            }

            return static::create(array_merge($criteria, $data));
        });
    }

    /**
     * Validate thresholds array structure
     */
    public static function validateThresholds(array $thresholds): bool
    {
        if (empty($thresholds)) {
            return false;
        }

        foreach ($thresholds as $threshold) {
            if (!isset($threshold['name'], $threshold['max_percentage'], $threshold['color'])) {
                return false;
            }

            if (!is_numeric($threshold['max_percentage']) ||
                $threshold['max_percentage'] < 0 ||
                $threshold['max_percentage'] > 100) {
                return false;
            }

            if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $threshold['color'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Sort thresholds by max_percentage ascending
     */
    public function getSortedThresholds(): array
    {
        $thresholds = $this->thresholds ?? self::DEFAULT_THRESHOLDS;

        usort($thresholds, function ($a, $b) {
            return $a['max_percentage'] <=> $b['max_percentage'];
        });

        return $thresholds;
    }
}
