<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration{
    public function up(): void{
        // Keep matrix aligned with the expected maximum points for Merit.
        DB::table('overall_points_matrix')
            ->where('grade', 'Merit')
            ->where('max', '>', 63)
            ->update(['max' => 63, 'updated_at' => now()]);

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $this->addCheckConstraintIfMissing(
            'external_exam_results',
            'chk_external_exam_results_overall_points_range',
            'overall_points IS NULL OR (overall_points >= 0 AND overall_points <= 63)'
        );

        $this->addCheckConstraintIfMissing(
            'external_exam_subject_results',
            'chk_external_exam_subject_results_grade_points_range',
            'grade_points IS NULL OR (grade_points >= 0 AND grade_points <= 9)'
        );
    }

    public function down(): void{
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $this->dropCheckConstraintIfExists(
            'external_exam_results',
            'chk_external_exam_results_overall_points_range'
        );

        $this->dropCheckConstraintIfExists(
            'external_exam_subject_results',
            'chk_external_exam_subject_results_grade_points_range'
        );
    }

    private function addCheckConstraintIfMissing(string $table, string $constraint, string $expression): void{
        if ($this->checkConstraintExists($table, $constraint)) {
            return;
        }

        try {
            DB::statement("ALTER TABLE `{$table}` ADD CONSTRAINT `{$constraint}` CHECK ({$expression})");
        } catch (\Throwable $e) {
            Log::warning('Unable to add check constraint', [
                'table' => $table,
                'constraint' => $constraint,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function dropCheckConstraintIfExists(string $table, string $constraint): void{
        if (!$this->checkConstraintExists($table, $constraint)) {
            return;
        }

        try {
            DB::statement("ALTER TABLE `{$table}` DROP CHECK `{$constraint}`");
        } catch (\Throwable $e) {
            Log::warning('Unable to drop check constraint', [
                'table' => $table,
                'constraint' => $constraint,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function checkConstraintExists(string $table, string $constraint): bool{
        $database = DB::getDatabaseName();

        return DB::table('information_schema.table_constraints')
            ->where('constraint_schema', $database)
            ->where('table_name', $table)
            ->where('constraint_name', $constraint)
            ->exists();
    }
};
