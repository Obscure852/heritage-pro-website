<?php

namespace App\Models\Activities;

use App\Models\Fee\FeeType;
use App\Models\Term;
use App\Models\User;
use App\Services\Activities\ActivitySettingsService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_ARCHIVED = 'archived';

    public const CATEGORY_CLUB = 'club';
    public const CATEGORY_SPORT = 'sport';
    public const CATEGORY_SOCIETY = 'society';
    public const CATEGORY_ARTS = 'arts';
    public const CATEGORY_SERVICE = 'service';
    public const CATEGORY_ACADEMIC = 'academic';
    public const CATEGORY_EVENT_PROGRAM = 'event_program';
    public const CATEGORY_OTHER = 'other';

    public const DELIVERY_RECURRING = 'recurring';
    public const DELIVERY_ONE_OFF = 'one_off';
    public const DELIVERY_HYBRID = 'hybrid';

    public const PARTICIPATION_INDIVIDUAL = 'individual';
    public const PARTICIPATION_TEAM = 'team';
    public const PARTICIPATION_MIXED = 'mixed';

    public const RESULT_ATTENDANCE_ONLY = 'attendance_only';
    public const RESULT_PLACEMENTS = 'placements';
    public const RESULT_POINTS = 'points';
    public const RESULT_AWARDS = 'awards';
    public const RESULT_MIXED = 'mixed';

    protected $fillable = [
        'name',
        'code',
        'category',
        'delivery_mode',
        'participation_mode',
        'result_mode',
        'description',
        'default_location',
        'capacity',
        'gender_policy',
        'attendance_required',
        'allow_house_linkage',
        'fee_type_id',
        'default_fee_amount',
        'status',
        'term_id',
        'year',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'attendance_required' => 'boolean',
        'allow_house_linkage' => 'boolean',
        'default_fee_amount' => 'decimal:2',
        'year' => 'integer',
    ];

    public static function defaultCategories(): array
    {
        return [
            self::CATEGORY_CLUB => 'Club',
            self::CATEGORY_SPORT => 'Sport',
            self::CATEGORY_SOCIETY => 'Society',
            self::CATEGORY_ARTS => 'Arts',
            self::CATEGORY_SERVICE => 'Service',
            self::CATEGORY_ACADEMIC => 'Academic',
            self::CATEGORY_EVENT_PROGRAM => 'Event Program',
            self::CATEGORY_OTHER => 'Other',
        ];
    }

    public static function defaultDeliveryModes(): array
    {
        return [
            self::DELIVERY_RECURRING => 'Recurring',
            self::DELIVERY_ONE_OFF => 'One Off',
            self::DELIVERY_HYBRID => 'Hybrid',
        ];
    }

    public static function defaultParticipationModes(): array
    {
        return [
            self::PARTICIPATION_INDIVIDUAL => 'Individual',
            self::PARTICIPATION_TEAM => 'Team',
            self::PARTICIPATION_MIXED => 'Mixed',
        ];
    }

    public static function defaultResultModes(): array
    {
        return [
            self::RESULT_ATTENDANCE_ONLY => 'Attendance Only',
            self::RESULT_PLACEMENTS => 'Placements',
            self::RESULT_POINTS => 'Points',
            self::RESULT_AWARDS => 'Awards',
            self::RESULT_MIXED => 'Mixed',
        ];
    }

    public static function defaultGenderPolicies(): array
    {
        return [
            'boys' => 'Boys',
            'girls' => 'Girls',
            'mixed' => 'Mixed',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_PAUSED => 'Paused',
            self::STATUS_CLOSED => 'Closed',
            self::STATUS_ARCHIVED => 'Archived',
        ];
    }

    public static function categories(): array
    {
        return app(ActivitySettingsService::class)->categoryLabels();
    }

    public static function deliveryModes(): array
    {
        return app(ActivitySettingsService::class)->deliveryModeLabels();
    }

    public static function participationModes(): array
    {
        return app(ActivitySettingsService::class)->participationModeLabels();
    }

    public static function resultModes(): array
    {
        return app(ActivitySettingsService::class)->resultModeLabels();
    }

    public static function genderPolicies(): array
    {
        return app(ActivitySettingsService::class)->genderPolicyLabels();
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function feeType(): BelongsTo
    {
        return $this->belongsTo(FeeType::class);
    }

    public function staffAssignments(): HasMany
    {
        return $this->hasMany(ActivityStaffAssignment::class);
    }

    public function activeStaffAssignments(): HasMany
    {
        return $this->staffAssignments()->where('active', true);
    }

    public function eligibilityTargets(): HasMany
    {
        return $this->hasMany(ActivityEligibilityTarget::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(ActivityEnrollment::class);
    }

    public function activeEnrollments(): HasMany
    {
        return $this->enrollments()->where('status', ActivityEnrollment::STATUS_ACTIVE);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ActivitySchedule::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ActivitySession::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(ActivityEvent::class);
    }

    public function feeCharges(): HasMany
    {
        return $this->hasMany(ActivityFeeCharge::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(ActivityAuditLog::class, 'entity_id')
            ->where('entity_type', self::class)
            ->latest('created_at');
    }

    public function scopeForTerm($query, int $termId)
    {
        return $query->where('term_id', $termId);
    }

    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function hasAssignedStaff(User $user): bool
    {
        return $this->staffAssignments()
            ->where('user_id', $user->id)
            ->where('active', true)
            ->exists();
    }
}
