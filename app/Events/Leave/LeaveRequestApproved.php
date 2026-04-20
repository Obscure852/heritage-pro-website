<?php

namespace App\Events\Leave;

use App\Models\Leave\LeaveRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a leave request is approved.
 *
 * This event triggers notifications to:
 * - Staff member (approval notification - NOTF-03)
 */
class LeaveRequestApproved {
    use Dispatchable, SerializesModels;

    /**
     * The leave request that was approved.
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
