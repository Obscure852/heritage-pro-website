<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscussionMessageMention extends Model
{
    use HasFactory;

    protected $table = 'crm_discussion_message_mentions';

    protected $fillable = [
        'message_id',
        'user_id',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(DiscussionMessage::class, 'message_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function label(): string
    {
        return trim((string) ($this->user?->name ?: ($this->user?->email ?: ('User #' . $this->user_id))));
    }
}
