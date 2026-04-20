<?php

namespace App\Http\Controllers\Activities;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activities\GenerateActivitySessionsRequest;
use App\Http\Requests\Activities\StoreActivityScheduleRequest;
use App\Models\Activities\Activity;
use App\Models\Activities\ActivitySchedule;
use App\Models\Activities\ActivitySession;
use App\Services\Activities\ActivityScheduleService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class ActivityScheduleController extends Controller
{
    public function __construct(private readonly ActivityScheduleService $activityScheduleService)
    {
    }

    public function index(Activity $activity)
    {
        $this->authorize('view', $activity);

        $activity->loadCount([
            'schedules as active_schedules_count' => fn ($query) => $query->where('active', true),
            'sessions as upcoming_sessions_count' => fn ($query) => $query
                ->whereDate('session_date', '>=', now()->toDateString())
                ->where('status', '!=', ActivitySession::STATUS_CANCELLED),
            'sessions as pending_attendance_sessions_count' => fn ($query) => $query
                ->whereDate('session_date', '<=', now()->toDateString())
                ->where('status', '!=', ActivitySession::STATUS_CANCELLED)
                ->where('attendance_locked', false),
        ]);

        $schedules = $activity->schedules()
            ->withCount('sessions')
            ->orderByDesc('active')
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        $sessions = $activity->sessions()
            ->with(['schedule', 'creator'])
            ->orderByDesc('session_date')
            ->orderByDesc('start_datetime')
            ->limit(30)
            ->get();

        $nextSession = $activity->sessions()
            ->whereDate('session_date', '>=', now()->toDateString())
            ->where('status', '!=', ActivitySession::STATUS_CANCELLED)
            ->orderBy('session_date')
            ->orderBy('start_datetime')
            ->first();

        return view('activities.schedules', [
            'activity' => $activity,
            'schedules' => $schedules,
            'sessions' => $sessions,
            'nextSession' => $nextSession,
            'scheduleFrequencies' => ActivitySchedule::frequencies(),
            'scheduleDays' => ActivitySchedule::dayLabels(),
            'sessionTypes' => ActivitySession::sessionTypes(),
            'sessionStatuses' => ActivitySession::statuses(),
            'today' => Carbon::today(),
        ]);
    }

    public function store(StoreActivityScheduleRequest $request, Activity $activity): RedirectResponse
    {
        $this->authorize('manageSchedules', $activity);

        try {
            $this->activityScheduleService->createSchedule($activity, $request->validated(), $request->user());
        } catch (ValidationException $exception) {
            return redirect()
                ->route('activities.schedules.index', $activity)
                ->withErrors($exception->errors())
                ->withInput()
                ->with('error', collect($exception->errors())->flatten()->first());
        }

        return redirect()
            ->route('activities.schedules.index', $activity)
            ->with('message', 'Recurring schedule saved successfully.');
    }

    public function update(StoreActivityScheduleRequest $request, Activity $activity, ActivitySchedule $schedule): RedirectResponse
    {
        $this->authorize('manageSchedules', $activity);

        try {
            $this->activityScheduleService->updateSchedule($activity, $schedule, $request->validated(), $request->user());
        } catch (ValidationException $exception) {
            return redirect()
                ->route('activities.schedules.index', $activity)
                ->withErrors($exception->errors())
                ->with('error', collect($exception->errors())->flatten()->first());
        }

        return redirect()
            ->route('activities.schedules.index', $activity)
            ->with('message', 'Recurring schedule updated successfully.');
    }

    public function generate(GenerateActivitySessionsRequest $request, Activity $activity, ActivitySchedule $schedule): RedirectResponse
    {
        $this->authorize('manageSchedules', $activity);

        try {
            $result = $this->activityScheduleService->generateSessions($activity, $schedule, $request->validated(), $request->user());
        } catch (ValidationException $exception) {
            return redirect()
                ->route('activities.schedules.index', $activity)
                ->withErrors($exception->errors())
                ->with('error', collect($exception->errors())->flatten()->first());
        }

        return redirect()
            ->route('activities.schedules.index', $activity)
            ->with('message', sprintf(
                '%d session(s) generated and %d duplicate date(s) skipped.',
                $result['created_count'],
                $result['skipped_count']
            ));
    }
}
