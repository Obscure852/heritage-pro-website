<?php

namespace App\Http\Controllers\Activities;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activities\StoreActivitySessionRequest;
use App\Models\Activities\Activity;
use App\Models\Activities\ActivitySession;
use App\Services\Activities\ActivityScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class ActivitySessionController extends Controller
{
    public function __construct(private readonly ActivityScheduleService $activityScheduleService)
    {
    }

    public function store(StoreActivitySessionRequest $request, Activity $activity): RedirectResponse
    {
        $this->authorize('manageSessions', $activity);

        try {
            $this->activityScheduleService->createSession($activity, $request->validated(), $request->user());
        } catch (ValidationException $exception) {
            return redirect()
                ->route('activities.schedules.index', $activity)
                ->withErrors($exception->errors())
                ->withInput()
                ->with('error', collect($exception->errors())->flatten()->first());
        }

        return redirect()
            ->route('activities.schedules.index', $activity)
            ->with('message', 'Manual session saved successfully.');
    }

    public function update(StoreActivitySessionRequest $request, Activity $activity, ActivitySession $session): RedirectResponse
    {
        $this->authorize('manageSessions', $activity);

        try {
            $this->activityScheduleService->updateSession($activity, $session, $request->validated(), $request->user());
        } catch (ValidationException $exception) {
            return redirect()
                ->route('activities.schedules.index', $activity)
                ->withErrors($exception->errors())
                ->with('error', collect($exception->errors())->flatten()->first());
        }

        return redirect()
            ->route('activities.schedules.index', $activity)
            ->with('message', 'Session updated successfully.');
    }
}
