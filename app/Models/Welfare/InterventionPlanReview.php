<?php

namespace App\Models\Welfare;

use App\Models\User;
use App\Traits\Welfare\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Intervention plan review model.
 *
 * Tracks periodic reviews of intervention plans.
 *
 * @property int $id
 * @property int $intervention_plan_id
 * @property int $reviewed_by
 * @property \Carbon\Carbon $review_date
 * @property string $progress_status
 * @property string|null $objectives_progress
 * @property string|null $strategies_effectiveness
 * @property string|null $challenges_encountered
 * @property string|null $adjustments_made
 * @property string|null $next_steps
 * @property bool $parent_attended
 * @property string|null $parent_feedback
 * @property bool $student_involved
 * @property string|null $student_feedback
 * @property string $recommendation
 * @property \Carbon\Carbon|null $next_review_date
 */
class InterventionPlanReview extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'intervention_plan_id',
        'reviewed_by',
        'review_date',
        'progress_status',
        'objectives_progress',
        'strategies_effectiveness',
        'challenges_encountered',
        'adjustments_made',
        'next_steps',
        'parent_attended',
        'parent_feedback',
        'student_involved',
        'student_feedback',
        'recommendation',
        'next_review_date',
    ];

    protected $casts = [
        'review_date' => 'date',
        'parent_attended' => 'boolean',
        'student_involved' => 'boolean',
        'next_review_date' => 'date',
    ];

    // Progress status constants
    public const PROGRESS_EXCELLENT = 'excellent';
    public const PROGRESS_GOOD = 'good';
    public const PROGRESS_SATISFACTORY = 'satisfactory';
    public const PROGRESS_LIMITED = 'limited';
    public const PROGRESS_NONE = 'none';
    public const PROGRESS_REGRESSED = 'regressed';

    // Recommendation constants
    public const RECOMMEND_CONTINUE = 'continue';
    public const RECOMMEND_MODIFY = 'modify';
    public const RECOMMEND_INTENSIFY = 'intensify';
    public const RECOMMEND_REDUCE = 'reduce';
    public const RECOMMEND_COMPLETE = 'complete';
    public const RECOMMEND_DISCONTINUE = 'discontinue';

    // ==================== RELATIONSHIPS ====================

    public function interventionPlan()
    {
        return $this->belongsTo(InterventionPlan::class);
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ==================== SCOPES ====================

    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('review_date', '>=', now()->subDays($days));
    }

    public function scopeWithParentAttendance(Builder $query): Builder
    {
        return $query->where('parent_attended', true);
    }

    public function scopeWithStudentInvolvement(Builder $query): Builder
    {
        return $query->where('student_involved', true);
    }

    public function scopeShowingProgress(Builder $query): Builder
    {
        return $query->whereIn('progress_status', [
            self::PROGRESS_EXCELLENT,
            self::PROGRESS_GOOD,
            self::PROGRESS_SATISFACTORY,
        ]);
    }

    public function scopeShowingConcern(Builder $query): Builder
    {
        return $query->whereIn('progress_status', [
            self::PROGRESS_LIMITED,
            self::PROGRESS_NONE,
            self::PROGRESS_REGRESSED,
        ]);
    }

    public function scopeByReviewer(Builder $query, int $userId): Builder
    {
        return $query->where('reviewed_by', $userId);
    }

    // ==================== HELPER METHODS ====================

    public function isShowingProgress(): bool
    {
        return in_array($this->progress_status, [
            self::PROGRESS_EXCELLENT,
            self::PROGRESS_GOOD,
            self::PROGRESS_SATISFACTORY,
        ]);
    }

    public function isShowingConcern(): bool
    {
        return in_array($this->progress_status, [
            self::PROGRESS_LIMITED,
            self::PROGRESS_NONE,
            self::PROGRESS_REGRESSED,
        ]);
    }

    public function hadParentAttendance(): bool
    {
        return $this->parent_attended;
    }

    public function hadStudentInvolvement(): bool
    {
        return $this->student_involved;
    }

    public function recommendsContinuation(): bool
    {
        return in_array($this->recommendation, [
            self::RECOMMEND_CONTINUE,
            self::RECOMMEND_MODIFY,
            self::RECOMMEND_INTENSIFY,
            self::RECOMMEND_REDUCE,
        ]);
    }

    public function recommendsConclusion(): bool
    {
        return in_array($this->recommendation, [
            self::RECOMMEND_COMPLETE,
            self::RECOMMEND_DISCONTINUE,
        ]);
    }

    /**
     * Get progress status badge color for UI.
     */
    public function getProgressColorAttribute(): string
    {
        return match ($this->progress_status) {
            self::PROGRESS_EXCELLENT => 'green',
            self::PROGRESS_GOOD => 'teal',
            self::PROGRESS_SATISFACTORY => 'blue',
            self::PROGRESS_LIMITED => 'yellow',
            self::PROGRESS_NONE => 'orange',
            self::PROGRESS_REGRESSED => 'red',
            default => 'gray',
        };
    }

    /**
     * Get recommendation badge color for UI.
     */
    public function getRecommendationColorAttribute(): string
    {
        return match ($this->recommendation) {
            self::RECOMMEND_CONTINUE => 'green',
            self::RECOMMEND_MODIFY => 'blue',
            self::RECOMMEND_INTENSIFY => 'orange',
            self::RECOMMEND_REDUCE => 'yellow',
            self::RECOMMEND_COMPLETE => 'teal',
            self::RECOMMEND_DISCONTINUE => 'red',
            default => 'gray',
        };
    }
}
