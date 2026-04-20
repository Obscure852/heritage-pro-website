<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            [
                'key' => 'modules.welfare_visible',
                'value' => '1',
                'category' => 'module_visibility',
                'type' => 'boolean',
                'display_name' => 'Student Welfare Module',
                'description' => 'Show or hide the Student Welfare module for all users',
                'validation_rules' => 'required|boolean',
                'is_editable' => true,
                'display_order' => 1,
            ],
            [
                'key' => 'modules.communications_visible',
                'value' => '1',
                'category' => 'module_visibility',
                'type' => 'boolean',
                'display_name' => 'Communication Module',
                'description' => 'Show or hide the Communication module for all users',
                'validation_rules' => 'required|boolean',
                'is_editable' => true,
                'display_order' => 2,
            ],
            [
                'key' => 'modules.assets_visible',
                'value' => '1',
                'category' => 'module_visibility',
                'type' => 'boolean',
                'display_name' => 'Assets Module',
                'description' => 'Show or hide the Assets module for all users',
                'validation_rules' => 'required|boolean',
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
        $keys = [
            'modules.welfare_visible',
            'modules.communications_visible',
            'modules.assets_visible',
        ];
        DB::table('s_m_s_api_settings')->whereIn('key', $keys)->delete();
    }
};
