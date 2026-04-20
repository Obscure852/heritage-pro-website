<?php

namespace App\Models\Lms;

use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAchievement extends Model {
    protected $table = 'lms_student_achievements';

    protected $fillable = [
        'student_id',
        'achievement_id',
        'course_id',
        'progress',
        'current_value',
        'target_value',
        'unlocked_at',
    ];

    protected $casts = [
        'progress' => 'integer',
        'current_value' => 'integer',
        'target_value' => 'integer',
        'unlocked_at' => 'datetime',
    ];

    public function student(): BelongsTo {
        return $this->belongsTo(Student::class);
    }

    public function achievement(): BelongsTo {
        return $this->belongsTo(Achievement::class);
    }

    public function course(): BelongsTo {
        return $this->belongsTo(Course::class);
    }

    public function scopeUnlocked($query) {
        return $query->whereNotNull('unlocked_at');
    }

    public function scopeInProgress($query) {
        return $query->whereNull('unlocked_at')->where('progress', '>', 0);
    }

    public function getIsUnlockedAttribute(): bool {
        return $this->unlocked_at !== null;
    }

    public function incrementProgress(int $amount = 1): bool {
        if ($this->is_unlocked) {
            return false;
        }

        $this->current_value += $amount;
        $this->progress = min(100, (int)(($this->current_value / $this->target_value) * 100));

        if ($this->current_value >= $this->target_value) {
            $this->unlock();
        }

        return $this->save();
    }

    public function unlock(): void {
        if ($this->is_unlocked) {
            return;
        }

        $this->unlocked_at = now();
        $this->progress = 100;
        $this->save();

        // Award points if configured
        if ($this->achievement->points_reward > 0) {
            $studentPoints = StudentPoints::getOrCreate($this->student_id, $this->course_id);
            $studentPoints->addPoints(
                $this->achievement->points_reward,
                PointsTransaction::TYPE_BADGE_EARNED,
                "Unlocked: {$this->achievement->name}",
                $this->achievement
            );
        }

        // Award badge if configured
        if ($this->achievement->badge_id) {
            $badge = $this->achievement->badge;
            if ($badge && !$badge->isEarnedBy($this->student, $this->course)) {
                $badge->awardTo($this->student, $this->course, [
                    'source' => 'achievement',
                    'achievement_id' => $this->achievement_id,
                ]);
            }
        }
    }
}
