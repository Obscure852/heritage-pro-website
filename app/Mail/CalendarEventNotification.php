<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Lms\CalendarEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CalendarEventNotification extends Mailable {
    use Queueable, SerializesModels;

    public function __construct(
        public CalendarEvent $event,
        public array $schoolDetails = []
    ) {}

    public function envelope(): Envelope {
        return new Envelope(
            subject: 'New Calendar Event: ' . $this->event->title,
        );
    }

    public function content(): Content {
        return new Content(
            view: 'emails.lms.calendar-event',
        );
    }

    public function build(): static {
        return $this->subject('New Calendar Event: ' . $this->event->title)
            ->view('emails.lms.calendar-event')
            ->with([
                'event' => $this->event,
                'schoolDetails' => $this->schoolDetails,
            ]);
    }
}
