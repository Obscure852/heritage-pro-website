@extends('layouts.master')
@section('title')
    Receipt {{ $payment->receipt_number }}
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

        .info-value a {
            color: #3b82f6;
            text-decoration: none;
        }

        .info-value a:hover {
            text-decoration: underline;
        }

        .summary-card {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            text-align: center;
        }

        @media (max-width: 992px) {
            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
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

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-active { background: #d1fae5; color: #065f46; }
        .status-voided { background: #fee2e2; color: #991b1b; text-decoration: line-through; }

        .payment-method-badge {
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .method-cash { background: #d1fae5; color: #065f46; }
        .method-cheque { background: #dbeafe; color: #1e40af; }
        .method-bank_transfer { background: #e0e7ff; color: #3730a3; }
        .method-mobile_money { background: #fef3c7; color: #92400e; }
        .method-card { background: #f3e8ff; color: #6b21a8; }

        .void-card {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .void-card .section-title {
            color: #991b1b;
            border-bottom-color: #fecaca;
        }

        .void-reason {
            background: #fee2e2;
            padding: 12px;
            border-radius: 4px;
            color: #991b1b;
            font-weight: 500;
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
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-1px);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border: none;
            color: white;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
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
        @slot('li_2')
            <a class="text-muted font-size-14" href="{{ route('fees.collection.invoices.show', $payment->student_invoice_id) }}">Invoice {{ $payment->invoice->invoice_number ?? '' }}</a>
        @endslot
        @slot('title')
            Receipt {{ $payment->receipt_number }}
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
                Receipt {{ $payment->receipt_number }}
                @if ($payment->voided_at)
                    <span class="status-badge status-voided">Voided</span>
                @else
                    <span class="status-badge status-active">Active</span>
                @endif
            </h1>
            <div class="header-actions">
                <a href="{{ route('fees.collection.payments.receipt', $payment->id) }}" class="btn btn-primary" target="_blank">
                    <i class="fas fa-print me-1"></i> Print Receipt
                </a>
                @can('view-fee-reports')
                    <a href="{{ route('fees.audit.payment-history', $payment) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-history me-1"></i> Audit History
                    </a>
                @endcan
                @if (!$payment->voided_at)
                    @can('request-refunds')
                        @if ($payment->canBeRefunded())
                            <a href="{{ route('fees.refunds.create', $payment) }}" class="btn btn-outline-warning">
                                <i class="fas fa-undo me-1"></i> Request Refund
                            </a>
                        @endif
                    @endcan
                    @can('void-payments')
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#voidModal">
                            <i class="fas fa-ban me-1"></i> Void Payment
                        </button>
                    @endcan
                @endif
            </div>
        </div>

        {{-- Student & Payment Info --}}
        <div class="info-grid">
            <div class="info-card">
                <h6 class="mb-3" style="font-weight: 600; color: #374151;">Student Information</h6>
                <div class="info-item">
                    <div class="info-label">Student Name</div>
                    <div class="info-value">{{ $payment->invoice->student->full_name ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Student Number</div>
                    <div class="info-value">{{ $payment->invoice->student->student_number ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Grade</div>
                    <div class="info-value">{{ $payment->invoice->student->klass->name ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Sponsor/Parent</div>
                    <div class="info-value">{{ $payment->invoice->student->sponsor->full_name ?? 'N/A' }}</div>
                </div>
            </div>

            <div class="info-card">
                <h6 class="mb-3" style="font-weight: 600; color: #374151;">Payment Details</h6>
                <div class="info-item">
                    <div class="info-label">Receipt Number</div>
                    <div class="info-value">{{ $payment->receipt_number }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Invoice Number</div>
                    <div class="info-value">
                        <a href="{{ route('fees.collection.invoices.show', $payment->student_invoice_id) }}">
                            {{ $payment->invoice->invoice_number ?? 'N/A' }}
                        </a>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Payment Date</div>
                    <div class="info-value">{{ $payment->payment_date ? $payment->payment_date->format('d M Y') : '-' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Payment Method</div>
                    <div class="info-value">
                        @php
                            $methodClass = 'method-' . str_replace(' ', '_', strtolower($payment->payment_method ?? 'cash'));
                        @endphp
                        <span class="payment-method-badge {{ $methodClass }}">{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'cash')) }}</span>
                    </div>
                </div>
                @if ($payment->reference_number)
                    <div class="info-item">
                        <div class="info-label">Reference Number</div>
                        <div class="info-value">{{ $payment->reference_number }}</div>
                    </div>
                @endif
                <div class="info-item">
                    <div class="info-label">Received By</div>
                    <div class="info-value">{{ $payment->receivedBy->name ?? 'N/A' }}</div>
                </div>
            </div>
        </div>

        {{-- Amount Summary --}}
        <div class="summary-card">
            <div class="summary-grid">
                <div class="summary-item">
                    <h4>{{ format_currency($payment->amount) }}</h4>
                    <small>Amount Paid</small>
                </div>
                <div class="summary-item">
                    <h4>{{ format_currency($payment->invoice->total_amount ?? 0) }}</h4>
                    <small>Invoice Total</small>
                </div>
                <div class="summary-item">
                    @php
                        $previousPayments = ($payment->invoice->amount_paid ?? 0) - $payment->amount;
                        if ($payment->voided_at) {
                            // If voided, the invoice amount_paid already excludes this payment
                            $previousPayments = $payment->invoice->amount_paid ?? 0;
                        }
                    @endphp
                    <h4>{{ format_currency(max(0, $previousPayments)) }}</h4>
                    <small>Previous Payments</small>
                </div>
                <div class="summary-item">
                    <h4>{{ format_currency($payment->invoice->balance ?? 0) }}</h4>
                    <small>Remaining Balance</small>
                </div>
            </div>
        </div>

        {{-- Void Information --}}
        @if ($payment->voided_at)
            <div class="void-card">
                <h3 class="section-title">Void Information</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Voided At</div>
                        <div class="info-value">{{ $payment->voided_at->format('d M Y H:i') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Voided By</div>
                        <div class="info-value">{{ $payment->voidedBy->name ?? 'N/A' }}</div>
                    </div>
                </div>
                <div class="info-item mt-3">
                    <div class="info-label">Reason</div>
                    <div class="void-reason">{{ $payment->void_reason }}</div>
                </div>
            </div>
        @endif

        {{-- Cheque Details --}}
        @if ($payment->payment_method === 'cheque' && ($payment->cheque_number || $payment->bank_name))
            <h3 class="section-title">Cheque Details</h3>
            <div class="info-card">
                <div class="info-grid">
                    @if ($payment->cheque_number)
                        <div class="info-item">
                            <div class="info-label">Cheque Number</div>
                            <div class="info-value">{{ $payment->cheque_number }}</div>
                        </div>
                    @endif
                    @if ($payment->bank_name)
                        <div class="info-item">
                            <div class="info-label">Bank Name</div>
                            <div class="info-value">{{ $payment->bank_name }}</div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Notes --}}
        @if ($payment->notes)
            <h3 class="section-title">Notes</h3>
            <div class="info-card">
                <p class="mb-0">{{ $payment->notes }}</p>
            </div>
        @endif

        <div class="form-actions">
            <a class="btn btn-secondary" href="{{ route('fees.collection.invoices.show', $payment->student_invoice_id) }}">
                <i class="bx bx-arrow-back me-1"></i> Back to Invoice
            </a>
            <div class="d-flex gap-2">
                <a href="{{ route('fees.collection.students.account', $payment->invoice->student_id ?? 0) }}" class="btn btn-primary">
                    <i class="fas fa-user me-1"></i> View Student Account
                </a>
            </div>
        </div>
    </div>

    {{-- Void Modal --}}
    @if (!$payment->voided_at)
        @can('void-payments')
            <div class="modal fade" id="voidModal" tabindex="-1" aria-labelledby="voidModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('fees.collection.payments.void', $payment->id) }}" method="POST">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="voidModalLabel">Void Payment</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to void receipt <strong>{{ $payment->receipt_number }}</strong>?</p>
                                <p class="text-danger"><small>This action will restore the payment amount ({{ format_currency($payment->amount) }}) to the invoice balance. This action cannot be undone.</small></p>
                                <div class="mb-3">
                                    <label for="reason" class="form-label">Reason for Voiding <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="reason" name="reason" rows="3"
                                        required minlength="10" placeholder="Please provide a reason (minimum 10 characters)"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger btn-loading">
                                    <span class="btn-text"><i class="fas fa-ban me-1"></i> Void Payment</span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        Voiding...
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcan
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
