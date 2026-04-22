<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiscussionCampaign extends Model
{
    use HasFactory;

    protected $table = 'crm_discussion_campaigns';

    protected $fillable = [
        'owner_id',
        'initiated_by_id',
        'thread_id',
        'integration_id',
        'channel',
        'status',
        'subject',
        'body',
        'notes',
        'audience_snapshot',
        'source_type',
        'source_id',
        'last_sent_at',
    ];

    protected $casts = [
        'audience_snapshot' => 'array',
        'last_sent_at' => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function initiatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by_id');
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(DiscussionThread::class, 'thread_id');
    }

    public function integration(): BelongsTo
    {
        return $this->belongsTo(Integration::class, 'integration_id');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(DiscussionCampaignRecipient::class, 'campaign_id')->orderBy('id');
    }
}
