<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiscussionForum extends Model {
    protected $table = 'lms_discussion_forums';

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'is_enabled',
        'allow_anonymous',
        'require_approval',
        'post_permission',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'allow_anonymous' => 'boolean',
        'require_approval' => 'boolean',
    ];

    public function course(): BelongsTo {
        return $this->belongsTo(Course::class);
    }

    public function categories(): HasMany {
        return $this->hasMany(DiscussionCategory::class, 'forum_id')->orderBy('sort_order');
    }

    public function threads(): HasMany {
        return $this->hasMany(DiscussionThread::class, 'forum_id');
    }

    public function scopeEnabled($query) {
        return $query->where('is_enabled', true);
    }

    public static function getOrCreateForCourse(Course $course): self {
        return self::firstOrCreate(
            ['course_id' => $course->id],
            [
                'title' => $course->title . ' Discussions',
                'is_enabled' => true,
            ]
        );
    }

    public function getThreadsCountAttribute(): int {
        return $this->threads()->count();
    }

    public function getPostsCountAttribute(): int {
        return DiscussionPost::whereIn('thread_id', $this->threads()->pluck('id'))->count();
    }
}
