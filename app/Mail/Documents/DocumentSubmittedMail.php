<?php

namespace App\Mail\Documents;

use App\Models\Document;
use App\Models\SchoolSetup;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable sent to reviewers when a document is submitted for review.
 */
class DocumentSubmittedMail extends Mailable implements ShouldQueue {
    use Queueable, SerializesModels;

    /** @var string Document title */
    public string $documentTitle;

    /** @var string Name of the user who submitted */
    public string $submitterName;

    /** @var string|null Submission notes from the author */
    public ?string $submissionNotes;

    /** @var string URL to view the document */
    public string $documentUrl;

    /** @var array School branding details */
    public array $school;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Document $document,
        public User $submitter,
    ) {
        $this->documentTitle = $document->title;
        $this->submitterName = $submitter->name ?? 'Staff Member';
        $this->submissionNotes = null;
        $this->documentUrl = route('documents.show', $document);
        $this->school = $this->getSchoolDetails();

        // Check if the document has a pending approval with notes
        $latestApproval = $document->approvals()->latest()->first();
        if ($latestApproval) {
            $this->submissionNotes = $latestApproval->submission_notes;
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope {
        return new Envelope(
            subject: "Document Submitted for Review: {$this->documentTitle}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content {
        return new Content(
            view: 'emails.documents.submitted',
        );
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        return $this->subject("Document Submitted for Review: {$this->documentTitle}")
                    ->view('emails.documents.submitted');
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
