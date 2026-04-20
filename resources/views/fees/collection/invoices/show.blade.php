@extends('layouts.master')
@section('title')
    Invoice {{ $invoice->invoice_number }}
@endsection
@section('css')
    <style>
        .form-container {
            background: white;
            border-radius: 3px;
            padding: 32px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        .page-title {
            font-size: 22px;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px;
            border-left: 4px solid #3b82f6;
            border-radius: 0 3px 3px 0;
            margin-bottom: 24px;
        }

        .help-text .help-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }

        .help-text .help-content {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.4;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin: 24px 0 16px 0;
            color: #1f2937;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .info-card {
            background: #f9fafb;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
        }

        .info-item {
            margin-bottom: 12px;
        }

        .info-label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 14px;
            font-weight: 500;
            color: #1f2937;
        }

        .summary-card {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 16px;
            text-align: center;
        }

        @media (max-width: 992px) {
            .summary-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 576px) {
            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .summary-item h4 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .summary-item small {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.9;
        }

        .table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
        }

        .table tbody tr:hover {
            background-color: #f9fafb;
        }

        .amount-cell {
            font-weight: 600;
            color: #059669;
        }

        .discount-cell {
            color: #dc2626;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-paid {
            background: #d1fae5;
            color: #065f46;
        }

        .status-partial {
            background: #fef3c7;
            color: #92400e;
        }

        .status-outstanding {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-overdue {
            background: #fca5a5;
            color: #7f1d1d;
        }

        .status-draft {
            background: #f3f4f6;
            color: #6b7280;
        }

        .status-cancelled {
            background: #e5e7eb;
            color: #9ca3af;
            text-decoration: line-through;
        }

        .status-issued {
            background: #fee2e2;
            color: #991b1b;
        }

        .payment-method-badge {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .method-cash {
            background: #d1fae5;
            color: #065f46;
        }

        .method-cheque {
            background: #dbeafe;
            color: #1e40af;
        }

        .method-bank_transfer {
            background: #e0e7ff;
            color: #3730a3;
        }

        .method-mobile_money {
            background: #fef3c7;
            color: #92400e;
        }

        .method-card {
            background: #f3e8ff;
            color: #6b21a8;
        }

        .voided-badge {
            background: #fee2e2;
            color: #991b1b;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            margin-left: 8px;
        }

        .voided-row {
            opacity: 0.6;
            text-decoration: line-through;
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            color: white;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #4b5563 0%, #374151 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(107, 114, 128, 0.3);
            color: white;
        }

        .btn-info {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-info:hover {
            background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
            color: white;
        }

        .btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-warning:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
            color: white;
        }

        .btn-loading.loading .btn-text {
            display: none;
        }

        .btn-loading.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }

        .btn-loading:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: space-between;
            padding-top: 24px;
            border-top: 1px solid #f3f4f6;
            margin-top: 32px;
        }

        .header-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn i {
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
            }

            .form-actions {
                flex-direction: column;
            }

            .form-actions .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('fees.collection.invoices.index') }}">Invoices</a>
        @endslot
        @slot('title')
            Invoice {{ $invoice->invoice_number }}
        @endslot
    @endcomponent

    @if (session('message'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <div class="form-container">
        <div class="page-header">
            <h1 class="page-title">
                Invoice {{ $invoice->invoice_number }}
                @php
                    $statusClass = match ($invoice->status) {
                        'paid' => 'status-paid',
                        'partial' => 'status-partial',
                        'issued' => 'status-outstanding',
                        'overdue' => 'status-overdue',
                        'draft' => 'status-draft',
                        'cancelled' => 'status-cancelled',
                        default => 'status-outstanding',
                    };
                @endphp
                <span class="status-badge {{ $statusClass }}">{{ ucfirst($invoice->status) }}</span>
            </h1>
            <div class="header-actions">
                @if (!in_array($invoice->status, ['cancelled', 'paid']))
                    <a href="{{ route('fees.collection.payments.create', $invoice->id) }}" class="btn btn-success">
                        <i class="far fa-credit-card me-1"></i> Record Payment
                    </a>
                    @if (!$invoice->hasActivePaymentPlan())
                        <a href="{{ route('fees.payment-plans.create', $invoice->id) }}" class="btn btn-outline-primary">
                            <i class="far fa-calendar-alt me-1"></i> Payment Plan
                        </a>
                    @else
                        <a href="{{ route('fees.payment-plans.show', $invoice->activePaymentPlan->id) }}" class="btn btn-outline-info">
                            <i class="far fa-calendar-check me-1"></i> View Plan
                        </a>
                    @endif
                @endif
                <a href="{{ route('fees.collection.invoices.pdf', $invoice->id) }}" class="btn btn-primary" target="_blank">
                    <i class="far fa-file-pdf me-1"></i> Print PDF
                </a>
                @can('request-refunds')
                    @if (!in_array($invoice->status, ['cancelled']))
                        <a href="{{ route('fees.refunds.credit-note.create', $invoice) }}" class="btn btn-info">
                            <i class="far fa-file-alt me-1"></i> Credit Note
                        </a>
                    @endif
                @endcan
                @can('view-fee-reports')
                    <a href="{{ route('fees.audit.invoice-history', $invoice) }}" class="btn btn-secondary">
                        <i class="far fa-clock me-1"></i> Audit History
                    </a>
                @endcan
                @if (!in_array($invoice->status, ['cancelled', 'paid']))
                    @can('collect-fees')
                        <button type="button" class="btn btn-warning" data-bs-toggle="modal"
                            data-bs-target="#recalculateModal">
                            <i class="fas fa-sync-alt me-1"></i> Recalculate
                        </button>
                    @endcan
                    @can('manage-fee-setup')
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                            <i class="far fa-times-circle me-1"></i> Cancel
                        </button>
                    @endcan
                @endif
            </div>
        </div>

        {{-- Student & Invoice Info --}}
        <div class="info-grid">
            <div class="info-card">
                <h6 class="mb-3" style="font-weight: 600; color: #374151;">Student Information</h6>
                <div class="info-item">
                    <div class="info-label">Student Name</div>
                    <div class="info-value">{{ $invoice->student->full_name ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Student Number</div>
                    <div class="info-value">{{ $invoice->student->formatted_id_number ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Grade</div>
                    <div class="info-value">{{ $invoice->student->currentGrade->name ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Sponsor/Parent</div>
                    <div class="info-value">{{ $invoice->student->sponsor->full_name ?? 'N/A' }}</div>
                </div>
            </div>

            <div class="info-card">
                <h6 class="mb-3" style="font-weight: 600; color: #374151;">Invoice Details</h6>
                <div class="info-item">
                    <div class="info-label">Invoice Number</div>
                    <div class="info-value">{{ $invoice->invoice_number }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Year</div>
                    <div class="info-value">{{ $invoice->year }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Issue Date</div>
                    <div class="info-value">{{ $invoice->issued_at ? $invoice->issued_at->format('d M Y') : '-' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Due Date</div>
                    <div class="info-value">{{ $invoice->due_date ? $invoice->due_date->format('d M Y') : '-' }}</div>
                </div>
            </div>
        </div>

        {{-- Amount Summary --}}
        <div class="summary-card">
            <div class="summary-grid">
                <div class="summary-item">
                    <h4>{{ format_currency($invoice->subtotal) }}</h4>
                    <small>Subtotal</small>
                </div>
                <div class="summary-item">
                    <h4>{{ format_currency($invoice->discount_amount) }}</h4>
                    <small>Discount</small>
                </div>
                <div class="summary-item">
                    <h4>{{ format_currency($invoice->total_amount) }}</h4>
                    <small>Total</small>
                </div>
                <div class="summary-item">
                    <h4>{{ format_currency($invoice->amount_paid) }}</h4>
                    <small>Paid</small>
                </div>
                <div class="summary-item">
                    <h4>{{ format_currency($invoice->balance) }}</h4>
                    <small>Balance</small>
                </div>
            </div>
        </div>

        {{-- Current Year Fee Items --}}
        <h3 class="section-title">Invoice Items ({{ $invoice->year }})</h3>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Description</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Discount</th>
                        <th class="text-end">Net Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $feeItems = $invoice->getFeeItems();
                        $rowIndex = 0;
                    @endphp
                    @forelse ($feeItems as $item)
                        @php $rowIndex++; @endphp
                        <tr>
                            <td>{{ $rowIndex }}</td>
                            <td>{{ $item->feeStructure->feeType->name ?? ($item->description ?? 'N/A') }}</td>
                            <td class="text-end">{{ format_currency($item->amount) }}</td>
                            <td class="text-end discount-cell">
                                @if ($item->discount_amount > 0)
                                    - {{ format_currency($item->discount_amount) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end amount-cell">{{ format_currency($item->net_amount) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No fee items on this invoice</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr style="border-top: 2px solid #e5e7eb;">
                        <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                        <td class="text-end amount-cell">
                            <strong>{{ format_currency($invoice->getFeeSubtotal()) }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Previous Years Outstanding (Carryover Items) --}}
        @php
            $carryoverItems = $invoice->getCarryoverItems();
        @endphp
        @if ($carryoverItems->isNotEmpty())
            <h3 class="section-title" style="color: #dc2626;">Previous Years Outstanding</h3>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Source Year</th>
                            <th>Description</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($carryoverItems as $item)
                            <tr>
                                <td><span class="badge bg-warning text-dark">{{ $item->source_year }}</span></td>
                                <td>{{ $item->description }}</td>
                                <td class="text-end amount-cell">{{ format_currency($item->net_amount) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="border-top: 2px solid #e5e7eb;">
                            <td colspan="2" class="text-end"><strong>Total Previous Balance:</strong></td>
                            <td class="text-end amount-cell">
                                <strong>{{ format_currency($invoice->getTotalCarryover()) }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif

        {{-- Late Fee Charges --}}
        @if ($invoice->lateFeeCharges && $invoice->lateFeeCharges->count() > 0)
            <h3 class="section-title" style="color: #dc2626;">Late Fee Charges</h3>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Applied Date</th>
                            <th>Days Overdue</th>
                            <th class="text-end">Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoice->lateFeeCharges as $lateFee)
                            <tr class="{{ $lateFee->waived ? 'text-muted text-decoration-line-through' : '' }}">
                                <td>{{ $lateFee->applied_date->format('d M Y') }}</td>
                                <td>{{ $lateFee->days_overdue }} days</td>
                                <td class="text-end amount-cell">{{ format_currency($lateFee->amount) }}</td>
                                <td>
                                    @if ($lateFee->waived)
                                        <span class="badge bg-secondary">Waived</span>
                                        <small class="text-muted d-block">{{ $lateFee->waived_reason }}</small>
                                    @else
                                        <span class="badge bg-danger">Applied</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="border-top: 2px solid #e5e7eb;">
                            <td colspan="2" class="text-end"><strong>Total Late Fees:</strong></td>
                            <td class="text-end amount-cell">
                                <strong>{{ format_currency($invoice->total_late_fee) }}</strong>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif

        {{-- Grand Total --}}
        <div class="info-card"
            style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-left: 4px solid #059669;">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0" style="color: #059669;">Grand Total</h5>
                <h4 class="mb-0" style="color: #059669; font-weight: 700;">
                    {{ format_currency($invoice->total_amount) }}</h4>
            </div>
        </div>

        {{-- Payments Section --}}
        @if ($invoice->payments && $invoice->payments->count() > 0)
            <h3 class="section-title">Payment History</h3>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Receipt #</th>
                            <th>Date</th>
                            <th class="text-end">Amount</th>
                            <th>Method</th>
                            <th>Received By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoice->payments as $index => $payment)
                            <tr class="{{ $payment->voided_at ? 'voided-row' : '' }}">
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <a href="{{ route('fees.collection.payments.show', $payment->id) }}">
                                        {{ $payment->receipt_number ?? 'N/A' }}
                                    </a>
                                    @if ($payment->voided_at)
                                        <span class="voided-badge">Voided</span>
                                    @endif
                                </td>
                                <td>{{ $payment->payment_date ? $payment->payment_date->format('d M Y') : '-' }}</td>
                                <td class="text-end amount-cell">{{ format_currency($payment->amount) }}</td>
                                <td>
                                    @php
                                        $methodClass =
                                            'method-' .
                                            str_replace(' ', '_', strtolower($payment->payment_method ?? 'cash'));
                                    @endphp
                                    <span
                                        class="payment-method-badge {{ $methodClass }}">{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'cash')) }}</span>
                                </td>
                                <td>{{ $payment->receivedBy->name ?? 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('fees.collection.payments.show', $payment->id) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="border-top: 2px solid #e5e7eb;">
                            <td colspan="3" class="text-end"><strong>Total Paid:</strong></td>
                            <td class="text-end amount-cell"><strong>{{ format_currency($invoice->amount_paid) }}</strong>
                            </td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif

        {{-- Notes Section --}}
        @if ($invoice->notes)
            <h3 class="section-title">Notes</h3>
            <div class="info-card">
                <p class="mb-0">{{ $invoice->notes }}</p>
            </div>
        @endif

        <div class="form-actions">
            <a class="btn btn-secondary" href="{{ route('fees.collection.invoices.index') }}">
                <i class="bx bx-arrow-back me-1"></i> Back to Invoices
            </a>
            <div class="d-flex gap-2">
                <a href="{{ route('fees.collection.students.account', $invoice->student_id) }}" class="btn btn-primary">
                    <i class="fas fa-user me-1"></i> View Student Account
                </a>
            </div>
        </div>
    </div>

    {{-- Recalculate Modal --}}
    @if (!in_array($invoice->status, ['cancelled', 'paid']))
        <div class="modal fade" id="recalculateModal" tabindex="-1" aria-labelledby="recalculateModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('fees.collection.invoices.recalculate', $invoice->id) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="recalculateModalLabel">Recalculate Invoice</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>This will recalculate invoice <strong>{{ $invoice->invoice_number }}</strong> with the
                                student's current discounts.</p>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Current discount on invoice:</strong>
                                {{ format_currency($invoice->discount_amount) }}
                            </div>
                            <p class="text-muted"><small>Use this after assigning or removing discounts to update the
                                    invoice totals.</small></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-warning btn-loading">
                                <span class="btn-text"><i class="fas fa-calculator me-1"></i> Recalculate</span>
                                <span class="btn-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status"
                                        aria-hidden="true"></span>
                                    Recalculating...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Cancel Modal --}}
    @if (!in_array($invoice->status, ['cancelled', 'paid']))
        <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('fees.collection.invoices.cancel', $invoice->id) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="cancelModalLabel">Cancel Invoice</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to cancel invoice <strong>{{ $invoice->invoice_number }}</strong>?</p>
                            <p class="text-danger"><small>This action cannot be undone. Any payments must be voided before
                                    cancellation.</small></p>
                            <div class="mb-3">
                                <label for="reason" class="form-label">Reason for Cancellation <span
                                        class="text-danger">*</span></label>
                                <textarea class="form-control" id="reason" name="reason" rows="3" required minlength="10"
                                    placeholder="Please provide a reason (minimum 10 characters)"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-danger btn-loading">
                                <span class="btn-text"><i class="fas fa-times me-1"></i> Cancel Invoice</span>
                                <span class="btn-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status"
                                        aria-hidden="true"></span>
                                    Cancelling...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection
@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeAlertDismissal();
            initializeBtnLoading();
        });

        function initializeAlertDismissal() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const dismissButton = alert.querySelector('.btn-close');
                    if (dismissButton) {
                        dismissButton.click();
                    }
                }, 5000);
            });
        }

        function initializeBtnLoading() {
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    const btn = this.querySelector('.btn-loading');
                    if (btn) {
                        btn.classList.add('loading');
                        btn.disabled = true;
                    }
                });
            });
        }
    </script>
@endsection
