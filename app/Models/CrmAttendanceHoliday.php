<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmAttendanceHoliday extends Model
{
    protected $fillable = [
        'name',
        'date',
        'is_recurring',
        'applies_to',
        'scope_id',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'is_recurring' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }

    public function appliesToUser(User $user): bool
    {
        if ($this->applies_to === 'all') {
            return true;
        }

        if ($this->applies_to === 'department') {
            return (int) $user->department_id === (int) $this->scope_id;
        }

        if ($this->applies_to === 'shift') {
            return (int) $user->shift_id === (int) $this->scope_id;
        }

        return false;
    }
}
