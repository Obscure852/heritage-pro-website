<?php

namespace App\Models\Welfare;

use App\Models\Student;
use App\Models\Term;
use App\Models\User;
use App\Traits\Welfare\Auditable;
use App\Traits\Welfare\HasTermScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Parent communication model.
 *
 * Tracks all communications with parents regarding welfare matters.
 */
class ParentCommunication extends Model
{
    use HasFactory, SoftDeletes, HasTermScope, Auditable;

    protected $fillable = [
        'welfare_case_id',
        'student_id',
        'term_id',
        'year',
        'communication_date',
        'communication_time',
        'type',
        'method',
        'direction',
        'staff_member_id',
        'parent_guardian_name',
        'relationship',
        'contact_used',
        'subject',
        'summary',
        'detailed_notes',
        'meeting_location',
        'meeting_duration_minutes',
        'meeting_attendees',
        'outcome',
        'action_items',
        'follow_up_required',
        'follow_up_date',
        'follow_up_completed',
        'parent_response',
        'parent_concerns',
    ];

    protected $casts = [
        'communication_date' => 'date',
        'follow_up_date' => 'date',
        'follow_up_required' => 'boolean',
        'follow_up_completed' => 'boolean',
        'meeting_duration_minutes' => 'integer',
    ];

    // Type constants
    public const TYPE_WELFARE_UPDATE = 'welfare_update';
    public const TYPE_CONCERN = 'concern';
    public const TYPE_POSITIVE_FEEDBACK = 'positive_feedback';
    public const TYPE_MEETING = 'meeting';
    public const TYPE_INCIDENT_NOTIFICATION = 'incident_notification';
    public const TYPE_GENERAL = 'general';

    // Method constants
    public const METHOD_PHONE = 'phone';
    public const METHOD_EMAIL = 'email';
    public const METHOD_SMS = 'sms';
    public const METHOD_IN_PERSON = 'in_person';
    public const METHOD_VIDEO_CALL = 'video_call';
    public const METHOD_LETTER = 'letter';
    public const METHOD_HOME_VISIT = 'home_visit';

    // Direction constants
    public const DIRECTION_OUTBOUND = 'outbound';
    public const DIRECTION_INBOUND = 'inbound';

    // ==================== RELATIONSHIPS ====================

    public function welfareCase()
    {
        return $this->belongsTo(WelfareCase::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function staffMember()
    {
        return $this->belongsTo(User::class, 'staff_member_id');
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    // ==================== SCOPES ====================

    public function scopeOutbound(Builder $query): Builder
    {
        return $query->where('direction', self::DIRECTION_OUTBOUND);
    }

    public function scopeInbound(Builder $query): Builder
    {
        return $query->where('direction', self::DIRECTION_INBOUND);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeByMethod(Builder $query, string $method): Builder
    {
        return $query->where('method', $method);
    }

    public function scopeRequiringFollowUp(Builder $query): Builder
    {
        return $query->where('follow_up_required', true)
            ->where('follow_up_completed', false);
    }

    public function scopeOverdueFollowUp(Builder $query): Builder
    {
        return $query->where('follow_up_required', true)
            ->where('follow_up_completed', false)
            ->whereNotNull('follow_up_date')
            ->where('follow_up_date', '<', now()->toDateString());
    }

    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('communication_date', '>=', now()->subDays($days));
    }

    public function scopeByStaffMember(Builder $query, int $userId): Builder
    {
        return $query->where('staff_member_id', $userId);
    }

    public function scopeMeetings(Builder $query): Builder
    {
        return $query->where('method', self::METHOD_IN_PERSON);
    }

    // ==================== HELPER METHODS ====================

    public function isOutbound(): bool
    {
        return $this->direction === self::DIRECTION_OUTBOUND;
    }

    public function isInbound(): bool
    {
        return $this->direction === self::DIRECTION_INBOUND;
    }

    public function isMeeting(): bool
    {
        return $this->method === self::METHOD_IN_PERSON;
    }

    public function requiresFollowUp(): bool
    {
        return $this->follow_up_required && !$this->follow_up_completed;
    }

    public function isFollowUpOverdue(): bool
    {
        return $this->requiresFollowUp() &&
            $this->follow_up_date &&
            $this->follow_up_date->isPast();
    }

    /**
     * Mark follow-up as completed.
     */
    public function completeFollowUp(?string $outcome = null): bool
    {
        return $this->update([
            'follow_up_completed' => true,
            'outcome' => $outcome ?? $this->outcome,
        ]);
    }

    /**
     * Schedule a follow-up.
     */
    public function scheduleFollowUp(\Carbon\Carbon $date): bool
    {
        return $this->update([
            'follow_up_required' => true,
            'follow_up_date' => $date,
            'follow_up_completed' => false,
        ]);
    }

    /**
     * Get method icon for UI.
     */
    public function getMethodIconAttribute(): string
    {
        return match ($this->method) {
            self::METHOD_PHONE => 'phone',
            self::METHOD_EMAIL => 'mail',
            self::METHOD_SMS => 'message-circle',
            self::METHOD_IN_PERSON => 'users',
            self::METHOD_VIDEO_CALL => 'video',
            self::METHOD_LETTER => 'file-text',
            self::METHOD_HOME_VISIT => 'home',
            default => 'message-square',
        };
    }

    /**
     * Get days until follow-up.
     */
    public function getDaysUntilFollowUpAttribute(): ?int
    {
        if (!$this->requiresFollowUp() || !$this->follow_up_date) {
            return null;
        }

        return now()->diffInDays($this->follow_up_date, false);
    }
}
