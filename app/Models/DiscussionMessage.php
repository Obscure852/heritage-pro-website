<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiscussionMessage extends Model
{
    use HasFactory;

    protected $table = 'crm_discussion_messages';

    protected $fillable = [
        'thread_id',
        'user_id',
        'direction',
        'channel',
        'body',
        'delivery_status',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(DiscussionThread::class, 'thread_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(DiscussionMessageAttachment::class, 'message_id')->orderBy('id');
    }
}
