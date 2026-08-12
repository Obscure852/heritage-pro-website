<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CrmClientSetupSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'lead_id',
        'customer_id',
        'primary_contact_id',
        'assigned_to_id',
        'schema_version',
        'status',
        'academic_status',
        'payload',
        'completed_stages',
        'academic_submitted_at',
        'completed_at',
        'last_activity_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'completed_stages' => 'array',
        'academic_submitted_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_activity_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $submission): void {
            if (! $submission->uuid) {
                $submission->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function primaryContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'primary_contact_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(CrmClientSetupInvitation::class, 'submission_id');
    }

    public function stageProgress(): HasMany
    {
        return $this->hasMany(CrmClientSetupStageProgress::class, 'submission_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(CrmClientSetupRevision::class, 'submission_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(CrmClientSetupEvent::class, 'submission_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(CrmClientSetupAttachment::class, 'submission_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(CrmClientSetupNote::class, 'submission_id');
    }

    public function changeRequests(): HasMany
    {
        return $this->hasMany(CrmClientSetupChangeRequest::class, 'submission_id');
    }

    public function migrationUploads(): HasMany
    {
        return $this->hasMany(CrmClientSetupMigrationUpload::class, 'submission_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(CrmClientSetupNotification::class, 'submission_id');
    }

    public function payloadArray(): array
    {
        return is_array($this->payload) ? $this->payload : [];
    }
}
