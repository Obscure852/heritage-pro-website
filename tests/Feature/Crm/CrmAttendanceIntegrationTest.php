<?php

namespace Tests\Feature\Crm;

use App\Jobs\CloseOvernightRecordsJob;
use App\Jobs\MarkAbsenteesJob;
use App\Models\CrmAttendanceCode;
use App\Models\CrmAttendanceCorrection;
use App\Models\CrmAttendanceDevice;
use App\Models\CrmAttendanceHoliday;
use App\Models\CrmAttendanceRecord;
use App\Models\CrmUserDepartment;
use App\Models\User;
use App\Services\Crm\AttendanceClockService;
use App\Services\Crm\BiometricEventProcessor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CrmAttendanceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_clock_in_out_cycle_appears_on_grid(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 08:00:00'));

        $dept = CrmUserDepartment::create(['name' => 'Ops', 'is_active' => true, 'sort_order' => 1]);
        $admin = $this->createUser(['role' => 'admin', 'department_id' => $dept->id]);

        // Clock in
        $this->actingAs($admin)
            ->postJson(route('crm.attendance.clock'))
            ->assertOk()
            ->assertJson(['status' => 'clocked_in', 'code' => 'P']);

        // Verify on dashboard
        $this->actingAs($admin)
            ->get(route('crm.dashboard'))
            ->assertOk()
            ->assertSee('Clocked In');

        // Clock out
        Carbon::setTestNow(Carbon::parse('2026-04-22 17:05:00'));

        $this->actingAs($admin)
            ->postJson(route('crm.attendance.clock'))
            ->assertOk()
            ->assertJson(['status' => 'clocked_out']);

        // Verify on grid
        $this->actingAs($admin)
            ->get(route('crm.attendance.grid'))
            ->assertOk()
            ->assertSee('P');

        // Verify record detail
        $record = CrmAttendanceRecord::where('user_id', $admin->id)->first();
        $this->assertNotNull($record);
        $this->assertSame(545, $record->total_minutes);

        $this->actingAs($admin)
            ->getJson(route('crm.attendance.records.show', $record))
            ->assertOk()
            ->assertJson([
                'clocked_in_at' => '08:00',
                'clocked_out_at' => '17:05',
            ]);

        Carbon::setTestNow();
    }

    public function test_forgotten_clock_out_auto_closes_and_shows_warning(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-21 08:00:00'));

        $user = $this->createUser();
        app(AttendanceClockService::class)->clockIn($user);

        // Next day the job runs
        Carbon::setTestNow(Carbon::parse('2026-04-22 00:05:00'));
        $closed = (new CloseOvernightRecordsJob())->handle();
        $this->assertSame(1, $closed);

        Carbon::setTestNow(Carbon::parse('2026-04-22 08:30:00'));

        $record = CrmAttendanceRecord::where('user_id', $user->id)->first();
        $this->assertTrue((bool) $record->auto_closed);
        $this->assertSame('23:59', $record->clocked_out_at->format('H:i'));

        Carbon::setTestNow();
    }

    public function test_manager_overrides_record_and_audit_is_logged(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $dept = CrmUserDepartment::create(['name' => 'Eng', 'is_active' => true, 'sort_order' => 1]);
        $manager = $this->createUser(['role' => 'manager', 'department_id' => $dept->id]);
        $rep = $this->createUser(['role' => 'rep', 'department_id' => $dept->id]);
        $presentCode = CrmAttendanceCode::where('code', 'P')->first();
        $wfhCode = CrmAttendanceCode::where('code', 'WFH')->first();

        $record = CrmAttendanceRecord::create([
            'user_id' => $rep->id,
            'date' => now()->startOfDay(),
            'attendance_code_id' => $presentCode->id,
            'clocked_in_at' => now()->setTime(8, 0),
            'source' => 'manual',
        ]);

        $this->actingAs($manager)
            ->putJson(route('crm.attendance.records.update', $record), [
                'attendance_code_id' => $wfhCode->id,
            ])
            ->assertOk();

        $record->refresh();
        $this->assertSame($wfhCode->id, $record->attendance_code_id);

        $audit = CrmAttendanceCorrection::where('attendance_record_id', $record->id)->first();
        $this->assertNotNull($audit);
        $this->assertSame('approved', $audit->status);
        $this->assertSame($presentCode->id, $audit->original_values['attendance_code_id']);

        Carbon::setTestNow();
    }

    public function test_biometric_event_creates_record_visible_on_grid(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 07:55:00'));

        $dept = CrmUserDepartment::create(['name' => 'Ops', 'is_active' => true, 'sort_order' => 1]);
        $user = $this->createUser(['role' => 'rep', 'department_id' => $dept->id, 'personal_payroll_number' => 'EMP-100']);
        $admin = $this->createUser(['role' => 'admin', 'department_id' => $dept->id]);
        $device = CrmAttendanceDevice::create([
            'name' => 'Test Scanner',
            'brand' => 'zkteco',
            'device_identifier' => 'BIO-INT-01',
            'direction' => 'both',
            'min_confidence' => 0.80,
            'is_active' => true,
        ]);

        $processor = app(BiometricEventProcessor::class);
        $log = $processor->process($device, [
            'device_id' => 'BIO-INT-01',
            'employee_identifier' => 'EMP-100',
            'event_type' => 'clock_in',
            'captured_at' => '2026-04-22T07:55:00+02:00',
            'confidence_score' => 0.95,
        ]);

        $this->assertSame('processed', $log->status);

        // Admin can see the record on grid
        $this->actingAs($admin)
            ->get(route('crm.attendance.grid'))
            ->assertOk()
            ->assertSee('P');

        Carbon::setTestNow();
    }

    public function test_holiday_created_then_backfilled_on_grid(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $admin = $this->createUser(['role' => 'admin']);

        $holiday = CrmAttendanceHoliday::create([
            'name' => 'Integration Holiday',
            'date' => '2026-04-22',
            'applies_to' => 'all',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        (new \App\Jobs\SyncHolidayAttendanceJob($holiday))->handle();

        $holidayCode = CrmAttendanceCode::where('code', 'H')->first();
        $record = CrmAttendanceRecord::where('user_id', $admin->id)
            ->whereDate('date', now())
            ->first();

        $this->assertNotNull($record);
        $this->assertSame($holidayCode->id, $record->attendance_code_id);

        // Visible in reports
        $this->actingAs($admin)
            ->get(route('crm.attendance.reports.show', 'daily-summary'))
            ->assertOk()
            ->assertSee('H');

        Carbon::setTestNow();
    }

    public function test_admin_creates_code_visible_in_grid_legend(): void
    {
        $admin = $this->createUser(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('crm.settings.attendance.codes.store'), [
                'code' => 'FLD',
                'label' => 'Field Work',
                'color' => '#22c55e',
                'category' => 'duty',
                'counts_as_working' => '1.00',
                'is_active' => '1',
            ])
            ->assertRedirect();

        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $this->actingAs($admin)
            ->get(route('crm.attendance.grid'))
            ->assertOk()
            ->assertSee('FLD');

        Carbon::setTestNow();
    }

    public function test_correction_cycle_submit_approve_updates_record(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $dept = CrmUserDepartment::create(['name' => 'Dept', 'is_active' => true, 'sort_order' => 1]);
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
        ]);

        // User submits correction
        $this->actingAs($user)
            ->postJson(route('crm.attendance.records.correction', $record), [
                'proposed_code_id' => $wfhCode->id,
                'reason' => 'Working remotely',
            ])
            ->assertOk();

        $record->refresh();
        $this->assertSame('pending_correction', $record->status);

        // Correction is pending
        $this->assertSame(1, CrmAttendanceCorrection::where('status', 'pending')->count());

        // Manager approves
        $correction = CrmAttendanceCorrection::where('attendance_record_id', $record->id)
            ->where('status', 'pending')
            ->first();

        $this->actingAs($manager)
            ->putJson(route('crm.attendance.corrections.review', $correction), [
                'action' => 'approve',
            ])
            ->assertOk();

        $record->refresh();
        $this->assertSame($wfhCode->id, $record->attendance_code_id);
        $this->assertSame('active', $record->status);

        Carbon::setTestNow();
    }

    // ── Permission Integration Tests ────────────────────────

    public function test_rep_can_only_access_own_attendance(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $rep = $this->createUser(['role' => 'rep']);

        $this->actingAs($rep)->postJson(route('crm.attendance.clock'))->assertOk();
        $this->actingAs($rep)->getJson(route('crm.attendance.clock-status'))->assertOk();

        $this->actingAs($rep)->get(route('crm.attendance.grid'))->assertForbidden();
        $this->actingAs($rep)->get(route('crm.attendance.reports'))->assertForbidden();

        Carbon::setTestNow();
    }

    public function test_finance_has_read_only_access(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $finance = $this->createUser(['role' => 'finance']);
        $code = CrmAttendanceCode::where('code', 'P')->first();

        $record = CrmAttendanceRecord::create([
            'user_id' => $finance->id,
            'date' => now()->startOfDay(),
            'attendance_code_id' => $code->id,
            'source' => 'manual',
        ]);

        $this->actingAs($finance)->postJson(route('crm.attendance.clock'))->assertOk();

        // Finance cannot edit records
        $this->actingAs($finance)
            ->putJson(route('crm.attendance.records.update', $record), [
                'attendance_code_id' => $code->id,
            ])
            ->assertForbidden();

        Carbon::setTestNow();
    }

    public function test_manager_scoped_to_department_across_all_features(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $deptA = CrmUserDepartment::create(['name' => 'Dept A', 'is_active' => true, 'sort_order' => 1]);
        $deptB = CrmUserDepartment::create(['name' => 'Dept B', 'is_active' => true, 'sort_order' => 2]);
        $manager = $this->createUser(['role' => 'manager', 'department_id' => $deptA->id]);
        $ownUser = $this->createUser(['role' => 'rep', 'department_id' => $deptA->id]);
        $otherUser = $this->createUser(['role' => 'rep', 'department_id' => $deptB->id]);
        $code = CrmAttendanceCode::where('code', 'P')->first();

        $ownRecord = CrmAttendanceRecord::create([
            'user_id' => $ownUser->id,
            'date' => now()->startOfDay(),
            'attendance_code_id' => $code->id,
            'source' => 'manual',
        ]);

        $otherRecord = CrmAttendanceRecord::create([
            'user_id' => $otherUser->id,
            'date' => now()->startOfDay(),
            'attendance_code_id' => $code->id,
            'source' => 'manual',
        ]);

        // Can view/edit own dept
        $this->actingAs($manager)
            ->getJson(route('crm.attendance.records.show', $ownRecord))
            ->assertOk();

        $this->actingAs($manager)
            ->putJson(route('crm.attendance.records.update', $ownRecord), [
                'attendance_code_id' => $code->id,
            ])
            ->assertOk();

        // Blocked on other dept
        $this->actingAs($manager)
            ->getJson(route('crm.attendance.records.show', $otherRecord))
            ->assertForbidden();

        $this->actingAs($manager)
            ->putJson(route('crm.attendance.records.update', $otherRecord), [
                'attendance_code_id' => $code->id,
            ])
            ->assertForbidden();

        Carbon::setTestNow();
    }

    public function test_admin_has_full_access_everywhere(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $admin = $this->createUser(['role' => 'admin']);

        $this->actingAs($admin)->get(route('crm.attendance.grid'))->assertOk();
        $this->actingAs($admin)->get(route('crm.attendance.reports'))->assertOk();
        $this->actingAs($admin)->get(route('crm.settings.attendance.index', ['tab' => 'codes']))->assertOk();
        $this->actingAs($admin)->get(route('crm.settings.attendance.index', ['tab' => 'shifts']))->assertOk();
        $this->actingAs($admin)->get(route('crm.settings.attendance.index', ['tab' => 'holidays']))->assertOk();
        $this->actingAs($admin)->get(route('crm.settings.attendance.index', ['tab' => 'devices']))->assertOk();

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
