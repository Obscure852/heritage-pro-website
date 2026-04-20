<?php

namespace App\Services\Messaging;

use App\Http\Controllers\NotificationController;
use App\Models\WhatsappTemplate;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TwilioWhatsAppService
{
    public function __construct(
        protected SettingsService $settingsService
    ) {
    }

    public function isConfigured(): bool
    {
        return filled($this->accountSid())
            && filled($this->authToken())
            && filled($this->sender());
    }

    public function accountSid(): ?string
    {
        return $this->settingsService->get('whatsapp.account_sid');
    }

    public function authToken(): ?string
    {
        return $this->settingsService->get('whatsapp.auth_token');
    }

    public function sender(): ?string
    {
        $sender = $this->settingsService->get('whatsapp.sender');

        return $sender ? $this->normalizeWhatsappAddress($sender) : null;
    }

    public function sendTemplateMessage(string $phoneNumber, WhatsappTemplate $template, array $variables = []): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('WhatsApp is not configured. Please complete Twilio settings in Communications Setup.');
        }

        $response = Http::asForm()
            ->withBasicAuth($this->accountSid(), $this->authToken())
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid()}/Messages.json", [
                'To' => $this->normalizeWhatsappAddress(NotificationController::verifyAndFormatPhoneNumber($phoneNumber)),
                'From' => $this->sender(),
                'ContentSid' => $template->external_id,
                'ContentVariables' => json_encode($this->normalizeTemplateVariables($variables)),
                'StatusCallback' => route('api.webhooks.whatsapp.status'),
            ]);

        if (!$response->successful()) {
            $message = $response->json('message') ?: $response->body();
            throw new RuntimeException("Twilio WhatsApp send failed: {$message}");
        }

        return $response->json();
    }

    public function syncTemplates(?int $limit = null): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('WhatsApp is not configured. Please complete Twilio settings before syncing templates.');
        }

        $limit = $limit ?: (int) $this->settingsService->get('whatsapp.template_sync_limit', 100);

        $response = Http::acceptJson()
            ->withBasicAuth($this->accountSid(), $this->authToken())
            ->get('https://content.twilio.com/v1/Content', [
                'PageSize' => $limit,
            ]);

        if (!$response->successful()) {
            throw new RuntimeException('Failed to sync WhatsApp templates from Twilio: ' . $response->body());
        }

        $payload = $response->json();
        $items = $payload['contents'] ?? $payload['content'] ?? $payload['templates'] ?? [];
        $synced = 0;

        foreach ($items as $item) {
            $externalId = $item['sid'] ?? $item['id'] ?? null;
            if (!$externalId) {
                continue;
            }

            $types = $item['types'] ?? [];
            $bodyPreview = $this->extractBodyPreview($types);

            WhatsappTemplate::updateOrCreate(
                ['external_id' => $externalId],
                [
                    'provider' => 'twilio',
                    'name' => $item['friendly_name'] ?? $item['name'] ?? $externalId,
                    'language' => $item['language'] ?? $this->settingsService->get('whatsapp.default_language', 'en'),
                    'category' => strtolower($item['category'] ?? 'utility'),
                    'status' => strtolower($item['status'] ?? ($item['approval_requests'][0]['status'] ?? 'draft')),
                    'body_preview' => $bodyPreview,
                    'variables' => $item['variables'] ?? [],
                    'content' => $item,
                    'last_synced_at' => now(),
                ]
            );

            $synced++;
        }

        return [
            'count' => $synced,
            'source_count' => count($items),
        ];
    }

    public function validateWebhookSignature(Request $request): bool
    {
        $signature = $request->header('X-Twilio-Signature');
        if (!$signature || !$this->authToken()) {
            return false;
        }

        $url = $request->fullUrl();
        $params = $request->all();
        ksort($params);

        $signedPayload = $url;
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $value = implode('', $value);
            }
            $signedPayload .= $key . $value;
        }

        $expected = base64_encode(hash_hmac('sha1', $signedPayload, $this->authToken(), true));

        return hash_equals($expected, $signature);
    }

    public function normalizeWhatsappAddress(string $address): string
    {
        $trimmed = trim($address);

        if (str_starts_with($trimmed, 'whatsapp:')) {
            return $trimmed;
        }

        return 'whatsapp:' . $trimmed;
    }

    protected function normalizeTemplateVariables(array $variables): array
    {
        $normalized = [];

        foreach ($variables as $key => $value) {
            $normalized[(string) $key] = (string) $value;
        }

        return $normalized;
    }

    protected function extractBodyPreview(array $types): ?string
    {
        foreach (['twilio/text', 'whatsapp/text', 'twilio/card', 'whatsapp/card'] as $key) {
            $candidate = $types[$key]['body'] ?? $types[$key]['text'] ?? null;
            if ($candidate) {
                return $candidate;
            }
        }

        return null;
    }
}
