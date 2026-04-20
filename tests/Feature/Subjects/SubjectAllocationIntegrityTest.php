<?php

namespace Tests\Feature\Subjects;

use App\Helpers\TermHelper;
use App\Http\Middleware\BlockNonAfricanCountries;
use App\Models\Department;
use App\Models\Grade;
use App\Models\GradeSubject;
use App\Models\Role;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SubjectAllocationIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_subject_creation_is_rejected_with_a_friendly_message(): void
    {
        $user = $this->createUserWithRole('Academic Admin');
        ['term' => $term, 'grade' => $grade, 'subject' => $subject, 'department' => $department] = $this->createCurrentTermContext();

        GradeSubject::create([
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

        $response = $this->withoutMiddleware(BlockNonAfricanCountries::class)
            ->actingAs($user)
            ->from(route('subjects.create'))
            ->post(route('subjects.store'), [
                'sequence' => 2,
                'grade_id' => $grade->id,
                'subject_id' => $subject->id,
                'type' => '1',
                'department_id' => $department->id,
                'mandatory' => '1',
                'active' => '1',
            ]);

        $response->assertSessionHas('error', 'The subject already exists for the selected grade and term.');
    }

    public function test_grade_subject_unique_constraint_blocks_duplicate_active_allocations(): void
    {
        ['term' => $term, 'grade' => $grade, 'subject' => $subject, 'department' => $department] = $this->createCurrentTermContext();

        GradeSubject::create([
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

        $this->expectException(QueryException::class);

        GradeSubject::create([
            'sequence' => 2,
            'grade_id' => $grade->id,
            'subject_id' => $subject->id,
            'department_id' => $department->id,
            'term_id' => $term->id,
            'year' => $term->year,
            'type' => '1',
            'mandatory' => true,
            'active' => true,
        ]);
    }

    public function test_existing_subject_allocation_cannot_be_moved_to_another_grade(): void
    {
        $user = $this->createUserWithRole('Academic Admin');
        ['term' => $term, 'grade' => $grade, 'subject' => $subject, 'department' => $department] = $this->createCurrentTermContext();
        $otherGrade = $this->createGrade($term, 'F6 ' . Str::upper(Str::random(4)));

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

        $response = $this->withoutMiddleware(BlockNonAfricanCountries::class)
            ->actingAs($user)
            ->withSession(['selected_term_id' => $term->id])
            ->from(route('subject.edit-subject', $gradeSubject->id))
            ->post(route('subject.update-subject', $gradeSubject->id), [
                'sequence' => 3,
                'subject_id' => $subject->id,
                'type' => '1',
                'mandatory' => '1',
                'active' => '1',
                'department_id' => $department->id,
                'grade_id' => $otherGrade->id,
            ]);

        $response->assertSessionHas(
            'error',
            'Changing the grade of an existing subject allocation is not allowed. Create the subject in the target grade instead.'
        );

        $this->assertSame($grade->id, $gradeSubject->fresh()->grade_id);
    }

    private function createUserWithRole(string $roleName): User
    {
        $role = Role::query()->firstOrCreate(['name' => $roleName], ['description' => $roleName]);
        $user = User::factory()->create([
            'status' => 'Current',
            'active' => true,
        ]);
        $user->roles()->syncWithoutDetaching([$role->id]);

        return $user->fresh();
    }

    /**
     * @return array{term: Term, grade: Grade, subject: Subject, department: Department}
     */
    private function createCurrentTermContext(): array
    {
        $term = TermHelper::getCurrentTerm() ?? $this->createFallbackTerm();
        $department = Department::query()->firstOrCreate(['name' => 'Subject Integrity']);
        $grade = $this->createGrade($term, 'F5 ' . Str::upper(Str::random(4)));
        $subject = Subject::create([
            'abbrev' => 'SUB' . Str::upper(Str::random(3)),
            'name' => 'Subject ' . Str::upper(Str::random(6)),
            'level' => 'Senior',
            'components' => false,
            'description' => 'Subject Integrity',
            'department' => $department->name,
        ]);

        return compact('term', 'grade', 'subject', 'department');
    }

    private function createFallbackTerm(): Term
    {
        return Term::create([
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->addDays(20)->toDateString(),
            'term' => 1,
            'year' => (int) now()->format('Y'),
            'closed' => false,
            'extension_days' => 0,
        ]);
    }

    private function createGrade(Term $term, string $name): Grade
    {
        return Grade::create([
            'sequence' => random_int(1, 20),
            'name' => $name,
            'promotion' => 'PROMO-' . Str::upper(Str::random(3)),
            'description' => $name,
            'level' => 'Senior',
            'active' => true,
            'term_id' => $term->id,
            'year' => $term->year,
        ]);
    }
}
