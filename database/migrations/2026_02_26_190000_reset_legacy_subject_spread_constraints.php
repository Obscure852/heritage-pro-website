<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        $legacyIds = [];

        $constraints = DB::table('timetable_constraints')
            ->where('constraint_type', 'subject_spread')
            ->where('is_active', true)
            ->get(['id', 'constraint_config']);

        foreach ($constraints as $constraint) {
            $rawConfig = $constraint->constraint_config;
            $config = is_array($rawConfig) ? $rawConfig : json_decode((string) $rawConfig, true);
            if (!is_array($config)) {
                continue;
            }

            $hasLegacyKey = array_key_exists('max_periods_per_day', $config);
            $hasLessonsKey = array_key_exists('max_lessons_per_day', $config);
            if ($hasLegacyKey && !$hasLessonsKey) {
                $legacyIds[] = (int) $constraint->id;
            }
        }

        if (!empty($legacyIds)) {
            DB::table('timetable_constraints')
                ->whereIn('id', $legacyIds)
                ->update([
                    'is_active' => false,
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void {
        // Intentionally no-op: previously-active legacy rows are not reactivated automatically.
    }
};
