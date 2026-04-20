<?php

namespace Tests\Feature\Assessment;

use App\Http\Middleware\BlockNonAfricanCountries;
use App\Http\Middleware\EnsureProfileComplete;
use App\Models\Department;
use App\Models\Grade;
use App\Models\GradeSubject;
use App\Models\Klass;
use App\Models\KlassSubject;
use App\Models\Logging;
use App\Models\OptionalSubject;
use App\Models\Role;
use App\Models\Sponsor;
use App\Models\Student;
use App\Models\StudentTest;
use App\Models\Subject;
use App\Models\Term;
use App\Models\Test;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class MarkbookAuditLoggingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            EnsureProfileComplete::class,
            BlockNonAfricanCountries::class,
        ]);
    }

    public function test_class_markbook_save_creates_compact_marks_saved_log(): void
    {
        $teacher = $this->createUserWithRole('Teacher');
        $context = $this->academicContext();
        $klass = $this->createKlass('F1A', $teacher->id, $context);
        $klassSubject = $this->createKlassSubject($klass, $teacher->id, $context);
        $students = [
            $this->createStudent('Neo', 'One', $context),
            $this->createStudent('Neo', 'Two', $context),
        ];

        foreach ($students as $student) {
            $this->enrollStudentInKlass($student, $klass, $context);
        }

        $tests = [
            $this->createTest($context, 'CA', 1),
            $this->createTest($context, 'CA', 2),
        ];

        $payload = $this->klassPayload($klassSubject, $context, $students, [
            $students[0]->id => [
                $tests[0]->id => 55,
                $tests[1]->id => 61,
            ],
            $students[1]->id => [
                $tests[0]->id => 72,
                $tests[1]->id => 68,
            ],
        ]);

        $baselineMarksSavedCount = $this->marksSavedLogs()->count();

        $response = $this->actingAs($teacher)
            ->withSession(['selected_term_id' => $context['term']->id])
            ->from('/assessment/markbook')
            ->post(route('assessment.update-marks'), $payload);

        $response
            ->assertRedirect('/assessment/markbook')
            ->assertSessionHas('message', 'Marks updated successfully!');

        $this->assertSame($baselineMarksSavedCount + 1, $this->marksSavedLogs()->count());

        $log = $this->marksSavedLogs()->latest('id')->firstOrFail();
        $input = json_decode($log->input, true);
        $changes = json_decode($log->changes, true);
        $data = $changes['data'] ?? [];

        $this->assertSame('Marks Saved', $changes['action'] ?? null);
        $this->assertSame([
            'scope_type' => 'klass_subject',
            'scope_id' => $klassSubject->id,
            'subject' => $context['gradeSubject']->id,
            'term' => $context['term']->id,
            'year' => $context['term']->year,
        ], $input);
        $this->assertSame('klass_subject', $data['scope_type'] ?? null);
        $this->assertSame($klassSubject->id, $data['scope_id'] ?? null);
        $this->assertSame(2, $data['students_touched'] ?? null);
        $this->assertSame(2, $data['tests_affected'] ?? null);
        $this->assertSame(4, $data['entered_count'] ?? null);
        $this->assertSame(0, $data['updated_count'] ?? null);
        $this->assertSame(0, $data['cleared_count'] ?? null);
        $this->assertContains('Class: F1A', $data['summary_badges'] ?? []);
        $this->assertContains('Subject: Mathematics', $data['summary_badges'] ?? []);
        $this->assertContains('Tests: CA 1, CA 2', $data['summary_badges'] ?? []);
        $this->assertArrayNotHasKey('students', $data);
        $this->assertArrayNotHasKey('scores', $data);
        $this->assertStringNotContainsString('"student_id"', $log->changes);
        $this->assertStringNotContainsString('"score"', $log->changes);
    }

    public function test_second_save_tracks_updates_and_clears_without_new_entries(): void
    {
        $teacher = $this->createUserWithRole('Teacher');
        $context = $this->academicContext();
        $klass = $this->createKlass('F1B', $teacher->id, $context);
        $klassSubject = $this->createKlassSubject($klass, $teacher->id, $context);
        $student = $this->createStudent('Ari', 'Stone', $context);

        $this->enrollStudentInKlass($student, $klass, $context);

        $tests = [
            $this->createTest($context, 'CA', 1),
            $this->createTest($context, 'CA', 2),
        ];

        StudentTest::create([
            'student_id' => $student->id,
            'test_id' => $tests[0]->id,
            'score' => 40,
            'percentage' => 40,
            'grade' => null,
            'points' => 0,
        ]);

        StudentTest::create([
            'student_id' => $student->id,
            'test_id' => $tests[1]->id,
            'score' => 65,
            'percentage' => 65,
            'grade' => null,
            'points' => 0,
        ]);

        $payload = $this->klassPayload($klassSubject, $context, [$student], [
            $student->id => [
                $tests[0]->id => 44,
                $tests[1]->id => '',
            ],
        ]);

        $baselineMarksSavedCount = $this->marksSavedLogs()->count();

        $this->actingAs($teacher)
            ->withSession(['selected_term_id' => $context['term']->id])
            ->from('/assessment/markbook')
            ->post(route('assessment.update-marks'), $payload)
            ->assertRedirect('/assessment/markbook');

        $this->assertSame($baselineMarksSavedCount + 1, $this->marksSavedLogs()->count());

        $log = $this->marksSavedLogs()->latest('id')->firstOrFail();
        $data = json_decode($log->changes, true)['data'] ?? [];

        $this->assertSame(0, $data['entered_count'] ?? null);
        $this->assertSame(1, $data['updated_count'] ?? null);
        $this->assertSame(1, $data['cleared_count'] ?? null);
    }

    public function test_optional_subject_save_logs_optional_and_compact_class_summary(): void
    {
        $teacher = $this->createUserWithRole('Teacher');
        $context = $this->academicContext();
        $klassA = $this->createKlass('F1C', $teacher->id, $context);
        $klassB = $this->createKlass('F1D', $teacher->id, $context);
        $optionalSubject = $this->createOptionalSubject('Agriculture Group A', $teacher->id, $context);
        $students = [
            $this->createStudent('Lebo', 'North', $context),
            $this->createStudent('Lebo', 'South', $context),
        ];

        $this->enrollStudentInKlass($students[0], $klassA, $context);
        $this->enrollStudentInKlass($students[1], $klassB, $context);
        $this->attachStudentToOptionalSubject($students[0], $optionalSubject, $klassA, $context);
        $this->attachStudentToOptionalSubject($students[1], $optionalSubject, $klassB, $context);

        $test = $this->createTest($context, 'CA', 1);

        $payload = [
            'scope_type' => 'optional_subject',
            'scope_id' => $optionalSubject->id,
            'subject' => $context['gradeSubject']->id,
            'term' => $context['term']->id,
            'year' => $context['term']->year,
            'students' => [
                $students[0]->id => [
                    'tests' => [
                        $test->id => ['out_of' => 100, 'score' => 49],
                    ],
                ],
                $students[1]->id => [
                    'tests' => [
                        $test->id => ['out_of' => 100, 'score' => 53],
                    ],
                ],
            ],
        ];

        $baselineMarksSavedCount = $this->marksSavedLogs()->count();

        $this->actingAs($teacher)
            ->withSession(['selected_term_id' => $context['term']->id])
            ->from('/assessment/markbook/options')
            ->post(route('assessment.update-marks'), $payload)
            ->assertRedirect('/assessment/markbook/options');

        $this->assertSame($baselineMarksSavedCount + 1, $this->marksSavedLogs()->count());

        $log = $this->marksSavedLogs()->latest('id')->firstOrFail();
        $data = json_decode($log->changes, true)['data'] ?? [];

        $this->assertSame('optional_subject', $data['scope_type'] ?? null);
        $this->assertContains('Optional: Agriculture Group A', $data['summary_badges'] ?? []);
        $this->assertContains('Classes: F1C, F1D', $data['summary_badges'] ?? []);
    }

    public function test_unauthorized_mark_save_does_not_create_a_log(): void
    {
        $assignedTeacher = $this->createUserWithRole('Teacher');
        $otherTeacher = $this->createUserWithRole('Teacher');
        $context = $this->academicContext();
        $klass = $this->createKlass('F1E', $assignedTeacher->id, $context);
        $otherKlass = $this->createKlass('F1F', $otherTeacher->id, $context);
        $klassSubject = $this->createKlassSubject($klass, $assignedTeacher->id, $context);
        $this->createKlassSubject($otherKlass, $otherTeacher->id, $context);

        $student = $this->createStudent('Pako', 'West', $context);
        $this->enrollStudentInKlass($student, $klass, $context);

        $test = $this->createTest($context, 'CA', 1);

        $payload = $this->klassPayload($klassSubject, $context, [$student], [
            $student->id => [
                $test->id => 77,
            ],
        ]);

        $baselineMarksSavedCount = $this->marksSavedLogs()->count();

        $this->actingAs($otherTeacher)
            ->withSession(['selected_term_id' => $context['term']->id])
            ->post(route('assessment.update-marks'), $payload)
            ->assertForbidden();

        $this->assertSame($baselineMarksSavedCount, $this->marksSavedLogs()->count());
    }

    public function test_no_op_save_does_not_create_a_log(): void
    {
        $teacher = $this->createUserWithRole('Teacher');
        $context = $this->academicContext();
        $klass = $this->createKlass('F1G', $teacher->id, $context);
        $klassSubject = $this->createKlassSubject($klass, $teacher->id, $context);
        $student = $this->createStudent('Rra', 'Quiet', $context);
        $this->enrollStudentInKlass($student, $klass, $context);

        $test = $this->createTest($context, 'CA', 1);

        StudentTest::create([
            'student_id' => $student->id,
            'test_id' => $test->id,
            'score' => 88,
            'percentage' => 88,
            'grade' => null,
            'points' => 0,
        ]);

        $payload = $this->klassPayload($klassSubject, $context, [$student], [
            $student->id => [
                $test->id => 88,
            ],
        ]);

        $baselineMarksSavedCount = $this->marksSavedLogs()->count();

        $this->actingAs($teacher)
            ->withSession(['selected_term_id' => $context['term']->id])
            ->from('/assessment/markbook')
            ->post(route('assessment.update-marks'), $payload)
            ->assertRedirect('/assessment/markbook');

        $this->assertSame($baselineMarksSavedCount, $this->marksSavedLogs()->count());
    }

    private function academicContext(): array
    {
        $grade = Grade::query()->where('level', 'Junior')->first();

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

        $department = Department::query()->first() ?? Department::create(['name' => 'Sciences']);

        $subject = Subject::query()
            ->where('name', 'Mathematics')
            ->where('level', 'Junior')
            ->first()
            ?? Subject::create([
                'abbrev' => 'MTH',
                'name' => 'Mathematics',
                'level' => 'Junior',
                'components' => false,
                'description' => '',
                'department' => $department->name,
            ]);

        $gradeSubject = GradeSubject::query()
            ->where('grade_id', $grade->id)
            ->where('subject_id', $subject->id)
            ->where('term_id', $term->id)
            ->first()
            ?? GradeSubject::create([
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

        $sponsor = Sponsor::query()->first() ?? Sponsor::create([
            'connect_id' => 1,
            'first_name' => 'Sponsor',
            'last_name' => Str::upper(Str::random(4)),
            'status' => 'Current',
            'password' => bcrypt('password'),
        ]);

        return [
            'term' => $term,
            'grade' => $grade,
            'department' => $department,
            'subject' => $subject,
            'gradeSubject' => $gradeSubject,
            'venue' => $venue,
            'sponsor' => $sponsor,
        ];
    }

    private function createUserWithRole(string $roleName, array $attributes = []): User
    {
        $role = Role::firstOrCreate(['name' => $roleName], ['description' => $roleName]);
        $user = User::factory()->create(array_merge([
            'area_of_work' => 'Teaching',
            'status' => 'Current',
            'active' => true,
        ], $attributes));
        $user->roles()->attach($role->id);

        return $user;
    }

    private function createKlass(string $name, int $teacherId, array $context): Klass
    {
        return Klass::create([
            'name' => $name,
            'user_id' => $teacherId,
            'term_id' => $context['term']->id,
            'grade_id' => $context['grade']->id,
            'year' => $context['term']->year,
        ]);
    }

    private function createKlassSubject(Klass $klass, int $teacherId, array $context): KlassSubject
    {
        return KlassSubject::create([
            'klass_id' => $klass->id,
            'grade_subject_id' => $context['gradeSubject']->id,
            'user_id' => $teacherId,
            'term_id' => $context['term']->id,
            'grade_id' => $context['grade']->id,
            'year' => $context['term']->year,
            'active' => true,
        ]);
    }

    private function createOptionalSubject(string $name, int $teacherId, array $context): OptionalSubject
    {
        return OptionalSubject::create([
            'name' => $name,
            'grade_subject_id' => $context['gradeSubject']->id,
            'user_id' => $teacherId,
            'venue_id' => $context['venue']->id,
            'term_id' => $context['term']->id,
            'grade_id' => $context['grade']->id,
            'active' => true,
        ]);
    }

    private function createStudent(string $firstName, string $lastName, array $context): Student
    {
        return Student::create([
            'connect_id' => $context['sponsor']->id,
            'sponsor_id' => $context['sponsor']->id,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'gender' => 'M',
            'date_of_birth' => '2012-01-01',
            'nationality' => 'Botswana',
            'id_number' => (string) random_int(10000000, 99999999),
            'status' => 'Current',
            'year' => $context['term']->year,
            'password' => bcrypt('password'),
        ]);
    }

    private function enrollStudentInKlass(Student $student, Klass $klass, array $context): void
    {
        DB::table('student_term')->insert([
            'student_id' => $student->id,
            'term_id' => $context['term']->id,
            'grade_id' => $context['grade']->id,
            'year' => $context['term']->year,
            'status' => 'Current',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('klass_student')->insert([
            'student_id' => $student->id,
            'klass_id' => $klass->id,
            'term_id' => $context['term']->id,
            'grade_id' => $context['grade']->id,
            'year' => $context['term']->year,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function attachStudentToOptionalSubject(Student $student, OptionalSubject $optionalSubject, Klass $klass, array $context): void
    {
        DB::table('student_optional_subjects')->insert([
            'student_id' => $student->id,
            'optional_subject_id' => $optionalSubject->id,
            'term_id' => $context['term']->id,
            'klass_id' => $klass->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createTest(array $context, string $type, int $sequence): Test
    {
        return Test::create([
            'sequence' => $sequence,
            'name' => $type === 'CA' ? 'CA ' . $sequence : 'Exam',
            'abbrev' => $type === 'CA' ? 'CA' . $sequence : 'EXAM',
            'grade_subject_id' => $context['gradeSubject']->id,
            'term_id' => $context['term']->id,
            'grade_id' => $context['grade']->id,
            'out_of' => 100,
            'year' => $context['term']->year,
            'type' => $type,
        ]);
    }

    private function klassPayload(KlassSubject $klassSubject, array $context, array $students, array $scores): array
    {
        $studentPayload = [];

        foreach ($students as $student) {
            $testPayload = [];

            foreach ($scores[$student->id] as $testId => $score) {
                $testPayload[$testId] = [
                    'out_of' => 100,
                    'score' => $score,
                ];
            }

            $studentPayload[$student->id] = [
                'tests' => $testPayload,
            ];
        }

        return [
            'scope_type' => 'klass_subject',
            'scope_id' => $klassSubject->id,
            'subject' => $context['gradeSubject']->id,
            'term' => $context['term']->id,
            'year' => $context['term']->year,
            'students' => $studentPayload,
        ];
    }

    private function marksSavedLogs()
    {
        return Logging::query()->where('changes', 'like', '%"action":"Marks Saved"%');
    }
}
