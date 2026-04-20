@extends('layouts.master')
@section('title')
    Invoices
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

        .stat-item {
            padding: 10px 0;
        }

        .stat-item h4 {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .stat-item small {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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

        .action-buttons .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .action-buttons .btn i {
            font-size: 16px;
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

        @media (max-width: 768px) {
            .stat-item h4 {
                font-size: 1.25rem;
            }

            .stat-item small {
                font-size: 0.75rem;
            }

            .fee-header {
                padding: 20px;
            }

            .fee-body {
                padding: 16px;
            }

            .header-buttons {
                flex-direction: column;
                gap: 8px;
            }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="javascript:history.back()">Back</a>
        @endslot
        @slot('title')
            Fee Administration
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

    <div class="fee-container">
        <div class="fee-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;">Invoices</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">View and manage student fee invoices</p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $totals['count'] }}</h4>
                                <small class="opacity-75">Total Invoices</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ format_currency($totals['amount'], 0) }}</h4>
                                <small class="opacity-75">Total Amount</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ format_currency($totals['outstanding'], 0) }}</h4>
                                <small class="opacity-75">Outstanding</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="fee-body">
            <div class="help-text">
                <div class="help-title">Invoice Management</div>
                <div class="help-content">
                    View all student invoices, filter by year or status, and manage invoice lifecycle.
                    Use "Generate Invoice" for individual students or "Bulk Generate" for entire grades.
                    Click on an invoice number to view details and record payments.
                </div>
            </div>

            <form method="GET" action="{{ route('fees.collection.invoices.index') }}" id="filterForm">
                <div class="row align-items-center mb-3">
                    <div class="col-lg-8 col-md-12">
                        <div class="controls">
                            <div class="row g-2 align-items-center">
                                <div class="col-lg-3 col-md-3 col-sm-6">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        <input type="text" class="form-control" placeholder="Invoice # or student..."
                                            id="searchInput" name="search" value="{{ $filters['search'] ?? '' }}">
                                    </div>
                                </div>
                                <div class="col-lg-2 col-md-2 col-sm-6">
                                    <select class="form-select" id="yearFilter" name="year">
                                        <option value="">All Years</option>
                                        @foreach ($years ?? [] as $year)
                                            <option value="{{ $year }}"
                                                {{ ($filters['year'] ?? '') == $year ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-2 col-sm-6">
                                    <select class="form-select" id="statusFilter" name="status">
                                        <option value="">All Status</option>
                                        <option value="draft"
                                            {{ ($filters['status'] ?? '') == 'draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="issued"
                                            {{ ($filters['status'] ?? '') == 'issued' ? 'selected' : '' }}>Issued</option>
                                        <option value="partial"
                                            {{ ($filters['status'] ?? '') == 'partial' ? 'selected' : '' }}>Partial
                                        </option>
                                        <option value="paid"
                                            {{ ($filters['status'] ?? '') == 'paid' ? 'selected' : '' }}>Paid</option>
                                        <option value="overdue"
                                            {{ ($filters['status'] ?? '') == 'overdue' ? 'selected' : '' }}>Overdue
                                        </option>
                                        <option value="cancelled"
                                            {{ ($filters['status'] ?? '') == 'cancelled' ? 'selected' : '' }}>Cancelled
                                        </option>
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-2 col-sm-6">
                                    <button type="submit" class="btn btn-light w-100">
                                        <i class="fas fa-filter me-1"></i> Filter
                                    </button>
                                </div>
                                <div class="col-lg-2 col-md-2 col-sm-6">
                                    <a href="{{ route('fees.collection.invoices.index') }}"
                                        class="btn btn-outline-secondary w-100">Reset</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                        <div class="d-flex flex-wrap align-items-center justify-content-end gap-2 header-buttons">
                            <a href="{{ route('fees.collection.invoices.bulk') }}" class="btn btn-success">
                                <i class="fas fa-layer-group me-1"></i> Bulk Generate
                            </a>
                            <a href="{{ route('fees.collection.invoices.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> Generate Invoice
                            </a>
                        </div>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table id="invoicesTable" class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Student Name</th>
                            <th>Year</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Balance</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoices as $invoice)
                            <tr class="invoice-row">
                                <td>
                                    <a href="{{ route('fees.collection.invoices.show', $invoice->id) }}"
                                        class="invoice-link">
                                        {{ $invoice->invoice_number }}
                                    </a>
                                </td>
                                <td>
                                    {{ $invoice->student->full_name ?? 'N/A' }}
                                    @if ($invoice->student->id_number ?? false)
                                        <br><small class="text-muted">{{ $invoice->student->formatted_id_number }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="year-badge">{{ $invoice->year }}</span>
                                </td>
                                <td class="text-end amount-cell">{{ format_currency($invoice->total_amount) }}</td>
                                <td class="text-end">{{ format_currency($invoice->amount_paid) }}</td>
                                <td
                                    class="text-end balance-cell {{ $invoice->balance > 0 ? 'has-balance' : 'no-balance' }}">
                                    {{ format_currency($invoice->balance) }}
                                </td>
                                <td>
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
                                </td>
                                <td>{{ $invoice->issued_at ? $invoice->issued_at->format('d M Y') : '-' }}</td>
                                <td class="text-end">
                                    <div class="action-buttons">
                                        <a href="{{ route('fees.collection.invoices.show', $invoice->id) }}"
                                            class="btn btn-sm btn-outline-info" title="View Invoice">
                                            <i class="bx bx-show"></i>
                                        </a>
                                        @if (!in_array($invoice->status, ['cancelled', 'paid']))
                                            @can('manage-fee-setup')
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    title="Cancel Invoice" data-bs-toggle="modal"
                                                    data-bs-target="#cancelModal{{ $invoice->id }}">
                                                    <i class="bx bx-x"></i>
                                                </button>
                                            @endcan
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="no-invoices-row">
                                <td colspan="9">
                                    <div class="text-center text-muted" style="padding: 40px 0;">
                                        <i class="fas fa-file-invoice" style="font-size: 48px; opacity: 0.3;"></i>
                                        <p class="mt-3 mb-0" style="font-size: 15px;">No Invoices Found</p>
                                        <p class="text-muted" style="font-size: 13px;">Generate invoices for students to
                                            get started</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($invoices->hasPages())
                <div class="d-flex justify-content-end mt-4">
                    {{ $invoices->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Cancel Modals --}}
    @foreach ($invoices as $invoice)
        @if (!in_array($invoice->status, ['cancelled', 'paid']))
            <div class="modal fade" id="cancelModal{{ $invoice->id }}" tabindex="-1"
                aria-labelledby="cancelModalLabel{{ $invoice->id }}" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('fees.collection.invoices.cancel', $invoice->id) }}" method="POST">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="cancelModalLabel{{ $invoice->id }}">Cancel Invoice</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to cancel invoice <strong>{{ $invoice->invoice_number }}</strong>?
                                </p>
                                <p class="text-danger"><small>This action cannot be undone. Any payments must be voided
                                        before cancellation.</small></p>
                                <div class="mb-3">
                                    <label for="reason{{ $invoice->id }}" class="form-label">Reason for Cancellation
                                        <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="reason{{ $invoice->id }}" name="reason" rows="3" required
                                        minlength="10" placeholder="Please provide a reason (minimum 10 characters)"></textarea>
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
    @endforeach
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
