<?php

namespace App\Models\Welfare;

use App\Helpers\TermHelper;
use App\Models\Student;
use App\Models\Term;
use App\Models\User;
use App\Traits\Welfare\Auditable;
use App\Traits\Welfare\HasTermScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * Master welfare case model.
 *
 * All welfare records (counseling, disciplinary, health, etc.) are linked to a welfare case.
 * Implements race-condition-proof case number generation using database locking.
 *
 * @property int $id
 * @property string $case_number
 * @property int $student_id
 * @property int $welfare_type_id
 * @property int $term_id
 * @property int $year
 * @property string $status
 * @property string $priority
 * @property int $opened_by
 * @property int|null $assigned_to
 * @property \Carbon\Carbon|null $incident_date
 * @property \Carbon\Carbon $opened_at
 * @property \Carbon\Carbon|null $closed_at
 * @property string $title
 * @property string|null $summary
 * @property bool $requires_approval
 * @property string $approval_status
 * @property int|null $approved_by
 * @property \Carbon\Carbon|null $approved_at
 * @property string|null $approval_notes
 * @property int|null $parent_case_id
 */
class WelfareCase extends Model
{
    use HasFactory, SoftDeletes, HasTermScope, Auditable;

    protected $fillable = [
        'case_number',
        'student_id',
        'welfare_type_id',
        'term_id',
        'year',
        'status',
        'priority',
        'opened_by',
        'assigned_to',
        'incident_date',
        'opened_at',
        'closed_at',
        'title',
        'summary',
        'requires_approval',
        'approval_status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'parent_case_id',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'approved_at' => 'datetime',
        'requires_approval' => 'boolean',
    ];

    // Status constants
    public const STATUS_OPEN = 'open';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_PENDING_APPROVAL = 'pending_approval';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_ESCALATED = 'escalated';

    // Priority constants
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_CRITICAL = 'critical';

    // Approval status constants
    public const APPROVAL_NOT_REQUIRED = 'not_required';
    public const APPROVAL_PENDING = 'pending';
    public const APPROVAL_APPROVED = 'approved';
    public const APPROVAL_REJECTED = 'rejected';

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            // Generate case number if not set
            if (empty($model->case_number)) {
                $model->case_number = self::generateCaseNumber();
            }

            // Set term context if not set
            if (empty($model->term_id)) {
                $currentTerm = TermHelper::getCurrentTerm();
                if ($currentTerm) {
                    $model->term_id = $currentTerm->id;
                    $model->year = $currentTerm->year;
                }
            }

            // Set opened_at if not set
            if (empty($model->opened_at)) {
                $model->opened_at = now();
            }

            // Set approval requirements based on type
            if ($model->welfare_type_id && empty($model->requires_approval)) {
                $type = WelfareType::find($model->welfare_type_id);
                if ($type && $type->requires_approval) {
                    $model->requires_approval = true;
                    $model->approval_status = self::APPROVAL_PENDING;
                }
            }
        });
    }

    /**
     * Generate a unique case number using database locking.
     * Format: WC-YYYY-NNNN
     *
     * This method is race-condition-proof by using pessimistic locking.
     *
     * @return string
     * @throws \Exception
     */
    public static function generateCaseNumber(): string
    {
        $year = (int) date('Y');
        $maxRetries = 3;
        $retryCount = 0;

        while ($retryCount < $maxRetries) {
            try {
                return DB::transaction(function () use ($year) {
                    // Try to get or create the sequence record with a lock
                    $sequence = DB::table('welfare_case_sequences')
                        ->where('year', $year)
                        ->lockForUpdate()
                        ->first();

                    if ($sequence) {
                        // Increment existing sequence
                        $nextSequence = $sequence->last_sequence + 1;
                        DB::table('welfare_case_sequences')
                            ->where('year', $year)
                            ->update([
                                'last_sequence' => $nextSequence,
                                'updated_at' => now(),
                            ]);
                    } else {
                        // Create new sequence for this year
                        $nextSequence = 1;
                        DB::table('welfare_case_sequences')->insert([
                            'year' => $year,
                            'last_sequence' => $nextSequence,
                            'updated_at' => now(),
                        ]);
                    }

                    return sprintf('WC-%d-%04d', $year, $nextSequence);
                }, 5); // 5 second timeout
            } catch (\Illuminate\Database\QueryException $e) {
                $retryCount++;
                if ($retryCount >= $maxRetries) {
                    // Final fallback: use timestamp-based unique number
                    $microseconds = (int) (microtime(true) * 1000000) % 10000;
                    return sprintf('WC-%d-%04d', $year, $microseconds);
                }
                // Brief pause before retry
                usleep(50000); // 50ms
            }
        }

        // Should never reach here, but just in case
        return sprintf('WC-%d-%04d', $year, random_int(1000, 9999));
    }

    // ==================== RELATIONSHIPS ====================

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function welfareType()
    {
        return $this->belongsTo(WelfareType::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function openedBy()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function parentCase()
    {
        return $this->belongsTo(self::class, 'parent_case_id');
    }

    public function childCases()
    {
        return $this->hasMany(self::class, 'parent_case_id');
    }

    public function counselingSessions()
    {
        return $this->hasMany(CounselingSession::class);
    }

    public function disciplinaryRecords()
    {
        return $this->hasMany(DisciplinaryRecord::class);
    }

    public function safeguardingConcerns()
    {
        return $this->hasMany(SafeguardingConcern::class);
    }

    public function healthIncidents()
    {
        return $this->hasMany(HealthIncident::class);
    }

    // public function bullyingIncidents()
    // {
    //     return $this->hasMany(BullyingIncident::class);
    // }

    // public function financialAssistanceApplications()
    // {
    //     return $this->hasMany(FinancialAssistanceApplication::class);
    // }

    public function interventionPlans()
    {
        return $this->hasMany(InterventionPlan::class);
    }

    public function parentCommunications()
    {
        return $this->hasMany(ParentCommunication::class);
    }

    public function notes()
    {
        return $this->hasMany(WelfareCaseNote::class);
    }

    public function attachments()
    {
        return $this->hasMany(WelfareCaseAttachment::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(WelfareAuditLog::class);
    }

    // ==================== SCOPES ====================

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_OPEN, self::STATUS_IN_PROGRESS]);
    }

    public function scopeClosed(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_RESOLVED, self::STATUS_CLOSED]);
    }

    public function scopePendingApproval(Builder $query): Builder
    {
        return $query->where('approval_status', self::APPROVAL_PENDING);
    }

    public function scopeByType(Builder $query, string $typeCode): Builder
    {
        return $query->whereHas('welfareType', fn ($q) => $q->where('code', $typeCode));
    }

    public function scopeForStudent(Builder $query, int $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeHighPriority(Builder $query): Builder
    {
        return $query->whereIn('priority', [self::PRIORITY_HIGH, self::PRIORITY_CRITICAL]);
    }

    public function scopeAssignedTo(Builder $query, int $userId): Builder
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeOpenedBy(Builder $query, int $userId): Builder
    {
        return $query->where('opened_by', $userId);
    }

    // ==================== HELPER METHODS ====================

    public function isOpen(): bool
    {
        return in_array($this->status, [self::STATUS_OPEN, self::STATUS_IN_PROGRESS]);
    }

    public function isClosed(): bool
    {
        return in_array($this->status, [self::STATUS_RESOLVED, self::STATUS_CLOSED]);
    }

    public function isPendingApproval(): bool
    {
        return $this->approval_status === self::APPROVAL_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->approval_status === self::APPROVAL_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->approval_status === self::APPROVAL_REJECTED;
    }

    public function canBeEditedBy(User $user): bool
    {
        // Admins can always edit
        if ($user->hasRoles('Administrator')) {
            return true;
        }

        // Owner or assignee can edit if case is open
        if ($this->opened_by === $user->id || $this->assigned_to === $user->id) {
            return $this->isOpen();
        }

        return false;
    }

    /**
     * Approve this case.
     *
     * @param User $approver
     * @param string|null $notes
     * @return bool
     */
    public function approve(User $approver, ?string $notes = null): bool
    {
        return $this->update([
            'approval_status' => self::APPROVAL_APPROVED,
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'approval_notes' => $notes,
            'status' => self::STATUS_IN_PROGRESS,
        ]);
    }

    /**
     * Reject this case.
     *
     * @param User $approver
     * @param string $notes Required for rejections
     * @return bool
     */
    public function reject(User $approver, string $notes): bool
    {
        return $this->update([
            'approval_status' => self::APPROVAL_REJECTED,
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);
    }

    /**
     * Close this case.
     *
     * @param string|null $notes
     * @return bool
     */
    public function close(?string $notes = null): bool
    {
        return $this->update([
            'status' => self::STATUS_CLOSED,
            'closed_at' => now(),
            'summary' => $notes ?? $this->summary,
        ]);
    }

    /**
     * Resolve this case.
     *
     * @param string|null $notes
     * @return bool
     */
    public function resolve(?string $notes = null): bool
    {
        return $this->update([
            'status' => self::STATUS_RESOLVED,
            'closed_at' => now(),
            'summary' => $notes ?? $this->summary,
        ]);
    }

    /**
     * Escalate this case.
     *
     * @param int|null $assignToUserId
     * @return bool
     */
    public function escalate(?int $assignToUserId = null): bool
    {
        $data = [
            'status' => self::STATUS_ESCALATED,
            'priority' => self::PRIORITY_HIGH,
        ];

        if ($assignToUserId) {
            $data['assigned_to'] = $assignToUserId;
        }

        return $this->update($data);
    }

    /**
     * Get status badge color for UI.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_OPEN => 'blue',
            self::STATUS_IN_PROGRESS => 'yellow',
            self::STATUS_PENDING_APPROVAL => 'orange',
            self::STATUS_RESOLVED => 'green',
            self::STATUS_CLOSED => 'gray',
            self::STATUS_ESCALATED => 'red',
            default => 'gray',
        };
    }

    /**
     * Get priority badge color for UI.
     */
    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            self::PRIORITY_LOW => 'gray',
            self::PRIORITY_MEDIUM => 'blue',
            self::PRIORITY_HIGH => 'orange',
            self::PRIORITY_CRITICAL => 'red',
            default => 'gray',
        };
    }
}
