<?php

namespace Tests\Unit;

use App\Services\Messaging\CommunicationChannelService;
use App\Services\SettingsService;
use PHPUnit\Framework\TestCase;

class CommunicationChannelServiceTest extends TestCase
{
    public function test_it_returns_enabled_channels_from_settings(): void
    {
        $settings = $this->createMock(SettingsService::class);
        $settings->method('get')
            ->willReturnMap([
                ['features.sms_enabled', false, false],
                ['features.whatsapp_enabled', false, true],
            ]);

        $service = new CommunicationChannelService($settings);

        $this->assertFalse($service->smsEnabled());
        $this->assertTrue($service->whatsappEnabled());
        $this->assertSame(['whatsapp'], $service->enabledChannels());
    }

    public function test_any_enabled_returns_true_when_one_of_the_requested_channels_is_enabled(): void
    {
        $settings = $this->createMock(SettingsService::class);
        $settings->method('get')
            ->willReturnMap([
                ['features.sms_enabled', false, true],
                ['features.whatsapp_enabled', false, false],
            ]);

        $service = new CommunicationChannelService($settings);

        $this->assertTrue($service->anyEnabled(['sms', 'whatsapp']));
        $this->assertFalse($service->anyEnabled(['whatsapp']));
    }
}
