<?php

namespace Tests\Feature\Activities;

use App\Http\Middleware\EnsureProfileComplete;
use App\Models\Activities\Activity;
use App\Models\Activities\ActivityStaffAssignment;
use App\Models\Fee\FeeType;
use App\Models\Role;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\EnsuresActivitiesPhaseOneSchema;
use Tests\TestCase;

class ActivityManagementTest extends TestCase
{
    use DatabaseTransactions;
    use EnsuresActivitiesPhaseOneSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(EnsureProfileComplete::class);
        $this->withoutMiddleware(AuthenticateSession::class);
        $this->withoutMiddleware(\App\Http\Middleware\Authenticate::class);
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
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

    public function test_activities_view_user_can_access_read_only_pages(): void
    {
        $user = $this->createUserWithRoles('activities-view@example.com', ['Activities View']);
        $term = $this->createTerm(2026, 1);
        $activity = $this->createActivity($term, $user);

        $this->actingAs($user)
            ->get(route('activities.index'))
            ->assertOk()
            ->assertSee('Activities Manager');

        $this->actingAs($user)
            ->get(route('activities.show', $activity))
            ->assertOk()
            ->assertSee($activity->name);

        $this->actingAs($user)
            ->get(route('activities.create'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('activities.edit', $activity))
            ->assertForbidden();
    }

    public function test_unrelated_user_cannot_access_activities_module(): void
    {
        $user = $this->createUserWithRoles('teacher-only@example.com', ['Teacher']);

        $this->actingAs($user)
            ->get(route('activities.index'))
            ->assertForbidden();
    }

    public function test_activity_admin_can_create_update_and_transition_activity(): void
    {
        $user = $this->createUserWithRoles('activities-admin@example.com', ['Activities Admin']);
        $term = $this->createTerm(2026, 2);
        $feeType = $this->createFeeType();
        $code = 'debate-' . substr(uniqid(), -6);
        $storePayload = $this->basePayload([
            'code' => $code,
            'fee_type_id' => $feeType->id,
        ]);

        $this->actingAs($user)
            ->withSession(['selected_term_id' => $term->id])
            ->post(route('activities.store'), $storePayload)
            ->assertRedirect();

        $activity = Activity::query()->where('code', strtoupper($code))->latest('id')->firstOrFail();

        $this->assertSame(Activity::STATUS_DRAFT, $activity->status);
        $this->assertSame($term->id, $activity->term_id);
        $this->assertSame((int) $term->year, (int) $activity->year);

        $this->assertDatabaseHas('activity_audit_logs', [
            'entity_type' => Activity::class,
            'entity_id' => $activity->id,
            'action' => 'created',
        ]);

        $updatePayload = array_merge($storePayload, [
            'name' => 'Debate Union',
            'capacity' => 48,
            'default_location' => 'Assembly Hall',
            'default_fee_amount' => 175.50,
        ]);

        $this->actingAs($user)
            ->put(route('activities.update', $activity), $updatePayload)
            ->assertRedirect(route('activities.show', $activity));

        $activity->refresh();

        $this->assertSame('Debate Union', $activity->name);
        $this->assertSame(48, $activity->capacity);
        $this->assertSame('Assembly Hall', $activity->default_location);
        $this->assertSame(Activity::STATUS_DRAFT, $activity->status);

        $this->actingAs($user)
            ->from(route('activities.show', $activity))
            ->post(route('activities.close', $activity))
            ->assertRedirect(route('activities.show', $activity))
            ->assertSessionHasErrors('status');

        $activity->refresh();
        $this->assertSame(Activity::STATUS_DRAFT, $activity->status);

        ActivityStaffAssignment::query()->create([
            'activity_id' => $activity->id,
            'user_id' => $user->id,
            'role' => ActivityStaffAssignment::ROLE_COORDINATOR,
            'is_primary' => true,
            'active' => true,
            'assigned_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('activities.activate', $activity))
            ->assertRedirect(route('activities.show', $activity));

        $activity->refresh();
        $this->assertSame(Activity::STATUS_ACTIVE, $activity->status);

        $this->actingAs($user)
            ->post(route('activities.pause', $activity))
            ->assertRedirect(route('activities.show', $activity));

        $activity->refresh();
        $this->assertSame(Activity::STATUS_PAUSED, $activity->status);

        $this->actingAs($user)
            ->post(route('activities.close', $activity))
            ->assertRedirect(route('activities.show', $activity));

        $activity->refresh();
        $this->assertSame(Activity::STATUS_CLOSED, $activity->status);

        $this->actingAs($user)
            ->post(route('activities.archive', $activity))
            ->assertRedirect(route('activities.show', $activity));

        $activity->refresh();
        $this->assertSame(Activity::STATUS_ARCHIVED, $activity->status);

        $this->assertDatabaseHas('activity_audit_logs', [
            'entity_type' => Activity::class,
            'entity_id' => $activity->id,
            'action' => 'updated',
        ]);

        $this->assertDatabaseHas('activity_audit_logs', [
            'entity_type' => Activity::class,
            'entity_id' => $activity->id,
            'action' => 'status_changed',
        ]);
    }

    public function test_activity_code_must_be_unique_within_the_same_year(): void
    {
        $user = $this->createUserWithRoles('activities-edit@example.com', ['Activities Edit']);
        $term2026 = $this->createTerm(2026, 1);
        $term2027 = $this->createTerm(2027, 1);
        $code = 'debate-' . substr(uniqid(), -6);

        $this->actingAs($user)
            ->withSession(['selected_term_id' => $term2026->id])
            ->post(route('activities.store'), $this->basePayload(['code' => $code]))
            ->assertRedirect();

        $this->actingAs($user)
            ->withSession(['selected_term_id' => $term2026->id])
            ->from(route('activities.create'))
            ->post(route('activities.store'), $this->basePayload([
                'name' => 'Duplicate Debate',
                'code' => $code,
            ]))
            ->assertRedirect(route('activities.create'))
            ->assertSessionHasErrors('code');

        $this->actingAs($user)
            ->withSession(['selected_term_id' => $term2027->id])
            ->post(route('activities.store'), $this->basePayload([
                'name' => 'Debate Club 2027',
                'code' => $code,
            ]))
            ->assertRedirect();

        $this->assertSame(
            2,
            Activity::query()->where('code', strtoupper($code))->count()
        );
    }

    private function createUserWithRoles(string $email, array $roles, array $overrides = []): User
    {
        if (User::withTrashed()->where('email', $email)->exists()) {
            $email = uniqid('activities-user-', true) . '@example.com';
        }

        $user = User::withoutEvents(fn () => User::query()->create(array_merge([
            'firstname' => 'Activity',
            'lastname' => 'Tester',
            'email' => $email,
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

    private function createFeeType(): FeeType
    {
        return FeeType::query()->create([
            'code' => 'ACT-' . strtoupper(substr(md5(uniqid('fee', true)), 0, 6)),
            'name' => 'Activity Optional Fee',
            'category' => FeeType::CATEGORY_OPTIONAL,
            'description' => 'Optional activity billing',
            'is_optional' => true,
            'is_active' => true,
        ]);
    }

    private function createActivity(Term $term, User $user, array $overrides = []): Activity
    {
        $code = $overrides['code'] ?? ('DEBATE-' . strtoupper(substr(md5(uniqid((string) $term->id, true)), 0, 6)));

        return Activity::query()->create(array_merge([
            'name' => 'Debate Club',
            'code' => $code,
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
        ], $overrides));
    }

    private function basePayload(array|int|null $overrides = null): array
    {
        $payload = [
            'name' => 'Debate Club',
            'code' => 'debate-01',
            'category' => Activity::CATEGORY_CLUB,
            'delivery_mode' => Activity::DELIVERY_RECURRING,
            'participation_mode' => Activity::PARTICIPATION_TEAM,
            'result_mode' => Activity::RESULT_MIXED,
            'description' => 'Develop public speaking and argument skills.',
            'default_location' => 'Hall 1',
            'capacity' => 36,
            'gender_policy' => 'mixed',
            'attendance_required' => '1',
            'allow_house_linkage' => '1',
            'fee_type_id' => null,
            'default_fee_amount' => '150.00',
        ];

        if (is_int($overrides)) {
            $payload['fee_type_id'] = $overrides;

            return $payload;
        }

        return array_merge($payload, $overrides ?? []);
    }
}
