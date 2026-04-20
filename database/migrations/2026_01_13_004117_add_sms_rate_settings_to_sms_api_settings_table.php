<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $now = now();

        // Insert SMS package rate settings
        $settings = [
            [
                'key' => 'sms_rate_basic',
                'value' => '0.35',
                'category' => 'pricing',
                'type' => 'decimal',
                'description' => 'Cost per SMS unit for Basic package (BWP)',
                'display_name' => 'Basic Package Rate',
                'validation_rules' => 'required|numeric|min:0',
                'is_editable' => true,
                'display_order' => 10,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'sms_rate_standard',
                'value' => '0.30',
                'category' => 'pricing',
                'type' => 'decimal',
                'description' => 'Cost per SMS unit for Standard package (BWP)',
                'display_name' => 'Standard Package Rate',
                'validation_rules' => 'required|numeric|min:0',
                'is_editable' => true,
                'display_order' => 20,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'sms_rate_premium',
                'value' => '0.25',
                'category' => 'pricing',
                'type' => 'decimal',
                'description' => 'Cost per SMS unit for Premium package (BWP)',
                'display_name' => 'Premium Package Rate',
                'validation_rules' => 'required|numeric|min:0',
                'is_editable' => true,
                'display_order' => 30,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($settings as $setting) {
            // Only insert if doesn't exist (idempotent)
            $exists = DB::table('s_m_s_api_settings')->where('key', $setting['key'])->exists();
            if (!$exists) {
                DB::table('s_m_s_api_settings')->insert($setting);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('s_m_s_api_settings')
            ->whereIn('key', ['sms_rate_basic', 'sms_rate_standard', 'sms_rate_premium'])
            ->delete();
    }
};
