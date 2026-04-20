<?php

namespace Tests\Feature\Activities;

use App\Http\Middleware\EnsureProfileComplete;
use App\Models\Activities\ActivityFeeCharge;
use App\Models\Fee\StudentInvoice;
use App\Models\Fee\StudentInvoiceItem;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Session\Middleware\AuthenticateSession;
use Tests\Concerns\BuildsActivitiesRosterFixtures;
use Tests\Concerns\EnsuresActivitiesPhaseOneSchema;
use Tests\TestCase;

class ActivityFeeIntegrationTest extends TestCase
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

    public function test_charge_posts_into_existing_active_annual_invoice_and_prevents_duplicate_invoice_items(): void
    {
        $admin = $this->createActivityUser('activities-fee-admin-' . uniqid() . '@example.com', ['Activities Admin']);
        $term = $this->createActivityTerm(2026, 1);
        $grade = $this->createGradeForTerm($term);
        $klass = $this->createKlassForTerm($term, $grade, $admin, '1A');
        $assistant = $this->createActivityUser('activities-fee-assistant-' . uniqid() . '@example.com', ['Teacher']);
        $house = $this->createHouseForTerm($term, $admin, $assistant, 'Tawana');
        $filter = $this->createStudentFilter();
        $feeType = $this->createActivityFeeType(['name' => 'Chess Participation Fee']);

        $activity = $this->createActivityRecord($term, $admin, [
            'fee_type_id' => $feeType->id,
            'default_fee_amount' => '150.00',
            'status' => \App\Models\Activities\Activity::STATUS_ACTIVE,
        ]);

        $student = $this->createStudentForActivity($term, $grade, $klass, $house, $filter, [
            'first_name' => 'Neo',
            'last_name' => 'Molefe',
        ]);

        $this->createActivityEnrollmentRecord($activity, $student, $admin);
        $invoice = $this->createStudentInvoice($student, $admin, $term->year);

        $this->actingAs($admin)
            ->post(route('activities.fees.store', $activity), [
                'student_id' => $student->id,
                'charge_type' => ActivityFeeCharge::CHARGE_TYPE_PARTICIPATION,
                'amount' => '150.00',
                'notes' => 'Participation charge for the first team.',
            ])
            ->assertRedirect(route('activities.fees.index', $activity));

        $charge = ActivityFeeCharge::query()->latest('id')->firstOrFail();
        $invoice->refresh();

        $this->assertSame(ActivityFeeCharge::STATUS_POSTED, $charge->billing_status);
        $this->assertSame($invoice->id, $charge->student_invoice_id);
        $this->assertNotNull($charge->student_invoice_item_id);
        $this->assertSame(150.0, (float) $invoice->subtotal_amount);
        $this->assertSame(150.0, (float) $invoice->total_amount);
        $this->assertSame(150.0, (float) $invoice->balance);

        $this->assertDatabaseHas('student_invoice_items', [
            'student_invoice_id' => $invoice->id,
            'activity_fee_charge_id' => $charge->id,
            'item_type' => StudentInvoiceItem::TYPE_ACTIVITY_FEE,
        ]);

        $this->actingAs($admin)
            ->post(route('activities.fees.post', [$activity, $charge]))
            ->assertRedirect(route('activities.fees.index', $activity))
            ->assertSessionHas('error');

        $this->assertSame(
            1,
            StudentInvoiceItem::query()->where('activity_fee_charge_id', $charge->id)->count()
        );
    }

    public function test_charge_stays_pending_until_an_invoice_exists_and_can_be_posted_later(): void
    {
        $admin = $this->createActivityUser('activities-fee-pending-' . uniqid() . '@example.com', ['Activities Admin']);
        $term = $this->createActivityTerm(2026, 2);
        $grade = $this->createGradeForTerm($term, 'F2');
        $klass = $this->createKlassForTerm($term, $grade, $admin, '2B');
        $assistant = $this->createActivityUser('activities-fee-pending-assistant-' . uniqid() . '@example.com', ['Teacher']);
        $house = $this->createHouseForTerm($term, $admin, $assistant, 'Khama');
        $feeType = $this->createActivityFeeType(['name' => 'Debate Fee']);

        $activity = $this->createActivityRecord($term, $admin, [
            'name' => 'Debate Club',
            'fee_type_id' => $feeType->id,
            'default_fee_amount' => '80.00',
            'status' => \App\Models\Activities\Activity::STATUS_ACTIVE,
        ]);

        $student = $this->createStudentForActivity($term, $grade, $klass, $house, null, [
            'first_name' => 'Mpho',
            'last_name' => 'Tau',
        ]);

        $this->createActivityEnrollmentRecord($activity, $student, $admin);

        $this->actingAs($admin)
            ->post(route('activities.fees.store', $activity), [
                'student_id' => $student->id,
                'charge_type' => ActivityFeeCharge::CHARGE_TYPE_SUPPLEMENTAL,
                'amount' => '80.00',
                'notes' => 'Transport top-up.',
            ])
            ->assertRedirect(route('activities.fees.index', $activity));

        $charge = ActivityFeeCharge::query()->latest('id')->firstOrFail();

        $this->assertSame(ActivityFeeCharge::STATUS_PENDING, $charge->billing_status);
        $this->assertNull($charge->student_invoice_id);
        $this->assertSame(
            0,
            StudentInvoiceItem::query()->where('activity_fee_charge_id', $charge->id)->count()
        );

        $invoice = $this->createStudentInvoice($student, $admin, $term->year);

        $this->actingAs($admin)
            ->post(route('activities.fees.post', [$activity, $charge]))
            ->assertRedirect(route('activities.fees.index', $activity));

        $charge->refresh();
        $invoice->refresh();

        $this->assertSame(ActivityFeeCharge::STATUS_POSTED, $charge->billing_status);
        $this->assertSame($invoice->id, $charge->student_invoice_id);
        $this->assertSame(80.0, (float) $invoice->total_amount);
        $this->assertSame(80.0, (float) $invoice->balance);
        $this->assertSame(
            1,
            StudentInvoiceItem::query()->where('activity_fee_charge_id', $charge->id)->count()
        );
    }

    public function test_charge_is_marked_blocked_when_only_cancelled_invoice_exists(): void
    {
        $admin = $this->createActivityUser('activities-fee-blocked-' . uniqid() . '@example.com', ['Activities Admin']);
        $term = $this->createActivityTerm(2026, 3);
        $grade = $this->createGradeForTerm($term, 'F3');
        $klass = $this->createKlassForTerm($term, $grade, $admin, '3C');
        $assistant = $this->createActivityUser('activities-fee-blocked-assistant-' . uniqid() . '@example.com', ['Teacher']);
        $house = $this->createHouseForTerm($term, $admin, $assistant, 'Sechele');
        $feeType = $this->createActivityFeeType(['name' => 'Athletics Fee']);

        $activity = $this->createActivityRecord($term, $admin, [
            'name' => 'Athletics',
            'fee_type_id' => $feeType->id,
            'default_fee_amount' => '120.00',
            'status' => \App\Models\Activities\Activity::STATUS_ACTIVE,
        ]);

        $student = $this->createStudentForActivity($term, $grade, $klass, $house, null, [
            'first_name' => 'Lebo',
            'last_name' => 'Kelebile',
        ]);

        $this->createActivityEnrollmentRecord($activity, $student, $admin);
        $cancelledInvoice = $this->createStudentInvoice($student, $admin, $term->year, [
            'status' => StudentInvoice::STATUS_CANCELLED,
        ]);

        $this->actingAs($admin)
            ->post(route('activities.fees.store', $activity), [
                'student_id' => $student->id,
                'charge_type' => ActivityFeeCharge::CHARGE_TYPE_PARTICIPATION,
                'amount' => '120.00',
            ])
            ->assertRedirect(route('activities.fees.index', $activity));

        $charge = ActivityFeeCharge::query()->latest('id')->firstOrFail();

        $this->assertSame(ActivityFeeCharge::STATUS_BLOCKED, $charge->billing_status);
        $this->assertSame($cancelledInvoice->id, $charge->student_invoice_id);
        $this->assertNull($charge->student_invoice_item_id);
        $this->assertSame(
            0,
            StudentInvoiceItem::query()->where('activity_fee_charge_id', $charge->id)->count()
        );
    }
}
