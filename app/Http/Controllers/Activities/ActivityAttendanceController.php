<?php

namespace App\Http\Controllers\Activities;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activities\MarkActivityAttendanceRequest;
use App\Models\Activities\Activity;
use App\Models\Activities\ActivitySession;
use App\Models\Activities\ActivitySessionAttendance;
use App\Services\Activities\ActivityAttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Throwable;

class ActivityAttendanceController extends Controller
{
    public function __construct(private readonly ActivityAttendanceService $activityAttendanceService)
    {
    }

    public function edit(Activity $activity, ActivitySession $session)
    {
        $this->authorize('manageAttendance', $activity);

        $session->load(['schedule', 'creator', 'activity']);

        $eligibleEnrollments = $this->activityAttendanceService->eligibleEnrollments($activity, $session);
        $attendanceMap = $this->activityAttendanceService->attendanceMap($session);

        return view('activities.attendance', [
            'activity' => $activity,
            'session' => $session,
            'eligibleEnrollments' => $eligibleEnrollments,
            'attendanceMap' => $attendanceMap,
            'attendanceSummary' => $this->activityAttendanceService->attendanceSummary($session),
            'attendanceStates' => ActivitySessionAttendance::statuses(),
            'canReopenAttendance' => request()->user()?->can('reopenAttendance', $activity) ?? false,
        ]);
    }

    public function update(MarkActivityAttendanceRequest $request, Activity $activity, ActivitySession $session): RedirectResponse
    {
        $this->authorize('manageAttendance', $activity);

        try {
            $this->activityAttendanceService->saveAttendance($activity, $session, $request->validated(), $request->user());
        } catch (ValidationException $exception) {
            return redirect()
                ->route('activities.attendance.edit', [$activity, $session])
                ->withErrors($exception->errors())
                ->withInput()
                ->with('error', collect($exception->errors())->flatten()->first());
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('activities.attendance.edit', [$activity, $session])
                ->withInput()
                ->with('error', 'Attendance could not be saved right now. Please try again.');
        }

        return redirect()
            ->route('activities.attendance.edit', [$activity, $session])
            ->with('message', 'Attendance saved successfully.');
    }

    public function finalize(Activity $activity, ActivitySession $session): RedirectResponse
    {
        $this->authorize('manageAttendance', $activity);

        try {
            $this->activityAttendanceService->finalizeAttendance($activity, $session, request()->user());
        } catch (ValidationException $exception) {
            return redirect()
                ->route('activities.attendance.edit', [$activity, $session])
                ->withErrors($exception->errors())
                ->with('error', collect($exception->errors())->flatten()->first());
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('activities.attendance.edit', [$activity, $session])
                ->with('error', 'Attendance could not be finalized right now. Please try again.');
        }

        return redirect()
            ->route('activities.attendance.edit', [$activity, $session])
            ->with('message', 'Attendance finalized and locked successfully.');
    }

    public function reopen(Activity $activity, ActivitySession $session): RedirectResponse
    {
        $this->authorize('reopenAttendance', $activity);

        try {
            $this->activityAttendanceService->reopenAttendance($activity, $session, request()->user());
        } catch (ValidationException $exception) {
            return redirect()
                ->route('activities.attendance.edit', [$activity, $session])
                ->withErrors($exception->errors())
                ->with('error', collect($exception->errors())->flatten()->first());
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('activities.attendance.edit', [$activity, $session])
                ->with('error', 'Attendance could not be reopened right now. Please try again.');
        }

        return redirect()
            ->route('activities.attendance.edit', [$activity, $session])
            ->with('message', 'Attendance reopened for corrections.');
    }
}
