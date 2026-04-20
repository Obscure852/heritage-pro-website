<?php

namespace App\Models\Lms;

use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PointsTransaction extends Model {
    protected $table = 'lms_points_transactions';

    protected $fillable = [
        'student_id',
        'course_id',
        'points',
        'balance_after',
        'type',
        'description',
        'pointable_type',
        'pointable_id',
    ];

    protected $casts = [
        'points' => 'integer',
        'balance_after' => 'integer',
    ];

    public const TYPE_COURSE_COMPLETE = 'course_complete';
    public const TYPE_MODULE_COMPLETE = 'module_complete';
    public const TYPE_CONTENT_COMPLETE = 'content_complete';
    public const TYPE_QUIZ_PASS = 'quiz_pass';
    public const TYPE_QUIZ_PERFECT = 'quiz_perfect';
    public const TYPE_ASSIGNMENT_SUBMIT = 'assignment_submit';
    public const TYPE_ASSIGNMENT_EXCELLENT = 'assignment_excellent';
    public const TYPE_BADGE_EARNED = 'badge_earned';
    public const TYPE_STREAK_BONUS = 'streak_bonus';
    public const TYPE_FIRST_LOGIN = 'first_login';
    public const TYPE_DAILY_LOGIN = 'daily_login';
    public const TYPE_BONUS = 'bonus';
    public const TYPE_PENALTY = 'penalty';
    public const TYPE_ADMIN_ADJUSTMENT = 'admin_adjustment';

    public static array $typeLabels = [
        'course_complete' => 'Course Completed',
        'module_complete' => 'Module Completed',
        'content_complete' => 'Content Completed',
        'quiz_pass' => 'Quiz Passed',
        'quiz_perfect' => 'Perfect Quiz Score',
        'assignment_submit' => 'Assignment Submitted',
        'assignment_excellent' => 'Excellent Assignment',
        'badge_earned' => 'Badge Earned',
        'streak_bonus' => 'Streak Bonus',
        'first_login' => 'First Login',
        'daily_login' => 'Daily Login',
        'discussion_post' => 'Discussion Post',
        'helpful_answer' => 'Helpful Answer',
        'bonus' => 'Bonus Points',
        'penalty' => 'Penalty',
        'admin_adjustment' => 'Admin Adjustment',
    ];

    public function student(): BelongsTo {
        return $this->belongsTo(Student::class);
    }

    public function course(): BelongsTo {
        return $this->belongsTo(Course::class);
    }

    public function pointable(): MorphTo {
        return $this->morphTo();
    }

    public function getTypeLabelAttribute(): string {
        return self::$typeLabels[$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }

    public function getIsPositiveAttribute(): bool {
        return $this->points > 0;
    }

    public function scopePositive($query) {
        return $query->where('points', '>', 0);
    }

    public function scopeNegative($query) {
        return $query->where('points', '<', 0);
    }

    public function scopeForCourse($query, int $courseId) {
        return $query->where('course_id', $courseId);
    }

    public function scopeOfType($query, string $type) {
        return $query->where('type', $type);
    }
}
