<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CrmClientSetupNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid', 'submission_id', 'invitation_id', 'recipient_user_id', 'audience', 'event_key',
        'channel', 'recipient_email', 'recipient_name', 'subject', 'payload', 'idempotency_key',
        'status', 'attempts', 'available_at', 'last_attempt_at', 'sent_at', 'failed_at', 'failure_message',
    ];

    protected $casts = [
        'payload' => 'array',
        'available_at' => 'datetime',
        'last_attempt_at' => 'datetime',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $notification): void {
            $notification->uuid ??= (string) Str::uuid();
        });
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(CrmClientSetupSubmission::class, 'submission_id');
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(CrmClientSetupInvitation::class, 'invitation_id');
    }

    public function recipientUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }
}
