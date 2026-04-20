<?php

namespace App\Events\Leave;

use App\Models\Leave\LeaveRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a leave request is submitted.
 *
 * This event triggers notifications to:
 * - Staff member (submission confirmation - NOTF-01)
 * - Manager (new pending request - NOTF-02)
 */
class LeaveRequestSubmitted {
    use Dispatchable, SerializesModels;

    /**
     * The leave request that was submitted.
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
