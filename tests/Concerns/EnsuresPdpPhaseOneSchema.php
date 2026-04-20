<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait EnsuresPdpPhaseOneSchema
{
    protected function ensurePdpPhaseOneSchema(): void
    {
        $this->ensureUsersTable();
        $this->ensureUserPdpProfileColumns();
        $this->ensureSystemSettingsTable();
        $this->ensureLicensesTable();
        $this->seedModuleVisibilitySettings();

        $migrations = [
            'user_profile_metadata' => '2026_03_12_000001_create_user_profile_metadata_table.php',
            'pdp_settings' => '2026_03_12_000002_create_pdp_settings_table.php',
            'pdp_templates' => '2026_03_12_000003_create_pdp_templates_table.php',
            'pdp_template_sections' => '2026_03_12_000004_create_pdp_template_sections_table.php',
            'pdp_template_fields' => '2026_03_12_000005_create_pdp_template_fields_table.php',
            'pdp_template_periods' => '2026_03_12_000006_create_pdp_template_periods_table.php',
            'pdp_template_rating_schemes' => '2026_03_12_000007_create_pdp_template_rating_schemes_table.php',
            'pdp_template_approval_steps' => '2026_03_12_000008_create_pdp_template_approval_steps_table.php',
            'pdp_template_section_rows' => '2026_03_12_000015_create_pdp_template_section_rows_table.php',
        ];

        foreach ($migrations as $table => $file) {
            if (Schema::hasTable($table)) {
                continue;
            }

            $migration = require database_path('migrations/' . $file);
            $migration->up();
        }

        $migration = require database_path('migrations/2026_03_12_000020_add_parent_row_id_to_pdp_template_section_rows_table.php');
        $migration->up();
    }

    private function ensureUsersTable(): void
    {
        if (Schema::hasTable('users')) {
            return;
        }

        Schema::create('users', function ($table): void {
            $table->id();
            $table->string('firstname');
            $table->string('middlename')->nullable();
            $table->string('lastname');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('avatar')->nullable();
            $table->string('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->unsignedBigInteger('reporting_to')->nullable();
            $table->string('department')->nullable();
            $table->string('position')->nullable();
            $table->string('area_of_work')->nullable();
            $table->string('personal_payroll_number')->nullable();
            $table->string('dpsm_personal_file_number')->nullable();
            $table->date('date_of_appointment')->nullable();
            $table->string('earning_band')->nullable();
            $table->string('nationality')->nullable();
            $table->string('signature_path')->nullable();
            $table->string('sms_signature')->nullable();
            $table->string('email_signature')->nullable();
            $table->string('phone')->nullable();
            $table->string('id_number')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->boolean('active')->default(true);
            $table->string('status')->default('Current');
            $table->unsignedBigInteger('user_filter_id')->nullable();
            $table->string('username')->nullable();
            $table->year('year')->default(date('Y'));
            $table->string('last_updated_by')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index('reporting_to');
        });
    }

    private function ensureUserPdpProfileColumns(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function ($table): void {
            if (!Schema::hasColumn('users', 'personal_payroll_number')) {
                $table->string('personal_payroll_number')->nullable();
            }

            if (!Schema::hasColumn('users', 'dpsm_personal_file_number')) {
                $table->string('dpsm_personal_file_number')->nullable();
            }

            if (!Schema::hasColumn('users', 'date_of_appointment')) {
                $table->date('date_of_appointment')->nullable();
            }

            if (!Schema::hasColumn('users', 'earning_band')) {
                $table->string('earning_band')->nullable();
            }

            if (!Schema::hasColumn('users', 'user_filter_id')) {
                $table->unsignedBigInteger('user_filter_id')->nullable();
            }
        });
    }

    private function ensureSystemSettingsTable(): void
    {
        if (Schema::hasTable('s_m_s_api_settings')) {
            return;
        }

        Schema::create('s_m_s_api_settings', function ($table): void {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('category')->nullable();
            $table->string('type')->nullable();
            $table->text('description')->nullable();
            $table->string('display_name')->nullable();
            $table->text('validation_rules')->nullable();
            $table->boolean('is_editable')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });
    }

    private function seedModuleVisibilitySettings(): void
    {
        foreach ([
            'modules.leave_visible',
            'modules.staff_attendance_visible',
            'modules.staff_pdp_visible',
            'modules.welfare_visible',
            'modules.schemes_visible',
            'modules.communications_visible',
            'modules.lms_visible',
            'modules.assets_visible',
            'modules.fees_visible',
            'modules.library_visible',
            'modules.timetable_visible',
            'modules.invigilation_visible',
        ] as $key) {
            DB::table('s_m_s_api_settings')->updateOrInsert(
                ['key' => $key],
                [
                    'value' => '0',
                    'category' => 'modules',
                    'type' => 'boolean',
                    'display_name' => $key,
                    'is_editable' => true,
                    'display_order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function ensureLicensesTable(): void
    {
        if (Schema::hasTable('licenses')) {
            return;
        }

        Schema::create('licenses', function ($table): void {
            $table->id();
            $table->string('name');
            $table->string('key')->nullable();
            $table->year('year');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('active')->default(false);
            $table->timestamps();
        });
    }
}
