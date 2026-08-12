<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CrmClientSetupMigrationUpload extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid', 'submission_id', 'invitation_id', 'uploaded_by_id', 'attachment_id',
        'kind', 'template_version', 'original_name', 'row_count', 'valid_row_count',
        'template_compatibility_status', 'template_fingerprint',
        'error_count', 'validation_status', 'validation_errors', 'headers', 'uploaded_at',
        'crm_approval_status', 'crm_approved_by_id', 'crm_approved_at', 'crm_approval_note',
        'import_status', 'import_requested_by_id', 'import_started_at', 'import_completed_at',
        'import_reference', 'import_summary', 'import_error',
    ];

    protected $casts = [
        'validation_errors' => 'array',
        'headers' => 'array',
        'row_count' => 'integer',
        'valid_row_count' => 'integer',
        'error_count' => 'integer',
        'uploaded_at' => 'datetime',
        'crm_approved_at' => 'datetime',
        'import_started_at' => 'datetime',
        'import_completed_at' => 'datetime',
        'import_summary' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $upload): void {
            $upload->uuid ??= (string) Str::uuid();
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

    public function attachment(): BelongsTo
    {
        return $this->belongsTo(CrmClientSetupAttachment::class, 'attachment_id');
    }

    public function crmApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'crm_approved_by_id');
    }

    public function migrationErrors(): HasMany
    {
        return $this->hasMany(CrmClientSetupMigrationError::class, 'migration_upload_id');
    }

    public function importRequestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'import_requested_by_id');
    }
}
