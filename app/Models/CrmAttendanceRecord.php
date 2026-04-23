<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CrmAttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'attendance_code_id',
        'clocked_in_at',
        'clocked_out_at',
        'source',
        'clock_in_note',
        'clock_out_note',
        'total_minutes',
        'overtime_minutes',
        'is_late',
        'is_early_out',
        'auto_closed',
        'status',
        'approved_by',
        'approved_at',
        'leave_request_id',
    ];

    protected $casts = [
        'date' => 'date',
        'clocked_in_at' => 'datetime',
        'clocked_out_at' => 'datetime',
        'total_minutes' => 'integer',
        'overtime_minutes' => 'integer',
        'is_late' => 'boolean',
        'is_early_out' => 'boolean',
        'auto_closed' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function code(): BelongsTo
    {
        return $this->belongsTo(CrmAttendanceCode::class, 'attendance_code_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function corrections(): HasMany
    {
        return $this->hasMany(CrmAttendanceCorrection::class, 'attendance_record_id')->latest();
    }

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(CrmLeaveRequest::class, 'leave_request_id');
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }

    public function scopeForDateRange($query, $start, $end)
    {
        return $query->whereBetween('date', [$start, $end]);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function isClockedIn(): bool
    {
        return $this->clocked_in_at !== null && $this->clocked_out_at === null;
    }
}
