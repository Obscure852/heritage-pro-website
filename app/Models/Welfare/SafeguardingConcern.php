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
 * Safeguarding concern model.
 *
 * Highly confidential - contains encrypted details (Level 4).
 * Tracks child protection concerns and responses.
 *
 * @property int $id
 * @property int $welfare_case_id
 * @property int $student_id
 * @property int $category_id
 * @property int $reported_by
 * @property string $risk_level
 * @property \Carbon\Carbon $date_identified
 * @property string $source_of_concern
 * @property string $concern_details
 * @property string|null $indicators_observed
 * @property string|null $disclosure_details
 * @property bool $immediate_action_taken
 * @property string|null $immediate_action_details
 * @property bool $authorities_notified
 * @property \Carbon\Carbon|null $authorities_notified_at
 * @property string|null $authority_reference
 * @property bool $parents_informed
 * @property \Carbon\Carbon|null $parents_informed_at
 * @property string|null $parent_response
 * @property string $status
 * @property string|null $outcome
 * @property \Carbon\Carbon|null $closed_at
 * @property int|null $closed_by
 * @property int $term_id
 * @property int $year
 */
class SafeguardingConcern extends Model
{
    use HasFactory, SoftDeletes, HasTermScope, Auditable, Encryptable;

    protected $fillable = [
        'welfare_case_id',
        'student_id',
        'category_id',
        'reported_by',
        'risk_level',
        'date_identified',
        'source_of_concern',
        'concern_details',
        'indicators_observed',
        'disclosure_details',
        'immediate_action_taken',
        'immediate_action_details',
        'authorities_notified',
        'authorities_notified_at',
        'authority_reference',
        'parents_informed',
        'parents_informed_at',
        'parent_response',
        'status',
        'outcome',
        'closed_at',
        'closed_by',
        'term_id',
        'year',
    ];

    protected $casts = [
        'date_identified' => 'date',
        'immediate_action_taken' => 'boolean',
        'authorities_notified' => 'boolean',
        'authorities_notified_at' => 'datetime',
        'parents_informed' => 'boolean',
        'parents_informed_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    /**
     * Fields that should be encrypted (Level 4 highly confidential).
     */
    protected array $encryptable = [
        'concern_details',
        'disclosure_details',
        'immediate_action_details',
    ];

    // Risk level constants
    public const RISK_LOW = 'low';
    public const RISK_MEDIUM = 'medium';
    public const RISK_HIGH = 'high';
    public const RISK_CRITICAL = 'critical';

    // Status constants
    public const STATUS_IDENTIFIED = 'identified';
    public const STATUS_INVESTIGATING = 'investigating';
    public const STATUS_REFERRED = 'referred';
    public const STATUS_MONITORING = 'monitoring';
    public const STATUS_CLOSED = 'closed';

    // Source constants
    public const SOURCE_STUDENT_DISCLOSURE = 'student_disclosure';
    public const SOURCE_STAFF_OBSERVATION = 'staff_observation';
    public const SOURCE_PARENT_REPORT = 'parent_report';
    public const SOURCE_PEER_REPORT = 'peer_report';
    public const SOURCE_EXTERNAL_REFERRAL = 'external_referral';
    public const SOURCE_ANONYMOUS = 'anonymous';

    // ==================== RELATIONSHIPS ====================

    public function welfareCase()
    {
        return $this->belongsTo(WelfareCase::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function category()
    {
        return $this->belongsTo(SafeguardingCategory::class, 'category_id');
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    // ==================== SCOPES ====================

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', [self::STATUS_CLOSED]);
    }

    public function scopeClosed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CLOSED);
    }

    public function scopeHighRisk(Builder $query): Builder
    {
        return $query->whereIn('risk_level', [self::RISK_HIGH, self::RISK_CRITICAL]);
    }

    public function scopeCritical(Builder $query): Builder
    {
        return $query->where('risk_level', self::RISK_CRITICAL);
    }

    public function scopeByCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeAwaitingAuthorityNotification(Builder $query): Builder
    {
        return $query->where('authorities_notified', false)
            ->whereHas('category', fn ($q) => $q->where('notify_authorities', true));
    }

    public function scopeReportedBy(Builder $query, int $userId): Builder
    {
        return $query->where('reported_by', $userId);
    }

    public function scopeRequiringImmediateAction(Builder $query): Builder
    {
        return $query->where('immediate_action_taken', false)
            ->whereHas('category', fn ($q) => $q->where('immediate_action_required', true));
    }

    // ==================== HELPER METHODS ====================

    public function isOpen(): bool
    {
        return $this->status !== self::STATUS_CLOSED;
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    public function isHighRisk(): bool
    {
        return in_array($this->risk_level, [self::RISK_HIGH, self::RISK_CRITICAL]);
    }

    public function isCritical(): bool
    {
        return $this->risk_level === self::RISK_CRITICAL;
    }

    public function requiresAuthorityNotification(): bool
    {
        return $this->category && $this->category->notify_authorities && !$this->authorities_notified;
    }

    public function requiresImmediateAction(): bool
    {
        return $this->category && $this->category->immediate_action_required && !$this->immediate_action_taken;
    }

    /**
     * Record authority notification.
     */
    public function notifyAuthorities(string $reference): bool
    {
        return $this->update([
            'authorities_notified' => true,
            'authorities_notified_at' => now(),
            'authority_reference' => $reference,
            'status' => self::STATUS_REFERRED,
        ]);
    }

    /**
     * Record parent notification.
     */
    public function notifyParents(?string $response = null): bool
    {
        return $this->update([
            'parents_informed' => true,
            'parents_informed_at' => now(),
            'parent_response' => $response,
        ]);
    }

    /**
     * Record immediate action taken.
     */
    public function recordImmediateAction(string $details): bool
    {
        return $this->update([
            'immediate_action_taken' => true,
            'immediate_action_details' => $details,
        ]);
    }

    /**
     * Close the concern.
     */
    public function close(User $closedBy, ?string $outcome = null): bool
    {
        return $this->update([
            'status' => self::STATUS_CLOSED,
            'closed_at' => now(),
            'closed_by' => $closedBy->id,
            'outcome' => $outcome,
        ]);
    }

    /**
     * Get risk badge color for UI.
     */
    public function getRiskColorAttribute(): string
    {
        return match ($this->risk_level) {
            self::RISK_LOW => 'green',
            self::RISK_MEDIUM => 'yellow',
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
            self::STATUS_IDENTIFIED => 'blue',
            self::STATUS_INVESTIGATING => 'yellow',
            self::STATUS_REFERRED => 'purple',
            self::STATUS_MONITORING => 'orange',
            self::STATUS_CLOSED => 'gray',
            default => 'gray',
        };
    }
}
