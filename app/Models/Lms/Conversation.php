<?php

namespace App\Models\Lms;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model {
    protected $table = 'lms_conversations';

    protected $fillable = [
        'student_id',
        'instructor_id',
        'course_id',
        'subject',
        'last_message_at',
        'student_read_at',
        'instructor_read_at',
        'is_archived_by_student',
        'is_archived_by_instructor',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'student_read_at' => 'datetime',
        'instructor_read_at' => 'datetime',
        'is_archived_by_student' => 'boolean',
        'is_archived_by_instructor' => 'boolean',
    ];

    public function student(): BelongsTo {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function instructor(): BelongsTo {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function course(): BelongsTo {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function messages(): HasMany {
        return $this->hasMany(DirectMessage::class, 'conversation_id');
    }

    public function latestMessage(): BelongsTo {
        return $this->belongsTo(DirectMessage::class, 'id')
            ->ofMany('created_at', 'max');
    }

    public function scopeForStudent($query, int $studentId) {
        return $query->where('student_id', $studentId);
    }

    public function scopeForInstructor($query, int $instructorId) {
        return $query->where('instructor_id', $instructorId);
    }

    public function scopeNotArchivedByStudent($query) {
        return $query->where('is_archived_by_student', false);
    }

    public function scopeNotArchivedByInstructor($query) {
        return $query->where('is_archived_by_instructor', false);
    }

    public function scopeRecent($query) {
        return $query->orderByDesc('last_message_at');
    }

    public function hasUnreadForStudent(): bool {
        if (!$this->student_read_at) {
            return $this->messages()->exists();
        }
        return $this->messages()
            ->where('sender_type', '!=', Student::class)
            ->where('created_at', '>', $this->student_read_at)
            ->exists();
    }

    public function hasUnreadForInstructor(): bool {
        if (!$this->instructor_read_at) {
            return $this->messages()->exists();
        }
        return $this->messages()
            ->where('sender_type', '!=', User::class)
            ->where('created_at', '>', $this->instructor_read_at)
            ->exists();
    }

    public function unreadCountForStudent(): int {
        if (!$this->student_read_at) {
            return $this->messages()->where('sender_type', '!=', Student::class)->count();
        }
        return $this->messages()
            ->where('sender_type', '!=', Student::class)
            ->where('created_at', '>', $this->student_read_at)
            ->count();
    }

    public function unreadCountForInstructor(): int {
        if (!$this->instructor_read_at) {
            return $this->messages()->where('sender_type', '!=', User::class)->count();
        }
        return $this->messages()
            ->where('sender_type', '!=', User::class)
            ->where('created_at', '>', $this->instructor_read_at)
            ->count();
    }

    public function markAsReadByStudent(): void {
        $this->update(['student_read_at' => now()]);
    }

    public function markAsReadByInstructor(): void {
        $this->update(['instructor_read_at' => now()]);
    }

    public function archiveForStudent(): void {
        $this->update(['is_archived_by_student' => true]);
    }

    public function archiveForInstructor(): void {
        $this->update(['is_archived_by_instructor' => true]);
    }

    public function unarchiveForStudent(): void {
        $this->update(['is_archived_by_student' => false]);
    }

    public function unarchiveForInstructor(): void {
        $this->update(['is_archived_by_instructor' => false]);
    }

    public function updateLastMessageTime(): void {
        $this->update(['last_message_at' => now()]);
    }

    public static function findOrCreateForStudentAndInstructor(
        int $studentId,
        int $instructorId,
        ?int $courseId = null,
        ?string $subject = null
    ): self {
        return static::firstOrCreate(
            [
                'student_id' => $studentId,
                'instructor_id' => $instructorId,
                'course_id' => $courseId,
            ],
            [
                'subject' => $subject,
                'last_message_at' => now(),
            ]
        );
    }
}
