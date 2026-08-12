<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmClientSetupRevision extends Model
{
    use HasFactory;

    protected $fillable = [
        'submission_id',
        'invitation_id',
        'user_id',
        'revision_number',
        'source',
        'stage_key',
        'payload',
        'changed_keys',
    ];

    protected $casts = [
        'payload' => 'array',
        'changed_keys' => 'array',
        'revision_number' => 'integer',
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
