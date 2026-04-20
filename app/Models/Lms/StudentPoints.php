<?php

namespace App\Models\Lms;

use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentPoints extends Model {
    protected $table = 'lms_student_points';

    protected $fillable = [
        'student_id',
        'course_id',
        'total_points',
        'level',
        'xp_to_next_level',
        'current_streak',
        'longest_streak',
        'last_activity_date',
    ];

    protected $casts = [
        'total_points' => 'integer',
        'level' => 'integer',
        'xp_to_next_level' => 'integer',
        'current_streak' => 'integer',
        'longest_streak' => 'integer',
        'last_activity_date' => 'date',
    ];

    // XP required per level (can be customized)
    public static array $levelThresholds = [
        1 => 0,
        2 => 100,
        3 => 250,
        4 => 500,
        5 => 1000,
        6 => 1750,
        7 => 2750,
        8 => 4000,
        9 => 5500,
        10 => 7500,
        11 => 10000,
        12 => 13000,
        13 => 17000,
        14 => 22000,
        15 => 28000,
        16 => 35000,
        17 => 43000,
        18 => 52000,
        19 => 62000,
        20 => 75000,
    ];

    public function student(): BelongsTo {
        return $this->belongsTo(Student::class);
    }

    public function course(): BelongsTo {
        return $this->belongsTo(Course::class);
    }

    public function transactions(): HasMany {
        return $this->hasMany(PointsTransaction::class, 'student_id', 'student_id')
            ->when($this->course_id, fn($q) => $q->where('course_id', $this->course_id));
    }

    public static function getOrCreate(int $studentId, ?int $courseId = null): self {
        return self::firstOrCreate(
            ['student_id' => $studentId, 'course_id' => $courseId],
            ['total_points' => 0, 'level' => 1, 'xp_to_next_level' => 100]
        );
    }

    public function addPoints(int $points, string $type, ?string $description = null, $pointable = null): PointsTransaction {
        $this->total_points += $points;
        $this->updateLevel();
        $this->updateStreak();
        $this->save();

        return PointsTransaction::create([
            'student_id' => $this->student_id,
            'course_id' => $this->course_id,
            'points' => $points,
            'balance_after' => $this->total_points,
            'type' => $type,
            'description' => $description,
            'pointable_type' => $pointable ? get_class($pointable) : null,
            'pointable_id' => $pointable?->id,
        ]);
    }

    public function updateLevel(): void {
        $newLevel = 1;
        foreach (self::$levelThresholds as $level => $threshold) {
            if ($this->total_points >= $threshold) {
                $newLevel = $level;
            }
        }

        $this->level = $newLevel;

        // Calculate XP to next level
        $nextLevel = $newLevel + 1;
        if (isset(self::$levelThresholds[$nextLevel])) {
            $this->xp_to_next_level = self::$levelThresholds[$nextLevel] - $this->total_points;
        } else {
            $this->xp_to_next_level = 0; // Max level reached
        }
    }

    public function updateStreak(): void {
        $today = now()->toDateString();
        $lastActivity = $this->last_activity_date?->toDateString();

        if ($lastActivity === $today) {
            return; // Already active today
        }

        $yesterday = now()->subDay()->toDateString();

        if ($lastActivity === $yesterday) {
            $this->current_streak++;
        } else {
            $this->current_streak = 1;
        }

        if ($this->current_streak > $this->longest_streak) {
            $this->longest_streak = $this->current_streak;
        }

        $this->last_activity_date = $today;
    }

    public function getProgressToNextLevelAttribute(): int {
        if ($this->level >= 20) {
            return 100;
        }

        $currentThreshold = self::$levelThresholds[$this->level] ?? 0;
        $nextThreshold = self::$levelThresholds[$this->level + 1] ?? $currentThreshold;
        $range = $nextThreshold - $currentThreshold;

        if ($range <= 0) {
            return 100;
        }

        $progress = $this->total_points - $currentThreshold;
        return min(100, (int)(($progress / $range) * 100));
    }

    public function getLevelTitleAttribute(): string {
        $titles = [
            1 => 'Newcomer',
            2 => 'Beginner',
            3 => 'Learner',
            4 => 'Student',
            5 => 'Apprentice',
            6 => 'Scholar',
            7 => 'Adept',
            8 => 'Expert',
            9 => 'Master',
            10 => 'Grandmaster',
            11 => 'Sage',
            12 => 'Virtuoso',
            13 => 'Luminary',
            14 => 'Prodigy',
            15 => 'Genius',
            16 => 'Visionary',
            17 => 'Legend',
            18 => 'Mythic',
            19 => 'Transcendent',
            20 => 'Enlightened',
        ];

        return $titles[$this->level] ?? 'Unknown';
    }
}
