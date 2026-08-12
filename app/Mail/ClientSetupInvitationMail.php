<?php

namespace App\Mail;

use App\Models\CrmClientSetupInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClientSetupInvitationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public CrmClientSetupInvitation $invitation,
        public string $setupUrl
    ) {
    }

    public function build(): self
    {
        return $this
            ->subject('Heritage Pro client setup invitation')
            ->view('emails.client-setup-invitation');
    }
}
