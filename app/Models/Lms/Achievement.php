<?php

namespace App\Models\Lms;

use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Achievement extends Model {
    protected $table = 'lms_achievements';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'type',
        'criteria',
        'points_reward',
        'badge_id',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'criteria' => 'array',
        'is_active' => 'boolean',
        'points_reward' => 'integer',
    ];

    public const TYPE_MILESTONE = 'milestone';
    public const TYPE_CUMULATIVE = 'cumulative';
    public const TYPE_STREAK = 'streak';
    public const TYPE_SPEED = 'speed';
    public const TYPE_QUALITY = 'quality';

    public function badge(): BelongsTo {
        return $this->belongsTo(Badge::class);
    }

    public function studentAchievements(): HasMany {
        return $this->hasMany(StudentAchievement::class);
    }

    public function scopeActive($query) {
        return $query->where('is_active', true);
    }

    public function getIconClassAttribute(): string {
        return $this->icon ?: 'fas fa-trophy';
    }

    public function checkProgress(Student $student, ?Course $course = null): StudentAchievement {
        $studentAchievement = StudentAchievement::firstOrCreate(
            [
                'student_id' => $student->id,
                'achievement_id' => $this->id,
                'course_id' => $course?->id,
            ],
            [
                'progress' => 0,
                'current_value' => 0,
                'target_value' => $this->criteria['target'] ?? 1,
            ]
        );

        return $studentAchievement;
    }

    public function isUnlockedBy(Student $student, ?Course $course = null): bool {
        return $this->studentAchievements()
            ->where('student_id', $student->id)
            ->when($course, fn($q) => $q->where('course_id', $course->id))
            ->whereNotNull('unlocked_at')
            ->exists();
    }
}
