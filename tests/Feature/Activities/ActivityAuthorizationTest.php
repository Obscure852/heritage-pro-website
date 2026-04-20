<?php

namespace Tests\Feature\Activities;

use App\Http\Middleware\EnsureProfileComplete;
use App\Models\Activities\Activity;
use App\Models\Activities\ActivityStaffAssignment;
use App\Models\Role;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\EnsuresActivitiesPhaseOneSchema;
use Tests\TestCase;

class ActivityAuthorizationTest extends TestCase
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

    public function test_activities_staff_user_can_only_open_assigned_activities(): void
    {
        $manager = $this->createUserWithRoles('activities-manager-auth@example.com', ['Activities Admin']);
        $staffOperator = $this->createUserWithRoles('activities-staff-auth@example.com', ['Activities Staff']);
        $term = $this->createTerm(2026, 1);

        $assignedActivity = $this->createActivity($term, $manager, 'CHESS-01', 'Chess Club');
        $unassignedActivity = $this->createActivity($term, $manager, 'DEBATE-01', 'Debate Club');

        ActivityStaffAssignment::query()->create([
            'activity_id' => $assignedActivity->id,
            'user_id' => $staffOperator->id,
            'role' => ActivityStaffAssignment::ROLE_COACH,
            'is_primary' => false,
            'active' => true,
            'assigned_at' => now(),
        ]);

        $this->actingAs($staffOperator)
            ->get(route('activities.index'))
            ->assertOk()
            ->assertSee('Chess Club')
            ->assertDontSee('Debate Club');

        $this->actingAs($staffOperator)
            ->get(route('activities.show', $assignedActivity))
            ->assertOk()
            ->assertSee('Chess Club');

        $this->actingAs($staffOperator)
            ->get(route('activities.show', $unassignedActivity))
            ->assertForbidden();
    }

    private function createUserWithRoles(string $email, array $roles, array $overrides = []): User
    {
        $resolvedEmail = $email;

        if (User::query()->where('email', $resolvedEmail)->exists()) {
            [$localPart, $domainPart] = array_pad(explode('@', $resolvedEmail, 2), 2, 'example.com');
            $resolvedEmail = sprintf('%s+%s@%s', $localPart, uniqid('activity-auth-', true), $domainPart);
        }

        $user = User::withoutEvents(fn () => User::query()->create(array_merge([
            'firstname' => 'Activity',
            'lastname' => 'Authorization Tester',
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

    private function createActivity(Term $term, User $user, string $code, string $name): Activity
    {
        $resolvedCode = strtoupper($code);

        if (Activity::query()->where('year', $term->year)->where('code', $resolvedCode)->exists()) {
            $resolvedCode .= '-' . strtoupper(substr(md5(uniqid((string) $term->id, true)), 0, 4));
        }

        return Activity::query()->create([
            'name' => $name,
            'code' => $resolvedCode,
            'category' => Activity::CATEGORY_CLUB,
            'delivery_mode' => Activity::DELIVERY_RECURRING,
            'participation_mode' => Activity::PARTICIPATION_TEAM,
            'result_mode' => Activity::RESULT_MIXED,
            'description' => 'Operational authorization test activity.',
            'default_location' => 'Hall 1',
            'capacity' => 30,
            'gender_policy' => 'mixed',
            'attendance_required' => true,
            'allow_house_linkage' => false,
            'status' => Activity::STATUS_DRAFT,
            'term_id' => $term->id,
            'year' => $term->year,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }
}
