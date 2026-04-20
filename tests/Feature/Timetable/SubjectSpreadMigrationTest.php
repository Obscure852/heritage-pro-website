<?php

namespace Tests\Feature\Timetable;

use App\Models\Term;
use App\Models\Timetable\Timetable;
use App\Models\Timetable\TimetableConstraint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubjectSpreadMigrationTest extends TestCase {
    use RefreshDatabase;

    public function test_migration_deactivates_active_legacy_subject_spread_constraints_only(): void {
        $timetable = $this->createTimetable();

        $legacy = TimetableConstraint::create([
            'timetable_id' => $timetable->id,
            'constraint_type' => TimetableConstraint::TYPE_SUBJECT_SPREAD,
            'constraint_config' => [
                'subject_id' => 10,
                'max_periods_per_day' => 2,
                'distribute_across_cycle' => true,
            ],
            'is_hard' => false,
            'is_active' => true,
        ]);

        $modern = TimetableConstraint::create([
            'timetable_id' => $timetable->id,
            'constraint_type' => TimetableConstraint::TYPE_SUBJECT_SPREAD,
            'constraint_config' => [
                'subject_id' => 11,
                'max_lessons_per_day' => 2,
                'distribute_across_cycle' => true,
            ],
            'is_hard' => false,
            'is_active' => true,
        ]);

        $beforeCount = TimetableConstraint::query()
            ->where('constraint_type', TimetableConstraint::TYPE_SUBJECT_SPREAD)
            ->count();

        $migration = require database_path('migrations/2026_02_26_190000_reset_legacy_subject_spread_constraints.php');
        $migration->up();

        $legacy->refresh();
        $modern->refresh();

        $this->assertFalse($legacy->is_active);
        $this->assertTrue($modern->is_active);

        $afterCount = TimetableConstraint::query()
            ->where('constraint_type', TimetableConstraint::TYPE_SUBJECT_SPREAD)
            ->count();

        $this->assertSame($beforeCount, $afterCount);
    }

    private function createTimetable(): Timetable {
        $owner = User::factory()->create();
        $term = Term::query()->first() ?? Term::create([
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
            'term' => 1,
            'year' => (int) now()->format('Y'),
            'closed' => false,
            'extension_days' => 0,
        ]);

        return Timetable::create([
            'term_id' => $term->id,
            'name' => 'Subject Spread Migration Test Timetable',
            'status' => Timetable::STATUS_DRAFT,
            'created_by' => $owner->id,
        ]);
    }
}
