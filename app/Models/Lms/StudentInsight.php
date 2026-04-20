<?php

namespace App\Models\Lms;

use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentInsight extends Model {
    protected $table = 'lms_student_insights';

    protected $fillable = [
        'student_id',
        'insight_type',
        'course_id',
        'severity',
        'title',
        'description',
        'data',
        'is_dismissed',
        'generated_at',
        'dismissed_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_dismissed' => 'boolean',
        'generated_at' => 'datetime',
        'dismissed_at' => 'datetime',
    ];

    public static array $insightTypes = [
        'at_risk' => 'At Risk',
        'inactive' => 'Inactive',
        'struggling' => 'Struggling',
        'improving' => 'Improving',
        'high_performer' => 'High Performer',
        'consistent' => 'Consistent',
        'deadline_risk' => 'Deadline Risk',
        'engagement_drop' => 'Engagement Drop',
        'grade_improvement' => 'Grade Improvement',
        'completion_near' => 'Near Completion',
    ];

    public static array $severities = [
        'info' => 'Info',
        'warning' => 'Warning',
        'critical' => 'Critical',
    ];

    public function student(): BelongsTo {
        return $this->belongsTo(Student::class);
    }

    public function course(): BelongsTo {
        return $this->belongsTo(Course::class);
    }

    public function scopeActive($query) {
        return $query->where('is_dismissed', false);
    }

    public function scopeForCourse($query, int $courseId) {
        return $query->where('course_id', $courseId);
    }

    public function scopeBySeverity($query, string $severity) {
        return $query->where('severity', $severity);
    }

    public function dismiss(): void {
        $this->update([
            'is_dismissed' => true,
            'dismissed_at' => now(),
        ]);
    }

    public function getSeverityColorAttribute(): string {
        return match($this->severity) {
            'critical' => 'danger',
            'warning' => 'warning',
            default => 'info',
        };
    }

    public function getInsightIconAttribute(): string {
        return match($this->insight_type) {
            'at_risk', 'struggling' => 'exclamation-triangle',
            'inactive' => 'clock',
            'improving', 'grade_improvement' => 'arrow-up',
            'high_performer', 'consistent' => 'star',
            'deadline_risk' => 'calendar-times',
            'engagement_drop' => 'chart-line-down',
            'completion_near' => 'flag-checkered',
            default => 'lightbulb',
        };
    }
}
