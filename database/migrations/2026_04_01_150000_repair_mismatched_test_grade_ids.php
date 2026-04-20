<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration {
    public function up(): void
    {
        $mismatchedTests = DB::table('tests as t')
            ->join('grade_subject as gs', 'gs.id', '=', 't.grade_subject_id')
            ->whereNull('t.deleted_at')
            ->whereNull('gs.deleted_at')
            ->whereColumn('t.grade_id', '!=', 'gs.grade_id')
            ->select('t.id', 'gs.grade_id')
            ->get();

        $updatedCount = 0;

        foreach ($mismatchedTests as $mismatchedTest) {
            $updatedCount += DB::table('tests')
                ->where('id', $mismatchedTest->id)
                ->update([
                    'grade_id' => $mismatchedTest->grade_id,
                    'updated_at' => now(),
                ]);
        }

        Log::info('Repaired mismatched test grade assignments.', [
            'updated_count' => $updatedCount,
        ]);
    }

    public function down(): void
    {
        // Data repair migration intentionally does not restore stale grade_id values.
    }
};
