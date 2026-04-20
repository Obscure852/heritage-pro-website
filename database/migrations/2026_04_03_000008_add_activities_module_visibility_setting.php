<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('s_m_s_api_settings')) {
            return;
        }

        DB::table('s_m_s_api_settings')->updateOrInsert(
            ['key' => 'modules.activities_visible'],
            [
                'value' => '1',
                'category' => 'module_visibility',
                'type' => 'boolean',
                'display_name' => 'Activities Module',
                'description' => 'Show or hide the Activities Manager module for all users',
                'validation_rules' => 'required|boolean',
                'is_editable' => true,
                'display_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        if (!Schema::hasTable('s_m_s_api_settings')) {
            return;
        }

        DB::table('s_m_s_api_settings')
            ->where('key', 'modules.activities_visible')
            ->delete();
    }
};
