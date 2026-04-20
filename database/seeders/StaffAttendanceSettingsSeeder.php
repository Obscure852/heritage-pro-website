<?php

namespace Database\Seeders;

use App\Models\StaffAttendance\StaffAttendanceSetting;
use Illuminate\Database\Seeder;

class StaffAttendanceSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeds default staff attendance module settings including work hours
     * and grace period configuration.
     *
     * @return void
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'work_start_time',
                'value' => ['time' => '07:30'],
                'description' => 'Standard work day start time (HH:MM format, 24-hour)',
            ],
            [
                'key' => 'work_end_time',
                'value' => ['time' => '16:30'],
                'description' => 'Standard work day end time (HH:MM format, 24-hour)',
            ],
            [
                'key' => 'grace_period_minutes',
                'value' => ['minutes' => 15],
                'description' => 'Minutes after work_start_time before marking as late',
            ],
            [
                'key' => 'minimum_hours_for_present',
                'value' => ['hours' => 4],
                'description' => 'Minimum hours worked to be marked as present vs half_day',
            ],
            [
                'key' => 'half_day_hours',
                'value' => ['hours' => 4],
                'description' => 'Minimum hours worked to qualify as half day attendance',
            ],
            [
                'key' => 'full_day_hours',
                'value' => ['hours' => 8],
                'description' => 'Minimum hours worked to qualify as full day attendance',
            ],
            [
                'key' => 'overtime_threshold_hours',
                'value' => ['hours' => 8],
                'description' => 'Hours after which overtime is calculated',
            ],
            [
                'key' => 'self_clock_in_enabled',
                'value' => ['enabled' => true],
                'description' => 'Enable/disable self-service clock in/out feature',
            ],
            [
                'key' => 'manual_attendance_enabled',
                'value' => ['enabled' => true],
                'description' => 'Enable/disable manual attendance entry feature',
            ],
        ];

        foreach ($settings as $setting) {
            StaffAttendanceSetting::firstOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'description' => $setting['description'],
                ]
            );
        }

        $this->command->info('Staff attendance settings seeded successfully!');
        $this->command->info('Total settings: ' . count($settings));
    }
}
