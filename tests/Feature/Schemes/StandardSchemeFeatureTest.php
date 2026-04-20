<?php

namespace Tests\Feature\Schemes;

use App\Mail\Schemes\StandardSchemeDistributedMail;
use App\Models\Department;
use App\Models\Grade;
use App\Models\GradeSubject;
use App\Models\Klass;
use App\Models\KlassSubject;
use App\Models\Role;
use App\Models\Schemes\SchemeOfWork;
use App\Models\Schemes\StandardScheme;
use App\Models\Schemes\StandardSchemeWorkflowAudit;
use App\Models\Schemes\Syllabus;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use App\Services\Messaging\StaffMessagingService;
use App\Services\Schemes\StandardSchemeService;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class StandardSchemeFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_standard_scheme_service_rejects_duplicate_creation_even_without_request_validation(): void
    {
        $schemeAdmin = $this->createUserWithRole('Scheme Admin');

        $term = Term::query()->updateOrCreate(
            [
                'term' => 1,
                'year' => 2026,
            ],
            [
                'start_date' => '2026-01-10',
                'end_date' => '2026-04-10',
                'closed' => false,
                'extension_days' => 0,
            ]
        );

        $grade = Grade::query()->create([
            'sequence' => 1,
            'name' => 'F1',
            'promotion' => 'F2',
            'description' => 'Form 1',
            'level' => 'Junior',
            'active' => true,
            'term_id' => $term->id,
            'year' => $term->year,
        ]);

        $subject = Subject::query()->create([
            'abbrev' => 'SCI',
            'name' => 'Science',
            'level' => 'Junior',
            'components' => false,
            'description' => 'Science',
            'department' => 'Sciences',
        ]);

        $department = Department::query()->create([
            'name' => 'Sciences',
        ]);

        GradeSubject::query()->create([
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

        app(StandardSchemeService::class)->createWithEntries([
            'subject_id' => $subject->id,
            'grade_id' => $grade->id,
            'term_id' => $term->id,
            'department_id' => $department->id,
            'total_weeks' => 10,
        ], $schemeAdmin->id);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('A standard scheme already exists for this subject, grade, and selected term.');

        app(StandardSchemeService::class)->createWithEntries([
            'subject_id' => $subject->id,
            'grade_id' => $grade->id,
            'term_id' => $term->id,
            'department_id' => $department->id,
            'total_weeks' => 10,
        ], $schemeAdmin->id);
    }

    public function test_standard_scheme_can_be_copied_to_a_different_year_and_term(): void
    {
        $schemeAdmin = $this->createUserWithRole('Scheme Admin');

        $sourceTerm = Term::query()->updateOrCreate(
            [
                'term' => 1,
                'year' => 2026,
            ],
            [
                'start_date' => '2026-01-10',
                'end_date' => '2026-04-10',
                'closed' => false,
                'extension_days' => 0,
            ]
        );

        $targetTerm = Term::query()->updateOrCreate(
            [
                'term' => 2,
                'year' => 2027,
            ],
            [
                'start_date' => '2027-05-10',
                'end_date' => '2027-08-10',
                'closed' => false,
                'extension_days' => 0,
            ]
        );

        $sourceGrade = Grade::query()->create([
            'sequence' => 1,
            'name' => 'F1',
            'promotion' => 'F2',
            'description' => 'Form 1',
            'level' => 'Junior',
            'active' => true,
            'term_id' => $sourceTerm->id,
            'year' => $sourceTerm->year,
        ]);

        $targetGrade = Grade::query()->create([
            'sequence' => 1,
            'name' => 'F1',
            'promotion' => 'F2',
            'description' => 'Form 1',
            'level' => 'Junior',
            'active' => true,
            'term_id' => $targetTerm->id,
            'year' => $targetTerm->year,
        ]);

        $subject = Subject::query()->create([
            'abbrev' => 'SCI',
            'name' => 'Science',
            'level' => 'Junior',
            'components' => false,
            'description' => 'Science',
            'department' => 'Sciences',
        ]);

        $sourceDepartment = Department::query()->create([
            'name' => 'Sciences 2026',
        ]);

        $targetDepartment = Department::query()->create([
            'name' => 'Sciences 2027',
        ]);

        GradeSubject::query()->create([
            'sequence' => 1,
            'grade_id' => $sourceGrade->id,
            'subject_id' => $subject->id,
            'department_id' => $sourceDepartment->id,
            'term_id' => $sourceTerm->id,
            'year' => $sourceTerm->year,
            'type' => '1',
            'mandatory' => true,
            'active' => true,
        ]);

        GradeSubject::query()->create([
            'sequence' => 1,
            'grade_id' => $targetGrade->id,
            'subject_id' => $subject->id,
            'department_id' => $targetDepartment->id,
            'term_id' => $targetTerm->id,
            'year' => $targetTerm->year,
            'type' => '1',
            'mandatory' => true,
            'active' => true,
        ]);

        $syllabus = Syllabus::query()->create([
            'subject_id' => $subject->id,
            'grades' => ['F1'],
            'level' => 'Junior',
            'is_active' => true,
            'description' => 'Science syllabus',
        ]);

        $topic = $syllabus->topics()->create([
            'sequence' => 1,
            'name' => 'Living Things',
            'description' => 'Topic description',
            'suggested_weeks' => 2,
        ]);

        $objective = $topic->objectives()->create([
            'sequence' => 1,
            'code' => 'SCI-1',
            'objective_text' => 'Describe characteristics of living things',
            'cognitive_level' => 'Knowledge',
        ]);

        $sourceScheme = app(StandardSchemeService::class)->createWithEntries([
            'subject_id' => $subject->id,
            'grade_id' => $sourceGrade->id,
            'term_id' => $sourceTerm->id,
            'department_id' => $sourceDepartment->id,
            'total_weeks' => 12,
        ], $schemeAdmin->id);

        $sourceEntry = $sourceScheme->entries()->where('week_number', 1)->firstOrFail();
        $sourceEntry->update([
            'syllabus_topic_id' => $topic->id,
            'topic' => 'Living Things',
            'sub_topic' => 'Characteristics',
            'learning_objectives' => '<ul><li>Describe characteristics of living things</li></ul>',
            'status' => 'completed',
        ]);
        $sourceEntry->objectives()->sync([$objective->id]);

        $response = $this->actingAs($schemeAdmin)
            ->post(route('standard-schemes.clone', $sourceScheme), [
                'term_id' => $targetTerm->id,
            ]);

        $clonedScheme = StandardScheme::query()
            ->where('term_id', $targetTerm->id)
            ->where('subject_id', $subject->id)
            ->firstOrFail();

        $response->assertRedirect(route('standard-schemes.show', $clonedScheme));

        $this->assertSame($targetGrade->id, $clonedScheme->grade_id);
        $this->assertSame($targetDepartment->id, $clonedScheme->department_id);
        $this->assertSame('draft', $clonedScheme->status);
        $this->assertSame(12, $clonedScheme->total_weeks);
        $this->assertSame($schemeAdmin->id, $clonedScheme->created_by);
        $this->assertSame($schemeAdmin->id, $clonedScheme->panel_lead_id);

        $clonedEntry = $clonedScheme->entries()->where('week_number', 1)->firstOrFail();

        $this->assertSame($topic->id, $clonedEntry->syllabus_topic_id);
        $this->assertSame('Living Things', $clonedEntry->topic);
        $this->assertSame('Characteristics', $clonedEntry->sub_topic);
        $this->assertSame('<ul><li>Describe characteristics of living things</li></ul>', $clonedEntry->learning_objectives);
        $this->assertSame('planned', $clonedEntry->status);
        $this->assertDatabaseHas('standard_scheme_entry_objectives', [
            'standard_scheme_entry_id' => $clonedEntry->id,
            'syllabus_objective_id' => $objective->id,
        ]);
    }

    public function test_standard_scheme_copy_rejects_duplicate_target_context(): void
    {
        $schemeAdmin = $this->createUserWithRole('Scheme Admin');

        $sourceTerm = Term::query()->updateOrCreate(
            [
                'term' => 1,
                'year' => 2026,
            ],
            [
                'start_date' => '2026-01-10',
                'end_date' => '2026-04-10',
                'closed' => false,
                'extension_days' => 0,
            ]
        );

        $targetTerm = Term::query()->updateOrCreate(
            [
                'term' => 2,
                'year' => 2026,
            ],
            [
                'start_date' => '2026-05-10',
                'end_date' => '2026-08-10',
                'closed' => false,
                'extension_days' => 0,
            ]
        );

        $sourceGrade = Grade::query()->create([
            'sequence' => 1,
            'name' => 'F1',
            'promotion' => 'F2',
            'description' => 'Form 1',
            'level' => 'Junior',
            'active' => true,
            'term_id' => $sourceTerm->id,
            'year' => $sourceTerm->year,
        ]);

        $targetGrade = Grade::query()->create([
            'sequence' => 1,
            'name' => 'F1',
            'promotion' => 'F2',
            'description' => 'Form 1',
            'level' => 'Junior',
            'active' => true,
            'term_id' => $targetTerm->id,
            'year' => $targetTerm->year,
        ]);

        $subject = Subject::query()->create([
            'abbrev' => 'SCI',
            'name' => 'Science',
            'level' => 'Junior',
            'components' => false,
            'description' => 'Science',
            'department' => 'Sciences',
        ]);

        $department = Department::query()->create([
            'name' => 'Sciences',
        ]);

        GradeSubject::query()->create([
            'sequence' => 1,
            'grade_id' => $sourceGrade->id,
            'subject_id' => $subject->id,
            'department_id' => $department->id,
            'term_id' => $sourceTerm->id,
            'year' => $sourceTerm->year,
            'type' => '1',
            'mandatory' => true,
            'active' => true,
        ]);

        GradeSubject::query()->create([
            'sequence' => 1,
            'grade_id' => $targetGrade->id,
            'subject_id' => $subject->id,
            'department_id' => $department->id,
            'term_id' => $targetTerm->id,
            'year' => $targetTerm->year,
            'type' => '1',
            'mandatory' => true,
            'active' => true,
        ]);

        $sourceScheme = app(StandardSchemeService::class)->createWithEntries([
            'subject_id' => $subject->id,
            'grade_id' => $sourceGrade->id,
            'term_id' => $sourceTerm->id,
            'department_id' => $department->id,
            'total_weeks' => 10,
        ], $schemeAdmin->id);

        app(StandardSchemeService::class)->createWithEntries([
            'subject_id' => $subject->id,
            'grade_id' => $targetGrade->id,
            'term_id' => $targetTerm->id,
            'department_id' => $department->id,
            'total_weeks' => 10,
        ], $schemeAdmin->id);

        $this->from(route('standard-schemes.show', $sourceScheme))
            ->actingAs($schemeAdmin)
            ->post(route('standard-schemes.clone', $sourceScheme), [
                'term_id' => $targetTerm->id,
            ])
            ->assertRedirect(route('standard-schemes.show', $sourceScheme))
            ->assertSessionHas('error', 'A standard scheme already exists for this subject, grade, and selected term.');

        $this->assertSame(2, StandardScheme::query()->count());
    }

    public function test_create_page_scopes_grades_to_the_selected_term(): void
    {
        $schemeAdmin = $this->createUserWithRole('Scheme Admin');

        $currentTerm = Term::query()->updateOrCreate(
            [
                'term' => 1,
                'year' => (int) now()->format('Y'),
            ],
            [
                'start_date' => now()->subWeek()->toDateString(),
                'end_date' => now()->addWeek()->toDateString(),
                'closed' => false,
                'extension_days' => 0,
            ]
        );

        $futureTerm = Term::query()->updateOrCreate(
            [
                'term' => 2,
                'year' => (int) now()->format('Y'),
            ],
            [
                'start_date' => now()->addMonth()->toDateString(),
                'end_date' => now()->addMonths(2)->toDateString(),
                'closed' => false,
                'extension_days' => 0,
            ]
        );

        $currentGrade = Grade::query()->updateOrCreate(
            [
                'name' => 'F1',
                'term_id' => $currentTerm->id,
            ],
            [
                'sequence' => 1,
                'promotion' => 'F2',
                'description' => 'Form 1',
                'level' => 'Junior',
                'active' => true,
                'year' => $currentTerm->year,
            ]
        );

        $futureGrade = Grade::query()->updateOrCreate(
            [
                'name' => 'F1',
                'term_id' => $futureTerm->id,
            ],
            [
                'sequence' => 1,
                'promotion' => 'F2',
                'description' => 'Form 1',
                'level' => 'Junior',
                'active' => true,
                'year' => $futureTerm->year,
            ]
        );

        $seniorGrade = Grade::query()->updateOrCreate(
            [
                'name' => 'F5',
                'term_id' => $currentTerm->id,
            ],
            [
                'sequence' => 5,
                'promotion' => 'Graduate',
                'description' => 'Form 5',
                'level' => 'Senior',
                'active' => true,
                'year' => $currentTerm->year,
            ]
        );

        $department = Department::query()->create([
            'name' => 'Sciences',
        ]);

        $juniorScience = Subject::query()->create([
            'abbrev' => 'SCI',
            'name' => 'Science',
            'level' => 'Junior',
            'components' => false,
            'description' => 'Science',
            'department' => 'Sciences',
        ]);

        $seniorScience = Subject::query()->create([
            'abbrev' => 'SCI-S',
            'name' => 'Science',
            'level' => 'Senior',
            'components' => false,
            'description' => 'Science',
            'department' => 'Sciences',
        ]);

        GradeSubject::query()->create([
            'sequence' => 1,
            'grade_id' => $currentGrade->id,
            'subject_id' => $juniorScience->id,
            'department_id' => $department->id,
            'term_id' => $currentTerm->id,
            'year' => $currentTerm->year,
            'type' => '1',
            'mandatory' => true,
            'active' => true,
        ]);

        GradeSubject::query()->create([
            'sequence' => 1,
            'grade_id' => $seniorGrade->id,
            'subject_id' => $seniorScience->id,
            'department_id' => $department->id,
            'term_id' => $currentTerm->id,
            'year' => $currentTerm->year,
            'type' => '1',
            'mandatory' => true,
            'active' => true,
        ]);

        $this->actingAs($schemeAdmin)
            ->get(route('standard-schemes.create'))
            ->assertOk()
            ->assertSee('Science (F1)')
            ->assertDontSee('Science (F5)')
            ->assertViewHas('grades', function ($grades) use ($currentGrade, $futureGrade): bool {
                return $grades->pluck('id')->contains($currentGrade->id)
                    && !$grades->pluck('id')->contains($futureGrade->id)
                    && $grades->every(fn (Grade $grade) => (int) $grade->term_id === (int) $currentGrade->term_id);
            });

        $this->actingAs($schemeAdmin)
            ->getJson(route('standard-schemes.grades-for-term', ['term_id' => $futureTerm->id]))
            ->assertOk()
            ->assertJsonFragment([
                'id' => $futureGrade->id,
                'name' => $futureGrade->name,
            ])
            ->assertJsonMissing([
                'id' => $currentGrade->id,
            ]);

        $this->actingAs($schemeAdmin)
            ->getJson(route('standard-schemes.subjects-for-context', [
                'term_id' => $currentTerm->id,
                'grade_id' => $seniorGrade->id,
            ]))
            ->assertOk()
            ->assertJsonFragment([
                'id' => $seniorScience->id,
                'name' => 'Science',
                'label' => 'Science (F5)',
            ])
            ->assertJsonMissing([
                'id' => $juniorScience->id,
            ]);
    }

    public function test_store_normalizes_grade_to_selected_term_when_grade_name_matches(): void
    {
        $schemeAdmin = $this->createUserWithRole('Scheme Admin');

        $selectedTerm = Term::query()->updateOrCreate(
            [
                'term' => 1,
                'year' => (int) now()->format('Y'),
            ],
            [
                'start_date' => now()->subWeek()->toDateString(),
                'end_date' => now()->addWeek()->toDateString(),
                'closed' => false,
                'extension_days' => 0,
            ]
        );

        $otherTerm = Term::query()->updateOrCreate(
            [
                'term' => 2,
                'year' => (int) now()->format('Y'),
            ],
            [
                'start_date' => now()->addMonth()->toDateString(),
                'end_date' => now()->addMonths(2)->toDateString(),
                'closed' => false,
                'extension_days' => 0,
            ]
        );

        $selectedTermGrade = Grade::query()->updateOrCreate(
            [
                'name' => 'F1',
                'term_id' => $selectedTerm->id,
            ],
            [
                'sequence' => 1,
                'promotion' => 'F2',
                'description' => 'Form 1',
                'level' => 'Junior',
                'active' => true,
                'year' => $selectedTerm->year,
            ]
        );

        $otherTermGrade = Grade::query()->updateOrCreate(
            [
                'name' => 'F1',
                'term_id' => $otherTerm->id,
            ],
            [
                'sequence' => 1,
                'promotion' => 'F2',
                'description' => 'Form 1',
                'level' => 'Junior',
                'active' => true,
                'year' => $otherTerm->year,
            ]
        );

        $subject = Subject::query()->create([
            'abbrev' => 'SCI',
            'name' => 'Science',
            'level' => 'Junior',
            'components' => false,
            'description' => 'Science',
            'department' => 'Sciences',
        ]);

        $department = Department::query()->create([
            'name' => 'Sciences',
        ]);

        GradeSubject::query()->create([
            'sequence' => 1,
            'grade_id' => $selectedTermGrade->id,
            'subject_id' => $subject->id,
            'department_id' => $department->id,
            'term_id' => $selectedTerm->id,
            'year' => $selectedTerm->year,
            'type' => '1',
            'mandatory' => true,
            'active' => true,
        ]);

        $response = $this->actingAs($schemeAdmin)
            ->post(route('standard-schemes.store'), [
                'subject_id' => $subject->id,
                'grade_id' => $otherTermGrade->id,
                'term_id' => $selectedTerm->id,
                'total_weeks' => 10,
            ]);

        $scheme = StandardScheme::query()->firstOrFail();

        $response->assertRedirect(route('standard-schemes.show', $scheme));

        $this->assertSame($selectedTermGrade->id, $scheme->grade_id);
        $this->assertSame($department->id, $scheme->department_id);
        $this->assertSame($selectedTerm->id, $scheme->term_id);
    }

    public function test_teacher_scheme_show_prefers_published_standard_scheme_over_syllabus_fallback(): void
    {
        [
            'teacher' => $teacher,
            'subject' => $subject,
            'grade' => $grade,
            'term' => $term,
            'department' => $department,
            'scheme' => $scheme,
        ] = $this->createTeacherSchemeContext([
            'cached_structure' => $this->remotePayload('Fallback English')['syllabus'],
            'cached_at' => now()->subHour(),
        ]);

        $schemeAdmin = $this->createUserWithRole('Scheme Admin');

        $standardScheme = app(StandardSchemeService::class)->createWithEntries([
            'subject_id' => $subject->id,
            'grade_id' => $grade->id,
            'term_id' => $term->id,
            'department_id' => $department->id,
            'total_weeks' => 10,
        ], $schemeAdmin->id);

        $standardScheme->update([
            'status' => 'approved',
            'published_at' => now(),
            'published_by' => $schemeAdmin->id,
        ]);

        $standardScheme->entries()->where('week_number', 1)->firstOrFail()->update([
            'topic' => 'Published Topic',
            'sub_topic' => 'Published Sub-topic',
            'learning_objectives' => '<ul><li>Use spoken examples confidently</li></ul>',
            'status' => 'planned',
        ]);

        $this->actingAs($teacher)
            ->get(route('schemes.show', $scheme))
            ->assertOk()
            ->assertSee('Browse Scheme')
            ->assertSee('Published Topic')
            ->assertSee('Published Sub-topic')
            ->assertSee('Use the published reference scheme below while planning each week.')
            ->assertDontSee('Fallback English');
    }

    public function test_teacher_scheme_show_falls_back_to_syllabus_when_no_published_standard_scheme_exists(): void
    {
        [
            'teacher' => $teacher,
            'scheme' => $scheme,
        ] = $this->createTeacherSchemeContext([
            'cached_structure' => $this->remotePayload('Fallback English')['syllabus'],
            'cached_at' => now()->subHour(),
        ]);

        $this->actingAs($teacher)
            ->get(route('schemes.show', $scheme))
            ->assertOk()
            ->assertSee('Browse Scheme')
            ->assertSee('Fallback English')
            ->assertSee('Pronunciation')
            ->assertDontSee('Published Topic');
    }

    public function test_distribution_creates_linked_read_only_teacher_scheme_and_logs_actor(): void
    {
        [
            'teacher' => $teacher,
            'subject' => $subject,
            'grade' => $grade,
            'term' => $term,
            'department' => $department,
            'syllabus' => $syllabus,
        ] = $this->createTeacherSchemeContext([
            'create_scheme' => false,
        ]);

        $schemeAdmin = $this->createUserWithRole('Scheme Admin');

        $topic = $syllabus->topics()->create([
            'sequence' => 1,
            'name' => 'Pronunciation',
            'description' => 'Section: Form 1 | Unit: [1.1] Listening | Path: Pronunciation',
            'suggested_weeks' => 1,
        ]);

        $objective = $topic->objectives()->create([
            'sequence' => 1,
            'code' => 'P-1',
            'objective_text' => 'identify speech sounds',
            'cognitive_level' => 'Knowledge',
        ]);

        $standardScheme = app(StandardSchemeService::class)->createWithEntries([
            'subject_id' => $subject->id,
            'grade_id' => $grade->id,
            'term_id' => $term->id,
            'department_id' => $department->id,
            'total_weeks' => 10,
        ], $schemeAdmin->id);

        $standardScheme->update(['status' => 'approved']);

        $standardEntry = $standardScheme->entries()->where('week_number', 1)->firstOrFail();
        $standardEntry->update([
            'syllabus_topic_id' => $topic->id,
            'topic' => 'Published Topic',
            'sub_topic' => 'Published Sub-topic',
            'learning_objectives' => '<ul><li>identify speech sounds</li></ul>',
            'status' => 'planned',
        ]);
        $standardEntry->objectives()->sync([$objective->id]);

        $this->actingAs($schemeAdmin)
            ->post(route('standard-schemes.distribute', $standardScheme))
            ->assertRedirect(route('standard-schemes.show', $standardScheme))
            ->assertSessionHas('success');

        $derivedScheme = SchemeOfWork::query()
            ->where('standard_scheme_id', $standardScheme->id)
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

        $this->assertSame('approved', $derivedScheme->status);
        $this->assertSame($standardScheme->id, $derivedScheme->standard_scheme_id);

        $derivedEntry = $derivedScheme->entries()->firstOrFail();

        $this->assertSame($standardEntry->id, $derivedEntry->standard_scheme_entry_id);
        $this->assertSame($topic->id, $derivedEntry->syllabus_topic_id);
        $this->assertSame('Published Topic', $derivedEntry->topic);
        $this->assertDatabaseHas('scheme_entry_objectives', [
            'scheme_of_work_entry_id' => $derivedEntry->id,
            'syllabus_objective_id' => $objective->id,
        ]);

        $audit = StandardSchemeWorkflowAudit::query()
            ->where('standard_scheme_id', $standardScheme->id)
            ->where('action', StandardSchemeWorkflowAudit::ACTION_DISTRIBUTED)
            ->latest('created_at')
            ->firstOrFail();

        $this->assertSame($schemeAdmin->id, $audit->actor_id);

        $this->flushSession();

        $this->actingAs($teacher)
            ->putJson(route('schemes.entries.update', [$derivedScheme, $derivedEntry]), [
                'topic' => 'Teacher Edit Attempt',
                'sub_topic' => 'Should Fail',
                'learning_objectives' => '<ul><li>Should fail</li></ul>',
                'status' => 'planned',
            ])
            ->assertForbidden()
            ->assertJsonPath('message', 'This entry is managed by the standard scheme and cannot be edited individually.');

    }

    public function test_publish_notifies_newly_assigned_teacher_by_email_and_direct_message(): void
    {
        Mail::fake();

        [
            'teacher' => $teacher,
            'subject' => $subject,
            'grade' => $grade,
            'term' => $term,
            'department' => $department,
        ] = $this->createTeacherSchemeContext([
            'create_scheme' => false,
        ]);

        $teacher->update([
            'firstname' => 'Casey',
            'lastname' => 'Teacher',
            'email' => 'casey.teacher@example.com',
        ]);

        $schemeAdmin = $this->createUserWithRole('Scheme Admin', [
            'firstname' => 'Morgan',
            'lastname' => 'Publisher',
            'email' => 'morgan.publisher@example.com',
        ]);

        $standardScheme = app(StandardSchemeService::class)->createWithEntries([
            'subject_id' => $subject->id,
            'grade_id' => $grade->id,
            'term_id' => $term->id,
            'department_id' => $department->id,
            'total_weeks' => 10,
        ], $schemeAdmin->id);

        $this->actingAs($schemeAdmin)
            ->post(route('standard-schemes.publish', $standardScheme))
            ->assertRedirect(route('standard-schemes.show', $standardScheme))
            ->assertSessionHas('success');

        $derivedScheme = SchemeOfWork::query()
            ->where('standard_scheme_id', $standardScheme->id)
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

        Mail::assertQueued(StandardSchemeDistributedMail::class, function (StandardSchemeDistributedMail $mail) use ($teacher, $subject, $grade, $term, $derivedScheme, $standardScheme, $schemeAdmin): bool {
            return $mail->hasTo($teacher->email)
                && $mail->recipient->is($teacher)
                && $mail->standardScheme->is($standardScheme)
                && $mail->publisher->is($schemeAdmin)
                && count($mail->schemeItems) === 1
                && $mail->schemeItems[0]['scheme_id'] === $derivedScheme->id
                && $mail->schemeItems[0]['label'] === 'Class F1A'
                && $mail->mailSubject === sprintf(
                    'New Scheme of Work Available: %s %s Term %s %s',
                    $subject->name,
                    $grade->name,
                    $term->term,
                    $term->year
                );
        });

        $this->assertDatabaseHas('staff_direct_conversations', [
            'user_one_id' => min($schemeAdmin->id, $teacher->id),
            'user_two_id' => max($schemeAdmin->id, $teacher->id),
        ]);

        $messageBody = DB::table('staff_direct_messages')->value('body');

        $this->assertNotNull($messageBody);
        $this->assertStringContainsString('Morgan Publisher', $messageBody);
        $this->assertStringContainsString($subject->name, $messageBody);
        $this->assertStringContainsString($grade->name, $messageBody);
        $this->assertStringContainsString('Class F1A', $messageBody);
        $this->assertStringContainsString((string) route('schemes.show', $derivedScheme), $messageBody);
    }

    public function test_manual_distribution_aggregates_multiple_new_schemes_for_the_same_teacher_into_one_notification(): void
    {
        Mail::fake();

        [
            'teacher' => $teacher,
            'subject' => $subject,
            'grade' => $grade,
            'term' => $term,
            'department' => $department,
            'gradeSubject' => $gradeSubject,
        ] = $this->createTeacherSchemeContext([
            'create_scheme' => false,
        ]);

        $teacher->update([
            'firstname' => 'Casey',
            'lastname' => 'Teacher',
            'email' => 'casey.teacher@example.com',
        ]);

        $secondKlass = Klass::query()->create([
            'name' => 'F1B',
            'user_id' => $teacher->id,
            'term_id' => $term->id,
            'grade_id' => $grade->id,
            'year' => $term->year,
        ]);

        KlassSubject::query()->create([
            'klass_id' => $secondKlass->id,
            'grade_subject_id' => $gradeSubject->id,
            'user_id' => $teacher->id,
            'term_id' => $term->id,
            'grade_id' => $grade->id,
            'year' => $term->year,
            'active' => true,
        ]);

        $schemeAdmin = $this->createUserWithRole('Scheme Admin', [
            'firstname' => 'Morgan',
            'lastname' => 'Publisher',
            'email' => 'morgan.publisher@example.com',
        ]);

        $standardScheme = app(StandardSchemeService::class)->createWithEntries([
            'subject_id' => $subject->id,
            'grade_id' => $grade->id,
            'term_id' => $term->id,
            'department_id' => $department->id,
            'total_weeks' => 10,
        ], $schemeAdmin->id);

        $standardScheme->update(['status' => 'approved']);

        $this->actingAs($schemeAdmin)
            ->post(route('standard-schemes.distribute', $standardScheme))
            ->assertRedirect(route('standard-schemes.show', $standardScheme))
            ->assertSessionHas('success');

        $derivedSchemes = SchemeOfWork::query()
            ->where('standard_scheme_id', $standardScheme->id)
            ->where('teacher_id', $teacher->id)
            ->orderBy('klass_subject_id')
            ->get();

        $this->assertCount(2, $derivedSchemes);

        Mail::assertQueued(StandardSchemeDistributedMail::class, function (StandardSchemeDistributedMail $mail) use ($teacher): bool {
            $labels = collect($mail->schemeItems)->pluck('label')->all();

            sort($labels);

            return $mail->hasTo($teacher->email)
                && $mail->recipient->is($teacher)
                && $labels === ['Class F1A', 'Class F1B'];
        });
        Mail::assertQueued(StandardSchemeDistributedMail::class, 1);

        $this->assertDatabaseCount('staff_direct_conversations', 1);
        $this->assertDatabaseCount('staff_direct_messages', 1);

        $messageBody = DB::table('staff_direct_messages')->value('body');

        $this->assertNotNull($messageBody);
        $this->assertStringContainsString('Class F1A', $messageBody);
        $this->assertStringContainsString('Class F1B', $messageBody);
    }

    public function test_distribution_skips_assignments_that_already_have_an_active_scheme(): void
    {
        Mail::fake();

        [
            'teacher' => $teacher,
            'subject' => $subject,
            'grade' => $grade,
            'term' => $term,
            'department' => $department,
            'klassSubject' => $klassSubject,
            'scheme' => $existingScheme,
        ] = $this->createTeacherSchemeContext();

        $schemeAdmin = $this->createUserWithRole('Scheme Admin');

        $standardScheme = app(StandardSchemeService::class)->createWithEntries([
            'subject_id' => $subject->id,
            'grade_id' => $grade->id,
            'term_id' => $term->id,
            'department_id' => $department->id,
            'total_weeks' => 10,
        ], $schemeAdmin->id);

        $standardScheme->update(['status' => 'approved']);

        $createdCount = app(StandardSchemeService::class)->distributeToTeachers($standardScheme, $schemeAdmin);

        $this->assertSame(0, $createdCount);
        $this->assertSame(1, SchemeOfWork::query()->where('klass_subject_id', $klassSubject->id)->where('term_id', $term->id)->count());
        $this->assertNull($existingScheme->fresh()->standard_scheme_id);
        $this->assertDatabaseMissing('schemes_of_work', [
            'standard_scheme_id' => $standardScheme->id,
            'teacher_id' => $teacher->id,
            'klass_subject_id' => $klassSubject->id,
            'term_id' => $term->id,
        ]);
        Mail::assertNothingQueued();
        $this->assertDatabaseCount('staff_direct_messages', 0);
    }

    public function test_distribution_skips_self_direct_message_but_still_queues_email_for_the_publisher_teacher(): void
    {
        Mail::fake();

        [
            'teacher' => $teacher,
            'subject' => $subject,
            'grade' => $grade,
            'term' => $term,
            'department' => $department,
        ] = $this->createTeacherSchemeContext([
            'create_scheme' => false,
        ]);

        $teacher->update([
            'firstname' => 'Morgan',
            'lastname' => 'Teacher',
            'email' => 'morgan.teacher@example.com',
        ]);

        $schemeAdminRole = Role::query()->firstOrCreate(
            ['name' => 'Scheme Admin'],
            ['description' => 'Scheme Admin']
        );
        $teacher->roles()->syncWithoutDetaching([$schemeAdminRole->id]);

        $standardScheme = app(StandardSchemeService::class)->createWithEntries([
            'subject_id' => $subject->id,
            'grade_id' => $grade->id,
            'term_id' => $term->id,
            'department_id' => $department->id,
            'total_weeks' => 10,
        ], $teacher->id);

        $this->actingAs($teacher)
            ->post(route('standard-schemes.publish', $standardScheme))
            ->assertRedirect(route('standard-schemes.show', $standardScheme))
            ->assertSessionHas('success');

        Mail::assertQueued(StandardSchemeDistributedMail::class, function (StandardSchemeDistributedMail $mail) use ($teacher): bool {
            return $mail->hasTo($teacher->email)
                && $mail->recipient->is($teacher);
        });

        $this->assertDatabaseCount('staff_direct_messages', 0);
    }

    public function test_distribution_skips_direct_message_notifications_when_feature_is_disabled(): void
    {
        Mail::fake();
        $this->seedNotificationSettings([
            'features.email_enabled' => '1',
            'features.staff_direct_messages_enabled' => '0',
        ]);

        [
            'teacher' => $teacher,
            'subject' => $subject,
            'grade' => $grade,
            'term' => $term,
            'department' => $department,
        ] = $this->createTeacherSchemeContext([
            'create_scheme' => false,
        ]);

        $teacher->update(['email' => 'casey.teacher@example.com']);

        $schemeAdmin = $this->createUserWithRole('Scheme Admin');

        $standardScheme = app(StandardSchemeService::class)->createWithEntries([
            'subject_id' => $subject->id,
            'grade_id' => $grade->id,
            'term_id' => $term->id,
            'department_id' => $department->id,
            'total_weeks' => 10,
        ], $schemeAdmin->id);

        $standardScheme->update(['status' => 'approved']);

        app(StandardSchemeService::class)->distributeToTeachers($standardScheme, $schemeAdmin);

        Mail::assertQueued(StandardSchemeDistributedMail::class);
        $this->assertDatabaseCount('staff_direct_messages', 0);
    }

    public function test_distribution_skips_email_notifications_when_email_feature_is_disabled(): void
    {
        Mail::fake();
        $this->seedNotificationSettings([
            'features.email_enabled' => '0',
            'features.staff_direct_messages_enabled' => '1',
        ]);

        [
            'teacher' => $teacher,
            'subject' => $subject,
            'grade' => $grade,
            'term' => $term,
            'department' => $department,
        ] = $this->createTeacherSchemeContext([
            'create_scheme' => false,
        ]);

        $teacher->update(['email' => 'casey.teacher@example.com']);

        $schemeAdmin = $this->createUserWithRole('Scheme Admin');

        $standardScheme = app(StandardSchemeService::class)->createWithEntries([
            'subject_id' => $subject->id,
            'grade_id' => $grade->id,
            'term_id' => $term->id,
            'department_id' => $department->id,
            'total_weeks' => 10,
        ], $schemeAdmin->id);

        $standardScheme->update(['status' => 'approved']);

        app(StandardSchemeService::class)->distributeToTeachers($standardScheme, $schemeAdmin);

        Mail::assertNothingQueued();
        $this->assertDatabaseCount('staff_direct_messages', 1);
    }

    public function test_distribution_skips_email_notifications_when_teacher_email_is_invalid(): void
    {
        Mail::fake();

        [
            'teacher' => $teacher,
            'subject' => $subject,
            'grade' => $grade,
            'term' => $term,
            'department' => $department,
        ] = $this->createTeacherSchemeContext([
            'create_scheme' => false,
        ]);

        $teacher->update(['email' => 'invalid-email']);

        $schemeAdmin = $this->createUserWithRole('Scheme Admin');

        $standardScheme = app(StandardSchemeService::class)->createWithEntries([
            'subject_id' => $subject->id,
            'grade_id' => $grade->id,
            'term_id' => $term->id,
            'department_id' => $department->id,
            'total_weeks' => 10,
        ], $schemeAdmin->id);

        $standardScheme->update(['status' => 'approved']);

        app(StandardSchemeService::class)->distributeToTeachers($standardScheme, $schemeAdmin);

        Mail::assertNothingQueued();
        $this->assertDatabaseCount('staff_direct_messages', 1);
    }

    public function test_distribution_skips_all_notifications_for_inactive_assigned_teacher(): void
    {
        Mail::fake();

        [
            'teacher' => $teacher,
            'subject' => $subject,
            'grade' => $grade,
            'term' => $term,
            'department' => $department,
        ] = $this->createTeacherSchemeContext([
            'create_scheme' => false,
        ]);

        $teacher->update([
            'active' => false,
            'email' => 'inactive.teacher@example.com',
        ]);

        $schemeAdmin = $this->createUserWithRole('Scheme Admin');

        $standardScheme = app(StandardSchemeService::class)->createWithEntries([
            'subject_id' => $subject->id,
            'grade_id' => $grade->id,
            'term_id' => $term->id,
            'department_id' => $department->id,
            'total_weeks' => 10,
        ], $schemeAdmin->id);

        $standardScheme->update(['status' => 'approved']);

        app(StandardSchemeService::class)->distributeToTeachers($standardScheme, $schemeAdmin);

        $this->assertDatabaseHas('schemes_of_work', [
            'standard_scheme_id' => $standardScheme->id,
            'teacher_id' => $teacher->id,
        ]);
        Mail::assertNothingQueued();
        $this->assertDatabaseCount('staff_direct_messages', 0);
    }

    public function test_notification_failures_do_not_break_publish_distribution_or_workflow_audit(): void
    {
        $this->seedNotificationSettings([
            'features.email_enabled' => '1',
            'features.staff_direct_messages_enabled' => '1',
        ]);

        [
            'teacher' => $teacher,
            'subject' => $subject,
            'grade' => $grade,
            'term' => $term,
            'department' => $department,
        ] = $this->createTeacherSchemeContext([
            'create_scheme' => false,
        ]);

        $teacher->update(['email' => 'casey.teacher@example.com']);

        $schemeAdmin = $this->createUserWithRole('Scheme Admin');

        $this->mock(StaffMessagingService::class, function ($mock): void {
            $mock->shouldReceive('startConversation')
                ->once()
                ->andThrow(new \RuntimeException('Direct message failure'));
        });

        Mail::shouldReceive('to')
            ->once()
            ->andReturnSelf();
        Mail::shouldReceive('queue')
            ->once()
            ->andThrow(new \RuntimeException('Mail queue failure'));

        $standardScheme = app(StandardSchemeService::class)->createWithEntries([
            'subject_id' => $subject->id,
            'grade_id' => $grade->id,
            'term_id' => $term->id,
            'department_id' => $department->id,
            'total_weeks' => 10,
        ], $schemeAdmin->id);

        $this->actingAs($schemeAdmin)
            ->post(route('standard-schemes.publish', $standardScheme))
            ->assertRedirect(route('standard-schemes.show', $standardScheme))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('schemes_of_work', [
            'standard_scheme_id' => $standardScheme->id,
            'teacher_id' => $teacher->id,
        ]);
        $this->assertDatabaseHas('standard_scheme_workflow_audits', [
            'standard_scheme_id' => $standardScheme->id,
            'action' => StandardSchemeWorkflowAudit::ACTION_PUBLISHED,
        ]);
        $this->assertDatabaseHas('standard_scheme_workflow_audits', [
            'standard_scheme_id' => $standardScheme->id,
            'action' => StandardSchemeWorkflowAudit::ACTION_DISTRIBUTED,
        ]);
        $this->assertDatabaseCount('staff_direct_messages', 0);
    }

    /**
     * @return array{teacher: User, subject: Subject, grade: Grade, term: Term, department: Department, gradeSubject: GradeSubject, klassSubject: KlassSubject, scheme: SchemeOfWork|null, syllabus: Syllabus}
     */
    private function createTeacherSchemeContext(array $syllabusOverrides = []): array
    {
        $teacher = $this->createUserWithRole('Teacher');
        $createScheme = $syllabusOverrides['create_scheme'] ?? true;
        unset($syllabusOverrides['create_scheme']);

        $term = Term::query()->firstOrCreate(
            [
                'term' => 1,
                'year' => (int) now()->format('Y'),
            ],
            [
                'start_date' => now()->subMonth()->toDateString(),
                'end_date' => now()->addMonth()->toDateString(),
                'closed' => false,
                'extension_days' => 0,
            ]
        );

        $grade = Grade::query()->create([
            'sequence' => 1,
            'name' => 'F1',
            'promotion' => 'F2',
            'description' => 'Form 1',
            'level' => 'Junior',
            'active' => true,
            'term_id' => $term->id,
            'year' => $term->year,
        ]);

        $subject = Subject::query()->create([
            'abbrev' => 'ENG',
            'name' => 'English',
            'level' => 'Junior',
            'components' => false,
            'description' => 'English',
            'department' => 'Languages',
        ]);

        $department = Department::query()->create([
            'name' => 'Languages',
        ]);

        $gradeSubject = GradeSubject::query()->create([
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

        $klass = Klass::query()->create([
            'name' => 'F1A',
            'user_id' => $teacher->id,
            'term_id' => $term->id,
            'grade_id' => $grade->id,
            'year' => $term->year,
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

        $syllabus = Syllabus::query()->create(array_merge([
            'subject_id' => $subject->id,
            'grades' => [$grade->name],
            'level' => $grade->level,
            'is_active' => true,
            'description' => 'English syllabus',
            'source_url' => null,
            'cached_structure' => null,
            'cached_at' => null,
        ], $syllabusOverrides));

        $scheme = null;
        if ($createScheme) {
            $scheme = SchemeOfWork::query()->create([
                'klass_subject_id' => $klassSubject->id,
                'term_id' => $term->id,
                'teacher_id' => $teacher->id,
                'status' => 'draft',
                'total_weeks' => 10,
            ]);
        }

        return [
            'teacher' => $teacher,
            'subject' => $subject,
            'grade' => $grade,
            'term' => $term,
            'department' => $department,
            'gradeSubject' => $gradeSubject,
            'klassSubject' => $klassSubject,
            'scheme' => $scheme,
            'syllabus' => $syllabus,
        ];
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

    private function seedNotificationSettings(array $overrides = []): void
    {
        $settings = [
            'features.email_enabled' => [
                'value' => '1',
                'category' => 'feature',
                'type' => 'boolean',
                'display_name' => 'Enable Email',
                'description' => 'Allow system email notifications.',
                'validation_rules' => 'required|boolean',
                'display_order' => 100,
            ],
            'features.staff_direct_messages_enabled' => [
                'value' => '1',
                'category' => 'feature',
                'type' => 'boolean',
                'display_name' => 'Enable Staff Direct Messaging',
                'description' => 'Allow internal staff direct messaging.',
                'validation_rules' => 'required|boolean',
                'display_order' => 101,
            ],
            'features.staff_presence_launcher_enabled' => [
                'value' => '1',
                'category' => 'feature',
                'type' => 'boolean',
                'display_name' => 'Enable Staff Presence Launcher',
                'description' => 'Allow the online staff launcher.',
                'validation_rules' => 'required|boolean',
                'display_order' => 102,
            ],
            'internal_messaging.online_window_minutes' => [
                'value' => '2',
                'category' => 'internal_messaging',
                'type' => 'integer',
                'display_name' => 'Online Window (minutes)',
                'description' => 'How long a heartbeat keeps a staff member online.',
                'validation_rules' => 'required|integer|min:1|max:60',
                'display_order' => 1,
            ],
        ];

        foreach ($overrides as $key => $value) {
            if (isset($settings[$key])) {
                $settings[$key]['value'] = (string) $value;
            }
        }

        foreach ($settings as $key => $setting) {
            DB::table('s_m_s_api_settings')->updateOrInsert(
                ['key' => $key],
                array_merge($setting, [
                    'key' => $key,
                    'is_editable' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        app(SettingsService::class)->refresh();
    }

    /**
     * @return array<string, mixed>
     */
    private function remotePayload(string $title): array
    {
        return [
            'syllabus' => [
                'title' => $title,
                'sections' => [
                    [
                        'form' => 'Form 1',
                        'units' => [
                            [
                                'id' => '1.1',
                                'title' => 'Listening',
                                'topics' => [
                                    [
                                        'title' => 'Pronunciation',
                                        'general_objectives' => [
                                            'appreciate different speech sounds and patterns in English',
                                        ],
                                        'specific_objectives' => [
                                            'distinguish between different speech sounds correctly',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
