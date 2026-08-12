<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CrmClientSetupInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'submission_id',
        'created_by_id',
        'email',
        'contact_name',
        'token_hash',
        'status',
        'expires_at',
        'last_accessed_at',
        'verified_at',
        'verification_code_hash',
        'verification_code_expires_at',
        'verification_attempts',
        'verification_sent_at',
        'revoked_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_accessed_at' => 'datetime',
        'verified_at' => 'datetime',
        'verification_code_expires_at' => 'datetime',
        'verification_sent_at' => 'datetime',
        'revoked_at' => 'datetime',
        'verification_attempts' => 'integer',
    ];

    protected $hidden = [
        'token_hash',
        'verification_code_hash',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $invitation): void {
            if (! $invitation->uuid) {
                $invitation->uuid = (string) Str::uuid();
            }
        });
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(CrmClientSetupSubmission::class, 'submission_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(CrmClientSetupRevision::class, 'invitation_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(CrmClientSetupEvent::class, 'invitation_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isRevoked(): bool
    {
        return $this->status === 'revoked' || $this->revoked_at !== null;
    }

    public function isUsable(): bool
    {
        return $this->status === 'active' && ! $this->isRevoked() && ! $this->isExpired();
    }

    public function revoke(): void
    {
        $this->forceFill([
            'status' => 'revoked',
            'revoked_at' => now(),
            'verification_code_hash' => null,
            'verification_code_expires_at' => null,
        ])->save();
    }
}
