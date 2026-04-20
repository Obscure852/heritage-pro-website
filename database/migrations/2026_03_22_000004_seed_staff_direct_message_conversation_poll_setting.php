<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('s_m_s_api_settings')->updateOrInsert(
            ['key' => 'internal_messaging.conversation_poll_seconds'],
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
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('s_m_s_api_settings')
            ->where('key', 'internal_messaging.conversation_poll_seconds')
            ->delete();
    }
};
