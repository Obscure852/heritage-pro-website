<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $duplicates = DB::table('grade_subject')
            ->whereNull('deleted_at')
            ->select('term_id', 'grade_id', 'subject_id', DB::raw('COUNT(*) as duplicate_count'))
            ->groupBy('term_id', 'grade_id', 'subject_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicates->isNotEmpty()) {
            $summary = $duplicates
                ->take(5)
                ->map(function ($duplicate) {
                    return sprintf(
                        'term_id=%s grade_id=%s subject_id=%s count=%s',
                        $duplicate->term_id,
                        $duplicate->grade_id,
                        $duplicate->subject_id,
                        $duplicate->duplicate_count
                    );
                })
                ->implode('; ');

            throw new RuntimeException(
                'Cannot add unique constraint to grade_subject while duplicate active allocations exist: ' . $summary
            );
        }

        Schema::table('grade_subject', function (Blueprint $table) {
            $table->unique(
                ['term_id', 'grade_id', 'subject_id'],
                'grade_subject_term_grade_subject_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('grade_subject', function (Blueprint $table) {
            $table->dropUnique('grade_subject_term_grade_subject_unique');
        });
    }
};
