<?php

namespace App\Console\Commands;

use App\Models\SchoolSetup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class K12MigrationPreviewCommand extends Command
{
    protected $signature = 'k12:migration:preview';

    protected $description = 'Preview K12 migration readiness for the current installation.';

    public function handle(): int
    {
        $summary = [
            ['school_mode', SchoolSetup::schoolType() ?? 'not_configured'],
            ['grades_total', (string) $this->countRows('grades')],
            ['primary_grades', (string) $this->countRows('grades', ['level' => SchoolSetup::LEVEL_PRIMARY])],
            ['junior_grades', (string) $this->countRows('grades', ['level' => SchoolSetup::LEVEL_JUNIOR])],
            ['senior_grades', (string) $this->countRows('grades', ['level' => SchoolSetup::LEVEL_SENIOR])],
            ['subjects_with_canonical_key', (string) $this->countNotNull('subjects', 'canonical_key')],
            ['grade_subject_rows', (string) $this->countRows('grade_subject')],
            ['psle_rows', (string) $this->countRows('psle_grades')],
            ['jce_rows', (string) $this->countRows('jce_grades')],
            ['psle_value_addition_mappings', (string) $this->countRows('value_addition_subject_mappings', ['exam_type' => 'PSLE'])],
            ['jce_value_addition_mappings', (string) $this->countRows('value_addition_subject_mappings', ['exam_type' => 'JCE'])],
            ['admissions_rows', (string) $this->countRows('admissions')],
            ['senior_admission_academics_rows', (string) $this->countRows('senior_admission_academics')],
            ['migration_conflicts_open', (string) $this->countRows('migration_conflicts', ['status' => 'open'])],
        ];

        $this->info('K12 migration readiness preview');
        $this->table(['Metric', 'Value'], $summary);

        return self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $where
     */
    private function countRows(string $table, array $where = []): int
    {
        if (!Schema::hasTable($table)) {
            return 0;
        }

        $query = DB::table($table);

        foreach ($where as $column => $value) {
            $query->where($column, $value);
        }

        return $query->count();
    }

    private function countNotNull(string $table, string $column): int
    {
        if (!Schema::hasTable($table)) {
            return 0;
        }

        return DB::table($table)->whereNotNull($column)->count();
    }
}
