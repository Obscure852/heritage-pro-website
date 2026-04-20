<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningPathMilestone extends Model {
    protected $table = 'lms_learning_path_milestones';

    protected $fillable = [
        'learning_path_id',
        'title',
        'description',
        'icon',
        'position',
        'courses_required',
        'badge_id',
        'points_awarded',
    ];

    // Relationships
    public function learningPath(): BelongsTo {
        return $this->belongsTo(LearningPath::class, 'learning_path_id');
    }

    public function badge(): BelongsTo {
        return $this->belongsTo(Badge::class, 'badge_id');
    }

    public function completions(): HasMany {
        return $this->hasMany(LearningPathMilestoneCompletion::class, 'milestone_id');
    }

    // Methods
    public function isCompletedBy(LearningPathEnrollment $enrollment): bool {
        return $this->completions()->where('enrollment_id', $enrollment->id)->exists();
    }

    public function checkCompletion(LearningPathEnrollment $enrollment): bool {
        if ($this->isCompletedBy($enrollment)) {
            return true;
        }

        // Check if enough courses are completed
        $completedCount = $enrollment->progress()->where('status', 'completed')->count();

        if ($completedCount >= $this->courses_required) {
            $this->markCompleted($enrollment);
            return true;
        }

        return false;
    }

    public function markCompleted(LearningPathEnrollment $enrollment): LearningPathMilestoneCompletion {
        $completion = LearningPathMilestoneCompletion::create([
            'enrollment_id' => $enrollment->id,
            'milestone_id' => $this->id,
            'completed_at' => now(),
        ]);

        // Award badge if configured
        if ($this->badge_id) {
            app(\App\Services\GamificationService::class)
                ->awardBadge($enrollment->student, $this->badge);
        }

        // Award points if configured
        if ($this->points_awarded > 0) {
            app(\App\Services\GamificationService::class)
                ->awardPoints($enrollment->student, $this->points_awarded, 'milestone', "Completed milestone: {$this->title}");
        }

        return $completion;
    }
}
