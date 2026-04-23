<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CrmLeaveType extends Model
{
    protected $fillable = [
        'name',
        'code',
        'color',
        'default_days_per_year',
        'requires_attachment',
        'attachment_required_after_days',
        'max_consecutive_days',
        'min_notice_days',
        'allow_half_day',
        'is_paid',
        'counts_as_working',
        'carry_over_limit',
        'gender_restriction',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'default_days_per_year' => 'decimal:1',
        'requires_attachment' => 'boolean',
        'attachment_required_after_days' => 'integer',
        'max_consecutive_days' => 'integer',
        'min_notice_days' => 'integer',
        'allow_half_day' => 'boolean',
        'is_paid' => 'boolean',
        'counts_as_working' => 'decimal:2',
        'carry_over_limit' => 'decimal:1',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function balances(): HasMany
    {
        return $this->hasMany(CrmLeaveBalance::class, 'leave_type_id');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(CrmLeaveRequest::class, 'leave_type_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeForGender($query, ?string $gender)
    {
        // Only filter if gender is explicitly female or male.
        // For null, other, prefer_not_to_say — show all leave types.
        if (in_array($gender, ['female', 'male'], true)) {
            return $query->where(function ($q) use ($gender) {
                $q->whereNull('gender_restriction')
                    ->orWhere('gender_restriction', $gender);
            });
        }

        return $query;
    }

    public function isAvailableForGender(?string $gender): bool
    {
        if ($this->gender_restriction === null) {
            return true;
        }

        // Only restrict when user has an explicit male/female gender set
        if (! in_array($gender, ['female', 'male'], true)) {
            return true;
        }

        return $this->gender_restriction === $gender;
    }

    public function requiresAttachmentForDays(float $totalDays): bool
    {
        if (! $this->requires_attachment) {
            return false;
        }

        if ($this->attachment_required_after_days === null) {
            return true;
        }

        return $totalDays > $this->attachment_required_after_days;
    }
}
