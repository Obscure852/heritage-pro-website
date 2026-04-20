@extends('layouts.master')
@section('title')
    Generate Bulk Invoices
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

        .required {
            color: #dc2626;
        }

        .text-danger {
            color: #dc2626;
        }

        .warning-box {
            background: #fef3c7;
            border: 1px solid #fcd34d;
            border-radius: 6px;
            padding: 16px;
            margin-top: 24px;
        }

        .warning-box .warning-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            color: #92400e;
            margin-bottom: 8px;
        }

        .warning-box .warning-content {
            color: #78350f;
            font-size: 13px;
            line-height: 1.5;
        }

        .warning-box .warning-content ul {
            margin: 8px 0 0 0;
            padding-left: 20px;
        }

        .warning-box .warning-content li {
            margin-bottom: 4px;
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
            Generate Bulk Invoices
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
            <h1 class="page-title">Generate Bulk Invoices</h1>
        </div>

        <div class="help-text">
            <div class="help-title">Bulk Invoice Generation</div>
            <div class="help-content">
                Generate invoices for all enrolled students in a selected grade for a specific year.
                This will create invoices based on the fee structures configured for the selected grade and year,
                with applicable discounts automatically applied to each student.
            </div>
        </div>

        <form class="needs-validation" method="POST" action="{{ route('fees.collection.invoices.bulk.store') }}" novalidate id="bulkGenerateForm">
            @csrf

            <h3 class="section-title">Selection Criteria</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="grade_id">Grade <span class="text-danger">*</span></label>
                    <select class="form-select @error('grade_id') is-invalid @enderror"
                        name="grade_id" id="grade_id" required>
                        <option value="">Select Grade</option>
                        @foreach ($grades ?? [] as $grade)
                            <option value="{{ $grade->id }}" {{ old('grade_id') == $grade->id ? 'selected' : '' }}>
                                {{ $grade->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('grade_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="year">Year <span class="text-danger">*</span></label>
                    <select class="form-select @error('year') is-invalid @enderror"
                        name="year" id="year" required>
                        <option value="">Select Year</option>
                        @php
                            $currentYear = date('Y');
                        @endphp
                        @for ($y = $currentYear - 1; $y <= $currentYear + 1; $y++)
                            <option value="{{ $y }}" {{ old('year', $currentYear) == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>
                    @error('year')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="due_date">Due Date</label>
                    <input type="date"
                        class="form-control @error('due_date') is-invalid @enderror"
                        name="due_date"
                        id="due_date"
                        value="{{ old('due_date', now()->addDays(30)->format('Y-m-d')) }}">
                    <small class="text-muted">Defaults to 30 days from today</small>
                    @error('due_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="warning-box">
                <div class="warning-title">
                    <i class="fas fa-exclamation-triangle"></i>
                    Important Information
                </div>
                <div class="warning-content">
                    Please note the following before generating bulk invoices:
                    <ul>
                        <li><strong>Existing invoices:</strong> Students who already have an invoice for the selected year will be skipped.</li>
                        <li><strong>Enrollment status:</strong> Only students with 'Current' enrollment status will receive invoices.</li>
                        <li><strong>Year enrollment:</strong> Only students enrolled in the selected grade for the specified year will be included.</li>
                        <li><strong>Fee structures:</strong> Invoices will include all active fee structures defined for the grade and year.</li>
                        <li><strong>Discounts:</strong> Any applicable student discounts will be automatically applied.</li>
                    </ul>
                </div>
            </div>

            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ route('fees.collection.invoices.index') }}">
                    <i class="bx bx-x"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary btn-loading">
                    <span class="btn-text"><i class="fas fa-file-invoice"></i> Generate Invoices</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Generating...
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
        });

        function initializeFormValidation() {
            const form = document.getElementById('bulkGenerateForm');
            const submitBtn = form.querySelector('button[type="submit"].btn-loading');

            submitBtn.addEventListener('click', function(event) {
                // Prevent double submission
                if (form.dataset.submitting === 'true') {
                    event.preventDefault();
                    return;
                }

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
                    form.classList.add('was-validated');
                    return;
                }

                // Prevent default to show confirm dialog
                event.preventDefault();

                // Confirm before bulk generation
                const gradeSelect = document.getElementById('grade_id');
                const yearSelect = document.getElementById('year');
                const gradeName = gradeSelect.options[gradeSelect.selectedIndex].text;
                const yearName = yearSelect.options[yearSelect.selectedIndex].text;

                if (confirm(`Are you sure you want to generate invoices for all students in ${gradeName} for ${yearName}?\n\nThis may take a moment depending on the number of students.`)) {
                    form.dataset.submitting = 'true';

                    // Show loading state
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;

                    // Submit the form
                    form.submit();
                }
            });
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
