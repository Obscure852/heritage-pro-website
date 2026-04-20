<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            [
                'key' => 'features.whatsapp_enabled',
                'value' => '0',
                'category' => 'feature',
                'type' => 'boolean',
                'display_name' => 'Enable WhatsApp Sending',
                'description' => 'Allow or block all WhatsApp sending system-wide',
                'validation_rules' => 'required|boolean',
                'is_editable' => true,
                'display_order' => 6,
            ],
            [
                'key' => 'whatsapp.account_sid',
                'value' => '',
                'category' => 'whatsapp',
                'type' => 'string',
                'display_name' => 'Twilio Account SID',
                'description' => 'Twilio account SID used for WhatsApp messaging',
                'validation_rules' => 'nullable|string|max:255',
                'is_editable' => true,
                'display_order' => 1,
            ],
            [
                'key' => 'whatsapp.auth_token',
                'value' => '',
                'category' => 'whatsapp',
                'type' => 'password',
                'display_name' => 'Twilio Auth Token',
                'description' => 'Twilio auth token used for WhatsApp API and webhook verification',
                'validation_rules' => 'nullable|string|max:255',
                'is_editable' => true,
                'display_order' => 2,
            ],
            [
                'key' => 'whatsapp.sender',
                'value' => '',
                'category' => 'whatsapp',
                'type' => 'string',
                'display_name' => 'WhatsApp Sender',
                'description' => 'Configured Twilio WhatsApp sender, for example whatsapp:+14155238886',
                'validation_rules' => 'nullable|string|max:255',
                'is_editable' => true,
                'display_order' => 3,
            ],
            [
                'key' => 'whatsapp.status_webhook_secret',
                'value' => '',
                'category' => 'whatsapp',
                'type' => 'password',
                'display_name' => 'Status Webhook Secret',
                'description' => 'Optional internal secret for WhatsApp status callbacks',
                'validation_rules' => 'nullable|string|max:255',
                'is_editable' => true,
                'display_order' => 4,
            ],
            [
                'key' => 'whatsapp.inbound_webhook_secret',
                'value' => '',
                'category' => 'whatsapp',
                'type' => 'password',
                'display_name' => 'Inbound Webhook Secret',
                'description' => 'Optional internal secret for inbound WhatsApp callbacks',
                'validation_rules' => 'nullable|string|max:255',
                'is_editable' => true,
                'display_order' => 5,
            ],
            [
                'key' => 'whatsapp.default_language',
                'value' => 'en',
                'category' => 'whatsapp',
                'type' => 'string',
                'display_name' => 'Default Template Language',
                'description' => 'Default language used when selecting WhatsApp templates',
                'validation_rules' => 'required|string|max:10',
                'is_editable' => true,
                'display_order' => 6,
            ],
            [
                'key' => 'whatsapp.sync_enabled',
                'value' => '1',
                'category' => 'whatsapp',
                'type' => 'boolean',
                'display_name' => 'Enable Template Sync',
                'description' => 'Allow scheduled and manual syncing of WhatsApp templates from Twilio',
                'validation_rules' => 'required|boolean',
                'is_editable' => true,
                'display_order' => 7,
            ],
            [
                'key' => 'whatsapp.template_sync_limit',
                'value' => '100',
                'category' => 'whatsapp',
                'type' => 'integer',
                'display_name' => 'Template Sync Limit',
                'description' => 'Maximum number of templates to request during a sync',
                'validation_rules' => 'required|integer|min:1|max:500',
                'is_editable' => true,
                'display_order' => 8,
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('s_m_s_api_settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }

    public function down(): void
    {
        DB::table('s_m_s_api_settings')->whereIn('key', [
            'features.whatsapp_enabled',
            'whatsapp.account_sid',
            'whatsapp.auth_token',
            'whatsapp.sender',
            'whatsapp.status_webhook_secret',
            'whatsapp.inbound_webhook_secret',
            'whatsapp.default_language',
            'whatsapp.sync_enabled',
            'whatsapp.template_sync_limit',
        ])->delete();
    }
};
