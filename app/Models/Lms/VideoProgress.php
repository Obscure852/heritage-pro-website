<?php

namespace App\Models\Lms;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoProgress extends Model
{
    use HasFactory;

    protected $table = 'lms_video_progress';

    protected $fillable = [
        'video_id',
        'student_id',
        'current_time',
        'furthest_time',
        'total_watch_time',
        'watch_percentage',
        'completed',
        'playback_rate',
        'events',
        'last_watched_at',
    ];

    protected $casts = [
        'watch_percentage' => 'decimal:2',
        'playback_rate' => 'decimal:2',
        'completed' => 'boolean',
        'events' => 'array',
        'last_watched_at' => 'datetime',
    ];

    // Relationships
    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    // Helper Methods
    public function updateProgress(int $currentTime, int $duration): void
    {
        $furthest = max($this->furthest_time, $currentTime);
        $percentage = $duration > 0 ? round(($furthest / $duration) * 100, 2) : 0;

        $updateData = [
            'current_time' => $currentTime,
            'furthest_time' => $furthest,
            'watch_percentage' => min($percentage, 100),
            'last_watched_at' => now(),
        ];

        // Check if completed based on threshold
        if ($percentage >= $this->video->completion_threshold && !$this->completed) {
            $updateData['completed'] = true;
        }

        $this->update($updateData);
    }

    public function addWatchTime(int $seconds): void
    {
        $this->increment('total_watch_time', $seconds);
    }

    public function logEvent(string $event, array $data = []): void
    {
        $events = $this->events ?? [];
        $events[] = [
            'event' => $event,
            'data' => $data,
            'timestamp' => now()->toIso8601String(),
        ];

        // Keep only last 100 events
        if (count($events) > 100) {
            $events = array_slice($events, -100);
        }

        $this->update(['events' => $events]);
    }

    public function getFormattedCurrentTimeAttribute(): string
    {
        return $this->formatTime($this->current_time);
    }

    public function getFormattedTotalWatchTimeAttribute(): string
    {
        return $this->formatTime($this->total_watch_time);
    }

    protected function formatTime(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $secs);
        }

        return sprintf('%d:%02d', $minutes, $secs);
    }
}
