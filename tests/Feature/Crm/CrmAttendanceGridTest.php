<?php

namespace Tests\Feature\Crm;

use App\Models\CrmAttendanceCode;
use App\Models\CrmAttendanceCorrection;
use App\Models\CrmAttendanceRecord;
use App\Models\CrmAttendanceShift;
use App\Models\CrmUserDepartment;
use App\Models\User;
use App\Services\Crm\AttendanceClockService;
use App\Services\Crm\AttendanceGridService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CrmAttendanceGridTest extends TestCase
{
    use RefreshDatabase;

    public function test_grid_page_renders_for_admin(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $admin = $this->createUser(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('crm.attendance.grid'))
            ->assertOk()
            ->assertSee('Team Attendance')
            ->assertSee('Attendance Grid');

        Carbon::setTestNow();
    }

    public function test_grid_shows_users_grouped_by_department(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $deptA = CrmUserDepartment::create(['name' => 'Engineering', 'is_active' => true, 'sort_order' => 1]);
        $deptB = CrmUserDepartment::create(['name' => 'Sales', 'is_active' => true, 'sort_order' => 2]);

        $admin = $this->createUser(['role' => 'admin', 'department_id' => $deptA->id]);
        $this->createUser(['role' => 'rep', 'department_id' => $deptA->id]);
        $this->createUser(['role' => 'rep', 'department_id' => $deptB->id]);

        $this->actingAs($admin)
            ->get(route('crm.attendance.grid'))
            ->assertOk()
            ->assertSee('Engineering')
            ->assertSee('Sales');

        Carbon::setTestNow();
    }

    public function test_manager_sees_only_own_department(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $deptA = CrmUserDepartment::create(['name' => 'Engineering', 'is_active' => true, 'sort_order' => 1]);
        $deptB = CrmUserDepartment::create(['name' => 'Sales', 'is_active' => true, 'sort_order' => 2]);

        $manager = $this->createUser(['role' => 'manager', 'department_id' => $deptA->id]);
        $this->createUser(['role' => 'rep', 'department_id' => $deptA->id]);
        $salesUser = $this->createUser(['role' => 'rep', 'department_id' => $deptB->id]);

        $gridService = app(AttendanceGridService::class);
        $start = now()->startOfWeek();
        $end = $start->copy()->addDays(13);

        $teamGrid = $gridService->buildTeamGrid($manager, $start, $end);

        $allUserIds = collect($teamGrid['departments'])
            ->flatMap(fn ($group) => collect($group['users'])->pluck('user.id'))
            ->all();

        $this->assertContains($manager->id, $allUserIds);
        $this->assertNotContains($salesUser->id, $allUserIds);

        Carbon::setTestNow();
    }

    public function test_admin_sees_all_departments(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $deptA = CrmUserDepartment::create(['name' => 'Engineering', 'is_active' => true, 'sort_order' => 1]);
        $deptB = CrmUserDepartment::create(['name' => 'Sales', 'is_active' => true, 'sort_order' => 2]);

        $admin = $this->createUser(['role' => 'admin', 'department_id' => $deptA->id]);
        $salesUser = $this->createUser(['role' => 'rep', 'department_id' => $deptB->id]);

        $gridService = app(AttendanceGridService::class);
        $start = now()->startOfWeek();
        $end = $start->copy()->addDays(13);

        $teamGrid = $gridService->buildTeamGrid($admin, $start, $end);

        $allUserIds = collect($teamGrid['departments'])
            ->flatMap(fn ($group) => collect($group['users'])->pluck('user.id'))
            ->all();

        $this->assertContains($admin->id, $allUserIds);
        $this->assertContains($salesUser->id, $allUserIds);

        Carbon::setTestNow();
    }

    public function test_grid_returns_14_date_headers(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $gridService = app(AttendanceGridService::class);
        $start = now()->startOfWeek();
        $end = $start->copy()->addDays(13);

        $headers = $gridService->buildDateHeaders($start, $end);

        $this->assertCount(14, $headers);
        $this->assertSame($start->toDateString(), $headers[0]['date_string']);
        $this->assertSame($end->toDateString(), $headers[13]['date_string']);

        Carbon::setTestNow();
    }

    public function test_grid_marks_today_and_weekends(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $gridService = app(AttendanceGridService::class);
        $start = now()->startOfWeek();
        $end = $start->copy()->addDays(13);

        $headers = $gridService->buildDateHeaders($start, $end);

        $todayHeader = collect($headers)->firstWhere('is_today', true);
        $this->assertNotNull($todayHeader);
        $this->assertSame('2026-04-22', $todayHeader['date_string']);

        $weekendHeaders = collect($headers)->where('is_weekend', true);
        $this->assertGreaterThanOrEqual(4, $weekendHeaders->count());

        Carbon::setTestNow();
    }

    public function test_grid_shows_attendance_codes_for_records(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $user = $this->createUser(['role' => 'admin']);
        $code = CrmAttendanceCode::where('code', 'P')->first();

        CrmAttendanceRecord::create([
            'user_id' => $user->id,
            'date' => now()->startOfDay(),
            'attendance_code_id' => $code->id,
            'clocked_in_at' => now()->setTime(8, 0),
            'source' => 'manual',
        ]);

        $gridService = app(AttendanceGridService::class);
        $start = now()->startOfWeek();
        $end = $start->copy()->addDays(13);

        $teamGrid = $gridService->buildTeamGrid($user, $start, $end);

        $userRow = collect($teamGrid['departments'])
            ->flatMap(fn ($g) => $g['users'])
            ->firstWhere('user.id', $user->id);

        $todayCell = collect($userRow['days'])->firstWhere('date_string', '2026-04-22');
        $this->assertNotNull($todayCell['code']);
        $this->assertSame('P', $todayCell['code']->code);

        Carbon::setTestNow();
    }

    public function test_record_show_returns_json(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $admin = $this->createUser(['role' => 'admin']);
        $code = CrmAttendanceCode::where('code', 'P')->first();

        $record = CrmAttendanceRecord::create([
            'user_id' => $admin->id,
            'date' => now()->startOfDay(),
            'attendance_code_id' => $code->id,
            'clocked_in_at' => now()->setTime(8, 0),
            'source' => 'manual',
        ]);

        $this->actingAs($admin)
            ->getJson(route('crm.attendance.records.show', $record))
            ->assertOk()
            ->assertJsonStructure([
                'id', 'user_name', 'date', 'code', 'clocked_in_at',
                'source', 'is_late', 'status',
            ])
            ->assertJson([
                'id' => $record->id,
                'clocked_in_at' => '08:00',
            ]);

        Carbon::setTestNow();
    }

    public function test_record_update_creates_audit_entry(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $admin = $this->createUser(['role' => 'admin']);
        $presentCode = CrmAttendanceCode::where('code', 'P')->first();
        $wfhCode = CrmAttendanceCode::where('code', 'WFH')->first();

        $record = CrmAttendanceRecord::create([
            'user_id' => $admin->id,
            'date' => now()->startOfDay(),
            'attendance_code_id' => $presentCode->id,
            'clocked_in_at' => now()->setTime(8, 0),
            'source' => 'manual',
        ]);

        $this->actingAs($admin)
            ->putJson(route('crm.attendance.records.update', $record), [
                'attendance_code_id' => $wfhCode->id,
            ])
            ->assertOk()
            ->assertJson(['message' => 'Record updated.']);

        $record->refresh();
        $this->assertSame($wfhCode->id, $record->attendance_code_id);

        $correction = CrmAttendanceCorrection::where('attendance_record_id', $record->id)->first();
        $this->assertNotNull($correction);
        $this->assertSame('approved', $correction->status);
        $this->assertSame($admin->id, $correction->reviewed_by);
        $this->assertSame($presentCode->id, $correction->original_values['attendance_code_id']);

        Carbon::setTestNow();
    }

    public function test_manager_cannot_update_record_outside_department(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $deptA = CrmUserDepartment::create(['name' => 'Dept A', 'is_active' => true, 'sort_order' => 1]);
        $deptB = CrmUserDepartment::create(['name' => 'Dept B', 'is_active' => true, 'sort_order' => 2]);

        $manager = $this->createUser(['role' => 'manager', 'department_id' => $deptA->id]);
        $otherUser = $this->createUser(['role' => 'rep', 'department_id' => $deptB->id]);
        $code = CrmAttendanceCode::where('code', 'P')->first();

        $record = CrmAttendanceRecord::create([
            'user_id' => $otherUser->id,
            'date' => now()->startOfDay(),
            'attendance_code_id' => $code->id,
            'source' => 'manual',
        ]);

        $this->actingAs($manager)
            ->putJson(route('crm.attendance.records.update', $record), [
                'attendance_code_id' => $code->id,
            ])
            ->assertForbidden();

        Carbon::setTestNow();
    }

    public function test_rep_cannot_access_grid(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $rep = $this->createUser(['role' => 'rep']);

        $this->actingAs($rep)
            ->get(route('crm.attendance.grid'))
            ->assertForbidden();

        Carbon::setTestNow();
    }

    public function test_week_navigation_shifts_date_range(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $admin = $this->createUser(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->get(route('crm.attendance.grid', ['week' => -1]))
            ->assertOk();

        $prevWeekStart = now()->startOfWeek()->subWeek();
        $response->assertSee($prevWeekStart->format('d M'));

        Carbon::setTestNow();
    }

    public function test_search_filter_narrows_users(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $admin = $this->createUser(['role' => 'admin', 'name' => 'Alice Admin']);
        $this->createUser(['role' => 'rep', 'name' => 'Bob Builder']);

        $gridService = app(AttendanceGridService::class);
        $start = now()->startOfWeek();
        $end = $start->copy()->addDays(13);

        $teamGrid = $gridService->buildTeamGrid($admin, $start, $end, ['search' => 'Bob']);

        $allNames = collect($teamGrid['departments'])
            ->flatMap(fn ($g) => collect($g['users'])->pluck('user.name'))
            ->all();

        $this->assertContains('Bob Builder', $allNames);
        $this->assertNotContains('Alice Admin', $allNames);

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
