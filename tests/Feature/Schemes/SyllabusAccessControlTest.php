<?php

namespace Tests\Feature\Schemes;

use App\Models\Grade;
use App\Models\Role;
use App\Models\Schemes\Syllabus;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyllabusAccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_sees_syllabus_edit_page_in_read_only_mode_and_no_delete_action(): void
    {
        $teacher = $this->createUserWithRole('Teacher');
        $syllabus = $this->createSyllabus();

        $topic = $syllabus->topics()->create([
            'sequence' => 1,
            'name' => 'Listening',
            'description' => 'Week 1 topic',
            'suggested_weeks' => 1,
        ]);

        $topic->objectives()->create([
            'sequence' => 1,
            'code' => 'L-1',
            'objective_text' => 'Identify key sounds.',
            'cognitive_level' => 'Knowledge',
        ]);

        $this->actingAs($teacher)
            ->get(route('syllabi.edit', $syllabus))
            ->assertOk()
            ->assertSee('View Syllabus')
            ->assertSee('Read-only access')
            ->assertDontSee('Save Changes');

        $this->actingAs($teacher)
            ->get(route('syllabi.index'))
            ->assertOk();
    }

    public function test_teacher_cannot_update_delete_or_mutate_syllabus_topics(): void
    {
        $teacher = $this->createUserWithRole('Teacher');
        $syllabus = $this->createSyllabus();

        $payload = [
            'subject_id' => $syllabus->subject_id,
            'grades' => $syllabus->grades,
            'level' => $syllabus->level,
            'description' => 'Teacher edit attempt',
            'is_active' => true,
            'source_url' => null,
            'document_id' => null,
        ];

        $this->actingAs($teacher)
            ->put(route('syllabi.update', $syllabus), $payload)
            ->assertForbidden();

        $this->actingAs($teacher)
            ->delete(route('syllabi.destroy', $syllabus))
            ->assertForbidden();

        $this->actingAs($teacher)
            ->postJson(route('syllabi.topics.store', $syllabus), [
                'sequence' => 1,
                'name' => 'Speaking',
                'description' => 'Teacher should not add this',
                'suggested_weeks' => 1,
            ])
            ->assertForbidden();
    }

    public function test_hod_can_update_and_delete_syllabus(): void
    {
        $hod = $this->createUserWithRole('HOD');
        $syllabus = $this->createSyllabus();

        $this->actingAs($hod)
            ->put(route('syllabi.update', $syllabus), [
                'subject_id' => $syllabus->subject_id,
                'grades' => $syllabus->grades,
                'level' => $syllabus->level,
                'description' => 'Updated by HOD',
                'is_active' => true,
                'source_url' => null,
                'document_id' => null,
            ])
            ->assertRedirect(route('syllabi.edit', $syllabus))
            ->assertSessionHas('success');

        $this->assertSame('Updated by HOD', $syllabus->fresh()->description);

        $this->actingAs($hod)
            ->delete(route('syllabi.destroy', $syllabus))
            ->assertRedirect(route('syllabi.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('syllabi', ['id' => $syllabus->id]);
    }

    private function createUserWithRole(string $roleName): User
    {
        $user = User::factory()->create([
            'area_of_work' => 'Teaching',
            'status' => 'Current',
            'active' => true,
        ]);

        $role = Role::query()->firstOrCreate(
            ['name' => $roleName],
            ['description' => $roleName]
        );

        $user->roles()->syncWithoutDetaching([$role->id]);

        return $user->fresh();
    }

    private function createSyllabus(): Syllabus
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
            'description' => 'English subject',
            'department' => 'Languages',
        ]);

        return Syllabus::query()->create([
            'subject_id' => $subject->id,
            'grades' => [$grade->name],
            'level' => $grade->level,
            'description' => 'Junior English syllabus',
            'is_active' => true,
            'source_url' => null,
            'document_id' => null,
            'cached_structure' => null,
            'cached_at' => null,
        ]);
    }
}
