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

class LegalHoldRemovedMail extends Mailable implements ShouldQueue {
    use Queueable, SerializesModels;

    public string $recipientName;
    public string $documentTitle;
    public string $removedByName;
    public string $documentUrl;
    public array $school;

    public function __construct(
        public Document $document,
        public User $removedBy,
    ) {
        $this->recipientName = $document->owner->name ?? 'Staff Member';
        $this->documentTitle = $document->title;
        $this->removedByName = $removedBy->name;
        $this->documentUrl = route('documents.show', $document);
        $this->school = $this->getSchoolDetails();
    }

    public function envelope(): Envelope {
        return new Envelope(
            subject: "Legal Hold Removed: {$this->documentTitle}",
        );
    }

    public function content(): Content {
        return new Content(
            view: 'emails.documents.legal-hold-removed',
        );
    }

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
