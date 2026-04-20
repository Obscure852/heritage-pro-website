<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffUserPresence extends Model
{
    protected $table = 'staff_user_presence';

    protected $fillable = [
        'session_id',
        'user_id',
        'last_seen_at',
        'last_path',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
