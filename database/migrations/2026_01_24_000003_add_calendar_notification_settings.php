<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {

    public function up(): void {
        $settings = [
            [
                'key' => 'lms_calendar_notifications_enabled',
                'value' => 'false',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'lms_calendar_notification_queue',
                'value' => 'calendar-notifications',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'lms_calendar_notification_batch_size',
                'value' => '100',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('s_m_s_api_settings')->updateOrInsert(
                ['key' => $setting['key']],
                $setting
            );
        }
    }

    public function down(): void {
        DB::table('s_m_s_api_settings')->whereIn('key', [
            'lms_calendar_notifications_enabled',
            'lms_calendar_notification_queue',
            'lms_calendar_notification_batch_size',
        ])->delete();
    }
};
