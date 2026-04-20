<?php

namespace App\Models\Lms;

use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscussionSubscription extends Model {
    protected $table = 'lms_discussion_subscriptions';

    protected $fillable = [
        'student_id',
        'thread_id',
        'frequency',
    ];

    public function student(): BelongsTo {
        return $this->belongsTo(Student::class);
    }

    public function thread(): BelongsTo {
        return $this->belongsTo(DiscussionThread::class);
    }
}
