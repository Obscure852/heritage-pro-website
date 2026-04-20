<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Converts syllabi.grade_name (single string) to syllabi.grades (JSON array)
 * so one syllabus can cover multiple grades (e.g. F1, F2, F3).
 */
return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('syllabi') || !Schema::hasColumn('syllabi', 'grade_name')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        // 1. Add the new JSON column (nullable temporarily for migration)
        if (!Schema::hasColumn('syllabi', 'grades')) {
            Schema::table('syllabi', function (Blueprint $table) {
                $table->json('grades')->nullable()->after('subject_id');
            });
        }

        // 2. Migrate existing data: wrap grade_name in a JSON array
        if ($driver === 'mysql') {
            DB::statement("UPDATE syllabi SET grades = JSON_ARRAY(grade_name)");
        } else {
            DB::table('syllabi')
                ->select(['id', 'grade_name'])
                ->orderBy('id')
                ->get()
                ->each(function ($row): void {
                    DB::table('syllabi')
                        ->where('id', $row->id)
                        ->update(['grades' => json_encode([$row->grade_name])]);
                });
        }

        // 3. Drop the old column (unique constraint already removed by prior migration)
        Schema::table('syllabi', function (Blueprint $table) {
            $table->dropColumn('grade_name');
        });

        // 5. Make grades non-nullable now that all rows have data
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE syllabi MODIFY grades JSON NOT NULL");
        } else {
            try {
                Schema::table('syllabi', function (Blueprint $table) {
                    $table->json('grades')->nullable(false)->change();
                });
            } catch (\Throwable) {
                // Best effort on SQLite/other test drivers.
            }
        }
    }

    public function down(): void {
        if (!Schema::hasTable('syllabi') || !Schema::hasColumn('syllabi', 'grades')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        // 1. Re-add grade_name
        if (!Schema::hasColumn('syllabi', 'grade_name')) {
            Schema::table('syllabi', function (Blueprint $table) {
                $table->string('grade_name', 30)->after('subject_id')->default('');
            });
        }

        // 2. Copy first element of JSON array back to grade_name
        if ($driver === 'mysql') {
            DB::statement("UPDATE syllabi SET grade_name = JSON_UNQUOTE(JSON_EXTRACT(grades, '$[0]'))");
        } else {
            DB::table('syllabi')
                ->select(['id', 'grades'])
                ->orderBy('id')
                ->get()
                ->each(function ($row): void {
                    $grades = json_decode((string) $row->grades, true);
                    $gradeName = is_array($grades) && !empty($grades) ? (string) $grades[0] : '';

                    DB::table('syllabi')
                        ->where('id', $row->id)
                        ->update(['grade_name' => $gradeName]);
                });
        }

        // 3. Drop grades column
        Schema::table('syllabi', function (Blueprint $table) {
            $table->dropColumn('grades');
        });

        // 4. Restore unique constraint
        try {
            Schema::table('syllabi', function (Blueprint $table) {
                $table->unique(['subject_id', 'grade_name', 'level'], 'uniq_syllabus_subject_grade_level');
            });
        } catch (\Throwable $e) {
            // Ignore rollback duplicate/index state drift.
        }

        // 5. Remove the default
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE syllabi MODIFY grade_name VARCHAR(30) NOT NULL");
        } else {
            try {
                Schema::table('syllabi', function (Blueprint $table) {
                    $table->string('grade_name', 30)->nullable(false)->change();
                });
            } catch (\Throwable) {
                // Best effort on SQLite/other test drivers.
            }
        }
    }
};
