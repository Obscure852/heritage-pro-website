<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            [
                'key' => 'features.staff_direct_messages_enabled',
                'value' => '1',
                'category' => 'feature',
                'type' => 'boolean',
                'display_name' => 'Enable Staff Direct Messaging',
                'description' => 'Allow or block internal staff direct messaging system-wide.',
                'validation_rules' => 'required|boolean',
                'is_editable' => true,
                'display_order' => 7,
            ],
            [
                'key' => 'features.staff_presence_launcher_enabled',
                'value' => '1',
                'category' => 'feature',
                'type' => 'boolean',
                'display_name' => 'Enable Online Staff Launcher',
                'description' => 'Show or hide the quiet online-staff launcher in the staff topbar.',
                'validation_rules' => 'required|boolean',
                'is_editable' => true,
                'display_order' => 8,
            ],
            [
                'key' => 'internal_messaging.online_window_minutes',
                'value' => '2',
                'category' => 'internal_messaging',
                'type' => 'integer',
                'display_name' => 'Online Window (minutes)',
                'description' => 'How long a staff heartbeat remains valid before the user appears offline.',
                'validation_rules' => 'required|integer|min:1|max:60',
                'is_editable' => true,
                'display_order' => 1,
            ],
            [
                'key' => 'internal_messaging.launcher_poll_seconds',
                'value' => '45',
                'category' => 'internal_messaging',
                'type' => 'integer',
                'display_name' => 'Launcher Poll Interval (seconds)',
                'description' => 'How often the quiet launcher refreshes presence and unread counts.',
                'validation_rules' => 'required|integer|min:15|max:300',
                'is_editable' => true,
                'display_order' => 2,
            ],
            [
                'key' => 'internal_messaging.conversation_poll_seconds',
                'value' => '5',
                'category' => 'internal_messaging',
                'type' => 'integer',
                'display_name' => 'Conversation Poll Interval (seconds)',
                'description' => 'How often an open direct-message conversation checks for new messages.',
                'validation_rules' => 'required|integer|min:3|max:30',
                'is_editable' => true,
                'display_order' => 3,
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
            'features.staff_direct_messages_enabled',
            'features.staff_presence_launcher_enabled',
            'internal_messaging.online_window_minutes',
            'internal_messaging.launcher_poll_seconds',
            'internal_messaging.conversation_poll_seconds',
        ])->delete();
    }
};
