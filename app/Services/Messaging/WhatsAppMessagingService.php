<?php

namespace App\Services\Messaging;

use App\Helpers\TermHelper;
use App\Models\CommunicationDeliveryEvent;
use App\Models\Message;
use App\Models\RecipientChannelConsent;
use App\Models\User;
use App\Models\WhatsappTemplate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class WhatsAppMessagingService
{
    public function __construct(
        protected CommunicationChannelService $channelService,
        protected RecipientChannelConsentService $consentService,
        protected TwilioWhatsAppService $transport
    ) {
    }

    public function sendDirectMessage(
        Model $recipient,
        User $author,
        WhatsappTemplate $template,
        array $templateVariables = [],
        array $options = []
    ): Message {
        $this->guardChannel();
        $this->guardTemplate($template, $templateVariables);

        if (empty($recipient->phone)) {
            throw new RuntimeException('This recipient does not have a phone number configured.');
        }

        if (!($options['record_consent'] ?? false) && !$this->consentService->hasOptedIn($recipient, CommunicationChannelService::CHANNEL_WHATSAPP)) {
            throw new RuntimeException('WhatsApp consent is required before sending.');
        }

        if ($options['record_consent'] ?? false) {
            $this->consentService->recordStatus(
                $recipient,
                CommunicationChannelService::CHANNEL_WHATSAPP,
                RecipientChannelConsent::STATUS_OPTED_IN,
                $author->id,
                $options['consent_source'] ?? 'staff_admin',
                $options['consent_notes'] ?? null
            );
        }

        $response = $this->transport->sendTemplateMessage($recipient->phone, $template, $templateVariables);

        $message = $this->createMessageRecord(
            $recipient,
            $author,
            $template,
            $templateVariables,
            $response,
            $options['type'] ?? 'direct',
            $options['num_recipients'] ?? 1
        );

        $this->recordDeliveryEvent($message, 'queued', $response);

        return $message;
    }

    public function sendBroadcast(
        Collection $recipients,
        User $author,
        WhatsappTemplate $template,
        array $templateVariables = [],
        array $options = []
    ): array {
        $sent = 0;
        $failed = 0;
        $skipped = 0;
        $errors = [];

        foreach ($recipients as $recipient) {
            if (empty($recipient->phone) || !$this->consentService->hasOptedIn($recipient, CommunicationChannelService::CHANNEL_WHATSAPP)) {
                $skipped++;
                continue;
            }

            try {
                $resolvedVariables = $this->resolveTemplateVariables($recipient, $templateVariables);
                $this->sendDirectMessage($recipient, $author, $template, $resolvedVariables, [
                    'type' => 'bulk',
                    'num_recipients' => $recipients->count(),
                ]);
                $sent++;
            } catch (\Throwable $exception) {
                $failed++;
                $errors[] = $recipient->full_name ?? $recipient->name ?? ('Recipient #' . $recipient->getKey()) . ': ' . $exception->getMessage();
            }
        }

        return [
            'sent' => $sent,
            'failed' => $failed,
            'skipped' => $skipped,
            'errors' => array_slice($errors, 0, 10),
        ];
    }

    protected function guardChannel(): void
    {
        if (!$this->channelService->isEnabled(CommunicationChannelService::CHANNEL_WHATSAPP)) {
            throw new RuntimeException('WhatsApp is disabled in Communications Setup.');
        }
    }

    protected function guardTemplate(WhatsappTemplate $template, array $templateVariables): void
    {
        if (!$template->exists) {
            throw new RuntimeException('Selected WhatsApp template was not found.');
        }

        if (!in_array($template->status, ['approved', 'active'], true)) {
            throw new RuntimeException('Selected WhatsApp template is not approved.');
        }

        $requiredVariables = array_keys($template->variables ?? []);
        foreach ($requiredVariables as $variableKey) {
            if (!array_key_exists($variableKey, $templateVariables) || $templateVariables[$variableKey] === '') {
                throw new RuntimeException("Missing WhatsApp template variable [{$variableKey}].");
            }
        }
    }

    protected function createMessageRecord(
        Model $recipient,
        User $author,
        WhatsappTemplate $template,
        array $templateVariables,
        array $response,
        string $type,
        int $numRecipients
    ): Message {
        $message = Message::create([
            'term_id' => TermHelper::getCurrentTerm()->id,
            'author' => $author->id,
            'user_id' => $recipient instanceof User ? $recipient->id : null,
            'sponsor_id' => $recipient instanceof \App\Models\Sponsor ? $recipient->id : null,
            'body' => $this->renderPreview($template, $templateVariables),
            'channel' => CommunicationChannelService::CHANNEL_WHATSAPP,
            'provider' => 'twilio',
            'recipient_address' => $recipient->phone,
            'template_name' => $template->name,
            'template_external_id' => $template->external_id,
            'metadata' => [
                'template_variables' => $templateVariables,
                'provider_response' => $response,
            ],
            'sms_count' => 0,
            'type' => $type,
            'num_recipients' => $numRecipients,
            'status' => 'sent',
            'external_message_id' => $response['sid'] ?? null,
            'delivery_status' => strtolower($response['status'] ?? 'queued'),
        ]);

        return $message;
    }

    protected function recordDeliveryEvent(Message $message, string $eventType, array $payload): void
    {
        CommunicationDeliveryEvent::create([
            'message_id' => $message->id,
            'channel' => $message->channel,
            'provider' => $message->provider,
            'external_message_id' => $message->external_message_id,
            'event_type' => $eventType,
            'status' => $message->delivery_status,
            'payload' => $payload,
            'occurred_at' => now(),
        ]);
    }

    protected function renderPreview(WhatsappTemplate $template, array $variables): string
    {
        $preview = $template->body_preview ?: ('Template: ' . $template->name);

        foreach ($variables as $key => $value) {
            $preview = str_replace(['{{' . $key . '}}', '{' . $key . '}'], (string) $value, $preview);
        }

        return $preview;
    }

    protected function resolveTemplateVariables(Model $recipient, array $templateVariables): array
    {
        $replacements = [
            '{{first_name}}' => $recipient->firstname ?? $recipient->first_name ?? '',
            '{{last_name}}' => $recipient->lastname ?? $recipient->last_name ?? '',
            '{{full_name}}' => $recipient->full_name ?? $recipient->name ?? '',
            '{{department}}' => $recipient->department ?? '',
            '{{position}}' => $recipient->position ?? '',
        ];

        $resolved = [];
        foreach ($templateVariables as $key => $value) {
            $resolved[$key] = strtr((string) $value, $replacements);
        }

        return $resolved;
    }
}
