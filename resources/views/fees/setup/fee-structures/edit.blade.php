@extends('layouts.master')
@section('title')
    Edit Fee Structure
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

        .help-text.warning {
            border-left-color: #f59e0b;
            background: #fffbeb;
        }

        .help-text.warning .help-title {
            color: #92400e;
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

        .form-group.full-width {
            grid-column: 1 / -1;
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

        .form-control:disabled,
        .form-select:disabled {
            background-color: #f3f4f6;
            cursor: not-allowed;
            opacity: 0.7;
        }

        .required {
            color: #dc2626;
        }

        .text-danger {
            color: #dc2626;
        }

        .input-group-text {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            border-right: none;
            color: #374151;
            font-weight: 500;
        }

        .input-group .form-control {
            border-left: none;
        }

        .input-group .form-control:focus {
            border-left: none;
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

        .btn-primary:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
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

        .form-hint {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }

        .locked-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            background: #fef3c7;
            color: #92400e;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
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
            <a class="text-muted font-size-14" href="{{ route('fees.setup.structures.index') }}">Fee Structures</a>
        @endslot
        @slot('title')
            Edit Fee Structure
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

    @php
        $isHistorical = $feeStructure->year < date('Y');
    @endphp

    <div class="form-container">
        <div class="page-header">
            <h1 class="page-title">
                Edit Fee Structure
                @if ($isHistorical)
                    <span class="locked-badge ms-2">
                        <i class="fas fa-lock"></i> Historical Year
                    </span>
                @endif
            </h1>
        </div>

        @if ($isHistorical)
            <div class="help-text warning">
                <div class="help-title"><i class="fas fa-exclamation-triangle me-1"></i> Read-Only Mode</div>
                <div class="help-content">
                    This fee structure belongs to a historical year ({{ $feeStructure->year }}) and cannot be modified.
                    Historical fee structures are locked to maintain financial record integrity.
                    If you need to create a similar structure, use the "Copy Structures" feature from the index page.
                </div>
            </div>
        @else
            <div class="help-text">
                <div class="help-title">Update Fee Structure</div>
                <div class="help-content">
                    Modify the fee structure details below. Changes will affect all students assigned to this
                    grade and term combination. Be cautious when updating amounts for structures that may
                    already have invoices generated.
                </div>
            </div>
        @endif

        <form class="needs-validation" method="POST" action="{{ route('fees.setup.structures.update', $feeStructure->id) }}" novalidate>
            @csrf
            @method('PUT')

            <h3 class="section-title">Structure Details</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="fee_type_id">Fee Type <span class="text-danger">*</span></label>
                    <select class="form-select @error('fee_type_id') is-invalid @enderror"
                        name="fee_type_id" id="fee_type_id" required {{ $isHistorical ? 'disabled' : '' }}>
                        <option value="">Select Fee Type</option>
                        @foreach ($feeTypes ?? [] as $feeType)
                            <option value="{{ $feeType->id }}"
                                {{ old('fee_type_id', $feeStructure->fee_type_id) == $feeType->id ? 'selected' : '' }}>
                                {{ $feeType->name }} ({{ $feeType->code }})
                            </option>
                        @endforeach
                    </select>
                    @if ($isHistorical)
                        <input type="hidden" name="fee_type_id" value="{{ $feeStructure->fee_type_id }}">
                    @endif
                    @error('fee_type_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="grade_id">Grade <span class="text-danger">*</span></label>
                    <select class="form-select @error('grade_id') is-invalid @enderror"
                        name="grade_id" id="grade_id" required {{ $isHistorical ? 'disabled' : '' }}>
                        <option value="">Select Grade</option>
                        @foreach ($grades ?? [] as $grade)
                            <option value="{{ $grade->id }}"
                                {{ old('grade_id', $feeStructure->grade_id) == $grade->id ? 'selected' : '' }}>
                                {{ $grade->name }}
                            </option>
                        @endforeach
                    </select>
                    @if ($isHistorical)
                        <input type="hidden" name="grade_id" value="{{ $feeStructure->grade_id }}">
                    @endif
                    @error('grade_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-grid" style="margin-top: 16px;">
                <div class="form-group">
                    <label class="form-label" for="year">Year <span class="text-danger">*</span></label>
                    <select class="form-select @error('year') is-invalid @enderror"
                        name="year" id="year" required {{ $isHistorical ? 'disabled' : '' }}>
                        <option value="">Select Year</option>
                        @foreach ($years ?? [] as $year)
                            <option value="{{ $year }}"
                                {{ old('year', $feeStructure->year) == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                    @if ($isHistorical)
                        <input type="hidden" name="year" value="{{ $feeStructure->year }}">
                        <div class="form-hint">Historical year - cannot be changed</div>
                    @else
                        <div class="form-hint">Select the academic year for this fee structure</div>
                    @endif
                    @error('year')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <h3 class="section-title">Amount</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="amount">Amount <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">P</span>
                        <input type="number"
                            class="form-control @error('amount') is-invalid @enderror"
                            name="amount" id="amount"
                            placeholder="0.00"
                            value="{{ old('amount', number_format($feeStructure->amount, 2, '.', '')) }}"
                            min="0"
                            step="0.01"
                            required
                            {{ $isHistorical ? 'disabled' : '' }}>
                    </div>
                    @if ($isHistorical)
                        <input type="hidden" name="amount" value="{{ $feeStructure->amount }}">
                        <div class="form-hint">Historical year - amount cannot be changed</div>
                    @else
                        <div class="form-hint">Enter the fee amount in Pula (BWP)</div>
                    @endif
                    @error('amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ route('fees.setup.structures.index') }}">
                    <i class="bx bx-x"></i> {{ $isHistorical ? 'Back' : 'Cancel' }}
                </a>
                @if (!$isHistorical)
                    <button type="submit" class="btn btn-primary btn-loading">
                        <span class="btn-text"><i class="fas fa-save"></i> Update Fee Structure</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Updating...
                        </span>
                    </button>
                @else
                    <button type="button" class="btn btn-primary" disabled>
                        <i class="fas fa-lock"></i> Cannot Edit Historical Year
                    </button>
                @endif
            </div>
        </form>
    </div>
@endsection
@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeFormValidation();
            initializeAlertDismissal();
            initializeAmountFormat();
        });

        function initializeFormValidation() {
            const forms = document.querySelectorAll('.needs-validation');

            Array.prototype.slice.call(forms).forEach(function(form) {
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
                        const submitBtn = form.querySelector('button[type="submit"].btn-loading');
                        if (submitBtn) {
                            submitBtn.classList.add('loading');
                            submitBtn.disabled = true;
                        }
                    }

                    form.classList.add('was-validated');
                }, false);
            });
        }

        function initializeAmountFormat() {
            const amountInput = document.getElementById('amount');
            if (amountInput && !amountInput.disabled) {
                amountInput.addEventListener('blur', function(e) {
                    const value = parseFloat(e.target.value);
                    if (!isNaN(value)) {
                        e.target.value = value.toFixed(2);
                    }
                });

                amountInput.addEventListener('input', function(e) {
                    const value = parseFloat(e.target.value);
                    if (value < 0) {
                        e.target.value = 0;
                    }
                });
            }
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

        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
@endsection
