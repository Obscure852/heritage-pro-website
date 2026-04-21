<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookDemoInquiry extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public array $submission)
    {
    }

    public function build(): self
    {
        return $this
            ->subject('New Heritage Pro demo request from ' . $this->submission['full_name'])
            ->replyTo($this->submission['work_email'], $this->submission['full_name'])
            ->view('emails.book-demo-inquiry');
    }
}
