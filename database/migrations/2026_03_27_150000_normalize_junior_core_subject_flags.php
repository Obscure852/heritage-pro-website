<?php

use App\Models\SchoolSetup;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $subjectIds = DB::table('subjects')
            ->where('level', SchoolSetup::LEVEL_JUNIOR)
            ->where(function ($query) {
                $query->whereIn('canonical_key', ['social_studies', 'agriculture', 'moral_education'])
                    ->orWhereIn(DB::raw('LOWER(name)'), ['social studies', 'agriculture', 'moral education']);
            })
            ->pluck('id');

        if ($subjectIds->isEmpty()) {
            return;
        }

        DB::table('grade_subject')
            ->whereIn('subject_id', $subjectIds)
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
