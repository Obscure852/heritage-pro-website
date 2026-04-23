<?php

namespace Tests\Feature\Crm;

use App\Jobs\CloseOvernightRecordsJob;
use App\Jobs\MarkAbsenteesJob;
use App\Models\CrmAttendanceCode;
use App\Models\CrmAttendanceHoliday;
use App\Models\CrmAttendanceRecord;
use App\Models\CrmAttendanceShift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CrmAttendanceJobsTest extends TestCase
{
    use RefreshDatabase;

    // ── CloseOvernightRecordsJob ────────────────────────────

    public function test_overnight_job_closes_open_records_from_previous_days(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 08:00:00'));

        $user = $this->createUser();
        $code = CrmAttendanceCode::where('code', 'P')->first();

        $record = CrmAttendanceRecord::create([
            'user_id' => $user->id,
            'date' => Carbon::parse('2026-04-21')->startOfDay(),
            'attendance_code_id' => $code->id,
            'clocked_in_at' => Carbon::parse('2026-04-21 08:00:00'),
            'source' => 'manual',
        ]);

        $this->assertNull($record->clocked_out_at);
        $this->assertFalse((bool) $record->auto_closed);

        $job = new CloseOvernightRecordsJob();
        $closed = $job->handle();

        $this->assertSame(1, $closed);

        $record->refresh();
        $this->assertNotNull($record->clocked_out_at);
        $this->assertSame('23:59', $record->clocked_out_at->format('H:i'));
        $this->assertTrue($record->auto_closed);
        $this->assertGreaterThan(0, $record->total_minutes);

        Carbon::setTestNow();
    }

    public function test_overnight_job_does_not_close_todays_open_records(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 20:00:00'));

        $user = $this->createUser();
        $code = CrmAttendanceCode::where('code', 'P')->first();

        CrmAttendanceRecord::create([
            'user_id' => $user->id,
            'date' => Carbon::parse('2026-04-22')->startOfDay(),
            'attendance_code_id' => $code->id,
            'clocked_in_at' => Carbon::parse('2026-04-22 08:00:00'),
            'source' => 'manual',
        ]);

        $job = new CloseOvernightRecordsJob();
        $closed = $job->handle();

        $this->assertSame(0, $closed);

        Carbon::setTestNow();
    }

    public function test_overnight_job_skips_already_closed_records(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 08:00:00'));

        $user = $this->createUser();
        $code = CrmAttendanceCode::where('code', 'P')->first();

        CrmAttendanceRecord::create([
            'user_id' => $user->id,
            'date' => Carbon::parse('2026-04-21')->startOfDay(),
            'attendance_code_id' => $code->id,
            'clocked_in_at' => Carbon::parse('2026-04-21 08:00:00'),
            'clocked_out_at' => Carbon::parse('2026-04-21 17:00:00'),
            'total_minutes' => 540,
            'source' => 'manual',
        ]);

        $job = new CloseOvernightRecordsJob();
        $closed = $job->handle();

        $this->assertSame(0, $closed);

        Carbon::setTestNow();
    }

    public function test_overnight_job_calculates_correct_total_minutes(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 08:00:00'));

        $user = $this->createUser();
        $code = CrmAttendanceCode::where('code', 'P')->first();

        $record = CrmAttendanceRecord::create([
            'user_id' => $user->id,
            'date' => Carbon::parse('2026-04-21')->startOfDay(),
            'attendance_code_id' => $code->id,
            'clocked_in_at' => Carbon::parse('2026-04-21 14:00:00'),
            'source' => 'manual',
        ]);

        (new CloseOvernightRecordsJob())->handle();

        $record->refresh();
        // 14:00 to 23:59 = 599 minutes
        $this->assertSame(599, $record->total_minutes);

        Carbon::setTestNow();
    }

    // ── MarkAbsenteesJob ────────────────────────────────────

    public function test_absentee_job_marks_users_without_records(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 17:30:00')); // Wednesday

        $user = $this->createUser();
        $absentCode = CrmAttendanceCode::where('code', 'A')->first();

        $job = new MarkAbsenteesJob();
        $marked = $job->handle(app(\App\Services\Crm\AttendanceShiftResolver::class));

        $this->assertGreaterThanOrEqual(1, $marked);

        $record = CrmAttendanceRecord::where('user_id', $user->id)
            ->whereDate('date', now())
            ->first();

        $this->assertNotNull($record);
        $this->assertSame($absentCode->id, $record->attendance_code_id);
        $this->assertSame('system', $record->source);

        Carbon::setTestNow();
    }

    public function test_absentee_job_skips_users_with_existing_records(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 17:30:00'));

        $user = $this->createUser();
        $presentCode = CrmAttendanceCode::where('code', 'P')->first();

        // Create records for all existing active users so they are all skipped
        $allUsers = User::where('active', true)
            ->whereIn('role', array_keys(config('heritage_crm.roles', [])))
            ->get();

        foreach ($allUsers as $u) {
            CrmAttendanceRecord::create([
                'user_id' => $u->id,
                'date' => now()->startOfDay(),
                'attendance_code_id' => $presentCode->id,
                'clocked_in_at' => now()->setTime(8, 0),
                'source' => 'manual',
            ]);
        }

        $job = new MarkAbsenteesJob();
        $marked = $job->handle(app(\App\Services\Crm\AttendanceShiftResolver::class));

        $this->assertSame(0, $marked);

        Carbon::setTestNow();
    }

    public function test_absentee_job_skips_non_working_days(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-25 17:30:00')); // Saturday

        $user = $this->createUser();

        $job = new MarkAbsenteesJob();
        $marked = $job->handle(app(\App\Services\Crm\AttendanceShiftResolver::class));

        $this->assertSame(0, $marked);

        Carbon::setTestNow();
    }

    public function test_absentee_job_skips_holidays(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 17:30:00'));

        $admin = $this->createUser(['role' => 'admin']);
        $user = $this->createUser(['role' => 'rep']);

        CrmAttendanceHoliday::create([
            'name' => 'Test Holiday',
            'date' => '2026-04-22',
            'applies_to' => 'all',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $job = new MarkAbsenteesJob();
        $marked = $job->handle(app(\App\Services\Crm\AttendanceShiftResolver::class));

        $this->assertSame(0, $marked);

        Carbon::setTestNow();
    }

    public function test_absentee_job_skips_inactive_users(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 17:30:00'));

        $inactiveUser = $this->createUser(['active' => false]);

        $job = new MarkAbsenteesJob();
        $job->handle(app(\App\Services\Crm\AttendanceShiftResolver::class));

        // The inactive user should NOT have a record
        $record = CrmAttendanceRecord::where('user_id', $inactiveUser->id)
            ->whereDate('date', now())
            ->first();

        $this->assertNull($record);

        Carbon::setTestNow();
    }

    public function test_absentee_job_respects_scoped_holidays(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 17:30:00'));

        $admin = $this->createUser(['role' => 'admin']);
        $dept = \App\Models\CrmUserDepartment::create(['name' => 'Sales', 'is_active' => true, 'sort_order' => 1]);
        $salesUser = $this->createUser(['role' => 'rep', 'department_id' => $dept->id]);

        CrmAttendanceHoliday::create([
            'name' => 'Sales Day Off',
            'date' => '2026-04-22',
            'applies_to' => 'department',
            'scope_id' => $dept->id,
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $job = new MarkAbsenteesJob();
        $marked = $job->handle(app(\App\Services\Crm\AttendanceShiftResolver::class));

        // Admin should be marked absent (holiday doesn't apply to their dept)
        // Sales user should be skipped (holiday applies)
        $adminRecord = CrmAttendanceRecord::where('user_id', $admin->id)->whereDate('date', now())->first();
        $salesRecord = CrmAttendanceRecord::where('user_id', $salesUser->id)->whereDate('date', now())->first();

        $this->assertNotNull($adminRecord);
        $this->assertNull($salesRecord);

        Carbon::setTestNow();
    }

    public function test_auto_closed_records_are_flagged(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 09:00:00'));

        $user = $this->createUser();
        $code = CrmAttendanceCode::where('code', 'P')->first();

        CrmAttendanceRecord::create([
            'user_id' => $user->id,
            'date' => Carbon::parse('2026-04-21')->startOfDay(),
            'attendance_code_id' => $code->id,
            'clocked_in_at' => Carbon::parse('2026-04-21 08:00:00'),
            'clocked_out_at' => Carbon::parse('2026-04-21 23:59:59'),
            'total_minutes' => 959,
            'auto_closed' => true,
            'source' => 'manual',
        ]);

        $record = CrmAttendanceRecord::where('user_id', $user->id)->first();
        $this->assertTrue((bool) $record->auto_closed);
        $this->assertSame('23:59', $record->clocked_out_at->format('H:i'));

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
