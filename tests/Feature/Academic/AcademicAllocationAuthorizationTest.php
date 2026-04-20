<?php

namespace Tests\Feature\Academic;

use App\Models\Department;
use App\Models\Grade;
use App\Models\GradeSubject;
use App\Models\Klass;
use App\Models\OptionalSubject;
use App\Models\Role;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Tests\TestCase;

class AcademicAllocationAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_class_teacher_can_access_class_allocations_for_owned_class_in_selected_term(): void
    {
        $context = $this->academicContext();
        $classTeacher = $this->createUserWithRoles(['Class Teacher']);
        $klass = $this->createKlass($classTeacher->id, $context['term'], $context['grade']);

        session(['selected_term_id' => $context['term']->id]);

        $this->assertTrue(Gate::forUser($classTeacher)->allows('access-class-allocations'));
        $this->assertTrue(Gate::forUser($classTeacher)->allows('access-academic'));
        $this->assertTrue(Gate::forUser($classTeacher)->allows('class-allocation-teacher', $klass));
        $this->assertFalse(Gate::forUser($classTeacher)->allows('access-optional'));
    }

    public function test_teacher_without_class_teacher_role_cannot_access_class_allocations_even_if_they_own_a_class(): void
    {
        $context = $this->academicContext();
        $teacher = $this->createUserWithRoles(['Teacher']);
        $klass = $this->createKlass($teacher->id, $context['term'], $context['grade']);

        session(['selected_term_id' => $context['term']->id]);

        $this->assertFalse(Gate::forUser($teacher)->allows('access-class-allocations'));
        $this->assertFalse(Gate::forUser($teacher)->allows('class-allocation-teacher', $klass));
        $this->assertFalse(Gate::forUser($teacher)->allows('access-academic'));
    }

    public function test_assigned_optional_teacher_can_access_optional_classes_without_class_allocations(): void
    {
        $context = $this->academicContext();
        $teacher = $this->createUserWithRoles(['Teacher']);
        $optionalSubject = $this->createOptionalSubject($teacher->id, null, $context);

        session(['selected_term_id' => $context['term']->id]);

        $this->assertTrue(Gate::forUser($teacher)->allows('access-optional'));
        $this->assertTrue(Gate::forUser($teacher)->allows('optional-teacher', $optionalSubject));
        $this->assertTrue(Gate::forUser($teacher)->allows('access-academic'));
        $this->assertFalse(Gate::forUser($teacher)->allows('access-class-allocations'));
    }

    public function test_optional_teacher_supervisor_inherits_optional_access_via_reporting_to(): void
    {
        $context = $this->academicContext();
        $supervisor = $this->createUserWithRoles(['Teacher']);
        $teacher = $this->createUserWithRoles(['Teacher'], ['reporting_to' => $supervisor->id]);
        $optionalSubject = $this->createOptionalSubject($teacher->id, null, $context);

        session(['selected_term_id' => $context['term']->id]);

        $this->assertTrue(Gate::forUser($supervisor)->allows('access-optional'));
        $this->assertTrue(Gate::forUser($supervisor)->allows('optional-teacher', $optionalSubject));
        $this->assertTrue(Gate::forUser($supervisor)->allows('access-academic'));
        $this->assertFalse(Gate::forUser($supervisor)->allows('access-class-allocations'));
    }

    public function test_class_teacher_keeps_class_allocation_access_when_selected_term_is_in_the_past(): void
    {
        $pastTerm = Term::create([
            'start_date' => now()->subMonths(6)->startOfMonth()->toDateString(),
            'end_date' => now()->subMonths(4)->endOfMonth()->toDateString(),
            'term' => 2,
            'year' => (int) now()->subYear()->format('Y'),
            'closed' => true,
        ]);
        $context = $this->academicContextForTerm($pastTerm);
        $classTeacher = $this->createUserWithRoles(['Class Teacher']);
        $klass = $this->createKlass($classTeacher->id, $context['term'], $context['grade']);

        session(['selected_term_id' => $pastTerm->id]);

        $this->assertTrue(Gate::forUser($classTeacher)->allows('access-class-allocations'));
        $this->assertTrue(Gate::forUser($classTeacher)->allows('class-allocation-teacher', $klass));
    }

    public function test_optional_supervisor_keeps_optional_access_when_selected_term_is_in_the_past(): void
    {
        $pastTerm = Term::create([
            'start_date' => now()->subMonths(6)->startOfMonth()->toDateString(),
            'end_date' => now()->subMonths(4)->endOfMonth()->toDateString(),
            'term' => 3,
            'year' => (int) now()->subYear()->format('Y'),
            'closed' => true,
        ]);
        $context = $this->academicContextForTerm($pastTerm);
        $supervisor = $this->createUserWithRoles(['Teacher']);
        $teacher = $this->createUserWithRoles(['Teacher'], ['reporting_to' => $supervisor->id]);
        $optionalSubject = $this->createOptionalSubject($teacher->id, null, $context);

        session(['selected_term_id' => $pastTerm->id]);

        $this->assertTrue(Gate::forUser($supervisor)->allows('access-optional'));
        $this->assertTrue(Gate::forUser($supervisor)->allows('optional-teacher', $optionalSubject));
    }

    private function createUserWithRoles(array $roles, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);

        $roleIds = collect($roles)->map(function (string $roleName) {
            return Role::firstOrCreate(['name' => $roleName], ['description' => $roleName])->id;
        });

        $user->roles()->syncWithoutDetaching($roleIds);

        return $user;
    }

    private function createKlass(int $teacherId, Term $term, Grade $grade): Klass
    {
        return Klass::create([
            'name' => 'Klass-' . Str::upper(Str::random(4)),
            'user_id' => $teacherId,
            'term_id' => $term->id,
            'grade_id' => $grade->id,
            'year' => $term->year,
            'active' => true,
        ]);
    }

    private function createOptionalSubject(int $teacherId, ?int $assistantId, array $context): OptionalSubject
    {
        return OptionalSubject::create([
            'name' => 'OPT-' . Str::upper(Str::random(4)),
            'grade_subject_id' => $context['gradeSubject']->id,
            'user_id' => $teacherId,
            'assistant_user_id' => $assistantId,
            'venue_id' => $context['venue']->id,
            'term_id' => $context['term']->id,
            'grade_id' => $context['grade']->id,
            'active' => true,
        ]);
    }

    private function academicContext(): array
    {
        $term = Term::query()->first() ?? Term::create([
            'start_date' => now()->subDays(10)->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
            'term' => 1,
            'year' => (int) now()->format('Y'),
            'closed' => false,
        ]);

        return $this->academicContextForTerm($term);
    }

    private function academicContextForTerm(Term $term): array
    {
        $grade = Grade::query()->where('term_id', $term->id)->first() ?? Grade::create([
            'sequence' => 1,
            'name' => 'F1-' . $term->id,
            'promotion' => 'F2',
            'description' => 'Form 1',
            'level' => 'Junior',
            'active' => true,
            'term_id' => $term->id,
            'year' => $term->year,
        ]);

        $department = Department::query()->first() ?? Department::create([
            'name' => 'General',
        ]);

        $subject = Subject::query()->first() ?? Subject::create([
            'abbrev' => 'SUB' . Str::upper(Str::random(2)),
            'name' => 'Subject ' . Str::upper(Str::random(4)),
            'level' => 'Junior',
            'components' => false,
            'description' => '',
            'department' => $department->name,
        ]);

        $gradeSubject = GradeSubject::query()
            ->where('term_id', $term->id)
            ->where('grade_id', $grade->id)
            ->first() ?? GradeSubject::create([
            'sequence' => 1,
            'grade_id' => $grade->id,
            'subject_id' => $subject->id,
            'department_id' => $department->id,
            'term_id' => $term->id,
            'year' => $term->year,
            'type' => false,
            'mandatory' => false,
            'active' => true,
        ]);

        $venue = Venue::query()->first() ?? Venue::create([
            'name' => 'Venue ' . Str::upper(Str::random(4)),
            'type' => 'Classroom',
            'capacity' => 30,
        ]);

        return [
            'term' => $term,
            'grade' => $grade,
            'gradeSubject' => $gradeSubject,
            'venue' => $venue,
        ];
    }
}
