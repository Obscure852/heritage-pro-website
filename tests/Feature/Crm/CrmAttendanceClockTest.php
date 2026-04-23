<?php

namespace Tests\Feature\Crm;

use App\Models\CrmAttendanceCode;
use App\Models\CrmAttendanceRecord;
use App\Models\CrmAttendanceShift;
use App\Models\User;
use App\Services\Crm\AttendanceClockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CrmAttendanceClockTest extends TestCase
{
    use RefreshDatabase;

    public function test_clock_in_creates_record_with_present_code(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 07:55:00'));

        $user = $this->createUser();
        $service = app(AttendanceClockService::class);

        $record = $service->clockIn($user, 'Good morning');

        $this->assertSame('P', $record->code->code);
        $this->assertSame('07:55', $record->clocked_in_at->format('H:i'));
        $this->assertNull($record->clocked_out_at);
        $this->assertSame('Good morning', $record->clock_in_note);
        $this->assertSame('manual', $record->source);
        $this->assertFalse($record->is_late);

        Carbon::setTestNow();
    }

    public function test_clock_in_after_grace_period_assigns_late_arrival_code(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 08:20:00'));

        $user = $this->createUser();
        $service = app(AttendanceClockService::class);

        $record = $service->clockIn($user);

        $this->assertSame('LA', $record->code->code);
        $this->assertTrue($record->is_late);

        Carbon::setTestNow();
    }

    public function test_clock_in_within_grace_period_assigns_present_code(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 08:14:00'));

        $user = $this->createUser();
        $service = app(AttendanceClockService::class);

        $record = $service->clockIn($user);

        $this->assertSame('P', $record->code->code);
        $this->assertFalse($record->is_late);

        Carbon::setTestNow();
    }

    public function test_clock_out_computes_total_minutes(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 07:55:00'));
        $user = $this->createUser();
        $service = app(AttendanceClockService::class);
        $service->clockIn($user);

        Carbon::setTestNow(Carbon::parse('2026-04-22 17:05:00'));
        $record = $service->clockOut($user, 'Bye');

        $this->assertNotNull($record->clocked_out_at);
        $this->assertSame('17:05', $record->clocked_out_at->format('H:i'));
        $this->assertSame('Bye', $record->clock_out_note);
        $expectedMinutes = 9 * 60 + 10;
        $this->assertSame($expectedMinutes, $record->total_minutes);

        Carbon::setTestNow();
    }

    public function test_clock_out_detects_early_out(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 08:00:00'));
        $user = $this->createUser();
        $service = app(AttendanceClockService::class);
        $service->clockIn($user);

        Carbon::setTestNow(Carbon::parse('2026-04-22 16:30:00'));
        $record = $service->clockOut($user);

        $this->assertTrue($record->is_early_out);

        Carbon::setTestNow();
    }

    public function test_clock_out_does_not_flag_early_when_past_threshold(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 08:00:00'));
        $user = $this->createUser();
        $service = app(AttendanceClockService::class);
        $service->clockIn($user);

        Carbon::setTestNow(Carbon::parse('2026-04-22 16:50:00'));
        $record = $service->clockOut($user);

        $this->assertFalse($record->is_early_out);

        Carbon::setTestNow();
    }

    public function test_clock_out_calculates_overtime(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 08:00:00'));
        $user = $this->createUser();
        $service = app(AttendanceClockService::class);
        $service->clockIn($user);

        Carbon::setTestNow(Carbon::parse('2026-04-22 18:00:00'));
        $record = $service->clockOut($user);

        $this->assertGreaterThan(0, $record->overtime_minutes);

        Carbon::setTestNow();
    }

    public function test_cannot_clock_out_without_clock_in(): void
    {
        $user = $this->createUser();
        $service = app(AttendanceClockService::class);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $service->clockOut($user);
    }

    public function test_clock_in_before_earliest_allowed_is_rejected(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 05:30:00'));

        $user = $this->createUser();
        $service = app(AttendanceClockService::class);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $service->clockIn($user);

        Carbon::setTestNow();
    }

    public function test_toggle_clocks_in_then_out(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 08:00:00'));
        $user = $this->createUser();
        $service = app(AttendanceClockService::class);

        $resultIn = $service->toggle($user);
        $this->assertSame('clocked_in', $resultIn['action']);

        Carbon::setTestNow(Carbon::parse('2026-04-22 17:00:00'));
        $resultOut = $service->toggle($user);
        $this->assertSame('clocked_out', $resultOut['action']);

        $resultDone = $service->toggle($user);
        $this->assertSame('already_completed', $resultDone['action']);

        Carbon::setTestNow();
    }

    public function test_duplicate_clock_in_returns_existing_record(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 08:00:00'));
        $user = $this->createUser();
        $service = app(AttendanceClockService::class);

        $first = $service->clockIn($user);

        Carbon::setTestNow(Carbon::parse('2026-04-22 08:00:30'));
        $second = $service->clockIn($user);

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, CrmAttendanceRecord::where('user_id', $user->id)->count());

        Carbon::setTestNow();
    }

    public function test_clock_route_returns_json(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 08:00:00'));
        $user = $this->createUser();

        $response = $this->actingAs($user)
            ->postJson(route('crm.attendance.clock'), ['note' => 'Starting work'])
            ->assertOk()
            ->assertJsonStructure(['status', 'message', 'clocked_in_at', 'code', 'is_late']);

        $this->assertSame('clocked_in', $response->json('status'));
        $this->assertSame('P', $response->json('code'));
        $this->assertSame('08:00', $response->json('clocked_in_at'));

        Carbon::setTestNow();
    }

    public function test_clock_status_route_returns_json(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 08:00:00'));
        $user = $this->createUser();

        $this->actingAs($user)
            ->getJson(route('crm.attendance.clock-status'))
            ->assertOk()
            ->assertJson(['state' => 'clocked_out']);

        $this->actingAs($user)
            ->postJson(route('crm.attendance.clock'))
            ->assertOk();

        $this->actingAs($user)
            ->getJson(route('crm.attendance.clock-status'))
            ->assertOk()
            ->assertJson(['state' => 'clocked_in']);

        Carbon::setTestNow();
    }

    public function test_dashboard_shows_clock_widget(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));
        $user = $this->createUser();

        $this->actingAs($user)
            ->get(route('crm.dashboard'))
            ->assertOk()
            ->assertSee('Clock In');

        Carbon::setTestNow();
    }

    public function test_dashboard_shows_clocked_in_status(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 08:00:00'));
        $user = $this->createUser();

        app(AttendanceClockService::class)->clockIn($user);

        $this->actingAs($user)
            ->get(route('crm.dashboard'))
            ->assertOk()
            ->assertSee('Clocked In')
            ->assertSee('Clock Out');

        Carbon::setTestNow();
    }

    public function test_clock_on_non_working_day_assigns_present_code(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-25 10:00:00'));

        $user = $this->createUser();
        $service = app(AttendanceClockService::class);

        $record = $service->clockIn($user);

        $this->assertSame('P', $record->code->code);
        $this->assertFalse($record->is_late);

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
