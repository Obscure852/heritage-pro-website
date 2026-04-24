<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\CrmCalendarEventAttendee;
use Illuminate\Contracts\View\View;

class CalendarAvailabilityController extends Controller
{
    public function __invoke(CrmCalendarEventAttendee $crmCalendarEventAttendee, string $response): View
    {
        abort_unless(array_key_exists($response, config('heritage_crm.calendar_attendee_response_statuses', [])), 404);

        $crmCalendarEventAttendee->update([
            'response_status' => $response,
        ]);

        $crmCalendarEventAttendee->load([
            'event.calendar',
            'event.owner',
            'event.createdBy',
        ]);

        return view('crm.calendar.availability-response', [
            'attendee' => $crmCalendarEventAttendee,
            'event' => $crmCalendarEventAttendee->event,
            'responseLabel' => config('heritage_crm.calendar_attendee_response_statuses.' . $response),
        ]);
    }
}
