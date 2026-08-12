<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CrmClientSetupNote extends Model
{
    use HasFactory;

    protected $fillable = ['uuid', 'submission_id', 'user_id', 'body'];

    protected static function booted(): void
    {
        static::creating(function (self $note): void {
            $note->uuid ??= (string) Str::uuid();
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
}
