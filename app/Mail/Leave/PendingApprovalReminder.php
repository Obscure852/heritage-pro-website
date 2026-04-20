<?php

namespace App\Mail\Leave;

use App\Models\SchoolSetup;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

/**
 * Mailable for manager reminder on pending requests (NOTF-06).
 *
 * Sent to managers when they have pending leave requests awaiting their approval.
 */
class PendingApprovalReminder extends Mailable implements ShouldQueue {
    use Queueable, SerializesModels;

    /** @var User The manager receiving the reminder */
    public User $manager;

    /** @var Collection Pending leave requests */
    public Collection $pendingRequests;

    /** @var int Count of pending requests */
    public int $pendingCount;

    /** @var int Hours since the oldest request was submitted */
    public int $oldestRequestHours;

    /** @var string Manager's name */
    public string $managerName;

    /** @var array School details for branding */
    public array $school;

    /**
     * Create a new message instance.
     *
     * @param User $manager
     * @param Collection $pendingRequests
     */
    public function __construct(User $manager, Collection $pendingRequests) {
        $this->manager = $manager;
        $this->pendingRequests = $pendingRequests;
        $this->managerName = $manager->name ?? 'Manager';
        $this->pendingCount = $pendingRequests->count();
        $this->oldestRequestHours = $this->calculateOldestRequestHours($pendingRequests);
        $this->school = $this->getSchoolDetails();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope {
        $requestWord = $this->pendingCount === 1 ? 'request' : 'requests';

        return new Envelope(
            subject: "Pending Leave Requests - {$this->pendingCount} {$requestWord} awaiting your approval",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content {
        return new Content(
            view: 'emails.leave.pending-reminder',
        );
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        $requestWord = $this->pendingCount === 1 ? 'request' : 'requests';

        return $this->subject("Pending Leave Requests - {$this->pendingCount} {$requestWord} awaiting your approval")
                    ->view('emails.leave.pending-reminder');
    }

    /**
     * Calculate hours since the oldest pending request was submitted.
     *
     * @param Collection $pendingRequests
     * @return int
     */
    protected function calculateOldestRequestHours(Collection $pendingRequests): int {
        if ($pendingRequests->isEmpty()) {
            return 0;
        }

        $oldestRequest = $pendingRequests->sortBy('submitted_at')->first();
        $submittedAt = $oldestRequest->submitted_at ?? $oldestRequest->created_at;

        if (!$submittedAt) {
            return 0;
        }

        return (int) Carbon::parse($submittedAt)->diffInHours(now());
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
