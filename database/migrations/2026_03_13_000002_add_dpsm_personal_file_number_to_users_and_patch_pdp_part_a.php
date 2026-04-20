<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $this->ensureDpsmPersonalFileNumberColumn();
        $this->backfillDpsmPersonalFileNumber();
        $this->patchSeededEmployeeInformationSections();
    }

    public function down(): void
    {
        if (!Schema::hasTable('users') || !Schema::hasColumn('users', 'dpsm_personal_file_number')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('dpsm_personal_file_number');
        });
    }

    private function ensureDpsmPersonalFileNumberColumn(): void
    {
        if (!Schema::hasTable('users') || Schema::hasColumn('users', 'dpsm_personal_file_number')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->string('dpsm_personal_file_number')->nullable()->after('personal_payroll_number');
            $table->index('dpsm_personal_file_number');
        });
    }

    private function backfillDpsmPersonalFileNumber(): void
    {
        if (
            !Schema::hasTable('users')
            || !Schema::hasColumn('users', 'dpsm_personal_file_number')
            || !Schema::hasTable('user_profile_metadata')
        ) {
            return;
        }

        $metadataByUser = DB::table('user_profile_metadata')
            ->whereIn('key', ['dpsm_file_no', 'dpsm_personal_file_no'])
            ->orderBy('user_id')
            ->get()
            ->groupBy('user_id');

        foreach ($metadataByUser as $userId => $records) {
            $user = DB::table('users')->where('id', $userId)->first();

            if (!$user || !empty($user->dpsm_personal_file_number)) {
                continue;
            }

            $value = $this->decodeMetadataValue($records->firstWhere('key', 'dpsm_file_no')?->value);
            if ($value === null || $value === '') {
                $value = $this->decodeMetadataValue($records->firstWhere('key', 'dpsm_personal_file_no')?->value);
            }

            if ($value === null || $value === '') {
                continue;
            }

            DB::table('users')
                ->where('id', $userId)
                ->update([
                    'dpsm_personal_file_number' => $value,
                    'updated_at' => now(),
                ]);
        }
    }

    private function patchSeededEmployeeInformationSections(): void
    {
        if (
            !Schema::hasTable('pdp_templates')
            || !Schema::hasTable('pdp_template_sections')
            || !Schema::hasTable('pdp_template_fields')
        ) {
            return;
        }

        $sections = DB::table('pdp_template_sections')
            ->join('pdp_templates', 'pdp_templates.id', '=', 'pdp_template_sections.pdp_template_id')
            ->where('pdp_template_sections.key', 'employee_information')
            ->whereIn('pdp_templates.code', ['staff-pdp-school-v4', 'staff-pdp-dpsm-v4'])
            ->select([
                'pdp_template_sections.id as section_id',
                'pdp_templates.code as template_code',
            ])
            ->get();

        foreach ($sections as $section) {
            $this->syncFieldDefinitions((int) $section->section_id, $this->employeeInformationFieldsFor($section->template_code));
        }
    }

    private function syncFieldDefinitions(int $sectionId, array $fields): void
    {
        $timestamp = now();

        foreach ($fields as $field) {
            DB::table('pdp_template_fields')->updateOrInsert(
                [
                    'pdp_template_section_id' => $sectionId,
                    'key' => $field['key'],
                ],
                array_merge([
                    'parent_field_id' => null,
                    'required' => false,
                    'validation_rules_json' => null,
                    'mapping_source' => null,
                    'mapping_key' => null,
                    'default_value_json' => null,
                    'options_json' => null,
                    'period_scope' => null,
                    'rating_scheme_key' => null,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ], $field)
            );
        }
    }

    private function employeeInformationFieldsFor(string $templateCode): array
    {
        return match ($templateCode) {
            'staff-pdp-dpsm-v4' => $this->officialDpsmEmployeeInformationFields(),
            default => $this->schoolEmployeeInformationFields(),
        };
    }

    private function schoolEmployeeInformationFields(): array
    {
        return [
            [
                'key' => 'employee_name',
                'label' => 'Name of Employee',
                'field_type' => 'text',
                'data_type' => 'string',
                'input_mode' => 'mapped_user_field',
                'mapping_source' => 'user',
                'mapping_key' => 'full_name',
                'required' => true,
                'sort_order' => 1,
            ],
            [
                'key' => 'payroll_no',
                'label' => 'Personal Payroll Number',
                'field_type' => 'text',
                'data_type' => 'string',
                'input_mode' => 'mapped_user_field',
                'mapping_source' => 'user',
                'mapping_key' => 'personal_payroll_number',
                'sort_order' => 2,
            ],
            [
                'key' => 'dpsm_file_no',
                'label' => 'DPSM Personal File No',
                'field_type' => 'text',
                'data_type' => 'string',
                'input_mode' => 'mapped_user_field',
                'mapping_source' => 'user',
                'mapping_key' => 'dpsm_personal_file_number',
                'sort_order' => 3,
            ],
            [
                'key' => 'plan_period_start',
                'label' => 'Plan Period From',
                'field_type' => 'date',
                'data_type' => 'date',
                'input_mode' => 'computed',
                'mapping_source' => 'plan',
                'mapping_key' => 'plan_period_start',
                'required' => true,
                'sort_order' => 4,
            ],
            [
                'key' => 'plan_period_end',
                'label' => 'Plan Period To',
                'field_type' => 'date',
                'data_type' => 'date',
                'input_mode' => 'computed',
                'mapping_source' => 'plan',
                'mapping_key' => 'plan_period_end',
                'required' => true,
                'sort_order' => 5,
            ],
            [
                'key' => 'ministry_department',
                'label' => 'Ministry / Department',
                'field_type' => 'text',
                'data_type' => 'string',
                'input_mode' => 'mapped_setting',
                'mapping_source' => 'settings',
                'mapping_key' => 'pdp.general.part_a_ministry_department',
                'sort_order' => 6,
            ],
            [
                'key' => 'school_name',
                'label' => 'Division / Unit',
                'field_type' => 'text',
                'data_type' => 'string',
                'input_mode' => 'mapped_setting',
                'mapping_source' => 'settings',
                'mapping_key' => 'school_setup.school_name',
                'sort_order' => 7,
            ],
            [
                'key' => 'position_title',
                'label' => 'Position Title',
                'field_type' => 'text',
                'data_type' => 'string',
                'input_mode' => 'mapped_user_field',
                'mapping_source' => 'user',
                'mapping_key' => 'position',
                'required' => true,
                'sort_order' => 8,
            ],
            [
                'key' => 'grade',
                'label' => 'Grade (Earning Band)',
                'field_type' => 'text',
                'data_type' => 'string',
                'input_mode' => 'mapped_user_field',
                'mapping_source' => 'user',
                'mapping_key' => 'earning_band',
                'sort_order' => 9,
            ],
            [
                'key' => 'date_of_appointment',
                'label' => 'Date of Appointment',
                'field_type' => 'date',
                'data_type' => 'date',
                'input_mode' => 'mapped_user_field',
                'mapping_source' => 'user',
                'mapping_key' => 'date_of_appointment',
                'sort_order' => 10,
            ],
            [
                'key' => 'duty_station',
                'label' => 'Duty Station',
                'field_type' => 'text',
                'data_type' => 'string',
                'input_mode' => 'mapped_setting',
                'mapping_source' => 'settings',
                'mapping_key' => 'school_setup.school_name',
                'sort_order' => 11,
            ],
            [
                'key' => 'supervisor_name',
                'label' => 'Supervisor Name',
                'field_type' => 'text',
                'data_type' => 'string',
                'input_mode' => 'computed',
                'mapping_source' => 'plan',
                'mapping_key' => 'supervisor.full_name',
                'sort_order' => 12,
            ],
            [
                'key' => 'supervisor_position',
                'label' => 'Supervisor Position',
                'field_type' => 'text',
                'data_type' => 'string',
                'input_mode' => 'computed',
                'mapping_source' => 'plan',
                'mapping_key' => 'supervisor.position',
                'sort_order' => 13,
            ],
            [
                'key' => 'supervisor_grade',
                'label' => 'Supervisor Grade',
                'field_type' => 'text',
                'data_type' => 'string',
                'input_mode' => 'computed',
                'mapping_source' => 'plan',
                'mapping_key' => 'supervisor.earning_band',
                'sort_order' => 14,
            ],
            [
                'key' => 'supervisor_duty_station',
                'label' => 'Supervisor Duty Station',
                'field_type' => 'text',
                'data_type' => 'string',
                'input_mode' => 'mapped_setting',
                'mapping_source' => 'settings',
                'mapping_key' => 'school_setup.school_name',
                'sort_order' => 15,
            ],
        ];
    }

    private function officialDpsmEmployeeInformationFields(): array
    {
        return [
            [
                'key' => 'employee_name',
                'label' => 'Name of Employee',
                'field_type' => 'text',
                'data_type' => 'string',
                'input_mode' => 'mapped_user_field',
                'mapping_source' => 'user',
                'mapping_key' => 'full_name',
                'required' => true,
                'sort_order' => 1,
            ],
            [
                'key' => 'payroll_no',
                'label' => 'Personal Payroll Number',
                'field_type' => 'text',
                'data_type' => 'string',
                'input_mode' => 'mapped_user_field',
                'mapping_source' => 'user',
                'mapping_key' => 'personal_payroll_number',
                'sort_order' => 2,
            ],
            [
                'key' => 'dpsm_file_no',
                'label' => 'DPSM Personal File No',
                'field_type' => 'text',
                'data_type' => 'string',
                'input_mode' => 'mapped_user_field',
                'mapping_source' => 'user',
                'mapping_key' => 'dpsm_personal_file_number',
                'sort_order' => 3,
            ],
            [
                'key' => 'plan_period_start',
                'label' => 'Plan Period From',
                'field_type' => 'date',
                'data_type' => 'date',
                'input_mode' => 'computed',
                'mapping_source' => 'plan',
                'mapping_key' => 'plan_period_start',
                'sort_order' => 4,
            ],
            [
                'key' => 'plan_period_end',
                'label' => 'Plan Period To',
                'field_type' => 'date',
                'data_type' => 'date',
                'input_mode' => 'computed',
                'mapping_source' => 'plan',
                'mapping_key' => 'plan_period_end',
                'sort_order' => 5,
            ],
            [
                'key' => 'ministry_department',
                'label' => 'Ministry / Department',
                'field_type' => 'text',
                'data_type' => 'string',
                'input_mode' => 'mapped_setting',
                'mapping_source' => 'settings',
                'mapping_key' => 'pdp.general.part_a_ministry_department',
                'sort_order' => 6,
            ],
            [
                'key' => 'position_title',
                'label' => 'Position Title',
                'field_type' => 'text',
                'data_type' => 'string',
                'input_mode' => 'mapped_user_field',
                'mapping_source' => 'user',
                'mapping_key' => 'position',
                'sort_order' => 7,
            ],
            [
                'key' => 'grade',
                'label' => 'Grade (Earning Band)',
                'field_type' => 'text',
                'data_type' => 'string',
                'input_mode' => 'mapped_user_field',
                'mapping_source' => 'user',
                'mapping_key' => 'earning_band',
                'sort_order' => 8,
            ],
            [
                'key' => 'date_of_appointment',
                'label' => 'Date of Appointment',
                'field_type' => 'date',
                'data_type' => 'date',
                'input_mode' => 'mapped_user_field',
                'mapping_source' => 'user',
                'mapping_key' => 'date_of_appointment',
                'sort_order' => 9,
            ],
            [
                'key' => 'school_name',
                'label' => 'Division / Unit',
                'field_type' => 'text',
                'data_type' => 'string',
                'input_mode' => 'mapped_setting',
                'mapping_source' => 'settings',
                'mapping_key' => 'school_setup.school_name',
                'sort_order' => 10,
            ],
            [
                'key' => 'duty_station',
                'label' => 'Duty Station',
                'field_type' => 'text',
                'data_type' => 'string',
                'input_mode' => 'mapped_setting',
                'mapping_source' => 'settings',
                'mapping_key' => 'school_setup.school_name',
                'sort_order' => 11,
            ],
            [
                'key' => 'supervisor_name',
                'label' => 'Supervisor Name',
                'field_type' => 'text',
                'data_type' => 'string',
                'input_mode' => 'computed',
                'mapping_source' => 'plan',
                'mapping_key' => 'supervisor.full_name',
                'sort_order' => 12,
            ],
            [
                'key' => 'supervisor_position',
                'label' => 'Supervisor Position',
                'field_type' => 'text',
                'data_type' => 'string',
                'input_mode' => 'computed',
                'mapping_source' => 'plan',
                'mapping_key' => 'supervisor.position',
                'sort_order' => 13,
            ],
            [
                'key' => 'supervisor_grade',
                'label' => 'Supervisor Grade',
                'field_type' => 'text',
                'data_type' => 'string',
                'input_mode' => 'computed',
                'mapping_source' => 'plan',
                'mapping_key' => 'supervisor.earning_band',
                'sort_order' => 14,
            ],
            [
                'key' => 'supervisor_duty_station',
                'label' => 'Supervisor Duty Station',
                'field_type' => 'text',
                'data_type' => 'string',
                'input_mode' => 'mapped_setting',
                'mapping_source' => 'settings',
                'mapping_key' => 'school_setup.school_name',
                'sort_order' => 15,
            ],
        ];
    }

    private function decodeMetadataValue(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_string($value)) {
            return $value;
        }

        $decoded = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }
};
