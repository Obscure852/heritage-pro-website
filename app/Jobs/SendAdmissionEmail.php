<?php

namespace App\Jobs;

use App\Mail\AdmissionCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Throwable;

class SendAdmissionEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public $timeout = 120;

    protected $admission;
    protected $parentEmailAddress;

    public function __construct($admission, $parentEmailAddress)
    {
        $this->admission = $admission;
        $this->parentEmailAddress = $parentEmailAddress;
    }

    public function handle()
    {
        try {
            Mail::to($this->parentEmailAddress)->send(new AdmissionCompleted($this->admission));

            Log::info('Admission email sent successfully', [
                'admission_id' => $this->admission->id ?? 'N/A',
                'recipient' => $this->parentEmailAddress
            ]);
        } catch (\Swift_TransportException $e) {
            // Retryable network/SMTP errors
            Log::warning('Admission email transport error (attempt ' . $this->attempts() . '/' . $this->tries . ')', [
                'recipient' => $this->parentEmailAddress,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Calculate the number of seconds to wait before retrying.
     */
    public function backoff(): array
    {
        return [30, 60, 120];
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('Admission email failed permanently', [
            'admission_id' => $this->admission->id ?? 'N/A',
            'recipient' => $this->parentEmailAddress,
            'attempts' => $this->attempts(),
            'error' => $exception->getMessage()
        ]);
    }
}
