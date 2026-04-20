<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drop the DB-level unique index on syllabi (subject_id, grade_name, level).
 *
 * The index does not include deleted_at, so soft-deleted rows block re-creation of a
 * syllabus for the same subject/grade/level — violating SYLL-05 requirements.
 *
 * Uniqueness enforcement moves entirely to the app layer:
 *   StoreSyllabusRequest and UpdateSyllabusRequest both use Rule::unique(...)->whereNull('deleted_at')
 * which correctly ignores soft-deleted rows.
 */
return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('syllabi')) {
            return;
        }

        try {
            Schema::table('syllabi', function (Blueprint $table) {
                $table->dropUnique('uniq_syllabus_subject_grade_level');
            });
        } catch (\Throwable $e) {
            // Fresh installs create syllabi without the historical unique index.
        }
    }

    public function down(): void {
        if (!Schema::hasTable('syllabi') || !Schema::hasColumn('syllabi', 'grade_name')) {
            return;
        }

        try {
            Schema::table('syllabi', function (Blueprint $table) {
                $table->unique(['subject_id', 'grade_name', 'level'], 'uniq_syllabus_subject_grade_level');
            });
        } catch (\Throwable $e) {
            // Ignore rollback duplicate/index state drift.
        }
    }
};
