<?php

namespace App\Services;

use App\Helpers\SMSHelper;
use App\Jobs\SendBulkWithLinkSMS;
use App\Models\SmsJobTracking;
use App\Models\AccountBalance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * SmsService
 *
 * Centralizes SMS business logic including cost calculation,
 * balance checking, job creation, and sending logic.
 */
class SmsService
{
    protected $recipientService;

    public function __construct(RecipientService $recipientService)
    {
        $this->recipientService = $recipientService;
    }

    /**
     * Calculate SMS cost for given message and recipients
     *
     * @param string $message
     * @param int $recipientCount
     * @return array [sms_count, total_units, cost_per_unit, total_cost]
     */
    public function calculateCost(string $message, int $recipientCount): array
    {
        $charactersPerUnit = (int) settings('sms.characters_per_unit', 160);
        $smsCount = (int) ceil(strlen($message) / $charactersPerUnit);
        $totalUnits = $smsCount * $recipientCount;
        $costPerUnit = SMSHelper::getPackageRate();
        $totalCost = $totalUnits * $costPerUnit;

        return [
            'sms_count' => $smsCount,
            'total_units' => $totalUnits,
            'cost_per_unit' => $costPerUnit,
            'total_cost' => $totalCost,
        ];
    }

    /**
     * Check if account has sufficient balance
     *
     * @param float $requiredAmount
     * @return bool
     * @throws \Exception
     */
    public function checkBalance(float $requiredAmount): bool
    {
        $balance = SMSHelper::getAccountBalance();

        if ($balance < $requiredAmount) {
            throw new \Exception(
                "Insufficient balance. Required: {$requiredAmount} BWP, Available: {$balance} BWP"
            );
        }

        return true;
    }

    /**
     * Deduct amount from account balance
     *
     * @param float $amount
     * @param string $description
     * @return bool
     */
    public function deductBalance(float $amount, string $description = 'SMS charges'): bool
    {
        return DB::transaction(function () use ($amount, $description) {
            $balance = AccountBalance::lockForUpdate()->first();

            if (!$balance || $balance->balance < $amount) {
                throw new \Exception('Insufficient balance for SMS sending');
            }

            $balance->balance -= $amount;
            $balance->save();

            Log::info('SMS balance deducted', [
                'amount' => $amount,
                'remaining_balance' => $balance->balance,
                'description' => $description,
            ]);

            return true;
        });
    }

    /**
     * Create SMS job tracking record
     *
     * @param array $data
     * @return SmsJobTracking
     */
    public function createJobTracking(array $data): SmsJobTracking
    {
        return DB::transaction(function () use ($data) {
            return SmsJobTracking::create([
                'term_id' => $data['term_id'],
                'author' => $data['author'],
                'message' => $data['message'],
                'num_recipients' => $data['num_recipients'],
                'sms_count' => $data['sms_count'],
                'price_bwp' => $data['price_bwp'],
                'status' => $data['status'] ?? 'pending',
                'progress' => 0,
                'success_count' => 0,
                'failure_count' => 0,
                'cancelled_at' => null,
            ]);
        });
    }

    /**
     * Determine if SMS should use queue based on recipient count
     *
     * @param int $recipientCount
     * @return bool
     */
    public function shouldUseQueue(int $recipientCount): bool
    {
        $threshold = (int) settings('sms.queue_threshold', 10);
        return $recipientCount > $threshold;
    }

    /**
     * Send bulk SMS (either queued or synchronous based on recipient count)
     *
     * @param array $recipients
     * @param string $message
     * @param int $termId
     * @param int $userId
     * @return array [job_id, status, message]
     */
    public function sendBulkSms(array $recipients, string $message, int $termId, int $userId): array
    {
        $recipientCount = count($recipients);

        if ($recipientCount === 0) {
            throw new \Exception('No recipients found');
        }

        // Calculate cost
        $costData = $this->calculateCost($message, $recipientCount);

        // Check balance
        $this->checkBalance($costData['total_cost']);

        // Create job tracking
        $jobTracking = $this->createJobTracking([
            'term_id' => $termId,
            'author' => $userId,
            'message' => $message,
            'num_recipients' => $recipientCount,
            'sms_count' => $costData['sms_count'],
            'price_bwp' => $costData['total_cost'],
            'status' => $this->shouldUseQueue($recipientCount) ? 'queued' : 'processing',
        ]);

        // Determine sending strategy
        if ($this->shouldUseQueue($recipientCount)) {
            // Use queue for large batches
            SendBulkWithLinkSMS::dispatch($message, $recipients, $jobTracking->id, $termId, $userId);

            return [
                'job_id' => $jobTracking->id,
                'status' => 'queued',
                'message' => "SMS job queued successfully. {$recipientCount} recipients.",
            ];
        } else {
            // Send synchronously for small batches
            $this->sendSynchronously($recipients, $message, $jobTracking);

            return [
                'job_id' => $jobTracking->id,
                'status' => 'completed',
                'message' => "SMS sent successfully to {$recipientCount} recipients.",
            ];
        }
    }

    /**
     * Send SMS synchronously (for small batches)
     *
     * @param array $recipients
     * @param string $message
     * @param SmsJobTracking $jobTracking
     * @return void
     */
    protected function sendSynchronously(array $recipients, string $message, SmsJobTracking $jobTracking): void
    {
        $successCount = 0;
        $failureCount = 0;
        $totalRecipients = count($recipients);

        foreach ($recipients as $index => $recipient) {
            try {
                // Send SMS using helper
                $result = SMSHelper::sendSMS($recipient['phone'], $message);

                if ($result) {
                    $successCount++;
                } else {
                    $failureCount++;
                }
            } catch (\Exception $e) {
                Log::error('SMS sending failed', [
                    'recipient' => $recipient,
                    'error' => $e->getMessage(),
                ]);
                $failureCount++;
            }

            // Update progress every 5 messages
            if (($index + 1) % 5 === 0 || ($index + 1) === $totalRecipients) {
                $progress = (($index + 1) / $totalRecipients) * 100;
                $jobTracking->update([
                    'progress' => $progress,
                    'success_count' => $successCount,
                    'failure_count' => $failureCount,
                ]);
            }
        }

        // Mark as completed
        $jobTracking->update([
            'status' => 'completed',
            'progress' => 100,
            'success_count' => $successCount,
            'failure_count' => $failureCount,
            'completed_at' => now(),
        ]);
    }

    /**
     * Cancel a running SMS job
     *
     * @param string $jobId
     * @return bool
     */
    public function cancelJob(string $jobId): bool
    {
        $job = SmsJobTracking::find($jobId);

        if (!$job) {
            throw new \Exception('Job not found');
        }

        if (in_array($job->status, ['completed', 'failed', 'cancelled'])) {
            throw new \Exception("Job cannot be cancelled (current status: {$job->status})");
        }

        $job->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return true;
    }

    /**
     * Get job progress
     *
     * @param string $jobId
     * @return array
     */
    public function getJobProgress(string $jobId): array
    {
        $job = SmsJobTracking::find($jobId);

        if (!$job) {
            throw new \Exception('Job not found');
        }

        return [
            'job_id' => $job->id,
            'status' => $job->status,
            'progress' => $job->progress,
            'success_count' => $job->success_count,
            'failure_count' => $job->failure_count,
            'total_recipients' => $job->num_recipients,
            'message' => $job->message,
            'cost' => $job->price_bwp,
            'created_at' => $job->created_at->toDateTimeString(),
            'completed_at' => $job->completed_at?->toDateTimeString(),
            'cancelled_at' => $job->cancelled_at?->toDateTimeString(),
        ];
    }

    /**
     * Prepare recipients for SMS sending
     *
     * @param array $filters
     * @return array
     */
    public function prepareRecipients(array $filters): array
    {
        return $this->recipientService->getSmsRecipientsFormatted($filters);
    }
}
