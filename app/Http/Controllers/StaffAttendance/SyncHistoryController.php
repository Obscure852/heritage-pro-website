<?php

namespace App\Http\Controllers\StaffAttendance;

use App\Http\Controllers\Controller;
use App\Models\StaffAttendance\AttendanceDevice;
use App\Models\StaffAttendance\AttendanceSyncLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller for viewing biometric device synchronization history.
 *
 * Provides administrators with tools to monitor sync operations, identify failures,
 * and troubleshoot device connectivity issues by viewing the last 30 days of sync history.
 */
class SyncHistoryController extends Controller
{
    /**
     * Display the sync history index with filtering capabilities.
     *
     * Shows sync logs for the last 30 days with filters for device and status.
     * Includes stats in the header for total syncs, successful, failed, and records processed.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        // Build query with filters
        $query = AttendanceSyncLog::with('device')
            ->recent(30)
            ->orderBy('created_at', 'desc');

        // Filter by device if provided
        if ($deviceId = $request->input('device')) {
            $query->forDevice($deviceId);
        }

        // Filter by status if provided
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $syncLogs = $query->paginate(50);

        // Calculate stats for header (always for last 30 days, ignoring filters)
        $stats = [
            'total_syncs' => AttendanceSyncLog::recent(30)->count(),
            'successful' => AttendanceSyncLog::recent(30)->where('status', AttendanceSyncLog::STATUS_SUCCESS)->count(),
            'failed' => AttendanceSyncLog::recent(30)->where('status', AttendanceSyncLog::STATUS_FAILED)->count(),
            'total_records' => AttendanceSyncLog::recent(30)->sum('records_processed'),
        ];

        // Get devices for filter dropdown
        $devices = AttendanceDevice::orderBy('name')->get();

        // Get status options for filter dropdown
        $statuses = [
            AttendanceSyncLog::STATUS_SUCCESS => 'Success',
            AttendanceSyncLog::STATUS_FAILED => 'Failed',
            AttendanceSyncLog::STATUS_PARTIAL => 'Partial',
            AttendanceSyncLog::STATUS_RUNNING => 'Running',
        ];

        return view('staff-attendance.sync-history.index', compact(
            'syncLogs',
            'stats',
            'devices',
            'statuses'
        ));
    }
}
