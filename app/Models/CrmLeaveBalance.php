<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmLeaveBalance extends Model
{
    protected $fillable = [
        'user_id',
        'leave_type_id',
        'year',
        'entitled_days',
        'carried_over_days',
        'adjustment_days',
        'used_days',
        'pending_days',
    ];

    protected $casts = [
        'year' => 'integer',
        'entitled_days' => 'decimal:1',
        'carried_over_days' => 'decimal:1',
        'adjustment_days' => 'decimal:1',
        'used_days' => 'decimal:1',
        'pending_days' => 'decimal:1',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(CrmLeaveType::class, 'leave_type_id');
    }

    public function getAvailableDaysAttribute(): float
    {
        return (float) $this->entitled_days
            + (float) $this->carried_over_days
            + (float) $this->adjustment_days
            - (float) $this->used_days;
    }

    public function getEffectiveAvailableDaysAttribute(): float
    {
        return $this->available_days - (float) $this->pending_days;
    }

    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
