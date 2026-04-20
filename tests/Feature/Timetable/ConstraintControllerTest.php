<?php

namespace Tests\Feature\Timetable;

use App\Models\Subject;
use App\Models\Term;
use App\Models\Timetable\Timetable;
use App\Models\Timetable\TimetableConstraint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class ConstraintControllerTest extends TestCase {
    use RefreshDatabase;

    protected function setUp(): void {
        parent::setUp();

        Gate::define('manage-timetable', fn(User $user): bool => true);
    }

    public function test_save_subject_spread_accepts_max_lessons_per_day_and_persists_new_key(): void {
        $ctx = $this->createTimetableContext();
        $this->actingAs(User::factory()->create());

        $response = $this->postJson(route('timetable.constraints.save-subject-spread'), [
            'timetable_id' => $ctx['timetable']->id,
            'subject_id' => $ctx['subject']->id,
            'max_lessons_per_day' => 2,
            'distribute_across_cycle' => true,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['constraint_id']);

        $constraint = TimetableConstraint::query()
            ->where('timetable_id', $ctx['timetable']->id)
            ->where('constraint_type', TimetableConstraint::TYPE_SUBJECT_SPREAD)
            ->firstOrFail();

        $config = (array) $constraint->constraint_config;
        $this->assertSame(2, (int) ($config['max_lessons_per_day'] ?? 0));
        $this->assertArrayNotHasKey('max_periods_per_day', $config);
    }

    public function test_save_subject_spread_accepts_legacy_alias_and_normalizes_to_max_lessons_per_day(): void {
        $ctx = $this->createTimetableContext();
        $this->actingAs(User::factory()->create());

        $response = $this->postJson(route('timetable.constraints.save-subject-spread'), [
            'timetable_id' => $ctx['timetable']->id,
            'subject_id' => $ctx['subject']->id,
            'max_periods_per_day' => 3,
            'distribute_across_cycle' => false,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $constraint = TimetableConstraint::query()
            ->where('timetable_id', $ctx['timetable']->id)
            ->where('constraint_type', TimetableConstraint::TYPE_SUBJECT_SPREAD)
            ->firstOrFail();

        $config = (array) $constraint->constraint_config;
        $this->assertSame(3, (int) ($config['max_lessons_per_day'] ?? 0));
        $this->assertArrayNotHasKey('max_periods_per_day', $config);
        $this->assertFalse((bool) ($config['distribute_across_cycle'] ?? true));
    }

    /**
     * @return array{timetable: Timetable, subject: Subject}
     */
    private function createTimetableContext(): array {
        $owner = User::factory()->create();
        $term = Term::query()->first() ?? Term::create([
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
            'term' => 1,
            'year' => (int) now()->format('Y'),
            'closed' => false,
            'extension_days' => 0,
        ]);

        $subject = Subject::create([
            'abbrev' => 'SUB' . random_int(100, 999),
            'name' => 'Constraint Subject',
            'level' => 'Junior',
            'components' => false,
            'description' => '',
            'department' => 'General',
        ]);

        $timetable = Timetable::create([
            'term_id' => $term->id,
            'name' => 'Constraint Controller Test Timetable',
            'status' => Timetable::STATUS_DRAFT,
            'created_by' => $owner->id,
        ]);

        return [
            'timetable' => $timetable,
            'subject' => $subject,
        ];
    }
}
