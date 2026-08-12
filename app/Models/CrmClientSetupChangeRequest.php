<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CrmClientSetupChangeRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid', 'submission_id', 'user_id', 'stage_key', 'field_key', 'body', 'client_response',
        'status', 'resolved_by_id', 'resolved_at',
    ];

    protected $casts = ['resolved_at' => 'datetime', 'responded_at' => 'datetime'];

    protected static function booted(): void
    {
        static::creating(function (self $request): void {
            $request->uuid ??= (string) Str::uuid();
        });
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(CrmClientSetupSubmission::class, 'submission_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by_id');
    }
}
