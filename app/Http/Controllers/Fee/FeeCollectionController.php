<?php

namespace App\Http\Controllers\Fee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fee\CancelInvoiceRequest;
use App\Http\Requests\Fee\GenerateBulkInvoicesRequest;
use App\Http\Requests\Fee\GenerateInvoiceRequest;
use App\Http\Requests\Fee\StorePaymentRequest;
use App\Http\Requests\Fee\VoidPaymentRequest;
use App\Helpers\TermHelper;
use App\Models\Fee\FeePayment;
use App\Models\Fee\StudentInvoice;
use App\Models\Grade;
use App\Models\SchoolSetup;
use App\Models\Student;
use App\Models\Term;
use App\Services\Fee\InvoiceService;
use App\Services\Fee\PaymentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class FeeCollectionController extends Controller
{
    protected InvoiceService $invoiceService;
    protected PaymentService $paymentService;

    public function __construct(InvoiceService $invoiceService, PaymentService $paymentService)
    {
        $this->middleware('auth');
        $this->invoiceService = $invoiceService;
        $this->paymentService = $paymentService;
    }

    // ========================================
    // Invoice Methods
    // ========================================

    /**
     * Display a listing of invoices with filters.
     */
    public function indexInvoices(Request $request): View
    {
        $this->authorize('viewAny', StudentInvoice::class);

        // Default to current term's year
        $currentTermYear = TermHelper::getCurrentTerm()?->year ?? (int) date('Y');

        $year = $request->filled('year')
            ? (int) $request->year
            : $currentTermYear;

        $query = StudentInvoice::with(['student', 'createdBy']);

        // Apply year filter
        $query->forYear($year);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($studentQuery) use ($search) {
                        $studentQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('id', 'like', "{$search}%");
                    });
            });
        }

        // Calculate totals across ALL matching invoices (before pagination)
        $totals = (clone $query)->selectRaw('
            COUNT(*) as total_count,
            COALESCE(SUM(total_amount), 0) as total_amount,
            COALESCE(SUM(balance), 0) as total_outstanding
        ')->first();

        $invoices = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('fees.collection.invoices.index', [
            'invoices' => $invoices,
            'years' => $this->getAvailableYears(),
            'statuses' => StudentInvoice::statuses(),
            'filters' => [
                'year' => $year,
                'status' => $request->status,
                'search' => $request->search,
            ],
            'totals' => [
                'count' => $totals->total_count ?? 0,
                'amount' => $totals->total_amount ?? 0,
                'outstanding' => $totals->total_outstanding ?? 0,
            ],
        ]);
    }

    /**
     * Display the specified invoice.
     */
    public function showInvoice(StudentInvoice $invoice): View
    {
        $this->authorize('view', $invoice);

        $invoice->load([
            'student.sponsor',
            'student.currentGrade',
            'items.feeStructure.feeType',
            'payments.receivedBy',
            'createdBy',
        ]);

        return view('fees.collection.invoices.show', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * Generate PDF for invoice.
     */
    public function printInvoice(StudentInvoice $invoice): Response
    {
        $this->authorize('view', $invoice);

        // Load all required relationships
        $invoice->load([
            'student.sponsor',
            'student.currentGrade',
            'items.feeStructure.feeType',
            'createdBy',
        ]);

        // Get school information
        $school = SchoolSetup::first();

        $pdf = Pdf::loadView('fees.collection.invoices.pdf', [
            'invoice' => $invoice,
            'school' => $school,
        ]);

        // Set paper size to A4
        $pdf->setPaper('A4', 'portrait');

        // Return PDF for browser viewing
        return $pdf->stream("invoice-{$invoice->invoice_number}.pdf");
    }

    /**
     * Show the form for creating a new invoice.
     */
    public function createInvoice(Request $request): View
    {
        $this->authorize('create', StudentInvoice::class);

        $student = null;
        if ($request->filled('student_id')) {
            $student = Student::with('currentGrade')->find($request->student_id);
        }

        return view('fees.collection.invoices.create', [
            'years' => $this->getAvailableYears(),
            'student' => $student,
        ]);
    }

    /**
     * Store a newly created invoice.
     */
    public function storeInvoice(GenerateInvoiceRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $student = Student::with('currentGrade')->findOrFail($validated['student_id']);

            // Get grade from student's current enrollment
            $gradeId = $student->currentGrade?->id;
            if (!$gradeId) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Student is not enrolled in any grade.');
            }

            $invoice = $this->invoiceService->generateInvoice(
                $student,
                $validated['year'],
                $gradeId,
                auth()->user(),
                $validated['due_date'] ?? null,
                $validated['notes'] ?? null
            );

            return redirect()
                ->route('fees.collection.invoices.show', $invoice)
                ->with('success', "Invoice #{$invoice->invoice_number} generated successfully.");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show the form for bulk invoice generation.
     */
    public function bulkInvoices(Request $request): View
    {
        $this->authorize('create', StudentInvoice::class);

        return view('fees.collection.invoices.bulk', [
            'grades' => Grade::where('active', true)->orderBy('name')->get(),
            'years' => $this->getAvailableYears(),
        ]);
    }

    /**
     * Generate bulk invoices for a grade (synchronous - for small batches).
     */
    public function storeBulkInvoices(GenerateBulkInvoicesRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Use async processing for better performance and UX
        if ($request->has('async') || $request->boolean('async', true)) {
            return $this->storeBulkInvoicesAsync($request);
        }

        $results = $this->invoiceService->generateBulkInvoices(
            $validated['grade_id'],
            $validated['year'],
            auth()->user(),
            $validated['due_date'] ?? null
        );

        // Check if no students were found
        if ($results['generated'] === 0 && $results['skipped'] === 0 && $results['errors'] === 0) {
            return redirect()
                ->back()
                ->with('error', 'No students found in the selected grade for this year. Please ensure students are enrolled with "Current" status.');
        }

        $message = "Bulk invoices generated: {$results['generated']} created";

        if ($results['skipped'] > 0) {
            $message .= ", {$results['skipped']} skipped (existing)";
        }

        if ($results['errors'] > 0) {
            $message .= ", {$results['errors']} errors";
        }

        // Use 'message' for success, 'error' for warnings (matches view session checks)
        $flashType = $results['errors'] > 0 ? 'error' : 'message';

        return redirect()
            ->route('fees.collection.invoices.index')
            ->with($flashType, $message);
    }

    /**
     * Generate bulk invoices asynchronously using queue batching.
     */
    public function storeBulkInvoicesAsync(GenerateBulkInvoicesRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $result = $this->invoiceService->generateBulkInvoicesAsync(
            $validated['grade_id'],
            $validated['year'],
            auth()->user(),
            $validated['due_date'] ?? null
        );

        if ($result['total_students'] === 0) {
            return redirect()
                ->back()
                ->with('error', 'No students found in the selected grade for this year. Please ensure students are enrolled with "Current" status.');
        }

        return redirect()
            ->route('fees.collection.invoices.bulk.progress', ['batch_key' => $result['batch_key']])
            ->with('message', "Bulk invoice generation started for {$result['total_students']} students. You can track progress on this page.");
    }

    /**
     * Show progress of bulk invoice generation.
     */
    public function bulkInvoiceProgress(Request $request): View|JsonResponse
    {
        $this->authorize('create', StudentInvoice::class);

        $batchKey = $request->get('batch_key');

        if (!$batchKey) {
            return redirect()->route('fees.collection.invoices.bulk')
                ->with('error', 'No batch key provided.');
        }

        $progress = $this->invoiceService->getBulkInvoiceProgress($batchKey);

        if (!$progress) {
            return redirect()->route('fees.collection.invoices.bulk')
                ->with('error', 'Batch not found or expired.');
        }

        // Return JSON for AJAX requests
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($progress);
        }

        return view('fees.collection.invoices.bulk-progress', [
            'progress' => $progress,
            'batchKey' => $batchKey,
        ]);
    }

    /**
     * Cancel a running bulk invoice generation batch.
     */
    public function cancelBulkInvoices(Request $request): JsonResponse|RedirectResponse
    {
        $this->authorize('create', StudentInvoice::class);

        $batchKey = $request->get('batch_key');

        if (!$batchKey) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No batch key provided.'], 400);
            }
            return redirect()->back()->with('error', 'No batch key provided.');
        }

        $cancelled = $this->invoiceService->cancelBulkInvoiceBatch($batchKey);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => $cancelled,
                'message' => $cancelled ? 'Batch cancelled successfully.' : 'Could not cancel batch.',
            ]);
        }

        return redirect()->route('fees.collection.invoices.index')
            ->with($cancelled ? 'message' : 'error',
                $cancelled ? 'Bulk invoice generation cancelled.' : 'Could not cancel batch.');
    }

    /**
     * Cancel the specified invoice.
     */
    public function cancelInvoice(CancelInvoiceRequest $request, StudentInvoice $invoice): RedirectResponse
    {
        try {
            $this->authorize('cancel', $invoice);

            $this->invoiceService->cancelInvoice(
                $invoice,
                auth()->user(),
                $request->validated()['reason']
            );

            return redirect()
                ->back()
                ->with('success', "Invoice #{$invoice->invoice_number} has been cancelled.");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Recalculate the specified invoice with current discounts.
     */
    public function recalculateInvoice(StudentInvoice $invoice): RedirectResponse
    {
        try {
            $this->authorize('update', $invoice);

            $this->invoiceService->recalculateInvoice($invoice, auth()->user());

            return redirect()
                ->back()
                ->with('success', "Invoice #{$invoice->invoice_number} has been recalculated with current discounts.");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    // ========================================
    // Student Search and Account Methods
    // ========================================

    /**
     * Search for students (AJAX autocomplete).
     */
    public function searchStudent(Request $request): JsonResponse
    {
        $this->authorize('create', StudentInvoice::class);

        $request->validate([
            'search' => ['required', 'string', 'min:2'],
        ]);

        $search = $request->search;

        $students = Student::with(['currentGrade', 'sponsor'])
            ->whereHas('currentGrade')
            ->where(function ($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "{$search}%");
            })
            ->where('status', Student::STATUS_CURRENT)
            ->limit(20)
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->full_name,
                    'student_id' => $student->id,
                    'grade_name' => $student->currentGrade?->name ?? 'N/A',
                ];
            });

        return response()->json($students);
    }

    /**
     * Display a student's fee account.
     */
    public function studentAccount(Student $student, Request $request): View
    {
        $this->authorize('viewAny', StudentInvoice::class);

        $student->load(['sponsor', 'currentGrade']);

        // Default to current term's year
        $currentTermYear = TermHelper::getCurrentTerm()?->year ?? (int) date('Y');

        // Get year filter - handle "All Years" vs default to current term year
        $year = null;
        if ($request->has('year') && $request->year === '') {
            // "All Years" explicitly selected - use null
            $year = null;
        } elseif ($request->filled('year')) {
            // Specific year selected
            $year = (int) $request->year;
        } else {
            // First page load - default to current term year
            $year = $currentTermYear;
        }

        // Get student's invoices
        $invoicesQuery = StudentInvoice::forStudent($student->id)
            ->with(['items', 'payments']);

        // Apply year filter if specific year selected
        if ($year) {
            $invoicesQuery->forYear($year);
        }

        $invoices = $invoicesQuery->orderBy('created_at', 'desc')->get();

        // Calculate totals
        $activeInvoices = $invoices->where('status', '!=', StudentInvoice::STATUS_CANCELLED);
        $totalInvoiced = $activeInvoices->sum('total_amount');
        $totalPaid = $activeInvoices->sum('amount_paid');
        $balance = bcsub((string) $totalInvoiced, (string) $totalPaid, 2);

        return view('fees.collection.student-account', [
            'student' => $student,
            'invoices' => $invoices,
            'years' => $this->getAvailableYears(),
            'selectedYear' => $year,
            'totalInvoiced' => $totalInvoiced,
            'totalPaid' => $totalPaid,
            'balance' => $balance,
        ]);
    }

    // ========================================
    // Payment Methods
    // ========================================

    /**
     * Show the form for creating a new payment.
     */
    public function createPayment(Request $request, StudentInvoice $invoice): View
    {
        $this->authorize('create', FeePayment::class);

        $invoice->load(['student']);

        // Check invoice is not cancelled or fully paid
        if ($invoice->isCancelled()) {
            abort(403, 'Cannot record payment for a cancelled invoice.');
        }

        if ($invoice->isPaid()) {
            abort(403, 'Invoice is already fully paid.');
        }

        return view('fees.collection.payments.create', [
            'invoice' => $invoice,
            'paymentMethods' => FeePayment::paymentMethods(),
        ]);
    }

    /**
     * Store a newly created payment.
     */
    public function storePayment(StorePaymentRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $invoice = StudentInvoice::findOrFail($validated['invoice_id']);

            $payment = $this->paymentService->recordPayment(
                $invoice,
                auth()->user(),
                $validated
            );

            return redirect()
                ->route('fees.collection.payments.show', $payment)
                ->with('success', "Payment recorded. Receipt #{$payment->receipt_number}");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified payment.
     */
    public function showPayment(FeePayment $payment): View
    {
        $this->authorize('view', $payment);

        $payment->load([
            'invoice.student.sponsor',
            'receivedBy',
            'voidedBy',
        ]);

        return view('fees.collection.payments.show', [
            'payment' => $payment,
        ]);
    }

    /**
     * Void the specified payment.
     */
    public function voidPayment(VoidPaymentRequest $request, FeePayment $payment): RedirectResponse
    {
        try {
            $this->authorize('void', $payment);

            $this->paymentService->voidPayment(
                $payment,
                auth()->user(),
                $request->validated()['reason']
            );

            return redirect()
                ->back()
                ->with('success', 'Payment voided. Invoice balance updated.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Generate PDF receipt for the payment.
     */
    public function printReceipt(FeePayment $payment): Response
    {
        $this->authorize('printReceipt', $payment);

        $payment->load([
            'invoice.student.sponsor',
            'invoice.student.currentGrade',
            'receivedBy',
        ]);

        $school = SchoolSetup::first();

        $pdf = Pdf::loadView('fees.collection.payments.receipt-pdf', [
            'payment' => $payment,
            'school' => $school,
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("receipt-{$payment->receipt_number}.pdf");
    }

    /**
     * Get available years for fee invoices.
     *
     * @return \Illuminate\Support\Collection
     */
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
