<?php

namespace App\Http\Controllers\Activities;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activities\AssignActivityStaffRequest;
use App\Models\Activities\Activity;
use App\Models\Activities\ActivityStaffAssignment;
use App\Models\User;
use App\Services\Activities\ActivityOwnershipService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class ActivityStaffController extends Controller
{
    public function __construct(private readonly ActivityOwnershipService $activityOwnershipService)
    {
    }

    public function index(Activity $activity)
    {
        $this->authorize('manageStaff', $activity);

        $activity->loadCount([
            'staffAssignments as active_staff_assignments_count' => fn ($query) => $query->where('active', true),
        ]);

        $assignments = $activity->staffAssignments()
            ->with('user')
            ->orderByDesc('active')
            ->orderByDesc('is_primary')
            ->orderBy('role')
            ->orderByDesc('assigned_at')
            ->get();

        $availableUsers = User::query()
            ->select(['id', 'firstname', 'lastname', 'position', 'status'])
            ->when(
                \Illuminate\Support\Facades\Schema::hasColumn('users', 'status'),
                fn ($query) => $query->where('status', 'Current')
            )
            ->orderBy('firstname')
            ->orderBy('lastname')
            ->get();

        return view('activities.staff', [
            'activity' => $activity,
            'assignments' => $assignments,
            'availableUsers' => $availableUsers,
            'staffRoles' => ActivityStaffAssignment::roles(),
        ]);
    }

    public function store(AssignActivityStaffRequest $request, Activity $activity): RedirectResponse
    {
        try {
            $this->activityOwnershipService->assignStaff($activity, $request->validated(), $request->user());
        } catch (ValidationException $exception) {
            return redirect()
                ->route('activities.staff.index', $activity)
                ->withErrors($exception->errors())
                ->withInput()
                ->with('error', collect($exception->errors())->flatten()->first());
        }

        return redirect()
            ->route('activities.staff.index', $activity)
            ->with('message', 'Activity staff assignment saved successfully.');
    }

    public function destroy(Activity $activity, ActivityStaffAssignment $assignment): RedirectResponse
    {
        $this->authorize('manageStaff', $activity);

        try {
            $this->activityOwnershipService->retireStaffAssignment($activity, $assignment, request()->user());
        } catch (ValidationException $exception) {
            return redirect()
                ->route('activities.staff.index', $activity)
                ->withErrors($exception->errors())
                ->with('error', collect($exception->errors())->flatten()->first());
        }

        return redirect()
            ->route('activities.staff.index', $activity)
            ->with('message', 'Activity staff assignment retired successfully.');
    }
}
