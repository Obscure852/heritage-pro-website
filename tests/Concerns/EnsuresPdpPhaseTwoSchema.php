<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\Schema;

trait EnsuresPdpPhaseTwoSchema
{
    use EnsuresPdpPhaseOneSchema;

    protected function ensurePdpPhaseTwoSchema(): void
    {
        $this->ensurePdpPhaseOneSchema();
        $this->ensureSchoolSetupTable();

        $migrations = [
            'pdp_plans' => '2026_03_12_000009_create_pdp_plans_table.php',
            'pdp_plan_reviews' => '2026_03_12_000010_create_pdp_plan_reviews_table.php',
            'pdp_plan_section_entries' => '2026_03_12_000011_create_pdp_plan_section_entries_table.php',
            'pdp_plan_signatures' => '2026_03_12_000012_create_pdp_plan_signatures_table.php',
            'pdp_template_section_rows' => '2026_03_12_000015_create_pdp_template_section_rows_table.php',
            'pdp_rollouts' => '2026_03_12_000016_create_pdp_rollouts_table.php',
        ];

        foreach ($migrations as $table => $file) {
            if (Schema::hasTable($table)) {
                continue;
            }

            $migration = require database_path('migrations/' . $file);
            $migration->up();
        }

        foreach ([
            '2026_03_12_000017_add_pdp_rollout_id_to_pdp_plans_table.php',
            '2026_03_12_000018_add_template_row_origin_to_pdp_plan_section_entries_table.php',
            '2026_03_12_000020_add_parent_row_id_to_pdp_template_section_rows_table.php',
        ] as $file) {
            $migration = require database_path('migrations/' . $file);
            $migration->up();
        }
    }

    private function ensureSchoolSetupTable(): void
    {
        if (Schema::hasTable('school_setup')) {
            return;
        }

        Schema::create('school_setup', function ($table): void {
            $table->id();
            $table->string('school_id', 30)->nullable()->unique();
            $table->string('ownership')->nullable();
            $table->string('school_name');
            $table->string('slogan')->nullable();
            $table->string('telephone')->nullable();
            $table->string('fax')->nullable();
            $table->string('email_address')->nullable();
            $table->string('physical_address')->nullable();
            $table->string('postal_address')->nullable();
            $table->string('website')->nullable();
            $table->string('region')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('letterhead_path')->nullable();
            $table->string('type');
            $table->boolean('boarding')->nullable();
            $table->string('school_sms_signature')->nullable();
            $table->string('school_email_signature')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
