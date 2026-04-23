<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CrmAttendanceShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_default',
        'grace_minutes',
        'early_out_minutes',
        'overtime_after_minutes',
        'earliest_clock_in',
        'latest_clock_in',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'grace_minutes' => 'integer',
        'early_out_minutes' => 'integer',
        'overtime_after_minutes' => 'integer',
        'is_active' => 'boolean',
    ];

    public function days(): HasMany
    {
        return $this->hasMany(CrmAttendanceShiftDay::class, 'shift_id')->orderBy('day_of_week');
    }

    public function overrides(): HasMany
    {
        return $this->hasMany(CrmAttendanceShiftOverride::class, 'shift_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'shift_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function dayForWeekday(int $dayOfWeek): ?CrmAttendanceShiftDay
    {
        return $this->days->firstWhere('day_of_week', $dayOfWeek);
    }
}
