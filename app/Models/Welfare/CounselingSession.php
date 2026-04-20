<?php

namespace App\Models\Welfare;

use App\Models\Student;
use App\Models\User;
use App\Traits\Welfare\Auditable;
use App\Traits\Welfare\Encryptable;
use App\Traits\Welfare\HasTermScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Counseling session record model.
 *
 * Contains confidential counseling notes with Level 4 encryption.
 *
 * @property int $id
 * @property int $welfare_case_id
 * @property int $student_id
 * @property int $counsellor_id
 * @property string $session_type
 * @property \Carbon\Carbon $session_date
 * @property string|null $session_time
 * @property int $duration_minutes
 * @property string|null $presenting_issue
 * @property string|null $session_notes
 * @property string|null $interventions_used
 * @property string|null $student_mood
 * @property string|null $risk_assessment
 * @property string|null $goals_discussed
 * @property string|null $homework_assigned
 * @property bool $follow_up_required
 * @property \Carbon\Carbon|null $next_session_date
 * @property string $status
 * @property int $confidentiality_level
 * @property int $term_id
 * @property int $year
 */
class CounselingSession extends Model
{
    use HasFactory, SoftDeletes, HasTermScope, Auditable, Encryptable;

    protected $fillable = [
        'welfare_case_id',
        'student_id',
        'counsellor_id',
        'session_type',
        'session_date',
        'session_time',
        'duration_minutes',
        'presenting_issue',
        'session_notes',
        'interventions_used',
        'student_mood',
        'risk_assessment',
        'goals_discussed',
        'homework_assigned',
        'follow_up_required',
        'next_session_date',
        'status',
        'confidentiality_level',
        'term_id',
        'year',
    ];

    protected $casts = [
        'session_date' => 'date',
        'next_session_date' => 'date',
        'follow_up_required' => 'boolean',
        'duration_minutes' => 'integer',
        'confidentiality_level' => 'integer',
    ];

    /**
     * Fields that should be encrypted (Level 4 confidential).
     */
    protected array $encryptable = [
        'session_notes',
        'risk_assessment',
    ];

    // Session type constants
    public const TYPE_INDIVIDUAL = 'individual';
    public const TYPE_FOLLOW_UP = 'follow_up';
    public const TYPE_CRISIS = 'crisis';
    public const TYPE_GROUP = 'group';
    public const TYPE_FAMILY = 'family';

    // Status constants
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_NO_SHOW = 'no_show';

    // Mood constants
    public const MOOD_VERY_LOW = 'very_low';
    public const MOOD_LOW = 'low';
    public const MOOD_NEUTRAL = 'neutral';
    public const MOOD_GOOD = 'good';
    public const MOOD_VERY_GOOD = 'very_good';

    // Risk level constants
    public const RISK_NONE = 'none';
    public const RISK_LOW = 'low';
    public const RISK_MODERATE = 'moderate';
    public const RISK_HIGH = 'high';
    public const RISK_CRITICAL = 'critical';

    // ==================== RELATIONSHIPS ====================

    public function welfareCase()
    {
        return $this->belongsTo(WelfareCase::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function counsellor()
    {
        return $this->belongsTo(User::class, 'counsellor_id');
    }

    // ==================== SCOPES ====================

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    public function scopeForCounsellor(Builder $query, int $userId): Builder
    {
        return $query->where('counsellor_id', $userId);
    }

    public function scopeRequiringFollowUp(Builder $query): Builder
    {
        return $query->where('follow_up_required', true)
            ->whereNull('next_session_date');
    }

    public function scopeHighRisk(Builder $query): Builder
    {
        return $query->whereIn('risk_assessment', [self::RISK_HIGH, self::RISK_CRITICAL]);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SCHEDULED)
            ->where('session_date', '>=', now()->toDateString())
            ->orderBy('session_date')
            ->orderBy('session_time');
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('session_date', now()->toDateString());
    }

    // ==================== HELPER METHODS ====================

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isScheduled(): bool
    {
        return $this->status === self::STATUS_SCHEDULED;
    }

    public function isCrisis(): bool
    {
        return $this->session_type === self::TYPE_CRISIS;
    }

    public function isHighRisk(): bool
    {
        return in_array($this->risk_assessment, [self::RISK_HIGH, self::RISK_CRITICAL]);
    }

    public function requiresFollowUp(): bool
    {
        return $this->follow_up_required && is_null($this->next_session_date);
    }

    /**
     * Mark session as completed.
     */
    public function complete(): bool
    {
        return $this->update(['status' => self::STATUS_COMPLETED]);
    }

    /**
     * Mark session as cancelled.
     */
    public function cancel(): bool
    {
        return $this->update(['status' => self::STATUS_CANCELLED]);
    }

    /**
     * Mark as no-show.
     */
    public function markNoShow(): bool
    {
        return $this->update(['status' => self::STATUS_NO_SHOW]);
    }

    /**
     * Get mood badge color for UI.
     */
    public function getMoodColorAttribute(): string
    {
        return match ($this->student_mood) {
            self::MOOD_VERY_LOW => 'red',
            self::MOOD_LOW => 'orange',
            self::MOOD_NEUTRAL => 'gray',
            self::MOOD_GOOD => 'blue',
            self::MOOD_VERY_GOOD => 'green',
            default => 'gray',
        };
    }

    /**
     * Get risk badge color for UI.
     */
    public function getRiskColorAttribute(): string
    {
        return match ($this->risk_assessment) {
            self::RISK_NONE => 'green',
            self::RISK_LOW => 'blue',
            self::RISK_MODERATE => 'yellow',
            self::RISK_HIGH => 'orange',
            self::RISK_CRITICAL => 'red',
            default => 'gray',
        };
    }

    /**
     * Get status badge color for UI.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_SCHEDULED => 'blue',
            self::STATUS_COMPLETED => 'green',
            self::STATUS_CANCELLED => 'gray',
            self::STATUS_NO_SHOW => 'red',
            default => 'gray',
        };
    }
}
