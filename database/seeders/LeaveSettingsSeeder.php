<?php

namespace Database\Seeders;

use App\Models\Leave\LeaveSetting;
use Illuminate\Database\Seeder;

class LeaveSettingsSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * Seeds default leave module settings including leave_year_start_month.
     *
     * @return void
     */
    public function run(): void {
        $settings = [
            [
                'key' => 'leave_year_start_month',
                'value' => ['month' => 1],
                'description' => 'The month when the leave year starts (1-12). January = 1, February = 2, etc.',
            ],
            [
                'key' => 'default_balance_mode',
                'value' => ['mode' => 'allocation'],
                'description' => 'Default balance mode for new leave types (allocation or accrual).',
            ],
            [
                'key' => 'default_carry_over_mode',
                'value' => ['mode' => 'none'],
                'description' => 'Default carry-over mode for new leave types (none, limited, or full).',
            ],
            [
                'key' => 'max_negative_balance',
                'value' => ['days' => 5],
                'description' => 'Maximum negative balance allowed (in days) for leave types that allow negative balance.',
            ],
            [
                'key' => 'leave_request_approval_required',
                'value' => ['required' => true],
                'description' => 'Whether leave requests require approval from HOD.',
            ],
            [
                'key' => 'allow_backdated_requests',
                'value' => ['allowed' => true, 'max_days' => 7],
                'description' => 'Whether to allow backdated leave requests and maximum days allowed.',
            ],
            [
                'key' => 'auto_cancel_pending_after_days',
                'value' => ['days' => 30, 'enabled' => false],
                'description' => 'Automatically cancel pending leave requests after specified days.',
            ],
            [
                'key' => 'weekend_days',
                'value' => ['days' => [6, 0]],
                'description' => 'Days of the week considered as weekends (0=Sunday, 1=Monday, ..., 6=Saturday).',
            ],
            [
                'key' => 'leave_reminder_days_before',
                'value' => ['days' => 3],
                'description' => 'Number of days before leave start date to send reminder notification.',
            ],
            [
                'key' => 'pending_approval_reminder_hours',
                'value' => ['hours' => 24],
                'description' => 'Hours after which to remind approver about pending requests.',
            ],
        ];

        foreach ($settings as $setting) {
            LeaveSetting::firstOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'description' => $setting['description'],
                ]
            );
        }

        $this->command->info('Leave settings seeded successfully!');
        $this->command->info('Total settings: ' . count($settings));
    }
}
