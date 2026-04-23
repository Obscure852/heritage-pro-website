<?php

namespace Tests\Feature\Crm;

use App\Models\CrmAttendanceCode;
use App\Models\CrmAttendanceDevice;
use App\Models\CrmAttendanceDeviceLog;
use App\Models\CrmAttendanceRecord;
use App\Models\CrmUserDepartment;
use App\Models\User;
use App\Services\Crm\AttendanceReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CrmAttendanceReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_index_renders_for_admin(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $admin = $this->createUser(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('crm.attendance.reports'))
            ->assertOk()
            ->assertSee('Attendance Reports')
            ->assertSee('Daily Summary')
            ->assertSee('Monthly Register');

        Carbon::setTestNow();
    }

    public function test_rep_cannot_access_reports(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $rep = $this->createUser(['role' => 'rep']);

        $this->actingAs($rep)
            ->get(route('crm.attendance.reports'))
            ->assertForbidden();

        Carbon::setTestNow();
    }

    public function test_daily_summary_report_renders(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $admin = $this->createUser(['role' => 'admin']);
        $code = CrmAttendanceCode::where('code', 'P')->first();

        CrmAttendanceRecord::create([
            'user_id' => $admin->id,
            'date' => now()->startOfDay(),
            'attendance_code_id' => $code->id,
            'clocked_in_at' => now()->setTime(8, 0),
            'clocked_out_at' => now()->setTime(17, 0),
            'total_minutes' => 540,
            'source' => 'manual',
        ]);

        $this->actingAs($admin)
            ->get(route('crm.attendance.reports.show', 'daily-summary'))
            ->assertOk()
            ->assertSee('Daily Attendance Summary');

        Carbon::setTestNow();
    }

    public function test_monthly_register_report_renders(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $admin = $this->createUser(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('crm.attendance.reports.show', 'monthly-register'))
            ->assertOk()
            ->assertSee('Monthly Attendance Register');

        Carbon::setTestNow();
    }

    public function test_hours_worked_report_renders(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $admin = $this->createUser(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('crm.attendance.reports.show', 'hours-worked'))
            ->assertOk()
            ->assertSee('Hours Worked Summary');

        Carbon::setTestNow();
    }

    public function test_daily_summary_service_returns_correct_data(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $user = $this->createUser();
        $code = CrmAttendanceCode::where('code', 'P')->first();

        CrmAttendanceRecord::create([
            'user_id' => $user->id,
            'date' => now()->startOfDay(),
            'attendance_code_id' => $code->id,
            'clocked_in_at' => now()->setTime(8, 0),
            'clocked_out_at' => now()->setTime(17, 0),
            'total_minutes' => 540,
            'source' => 'manual',
            'is_late' => false,
        ]);

        $service = app(AttendanceReportService::class);
        $rows = $service->dailySummary(now());

        $this->assertGreaterThanOrEqual(1, $rows->count());

        $row = $rows->firstWhere('user_name', $user->name);
        $this->assertNotNull($row);
        $this->assertSame('P', $row['code']);
        $this->assertSame('08:00', $row['clocked_in']);
        $this->assertSame('17:00', $row['clocked_out']);
        $this->assertSame(9.0, $row['total_hours']);

        Carbon::setTestNow();
    }

    public function test_hours_worked_service_groups_by_user(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $user = $this->createUser();
        $code = CrmAttendanceCode::where('code', 'P')->first();

        CrmAttendanceRecord::create([
            'user_id' => $user->id,
            'date' => Carbon::parse('2026-04-21')->startOfDay(),
            'attendance_code_id' => $code->id,
            'total_minutes' => 480,
            'source' => 'manual',
        ]);

        CrmAttendanceRecord::create([
            'user_id' => $user->id,
            'date' => Carbon::parse('2026-04-22')->startOfDay(),
            'attendance_code_id' => $code->id,
            'total_minutes' => 510,
            'source' => 'manual',
        ]);

        $service = app(AttendanceReportService::class);
        $rows = $service->hoursWorked(Carbon::parse('2026-04-20'), Carbon::parse('2026-04-22'));

        $row = $rows->firstWhere('user_name', $user->name);
        $this->assertNotNull($row);
        $this->assertSame(2, $row['working_days']);
        $this->assertSame(16.5, $row['total_hours']);

        Carbon::setTestNow();
    }

    public function test_late_arrivals_service_returns_late_records(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $user = $this->createUser();
        $laCode = CrmAttendanceCode::where('code', 'LA')->first();

        CrmAttendanceRecord::create([
            'user_id' => $user->id,
            'date' => now()->startOfDay(),
            'attendance_code_id' => $laCode->id,
            'clocked_in_at' => now()->setTime(8, 25),
            'is_late' => true,
            'source' => 'manual',
        ]);

        $service = app(AttendanceReportService::class);
        $rows = $service->lateArrivals(now()->subDays(1), now());

        $this->assertGreaterThanOrEqual(1, $rows->count());
        $row = $rows->firstWhere('user_name', $user->name);
        $this->assertSame('08:25', $row['clocked_in']);

        Carbon::setTestNow();
    }

    public function test_absenteeism_service_counts_absent_days(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $user = $this->createUser();
        $absentCode = CrmAttendanceCode::where('code', 'A')->first();

        CrmAttendanceRecord::create([
            'user_id' => $user->id,
            'date' => Carbon::parse('2026-04-21')->startOfDay(),
            'attendance_code_id' => $absentCode->id,
            'source' => 'system',
        ]);

        CrmAttendanceRecord::create([
            'user_id' => $user->id,
            'date' => Carbon::parse('2026-04-22')->startOfDay(),
            'attendance_code_id' => $absentCode->id,
            'source' => 'system',
        ]);

        $service = app(AttendanceReportService::class);
        $rows = $service->absenteeism(Carbon::parse('2026-04-20'), Carbon::parse('2026-04-22'));

        $row = $rows->firstWhere('user_name', $user->name);
        $this->assertNotNull($row);
        $this->assertSame(2, $row['absent_days']);

        Carbon::setTestNow();
    }

    public function test_monthly_register_service_builds_grid(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $user = $this->createUser();
        $code = CrmAttendanceCode::where('code', 'P')->first();

        CrmAttendanceRecord::create([
            'user_id' => $user->id,
            'date' => Carbon::parse('2026-04-15')->startOfDay(),
            'attendance_code_id' => $code->id,
            'source' => 'manual',
        ]);

        $service = app(AttendanceReportService::class);
        $register = $service->monthlyRegister(Carbon::parse('2026-04-01'));

        $this->assertSame('April 2026', $register['month_label']);
        $this->assertSame(30, count($register['days']));
        $this->assertGreaterThanOrEqual(1, count($register['rows']));

        $userRow = collect($register['rows'])->firstWhere(fn ($r) => $r['user']->id === $user->id);
        $this->assertNotNull($userRow);
        $this->assertSame('P', $userRow['codes'][14]); // April 15 = index 14

        Carbon::setTestNow();
    }

    public function test_excel_export_downloads_file(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $admin = $this->createUser(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('crm.attendance.reports.export', 'daily-summary'))
            ->assertOk()
            ->assertHeader('content-disposition');

        Carbon::setTestNow();
    }

    public function test_monthly_register_export_downloads_file(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $admin = $this->createUser(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('crm.attendance.reports.export', 'monthly-register'))
            ->assertOk()
            ->assertHeader('content-disposition');

        Carbon::setTestNow();
    }

    public function test_department_filter_narrows_daily_summary(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $deptA = CrmUserDepartment::create(['name' => 'Engineering', 'is_active' => true, 'sort_order' => 1]);
        $deptB = CrmUserDepartment::create(['name' => 'Sales', 'is_active' => true, 'sort_order' => 2]);

        $engUser = $this->createUser(['department_id' => $deptA->id, 'name' => 'Eng User']);
        $salesUser = $this->createUser(['department_id' => $deptB->id, 'name' => 'Sales User']);
        $code = CrmAttendanceCode::where('code', 'P')->first();

        CrmAttendanceRecord::create(['user_id' => $engUser->id, 'date' => now()->startOfDay(), 'attendance_code_id' => $code->id, 'source' => 'manual']);
        CrmAttendanceRecord::create(['user_id' => $salesUser->id, 'date' => now()->startOfDay(), 'attendance_code_id' => $code->id, 'source' => 'manual']);

        $service = app(AttendanceReportService::class);
        $filtered = $service->dailySummary(now(), ['department_id' => $deptA->id]);

        $names = $filtered->pluck('user_name')->toArray();
        $this->assertContains('Eng User', $names);
        $this->assertNotContains('Sales User', $names);

        Carbon::setTestNow();
    }

    public function test_today_stats_returns_counts(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $user = $this->createUser();
        $presentCode = CrmAttendanceCode::where('code', 'P')->first();

        CrmAttendanceRecord::create([
            'user_id' => $user->id,
            'date' => now()->startOfDay(),
            'attendance_code_id' => $presentCode->id,
            'source' => 'manual',
        ]);

        $service = app(AttendanceReportService::class);
        $stats = $service->todayStats();

        $this->assertArrayHasKey('total', $stats);
        $this->assertArrayHasKey('present', $stats);
        $this->assertArrayHasKey('late', $stats);
        $this->assertArrayHasKey('absent', $stats);
        $this->assertGreaterThanOrEqual(1, $stats['total']);
        $this->assertGreaterThanOrEqual(1, $stats['present']);

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
