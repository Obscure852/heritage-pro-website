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
 * Mailable for staff reminder before leave starts (NOTF-05).
 *
 * Sent to staff as a reminder that their approved leave is about to start.
 */
class LeaveReminder extends Mailable implements ShouldQueue {
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

    /** @var float Total days of leave */
    public float $totalDays;

    /** @var int Days until leave starts */
    public int $daysUntilStart;

    /** @var array School details for branding */
    public array $school;

    /**
     * Create a new message instance.
     *
     * @param LeaveRequest $request
     * @param int $daysUntilStart
     */
    public function __construct(LeaveRequest $request, int $daysUntilStart) {
        $this->request = $request;
        $this->userName = $request->user->name ?? 'Staff Member';
        $this->leaveType = $request->leaveType->name ?? 'Leave';
        $this->startDate = $request->start_date->format('d M Y');
        $this->endDate = $request->end_date->format('d M Y');
        $this->totalDays = (float) $request->total_days;
        $this->daysUntilStart = $daysUntilStart;
        $this->school = $this->getSchoolDetails();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope {
        $dayWord = $this->daysUntilStart === 1 ? 'day' : 'days';

        return new Envelope(
            subject: "Leave Reminder - Your {$this->leaveType} starts in {$this->daysUntilStart} {$dayWord}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content {
        return new Content(
            view: 'emails.leave.reminder',
        );
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        $dayWord = $this->daysUntilStart === 1 ? 'day' : 'days';

        return $this->subject("Leave Reminder - Your {$this->leaveType} starts in {$this->daysUntilStart} {$dayWord}")
                    ->view('emails.leave.reminder');
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
