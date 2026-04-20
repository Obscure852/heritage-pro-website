<?php

namespace App\Mail\Documents;

use App\Models\DocumentApproval;
use App\Models\SchoolSetup;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable sent to reviewers when a review deadline is approaching.
 */
class ReviewDeadlineReminderMail extends Mailable implements ShouldQueue {
    use Queueable, SerializesModels;

    /** @var string Document title */
    public string $documentTitle;

    /** @var string Name of the reviewer receiving this email */
    public string $recipientName;

    /** @var string Name of the user who submitted the document */
    public string $submitterName;

    /** @var string Due date formatted */
    public string $dueDate;

    /** @var int Days remaining until deadline */
    public int $daysRemaining;

    /** @var string URL to view the document */
    public string $documentUrl;

    /** @var array School branding details */
    public array $school;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public DocumentApproval $approval,
    ) {
        $approval->loadMissing(['document', 'reviewer', 'submittedBy']);

        $this->documentTitle = $approval->document->title ?? 'Document';
        $this->recipientName = $approval->reviewer->name ?? 'Reviewer';
        $this->submitterName = $approval->submittedBy->name ?? 'Staff Member';
        $this->dueDate = $approval->due_date?->format('d M Y') ?? 'N/A';
        $this->daysRemaining = $approval->due_date ? max(0, (int) now()->diffInDays($approval->due_date, false)) : 0;
        $this->documentUrl = route('documents.show', $approval->document);
        $this->school = $this->getSchoolDetails();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope {
        return new Envelope(
            subject: "Review Deadline Approaching: {$this->documentTitle}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content {
        return new Content(
            view: 'emails.documents.deadline-reminder',
        );
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        return $this->subject("Review Deadline Approaching: {$this->documentTitle}")
                    ->view('emails.documents.deadline-reminder');
    }

    /**
     * Get school details for email branding.
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
