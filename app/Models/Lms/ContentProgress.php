<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentProgress extends Model
{
    use HasFactory;

    protected $table = 'lms_content_progress';

    protected $fillable = [
        'enrollment_id',
        'content_item_id',
        'status',
        'started_at',
        'completed_at',
        'time_spent_seconds',
        'score',
        'score_percentage',
        'attempts',
        'best_score',
        'last_position',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'score' => 'decimal:2',
        'score_percentage' => 'decimal:2',
        'best_score' => 'decimal:2',
        'last_position' => 'array',
    ];

    // Relationships
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function contentItem(): BelongsTo
    {
        return $this->belongsTo(ContentItem::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeNotStarted($query)
    {
        return $query->where('status', 'not_started');
    }

    // Helper Methods
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function markAsStarted(): void
    {
        if ($this->status === 'not_started') {
            $this->update([
                'status' => 'in_progress',
                'started_at' => now(),
            ]);
        }
    }

    public function markAsCompleted(?float $score = null, ?float $percentage = null): void
    {
        $updateData = [
            'status' => 'completed',
            'completed_at' => now(),
        ];

        if ($score !== null) {
            $updateData['score'] = $score;
            if ($this->best_score === null || $score > $this->best_score) {
                $updateData['best_score'] = $score;
            }
        }

        if ($percentage !== null) {
            $updateData['score_percentage'] = $percentage;
        }

        $this->update($updateData);
        $this->enrollment->updateProgress();
    }

    public function incrementAttempts(): void
    {
        $this->increment('attempts');
    }

    public function addTimeSpent(int $seconds): void
    {
        $this->increment('time_spent_seconds', $seconds);
    }

    public function updatePosition(array $position): void
    {
        $this->update(['last_position' => $position]);
    }

    public function getFormattedTimeSpentAttribute(): string
    {
        $seconds = $this->time_spent_seconds;
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $secs);
        }

        return sprintf('%d:%02d', $minutes, $secs);
    }
}
