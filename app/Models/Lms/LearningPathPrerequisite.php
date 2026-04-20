<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningPathPrerequisite extends Model {
    protected $table = 'lms_learning_path_prerequisites';

    protected $fillable = [
        'path_course_id',
        'prerequisite_course_id',
        'minimum_score',
    ];

    protected $casts = [
        'minimum_score' => 'integer',
    ];

    public function pathCourse(): BelongsTo {
        return $this->belongsTo(LearningPathCourse::class, 'path_course_id');
    }

    public function prerequisiteCourse(): BelongsTo {
        return $this->belongsTo(LearningPathCourse::class, 'prerequisite_course_id');
    }
}
