<?php

namespace Tests\Feature\Crm;

use App\Models\CrmAttendanceCode;
use App\Models\CrmAttendanceDevice;
use App\Models\CrmAttendanceDeviceLog;
use App\Models\CrmAttendanceRecord;
use App\Models\User;
use App\Services\Crm\BiometricEventProcessor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CrmAttendanceBiometricTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_biometric_clock_in_creates_attendance_record(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 08:00:00'));

        $user = $this->createUser(['personal_payroll_number' => 'EMP-001']);
        $device = $this->createDevice();
        $processor = app(BiometricEventProcessor::class);

        $log = $processor->process($device, [
            'device_id' => $device->device_identifier,
            'employee_identifier' => 'EMP-001',
            'event_type' => 'clock_in',
            'captured_at' => '2026-04-22T08:00:00+02:00',
            'verification_method' => 'fingerprint',
            'confidence_score' => 0.95,
        ]);

        $this->assertSame('processed', $log->status);
        $this->assertSame($user->id, $log->matched_user_id);
        $this->assertNotNull($log->attendance_record_id);

        $record = CrmAttendanceRecord::find($log->attendance_record_id);
        $this->assertNotNull($record);
        $this->assertSame($user->id, $record->user_id);
        $this->assertSame('P', $record->code->code);

        Carbon::setTestNow();
    }

    public function test_unmatched_employee_is_logged_not_processed(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 08:00:00'));

        $device = $this->createDevice();
        $processor = app(BiometricEventProcessor::class);

        $log = $processor->process($device, [
            'device_id' => $device->device_identifier,
            'employee_identifier' => 'UNKNOWN-999',
            'event_type' => 'clock_in',
            'captured_at' => '2026-04-22T08:00:00+02:00',
        ]);

        $this->assertSame('unmatched', $log->status);
        $this->assertNull($log->matched_user_id);
        $this->assertNull($log->attendance_record_id);
        $this->assertStringContains('No active user', $log->error_message);

        Carbon::setTestNow();
    }

    public function test_below_confidence_is_logged_not_processed(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 08:00:00'));

        $this->createUser(['personal_payroll_number' => 'EMP-001']);
        $device = $this->createDevice(['min_confidence' => 0.90]);
        $processor = app(BiometricEventProcessor::class);

        $log = $processor->process($device, [
            'device_id' => $device->device_identifier,
            'employee_identifier' => 'EMP-001',
            'event_type' => 'clock_in',
            'captured_at' => '2026-04-22T08:00:00+02:00',
            'confidence_score' => 0.75,
        ]);

        $this->assertSame('below_confidence', $log->status);
        $this->assertNull($log->attendance_record_id);

        Carbon::setTestNow();
    }

    public function test_duplicate_within_debounce_is_logged_not_processed(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 08:00:00'));

        $this->createUser(['personal_payroll_number' => 'EMP-001']);
        $device = $this->createDevice();
        $processor = app(BiometricEventProcessor::class);

        $payload = [
            'device_id' => $device->device_identifier,
            'employee_identifier' => 'EMP-001',
            'event_type' => 'clock_in',
            'captured_at' => '2026-04-22T08:00:00+02:00',
            'confidence_score' => 0.95,
        ];

        $first = $processor->process($device, $payload);
        $this->assertSame('processed', $first->status);

        Carbon::setTestNow(Carbon::parse('2026-04-22 08:00:30'));
        $second = $processor->process($device, array_merge($payload, [
            'captured_at' => '2026-04-22T08:00:30+02:00',
        ]));
        $this->assertSame('duplicate', $second->status);

        Carbon::setTestNow();
    }

    public function test_inactive_device_rejects_events(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 08:00:00'));

        $this->createUser(['personal_payroll_number' => 'EMP-001']);
        $device = $this->createDevice(['is_active' => false]);
        $processor = app(BiometricEventProcessor::class);

        $log = $processor->process($device, [
            'device_id' => $device->device_identifier,
            'employee_identifier' => 'EMP-001',
            'event_type' => 'clock_in',
            'captured_at' => '2026-04-22T08:00:00+02:00',
        ]);

        $this->assertSame('device_inactive', $log->status);

        Carbon::setTestNow();
    }

    public function test_biometric_api_endpoint_queues_event(): void
    {
        Queue::fake();
        Carbon::setTestNow(Carbon::parse('2026-04-22 08:00:00'));

        $admin = $this->createUser(['personal_payroll_number' => 'EMP-001']);
        $device = $this->createDevice();
        $token = $admin->createToken('test-device', ['attendance:biometric-push']);

        $this->postJson(route('api.crm.attendance.biometric-event'), [
            'device_id' => $device->device_identifier,
            'employee_identifier' => 'EMP-001',
            'event_type' => 'clock_in',
            'captured_at' => '2026-04-22T08:00:00+02:00',
            'verification_method' => 'fingerprint',
            'confidence_score' => 0.95,
        ], [
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])
        ->assertStatus(202)
        ->assertJson(['message' => 'Event queued for processing.']);

        Queue::assertPushed(\App\Jobs\ProcessBiometricEventJob::class);

        Carbon::setTestNow();
    }

    public function test_biometric_api_rejects_unknown_device(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 08:00:00'));

        $admin = $this->createUser();
        $token = $admin->createToken('test', ['attendance:biometric-push']);

        $this->postJson(route('api.crm.attendance.biometric-event'), [
            'device_id' => 'NONEXISTENT',
            'employee_identifier' => 'EMP-001',
            'event_type' => 'clock_in',
            'captured_at' => '2026-04-22T08:00:00+02:00',
        ], [
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])
        ->assertStatus(422)
        ->assertJson(['error' => 'Unknown or inactive device.']);

        Carbon::setTestNow();
    }

    public function test_heartbeat_updates_device_timestamp(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-22 10:00:00'));

        $admin = $this->createUser();
        $device = $this->createDevice();
        $token = $admin->createToken('test', ['attendance:biometric-push']);

        $this->assertNull($device->last_heartbeat_at);

        $this->postJson(route('api.crm.attendance.biometric-heartbeat'), [
            'device_id' => $device->device_identifier,
        ], [
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])
        ->assertOk()
        ->assertJson(['message' => 'Heartbeat received.']);

        $device->refresh();
        $this->assertNotNull($device->last_heartbeat_at);
        $this->assertSame('2026-04-22 10:00:00', $device->last_heartbeat_at->format('Y-m-d H:i:s'));

        Carbon::setTestNow();
    }

    public function test_device_crud_admin_only(): void
    {
        $admin = $this->createUser(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('crm.settings.attendance.index', ['tab' => 'devices']))
            ->assertOk()
            ->assertSee('Device List');

        $this->actingAs($admin)
            ->post(route('crm.settings.attendance.devices.store'), [
                'name' => 'Front Scanner',
                'brand' => 'zkteco',
                'model' => 'SpeedFace-V5L',
                'device_identifier' => 'BIO-FRONT-01',
                'serial_number' => 'CGXH201360239',
                'ip_address' => '192.168.1.201',
                'port' => '80',
                'direction' => 'both',
                'min_confidence' => '0.85',
                'is_active' => '1',
            ])
            ->assertRedirect(route('crm.settings.attendance.index', ['tab' => 'devices']));

        $device = CrmAttendanceDevice::where('device_identifier', 'BIO-FRONT-01')->first();
        $this->assertNotNull($device);
        $this->assertSame('Front Scanner', $device->name);
        $this->assertSame('zkteco', $device->brand);
        $this->assertSame('SpeedFace-V5L', $device->model);
        $this->assertSame('192.168.1.201', $device->ip_address);
        $this->assertSame('adms', $device->protocol);
        $this->assertNotNull($device->api_token_id);
    }

    public function test_rep_cannot_access_device_settings(): void
    {
        $rep = $this->createUser(['role' => 'rep']);

        $this->actingAs($rep)
            ->get(route('crm.settings.attendance.index', ['tab' => 'devices']))
            ->assertForbidden();
    }

    public function test_device_delete_removes_device_and_logs(): void
    {
        $admin = $this->createUser(['role' => 'admin']);
        $device = $this->createDevice();
        $deviceId = $device->id;

        CrmAttendanceDeviceLog::create([
            'device_id' => $device->id,
            'employee_identifier' => 'EMP-001',
            'event_type' => 'clock_in',
            'captured_at' => now(),
            'status' => 'processed',
            'created_at' => now(),
        ]);

        $this->assertSame(1, CrmAttendanceDeviceLog::where('device_id', $deviceId)->count());

        $this->actingAs($admin)
            ->delete(route('crm.settings.attendance.devices.destroy', $device))
            ->assertRedirect(route('crm.settings.attendance.index', ['tab' => 'devices']));

        $this->assertNull(CrmAttendanceDevice::find($deviceId));
        $this->assertSame(0, CrmAttendanceDeviceLog::where('device_id', $deviceId)->count());
    }

    public function test_device_token_regeneration(): void
    {
        $admin = $this->createUser(['role' => 'admin']);
        $device = $this->createDevice();
        $oldTokenId = $device->api_token_id;

        $this->actingAs($admin)
            ->post(route('crm.settings.attendance.devices.regenerate-token', $device))
            ->assertRedirect(route('crm.settings.attendance.index', ['tab' => 'devices']));

        $device->refresh();
        $this->assertNotNull($device->api_token_id);

        if ($oldTokenId) {
            $this->assertNotSame($oldTokenId, $device->api_token_id);
        }
    }

    public function test_device_online_status(): void
    {
        $device = $this->createDevice(['last_heartbeat_at' => now()->subMinutes(5)]);
        $this->assertTrue($device->isOnline());

        $offlineDevice = $this->createDevice([
            'device_identifier' => 'BIO-OFFLINE',
            'last_heartbeat_at' => now()->subMinutes(45),
        ]);
        $this->assertFalse($offlineDevice->isOnline());

        $neverDevice = $this->createDevice([
            'device_identifier' => 'BIO-NEVER',
            'last_heartbeat_at' => null,
        ]);
        $this->assertFalse($neverDevice->isOnline());
    }

    private function assertStringContains(string $needle, ?string $haystack): void
    {
        $this->assertNotNull($haystack);
        $this->assertStringContainsString($needle, $haystack);
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

    private function createDevice(array $attributes = []): CrmAttendanceDevice
    {
        $admin = User::query()->where('role', 'admin')->where('active', true)->first()
            ?? $this->createUser(['role' => 'admin']);

        $device = CrmAttendanceDevice::create(array_merge([
            'name' => 'Test Scanner',
            'brand' => 'zkteco',
            'device_identifier' => 'BIO-TEST-' . uniqid(),
            'direction' => 'both',
            'min_confidence' => 0.80,
            'is_active' => true,
        ], $attributes));

        if (! $device->api_token_id) {
            $token = $admin->createToken('device-' . $device->device_identifier, ['attendance:biometric-push']);
            $device->update(['api_token_id' => $token->accessToken->id]);
        }

        return $device;
    }
}
