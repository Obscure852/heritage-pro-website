<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RubricScore extends Model {
    protected $table = 'lms_rubric_scores';

    protected $fillable = [
        'grade_id',
        'criterion_id',
        'level_id',
        'score',
        'feedback',
    ];

    protected $casts = [
        'score' => 'decimal:2',
    ];

    // Relationships
    public function grade(): BelongsTo {
        return $this->belongsTo(Grade::class, 'grade_id');
    }

    public function criterion(): BelongsTo {
        return $this->belongsTo(RubricCriterion::class, 'criterion_id');
    }

    public function level(): BelongsTo {
        return $this->belongsTo(RubricLevel::class, 'level_id');
    }

    // Accessors
    public function getEffectiveScoreAttribute(): float {
        // If a manual score is set, use it; otherwise use the level's points
        return $this->score ?? $this->level?->points ?? 0;
    }

    // Methods
    public static function scoreFromLevel(Grade $grade, RubricCriterion $criterion, RubricLevel $level, ?string $feedback = null): self {
        return self::updateOrCreate(
            [
                'grade_id' => $grade->id,
                'criterion_id' => $criterion->id,
            ],
            [
                'level_id' => $level->id,
                'score' => $level->points,
                'feedback' => $feedback,
            ]
        );
    }

    public static function scoreManual(Grade $grade, RubricCriterion $criterion, float $score, ?string $feedback = null): self {
        return self::updateOrCreate(
            [
                'grade_id' => $grade->id,
                'criterion_id' => $criterion->id,
            ],
            [
                'level_id' => null,
                'score' => $score,
                'feedback' => $feedback,
            ]
        );
    }
}
