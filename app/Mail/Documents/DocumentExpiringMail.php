<?php

namespace App\Mail\Documents;

use App\Models\Document;
use App\Models\SchoolSetup;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentExpiringMail extends Mailable implements ShouldQueue {
    use Queueable, SerializesModels;

    public string $recipientName;
    public string $documentTitle;
    public string $expiryDate;
    public int $daysRemaining;
    public string $documentUrl;
    public array $school;

    public function __construct(
        public Document $document,
    ) {
        $this->recipientName = $document->owner->name ?? 'Staff Member';
        $this->documentTitle = $document->title;
        $this->expiryDate = $document->expiry_date->format('d M Y');
        $this->daysRemaining = (int) now()->diffInDays($document->expiry_date, false);
        $this->documentUrl = route('documents.show', $document);
        $this->school = $this->getSchoolDetails();
    }

    public function envelope(): Envelope {
        return new Envelope(
            subject: "Document Expiring: {$this->documentTitle}",
        );
    }

    public function content(): Content {
        return new Content(
            view: 'emails.documents.expiring',
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
