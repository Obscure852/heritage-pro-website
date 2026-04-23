<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmAttendanceDeviceLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'device_id',
        'employee_identifier',
        'event_type',
        'captured_at',
        'verification_method',
        'card_number',
        'temperature',
        'work_code',
        'confidence_score',
        'status',
        'matched_user_id',
        'attendance_record_id',
        'error_message',
        'raw_payload',
        'created_at',
    ];

    protected $casts = [
        'captured_at' => 'datetime',
        'temperature' => 'decimal:1',
        'confidence_score' => 'decimal:3',
        'created_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(CrmAttendanceDevice::class, 'device_id');
    }

    public function matchedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'matched_user_id');
    }

    public function attendanceRecord(): BelongsTo
    {
        return $this->belongsTo(CrmAttendanceRecord::class, 'attendance_record_id');
    }

    public function verifyMethodLabel(): string
    {
        return config('heritage_crm.attendance.verify_methods.' . $this->verification_method, $this->verification_method ?? 'Unknown');
    }
}
