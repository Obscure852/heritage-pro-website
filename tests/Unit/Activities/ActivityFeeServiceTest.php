<?php

namespace Tests\Unit\Activities;

use App\Models\Activities\Activity;
use App\Models\Activities\ActivityFeeCharge;
use App\Models\Fee\StudentInvoice;
use App\Services\Activities\ActivityFeeService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\BuildsActivitiesRosterFixtures;
use Tests\Concerns\EnsuresActivitiesPhaseOneSchema;
use Tests\TestCase;

class ActivityFeeServiceTest extends TestCase
{
    use DatabaseTransactions;
    use BuildsActivitiesRosterFixtures;
    use EnsuresActivitiesPhaseOneSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureActivitiesPhaseOneSchema();
        $this->seedActivitiesSchoolSetup();
    }

    public function test_activity_billing_summary_rolls_up_status_counts_and_amounts(): void
    {
        $admin = $this->createActivityUser('activities-fee-summary-' . uniqid() . '@example.com', ['Activities Admin']);
        $term = $this->createActivityTerm(2026, 1);
        $feeType = $this->createActivityFeeType();
        $student = $this->createStudentForActivity($term, $this->createGradeForTerm($term), null, null, null, [
            'first_name' => 'Neo',
            'last_name' => 'Summary',
        ]);

        $activity = $this->createActivityRecord($term, $admin, [
            'fee_type_id' => $feeType->id,
            'default_fee_amount' => '100.00',
            'status' => Activity::STATUS_ACTIVE,
        ]);

        $invoice = $this->createStudentInvoice($student, $admin, $term->year, [
            'balance' => '50.00',
            'total_amount' => '50.00',
        ]);

        ActivityFeeCharge::query()->create([
            'activity_id' => $activity->id,
            'student_id' => $student->id,
            'fee_type_id' => $feeType->id,
            'term_id' => $term->id,
            'year' => $term->year,
            'charge_type' => ActivityFeeCharge::CHARGE_TYPE_PARTICIPATION,
            'amount' => '100.00',
            'billing_status' => ActivityFeeCharge::STATUS_POSTED,
            'student_invoice_id' => $invoice->id,
            'generated_by' => $admin->id,
            'generated_at' => now(),
        ]);

        ActivityFeeCharge::query()->create([
            'activity_id' => $activity->id,
            'student_id' => $student->id,
            'fee_type_id' => $feeType->id,
            'term_id' => $term->id,
            'year' => $term->year,
            'charge_type' => ActivityFeeCharge::CHARGE_TYPE_SUPPLEMENTAL,
            'amount' => '30.00',
            'billing_status' => ActivityFeeCharge::STATUS_PENDING,
            'generated_by' => $admin->id,
            'generated_at' => now(),
        ]);

        ActivityFeeCharge::query()->create([
            'activity_id' => $activity->id,
            'student_id' => $student->id,
            'fee_type_id' => $feeType->id,
            'term_id' => $term->id,
            'year' => $term->year,
            'charge_type' => ActivityFeeCharge::CHARGE_TYPE_EVENT,
            'amount' => '20.00',
            'billing_status' => ActivityFeeCharge::STATUS_BLOCKED,
            'generated_by' => $admin->id,
            'generated_at' => now(),
        ]);

        $summary = app(ActivityFeeService::class)->activityBillingSummary($activity);

        $this->assertSame(3, $summary['total_count']);
        $this->assertSame(1, $summary['posted_count']);
        $this->assertSame(1, $summary['pending_count']);
        $this->assertSame(1, $summary['blocked_count']);
        $this->assertSame(150.0, (float) $summary['total_amount']);
        $this->assertSame(100.0, (float) $summary['posted_amount']);
        $this->assertSame(100.0, (float) $summary['outstanding_amount']);
    }

    public function test_student_summary_groups_active_history_and_charge_state_for_selected_term(): void
    {
        $admin = $this->createActivityUser('activities-fee-student-summary-' . uniqid() . '@example.com', ['Activities Admin']);
        $term = $this->createActivityTerm(2026, 2);
        $grade = $this->createGradeForTerm($term, 'F2');
        $klass = $this->createKlassForTerm($term, $grade, $admin, '2B');
        $assistant = $this->createActivityUser('activities-fee-student-summary-helper-' . uniqid() . '@example.com', ['Teacher']);
        $house = $this->createHouseForTerm($term, $admin, $assistant, 'Khama');
        $student = $this->createStudentForActivity($term, $grade, $klass, $house, null, [
            'first_name' => 'Mpho',
            'last_name' => 'Tau',
        ]);
        $feeType = $this->createActivityFeeType();

        $activeActivity = $this->createActivityRecord($term, $admin, [
            'name' => 'Debate Club',
            'code' => 'DB001',
            'fee_type_id' => $feeType->id,
        ]);

        $historicalActivity = $this->createActivityRecord($term, $admin, [
            'name' => 'Athletics',
            'code' => 'AT001',
            'fee_type_id' => $feeType->id,
        ]);

        $this->createActivityEnrollmentRecord($activeActivity, $student, $admin);
        $this->createActivityEnrollmentRecord($historicalActivity, $student, $admin, [
            'status' => \App\Models\Activities\ActivityEnrollment::STATUS_WITHDRAWN,
            'left_at' => now(),
            'left_by' => $admin->id,
            'exit_reason' => 'Shifted to another activity.',
        ]);

        ActivityFeeCharge::query()->create([
            'activity_id' => $activeActivity->id,
            'student_id' => $student->id,
            'fee_type_id' => $feeType->id,
            'term_id' => $term->id,
            'year' => $term->year,
            'charge_type' => ActivityFeeCharge::CHARGE_TYPE_PARTICIPATION,
            'amount' => '75.00',
            'billing_status' => ActivityFeeCharge::STATUS_PENDING,
            'generated_by' => $admin->id,
            'generated_at' => now(),
        ]);

        $summary = app(ActivityFeeService::class)->studentSummary($student, $term->id);

        $this->assertCount(1, $summary['activeEnrollments']);
        $this->assertCount(1, $summary['historicalEnrollments']);
        $this->assertCount(1, $summary['charges']);
        $this->assertSame(1, $summary['summary']['active_count']);
        $this->assertSame(1, $summary['summary']['pending_count']);
    }
}
