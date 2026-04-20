<?php

namespace Tests\Unit\Timetable;

use App\Models\Department;
use App\Models\Grade;
use App\Models\GradeSubject;
use App\Models\Klass;
use App\Models\KlassSubject;
use App\Models\Subject;
use App\Models\Term;
use App\Models\Timetable\Timetable;
use App\Models\Timetable\TimetableConstraint;
use App\Models\Timetable\TimetableSlot;
use App\Models\User;
use App\Services\Timetable\ConstraintValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ConstraintValidationServiceTest extends TestCase {
    use RefreshDatabase;

    public function test_subject_spread_counts_double_block_as_single_lesson(): void {
        $ctx = $this->createAcademicContext();

        TimetableConstraint::create([
            'timetable_id' => $ctx['timetable']->id,
            'constraint_type' => TimetableConstraint::TYPE_SUBJECT_SPREAD,
            'constraint_config' => [
                'subject_id' => $ctx['subject']->id,
                'max_lessons_per_day' => 1,
                'distribute_across_cycle' => true,
            ],
            'is_hard' => false,
            'is_active' => true,
        ]);

        $blockId = Str::uuid()->toString();
        TimetableSlot::create([
            'timetable_id' => $ctx['timetable']->id,
            'klass_subject_id' => $ctx['klassSubject']->id,
            'teacher_id' => $ctx['teacher']->id,
            'day_of_cycle' => 1,
            'period_number' => 1,
            'duration' => 2,
            'is_locked' => false,
            'block_id' => $blockId,
        ]);
        TimetableSlot::create([
            'timetable_id' => $ctx['timetable']->id,
            'klass_subject_id' => $ctx['klassSubject']->id,
            'teacher_id' => $ctx['teacher']->id,
            'day_of_cycle' => 1,
            'period_number' => 2,
            'duration' => 2,
            'is_locked' => false,
            'block_id' => $blockId,
        ]);

        $violations = (new ConstraintValidationService())->validateSlotPlacement(
            $ctx['timetable']->id,
            $ctx['teacher']->id,
            $ctx['klass']->id,
            1,
            4,
            $ctx['subject']->id
        );

        $spreadViolation = collect($violations)
            ->firstWhere('constraint_type', TimetableConstraint::TYPE_SUBJECT_SPREAD);

        $this->assertNotNull($spreadViolation);
        $this->assertSame('soft', $spreadViolation['type']);
        $this->assertStringContainsString('1 lesson(s)', $spreadViolation['message']);
    }

    public function test_subject_spread_counts_mixed_single_and_double_as_two_lessons(): void {
        $ctx = $this->createAcademicContext();

        TimetableConstraint::create([
            'timetable_id' => $ctx['timetable']->id,
            'constraint_type' => TimetableConstraint::TYPE_SUBJECT_SPREAD,
            'constraint_config' => [
                'subject_id' => $ctx['subject']->id,
                'max_lessons_per_day' => 2,
                'distribute_across_cycle' => true,
            ],
            'is_hard' => false,
            'is_active' => true,
        ]);

        $blockId = Str::uuid()->toString();
        TimetableSlot::create([
            'timetable_id' => $ctx['timetable']->id,
            'klass_subject_id' => $ctx['klassSubject']->id,
            'teacher_id' => $ctx['teacher']->id,
            'day_of_cycle' => 2,
            'period_number' => 1,
            'duration' => 2,
            'is_locked' => false,
            'block_id' => $blockId,
        ]);
        TimetableSlot::create([
            'timetable_id' => $ctx['timetable']->id,
            'klass_subject_id' => $ctx['klassSubject']->id,
            'teacher_id' => $ctx['teacher']->id,
            'day_of_cycle' => 2,
            'period_number' => 2,
            'duration' => 2,
            'is_locked' => false,
            'block_id' => $blockId,
        ]);
        TimetableSlot::create([
            'timetable_id' => $ctx['timetable']->id,
            'klass_subject_id' => $ctx['klassSubject']->id,
            'teacher_id' => $ctx['teacher']->id,
            'day_of_cycle' => 2,
            'period_number' => 4,
            'duration' => 1,
            'is_locked' => false,
            'block_id' => null,
        ]);

        $violations = (new ConstraintValidationService())->validateSlotPlacement(
            $ctx['timetable']->id,
            $ctx['teacher']->id,
            $ctx['klass']->id,
            2,
            5,
            $ctx['subject']->id
        );

        $spreadViolation = collect($violations)
            ->firstWhere('constraint_type', TimetableConstraint::TYPE_SUBJECT_SPREAD);

        $this->assertNotNull($spreadViolation);
        $this->assertStringContainsString('2 lesson(s)', $spreadViolation['message']);
        $this->assertStringContainsString('max: 2', $spreadViolation['message']);
    }

    public function test_subject_spread_does_not_warn_when_lessons_are_below_daily_limit(): void {
        $ctx = $this->createAcademicContext();

        TimetableConstraint::create([
            'timetable_id' => $ctx['timetable']->id,
            'constraint_type' => TimetableConstraint::TYPE_SUBJECT_SPREAD,
            'constraint_config' => [
                'subject_id' => $ctx['subject']->id,
                'max_lessons_per_day' => 3,
                'distribute_across_cycle' => true,
            ],
            'is_hard' => false,
            'is_active' => true,
        ]);

        $blockId = Str::uuid()->toString();
        TimetableSlot::create([
            'timetable_id' => $ctx['timetable']->id,
            'klass_subject_id' => $ctx['klassSubject']->id,
            'teacher_id' => $ctx['teacher']->id,
            'day_of_cycle' => 3,
            'period_number' => 1,
            'duration' => 2,
            'is_locked' => false,
            'block_id' => $blockId,
        ]);
        TimetableSlot::create([
            'timetable_id' => $ctx['timetable']->id,
            'klass_subject_id' => $ctx['klassSubject']->id,
            'teacher_id' => $ctx['teacher']->id,
            'day_of_cycle' => 3,
            'period_number' => 2,
            'duration' => 2,
            'is_locked' => false,
            'block_id' => $blockId,
        ]);
        TimetableSlot::create([
            'timetable_id' => $ctx['timetable']->id,
            'klass_subject_id' => $ctx['klassSubject']->id,
            'teacher_id' => $ctx['teacher']->id,
            'day_of_cycle' => 3,
            'period_number' => 5,
            'duration' => 1,
            'is_locked' => false,
            'block_id' => null,
        ]);

        $violations = (new ConstraintValidationService())->validateSlotPlacement(
            $ctx['timetable']->id,
            $ctx['teacher']->id,
            $ctx['klass']->id,
            3,
            6,
            $ctx['subject']->id
        );

        $spreadViolation = collect($violations)
            ->firstWhere('constraint_type', TimetableConstraint::TYPE_SUBJECT_SPREAD);

        $this->assertNull($spreadViolation);
    }

    /**
     * @return array{
     *   timetable: Timetable,
     *   klass: Klass,
     *   klassSubject: KlassSubject,
     *   teacher: User,
     *   subject: Subject
     * }
     */
    private function createAcademicContext(): array {
        $teacher = User::factory()->create();
        $term = Term::query()->first() ?? Term::create([
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
            'term' => 1,
            'year' => (int) now()->format('Y'),
            'closed' => false,
            'extension_days' => 0,
        ]);

        $grade = Grade::create([
            'sequence' => 1,
            'name' => 'F1',
            'promotion' => 'F2',
            'description' => 'Form 1',
            'level' => 'Junior',
            'active' => true,
            'term_id' => $term->id,
            'year' => (int) $term->year,
        ]);

        $klass = Klass::create([
            'name' => '1A',
            'user_id' => $teacher->id,
            'term_id' => $term->id,
            'grade_id' => $grade->id,
            'year' => (int) $term->year,
            'active' => true,
        ]);

        $departmentId = (int) (Department::query()->value('id') ?? 0);
        if ($departmentId <= 0) {
            $departmentId = (int) Department::create(['name' => 'Default'])->id;
        }

        $subject = Subject::create([
            'abbrev' => 'SUB' . random_int(100, 999),
            'name' => 'Spread Subject',
            'level' => 'Junior',
            'components' => false,
            'description' => '',
            'department' => 'General',
        ]);

        $gradeSubject = GradeSubject::create([
            'sequence' => 1,
            'grade_id' => $grade->id,
            'subject_id' => $subject->id,
            'department_id' => $departmentId,
            'term_id' => $term->id,
            'year' => (int) $term->year,
            'type' => '1',
            'mandatory' => true,
            'active' => true,
        ]);

        $klassSubject = KlassSubject::create([
            'klass_id' => $klass->id,
            'grade_subject_id' => $gradeSubject->id,
            'user_id' => $teacher->id,
            'term_id' => $term->id,
            'grade_id' => $grade->id,
            'year' => (int) $term->year,
            'active' => true,
        ]);

        $timetable = Timetable::create([
            'term_id' => $term->id,
            'name' => 'Constraint Validation Test Timetable',
            'status' => Timetable::STATUS_DRAFT,
            'created_by' => $teacher->id,
        ]);

        return [
            'timetable' => $timetable,
            'klass' => $klass,
            'klassSubject' => $klassSubject,
            'teacher' => $teacher,
            'subject' => $subject,
        ];
    }
}
