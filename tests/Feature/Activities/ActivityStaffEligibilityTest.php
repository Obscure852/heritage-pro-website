<?php

namespace Tests\Feature\Activities;

use App\Http\Middleware\EnsureProfileComplete;
use App\Models\Activities\Activity;
use App\Models\Activities\ActivityEligibilityTarget;
use App\Models\Activities\ActivityStaffAssignment;
use App\Models\Grade;
use App\Models\House;
use App\Models\Klass;
use App\Models\Role;
use App\Models\StudentFilter;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\EnsuresActivitiesPhaseOneSchema;
use Tests\TestCase;

class ActivityStaffEligibilityTest extends TestCase
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

    public function test_activity_admin_can_manage_staff_history_and_eligibility_targets(): void
    {
        $admin = $this->createUserWithRoles('activities-phase2-admin@example.com', ['Activities Admin']);
        $coordinator = $this->createUserWithRoles('activities-phase2-coordinator@example.com', ['Teacher']);
        $assistant = $this->createUserWithRoles('activities-phase2-assistant@example.com', ['Teacher']);
        $term = $this->createTerm(2026, 1);
        $activity = $this->createActivity($term, $admin);

        $grade = Grade::query()->create([
            'sequence' => 1,
            'name' => 'F1',
            'promotion' => 'Yes',
            'description' => 'Form 1',
            'level' => 'Junior',
            'active' => true,
            'term_id' => $term->id,
            'year' => $term->year,
        ]);

        $klass = Klass::query()->create([
            'name' => 'F1 Blue',
            'user_id' => $admin->id,
            'term_id' => $term->id,
            'grade_id' => $grade->id,
            'type' => true,
            'year' => $term->year,
        ]);

        $house = House::query()->create([
            'name' => 'Kgosi',
            'head' => $admin->id,
            'assistant' => $coordinator->id,
            'term_id' => $term->id,
            'year' => $term->year,
        ]);

        $studentFilter = StudentFilter::query()->create([
            'name' => 'Boarders',
        ]);

        $this->actingAs($admin)
            ->post(route('activities.staff.store', $activity), [
                'user_id' => $coordinator->id,
                'role' => ActivityStaffAssignment::ROLE_COORDINATOR,
                'is_primary' => '1',
                'notes' => 'Lead coordinator for the term.',
            ])
            ->assertRedirect(route('activities.staff.index', $activity));

        $assistantAssignmentResponse = $this->actingAs($admin)
            ->post(route('activities.staff.store', $activity), [
                'user_id' => $assistant->id,
                'role' => ActivityStaffAssignment::ROLE_ASSISTANT,
                'notes' => 'Supports logistics.',
            ]);

        $assistantAssignmentResponse->assertRedirect(route('activities.staff.index', $activity));

        $assistantAssignment = ActivityStaffAssignment::query()
            ->where('activity_id', $activity->id)
            ->where('user_id', $assistant->id)
            ->where('role', ActivityStaffAssignment::ROLE_ASSISTANT)
            ->active()
            ->firstOrFail();

        $this->actingAs($admin)
            ->delete(route('activities.staff.destroy', [$activity, $assistantAssignment]))
            ->assertRedirect(route('activities.staff.index', $activity));

        $this->actingAs($admin)
            ->post(route('activities.staff.store', $activity), [
                'user_id' => $assistant->id,
                'role' => ActivityStaffAssignment::ROLE_ASSISTANT,
                'notes' => 'Reassigned after schedule change.',
            ])
            ->assertRedirect(route('activities.staff.index', $activity));

        $this->assertSame(
            2,
            ActivityStaffAssignment::query()
                ->where('activity_id', $activity->id)
                ->where('user_id', $assistant->id)
                ->where('role', ActivityStaffAssignment::ROLE_ASSISTANT)
                ->count()
        );

        $this->actingAs($admin)
            ->put(route('activities.eligibility.update', $activity), [
                'grades' => [$grade->id],
                'klasses' => [$klass->id],
                'houses' => [$house->id],
                'student_filters' => [$studentFilter->id],
            ])
            ->assertRedirect(route('activities.eligibility.edit', $activity));

        $this->assertDatabaseHas('activity_eligibility_targets', [
            'activity_id' => $activity->id,
            'target_type' => ActivityEligibilityTarget::TARGET_GRADE,
            'target_id' => $grade->id,
        ]);

        $this->assertDatabaseHas('activity_eligibility_targets', [
            'activity_id' => $activity->id,
            'target_type' => ActivityEligibilityTarget::TARGET_CLASS,
            'target_id' => $klass->id,
        ]);

        $this->assertDatabaseHas('activity_eligibility_targets', [
            'activity_id' => $activity->id,
            'target_type' => ActivityEligibilityTarget::TARGET_HOUSE,
            'target_id' => $house->id,
        ]);

        $this->assertDatabaseHas('activity_eligibility_targets', [
            'activity_id' => $activity->id,
            'target_type' => ActivityEligibilityTarget::TARGET_STUDENT_FILTER,
            'target_id' => $studentFilter->id,
        ]);

        $this->actingAs($admin)
            ->get(route('activities.staff.index', $activity))
            ->assertOk()
            ->assertSee($coordinator->full_name)
            ->assertSee('Primary Coordinator')
            ->assertSee($assistant->full_name);

        $this->actingAs($admin)
            ->get(route('activities.eligibility.edit', $activity))
            ->assertOk()
            ->assertSee('F1')
            ->assertSee('F1 Blue')
            ->assertSee('Kgosi')
            ->assertSee('Boarders');

        $this->actingAs($admin)
            ->post(route('activities.activate', $activity))
            ->assertRedirect(route('activities.show', $activity));

        $this->assertSame(Activity::STATUS_ACTIVE, $activity->fresh()->status);

        $this->assertDatabaseHas('activity_audit_logs', [
            'entity_type' => Activity::class,
            'entity_id' => $activity->id,
            'action' => 'staff_assigned',
        ]);

        $this->assertDatabaseHas('activity_audit_logs', [
            'entity_type' => Activity::class,
            'entity_id' => $activity->id,
            'action' => 'staff_removed',
        ]);

        $this->assertDatabaseHas('activity_audit_logs', [
            'entity_type' => Activity::class,
            'entity_id' => $activity->id,
            'action' => 'eligibility_updated',
        ]);
    }

    public function test_activity_cannot_activate_without_primary_coordinator(): void
    {
        $admin = $this->createUserWithRoles('activities-phase2-activate@example.com', ['Activities Admin']);
        $term = $this->createTerm(2026, 2);
        $activity = $this->createActivity($term, $admin, 'ROBOTICS-01', 'Robotics Club');

        $this->actingAs($admin)
            ->from(route('activities.show', $activity))
            ->post(route('activities.activate', $activity))
            ->assertRedirect(route('activities.show', $activity))
            ->assertSessionHasErrors('status');

        $this->assertSame(Activity::STATUS_DRAFT, $activity->fresh()->status);
    }

    public function test_eligibility_edit_deduplicates_repeated_grade_class_house_and_filter_options(): void
    {
        $admin = $this->createUserWithRoles('activities-phase2-dedupe@example.com', ['Activities Admin']);
        $assistant = $this->createUserWithRoles('activities-phase2-dedupe-assistant@example.com', ['Teacher']);
        $term = $this->createTerm(2026, 3);
        $activity = $this->createActivity($term, $admin, 'SCIENCE-01', 'Science Club');

        $gradeOne = Grade::query()->create([
            'sequence' => 1,
            'name' => 'F1',
            'promotion' => 'Yes',
            'description' => 'Form 1',
            'level' => 'Junior',
            'active' => true,
            'term_id' => $term->id,
            'year' => $term->year,
        ]);

        $gradeTwo = Grade::query()->create([
            'sequence' => 2,
            'name' => 'F1',
            'promotion' => 'Yes',
            'description' => 'Form 1 duplicate',
            'level' => 'Junior',
            'active' => true,
            'term_id' => $term->id,
            'year' => $term->year,
        ]);

        $klassOne = Klass::query()->create([
            'name' => '1A',
            'user_id' => $admin->id,
            'term_id' => $term->id,
            'grade_id' => $gradeOne->id,
            'type' => true,
            'year' => $term->year,
        ]);

        Klass::query()->create([
            'name' => '1A',
            'user_id' => $admin->id,
            'term_id' => $term->id,
            'grade_id' => $gradeTwo->id,
            'type' => true,
            'year' => $term->year,
        ]);

        $houseOne = House::query()->create([
            'name' => 'Kgosi',
            'head' => $admin->id,
            'assistant' => $assistant->id,
            'term_id' => $term->id,
            'year' => $term->year,
        ]);

        House::query()->create([
            'name' => 'Kgosi',
            'head' => $admin->id,
            'assistant' => $assistant->id,
            'term_id' => $term->id,
            'year' => $term->year,
        ]);

        $studentFilterOne = StudentFilter::query()->create([
            'name' => 'Boarders',
        ]);

        $studentFilterTwo = StudentFilter::query()->create([
            'name' => 'Boarders',
        ]);

        ActivityEligibilityTarget::query()->create([
            'activity_id' => $activity->id,
            'target_type' => ActivityEligibilityTarget::TARGET_GRADE,
            'target_id' => $gradeTwo->id,
        ]);

        ActivityEligibilityTarget::query()->create([
            'activity_id' => $activity->id,
            'target_type' => ActivityEligibilityTarget::TARGET_CLASS,
            'target_id' => $klassOne->id,
        ]);

        ActivityEligibilityTarget::query()->create([
            'activity_id' => $activity->id,
            'target_type' => ActivityEligibilityTarget::TARGET_HOUSE,
            'target_id' => $houseOne->id,
        ]);

        ActivityEligibilityTarget::query()->create([
            'activity_id' => $activity->id,
            'target_type' => ActivityEligibilityTarget::TARGET_STUDENT_FILTER,
            'target_id' => $studentFilterTwo->id,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('activities.eligibility.edit', $activity));

        $response->assertOk();

        $content = $response->getContent();

        $this->assertSame(1, preg_match_all('/<option[^>]*>\s*F1\s*<\/option>/', $content));
        $this->assertSame(1, preg_match_all('/<option[^>]*>\s*1A \(F1\)\s*<\/option>/', $content));
        $this->assertSame(1, preg_match_all('/<option[^>]*>\s*Kgosi\s*<\/option>/', $content));
        $this->assertSame(1, preg_match_all('/<option[^>]*>\s*Boarders\s*<\/option>/', $content));
        $this->assertSame(1, preg_match_all('/<span class="summary-chip">\s*F1\s*<\/span>/', $content));
        $this->assertSame(1, preg_match_all('/<span class="summary-chip">\s*1A \(F1\)\s*<\/span>/', $content));
        $this->assertSame(1, preg_match_all('/<span class="summary-chip">\s*Kgosi\s*<\/span>/', $content));
        $this->assertSame(1, preg_match_all('/<span class="summary-chip">\s*Boarders\s*<\/span>/', $content));
    }

    private function createUserWithRoles(string $email, array $roles, array $overrides = []): User
    {
        $resolvedEmail = $email;

        if (User::query()->where('email', $resolvedEmail)->exists()) {
            [$localPart, $domainPart] = array_pad(explode('@', $resolvedEmail, 2), 2, 'example.com');
            $resolvedEmail = sprintf('%s+%s@%s', $localPart, uniqid('activity-phase2-', true), $domainPart);
        }

        $user = User::withoutEvents(fn () => User::query()->create(array_merge([
            'firstname' => 'Activity',
            'lastname' => 'Phase Two Tester',
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

    private function createActivity(Term $term, User $user, string $code = 'CHESS-01', string $name = 'Chess Tournament'): Activity
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
            'description' => 'Phase 2 test activity.',
            'default_location' => 'School Hall',
            'capacity' => 40,
            'gender_policy' => 'mixed',
            'attendance_required' => true,
            'allow_house_linkage' => true,
            'status' => Activity::STATUS_DRAFT,
            'term_id' => $term->id,
            'year' => $term->year,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }
}
