<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens;

    protected $fillable = [
        'name',
        'firstname',
        'lastname',
        'username',
        'email',
        'password',
        'role',
        'active',
        'date_of_birth',
        'gender',
        'nationality',
        'id_number',
        'phone',
        'employment_status',
        'department',
        'department_id',
        'position',
        'position_id',
        'reporting_to',
        'reports_to_user_id',
        'personal_payroll_number',
        'date_of_appointment',
        'avatar_path',
        'shift_id',
        'crm_onboarding_required_at',
        'crm_onboarding_step',
        'crm_onboarded_at',
        'crm_discussion_sound_enabled',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $attributes = [
        'crm_discussion_sound_enabled' => true,
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'active' => 'boolean',
        'date_of_birth' => 'date',
        'date_of_appointment' => 'date',
        'crm_onboarding_required_at' => 'datetime',
        'crm_onboarding_step' => 'integer',
        'crm_onboarded_at' => 'datetime',
        'crm_discussion_sound_enabled' => 'boolean',
    ];

    public function leads()
    {
        return $this->hasMany(Lead::class, 'owner_id');
    }

    public function customers()
    {
        return $this->hasMany(Customer::class, 'owner_id');
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class, 'owner_id');
    }

    public function requests()
    {
        return $this->hasMany(CrmRequest::class, 'owner_id');
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(CrmQuote::class, 'owner_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(CrmInvoice::class, 'owner_id');
    }

    public function crmPresence(): HasOne
    {
        return $this->hasOne(CrmUserPresence::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(CrmUserDepartment::class, 'department_id');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(CrmUserPosition::class, 'position_id');
    }

    public function reportsTo(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reports_to_user_id');
    }

    public function directReports(): HasMany
    {
        return $this->hasMany(self::class, 'reports_to_user_id');
    }

    public function customFilters(): BelongsToMany
    {
        return $this->belongsToMany(CrmUserFilter::class, 'crm_user_filter_user', 'user_id', 'crm_user_filter_id')
            ->withTimestamps();
    }

    public function qualifications(): HasMany
    {
        return $this->hasMany(CrmUserQualification::class)->latest('completion_date')->latest('id');
    }

    public function signatures(): HasMany
    {
        return $this->hasMany(CrmUserSignature::class)->latest('is_default')->latest('id');
    }

    public function loginEvents(): HasMany
    {
        return $this->hasMany(CrmUserLoginEvent::class)->latest('occurred_at');
    }

    public function discussionThreadsStarted(): HasMany
    {
        return $this->hasMany(DiscussionThread::class, 'initiated_by_id')->latest('last_message_at');
    }

    public function discussionParticipantRecords(): HasMany
    {
        return $this->hasMany(DiscussionThreadParticipant::class, 'user_id')->latest('last_read_at');
    }

    public function discussionMessages(): HasMany
    {
        return $this->hasMany(DiscussionMessage::class)->latest('created_at');
    }

    public function modulePermissions(): HasMany
    {
        return $this->hasMany(CrmUserModulePermission::class)->orderBy('module_key');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(CrmAttendanceShift::class, 'shift_id');
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(CrmAttendanceRecord::class)->latest('date');
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(CrmLeaveRequest::class)->latest('created_at');
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(CrmLeaveBalance::class);
    }

    public function pendingLeaveApprovals(): HasMany
    {
        return $this->hasMany(CrmLeaveRequest::class, 'current_approver_id')->where('status', 'pending');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isFinance(): bool
    {
        return $this->role === 'finance';
    }

    public function isRep(): bool
    {
        return $this->role === 'rep';
    }

    public function canAccessCrm(): bool
    {
        return $this->active && in_array($this->role, array_keys(config('heritage_crm.roles', [])), true);
    }

    public function crmModulePermissionLevel(string $moduleKey): ?string
    {
        return app(\App\Services\Crm\CrmModulePermissionService::class)->effectivePermissionLevel($this, $moduleKey);
    }

    public function canAccessCrmModule(string $moduleKey, string $requiredLevel = 'view'): bool
    {
        return $this->canAccessCrm()
            && app(\App\Services\Crm\CrmModulePermissionService::class)->hasAccess($this, $moduleKey, $requiredLevel);
    }

    public function canManageCrmUsers(): bool
    {
        return $this->canAccessCrmModule('users', 'admin');
    }

    public function canManageCrmSettings(): bool
    {
        return $this->canAccessCrmModule('settings', 'admin');
    }

    public function canManageCommercialSettings(): bool
    {
        return $this->canAccessCrmModule('products', 'admin');
    }

    public function canManageCommercialCatalog(): bool
    {
        return $this->canAccessCrmModule('products', 'admin');
    }

    public function canIssueCommercialInvoices(): bool
    {
        return $this->canAccessCrmModule('products', 'admin');
    }

    public function canManageOperationalRecords(): bool
    {
        return $this->isAdmin() || $this->isManager();
    }

    public function canAccessOwnedRecord(?int $ownerId): bool
    {
        return $this->canManageOperationalRecords() || $ownerId === $this->id;
    }

    public function canAccessCommercialContextRecord(?int $ownerId): bool
    {
        return $this->canManageOperationalRecords() || $this->isFinance() || $ownerId === $this->id;
    }

    public function canAccessCommercialDocumentRecord(?int $ownerId): bool
    {
        return $this->canAccessCommercialContextRecord($ownerId);
    }

    public function hasCompletedCrmOnboarding(): bool
    {
        return $this->crm_onboarded_at !== null;
    }

    public function requiresCrmOnboarding(): bool
    {
        return ! $this->hasCompletedCrmOnboarding() && $this->crm_onboarding_required_at !== null;
    }

    public function crmOnboardingRouteName(): string
    {
        return (int) ($this->crm_onboarding_step ?? 1) >= 2
            ? 'crm.onboarding.work'
            : 'crm.onboarding.profile';
    }

    public function markCrmOnboardingRequired(): void
    {
        if ($this->hasCompletedCrmOnboarding()) {
            return;
        }

        $this->forceFill([
            'crm_onboarding_required_at' => now(),
            'crm_onboarding_step' => 1,
        ])->save();
    }

    public function advanceCrmOnboardingToWork(): void
    {
        $this->forceFill([
            'crm_onboarding_required_at' => $this->crm_onboarding_required_at ?: now(),
            'crm_onboarding_step' => 2,
        ])->save();
    }

    public function completeCrmOnboarding(): void
    {
        $this->forceFill([
            'crm_onboarding_required_at' => null,
            'crm_onboarding_step' => null,
            'crm_onboarded_at' => now(),
        ])->save();
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: function ($value, array $attributes) {
                if (is_string($value) && trim($value) !== '') {
                    return trim($value);
                }

                $fullName = trim(implode(' ', array_filter([
                    $attributes['firstname'] ?? null,
                    $attributes['lastname'] ?? null,
                ])));

                return $fullName !== '' ? $fullName : ($attributes['username'] ?? $attributes['email'] ?? 'User');
            },
            set: function (?string $value) {
                $value = trim((string) $value);

                if ($value === '') {
                    return [];
                }

                if (Schema::hasColumn($this->getTable(), 'name')) {
                    return ['name' => $value];
                }

                $parts = preg_split('/\s+/', $value, 2) ?: [];
                $firstname = $parts[0] ?? $value;
                $lastname = $parts[1] ?? 'CRM User';

                return [
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'username' => $this->attributes['username'] ?? $this->attributes['email'] ?? strtolower($firstname . '.' . $lastname),
                ];
            }
        );
    }

    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->avatar_path ? Storage::disk('public')->url($this->avatar_path) : null
        );
    }

    protected function crmDepartmentName(): Attribute
    {
        return Attribute::make(
            get: fn ($value, array $attributes) => $this->resolvedRelationName('department', 'department', $attributes)
        );
    }

    protected function crmPositionName(): Attribute
    {
        return Attribute::make(
            get: fn ($value, array $attributes) => $this->resolvedRelationName('position', 'position', $attributes)
        );
    }

    protected function crmReportsToName(): Attribute
    {
        return Attribute::make(
            get: fn ($value, array $attributes) => $this->resolvedRelationName('reportsTo', 'reporting_to', $attributes)
        );
    }

    private function resolvedRelationName(string $relation, string $legacyAttribute, array $attributes): ?string
    {
        if ($this->relationLoaded($relation)) {
            $related = $this->getRelation($relation);

            if ($related && isset($related->name) && trim((string) $related->name) !== '') {
                return trim((string) $related->name);
            }
        }

        $related = $this->{$relation}()->getResults();

        if ($related && isset($related->name) && trim((string) $related->name) !== '') {
            return trim((string) $related->name);
        }

        $legacyValue = trim((string) ($attributes[$legacyAttribute] ?? ''));

        return $legacyValue !== '' ? $legacyValue : null;
    }
}
