<?php

namespace Tests\Feature\Crm;

use App\Models\CrmAttendanceCode;
use App\Models\CrmAttendanceCorrection;
use App\Models\CrmAttendanceHoliday;
use App\Models\CrmAttendanceRecord;
use App\Models\CrmAttendanceShift;
use App\Models\CrmAttendanceShiftDay;
use App\Models\CrmAttendanceShiftOverride;
use App\Models\User;
use App\Services\Crm\AttendanceShiftResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CrmAttendanceFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_migrations_create_all_attendance_tables(): void
    {
        $this->assertTrue(\Schema::hasTable('crm_attendance_codes'));
        $this->assertTrue(\Schema::hasTable('crm_attendance_shifts'));
        $this->assertTrue(\Schema::hasTable('crm_attendance_shift_days'));
        $this->assertTrue(\Schema::hasTable('crm_attendance_shift_overrides'));
        $this->assertTrue(\Schema::hasTable('crm_attendance_records'));
        $this->assertTrue(\Schema::hasTable('crm_attendance_corrections'));
        $this->assertTrue(\Schema::hasTable('crm_attendance_holidays'));
        $this->assertTrue(\Schema::hasColumn('users', 'shift_id'));
    }

    public function test_default_attendance_codes_are_seeded(): void
    {
        $codes = CrmAttendanceCode::all();

        $this->assertCount(11, $codes);
        $this->assertNotNull(CrmAttendanceCode::where('code', 'P')->first());
        $this->assertNotNull(CrmAttendanceCode::where('code', 'A')->first());
        $this->assertNotNull(CrmAttendanceCode::where('code', 'LA')->first());
        $this->assertNotNull(CrmAttendanceCode::where('code', 'H')->first());
        $this->assertNotNull(CrmAttendanceCode::where('code', 'L')->first());

        $present = CrmAttendanceCode::where('code', 'P')->first();
        $this->assertTrue($present->is_system);
        $this->assertTrue($present->is_active);
        $this->assertSame('1.00', $present->counts_as_working);
        $this->assertSame('presence', $present->category);

        $absent = CrmAttendanceCode::where('code', 'A')->first();
        $this->assertSame('0.00', $absent->counts_as_working);
        $this->assertSame('absence', $absent->category);

        $halfDay = CrmAttendanceCode::where('code', 'HD')->first();
        $this->assertSame('0.50', $halfDay->counts_as_working);
    }

    public function test_default_shift_is_seeded_with_seven_days(): void
    {
        $shift = CrmAttendanceShift::where('is_default', true)->first();

        $this->assertNotNull($shift);
        $this->assertSame('Standard Office', $shift->name);
        $this->assertTrue($shift->is_default);
        $this->assertTrue($shift->is_active);
        $this->assertSame(15, $shift->grace_minutes);

        $days = $shift->days;
        $this->assertCount(7, $days);

        foreach (range(0, 4) as $workday) {
            $day = $days->firstWhere('day_of_week', $workday);
            $this->assertTrue($day->is_working_day, "Day {$workday} should be a working day");
            $this->assertSame('08:00:00', $day->start_time);
            $this->assertSame('17:00:00', $day->end_time);
        }

        foreach ([5, 6] as $weekend) {
            $day = $days->firstWhere('day_of_week', $weekend);
            $this->assertFalse($day->is_working_day, "Day {$weekend} should not be a working day");
        }
    }

    public function test_user_has_shift_relationship(): void
    {
        $shift = CrmAttendanceShift::where('is_default', true)->first();
        $user = $this->createUser(['shift_id' => $shift->id]);

        $this->assertNotNull($user->shift);
        $this->assertSame($shift->id, $user->shift->id);
    }

    public function test_user_has_attendance_records_relationship(): void
    {
        $user = $this->createUser();
        $code = CrmAttendanceCode::where('code', 'P')->first();

        CrmAttendanceRecord::create([
            'user_id' => $user->id,
            'date' => today(),
            'attendance_code_id' => $code->id,
            'source' => 'manual',
        ]);

        $this->assertCount(1, $user->attendanceRecords);
    }

    public function test_attendance_record_belongs_to_user_and_code(): void
    {
        $user = $this->createUser();
        $code = CrmAttendanceCode::where('code', 'P')->first();

        $record = CrmAttendanceRecord::create([
            'user_id' => $user->id,
            'date' => today(),
            'attendance_code_id' => $code->id,
            'clocked_in_at' => now(),
            'source' => 'manual',
        ]);

        $this->assertSame($user->id, $record->user->id);
        $this->assertSame($code->id, $record->code->id);
        $this->assertTrue($record->isClockedIn());
    }

    public function test_attendance_record_unique_constraint_per_user_per_date(): void
    {
        $user = $this->createUser();
        $code = CrmAttendanceCode::where('code', 'P')->first();

        CrmAttendanceRecord::create([
            'user_id' => $user->id,
            'date' => today(),
            'attendance_code_id' => $code->id,
            'source' => 'manual',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        CrmAttendanceRecord::create([
            'user_id' => $user->id,
            'date' => today(),
            'attendance_code_id' => $code->id,
            'source' => 'manual',
        ]);
    }

    public function test_correction_belongs_to_record_and_requester(): void
    {
        $user = $this->createUser();
        $code = CrmAttendanceCode::where('code', 'P')->first();

        $record = CrmAttendanceRecord::create([
            'user_id' => $user->id,
            'date' => today(),
            'attendance_code_id' => $code->id,
            'source' => 'manual',
        ]);

        $correction = CrmAttendanceCorrection::create([
            'attendance_record_id' => $record->id,
            'requested_by' => $user->id,
            'original_values' => ['attendance_code_id' => $code->id],
            'reason' => 'Wrong code applied',
            'status' => 'pending',
        ]);

        $this->assertSame($record->id, $correction->record->id);
        $this->assertSame($user->id, $correction->requester->id);
        $this->assertTrue($correction->isPending());
    }

    public function test_holiday_applies_to_user_correctly(): void
    {
        $dept1 = \App\Models\CrmUserDepartment::create(['name' => 'Dept A', 'is_active' => true, 'sort_order' => 1]);
        $dept2 = \App\Models\CrmUserDepartment::create(['name' => 'Dept B', 'is_active' => true, 'sort_order' => 2]);

        $user = $this->createUser(['department_id' => $dept1->id]);
        $shift = CrmAttendanceShift::where('is_default', true)->first();
        $userWithShift = $this->createUser(['shift_id' => $shift->id, 'department_id' => $dept2->id]);

        $globalHoliday = CrmAttendanceHoliday::create([
            'name' => 'Christmas',
            'date' => '2026-12-25',
            'applies_to' => 'all',
            'created_by' => $user->id,
        ]);

        $deptHoliday = CrmAttendanceHoliday::create([
            'name' => 'Dept Day',
            'date' => '2026-06-01',
            'applies_to' => 'department',
            'scope_id' => $dept1->id,
            'created_by' => $user->id,
        ]);

        $shiftHoliday = CrmAttendanceHoliday::create([
            'name' => 'Shift Day',
            'date' => '2026-07-01',
            'applies_to' => 'shift',
            'scope_id' => $shift->id,
            'created_by' => $user->id,
        ]);

        $this->assertTrue($globalHoliday->appliesToUser($user));
        $this->assertTrue($globalHoliday->appliesToUser($userWithShift));

        $this->assertTrue($deptHoliday->appliesToUser($user));
        $this->assertFalse($deptHoliday->appliesToUser($userWithShift));

        $this->assertFalse($shiftHoliday->appliesToUser($user));
        $this->assertTrue($shiftHoliday->appliesToUser($userWithShift));
    }

    public function test_shift_resolver_returns_default_shift_when_user_has_no_assignment(): void
    {
        $resolver = app(AttendanceShiftResolver::class);
        $user = $this->createUser();

        $shift = $resolver->resolveForUserAndDate($user, Carbon::parse('2026-04-23'));

        $this->assertNotNull($shift);
        $this->assertTrue($shift->is_default);
        $this->assertSame('Standard Office', $shift->name);
    }

    public function test_shift_resolver_returns_user_assigned_shift(): void
    {
        $resolver = app(AttendanceShiftResolver::class);

        $earlyShift = CrmAttendanceShift::create([
            'name' => 'Early Shift',
            'is_default' => false,
            'grace_minutes' => 10,
            'early_out_minutes' => 10,
            'overtime_after_minutes' => 20,
            'earliest_clock_in' => '05:00:00',
            'is_active' => true,
        ]);

        foreach (range(0, 6) as $day) {
            CrmAttendanceShiftDay::create([
                'shift_id' => $earlyShift->id,
                'day_of_week' => $day,
                'start_time' => '06:00:00',
                'end_time' => '14:00:00',
                'is_working_day' => $day <= 4,
            ]);
        }

        $user = $this->createUser(['shift_id' => $earlyShift->id]);

        $shift = $resolver->resolveForUserAndDate($user, Carbon::parse('2026-04-23'));

        $this->assertSame($earlyShift->id, $shift->id);
        $this->assertSame('Early Shift', $shift->name);
    }

    public function test_shift_resolver_prioritises_override_over_user_shift(): void
    {
        $resolver = app(AttendanceShiftResolver::class);

        $earlyShift = CrmAttendanceShift::create([
            'name' => 'Early Shift',
            'is_default' => false,
            'is_active' => true,
        ]);
        foreach (range(0, 6) as $day) {
            CrmAttendanceShiftDay::create([
                'shift_id' => $earlyShift->id,
                'day_of_week' => $day,
                'start_time' => '06:00:00',
                'end_time' => '14:00:00',
                'is_working_day' => $day <= 4,
            ]);
        }

        $nightShift = CrmAttendanceShift::create([
            'name' => 'Night Shift',
            'is_default' => false,
            'is_active' => true,
        ]);
        foreach (range(0, 6) as $day) {
            CrmAttendanceShiftDay::create([
                'shift_id' => $nightShift->id,
                'day_of_week' => $day,
                'start_time' => '22:00:00',
                'end_time' => '06:00:00',
                'is_working_day' => $day <= 4,
            ]);
        }

        $user = $this->createUser(['shift_id' => $earlyShift->id]);

        CrmAttendanceShiftOverride::create([
            'user_id' => $user->id,
            'shift_id' => $nightShift->id,
            'start_date' => '2026-04-20',
            'end_date' => '2026-04-26',
            'reason' => 'Temporary night shift',
            'created_by' => $user->id,
        ]);

        $withinOverride = $resolver->resolveForUserAndDate($user, Carbon::parse('2026-04-23'));
        $this->assertSame($nightShift->id, $withinOverride->id);

        $outsideOverride = $resolver->resolveForUserAndDate($user, Carbon::parse('2026-04-28'));
        $this->assertSame($earlyShift->id, $outsideOverride->id);
    }

    public function test_shift_resolver_is_working_day(): void
    {
        $resolver = app(AttendanceShiftResolver::class);
        $user = $this->createUser();

        $wednesday = Carbon::parse('2026-04-22');
        $saturday = Carbon::parse('2026-04-25');

        $this->assertTrue($resolver->isWorkingDay($user, $wednesday));
        $this->assertFalse($resolver->isWorkingDay($user, $saturday));
    }

    public function test_attendance_module_is_registered_in_config(): void
    {
        $modules = config('heritage_crm.modules');

        $this->assertArrayHasKey('attendance', $modules);
        $this->assertSame('Attendance', $modules['attendance']['label']);
        $this->assertSame('crm.attendance.grid', $modules['attendance']['route']);
        $this->assertSame('admin', $modules['attendance']['default_permissions']['admin']);
        $this->assertSame('view', $modules['attendance']['default_permissions']['finance']);
        $this->assertSame('edit', $modules['attendance']['default_permissions']['manager']);
        $this->assertSame('view', $modules['attendance']['default_permissions']['rep']);
    }

    public function test_attendance_config_block_exists(): void
    {
        $config = config('heritage_crm.attendance');

        $this->assertNotNull($config);
        $this->assertArrayHasKey('queue', $config);
        $this->assertArrayHasKey('clock_debounce_seconds', $config);
        $this->assertSame(60, $config['clock_debounce_seconds']);
        $this->assertSame('17:30', $config['mark_absent_at']);
    }

    public function test_shift_day_for_weekday_helper(): void
    {
        $shift = CrmAttendanceShift::where('is_default', true)->with('days')->first();

        $monday = $shift->dayForWeekday(0);
        $this->assertNotNull($monday);
        $this->assertTrue($monday->is_working_day);
        $this->assertSame('08:00:00', $monday->start_time);

        $sunday = $shift->dayForWeekday(6);
        $this->assertNotNull($sunday);
        $this->assertFalse($sunday->is_working_day);
    }

    public function test_attendance_record_scopes(): void
    {
        $user = $this->createUser();
        $code = CrmAttendanceCode::where('code', 'P')->first();

        CrmAttendanceRecord::create([
            'user_id' => $user->id,
            'date' => '2026-04-22',
            'attendance_code_id' => $code->id,
            'source' => 'manual',
        ]);

        CrmAttendanceRecord::create([
            'user_id' => $user->id,
            'date' => '2026-04-23',
            'attendance_code_id' => $code->id,
            'source' => 'manual',
        ]);

        $this->assertCount(1, CrmAttendanceRecord::forDate(Carbon::parse('2026-04-22'))->get());
        $this->assertCount(2, CrmAttendanceRecord::forDateRange(Carbon::parse('2026-04-22'), Carbon::parse('2026-04-23'))->get());
        $this->assertCount(2, CrmAttendanceRecord::forUser($user->id)->get());
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
