<?php

namespace App\Http\Controllers\Fee;

use App\Http\Controllers\Controller;
use App\Models\Fee\FeePayment;
use App\Models\Fee\FeeRefund;
use App\Models\Fee\StudentInvoice;
use App\Models\SchoolSetup;
use App\Models\Term;
use App\Services\Fee\RefundService;
use App\Helpers\TermHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class FeeRefundController extends Controller
{
    protected RefundService $refundService;

    public function __construct(RefundService $refundService)
    {
        $this->middleware('auth');
        $this->refundService = $refundService;
    }

    /**
     * Display a listing of refunds.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', FeeRefund::class);

        // Default to current term's year
        $currentTermYear = TermHelper::getCurrentTerm()?->year ?? (int) date('Y');

        $year = $request->filled('year')
            ? (int) $request->year
            : $currentTermYear;

        $query = FeeRefund::with(['invoice.student', 'payment', 'requestedBy', 'approvedBy']);

        // Apply filters
        if ($year) {
            $query->forYear($year);
        }

        if ($request->filled('status')) {
            $query->forStatus($request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('refund_number', 'like', "%{$search}%")
                    ->orWhereHas('invoice.student', function ($studentQuery) use ($search) {
                        $studentQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        $refunds = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('fees.refunds.index', [
            'refunds' => $refunds,
            'years' => $this->getAvailableYears(),
            'statuses' => FeeRefund::statuses(),
            'filters' => [
                'year' => $year,
                'status' => $request->status,
                'search' => $request->search,
            ],
        ]);
    }

    /**
     * Display pending refunds awaiting approval.
     */
    public function pending(Request $request): View
    {
        $this->authorize('approve', FeeRefund::class);

        $year = $request->filled('year') ? (int) $request->year : null;
        $refunds = $this->refundService->getPendingRefunds($year);

        return view('fees.refunds.pending', [
            'refunds' => $refunds,
            'years' => $this->getAvailableYears(),
            'selectedYear' => $year,
        ]);
    }

    /**
     * Show the form for requesting a refund on a payment.
     */
    public function createFromPayment(FeePayment $payment): View
    {
        $this->authorize('create', FeeRefund::class);

        if (!$payment->canBeRefunded()) {
            abort(403, 'This payment cannot be refunded.');
        }

        $payment->load(['invoice.student', 'receivedBy']);

        return view('fees.refunds.create', [
            'payment' => $payment,
            'invoice' => $payment->invoice,
            'refundTypes' => FeeRefund::refundTypes(),
            'refundMethods' => FeeRefund::refundMethods(),
            'maxRefundAmount' => $payment->refundable_amount,
        ]);
    }

    /**
     * Show the form for creating a credit note for an invoice.
     */
    public function createCreditNote(StudentInvoice $invoice): View
    {
        $this->authorize('create', FeeRefund::class);

        if ($invoice->isCancelled()) {
            abort(403, 'Cannot issue credit note for a cancelled invoice.');
        }

        $invoice->load(['student', 'payments']);

        return view('fees.refunds.credit-note', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * Store a new refund request.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', FeeRefund::class);

        $validated = $request->validate([
            'payment_id' => ['required', 'exists:fee_payments,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'refund_method' => ['required', 'in:cash,bank_transfer,mobile_money,cheque,credit_to_account'],
            'refund_date' => ['nullable', 'date'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'reason' => ['required', 'string', 'min:10'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $payment = FeePayment::findOrFail($validated['payment_id']);

            $refund = $this->refundService->requestRefund(
                $payment,
                auth()->user(),
                $validated
            );

            return redirect()
                ->route('fees.refunds.show', $refund)
                ->with('message', "Refund request #{$refund->refund_number} submitted for approval.");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Store a new credit note.
     */
    public function storeCreditNote(Request $request): RedirectResponse
    {
        $this->authorize('create', FeeRefund::class);

        $validated = $request->validate([
            'invoice_id' => ['required', 'exists:student_invoices,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'refund_date' => ['nullable', 'date'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'reason' => ['required', 'string', 'min:10'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $invoice = StudentInvoice::findOrFail($validated['invoice_id']);

            $refund = $this->refundService->requestCreditNote(
                $invoice,
                auth()->user(),
                $validated
            );

            return redirect()
                ->route('fees.refunds.show', $refund)
                ->with('message', "Credit note #{$refund->refund_number} submitted for approval.");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified refund.
     */
    public function show(FeeRefund $refund): View
    {
        $this->authorize('view', $refund);

        $refund->load([
            'invoice.student.sponsor',
            'payment.receivedBy',
            'requestedBy',
            'approvedBy',
            'processedBy',
        ]);

        return view('fees.refunds.show', [
            'refund' => $refund,
        ]);
    }

    /**
     * Approve a pending refund.
     */
    public function approve(FeeRefund $refund): RedirectResponse
    {
        $this->authorize('approve', $refund);

        try {
            $this->refundService->approveRefund($refund, auth()->user());

            return redirect()
                ->back()
                ->with('message', "Refund #{$refund->refund_number} has been approved.");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Reject a pending refund.
     */
    public function reject(Request $request, FeeRefund $refund): RedirectResponse
    {
        $this->authorize('approve', $refund);

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:10'],
        ]);

        try {
            $this->refundService->rejectRefund($refund, auth()->user(), $validated['rejection_reason']);

            return redirect()
                ->back()
                ->with('message', "Refund #{$refund->refund_number} has been rejected.");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Process an approved refund.
     */
    public function process(Request $request, FeeRefund $refund): RedirectResponse
    {
        $this->authorize('process', $refund);

        $validated = $request->validate([
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $this->refundService->processRefund($refund, auth()->user(), $validated);

            return redirect()
                ->back()
                ->with('message', "Refund #{$refund->refund_number} has been processed.");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Generate PDF for refund/credit note.
     */
    public function print(FeeRefund $refund): Response
    {
        $this->authorize('view', $refund);

        $refund->load([
            'invoice.student.sponsor',
            'invoice.student.currentGrade',
            'payment',
            'requestedBy',
            'approvedBy',
            'processedBy',
        ]);

        $school = SchoolSetup::first();

        $pdf = Pdf::loadView('fees.refunds.pdf', [
            'refund' => $refund,
            'school' => $school,
        ]);

        $pdf->setPaper('A4', 'portrait');
        $documentType = $refund->isCreditNote() ? 'credit-note' : 'refund';

        return $pdf->stream("{$documentType}-{$refund->refund_number}.pdf");
    }

    /**
     * Get available years from the terms table.
     */
    protected function getAvailableYears()
    {
        return Term::select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
    }
}
