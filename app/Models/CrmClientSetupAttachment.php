<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CrmClientSetupAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'submission_id',
        'invitation_id',
        'uploaded_by_id',
        'category',
        'requirement',
        'original_name',
        'disk',
        'path',
        'mime_type',
        'size_bytes',
        'sha256',
        'scan_status',
        'scan_provider',
        'scan_reference',
        'scan_message',
        'scan_completed_at',
        'uploaded_at',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
        'uploaded_at' => 'datetime',
        'scan_completed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $attachment): void {
            if (! $attachment->uuid) {
                $attachment->uuid = (string) Str::uuid();
            }
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

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }

    public function migrationUploads(): HasMany
    {
        return $this->hasMany(CrmClientSetupMigrationUpload::class, 'attachment_id');
    }
}
