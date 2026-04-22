<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmCalendarMembership extends Model
{
    use HasFactory;

    protected $fillable = [
        'calendar_id',
        'user_id',
        'permission',
        'is_visible',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
    ];

    public function calendar(): BelongsTo
    {
        return $this->belongsTo(CrmCalendar::class, 'calendar_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
