<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pdp_plan_section_entries')) {
            return;
        }

        Schema::table('pdp_plan_section_entries', function (Blueprint $table): void {
            if (!Schema::hasColumn('pdp_plan_section_entries', 'pdp_template_section_row_id')) {
                $table->foreignId('pdp_template_section_row_id')
                    ->nullable()
                    ->after('parent_entry_id')
                    ->constrained('pdp_template_section_rows')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('pdp_plan_section_entries', 'origin_type')) {
                $table->string('origin_type', 32)
                    ->default('custom')
                    ->after('entry_group_key');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('pdp_plan_section_entries')) {
            return;
        }

        Schema::table('pdp_plan_section_entries', function (Blueprint $table): void {
            if (Schema::hasColumn('pdp_plan_section_entries', 'pdp_template_section_row_id')) {
                $table->dropConstrainedForeignId('pdp_template_section_row_id');
            }

            if (Schema::hasColumn('pdp_plan_section_entries', 'origin_type')) {
                $table->dropColumn('origin_type');
            }
        });
    }
};
