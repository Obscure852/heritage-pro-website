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
 * Mailable for staff confirmation on leave request submission (NOTF-01).
 *
 * Sent to staff when their leave request has been submitted and is awaiting approval.
 */
class LeaveRequestSubmitted extends Mailable implements ShouldQueue {
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

    /** @var string|null Reason for leave */
    public ?string $reason;

    /** @var string Submitted at timestamp formatted */
    public string $submittedAt;

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
        $this->reason = $request->reason;
        $this->submittedAt = $request->submitted_at?->format('d M Y H:i') ?? now()->format('d M Y H:i');
        $this->school = $this->getSchoolDetails();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope {
        return new Envelope(
            subject: "Leave Request Submitted - {$this->leaveType} ({$this->totalDays} days)",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content {
        return new Content(
            view: 'emails.leave.submitted',
        );
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        return $this->subject("Leave Request Submitted - {$this->leaveType} ({$this->totalDays} days)")
                    ->view('emails.leave.submitted');
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
