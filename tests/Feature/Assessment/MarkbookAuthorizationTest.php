<?php

namespace Tests\Feature\Assessment;

use App\Models\Department;
use App\Models\Grade;
use App\Models\GradeSubject;
use App\Models\Klass;
use App\Models\KlassSubject;
use App\Models\OptionalSubject;
use App\Models\Role;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use App\Models\Venue;
use App\Policies\KlassSubjectPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MarkbookAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private KlassSubjectPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = app(KlassSubjectPolicy::class);
    }

    public function test_enter_marks_allows_assigned_teacher(): void
    {
        $teacher = $this->createUserWithRole('Teacher');
        $klassSubject = $this->createKlassSubject($teacher->id);

        $this->assertTrue($this->policy->enterMarks($teacher, $klassSubject));
    }

    public function test_enter_marks_allows_assigned_assistant_teacher(): void
    {
        $teacher = $this->createUserWithRole('Teacher');
        $assistant = $this->createUserWithRole('Teacher');
        $klassSubject = $this->createKlassSubject($teacher->id, $assistant->id);

        $this->assertTrue($this->policy->enterMarks($assistant, $klassSubject));
    }

    public function test_enter_marks_allows_direct_supervisor(): void
    {
        $supervisor = $this->createUserWithRole('Teacher');
        $teacher = $this->createUserWithRole('Teacher', ['reporting_to' => $supervisor->id]);
        $klassSubject = $this->createKlassSubject($teacher->id);

        $this->assertTrue($this->policy->enterMarks($supervisor, $klassSubject));
    }

    public function test_enter_marks_denies_unrelated_teacher(): void
    {
        $teacher = $this->createUserWithRole('Teacher');
        $unrelated = $this->createUserWithRole('Teacher');
        $klassSubject = $this->createKlassSubject($teacher->id);

        $this->assertFalse($this->policy->enterMarks($unrelated, $klassSubject));
    }

    public function test_enter_marks_allows_markbook_admin_roles(): void
    {
        $teacher = $this->createUserWithRole('Teacher');
        $klassSubject = $this->createKlassSubject($teacher->id);

        foreach (['Administrator', 'Assessment Admin', 'Academic Admin', 'HOD'] as $roleName) {
            $adminLikeUser = $this->createUserWithRole($roleName);
            $this->assertTrue($this->policy->enterMarks($adminLikeUser, $klassSubject), "Failed for role {$roleName}");
        }
    }

    public function test_assess_options_allows_direct_supervisor_of_assigned_teacher(): void
    {
        $supervisor = $this->createUserWithRole('Teacher');
        $teacher = $this->createUserWithRole('Teacher', ['reporting_to' => $supervisor->id]);
        $optionalSubject = $this->createOptionalSubject($teacher->id);

        $this->assertTrue($this->policy->assessOptions($supervisor, $optionalSubject));
    }

    public function test_selected_subject_endpoint_returns_403_json_for_unauthorized_ajax_request(): void
    {
        $assignedTeacher = $this->createUserWithRole('Teacher');
        $unrelatedTeacher = $this->createUserWithRole('Teacher');
        $klassSubject = $this->createKlassSubject($assignedTeacher->id);

        $response = $this->actingAs($unrelatedTeacher)
            ->withSession(['selected_term_id' => $klassSubject->term_id])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('assessment.selected-subject', ['subjectId' => $klassSubject->id]));

        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'You are not authorized to access this class markbook.',
        ]);
    }

    private function createUserWithRole(string $roleName, array $attributes = []): User
    {
        $role = Role::firstOrCreate(['name' => $roleName], ['description' => $roleName]);
        $user = User::factory()->create($attributes);
        $user->roles()->attach($role->id);

        return $user;
    }

    private function createKlassSubject(int $teacherId, ?int $assistantId = null): KlassSubject
    {
        $context = $this->academicContext();

        $klass = Klass::create([
            'name' => 'Class-' . Str::upper(Str::random(4)),
            'user_id' => $teacherId,
            'term_id' => $context['term']->id,
            'grade_id' => $context['grade']->id,
            'year' => $context['term']->year,
            'active' => true,
        ]);

        return KlassSubject::create([
            'klass_id' => $klass->id,
            'grade_subject_id' => $context['gradeSubject']->id,
            'user_id' => $teacherId,
            'assistant_user_id' => $assistantId,
            'term_id' => $context['term']->id,
            'grade_id' => $context['grade']->id,
            'year' => $context['term']->year,
            'active' => true,
        ]);
    }

    private function createOptionalSubject(int $teacherId, ?int $assistantId = null): OptionalSubject
    {
        $context = $this->academicContext();

        return OptionalSubject::create([
            'name' => 'Optional-' . Str::upper(Str::random(4)),
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
        $grade = Grade::query()->first();
        if (!$grade) {
            $term = Term::create([
                'start_date' => now()->subDays(10)->toDateString(),
                'end_date' => now()->addDays(10)->toDateString(),
                'term' => 1,
                'year' => (int) now()->format('Y'),
                'closed' => false,
            ]);

            $grade = Grade::create([
                'sequence' => 1,
                'name' => 'F1',
                'promotion' => 'F2',
                'description' => 'Form 1',
                'level' => 'Junior',
                'active' => true,
                'term_id' => $term->id,
                'year' => $term->year,
            ]);
        }

        $term = Term::findOrFail($grade->term_id);
        $department = Department::query()->first() ?? Department::create(['name' => 'General']);
        $subject = Subject::query()->first() ?? Subject::create([
            'abbrev' => 'SUB' . Str::upper(Str::random(2)),
            'name' => 'Subject ' . Str::upper(Str::random(4)),
            'level' => 'Junior',
            'components' => false,
            'description' => '',
            'department' => $department->name,
        ]);

        $gradeSubject = GradeSubject::create([
            'sequence' => 1,
            'grade_id' => $grade->id,
            'subject_id' => $subject->id,
            'department_id' => $department->id,
            'term_id' => $term->id,
            'year' => $term->year,
            'type' => '1',
            'mandatory' => true,
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
