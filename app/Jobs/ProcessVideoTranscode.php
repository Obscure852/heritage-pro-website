<?php

namespace App\Jobs;

use App\Models\Lms\Video;
use App\Models\Lms\VideoQuality;
use App\Models\Lms\VideoTranscodingJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessVideoTranscode implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600; // 1 hour timeout
    public int $tries = 3;
    public int $maxExceptions = 1;

    protected VideoTranscodingJob $job;

    public function __construct(VideoTranscodingJob $job)
    {
        $this->job = $job;
        $this->onQueue('video-transcoding');
    }

    public function handle(): void
    {
        $this->job->markAsProcessing();

        try {
            $video = $this->job->video;
            $preset = VideoTranscodingJob::getPreset($this->job->format);

            if (!$preset) {
                throw new \Exception("Unknown format: {$this->job->format}");
            }

            // Build paths
            $inputPath = Storage::disk('public')->path($this->job->input_path);
            $outputDir = 'lms/videos/transcoded/' . $video->id;
            $outputFileName = pathinfo($video->original_filename, PATHINFO_FILENAME)
                . '_' . $this->job->format . '.mp4';
            $outputRelativePath = $outputDir . '/' . $outputFileName;
            $outputPath = Storage::disk('public')->path($outputRelativePath);

            // Create output directory
            if (!is_dir(dirname($outputPath))) {
                mkdir(dirname($outputPath), 0755, true);
            }

            // Build FFmpeg command
            $command = $this->buildFfmpegCommand(
                $inputPath,
                $outputPath,
                $preset
            );

            Log::info('Starting video transcode', [
                'job_id' => $this->job->id,
                'video_id' => $video->id,
                'format' => $this->job->format,
            ]);

            // Execute transcoding
            $this->executeWithProgress($command);

            // Verify output file
            if (!file_exists($outputPath)) {
                throw new \Exception('Output file was not created');
            }

            $outputSize = filesize($outputPath);

            // Create quality record
            VideoQuality::create([
                'video_id' => $video->id,
                'label' => $this->job->format,
                'width' => $preset['width'],
                'height' => $preset['height'],
                'bitrate' => $preset['bitrate'],
                'codec' => 'h264',
                'container' => 'mp4',
                'file_path' => $outputRelativePath,
                'file_size' => $outputSize,
                'is_default' => $this->job->format === '720p',
            ]);

            // Mark job as completed
            $this->job->markAsCompleted($outputRelativePath, $outputSize);

            Log::info('Video transcode completed', [
                'job_id' => $this->job->id,
                'video_id' => $video->id,
                'output_size' => $outputSize,
            ]);

            // Check if all transcoding is complete
            $video->checkTranscodingComplete();

        } catch (\Exception $e) {
            Log::error('Video transcode failed', [
                'job_id' => $this->job->id,
                'error' => $e->getMessage(),
            ]);

            $this->job->markAsFailed($e->getMessage());
            $this->job->video->checkTranscodingComplete();

            throw $e;
        }
    }

    protected function buildFfmpegCommand(string $input, string $output, array $preset): string
    {
        $width = $preset['width'];
        $height = $preset['height'];
        $videoBitrate = $preset['bitrate'];
        $audioBitrate = $preset['audioBitrate'];

        // Use scale filter that maintains aspect ratio
        $scaleFilter = "scale='min({$width},iw)':min'({$height},ih)':force_original_aspect_ratio=decrease,pad={$width}:{$height}:(ow-iw)/2:(oh-ih)/2";

        // Simpler scale filter
        $scaleFilter = "scale={$width}:{$height}:force_original_aspect_ratio=decrease,pad={$width}:{$height}:(ow-iw)/2:(oh-ih)/2";

        return sprintf(
            'ffmpeg -i %s ' .
            '-c:v libx264 -preset medium -crf 23 -maxrate %dk -bufsize %dk ' .
            '-vf "%s" ' .
            '-c:a aac -b:a %dk -ar 44100 ' .
            '-movflags +faststart ' .
            '-y %s 2>&1',
            escapeshellarg($input),
            $videoBitrate,
            $videoBitrate * 2,
            $scaleFilter,
            $audioBitrate,
            escapeshellarg($output)
        );
    }

    protected function executeWithProgress(string $command): void
    {
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($command, $descriptors, $pipes);

        if (!is_resource($process)) {
            throw new \Exception('Failed to start FFmpeg process');
        }

        fclose($pipes[0]);

        $output = '';
        $duration = null;

        // Read output and track progress
        while (!feof($pipes[2])) {
            $line = fgets($pipes[2]);
            $output .= $line;

            // Extract duration from input file info
            if (preg_match('/Duration: (\d{2}):(\d{2}):(\d{2})/', $line, $matches)) {
                $duration = ($matches[1] * 3600) + ($matches[2] * 60) + $matches[3];
            }

            // Track progress from time= output
            if ($duration && preg_match('/time=(\d{2}):(\d{2}):(\d{2})/', $line, $matches)) {
                $currentTime = ($matches[1] * 3600) + ($matches[2] * 60) + $matches[3];
                $progress = min(99, (int)(($currentTime / $duration) * 100));
                $this->job->updateProgress($progress);
            }
        }

        fclose($pipes[1]);
        fclose($pipes[2]);

        $returnCode = proc_close($process);

        if ($returnCode !== 0) {
            throw new \Exception("FFmpeg failed with code {$returnCode}: " . substr($output, -500));
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Video transcode job failed completely', [
            'job_id' => $this->job->id,
            'error' => $exception->getMessage(),
        ]);

        $this->job->markAsFailed($exception->getMessage());
        $this->job->video->checkTranscodingComplete();
    }
}
