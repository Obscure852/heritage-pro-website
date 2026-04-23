<?php

namespace App\Services\Crm;

use App\Models\CrmAttendanceDevice;
use App\Models\CrmAttendanceDeviceLog;
use App\Models\CrmAttendanceRecord;
use App\Models\User;
use Illuminate\Support\Carbon;

class BiometricEventProcessor
{
    public function __construct(
        private readonly AttendanceClockService $clockService
    ) {
    }

    public function process(CrmAttendanceDevice $device, array $payload): CrmAttendanceDeviceLog
    {
        $normalised = $this->normalisePayload($device, $payload);
        $capturedAt = Carbon::parse($normalised['captured_at']);

        if (! $device->is_active) {
            return $this->createLog($device, $normalised, 'device_inactive', errorMessage: 'Device is deactivated.', rawPayload: $payload);
        }

        $confidence = $normalised['confidence_score'];

        if ($confidence !== null && $confidence < (float) $device->min_confidence) {
            return $this->createLog($device, $normalised, 'below_confidence',
                errorMessage: 'Confidence ' . $confidence . ' below threshold ' . $device->min_confidence . '.', rawPayload: $payload);
        }

        $user = User::query()
            ->where('personal_payroll_number', $normalised['employee_identifier'])
            ->where('active', true)
            ->first();

        if (! $user) {
            return $this->createLog($device, $normalised, 'unmatched',
                errorMessage: 'No active user found with payroll number: ' . $normalised['employee_identifier'], rawPayload: $payload);
        }

        $debounceSeconds = (int) config('heritage_crm.attendance.clock_debounce_seconds', 60);
        $recentLog = CrmAttendanceDeviceLog::query()
            ->where('device_id', $device->id)
            ->where('employee_identifier', $normalised['employee_identifier'])
            ->where('event_type', $normalised['event_type'])
            ->where('status', 'processed')
            ->where('captured_at', '>=', $capturedAt->copy()->subSeconds($debounceSeconds))
            ->exists();

        if ($recentLog) {
            return $this->createLog($device, $normalised, 'duplicate', matchedUserId: $user->id,
                errorMessage: 'Duplicate event within debounce window.', rawPayload: $payload);
        }

        $eventType = $normalised['event_type'];
        $record = null;

        if ($eventType === 'clock_in') {
            $record = $this->processClockIn($user, $capturedAt);
        } elseif ($eventType === 'clock_out') {
            $record = $this->processClockOut($user, $capturedAt);
        }

        return $this->createLog($device, $normalised, 'processed',
            matchedUserId: $user->id,
            attendanceRecordId: $record?->id,
            rawPayload: $payload);
    }

    /**
     * Normalise payloads from different device brands into a common format.
     */
    private function normalisePayload(CrmAttendanceDevice $device, array $payload): array
    {
        if ($device->isZkteco()) {
            return $this->normaliseZktecoPayload($payload);
        }

        if ($device->isHikvision()) {
            return $this->normaliseHikvisionPayload($payload);
        }

        return $this->normaliseGenericPayload($payload);
    }

    /**
     * ZKTeco ADMS format:
     * PIN \t Timestamp \t Status \t Verify \t WorkCode \t Reserved1 \t Reserved2 \t Reserved3
     *
     * Or structured JSON when forwarded via middleware.
     */
    private function normaliseZktecoPayload(array $payload): array
    {
        $verifyCodes = config('heritage_crm.attendance.zkteco_verify_codes', []);
        $punchStates = config('heritage_crm.attendance.zkteco_punch_states', []);

        if (isset($payload['raw_line'])) {
            $parts = preg_split('/\t/', $payload['raw_line']);

            return [
                'employee_identifier' => trim($parts[0] ?? ''),
                'captured_at' => trim($parts[1] ?? now()->toDateTimeString()),
                'event_type' => $punchStates[(int) ($parts[2] ?? 0)] ?? 'clock_in',
                'verification_method' => $verifyCodes[(int) ($parts[3] ?? 0)] ?? 'fingerprint',
                'work_code' => trim($parts[4] ?? '') ?: null,
                'confidence_score' => isset($payload['confidence_score']) ? (float) $payload['confidence_score'] : null,
                'card_number' => $payload['card_number'] ?? null,
                'temperature' => isset($payload['temperature']) ? (float) $payload['temperature'] : null,
            ];
        }

        $verifyCode = isset($payload['verify_type']) ? (int) $payload['verify_type'] : null;
        $punchState = isset($payload['punch_state']) ? (int) $payload['punch_state'] : null;

        return [
            'employee_identifier' => $payload['pin'] ?? $payload['employee_identifier'] ?? '',
            'captured_at' => $payload['timestamp'] ?? $payload['captured_at'] ?? now()->toDateTimeString(),
            'event_type' => $punchState !== null
                ? ($punchStates[$punchState] ?? 'clock_in')
                : ($payload['event_type'] ?? 'clock_in'),
            'verification_method' => $verifyCode !== null
                ? ($verifyCodes[$verifyCode] ?? 'fingerprint')
                : ($payload['verification_method'] ?? null),
            'work_code' => $payload['work_code'] ?? null,
            'confidence_score' => isset($payload['confidence_score']) ? (float) $payload['confidence_score'] : null,
            'card_number' => $payload['card_number'] ?? $payload['card_no'] ?? null,
            'temperature' => isset($payload['temperature']) ? (float) $payload['temperature'] : null,
        ];
    }

    /**
     * Hikvision ISAPI AccessControllerEvent format.
     */
    private function normaliseHikvisionPayload(array $payload): array
    {
        $event = $payload['AccessControllerEvent'] ?? $payload;
        $attendanceStatus = $event['attendanceStatus'] ?? $payload['attendanceStatus'] ?? null;

        $eventType = match ($attendanceStatus) {
            'checkIn', 'goIn' => 'clock_in',
            'checkOut', 'goOut' => 'clock_out',
            'breakOut' => 'break_out',
            'breakIn' => 'break_in',
            'overtimeIn' => 'overtime_in',
            'overtimeOut' => 'overtime_out',
            default => $payload['event_type'] ?? 'clock_in',
        };

        $verifyMode = $event['currentVerifyMode'] ?? $payload['currentVerifyMode'] ?? null;
        $verifyMethod = match ($verifyMode) {
            'fingerPrint', 'fingerprint' => 'fingerprint',
            'face', 'faceRecognition' => 'face',
            'card', 'cardNo' => 'card',
            'password', 'pin' => 'pin',
            'iris' => 'iris',
            'palm', 'palmPrint' => 'palm',
            default => $verifyMode,
        };

        return [
            'employee_identifier' => $event['employeeNoString'] ?? $event['employeeNo'] ?? $payload['employee_identifier'] ?? '',
            'captured_at' => $payload['dateTime'] ?? $event['dateTime'] ?? $payload['captured_at'] ?? now()->toDateTimeString(),
            'event_type' => $eventType,
            'verification_method' => $verifyMethod,
            'work_code' => null,
            'confidence_score' => isset($payload['confidence_score']) ? (float) $payload['confidence_score'] : null,
            'card_number' => $event['cardNo'] ?? $payload['card_number'] ?? null,
            'temperature' => isset($event['currTemperature']) ? (float) $event['currTemperature'] : (isset($payload['temperature']) ? (float) $payload['temperature'] : null),
        ];
    }

    /**
     * Generic / other brands — expects a standard JSON payload.
     */
    private function normaliseGenericPayload(array $payload): array
    {
        return [
            'employee_identifier' => $payload['employee_identifier'] ?? $payload['pin'] ?? '',
            'captured_at' => $payload['captured_at'] ?? $payload['timestamp'] ?? now()->toDateTimeString(),
            'event_type' => $payload['event_type'] ?? 'clock_in',
            'verification_method' => $payload['verification_method'] ?? null,
            'work_code' => $payload['work_code'] ?? null,
            'confidence_score' => isset($payload['confidence_score']) ? (float) $payload['confidence_score'] : null,
            'card_number' => $payload['card_number'] ?? null,
            'temperature' => isset($payload['temperature']) ? (float) $payload['temperature'] : null,
        ];
    }

    private function processClockIn(User $user, Carbon $capturedAt): ?CrmAttendanceRecord
    {
        Carbon::setTestNow($capturedAt);

        try {
            return $this->clockService->clockIn($user);
        } finally {
            Carbon::setTestNow();
        }
    }

    private function processClockOut(User $user, Carbon $capturedAt): ?CrmAttendanceRecord
    {
        Carbon::setTestNow($capturedAt);

        try {
            return $this->clockService->clockOut($user);
        } catch (\Throwable) {
            return null;
        } finally {
            Carbon::setTestNow();
        }
    }

    private function createLog(
        CrmAttendanceDevice $device,
        array $normalised,
        string $status,
        ?int $matchedUserId = null,
        ?int $attendanceRecordId = null,
        ?string $errorMessage = null,
        ?array $rawPayload = null
    ): CrmAttendanceDeviceLog {
        return CrmAttendanceDeviceLog::create([
            'device_id' => $device->id,
            'employee_identifier' => $normalised['employee_identifier'],
            'event_type' => $normalised['event_type'],
            'captured_at' => Carbon::parse($normalised['captured_at']),
            'verification_method' => $normalised['verification_method'] ?? null,
            'card_number' => $normalised['card_number'] ?? null,
            'temperature' => $normalised['temperature'] ?? null,
            'work_code' => $normalised['work_code'] ?? null,
            'confidence_score' => $normalised['confidence_score'] ?? null,
            'status' => $status,
            'matched_user_id' => $matchedUserId,
            'attendance_record_id' => $attendanceRecordId,
            'error_message' => $errorMessage,
            'raw_payload' => $rawPayload ? json_encode($rawPayload) : null,
            'created_at' => now(),
        ]);
    }
}
