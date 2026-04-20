<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            [
                'key' => 'features.sms_enabled',
                'value' => '0',
                'category' => 'feature',
                'type' => 'boolean',
                'display_name' => 'Enable SMS Sending',
                'description' => 'Allow or block all SMS sending system-wide',
                'validation_rules' => 'required|boolean',
                'is_editable' => true,
                'display_order' => 1,
            ],
            [
                'key' => 'features.whatsapp_enabled',
                'value' => '0',
                'category' => 'feature',
                'type' => 'boolean',
                'display_name' => 'Enable WhatsApp Sending',
                'description' => 'Allow or block all WhatsApp sending system-wide',
                'validation_rules' => 'required|boolean',
                'is_editable' => true,
                'display_order' => 2,
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
        DB::table('s_m_s_api_settings')->where('key', 'features.sms_enabled')->update([
            'value' => '1',
            'updated_at' => now(),
        ]);

        DB::table('s_m_s_api_settings')->where('key', 'features.whatsapp_enabled')->update([
            'value' => '0',
            'updated_at' => now(),
        ]);
    }
};
