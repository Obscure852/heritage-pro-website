@extends('layouts.master')
@section('title')
    Assign Student Discount
@endsection
@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        .select2-container--bootstrap-5 .select2-selection {
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
            min-height: 42px;
        }

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            padding: 0;
            line-height: 1.5;
        }

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
            height: 40px;
        }

        .select2-container--bootstrap-5.select2-container--focus .select2-selection,
        .select2-container--bootstrap-5.select2-container--open .select2-selection {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

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

        .siblings-info {
            background: #dbeafe;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 20px;
        }

        .siblings-info h6 {
            color: #1e40af;
            margin: 0 0 8px 0;
        }

        .siblings-info ul {
            margin: 0;
            padding-left: 20px;
        }

        .siblings-info li {
            color: #1e40af;
            font-size: 14px;
            padding: 4px 0;
        }

        .preselected-student {
            background: #f0fdf4;
            border: 1px solid #22c55e;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 20px;
        }

        .preselected-student h6 {
            color: #166534;
            margin: 0 0 8px 0;
        }

        .preselected-student .student-name {
            font-size: 16px;
            font-weight: 600;
            color: #166534;
        }

        .preselected-student .student-details {
            font-size: 13px;
            color: #15803d;
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
            <a class="text-muted font-size-14" href="{{ route('fees.discounts.index') }}">Student Discounts</a>
        @endslot
        @slot('title')
            Assign Discount
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
            <h1 class="page-title">Assign Discount to Student</h1>
        </div>

        <div class="help-text">
            <div class="help-title">Discount Assignment</div>
            <div class="help-content">
                Assign a discount to a specific student for a year. The discount will be applied when generating fee invoices.
                Students can have multiple discount types assigned, but each type can only be assigned once per year.
            </div>
        </div>

        {{-- Show preselected student info --}}
        @if($preselectedStudent)
            <div class="preselected-student">
                <h6><i class="fas fa-user-check me-2"></i>Selected Student</h6>
                <div class="student-name">{{ $preselectedStudent->full_name }}</div>
                <div class="student-details">
                    @if($preselectedStudent->sponsor)
                        Sponsor: {{ $preselectedStudent->sponsor->name }}
                    @endif
                </div>
            </div>
        @endif

        {{-- Show siblings info if preselected student has siblings --}}
        @if($siblings->isNotEmpty())
            <div class="siblings-info">
                <h6><i class="fas fa-users me-2"></i>Siblings (Same Sponsor)</h6>
                <ul>
                    @foreach($siblings as $sibling)
                        <li>{{ $sibling->full_name }} ({{ $sibling->currentGrade?->name ?? 'N/A' }})</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="needs-validation" method="POST" action="{{ route('fees.discounts.store') }}" novalidate>
            @csrf

            <h3 class="section-title">Student Information</h3>
            <div class="form-grid">
                @if($preselectedStudent)
                    <input type="hidden" name="student_id" value="{{ $preselectedStudent->id }}">
                    <div class="form-group">
                        <label class="form-label">Student</label>
                        <input type="text" class="form-control" value="{{ $preselectedStudent->full_name }}" disabled>
                    </div>
                @else
                    <div class="form-group">
                        <label class="form-label" for="student_id">Student <span class="text-danger">*</span></label>
                        <select class="form-select @error('student_id') is-invalid @enderror"
                            name="student_id" id="student_id" required>
                            <option value="">Search for a student...</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                    {{ $student->last_name }}, {{ $student->first_name }} (#{{ $student->id }})
                                </option>
                            @endforeach
                        </select>
                        @error('student_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endif
            </div>

            <h3 class="section-title">Discount Details</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="discount_type_id">Discount Type <span class="text-danger">*</span></label>
                    <select class="form-select @error('discount_type_id') is-invalid @enderror"
                        name="discount_type_id" id="discount_type_id" required>
                        <option value="">Select Discount Type</option>
                        @foreach($discountTypes as $type)
                            <option value="{{ $type->id }}"
                                {{ old('discount_type_id') == $type->id ? 'selected' : '' }}
                                data-percentage="{{ $type->percentage }}"
                                data-amount="{{ $type->fixed_amount }}">
                                {{ $type->name }}
                                @if($type->percentage)
                                    ({{ $type->percentage }}%)
                                @elseif($type->fixed_amount)
                                    ({{ format_currency($type->fixed_amount) }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('discount_type_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="year">Year <span class="text-danger">*</span></label>
                    <select class="form-select @error('year') is-invalid @enderror"
                        name="year" id="year" required>
                        <option value="">Select Year</option>
                        @foreach ($years ?? [] as $year)
                            <option value="{{ $year }}" {{ old('year', request('year', $currentYear ?? date('Y'))) == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                    @error('year')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-grid" style="margin-top: 16px;">
                <div class="form-group full-width">
                    <label class="form-label" for="notes">Notes</label>
                    <input type="text"
                        class="form-control @error('notes') is-invalid @enderror"
                        name="notes" id="notes"
                        placeholder="Optional notes about this discount"
                        value="{{ old('notes') }}"
                        maxlength="500">
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ route('fees.discounts.index') }}">
                    <i class="bx bx-arrow-back"></i> Back
                </a>
                <button type="submit" class="btn btn-primary btn-loading">
                    <span class="btn-text"><i class="fas fa-save"></i> Assign Discount</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Assigning...
                    </span>
                </button>
            </div>
        </form>
    </div>
@endsection
@section('script')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeFormValidation();
            initializeAlertDismissal();
            initializeSelect2();
        });

        function initializeSelect2() {
            const studentSelect = document.getElementById('student_id');
            if (studentSelect && typeof jQuery !== 'undefined') {
                $(studentSelect).select2({
                    theme: 'bootstrap-5',
                    placeholder: 'Search for a student...',
                    allowClear: true,
                    width: '100%'
                });
            }
        }

        // Term-year sync removed - now year-based only

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
