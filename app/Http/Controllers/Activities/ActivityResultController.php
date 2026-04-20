<?php

namespace App\Http\Controllers\Activities;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activities\SyncActivityResultsRequest;
use App\Models\Activities\Activity;
use App\Models\Activities\ActivityEvent;
use App\Models\Activities\ActivityResult;
use App\Services\Activities\ActivityResultService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Throwable;

class ActivityResultController extends Controller
{
    public function __construct(private readonly ActivityResultService $activityResultService)
    {
    }

    public function edit(Activity $activity, ActivityEvent $event)
    {
        $this->authorize('view', $activity);

        if ($event->activity_id !== $activity->id) {
            abort(404);
        }

        $event->load(['creator', 'results.recordedBy']);

        return view('activities.results', [
            'activity' => $activity,
            'event' => $event,
            'resultsSummary' => $this->activityResultService->resultsSummary($event),
            'groupedResults' => $this->activityResultService->groupedResults($event),
            'studentParticipants' => $this->activityResultService->eligibleStudentParticipants($activity, $event),
            'houseParticipants' => $event->house_linked
                ? $this->activityResultService->availableHouses($activity)
                : collect(),
            'studentResultMap' => $this->activityResultService->existingResultsMap($event, ActivityResult::PARTICIPANT_STUDENT),
            'houseResultMap' => $this->activityResultService->existingResultsMap($event, ActivityResult::PARTICIPANT_HOUSE),
            'canManageResults' => request()->user()?->can('manageResults', $activity) ?? false,
        ]);
    }

    public function update(SyncActivityResultsRequest $request, Activity $activity, ActivityEvent $event): RedirectResponse
    {
        $this->authorize('manageResults', $activity);

        try {
            $result = $this->activityResultService->syncResults($activity, $event, $request->validated(), $request->user());
        } catch (ValidationException $exception) {
            return redirect()
                ->route('activities.results.edit', [$activity, $event])
                ->withErrors($exception->errors())
                ->withInput()
                ->with('error', collect($exception->errors())->flatten()->first());
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('activities.results.edit', [$activity, $event])
                ->withInput()
                ->with('error', 'Results could not be saved right now. Please try again.');
        }

        return redirect()
            ->route('activities.results.edit', [$activity, $event])
            ->with('message', sprintf(
                '%s results saved successfully. %d row(s) kept and %d row(s) removed.',
                ucfirst($result['scope']),
                $result['saved_count'],
                $result['deleted_count']
            ));
    }
}
