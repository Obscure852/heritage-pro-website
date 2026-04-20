<?php

namespace App\Models\Lms;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model {
    use SoftDeletes;

    protected $table = 'lms_announcements';

    protected $fillable = [
        'course_id',
        'author_id',
        'title',
        'content',
        'priority',
        'send_email',
        'is_pinned',
        'published_at',
        'expires_at',
    ];

    protected $casts = [
        'send_email' => 'boolean',
        'is_pinned' => 'boolean',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    public function course(): BelongsTo {
        return $this->belongsTo(Course::class);
    }

    public function author(): BelongsTo {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function readBy(): BelongsToMany {
        return $this->belongsToMany(Student::class, 'lms_announcement_reads')
            ->withPivot('read_at');
    }

    public function scopePublished($query) {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeActive($query) {
        return $query->published()
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function scopeForCourse($query, int $courseId) {
        return $query->where('course_id', $courseId);
    }

    public function scopeGlobal($query) {
        return $query->whereNull('course_id');
    }

    public function getIsPublishedAttribute(): bool {
        return $this->published_at && $this->published_at->isPast();
    }

    public function getIsExpiredAttribute(): bool {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isReadBy(Student $student): bool {
        return $this->readBy()->where('student_id', $student->id)->exists();
    }

    public function markAsRead(Student $student): void {
        if (!$this->isReadBy($student)) {
            $this->readBy()->attach($student->id, ['read_at' => now()]);
        }
    }

    public function publish(): void {
        $this->update(['published_at' => now()]);
    }

    public function getPriorityBadgeAttribute(): string {
        return match ($this->priority) {
            'low' => 'bg-secondary',
            'normal' => 'bg-info',
            'high' => 'bg-warning',
            'urgent' => 'bg-danger',
            default => 'bg-secondary',
        };
    }
}
