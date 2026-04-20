<?php

namespace Tests\Feature\Activities;

use App\Http\Middleware\EnsureProfileComplete;
use App\Models\Activities\Activity;
use App\Models\Role;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\EnsuresActivitiesPhaseOneSchema;
use Tests\TestCase;

class ActivityFormRenderingTest extends TestCase
{
    use DatabaseTransactions;
    use EnsuresActivitiesPhaseOneSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(EnsureProfileComplete::class);
        $this->withoutMiddleware(AuthenticateSession::class);
        $this->ensureActivitiesPhaseOneSchema();

        DB::table('school_setup')->updateOrInsert(
            ['id' => 1],
            [
                'school_name' => 'Merementsi Junior Secondary School',
                'type' => 'Junior',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function test_create_form_matches_admissions_style_contract(): void
    {
        $user = $this->createUserWithRoles('activities-form-create@example.com', ['Activities Admin']);
        $term = $this->createTerm(2026, 1);

        $this->actingAs($user)
            ->withSession(['selected_term_id' => $term->id])
            ->get(route('activities.create'))
            ->assertOk()
            ->assertSee('placeholder="Debate Club"', false)
            ->assertSee('placeholder="DEBATE-01"', false)
            ->assertSee('placeholder="Assembly Hall"', false)
            ->assertSee('placeholder="150.00"', false)
            ->assertSee('placeholder="Describe the purpose, structure, and expected outcomes of this activity."', false)
            ->assertSee('<i class="bx bx-x"></i> Cancel', false)
            ->assertSee('<span class="btn-text"><i class="fas fa-save"></i> Create Activity</span>', false);
    }

    public function test_edit_form_uses_same_button_and_field_shell(): void
    {
        $user = $this->createUserWithRoles('activities-form-edit@example.com', ['Activities Admin']);
        $term = $this->createTerm(2026, 2);
        $activity = $this->createActivity($term, $user);

        $this->actingAs($user)
            ->get(route('activities.edit', $activity))
            ->assertOk()
            ->assertSee('placeholder="Debate Club"', false)
            ->assertSee('placeholder="DEBATE-01"', false)
            ->assertSee('class="needs-validation"', false)
            ->assertSee('<i class="bx bx-x"></i> Cancel', false)
            ->assertSee('<span class="btn-text"><i class="fas fa-save"></i> Save Changes</span>', false)
            ->assertDontSee('Creating...');
    }

    public function test_staff_form_uses_loading_button_contract(): void
    {
        $user = $this->createUserWithRoles('activities-form-staff@example.com', ['Activities Admin']);
        $term = $this->createTerm(2026, 2);
        $activity = $this->createActivity($term, $user);

        $this->actingAs($user)
            ->get(route('activities.staff.index', $activity))
            ->assertOk()
            ->assertSee('id="activity-staff-form"', false)
            ->assertSee('data-activity-form', false)
            ->assertSee('<span class="btn-text"><i class="fas fa-save"></i> Save Assignment</span>', false)
            ->assertSee('Saving...', false);
    }

    public function test_eligibility_form_uses_loading_button_contract(): void
    {
        $user = $this->createUserWithRoles('activities-form-eligibility@example.com', ['Activities Admin']);
        $term = $this->createTerm(2026, 3);
        $activity = $this->createActivity($term, $user);

        $this->actingAs($user)
            ->get(route('activities.eligibility.edit', $activity))
            ->assertOk()
            ->assertSee('id="activity-eligibility-form"', false)
            ->assertSee('data-activity-form', false)
            ->assertSee('<span class="btn-text"><i class="fas fa-save"></i> Save Eligibility</span>', false)
            ->assertSee('Saving...', false);
    }

    public function test_index_uses_admissions_style_header_and_empty_state_action(): void
    {
        $user = $this->createUserWithRoles('activities-index-render@example.com', ['Activities Admin']);
        $term = $this->createTerm(2046, 1);

        $this->actingAs($user)
            ->withSession(['selected_term_id' => $term->id])
            ->get(route('activities.index', ['search' => 'zzzz-activities-empty-filter']))
            ->assertOk()
            ->assertSee('<h3 style="margin:0;">Activities Manager</h3>', false)
            ->assertSee('<span class="input-group-text"><i class="fas fa-search"></i></span>', false)
            ->assertSee('<i class="fas fa-plus me-1"></i> New Activity', false)
            ->assertSee('class="btn btn-light w-100">Reset</a>', false)
            ->assertSee('No activities found for the selected filters.');
    }

    public function test_index_empty_state_does_not_repeat_new_activity_button(): void
    {
        $user = $this->createUserWithRoles('activities-index-empty@example.com', ['Activities Admin']);
        $term = $this->createTerm(2035, 1);

        $response = $this->actingAs($user)
            ->withSession(['selected_term_id' => $term->id])
            ->get(route('activities.index'));

        $response->assertOk()
            ->assertSee('No activities have been created for the selected term yet.');

        $this->assertSame(
            1,
            substr_count($response->getContent(), '<i class="fas fa-plus me-1"></i> New Activity')
        );
    }

    private function createUserWithRoles(string $email, array $roles, array $overrides = []): User
    {
        $resolvedEmail = $email;

        if (User::query()->where('email', $resolvedEmail)->exists()) {
            [$localPart, $domainPart] = array_pad(explode('@', $resolvedEmail, 2), 2, 'example.com');
            $resolvedEmail = sprintf('%s+%s@%s', $localPart, uniqid('activity-form-', true), $domainPart);
        }

        $user = User::withoutEvents(fn () => User::query()->create(array_merge([
            'firstname' => 'Activity',
            'lastname' => 'Form Tester',
            'email' => $resolvedEmail,
            'password' => 'secret',
            'status' => 'Current',
            'position' => 'Teacher',
            'year' => 2026,
        ], $overrides)));

        $roleIds = collect($roles)
            ->map(fn (string $name): int => (int) Role::query()->firstOrCreate(
                ['name' => $name],
                ['description' => $name]
            )->id)
            ->all();

        $user->roles()->syncWithoutDetaching($roleIds);

        return $user->fresh();
    }

    private function createTerm(int $year, int $termNumber): Term
    {
        $attributes = [
            'term' => $termNumber,
            'year' => $year,
            'start_date' => sprintf('%d-0%d-01', $year, max(1, min(9, $termNumber))),
            'end_date' => sprintf('%d-0%d-28', $year, max(1, min(9, $termNumber))),
            'closed' => false,
        ];

        if (Schema::hasColumn('terms', 'term_type')) {
            $attributes['term_type'] = 'Academic';
        }

        return Term::query()->firstOrCreate(
            [
                'term' => $termNumber,
                'year' => $year,
            ],
            $attributes
        );
    }

    private function createActivity(Term $term, User $user): Activity
    {
        $resolvedCode = 'DEBATE-' . strtoupper(substr(md5(uniqid((string) $term->id, true)), 0, 6));

        return Activity::query()->create([
            'name' => 'Debate Club',
            'code' => $resolvedCode,
            'category' => Activity::CATEGORY_CLUB,
            'delivery_mode' => Activity::DELIVERY_RECURRING,
            'participation_mode' => Activity::PARTICIPATION_TEAM,
            'result_mode' => Activity::RESULT_MIXED,
            'description' => 'Weekly debate preparation.',
            'default_location' => 'Hall 1',
            'capacity' => 40,
            'gender_policy' => 'mixed',
            'attendance_required' => true,
            'allow_house_linkage' => true,
            'status' => Activity::STATUS_ACTIVE,
            'term_id' => $term->id,
            'year' => $term->year,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }
}
