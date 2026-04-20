<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('s_m_s_api_settings')->updateOrInsert(
            ['key' => 'modules.staff_pdp_visible'],
            [
                'value' => '1',
                'category' => 'modules',
                'type' => 'boolean',
                'description' => 'Controls visibility of the Staff PDP module',
                'display_name' => 'Staff PDP Visible',
                'is_editable' => true,
                'display_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('s_m_s_api_settings')
            ->where('key', 'modules.staff_pdp_visible')
            ->delete();
    }
};
