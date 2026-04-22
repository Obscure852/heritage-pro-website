<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class DiscussionThread extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'crm_discussion_threads';

    protected $fillable = [
        'owner_id',
        'initiated_by_id',
        'recipient_user_id',
        'direct_participant_key',
        'integration_id',
        'subject',
        'channel',
        'kind',
        'recipient_email',
        'recipient_phone',
        'delivery_status',
        'status',
        'last_message_at',
        'notes',
        'source_type',
        'source_id',
        'target_type',
        'target_id',
        'metadata_updated_at',
        'edited_by_id',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'metadata_updated_at' => 'datetime',
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

    public function editedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by_id');
    }

    public function integration(): BelongsTo
    {
        return $this->belongsTo(Integration::class, 'integration_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(DiscussionMessage::class, 'thread_id')->orderBy('created_at');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(DiscussionMessage::class, 'thread_id')->latestOfMany();
    }

    public function participants(): HasMany
    {
        return $this->hasMany(DiscussionThreadParticipant::class, 'thread_id')->orderBy('id');
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(DiscussionCampaign::class, 'thread_id')->latest('id');
    }

    public function isAppThread(): bool
    {
        return $this->channel === 'app';
    }

    public function isCompanyChat(): bool
    {
        return $this->channel === 'app' && $this->kind === 'company_chat';
    }

    public function isGroupChat(): bool
    {
        return $this->channel === 'app' && $this->kind === 'group';
    }

    public function isDirectMessage(): bool
    {
        return $this->kind === 'direct';
    }

    public function otherParticipantsFor(User $user): Collection
    {
        if (! $this->relationLoaded('participants')) {
            $this->loadMissing('participants.user');
        }

        return $this->participants
            ->reject(fn (DiscussionThreadParticipant $participant) => (int) $participant->user_id === (int) $user->id)
            ->pluck('user')
            ->filter()
            ->values();
    }

    public function counterpartFor(User $user): ?User
    {
        return $this->otherParticipantsFor($user)->first();
    }
}
