<?php

namespace Tests\Feature\Schemes;

use App\Models\Department;
use App\Models\Grade;
use App\Models\GradeSubject;
use App\Models\Klass;
use App\Models\KlassSubject;
use App\Models\Role;
use App\Models\Schemes\LessonPlan;
use App\Models\Schemes\SchemeOfWork;
use App\Models\Schemes\Syllabus;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use App\Services\Schemes\SchemeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class SchemeRobustnessTest extends TestCase
{
    use RefreshDatabase;

    public function test_hod_can_view_but_cannot_edit_scheme_entries_or_lesson_plans(): void
    {
        [
            'teacher' => $teacher,
            'department' => $department,
            'scheme' => $scheme,
            'syllabus' => $syllabus,
        ] = $this->createSchemeContext();

        $hod = User::factory()->create([
            'area_of_work' => 'Teaching',
            'status' => 'Current',
            'active' => true,
        ]);
        $hodRole = Role::query()->firstOrCreate(['name' => 'HOD'], ['description' => 'Head of Department']);
        $hod->roles()->syncWithoutDetaching([$hodRole->id]);
        $department->update(['department_head' => $hod->id]);

        $topic = $syllabus->topics()->create([
            'sequence' => 1,
            'name' => 'Pronunciation',
            'description' => 'Section: Form 1 | Unit: [1.1] Listening | Path: Pronunciation',
            'suggested_weeks' => null,
        ]);
        $objective = $topic->objectives()->create([
            'sequence' => 1,
            'code' => 'P-1',
            'objective_text' => 'identify speech sounds',
            'cognitive_level' => 'Knowledge',
        ]);
        $entry = $scheme->entries()->create([
            'week_number' => 1,
            'status' => 'planned',
        ]);
        $plan = LessonPlan::create([
            'scheme_of_work_id' => $scheme->id,
            'scheme_of_work_entry_id' => $entry->id,
            'teacher_id' => $teacher->id,
            'date' => now()->toDateString(),
            'topic' => 'Listening',
            'sub_topic' => 'Pronunciation',
            'status' => 'planned',
        ]);

        $this->actingAs($hod)
            ->get(route('schemes.show', $scheme))
            ->assertOk()
            ->assertSee('View Only')
            ->assertDontSee('Save Entry');

        $this->actingAs($hod)
            ->putJson(route('schemes.entries.update', [$scheme, $entry]), [
                'topic' => 'Listening',
                'sub_topic' => 'Pronunciation',
                'learning_objectives' => '<ul><li>identify speech sounds</li></ul>',
                'syllabus_topic_id' => $topic->id,
                'objective_ids' => [$objective->id],
                'status' => 'planned',
            ])
            ->assertForbidden();

        $this->actingAs($hod)
            ->get(route('lesson-plans.show', $plan))
            ->assertOk();

        $this->actingAs($hod)
            ->get(route('lesson-plans.edit', $plan))
            ->assertForbidden();
    }

    public function test_scheme_service_rejects_duplicate_creation_even_without_request_validation(): void
    {
        [
            'teacher' => $teacher,
            'klassSubject' => $klassSubject,
            'term' => $term,
        ] = $this->createSchemeContext();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A scheme already exists for this assignment in the selected term.');

        app(SchemeService::class)->createWithEntries([
            'klass_subject_id' => $klassSubject->id,
            'optional_subject_id' => null,
            'term_id' => $term->id,
            'total_weeks' => 10,
        ], $teacher->id);
    }

    public function test_scheme_service_rejects_duplicate_clone_target_term(): void
    {
        [
            'teacher' => $teacher,
            'klassSubject' => $klassSubject,
            'scheme' => $scheme,
            'term' => $term,
        ] = $this->createSchemeContext();

        $newTerm = Term::query()
            ->where('term', 2)
            ->where('year', $term->year)
            ->first()
            ?? Term::create([
                'start_date' => now()->addMonths(4)->toDateString(),
                'end_date' => now()->addMonths(5)->toDateString(),
                'term' => 2,
                'year' => $term->year,
                'closed' => false,
                'extension_days' => 0,
            ]);

        SchemeOfWork::create([
            'klass_subject_id' => $klassSubject->id,
            'term_id' => $newTerm->id,
            'teacher_id' => $teacher->id,
            'status' => 'draft',
            'total_weeks' => 10,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A scheme already exists for this assignment in the selected term.');

        app(SchemeService::class)->cloneScheme($scheme, $teacher->id, $newTerm->id);
    }

    public function test_completed_entry_on_approved_scheme_cannot_be_updated(): void
    {
        [
            'teacher' => $teacher,
            'scheme' => $scheme,
            'syllabus' => $syllabus,
        ] = $this->createSchemeContext();

        $scheme->update(['status' => 'approved']);

        $topic = $syllabus->topics()->create([
            'sequence' => 1,
            'name' => 'Pronunciation',
            'description' => 'Section: Form 1 | Unit: [1.1] Listening | Path: Pronunciation',
            'suggested_weeks' => null,
        ]);
        $objective = $topic->objectives()->create([
            'sequence' => 1,
            'code' => 'P-1',
            'objective_text' => 'identify speech sounds',
            'cognitive_level' => 'Knowledge',
        ]);
        $entry = $scheme->entries()->create([
            'week_number' => 1,
            'status' => 'completed',
            'syllabus_topic_id' => $topic->id,
        ]);

        $this->actingAs($teacher)
            ->putJson(route('schemes.entries.update', [$scheme, $entry]), [
                'topic' => 'Listening',
                'sub_topic' => 'Pronunciation',
                'learning_objectives' => '<ul><li>identify speech sounds</li></ul>',
                'syllabus_topic_id' => $topic->id,
                'objective_ids' => [$objective->id],
                'status' => 'completed',
            ])
            ->assertForbidden();
    }

    public function test_hod_can_publish_scheme_as_reference_and_replace_existing_reference(): void
    {
        [
            'teacher' => $teacher,
            'department' => $department,
            'scheme' => $scheme,
            'klassSubject' => $klassSubject,
            'term' => $term,
        ] = $this->createSchemeContext();

        $scheme->update(['status' => 'approved']);

        $otherTeacher = User::factory()->create([
            'area_of_work' => 'Teaching',
            'status' => 'Current',
            'active' => true,
        ]);
        $teacherRole = Role::query()->firstOrCreate(['name' => 'Teacher'], ['description' => 'Teacher']);
        $otherTeacher->roles()->syncWithoutDetaching([$teacherRole->id]);

        $klass = Klass::create([
            'name' => 'F1B',
            'user_id' => $otherTeacher->id,
            'term_id' => $term->id,
            'grade_id' => $klassSubject->grade_id,
            'year' => $term->year,
        ]);

        $otherKlassSubject = KlassSubject::create([
            'klass_id' => $klass->id,
            'grade_subject_id' => $klassSubject->grade_subject_id,
            'user_id' => $otherTeacher->id,
            'term_id' => $term->id,
            'grade_id' => $klassSubject->grade_id,
            'year' => $term->year,
            'active' => true,
        ]);

        $existingReference = SchemeOfWork::create([
            'klass_subject_id' => $otherKlassSubject->id,
            'term_id' => $term->id,
            'teacher_id' => $otherTeacher->id,
            'status' => 'approved',
            'is_published' => true,
            'published_at' => now()->subDay(),
            'published_by' => $otherTeacher->id,
            'total_weeks' => 10,
        ]);

        $hod = User::factory()->create([
            'area_of_work' => 'Teaching',
            'status' => 'Current',
            'active' => true,
        ]);
        $hodRole = Role::query()->firstOrCreate(['name' => 'HOD'], ['description' => 'Head of Department']);
        $hod->roles()->syncWithoutDetaching([$hodRole->id]);
        $department->update(['department_head' => $hod->id]);

        $this->actingAs($hod)
            ->post(route('schemes.publish-reference', $scheme))
            ->assertRedirect(route('schemes.show', $scheme))
            ->assertSessionHas('success');

        $this->assertTrue($scheme->fresh()->is_published);
        $this->assertFalse($existingReference->fresh()->is_published);
    }

    /**
     * @return array{teacher: User, syllabus: Syllabus, scheme: SchemeOfWork, department: Department, klassSubject: KlassSubject, term: Term}
     */
    private function createSchemeContext(array $syllabusOverrides = []): array
    {
        $teacher = User::factory()->create([
            'area_of_work' => 'Teaching',
            'status' => 'Current',
            'active' => true,
        ]);

        $teacherRole = Role::query()->firstOrCreate(
            ['name' => 'Teacher'],
            ['description' => 'Teacher']
        );
        $teacher->roles()->syncWithoutDetaching([$teacherRole->id]);
        $teacher = $teacher->fresh();

        $term = Term::query()
            ->where('year', (int) now()->format('Y'))
            ->where('term', 1)
            ->first()
            ?? Term::create([
                'start_date' => now()->subMonth()->toDateString(),
                'end_date' => now()->addMonth()->toDateString(),
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
            'year' => $term->year,
        ]);

        $subject = Subject::create([
            'abbrev' => 'ENGX',
            'name' => 'English',
            'level' => 'Junior',
            'components' => false,
            'description' => 'English',
            'department' => 'Languages',
        ]);

        $department = Department::create([
            'name' => 'Languages',
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

        $klass = Klass::create([
            'name' => 'F1A',
            'user_id' => $teacher->id,
            'term_id' => $term->id,
            'grade_id' => $grade->id,
            'year' => $term->year,
        ]);

        $klassSubject = KlassSubject::create([
            'klass_id' => $klass->id,
            'grade_subject_id' => $gradeSubject->id,
            'user_id' => $teacher->id,
            'term_id' => $term->id,
            'grade_id' => $grade->id,
            'year' => $term->year,
            'active' => true,
        ]);

        $syllabus = Syllabus::create(array_merge([
            'subject_id' => $subject->id,
            'grades' => [$grade->name],
            'level' => $grade->level,
            'is_active' => true,
            'description' => 'Remote syllabus',
            'source_url' => null,
            'cached_structure' => null,
            'cached_at' => null,
        ], $syllabusOverrides));

        $scheme = SchemeOfWork::create([
            'klass_subject_id' => $klassSubject->id,
            'term_id' => $term->id,
            'teacher_id' => $teacher->id,
            'status' => 'draft',
            'total_weeks' => 10,
        ]);

        return [
            'teacher' => $teacher,
            'syllabus' => $syllabus,
            'scheme' => $scheme,
            'department' => $department,
            'klassSubject' => $klassSubject,
            'term' => $term,
        ];
    }
}
