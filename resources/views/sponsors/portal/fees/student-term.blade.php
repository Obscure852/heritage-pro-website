@include('sponsors.portal.partials.sponsor-portal-styles')

<style>
/* Fees-specific premium styles */
.balance-card {
    background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    color: white;
    padding: 24px;
    border-radius: 3px;
    position: relative;
    overflow: hidden;
    margin-bottom: 24px;
}

.balance-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 60%;
    height: 200%;
    background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, transparent 50%);
    transform: rotate(-15deg);
    pointer-events: none;
}

.balance-amount-large {
    font-size: 2.5rem;
    font-weight: 700;
    position: relative;
    z-index: 1;
}

.balance-stat {
    background: rgba(255, 255, 255, 0.15);
    border-radius: 3px;
    padding: 12px 16px;
    text-align: center;
}

.balance-stat-label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    opacity: 0.85;
}

.balance-stat-value {
    font-size: 16px;
    font-weight: 600;
    margin-top: 4px;
}

.invoice-meta {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 16px;
    padding: 16px;
    background: #f8f9fc;
    border-radius: 3px;
    margin-bottom: 16px;
}

.invoice-meta-item {
    text-align: center;
}

.invoice-meta-label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6b7280;
}

.invoice-meta-value {
    font-size: 14px;
    font-weight: 600;
    color: #1f2937;
    margin-top: 4px;
}

.status-badge-paid {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    color: #065f46;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
}

.status-badge-partial {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    color: #92400e;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
}

.status-badge-outstanding {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    color: #991b1b;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
}

.payment-method-badge {
    padding: 4px 12px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 500;
}

.method-cash { background: #d1fae5; color: #065f46; }
.method-bank { background: #dbeafe; color: #1e40af; }
.method-mobile { background: #e9d5ff; color: #6b21a8; }
.method-cheque { background: #fef3c7; color: #92400e; }

.download-btn {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 3px;
    font-weight: 500;
    font-size: 14px;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.download-btn:hover {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    color: white;
}
</style>

@php
    $balance = (float)$student->feeBalance['balance'];
    $totalPaid = (float)$student->feeBalance['total_paid'];
    $totalInvoiced = (float)$student->feeBalance['total_invoiced'];
@endphp

<div class="container-fluid px-0">
    <!-- Balance Card -->
    <div class="balance-card">
        <div class="row align-items-center g-3">
            <div class="col">
                <div class="balance-stat-label" style="font-size: 13px;">Current Balance</div>
                <div class="balance-amount-large">
                    P {{ number_format($balance, 2) }}
                </div>
                <div class="mt-2">
                    @if ($balance == 0)
                        <span class="status-badge-paid">Fully Paid</span>
                    @elseif ($totalPaid > 0)
                        <span class="status-badge-partial">Partial Payment</span>
                    @else
                        <span class="status-badge-outstanding">Outstanding</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="row mt-4 g-3">
            <div class="col-6">
                <div class="balance-stat">
                    <div class="balance-stat-label">Total Invoiced</div>
                    <div class="balance-stat-value">P {{ number_format($totalInvoiced, 2) }}</div>
                </div>
            </div>
            <div class="col-6">
                <div class="balance-stat">
                    <div class="balance-stat-label">Total Paid</div>
                    <div class="balance-stat-value">P {{ number_format($totalPaid, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoice Section -->
    <div class="section-title">
        <i class="bx bx-file"></i>
        Invoice Details
    </div>

    @if ($student->invoice)
        <div class="invoice-meta">
            <div class="invoice-meta-item">
                <div class="invoice-meta-label">Invoice Number</div>
                <div class="invoice-meta-value">{{ $student->invoice->invoice_number }}</div>
            </div>
            <div class="invoice-meta-item">
                <div class="invoice-meta-label">Issue Date</div>
                <div class="invoice-meta-value">{{ $student->invoice->issued_at ? $student->invoice->issued_at->format('d M Y') : 'N/A' }}</div>
            </div>
            <div class="invoice-meta-item">
                <div class="invoice-meta-label">Due Date</div>
                <div class="invoice-meta-value">{{ $student->invoice->due_date ? $student->invoice->due_date->format('d M Y') : 'N/A' }}</div>
            </div>
            <div class="invoice-meta-item">
                <div class="invoice-meta-label">Status</div>
                <div class="invoice-meta-value">
                    <span class="{{ $student->invoice->status_color }}">
                        {{ ucfirst($student->invoice->status) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Invoice Items Table -->
        <div class="subject-table-container">
            <table class="subject-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="text-end" style="width: 120px;">Amount</th>
                        <th class="text-end" style="width: 100px;">Discount</th>
                        <th class="text-end" style="width: 120px;">Net</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($student->invoice->items as $item)
                        <tr>
                            <td>
                                <div class="subject-name">
                                    <span class="subject-icon">
                                        <i class="bx bx-receipt"></i>
                                    </span>
                                    {{ $item->description }}
                                </div>
                            </td>
                            <td class="text-end">P {{ number_format((float)$item->amount, 2) }}</td>
                            <td class="text-end text-success">- P {{ number_format((float)$item->discount_amount, 2) }}</td>
                            <td class="text-end fw-semibold">P {{ number_format((float)$item->net_amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot style="background: #f8f9fc;">
                    <tr>
                        <td colspan="3" class="text-end fw-semibold">Subtotal</td>
                        <td class="text-end">P {{ number_format((float)$student->invoice->subtotal_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-end fw-semibold">Total Discount</td>
                        <td class="text-end text-success">- P {{ number_format((float)$student->invoice->discount_amount, 2) }}</td>
                    </tr>
                    <tr style="background: #e8f4fd;">
                        <td colspan="3" class="text-end fw-bold">Total Amount</td>
                        <td class="text-end fw-bold">P {{ number_format((float)$student->invoice->total_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-end fw-semibold">Amount Paid</td>
                        <td class="text-end text-success fw-semibold">P {{ number_format((float)$student->invoice->amount_paid, 2) }}</td>
                    </tr>
                    <tr style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);">
                        <td colspan="3" class="text-end fw-bold">Balance Due</td>
                        <td class="text-end fw-bold">P {{ number_format((float)$student->invoice->balance, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @else
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="bx bx-file-blank"></i>
            </div>
            <h5>No Invoice Generated</h5>
            <p>An invoice has not been generated for this year yet.</p>
        </div>
    @endif

    <!-- Payment History Section -->
    <div class="section-title mt-4">
        <i class="bx bx-credit-card"></i>
        Payment History
    </div>

    @if ($student->payments && $student->payments->count() > 0)
        <div class="subject-table-container">
            <table class="subject-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Receipt #</th>
                        <th class="text-end">Amount</th>
                        <th>Method</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($student->payments as $payment)
                        <tr>
                            <td>{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') : 'N/A' }}</td>
                            <td>
                                <span class="fw-semibold">{{ $payment->receipt_number }}</span>
                            </td>
                            <td class="text-end">
                                <span class="score-cell score-excellent">P {{ number_format((float)$payment->amount, 2) }}</span>
                            </td>
                            <td>
                                @php
                                    $methodClass = match($payment->payment_method) {
                                        'cash' => 'method-cash',
                                        'bank_transfer', 'bank_deposit' => 'method-bank',
                                        'mobile_money' => 'method-mobile',
                                        'cheque' => 'method-cheque',
                                        default => 'bg-secondary text-white'
                                    };
                                @endphp
                                <span class="payment-method-badge {{ $methodClass }}">
                                    {{ ucwords(str_replace('_', ' ', $payment->payment_method)) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="bx bx-credit-card"></i>
            </div>
            <h5>No Payments Recorded</h5>
            <p>No payments have been made yet.</p>
        </div>
    @endif

    <!-- Download Statement -->
    <div class="d-flex justify-content-end mt-4 pt-4 border-top">
        <a href="{{ route('sponsor.statement-pdf', ['student' => $student->id]) }}" class="download-btn" target="_blank">
            <i class="bx bx-download"></i>
            Download Statement (PDF)
        </a>
    </div>
</div>
