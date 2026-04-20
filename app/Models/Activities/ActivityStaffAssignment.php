<?php

namespace App\Models\Activities;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityStaffAssignment extends Model
{
    use HasFactory;

    public const ROLE_COORDINATOR = 'coordinator';
    public const ROLE_PATRON = 'patron';
    public const ROLE_COACH = 'coach';
    public const ROLE_ASSISTANT = 'assistant';
    public const ROLE_SCORER = 'scorer';
    public const ROLE_VIEWER = 'viewer';

    protected $fillable = [
        'activity_id',
        'user_id',
        'role',
        'is_primary',
        'active',
        'assigned_at',
        'removed_at',
        'notes',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'active' => 'boolean',
        'assigned_at' => 'datetime',
        'removed_at' => 'datetime',
    ];

    public static function roles(): array
    {
        return [
            self::ROLE_COORDINATOR => 'Coordinator',
            self::ROLE_PATRON => 'Patron',
            self::ROLE_COACH => 'Coach',
            self::ROLE_ASSISTANT => 'Assistant',
            self::ROLE_SCORER => 'Scorer',
            self::ROLE_VIEWER => 'Viewer',
        ];
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
