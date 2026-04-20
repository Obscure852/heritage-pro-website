<?php

namespace Database\Seeders;

use App\Models\PassingThresholdSetting;
use Illuminate\Database\Seeder;

class PassingThresholdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates the global default threshold setting.
     */
    public function run(): void
    {
        // Only create if no settings exist yet
        if (PassingThresholdSetting::count() > 0) {
            $this->command->info('Threshold settings already exist, skipping seeder.');
            return;
        }

        // Create global default threshold (applies to all school types)
        PassingThresholdSetting::create([
            'school_type' => null,
            'grade_id' => null,
            'grade_subject_id' => null,
            'test_type' => null,
            'thresholds' => [
                [
                    'name' => 'failing',
                    'max_percentage' => 39,
                    'color' => '#fee2e2', // Light red
                ],
                [
                    'name' => 'warning',
                    'max_percentage' => 49,
                    'color' => '#fef3c7', // Amber
                ],
                [
                    'name' => 'caution',
                    'max_percentage' => 59,
                    'color' => '#fefce8', // Light yellow
                ],
            ],
            'is_active' => true,
        ]);

        $this->command->info('Global default threshold setting created successfully.');
    }
}
