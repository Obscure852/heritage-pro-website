<?php

use App\Models\SchoolSetup;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $juniorCoreSubjectIds = $this->subjectIds(
            SchoolSetup::LEVEL_JUNIOR,
            ['social_studies', 'agriculture', 'moral_education'],
            ['social studies', 'agriculture', 'moral education']
        );

        if ($juniorCoreSubjectIds->isNotEmpty()) {
            DB::table('grade_subject')
                ->whereIn('subject_id', $juniorCoreSubjectIds)
                ->update([
                    'type' => 1,
                    'mandatory' => false,
                    'updated_at' => $now,
                ]);
        }

        $juniorSetswanaSubjectIds = $this->subjectIds(
            SchoolSetup::LEVEL_JUNIOR,
            ['setswana'],
            ['setswana']
        );

        if ($juniorSetswanaSubjectIds->isNotEmpty()) {
            DB::table('grade_subject')
                ->whereIn('subject_id', $juniorSetswanaSubjectIds)
                ->update([
                    'type' => 0,
                    'mandatory' => true,
                    'updated_at' => $now,
                ]);
        }

        $seniorSubjectIds = DB::table('subjects')
            ->where('level', SchoolSetup::LEVEL_SENIOR)
            ->pluck('id');

        if ($seniorSubjectIds->isNotEmpty()) {
            DB::table('grade_subject')
                ->whereIn('subject_id', $seniorSubjectIds)
                ->update([
                    'type' => 0,
                    'mandatory' => false,
                    'updated_at' => $now,
                ]);
        }

        $seniorEnglishSubjectIds = $this->subjectIds(
            SchoolSetup::LEVEL_SENIOR,
            ['english'],
            ['english']
        );

        if ($seniorEnglishSubjectIds->isNotEmpty()) {
            DB::table('grade_subject')
                ->whereIn('subject_id', $seniorEnglishSubjectIds)
                ->update([
                    'type' => 1,
                    'mandatory' => true,
                    'updated_at' => $now,
                ]);
        }
    }

    public function down(): void
    {
        //
    }

    private function subjectIds(string $level, array $canonicalKeys, array $names)
    {
        return DB::table('subjects')
            ->where('level', $level)
            ->where(function ($query) use ($canonicalKeys, $names) {
                if ($canonicalKeys !== []) {
                    $query->whereIn('canonical_key', $canonicalKeys);
                }

                if ($names !== []) {
                    $query->orWhereIn(DB::raw('LOWER(name)'), $names);
                }
            })
            ->pluck('id');
    }
};
