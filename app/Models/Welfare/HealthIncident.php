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
 * Health incident model.
 *
 * Tracks student health incidents, treatments, and outcomes.
 *
 * @property int $id
 * @property int $welfare_case_id
 * @property int $student_id
 * @property int $incident_type_id
 * @property int $reported_by
 * @property \Carbon\Carbon $incident_date
 * @property string|null $incident_time
 * @property string|null $location
 * @property string $description
 * @property string|null $symptoms
 * @property string|null $treatment_given
 * @property int|null $treated_by
 * @property bool $medication_administered
 * @property string|null $medication_details
 * @property string $outcome
 * @property bool $parent_notified
 * @property \Carbon\Carbon|null $parent_notified_at
 * @property string|null $parent_response
 * @property bool $sent_home
 * @property \Carbon\Carbon|null $sent_home_at
 * @property string|null $collected_by
 * @property bool $ambulance_called
 * @property bool $hospital_visit
 * @property string|null $hospital_notes
 * @property string|null $follow_up_required
 * @property \Carbon\Carbon|null $follow_up_date
 * @property int $term_id
 * @property int $year
 */
class HealthIncident extends Model
{
    use HasFactory, SoftDeletes, HasTermScope, Auditable;

    protected $fillable = [
        'welfare_case_id',
        'student_id',
        'incident_type_id',
        'reported_by',
        'incident_date',
        'incident_time',
        'location',
        'description',
        'symptoms',
        'treatment_given',
        'treated_by',
        'medication_administered',
        'medication_details',
        'outcome',
        'parent_notified',
        'parent_notified_at',
        'parent_response',
        'sent_home',
        'sent_home_at',
        'collected_by',
        'ambulance_called',
        'hospital_visit',
        'hospital_notes',
        'follow_up_required',
        'follow_up_date',
        'term_id',
        'year',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'medication_administered' => 'boolean',
        'parent_notified' => 'boolean',
        'parent_notified_at' => 'datetime',
        'sent_home' => 'boolean',
        'sent_home_at' => 'datetime',
        'ambulance_called' => 'boolean',
        'hospital_visit' => 'boolean',
        'follow_up_date' => 'date',
    ];

    // Outcome constants
    public const OUTCOME_RETURNED_TO_CLASS = 'returned_to_class';
    public const OUTCOME_RESTED_AND_RETURNED = 'rested_and_returned';
    public const OUTCOME_SENT_HOME = 'sent_home';
    public const OUTCOME_HOSPITAL = 'hospital';
    public const OUTCOME_ONGOING_MONITORING = 'ongoing_monitoring';

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
        return $this->belongsTo(HealthIncidentType::class, 'incident_type_id');
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function treatedBy()
    {
        return $this->belongsTo(User::class, 'treated_by');
    }

    // ==================== SCOPES ====================

    public function scopeEmergency(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('ambulance_called', true)
                ->orWhere('hospital_visit', true);
        });
    }

    public function scopeSentHome(Builder $query): Builder
    {
        return $query->where('sent_home', true);
    }

    public function scopeRequiringFollowUp(Builder $query): Builder
    {
        return $query->whereNotNull('follow_up_required')
            ->where(function ($q) {
                $q->whereNull('follow_up_date')
                    ->orWhere('follow_up_date', '>=', now()->toDateString());
            });
    }

    public function scopeByIncidentType(Builder $query, int $typeId): Builder
    {
        return $query->where('incident_type_id', $typeId);
    }

    public function scopeParentNotNotified(Builder $query): Builder
    {
        return $query->where('parent_notified', false);
    }

    public function scopeWithMedication(Builder $query): Builder
    {
        return $query->where('medication_administered', true);
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('incident_date', now()->toDateString());
    }

    // ==================== HELPER METHODS ====================

    public function isEmergency(): bool
    {
        return $this->ambulance_called || $this->hospital_visit;
    }

    public function wasSentHome(): bool
    {
        return $this->sent_home;
    }

    public function requiresFollowUp(): bool
    {
        return !empty($this->follow_up_required) &&
            ($this->follow_up_date === null || $this->follow_up_date->isFuture());
    }

    public function requiresParentNotification(): bool
    {
        if ($this->parent_notified) {
            return false;
        }

        return $this->incidentType && $this->incidentType->requires_parent_notification;
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
     * Record student sent home.
     */
    public function recordSentHome(string $collectedBy): bool
    {
        return $this->update([
            'sent_home' => true,
            'sent_home_at' => now(),
            'collected_by' => $collectedBy,
            'outcome' => self::OUTCOME_SENT_HOME,
        ]);
    }

    /**
     * Record hospital visit.
     */
    public function recordHospitalVisit(?string $notes = null): bool
    {
        return $this->update([
            'hospital_visit' => true,
            'hospital_notes' => $notes,
            'outcome' => self::OUTCOME_HOSPITAL,
        ]);
    }

    /**
     * Get outcome badge color for UI.
     */
    public function getOutcomeColorAttribute(): string
    {
        return match ($this->outcome) {
            self::OUTCOME_RETURNED_TO_CLASS => 'green',
            self::OUTCOME_RESTED_AND_RETURNED => 'blue',
            self::OUTCOME_SENT_HOME => 'yellow',
            self::OUTCOME_HOSPITAL => 'red',
            self::OUTCOME_ONGOING_MONITORING => 'orange',
            default => 'gray',
        };
    }
}
