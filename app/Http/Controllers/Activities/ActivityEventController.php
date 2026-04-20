<?php

namespace App\Http\Controllers\Activities;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activities\StoreActivityEventRequest;
use App\Models\Activities\Activity;
use App\Models\Activities\ActivityEvent;
use App\Services\Activities\ActivityEventService;
use App\Services\Activities\ActivityResultService;
use App\Services\Activities\ActivitySettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class ActivityEventController extends Controller
{
    public function __construct(
        private readonly ActivityEventService $activityEventService,
        private readonly ActivityResultService $activityResultService,
        private readonly ActivitySettingsService $activitySettingsService
    ) {
    }

    public function index(Activity $activity)
    {
        $this->authorize('view', $activity);

        $activity->loadCount([
            'events',
            'events as completed_events_count' => fn ($query) => $query->where('status', ActivityEvent::STATUS_COMPLETED),
            'events as house_linked_events_count' => fn ($query) => $query->where('house_linked', true),
        ]);

        $events = $activity->events()
            ->with(['creator', 'results'])
            ->orderByDesc('start_datetime')
            ->get()
            ->map(function (ActivityEvent $event) {
                $event->setAttribute('result_summary', $this->activityResultService->resultsSummary($event));

                return $event;
            });

        return view('activities.events', [
            'activity' => $activity,
            'events' => $events,
            'eventTypes' => ActivityEvent::eventTypes(),
            'createEventTypes' => $this->activitySettingsService->activeEventTypeOptions(),
            'eventStatuses' => ActivityEvent::statuses(),
            'eventOutputsSummary' => $this->activityResultService->activityOutputsSummary($activity),
            'eventDefaults' => $this->activitySettingsService->eventDefaults($activity),
        ]);
    }

    public function store(StoreActivityEventRequest $request, Activity $activity): RedirectResponse
    {
        $this->authorize('manageEvents', $activity);

        try {
            $this->activityEventService->createEvent($activity, $request->validated(), $request->user());
        } catch (ValidationException $exception) {
            return redirect()
                ->route('activities.events.index', $activity)
                ->withErrors($exception->errors())
                ->withInput()
                ->with('error', collect($exception->errors())->flatten()->first());
        }

        return redirect()
            ->route('activities.events.index', $activity)
            ->with('message', 'Event saved successfully.');
    }

    public function update(StoreActivityEventRequest $request, Activity $activity, ActivityEvent $event): RedirectResponse
    {
        $this->authorize('manageEvents', $activity);

        try {
            $this->activityEventService->updateEvent($activity, $event, $request->validated(), $request->user());
        } catch (ValidationException $exception) {
            return redirect()
                ->route('activities.events.index', $activity)
                ->withErrors($exception->errors())
                ->with('error', collect($exception->errors())->flatten()->first());
        }

        return redirect()
            ->route('activities.events.index', $activity)
            ->with('message', 'Event updated successfully.');
    }
}
