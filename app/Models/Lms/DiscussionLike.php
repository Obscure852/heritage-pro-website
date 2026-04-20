<?php

namespace App\Models\Lms;

use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DiscussionLike extends Model {
    protected $table = 'lms_discussion_likes';

    protected $fillable = [
        'student_id',
        'likeable_type',
        'likeable_id',
    ];

    public function student(): BelongsTo {
        return $this->belongsTo(Student::class);
    }

    public function likeable(): MorphTo {
        return $this->morphTo();
    }
}
