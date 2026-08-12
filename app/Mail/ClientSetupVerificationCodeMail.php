<?php

namespace App\Mail;

use App\Models\CrmClientSetupInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClientSetupVerificationCodeMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public CrmClientSetupInvitation $invitation,
        public string $code,
        public int $expiresInMinutes
    ) {
    }

    public function build(): self
    {
        return $this
            ->subject('Your Heritage Pro setup verification code')
            ->view('emails.client-setup-verification-code');
    }
}
