<?php

namespace App\Models\Lms;

use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscussionMention extends Model {
    protected $table = 'lms_discussion_mentions';

    protected $fillable = [
        'post_id',
        'mentioned_student_id',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function post(): BelongsTo {
        return $this->belongsTo(DiscussionPost::class, 'post_id');
    }

    public function mentionedStudent(): BelongsTo {
        return $this->belongsTo(Student::class, 'mentioned_student_id');
    }

    public function markAsRead(): void {
        $this->update(['is_read' => true]);
    }
}
