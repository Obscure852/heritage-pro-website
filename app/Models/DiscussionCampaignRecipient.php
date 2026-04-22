<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscussionCampaignRecipient extends Model
{
    use HasFactory;

    protected $table = 'crm_discussion_campaign_recipients';

    protected $fillable = [
        'campaign_id',
        'thread_id',
        'message_id',
        'recipient_user_id',
        'recipient_type',
        'recipient_id',
        'recipient_label',
        'recipient_address',
        'delivery_status',
        'error_message',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(DiscussionCampaign::class, 'campaign_id');
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(DiscussionThread::class, 'thread_id');
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(DiscussionMessage::class, 'message_id');
    }

    public function recipientUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }
}
