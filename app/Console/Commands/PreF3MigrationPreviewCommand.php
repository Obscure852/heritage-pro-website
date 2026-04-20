<?php

namespace App\Console\Commands;

use App\Models\SchoolSetup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PreF3MigrationPreviewCommand extends Command
{
    protected $signature = 'pref3:migration:preview';

    protected $description = 'Preview PRE_F3 migration readiness for the current installation.';

    public function handle(): int
    {
        $summary = [
            ['school_mode', SchoolSetup::schoolType() ?? 'not_configured'],
            ['grades_total', (string) DB::table('grades')->count()],
            ['primary_grades', (string) DB::table('grades')->whereIn('level', ['Pre-primary', 'Primary'])->count()],
            ['junior_grades', (string) DB::table('grades')->where('level', 'Junior')->count()],
            ['senior_grades', (string) DB::table('grades')->where('level', 'Senior')->count()],
            ['subjects_with_canonical_key', (string) DB::table('subjects')->whereNotNull('canonical_key')->count()],
            ['grade_subject_rows', (string) DB::table('grade_subject')->count()],
        ];

        $this->info('PRE_F3 migration readiness preview');
        $this->table(['Metric', 'Value'], $summary);

        return self::SUCCESS;
    }
}
