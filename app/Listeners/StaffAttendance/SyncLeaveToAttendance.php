<?php

namespace App\Listeners\StaffAttendance;

use App\Events\Leave\LeaveRequestApproved;
use App\Events\Leave\LeaveRequestCancelled;
use App\Models\Leave\LeaveRequest;
use App\Services\StaffAttendance\LeaveAttendanceCorrelationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * Listener that syncs approved leave to attendance records.
 *
 * When a leave request is approved, this listener creates corresponding
 * attendance records marked as "On Leave" for each applicable day.
 *
 * When a leave request is cancelled (from approved status), this listener
 * removes the corresponding attendance records.
 */
class SyncLeaveToAttendance implements ShouldQueue {
    /**
     * @var LeaveAttendanceCorrelationService
     */
    protected LeaveAttendanceCorrelationService $correlationService;

    /**
     * Create the listener.
     *
     * @param LeaveAttendanceCorrelationService $correlationService
     */
    public function __construct(LeaveAttendanceCorrelationService $correlationService) {
        $this->correlationService = $correlationService;
    }

    /**
     * Handle leave approval or cancellation events.
     *
     * @param LeaveRequestApproved|LeaveRequestCancelled $event
     * @return void
     */
    public function handle($event): void {
        try {
            if ($event instanceof LeaveRequestApproved) {
                $this->correlationService->syncLeaveToAttendance($event->leaveRequest);
                Log::info('Synced leave to attendance', [
                    'request_id' => $event->leaveRequest->id,
                ]);
            } elseif ($event instanceof LeaveRequestCancelled) {
                if ($event->previousStatus === LeaveRequest::STATUS_APPROVED) {
                    $deleted = $this->correlationService->removeLeaveAttendanceRecords($event->leaveRequest);
                    Log::info('Removed leave attendance records on cancellation', [
                        'request_id' => $event->leaveRequest->id,
                        'records_deleted' => $deleted,
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to sync leave to attendance', [
                'request_id' => $event->leaveRequest->id,
                'event_type' => get_class($event),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
