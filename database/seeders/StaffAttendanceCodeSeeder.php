<?php

namespace Database\Seeders;

use App\Models\StaffAttendance\StaffAttendanceCode;
use Illuminate\Database\Seeder;

class StaffAttendanceCodeSeeder extends Seeder
{
    /**
     * Seed the default staff attendance codes.
     *
     * @return void
     */
    public function run(): void
    {
        $codes = [
            [
                'code' => 'P',
                'name' => 'Present',
                'description' => 'Staff member was present for work',
                'color' => '#10b981',
                'counts_as_present' => true,
                'order' => 1,
            ],
            [
                'code' => 'A',
                'name' => 'Absent',
                'description' => 'Staff member was absent without authorization',
                'color' => '#ef4444',
                'counts_as_present' => false,
                'order' => 2,
            ],
            [
                'code' => 'L',
                'name' => 'Late',
                'description' => 'Staff member arrived late to work',
                'color' => '#f59e0b',
                'counts_as_present' => true,
                'order' => 3,
            ],
            [
                'code' => 'HD',
                'name' => 'Half Day',
                'description' => 'Staff member worked half day',
                'color' => '#8b5cf6',
                'counts_as_present' => true,
                'order' => 4,
            ],
            [
                'code' => 'OL',
                'name' => 'On Leave',
                'description' => 'Staff member is on approved leave',
                'color' => '#3b82f6',
                'counts_as_present' => false,
                'order' => 5,
            ],
            [
                'code' => 'SL',
                'name' => 'Sick Leave',
                'description' => 'Staff member is on sick leave',
                'color' => '#06b6d4',
                'counts_as_present' => false,
                'order' => 6,
            ],
            [
                'code' => 'WFH',
                'name' => 'Work From Home',
                'description' => 'Staff member is working from home',
                'color' => '#10b981',
                'counts_as_present' => true,
                'order' => 7,
            ],
            [
                'code' => 'H',
                'name' => 'Holiday',
                'description' => 'Public holiday - office closed',
                'color' => '#6366f1',
                'counts_as_present' => false,
                'order' => 8,
            ],
        ];

        foreach ($codes as $code) {
            StaffAttendanceCode::updateOrCreate(
                ['code' => $code['code']],
                $code
            );
        }
    }
}
