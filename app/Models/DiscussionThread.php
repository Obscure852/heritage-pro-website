<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscussionThread extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'crm_discussion_threads';

    protected $fillable = [
        'owner_id',
        'initiated_by_id',
        'recipient_user_id',
        'integration_id',
        'subject',
        'channel',
        'recipient_email',
        'recipient_phone',
        'delivery_status',
        'last_message_at',
        'notes',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function initiatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by_id');
    }

    public function recipientUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function integration(): BelongsTo
    {
        return $this->belongsTo(Integration::class, 'integration_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(DiscussionMessage::class, 'thread_id')->orderBy('created_at');
    }
}
