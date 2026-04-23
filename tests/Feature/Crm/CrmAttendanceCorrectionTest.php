<?php

namespace Tests\Feature\Crm;

use App\Models\CrmAttendanceCode;
use App\Models\CrmAttendanceCorrection;
use App\Models\CrmAttendanceRecord;
use App\Models\CrmUserDepartment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CrmAttendanceCorrectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_submit_correction_for_own_record(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $user = $this->createUser(['role' => 'rep']);
        $code = CrmAttendanceCode::where('code', 'P')->first();
        $wfhCode = CrmAttendanceCode::where('code', 'WFH')->first();

        $record = CrmAttendanceRecord::create([
            'user_id' => $user->id,
            'date' => now()->startOfDay(),
            'attendance_code_id' => $code->id,
            'clocked_in_at' => now()->setTime(8, 0),
            'source' => 'manual',
        ]);

        $this->actingAs($user)
            ->postJson(route('crm.attendance.records.correction', $record), [
                'proposed_code_id' => $wfhCode->id,
                'reason' => 'Was working from home today',
            ])
            ->assertOk()
            ->assertJson(['message' => 'Correction request submitted. Your manager will review it.']);

        $record->refresh();
        $this->assertSame('pending_correction', $record->status);

        $correction = CrmAttendanceCorrection::where('attendance_record_id', $record->id)->first();
        $this->assertNotNull($correction);
        $this->assertSame('pending', $correction->status);
        $this->assertSame($user->id, $correction->requested_by);
        $this->assertSame($wfhCode->id, $correction->proposed_code_id);
        $this->assertSame('Was working from home today', $correction->reason);

        Carbon::setTestNow();
    }

    public function test_user_cannot_submit_correction_for_other_users_record(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $user = $this->createUser(['role' => 'rep']);
        $otherUser = $this->createUser(['role' => 'rep']);
        $code = CrmAttendanceCode::where('code', 'P')->first();

        $record = CrmAttendanceRecord::create([
            'user_id' => $otherUser->id,
            'date' => now()->startOfDay(),
            'attendance_code_id' => $code->id,
            'source' => 'manual',
        ]);

        $this->actingAs($user)
            ->postJson(route('crm.attendance.records.correction', $record), [
                'proposed_code_id' => $code->id,
                'reason' => 'Test',
            ])
            ->assertForbidden();

        Carbon::setTestNow();
    }

    public function test_cannot_submit_correction_when_one_is_already_pending(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $user = $this->createUser(['role' => 'rep']);
        $code = CrmAttendanceCode::where('code', 'P')->first();

        $record = CrmAttendanceRecord::create([
            'user_id' => $user->id,
            'date' => now()->startOfDay(),
            'attendance_code_id' => $code->id,
            'source' => 'manual',
            'status' => 'pending_correction',
        ]);

        $this->actingAs($user)
            ->postJson(route('crm.attendance.records.correction', $record), [
                'proposed_code_id' => $code->id,
                'reason' => 'Duplicate attempt',
            ])
            ->assertStatus(422);

        Carbon::setTestNow();
    }

    public function test_correction_requires_at_least_one_proposed_change(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $user = $this->createUser(['role' => 'rep']);
        $code = CrmAttendanceCode::where('code', 'P')->first();

        $record = CrmAttendanceRecord::create([
            'user_id' => $user->id,
            'date' => now()->startOfDay(),
            'attendance_code_id' => $code->id,
            'source' => 'manual',
        ]);

        $this->actingAs($user)
            ->postJson(route('crm.attendance.records.correction', $record), [
                'reason' => 'No changes proposed',
            ])
            ->assertStatus(422);

        Carbon::setTestNow();
    }

    public function test_manager_can_approve_correction(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $dept = CrmUserDepartment::create(['name' => 'Engineering', 'is_active' => true, 'sort_order' => 1]);
        $manager = $this->createUser(['role' => 'manager', 'department_id' => $dept->id]);
        $user = $this->createUser(['role' => 'rep', 'department_id' => $dept->id]);
        $presentCode = CrmAttendanceCode::where('code', 'P')->first();
        $wfhCode = CrmAttendanceCode::where('code', 'WFH')->first();

        $record = CrmAttendanceRecord::create([
            'user_id' => $user->id,
            'date' => now()->startOfDay(),
            'attendance_code_id' => $presentCode->id,
            'clocked_in_at' => now()->setTime(8, 0),
            'source' => 'manual',
            'status' => 'pending_correction',
        ]);

        $correction = CrmAttendanceCorrection::create([
            'attendance_record_id' => $record->id,
            'requested_by' => $user->id,
            'original_values' => ['attendance_code_id' => $presentCode->id],
            'proposed_code_id' => $wfhCode->id,
            'reason' => 'Was working from home',
            'status' => 'pending',
        ]);

        $this->actingAs($manager)
            ->putJson(route('crm.attendance.corrections.review', $correction), [
                'action' => 'approve',
            ])
            ->assertOk()
            ->assertJson(['message' => 'Correction approved and record updated.']);

        $correction->refresh();
        $this->assertSame('approved', $correction->status);
        $this->assertSame($manager->id, $correction->reviewed_by);
        $this->assertNotNull($correction->reviewed_at);

        $record->refresh();
        $this->assertSame($wfhCode->id, $record->attendance_code_id);
        $this->assertSame('active', $record->status);

        Carbon::setTestNow();
    }

    public function test_manager_can_reject_correction(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $dept = CrmUserDepartment::create(['name' => 'Engineering', 'is_active' => true, 'sort_order' => 1]);
        $manager = $this->createUser(['role' => 'manager', 'department_id' => $dept->id]);
        $user = $this->createUser(['role' => 'rep', 'department_id' => $dept->id]);
        $presentCode = CrmAttendanceCode::where('code', 'P')->first();

        $record = CrmAttendanceRecord::create([
            'user_id' => $user->id,
            'date' => now()->startOfDay(),
            'attendance_code_id' => $presentCode->id,
            'source' => 'manual',
            'status' => 'pending_correction',
        ]);

        $correction = CrmAttendanceCorrection::create([
            'attendance_record_id' => $record->id,
            'requested_by' => $user->id,
            'original_values' => ['attendance_code_id' => $presentCode->id],
            'proposed_clock_in' => '2026-04-22 07:30:00',
            'reason' => 'I was actually early',
            'status' => 'pending',
        ]);

        $this->actingAs($manager)
            ->putJson(route('crm.attendance.corrections.review', $correction), [
                'action' => 'reject',
                'rejection_reason' => 'Clock records show 08:00 arrival.',
            ])
            ->assertOk()
            ->assertJson(['message' => 'Correction rejected.']);

        $correction->refresh();
        $this->assertSame('rejected', $correction->status);
        $this->assertSame('Clock records show 08:00 arrival.', $correction->rejection_reason);

        $record->refresh();
        $this->assertSame($presentCode->id, $record->attendance_code_id);
        $this->assertSame('active', $record->status);

        Carbon::setTestNow();
    }

    public function test_user_cannot_review_own_correction(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $admin = $this->createUser(['role' => 'admin']);
        $code = CrmAttendanceCode::where('code', 'P')->first();

        $record = CrmAttendanceRecord::create([
            'user_id' => $admin->id,
            'date' => now()->startOfDay(),
            'attendance_code_id' => $code->id,
            'source' => 'manual',
            'status' => 'pending_correction',
        ]);

        $correction = CrmAttendanceCorrection::create([
            'attendance_record_id' => $record->id,
            'requested_by' => $admin->id,
            'original_values' => ['attendance_code_id' => $code->id],
            'proposed_clock_in' => '2026-04-22 07:30:00',
            'reason' => 'Wrong time',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->putJson(route('crm.attendance.corrections.review', $correction), [
                'action' => 'approve',
            ])
            ->assertForbidden();

        Carbon::setTestNow();
    }

    public function test_rep_cannot_review_corrections(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $dept = CrmUserDepartment::create(['name' => 'Dept', 'is_active' => true, 'sort_order' => 1]);
        $rep = $this->createUser(['role' => 'rep', 'department_id' => $dept->id]);
        $otherUser = $this->createUser(['role' => 'rep', 'department_id' => $dept->id]);
        $code = CrmAttendanceCode::where('code', 'P')->first();

        $record = CrmAttendanceRecord::create([
            'user_id' => $otherUser->id,
            'date' => now()->startOfDay(),
            'attendance_code_id' => $code->id,
            'source' => 'manual',
            'status' => 'pending_correction',
        ]);

        $correction = CrmAttendanceCorrection::create([
            'attendance_record_id' => $record->id,
            'requested_by' => $otherUser->id,
            'original_values' => ['attendance_code_id' => $code->id],
            'proposed_clock_in' => '2026-04-22 07:30:00',
            'reason' => 'Test',
            'status' => 'pending',
        ]);

        $this->actingAs($rep)
            ->putJson(route('crm.attendance.corrections.review', $correction), [
                'action' => 'approve',
            ])
            ->assertForbidden();

        Carbon::setTestNow();
    }

    public function test_cannot_review_already_reviewed_correction(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $dept = CrmUserDepartment::create(['name' => 'Dept', 'is_active' => true, 'sort_order' => 1]);
        $manager = $this->createUser(['role' => 'manager', 'department_id' => $dept->id]);
        $user = $this->createUser(['role' => 'rep', 'department_id' => $dept->id]);
        $code = CrmAttendanceCode::where('code', 'P')->first();

        $record = CrmAttendanceRecord::create([
            'user_id' => $user->id,
            'date' => now()->startOfDay(),
            'attendance_code_id' => $code->id,
            'source' => 'manual',
        ]);

        $correction = CrmAttendanceCorrection::create([
            'attendance_record_id' => $record->id,
            'requested_by' => $user->id,
            'original_values' => ['attendance_code_id' => $code->id],
            'proposed_clock_in' => '2026-04-22 07:30:00',
            'reason' => 'Test',
            'status' => 'approved',
            'reviewed_by' => $manager->id,
            'reviewed_at' => now(),
        ]);

        $this->actingAs($manager)
            ->putJson(route('crm.attendance.corrections.review', $correction), [
                'action' => 'reject',
                'rejection_reason' => 'Changed my mind',
            ])
            ->assertStatus(422);

        Carbon::setTestNow();
    }

    public function test_manager_cannot_review_correction_outside_department(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $deptA = CrmUserDepartment::create(['name' => 'Dept A', 'is_active' => true, 'sort_order' => 1]);
        $deptB = CrmUserDepartment::create(['name' => 'Dept B', 'is_active' => true, 'sort_order' => 2]);
        $manager = $this->createUser(['role' => 'manager', 'department_id' => $deptA->id]);
        $user = $this->createUser(['role' => 'rep', 'department_id' => $deptB->id]);
        $code = CrmAttendanceCode::where('code', 'P')->first();

        $record = CrmAttendanceRecord::create([
            'user_id' => $user->id,
            'date' => now()->startOfDay(),
            'attendance_code_id' => $code->id,
            'source' => 'manual',
            'status' => 'pending_correction',
        ]);

        $correction = CrmAttendanceCorrection::create([
            'attendance_record_id' => $record->id,
            'requested_by' => $user->id,
            'original_values' => ['attendance_code_id' => $code->id],
            'proposed_clock_in' => '2026-04-22 07:30:00',
            'reason' => 'Test',
            'status' => 'pending',
        ]);

        $this->actingAs($manager)
            ->putJson(route('crm.attendance.corrections.review', $correction), [
                'action' => 'approve',
            ])
            ->assertForbidden();

        Carbon::setTestNow();
    }

    public function test_approved_correction_with_clock_times_recalculates_total(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $dept = CrmUserDepartment::create(['name' => 'Dept', 'is_active' => true, 'sort_order' => 1]);
        $manager = $this->createUser(['role' => 'manager', 'department_id' => $dept->id]);
        $user = $this->createUser(['role' => 'rep', 'department_id' => $dept->id]);
        $code = CrmAttendanceCode::where('code', 'P')->first();

        $record = CrmAttendanceRecord::create([
            'user_id' => $user->id,
            'date' => now()->startOfDay(),
            'attendance_code_id' => $code->id,
            'clocked_in_at' => now()->setTime(8, 0),
            'clocked_out_at' => now()->setTime(17, 0),
            'total_minutes' => 540,
            'source' => 'manual',
            'status' => 'pending_correction',
        ]);

        $correction = CrmAttendanceCorrection::create([
            'attendance_record_id' => $record->id,
            'requested_by' => $user->id,
            'original_values' => ['clocked_in_at' => '2026-04-22 08:00:00', 'clocked_out_at' => '2026-04-22 17:00:00'],
            'proposed_clock_in' => '2026-04-22 07:30:00',
            'proposed_clock_out' => '2026-04-22 17:30:00',
            'reason' => 'Arrived earlier and left later',
            'status' => 'pending',
        ]);

        $this->actingAs($manager)
            ->putJson(route('crm.attendance.corrections.review', $correction), [
                'action' => 'approve',
            ])
            ->assertOk();

        $record->refresh();
        $this->assertSame('07:30', $record->clocked_in_at->format('H:i'));
        $this->assertSame('17:30', $record->clocked_out_at->format('H:i'));
        $this->assertSame(600, $record->total_minutes);

        Carbon::setTestNow();
    }

    public function test_pending_corrections_endpoint_returns_json(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $dept = CrmUserDepartment::create(['name' => 'Dept', 'is_active' => true, 'sort_order' => 1]);
        $manager = $this->createUser(['role' => 'manager', 'department_id' => $dept->id]);
        $user = $this->createUser(['role' => 'rep', 'department_id' => $dept->id]);
        $code = CrmAttendanceCode::where('code', 'P')->first();

        $record = CrmAttendanceRecord::create([
            'user_id' => $user->id,
            'date' => now()->startOfDay(),
            'attendance_code_id' => $code->id,
            'source' => 'manual',
            'status' => 'pending_correction',
        ]);

        CrmAttendanceCorrection::create([
            'attendance_record_id' => $record->id,
            'requested_by' => $user->id,
            'original_values' => ['attendance_code_id' => $code->id],
            'proposed_clock_in' => '2026-04-22 07:30:00',
            'reason' => 'Test',
            'status' => 'pending',
        ]);

        $this->actingAs($manager)
            ->getJson(route('crm.attendance.corrections.pending'))
            ->assertOk()
            ->assertJsonStructure(['count', 'corrections'])
            ->assertJson(['count' => 1]);

        Carbon::setTestNow();
    }

    public function test_pending_correction_is_visible_in_pending_endpoint(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $dept = CrmUserDepartment::create(['name' => 'Dept', 'is_active' => true, 'sort_order' => 1]);
        $manager = $this->createUser(['role' => 'manager', 'department_id' => $dept->id]);
        $user = $this->createUser(['role' => 'rep', 'department_id' => $dept->id]);
        $code = CrmAttendanceCode::where('code', 'P')->first();

        $record = CrmAttendanceRecord::create([
            'user_id' => $user->id,
            'date' => now()->startOfDay(),
            'attendance_code_id' => $code->id,
            'source' => 'manual',
            'status' => 'pending_correction',
        ]);

        CrmAttendanceCorrection::create([
            'attendance_record_id' => $record->id,
            'requested_by' => $user->id,
            'original_values' => ['attendance_code_id' => $code->id],
            'proposed_clock_in' => '2026-04-22 07:30:00',
            'reason' => 'I arrived earlier',
            'status' => 'pending',
        ]);

        $this->actingAs($manager)
            ->getJson(route('crm.attendance.corrections.pending'))
            ->assertOk()
            ->assertJson(['count' => 1]);

        Carbon::setTestNow();
    }

    private function createUser(array $attributes = []): User
    {
        return User::query()->create(array_merge([
            'name' => 'Test User',
            'email' => 'test-' . uniqid('', true) . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'active' => true,
        ], $attributes));
    }
}
