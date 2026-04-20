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
 * Mailable sent to the document author when their document is approved.
 */
class DocumentApprovedMail extends Mailable implements ShouldQueue {
    use Queueable, SerializesModels;

    /** @var string Document title */
    public string $documentTitle;

    /** @var string Name of the author receiving this email */
    public string $recipientName;

    /** @var string Name of the reviewer who approved */
    public string $reviewerName;

    /** @var string URL to view the document */
    public string $documentUrl;

    /** @var array School branding details */
    public array $school;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Document $document,
        public User $reviewer,
    ) {
        $this->documentTitle = $document->title;
        $this->recipientName = $document->owner->name ?? 'Staff Member';
        $this->reviewerName = $reviewer->name ?? 'Reviewer';
        $this->documentUrl = route('documents.show', $document);
        $this->school = $this->getSchoolDetails();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope {
        return new Envelope(
            subject: "Document Approved: {$this->documentTitle}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content {
        return new Content(
            view: 'emails.documents.approved',
        );
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        return $this->subject("Document Approved: {$this->documentTitle}")
                    ->view('emails.documents.approved');
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
