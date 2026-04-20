<?php

namespace App\Helpers;

use App\Http\Controllers\NotificationController;
use App\Models\CommunicationDeliveryEvent;
use App\Models\SmsDeliveryLog;
use Illuminate\Support\Facades\Log;

class LinkSMSHelper{

    public function sendMessage($message, $phoneNumber, $senderId, $senderType, $type, $num_recipients){
        $formattedPhoneNumber = NotificationController::verifyAndFormatPhoneNumber($phoneNumber);
        Log::info('Formatted phone number:', ['formattedPhoneNumber' => $formattedPhoneNumber]);
        $currentTerm = TermHelper::getCurrentTerm();

        // Read API credentials from database settings
        $apiKey = settings('api.link_api_key', env('LINK_API_KEY'));
        $sender_id = settings('api.link_sender_id', env('LINK_SENDER_ID'));
        $countryCode = '267';

        $user = auth()->user();
        $smsCount = ceil(strlen($message) / 160);

        $url = 'https://apiv2client.linksms.co.bw/v1/api/developers/send-sms';
        $postData = [
            'sender_id' => $sender_id,
            'country_code' => $countryCode,
            'phone_numbers' => [$formattedPhoneNumber],
            'message' => $message,
            'api_key' => $apiKey,
        ];

        // Generate internal message ID for tracking
        $internalMessageId = 'sms_' . uniqid() . '_' . time();

        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $responseBody = json_decode($response, true);

            // Extract external message ID from response if available
            $externalMessageId = $responseBody['message_id'] ?? $responseBody['msgid'] ?? $responseBody['id'] ?? null;

            if ($httpCode == 200) {
                $messageDetails = [
                    'userId' => $user->id,
                    'termId' => $currentTerm->id,
                    'message' => $message,
                    'smsCount' => $smsCount,
                    'type' => $type,
                    'numRecipients' => $num_recipients,
                    'senderType' => $senderType,
                    'senderId' => $senderId,
                    'externalMessageId' => $externalMessageId,
                    'recipientAddress' => $formattedPhoneNumber,
                    'providerResponse' => $responseBody,
                    'provider' => 'link_sms',
                    'deliveryStatus' => 'sent',
                ];

                $messageRecord = SMSHelper::processMessageResponse($response, $httpCode, $messageDetails);

                // Log delivery entry for tracking
                SmsDeliveryLog::logSent(
                    $internalMessageId,
                    $formattedPhoneNumber,
                    $externalMessageId,
                    $responseBody
                );

                Log::info('SMS sent and logged for delivery tracking', [
                    'internal_id' => $internalMessageId,
                    'external_id' => $externalMessageId,
                    'phone' => $formattedPhoneNumber
                ]);

                if ($messageRecord) {
                    CommunicationDeliveryEvent::create([
                        'message_id' => $messageRecord->id,
                        'channel' => 'sms',
                        'provider' => 'link_sms',
                        'external_message_id' => $externalMessageId,
                        'event_type' => 'sent',
                        'status' => 'sent',
                        'payload' => $responseBody,
                        'occurred_at' => now(),
                    ]);
                }
            } else {
                Log::error('HTTP error sending SMS via Link SMS: ' . $httpCode);
                Log::info('Response structure:', [
                    'full_response' => $responseBody,
                    'success_exists' => isset($responseBody['success']),
                    'success_value' => $responseBody['success'] ?? null,
                    'status_code' => $responseBody['status_code'] ?? null
                ]);

                // Log failed attempt
                SmsDeliveryLog::create([
                    'message_id' => $internalMessageId,
                    'external_id' => $externalMessageId,
                    'phone_number' => $formattedPhoneNumber,
                    'status' => SmsDeliveryLog::STATUS_FAILED,
                    'status_code' => (string) $httpCode,
                    'status_message' => $responseBody['message'] ?? $responseBody['error'] ?? 'HTTP error',
                    'provider' => 'link_sms',
                    'raw_response' => $responseBody,
                    'failed_at' => now(),
                ]);
            }

            curl_close($ch);
        } catch (\Exception $e) {
            Log::error('Error sending SMS: ' . $e->getMessage());

            // Log exception as failed
            SmsDeliveryLog::create([
                'message_id' => $internalMessageId,
                'phone_number' => $formattedPhoneNumber,
                'status' => SmsDeliveryLog::STATUS_FAILED,
                'status_message' => $e->getMessage(),
                'provider' => 'link_sms',
                'failed_at' => now(),
            ]);
        }
    }
}
