<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmAttendanceCorrection extends Model
{
    protected $fillable = [
        'attendance_record_id',
        'requested_by',
        'original_values',
        'proposed_clock_in',
        'proposed_clock_out',
        'proposed_code_id',
        'reason',
        'status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
    ];

    protected $casts = [
        'original_values' => 'array',
        'proposed_clock_in' => 'datetime',
        'proposed_clock_out' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function record(): BelongsTo
    {
        return $this->belongsTo(CrmAttendanceRecord::class, 'attendance_record_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function proposedCode(): BelongsTo
    {
        return $this->belongsTo(CrmAttendanceCode::class, 'proposed_code_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
