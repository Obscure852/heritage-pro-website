<?php

namespace Tests\Feature\Assessment;

use App\Http\Middleware\BlockNonAfricanCountries;
use App\Models\Department;
use App\Models\Grade;
use App\Models\GradeSubject;
use App\Models\Role;
use App\Models\Subject;
use App\Models\Term;
use App\Models\Test;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TestIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_repair_migration_corrects_tests_with_grade_subject_grade_drift(): void
    {
        ['term' => $term, 'grade' => $grade, 'gradeSubject' => $gradeSubject] = $this->createAssessmentContext();
        $wrongGrade = $this->createGrade($term, 'F6 ' . Str::upper(Str::random(4)));

        $test = Test::create([
            'sequence' => 1,
            'name' => 'Add Mathematics CA',
            'abbrev' => 'AMCA',
            'grade_subject_id' => $gradeSubject->id,
            'term_id' => $term->id,
            'grade_id' => $wrongGrade->id,
            'out_of' => 100,
            'year' => $term->year,
            'type' => 'CA',
            'assessment' => true,
            'start_date' => $term->start_date,
            'end_date' => $term->end_date,
        ]);

        $this->assertSame($wrongGrade->id, $test->grade_id);

        $migration = require database_path('migrations/2026_04_01_150000_repair_mismatched_test_grade_ids.php');
        $migration->up();

        $this->assertSame($grade->id, $test->fresh()->grade_id);
    }

    public function test_store_persists_the_selected_grade_subject_grade(): void
    {
        $user = $this->createUserWithRole('Teacher');
        ['term' => $term, 'grade' => $grade, 'gradeSubject' => $gradeSubject] = $this->createAssessmentContext();

        $response = $this->withoutMiddleware(BlockNonAfricanCountries::class)
            ->actingAs($user)
            ->from(route('assessment.create-test'))
            ->post(route('assessment.test-store'), [
                'sequence' => 1,
                'name' => 'Term Test',
                'abbrev' => 'TT',
                'subject' => $gradeSubject->id,
                'type' => 'CA',
                'assessment' => '1',
                'out_of' => 100,
                'start_date' => $term->start_date,
                'end_date' => $term->end_date,
                'grade' => $grade->id,
                'term' => $term->id,
                'year' => $term->year,
            ]);

        $response->assertSessionHas('message', 'Test created successfully!');

        $this->assertDatabaseHas('tests', [
            'name' => 'Term Test',
            'grade_subject_id' => $gradeSubject->id,
            'grade_id' => $grade->id,
            'term_id' => $term->id,
        ]);
    }

    public function test_update_cannot_create_grade_drift(): void
    {
        $user = $this->createUserWithRole('Teacher');
        ['term' => $term, 'grade' => $grade, 'gradeSubject' => $gradeSubject] = $this->createAssessmentContext();
        $otherGrade = $this->createGrade($term, 'F7 ' . Str::upper(Str::random(4)));

        $test = Test::create([
            'sequence' => 1,
            'name' => 'Grade Locked Test',
            'abbrev' => 'GLT',
            'grade_subject_id' => $gradeSubject->id,
            'term_id' => $term->id,
            'grade_id' => $grade->id,
            'out_of' => 50,
            'year' => $term->year,
            'type' => 'CA',
            'assessment' => true,
            'start_date' => $term->start_date,
            'end_date' => $term->end_date,
        ]);

        $response = $this->withoutMiddleware(BlockNonAfricanCountries::class)
            ->actingAs($user)
            ->from('/assessment/grade/test/edit/' . $test->id)
            ->post(route('assessment.ca-exam-update', $test->id), [
                'sequence' => 2,
                'abbrev' => 'GLT2',
                'out_of' => 60,
                'grade_id' => $otherGrade->id,
                'grade_subject_id' => $gradeSubject->id,
                'type' => 'CA',
                'start_date' => $term->start_date,
                'end_date' => $term->end_date,
            ]);

        $response->assertSessionHasErrors();

        $freshTest = $test->fresh();
        $this->assertSame($grade->id, $freshTest->grade_id);
        $this->assertSame($gradeSubject->id, $freshTest->grade_subject_id);
    }

    public function test_copy_uses_the_target_subject_grade_even_when_the_source_test_grade_is_stale(): void
    {
        $user = $this->createUserWithRole('Teacher');
        ['term' => $term, 'grade' => $grade, 'department' => $department] = $this->createAssessmentContext();

        $sourceSubject = $this->createSubject('Add Mathematics ' . Str::upper(Str::random(4)), 'AMA' . Str::upper(Str::random(2)));
        $targetSubject = $this->createSubject('Physics ' . Str::upper(Str::random(4)), 'PHY' . Str::upper(Str::random(2)));
        $wrongGrade = $this->createGrade($term, 'F8 ' . Str::upper(Str::random(4)));

        $sourceGradeSubject = $this->createGradeSubject($grade, $sourceSubject, $department, $term);
        $targetGradeSubject = $this->createGradeSubject($grade, $targetSubject, $department, $term);

        $sourceTest = Test::create([
            'sequence' => 1,
            'name' => 'Copied CA',
            'abbrev' => 'CCA',
            'grade_subject_id' => $sourceGradeSubject->id,
            'term_id' => $term->id,
            'grade_id' => $wrongGrade->id,
            'out_of' => 100,
            'year' => $term->year,
            'type' => 'CA',
            'assessment' => true,
            'start_date' => $term->start_date,
            'end_date' => $term->end_date,
        ]);

        $response = $this->withoutMiddleware(BlockNonAfricanCountries::class)
            ->actingAs($user)
            ->from('/assessment/exam/list')
            ->post(route('assessment.copy-test'), [
                'test_id' => $sourceTest->id,
                'target_subject_id' => $targetGradeSubject->id,
            ]);

        $response->assertSessionHas('message');

        $copiedTest = Test::query()
            ->where('grade_subject_id', $targetGradeSubject->id)
            ->where('name', 'Copied CA')
            ->where('id', '!=', $sourceTest->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($copiedTest);
        $this->assertSame($grade->id, $copiedTest->grade_id);
    }

    public function test_grade_test_list_includes_same_grade_subjects_without_existing_tests_as_copy_targets(): void
    {
        $user = $this->createUserWithRole('Teacher');
        ['term' => $term, 'grade' => $grade, 'department' => $department] = $this->createAssessmentContext();

        $sourceSubject = $this->createSubject('Accounting ' . Str::upper(Str::random(4)), 'ACC' . Str::upper(Str::random(2)));
        $targetSubject = $this->createSubject('History ' . Str::upper(Str::random(4)), 'HIS' . Str::upper(Str::random(2)));

        $sourceGradeSubject = $this->createGradeSubject($grade, $sourceSubject, $department, $term);
        $targetGradeSubject = $this->createGradeSubject($grade, $targetSubject, $department, $term);

        Test::create([
            'sequence' => 1,
            'name' => 'Visible Test',
            'abbrev' => 'VT',
            'grade_subject_id' => $sourceGradeSubject->id,
            'term_id' => $term->id,
            'grade_id' => $grade->id,
            'out_of' => 100,
            'year' => $term->year,
            'type' => 'CA',
            'assessment' => true,
            'start_date' => $term->start_date,
            'end_date' => $term->end_date,
        ]);

        $response = $this->withoutMiddleware(BlockNonAfricanCountries::class)
            ->actingAs($user)
            ->get(route('assessment.tests-lists', ['termId' => $term->id, 'gradeId' => $grade->id]));

        $response->assertOk();
        $response->assertSee($targetGradeSubject->subject->name);
    }

    private function createUserWithRole(string $roleName): User
    {
        $role = Role::query()->firstOrCreate(['name' => $roleName], ['description' => $roleName]);
        $user = User::factory()->create([
            'area_of_work' => 'Teaching',
            'status' => 'Current',
            'active' => true,
        ]);
        $user->roles()->syncWithoutDetaching([$role->id]);

        return $user->fresh();
    }

    /**
     * @return array{term: Term, grade: Grade, gradeSubject: GradeSubject, subject: Subject, department: Department}
     */
    private function createAssessmentContext(): array
    {
        $term = $this->createTerm();
        $grade = $this->createGrade($term, 'F5 ' . Str::upper(Str::random(4)));
        $department = Department::query()->firstOrCreate(['name' => 'Assessment Integrity']);
        $subject = $this->createSubject('Mathematics ' . Str::upper(Str::random(4)), 'MAT' . Str::upper(Str::random(2)));
        $gradeSubject = $this->createGradeSubject($grade, $subject, $department, $term);

        return compact('term', 'grade', 'gradeSubject', 'subject', 'department');
    }

    private function createTerm(): Term
    {
        return Term::create([
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->addDays(25)->toDateString(),
            'term' => random_int(4, 99),
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

    private function createSubject(string $name, string $abbrev): Subject
    {
        return Subject::create([
            'abbrev' => $abbrev,
            'name' => $name,
            'level' => 'Senior',
            'components' => false,
            'description' => $name,
            'department' => 'Assessment Integrity',
        ]);
    }

    private function createGradeSubject(Grade $grade, Subject $subject, Department $department, Term $term): GradeSubject
    {
        return GradeSubject::create([
            'sequence' => random_int(1, 20),
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
}
