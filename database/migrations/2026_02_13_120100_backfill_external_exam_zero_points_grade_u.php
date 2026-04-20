<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration{
    public function up(): void{
        DB::table('external_exam_results')
            ->whereNull('overall_grade')
            ->where('overall_points', 0)
            ->update([
                'overall_grade' => 'U',
                'updated_at' => now(),
            ]);
    }

    public function down(): void{
        // No-op: historical null grades are intentionally backfilled to U.
    }
};
