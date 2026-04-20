@extends('layouts.master')
@section('title')
    New Leave Type
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

        .section-title:first-of-type {
            margin-top: 0;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        @media (max-width: 992px) {
            .form-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
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

        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 0;
        }

        .form-check-input {
            width: 18px;
            height: 18px;
            margin: 0;
        }

        .form-check-label {
            font-size: 14px;
            color: #374151;
            margin: 0;
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

        .color-preview {
            width: 40px;
            height: 38px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            cursor: pointer;
        }

        .color-input-group {
            display: flex;
            gap: 8px;
        }

        .color-input-group .form-control {
            flex: 1;
        }

        .form-text {
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
            <a class="text-muted font-size-14" href="{{ route('leave.settings.index') . '#leaveTypes' }}">Leave Settings</a>
        @endslot
        @slot('title')
            New Leave Type
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
            <h1 class="page-title">New Leave Type</h1>
        </div>

        <div class="help-text">
            <div class="help-title">Create Leave Type</div>
            <div class="help-content">
                Define a new leave type with its entitlement settings, documentation requirements, and restrictions.
                Fields marked with <span class="text-danger">*</span> are required.
            </div>
        </div>

        <form class="needs-validation" method="post" action="{{ route('leave.types.store') }}" novalidate>
            @csrf

            <h3 class="section-title">Basic Information</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="code">Code <span class="text-danger">*</span></label>
                    <input type="text"
                        class="form-control @error('code') is-invalid @enderror"
                        name="code" id="code" placeholder="e.g., ANNUAL"
                        value="{{ old('code') }}" required maxlength="20">
                    <div class="form-text">Unique identifier (max 20 characters)</div>
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="name">Name <span class="text-danger">*</span></label>
                    <input type="text"
                        class="form-control @error('name') is-invalid @enderror"
                        name="name" id="name" placeholder="e.g., Annual Leave"
                        value="{{ old('name') }}" required maxlength="100">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="description">Description</label>
                    <input type="text"
                        class="form-control @error('description') is-invalid @enderror"
                        name="description" id="description"
                        placeholder="Brief description of this leave type"
                        value="{{ old('description') }}" maxlength="500">
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <h3 class="section-title">Entitlement Settings</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="default_entitlement">Default Entitlement (days) <span class="text-danger">*</span></label>
                    <input type="number"
                        class="form-control @error('default_entitlement') is-invalid @enderror"
                        name="default_entitlement" id="default_entitlement"
                        placeholder="e.g., 21"
                        value="{{ old('default_entitlement', 0) }}"
                        required min="0" max="365" step="0.5">
                    @error('default_entitlement')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Allow Half-Day <span class="text-danger">*</span></label>
                    <select class="form-select @error('allow_half_day') is-invalid @enderror"
                        name="allow_half_day" id="allow_half_day" data-trigger required>
                        <option value="1" {{ old('allow_half_day', '1') == '1' ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ old('allow_half_day') == '0' ? 'selected' : '' }}>No</option>
                    </select>
                    @error('allow_half_day')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Allow Negative Balance <span class="text-danger">*</span></label>
                    <select class="form-select @error('allow_negative_balance') is-invalid @enderror"
                        name="allow_negative_balance" id="allow_negative_balance" data-trigger required>
                        <option value="0" {{ old('allow_negative_balance', '0') == '0' ? 'selected' : '' }}>No</option>
                        <option value="1" {{ old('allow_negative_balance') == '1' ? 'selected' : '' }}>Yes</option>
                    </select>
                    @error('allow_negative_balance')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <h3 class="section-title">Restrictions</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="gender_restriction">Gender Restriction</label>
                    <select class="form-select @error('gender_restriction') is-invalid @enderror"
                        name="gender_restriction" id="gender_restriction" data-trigger>
                        <option value="" {{ old('gender_restriction') == '' ? 'selected' : '' }}>No Restriction (All)</option>
                        <option value="male" {{ old('gender_restriction') == 'male' ? 'selected' : '' }}>Male Only</option>
                        <option value="female" {{ old('gender_restriction') == 'female' ? 'selected' : '' }}>Female Only</option>
                    </select>
                    @error('gender_restriction')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="min_notice_days">Minimum Notice (days)</label>
                    <input type="number"
                        class="form-control @error('min_notice_days') is-invalid @enderror"
                        name="min_notice_days" id="min_notice_days"
                        placeholder="e.g., 7"
                        value="{{ old('min_notice_days') }}"
                        min="0" max="90">
                    <div class="form-text">Leave blank if no minimum notice required</div>
                    @error('min_notice_days')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="max_consecutive_days">Max Consecutive Days</label>
                    <input type="number"
                        class="form-control @error('max_consecutive_days') is-invalid @enderror"
                        name="max_consecutive_days" id="max_consecutive_days"
                        placeholder="e.g., 14"
                        value="{{ old('max_consecutive_days') }}"
                        min="1" max="365">
                    <div class="form-text">Leave blank if no maximum</div>
                    @error('max_consecutive_days')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <h3 class="section-title">Documentation</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Requires Attachment <span class="text-danger">*</span></label>
                    <select class="form-select @error('requires_attachment') is-invalid @enderror"
                        name="requires_attachment" id="requires_attachment" data-trigger required>
                        <option value="0" {{ old('requires_attachment', '0') == '0' ? 'selected' : '' }}>No</option>
                        <option value="1" {{ old('requires_attachment') == '1' ? 'selected' : '' }}>Yes</option>
                    </select>
                    @error('requires_attachment')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="attachment_required_after_days">Attachment Required After (days)</label>
                    <input type="number"
                        class="form-control @error('attachment_required_after_days') is-invalid @enderror"
                        name="attachment_required_after_days" id="attachment_required_after_days"
                        placeholder="e.g., 3"
                        value="{{ old('attachment_required_after_days') }}"
                        min="1" max="30">
                    <div class="form-text">Only applies if attachment is required</div>
                    @error('attachment_required_after_days')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <h3 class="section-title">Display Settings</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="color">Color</label>
                    <div class="color-input-group">
                        <input type="text"
                            class="form-control @error('color') is-invalid @enderror"
                            name="color" id="color"
                            placeholder="#3b82f6"
                            value="{{ old('color') }}"
                            maxlength="7"
                            pattern="^#[0-9A-Fa-f]{6}$">
                        <input type="color" class="color-preview" id="color_picker" value="{{ old('color', '#3b82f6') }}">
                    </div>
                    <div class="form-text">Hex color code for visual identification</div>
                    @error('color')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Is Paid Leave <span class="text-danger">*</span></label>
                    <select class="form-select @error('is_paid') is-invalid @enderror"
                        name="is_paid" id="is_paid" data-trigger required>
                        <option value="1" {{ old('is_paid', '1') == '1' ? 'selected' : '' }}>Yes (Paid)</option>
                        <option value="0" {{ old('is_paid') == '0' ? 'selected' : '' }}>No (Unpaid)</option>
                    </select>
                    @error('is_paid')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select class="form-select @error('is_active') is-invalid @enderror"
                        name="is_active" id="is_active" data-trigger required>
                        <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('is_active')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ route('leave.settings.index') . '#leaveTypes' }}">
                    <i class="bx bx-x"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary btn-loading">
                    <span class="btn-text"><i class="fas fa-save"></i> Save Leave Type</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Saving...
                    </span>
                </button>
            </div>
        </form>
    </div>
@endsection
@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeChoices();
            initializeFormValidation();
            initializeColorPicker();
            initializeAlertDismissal();
        });

        function initializeChoices() {
            const searchableSelects = document.querySelectorAll('select[data-trigger]');
            searchableSelects.forEach(function(element) {
                new Choices(element, {
                    searchEnabled: false,
                    removeItemButton: false,
                    shouldSort: false,
                    itemSelectText: '',
                    classNames: {
                        containerOuter: 'choices'
                    }
                });
            });
        }

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
                        // Show loading state on submit button
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

        function initializeColorPicker() {
            const colorInput = document.getElementById('color');
            const colorPicker = document.getElementById('color_picker');

            if (colorInput && colorPicker) {
                // Sync color picker to input
                colorPicker.addEventListener('input', function() {
                    colorInput.value = this.value;
                });

                // Sync input to color picker
                colorInput.addEventListener('input', function() {
                    if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
                        colorPicker.value = this.value;
                    }
                });

                // Set initial color picker value from input
                if (colorInput.value && /^#[0-9A-Fa-f]{6}$/.test(colorInput.value)) {
                    colorPicker.value = colorInput.value;
                }
            }
        }

        function initializeAlertDismissal() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const dismissButton = alert.querySelector('.btn-close');
                    if (dismissButton) {
                        dismissButton.click();
                    } else {
                        alert.classList.remove('show');
                        alert.classList.add('fade');
                    }
                }, 5000);
            });
        }

        // Convert code to uppercase
        const codeInput = document.getElementById('code');
        if (codeInput) {
            codeInput.addEventListener('input', function() {
                this.value = this.value.toUpperCase().replace(/[^A-Z0-9_-]/g, '');
            });
        }
    </script>
@endsection
