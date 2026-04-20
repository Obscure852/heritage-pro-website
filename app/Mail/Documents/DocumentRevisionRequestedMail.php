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
 * Mailable sent to the document author when revisions are requested.
 */
class DocumentRevisionRequestedMail extends Mailable implements ShouldQueue {
    use Queueable, SerializesModels;

    /** @var string Document title */
    public string $documentTitle;

    /** @var string Name of the author receiving this email */
    public string $recipientName;

    /** @var string Name of the reviewer requesting revisions */
    public string $reviewerName;

    /** @var string Revision comments from the reviewer */
    public string $comments;

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
        string $comments,
    ) {
        $this->documentTitle = $document->title;
        $this->recipientName = $document->owner->name ?? 'Staff Member';
        $this->reviewerName = $reviewer->name ?? 'Reviewer';
        $this->comments = $comments;
        $this->documentUrl = route('documents.show', $document);
        $this->school = $this->getSchoolDetails();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope {
        return new Envelope(
            subject: "Revision Requested: {$this->documentTitle}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content {
        return new Content(
            view: 'emails.documents.revision-requested',
        );
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        return $this->subject("Revision Requested: {$this->documentTitle}")
                    ->view('emails.documents.revision-requested');
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
