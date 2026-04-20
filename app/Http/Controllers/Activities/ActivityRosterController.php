<?php

namespace App\Http\Controllers\Activities;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activities\BulkEnrollActivityStudentsRequest;
use App\Http\Requests\Activities\StoreActivityEnrollmentRequest;
use App\Http\Requests\Activities\UpdateActivityEnrollmentStatusRequest;
use App\Models\Activities\Activity;
use App\Models\Activities\ActivityEnrollment;
use App\Services\Activities\ActivityRosterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Validation\ValidationException;
use Throwable;

class ActivityRosterController extends Controller
{
    public function __construct(private readonly ActivityRosterService $activityRosterService)
    {
    }

    public function index(Activity $activity)
    {
        $this->authorize('view', $activity);

        $activity->loadCount([
            'enrollments as active_enrollments_count' => fn ($query) => $query->where('status', ActivityEnrollment::STATUS_ACTIVE),
            'enrollments as historical_enrollments_count' => fn ($query) => $query->where('status', '!=', ActivityEnrollment::STATUS_ACTIVE),
        ]);

        $activeEnrollments = $activity->enrollments()
            ->active()
            ->with($this->activityRosterService->enrollmentRelations())
            ->orderBy('joined_at')
            ->orderBy('id')
            ->get();

        $historicalEnrollments = $activity->enrollments()
            ->historical()
            ->with($this->activityRosterService->enrollmentRelations())
            ->orderByDesc('left_at')
            ->orderByDesc('updated_at')
            ->get();

        $bulkPreview = $this->activityRosterService->bulkEligibilityPreview($activity);

        return view('activities.roster', [
            'activity' => $activity,
            'activeEnrollments' => $activeEnrollments,
            'historicalEnrollments' => $historicalEnrollments,
            'manualCandidates' => $this->activityRosterService->manualEnrollmentCandidates($activity),
            'bulkPreview' => $bulkPreview,
            'eligibleBulkCandidates' => $this->activityRosterService->bulkEligibilityPreview($activity, 0)['students'],
            'remainingCapacity' => $activity->capacity
                ? max($activity->capacity - $activity->active_enrollments_count, 0)
                : null,
            'enrollmentStatuses' => ActivityEnrollment::statuses(),
            'closableStatuses' => ActivityEnrollment::closableStatuses(),
            'sources' => ActivityEnrollment::sources(),
        ]);
    }

    public function store(StoreActivityEnrollmentRequest $request, Activity $activity): RedirectResponse
    {
        try {
            $this->activityRosterService->enrollStudent($activity, $request->validated(), $request->user());
        } catch (ValidationException $exception) {
            return redirect()
                ->route('activities.roster.index', $activity)
                ->withErrors($exception->errors())
                ->withInput()
                ->with('error', collect($exception->errors())->flatten()->first());
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('activities.roster.index', $activity)
                ->withInput()
                ->with('error', 'The roster change could not be completed right now. Please try again.');
        }

        return redirect()
            ->route('activities.roster.index', $activity)
            ->with('message', 'Student added to the activity roster successfully.');
    }

    public function bulkStore(BulkEnrollActivityStudentsRequest $request, Activity $activity): RedirectResponse
    {
        try {
            $count = $this->activityRosterService->bulkEnrollEligibleStudents($activity, $request->validated(), $request->user());
        } catch (ValidationException $exception) {
            return redirect()
                ->route('activities.roster.index', $activity)
                ->withErrors($exception->errors())
                ->withInput()
                ->with('error', collect($exception->errors())->flatten()->first());
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('activities.roster.index', $activity)
                ->withInput()
                ->with('error', 'Bulk roster allocation could not be completed right now. Please try again.');
        }

        return redirect()
            ->route('activities.roster.index', $activity)
            ->with('message', sprintf('%d selected student(s) were added through bulk enrollment.', $count));
    }

    public function update(UpdateActivityEnrollmentStatusRequest $request, Activity $activity, ActivityEnrollment $enrollment): RedirectResponse
    {
        try {
            $this->activityRosterService->updateEnrollmentStatus($activity, $enrollment, $request->validated(), $request->user());
        } catch (ValidationException $exception) {
            return redirect()
                ->route('activities.roster.index', $activity)
                ->withErrors($exception->errors())
                ->with('error', collect($exception->errors())->flatten()->first());
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('activities.roster.index', $activity)
                ->with('error', 'The roster status change could not be completed right now. Please try again.');
        }

        return redirect()
            ->route('activities.roster.index', $activity)
            ->with('message', 'Roster status updated successfully.');
    }

    public function export(Request $request, Activity $activity): Response
    {
        $this->authorize('view', $activity);

        $enrollments = $activity->enrollments()
            ->with($this->activityRosterService->enrollmentRelations())
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->orderBy('joined_at')
            ->orderBy('id')
            ->get();

        $rows = collect([
            ['Student', 'Status', 'Source', 'Joined Date', 'Exit Date', 'Exit Reason', 'Grade', 'Class', 'House'],
        ])->merge($enrollments->map(function (ActivityEnrollment $enrollment) {
            return [
                $enrollment->student?->full_name ?? 'Unknown student',
                ActivityEnrollment::statuses()[$enrollment->status] ?? ucfirst($enrollment->status),
                ActivityEnrollment::sources()[$enrollment->source] ?? $enrollment->source,
                optional($enrollment->joined_at)->format('Y-m-d'),
                optional($enrollment->left_at)->format('Y-m-d'),
                $enrollment->exit_reason,
                $enrollment->gradeSnapshot?->name,
                $enrollment->klassSnapshot?->name,
                $enrollment->houseSnapshot?->name,
            ];
        }));

        $csv = $rows
            ->map(fn (array $row) => collect($row)
                ->map(fn ($value) => '"' . str_replace('"', '""', (string) $value) . '"')
                ->implode(','))
            ->implode("\n");

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="activity-roster-' . $activity->id . '.csv"',
        ]);
    }
}
