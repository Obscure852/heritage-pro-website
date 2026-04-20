<?php

namespace App\Models\Lms;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscussionPost extends Model {
    use SoftDeletes;

    protected $table = 'lms_discussion_posts';

    protected $fillable = [
        'thread_id',
        'parent_id',
        'author_id',
        'author_type',
        'body',
        'is_anonymous',
        'is_answer',
        'likes_count',
        'replies_count',
        'status',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'is_answer' => 'boolean',
        'likes_count' => 'integer',
        'replies_count' => 'integer',
    ];

    public const STATUS_VISIBLE = 'visible';
    public const STATUS_HIDDEN = 'hidden';
    public const STATUS_PENDING = 'pending';

    protected static function boot() {
        parent::boot();

        static::created(function ($post) {
            $post->thread->updateReplyCount();
            $post->thread->updateLastActivity($post);

            // Update parent reply count
            if ($post->parent_id) {
                $post->parent->increment('replies_count');
            }
        });

        static::deleted(function ($post) {
            $post->thread->updateReplyCount();

            if ($post->parent_id) {
                $post->parent->decrement('replies_count');
            }
        });
    }

    public function thread(): BelongsTo {
        return $this->belongsTo(DiscussionThread::class, 'thread_id');
    }

    public function parent(): BelongsTo {
        return $this->belongsTo(DiscussionPost::class, 'parent_id');
    }

    public function author(): MorphTo {
        return $this->morphTo('author');
    }

    /**
     * Check if the author is an instructor (User model)
     */
    public function isAuthorInstructor(): bool {
        return $this->author_type === User::class || $this->author_type === 'App\\Models\\User';
    }

    /**
     * Check if the author is a student
     */
    public function isAuthorStudent(): bool {
        return $this->author_type === Student::class || $this->author_type === 'App\\Models\\Student';
    }

    public function replies(): HasMany {
        return $this->hasMany(DiscussionPost::class, 'parent_id');
    }

    public function likes(): MorphMany {
        return $this->morphMany(DiscussionLike::class, 'likeable');
    }

    public function attachments(): HasMany {
        return $this->hasMany(DiscussionAttachment::class, 'post_id');
    }

    public function mentions(): HasMany {
        return $this->hasMany(DiscussionMention::class, 'post_id');
    }

    public function scopeVisible($query) {
        return $query->where('status', self::STATUS_VISIBLE);
    }

    public function scopeTopLevel($query) {
        return $query->whereNull('parent_id');
    }

    public function getDisplayAuthorAttribute(): string {
        if ($this->is_anonymous && !$this->isAuthorInstructor()) {
            return 'Anonymous';
        }

        if ($this->isAuthorInstructor()) {
            return $this->author?->full_name ?? 'Unknown Instructor';
        }

        return $this->author?->full_name ?? 'Unknown';
    }

    /**
     * Get the author's initials for avatar display
     */
    public function getAuthorInitialsAttribute(): string {
        if ($this->isAuthorInstructor()) {
            return strtoupper(substr($this->author?->firstname ?? 'T', 0, 1));
        }
        return strtoupper(substr($this->author?->first_name ?? 'S', 0, 1));
    }

    public function isLikedBy(Student $student): bool {
        return $this->likes()->where('student_id', $student->id)->exists();
    }

    public function toggleLike(Student $student): bool {
        $existing = $this->likes()->where('student_id', $student->id)->first();

        if ($existing) {
            $existing->delete();
            $this->decrement('likes_count');
            return false;
        }

        $this->likes()->create(['student_id' => $student->id]);
        $this->increment('likes_count');
        return true;
    }

    public function markAsAnswer(): void {
        // Remove previous answer
        $this->thread->posts()->where('is_answer', true)->update(['is_answer' => false]);

        $this->is_answer = true;
        $this->save();

        $this->thread->markAsResolved($this);
    }

    public function hide(): void {
        $this->status = self::STATUS_HIDDEN;
        $this->save();
        $this->thread->updateReplyCount();
    }

    public function show(): void {
        $this->status = self::STATUS_VISIBLE;
        $this->save();
        $this->thread->updateReplyCount();
    }
}
