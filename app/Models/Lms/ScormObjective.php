<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScormObjective extends Model
{
    use HasFactory;

    protected $table = 'lms_scorm_objectives';

    protected $fillable = [
        'attempt_id',
        'objective_id',
        'score_raw',
        'score_min',
        'score_max',
        'score_scaled',
        'success_status',
        'completion_status',
        'progress_measure',
        'description',
    ];

    protected $casts = [
        'score_raw' => 'decimal:2',
        'score_min' => 'decimal:2',
        'score_max' => 'decimal:2',
        'score_scaled' => 'decimal:4',
        'progress_measure' => 'decimal:4',
    ];

    // Status values
    public const SUCCESS_STATUSES = ['passed', 'failed', 'unknown'];
    public const COMPLETION_STATUSES = ['completed', 'incomplete', 'not attempted', 'unknown'];

    // Relationships
    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ScormAttempt::class, 'attempt_id');
    }

    // Accessors
    public function getIsPassedAttribute(): bool
    {
        return $this->success_status === 'passed';
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->completion_status === 'completed';
    }

    public function getScorePercentageAttribute(): ?float
    {
        if ($this->score_scaled !== null) {
            return round($this->score_scaled * 100, 2);
        }

        if ($this->score_raw !== null && $this->score_max) {
            return round(($this->score_raw / $this->score_max) * 100, 2);
        }

        return null;
    }
}
