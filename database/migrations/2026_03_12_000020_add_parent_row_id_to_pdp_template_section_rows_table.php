<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pdp_template_section_rows', function (Blueprint $table): void {
            if (!Schema::hasColumn('pdp_template_section_rows', 'parent_row_id')) {
                $table->foreignId('parent_row_id')
                    ->nullable()
                    ->after('pdp_template_section_id')
                    ->constrained('pdp_template_section_rows')
                    ->cascadeOnDelete();

                $table->index(['pdp_template_section_id', 'parent_row_id'], 'pdp_template_rows_section_parent_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pdp_template_section_rows', function (Blueprint $table): void {
            if (Schema::hasColumn('pdp_template_section_rows', 'parent_row_id')) {
                $table->dropIndex('pdp_template_rows_section_parent_idx');
                $table->dropConstrainedForeignId('parent_row_id');
            }
        });
    }
};
