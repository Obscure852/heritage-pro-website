<?php

namespace Tests\Feature\Activities;

use App\Http\Middleware\EnsureProfileComplete;
use App\Models\Activities\ActivityEligibilityTarget;
use App\Models\Activities\ActivityEnrollment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Session\Middleware\AuthenticateSession;
use Tests\Concerns\BuildsActivitiesRosterFixtures;
use Tests\Concerns\EnsuresActivitiesPhaseOneSchema;
use Tests\TestCase;

class ActivityCapacityTest extends TestCase
{
    use DatabaseTransactions;
    use BuildsActivitiesRosterFixtures;
    use EnsuresActivitiesPhaseOneSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(EnsureProfileComplete::class);
        $this->withoutMiddleware(AuthenticateSession::class);
        $this->withoutMiddleware(\App\Http\Middleware\Authenticate::class);
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->ensureActivitiesPhaseOneSchema();
        $this->seedActivitiesSchoolSetup();
    }

    public function test_duplicate_active_enrollment_is_blocked(): void
    {
        $admin = $this->createActivityUser('activities-capacity-admin@example.com', ['Activities Admin']);
        $assistant = $this->createActivityUser('activities-capacity-assistant@example.com', ['Teacher']);
        $term = $this->createActivityTerm(2026, 1);
        $grade = $this->createGradeForTerm($term);
        $klass = $this->createKlassForTerm($term, $grade, $admin);
        $house = $this->createHouseForTerm($term, $admin, $assistant);
        $activity = $this->createActivityRecord($term, $admin, ['capacity' => 3]);
        $student = $this->createStudentForActivity($term, $grade, $klass, $house, null, [
            'first_name' => 'Duplicate',
            'last_name' => 'Student',
        ]);

        $this->actingAs($admin)
            ->post(route('activities.roster.store', $activity), [
                'student_id' => $student->id,
            ])
            ->assertRedirect(route('activities.roster.index', $activity));

        $this->actingAs($admin)
            ->from(route('activities.roster.index', $activity))
            ->post(route('activities.roster.store', $activity), [
                'student_id' => $student->id,
            ])
            ->assertRedirect(route('activities.roster.index', $activity))
            ->assertSessionHasErrors('student_id');

        $this->assertSame(
            1,
            ActivityEnrollment::query()
                ->where('activity_id', $activity->id)
                ->where('student_id', $student->id)
                ->where('status', ActivityEnrollment::STATUS_ACTIVE)
                ->count()
        );
    }

    public function test_capacity_blocks_single_and_bulk_enrollment_writes(): void
    {
        $admin = $this->createActivityUser('activities-capacity-limit-admin@example.com', ['Activities Admin']);
        $assistant = $this->createActivityUser('activities-capacity-limit-assistant@example.com', ['Teacher']);
        $term = $this->createActivityTerm(2026, 2);
        $grade = $this->createGradeForTerm($term, 'F2');
        $klass = $this->createKlassForTerm($term, $grade, $admin, 'F2 Red');
        $house = $this->createHouseForTerm($term, $admin, $assistant, 'Ngwato');
        $filter = $this->createStudentFilter('STEM');

        $singleCapacityActivity = $this->createActivityRecord($term, $admin, [
            'code' => 'CT002',
            'capacity' => 1,
        ]);

        $firstStudent = $this->createStudentForActivity($term, $grade, $klass, $house, $filter, [
            'first_name' => 'First',
            'last_name' => 'Seat',
        ]);

        $secondStudent = $this->createStudentForActivity($term, $grade, $klass, $house, $filter, [
            'first_name' => 'Second',
            'last_name' => 'Seat',
        ]);

        $this->actingAs($admin)
            ->post(route('activities.roster.store', $singleCapacityActivity), [
                'student_id' => $firstStudent->id,
            ])
            ->assertRedirect(route('activities.roster.index', $singleCapacityActivity));

        $this->actingAs($admin)
            ->from(route('activities.roster.index', $singleCapacityActivity))
            ->post(route('activities.roster.store', $singleCapacityActivity), [
                'student_id' => $secondStudent->id,
            ])
            ->assertRedirect(route('activities.roster.index', $singleCapacityActivity))
            ->assertSessionHasErrors('capacity');

        $bulkCapacityActivity = $this->createActivityRecord($term, $admin, [
            'code' => 'CT003',
            'capacity' => 1,
        ]);

        $this->attachEligibilityTargets($bulkCapacityActivity, [
            ActivityEligibilityTarget::TARGET_GRADE => [$grade->id],
            ActivityEligibilityTarget::TARGET_CLASS => [$klass->id],
            ActivityEligibilityTarget::TARGET_HOUSE => [$house->id],
            ActivityEligibilityTarget::TARGET_STUDENT_FILTER => [$filter->id],
        ]);

        $this->actingAs($admin)
            ->from(route('activities.roster.index', $bulkCapacityActivity))
            ->post(route('activities.roster.bulk-store', $bulkCapacityActivity), [
                'student_ids' => [$firstStudent->id, $secondStudent->id],
            ])
            ->assertRedirect(route('activities.roster.index', $bulkCapacityActivity))
            ->assertSessionHasErrors('capacity');

        $this->assertSame(
            0,
            ActivityEnrollment::query()
                ->where('activity_id', $bulkCapacityActivity->id)
                ->count()
        );
    }
}
