<?php

namespace Tests\Feature\Schemes;

use App\Models\Department;
use App\Models\Grade;
use App\Models\GradeSubject;
use App\Models\Klass;
use App\Models\KlassSubject;
use App\Models\Role;
use App\Models\Schemes\SchemeOfWork;
use App\Models\Schemes\StandardScheme;
use App\Models\Schemes\Syllabus;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RemoteSyllabusCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_scheme_show_fetches_and_caches_remote_syllabus_then_reuses_cached_copy(): void
    {
        Http::fake([
            'https://example.com/syllabi/english.json' => Http::response(
                json_encode($this->remotePayload('Remote English')),
                200,
                ['Content-Type' => 'application/json']
            ),
        ]);

        ['teacher' => $teacher, 'syllabus' => $syllabus, 'scheme' => $scheme] = $this->createSchemeContext([
            'source_url' => 'https://example.com/syllabi/english.json',
        ]);

        $this->actingAs($teacher)
            ->get(route('schemes.show', $scheme))
            ->assertOk()
            ->assertSee('Remote English')
            ->assertSee('Pronunciation')
            ->assertSee('Target week')
            ->assertSee('No published reference scheme is available for this subject right now, so the syllabus is shown instead.')
            ->assertSee('Search topics, sub-topics, or objectives');

        $this->assertNotNull($syllabus->fresh()->cached_at);
        $this->assertSame('Remote English', $syllabus->fresh()->cached_structure['title']);

        $this->actingAs($teacher)
            ->get(route('schemes.show', $scheme))
            ->assertOk()
            ->assertSee('Remote English');

        Http::assertSentCount(1);
    }

    public function test_scheme_show_prefers_published_reference_scheme_before_syllabus_fallback(): void
    {
        Http::fake([
            'https://example.com/syllabi/english.json' => Http::response(
                json_encode($this->remotePayload('Remote English')),
                200,
                ['Content-Type' => 'application/json']
            ),
        ]);

        ['teacher' => $teacher, 'syllabus' => $syllabus, 'scheme' => $scheme] = $this->createSchemeContext([
            'source_url' => 'https://example.com/syllabi/english.json',
        ]);

        $scheme->load('klassSubject');

        $referenceTeacher = $this->createSyllabusEditor('Scheme Admin');
        $scheme->load('klassSubject.gradeSubject');
        $gradeSubject = $scheme->klassSubject->gradeSubject;

        $referenceScheme = StandardScheme::create([
            'subject_id' => $gradeSubject->subject_id,
            'grade_id' => $scheme->klassSubject->grade_id,
            'term_id' => $scheme->term_id,
            'department_id' => $gradeSubject->department_id,
            'created_by' => $referenceTeacher->id,
            'panel_lead_id' => $referenceTeacher->id,
            'status' => 'approved',
            'total_weeks' => 10,
            'published_at' => now(),
            'published_by' => $referenceTeacher->id,
        ]);

        $referenceScheme->entries()->create([
            'week_number' => 1,
            'topic' => 'Published Topic',
            'sub_topic' => 'Published Sub-topic',
            'learning_objectives' => '<ul><li>Use spoken examples confidently</li></ul>',
            'status' => 'planned',
        ]);

        $this->actingAs($teacher)
            ->get(route('schemes.show', $scheme))
            ->assertOk()
            ->assertSee('Browse Scheme')
            ->assertSee('Published Standard Scheme')
            ->assertSee('Published Topic')
            ->assertSee('Published Sub-topic')
            ->assertSee('Use the published reference scheme below while planning each week.')
            ->assertDontSee('Remote English');
    }

    public function test_entry_update_can_persist_topic_and_linked_objectives_together(): void
    {
        ['teacher' => $teacher, 'syllabus' => $syllabus, 'scheme' => $scheme] = $this->createSchemeContext();

        $topic = $syllabus->topics()->create([
            'sequence' => 1,
            'name' => 'Pronunciation',
            'description' => 'Section: Form 1 | Unit: 1.1 Listening',
            'suggested_weeks' => null,
        ]);

        $objectiveA = $topic->objectives()->create([
            'sequence' => 1,
            'code' => '11-G1',
            'objective_text' => 'appreciate different speech sounds and patterns in English',
            'cognitive_level' => 'Knowledge',
        ]);

        $objectiveB = $topic->objectives()->create([
            'sequence' => 2,
            'code' => '11-S1',
            'objective_text' => 'distinguish between different speech sounds correctly',
            'cognitive_level' => 'Application',
        ]);

        $entry = $scheme->entries()->create([
            'week_number' => 1,
            'status' => 'planned',
        ]);

        $response = $this->actingAs($teacher)->putJson(route('schemes.entries.update', [$scheme, $entry]), [
            'topic' => 'Listening',
            'sub_topic' => 'Pronunciation',
            'learning_objectives' => '<ul><li>appreciate different speech sounds and patterns in English</li><li>distinguish between different speech sounds correctly</li></ul>',
            'syllabus_topic_id' => $topic->id,
            'objective_ids' => [$objectiveA->id, $objectiveB->id],
            'status' => 'planned',
        ]);

        $response->assertOk()
            ->assertJsonPath('entry.topic', 'Listening')
            ->assertJsonPath('entry.sub_topic', 'Pronunciation')
            ->assertJsonPath('entry.syllabus_topic_id', $topic->id)
            ->assertJsonCount(2, 'entry.objectives');

        $entry->refresh();

        $this->assertSame('Listening', $entry->topic);
        $this->assertSame('Pronunciation', $entry->sub_topic);
        $this->assertSame($topic->id, $entry->syllabus_topic_id);
        $this->assertDatabaseHas('scheme_entry_objectives', [
            'scheme_of_work_entry_id' => $entry->id,
            'syllabus_objective_id' => $objectiveA->id,
        ]);
        $this->assertDatabaseHas('scheme_entry_objectives', [
            'scheme_of_work_entry_id' => $entry->id,
            'syllabus_objective_id' => $objectiveB->id,
        ]);
    }

    public function test_refresh_route_updates_cached_remote_syllabus(): void
    {
        Http::fake([
            'https://example.com/syllabi/english.json' => Http::response(
                json_encode($this->remotePayload('Fresh English')),
                200,
                ['Content-Type' => 'application/json']
            ),
        ]);

        ['teacher' => $teacher, 'syllabus' => $syllabus] = $this->createSchemeContext([
            'source_url' => 'https://example.com/syllabi/english.json',
        ]);
        $editor = $this->createSyllabusEditor('HOD');

        $this->actingAs($editor)
            ->post(route('syllabi.refresh-cache', $syllabus))
            ->assertRedirect(route('syllabi.edit', $syllabus))
            ->assertSessionHas('success');

        $syllabus->refresh();

        $this->assertNotNull($syllabus->cached_at);
        $this->assertSame('Fresh English', $syllabus->cached_structure['title']);
        $this->assertCount(1, $syllabus->topics);
        $this->assertSame('Pronunciation', $syllabus->topics->first()->name);
        $this->assertCount(2, $syllabus->topics->first()->objectives);
    }

    public function test_populate_from_cache_route_imports_topics_and_objectives_from_existing_cache(): void
    {
        ['teacher' => $teacher, 'syllabus' => $syllabus] = $this->createSchemeContext([
            'cached_structure' => $this->remotePayload('Existing Cached English')['syllabus'],
            'cached_at' => now()->subHour(),
        ]);
        $editor = $this->createSyllabusEditor('HOD');

        $this->actingAs($editor)
            ->post(route('syllabi.populate-from-cache', $syllabus))
            ->assertRedirect(route('syllabi.edit', $syllabus))
            ->assertSessionHas('success');

        $syllabus->refresh()->load('topics.objectives');

        $this->assertCount(1, $syllabus->topics);
        $this->assertSame('Pronunciation', $syllabus->topics->first()->name);
        $this->assertSame(
            [
                'appreciate different speech sounds and patterns in English',
                'distinguish between different speech sounds correctly',
            ],
            $syllabus->topics->first()->objectives->pluck('objective_text')->all()
        );
    }

    public function test_populate_from_cache_route_imports_nested_subtopics_without_losing_objectives(): void
    {
        ['teacher' => $teacher, 'syllabus' => $syllabus] = $this->createSchemeContext([
            'cached_structure' => $this->nestedRemotePayload('Nested English')['syllabus'],
            'cached_at' => now()->subHour(),
        ]);
        $editor = $this->createSyllabusEditor('HOD');

        $this->actingAs($editor)
            ->post(route('syllabi.populate-from-cache', $syllabus))
            ->assertRedirect(route('syllabi.edit', $syllabus))
            ->assertSessionHas('success');

        $syllabus->refresh()->load('topics.objectives');

        $this->assertSame(
            ['Pronunciation', 'Vowels'],
            $syllabus->topics->pluck('name')->all()
        );
        $this->assertSame(
            'Section: Form 1 | Unit: [1.1] Listening | Path: Pronunciation > Vowels',
            $syllabus->topics->last()->description
        );
        $this->assertSame(
            ['identify speech sounds', 'differentiate short vowel sounds in familiar words'],
            $syllabus->topics->flatMap->objectives->pluck('objective_text')->all()
        );
    }

    public function test_sync_from_cache_route_updates_matching_rows_in_place_and_deletes_unlinked_stale_rows(): void
    {
        ['teacher' => $teacher, 'syllabus' => $syllabus, 'scheme' => $scheme] = $this->createSchemeContext([
            'cached_structure' => $this->remotePayload('Synced English')['syllabus'],
            'cached_at' => now()->subHour(),
        ]);
        $editor = $this->createSyllabusEditor('HOD');

        $topic = $syllabus->topics()->create([
            'sequence' => 1,
            'name' => 'Phonetics',
            'description' => 'Old imported topic',
            'suggested_weeks' => null,
        ]);

        $matchedObjective = $topic->objectives()->create([
            'sequence' => 1,
            'code' => '11-G1',
            'objective_text' => 'Outdated wording',
            'cognitive_level' => 'Knowledge',
        ]);

        $staleObjective = $topic->objectives()->create([
            'sequence' => 2,
            'code' => 'LEG-1',
            'objective_text' => 'Legacy objective to remove',
            'cognitive_level' => 'Analysis',
        ]);

        $staleTopic = $syllabus->topics()->create([
            'sequence' => 2,
            'name' => 'Obsolete Topic',
            'description' => 'Should be removed',
            'suggested_weeks' => null,
        ]);

        $staleTopic->objectives()->create([
            'sequence' => 1,
            'code' => 'OLD-1',
            'objective_text' => 'Old topic objective',
            'cognitive_level' => 'Comprehension',
        ]);

        $entry = $scheme->entries()->create([
            'week_number' => 1,
            'syllabus_topic_id' => $topic->id,
            'status' => 'planned',
        ]);
        $entry->objectives()->attach($matchedObjective->id);

        $this->actingAs($editor)
            ->post(route('syllabi.sync-from-cache', $syllabus))
            ->assertRedirect(route('syllabi.edit', $syllabus))
            ->assertSessionHas('success');

        $syncedTopic = $topic->fresh()->load('objectives');
        $syncedObjective = $matchedObjective->fresh();

        $this->assertSame('Pronunciation', $syncedTopic->name);
        $this->assertSame('Section: Form 1 | Unit: [1.1] Listening | Path: Pronunciation', $syncedTopic->description);
        $this->assertSame(1, $syncedTopic->sequence);
        $this->assertCount(2, $syncedTopic->objectives);

        $this->assertSame(
            'appreciate different speech sounds and patterns in English',
            $syncedObjective->objective_text
        );
        $this->assertSame('Knowledge', $syncedObjective->cognitive_level);
        $this->assertDatabaseHas('scheme_entry_objectives', [
            'scheme_of_work_entry_id' => $entry->id,
            'syllabus_objective_id' => $matchedObjective->id,
        ]);
        $this->assertDatabaseMissing('syllabus_objectives', ['id' => $staleObjective->id]);
        $this->assertDatabaseMissing('syllabus_topics', ['id' => $staleTopic->id]);
    }

    public function test_preview_sync_from_cache_route_shows_diff_without_persisting_changes(): void
    {
        ['teacher' => $teacher, 'syllabus' => $syllabus] = $this->createSchemeContext([
            'cached_structure' => $this->remotePayload('Preview English')['syllabus'],
            'cached_at' => now()->subHour(),
        ]);
        $editor = $this->createSyllabusEditor('HOD');

        $topic = $syllabus->topics()->create([
            'sequence' => 1,
            'name' => 'Phonetics',
            'description' => 'Old imported topic',
            'suggested_weeks' => null,
        ]);

        $objective = $topic->objectives()->create([
            'sequence' => 1,
            'code' => '11-G1',
            'objective_text' => 'Outdated wording',
            'cognitive_level' => 'Knowledge',
        ]);

        $response = $this->actingAs($editor)
            ->post(route('syllabi.preview-sync-from-cache', $syllabus));

        $response->assertOk()
            ->assertSee('Sync Preview')
            ->assertSee('Dry run only')
            ->assertSee('Topic Updates')
            ->assertSee('Objective Updates')
            ->assertSee('Phonetics')
            ->assertSee('Pronunciation');

        $this->assertSame('Phonetics', $topic->fresh()->name);
        $this->assertSame('Outdated wording', $objective->fresh()->objective_text);
        $this->assertDatabaseMissing('syllabus_topics', ['name' => 'Pronunciation']);
    }

    public function test_sync_from_cache_route_preserves_linked_legacy_rows_not_present_in_remote_json(): void
    {
        ['teacher' => $teacher, 'syllabus' => $syllabus, 'scheme' => $scheme] = $this->createSchemeContext([
            'cached_structure' => $this->remotePayload('Synced English')['syllabus'],
            'cached_at' => now()->subHour(),
        ]);
        $editor = $this->createSyllabusEditor('HOD');

        $legacyTopic = $syllabus->topics()->create([
            'sequence' => 1,
            'name' => 'Legacy Topic',
            'description' => 'Must stay linked',
            'suggested_weeks' => null,
        ]);

        $legacyObjective = $legacyTopic->objectives()->create([
            'sequence' => 1,
            'code' => 'LEG-9',
            'objective_text' => 'Keep this linked objective',
            'cognitive_level' => 'Application',
        ]);

        $entry = $scheme->entries()->create([
            'week_number' => 1,
            'syllabus_topic_id' => $legacyTopic->id,
            'status' => 'planned',
        ]);
        $entry->objectives()->attach($legacyObjective->id);

        $this->actingAs($editor)
            ->post(route('syllabi.sync-from-cache', $syllabus))
            ->assertRedirect(route('syllabi.edit', $syllabus))
            ->assertSessionHas('success');

        $syllabus->refresh()->load('topics.objectives');

        $this->assertCount(2, $syllabus->topics);
        $this->assertSame(
            ['Pronunciation', 'Legacy Topic'],
            $syllabus->topics->pluck('name')->all()
        );
        $this->assertSame(2, $legacyTopic->fresh()->sequence);
        $this->assertNotNull($legacyObjective->fresh());
        $this->assertDatabaseHas('scheme_entry_objectives', [
            'scheme_of_work_entry_id' => $entry->id,
            'syllabus_objective_id' => $legacyObjective->id,
        ]);
    }

    public function test_invalid_remote_json_does_not_overwrite_last_good_cached_copy(): void
    {
        Http::fake([
            'https://example.com/syllabi/english.json' => Http::response('not-json', 200, ['Content-Type' => 'application/json']),
        ]);

        ['teacher' => $teacher, 'syllabus' => $syllabus] = $this->createSchemeContext([
            'source_url' => 'https://example.com/syllabi/english.json',
            'cached_structure' => $this->remotePayload('Existing English')['syllabus'],
            'cached_at' => now()->subDay(),
        ]);
        $editor = $this->createSyllabusEditor('HOD');

        $this->actingAs($editor)
            ->post(route('syllabi.refresh-cache', $syllabus))
            ->assertRedirect(route('syllabi.edit', $syllabus))
            ->assertSessionHas('error');

        $syllabus->refresh();

        $this->assertSame('Existing English', $syllabus->cached_structure['title']);
        $this->assertSame('Pronunciation', $syllabus->cached_structure['sections'][0]['units'][0]['topics'][0]['title']);
    }

    public function test_scheme_show_displays_unavailable_state_when_remote_fetch_fails_without_cache_or_pdf(): void
    {
        Http::fake([
            'https://example.com/syllabi/english.json' => Http::response('', 500),
        ]);

        ['teacher' => $teacher, 'syllabus' => $syllabus, 'scheme' => $scheme] = $this->createSchemeContext([
            'source_url' => 'https://example.com/syllabi/english.json',
        ]);

        $this->actingAs($teacher)
            ->get(route('schemes.show', $scheme))
            ->assertOk()
            ->assertSee('Syllabus Unavailable');

        $this->assertNull($syllabus->fresh()->cached_at);
    }

    public function test_scheme_show_renders_nested_subtopics_and_objectives_from_cached_structure(): void
    {
        ['teacher' => $teacher, 'syllabus' => $syllabus, 'scheme' => $scheme] = $this->createSchemeContext([
            'cached_structure' => $this->nestedRemotePayload('Nested English')['syllabus'],
            'cached_at' => now()->subHour(),
        ]);

        $this->actingAs($teacher)
            ->get(route('schemes.show', $scheme))
            ->assertOk()
            ->assertSee('Nested English')
            ->assertSee('Pronunciation')
            ->assertSee('Vowels')
            ->assertSee('differentiate short vowel sounds in familiar words');
    }

    public function test_entry_update_rejects_objectives_outside_the_selected_syllabus_topic_scope(): void
    {
        ['teacher' => $teacher, 'syllabus' => $syllabus, 'scheme' => $scheme] = $this->createSchemeContext();

        $selectedTopic = $syllabus->topics()->create([
            'sequence' => 1,
            'name' => 'Pronunciation',
            'description' => 'Section: Form 1 | Unit: [1.1] Listening | Path: Pronunciation',
            'suggested_weeks' => null,
        ]);
        $otherTopic = $syllabus->topics()->create([
            'sequence' => 2,
            'name' => 'Speech Etiquette',
            'description' => 'Section: Form 1 | Unit: [1.1] Listening | Path: Speech Etiquette',
            'suggested_weeks' => null,
        ]);

        $selectedObjective = $selectedTopic->objectives()->create([
            'sequence' => 1,
            'code' => 'P-1',
            'objective_text' => 'identify speech sounds',
            'cognitive_level' => 'Knowledge',
        ]);
        $otherObjective = $otherTopic->objectives()->create([
            'sequence' => 1,
            'code' => 'S-1',
            'objective_text' => 'practise respectful speaking turns',
            'cognitive_level' => 'Application',
        ]);

        $entry = $scheme->entries()->create([
            'week_number' => 1,
            'status' => 'planned',
        ]);

        $this->actingAs($teacher)
            ->putJson(route('schemes.entries.update', [$scheme, $entry]), [
                'topic' => 'Listening',
                'sub_topic' => 'Pronunciation',
                'learning_objectives' => '<ul><li>identify speech sounds</li></ul>',
                'syllabus_topic_id' => $selectedTopic->id,
                'objective_ids' => [$selectedObjective->id, $otherObjective->id],
                'status' => 'planned',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('objective_ids');
    }

    /**
     * @param array<string, mixed> $syllabusOverrides
     * @return array{teacher: User, syllabus: Syllabus, scheme: SchemeOfWork}
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
        ];
    }

    private function createSyllabusEditor(string $roleName = 'HOD'): User
    {
        $editor = User::factory()->create([
            'area_of_work' => 'Teaching',
            'status' => 'Current',
            'active' => true,
        ]);

        $role = Role::query()->firstOrCreate(
            ['name' => $roleName],
            ['description' => $roleName]
        );

        $editor->roles()->syncWithoutDetaching([$role->id]);

        return $editor->fresh();
    }

    /**
     * @return array<string, mixed>
     */
    private function remotePayload(string $title): array
    {
        return $this->remotePayloadWithTopics($title, [
            [
                'title' => 'Pronunciation',
                'general_objectives' => [
                    'appreciate different speech sounds and patterns in English',
                ],
                'specific_objectives' => [
                    'distinguish between different speech sounds correctly',
                ],
            ],
        ]);
    }

    /**
     * @param array<int, array<string, mixed>> $topics
     * @return array<string, mixed>
     */
    private function remotePayloadWithTopics(string $title, array $topics): array
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
                                'topics' => $topics,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function nestedRemotePayload(string $title): array
    {
        return $this->remotePayloadWithTopics($title, [
            [
                'title' => 'Pronunciation',
                'objectives' => [
                    'general' => [
                        [
                            'code' => 'P-1',
                            'objective_text' => 'identify speech sounds',
                            'cognitive_level' => 'Knowledge',
                        ],
                    ],
                ],
                'sub_topics' => [
                    [
                        'title' => 'Vowels',
                        'objectives' => [
                            [
                                'code' => 'V-1',
                                'text' => 'differentiate short vowel sounds in familiar words',
                                'cognitive_level' => 'Application',
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }
}
