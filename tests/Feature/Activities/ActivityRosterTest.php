<?php

namespace Tests\Feature\Activities;

use App\Http\Middleware\EnsureProfileComplete;
use App\Models\Activities\Activity;
use App\Models\Activities\ActivityEligibilityTarget;
use App\Models\Activities\ActivityEnrollment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Session\Middleware\AuthenticateSession;
use Tests\Concerns\BuildsActivitiesRosterFixtures;
use Tests\Concerns\EnsuresActivitiesPhaseOneSchema;
use Tests\TestCase;

class ActivityRosterTest extends TestCase
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

    public function test_activity_admin_can_add_student_manually_and_move_enrollment_to_history(): void
    {
        $admin = $this->createActivityUser('activities-roster-admin@example.com', ['Activities Admin']);
        $assistant = $this->createActivityUser('activities-roster-assistant@example.com', ['Teacher']);
        $term = $this->createActivityTerm(2026, 1);
        $grade = $this->createGradeForTerm($term);
        $klass = $this->createKlassForTerm($term, $grade, $admin);
        $house = $this->createHouseForTerm($term, $admin, $assistant);
        $filter = $this->createStudentFilter();
        $activity = $this->createActivityRecord($term, $admin);
        $student = $this->createStudentForActivity($term, $grade, $klass, $house, $filter, [
            'first_name' => 'Kabelo',
            'last_name' => 'Molefe',
        ]);

        $this->actingAs($admin)
            ->post(route('activities.roster.store', $activity), [
                'student_id' => $student->id,
                'joined_at' => '2026-01-15',
            ])
            ->assertRedirect(route('activities.roster.index', $activity));

        $enrollment = ActivityEnrollment::query()->where('activity_id', $activity->id)->firstOrFail();

        $this->assertSame(ActivityEnrollment::STATUS_ACTIVE, $enrollment->status);
        $this->assertSame(ActivityEnrollment::SOURCE_MANUAL, $enrollment->source);
        $this->assertSame($grade->id, $enrollment->grade_id_snapshot);
        $this->assertSame($klass->id, $enrollment->klass_id_snapshot);
        $this->assertSame($house->id, $enrollment->house_id_snapshot);

        $this->actingAs($admin)
            ->patch(route('activities.roster.update', [$activity, $enrollment]), [
                'status' => ActivityEnrollment::STATUS_WITHDRAWN,
                'left_at' => '2026-02-01',
                'exit_reason' => 'Moved to robotics roster.',
            ])
            ->assertRedirect(route('activities.roster.index', $activity));

        $this->assertDatabaseHas('activity_enrollments', [
            'id' => $enrollment->id,
            'status' => ActivityEnrollment::STATUS_WITHDRAWN,
            'exit_reason' => 'Moved to robotics roster.',
        ]);

        $this->actingAs($admin)
            ->get(route('activities.roster.index', $activity))
            ->assertOk()
            ->assertSee('Kabelo Molefe')
            ->assertSee('Moved to robotics roster.')
            ->assertSee('Roster History');

        $this->assertDatabaseHas('activity_audit_logs', [
            'entity_type' => Activity::class,
            'entity_id' => $activity->id,
            'action' => 'enrollment_added',
        ]);

        $this->assertDatabaseHas('activity_audit_logs', [
            'entity_type' => Activity::class,
            'entity_id' => $activity->id,
            'action' => 'enrollment_status_changed',
        ]);
    }

    public function test_bulk_enrollment_uses_saved_eligibility_targets_and_export_includes_enrolled_students(): void
    {
        $admin = $this->createActivityUser('activities-roster-bulk-admin@example.com', ['Activities Admin']);
        $assistant = $this->createActivityUser('activities-roster-bulk-assistant@example.com', ['Teacher']);
        $term = $this->createActivityTerm(2026, 2);
        $grade = $this->createGradeForTerm($term, 'F2');
        $otherGrade = $this->createGradeForTerm($term, 'F3', 2);
        $klass = $this->createKlassForTerm($term, $grade, $admin, 'F2 Blue');
        $otherKlass = $this->createKlassForTerm($term, $otherGrade, $admin, 'F3 Green');
        $house = $this->createHouseForTerm($term, $admin, $assistant, 'Tawana');
        $otherHouse = $this->createHouseForTerm($term, $assistant, $admin, 'Sechele');
        $filter = $this->createStudentFilter('Day Scholars');
        $otherFilter = $this->createStudentFilter('Boarders');
        $activity = $this->createActivityRecord($term, $admin, ['capacity' => 10]);

        $this->attachEligibilityTargets($activity, [
            ActivityEligibilityTarget::TARGET_GRADE => [$grade->id],
            ActivityEligibilityTarget::TARGET_CLASS => [$klass->id],
            ActivityEligibilityTarget::TARGET_HOUSE => [$house->id],
            ActivityEligibilityTarget::TARGET_STUDENT_FILTER => [$filter->id],
        ]);

        $matchingOne = $this->createStudentForActivity($term, $grade, $klass, $house, $filter, [
            'first_name' => 'Neo',
            'last_name' => 'Bantsi',
        ]);

        $matchingTwo = $this->createStudentForActivity($term, $grade, $klass, $house, $filter, [
            'first_name' => 'Mpho',
            'last_name' => 'Tau',
        ]);

        $nonMatching = $this->createStudentForActivity($term, $otherGrade, $otherKlass, $otherHouse, $otherFilter, [
            'first_name' => 'Lebo',
            'last_name' => 'Kelebile',
        ]);

        $rosterResponse = $this->actingAs($admin)
            ->get(route('activities.roster.index', $activity));

        $rosterResponse
            ->assertOk()
            ->assertSee('Neo Bantsi')
            ->assertSee('Mpho Tau');

        $this->assertStringContainsString('id="bulk-student-search"', $rosterResponse->getContent());
        $this->assertStringContainsString('placeholder="Search by student first name or last name"', $rosterResponse->getContent());
        $this->assertStringContainsString('<span class="summary-chip pill-muted">F2</span>', $rosterResponse->getContent());
        $this->assertStringContainsString('<span class="summary-chip pill-muted">F2 Blue</span>', $rosterResponse->getContent());

        $this->actingAs($admin)
            ->post(route('activities.roster.bulk-store', $activity), [
                'student_ids' => [$matchingOne->id, $matchingTwo->id],
                'joined_at' => '2026-04-10',
            ])
            ->assertRedirect(route('activities.roster.index', $activity));

        $this->assertDatabaseHas('activity_enrollments', [
            'activity_id' => $activity->id,
            'student_id' => $matchingOne->id,
            'source' => ActivityEnrollment::SOURCE_BULK_FILTER,
        ]);

        $this->assertDatabaseHas('activity_enrollments', [
            'activity_id' => $activity->id,
            'student_id' => $matchingTwo->id,
            'source' => ActivityEnrollment::SOURCE_BULK_FILTER,
        ]);

        $this->assertDatabaseMissing('activity_enrollments', [
            'activity_id' => $activity->id,
            'student_id' => $nonMatching->id,
        ]);

        $this->actingAs($admin)
            ->get(route('activities.roster.index', $activity))
            ->assertOk()
            ->assertSee('Neo Bantsi')
            ->assertSee('Mpho Tau');

        $this->actingAs($admin)
            ->get(route('activities.roster.export', $activity))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->assertSee('Neo Bantsi')
            ->assertSee('Mpho Tau')
            ->assertDontSee('Lebo Kelebile');
    }

    public function test_assigned_activities_staff_can_view_roster_but_cannot_change_it(): void
    {
        $editor = $this->createActivityUser('activities-roster-editor@example.com', ['Activities Admin']);
        $staffUser = $this->createActivityUser('activities-roster-staff@example.com', ['Activities Staff']);
        $assistant = $this->createActivityUser('activities-roster-staff-helper@example.com', ['Teacher']);
        $term = $this->createActivityTerm(2026, 3);
        $grade = $this->createGradeForTerm($term, 'F4');
        $klass = $this->createKlassForTerm($term, $grade, $editor, 'F4 Gold');
        $house = $this->createHouseForTerm($term, $editor, $assistant, 'Khama');
        $activity = $this->createActivityRecord($term, $editor);
        $student = $this->createStudentForActivity($term, $grade, $klass, $house, null, [
            'first_name' => 'Tebogo',
            'last_name' => 'Raletobana',
        ]);

        $this->assignPrimaryCoordinator($activity, $staffUser);

        ActivityEnrollment::query()->create([
            'activity_id' => $activity->id,
            'student_id' => $student->id,
            'term_id' => $term->id,
            'year' => $term->year,
            'status' => ActivityEnrollment::STATUS_ACTIVE,
            'joined_at' => now(),
            'joined_by' => $editor->id,
            'source' => ActivityEnrollment::SOURCE_MANUAL,
            'grade_id_snapshot' => $grade->id,
            'klass_id_snapshot' => $klass->id,
            'house_id_snapshot' => $house->id,
        ]);

        $this->actingAs($staffUser)
            ->get(route('activities.roster.index', $activity))
            ->assertOk()
            ->assertSee('Tebogo Raletobana')
            ->assertSee('Roster Access');

        $this->actingAs($staffUser)
            ->post(route('activities.roster.store', $activity), [
                'student_id' => $student->id,
            ])
            ->assertForbidden();
    }
}
