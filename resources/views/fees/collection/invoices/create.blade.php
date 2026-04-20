@extends('layouts.master')
@section('title')
    Generate Invoice
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

        /* Student Search Autocomplete */
        .student-search-container {
            position: relative;
        }

        .student-search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #d1d5db;
            border-top: none;
            border-radius: 0 0 3px 3px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }

        .student-search-results.show {
            display: block;
        }

        .student-search-item {
            padding: 10px 12px;
            cursor: pointer;
            border-bottom: 1px solid #f3f4f6;
            transition: background 0.15s;
        }

        .student-search-item:hover {
            background: #f3f4f6;
        }

        .student-search-item:last-child {
            border-bottom: none;
        }

        .student-search-name {
            font-weight: 500;
            color: #1f2937;
        }

        .student-search-meta {
            font-size: 12px;
            color: #6b7280;
        }

        .selected-student-card {
            background: #f0fdf4;
            border: 1px solid #86efac;
            border-radius: 6px;
            padding: 16px;
            margin-top: 12px;
            display: none;
        }

        .selected-student-card.show {
            display: block;
        }

        .selected-student-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .selected-student-name {
            font-weight: 600;
            color: #166534;
        }

        .selected-student-remove {
            color: #dc2626;
            cursor: pointer;
            font-size: 14px;
        }

        .selected-student-remove:hover {
            text-decoration: underline;
        }

        .selected-student-details {
            font-size: 13px;
            color: #4b5563;
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
            Generate Invoice
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
            <h1 class="page-title">Generate Invoice</h1>
        </div>

        <div class="help-text">
            <div class="help-title">Generate Student Invoice</div>
            <div class="help-content">
                Search for a student and select the year to generate an invoice.
                The invoice will include all fee structures applicable to the student's grade for the selected year,
                with any applicable discounts automatically applied.
            </div>
        </div>

        <form class="needs-validation" method="POST" action="{{ route('fees.collection.invoices.store') }}" novalidate id="generateInvoiceForm">
            @csrf

            <h3 class="section-title">Student Selection</h3>
            <div class="mb-4">
                <div class="form-group">
                    <label class="form-label" for="studentSearch">Search Student <span class="text-danger">*</span></label>
                    <div class="student-search-container">
                        <input type="text"
                            class="form-control"
                            id="studentSearch"
                            placeholder="Type student name or number to search..."
                            autocomplete="off">
                        <div class="student-search-results" id="studentSearchResults"></div>
                    </div>
                    <input type="hidden" name="student_id" id="student_id" value="{{ old('student_id', $student->id ?? '') }}" required>
                    @error('student_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="selected-student-card {{ isset($student) ? 'show' : '' }}" id="selectedStudentCard">
                    <div class="selected-student-header">
                        <span class="selected-student-name" id="selectedStudentName">{{ $student->full_name ?? '' }}</span>
                        <span class="selected-student-remove" id="removeStudent">Remove</span>
                    </div>
                    <div class="selected-student-details">
                        <span id="selectedStudentNumber">{{ $student->student_number ?? '' }}</span>
                        @if (isset($student->klass))
                            | <span id="selectedStudentGrade">{{ $student->klass->name }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <h3 class="section-title">Invoice Details</h3>
            <div class="form-grid">
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
                    <small class="text-muted">Defaults to 30 days from today if not specified</small>
                    @error('due_date')
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
                    placeholder="Optional notes for this invoice...">{{ old('notes') }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ route('fees.collection.invoices.index') }}">
                    <i class="bx bx-x"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary btn-loading">
                    <span class="btn-text"><i class="fas fa-file-invoice"></i> Generate Invoice</span>
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
            initializeStudentSearch();
            initializeFormValidation();
            initializeAlertDismissal();
            checkPreselectedStudent();
        });

        let searchTimeout = null;
        const searchInput = document.getElementById('studentSearch');
        const searchResults = document.getElementById('studentSearchResults');
        const studentIdInput = document.getElementById('student_id');
        const selectedCard = document.getElementById('selectedStudentCard');
        const selectedName = document.getElementById('selectedStudentName');
        const selectedNumber = document.getElementById('selectedStudentNumber');
        const removeBtn = document.getElementById('removeStudent');

        function initializeStudentSearch() {
            searchInput.addEventListener('input', function(e) {
                clearTimeout(searchTimeout);
                const query = e.target.value.trim();

                if (query.length < 2) {
                    searchResults.classList.remove('show');
                    return;
                }

                searchTimeout = setTimeout(() => {
                    fetchStudents(query);
                }, 300);
            });

            searchInput.addEventListener('blur', function() {
                setTimeout(() => {
                    searchResults.classList.remove('show');
                }, 200);
            });

            searchInput.addEventListener('focus', function() {
                if (searchResults.children.length > 0) {
                    searchResults.classList.add('show');
                }
            });

            removeBtn.addEventListener('click', function() {
                clearSelection();
            });
        }

        function fetchStudents(query) {
            const url = "{{ route('fees.collection.students.search') }}?search=" + encodeURIComponent(query);

            fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Search failed');
                }
                return response.json();
            })
            .then(data => {
                renderSearchResults(data);
            })
            .catch(error => {
                console.error('Search error:', error);
                searchResults.innerHTML = '<div class="student-search-item text-muted">Error searching students</div>';
                searchResults.classList.add('show');
            });
        }

        function renderSearchResults(students) {
            if (!students || students.length === 0) {
                searchResults.innerHTML = '<div class="student-search-item text-muted">No students found</div>';
                searchResults.classList.add('show');
                return;
            }

            // Response format from controller: id, name, student_id, grade_name
            searchResults.innerHTML = students.map(student => `
                <div class="student-search-item" data-id="${student.id}" data-name="${student.name}" data-number="#${student.student_id}" data-grade="${student.grade_name || 'N/A'}">
                    <div class="student-search-name">${student.name}</div>
                    <div class="student-search-meta">#${student.student_id} | ${student.grade_name || 'N/A'}</div>
                </div>
            `).join('');

            searchResults.classList.add('show');

            // Add click handlers to results
            searchResults.querySelectorAll('.student-search-item').forEach(item => {
                item.addEventListener('click', function() {
                    selectStudent(
                        this.dataset.id,
                        this.dataset.name,
                        this.dataset.number,
                        this.dataset.grade
                    );
                });
            });
        }

        function selectStudent(id, name, number, grade) {
            studentIdInput.value = id;
            selectedName.textContent = name;
            selectedNumber.textContent = number;

            const gradeSpan = document.getElementById('selectedStudentGrade');
            if (gradeSpan) {
                gradeSpan.textContent = grade;
            }

            selectedCard.classList.add('show');
            searchInput.value = '';
            searchResults.classList.remove('show');
        }

        function clearSelection() {
            studentIdInput.value = '';
            selectedName.textContent = '';
            selectedNumber.textContent = '';
            selectedCard.classList.remove('show');
            searchInput.value = '';
            searchInput.focus();
        }

        function checkPreselectedStudent() {
            // Check URL params for preselected student
            const urlParams = new URLSearchParams(window.location.search);
            const preselectedId = urlParams.get('student_id');

            if (preselectedId && !studentIdInput.value) {
                // Student ID provided in URL but not loaded yet - would need to fetch
                // For now, trust that the controller passed $student variable
            }
        }

        function initializeFormValidation() {
            const form = document.getElementById('generateInvoiceForm');

            form.addEventListener('submit', function(event) {
                // Check if student is selected
                if (!studentIdInput.value) {
                    event.preventDefault();
                    event.stopPropagation();
                    alert('Please select a student');
                    searchInput.focus();
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
