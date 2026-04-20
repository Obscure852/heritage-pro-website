<?php

namespace App\Models\Lms;

use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model {
    protected $table = 'lms_activity_logs';
    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'course_id',
        'activity_type',
        'subject_type',
        'subject_id',
        'metadata',
        'duration_seconds',
        'ip_address',
        'user_agent',
        'device_type',
        'created_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public static array $activityTypes = [
        'login' => 'Login',
        'logout' => 'Logout',
        'content_view' => 'Content View',
        'content_complete' => 'Content Complete',
        'video_play' => 'Video Play',
        'video_pause' => 'Video Pause',
        'video_complete' => 'Video Complete',
        'quiz_start' => 'Quiz Start',
        'quiz_submit' => 'Quiz Submit',
        'assignment_view' => 'Assignment View',
        'assignment_submit' => 'Assignment Submit',
        'discussion_view' => 'Discussion View',
        'discussion_post' => 'Discussion Post',
        'discussion_reply' => 'Discussion Reply',
        'file_download' => 'File Download',
        'page_view' => 'Page View',
        'search' => 'Search',
        'enrollment' => 'Enrollment',
        'badge_earned' => 'Badge Earned',
    ];

    public function student(): BelongsTo {
        return $this->belongsTo(Student::class);
    }

    public function course(): BelongsTo {
        return $this->belongsTo(Course::class);
    }

    public function subject(): MorphTo {
        return $this->morphTo();
    }

    public static function log(
        int $studentId,
        string $activityType,
        ?int $courseId = null,
        ?Model $subject = null,
        array $metadata = [],
        ?int $durationSeconds = null
    ): self {
        return self::create([
            'student_id' => $studentId,
            'course_id' => $courseId,
            'activity_type' => $activityType,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id,
            'metadata' => $metadata ?: null,
            'duration_seconds' => $durationSeconds,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'device_type' => self::detectDeviceType(request()->userAgent()),
            'created_at' => now(),
        ]);
    }

    protected static function detectDeviceType(?string $userAgent): ?string {
        if (!$userAgent) return null;

        $userAgent = strtolower($userAgent);

        if (str_contains($userAgent, 'mobile') || str_contains($userAgent, 'android')) {
            return 'mobile';
        }
        if (str_contains($userAgent, 'tablet') || str_contains($userAgent, 'ipad')) {
            return 'tablet';
        }
        return 'desktop';
    }
}
