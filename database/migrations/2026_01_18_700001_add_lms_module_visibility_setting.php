<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        // Add LMS module visibility setting
        DB::table('s_m_s_api_settings')->insert([
            'key' => 'modules.lms_visible',
            'value' => '1', // Visible by default
            'category' => 'module_visibility',
            'type' => 'boolean',
            'description' => 'Controls visibility of the Learning Management System module',
            'display_name' => 'LMS Module Visible',
            'is_editable' => true,
            'display_order' => 4,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void {
        DB::table('s_m_s_api_settings')->where('key', 'modules.lms_visible')->delete();
    }
};
