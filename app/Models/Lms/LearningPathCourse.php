<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningPathCourse extends Model {
    protected $table = 'lms_learning_path_courses';

    protected $fillable = [
        'learning_path_id',
        'course_id',
        'position',
        'is_required',
        'is_milestone',
        'milestone_title',
        'unlock_after_days',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_milestone' => 'boolean',
    ];

    // Relationships
    public function learningPath(): BelongsTo {
        return $this->belongsTo(LearningPath::class, 'learning_path_id');
    }

    public function course(): BelongsTo {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function prerequisites(): HasMany {
        return $this->hasMany(LearningPathPrerequisite::class, 'path_course_id');
    }

    public function dependents(): HasMany {
        return $this->hasMany(LearningPathPrerequisite::class, 'prerequisite_course_id');
    }

    public function progress(): HasMany {
        return $this->hasMany(LearningPathProgress::class, 'path_course_id');
    }

    // Methods
    public function hasPrerequisites(): bool {
        return $this->prerequisites()->exists();
    }

    public function getPrerequisiteCourses(): \Illuminate\Support\Collection {
        return $this->prerequisites->map(fn($p) => $p->prerequisiteCourse->course);
    }

    public function isUnlockedFor($enrollment): bool {
        // If no prerequisites, check drip content
        if (!$this->hasPrerequisites()) {
            if ($this->unlock_after_days) {
                return $enrollment->enrolled_at->addDays($this->unlock_after_days)->isPast();
            }
            return true;
        }

        // Check all prerequisites are completed
        foreach ($this->prerequisites as $prereq) {
            $progress = LearningPathProgress::where('enrollment_id', $enrollment->id)
                ->where('path_course_id', $prereq->prerequisite_course_id)
                ->first();

            if (!$progress || $progress->status !== 'completed') {
                return false;
            }

            // Check minimum score if required
            if ($prereq->minimum_score && $progress->grade < $prereq->minimum_score) {
                return false;
            }
        }

        return true;
    }

    public function addPrerequisite(LearningPathCourse $prerequisite, ?int $minimumScore = null): LearningPathPrerequisite {
        return LearningPathPrerequisite::create([
            'path_course_id' => $this->id,
            'prerequisite_course_id' => $prerequisite->id,
            'minimum_score' => $minimumScore,
        ]);
    }
}
