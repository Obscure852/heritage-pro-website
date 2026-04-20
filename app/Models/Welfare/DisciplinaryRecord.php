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
 * Disciplinary record model.
 *
 * Tracks disciplinary incidents, actions taken, and resolutions.
 *
 * @property int $id
 * @property int $welfare_case_id
 * @property int $student_id
 * @property int $incident_type_id
 * @property int|null $action_id
 * @property int $reported_by
 * @property \Carbon\Carbon $incident_date
 * @property string|null $incident_time
 * @property string|null $location
 * @property string $description
 * @property string|null $witnesses
 * @property string|null $evidence
 * @property string $status
 * @property bool $parent_notified
 * @property \Carbon\Carbon|null $parent_notified_at
 * @property string|null $parent_response
 * @property \Carbon\Carbon|null $action_start_date
 * @property \Carbon\Carbon|null $action_end_date
 * @property string|null $action_notes
 * @property string|null $resolution
 * @property \Carbon\Carbon|null $resolved_at
 * @property int|null $resolved_by
 * @property int $term_id
 * @property int $year
 */
class DisciplinaryRecord extends Model
{
    use HasFactory, SoftDeletes, HasTermScope, Auditable;

    protected $fillable = [
        'welfare_case_id',
        'student_id',
        'incident_type_id',
        'action_id',
        'reported_by',
        'incident_date',
        'incident_time',
        'location',
        'description',
        'witnesses',
        'evidence',
        'status',
        'parent_notified',
        'parent_notified_at',
        'parent_response',
        'action_start_date',
        'action_end_date',
        'action_notes',
        'resolution',
        'resolved_at',
        'resolved_by',
        'term_id',
        'year',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'parent_notified' => 'boolean',
        'parent_notified_at' => 'datetime',
        'action_start_date' => 'date',
        'action_end_date' => 'date',
        'resolved_at' => 'datetime',
        'witnesses' => 'array',
        'evidence' => 'array',
    ];

    // Status constants
    public const STATUS_REPORTED = 'reported';
    public const STATUS_INVESTIGATING = 'investigating';
    public const STATUS_PENDING_ACTION = 'pending_action';
    public const STATUS_ACTION_IN_PROGRESS = 'action_in_progress';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_APPEALED = 'appealed';

    // ==================== RELATIONSHIPS ====================

    public function welfareCase()
    {
        return $this->belongsTo(WelfareCase::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function incidentType()
    {
        return $this->belongsTo(DisciplinaryIncidentType::class, 'incident_type_id');
    }

    public function action()
    {
        return $this->belongsTo(DisciplinaryAction::class, 'action_id');
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    // ==================== SCOPES ====================

    public function scopeUnresolved(Builder $query): Builder
    {
        return $query->whereNotIn('status', [self::STATUS_RESOLVED]);
    }

    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_RESOLVED);
    }

    public function scopePendingAction(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING_ACTION);
    }

    public function scopeByIncidentType(Builder $query, int $typeId): Builder
    {
        return $query->where('incident_type_id', $typeId);
    }

    public function scopeReportedBy(Builder $query, int $userId): Builder
    {
        return $query->where('reported_by', $userId);
    }

    public function scopeWithActiveAction(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTION_IN_PROGRESS)
            ->where('action_end_date', '>=', now()->toDateString());
    }

    public function scopeParentNotNotified(Builder $query): Builder
    {
        return $query->where('parent_notified', false);
    }

    // ==================== HELPER METHODS ====================

    public function isResolved(): bool
    {
        return $this->status === self::STATUS_RESOLVED;
    }

    public function hasActiveAction(): bool
    {
        return $this->status === self::STATUS_ACTION_IN_PROGRESS
            && $this->action_end_date
            && $this->action_end_date->isFuture();
    }

    public function isActionExpired(): bool
    {
        return $this->action_end_date && $this->action_end_date->isPast();
    }

    /**
     * Apply disciplinary action.
     */
    public function applyAction(int $actionId, \Carbon\Carbon $startDate, ?\Carbon\Carbon $endDate = null, ?string $notes = null): bool
    {
        return $this->update([
            'action_id' => $actionId,
            'action_start_date' => $startDate,
            'action_end_date' => $endDate,
            'action_notes' => $notes,
            'status' => self::STATUS_ACTION_IN_PROGRESS,
        ]);
    }

    /**
     * Resolve the disciplinary record.
     */
    public function resolve(User $resolver, ?string $resolution = null): bool
    {
        return $this->update([
            'status' => self::STATUS_RESOLVED,
            'resolved_at' => now(),
            'resolved_by' => $resolver->id,
            'resolution' => $resolution,
        ]);
    }

    /**
     * Record parent notification.
     */
    public function recordParentNotification(?string $response = null): bool
    {
        return $this->update([
            'parent_notified' => true,
            'parent_notified_at' => now(),
            'parent_response' => $response,
        ]);
    }

    /**
     * Get status badge color for UI.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_REPORTED => 'blue',
            self::STATUS_INVESTIGATING => 'yellow',
            self::STATUS_PENDING_ACTION => 'orange',
            self::STATUS_ACTION_IN_PROGRESS => 'red',
            self::STATUS_RESOLVED => 'green',
            self::STATUS_APPEALED => 'purple',
            default => 'gray',
        };
    }

    /**
     * Get days remaining on action (if applicable).
     */
    public function getActionDaysRemainingAttribute(): ?int
    {
        if (!$this->hasActiveAction()) {
            return null;
        }

        return now()->diffInDays($this->action_end_date, false);
    }
}
