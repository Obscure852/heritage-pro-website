<?php

namespace App\Models\Lms;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DirectMessage extends Model {
    use SoftDeletes;

    protected $table = 'lms_direct_messages';

    protected $fillable = [
        'conversation_id',
        'sender_type',
        'sender_id',
        'body',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    protected static function boot() {
        parent::boot();

        static::created(function ($message) {
            $message->conversation->updateLastMessageTime();
        });
    }

    public function conversation(): BelongsTo {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    public function sender(): MorphTo {
        return $this->morphTo();
    }

    public function attachments(): HasMany {
        return $this->hasMany(MessageAttachment::class, 'message_id');
    }

    public function isSentByStudent(): bool {
        return $this->sender_type === Student::class;
    }

    public function isSentByInstructor(): bool {
        return $this->sender_type === User::class;
    }

    public function getSenderNameAttribute(): string {
        if ($this->sender) {
            if ($this->isSentByStudent()) {
                return $this->sender->full_name ?? 'Unknown Student';
            }
            return $this->sender->name ?? 'Unknown Instructor';
        }
        return 'Unknown';
    }

    public function markAsRead(): void {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    public function scopeUnread($query) {
        return $query->whereNull('read_at');
    }

    public function scopeFromStudent($query) {
        return $query->where('sender_type', Student::class);
    }

    public function scopeFromInstructor($query) {
        return $query->where('sender_type', User::class);
    }
}
