<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAnalytics extends Model {
    protected $table = 'lms_quiz_analytics';

    protected $fillable = [
        'quiz_id',
        'date',
        'attempts',
        'completions',
        'passes',
        'avg_score',
        'median_score',
        'highest_score',
        'lowest_score',
        'avg_duration_seconds',
        'question_analytics',
        'score_distribution',
    ];

    protected $casts = [
        'date' => 'date',
        'avg_score' => 'decimal:2',
        'median_score' => 'decimal:2',
        'highest_score' => 'decimal:2',
        'lowest_score' => 'decimal:2',
        'question_analytics' => 'array',
        'score_distribution' => 'array',
    ];

    public function quiz(): BelongsTo {
        return $this->belongsTo(Quiz::class);
    }

    public function getPassRateAttribute(): float {
        if ($this->completions === 0) return 0;
        return round(($this->passes / $this->completions) * 100, 2);
    }

    public function getCompletionRateAttribute(): float {
        if ($this->attempts === 0) return 0;
        return round(($this->completions / $this->attempts) * 100, 2);
    }

    public function getAvgDurationFormattedAttribute(): string {
        $minutes = floor($this->avg_duration_seconds / 60);
        $seconds = $this->avg_duration_seconds % 60;
        return "{$minutes}m {$seconds}s";
    }

    public function getDifficultQuestionsAttribute(): array {
        if (!$this->question_analytics) return [];

        return collect($this->question_analytics)
            ->filter(fn($q) => ($q['correct_rate'] ?? 100) < 50)
            ->sortBy('correct_rate')
            ->take(5)
            ->all();
    }
}
