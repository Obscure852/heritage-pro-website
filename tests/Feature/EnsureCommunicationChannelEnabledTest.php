<?php

namespace Tests\Feature;

use App\Services\Messaging\CommunicationChannelService;
use App\Services\SettingsService;
use Illuminate\Support\Facades\Route;
use Mockery;
use Tests\TestCase;

class EnsureCommunicationChannelEnabledTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('channel.enabled:sms')->get('/test-channel-sms', function () {
            return response()->json(['success' => true]);
        });

        Route::middleware('channel.enabled:sms,whatsapp')->get('/test-channel-any', function () {
            return response()->json(['success' => true]);
        });
    }

    public function test_it_blocks_json_requests_when_channel_is_disabled(): void
    {
        $settings = Mockery::mock(SettingsService::class);
        $settings->shouldReceive('get')
            ->andReturnUsing(function ($key, $default = null) {
                return match ($key) {
                    'features.sms_enabled' => false,
                    'features.whatsapp_enabled' => false,
                    default => $default,
                };
            });

        $this->app->instance(CommunicationChannelService::class, new CommunicationChannelService($settings));

        $response = $this->getJson('/test-channel-sms');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'SMS is disabled in Communications Setup.',
            ]);
    }

    public function test_it_allows_request_when_any_requested_channel_is_enabled(): void
    {
        $settings = Mockery::mock(SettingsService::class);
        $settings->shouldReceive('get')
            ->andReturnUsing(function ($key, $default = null) {
                return match ($key) {
                    'features.sms_enabled' => true,
                    'features.whatsapp_enabled' => false,
                    default => $default,
                };
            });

        $this->app->instance(CommunicationChannelService::class, new CommunicationChannelService($settings));

        $response = $this->getJson('/test-channel-any');

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);
    }
}
