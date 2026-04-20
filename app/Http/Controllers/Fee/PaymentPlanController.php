<?php

namespace App\Http\Controllers\Fee;

use App\Http\Controllers\Controller;
use App\Models\Fee\PaymentPlan;
use App\Models\Fee\StudentInvoice;
use App\Services\Fee\PaymentPlanService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PaymentPlanController extends Controller
{
    public function __construct(protected PaymentPlanService $paymentPlanService)
    {}

    /**
     * List all payment plans.
     */
    public function index(Request $request)
    {
        Gate::authorize('collect-fees');

        $query = PaymentPlan::with(['student', 'invoice', 'installments'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by year
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        // Search by student
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('student_number', 'like', "%{$search}%");
            });
        }

        $paymentPlans = $query->paginate(20)->withQueryString();

        return view('fees.payment-plans.index', compact('paymentPlans'));
    }

    /**
     * Show form to create a payment plan.
     */
    public function create(StudentInvoice $invoice)
    {
        Gate::authorize('collect-fees');

        $invoice->load('student');

        if ($invoice->hasActivePaymentPlan()) {
            return redirect()
                ->route('fees.collection.invoices.show', $invoice)
                ->with('error', 'This invoice already has an active payment plan.');
        }

        if ($invoice->isPaid()) {
            return redirect()
                ->route('fees.collection.invoices.show', $invoice)
                ->with('error', 'Cannot create payment plan for a fully paid invoice.');
        }

        $frequencies = PaymentPlan::frequencies();

        return view('fees.payment-plans.create', compact('invoice', 'frequencies'));
    }

    /**
     * Preview payment plan installments (AJAX).
     */
    public function preview(Request $request)
    {
        Gate::authorize('collect-fees');

        $request->validate([
            'invoice_id' => 'required|exists:student_invoices,id',
            'number_of_installments' => 'required|integer|min:2|max:12',
            'frequency' => 'required|in:monthly,termly,custom',
            'start_date' => 'required|date|after_or_equal:today',
        ]);

        $invoice = StudentInvoice::findOrFail($request->invoice_id);

        $preview = $this->paymentPlanService->previewPaymentPlan(
            $invoice,
            $request->number_of_installments,
            $request->frequency,
            Carbon::parse($request->start_date)
        );

        return response()->json($preview);
    }

    /**
     * Store a new payment plan.
     */
    public function store(Request $request)
    {
        Gate::authorize('collect-fees');

        $request->validate([
            'invoice_id' => 'required|exists:student_invoices,id',
            'number_of_installments' => 'required|integer|min:2|max:12',
            'frequency' => 'required|in:monthly,termly,custom',
            'start_date' => 'required|date|after_or_equal:today',
            'name' => 'nullable|string|max:255',
        ]);

        $invoice = StudentInvoice::findOrFail($request->invoice_id);

        try {
            $plan = $this->paymentPlanService->createPaymentPlan(
                $invoice,
                $request->number_of_installments,
                $request->frequency,
                Carbon::parse($request->start_date),
                $request->user(),
                $request->name
            );

            return redirect()
                ->route('fees.payment-plans.show', $plan)
                ->with('message', 'Payment plan created successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show a payment plan.
     */
    public function show(PaymentPlan $paymentPlan)
    {
        Gate::authorize('collect-fees');

        $paymentPlan->load([
            'student',
            'invoice',
            'installments.payments',
            'createdBy',
            'cancelledBy',
        ]);

        return view('fees.payment-plans.show', compact('paymentPlan'));
    }

    /**
     * Cancel a payment plan.
     */
    public function cancel(Request $request, PaymentPlan $paymentPlan)
    {
        Gate::authorize('manage-fee-setup');

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $this->paymentPlanService->cancelPaymentPlan(
                $paymentPlan,
                $request->user(),
                $request->reason
            );

            return redirect()
                ->route('fees.payment-plans.show', $paymentPlan)
                ->with('message', 'Payment plan cancelled successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
