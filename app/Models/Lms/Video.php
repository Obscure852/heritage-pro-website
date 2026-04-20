<?php

namespace App\Models\Lms;

use App\Jobs\ProcessVideoTranscode;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Storage;

class Video extends Model
{
    use HasFactory;

    protected $table = 'lms_videos';

    protected $fillable = [
        'content_item_id',
        'source_type',
        'source_id',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size_bytes',
        'duration_seconds',
        'width',
        'height',
        'bitrate',
        'codec',
        'thumbnail_path',
        'poster_path',
        'transcoding_status',
        'hls_playlist_path',
        'transcoded_formats',
        'captions',
        'transcript',
        'chapters',
        'interactive_elements',
        'completion_threshold',
        'uploaded_by',
    ];

    protected $casts = [
        'transcoded_formats' => 'array',
        'captions' => 'array',
        'chapters' => 'array',
        'interactive_elements' => 'array',
    ];

    // Transcoding status constants
    public const TRANSCODING_PENDING = 'pending';
    public const TRANSCODING_PROCESSING = 'processing';
    public const TRANSCODING_COMPLETED = 'completed';
    public const TRANSCODING_FAILED = 'failed';
    public const TRANSCODING_NOT_REQUIRED = 'not_required';

    // Supported video formats
    public const SUPPORTED_FORMATS = ['mp4', 'mov', 'avi', 'mkv', 'webm', 'wmv', 'flv', 'm4v'];
    public const MAX_UPLOAD_SIZE_MB = 2048; // 2GB

    // Relationships
    public function contentItem(): BelongsTo
    {
        return $this->belongsTo(ContentItem::class);
    }

    public function contentItemMorph(): MorphOne
    {
        return $this->morphOne(ContentItem::class, 'contentable');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(VideoProgress::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function transcodingJobs(): HasMany
    {
        return $this->hasMany(VideoTranscodingJob::class);
    }

    public function qualities(): HasMany
    {
        return $this->hasMany(VideoQuality::class);
    }

    public function chunks(): HasMany
    {
        return $this->hasMany(VideoChunk::class);
    }

    // Helper Methods
    public function isYouTube(): bool
    {
        return $this->source_type === 'youtube';
    }

    public function isUpload(): bool
    {
        return $this->source_type === 'upload';
    }

    public function getYouTubeEmbedUrl(): ?string
    {
        if (!$this->isYouTube() || !$this->source_id) {
            return null;
        }

        return "https://www.youtube.com/embed/{$this->source_id}";
    }

    public function getYouTubeThumbnailUrl(): ?string
    {
        if (!$this->isYouTube() || !$this->source_id) {
            return null;
        }

        return "https://img.youtube.com/vi/{$this->source_id}/maxresdefault.jpg";
    }

    public function getDurationFormattedAttribute(): string
    {
        if (!$this->duration_seconds) {
            return '0:00';
        }

        $hours = floor($this->duration_seconds / 3600);
        $minutes = floor(($this->duration_seconds % 3600) / 60);
        $seconds = $this->duration_seconds % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    public function getProgressForStudent($studentId): ?VideoProgress
    {
        return $this->progress()->where('student_id', $studentId)->first();
    }

    public static function extractYouTubeId(string $url): ?string
    {
        $patterns = [
            '/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/',
            '/^([a-zA-Z0-9_-]{11})$/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    // Transcoding methods
    public function needsTranscoding(): bool
    {
        return $this->isUpload() && $this->transcoding_status === self::TRANSCODING_PENDING;
    }

    public function isTranscoding(): bool
    {
        return $this->transcoding_status === self::TRANSCODING_PROCESSING;
    }

    public function isTranscoded(): bool
    {
        return $this->transcoding_status === self::TRANSCODING_COMPLETED;
    }

    public function getResolutionAttribute(): ?string
    {
        if ($this->width && $this->height) {
            return $this->width . 'x' . $this->height;
        }
        return null;
    }

    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_size_bytes) {
            return 'Unknown';
        }

        $bytes = $this->file_size_bytes;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getVideoUrlAttribute(): ?string
    {
        if ($this->isYouTube()) {
            return $this->getYouTubeEmbedUrl();
        }

        if ($this->file_path) {
            return Storage::disk('public')->url($this->file_path);
        }

        return null;
    }

    public function getDefaultQuality(): ?VideoQuality
    {
        return $this->qualities()->where('is_default', true)->first()
            ?? $this->qualities()->orderByDesc('height')->first();
    }

    public function getAvailableQualities(): array
    {
        return $this->qualities()
            ->orderByDesc('height')
            ->get()
            ->map(fn($q) => [
                'label' => $q->label,
                'url' => $q->url,
                'width' => $q->width,
                'height' => $q->height,
            ])
            ->toArray();
    }

    public function startTranscoding(array $formats = ['720p', '480p', '360p']): void
    {
        $this->update(['transcoding_status' => self::TRANSCODING_PROCESSING]);

        foreach ($formats as $format) {
            $preset = VideoTranscodingJob::getPreset($format);
            if (!$preset) continue;

            // Skip if higher than source resolution
            if ($this->height && $preset['height'] > $this->height) {
                continue;
            }

            $job = $this->transcodingJobs()->create([
                'format' => $format,
                'codec' => 'h264',
                'container' => 'mp4',
                'status' => VideoTranscodingJob::STATUS_PENDING,
                'input_path' => $this->file_path,
                'resolution' => $preset['width'] . 'x' . $preset['height'],
                'bitrate' => $preset['bitrate'],
            ]);

            // Dispatch background job
            ProcessVideoTranscode::dispatch($job);
        }
    }

    public function checkTranscodingComplete(): void
    {
        $pendingJobs = $this->transcodingJobs()
            ->whereIn('status', [
                VideoTranscodingJob::STATUS_PENDING,
                VideoTranscodingJob::STATUS_PROCESSING
            ])
            ->count();

        if ($pendingJobs === 0) {
            $completedJobs = $this->transcodingJobs()
                ->where('status', VideoTranscodingJob::STATUS_COMPLETED)
                ->count();

            $this->update([
                'transcoding_status' => $completedJobs > 0
                    ? self::TRANSCODING_COMPLETED
                    : self::TRANSCODING_FAILED,
            ]);
        }
    }

    public function generateThumbnail(): ?string
    {
        if (!$this->file_path || !$this->isUpload()) {
            return null;
        }

        $inputPath = Storage::disk('public')->path($this->file_path);
        $thumbnailDir = 'lms/videos/thumbnails';
        $thumbnailName = pathinfo($this->file_path, PATHINFO_FILENAME) . '_thumb.jpg';
        $thumbnailPath = $thumbnailDir . '/' . $thumbnailName;
        $outputPath = Storage::disk('public')->path($thumbnailPath);

        // Create directory if not exists
        if (!is_dir(dirname($outputPath))) {
            mkdir(dirname($outputPath), 0755, true);
        }

        // Generate thumbnail at 5 seconds or 10% of duration
        $seekTime = min(5, floor(($this->duration_seconds ?? 10) * 0.1));

        $command = sprintf(
            'ffmpeg -i %s -ss %d -vframes 1 -vf "scale=640:-1" -y %s 2>&1',
            escapeshellarg($inputPath),
            $seekTime,
            escapeshellarg($outputPath)
        );

        exec($command, $output, $returnCode);

        if ($returnCode === 0 && file_exists($outputPath)) {
            $this->update(['thumbnail_path' => $thumbnailPath]);
            return $thumbnailPath;
        }

        return null;
    }

    public function extractMetadata(): array
    {
        if (!$this->file_path || !$this->isUpload()) {
            return [];
        }

        $inputPath = Storage::disk('public')->path($this->file_path);

        // Use ffprobe to get video metadata
        $command = sprintf(
            'ffprobe -v quiet -print_format json -show_format -show_streams %s 2>&1',
            escapeshellarg($inputPath)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            return [];
        }

        $metadata = json_decode(implode("\n", $output), true);

        if (!$metadata) {
            return [];
        }

        $videoStream = null;
        foreach ($metadata['streams'] ?? [] as $stream) {
            if ($stream['codec_type'] === 'video') {
                $videoStream = $stream;
                break;
            }
        }

        $result = [];

        if ($videoStream) {
            $result['width'] = $videoStream['width'] ?? null;
            $result['height'] = $videoStream['height'] ?? null;
            $result['codec'] = $videoStream['codec_name'] ?? null;
            $result['duration_seconds'] = isset($videoStream['duration'])
                ? (int) $videoStream['duration']
                : null;
        }

        if (isset($metadata['format'])) {
            $result['duration_seconds'] = $result['duration_seconds']
                ?? (int) ($metadata['format']['duration'] ?? 0);
            $result['bitrate'] = isset($metadata['format']['bit_rate'])
                ? (int) ($metadata['format']['bit_rate'] / 1000)
                : null;
        }

        return $result;
    }

    public function deleteVideoFiles(): void
    {
        // Delete original file
        if ($this->file_path && Storage::disk('public')->exists($this->file_path)) {
            Storage::disk('public')->delete($this->file_path);
        }

        // Delete thumbnail
        if ($this->thumbnail_path && Storage::disk('public')->exists($this->thumbnail_path)) {
            Storage::disk('public')->delete($this->thumbnail_path);
        }

        // Delete poster
        if ($this->poster_path && Storage::disk('public')->exists($this->poster_path)) {
            Storage::disk('public')->delete($this->poster_path);
        }

        // Delete HLS playlist
        if ($this->hls_playlist_path && Storage::disk('public')->exists($this->hls_playlist_path)) {
            Storage::disk('public')->delete($this->hls_playlist_path);
        }

        // Delete quality files
        foreach ($this->qualities as $quality) {
            $quality->deleteFile();
            $quality->delete();
        }

        // Delete chunk files
        foreach ($this->chunks as $chunk) {
            $chunk->deleteFile();
            $chunk->delete();
        }
    }
}
