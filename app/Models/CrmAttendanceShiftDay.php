<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmAttendanceShiftDay extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'shift_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_working_day',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'is_working_day' => 'boolean',
    ];

    public function shift(): BelongsTo
    {
        return $this->belongsTo(CrmAttendanceShift::class, 'shift_id');
    }
}
