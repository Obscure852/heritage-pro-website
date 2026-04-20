<?php

use App\Models\Timetable\TimetableSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        // 1. Seed period_definitions with 7 default periods and bell schedule times
        TimetableSetting::set('period_definitions', [
            ['period' => 1, 'start_time' => '07:30', 'end_time' => '08:10', 'duration' => 40],
            ['period' => 2, 'start_time' => '08:10', 'end_time' => '08:50', 'duration' => 40],
            ['period' => 3, 'start_time' => '08:50', 'end_time' => '09:30', 'duration' => 40],
            ['period' => 4, 'start_time' => '09:50', 'end_time' => '10:30', 'duration' => 40],
            ['period' => 5, 'start_time' => '10:30', 'end_time' => '11:10', 'duration' => 40],
            ['period' => 6, 'start_time' => '11:55', 'end_time' => '12:35', 'duration' => 40],
            ['period' => 7, 'start_time' => '12:35', 'end_time' => '13:15', 'duration' => 40],
        ]);

        // 2. Update break_intervals to include start_time and end_time
        TimetableSetting::set('break_intervals', [
            ['after_period' => 3, 'duration' => 20, 'label' => 'Tea Break', 'start_time' => '09:30', 'end_time' => '09:50'],
            ['after_period' => 5, 'duration' => 45, 'label' => 'Lunch', 'start_time' => '11:10', 'end_time' => '11:55'],
        ]);

        // 3. Seed optional_coupling_groups with empty array
        TimetableSetting::set('optional_coupling_groups', []);
    }

    public function down(): void {
        // Delete period_definitions and optional_coupling_groups
        DB::table('timetable_settings')->where('key', 'period_definitions')->delete();
        DB::table('timetable_settings')->where('key', 'optional_coupling_groups')->delete();

        // Revert break_intervals to Phase 1 format (without times)
        TimetableSetting::set('break_intervals', [
            ['after_period' => 3, 'duration' => 20, 'label' => 'Tea Break'],
            ['after_period' => 5, 'duration' => 45, 'label' => 'Lunch'],
        ]);
    }
};
