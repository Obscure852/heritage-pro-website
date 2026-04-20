<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseAnalytics extends Model {
    protected $table = 'lms_course_analytics';

    protected $fillable = [
        'course_id',
        'date',
        'period',
        'total_enrollments',
        'active_students',
        'new_enrollments',
        'completions',
        'avg_progress',
        'avg_grade',
        'total_time_spent',
        'avg_time_per_student',
        'content_views',
        'quiz_attempts',
        'quiz_passes',
        'assignment_submissions',
        'discussion_posts',
        'engagement_score',
        'completion_funnel',
        'grade_distribution',
    ];

    protected $casts = [
        'date' => 'date',
        'avg_progress' => 'decimal:2',
        'avg_grade' => 'decimal:2',
        'avg_time_per_student' => 'decimal:2',
        'engagement_score' => 'decimal:2',
        'completion_funnel' => 'array',
        'grade_distribution' => 'array',
    ];

    public static array $periods = [
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
    ];

    public function course(): BelongsTo {
        return $this->belongsTo(Course::class);
    }

    public function getCompletionRateAttribute(): float {
        if ($this->total_enrollments === 0) return 0;
        return round(($this->completions / $this->total_enrollments) * 100, 2);
    }

    public function getActiveRateAttribute(): float {
        if ($this->total_enrollments === 0) return 0;
        return round(($this->active_students / $this->total_enrollments) * 100, 2);
    }

    public function getQuizPassRateAttribute(): float {
        if ($this->quiz_attempts === 0) return 0;
        return round(($this->quiz_passes / $this->quiz_attempts) * 100, 2);
    }

    public function getFormattedTotalTimeAttribute(): string {
        $hours = floor($this->total_time_spent / 3600);
        return number_format($hours) . ' hours';
    }
}
