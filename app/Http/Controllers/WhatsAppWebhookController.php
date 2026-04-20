<?php

namespace App\Http\Controllers;

use App\Models\CommunicationDeliveryEvent;
use App\Models\CommunicationInboundMessage;
use App\Models\Message;
use App\Services\Messaging\CommunicationChannelService;
use App\Services\Messaging\TwilioWhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    public function __construct(
        protected CommunicationChannelService $channelService,
        protected TwilioWhatsAppService $twilioService
    ) {
    }

    public function handleStatus(Request $request)
    {
        if (!$this->channelService->isEnabled(CommunicationChannelService::CHANNEL_WHATSAPP)) {
            return response()->json(['error' => 'WhatsApp disabled'], 403);
        }

        if (!$this->twilioService->validateWebhookSignature($request)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $externalId = $request->input('MessageSid');
        $status = strtolower($request->input('MessageStatus', 'unknown'));
        $message = Message::where('external_message_id', $externalId)->first();

        if ($message) {
            $message->update([
                'delivery_status' => $status,
                'delivered_at' => $status === 'delivered' ? now() : $message->delivered_at,
                'metadata' => array_merge($message->metadata ?? [], [
                    'status_callback' => $request->all(),
                ]),
            ]);
        }

        CommunicationDeliveryEvent::create([
            'message_id' => $message?->id,
            'channel' => CommunicationChannelService::CHANNEL_WHATSAPP,
            'provider' => 'twilio',
            'external_message_id' => $externalId,
            'event_type' => 'status_callback',
            'status' => $status,
            'payload' => $request->all(),
            'occurred_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function handleInbound(Request $request)
    {
        if (!$this->channelService->isEnabled(CommunicationChannelService::CHANNEL_WHATSAPP)) {
            return response()->json(['error' => 'WhatsApp disabled'], 403);
        }

        if (!$this->twilioService->validateWebhookSignature($request)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        CommunicationInboundMessage::create([
            'channel' => CommunicationChannelService::CHANNEL_WHATSAPP,
            'provider' => 'twilio',
            'external_message_id' => $request->input('MessageSid'),
            'from_address' => $request->input('From'),
            'to_address' => $request->input('To'),
            'body' => $request->input('Body'),
            'payload' => $request->all(),
            'received_at' => now(),
        ]);

        Log::info('Inbound WhatsApp message logged', [
            'from' => $request->input('From'),
            'message_sid' => $request->input('MessageSid'),
        ]);

        return response()->json(['success' => true]);
    }
}
