<?php

namespace App\Http\Controllers\Crm;

use App\Http\Requests\Crm\CrmCalendarEventUpsertRequest;
use App\Http\Requests\Crm\CrmCalendarStoreRequest;
use App\Models\Contact;
use App\Models\CrmCalendar;
use App\Models\CrmCalendarEvent;
use App\Models\CrmRequest;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use App\Services\Crm\CrmCalendarService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CalendarController extends CrmController
{
    public function __construct(private readonly CrmCalendarService $calendarService)
    {
    }

    public function index(Request $request): View
    {
        $crmUser = $this->crmUser();
        $visibleCalendars = $this->calendarService->visibleCalendarsFor($crmUser);
        $selectedDate = $request->filled('date')
            ? Carbon::parse((string) $request->query('date'))->startOfDay()
            : now()->startOfDay();

        $selectedCalendarIds = collect((array) $request->query('calendar_ids', $visibleCalendars->pluck('id')->all()))
            ->map(fn ($calendarId) => (int) $calendarId)
            ->filter(fn ($calendarId) => $visibleCalendars->pluck('id')->contains($calendarId))
            ->values()
            ->all();

        if ($selectedCalendarIds === []) {
            $selectedCalendarIds = $visibleCalendars->pluck('id')->map(fn ($calendarId) => (int) $calendarId)->all();
        }

        $upcomingEvents = $this->calendarService->visibleEventsForRange(
            $crmUser,
            now()->copy()->startOfDay(),
            now()->copy()->addDays((int) config('heritage_crm.calendar.agenda_days', 14))->endOfDay(),
            $selectedCalendarIds
        )->take(8)
            ->map(function (CrmCalendarEvent $event) use ($crmUser) {
                $canViewSensitive = $this->calendarService->canViewEventSensitiveDetails($crmUser, $event);

                return [
                    'title' => $canViewSensitive
                        ? $event->title
                        : ($event->visibility === 'busy_only' ? 'Busy' : 'Private event'),
                    'starts_at' => $event->starts_at,
                    'all_day' => $event->all_day,
                    'calendar_name' => $event->calendar?->name,
                    'owner_name' => $event->owner?->name ?: $event->calendar?->owner?->name ?: 'Unassigned owner',
                    'location' => $canViewSensitive ? $event->location : null,
                ];
            });

        $todayStart = now()->copy()->startOfDay();
        $todayEnd = now()->copy()->endOfDay();
        $weekStart = $selectedDate->copy()->startOfWeek();
        $weekEnd = $selectedDate->copy()->endOfWeek();

        $todayCount = $this->calendarService->visibleEventsForRange($crmUser, $todayStart, $todayEnd, $selectedCalendarIds)->count();
        $weekCount = $this->calendarService->visibleEventsForRange($crmUser, $weekStart, $weekEnd, $selectedCalendarIds)->count();
        $completedThisWeek = $this->calendarService->visibleEventsForRange($crmUser, $weekStart, $weekEnd, $selectedCalendarIds)
            ->where('status', 'completed')
            ->count();
        $overdueCount = $this->calendarService->visibleEventsForRange(
            $crmUser,
            now()->copy()->subMonths(2)->startOfDay(),
            now()->copy()->subMinute(),
            $selectedCalendarIds
        )->where('status', 'scheduled')->count();

        return view('crm.calendar.index', [
            'visibleCalendars' => $visibleCalendars,
            'selectedCalendarIds' => $selectedCalendarIds,
            'selectedDate' => $selectedDate,
            'defaultView' => (string) $request->query('view', 'timeGridWeek'),
            'miniMonthWeeks' => $this->miniMonthWeeks($selectedDate),
            'upcomingEvents' => $upcomingEvents,
            'metrics' => [
                ['label' => 'Due today', 'value' => $todayCount],
                ['label' => 'This week', 'value' => $weekCount],
                ['label' => 'Completed', 'value' => $completedThisWeek],
                ['label' => 'Overdue', 'value' => $overdueCount],
            ],
            'owners' => $this->owners(),
            'leads' => $this->scopeOwned(Lead::query()->select(['id', 'company_name', 'owner_id'])->orderBy('company_name'))->get(),
            'customers' => $this->scopeOwned(Customer::query()->select(['id', 'company_name', 'owner_id'])->orderBy('company_name'))->get(),
            'contacts' => $this->scopeOwned(
                Contact::query()->select(['id', 'name', 'owner_id', 'lead_id', 'customer_id'])->orderBy('name')
            )->get(),
            'crmRequests' => $this->scopeOwned(
                CrmRequest::query()->select(['id', 'title', 'owner_id', 'lead_id', 'customer_id'])->latest()->limit(50)
            )->get(),
            'calendarStatuses' => config('heritage_crm.calendar_event_statuses'),
            'calendarVisibility' => config('heritage_crm.calendar_event_visibility'),
            'calendarReminderMinutes' => config('heritage_crm.calendar_reminder_minutes'),
            'calendarColors' => config('heritage_crm.calendar_default_colors'),
            'canCreateSharedCalendars' => $crmUser->canManageOperationalRecords() || $crmUser->isFinance(),
            'sharedCalendarMembers' => User::query()
                ->where('active', true)
                ->whereIn('role', array_keys(config('heritage_crm.roles', [])))
                ->orderBy('email')
                ->get(['id', 'firstname', 'lastname', 'username', 'email', 'role', 'active']),
        ]);
    }

    public function feed(Request $request): JsonResponse
    {
        $rangeStart = $request->filled('start')
            ? Carbon::parse((string) $request->query('start'))
            : now()->copy()->startOfMonth()->startOfWeek();
        $rangeEnd = $request->filled('end')
            ? Carbon::parse((string) $request->query('end'))
            : now()->copy()->endOfMonth()->endOfWeek();

        $events = $this->calendarService->visibleEventsForRange(
            $this->crmUser(),
            $rangeStart,
            $rangeEnd,
            (array) $request->query('calendar_ids', [])
        );

        return response()->json(
            $events->map(fn (CrmCalendarEvent $event) => $this->eventPayload($event))->values()->all()
        );
    }

    public function storeCalendar(CrmCalendarStoreRequest $request): RedirectResponse
    {
        abort_unless($this->crmUser()->canManageOperationalRecords() || $this->crmUser()->isFinance(), 403);

        $this->calendarService->createSharedCalendar(
            $this->crmUser(),
            $request->validated(),
            $request->validated('member_user_ids', [])
        );

        return redirect()
            ->route('crm.calendar.index')
            ->with('crm_success', 'Shared calendar created successfully.');
    }

    public function store(CrmCalendarEventUpsertRequest $request): JsonResponse
    {
        $crmUser = $this->crmUser();
        $calendar = CrmCalendar::query()->with('memberships')->findOrFail((int) $request->validated('calendar_id'));

        abort_unless($this->calendarService->canEditCalendar($crmUser, $calendar), 403);

        $linkedRecords = $this->calendarService->resolveLinkedRecords($request->validated());
        $this->authorizeLinkedRecords(
            $linkedRecords['lead'],
            $linkedRecords['customer'],
            $linkedRecords['contact'],
            $linkedRecords['request']
        );

        $event = DB::transaction(function () use ($request, $crmUser) {
            $payload = $this->calendarService->normalizeEventPayload($crmUser, $request->validated());
            $payload['owner_id'] = $this->normalizeOwnerId($payload['owner_id']);
            $payload['created_by_id'] = $crmUser->id;
            $payload['updated_by_id'] = $crmUser->id;

            $event = CrmCalendarEvent::query()->create($payload);
            $contact = isset($payload['contact_id']) && $payload['contact_id']
                ? Contact::query()->find($payload['contact_id'])
                : null;

            $this->calendarService->syncAttendees(
                $event,
                $request->validated('attendee_user_ids', []),
                $contact
            );

            return $event;
        });

        $event->load([
            'calendar.owner:id,firstname,lastname,username,email,role',
            'owner:id,firstname,lastname,username,email,role',
            'lead:id,company_name',
            'customer:id,company_name',
            'contact:id,name,email,phone,lead_id,customer_id',
            'request:id,title,owner_id,lead_id,customer_id',
            'attendees.user:id,firstname,lastname,username,email,role',
            'attendees.contact:id,name,email',
        ]);

        return response()->json([
            'message' => 'Calendar event created successfully.',
            'event' => $this->eventPayload($event),
        ], 201);
    }

    public function update(CrmCalendarEventUpsertRequest $request, CrmCalendarEvent $crmCalendarEvent): JsonResponse
    {
        $crmCalendarEvent->loadMissing('calendar.memberships');
        abort_unless($this->calendarService->canEditEvent($this->crmUser(), $crmCalendarEvent), 403);

        $calendar = CrmCalendar::query()->with('memberships')->findOrFail((int) $request->validated('calendar_id'));
        abort_unless($this->calendarService->canEditCalendar($this->crmUser(), $calendar), 403);

        $linkedRecords = $this->calendarService->resolveLinkedRecords($request->validated());
        $this->authorizeLinkedRecords(
            $linkedRecords['lead'],
            $linkedRecords['customer'],
            $linkedRecords['contact'],
            $linkedRecords['request']
        );

        DB::transaction(function () use ($request, $crmCalendarEvent) {
            $payload = $this->calendarService->normalizeEventPayload($this->crmUser(), $request->validated());
            $payload['owner_id'] = $this->normalizeOwnerId($payload['owner_id']);
            $payload['updated_by_id'] = $this->crmUser()->id;

            $crmCalendarEvent->update($payload);

            $contact = isset($payload['contact_id']) && $payload['contact_id']
                ? Contact::query()->find($payload['contact_id'])
                : null;

            $this->calendarService->syncAttendees(
                $crmCalendarEvent,
                $request->validated('attendee_user_ids', []),
                $contact
            );
        });

        $crmCalendarEvent->refresh()->load([
            'calendar.owner:id,firstname,lastname,username,email,role',
            'owner:id,firstname,lastname,username,email,role',
            'lead:id,company_name',
            'customer:id,company_name',
            'contact:id,name,email,phone,lead_id,customer_id',
            'request:id,title,owner_id,lead_id,customer_id',
            'attendees.user:id,firstname,lastname,username,email,role',
            'attendees.contact:id,name,email',
        ]);

        return response()->json([
            'message' => 'Calendar event updated successfully.',
            'event' => $this->eventPayload($crmCalendarEvent),
        ]);
    }

    public function updateStatus(Request $request, CrmCalendarEvent $crmCalendarEvent): JsonResponse
    {
        $crmCalendarEvent->loadMissing('calendar.memberships');
        abort_unless($this->calendarService->canEditEvent($this->crmUser(), $crmCalendarEvent), 403);

        $validated = $request->validate([
            'status' => ['required', 'in:' . implode(',', array_keys(config('heritage_crm.calendar_event_statuses', [])))],
        ]);

        $crmCalendarEvent->update([
            'status' => $validated['status'],
            'updated_by_id' => $this->crmUser()->id,
        ]);

        $crmCalendarEvent->refresh()->load([
            'calendar.owner:id,firstname,lastname,username,email,role',
            'owner:id,firstname,lastname,username,email,role',
            'lead:id,company_name',
            'customer:id,company_name',
            'contact:id,name,email,phone,lead_id,customer_id',
            'request:id,title,owner_id,lead_id,customer_id',
            'attendees.user:id,firstname,lastname,username,email,role',
            'attendees.contact:id,name,email',
        ]);

        return response()->json([
            'message' => 'Calendar event status updated successfully.',
            'event' => $this->eventPayload($crmCalendarEvent),
        ]);
    }

    public function destroy(CrmCalendarEvent $crmCalendarEvent): JsonResponse
    {
        $crmCalendarEvent->loadMissing('calendar.memberships');
        abort_unless($this->calendarService->canEditEvent($this->crmUser(), $crmCalendarEvent), 403);

        $crmCalendarEvent->delete();

        return response()->json([
            'message' => 'Calendar event deleted successfully.',
        ]);
    }

    private function eventPayload(CrmCalendarEvent $event): array
    {
        $canViewSensitive = $this->calendarService->canViewEventSensitiveDetails($this->crmUser(), $event);
        $title = $canViewSensitive
            ? $event->title
            : ($event->visibility === 'busy_only' ? 'Busy' : 'Private event');

        return [
            'id' => $event->id,
            'title' => $title,
            'start' => $event->starts_at?->toIso8601String(),
            'end' => $event->ends_at?->toIso8601String(),
            'allDay' => (bool) $event->all_day,
            'backgroundColor' => $event->calendar?->color ?: '#5156be',
            'borderColor' => $event->calendar?->color ?: '#5156be',
            'editable' => $this->calendarService->canEditEvent($this->crmUser(), $event),
            'classNames' => [
                'crm-calendar-status-' . $event->status,
                'crm-calendar-visibility-' . $event->visibility,
            ],
            'extendedProps' => [
                'calendar_id' => $event->calendar_id,
                'calendar_name' => $event->calendar?->name,
                'owner_id' => $event->owner_id,
                'owner_name' => $event->owner?->name ?: $event->calendar?->owner?->name,
                'status' => $event->status,
                'visibility' => $event->visibility,
                'location' => $canViewSensitive ? $event->location : null,
                'description' => $canViewSensitive ? $event->description : null,
                'lead_id' => $canViewSensitive ? $event->lead_id : null,
                'lead_name' => $canViewSensitive ? $event->lead?->company_name : null,
                'customer_id' => $canViewSensitive ? $event->customer_id : null,
                'customer_name' => $canViewSensitive ? $event->customer?->company_name : null,
                'contact_id' => $canViewSensitive ? $event->contact_id : null,
                'contact_name' => $canViewSensitive ? $event->contact?->name : null,
                'request_id' => $canViewSensitive ? $event->request_id : null,
                'request_title' => $canViewSensitive ? $event->request?->title : null,
                'timezone' => $event->timezone ?: config('app.timezone'),
                'reminders' => $canViewSensitive ? ($event->reminders ?? []) : [],
                'attendees' => $canViewSensitive
                    ? $event->attendees->map(function ($attendee) {
                        return [
                            'user_id' => $attendee->user_id,
                            'contact_id' => $attendee->contact_id,
                            'name' => $attendee->display_name ?: $attendee->user?->name ?: $attendee->contact?->name,
                            'email' => $attendee->email ?: $attendee->user?->email ?: $attendee->contact?->email,
                        ];
                    })->values()->all()
                    : [],
                'attendee_user_ids' => $canViewSensitive
                    ? $event->attendees->pluck('user_id')->filter()->map(fn ($userId) => (int) $userId)->values()->all()
                    : [],
                'can_view_sensitive' => $canViewSensitive,
                'can_edit' => $this->calendarService->canEditEvent($this->crmUser(), $event),
            ],
        ];
    }

    private function miniMonthWeeks(Carbon $selectedDate): array
    {
        $cursor = $selectedDate->copy()->startOfMonth()->startOfWeek();
        $monthEnd = $selectedDate->copy()->endOfMonth()->endOfWeek();
        $weeks = [];

        while ($cursor->lte($monthEnd)) {
            $week = [];

            for ($dayIndex = 0; $dayIndex < 7; $dayIndex++) {
                $day = $cursor->copy();
                $week[] = [
                    'label' => $day->day,
                    'date' => $day->toDateString(),
                    'is_current_month' => $day->month === $selectedDate->month,
                    'is_today' => $day->isToday(),
                    'is_selected' => $day->isSameDay($selectedDate),
                ];
                $cursor->addDay();
            }

            $weeks[] = $week;
        }

        return $weeks;
    }
}
