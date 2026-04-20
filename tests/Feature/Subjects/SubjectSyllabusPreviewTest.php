<?php

namespace Tests\Feature\Subjects;

use App\Http\Middleware\BlockNonAfricanCountries;
use App\Models\Department;
use App\Models\Grade;
use App\Models\GradeSubject;
use App\Models\Role;
use App\Models\Schemes\Syllabus;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SubjectSyllabusPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_endpoint_returns_cached_syllabus_structure_for_grade_subject(): void
    {
        ['grade' => $grade, 'subject' => $subject, 'gradeSubject' => $gradeSubject] = $this->createPreviewContext();
        $user = $this->createAcademicUser();

        Syllabus::create([
            'subject_id' => $subject->id,
            'grades' => [$grade->name],
            'level' => $grade->level,
            'is_active' => true,
            'source_url' => 'https://example.com/syllabi/english.json',
            'cached_structure' => $this->remotePayload('English Junior Syllabus')['syllabus'],
            'cached_at' => now()->subMinute(),
        ]);

        $response = $this->withoutMiddleware(BlockNonAfricanCountries::class)
            ->actingAs($user)
            ->getJson(route('subjects.syllabus-preview', $gradeSubject));

        $response
            ->assertOk()
            ->assertJsonPath('title', 'English Junior Syllabus')
            ->assertJsonPath('subject_name', 'English')
            ->assertJsonPath('grade_name', 'F1')
            ->assertJsonPath('source_url', 'https://example.com/syllabi/english.json')
            ->assertJsonPath('structure.sections.0.form', 'Form 1')
            ->assertJsonPath('structure.sections.0.units.0.title', 'Listening')
            ->assertJsonPath('structure.sections.0.units.0.topics.0.title', 'Pronunciation');
    }

    public function test_preview_endpoint_fetches_subject_source_url_when_no_local_syllabus_exists(): void
    {
        $sourceUrl = 'https://example.com/syllabi/science.json';
        $user = $this->createAcademicUser();

        Http::fake([
            $sourceUrl => Http::response(
                json_encode($this->remotePayload('Science Junior Syllabus')),
                200,
                ['Content-Type' => 'application/json']
            ),
        ]);

        ['gradeSubject' => $gradeSubject] = $this->createPreviewContext([
            'name' => 'Science',
            'abbrev' => 'SCI',
            'syllabus_url' => $sourceUrl,
        ]);

        $response = $this->withoutMiddleware(BlockNonAfricanCountries::class)
            ->actingAs($user)
            ->getJson(route('subjects.syllabus-preview', $gradeSubject));

        $response
            ->assertOk()
            ->assertJsonPath('title', 'Science Junior Syllabus')
            ->assertJsonPath('subject_name', 'Science')
            ->assertJsonPath('source_url', $sourceUrl)
            ->assertJsonPath('structure.sections.0.units.0.topics.0.title', 'Pronunciation');

        Http::assertSentCount(1);
    }

    /**
     * @param array<string, mixed> $subjectOverrides
     * @return array{grade: Grade, subject: Subject, gradeSubject: GradeSubject}
     */
    private function createPreviewContext(array $subjectOverrides = []): array
    {
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

        $grade = Grade::query()->firstOrCreate(
            [
                'name' => 'F1',
                'term_id' => $term->id,
                'year' => $term->year,
            ],
            [
                'sequence' => 1,
                'promotion' => 'F2',
                'description' => 'Form 1',
                'level' => 'Junior',
                'active' => true,
            ]
        );

        $subjectAttributes = array_merge([
            'abbrev' => 'ENG',
            'name' => 'English',
            'level' => 'Junior',
            'components' => false,
            'description' => 'English Language',
            'department' => 'Languages',
            'syllabus_url' => null,
        ], $subjectOverrides);

        $subject = Subject::query()->updateOrCreate(
            [
                'abbrev' => $subjectAttributes['abbrev'],
                'level' => $subjectAttributes['level'],
            ],
            $subjectAttributes
        );

        $department = Department::query()->firstOrCreate([
            'name' => 'Languages',
        ]);

        $gradeSubject = GradeSubject::query()->firstOrCreate(
            [
                'grade_id' => $grade->id,
                'subject_id' => $subject->id,
                'term_id' => $term->id,
            ],
            [
                'sequence' => 1,
                'department_id' => $department->id,
                'year' => $term->year,
                'type' => true,
                'mandatory' => true,
                'active' => true,
            ]
        );

        return [
            'grade' => $grade,
            'subject' => $subject,
            'gradeSubject' => $gradeSubject,
        ];
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

    private function createAcademicUser(): User
    {
        $user = User::factory()->create([
            'area_of_work' => 'Teaching',
            'status' => 'Current',
            'active' => true,
        ]);

        $role = Role::query()->firstOrCreate(
            ['name' => 'HOD'],
            ['description' => 'Head of Department']
        );

        $user->roles()->syncWithoutDetaching([$role->id]);

        return $user->fresh();
    }
}
