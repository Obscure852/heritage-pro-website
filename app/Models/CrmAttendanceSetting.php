<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmAttendanceSetting extends Model
{
    protected $table = 'crm_attendance_settings';

    protected $fillable = [
        'show_topbar_clock',
        'show_dashboard_clock',
    ];

    protected $casts = [
        'show_topbar_clock' => 'boolean',
        'show_dashboard_clock' => 'boolean',
    ];

    public static function resolve(): self
    {
        return static::query()->first() ?? static::query()->create([
            'show_topbar_clock' => true,
            'show_dashboard_clock' => true,
        ]);
    }
}
