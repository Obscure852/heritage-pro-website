<?php

namespace App\Jobs;

use App\Helpers\LinkSMSHelper;
use App\Helpers\TermHelper;
use App\Models\SmsJobTracking;
use App\Services\Messaging\SmsBalanceService;
use App\Services\Messaging\SmsPlaceholderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendBulkWithLinkSMS implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The queue this job should be dispatched to.
     * Uses a dedicated 'sms' queue to avoid competing with other jobs like emails.
     *
     * @var string
     */
    public $queue = 'sms';

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries;

    /**
     * The maximum number of seconds the job can run.
     *
     * @var int
     */
    public $timeout;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff;

    protected $messages;
    protected $jobId;
    protected $reservedCost;

    public function __construct(array $messages, $jobId = null, float $reservedCost = 0)
    {
        $this->messages = $messages;
        $this->jobId = $jobId;
        $this->reservedCost = $reservedCost;

        // Set retry configuration from database settings
        $this->tries = settings('queue.job_retries', 3);
        $this->timeout = settings('queue.job_timeout', 300);
        $this->backoff = settings('queue.retry_delay', 60);
    }

    public function handle()
    {
        $link = new LinkSMSHelper();
        $currentTerm = TermHelper::getCurrentTerm();
        $placeholderService = app(SmsPlaceholderService::class);

        $totalMessages = count($this->messages);
        $sentCount = 0;
        $failedCount = 0;

        foreach ($this->messages as $index => $messageData) {
            if ($this->jobId) {
                $progress = Cache::get($this->jobId);
                if ($progress && $progress['status'] === 'cancelled') {
                    Log::info("Job {$this->jobId} was cancelled. Stopping execution.");
                    break;
                }
            }

            $messageData['term_id'] = $currentTerm->id;

            // Replace placeholders with recipient-specific data
            $messageBody = $messageData['messageBody'];
            if (!empty($messageData['recipientContext'])) {
                $messageBody = $placeholderService->replacePlaceholders(
                    $messageBody,
                    $messageData['recipientContext']
                );
            }

            try {
                $link->sendMessage(
                    $messageBody,
                    $messageData['formattedPhoneNumber'],
                    $messageData['senderId'],
                    $messageData['senderType'],
                    $messageData['type'],
                    $messageData['num_recipients']
                );
                $sentCount++;
            } catch (\Exception $e) {
                $failedCount++;
                Log::error('Failed to send SMS in job: ' . $e->getMessage());
            }

            if ($this->jobId && ($index % 5 === 0 || $index === $totalMessages - 1)) {
                $this->updateProgress($sentCount, $failedCount, $totalMessages);
            }
        }

        if ($this->jobId) {
            $this->updateProgress($sentCount, $failedCount, $totalMessages, true);
        }

        // Release the balance reservation on completion
        // (Actual deductions already happened per-message via SMSHelper)
        if ($this->reservedCost > 0) {
            try {
                app(SmsBalanceService::class)->releaseReservation($this->reservedCost);
                Log::info("Balance reservation released for completed job {$this->jobId}", [
                    'amount' => $this->reservedCost
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to release balance reservation for job {$this->jobId}", [
                    'amount' => $this->reservedCost,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
    
    private function updateProgress($sent, $failed, $total, $completed = false){
        $progress = Cache::get($this->jobId);
        if (!$progress) {
            return;
        }
        
        $currentSent = $progress['sent'] + $sent;
        $currentFailed = $progress['failed'] + $failed;
        $percentage = $total > 0 ? round((($currentSent + $currentFailed) / $progress['total']) * 100) : 0;
        
        $progressData = [
            'status' => $completed ? 'completed' : 'processing',
            'total' => $progress['total'],
            'sent' => $currentSent,
            'failed' => $currentFailed,
            'percentage' => $percentage,
            'message' => $completed 
                ? "SMS sending completed. Sent: {$currentSent}, Failed: {$currentFailed}" 
                : "Processing... {$currentSent}/{$progress['total']} sent",
            'updated_at' => now()->toISOString()
        ];
        
        if ($completed) {
            $progressData['completed_at'] = now()->toISOString();
        }
        
        Cache::put($this->jobId, $progressData, now()->addHours(2));
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        Log::error('Bulk SMS job failed permanently', [
            'job_id' => $this->jobId,
            'message_count' => count($this->messages),
            'attempts' => $this->attempts(),
            'error' => $exception->getMessage()
        ]);

        // Release the reserved balance since the job failed
        if ($this->reservedCost > 0) {
            try {
                app(SmsBalanceService::class)->releaseReservation($this->reservedCost);
                Log::info("Released reserved balance for failed job {$this->jobId}", [
                    'amount' => $this->reservedCost
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to release reserved balance for job {$this->jobId}", [
                    'amount' => $this->reservedCost,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Update job tracking if jobId exists
        if ($this->jobId) {
            try {
                $jobTracking = SmsJobTracking::where('job_id', $this->jobId)->first();
                if ($jobTracking) {
                    $jobTracking->markAsFailed('Job failed after ' . $this->attempts() . ' attempts: ' . $exception->getMessage());
                }

                // Update cache status
                $progress = Cache::get($this->jobId);
                if ($progress) {
                    $progress['status'] = 'failed';
                    $progress['message'] = 'Job failed: ' . $exception->getMessage();
                    $progress['failed_at'] = now()->toISOString();
                    Cache::put($this->jobId, $progress, now()->addHours(2));
                }
            } catch (\Exception $e) {
                Log::error('Failed to update job tracking on failure', ['error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * @return array
     */
    public function backoff()
    {
        // Exponential backoff: 60s, 120s, 240s
        return [60, 120, 240];
    }
}