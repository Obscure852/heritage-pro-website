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

        $settings = [
            [
                'key' => 'activities.options.categories',
                'value' => json_encode($this->optionRows([
                    'club' => 'Club',
                    'sport' => 'Sport',
                    'society' => 'Society',
                    'arts' => 'Arts',
                    'service' => 'Service',
                    'academic' => 'Academic',
                    'event_program' => 'Event Program',
                    'other' => 'Other',
                ])),
                'display_name' => 'Activities Categories',
                'display_order' => 10,
            ],
            [
                'key' => 'activities.options.delivery_modes',
                'value' => json_encode($this->optionRows([
                    'recurring' => 'Recurring',
                    'one_off' => 'One Off',
                    'hybrid' => 'Hybrid',
                ])),
                'display_name' => 'Activities Delivery Modes',
                'display_order' => 20,
            ],
            [
                'key' => 'activities.options.participation_modes',
                'value' => json_encode($this->optionRows([
                    'individual' => 'Individual',
                    'team' => 'Team',
                    'mixed' => 'Mixed',
                ])),
                'display_name' => 'Activities Participation Modes',
                'display_order' => 30,
            ],
            [
                'key' => 'activities.options.result_modes',
                'value' => json_encode($this->optionRows([
                    'attendance_only' => 'Attendance Only',
                    'placements' => 'Placements',
                    'points' => 'Points',
                    'awards' => 'Awards',
                    'mixed' => 'Mixed',
                ], [
                    'attendance_only' => ['allows_results' => false],
                    'placements' => ['allows_results' => true],
                    'points' => ['allows_results' => true],
                    'awards' => ['allows_results' => true],
                    'mixed' => ['allows_results' => true],
                ])),
                'display_name' => 'Activities Result Modes',
                'display_order' => 40,
            ],
            [
                'key' => 'activities.options.gender_policies',
                'value' => json_encode($this->optionRows([
                    'boys' => 'Boys',
                    'girls' => 'Girls',
                    'mixed' => 'Mixed',
                ])),
                'display_name' => 'Activities Gender Policies',
                'display_order' => 50,
            ],
            [
                'key' => 'activities.options.event_types',
                'value' => json_encode($this->optionRows([
                    'fixture' => 'Fixture',
                    'showcase' => 'Showcase',
                    'competition' => 'Competition',
                    'workshop' => 'Workshop',
                    'exhibition' => 'Exhibition',
                    'other' => 'Other',
                ])),
                'display_name' => 'Activities Event Types',
                'display_order' => 60,
            ],
            [
                'key' => 'activities.defaults.activity',
                'value' => json_encode([
                    'category' => 'club',
                    'delivery_mode' => 'recurring',
                    'participation_mode' => 'team',
                    'result_mode' => 'mixed',
                    'gender_policy' => 'mixed',
                    'capacity' => null,
                    'attendance_required' => true,
                    'allow_house_linkage' => false,
                ]),
                'display_name' => 'Activities Default Activity Settings',
                'display_order' => 70,
            ],
            [
                'key' => 'activities.defaults.events',
                'value' => json_encode([
                    'event_type' => 'fixture',
                    'publish_to_calendar' => false,
                    'house_linked' => false,
                ]),
                'display_name' => 'Activities Default Event Settings',
                'display_order' => 80,
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('s_m_s_api_settings')->updateOrInsert(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'category' => 'activities',
                    'type' => 'json',
                    'description' => $setting['display_name'],
                    'display_name' => $setting['display_name'],
                    'is_editable' => true,
                    'display_order' => $setting['display_order'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('s_m_s_api_settings')) {
            return;
        }

        DB::table('s_m_s_api_settings')->whereIn('key', [
            'activities.options.categories',
            'activities.options.delivery_modes',
            'activities.options.participation_modes',
            'activities.options.result_modes',
            'activities.options.gender_policies',
            'activities.options.event_types',
            'activities.defaults.activity',
            'activities.defaults.events',
        ])->delete();
    }

    private function optionRows(array $labels, array $extraByKey = []): array
    {
        $rows = [];

        foreach ($labels as $key => $label) {
            $rows[] = array_merge([
                'key' => $key,
                'label' => $label,
                'active' => true,
                'system' => true,
            ], $extraByKey[$key] ?? []);
        }

        return $rows;
    }
};
