<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiscussionCategory extends Model {
    protected $table = 'lms_discussion_categories';

    protected $fillable = [
        'forum_id',
        'name',
        'description',
        'color',
        'icon',
        'sort_order',
        'is_locked',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
    ];

    public function forum(): BelongsTo {
        return $this->belongsTo(DiscussionForum::class, 'forum_id');
    }

    public function threads(): HasMany {
        return $this->hasMany(DiscussionThread::class, 'category_id');
    }

    public function getThreadsCountAttribute(): int {
        return $this->threads()->count();
    }
}
