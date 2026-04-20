<?php

namespace App\Events\Leave;

use App\Models\Leave\LeaveRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a leave request is rejected.
 *
 * This event triggers notifications to:
 * - Staff member (rejection notification - NOTF-04)
 */
class LeaveRequestRejected {
    use Dispatchable, SerializesModels;

    /**
     * The leave request that was rejected.
     *
     * @var LeaveRequest
     */
    public LeaveRequest $leaveRequest;

    /**
     * Create a new event instance.
     *
     * @param LeaveRequest $leaveRequest
     */
    public function __construct(LeaveRequest $leaveRequest) {
        $this->leaveRequest = $leaveRequest;
    }
}
