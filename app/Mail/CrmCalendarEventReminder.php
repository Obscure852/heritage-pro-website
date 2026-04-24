<?php

namespace App\Mail;

use App\Models\CrmCalendarEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CrmCalendarEventReminder extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public CrmCalendarEvent $event,
        public int $reminderMinutes,
        public ?string $recipientName = null
    ) {
    }

    public function build(): self
    {
        $this->event->loadMissing(['calendar', 'owner', 'createdBy']);
        $reminderLabel = $this->reminderLabel();

        return $this
            ->subject('Reminder: ' . $this->event->title . ' starts ' . $reminderLabel)
            ->view('emails.crm.calendar-event-reminder', [
                'event' => $this->event,
                'recipientName' => $this->recipientName,
                'reminderLabel' => $reminderLabel,
                'organizerName' => $this->event->createdBy?->name ?: $this->event->owner?->name,
            ]);
    }

    private function reminderLabel(): string
    {
        if ($this->reminderMinutes <= 0) {
            return 'now';
        }

        if ($this->reminderMinutes < 60) {
            return 'in ' . $this->reminderMinutes . ' minute' . ($this->reminderMinutes === 1 ? '' : 's');
        }

        if ($this->reminderMinutes % 1440 === 0) {
            $days = (int) ($this->reminderMinutes / 1440);

            return 'in ' . $days . ' day' . ($days === 1 ? '' : 's');
        }

        if ($this->reminderMinutes % 60 === 0) {
            $hours = (int) ($this->reminderMinutes / 60);

            return 'in ' . $hours . ' hour' . ($hours === 1 ? '' : 's');
        }

        return 'in ' . $this->reminderMinutes . ' minutes';
    }
}
