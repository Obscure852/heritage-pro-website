<?php

namespace App\Models\Lms;

use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Notification extends Model {
    protected $table = 'lms_notifications';

    protected $fillable = [
        'student_id',
        'type',
        'title',
        'message',
        'icon',
        'color',
        'action_url',
        'action_text',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at',
        'email_sent_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'email_sent_at' => 'datetime',
    ];

    // Notification types
    public const TYPE_COURSE_ENROLLED = 'course_enrolled';
    public const TYPE_COURSE_COMPLETED = 'course_completed';
    public const TYPE_MODULE_UNLOCKED = 'module_unlocked';
    public const TYPE_QUIZ_GRADED = 'quiz_graded';
    public const TYPE_ASSIGNMENT_GRADED = 'assignment_graded';
    public const TYPE_ASSIGNMENT_DUE = 'assignment_due';
    public const TYPE_BADGE_EARNED = 'badge_earned';
    public const TYPE_NEW_REPLY = 'new_reply';
    public const TYPE_MENTION = 'mention';
    public const TYPE_THREAD_ANSWERED = 'thread_answered';
    public const TYPE_ANNOUNCEMENT = 'announcement';
    public const TYPE_DEADLINE_REMINDER = 'deadline_reminder';

    public static array $typeConfig = [
        'course_enrolled' => ['icon' => 'fas fa-book', 'color' => '#3b82f6'],
        'course_completed' => ['icon' => 'fas fa-graduation-cap', 'color' => '#10b981'],
        'module_unlocked' => ['icon' => 'fas fa-unlock', 'color' => '#8b5cf6'],
        'quiz_graded' => ['icon' => 'fas fa-check-circle', 'color' => '#f59e0b'],
        'assignment_graded' => ['icon' => 'fas fa-file-alt', 'color' => '#f59e0b'],
        'assignment_due' => ['icon' => 'fas fa-clock', 'color' => '#ef4444'],
        'badge_earned' => ['icon' => 'fas fa-award', 'color' => '#eab308'],
        'new_reply' => ['icon' => 'fas fa-reply', 'color' => '#6366f1'],
        'mention' => ['icon' => 'fas fa-at', 'color' => '#ec4899'],
        'thread_answered' => ['icon' => 'fas fa-check', 'color' => '#10b981'],
        'announcement' => ['icon' => 'fas fa-bullhorn', 'color' => '#f97316'],
        'deadline_reminder' => ['icon' => 'fas fa-bell', 'color' => '#ef4444'],
    ];

    public function student(): BelongsTo {
        return $this->belongsTo(Student::class);
    }

    public function notifiable(): MorphTo {
        return $this->morphTo();
    }

    public function email(): HasOne {
        return $this->hasOne(NotificationEmail::class, 'notification_id');
    }

    public function scopeUnread($query) {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query) {
        return $query->whereNotNull('read_at');
    }

    public function scopeRecent($query, int $days = 30) {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeOfType($query, string $type) {
        return $query->where('type', $type);
    }

    public function getIsReadAttribute(): bool {
        return $this->read_at !== null;
    }

    public function markAsRead(): void {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    public function markAsUnread(): void {
        $this->update(['read_at' => null]);
    }

    public static function send(
        Student $student,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        ?string $actionText = null,
        $notifiable = null,
        array $data = []
    ): self {
        $config = self::$typeConfig[$type] ?? ['icon' => 'fas fa-bell', 'color' => '#6b7280'];

        return self::create([
            'student_id' => $student->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'icon' => $config['icon'],
            'color' => $config['color'],
            'action_url' => $actionUrl,
            'action_text' => $actionText,
            'notifiable_type' => $notifiable ? get_class($notifiable) : null,
            'notifiable_id' => $notifiable?->id,
            'data' => $data,
        ]);
    }

    public static function getUnreadCount(int $studentId): int {
        return self::where('student_id', $studentId)->unread()->count();
    }
}
