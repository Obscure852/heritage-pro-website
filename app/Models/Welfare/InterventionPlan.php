<?php

namespace App\Models\Welfare;

use App\Models\Student;
use App\Models\User;
use App\Traits\Welfare\Auditable;
use App\Traits\Welfare\HasTermScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Intervention plan model.
 *
 * Tracks structured intervention plans for students with welfare concerns.
 *
 * @property int $id
 * @property int $welfare_case_id
 * @property int $student_id
 * @property int $created_by
 * @property string $plan_type
 * @property string $title
 * @property string|null $objectives
 * @property string|null $strategies
 * @property string|null $resources_required
 * @property string|null $success_criteria
 * @property \Carbon\Carbon $start_date
 * @property \Carbon\Carbon|null $target_end_date
 * @property \Carbon\Carbon|null $actual_end_date
 * @property string $status
 * @property string|null $review_frequency
 * @property \Carbon\Carbon|null $next_review_date
 * @property string|null $stakeholders
 * @property bool $parent_consent_obtained
 * @property \Carbon\Carbon|null $parent_consent_date
 * @property string|null $progress_notes
 * @property string|null $outcome
 * @property int $term_id
 * @property int $year
 */
class InterventionPlan extends Model
{
    use HasFactory, SoftDeletes, HasTermScope, Auditable;

    protected $fillable = [
        'welfare_case_id',
        'student_id',
        'created_by',
        'plan_type',
        'title',
        'objectives',
        'strategies',
        'resources_required',
        'success_criteria',
        'start_date',
        'target_end_date',
        'actual_end_date',
        'status',
        'review_frequency',
        'next_review_date',
        'stakeholders',
        'parent_consent_obtained',
        'parent_consent_date',
        'progress_notes',
        'outcome',
        'term_id',
        'year',
    ];

    protected $casts = [
        'start_date' => 'date',
        'target_end_date' => 'date',
        'actual_end_date' => 'date',
        'next_review_date' => 'date',
        'parent_consent_obtained' => 'boolean',
        'parent_consent_date' => 'date',
    ];

    // Plan type constants
    public const TYPE_BEHAVIORAL = 'behavioral';
    public const TYPE_ACADEMIC = 'academic';
    public const TYPE_EMOTIONAL = 'emotional';
    public const TYPE_SOCIAL = 'social';
    public const TYPE_ATTENDANCE = 'attendance';
    public const TYPE_COMBINED = 'combined';

    // Status constants
    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_ON_HOLD = 'on_hold';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_DISCONTINUED = 'discontinued';

    // Review frequency constants
    public const REVIEW_WEEKLY = 'weekly';
    public const REVIEW_FORTNIGHTLY = 'fortnightly';
    public const REVIEW_MONTHLY = 'monthly';
    public const REVIEW_TERMLY = 'termly';

    // ==================== RELATIONSHIPS ====================

    public function welfareCase()
    {
        return $this->belongsTo(WelfareCase::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviews()
    {
        return $this->hasMany(InterventionPlanReview::class);
    }

    public function latestReview()
    {
        return $this->hasOne(InterventionPlanReview::class)->latestOfMany();
    }

    // ==================== SCOPES ====================

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->whereNotNull('target_end_date')
            ->where('target_end_date', '<', now()->toDateString());
    }

    public function scopeReviewDue(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->whereNotNull('next_review_date')
            ->where('next_review_date', '<=', now()->toDateString());
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('plan_type', $type);
    }

    public function scopeAwaitingConsent(Builder $query): Builder
    {
        return $query->where('parent_consent_obtained', false);
    }

    public function scopeCreatedBy(Builder $query, int $userId): Builder
    {
        return $query->where('created_by', $userId);
    }

    // ==================== HELPER METHODS ====================

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isOverdue(): bool
    {
        return $this->isActive() &&
            $this->target_end_date &&
            $this->target_end_date->isPast();
    }

    public function isReviewDue(): bool
    {
        return $this->isActive() &&
            $this->next_review_date &&
            $this->next_review_date->isPast();
    }

    public function hasParentConsent(): bool
    {
        return $this->parent_consent_obtained;
    }

    /**
     * Activate the plan.
     */
    public function activate(): bool
    {
        if ($this->status !== self::STATUS_DRAFT) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_ACTIVE,
            'start_date' => $this->start_date ?? now(),
        ]);
    }

    /**
     * Put plan on hold.
     */
    public function putOnHold(): bool
    {
        return $this->update(['status' => self::STATUS_ON_HOLD]);
    }

    /**
     * Resume plan.
     */
    public function resume(): bool
    {
        if ($this->status !== self::STATUS_ON_HOLD) {
            return false;
        }

        return $this->update(['status' => self::STATUS_ACTIVE]);
    }

    /**
     * Complete the plan.
     */
    public function complete(?string $outcome = null): bool
    {
        return $this->update([
            'status' => self::STATUS_COMPLETED,
            'actual_end_date' => now(),
            'outcome' => $outcome,
        ]);
    }

    /**
     * Discontinue the plan.
     */
    public function discontinue(?string $reason = null): bool
    {
        return $this->update([
            'status' => self::STATUS_DISCONTINUED,
            'actual_end_date' => now(),
            'outcome' => $reason,
        ]);
    }

    /**
     * Record parent consent.
     */
    public function recordParentConsent(): bool
    {
        return $this->update([
            'parent_consent_obtained' => true,
            'parent_consent_date' => now(),
        ]);
    }

    /**
     * Schedule next review based on frequency.
     */
    public function scheduleNextReview(): bool
    {
        $nextDate = match ($this->review_frequency) {
            self::REVIEW_WEEKLY => now()->addWeek(),
            self::REVIEW_FORTNIGHTLY => now()->addWeeks(2),
            self::REVIEW_MONTHLY => now()->addMonth(),
            self::REVIEW_TERMLY => now()->addMonths(3),
            default => null,
        };

        if (!$nextDate) {
            return false;
        }

        return $this->update(['next_review_date' => $nextDate]);
    }

    /**
     * Get status badge color for UI.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'gray',
            self::STATUS_ACTIVE => 'green',
            self::STATUS_ON_HOLD => 'yellow',
            self::STATUS_COMPLETED => 'blue',
            self::STATUS_DISCONTINUED => 'red',
            default => 'gray',
        };
    }

    /**
     * Get plan type badge color for UI.
     */
    public function getTypeColorAttribute(): string
    {
        return match ($this->plan_type) {
            self::TYPE_BEHAVIORAL => 'orange',
            self::TYPE_ACADEMIC => 'blue',
            self::TYPE_EMOTIONAL => 'purple',
            self::TYPE_SOCIAL => 'teal',
            self::TYPE_ATTENDANCE => 'yellow',
            self::TYPE_COMBINED => 'gray',
            default => 'gray',
        };
    }

    /**
     * Get days until target end date.
     */
    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->target_end_date || !$this->isActive()) {
            return null;
        }

        return now()->diffInDays($this->target_end_date, false);
    }
}
