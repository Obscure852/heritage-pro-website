<?php

namespace Tests\Feature\Schemes;

use App\Mail\Schemes\SchemeDocumentMail;
use App\Models\Department;
use App\Models\Grade;
use App\Models\GradeSubject;
use App\Models\Klass;
use App\Models\KlassSubject;
use App\Models\Role;
use App\Models\Schemes\SchemeOfWork;
use App\Models\SchoolSetup;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SchemeDocumentEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_view_prefills_supervisor_and_deputy_head_emails(): void
    {
        $supervisor = $this->createUserWithRole('Teacher', [
            'email' => 'supervisor@example.com',
        ]);

        $teacher = $this->createUserWithRole('Teacher', [
            'email' => 'teacher@example.com',
            'reporting_to' => $supervisor->id,
        ]);

        $deputy = $this->createUserWithRole('Administrator', [
            'email' => 'deputy@example.com',
            'position' => 'Deputy Principal',
        ]);

        $scheme = $this->createSchemeForTeacher($teacher, 'Science', 'Form 1 Blue');

        $this->actingAs($teacher)
            ->get(route('schemes.document', $scheme))
            ->assertOk()
            ->assertSee('supervisor@example.com')
            ->assertSee('deputy@example.com');
    }

    public function test_teacher_can_email_scheme_document_to_multiple_recipients(): void
    {
        Mail::fake();

        SchoolSetup::query()->create([
            'school_name' => 'Heritage Junior',
            'type' => 'Junior',
            'email_address' => 'info@heritage.test',
            'physical_address' => 'Gaborone',
        ]);

        $supervisor = $this->createUserWithRole('Teacher', [
            'email' => 'supervisor@example.com',
        ]);

        $teacher = $this->createUserWithRole('Teacher', [
            'email' => 'teacher@example.com',
            'reporting_to' => $supervisor->id,
        ]);

        $deputy = $this->createUserWithRole('Administrator', [
            'email' => 'deputy@example.com',
            'position' => 'Deputy Principal',
        ]);

        $scheme = $this->createSchemeForTeacher($teacher, 'Science', 'Form 1 Blue');

        $response = $this->actingAs($teacher)->post(route('schemes.email-document', $scheme), [
            'recipients' => "supervisor@example.com,\ndeputy@example.com",
            'subject' => 'Science scheme of work',
            'message' => 'Please review the latest scheme document.',
        ]);

        $response
            ->assertRedirect(route('schemes.document', $scheme))
            ->assertSessionHas('success', 'Scheme document emailed successfully.');

        Mail::assertSent(SchemeDocumentMail::class, function (SchemeDocumentMail $mail) use ($supervisor, $deputy, $teacher): bool {
            return $mail->hasTo($supervisor->email)
                && $mail->hasTo($deputy->email)
                && $mail->sender->is($teacher)
                && $mail->mailSubject === 'Science scheme of work'
                && $mail->messageNote === 'Please review the latest scheme document.';
        });
    }

    private function createUserWithRole(string $roleName, array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'area_of_work' => 'Teaching',
            'status' => 'Current',
            'active' => true,
            'position' => $attributes['position'] ?? 'Teacher',
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
            'name' => 'Sciences',
        ]);

        return [
            'term' => $term,
            'grade' => $grade,
            'department' => $department,
        ];
    }
}
