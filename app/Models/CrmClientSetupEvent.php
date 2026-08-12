<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class CrmClientSetupEvent extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::updating(function (): void {
            throw new LogicException('Client setup audit events are immutable.');
        });

        static::deleting(function (): void {
            throw new LogicException('Client setup audit events are immutable.');
        });
    }

    protected $fillable = [
        'submission_id',
        'invitation_id',
        'user_id',
        'actor_type',
        'event_type',
        'stage_key',
        'metadata',
        'ip_address',
        'user_agent',
        'occurred_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(CrmClientSetupSubmission::class, 'submission_id');
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(CrmClientSetupInvitation::class, 'invitation_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
