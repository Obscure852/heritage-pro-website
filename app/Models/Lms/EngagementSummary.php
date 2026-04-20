<?php

namespace App\Models\Lms;

use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EngagementSummary extends Model {
    protected $table = 'lms_engagement_summaries';

    protected $fillable = [
        'student_id',
        'course_id',
        'date',
        'total_time_seconds',
        'content_views',
        'quiz_attempts',
        'assignment_submissions',
        'discussion_posts',
        'videos_watched',
        'login_count',
        'progress_delta',
    ];

    protected $casts = [
        'date' => 'date',
        'progress_delta' => 'decimal:2',
    ];

    public function student(): BelongsTo {
        return $this->belongsTo(Student::class);
    }

    public function course(): BelongsTo {
        return $this->belongsTo(Course::class);
    }

    public function getFormattedTimeAttribute(): string {
        $hours = floor($this->total_time_seconds / 3600);
        $minutes = floor(($this->total_time_seconds % 3600) / 60);

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }
        return "{$minutes}m";
    }

    public function getEngagementScoreAttribute(): float {
        // Weighted engagement score
        $score = 0;
        $score += min($this->total_time_seconds / 3600, 3) * 10; // Up to 30 for 3+ hours
        $score += min($this->content_views, 10) * 2; // Up to 20 for content views
        $score += min($this->quiz_attempts, 5) * 5; // Up to 25 for quiz attempts
        $score += min($this->discussion_posts, 5) * 3; // Up to 15 for discussions
        $score += min($this->login_count, 2) * 5; // Up to 10 for logins

        return min($score, 100);
    }
}
