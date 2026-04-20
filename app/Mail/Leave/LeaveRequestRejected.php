<?php

namespace App\Mail\Leave;

use App\Models\Leave\LeaveRequest;
use App\Models\SchoolSetup;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable for staff notification on leave request rejection (NOTF-04).
 *
 * Sent to staff when their leave request has been rejected by their manager.
 */
class LeaveRequestRejected extends Mailable implements ShouldQueue {
    use Queueable, SerializesModels;

    /** @var LeaveRequest The leave request */
    public LeaveRequest $request;

    /** @var string Staff member's name */
    public string $userName;

    /** @var string Leave type name */
    public string $leaveType;

    /** @var string Start date formatted */
    public string $startDate;

    /** @var string End date formatted */
    public string $endDate;

    /** @var float Total days requested */
    public float $totalDays;

    /** @var string Approver's name (who rejected) */
    public string $approverName;

    /** @var string Rejection reason */
    public string $rejectionReason;

    /** @var array School details for branding */
    public array $school;

    /**
     * Create a new message instance.
     *
     * @param LeaveRequest $request
     */
    public function __construct(LeaveRequest $request) {
        $this->request = $request;
        $this->userName = $request->user->name ?? 'Staff Member';
        $this->leaveType = $request->leaveType->name ?? 'Leave';
        $this->startDate = $request->start_date->format('d M Y');
        $this->endDate = $request->end_date->format('d M Y');
        $this->totalDays = (float) $request->total_days;
        $this->approverName = $request->approver->name ?? 'Manager';
        $this->rejectionReason = $request->approver_comments ?? 'No reason provided';
        $this->school = $this->getSchoolDetails();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope {
        return new Envelope(
            subject: "Leave Request Rejected - {$this->leaveType}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content {
        return new Content(
            view: 'emails.leave.rejected',
        );
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        return $this->subject("Leave Request Rejected - {$this->leaveType}")
                    ->view('emails.leave.rejected');
    }

    /**
     * Get school details for email branding.
     *
     * @return array
     */
    protected function getSchoolDetails(): array {
        $setup = SchoolSetup::latest()->first();

        return [
            'name' => $setup->school_name ?? 'Heritage Pro',
            'logo' => $setup->logo_path ?? null,
            'address' => $setup->physical_address ?? '',
            'email' => $setup->email_address ?? 'support@heritagepro.com',
            'telephone' => $setup->telephone ?? '',
        ];
    }
}
