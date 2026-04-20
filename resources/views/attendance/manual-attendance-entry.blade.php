@extends('layouts.master')
@section('title')
    Manual Attendance Entry | Attendance
@endsection

@section('css')
    <style>
        .settings-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .settings-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .settings-header h3 {
            margin: 0;
            font-weight: 600;
        }

        .settings-header p {
            margin: 6px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .settings-body {
            padding: 24px;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px;
            border-left: 4px solid #3b82f6;
            border-radius: 0 3px 3px 0;
            margin-bottom: 20px;
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

        .form-section {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .form-section-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
            font-size: 14px;
        }

        .form-control,
        .form-select {
            border: 1px solid #d1d5db;
            border-radius: 3px;
            padding: 10px 12px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-text {
            color: #6b7280;
            font-size: 12px;
            margin-top: 4px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .btn-secondary {
            background: #6b7280;
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-secondary:hover {
            background: #4b5563;
            color: white;
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            color: white;
        }

        .btn-loading .btn-text {
            display: inline-flex;
            align-items: center;
        }

        .btn-loading .btn-spinner {
            display: none;
        }

        .btn-loading.loading .btn-text {
            display: none;
        }

        .btn-loading.loading .btn-spinner {
            display: inline-flex;
            align-items: center;
        }

        .progress-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
        }

        .char-counter {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 4px;
        }

        .char-counter .count {
            font-size: 12px;
            color: #6b7280;
        }

        .char-counter .count.warning {
            color: #f59e0b;
        }

        .char-counter .count.danger {
            color: #ef4444;
        }

        @media (max-width: 768px) {
            .settings-header {
                padding: 20px;
            }

            .settings-body {
                padding: 16px;
            }

            .header-content {
                flex-direction: column;
                gap: 12px;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('attendance.index') }}">Attendance</a>
        @endslot
        @slot('title')
            Manual Attendance Entry
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

    @php
        $studentNumber = $index + 1;
        $totalStudents = count(explode(',', $studentIds));
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="settings-container">
                <div class="settings-header">
                    <div class="d-flex justify-content-between align-items-start header-content">
                        <div>
                            <h3><i class="fas fa-user-edit me-2"></i>{{ $student->fullName ?? 'Student' }}</h3>
                            <p>Enter attendance details for this student</p>
                        </div>
                        <div class="progress-badge">
                            <i class="fas fa-users me-1"></i>
                            {{ $studentNumber }} of {{ $totalStudents }}
                        </div>
                    </div>
                </div>

                <div class="settings-body">
                    <div class="help-text">
                        <div class="help-title">Manual Entry Mode</div>
                        <div class="help-content">
                            Record attendance information manually for students. Use "Save and Next" to continue
                            to the next student, or "Save" to finish and return to the attendance page.
                        </div>
                    </div>

                    <form id="manualEntryForm" method="post" action="{{ route('attendance.manual-entry') }}">
                        @csrf
                        <input type="hidden" id="studentId" name="studentId" value="{{ $student->id ?? '' }}">
                        <input type="hidden" name="term_id" value="{{ $termId }}">
                        <input type="hidden" name="student_ids" value="{{ $studentIds }}">
                        <input type="hidden" name="index" value="{{ $index }}">

                        <div class="form-section">
                            <h6 class="form-section-title"><i class="fas fa-clipboard-list me-2"></i>Attendance Details</h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="daysAbsent" class="form-label">Days Absent <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('daysAbsent') is-invalid @enderror" id="daysAbsent" name="daysAbsent"
                                        placeholder="Enter number of days"
                                        value="{{ old('daysAbsent', $manualEntry->days_absent ?? '') }}" min="0" required>
                                    @error('daysAbsent')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Total days absent this term</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="schoolFeesOwing" class="form-label">Fees Owing</label>
                                    <div class="input-group">
                                        <span class="input-group-text">P</span>
                                        <input type="text" class="form-control" id="schoolFeesOwing" name="schoolFeesOwing"
                                            placeholder="0.00"
                                            value="{{ old('schoolFeesOwing', $manualEntry->school_fees_owing ?? '') }}">
                                    </div>
                                    <div class="form-text">Outstanding school fees amount</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="other" class="form-label">Additional Information</label>
                                <textarea class="form-control" id="other" name="other" rows="4"
                                    placeholder="Enter any additional notes about this student's attendance..."
                                    maxlength="200">{{ old('other', $manualEntry->other_info ?? '') }}</textarea>
                                <div class="char-counter">
                                    <span class="form-text">Optional notes about attendance</span>
                                    <span class="count" id="charCount">200 characters remaining</span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 flex-wrap">
                            <a href="{{ route('attendance.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back
                            </a>
                            @if ($index < $totalStudents - 1)
                                <button type="submit" name="action" value="save_and_next" class="btn btn-success btn-loading" id="saveNextBtn">
                                    <span class="btn-text"><i class="fas fa-forward me-1"></i> Save & Next</span>
                                    <span class="btn-spinner">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        Saving...
                                    </span>
                                </button>
                            @endif
                            <button type="submit" class="btn btn-primary btn-loading" id="saveBtn">
                                <span class="btn-text"><i class="fas fa-save me-1"></i> Save</span>
                                <span class="btn-spinner">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Saving...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            updateCharCount();
            $('#other').on('input', updateCharCount);

            // Form submit loading state
            const form = document.getElementById('manualEntryForm');
            const saveBtn = document.getElementById('saveBtn');
            const saveNextBtn = document.getElementById('saveNextBtn');

            if (form) {
                form.addEventListener('submit', function(e) {
                    const submitter = e.submitter;
                    if (submitter && submitter.name && submitter.value) {
                        let hidden = form.querySelector('input[name="' + submitter.name + '"][type="hidden"]');
                        if (!hidden) {
                            hidden = document.createElement('input');
                            hidden.type = 'hidden';
                            hidden.name = submitter.name;
                            form.appendChild(hidden);
                        }
                        hidden.value = submitter.value;
                    }
                    if (submitter) {
                        submitter.classList.add('loading');
                        submitter.disabled = true;
                    }
                });
            }
        });

        function updateCharCount() {
            var maxLength = 200;
            var currentLength = $('#other').val().length;
            var remaining = maxLength - currentLength;
            var countEl = $('#charCount');

            countEl.text(remaining + ' characters remaining');

            // Update color based on remaining characters
            countEl.removeClass('warning danger');
            if (remaining <= 20 && remaining > 0) {
                countEl.addClass('warning');
            } else if (remaining <= 0) {
                countEl.addClass('danger');
            }
        }
    </script>
@endsection
