@extends('layouts.master')
@section('title')
    Class List Report
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

        /* Export bar */
        .export-bar {
            display: flex;
            gap: 12px;
            align-items: center;
            justify-content: flex-end;
            padding: 16px 0;
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

        .btn-outline-secondary {
            background: white;
            color: #6b7280;
            border: 1px solid #d1d5db;
        }

        .btn-outline-secondary:hover {
            background: #f3f4f6;
            color: #374151;
            transform: translateY(-1px);
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

        /* Preview section */
        .preview-section {
            margin-top: 28px;
            border-top: 2px solid #e5e7eb;
            padding-top: 20px;
        }

        .preview-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .preview-title {
            font-size: 17px;
            font-weight: 600;
            color: #1f2937;
            margin: 0 0 4px 0;
        }

        .preview-stats {
            font-size: 13px;
            color: #6b7280;
        }

        .preview-stats strong {
            color: #374151;
        }

        .loading-indicator {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
        }

        .loading-indicator .spinner-border {
            width: 2rem;
            height: 2rem;
            margin-bottom: 12px;
        }

        /* Print styles */
        @media print {
            body {
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
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            .printable .table {
                width: 100%;
                margin: 0;
            }

            .printable .table th,
            .printable .table td {
                padding: 6px;
                text-align: left;
            }

            .no-print {
                display: none !important;
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

            .export-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .export-bar .btn {
                justify-content: center;
            }

            .preview-header {
                flex-direction: column;
                gap: 8px;
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
            Class List Report
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
        <div class="page-header no-print">
            <h1 class="page-title"><i class="bx bx-list-ol me-2"></i>Class List Report</h1>
            <div class="school-info">
                <strong>{{ $school_data->school_name ?? 'School Name' }}</strong><br>
                {{ $school_data->physical_address ?? '' }}<br>
                Tel: {{ $school_data->telephone ?? '' }}
            </div>
        </div>

        <div class="help-text no-print">
            <div class="help-title">Generate Class Lists</div>
            <div class="help-content">
                Select a grade and then choose a class or optional subject. The class list will load automatically.
                You can then print, export to Excel, or export to PDF.
            </div>
        </div>

        {{-- Selection Controls --}}
        <div class="no-print">
            <h3 class="section-title"><i class="bx bx-filter-alt me-2"></i>Selection Criteria</h3>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label" for="grade">Grade</label>
                    <select class="form-select" id="grade">
                        <option value="">Select Grade</option>
                        @foreach ($grades as $grade)
                            <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group hidden-section" id="selection-group">
                    <label class="form-label" for="selection">Class / Optional Subject</label>
                    <select class="form-select" id="selection">
                        <option value="">Select Class or Optional Subject</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Preview Section (hidden until data loads) --}}
        <div class="hidden-section" id="preview-section">
            {{-- Export / Print bar --}}
            <div class="export-bar no-print">
                <button type="button" class="btn btn-outline-secondary" id="print-btn">
                    <i class="bx bx-printer"></i> Print
                </button>
                <form id="exportForm" method="POST" action="{{ route('students.generate-class-list-report') }}" style="display:inline-flex; gap:12px;">
                    @csrf
                    <input type="hidden" name="selection" id="exportSelection">
                    <input type="hidden" name="export_action" id="exportAction">
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
                </form>
            </div>

            {{-- Printable area --}}
            <div class="printable" id="printable-area">
                <div class="preview-header">
                    <div>
                        <h4 class="preview-title" id="preview-title"></h4>
                        <div class="preview-stats" id="preview-stats"></div>
                    </div>
                    <div class="text-end text-muted" style="font-size: 12px;" id="preview-date"></div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-sm">
                        <thead style="background-color: #6b7280; color: white;">
                            <tr>
                                <th style="width:35px">#</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th style="width:60px">Gender</th>
                                <th style="width:55px">PSLE</th>
                                <th style="width:150px"></th>
                                <th style="width:150px"></th>
                                <th style="width:150px"></th>
                                <th style="width:150px"></th>
                                <th style="width:150px"></th>
                            </tr>
                        </thead>
                        <tbody id="preview-tbody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Loading indicator --}}
        <div class="hidden-section" id="loading-section">
            <div class="loading-indicator">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div>Loading class list...</div>
            </div>
        </div>

        {{-- Empty state --}}
        <div class="hidden-section" id="empty-section">
            <div class="alert alert-info mt-4">
                No students found for the selected class or optional subject.
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const gradeSelect = document.getElementById('grade');
            const selectionSelect = document.getElementById('selection');
            const exportForm = document.getElementById('exportForm');
            const exportSelection = document.getElementById('exportSelection');
            const exportAction = document.getElementById('exportAction');

            // Grade change → fetch options
            gradeSelect.addEventListener('change', function() {
                hideSection('preview-section');
                hideSection('empty-section');

                if (this.value) {
                    fetchClassListOptions(this.value);
                } else {
                    hideSection('selection-group');
                }
            });

            // Selection change → fetch preview
            selectionSelect.addEventListener('change', function() {
                if (this.value) {
                    fetchPreview(this.value);
                } else {
                    hideSection('preview-section');
                    hideSection('empty-section');
                }
            });

            // Print button
            document.getElementById('print-btn').addEventListener('click', function() {
                window.print();
            });

            // Export button handlers
            document.getElementById('export-to-excel').addEventListener('click', function(e) {
                exportAction.value = 'excel';
            });

            document.getElementById('export-to-pdf').addEventListener('click', function(e) {
                exportAction.value = 'pdf';
            });

            // Export form submit – loading state + re-enable
            exportForm.addEventListener('submit', function(e) {
                const clicked = exportAction.value === 'excel'
                    ? document.getElementById('export-to-excel')
                    : document.getElementById('export-to-pdf');

                if (clicked) {
                    clicked.classList.add('loading');
                }

                const buttons = exportForm.querySelectorAll('button[type="submit"]');

                setTimeout(function() {
                    buttons.forEach(btn => btn.disabled = true);
                }, 0);

                // Re-enable after download starts (page stays)
                setTimeout(function() {
                    buttons.forEach(btn => {
                        btn.disabled = false;
                        btn.classList.remove('loading');
                    });
                }, 3000);
            });

            // Alert auto-dismiss
            document.querySelectorAll('.alert').forEach(function(alert) {
                setTimeout(function() {
                    const btn = alert.querySelector('.btn-close');
                    if (btn) btn.click();
                }, 5000);
            });

            function fetchClassListOptions(gradeId) {
                selectionSelect.innerHTML = '<option value="">Loading...</option>';
                selectionSelect.disabled = true;

                fetch('{{ route("students.class-list-options") }}', {
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
                .then(data => {
                    selectionSelect.innerHTML = '<option value="">Select Class or Optional Subject</option>';

                    if (data.classes && data.classes.length > 0) {
                        const group = document.createElement('optgroup');
                        group.label = 'Classes';
                        data.classes.forEach(item => {
                            const opt = document.createElement('option');
                            opt.value = item.id;
                            opt.textContent = item.label || item.name;
                            group.appendChild(opt);
                        });
                        selectionSelect.appendChild(group);
                    }

                    if (data.optional_subjects && data.optional_subjects.length > 0) {
                        const group = document.createElement('optgroup');
                        group.label = 'Optional Subjects';
                        data.optional_subjects.forEach(item => {
                            const opt = document.createElement('option');
                            opt.value = item.id;
                            opt.textContent = item.label || item.name;
                            group.appendChild(opt);
                        });
                        selectionSelect.appendChild(group);
                    }

                    selectionSelect.disabled = false;
                    showSection('selection-group');
                })
                .catch(error => {
                    console.error('Error fetching options:', error);
                    selectionSelect.innerHTML = '<option value="">Error loading options</option>';
                    selectionSelect.disabled = false;
                    showSection('selection-group');
                });
            }

            function fetchPreview(selection) {
                hideSection('preview-section');
                hideSection('empty-section');
                showSection('loading-section');

                // Update the hidden export form value
                exportSelection.value = selection;

                fetch('{{ route("students.class-list-preview") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({ selection: selection }),
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    hideSection('loading-section');

                    if (!data.students || data.students.length === 0) {
                        showSection('empty-section');
                        return;
                    }

                    // Populate header
                    document.getElementById('preview-title').textContent =
                        data.grade_name + ' — ' + data.list_name;
                    let statsHtml = 'Total: <strong>' + data.statistics.total + '</strong> &nbsp;|&nbsp; ' +
                        'Male: <strong>' + data.statistics.male + '</strong> &nbsp;|&nbsp; ' +
                        'Female: <strong>' + data.statistics.female + '</strong>';
                    if (data.statistics.show_boarding) {
                        statsHtml += ' &nbsp;|&nbsp; Boarding: <strong>' + data.statistics.boarding + '</strong>' +
                            ' &nbsp;|&nbsp; Day: <strong>' + data.statistics.day + '</strong>';
                    }
                    document.getElementById('preview-stats').innerHTML = statsHtml;
                    document.getElementById('preview-date').textContent =
                        'Generated: ' + new Date().toLocaleDateString('en-GB', {
                            day: '2-digit', month: 'short', year: 'numeric',
                            hour: '2-digit', minute: '2-digit'
                        });

                    // Populate table
                    const tbody = document.getElementById('preview-tbody');
                    tbody.innerHTML = '';

                    data.students.forEach(function(student) {
                        const tr = document.createElement('tr');
                        tr.innerHTML =
                            '<td>' + escapeHtml(String(student.index)) + '</td>' +
                            '<td>' + escapeHtml(student.first_name) + '</td>' +
                            '<td>' + escapeHtml(student.last_name) + '</td>' +
                            '<td>' + escapeHtml(student.gender) + '</td>' +
                            '<td>' + escapeHtml(student.psle) + '</td>' +
                            '<td></td><td></td><td></td><td></td><td></td>';
                        tbody.appendChild(tr);
                    });

                    showSection('preview-section');
                })
                .catch(error => {
                    console.error('Error fetching preview:', error);
                    hideSection('loading-section');
                    hideSection('preview-section');
                    document.getElementById('empty-section').querySelector('.alert').textContent =
                        'Error loading class list. Please try again.';
                    showSection('empty-section');
                });
            }

            function showSection(id) {
                const el = document.getElementById(id);
                if (el) el.classList.add('visible');
            }

            function hideSection(id) {
                const el = document.getElementById(id);
                if (el) el.classList.remove('visible');
            }

            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
        });

        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
@endsection
