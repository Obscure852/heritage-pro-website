<?php

namespace App\Services\Messaging;

use App\Models\SmsJobTracking;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * SmsJobService
 *
 * Handles SMS job operations with proper authorization checks.
 * Users can only view/cancel their own jobs.
 */
class SmsJobService
{
    /**
     * Get job progress for a specific user
     *
     * @param string $jobId The job ID to look up
     * @param User $user The user making the request
     * @return array|null Progress data or null if not found/unauthorized
     */
    public function getJobProgress(string $jobId, User $user): ?array
    {
        // First check cache for active job
        $cacheProgress = Cache::get($jobId);

        if ($cacheProgress) {
            // Verify user owns this job
            if (isset($cacheProgress['user_id']) && $cacheProgress['user_id'] !== $user->id) {
                Log::warning('SmsJobService: Unauthorized job progress access attempt', [
                    'job_id' => $jobId,
                    'requesting_user_id' => $user->id,
                    'job_owner_id' => $cacheProgress['user_id'],
                ]);
                return null;
            }

            return [
                'source' => 'cache',
                'job_id' => $jobId,
                'status' => $cacheProgress['status'] ?? 'unknown',
                'percentage' => $cacheProgress['percentage'] ?? 0,
                'sent' => $cacheProgress['sent'] ?? 0,
                'failed' => $cacheProgress['failed'] ?? 0,
                'total' => $cacheProgress['total'] ?? 0,
                'message' => $cacheProgress['message'] ?? '',
                'started_at' => $cacheProgress['started_at'] ?? null,
            ];
        }

        // Fall back to database
        $jobTracking = SmsJobTracking::where('job_id', $jobId)->first();

        if (!$jobTracking) {
            return null;
        }

        // Authorization check
        if ($jobTracking->user_id !== $user->id) {
            Log::warning('SmsJobService: Unauthorized job progress access attempt', [
                'job_id' => $jobId,
                'requesting_user_id' => $user->id,
                'job_owner_id' => $jobTracking->user_id,
            ]);
            return null;
        }

        return [
            'source' => 'database',
            'job_id' => $jobTracking->job_id,
            'status' => $jobTracking->status,
            'percentage' => $jobTracking->percentage ?? 0,
            'sent' => $jobTracking->sent_count ?? 0,
            'failed' => $jobTracking->failed_count ?? 0,
            'total' => $jobTracking->total_recipients ?? 0,
            'message' => $jobTracking->status_message ?? '',
            'started_at' => $jobTracking->started_at?->toISOString(),
            'completed_at' => $jobTracking->completed_at?->toISOString(),
            'total_cost' => $jobTracking->total_cost,
            'sms_units_used' => $jobTracking->sms_units_used,
        ];
    }

    /**
     * Cancel a job for a specific user
     *
     * @param string $jobId The job ID to cancel
     * @param User $user The user making the request
     * @return bool True if cancelled successfully, false if not found/unauthorized
     */
    public function cancelJob(string $jobId, User $user): bool
    {
        // Update cache if exists
        $cacheProgress = Cache::get($jobId);

        if ($cacheProgress) {
            // Verify ownership from cache
            if (isset($cacheProgress['user_id']) && $cacheProgress['user_id'] !== $user->id) {
                Log::warning('SmsJobService: Unauthorized job cancel attempt', [
                    'job_id' => $jobId,
                    'requesting_user_id' => $user->id,
                ]);
                return false;
            }

            $cacheProgress['status'] = 'cancelled';
            Cache::put($jobId, $cacheProgress, now()->addHours(2));
        }

        // Update database
        $jobTracking = SmsJobTracking::where('job_id', $jobId)->first();

        if (!$jobTracking) {
            // If only in cache, still consider it a success
            return $cacheProgress !== null;
        }

        // Authorization check
        if ($jobTracking->user_id !== $user->id) {
            Log::warning('SmsJobService: Unauthorized job cancel attempt', [
                'job_id' => $jobId,
                'requesting_user_id' => $user->id,
                'job_owner_id' => $jobTracking->user_id,
            ]);
            return false;
        }

        // Check if job can be cancelled
        if (in_array($jobTracking->status, ['completed', 'failed', 'cancelled'])) {
            Log::info('SmsJobService: Job already in terminal state', [
                'job_id' => $jobId,
                'status' => $jobTracking->status,
            ]);
            return false;
        }

        $jobTracking->cancel();

        Log::info('SmsJobService: Job cancelled', [
            'job_id' => $jobId,
            'user_id' => $user->id,
        ]);

        return true;
    }

    /**
     * Get job history for a user
     *
     * @param User $user The user to get history for
     * @param int $limit Number of jobs to retrieve
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getJobHistory(User $user, int $limit = 20)
    {
        return SmsJobTracking::forUser($user->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Check if user owns a job
     *
     * @param string $jobId The job ID
     * @param User $user The user to check
     * @return bool True if user owns the job
     */
    public function userOwnsJob(string $jobId, User $user): bool
    {
        $jobTracking = SmsJobTracking::where('job_id', $jobId)->first();

        if (!$jobTracking) {
            return false;
        }

        return $jobTracking->user_id === $user->id;
    }

    /**
     * Get active jobs for a user
     *
     * @param User $user The user to get active jobs for
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveJobs(User $user)
    {
        return SmsJobTracking::forUser($user->id)
            ->active()
            ->get();
    }
}
