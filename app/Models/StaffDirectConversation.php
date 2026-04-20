<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class StaffDirectConversation extends Model
{
    protected $fillable = [
        'user_one_id',
        'user_two_id',
        'last_message_at',
        'user_one_read_at',
        'user_two_read_at',
        'user_one_last_read_message_id',
        'user_two_last_read_message_id',
        'is_archived_by_user_one',
        'is_archived_by_user_two',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'user_one_read_at' => 'datetime',
        'user_two_read_at' => 'datetime',
        'user_one_last_read_message_id' => 'integer',
        'user_two_last_read_message_id' => 'integer',
        'is_archived_by_user_one' => 'boolean',
        'is_archived_by_user_two' => 'boolean',
    ];

    public function userOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_one_id')->withTrashed();
    }

    public function userTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_two_id')->withTrashed();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(StaffDirectMessage::class, 'conversation_id');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(StaffDirectMessage::class, 'conversation_id')->latestOfMany();
    }

    public function scopeForUser($query, User|int $user)
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $query->where(function ($conversationQuery) use ($userId) {
            $conversationQuery->where('user_one_id', $userId)
                ->orWhere('user_two_id', $userId);
        });
    }

    public function isParticipant(User|int $user): bool
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $this->user_one_id === $userId || $this->user_two_id === $userId;
    }

    public function otherParticipantFor(User|int $user): User
    {
        $userId = $user instanceof User ? $user->id : $user;

        if ($this->user_one_id === $userId) {
            return $this->userTwo;
        }

        if ($this->user_two_id === $userId) {
            return $this->userOne;
        }

        abort(403, 'Unauthorized access to conversation.');
    }

    public function hasUnreadFor(User|int $user): bool
    {
        if ($this->offsetExists('unread_count')) {
            return (int) $this->getAttribute('unread_count') > 0;
        }

        $userId = $user instanceof User ? $user->id : $user;
        $lastReadMessageId = $this->{$this->lastReadMessageColumnFor($userId)};

        if ($this->relationLoaded('messages')) {
            return $this->messages
                ->where('sender_id', '!=', $userId)
                ->when($lastReadMessageId, function ($messages, $readMessageId) {
                    return $messages->filter(fn (StaffDirectMessage $message) => $message->id > $readMessageId);
                })
                ->isNotEmpty();
        }

        $query = $this->messages()->where('sender_id', '!=', $userId);

        if ($lastReadMessageId) {
            $query->where('id', '>', $lastReadMessageId);
        }

        return $query->exists();
    }

    public function unreadCountFor(User|int $user): int
    {
        if ($this->offsetExists('unread_count')) {
            return (int) $this->getAttribute('unread_count');
        }

        $userId = $user instanceof User ? $user->id : $user;
        $lastReadMessageId = $this->{$this->lastReadMessageColumnFor($userId)};

        if ($this->relationLoaded('messages')) {
            return $this->messages
                ->where('sender_id', '!=', $userId)
                ->when($lastReadMessageId, function ($messages, $readMessageId) {
                    return $messages->filter(fn (StaffDirectMessage $message) => $message->id > $readMessageId);
                })
                ->count();
        }

        $query = $this->messages()->where('sender_id', '!=', $userId);

        if ($lastReadMessageId) {
            $query->where('id', '>', $lastReadMessageId);
        }

        return $query->count();
    }

    public function isArchivedFor(User|int $user): bool
    {
        $archiveColumn = $this->archiveColumnFor($user instanceof User ? $user->id : $user);

        return (bool) $this->{$archiveColumn};
    }

    public function markAsReadFor(User|int $user, ?int $upToMessageId = null): void
    {
        $userId = $user instanceof User ? $user->id : $user;
        $readColumn = $this->readColumnFor($userId);
        $lastReadMessageColumn = $this->lastReadMessageColumnFor($userId);
        $now = now();
        $currentLastReadMessageId = (int) ($this->{$lastReadMessageColumn} ?? 0);
        $resolvedLastReadMessageId = $upToMessageId ? max($currentLastReadMessageId, $upToMessageId) : $currentLastReadMessageId;

        if ($resolvedLastReadMessageId === 0) {
            $resolvedLastReadMessageId = (int) ($this->messages()->max('id') ?? 0);
        }

        $this->forceFill([
            $readColumn => $now,
            $lastReadMessageColumn => $resolvedLastReadMessageId ?: null,
        ])->save();

        if ($resolvedLastReadMessageId > 0) {
            $this->messages()
                ->where('sender_id', '!=', $userId)
                ->where('id', '<=', $resolvedLastReadMessageId)
                ->whereNull('read_at')
                ->update(['read_at' => $now]);
        }
    }

    public function archiveFor(User|int $user): void
    {
        $archiveColumn = $this->archiveColumnFor($user instanceof User ? $user->id : $user);

        $this->forceFill([$archiveColumn => true])->save();
    }

    public function unarchiveFor(User|int $user): void
    {
        $archiveColumn = $this->archiveColumnFor($user instanceof User ? $user->id : $user);

        $this->forceFill([$archiveColumn => false])->save();
    }

    public function updateLastMessageTime($timestamp = null): void
    {
        $this->forceFill(['last_message_at' => $timestamp ?? now()])->save();
    }

    public function readColumnFor(int $userId): string
    {
        if ($this->user_one_id === $userId) {
            return 'user_one_read_at';
        }

        if ($this->user_two_id === $userId) {
            return 'user_two_read_at';
        }

        abort(403, 'Unauthorized access to conversation.');
    }

    public function lastReadMessageColumnFor(int $userId): string
    {
        if ($this->user_one_id === $userId) {
            return 'user_one_last_read_message_id';
        }

        if ($this->user_two_id === $userId) {
            return 'user_two_last_read_message_id';
        }

        abort(403, 'Unauthorized access to conversation.');
    }

    public function archiveColumnFor(int $userId): string
    {
        if ($this->user_one_id === $userId) {
            return 'is_archived_by_user_one';
        }

        if ($this->user_two_id === $userId) {
            return 'is_archived_by_user_two';
        }

        abort(403, 'Unauthorized access to conversation.');
    }
}
