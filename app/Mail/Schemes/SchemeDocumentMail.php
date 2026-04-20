<?php

namespace App\Mail\Schemes;

use App\Models\SchoolSetup;
use App\Models\Schemes\SchemeOfWork;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SchemeDocumentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public SchemeOfWork $scheme,
        public User $sender,
        public string $mailSubject,
        public ?string $messageNote = null,
        public ?SchoolSetup $schoolSetup = null,
    ) {
    }

    public function build(): self
    {
        return $this->subject($this->mailSubject)
            ->view('emails.schemes.document')
            ->with([
                'scheme' => $this->scheme,
                'sender' => $this->sender,
                'messageNote' => $this->messageNote,
                'schoolSetup' => $this->schoolSetup,
                'documentUrl' => route('schemes.document', $this->scheme),
            ]);
    }
}
