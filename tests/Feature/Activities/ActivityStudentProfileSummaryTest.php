<?php

namespace Tests\Feature\Activities;

use App\Http\Middleware\EnsureProfileComplete;
use App\Models\Activities\ActivityFeeCharge;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Session\Middleware\AuthenticateSession;
use Tests\Concerns\BuildsActivitiesRosterFixtures;
use Tests\Concerns\EnsuresActivitiesPhaseOneSchema;
use Tests\TestCase;

class ActivityStudentProfileSummaryTest extends TestCase
{
    use DatabaseTransactions;
    use BuildsActivitiesRosterFixtures;
    use EnsuresActivitiesPhaseOneSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(EnsureProfileComplete::class);
        $this->withoutMiddleware(AuthenticateSession::class);
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->ensureActivitiesPhaseOneSchema();
        $this->seedActivitiesSchoolSetup();
    }

    public function test_student_profile_shows_activity_participation_and_charge_state_summary(): void
    {
        $viewer = $this->createActivityUser('students-activities-viewer-' . uniqid() . '@example.com', ['Students Admin', 'Fee Collection']);
        $activityAdmin = $this->createActivityUser('students-activities-admin-' . uniqid() . '@example.com', ['Activities Admin']);
        $term = $this->createActivityTerm(2026, 1);
        $grade = $this->createGradeForTerm($term, 'F1');
        $klass = $this->createKlassForTerm($term, $grade, $viewer, '1A');
        $house = $this->createHouseForTerm($term, $viewer, $activityAdmin, 'Tawana');
        $feeType = $this->createActivityFeeType(['name' => 'Chess Fee']);

        $activity = $this->createActivityRecord($term, $activityAdmin, [
            'name' => 'Chess Tournament',
            'fee_type_id' => $feeType->id,
            'default_fee_amount' => '95.00',
            'status' => \App\Models\Activities\Activity::STATUS_ACTIVE,
        ]);

        $student = $this->createStudentForActivity($term, $grade, $klass, $house, null, [
            'first_name' => 'Kabelo',
            'last_name' => 'Molefe',
        ]);

        $this->createActivityEnrollmentRecord($activity, $student, $activityAdmin);
        $this->createStudentInvoice($student, $activityAdmin, $term->year);

        $this->actingAs($activityAdmin)
            ->post(route('activities.fees.store', $activity), [
                'student_id' => $student->id,
                'charge_type' => ActivityFeeCharge::CHARGE_TYPE_PARTICIPATION,
                'amount' => '95.00',
                'notes' => 'House competition charge.',
            ])
            ->assertRedirect(route('activities.fees.index', $activity));

        $this->actingAs($viewer)
            ->withSession(['selected_term_id' => $term->id])
            ->get(route('students.show', $student->id))
            ->assertOk()
            ->assertSee('Student Activities')
            ->assertSee('Chess Tournament')
            ->assertSee('Posted to Invoice')
            ->assertSee('Fee Account');
    }
}
