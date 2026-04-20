<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScormInteraction extends Model
{
    use HasFactory;

    protected $table = 'lms_scorm_interactions';

    protected $fillable = [
        'attempt_id',
        'interaction_id',
        'type',
        'weighting',
        'learner_response',
        'result',
        'latency',
        'description',
        'correct_responses',
        'timestamp',
    ];

    protected $casts = [
        'correct_responses' => 'array',
        'weighting' => 'decimal:2',
        'timestamp' => 'datetime',
    ];

    // Interaction types
    public const TYPES = [
        'true-false',
        'choice',
        'fill-in',
        'long-fill-in',
        'matching',
        'performance',
        'sequencing',
        'likert',
        'numeric',
        'other',
    ];

    // Relationships
    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ScormAttempt::class, 'attempt_id');
    }
}
