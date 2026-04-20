@extends('layouts.master')
@section('title')
    Student ID Cards
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

        .form-grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        @media (max-width: 576px) {
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

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
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

        .hidden-section {
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .hidden-section.visible {
            display: block;
            opacity: 1;
        }

        .card-preview {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 20px;
            margin-top: 20px;
        }

        .card-preview-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .cards-preview-container {
            display: flex;
            gap: 24px;
            flex-wrap: wrap;
        }

        .card-preview-item {
            text-align: center;
        }

        .card-preview-label {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 8px;
            font-weight: 500;
        }

        /* Front Card Preview */
        .sample-card-front {
            width: 85.6mm;
            height: 53.98mm;
            border-radius: 3mm;
            background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            overflow: hidden;
            position: relative;
        }

        .preview-front-header {
            background: rgba(255,255,255,0.95);
            padding: 8px 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .preview-logo {
            width: 28px;
            height: 28px;
            background: #e5e7eb;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: #9ca3af;
        }

        .preview-school-info {
            flex: 1;
        }

        .preview-school-name {
            font-size: 10px;
            font-weight: bold;
            color: #1e3a5f;
        }

        .preview-card-type {
            font-size: 7px;
            color: #6b7280;
            text-transform: uppercase;
        }

        .preview-front-body {
            padding: 10px 12px;
            display: flex;
            gap: 10px;
        }

        .preview-photo {
            width: 50px;
            height: 60px;
            background: #e5e7eb;
            border: 2px solid white;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            color: #9ca3af;
        }

        .preview-student-info {
            flex: 1;
            color: white;
        }

        .preview-student-name {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .preview-info-row {
            margin-bottom: 3px;
        }

        .preview-info-label {
            font-size: 6px;
            color: rgba(255,255,255,0.7);
            text-transform: uppercase;
        }

        .preview-info-value {
            font-size: 9px;
            color: white;
        }

        .preview-front-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0,0,0,0.2);
            padding: 4px;
            text-align: center;
        }

        .preview-year {
            font-size: 7px;
            color: rgba(255,255,255,0.9);
        }

        /* Back Card Preview */
        .sample-card-back {
            width: 85.6mm;
            height: 53.98mm;
            border-radius: 3mm;
            background: #f8f9fa;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            overflow: hidden;
            position: relative;
        }

        .preview-back-header {
            background: #1e3a5f;
            padding: 6px 12px;
            text-align: center;
        }

        .preview-back-title {
            font-size: 9px;
            font-weight: bold;
            color: white;
            text-transform: uppercase;
        }

        .preview-back-body {
            padding: 8px 12px;
        }

        .preview-back-section {
            margin-bottom: 6px;
        }

        .preview-back-section-title {
            font-size: 7px;
            font-weight: bold;
            color: #1e3a5f;
            text-transform: uppercase;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 2px;
            margin-bottom: 3px;
        }

        .preview-back-info {
            font-size: 8px;
            color: #374151;
            line-height: 1.3;
        }

        .preview-emergency {
            background: #fff3cd;
            padding: 6px 8px;
            border-radius: 3px;
            margin-bottom: 6px;
        }

        .preview-emergency-label {
            font-size: 6px;
            font-weight: bold;
            color: #856404;
            text-transform: uppercase;
        }

        .preview-emergency-value {
            font-size: 8px;
            color: #856404;
        }

        .preview-motto {
            font-size: 7px;
            font-style: italic;
            color: #6b7280;
            text-align: center;
        }

        .preview-back-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: #1e3a5f;
            padding: 4px 8px;
        }

        .preview-terms {
            font-size: 5px;
            color: rgba(255,255,255,0.8);
            text-align: center;
            line-height: 1.3;
        }

        @media (max-width: 768px) {
            .cards-preview-container {
                flex-direction: column;
                align-items: center;
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
            Student ID Cards
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

    <div class="form-container">
        <div class="page-header">
            <h1 class="page-title"><i class="fas fa-id-card me-2"></i>Student ID Cards</h1>
            <div class="school-info">
                <strong>{{ $school_data->school_name ?? 'School Name' }}</strong><br>
                {{ $school_data->physical_address ?? '' }}<br>
                Tel: {{ $school_data->telephone ?? '' }}
            </div>
        </div>

        <div class="help-text">
            <div class="help-title">Generate Printable Student ID Cards</div>
            <div class="help-content">
                Select a grade and class to generate printable ID cards in standard credit card format (85.6mm x 53.98mm).
                Cards include student photo, name, class, grade, and emergency contact information.
            </div>
        </div>

        <form id="idCardsForm" method="POST" action="{{ route('students.preview-id-cards') }}">
            @csrf

            <h3 class="section-title"><i class="bx bx-filter-alt me-2"></i>Selection Criteria</h3>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label" for="grade">Grade <span class="text-danger">*</span></label>
                    <select class="form-select" id="grade" name="grade_id" required>
                        <option value="">Select Grade</option>
                        @foreach ($grades as $grade)
                            <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group hidden-section" id="class-selection">
                    <label class="form-label" for="class">Class <span class="text-danger">*</span></label>
                    <select class="form-select" id="class" name="class_id" required>
                        <option value="">Select Class</option>
                    </select>
                </div>
            </div>

            <div class="card-preview hidden-section" id="preview-section">
                <div class="card-preview-title"><i class="fas fa-eye me-2"></i>Card Preview (Actual Size: 85.6mm x 53.98mm)</div>
                <div class="cards-preview-container">
                    <!-- Front Card Preview -->
                    <div class="card-preview-item">
                        <div class="card-preview-label">Front Side</div>
                        <div class="sample-card-front">
                            <div class="preview-front-header">
                                <div class="preview-logo">
                                    @if ($school_data->logo_path)
                                        <img src="{{ asset($school_data->logo_path) }}" alt="Logo" style="width: 24px; height: 24px; object-fit: contain;">
                                    @else
                                        <i class="fas fa-school"></i>
                                    @endif
                                </div>
                                <div class="preview-school-info">
                                    <div class="preview-school-name">{{ $school_data->school_name ?? 'School Name' }}</div>
                                    <div class="preview-card-type">Student Identification Card</div>
                                </div>
                            </div>
                            <div class="preview-front-body">
                                <div class="preview-photo">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="preview-student-info">
                                    <div class="preview-student-name">Student Name</div>
                                    <div class="preview-info-row">
                                        <div class="preview-info-label">Class</div>
                                        <div class="preview-info-value">1A</div>
                                    </div>
                                    <div class="preview-info-row">
                                        <div class="preview-info-label">Grade</div>
                                        <div class="preview-info-value">Form 1</div>
                                    </div>
                                    <div class="preview-info-row">
                                        <div class="preview-info-label">Class Teacher</div>
                                        <div class="preview-info-value">Mr. Teacher</div>
                                    </div>
                                </div>
                            </div>
                            <div class="preview-front-footer">
                                <div class="preview-year">Academic Year: {{ now()->year }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Back Card Preview -->
                    <div class="card-preview-item">
                        <div class="card-preview-label">Back Side</div>
                        <div class="sample-card-back">
                            <div class="preview-back-header">
                                <div class="preview-back-title">{{ $school_data->school_name ?? 'School Name' }}</div>
                            </div>
                            <div class="preview-back-body">
                                <div class="preview-back-section">
                                    <div class="preview-back-section-title">School Contact</div>
                                    <div class="preview-back-info">
                                        {{ $school_data->physical_address ?? 'School Address' }}<br>
                                        Tel: {{ $school_data->telephone ?? '000-0000' }}
                                    </div>
                                </div>
                                <div class="preview-emergency">
                                    <div class="preview-emergency-label">Emergency Contact</div>
                                    <div class="preview-emergency-value">Parent/Guardian Name<br>Tel: 000-0000</div>
                                </div>
                                @if ($school_data->motto ?? false)
                                    <div class="preview-motto">"{{ $school_data->motto }}"</div>
                                @endif
                            </div>
                            <div class="preview-back-footer">
                                <div class="preview-terms">This card is the property of {{ $school_data->school_name ?? 'the school' }}. If found, please return to the school office.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions hidden-section" id="actions-section">
                <button type="submit" class="btn btn-primary btn-loading" id="preview-btn" name="action" value="preview">
                    <span class="btn-text"><i class="fas fa-eye"></i> Preview Cards</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Loading...
                    </span>
                </button>
                <button type="button" class="btn btn-success btn-loading" id="generate-btn">
                    <span class="btn-text"><i class="fas fa-file-pdf"></i> Download PDF</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Generating...
                    </span>
                </button>
                <a href="{{ route('students.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Students
                </a>
            </div>
        </form>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeGradeSelection();
            initializeClassSelection();
            initializeFormSubmission();
            initializeAlertDismissal();
        });

        function initializeGradeSelection() {
            const gradeSelect = document.getElementById('grade');
            if (!gradeSelect) return;

            gradeSelect.addEventListener('change', function() {
                const gradeId = this.value;

                if (gradeId) {
                    fetchClasses(gradeId);
                } else {
                    hideAllSections();
                }
            });
        }

        function initializeClassSelection() {
            const classSelect = document.getElementById('class');
            if (!classSelect) return;

            classSelect.addEventListener('change', function() {
                if (this.value) {
                    showSection('preview-section');
                    showSection('actions-section');
                } else {
                    hideSection('preview-section');
                    hideSection('actions-section');
                }
            });
        }

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
                hideSection('preview-section');
                hideSection('actions-section');
            })
            .catch(error => {
                console.error('Error fetching classes:', error);
                classSelect.innerHTML = '<option value="">Error loading classes</option>';
                classSelect.disabled = false;
                showSection('class-selection');
            });
        }

        function initializeFormSubmission() {
            const form = document.getElementById('idCardsForm');
            if (!form) return;

            const previewBtn = document.getElementById('preview-btn');
            const generateBtn = document.getElementById('generate-btn');

            // Preview button - submits form normally to preview page
            form.addEventListener('submit', function(e) {
                const gradeSelect = document.getElementById('grade');
                const classSelect = document.getElementById('class');

                if (!gradeSelect.value || !classSelect.value) {
                    e.preventDefault();
                    alert('Please select both a grade and a class.');
                    return false;
                }

                // Show loading state on preview button
                if (previewBtn) {
                    previewBtn.classList.add('loading');
                    previewBtn.disabled = true;
                }
            });

            // Generate PDF button - uses fetch for download
            if (generateBtn) {
                generateBtn.addEventListener('click', function(e) {
                    e.preventDefault();

                    const gradeSelect = document.getElementById('grade');
                    const classSelect = document.getElementById('class');

                    if (!gradeSelect.value || !classSelect.value) {
                        alert('Please select both a grade and a class.');
                        return false;
                    }

                    generateBtn.classList.add('loading');
                    generateBtn.disabled = true;

                    const formData = new FormData(form);

                    fetch('{{ route("students.generate-id-cards") }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.text().then(text => {
                                throw new Error(text || 'Failed to generate ID cards');
                            });
                        }
                        const contentDisposition = response.headers.get('Content-Disposition');
                        let filename = 'student-id-cards.pdf';
                        if (contentDisposition) {
                            const match = contentDisposition.match(/filename="?(.+)"?/);
                            if (match) filename = match[1];
                        }
                        return response.blob().then(blob => ({ blob, filename }));
                    })
                    .then(({ blob, filename }) => {
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = filename;
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(url);
                        a.remove();
                        resetGenerateButton();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error generating ID cards. Please try again.');
                        resetGenerateButton();
                    });
                });
            }

            function resetGenerateButton() {
                if (generateBtn) {
                    generateBtn.classList.remove('loading');
                    generateBtn.disabled = false;
                }
            }
        }

        function hideAllSections() {
            hideSection('class-selection');
            hideSection('preview-section');
            hideSection('actions-section');
        }

        function showSection(sectionId) {
            const section = document.getElementById(sectionId);
            if (section) {
                section.classList.add('visible');
            }
        }

        function hideSection(sectionId) {
            const section = document.getElementById(sectionId);
            if (section) {
                section.classList.remove('visible');
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
