<?php

namespace App\Services;

use App\Models\SmsJobTracking;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class JobProgressService
{
    protected $cacheTtl;

    public function __construct()
    {
        $this->cacheTtl = 7200; // 2 hours default
    }

    /**
     * Initialize job progress tracking
     *
     * @param string $jobId
     * @param int $totalRecipients
     * @param int|null $dbId Database tracking ID
     * @return void
     */
    public function initializeJob(string $jobId, int $totalRecipients, ?int $dbId = null): void
    {
        $progressData = [
            'status' => 'processing',
            'total' => $totalRecipients,
            'sent' => 0,
            'failed' => 0,
            'percentage' => 0,
            'message' => 'Initializing...',
            'started_at' => now()->toISOString(),
            'db_id' => $dbId,
            'errors' => []
        ];

        Cache::put($jobId, $progressData, now()->addSeconds($this->cacheTtl));

        Log::info("Job initialized", [
            'job_id' => $jobId,
            'total' => $totalRecipients
        ]);
    }

    /**
     * Update job progress
     *
     * @param string $jobId
     * @param int $sentCount
     * @param int $failedCount
     * @param int $totalRecipients
     * @param SmsJobTracking|null $jobTracking
     * @return void
     */
    public function updateProgress(
        string $jobId,
        int $sentCount,
        int $failedCount,
        int $totalRecipients,
        ?SmsJobTracking $jobTracking = null
    ): void {
        $processedCount = $sentCount + $failedCount;
        $percentage = $totalRecipients > 0 ? round(($processedCount / $totalRecipients) * 100) : 0;

        // Update cache
        $currentProgress = Cache::get($jobId, []);
        $progressData = array_merge($currentProgress, [
            'status' => $processedCount >= $totalRecipients ? 'completed' : 'processing',
            'sent' => $sentCount,
            'failed' => $failedCount,
            'percentage' => $percentage,
            'message' => "Sent {$sentCount} of {$totalRecipients} messages...",
            'updated_at' => now()->toISOString(),
        ]);

        if ($processedCount >= $totalRecipients) {
            $progressData['completed_at'] = now()->toISOString();
        }

        Cache::put($jobId, $progressData, now()->addSeconds($this->cacheTtl));

        // Update database if tracking record exists
        if ($jobTracking) {
            $jobTracking->update([
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'status_message' => $progressData['message'],
                'status' => $progressData['status'],
            ]);
        }

        Log::debug("Job progress updated", [
            'job_id' => $jobId,
            'sent' => $sentCount,
            'failed' => $failedCount,
            'percentage' => $percentage
        ]);
    }

    /**
     * Mark job as completed
     *
     * @param string $jobId
     * @param int $sentCount
     * @param int $failedCount
     * @param SmsJobTracking|null $jobTracking
     * @return void
     */
    public function completeJob(
        string $jobId,
        int $sentCount,
        int $failedCount,
        ?SmsJobTracking $jobTracking = null
    ): void {
        $currentProgress = Cache::get($jobId, []);
        $progressData = array_merge($currentProgress, [
            'status' => 'completed',
            'sent' => $sentCount,
            'failed' => $failedCount,
            'percentage' => 100,
            'message' => "Completed! Sent: {$sentCount}, Failed: {$failedCount}",
            'completed_at' => now()->toISOString(),
        ]);

        Cache::put($jobId, $progressData, now()->addSeconds($this->cacheTtl));

        // Update database
        if ($jobTracking) {
            $jobTracking->complete($sentCount, $failedCount);
        }

        Log::info("Job completed", [
            'job_id' => $jobId,
            'sent' => $sentCount,
            'failed' => $failedCount
        ]);
    }

    /**
     * Mark job as failed
     *
     * @param string $jobId
     * @param string $errorMessage
     * @param SmsJobTracking|null $jobTracking
     * @return void
     */
    public function failJob(string $jobId, string $errorMessage, ?SmsJobTracking $jobTracking = null): void
    {
        $currentProgress = Cache::get($jobId, []);
        $progressData = array_merge($currentProgress, [
            'status' => 'failed',
            'message' => $errorMessage,
            'failed_at' => now()->toISOString(),
        ]);

        Cache::put($jobId, $progressData, now()->addSeconds($this->cacheTtl));

        // Update database
        if ($jobTracking) {
            $jobTracking->fail($errorMessage);
        }

        Log::error("Job failed", [
            'job_id' => $jobId,
            'error' => $errorMessage
        ]);
    }

    /**
     * Cancel a job
     *
     * @param string $jobId
     * @param SmsJobTracking|null $jobTracking
     * @return void
     */
    public function cancelJob(string $jobId, ?SmsJobTracking $jobTracking = null): void
    {
        $currentProgress = Cache::get($jobId, []);
        $progressData = array_merge($currentProgress, [
            'status' => 'cancelled',
            'message' => 'Job cancelled by user',
            'cancelled_at' => now()->toISOString(),
        ]);

        Cache::put($jobId, $progressData, now()->addSeconds($this->cacheTtl));

        // Update database
        if ($jobTracking) {
            $jobTracking->cancel();
        }

        Log::info("Job cancelled", ['job_id' => $jobId]);
    }

    /**
     * Add an error to the job
     *
     * @param string $jobId
     * @param string $error
     * @param int $maxErrors Maximum errors to keep
     * @return void
     */
    public function addError(string $jobId, string $error, int $maxErrors = 10): void
    {
        $currentProgress = Cache::get($jobId, []);
        $errors = $currentProgress['errors'] ?? [];
        $errors[] = $error;

        // Keep only last N errors
        $errors = array_slice($errors, -$maxErrors);

        $currentProgress['errors'] = $errors;
        Cache::put($jobId, $currentProgress, now()->addSeconds($this->cacheTtl));
    }

    /**
     * Get job progress
     *
     * @param string $jobId
     * @return array|null
     */
    public function getProgress(string $jobId): ?array
    {
        return Cache::get($jobId);
    }

    /**
     * Get job progress from database
     *
     * @param string $jobId
     * @return SmsJobTracking|null
     */
    public function getProgressFromDatabase(string $jobId): ?SmsJobTracking
    {
        return SmsJobTracking::where('job_id', $jobId)->first();
    }

    /**
     * Check if job is cancelled
     *
     * @param string $jobId
     * @return bool
     */
    public function isCancelled(string $jobId): bool
    {
        $progress = Cache::get($jobId);
        return $progress && $progress['status'] === 'cancelled';
    }

    /**
     * Check if job exists
     *
     * @param string $jobId
     * @return bool
     */
    public function jobExists(string $jobId): bool
    {
        return Cache::has($jobId);
    }

    /**
     * Delete job progress (cleanup)
     *
     * @param string $jobId
     * @return void
     */
    public function deleteProgress(string $jobId): void
    {
        Cache::forget($jobId);
        Log::debug("Job progress deleted", ['job_id' => $jobId]);
    }

    /**
     * Get all active jobs (if using Redis/Memcached with key scanning)
     * Note: This won't work with file cache driver
     *
     * @return array
     */
    public function getActiveJobs(): array
    {
        // This is a simplified version
        // For production, you'd query the SmsJobTracking table
        return SmsJobTracking::whereIn('status', ['pending', 'processing'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->toArray();
    }
}
