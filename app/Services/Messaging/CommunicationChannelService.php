<?php

namespace App\Services\Messaging;

use App\Services\SettingsService;
use InvalidArgumentException;

class CommunicationChannelService
{
    public const CHANNEL_SMS = 'sms';
    public const CHANNEL_WHATSAPP = 'whatsapp';

    public function __construct(
        protected SettingsService $settingsService
    ) {
    }

    public function smsEnabled(): bool
    {
        return (bool) $this->settingsService->get('features.sms_enabled', false);
    }

    public function whatsappEnabled(): bool
    {
        return (bool) $this->settingsService->get('features.whatsapp_enabled', false);
    }

    public function isEnabled(string $channel): bool
    {
        return match ($this->normalizeChannel($channel)) {
            self::CHANNEL_SMS => $this->smsEnabled(),
            self::CHANNEL_WHATSAPP => $this->whatsappEnabled(),
        };
    }

    public function anyEnabled(array $channels = [self::CHANNEL_SMS, self::CHANNEL_WHATSAPP]): bool
    {
        foreach ($channels as $channel) {
            if ($this->isEnabled($channel)) {
                return true;
            }
        }

        return false;
    }

    public function enabledChannels(): array
    {
        $channels = [];

        if ($this->smsEnabled()) {
            $channels[] = self::CHANNEL_SMS;
        }

        if ($this->whatsappEnabled()) {
            $channels[] = self::CHANNEL_WHATSAPP;
        }

        return $channels;
    }

    public function visibleDirectChannels(): array
    {
        return $this->enabledChannels();
    }

    public function toArray(): array
    {
        return [
            'sms_enabled' => $this->smsEnabled(),
            'whatsapp_enabled' => $this->whatsappEnabled(),
            'enabled_channels' => $this->enabledChannels(),
        ];
    }

    public function normalizeChannel(string $channel): string
    {
        $normalized = strtolower(trim($channel));

        if (!in_array($normalized, [self::CHANNEL_SMS, self::CHANNEL_WHATSAPP], true)) {
            throw new InvalidArgumentException("Unsupported communication channel [{$channel}]");
        }

        return $normalized;
    }
}
