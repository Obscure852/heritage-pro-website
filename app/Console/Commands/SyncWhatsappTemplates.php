<?php

namespace App\Console\Commands;

use App\Services\SettingsService;
use App\Services\Messaging\TwilioWhatsAppService;
use Illuminate\Console\Command;

class SyncWhatsappTemplates extends Command
{
    protected $signature = 'whatsapp:sync-templates {--limit=}';

    protected $description = 'Sync approved WhatsApp templates from Twilio';

    public function handle(TwilioWhatsAppService $service, SettingsService $settingsService): int
    {
        if (!(bool) $settingsService->get('whatsapp.sync_enabled', true)) {
            $this->info('WhatsApp template sync is disabled.');

            return self::SUCCESS;
        }

        $result = $service->syncTemplates($this->option('limit') ? (int) $this->option('limit') : null);

        $this->info("Synced {$result['count']} WhatsApp templates.");

        return self::SUCCESS;
    }
}
