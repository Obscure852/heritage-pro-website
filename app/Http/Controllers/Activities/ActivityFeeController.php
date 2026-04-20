<?php

namespace App\Http\Controllers\Activities;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activities\StoreActivityFeeChargeRequest;
use App\Models\Activities\Activity;
use App\Models\Activities\ActivityFeeCharge;
use App\Models\Fee\StudentInvoice;
use App\Services\Activities\ActivityFeeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class ActivityFeeController extends Controller
{
    public function __construct(private readonly ActivityFeeService $activityFeeService)
    {
    }

    public function index(Activity $activity): View
    {
        $this->authorize('view', $activity);

        $activity->load(['term', 'feeType'])
            ->loadCount([
                'feeCharges',
                'feeCharges as posted_fee_charges_count' => fn ($query) => $query->where('billing_status', ActivityFeeCharge::STATUS_POSTED),
                'feeCharges as pending_fee_charges_count' => fn ($query) => $query->where('billing_status', ActivityFeeCharge::STATUS_PENDING),
                'feeCharges as blocked_fee_charges_count' => fn ($query) => $query->where('billing_status', ActivityFeeCharge::STATUS_BLOCKED),
            ]);

        $charges = $activity->feeCharges()
            ->with([
                'student:id,first_name,last_name',
                'enrollment.gradeSnapshot:id,name',
                'enrollment.klassSnapshot:id,name',
                'event:id,activity_id,title',
                'invoice:id,invoice_number,status,balance',
                'generatedBy:id,firstname,lastname',
            ])
            ->orderByDesc('generated_at')
            ->orderByDesc('id')
            ->get();

        $activeEnrollments = $activity->enrollments()
            ->active()
            ->with([
                'student:id,first_name,last_name',
                'gradeSnapshot:id,name',
                'klassSnapshot:id,name',
            ])
            ->orderBy('student_id')
            ->get();

        $events = $activity->events()
            ->orderByDesc('start_datetime')
            ->get(['id', 'activity_id', 'title', 'status']);

        return view('activities.fees', [
            'activity' => $activity,
            'charges' => $charges,
            'activeEnrollments' => $activeEnrollments,
            'events' => $events,
            'chargeTypes' => ActivityFeeCharge::chargeTypes(),
            'chargeStatuses' => ActivityFeeCharge::statuses(),
            'billingSummary' => $this->activityFeeService->activityBillingSummary($activity),
            'canViewInvoices' => request()->user()?->can('viewAny', StudentInvoice::class) ?? false,
        ]);
    }

    public function store(StoreActivityFeeChargeRequest $request, Activity $activity): RedirectResponse
    {
        $this->authorize('manageFees', $activity);

        try {
            $this->activityFeeService->createCharge($activity, $request->validated(), $request->user());
        } catch (ValidationException $exception) {
            return redirect()
                ->route('activities.fees.index', $activity)
                ->withErrors($exception->errors())
                ->withInput()
                ->with('error', collect($exception->errors())->flatten()->first());
        } catch (\Throwable $exception) {
            return redirect()
                ->route('activities.fees.index', $activity)
                ->withInput()
                ->with('error', $exception->getMessage() ?: 'An error occurred while saving the activity charge.');
        }

        return redirect()
            ->route('activities.fees.index', $activity)
            ->with('message', 'Activity charge saved successfully.');
    }

    public function post(Activity $activity, ActivityFeeCharge $charge): RedirectResponse
    {
        $this->authorize('manageFees', $activity);

        if (
            $charge->billing_status === ActivityFeeCharge::STATUS_POSTED
            || !is_null($charge->student_invoice_item_id)
        ) {
            return redirect()
                ->route('activities.fees.index', $activity)
                ->with('error', 'This charge is already linked to an invoice item.');
        }

        try {
            $this->activityFeeService->postCharge($activity, $charge, request()->user());
        } catch (ValidationException $exception) {
            return redirect()
                ->route('activities.fees.index', $activity)
                ->withErrors($exception->errors())
                ->with('error', collect($exception->errors())->flatten()->first());
        } catch (\Throwable $exception) {
            return redirect()
                ->route('activities.fees.index', $activity)
                ->with('error', $exception->getMessage() ?: 'An error occurred while posting the activity charge.');
        }

        return redirect()
            ->route('activities.fees.index', $activity)
            ->with('message', 'Charge posted to the annual invoice successfully.');
    }
}
