<?php

namespace App\Jobs;

use App\Mail\BulkEmail;
use App\Models\Email;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SendBulkEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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

    protected $recipient;
    protected $details;
    protected $attachmentPath;
    protected $attachmentName;
    protected $attachmentMime;
    protected $termId;
    protected $senderId;

    public function __construct($recipient, $details, $attachmentPath, $attachmentName, $attachmentMime, $termId = null, $senderId = null)
    {
        $this->recipient = $recipient;
        $this->details = $details;
        $this->attachmentPath = $attachmentPath;
        $this->attachmentName = $attachmentName;
        $this->attachmentMime = $attachmentMime;
        $this->termId = $termId;
        $this->senderId = $senderId;

        // Set retry configuration from database settings
        $this->tries = settings('queue.job_retries', 3);
        $this->timeout = settings('queue.job_timeout', 300);
        $this->backoff = settings('queue.retry_delay', 60);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $email = new BulkEmail($this->details, $this->attachmentPath, $this->attachmentName, $this->attachmentMime);
            Mail::to($this->recipient->email)->send($email);

            Log::info('Bulk email sent successfully', [
                'recipient' => $this->recipient->email,
                'subject' => $this->details['subject'] ?? 'N/A'
            ]);

        } catch (\Swift_TransportException $e) {
            // Retryable: Network/SMTP issues
            Log::warning('Retryable email error (attempt ' . $this->attempts() . '/' . $this->tries . ')', [
                'recipient' => $this->recipient->email,
                'error' => $e->getMessage()
            ]);

            // Release back to queue if we haven't exceeded max attempts
            if ($this->attempts() < $this->tries) {
                $this->release($this->backoff);
                return;
            }

            // Max attempts reached, let it fail
            throw $e;

        } catch (\Exception $e) {
            // Potentially permanent error, but still retry in case it's temporary
            Log::error('Email send error (attempt ' . $this->attempts() . '/' . $this->tries . ')', [
                'recipient' => $this->recipient->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e; // Will be retried automatically
        }
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        Log::error('Bulk email job failed permanently', [
            'recipient' => $this->recipient->email,
            'subject' => $this->details['subject'] ?? 'N/A',
            'attempts' => $this->attempts(),
            'error' => $exception->getMessage()
        ]);

        // Log as failed email in database
        try {
            Email::create([
                'term_id' => $this->termId,
                'sender_id' => $this->senderId,
                'receiver_type' => $this->recipient instanceof \App\Models\Sponsor ? 'sponsor' : 'user',
                'receiver_id' => $this->recipient->id,
                'subject' => $this->details['subject'] ?? 'N/A',
                'body' => $this->details['body'] ?? '',
                'attachment_path' => $this->attachmentPath,
                'status' => 'failed',
                'num_of_recipients' => 1,
                'type' => 'Bulk',
                'error_message' => substr($exception->getMessage(), 0, 500),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log failed email', ['error' => $e->getMessage()]);
        }

        // Cleanup attachment if all attempts failed (for shared bulk attachments, be careful)
        // Only delete if this is the last recipient job
        // Note: For true safety, we'd need to track how many jobs are using this attachment
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