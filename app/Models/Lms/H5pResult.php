<?php

namespace App\Models\Lms;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class H5pResult extends Model
{
    use HasFactory;

    protected $table = 'lms_h5p_results';

    protected $fillable = [
        'h5p_content_id',
        'content_item_id',
        'student_id',
        'score',
        'max_score',
        'opened',
        'finished',
        'time_spent',
        'first_opened_at',
        'last_opened_at',
        'completed_at',
    ];

    protected $casts = [
        'first_opened_at' => 'datetime',
        'last_opened_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relationships
    public function h5pContent(): BelongsTo
    {
        return $this->belongsTo(H5pContent::class, 'h5p_content_id');
    }

    public function contentItem(): BelongsTo
    {
        return $this->belongsTo(ContentItem::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    // Accessors
    public function getScorePercentageAttribute(): ?float
    {
        if ($this->max_score === null || $this->max_score === 0) {
            return null;
        }

        return round(($this->score / $this->max_score) * 100, 2);
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->completed_at !== null;
    }

    public function getFormattedTimeSpentAttribute(): string
    {
        $seconds = $this->time_spent;
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%dh %dm %ds', $hours, $minutes, $secs);
        } elseif ($minutes > 0) {
            return sprintf('%dm %ds', $minutes, $secs);
        } else {
            return sprintf('%ds', $secs);
        }
    }

    // Methods
    public function recordOpen(): void
    {
        $this->increment('opened');
        $this->update(['last_opened_at' => now()]);
    }

    public function recordFinish(int $score, int $maxScore): void
    {
        $this->increment('finished');
        $this->update([
            'score' => $score,
            'max_score' => $maxScore,
            'completed_at' => $this->completed_at ?? now(),
        ]);
    }

    public function addTimeSpent(int $seconds): void
    {
        $this->increment('time_spent', $seconds);
    }

    public function updateContentProgress(): void
    {
        if (!$this->content_item_id) {
            return;
        }

        $contentItem = $this->contentItem;
        if (!$contentItem) {
            return;
        }

        $enrollment = Enrollment::where('course_id', $contentItem->module->course_id)
            ->where('student_id', $this->student_id)
            ->first();

        if (!$enrollment) {
            return;
        }

        $progress = ContentProgress::firstOrCreate([
            'enrollment_id' => $enrollment->id,
            'content_item_id' => $contentItem->id,
        ]);

        if ($this->is_completed) {
            $progress->markAsCompleted($this->score, $this->score_percentage);
        }
    }
}
