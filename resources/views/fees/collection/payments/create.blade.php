@extends('layouts.master')
@section('title')
    Record Payment
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

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            margin-bottom: 0;
        }

        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #374151;
            font-size: 14px;
        }

        .form-control,
        .form-select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
            transition: all 0.2s;
        }

        .form-control:focus,
        .form-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .required {
            color: #dc2626;
        }

        .text-danger {
            color: #dc2626;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: space-between;
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

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
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

        /* Invoice Summary Card */
        .invoice-summary {
            background: #f9fafb;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .invoice-summary-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 16px;
            font-size: 15px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        @media (max-width: 768px) {
            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .summary-item {
            margin-bottom: 0;
        }

        .summary-label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .summary-value {
            font-size: 14px;
            font-weight: 500;
            color: #1f2937;
        }

        .balance-highlight {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border: 1px solid #fecaca;
            border-radius: 6px;
            padding: 16px;
            text-align: center;
        }

        .balance-highlight .summary-label {
            color: #991b1b;
        }

        .balance-highlight .summary-value {
            font-size: 24px;
            font-weight: 700;
            color: #dc2626;
        }

        /* Amount input with max info */
        .amount-input-wrapper {
            position: relative;
        }

        .max-amount-info {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }

        .max-amount-value {
            font-weight: 600;
            color: #059669;
        }

        /* Conditional field */
        .conditional-field {
            display: none;
        }

        .conditional-field.show {
            display: block;
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
            <a class="text-muted font-size-14" href="{{ route('fees.collection.invoices.show', $invoice->id) }}">Invoice {{ $invoice->invoice_number }}</a>
        @endslot
        @slot('title')
            Record Payment
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

    @if ($errors->any())
        <div class="row mb-3">
            <div class="col-md-12">
                @foreach ($errors->all() as $error)
                    <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        <i class="mdi mdi-block-helper label-icon"></i><strong>{{ $error }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="form-container">
        <div class="page-header">
            <h1 class="page-title">Record Payment</h1>
        </div>

        <div class="help-text">
            <div class="help-title">Recording a Payment</div>
            <div class="help-content">
                Record payment received against invoice {{ $invoice->invoice_number }}. The invoice balance will be updated automatically.
            </div>
        </div>

        {{-- Invoice Summary --}}
        <div class="invoice-summary">
            <div class="invoice-summary-title">Invoice Summary</div>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-label">Student</div>
                    <div class="summary-value">{{ $invoice->student->full_name ?? 'N/A' }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Student Number</div>
                    <div class="summary-value">{{ $invoice->student->student_number ?? 'N/A' }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Grade</div>
                    <div class="summary-value">{{ $invoice->student->klass->name ?? 'N/A' }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Invoice Number</div>
                    <div class="summary-value">{{ $invoice->invoice_number }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Year</div>
                    <div class="summary-value">{{ $invoice->year }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Due Date</div>
                    <div class="summary-value">{{ $invoice->due_date ? $invoice->due_date->format('d M Y') : '-' }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Total Amount</div>
                    <div class="summary-value">{{ format_currency($invoice->total_amount) }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Amount Paid</div>
                    <div class="summary-value">{{ format_currency($invoice->amount_paid) }}</div>
                </div>
            </div>
            <div class="mt-3">
                <div class="balance-highlight">
                    <div class="summary-label">Balance Due</div>
                    <div class="summary-value">{{ format_currency($invoice->balance) }}</div>
                </div>
            </div>
        </div>

        <h3 class="section-title">Payment Details</h3>

        <form class="needs-validation" method="POST" action="{{ route('fees.collection.payments.store') }}" novalidate id="recordPaymentForm">
            @csrf
            <input type="hidden" name="invoice_id" id="invoice_id" value="{{ $invoice->id }}">

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="amount">Amount (P) <span class="text-danger">*</span></label>
                    <div class="amount-input-wrapper">
                        <input type="number"
                            class="form-control @error('amount') is-invalid @enderror"
                            name="amount"
                            id="amount"
                            step="0.01"
                            min="0.01"
                            max="{{ $invoice->balance }}"
                            value="{{ old('amount') }}"
                            placeholder="Enter payment amount"
                            required>
                        <div class="max-amount-info">
                            Max: <span class="max-amount-value">{{ format_currency($invoice->balance) }}</span>
                        </div>
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="payment_method">Payment Method <span class="text-danger">*</span></label>
                    <select class="form-select @error('payment_method') is-invalid @enderror"
                        name="payment_method" id="payment_method" required>
                        <option value="">Select Payment Method</option>
                        @foreach ($paymentMethods ?? ['cash' => 'Cash', 'bank_transfer' => 'Bank Transfer', 'mobile_money' => 'Mobile Money', 'cheque' => 'Cheque'] as $value => $label)
                            <option value="{{ $value }}" {{ old('payment_method') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('payment_method')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="payment_date">Payment Date</label>
                    <input type="date"
                        class="form-control @error('payment_date') is-invalid @enderror"
                        name="payment_date"
                        id="payment_date"
                        max="{{ date('Y-m-d') }}"
                        value="{{ old('payment_date', date('Y-m-d')) }}">
                    <small class="text-muted">Defaults to today if not specified</small>
                    @error('payment_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="reference_number">Reference Number</label>
                    <input type="text"
                        class="form-control @error('reference_number') is-invalid @enderror"
                        name="reference_number"
                        id="reference_number"
                        value="{{ old('reference_number') }}"
                        placeholder="Transaction reference number">
                    @error('reference_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group conditional-field" id="chequeNumberField">
                    <label class="form-label" for="cheque_number">Cheque Number <span class="text-danger">*</span></label>
                    <input type="text"
                        class="form-control @error('cheque_number') is-invalid @enderror"
                        name="cheque_number"
                        id="cheque_number"
                        value="{{ old('cheque_number') }}"
                        placeholder="Enter cheque number">
                    @error('cheque_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="bank_name">Bank Name</label>
                    <input type="text"
                        class="form-control @error('bank_name') is-invalid @enderror"
                        name="bank_name"
                        id="bank_name"
                        value="{{ old('bank_name') }}"
                        placeholder="Bank name">
                    @error('bank_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group mt-3">
                <label class="form-label" for="notes">Notes</label>
                <textarea class="form-control @error('notes') is-invalid @enderror"
                    name="notes"
                    id="notes"
                    rows="3"
                    placeholder="Any additional notes about this payment">{{ old('notes') }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ route('fees.collection.invoices.show', $invoice->id) }}">
                    <i class="bx bx-arrow-back"></i> Back to Invoice
                </a>
                <button type="submit" class="btn btn-success btn-loading">
                    <span class="btn-text"><i class="fas fa-money-bill-wave"></i> Record Payment</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Recording...
                    </span>
                </button>
            </div>
        </form>
    </div>
@endsection
@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializePaymentMethodToggle();
            initializeFormValidation();
            initializeAlertDismissal();
        });

        const paymentMethodSelect = document.getElementById('payment_method');
        const chequeNumberField = document.getElementById('chequeNumberField');
        const chequeNumberInput = document.getElementById('cheque_number');

        function initializePaymentMethodToggle() {
            paymentMethodSelect.addEventListener('change', function() {
                toggleChequeField(this.value);
            });

            // Check initial value (for validation errors / old input)
            toggleChequeField(paymentMethodSelect.value);
        }

        function toggleChequeField(method) {
            if (method === 'cheque') {
                chequeNumberField.classList.add('show');
                chequeNumberInput.setAttribute('required', 'required');
            } else {
                chequeNumberField.classList.remove('show');
                chequeNumberInput.removeAttribute('required');
                chequeNumberInput.value = '';
            }
        }

        function initializeFormValidation() {
            const form = document.getElementById('recordPaymentForm');

            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();

                    const firstInvalidElement = form.querySelector(':invalid');
                    if (firstInvalidElement) {
                        firstInvalidElement.focus();
                        firstInvalidElement.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                } else {
                    // Show loading state
                    const submitBtn = form.querySelector('button[type="submit"].btn-loading');
                    if (submitBtn) {
                        submitBtn.classList.add('loading');
                        submitBtn.disabled = true;
                    }
                }

                form.classList.add('was-validated');
            }, false);
        }

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
