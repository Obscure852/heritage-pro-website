@extends('layouts.master')
@section('title')
    Student Fee Account - {{ $student->full_name }}
@endsection
@section('css')
    <style>
        .fee-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .fee-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .fee-body {
            padding: 24px;
        }

        .student-info {
            margin-bottom: 8px;
        }

        .student-name {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .student-details {
            font-size: 14px;
            opacity: 0.9;
        }

        .balance-highlight {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 6px;
            padding: 16px;
            text-align: center;
        }

        .balance-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.9;
            margin-bottom: 4px;
        }

        .balance-amount {
            font-size: 28px;
            font-weight: 700;
        }

        .balance-status {
            font-size: 12px;
            margin-top: 4px;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px;
            border-left: 4px solid #3b82f6;
            border-radius: 0 3px 3px 0;
            margin-bottom: 20px;
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

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        @media (max-width: 992px) {
            .summary-cards {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .summary-cards {
                grid-template-columns: 1fr;
            }
        }

        .summary-card {
            background: #f9fafb;
            border-radius: 6px;
            padding: 16px;
            text-align: center;
        }

        .summary-card.balance-card {
            background: linear-gradient(135deg, #fef3c7 0%, #fcd34d 100%);
        }

        .summary-card.balance-card.no-balance {
            background: linear-gradient(135deg, #d1fae5 0%, #6ee7b7 100%);
        }

        .summary-card .card-value {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
        }

        .summary-card .card-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            margin-top: 4px;
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

        .action-buttons {
            display: flex;
            gap: 4px;
            justify-content: flex-end;
        }

        .action-buttons .btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .action-buttons .btn:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .action-buttons .btn i {
            font-size: 16px;
        }

        .amount-cell {
            font-weight: 600;
            color: #059669;
        }

        .balance-cell {
            font-weight: 600;
        }

        .balance-cell.has-balance {
            color: #dc2626;
        }

        .balance-cell.no-balance {
            color: #059669;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-paid { background: #d1fae5; color: #065f46; }
        .status-partial { background: #fef3c7; color: #92400e; }
        .status-outstanding { background: #fee2e2; color: #991b1b; }
        .status-overdue { background: #fca5a5; color: #7f1d1d; }
        .status-draft { background: #f3f4f6; color: #6b7280; }
        .status-cancelled { background: #e5e7eb; color: #9ca3af; text-decoration: line-through; }
        .status-issued { background: #fee2e2; color: #991b1b; }

        .year-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            background: #d1fae5;
            color: #065f46;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .invoice-link {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
        }

        .invoice-link:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }

        .payment-method-badge {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .method-cash { background: #d1fae5; color: #065f46; }
        .method-cheque { background: #dbeafe; color: #1e40af; }
        .method-bank_transfer { background: #e0e7ff; color: #3730a3; }
        .method-mobile_money { background: #fef3c7; color: #92400e; }
        .method-card { background: #f3e8ff; color: #6b21a8; }

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

        .header-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            .fee-header {
                padding: 20px;
            }

            .fee-body {
                padding: 16px;
            }

            .student-name {
                font-size: 20px;
            }

            .balance-amount {
                font-size: 22px;
            }

            .header-actions {
                margin-top: 16px;
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
            Student Account
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

    {{-- Year Filter - Top Right --}}
    <div class="row mb-3">
        <div class="col-9"></div>
        <div class="col-3 d-flex justify-content-end">
            <form method="GET" action="{{ route('fees.collection.students.account', $student->id) }}">
                <select name="year" class="form-select" onchange="this.form.submit()">
                    <option value="">All Years</option>
                    @foreach ($years ?? [] as $year)
                        <option value="{{ $year }}"
                            {{ ($selectedYear ?? '') == $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    <div class="fee-container">
        <div class="fee-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="student-info">
                        <div class="student-name">{{ $student->full_name }}</div>
                        <div class="student-details">
                            {{ $student->student_number ?? 'No Student #' }}
                            @if ($student->klass)
                                | {{ $student->klass->name }}
                            @endif
                            @if ($student->sponsor)
                                | Sponsor: {{ $student->sponsor->full_name }}
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="balance-highlight">
                        <div class="balance-label">Current Balance</div>
                        <div class="balance-amount">{{ format_currency($balance ?? 0) }}</div>
                        <div class="balance-status">
                            @if (($balance ?? 0) <= 0)
                                <i class="fas fa-check-circle"></i> All Paid
                            @else
                                <i class="fas fa-exclamation-circle"></i> Outstanding
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="fee-body">
            {{-- Help Text --}}
            <div class="help-text">
                <div class="help-title">Student Fee Account</div>
                <div class="help-content">
                    View this student's fee account summary, invoices, and payment history. Use the year filter above to view records for a specific year. Generate new invoices or record payments as needed.
                </div>
            </div>

            {{-- Action Button --}}
            <div class="mb-4 d-flex justify-content-end">
                <a href="{{ route('fees.collection.invoices.create', ['student_id' => $student->id]) }}" class="btn btn-success">
                    <i class="fas fa-file-invoice me-1"></i> Generate Invoice
                </a>
            </div>

            {{-- Account Summary --}}
            <div class="summary-cards">
                <div class="summary-card">
                    <div class="card-value">{{ format_currency($totalInvoiced ?? 0) }}</div>
                    <div class="card-label">Total Invoiced</div>
                </div>
                <div class="summary-card">
                    <div class="card-value">{{ format_currency($totalPaid ?? 0) }}</div>
                    <div class="card-label">Total Paid</div>
                </div>
                <div class="summary-card balance-card {{ ($balance ?? 0) <= 0 ? 'no-balance' : '' }}">
                    <div class="card-value">{{ format_currency($balance ?? 0) }}</div>
                    <div class="card-label">Current Balance</div>
                </div>
                <div class="summary-card">
                    <div class="card-value">
                        @php
                            $balanceValue = $balance ?? 0;
                            $accountStatus = $balanceValue <= 0 ? 'Paid' : ($balanceValue < ($totalInvoiced ?? 1) ? 'Partial' : 'Outstanding');
                            $statusClass = match($accountStatus) {
                                'Paid' => 'status-paid',
                                'Partial' => 'status-partial',
                                default => 'status-outstanding'
                            };
                        @endphp
                        <span class="status-badge {{ $statusClass }}">{{ $accountStatus }}</span>
                    </div>
                    <div class="card-label">Account Status</div>
                </div>
            </div>

            {{-- Invoices Table --}}
            <h3 class="section-title">Invoices</h3>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Year</th>
                            <th>Date</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Balance</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoices ?? [] as $invoice)
                            <tr>
                                <td>
                                    <a href="{{ route('fees.collection.invoices.show', $invoice->id) }}" class="invoice-link">
                                        {{ $invoice->invoice_number }}
                                    </a>
                                </td>
                                <td><span class="year-badge">{{ $invoice->year }}</span></td>
                                <td>{{ $invoice->issue_date ? $invoice->issue_date->format('d M Y') : '-' }}</td>
                                <td class="text-end amount-cell">{{ format_currency($invoice->total_amount) }}</td>
                                <td class="text-end">{{ format_currency($invoice->amount_paid) }}</td>
                                <td class="text-end balance-cell {{ $invoice->balance > 0 ? 'has-balance' : 'no-balance' }}">
                                    {{ format_currency($invoice->balance) }}
                                </td>
                                <td>
                                    @php
                                        $statusClass = match($invoice->status) {
                                            'paid' => 'status-paid',
                                            'partial' => 'status-partial',
                                            'issued' => 'status-outstanding',
                                            'overdue' => 'status-overdue',
                                            'draft' => 'status-draft',
                                            'cancelled' => 'status-cancelled',
                                            default => 'status-outstanding'
                                        };
                                    @endphp
                                    <span class="status-badge {{ $statusClass }}">{{ ucfirst($invoice->status) }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="action-buttons">
                                        <a href="{{ route('fees.collection.invoices.show', $invoice->id) }}"
                                            class="btn btn-sm btn-outline-info"
                                            title="View Invoice">
                                            <i class="bx bx-show"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted" style="padding: 40px 0;">
                                    <i class="fas fa-file-invoice" style="font-size: 32px; opacity: 0.3;"></i>
                                    <p class="mt-2 mb-0">No invoices found for this student</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Recent Payments Table --}}
            @if (isset($payments) && $payments->count() > 0)
                <h3 class="section-title">Recent Payments</h3>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Receipt #</th>
                                <th>Date</th>
                                <th>Invoice #</th>
                                <th class="text-end">Amount</th>
                                <th>Method</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($payments as $payment)
                                <tr>
                                    <td>{{ $payment->receipt_number ?? 'N/A' }}</td>
                                    <td>{{ $payment->payment_date ? $payment->payment_date->format('d M Y') : '-' }}</td>
                                    <td>
                                        @if ($payment->invoice)
                                            <a href="{{ route('fees.collection.invoices.show', $payment->invoice->id) }}" class="invoice-link">
                                                {{ $payment->invoice->invoice_number }}
                                            </a>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td class="text-end amount-cell">{{ format_currency($payment->amount) }}</td>
                                    <td>
                                        @php
                                            $methodClass = 'method-' . str_replace(' ', '_', strtolower($payment->payment_method ?? 'cash'));
                                        @endphp
                                        <span class="payment-method-badge {{ $methodClass }}">{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'cash')) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- Back Button --}}
            <div class="mt-4">
                <a href="{{ route('fees.collection.invoices.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Back to Invoices
                </a>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeAlertDismissal();
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
    </script>
@endsection
