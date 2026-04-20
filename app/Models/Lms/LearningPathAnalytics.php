<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningPathAnalytics extends Model {
    protected $table = 'lms_learning_path_analytics';

    protected $fillable = [
        'learning_path_id',
        'date',
        'enrollments',
        'active_learners',
        'completions',
        'avg_progress',
        'avg_completion_days',
        'course_completion_rates',
        'milestone_completion_rates',
    ];

    protected $casts = [
        'date' => 'date',
        'avg_progress' => 'decimal:2',
        'course_completion_rates' => 'array',
        'milestone_completion_rates' => 'array',
    ];

    public function learningPath(): BelongsTo {
        return $this->belongsTo(LearningPath::class);
    }

    public function getCompletionRateAttribute(): float {
        if ($this->enrollments === 0) return 0;
        return round(($this->completions / $this->enrollments) * 100, 2);
    }

    public function getActiveRateAttribute(): float {
        if ($this->enrollments === 0) return 0;
        return round(($this->active_learners / $this->enrollments) * 100, 2);
    }
}
