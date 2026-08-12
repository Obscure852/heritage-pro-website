<?php

namespace App\Mail;

use App\Models\CrmClientSetupNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClientSetupNotificationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public CrmClientSetupNotification $notification)
    {
    }

    public function build(): self
    {
        return $this
            ->subject($this->notification->subject)
            ->view('emails.client-setup-notification');
    }
}
