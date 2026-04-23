<?php

namespace App\Http\Controllers\Api\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\BiometricEventRequest;
use App\Jobs\ProcessBiometricEventJob;
use App\Models\CrmAttendanceDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BiometricController extends Controller
{
    /**
     * Generic JSON push endpoint for all device brands.
     * POST /api/crm/attendance/biometric-event
     */
    public function event(BiometricEventRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $device = CrmAttendanceDevice::query()
            ->where('device_identifier', $validated['device_id'])
            ->where('is_active', true)
            ->first();

        if (! $device) {
            return response()->json(['error' => 'Unknown or inactive device.'], 422);
        }

        ProcessBiometricEventJob::dispatch($device, $validated);

        return response()->json(['message' => 'Event queued for processing.'], 202);
    }

    /**
     * Device heartbeat / keep-alive.
     * POST /api/crm/attendance/biometric-heartbeat
     */
    public function heartbeat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => ['required', 'string', 'max:50'],
        ]);

        $device = CrmAttendanceDevice::query()
            ->where('device_identifier', $validated['device_id'])
            ->where('is_active', true)
            ->first();

        if (! $device) {
            return response()->json(['error' => 'Unknown or inactive device.'], 422);
        }

        $device->update(['last_heartbeat_at' => now()]);

        return response()->json(['message' => 'Heartbeat received.']);
    }

    /**
     * ZKTeco ADMS — device handshake and data exchange.
     * GET  /api/crm/attendance/iclock/cdata  (handshake + config)
     * POST /api/crm/attendance/iclock/cdata  (attendance log push)
     */
    public function iclockCdata(Request $request): Response
    {
        $serialNumber = $request->query('SN', '');

        $device = CrmAttendanceDevice::query()
            ->where(function ($q) use ($serialNumber) {
                $q->where('serial_number', $serialNumber)
                    ->orWhere('device_identifier', $serialNumber);
            })
            ->where('brand', 'zkteco')
            ->first();

        if (! $device) {
            return response('UNKNOWN DEVICE', 404)
                ->header('Content-Type', 'text/plain');
        }

        $device->update(['last_heartbeat_at' => now()]);

        if ($request->isMethod('GET')) {
            return $this->iclockHandshake($device, $request);
        }

        return $this->iclockReceiveData($device, $request);
    }

    /**
     * ZKTeco ADMS — device polls for pending commands.
     * GET /api/crm/attendance/iclock/getrequest
     */
    public function iclockGetRequest(Request $request): Response
    {
        $serialNumber = $request->query('SN', '');

        $device = CrmAttendanceDevice::query()
            ->where(function ($q) use ($serialNumber) {
                $q->where('serial_number', $serialNumber)
                    ->orWhere('device_identifier', $serialNumber);
            })
            ->where('brand', 'zkteco')
            ->first();

        if (! $device) {
            return response('UNKNOWN DEVICE', 404)
                ->header('Content-Type', 'text/plain');
        }

        $device->update(['last_heartbeat_at' => now()]);

        // Return OK (no pending commands). Command queue can be implemented later.
        return response('OK', 200)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * ZKTeco ADMS — device acknowledges a command.
     * POST /api/crm/attendance/iclock/devicecmd
     */
    public function iclockDeviceCmd(Request $request): Response
    {
        return response('OK', 200)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * Respond to ZKTeco ADMS GET handshake with device configuration.
     */
    private function iclockHandshake(CrmAttendanceDevice $device, Request $request): Response
    {
        $pushInterval = $device->push_interval ?? 30;
        $timezone = $device->timezone ?? config('app.timezone_offset', '2');

        $options = implode("\n", [
            'GET OPTION FROM: ' . ($device->serial_number ?? $device->device_identifier),
            'ATTLOGStamp=None',
            'OPERLOGStamp=None',
            'ATTPHOTOStamp=None',
            'ErrorDelay=60',
            'Delay=' . $pushInterval,
            'TransTimes=00:00;14:05',
            'TransInterval=1',
            'TransFlag=TransData AttLog OpLog',
            'TimeZone=' . $timezone,
            'Realtime=1',
            'Encrypt=None',
            'ServerVer=2.4.1',
        ]);

        if ($device->firmware_version === null) {
            $firmwareVersion = $request->query('pushver');

            if ($firmwareVersion) {
                $device->update(['firmware_version' => $firmwareVersion]);
            }
        }

        return response($options, 200)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * Parse ZKTeco ADMS POST body (tab-delimited attendance rows).
     * Format: PIN \t Timestamp \t Status \t Verify \t WorkCode \t Reserved1 \t Reserved2 \t Reserved3
     */
    private function iclockReceiveData(CrmAttendanceDevice $device, Request $request): Response
    {
        $table = $request->query('table', 'ATTLOG');

        if ($table !== 'ATTLOG') {
            return response('OK', 200)
                ->header('Content-Type', 'text/plain');
        }

        $body = $request->getContent();
        $lines = array_filter(explode("\n", $body), fn ($line) => trim($line) !== '');

        foreach ($lines as $line) {
            ProcessBiometricEventJob::dispatch($device, [
                'raw_line' => trim($line),
                'device_id' => $device->device_identifier,
            ]);
        }

        return response('OK', 200)
            ->header('Content-Type', 'text/plain');
    }
}
