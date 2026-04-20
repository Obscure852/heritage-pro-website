@extends('layouts.master')
@section('title')
    Request Refund
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
            margin-bottom: 8px;
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
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            text-align: center;
        }

        .summary-item h4 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .summary-item small {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.9;
        }

        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }

        .form-control:focus,
        .form-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 20px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .btn-loading.loading .btn-text {
            display: none;
        }

        .btn-loading.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: space-between;
            padding-top: 24px;
            border-top: 1px solid #f3f4f6;
            margin-top: 32px;
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
            }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('fees.collection.payments.show', $payment) }}">
                Receipt {{ $payment->receipt_number }}
            </a>
        @endslot
        @slot('title')
            Request Refund
        @endslot
    @endcomponent

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
            <h1 class="page-title">Request Refund</h1>
        </div>

        <div class="help-text">
            <div class="help-title">Refund Request</div>
            <div class="help-content">
                Submit a refund request for this payment. The refund will require approval before it can be processed.
                You can request a full or partial refund, or issue a credit note to the student's account.
            </div>
        </div>

        {{-- Payment Summary --}}
        <h3 class="section-title">Original Payment</h3>
        <div class="summary-card">
            <div class="summary-grid">
                <div class="summary-item">
                    <h4>{{ format_currency($payment->amount) }}</h4>
                    <small>Original Amount</small>
                </div>
                <div class="summary-item">
                    <h4>{{ format_currency($payment->total_refunded) }}</h4>
                    <small>Already Refunded</small>
                </div>
                <div class="summary-item">
                    <h4>{{ format_currency($maxRefundAmount) }}</h4>
                    <small>Available to Refund</small>
                </div>
            </div>
        </div>

        {{-- Student & Payment Info --}}
        <div class="info-grid">
            <div class="info-card">
                <h6 class="mb-3" style="font-weight: 600; color: #374151;">Student Information</h6>
                <div class="info-item">
                    <div class="info-label">Student Name</div>
                    <div class="info-value">{{ $invoice->student->full_name ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Invoice Number</div>
                    <div class="info-value">{{ $invoice->invoice_number }}</div>
                </div>
            </div>
            <div class="info-card">
                <h6 class="mb-3" style="font-weight: 600; color: #374151;">Payment Details</h6>
                <div class="info-item">
                    <div class="info-label">Receipt Number</div>
                    <div class="info-value">{{ $payment->receipt_number }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Payment Date</div>
                    <div class="info-value">{{ $payment->payment_date->format('d M Y') }}</div>
                </div>
            </div>
        </div>

        {{-- Refund Form --}}
        <h3 class="section-title">Refund Details</h3>
        <form action="{{ route('fees.refunds.store') }}" method="POST" id="refundForm">
            @csrf
            <input type="hidden" name="payment_id" value="{{ $payment->id }}">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Refund Amount <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">{{ get_currency_symbol() }}</span>
                        <input type="number" class="form-control @error('amount') is-invalid @enderror"
                            name="amount" id="amount" step="0.01" min="0.01" max="{{ $maxRefundAmount }}"
                            value="{{ old('amount', $maxRefundAmount) }}" required>
                    </div>
                    <small class="text-muted">Maximum: {{ format_currency($maxRefundAmount) }}</small>
                    @error('amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Refund Method <span class="text-danger">*</span></label>
                    <select class="form-select @error('refund_method') is-invalid @enderror" name="refund_method" required>
                        <option value="">Select method...</option>
                        @foreach ($refundMethods as $value => $label)
                            <option value="{{ $value }}" {{ old('refund_method') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('refund_method')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Refund Date</label>
                    <input type="date" class="form-control @error('refund_date') is-invalid @enderror"
                        name="refund_date" value="{{ old('refund_date', date('Y-m-d')) }}">
                    @error('refund_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Reference Number</label>
                    <input type="text" class="form-control @error('reference_number') is-invalid @enderror"
                        name="reference_number" value="{{ old('reference_number') }}" placeholder="Transaction reference">
                    @error('reference_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Reason for Refund <span class="text-danger">*</span></label>
                <textarea class="form-control @error('reason') is-invalid @enderror" name="reason" rows="3"
                    required minlength="10" placeholder="Please provide a detailed reason (minimum 10 characters)">{{ old('reason') }}</textarea>
                @error('reason')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Additional Notes</label>
                <textarea class="form-control @error('notes') is-invalid @enderror" name="notes" rows="2"
                    placeholder="Optional notes">{{ old('notes') }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ route('fees.collection.payments.show', $payment) }}">
                    <i class="bx bx-arrow-back me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary btn-loading">
                    <span class="btn-text"><i class="fas fa-paper-plane me-1"></i> Submit Refund Request</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2"></span>Submitting...
                    </span>
                </button>
            </div>
        </form>
    </div>
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
            const form = document.getElementById('refundForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const btn = this.querySelector('.btn-loading');
                    if (btn) {
                        btn.classList.add('loading');
                        btn.disabled = true;
                    }
                });
            }
        }
    </script>
@endsection
