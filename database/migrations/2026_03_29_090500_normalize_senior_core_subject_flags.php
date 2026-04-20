<?php

use App\Models\SchoolSetup;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $seniorSubjectIds = DB::table('subjects')
            ->where('level', SchoolSetup::LEVEL_SENIOR)
            ->pluck('id');

        if ($seniorSubjectIds->isEmpty()) {
            return;
        }

        DB::table('grade_subject')
            ->whereIn('subject_id', $seniorSubjectIds)
            ->update([
                'type' => 0,
                'mandatory' => false,
                'updated_at' => now(),
            ]);

        $englishSubjectIds = DB::table('subjects')
            ->where('level', SchoolSetup::LEVEL_SENIOR)
            ->where(function ($query) {
                $query->where('canonical_key', 'english')
                    ->orWhere(DB::raw('LOWER(name)'), 'english');
            })
            ->pluck('id');

        if ($englishSubjectIds->isEmpty()) {
            return;
        }

        DB::table('grade_subject')
            ->whereIn('subject_id', $englishSubjectIds)
            ->update([
                'type' => 1,
                'mandatory' => true,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        //
    }
};
