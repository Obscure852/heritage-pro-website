<?php

namespace Tests\Feature\Activities;

use App\Http\Middleware\EnsureProfileComplete;
use App\Models\Activities\Activity;
use App\Models\Activities\ActivityEvent;
use App\Models\Activities\ActivityFeeCharge;
use App\Models\Activities\ActivityResult;
use App\Models\Activities\ActivitySession;
use App\Models\Activities\ActivitySessionAttendance;
use App\Models\Activities\ActivityStaffAssignment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Session\Middleware\AuthenticateSession;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Concerns\BuildsActivitiesRosterFixtures;
use Tests\Concerns\EnsuresActivitiesPhaseOneSchema;
use Tests\TestCase;

class ActivityReportTest extends TestCase
{
    use DatabaseTransactions;
    use BuildsActivitiesRosterFixtures;
    use EnsuresActivitiesPhaseOneSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(EnsureProfileComplete::class);
        $this->withoutMiddleware(AuthenticateSession::class);
        $this->ensureActivitiesPhaseOneSchema();
        $this->seedActivitiesSchoolSetup();
    }

    public function test_admin_can_view_activity_reports_and_export_filtered_rows(): void
    {
        $admin = $this->createActivityUser('activities-report-admin-' . uniqid() . '@example.com', ['Activities Admin']);
        $assistant = $this->createActivityUser('activities-report-assistant-' . uniqid() . '@example.com', ['Teacher']);
        $term = $this->createActivityTerm(2026, 1);
        $grade = $this->createGradeForTerm($term, 'F1');
        $klass = $this->createKlassForTerm($term, $grade, $admin, '1A');
        $house = $this->createHouseForTerm($term, $admin, $assistant, 'Tawana');
        $feeType = $this->createActivityFeeType(['name' => 'Chess Participation Fee']);

        $activity = $this->createActivityRecord($term, $admin, [
            'status' => Activity::STATUS_ACTIVE,
            'fee_type_id' => $feeType->id,
            'default_fee_amount' => '150.00',
        ]);

        $this->assignPrimaryCoordinator($activity, $admin);

        $studentOne = $this->createStudentForActivity($term, $grade, $klass, $house, null, [
            'first_name' => 'Neo',
            'last_name' => 'Molefe',
        ]);
        $studentTwo = $this->createStudentForActivity($term, $grade, $klass, $house, null, [
            'first_name' => 'Amina',
            'last_name' => 'Dube',
        ]);

        $enrollmentOne = $this->createActivityEnrollmentRecord($activity, $studentOne, $admin);
        $enrollmentTwo = $this->createActivityEnrollmentRecord($activity, $studentTwo, $admin);

        $schedule = $this->createActivityScheduleRecord($activity);
        $session = $this->createActivitySessionRecord($activity, $schedule, [
            'status' => ActivitySession::STATUS_COMPLETED,
        ]);

        ActivitySessionAttendance::query()->create([
            'activity_session_id' => $session->id,
            'activity_enrollment_id' => $enrollmentOne->id,
            'student_id' => $studentOne->id,
            'status' => ActivitySessionAttendance::STATUS_PRESENT,
            'marked_by' => $admin->id,
            'marked_at' => now(),
        ]);

        $event = $this->createActivityEventRecord($activity, $admin, [
            'status' => ActivityEvent::STATUS_COMPLETED,
            'house_linked' => true,
        ]);

        $this->createActivityResultRecord($event, $admin, [
            'participant_type' => ActivityResult::PARTICIPANT_HOUSE,
            'participant_id' => $house->id,
            'award_name' => 'Winning House',
            'points' => 6,
            'result_label' => 'Champions',
        ]);

        ActivityFeeCharge::query()->create([
            'activity_id' => $activity->id,
            'activity_enrollment_id' => $enrollmentTwo->id,
            'student_id' => $studentTwo->id,
            'fee_type_id' => $feeType->id,
            'term_id' => $term->id,
            'year' => $term->year,
            'charge_type' => ActivityFeeCharge::CHARGE_TYPE_PARTICIPATION,
            'amount' => '150.00',
            'billing_status' => ActivityFeeCharge::STATUS_PENDING,
            'generated_by' => $admin->id,
            'generated_at' => now(),
            'notes' => 'Waiting for annual invoice creation.',
        ]);

        $response = $this->withSession(['selected_term_id' => $term->id])
            ->actingAs($admin)
            ->get(route('activities.reports.index'));

        $response->assertOk()
            ->assertSee('Activity Performance Summary')
            ->assertSee('House Performance')
            ->assertSee('Chess Tournament')
            ->assertSee('Tawana')
            ->assertSee('Amina Dube')
            ->assertSee('Pending Invoice');

        Excel::fake();

        $this->withSession(['selected_term_id' => $term->id])
            ->actingAs($admin)
            ->get(route('activities.reports.export'))
            ->assertOk();

        Excel::assertDownloaded('activities-report-2026-term-1.xlsx');
    }

    public function test_activities_staff_report_scope_is_limited_to_assigned_activities(): void
    {
        $admin = $this->createActivityUser('activities-report-owner-' . uniqid() . '@example.com', ['Activities Admin']);
        $staffOperator = $this->createActivityUser('activities-report-staff-' . uniqid() . '@example.com', ['Activities Staff']);
        $term = $this->createActivityTerm(2026, 2);

        $assignedActivity = $this->createActivityRecord($term, $admin, [
            'name' => 'Chess Club',
            'code' => 'CHESS2',
            'status' => Activity::STATUS_ACTIVE,
        ]);
        $unassignedActivity = $this->createActivityRecord($term, $admin, [
            'name' => 'Debate Club',
            'code' => 'DEBATE2',
            'status' => Activity::STATUS_ACTIVE,
        ]);

        ActivityStaffAssignment::query()->create([
            'activity_id' => $assignedActivity->id,
            'user_id' => $staffOperator->id,
            'role' => ActivityStaffAssignment::ROLE_COACH,
            'is_primary' => false,
            'active' => true,
            'assigned_at' => now(),
        ]);

        $this->withSession(['selected_term_id' => $term->id])
            ->actingAs($staffOperator)
            ->get(route('activities.reports.index'))
            ->assertOk()
            ->assertSee('Chess Club')
            ->assertDontSee('Debate Club');
    }
}
