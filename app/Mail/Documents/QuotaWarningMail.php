<?php

namespace App\Mail\Documents;

use App\Models\SchoolSetup;
use App\Models\User;
use App\Models\UserDocumentQuota;
use App\Services\Documents\QuotaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable sent when a user's storage quota reaches the warning threshold (80%).
 */
class QuotaWarningMail extends Mailable implements ShouldQueue {
    use Queueable, SerializesModels;

    /** @var string Recipient's name */
    public string $recipientName;

    /** @var float Usage percentage */
    public float $usagePercent;

    /** @var string Formatted used bytes */
    public string $usedFormatted;

    /** @var string Formatted quota bytes */
    public string $quotaFormatted;

    /** @var string URL to manage documents */
    public string $manageUrl;

    /** @var array School branding details */
    public array $school;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $user,
        public UserDocumentQuota $quota,
    ) {
        $quotaService = app(QuotaService::class);

        $this->recipientName = $user->name ?? 'Staff Member';
        $this->usagePercent = $quota->usage_percent;
        $this->usedFormatted = $quotaService->formatBytes($quota->used_bytes);
        $this->quotaFormatted = $quotaService->formatBytes($quota->quota_bytes);
        $this->manageUrl = route('documents.index');
        $this->school = $this->getSchoolDetails();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope {
        return new Envelope(
            subject: "Storage Quota Warning - " . number_format($this->usagePercent, 0) . "% Used",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content {
        return new Content(
            view: 'emails.documents.quota-warning',
        );
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        return $this->subject("Storage Quota Warning - " . number_format($this->usagePercent, 0) . "% Used")
                    ->view('emails.documents.quota-warning');
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
