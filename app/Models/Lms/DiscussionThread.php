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
use Illuminate\Support\Str;

class DiscussionThread extends Model {
    use SoftDeletes;

    protected $table = 'lms_discussion_threads';

    protected $fillable = [
        'forum_id',
        'category_id',
        'author_id',
        'author_type',
        'content_item_id',
        'title',
        'body',
        'slug',
        'type',
        'status',
        'is_pinned',
        'is_locked',
        'is_anonymous',
        'views_count',
        'replies_count',
        'likes_count',
        'last_activity_at',
        'last_reply_id',
        'accepted_answer_id',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_locked' => 'boolean',
        'is_anonymous' => 'boolean',
        'views_count' => 'integer',
        'replies_count' => 'integer',
        'likes_count' => 'integer',
        'last_activity_at' => 'datetime',
    ];

    public const TYPE_DISCUSSION = 'discussion';
    public const TYPE_QUESTION = 'question';
    public const TYPE_ANNOUNCEMENT = 'announcement';

    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_PENDING = 'pending';

    protected static function boot() {
        parent::boot();

        static::creating(function ($thread) {
            if (!$thread->slug) {
                $thread->slug = Str::slug($thread->title) . '-' . Str::random(6);
            }
            $thread->last_activity_at = now();
        });
    }

    public function forum(): BelongsTo {
        return $this->belongsTo(DiscussionForum::class, 'forum_id');
    }

    public function category(): BelongsTo {
        return $this->belongsTo(DiscussionCategory::class, 'category_id');
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

    public function contentItem(): BelongsTo {
        return $this->belongsTo(ContentItem::class, 'content_item_id');
    }

    public function posts(): HasMany {
        return $this->hasMany(DiscussionPost::class, 'thread_id');
    }

    public function replies(): HasMany {
        return $this->posts()->whereNull('parent_id');
    }

    public function lastReply(): BelongsTo {
        return $this->belongsTo(DiscussionPost::class, 'last_reply_id');
    }

    public function acceptedAnswer(): BelongsTo {
        return $this->belongsTo(DiscussionPost::class, 'accepted_answer_id');
    }

    public function likes(): MorphMany {
        return $this->morphMany(DiscussionLike::class, 'likeable');
    }

    public function subscriptions(): HasMany {
        return $this->hasMany(DiscussionSubscription::class, 'thread_id');
    }

    public function scopePinned($query) {
        return $query->where('is_pinned', true);
    }

    public function scopeOpen($query) {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function scopeRecent($query) {
        return $query->orderByDesc('last_activity_at');
    }

    public function scopePopular($query) {
        return $query->orderByDesc('views_count');
    }

    public function scopeForContent($query, int $contentItemId) {
        return $query->where('content_item_id', $contentItemId);
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

    public function incrementViews(): void {
        $this->increment('views_count');
    }

    public function updateReplyCount(): void {
        $this->replies_count = $this->posts()->visible()->count();
        $this->save();
    }

    public function updateLastActivity(?DiscussionPost $post = null): void {
        $this->last_activity_at = now();
        if ($post) {
            $this->last_reply_id = $post->id;
        }
        $this->save();
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

    public function markAsResolved(?DiscussionPost $answer = null): void {
        $this->status = self::STATUS_RESOLVED;
        if ($answer) {
            $this->accepted_answer_id = $answer->id;
            $answer->update(['is_answer' => true]);
        }
        $this->save();
    }

    public function isSubscribedBy(Student $student): bool {
        return $this->subscriptions()->where('student_id', $student->id)->exists();
    }

    public function subscribe(Student $student, string $frequency = 'instant'): void {
        $this->subscriptions()->updateOrCreate(
            ['student_id' => $student->id],
            ['frequency' => $frequency]
        );
    }

    public function unsubscribe(Student $student): void {
        $this->subscriptions()->where('student_id', $student->id)->delete();
    }
}
