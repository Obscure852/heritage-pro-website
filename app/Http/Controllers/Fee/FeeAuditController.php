<?php

namespace App\Http\Controllers\Fee;

use App\Http\Controllers\Controller;
use App\Models\Fee\FeePayment;
use App\Models\Fee\StudentInvoice;
use App\Services\Fee\FeeAuditService;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class FeeAuditController extends Controller
{
    protected FeeAuditService $auditService;

    public function __construct(FeeAuditService $auditService)
    {
        $this->middleware('auth');
        $this->auditService = $auditService;
    }

    /**
     * Display audit history for an invoice.
     */
    public function invoiceHistory(StudentInvoice $invoice): View
    {
        Gate::authorize('view-fee-reports');

        $invoice->load(['student']);

        $auditLogs = $this->auditService->getAuditHistoryForInvoice($invoice);

        // Format changes for each log
        $logsWithChanges = $auditLogs->map(function ($log) {
            $log->formatted_changes = $this->auditService->formatChanges(
                $log->old_values,
                $log->new_values
            );
            return $log;
        });

        return view('fees.audit.invoice-history', [
            'invoice' => $invoice,
            'auditLogs' => $logsWithChanges,
        ]);
    }

    /**
     * Display audit history for a payment.
     */
    public function paymentHistory(FeePayment $payment): View
    {
        Gate::authorize('view-fee-reports');

        $payment->load(['invoice.student', 'receivedBy']);

        $auditLogs = $this->auditService->getAuditHistoryForPayment($payment);

        // Format changes for each log
        $logsWithChanges = $auditLogs->map(function ($log) {
            $log->formatted_changes = $this->auditService->formatChanges(
                $log->old_values,
                $log->new_values
            );
            return $log;
        });

        return view('fees.audit.payment-history', [
            'payment' => $payment,
            'auditLogs' => $logsWithChanges,
        ]);
    }
}
