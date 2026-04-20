@extends('layouts.master')
@section('title')
    Payment Plan: {{ $paymentPlan->name ?? 'Plan #'.$paymentPlan->id }}
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
            align-items: flex-start;
            margin-bottom: 24px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        .page-title {
            font-size: 22px;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }

        .page-subtitle {
            font-size: 14px;
            color: #6b7280;
            margin-top: 4px;
        }

        .status-badge {
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .status-active { background: #dbeafe; color: #1e40af; }
        .status-completed { background: #d1fae5; color: #065f46; }
        .status-cancelled { background: #e5e7eb; color: #374151; }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin: 24px 0 16px 0;
            color: #1f2937;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 24px;
        }

        @media (max-width: 992px) {
            .info-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
        }

        .info-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 20px;
        }

        .info-card h6 {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .info-card p {
            margin-bottom: 8px;
            font-size: 14px;
            color: #374151;
        }

        .info-card p:last-child {
            margin-bottom: 0;
        }

        .info-card strong {
            color: #6b7280;
            font-weight: 500;
        }

        .progress-card {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border: 1px solid #93c5fd;
            text-align: center;
        }

        .progress-circle {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: conic-gradient(#3b82f6 var(--progress), #e5e7eb var(--progress));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
        }

        .progress-circle-inner {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }

        .progress-circle-inner .percentage {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
        }

        .progress-circle-inner .label {
            font-size: 12px;
            color: #6b7280;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
            padding: 12px;
        }

        .table tbody td {
            padding: 12px;
            vertical-align: middle;
            font-size: 14px;
        }

        .table tbody tr:hover {
            background-color: #f9fafb;
        }

        .installment-pending { background: #f9fafb; }
        .installment-partial { background: #fef3c7; }
        .installment-paid { background: #d1fae5; }
        .installment-overdue { background: #fee2e2; }

        .cancelled-info {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 20px;
            margin-top: 24px;
        }

        .cancelled-info h6 {
            color: #374151;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            padding-top: 24px;
            border-top: 1px solid #f3f4f6;
            margin-top: 32px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
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
            color: white;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
            color: white;
        }

        .btn-outline-primary {
            background: transparent;
            border: 1px solid #3b82f6;
            color: #3b82f6;
        }

        .btn-outline-primary:hover {
            background: #3b82f6;
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

        .meta-info {
            font-size: 13px;
            color: #6b7280;
            margin-top: 16px;
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
            }

            .page-header {
                flex-direction: column;
                gap: 12px;
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
            <a class="text-muted font-size-14" href="{{ route('fees.collection.invoices.index') }}">Fee Administration</a>
        @endslot
        @slot('title')
            Payment Plan
        @endslot
    @endcomponent

    @if(session('message'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
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
            <div>
                <h1 class="page-title">{{ $paymentPlan->student->full_name ?? 'N/A' }}</h1>
                <p class="page-subtitle">{{ $paymentPlan->name ?? 'Payment Plan' }} &bull; {{ $paymentPlan->invoice->invoice_number ?? 'N/A' }}</p>
            </div>
            <span class="status-badge status-{{ $paymentPlan->status }}">
                {{ ucfirst($paymentPlan->status) }}
            </span>
        </div>

        <!-- Info Cards -->
        <div class="info-grid">
            <!-- Progress Card -->
            <div class="info-card progress-card">
                @php
                    $progress = $paymentPlan->total_amount > 0
                        ? round(($paymentPlan->total_paid / $paymentPlan->total_amount) * 100)
                        : 0;
                @endphp
                <div class="progress-circle" style="--progress: {{ $progress * 3.6 }}deg">
                    <div class="progress-circle-inner">
                        <span class="percentage">{{ $progress }}%</span>
                        <span class="label">Paid</span>
                    </div>
                </div>
                <p class="mb-1"><strong>P{{ number_format($paymentPlan->total_paid, 2) }}</strong> of P{{ number_format($paymentPlan->total_amount, 2) }}</p>
                <p class="mb-0 text-muted">Remaining: <strong class="text-danger">P{{ number_format($paymentPlan->remaining_balance, 2) }}</strong></p>
            </div>

            <!-- Plan Details Card -->
            <div class="info-card">
                <h6><i class="fas fa-calendar-alt me-2"></i>Plan Details</h6>
                <p><strong>Year:</strong> {{ $paymentPlan->year }}</p>
                <p><strong>Frequency:</strong> {{ \App\Models\Fee\PaymentPlan::frequencies()[$paymentPlan->frequency] ?? $paymentPlan->frequency }}</p>
                <p><strong>Installments:</strong> {{ $paymentPlan->number_of_installments }}</p>
                <p><strong>Start Date:</strong> {{ $paymentPlan->start_date->format('d M Y') }}</p>
            </div>

            <!-- Invoice Details Card -->
            <div class="info-card">
                <h6><i class="fas fa-file-invoice me-2"></i>Invoice Details</h6>
                <p><strong>Student:</strong> {{ $paymentPlan->student->full_name ?? 'N/A' }}</p>
                <p><strong>Student #:</strong> {{ $paymentPlan->student->student_number ?? '-' }}</p>
                <p>
                    <strong>Invoice #:</strong>
                    <a href="{{ route('fees.collection.invoices.show', $paymentPlan->student_invoice_id) }}">
                        {{ $paymentPlan->invoice->invoice_number ?? 'N/A' }}
                    </a>
                </p>
                <p><strong>Created By:</strong> {{ $paymentPlan->createdBy->full_name ?? 'System' }}</p>
            </div>
        </div>

        <!-- Installments Table -->
        <h3 class="section-title"><i class="fas fa-list-ol me-2"></i>Installment Schedule</h3>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Due Date</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Paid</th>
                        <th class="text-end">Balance</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($paymentPlan->installments as $installment)
                        <tr class="installment-{{ $installment->status }}">
                            <td><strong>{{ $installment->installment_number }}</strong></td>
                            <td>
                                {{ $installment->due_date->format('d M Y') }}
                                @if($installment->is_overdue && $installment->status !== 'paid')
                                    <span class="badge bg-danger ms-1">Overdue</span>
                                @endif
                            </td>
                            <td class="text-end">P{{ number_format($installment->amount, 2) }}</td>
                            <td class="text-end">P{{ number_format($installment->amount_paid, 2) }}</td>
                            <td class="text-end fw-semibold {{ $installment->balance > 0 ? 'text-danger' : 'text-success' }}">
                                P{{ number_format($installment->balance, 2) }}
                            </td>
                            <td>
                                <span class="badge bg-{{ $installment->status_color }}">
                                    {{ $installment->status_label }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($paymentPlan->isCancelled())
            <div class="cancelled-info">
                <h6><i class="fas fa-ban me-2"></i>Plan Cancelled</h6>
                <p class="mb-1"><strong>Cancelled by:</strong> {{ $paymentPlan->cancelledBy->full_name ?? 'System' }}</p>
                <p class="mb-1"><strong>Cancelled at:</strong> {{ $paymentPlan->cancelled_at?->format('d M Y H:i') ?? '-' }}</p>
                <p class="mb-0"><strong>Reason:</strong> {{ $paymentPlan->cancellation_reason ?? 'No reason provided' }}</p>
            </div>
        @endif

        <div class="meta-info">
            Created on {{ $paymentPlan->created_at->format('d M Y') }} at {{ $paymentPlan->created_at->format('H:i') }}
        </div>

        <div class="form-actions">
            <a class="btn btn-secondary" href="{{ route('fees.collection.invoices.show', $paymentPlan->student_invoice_id) }}">
                <i class="bx bx-arrow-back"></i> Back to Invoice
            </a>
            @if($paymentPlan->isActive())
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                    <i class="fas fa-times"></i> Cancel Plan
                </button>
            @endif
        </div>
    </div>

    <!-- Cancel Modal -->
    @if($paymentPlan->isActive())
        <div class="modal fade" id="cancelModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Cancel Payment Plan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="{{ route('fees.payment-plans.cancel', $paymentPlan) }}" id="cancelForm">
                        @csrf
                        <div class="modal-body">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                This will cancel the payment plan. Payments already made will remain recorded against the invoice.
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Reason for Cancellation <span class="text-danger">*</span></label>
                                <textarea name="reason" class="form-control" rows="3" required
                                          placeholder="Please provide a reason..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bx bx-x"></i> Close
                            </button>
                            <button type="submit" class="btn btn-danger btn-loading">
                                <span class="btn-text"><i class="fas fa-times"></i> Cancel Plan</span>
                                <span class="btn-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Processing...
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
            // Auto-dismiss alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const dismissButton = alert.querySelector('.btn-close');
                    if (dismissButton) {
                        dismissButton.click();
                    }
                }, 5000);
            });

            // Cancel form loading state
            const cancelForm = document.getElementById('cancelForm');
            if (cancelForm) {
                cancelForm.addEventListener('submit', function(event) {
                    const submitBtn = cancelForm.querySelector('button[type="submit"].btn-loading');
                    if (submitBtn) {
                        submitBtn.classList.add('loading');
                        submitBtn.disabled = true;
                    }
                });
            }
        });
    </script>
@endsection
