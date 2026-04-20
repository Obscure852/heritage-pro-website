<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('students')
            ->whereIn('status', ['Active', 'active'])
            ->update([
                'status' => 'Current',
                'updated_at' => now(),
            ]);

        DB::table('student_term')
            ->whereIn('status', ['Active', 'active'])
            ->update([
                'status' => 'Current',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        //
    }
};
