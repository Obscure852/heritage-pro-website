<?php

namespace Tests\Feature\Schemes;

use App\Models\Department;
use App\Models\Grade;
use App\Models\GradeSubject;
use App\Models\Klass;
use App\Models\KlassSubject;
use App\Models\Role;
use App\Models\Schemes\SchemeOfWork;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchemeVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_index_shows_only_own_schemes(): void
    {
        $teacher = $this->createUserWithRole('Teacher');
        $otherTeacher = $this->createUserWithRole('Teacher');

        $ownScheme = $this->createSchemeForTeacher($teacher, 'Teacher English', 'Class A');
        $otherScheme = $this->createSchemeForTeacher($otherTeacher, 'Other Science', 'Class B');

        $this->actingAs($teacher)
            ->get(route('schemes.index'))
            ->assertOk()
            ->assertSee('My Schemes')
            ->assertSee('Teacher English')
            ->assertSee('Class A')
            ->assertDontSee('Other Science')
            ->assertDontSee('Class B')
            ->assertDontSee($otherTeacher->full_name);

        $this->actingAs($teacher)
            ->get(route('schemes.show', $ownScheme))
            ->assertOk();

        $this->actingAs($teacher)
            ->get(route('schemes.show', $otherScheme))
            ->assertForbidden();
    }

    public function test_supervisor_index_shows_own_and_supervised_teacher_schemes_only(): void
    {
        $supervisor = $this->createUserWithRole('Teacher');
        $supervisedTeacher = $this->createUserWithRole('Teacher', ['reporting_to' => $supervisor->id]);
        $unrelatedTeacher = $this->createUserWithRole('Teacher');

        $supervisorScheme = $this->createSchemeForTeacher($supervisor, 'Supervisor Commerce', 'Class S');
        $supervisedScheme = $this->createSchemeForTeacher($supervisedTeacher, 'Supervised Biology', 'Class T');
        $unrelatedScheme = $this->createSchemeForTeacher($unrelatedTeacher, 'Unrelated Physics', 'Class U');

        $this->actingAs($supervisor)
            ->get(route('schemes.index'))
            ->assertOk()
            ->assertSee('My &amp; Supervised Schemes', false)
            ->assertSee($supervisor->full_name)
            ->assertSee($supervisedTeacher->full_name)
            ->assertSee('Supervisor Commerce')
            ->assertSee('Supervised Biology')
            ->assertDontSee('Unrelated Physics')
            ->assertDontSee($unrelatedTeacher->full_name);

        $this->actingAs($supervisor)
            ->get(route('schemes.show', $supervisorScheme))
            ->assertOk();

        $this->actingAs($supervisor)
            ->get(route('schemes.show', $supervisedScheme))
            ->assertOk();

        $this->actingAs($supervisor)
            ->get(route('schemes.show', $unrelatedScheme))
            ->assertForbidden();
    }

    public function test_hod_can_see_all_teacher_schemes_in_index_and_open_any_scheme(): void
    {
        $hod = $this->createUserWithRole('HOD');
        $teacherOne = $this->createUserWithRole('Teacher');
        $teacherTwo = $this->createUserWithRole('Teacher');

        $schemeOne = $this->createSchemeForTeacher($teacherOne, 'Global English', 'Class X');
        $schemeTwo = $this->createSchemeForTeacher($teacherTwo, 'Global Science', 'Class Y');

        $this->actingAs($hod)
            ->get(route('schemes.index'))
            ->assertOk()
            ->assertSee('All Schemes')
            ->assertSee($teacherOne->full_name)
            ->assertSee($teacherTwo->full_name)
            ->assertSee('Global English')
            ->assertSee('Global Science');

        $this->actingAs($hod)
            ->get(route('schemes.show', $schemeOne))
            ->assertOk();

        $this->actingAs($hod)
            ->get(route('schemes.show', $schemeTwo))
            ->assertOk();
    }

    public function test_administrator_can_see_all_teacher_schemes(): void
    {
        $admin = $this->createUserWithRole('Administrator');
        $teacherOne = $this->createUserWithRole('Teacher');
        $teacherTwo = $this->createUserWithRole('Teacher');

        $this->createSchemeForTeacher($teacherOne, 'Admin English', 'Class M');
        $this->createSchemeForTeacher($teacherTwo, 'Admin Science', 'Class N');

        $this->actingAs($admin)
            ->get(route('schemes.index'))
            ->assertOk()
            ->assertSee('All Schemes')
            ->assertSee('Admin English')
            ->assertSee('Admin Science')
            ->assertSee($teacherOne->full_name)
            ->assertSee($teacherTwo->full_name);
    }

    private function createUserWithRole(string $roleName, array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'area_of_work' => 'Teaching',
            'status' => 'Current',
            'active' => true,
        ], $attributes));

        $role = Role::query()->firstOrCreate(
            ['name' => $roleName],
            ['description' => $roleName]
        );

        $user->roles()->syncWithoutDetaching([$role->id]);

        return $user->fresh();
    }

    private function createSchemeForTeacher(User $teacher, string $subjectName, string $className): SchemeOfWork
    {
        ['term' => $term, 'grade' => $grade, 'department' => $department] = $this->academicContext();

        $subject = Subject::query()->create([
            'abbrev' => 'S' . (Subject::query()->count() + 1),
            'name' => $subjectName,
            'level' => 'Junior',
            'components' => false,
            'description' => $subjectName,
            'department' => $department->name,
        ]);

        $gradeSubject = GradeSubject::query()->create([
            'sequence' => GradeSubject::query()->count() + 1,
            'grade_id' => $grade->id,
            'subject_id' => $subject->id,
            'department_id' => $department->id,
            'term_id' => $term->id,
            'year' => $term->year,
            'type' => '1',
            'mandatory' => true,
            'active' => true,
        ]);

        $klass = Klass::query()->create([
            'name' => $className,
            'user_id' => $teacher->id,
            'term_id' => $term->id,
            'grade_id' => $grade->id,
            'year' => $term->year,
            'active' => true,
        ]);

        $klassSubject = KlassSubject::query()->create([
            'klass_id' => $klass->id,
            'grade_subject_id' => $gradeSubject->id,
            'user_id' => $teacher->id,
            'term_id' => $term->id,
            'grade_id' => $grade->id,
            'year' => $term->year,
            'active' => true,
        ]);

        return SchemeOfWork::query()->create([
            'klass_subject_id' => $klassSubject->id,
            'optional_subject_id' => null,
            'term_id' => $term->id,
            'teacher_id' => $teacher->id,
            'status' => 'draft',
            'total_weeks' => 10,
        ]);
    }

    /**
     * @return array{term: Term, grade: Grade, department: Department}
     */
    private function academicContext(): array
    {
        $term = Term::query()->firstOrCreate(
            ['term' => 1, 'year' => (int) now()->format('Y')],
            [
                'start_date' => now()->subMonth()->toDateString(),
                'end_date' => now()->addMonth()->toDateString(),
                'closed' => false,
                'extension_days' => 0,
            ]
        );

        $grade = Grade::query()->firstOrCreate(
            ['name' => 'F1', 'year' => $term->year, 'term_id' => $term->id],
            [
                'sequence' => 1,
                'promotion' => 'F2',
                'description' => 'Form 1',
                'level' => 'Junior',
                'active' => true,
            ]
        );

        $department = Department::query()->firstOrCreate([
            'name' => 'Languages',
        ]);

        return [
            'term' => $term,
            'grade' => $grade,
            'department' => $department,
        ];
    }
}
