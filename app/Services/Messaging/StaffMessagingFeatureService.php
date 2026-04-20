<?php

namespace App\Services\Messaging;

use App\Services\SettingsService;

class StaffMessagingFeatureService
{
    public function __construct(
        protected SettingsService $settingsService
    ) {
    }

    public function directMessagesEnabled(): bool
    {
        return (bool) $this->settingsService->get('features.staff_direct_messages_enabled', true);
    }

    public function presenceLauncherEnabled(): bool
    {
        return $this->directMessagesEnabled()
            && (bool) $this->settingsService->get('features.staff_presence_launcher_enabled', true);
    }

    public function onlineWindowMinutes(): int
    {
        $value = (int) $this->settingsService->get('internal_messaging.online_window_minutes', 2);

        return max(1, min($value, 60));
    }

    public function launcherPollSeconds(): int
    {
        $value = (int) $this->settingsService->get('internal_messaging.launcher_poll_seconds', 45);

        return max(15, min($value, 300));
    }

    public function conversationPollSeconds(): int
    {
        $value = (int) $this->settingsService->get('internal_messaging.conversation_poll_seconds', 5);

        return max(3, min($value, 30));
    }

    public function toArray(): array
    {
        return [
            'direct_messages_enabled' => $this->directMessagesEnabled(),
            'presence_launcher_enabled' => $this->presenceLauncherEnabled(),
            'online_window_minutes' => $this->onlineWindowMinutes(),
            'launcher_poll_seconds' => $this->launcherPollSeconds(),
            'conversation_poll_seconds' => $this->conversationPollSeconds(),
        ];
    }
}
