<?php

namespace Tests\Feature\Crm;

use App\Models\CrmAttendanceCode;
use App\Models\CrmAttendanceHoliday;
use App\Models\CrmAttendanceRecord;
use App\Models\CrmAttendanceShift;
use App\Models\CrmAttendanceShiftDay;
use App\Models\CrmUserDepartment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CrmAttendanceSettingsTest extends TestCase
{
    use RefreshDatabase;

    // ── Codes ───────────────────────────────────────────────

    public function test_admin_can_view_codes_settings(): void
    {
        $admin = $this->createUser(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('crm.settings.attendance.index', ['tab' => 'codes']))
            ->assertOk()
            ->assertSee('Attendance Code List')
            ->assertSee('Present');
    }

    public function test_non_admin_cannot_access_attendance_settings(): void
    {
        $rep = $this->createUser(['role' => 'rep']);

        $this->actingAs($rep)
            ->get(route('crm.settings.attendance.index', ['tab' => 'codes']))
            ->assertForbidden();
    }

    public function test_admin_can_create_attendance_code(): void
    {
        $admin = $this->createUser(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('crm.settings.attendance.codes.store'), [
                'code' => 'BRK',
                'label' => 'Break',
                'color' => '#ff9900',
                'category' => 'presence',
                'counts_as_working' => '0.50',
                'is_active' => '1',
                'sort_order' => '20',
            ])
            ->assertRedirect(route('crm.settings.attendance.index', ['tab' => 'codes']));

        $this->assertDatabaseHas('crm_attendance_codes', [
            'code' => 'BRK',
            'label' => 'Break',
            'color' => '#ff9900',
            'category' => 'presence',
        ]);
    }

    public function test_code_is_uppercased_on_create(): void
    {
        $admin = $this->createUser(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('crm.settings.attendance.codes.store'), [
                'code' => 'brk',
                'label' => 'Break',
                'color' => '#ff9900',
                'category' => 'presence',
                'counts_as_working' => '1.00',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('crm_attendance_codes', ['code' => 'BRK']);
    }

    public function test_admin_can_update_attendance_code(): void
    {
        $admin = $this->createUser(['role' => 'admin']);
        $code = CrmAttendanceCode::where('code', 'WFH')->first();

        $this->actingAs($admin)
            ->put(route('crm.settings.attendance.codes.update', $code), [
                'code' => 'WFH',
                'label' => 'Remote Work',
                'color' => '#22c55e',
                'category' => 'presence',
                'counts_as_working' => '1.00',
                'is_active' => '1',
            ])
            ->assertRedirect(route('crm.settings.attendance.index', ['tab' => 'codes']));

        $code->refresh();
        $this->assertSame('Remote Work', $code->label);
        $this->assertSame('#22c55e', $code->color);
    }

    public function test_system_code_cannot_be_deleted(): void
    {
        $admin = $this->createUser(['role' => 'admin']);
        $present = CrmAttendanceCode::where('code', 'P')->first();

        $this->assertTrue($present->is_system);

        $this->actingAs($admin)
            ->delete(route('crm.settings.attendance.codes.destroy', $present))
            ->assertStatus(422);

        $present->refresh();
        $this->assertTrue($present->is_active);
    }

    public function test_non_system_code_can_be_deactivated(): void
    {
        $admin = $this->createUser(['role' => 'admin']);
        $wfh = CrmAttendanceCode::where('code', 'WFH')->first();

        $this->assertFalse($wfh->is_system);

        $this->actingAs($admin)
            ->delete(route('crm.settings.attendance.codes.destroy', $wfh))
            ->assertRedirect(route('crm.settings.attendance.index', ['tab' => 'codes']));

        $wfh->refresh();
        $this->assertFalse($wfh->is_active);
    }

    // ── Shifts ──────────────────────────────────────────────

    public function test_admin_can_view_shifts_settings(): void
    {
        $admin = $this->createUser(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('crm.settings.attendance.index', ['tab' => 'shifts']))
            ->assertOk()
            ->assertSee('Shift List')
            ->assertSee('Standard Office');
    }

    public function test_admin_can_create_shift(): void
    {
        $admin = $this->createUser(['role' => 'admin']);

        $days = [];
        foreach (range(0, 6) as $i) {
            $days[$i] = [
                'start_time' => '09:00',
                'end_time' => '18:00',
                'is_working_day' => $i < 5 ? '1' : null,
            ];
        }

        $this->actingAs($admin)
            ->post(route('crm.settings.attendance.shifts.store'), [
                'name' => 'Late Shift',
                'grace_minutes' => '10',
                'early_out_minutes' => '10',
                'overtime_after_minutes' => '20',
                'earliest_clock_in' => '07:00',
                'is_active' => '1',
                'days' => $days,
            ])
            ->assertRedirect(route('crm.settings.attendance.index', ['tab' => 'shifts']));

        $shift = CrmAttendanceShift::where('name', 'Late Shift')->first();
        $this->assertNotNull($shift);
        $this->assertCount(7, $shift->days);
        $this->assertSame(10, $shift->grace_minutes);

        $monday = $shift->days->firstWhere('day_of_week', 0);
        $this->assertTrue($monday->is_working_day);
        $this->assertSame('09:00:00', $monday->start_time);

        $saturday = $shift->days->firstWhere('day_of_week', 5);
        $this->assertFalse($saturday->is_working_day);
    }

    public function test_setting_new_default_shift_unsets_previous(): void
    {
        $admin = $this->createUser(['role' => 'admin']);
        $oldDefault = CrmAttendanceShift::where('is_default', true)->first();
        $this->assertTrue($oldDefault->is_default);

        $days = [];
        foreach (range(0, 6) as $i) {
            $days[$i] = ['start_time' => '08:00', 'end_time' => '16:00', 'is_working_day' => $i < 5 ? '1' : null];
        }

        $this->actingAs($admin)
            ->post(route('crm.settings.attendance.shifts.store'), [
                'name' => 'New Default',
                'grace_minutes' => '15',
                'early_out_minutes' => '15',
                'overtime_after_minutes' => '30',
                'is_default' => '1',
                'is_active' => '1',
                'days' => $days,
            ])
            ->assertRedirect();

        $oldDefault->refresh();
        $this->assertFalse($oldDefault->is_default);

        $newDefault = CrmAttendanceShift::where('name', 'New Default')->first();
        $this->assertTrue($newDefault->is_default);
    }

    public function test_default_shift_cannot_be_deactivated(): void
    {
        $admin = $this->createUser(['role' => 'admin']);
        $defaultShift = CrmAttendanceShift::where('is_default', true)->first();

        $this->actingAs($admin)
            ->delete(route('crm.settings.attendance.shifts.destroy', $defaultShift))
            ->assertStatus(422);
    }

    public function test_bulk_assign_shift_to_department(): void
    {
        $admin = $this->createUser(['role' => 'admin']);
        $dept = CrmUserDepartment::create(['name' => 'Engineering', 'is_active' => true, 'sort_order' => 1]);
        $user1 = $this->createUser(['role' => 'rep', 'department_id' => $dept->id]);
        $user2 = $this->createUser(['role' => 'rep', 'department_id' => $dept->id]);

        $shift = CrmAttendanceShift::where('is_default', true)->first();

        $this->actingAs($admin)
            ->post(route('crm.settings.attendance.shifts.bulk-assign'), [
                'shift_id' => $shift->id,
                'department_id' => $dept->id,
            ])
            ->assertRedirect(route('crm.settings.attendance.index', ['tab' => 'shifts']));

        $user1->refresh();
        $user2->refresh();
        $this->assertSame($shift->id, $user1->shift_id);
        $this->assertSame($shift->id, $user2->shift_id);
    }

    // ── Holidays ────────────────────────────────────────────

    public function test_admin_can_view_holidays_settings(): void
    {
        $admin = $this->createUser(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('crm.settings.attendance.index', ['tab' => 'holidays']))
            ->assertOk()
            ->assertSee('Holiday List');
    }

    public function test_admin_can_create_holiday(): void
    {
        Queue::fake();

        $admin = $this->createUser(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('crm.settings.attendance.holidays.store'), [
                'name' => 'Christmas Day',
                'date' => '2026-12-25',
                'is_recurring' => '1',
                'applies_to' => 'all',
                'is_active' => '1',
            ])
            ->assertRedirect(route('crm.settings.attendance.index', ['tab' => 'holidays']));

        $this->assertDatabaseHas('crm_attendance_holidays', [
            'name' => 'Christmas Day',
            'is_recurring' => true,
            'applies_to' => 'all',
        ]);

        Queue::assertPushed(\App\Jobs\SyncHolidayAttendanceJob::class);
    }

    public function test_admin_can_delete_holiday(): void
    {
        $admin = $this->createUser(['role' => 'admin']);
        $holiday = CrmAttendanceHoliday::create([
            'name' => 'Test Holiday',
            'date' => '2026-06-01',
            'applies_to' => 'all',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->delete(route('crm.settings.attendance.holidays.destroy', $holiday))
            ->assertRedirect(route('crm.settings.attendance.index', ['tab' => 'holidays']));

        $this->assertDatabaseMissing('crm_attendance_holidays', ['id' => $holiday->id]);
    }

    public function test_holiday_sync_job_creates_records(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $admin = $this->createUser(['role' => 'admin']);
        $user = $this->createUser(['role' => 'rep']);

        $holiday = CrmAttendanceHoliday::create([
            'name' => 'Public Holiday',
            'date' => '2026-04-22',
            'applies_to' => 'all',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $job = new \App\Jobs\SyncHolidayAttendanceJob($holiday);
        $job->handle();

        $holidayCode = CrmAttendanceCode::where('code', 'H')->first();

        $this->assertDatabaseHas('crm_attendance_records', [
            'user_id' => $admin->id,
            'attendance_code_id' => $holidayCode->id,
            'source' => 'system',
        ]);

        $this->assertDatabaseHas('crm_attendance_records', [
            'user_id' => $user->id,
            'attendance_code_id' => $holidayCode->id,
        ]);

        Carbon::setTestNow();
    }

    public function test_holiday_sync_only_overwrites_absent_records(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $admin = $this->createUser(['role' => 'admin']);
        $presentCode = CrmAttendanceCode::where('code', 'P')->first();
        $absentCode = CrmAttendanceCode::where('code', 'A')->first();
        $holidayCode = CrmAttendanceCode::where('code', 'H')->first();

        CrmAttendanceRecord::create([
            'user_id' => $admin->id,
            'date' => now()->startOfDay(),
            'attendance_code_id' => $presentCode->id,
            'source' => 'manual',
        ]);

        $user2 = $this->createUser(['role' => 'rep']);
        CrmAttendanceRecord::create([
            'user_id' => $user2->id,
            'date' => now()->startOfDay(),
            'attendance_code_id' => $absentCode->id,
            'source' => 'system',
        ]);

        $holiday = CrmAttendanceHoliday::create([
            'name' => 'Test',
            'date' => '2026-04-22',
            'applies_to' => 'all',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        (new \App\Jobs\SyncHolidayAttendanceJob($holiday))->handle();

        $adminRecord = CrmAttendanceRecord::where('user_id', $admin->id)->whereDate('date', now())->first();
        $this->assertSame($presentCode->id, $adminRecord->attendance_code_id);

        $user2Record = CrmAttendanceRecord::where('user_id', $user2->id)->whereDate('date', now())->first();
        $this->assertSame($holidayCode->id, $user2Record->attendance_code_id);

        Carbon::setTestNow();
    }

    // ── Helpers ─────────────────────────────────────────────

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
