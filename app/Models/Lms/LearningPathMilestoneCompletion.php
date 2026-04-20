<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningPathMilestoneCompletion extends Model {
    protected $table = 'lms_path_milestone_completions';

    protected $fillable = [
        'enrollment_id',
        'milestone_id',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    // Relationships
    public function enrollment(): BelongsTo {
        return $this->belongsTo(LearningPathEnrollment::class, 'enrollment_id');
    }

    public function milestone(): BelongsTo {
        return $this->belongsTo(LearningPathMilestone::class, 'milestone_id');
    }
}
