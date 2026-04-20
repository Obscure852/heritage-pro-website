@extends('layouts.master')
@section('title')
    Create Discount Type
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

        .required {
            color: #dc2626;
        }

        .text-danger {
            color: #dc2626;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px;
            background: #f9fafb;
            border-radius: 3px;
            margin-top: 8px;
        }

        .form-check-input {
            width: 18px;
            height: 18px;
            margin: 0;
            cursor: pointer;
        }

        .form-check-label {
            font-size: 14px;
            color: #374151;
            cursor: pointer;
        }

        .form-check-description {
            font-size: 12px;
            color: #6b7280;
            margin-top: 2px;
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

        .field-hint {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
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
            <a class="text-muted font-size-14" href="{{ route('fees.setup.discount-types.index') }}">Discount Types</a>
        @endslot
        @slot('title')
            Create Discount Type
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
            <h1 class="page-title">Create Discount Type</h1>
        </div>

        <div class="help-text">
            <div class="help-title">Discount Type Information</div>
            <div class="help-content">
                Define a new discount type with a unique code and percentage. Discount types can be assigned
                to students to reduce their fee amounts. Choose whether the discount applies to all fees
                or only tuition fees.
            </div>
        </div>

        <form class="needs-validation" method="POST" action="{{ route('fees.setup.discount-types.store') }}" novalidate>
            @csrf

            <h3 class="section-title">Basic Information</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="code">Code <span class="text-danger">*</span></label>
                    <input type="text"
                        class="form-control @error('code') is-invalid @enderror"
                        name="code" id="code"
                        placeholder="e.g., SIBLING"
                        value="{{ old('code') }}"
                        maxlength="20"
                        required>
                    <div class="field-hint">A unique identifier for this discount type (e.g., SIBLING, STAFF, SCHOL-50)</div>
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="name">Name <span class="text-danger">*</span></label>
                    <input type="text"
                        class="form-control @error('name') is-invalid @enderror"
                        name="name" id="name"
                        placeholder="e.g., Sibling Discount"
                        value="{{ old('name') }}"
                        maxlength="100"
                        required>
                    <div class="field-hint">A descriptive name for this discount type</div>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-grid" style="margin-top: 16px;">
                <div class="form-group">
                    <label class="form-label" for="percentage">Percentage <span class="text-danger">*</span></label>
                    <input type="number"
                        class="form-control @error('percentage') is-invalid @enderror"
                        name="percentage" id="percentage"
                        placeholder="e.g., 10.00"
                        value="{{ old('percentage') }}"
                        min="0"
                        max="100"
                        step="0.01"
                        required>
                    <div class="field-hint">The percentage discount to apply (0-100)</div>
                    @error('percentage')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="applies_to">Applies To <span class="text-danger">*</span></label>
                    <select class="form-select @error('applies_to') is-invalid @enderror"
                        name="applies_to" id="applies_to" required>
                        <option value="">Select...</option>
                        @foreach ($appliesOptions as $value => $label)
                            <option value="{{ $value }}" {{ old('applies_to') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="field-hint">Choose whether this discount applies to all fees or only tuition</div>
                    @error('applies_to')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-grid" style="margin-top: 16px;">
                <div class="form-group full-width">
                    <label class="form-label" for="description">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror"
                        name="description" id="description"
                        placeholder="Brief description of this discount type"
                        rows="3">{{ old('description') }}</textarea>
                    <div class="field-hint">Optional description explaining when this discount should be applied</div>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <h3 class="section-title">Settings</h3>
            <div class="form-grid">
                <div class="form-group">
                    <div class="form-check">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox"
                            class="form-check-input"
                            name="is_active"
                            id="is_active"
                            value="1"
                            {{ old('is_active', true) ? 'checked' : '' }}>
                        <div>
                            <label class="form-check-label" for="is_active">Active</label>
                            <div class="form-check-description">Discount type can be assigned to students</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ route('fees.setup.discount-types.index') }}">
                    <i class="bx bx-x"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary btn-loading">
                    <span class="btn-text"><i class="fas fa-save"></i> Create Discount Type</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Creating...
                    </span>
                </button>
            </div>
        </form>
    </div>
@endsection
@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeFormValidation();
            initializeAlertDismissal();
            initializeCodeFormat();
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

        function initializeCodeFormat() {
            const codeInput = document.getElementById('code');
            if (codeInput) {
                codeInput.addEventListener('input', function(e) {
                    e.target.value = e.target.value.toUpperCase().replace(/[^A-Z0-9-]/g, '');
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
