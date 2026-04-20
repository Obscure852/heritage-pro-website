@extends('layouts.master')
@section('title')
    Edit Grading Scale | Academic Management
@endsection

@section('css')
    <style>
        .settings-container {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 24px;
        }

        .settings-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .settings-header h3 {
            margin: 0 0 8px 0;
            font-size: 22px;
            font-weight: 600;
        }

        .settings-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .settings-body {
            padding: 24px;
        }

        /* Header Badges */
        .header-badges {
            display: flex;
            gap: 10px;
            margin-top: 12px;
            flex-wrap: wrap;
        }

        .header-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            font-size: 12px;
        }

        .header-badge i {
            margin-right: 6px;
        }

        .header-badge.warning {
            background: rgba(251, 191, 36, 0.3);
        }

        .header-badge.success {
            background: rgba(34, 197, 94, 0.3);
        }

        /* Help Text */
        .help-text {
            background: #f0f9ff;
            border-left: 4px solid #3b82f6;
            padding: 16px;
            border-radius: 0 3px 3px 0;
            margin-bottom: 24px;
        }

        .help-text p {
            margin: 0;
            color: #1e40af;
            font-size: 14px;
            line-height: 1.5;
        }

        .help-text p i {
            margin-right: 8px;
        }

        .help-text.warning {
            background: #fffbeb;
            border-left-color: #f59e0b;
        }

        .help-text.warning p {
            color: #92400e;
        }

        /* Alert Styling */
        .alert-styled {
            border-radius: 3px;
            padding: 16px;
            margin-bottom: 20px;
            border: none;
        }

        .alert-styled.alert-success {
            background: #d1fae5;
            color: #065f46;
        }

        .alert-styled.alert-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .alert-styled.alert-warning {
            background: #fef3c7;
            color: #92400e;
        }

        /* Grading Table */
        .grading-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        .grading-table thead th {
            background: #f9fafb;
            padding: 12px 16px;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e5e7eb;
            text-align: left;
        }

        .grading-table tbody td {
            padding: 10px 8px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }

        .grading-table tbody tr:hover {
            background: #f9fafb;
        }

        .grading-table tbody tr:last-child td {
            border-bottom: none;
        }

        .grading-table .form-control {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 8px 12px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .grading-table .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .grading-table .input-description {
            width: 100%;
            min-width: 200px;
        }

        .grading-table .input-score {
            width: 80px;
            text-align: center;
        }

        .grading-table .input-grade {
            width: 60px;
            text-align: center;
            text-transform: uppercase;
        }

        .grading-table .input-points {
            width: 70px;
            text-align: center;
        }

        /* Row Number */
        .row-number {
            width: 40px;
            font-weight: 600;
            color: #6b7280;
            font-size: 13px;
        }

        /* Buttons */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            color: #4b5563;
            font-weight: 500;
            font-size: 14px;
            background: white;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-back:hover {
            background: #f9fafb;
            color: #374151;
            border-color: #d1d5db;
        }

        .btn-save {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 24px;
            border: none;
            border-radius: 3px;
            color: white;
            font-weight: 500;
            font-size: 14px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-save:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }

        .btn-save:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn-save.loading .btn-text {
            display: none;
        }

        .btn-save.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }

        /* Copy Section */
        .copy-section {
            background: #f9fafb;
            border-radius: 3px;
            padding: 20px;
            margin-top: 24px;
            border: 1px solid #e5e7eb;
        }

        .copy-section-title {
            font-size: 15px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .copy-section-title i {
            color: #6b7280;
        }

        .copy-section .form-label {
            font-weight: 500;
            color: #374151;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .copy-section .form-select {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 10px 14px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .copy-section .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .btn-copy {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border: none;
            border-radius: 3px;
            color: white;
            font-weight: 500;
            font-size: 14px;
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-copy:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(107, 114, 128, 0.4);
        }

        .btn-copy:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .btn-copy.loading .btn-text {
            display: none;
        }

        .btn-copy.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .settings-body {
                padding: 16px;
            }

            .grading-table {
                display: block;
                overflow-x: auto;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn-back,
            .btn-save {
                width: 100%;
                justify-content: center;
            }

            .header-badges {
                flex-direction: column;
                gap: 6px;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('subjects.index') }}">Subject Allocations</a>
        @endslot
        @slot('title')
            Edit Grading Scale
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-12">
            @if (isset($isPastTerm) && $isPastTerm)
                <div class="alert alert-styled alert-warning alert-dismissible fade show" role="alert">
                    <i class="bx bx-error me-2"></i>
                    <strong>Past Term Warning:</strong> You are editing a grading scale from a previous term
                    (Term {{ $selectedTerm->term ?? 'Unknown' }}, {{ $selectedTerm->year ?? '' }}).
                    Changes may affect historical records.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

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
                @foreach ($errors->all() as $error)
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                                <i class="mdi mdi-block-helper label-icon"></i><strong>{{ $error }}</strong>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

            <div class="settings-container">
                <div class="settings-header">
                    <h3><i class="bx bx-slider-alt me-2"></i>Edit Grading Scale</h3>
                    <p>Modify the grading scale for this subject</p>
                    <div class="header-badges">
                        <div class="header-badge">
                            <i class="bx bx-book-open"></i>
                            {{ $subject->subject->name ?? 'Unknown Subject' }} - {{ $subject->grade->name ?? 'N/A' }}
                        </div>
                        @if (isset($isPastTerm) && $isPastTerm)
                            <div class="header-badge warning">
                                <i class="bx bx-time"></i>
                                Past Term: {{ $selectedTerm->term ?? 'Unknown' }}, {{ $selectedTerm->year ?? '' }}
                            </div>
                        @else
                            <div class="header-badge success">
                                <i class="bx bx-check-circle"></i>
                                Current Term: {{ $currentTerm->term ?? 'Unknown' }}, {{ $currentTerm->year ?? '' }}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="settings-body">
                    @if (isset($isPastTerm) && $isPastTerm)
                        <div class="help-text warning">
                            <p><i class="bx bx-error"></i>You are editing historical data. Changes to past term grading scales may affect student records and reports from that term.</p>
                        </div>
                    @else
                        <div class="help-text">
                            <p><i class="bx bx-info-circle"></i>Update the grading scale by modifying score ranges and their corresponding grades. Changes will apply to this subject's assessments.</p>
                        </div>
                    @endif

                    <form class="needs-validation" method="post"
                        action="{{ route('subjects.update-grading-scale', $subject->id) }}" id="gradingScaleForm">
                        @csrf
                        @if (isset($isPastTerm) && $isPastTerm)
                            <input type="hidden" name="past_term_edit" value="1">
                        @endif

                        <input name="grade_subject_id" type="hidden" value="{{ $subject->id }}">
                        <input name="term_id" type="hidden" value="{{ $currentTerm->id }}">
                        <input name="grade_id" type="hidden" value="{{ $subject->grade->id }}">

                        <div class="table-responsive">
                            <table class="grading-table">
                                <thead>
                                    <tr>
                                        <th class="row-number">#</th>
                                        <th>Description</th>
                                        <th>From (%)</th>
                                        <th>To (%)</th>
                                        <th>Grade</th>
                                        <th>Points</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $descriptionPlaceholders = ['e.g., Excellent', 'e.g., Very Good', 'e.g., Good', 'e.g., Above Average', 'e.g., Average', 'e.g., Below Average', 'e.g., Fair', 'e.g., Poor', 'e.g., Very Poor', 'e.g., Fail'];
                                    @endphp
                                    @for ($i = 0; $i < 10; $i++)
                                        <tr>
                                            <td class="row-number">{{ $i + 1 }}</td>
                                            <td>
                                                <input name="description[]" type="text"
                                                    value="{{ $gradingScales[$i]->description ?? '' }}"
                                                    class="form-control input-description"
                                                    placeholder="{{ $descriptionPlaceholders[$i] }}">
                                            </td>
                                            <td>
                                                <input name="min_score[]" type="number" step="0.01"
                                                    value="{{ $gradingScales[$i]->min_score ?? '' }}"
                                                    class="form-control input-score"
                                                    placeholder="0">
                                            </td>
                                            <td>
                                                <input name="max_score[]" type="number" step="0.01"
                                                    value="{{ $gradingScales[$i]->max_score ?? '' }}"
                                                    class="form-control input-score"
                                                    placeholder="100">
                                            </td>
                                            <td>
                                                <input name="grade[]" type="text"
                                                    value="{{ $gradingScales[$i]->grade ?? '' }}"
                                                    class="form-control input-grade"
                                                    placeholder="A"
                                                    maxlength="2">
                                            </td>
                                            <td>
                                                <input name="points[]" type="number" step="0.01"
                                                    value="{{ $gradingScales[$i]->points ?? '' }}"
                                                    class="form-control input-points"
                                                    placeholder="0">
                                            </td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>

                        <div class="form-actions">
                            <a href="{{ route('subjects.index') }}" class="btn-back">
                                <i class="bx bx-arrow-back"></i>
                                Back to Subjects
                            </a>
                            @can('manage-academic')
                                <button type="submit" class="btn-save">
                                    <span class="btn-text"><i class="fas fa-save"></i> Update Grading Scale</span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        Updating...
                                    </span>
                                </button>
                            @endcan
                        </div>
                    </form>

                    <!-- Copy to Subject Section -->
                    <div class="copy-section">
                        <h5 class="copy-section-title">
                            <i class="bx bx-copy"></i>
                            Copy Grading Scale to Another Subject
                        </h5>
                        <form action="{{ route('subjects.copy-grading-scale', ['fromSubjectId' => $subject->id]) }}"
                            method="POST" id="copyForm">
                            @csrf
                            <div class="mb-3">
                                <label for="copyToSubject" class="form-label">Select Target Subject</label>
                                <select name="copyToSubject" id="copyToSubject" class="form-select">
                                    <option value="">Select subject...</option>
                                    @foreach ($subjects as $subjectOption)
                                        @if ($subjectOption->subject !== null && $subjectOption->subject->name !== null)
                                            <option value="{{ $subjectOption->id }}">
                                                {{ $subjectOption->subject->name }} ({{ $subjectOption->grade->name ?? 'N/A' }})
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            @can('manage-academic')
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn-copy">
                                        <span class="btn-text"><i class="bx bx-copy"></i> Copy to Subject</span>
                                        <span class="btn-spinner d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            Copying...
                                        </span>
                                    </button>
                                </div>
                            @endcan
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Loading animation for main form
            const mainForm = document.getElementById('gradingScaleForm');
            mainForm.addEventListener('submit', function(e) {
                if (!mainForm.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                } else {
                    const btn = mainForm.querySelector('.btn-save');
                    if (btn) {
                        btn.classList.add('loading');
                        btn.disabled = true;
                    }
                }
                mainForm.classList.add('was-validated');
            }, false);

            // Loading animation for copy form
            const copyForm = document.getElementById('copyForm');
            copyForm.addEventListener('submit', function(e) {
                const select = copyForm.querySelector('#copyToSubject');
                if (!select.value) {
                    e.preventDefault();
                    alert('Please select a subject to copy to.');
                    return;
                }
                const btn = copyForm.querySelector('.btn-copy');
                if (btn) {
                    btn.classList.add('loading');
                    btn.disabled = true;
                }
            });
        });
    </script>
@endsection
