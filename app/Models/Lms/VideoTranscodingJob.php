<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoTranscodingJob extends Model
{
    use HasFactory;

    protected $table = 'lms_video_transcoding_jobs';

    protected $fillable = [
        'video_id',
        'format',
        'codec',
        'container',
        'status',
        'input_path',
        'output_path',
        'output_size',
        'bitrate',
        'resolution',
        'progress',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    // Standard transcoding presets
    public const PRESETS = [
        '360p' => [
            'width' => 640,
            'height' => 360,
            'bitrate' => 800, // kbps
            'audioBitrate' => 96,
        ],
        '480p' => [
            'width' => 854,
            'height' => 480,
            'bitrate' => 1400,
            'audioBitrate' => 128,
        ],
        '720p' => [
            'width' => 1280,
            'height' => 720,
            'bitrate' => 2800,
            'audioBitrate' => 128,
        ],
        '1080p' => [
            'width' => 1920,
            'height' => 1080,
            'bitrate' => 5000,
            'audioBitrate' => 192,
        ],
    ];

    // Relationships
    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    // Status helpers
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    // Methods
    public function markAsProcessing(): void
    {
        $this->update([
            'status' => self::STATUS_PROCESSING,
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted(string $outputPath, int $outputSize): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'output_path' => $outputPath,
            'output_size' => $outputSize,
            'progress' => 100,
            'completed_at' => now(),
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
            'completed_at' => now(),
        ]);
    }

    public function updateProgress(int $progress): void
    {
        $this->update(['progress' => min(100, max(0, $progress))]);
    }

    public static function getPreset(string $format): ?array
    {
        return self::PRESETS[$format] ?? null;
    }

    public function getDurationAttribute(): ?string
    {
        if (!$this->started_at) {
            return null;
        }

        $end = $this->completed_at ?? now();
        $seconds = $this->started_at->diffInSeconds($end);

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%dh %dm %ds', $hours, $minutes, $secs);
        } elseif ($minutes > 0) {
            return sprintf('%dm %ds', $minutes, $secs);
        }

        return sprintf('%ds', $secs);
    }
}
