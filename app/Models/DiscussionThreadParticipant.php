<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscussionThreadParticipant extends Model
{
    use HasFactory;

    protected $table = 'crm_discussion_thread_participants';

    protected $fillable = [
        'thread_id',
        'user_id',
        'role',
        'last_read_at',
        'archived_at',
    ];

    protected $casts = [
        'last_read_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(DiscussionThread::class, 'thread_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
