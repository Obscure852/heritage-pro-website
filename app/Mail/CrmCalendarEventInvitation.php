<?php

namespace App\Mail;

use App\Models\CrmCalendarEvent;
use App\Models\CrmCalendarEventAttendee;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class CrmCalendarEventInvitation extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public CrmCalendarEvent $event,
        public CrmCalendarEventAttendee $attendee
    ) {
    }

    public function build(): self
    {
        $this->event->loadMissing(['calendar', 'owner', 'createdBy']);

        return $this
            ->subject('Calendar invitation: ' . $this->event->title)
            ->view('emails.crm.calendar-event-invitation', [
                'event' => $this->event,
                'attendee' => $this->attendee,
                'responseOptions' => $this->responseOptions(),
                'organizerName' => $this->event->createdBy?->name ?: $this->event->owner?->name,
            ]);
    }

    private function responseOptions(): array
    {
        $expiresAt = $this->event->ends_at
            ? $this->event->ends_at->copy()->addDays(14)
            : now()->addDays(30);

        if ($expiresAt->isPast()) {
            $expiresAt = now()->addDays(14);
        }

        return collect(config('heritage_crm.calendar_attendee_response_statuses', []))
            ->map(fn (string $label, string $response): array => [
                'key' => $response,
                'label' => $label,
                'url' => URL::temporarySignedRoute(
                    'crm.calendar.attendees.availability',
                    $expiresAt,
                    [
                        'crmCalendarEventAttendee' => $this->attendee->id,
                        'response' => $response,
                    ]
                ),
            ])
            ->values()
            ->all();
    }
}
