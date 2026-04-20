@extends('layouts.master')
@section('title')
    ID Cards Preview
@endsection

@section('css')
    <style>
        .preview-header {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 20px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .preview-info h1 {
            font-size: 20px;
            font-weight: 600;
            color: #1f2937;
            margin: 0 0 8px 0;
        }

        .preview-meta {
            font-size: 14px;
            color: #6b7280;
        }

        .preview-meta span {
            margin-right: 16px;
        }

        .preview-actions {
            display: flex;
            gap: 12px;
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

        /* A4 Page Container */
        .pages-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 30px;
            padding: 20px;
            background: #e5e7eb;
        }

        .a4-page {
            width: 210mm;
            min-height: 297mm;
            background: white;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            padding: 10mm;
            box-sizing: border-box;
        }

        .page-title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            color: #1e3a5f;
            margin-bottom: 5mm;
            padding-bottom: 2mm;
            border-bottom: 1px solid #e5e7eb;
        }

        .page-subtitle {
            text-align: center;
            font-size: 10px;
            color: #6b7280;
            margin-bottom: 5mm;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Cards Grid - 2 columns */
        .cards-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 5mm;
        }

        .card-cell {
            width: calc(50% - 2.5mm);
            display: flex;
            justify-content: center;
            margin-bottom: 3mm;
        }

        /* ID Card - Exact debit card size */
        .id-card {
            width: 85.6mm;
            height: 53.98mm;
            border: 1px solid #ccc;
            border-radius: 3mm;
            overflow: hidden;
            position: relative;
            flex-shrink: 0;
            font-family: 'Helvetica', 'Arial', sans-serif;
        }

        /* Front Card */
        .card-front {
            background-color: #1e3a5f;
        }

        .card-front-header {
            background-color: #ffffff;
            padding: 3mm 4mm;
            display: flex;
            align-items: center;
            gap: 2mm;
        }

        .logo-container {
            width: 10mm;
            height: 10mm;
            flex-shrink: 0;
        }

        .logo-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .logo-placeholder {
            width: 10mm;
            height: 10mm;
            background: #e5e7eb;
            border-radius: 2mm;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            font-size: 8px;
        }

        .school-info-header {
            flex: 1;
        }

        .school-name {
            font-size: 9px;
            font-weight: bold;
            color: #1e3a5f;
            line-height: 1.2;
        }

        .card-type {
            font-size: 7px;
            color: #4a5568;
            margin-top: 1mm;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card-front-body {
            padding: 3mm 4mm;
            display: flex;
            gap: 3mm;
            background-color: #1e3a5f;
            height: 35mm;
        }

        .photo-container {
            width: 20mm;
            height: 25mm;
            flex-shrink: 0;
        }

        .student-photo {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border: 2px solid white;
            border-radius: 2mm;
            background: #e5e7eb;
        }

        .photo-placeholder {
            width: 100%;
            height: 100%;
            border: 2px solid white;
            border-radius: 2mm;
            background: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            font-size: 6px;
            text-align: center;
        }

        .student-details {
            flex: 1;
            color: white;
        }

        .student-name {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 2mm;
            text-transform: uppercase;
        }

        .detail-row {
            margin-bottom: 1.5mm;
        }

        .detail-label {
            font-size: 6px;
            color: #a0aec0;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .detail-value {
            font-size: 8px;
            color: white;
            font-weight: 500;
        }

        .card-front-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: #152a45;
            padding: 2mm 4mm;
            text-align: center;
        }

        .academic-year {
            font-size: 7px;
            color: #e2e8f0;
        }

        /* Back Card */
        .card-back {
            background: #f8f9fa;
        }

        .card-back-header {
            background: #1e3a5f;
            padding: 2mm 4mm;
            text-align: center;
        }

        .back-title {
            font-size: 8px;
            font-weight: bold;
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card-back-body {
            padding: 3mm 4mm;
        }

        .back-section {
            margin-bottom: 2mm;
        }

        .back-section-title {
            font-size: 6px;
            font-weight: bold;
            color: #1e3a5f;
            text-transform: uppercase;
            border-bottom: 0.5px solid #e5e7eb;
            padding-bottom: 0.5mm;
            margin-bottom: 1mm;
        }

        .back-info {
            font-size: 7px;
            color: #374151;
            line-height: 1.4;
        }

        .emergency-box {
            background: #fff3cd;
            padding: 2mm;
            border-radius: 1mm;
            margin-bottom: 2mm;
        }

        .emergency-label {
            font-size: 6px;
            font-weight: bold;
            color: #856404;
            text-transform: uppercase;
        }

        .emergency-value {
            font-size: 7px;
            color: #856404;
        }

        .motto {
            font-size: 6px;
            font-style: italic;
            color: #6b7280;
            text-align: center;
            margin-top: 2mm;
        }

        .card-back-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: #1e3a5f;
            padding: 2mm 4mm;
        }

        .terms-text {
            font-size: 5px;
            color: #cbd5e0;
            text-align: center;
            line-height: 1.3;
        }

        /* Print Styles */
        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm;
            }

            body {
                margin: 0;
                padding: 0;
                background: white;
            }

            .preview-header {
                display: none !important;
            }

            .pages-container {
                padding: 0;
                background: white;
                gap: 0;
            }

            .a4-page {
                box-shadow: none;
                page-break-after: always;
                margin: 0;
                padding: 10mm;
            }

            .a4-page:last-child {
                page-break-after: avoid;
            }
        }

        @media (max-width: 900px) {
            .preview-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .a4-page {
                width: 100%;
                min-height: auto;
                transform-origin: top left;
            }

            .pages-container {
                padding: 10px;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('students.index') }}">Students</a>
        @endslot
        @slot('li_2')
            <a class="text-muted font-size-14" href="{{ route('students.id-cards') }}">ID Cards</a>
        @endslot
        @slot('title')
            Preview
        @endslot
    @endcomponent

    <div class="preview-header">
        <div class="preview-info">
            <h1><i class="fas fa-id-card me-2"></i>ID Cards Preview</h1>
            <div class="preview-meta">
                <span><i class="fas fa-layer-group me-1"></i>{{ $selectedGrade }}</span>
                <span><i class="fas fa-chalkboard me-1"></i>{{ $selectedClass }}</span>
                <span><i class="fas fa-users me-1"></i>{{ $students->count() }} Students</span>
            </div>
        </div>
        <div class="preview-actions">
            <a href="{{ route('students.id-cards') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <button type="button" class="btn btn-success" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
            <form id="downloadForm" method="POST" action="{{ route('students.generate-id-cards') }}" style="display: inline;">
                @csrf
                <input type="hidden" name="grade_id" value="{{ $gradeId }}">
                <input type="hidden" name="class_id" value="{{ $classId }}">
                <button type="submit" class="btn btn-primary btn-loading" id="download-btn">
                    <span class="btn-text"><i class="fas fa-file-pdf"></i> Download PDF</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                        Downloading...
                    </span>
                </button>
            </form>
        </div>
    </div>

    <div class="pages-container">
        @php
            $chunkedStudents = $students->chunk(10);
        @endphp

        @foreach ($chunkedStudents as $pageIndex => $pageStudents)
            <!-- Front Cards Page -->
            <div class="a4-page">
                <div class="page-title">{{ $school_data->school_name ?? 'School' }} - Student ID Cards</div>
                <div class="page-subtitle">Front Side</div>

                <div class="cards-grid">
                    @foreach ($pageStudents as $student)
                        @php
                            $currentClass = $student->currentClassRelation->first();
                        @endphp
                        <div class="card-cell">
                            <div class="id-card card-front">
                                <div class="card-front-header">
                                    <div class="logo-container">
                                        @if ($school_data->logo_path)
                                            <img src="{{ asset($school_data->logo_path) }}" alt="Logo">
                                        @else
                                            <div class="logo-placeholder"><i class="fas fa-school"></i></div>
                                        @endif
                                    </div>
                                    <div class="school-info-header">
                                        <div class="school-name">{{ $school_data->school_name ?? 'School Name' }}</div>
                                        <div class="card-type">Student Identification Card</div>
                                    </div>
                                </div>
                                <div class="card-front-body">
                                    <div class="photo-container">
                                        @if ($student->photo_path)
                                            <img src="{{ asset($student->photo_path) }}" alt="Photo" class="student-photo">
                                        @else
                                            <div class="photo-placeholder">No Photo</div>
                                        @endif
                                    </div>
                                    <div class="student-details">
                                        <div class="student-name">{{ $student->first_name }} {{ $student->last_name }}</div>
                                        <div class="detail-row">
                                            <div class="detail-label">Class</div>
                                            <div class="detail-value">{{ $currentClass->name ?? 'N/A' }}</div>
                                        </div>
                                        <div class="detail-row">
                                            <div class="detail-label">Grade</div>
                                            <div class="detail-value">{{ $currentClass->grade->name ?? 'N/A' }}</div>
                                        </div>
                                        <div class="detail-row">
                                            <div class="detail-label">Class Teacher</div>
                                            <div class="detail-value">
                                                {{ optional($currentClass->teacher)->first_name ?? '' }}
                                                {{ optional($currentClass->teacher)->last_name ?? 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-front-footer">
                                    <div class="academic-year">Academic Year: {{ $term->year ?? now()->year }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Back Cards Page -->
            <div class="a4-page">
                <div class="page-title">{{ $school_data->school_name ?? 'School' }} - Student ID Cards</div>
                <div class="page-subtitle">Back Side</div>

                <div class="cards-grid">
                    @foreach ($pageStudents as $student)
                        <div class="card-cell">
                            <div class="id-card card-back">
                                <div class="card-back-header">
                                    <div class="back-title">{{ $school_data->school_name ?? 'School' }}</div>
                                </div>
                                <div class="card-back-body">
                                    <div class="back-section">
                                        <div class="back-section-title">School Contact</div>
                                        <div class="back-info">
                                            {{ $school_data->physical_address ?? '' }}<br>
                                            Tel: {{ $school_data->telephone ?? 'N/A' }}
                                            @if ($school_data->email ?? false)
                                                <br>{{ $school_data->email }}
                                            @endif
                                        </div>
                                    </div>
                                    <div class="emergency-box">
                                        <div class="emergency-label">Emergency Contact</div>
                                        <div class="emergency-value">
                                            @if ($student->sponsor)
                                                {{ $student->sponsor->title ?? '' }}
                                                {{ $student->sponsor->first_name ?? '' }}
                                                {{ $student->sponsor->last_name ?? '' }}<br>
                                                Tel: {{ $student->sponsor->telephone ?? $student->sponsor->cell_phone ?? 'N/A' }}
                                            @else
                                                Not specified
                                            @endif
                                        </div>
                                    </div>
                                    @if ($school_data->motto ?? false)
                                        <div class="motto">"{{ $school_data->motto }}"</div>
                                    @endif
                                </div>
                                <div class="card-back-footer">
                                    <div class="terms-text">
                                        This card is the property of {{ $school_data->school_name ?? 'the school' }}.
                                        If found, please return to the school office.
                                        Valid for {{ $term->year ?? now()->year }} academic year only.
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('downloadForm');
            if (!form) return;

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const submitBtn = document.getElementById('download-btn');
                if (submitBtn) {
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                }

                const formData = new FormData(form);

                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to generate PDF');
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
                    resetButton();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error generating PDF. Please try again.');
                    resetButton();
                });
            });

            function resetButton() {
                const submitBtn = document.getElementById('download-btn');
                if (submitBtn) {
                    submitBtn.classList.remove('loading');
                    submitBtn.disabled = false;
                }
            }
        });
    </script>
@endsection
