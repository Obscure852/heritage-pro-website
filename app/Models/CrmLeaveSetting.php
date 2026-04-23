<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmLeaveSetting extends Model
{
    protected $fillable = [
        'attendance_integration_enabled',
        'auto_mark_attendance_on_approve',
        'auto_clear_attendance_on_cancel',
        'approval_reminder_hours',
        'max_escalation_levels',
        'escalation_after_hours',
        'allow_retroactive_leave',
        'retroactive_limit_days',
        'balance_year_start_month',
    ];

    protected $casts = [
        'attendance_integration_enabled' => 'boolean',
        'auto_mark_attendance_on_approve' => 'boolean',
        'auto_clear_attendance_on_cancel' => 'boolean',
        'approval_reminder_hours' => 'integer',
        'max_escalation_levels' => 'integer',
        'escalation_after_hours' => 'integer',
        'allow_retroactive_leave' => 'boolean',
        'retroactive_limit_days' => 'integer',
        'balance_year_start_month' => 'integer',
    ];

    public static function instance(): self
    {
        return static::query()->firstOrCreate([], [
            'attendance_integration_enabled' => false,
            'auto_mark_attendance_on_approve' => true,
            'auto_clear_attendance_on_cancel' => true,
            'approval_reminder_hours' => 48,
            'max_escalation_levels' => 2,
            'escalation_after_hours' => 72,
            'allow_retroactive_leave' => false,
            'retroactive_limit_days' => 5,
            'balance_year_start_month' => 1,
        ]);
    }
}
