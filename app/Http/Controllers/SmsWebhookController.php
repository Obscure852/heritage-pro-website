<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\SmsDeliveryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SmsWebhookController extends Controller
{
    /**
     * Handle incoming delivery status webhook from Link SMS
     *
     * Expected payload structure (adjust based on actual Link SMS API):
     * {
     *     "message_id": "external_message_id",
     *     "phone_number": "26771234567",
     *     "status": "delivered|failed|rejected|expired",
     *     "status_code": "0",
     *     "status_message": "Message delivered successfully",
     *     "timestamp": "2026-01-13T10:00:00Z"
     * }
     */
    public function handleDeliveryStatus(Request $request)
    {
        Log::info('SMS Webhook received', [
            'payload' => $request->all(),
            'headers' => $request->headers->all(),
        ]);

        // Verify webhook signature if provided by Link SMS
        if (!$this->verifyWebhookSignature($request)) {
            Log::warning('SMS Webhook signature verification failed');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        try {
            $payload = $request->all();

            // Extract data from payload (adjust field names based on actual Link SMS API)
            $externalId = $payload['message_id'] ?? $payload['msgid'] ?? $payload['id'] ?? null;
            $phoneNumber = $payload['phone_number'] ?? $payload['msisdn'] ?? $payload['to'] ?? null;
            $status = strtolower($payload['status'] ?? $payload['dlr_status'] ?? 'unknown');
            $statusCode = $payload['status_code'] ?? $payload['error_code'] ?? null;
            $statusMessage = $payload['status_message'] ?? $payload['error_message'] ?? $payload['description'] ?? null;

            if (!$externalId) {
                Log::warning('SMS Webhook: Missing message ID', ['payload' => $payload]);
                return response()->json(['error' => 'Missing message ID'], 400);
            }

            // Map status to our internal status
            $mappedStatus = $this->mapStatus($status);

            // Update delivery log
            $this->updateDeliveryLog($externalId, $mappedStatus, $statusCode, $statusMessage, $payload);

            // Update message record if we have a phone number
            if ($phoneNumber) {
                $this->updateMessageRecord($externalId, $phoneNumber, $mappedStatus);
            }

            Log::info('SMS Webhook processed successfully', [
                'external_id' => $externalId,
                'status' => $mappedStatus,
            ]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('SMS Webhook processing failed', [
                'error' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    /**
     * Verify webhook signature from Link SMS
     * Adjust this method based on Link SMS's actual signature verification method
     */
    protected function verifyWebhookSignature(Request $request): bool
    {
        // Get webhook secret from settings
        $webhookSecret = settings('api.link_webhook_secret', env('LINK_WEBHOOK_SECRET'));

        // If no secret configured, reject webhooks (secure by default)
        if (empty($webhookSecret)) {
            Log::warning('SMS Webhook rejected: No webhook secret configured. Set LINK_WEBHOOK_SECRET in .env');
            return false;
        }

        // Get signature from header (adjust header name based on Link SMS docs)
        $signature = $request->header('X-Link-Signature') ?? $request->header('X-Webhook-Signature');

        if (!$signature) {
            return false;
        }

        // Verify signature (adjust algorithm based on Link SMS docs)
        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Map Link SMS status to our internal status
     */
    protected function mapStatus(string $status): string
    {
        $statusMap = [
            'delivered' => SmsDeliveryLog::STATUS_DELIVERED,
            'sent' => SmsDeliveryLog::STATUS_SENT,
            'failed' => SmsDeliveryLog::STATUS_FAILED,
            'rejected' => SmsDeliveryLog::STATUS_REJECTED,
            'expired' => SmsDeliveryLog::STATUS_EXPIRED,
            'undelivered' => SmsDeliveryLog::STATUS_FAILED,
            'undeliverable' => SmsDeliveryLog::STATUS_FAILED,
            'accepted' => SmsDeliveryLog::STATUS_SENT,
            'queued' => SmsDeliveryLog::STATUS_PENDING,
        ];

        return $statusMap[$status] ?? SmsDeliveryLog::STATUS_PENDING;
    }

    /**
     * Update or create delivery log entry
     */
    protected function updateDeliveryLog(string $externalId, string $status, ?string $statusCode, ?string $statusMessage, array $rawResponse): void
    {
        $log = SmsDeliveryLog::where('external_id', $externalId)->first();

        if ($log) {
            $updateData = [
                'status' => $status,
                'status_code' => $statusCode,
                'status_message' => $statusMessage,
                'raw_response' => $rawResponse,
            ];

            if ($status === SmsDeliveryLog::STATUS_DELIVERED) {
                $updateData['delivered_at'] = now();
            } elseif (in_array($status, [SmsDeliveryLog::STATUS_FAILED, SmsDeliveryLog::STATUS_REJECTED, SmsDeliveryLog::STATUS_EXPIRED])) {
                $updateData['failed_at'] = now();
            }

            $log->update($updateData);
        } else {
            // Create new log entry if not found (shouldn't normally happen)
            SmsDeliveryLog::create([
                'message_id' => 'webhook_' . $externalId,
                'external_id' => $externalId,
                'phone_number' => 'unknown',
                'status' => $status,
                'status_code' => $statusCode,
                'status_message' => $statusMessage,
                'provider' => 'link_sms',
                'raw_response' => $rawResponse,
                'delivered_at' => $status === SmsDeliveryLog::STATUS_DELIVERED ? now() : null,
                'failed_at' => in_array($status, [SmsDeliveryLog::STATUS_FAILED, SmsDeliveryLog::STATUS_REJECTED]) ? now() : null,
            ]);
        }
    }

    /**
     * Update message record with delivery status
     */
    protected function updateMessageRecord(string $externalId, string $phoneNumber, string $status): void
    {
        // Try to find message by external ID first
        $message = Message::where('external_message_id', $externalId)->first();

        if ($message) {
            $message->update([
                'delivery_status' => $status,
                'delivered_at' => $status === SmsDeliveryLog::STATUS_DELIVERED ? now() : null,
            ]);
        }
    }

    /**
     * Health check endpoint for webhook
     */
    public function healthCheck()
    {
        return response()->json([
            'status' => 'ok',
            'service' => 'sms_webhook',
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Get delivery statistics (for admin dashboard)
     */
    public function getStats(Request $request)
    {
        $days = $request->input('days', 7);
        $startDate = now()->subDays($days);

        $stats = [
            'total' => SmsDeliveryLog::where('created_at', '>=', $startDate)->count(),
            'delivered' => SmsDeliveryLog::delivered()->where('created_at', '>=', $startDate)->count(),
            'failed' => SmsDeliveryLog::failed()->where('created_at', '>=', $startDate)->count(),
            'pending' => SmsDeliveryLog::pending()->where('created_at', '>=', $startDate)->count(),
        ];

        $stats['delivery_rate'] = $stats['total'] > 0
            ? round(($stats['delivered'] / $stats['total']) * 100, 2)
            : 0;

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'period_days' => $days,
        ]);
    }
}
