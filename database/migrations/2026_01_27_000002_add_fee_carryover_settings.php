<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $settings = [
            [
                'key' => 'fee.carryover_lookback_years',
                'value' => '3',
                'category' => 'fees',
                'type' => 'integer',
                'display_name' => 'Carryover Lookback Years',
                'description' => 'Number of years to check for outstanding balances when generating invoices',
                'validation_rules' => 'required|integer|min:1|max:10',
                'is_editable' => true,
                'display_order' => 50,
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('s_m_s_api_settings')
            ->where('key', 'fee.carryover_lookback_years')
            ->delete();
    }
};
