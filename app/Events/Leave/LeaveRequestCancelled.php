<?php

namespace App\Events\Leave;

use App\Models\Leave\LeaveRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a leave request is cancelled.
 *
 * This event triggers:
 * - Removal of leave-synced attendance records (LVE-05)
 */
class LeaveRequestCancelled {
    use Dispatchable, SerializesModels;

    /**
     * The leave request that was cancelled.
     *
     * @var LeaveRequest
     */
    public LeaveRequest $leaveRequest;

    /**
     * The previous status before cancellation.
     *
     * @var string
     */
    public string $previousStatus;

    /**
     * Create a new event instance.
     *
     * @param LeaveRequest $leaveRequest
     * @param string $previousStatus
     */
    public function __construct(LeaveRequest $leaveRequest, string $previousStatus) {
        $this->leaveRequest = $leaveRequest;
        $this->previousStatus = $previousStatus;
    }
}
