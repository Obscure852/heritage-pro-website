@extends('layouts.master')
@section('title')
    Custom Report Builder
@endsection

@section('css')
    <style>
        .form-container {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 32px;
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

        .school-info {
            text-align: right;
            font-size: 13px;
            color: #6b7280;
            line-height: 1.5;
        }

        .school-info strong {
            color: #1f2937;
            font-size: 14px;
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
            font-size: 15px;
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

        .form-grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        @media (max-width: 992px) {
            .form-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .form-grid,
            .form-grid-2 {
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
            background-color: #fff;
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

        /* Checkbox Styling */
        .fields-container {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 16px;
            margin-top: 8px;
        }

        .fields-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }

        @media (max-width: 992px) {
            .fields-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .fields-grid {
                grid-template-columns: 1fr;
            }
        }

        .field-checkbox {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .field-checkbox:hover {
            border-color: #3b82f6;
            background: #f0f7ff;
        }

        .field-checkbox input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
            accent-color: #3b82f6;
        }

        .field-checkbox label {
            margin: 0;
            cursor: pointer;
            font-size: 13px;
            color: #374151;
            flex: 1;
        }

        .select-all-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            background: #3b82f6;
            border-radius: 3px;
            margin-bottom: 12px;
            width: fit-content;
        }

        .select-all-wrapper input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
            accent-color: white;
        }

        .select-all-wrapper label {
            margin: 0;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            color: white;
        }

        /* Button Styling */
        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-start;
            padding-top: 24px;
            border-top: 1px solid #f3f4f6;
            margin-top: 24px;
            flex-wrap: wrap;
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
            transform: none !important;
            box-shadow: none !important;
        }

        /* Hidden sections animation */
        .hidden-section {
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .hidden-section.visible {
            display: block;
            opacity: 1;
        }

        .hidden-section.visible.flex {
            display: flex;
        }

        /* Print Styles */
        @media print {
            body {
                width: 100%;
                margin: 0;
                padding: 0;
                line-height: normal;
            }

            body * {
                visibility: hidden;
            }

            .printable,
            .printable * {
                visibility: visible;
            }

            .printable {
                position: relative;
                margin: 0 auto;
                width: 80%;
                max-width: 1000px;
            }
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .school-info {
                text-align: left;
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
            <a class="text-muted font-size-14" href="{{ route('students.index') }}">Students</a>
        @endslot
        @slot('title')
            Custom Report Builder
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

    <div class="form-container printable">
        <div class="page-header">
            <h1 class="page-title"><i class="bx bx-table me-2"></i>Custom Report Builder</h1>
            <div class="school-info">
                <strong>{{ $school_data->school_name ?? 'School Name' }}</strong><br>
                {{ $school_data->physical_address ?? '' }}<br>
                Tel: {{ $school_data->telephone ?? '' }}
            </div>
        </div>

        <div class="help-text">
            <div class="help-title">Build Custom Student Reports</div>
            <div class="help-content">
                Select a grade and class, apply filters, then choose which fields to include in your report.
                You can generate a preview, export to Excel, or export to PDF.
            </div>
        </div>

        <form id="reportForm" method="POST" action="{{ route('students.generate-custom-report') }}">
            @csrf

            <h3 class="section-title"><i class="bx bx-filter-alt me-2"></i>Selection Criteria</h3>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label" for="grade">Grade</label>
                    <select class="form-select" id="grade" name="grade_id">
                        <option value="">Select Grade</option>
                        <option value="all">All Grades</option>
                        @foreach ($grades as $grade)
                            <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group hidden-section" id="class-selection">
                    <label class="form-label" for="class">Class</label>
                    <select class="form-select" id="class" name="class_id">
                        <option value="">Select Class</option>
                    </select>
                </div>
            </div>

            <div class="hidden-section" id="filters-section" style="margin-top: 16px;">
                <h3 class="section-title"><i class="bx bx-filter me-2"></i>Filters</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="status_filter">Status</label>
                        <select class="form-select" id="status_filter" name="status_filter">
                            <option value="all">All Statuses</option>
                            <option value="Current" selected>Current</option>
                            <option value="Left">Past/Left</option>
                            <option value="Suspended">Suspended</option>
                            <option value="Graduated">Graduated</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="gender_filter">Gender</label>
                        <select class="form-select" id="gender_filter" name="gender_filter">
                            <option value="all">All Genders</option>
                            <option value="M">Male</option>
                            <option value="F">Female</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="student_type_filter">Student Type</label>
                        <select class="form-select" id="student_type_filter" name="student_type_filter">
                            <option value="all">All Types</option>
                            @foreach ($student_types as $type)
                                <option value="{{ $type->id }}">{{ $type->type }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="hidden-section" id="fields-section" style="margin-top: 16px;">
                <h3 class="section-title"><i class="bx bx-list-check me-2"></i>Report Fields</h3>
                <div class="fields-container">
                    <div class="select-all-wrapper">
                        <input type="checkbox" id="selectAllFields">
                        <label for="selectAllFields">Select All Fields</label>
                    </div>
                    <div class="fields-grid" id="fields"></div>
                </div>
            </div>

            <input type="hidden" name="export_action" id="exportAction" value="">

            <div class="form-actions hidden-section" id="actions-section">
                <button type="submit" class="btn btn-primary btn-loading" id="generate-report-btn">
                    <span class="btn-text"><i class="bx bx-table"></i> Generate Report</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Generating...
                    </span>
                </button>
                <button type="submit" class="btn btn-success btn-loading" id="export-to-excel">
                    <span class="btn-text"><i class="bx bx-file"></i> Export to Excel</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Exporting...
                    </span>
                </button>
                <button type="submit" class="btn btn-danger btn-loading" id="export-to-pdf">
                    <span class="btn-text"><i class="bx bxs-file-pdf"></i> Export to PDF</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Exporting...
                    </span>
                </button>
            </div>
        </form>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeGradeSelection();
            initializeClassSelection();
            initializeSelectAllFields();
            initializeFormSubmission();
            initializeAlertDismissal();
        });

        /**
         * Initialize grade selection change handler
         */
        function initializeGradeSelection() {
            const gradeSelect = document.getElementById('grade');
            if (!gradeSelect) return;

            gradeSelect.addEventListener('change', function() {
                const gradeId = this.value;

                if (gradeId === 'all') {
                    hideSection('class-selection');
                    resetClassSelect();
                    showFiltersAndFields();
                } else if (gradeId) {
                    fetchClasses(gradeId);
                } else {
                    hideAllSections();
                }
            });
        }

        /**
         * Initialize class selection change handler
         */
        function initializeClassSelection() {
            const classSelect = document.getElementById('class');
            if (!classSelect) return;

            classSelect.addEventListener('change', function() {
                if (this.value) {
                    showFiltersAndFields();
                } else {
                    hideFiltersAndFields();
                }
            });
        }

        /**
         * Fetch classes for a given grade
         */
        function fetchClasses(gradeId) {
            const classSelect = document.getElementById('class');
            classSelect.innerHTML = '<option value="">Loading...</option>';
            classSelect.disabled = true;

            fetch('{{ route("students.students-get-classes") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ grade_id: gradeId }),
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(classes => {
                classSelect.innerHTML = '<option value="">Select Class</option>';

                if (Array.isArray(classes) && classes.length > 0) {
                    classes.forEach(klass => {
                        const option = document.createElement('option');
                        option.value = klass.id;
                        option.textContent = klass.label || klass.name;
                        classSelect.appendChild(option);
                    });
                }

                classSelect.disabled = false;
                showSection('class-selection');
            })
            .catch(error => {
                console.error('Error fetching classes:', error);
                classSelect.innerHTML = '<option value="">Error loading classes</option>';
                classSelect.disabled = false;
                showSection('class-selection');
            });
        }

        /**
         * Fetch available fields for the report
         */
        function fetchFields() {
            const fieldsContainer = document.getElementById('fields');
            fieldsContainer.innerHTML = '<div class="text-muted">Loading fields...</div>';

            fetch('{{ route("students.students-get-fields") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(fields => {
                fieldsContainer.innerHTML = '';

                if (fields && typeof fields === 'object') {
                    Object.entries(fields).forEach(([key, label]) => {
                        const div = document.createElement('div');
                        div.className = 'field-checkbox';
                        div.innerHTML = `
                            <input type="checkbox" name="fields[]" value="${escapeHtml(key)}" id="field_${escapeHtml(key)}">
                            <label for="field_${escapeHtml(key)}">${escapeHtml(label)}</label>
                        `;
                        fieldsContainer.appendChild(div);
                    });
                }

                // Reset select all checkbox
                const selectAllCheckbox = document.getElementById('selectAllFields');
                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = false;
                }

                showSection('fields-section');
                showSection('actions-section');
            })
            .catch(error => {
                console.error('Error fetching fields:', error);
                fieldsContainer.innerHTML = '<div class="text-danger">Error loading fields. Please try again.</div>';
            });
        }

        /**
         * Initialize select all fields checkbox
         */
        function initializeSelectAllFields() {
            const selectAllCheckbox = document.getElementById('selectAllFields');
            if (!selectAllCheckbox) return;

            selectAllCheckbox.addEventListener('change', function() {
                const fieldCheckboxes = document.querySelectorAll('#fields input[type="checkbox"]');
                fieldCheckboxes.forEach(checkbox => {
                    checkbox.checked = selectAllCheckbox.checked;
                });
            });
        }

        /**
         * Initialize form submission with loading states
         */
        function initializeFormSubmission() {
            const form = document.getElementById('reportForm');
            if (!form) return;

            const exportActionInput = document.getElementById('exportAction');
            const submitButtons = form.querySelectorAll('button[type="submit"]');

            // Track which button was clicked and set the hidden input value
            submitButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove loading state from all buttons first
                    submitButtons.forEach(btn => btn.classList.remove('loading'));

                    if (button.id === 'export-to-excel') {
                        exportActionInput.value = 'excel';
                    } else if (button.id === 'export-to-pdf') {
                        exportActionInput.value = 'pdf';
                    } else {
                        exportActionInput.value = 'preview';
                    }
                });
            });

            form.addEventListener('submit', function(e) {
                const selectedFields = document.querySelectorAll('#fields input[type="checkbox"]:checked');

                if (selectedFields.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one field for the report.');
                    return false;
                }

                const action = exportActionInput.value;
                const isExport = (action === 'excel' || action === 'pdf');

                // Find the clicked button by matching the action
                let clickedButton = null;
                if (action === 'excel') {
                    clickedButton = document.getElementById('export-to-excel');
                } else if (action === 'pdf') {
                    clickedButton = document.getElementById('export-to-pdf');
                } else {
                    clickedButton = document.getElementById('generate-report-btn');
                }

                // Show loading state on the clicked button
                if (clickedButton) {
                    clickedButton.classList.add('loading');
                }

                // Defer disabling buttons so form data is serialized first
                setTimeout(function() {
                    submitButtons.forEach(btn => btn.disabled = true);
                }, 0);

                // For export actions (file download), re-enable buttons after a delay
                // since the page doesn't navigate away
                if (isExport) {
                    setTimeout(function() {
                        submitButtons.forEach(btn => {
                            btn.disabled = false;
                            btn.classList.remove('loading');
                        });
                    }, 3000);
                }
            });
        }

        /**
         * Show filters and fields sections
         */
        function showFiltersAndFields() {
            showSection('filters-section');
            fetchFields();
        }

        /**
         * Hide filters and fields sections
         */
        function hideFiltersAndFields() {
            hideSection('filters-section');
            hideSection('fields-section');
            hideSection('actions-section');
        }

        /**
         * Hide all dynamic sections
         */
        function hideAllSections() {
            hideSection('class-selection');
            hideSection('filters-section');
            hideSection('fields-section');
            hideSection('actions-section');
        }

        /**
         * Show a section with animation
         */
        function showSection(sectionId) {
            const section = document.getElementById(sectionId);
            if (section) {
                section.classList.add('visible');
            }
        }

        /**
         * Hide a section
         */
        function hideSection(sectionId) {
            const section = document.getElementById(sectionId);
            if (section) {
                section.classList.remove('visible');
            }
        }

        /**
         * Reset class select to default state
         */
        function resetClassSelect() {
            const classSelect = document.getElementById('class');
            if (classSelect) {
                classSelect.innerHTML = '<option value="all">All Classes</option>';
                classSelect.value = 'all';
            }
        }

        /**
         * Escape HTML to prevent XSS
         */
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        /**
         * Initialize auto-dismissal of alerts
         */
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

        // Prevent form resubmission on page refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
@endsection
