@extends('layouts.master')
@section('title')
    Edit Subject | Academic Management
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

        .help-text.warning {
            border-left-color: #f59e0b;
            background: #fffbeb;
        }

        .help-text.warning .help-title {
            color: #b45309;
        }

        .help-text.warning .help-content {
            color: #92400e;
        }

        /* Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .form-group {
            margin-bottom: 0;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
            font-size: 14px;
        }

        .form-group label .required {
            color: #dc2626;
        }

        .form-group .form-control,
        .form-group .form-select {
            border: 1px solid #d1d5db;
            border-radius: 3px;
            padding: 10px 12px;
            font-size: 14px;
            transition: all 0.2s ease;
            width: 100%;
        }

        .form-group .form-control:focus,
        .form-group .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .form-group .form-hint {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }

        .form-group .form-hint.warning {
            color: #d97706;
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            color: #374151;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-back:hover {
            background: #f3f4f6;
            color: #1f2937;
        }

        .btn-save {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            border-radius: 3px;
            color: white;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .btn-save:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-save.loading .btn-text {
            display: none;
        }

        .btn-save.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }

        .btn-save:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* Confirmation Warning Card */
        .warning-card {
            border: 1px solid #dc2626;
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 24px;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.15);
        }

        .warning-card .warning-header {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            padding: 16px 20px;
        }

        .warning-card .warning-header h5 {
            margin: 0;
            font-weight: 600;
        }

        .warning-card .warning-body {
            padding: 20px;
            background: white;
        }

        .affected-list {
            border: 1px solid #fecaca;
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 16px;
        }

        .affected-list .list-group-item {
            border-left: none;
            border-right: none;
            padding: 10px 16px;
            background: #fef2f2;
        }

        .affected-list .list-group-item:first-child {
            border-top: none;
        }

        .fancy-checkbox {
            padding: 12px 16px;
            border: 1px solid #fecaca;
            border-radius: 3px;
            background: #fef2f2;
            margin-bottom: 16px;
        }

        .btn-cancel {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            color: #374151;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-cancel:hover {
            background: #f3f4f6;
            color: #1f2937;
        }

        .btn-danger-confirm {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border: none;
            border-radius: 3px;
            color: white;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-danger-confirm:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
            color: white;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .settings-header {
                padding: 20px;
            }

            .settings-body {
                padding: 16px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn-back,
            .btn-save {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="{{ route('subjects.index') }}">Subjects</a>
        @endslot
        @slot('title')
            Edit Subject
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

    @if (session('warning'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-warning alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-alert-outline label-icon"></i><strong>{{ session('warning') }}</strong>
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

    <!-- Confirmation Warning Box for Type Change -->
    @if (session('requires_confirmation') && session('affected_data'))
        <div class="warning-card">
            <div class="warning-header">
                <h5><i class="mdi mdi-alert-circle me-2"></i>Warning: This change will delete related data</h5>
            </div>
            <div class="warning-body">
                <p class="mb-3">Changing this subject's type from
                    <strong>{{ session('form_data')['type'] ? 'Core' : 'Optional' }}</strong>
                    to <strong>{{ !session('form_data')['type'] ? 'Core' : 'Optional' }}</strong> will
                    permanently delete the following data:
                </p>

                <div class="affected-list">
                    <ul class="list-group list-group-flush">
                        @foreach (session('affected_data') as $key => $count)
                            @if ($count > 0)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ Str::title(Str::replace('_', ' ', $key)) }}
                                    <span class="badge bg-danger rounded-pill">{{ $count }}</span>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>

                <div class="alert alert-danger mb-3">
                    <i class="mdi mdi-information-outline me-2"></i>
                    This action will permanently delete student records, tests, and grades associated with this subject.
                    This data cannot be recovered after deletion.
                </div>

                <form action="{{ route('subject.update-subject', ['id' => session('grade_subject_id')]) }}" method="POST">
                    @csrf
                    @foreach (session('form_data') as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach

                    <div class="fancy-checkbox">
                        <div class="form-check mb-0">
                            <input type="checkbox" class="form-check-input" id="confirm_change" name="confirm_change"
                                value="1" required>
                            <label class="form-check-label fw-medium" for="confirm_change">
                                I understand that this change will permanently delete the data listed above
                            </label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('subjects.index') }}" class="btn-cancel">
                            <i class="bx bx-x"></i> Cancel
                        </a>
                        <button type="submit" class="btn-danger-confirm">
                            <i class="bx bx-check"></i> Confirm Change
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @else
        <!-- Regular Edit Form -->
        <div class="row">
            <div class="col-12">
                <div class="settings-container">
                    <div class="settings-header">
                        <h3><i class="fas fa-edit me-2"></i>Edit Subject: {{ $grade_subject->subject->name ?? 'Subject' }}
                        </h3>
                        <p>Update subject allocation settings for {{ $grade_subject->grade->name ?? 'this grade' }}</p>
                    </div>

                    <div class="settings-body">
                        <div class="help-text">
                            <div class="help-title">Subject Settings</div>
                            <div class="help-content">
                                Configure the subject's properties for this grade level. Fields marked with <span
                                    style="color: #dc2626;">*</span> are required.
                            </div>
                        </div>

                        <form class="needs-validation" method="post"
                            action="{{ route('subject.update-subject', ['id' => $grade_subject->id]) }}" novalidate
                            id="editSubjectForm">
                            @csrf

                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="sequence">Sequence <span class="required">*</span></label>
                                    <select name="sequence" id="sequence" class="form-select" required>
                                        <option value="">Choose...</option>
                                        @for ($i = 1; $i <= 35; $i++)
                                            <option value="{{ $i }}"
                                                {{ old('sequence', $grade_subject->sequence ?? 0) == $i ? 'selected' : '' }}>
                                                {{ $i }}
                                            </option>
                                        @endfor
                                    </select>
                                    <div class="form-hint">
                                        <i class="bx bx-info-circle"></i> Order in which subjects appear (lower numbers
                                        first)
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="grade_id">Grade <span class="required">*</span></label>
                                    <select id="grade_id" class="form-select" disabled>
                                        <option value="">Choose...</option>
                                        @if (!empty($grades))
                                            @foreach ($grades as $grade)
                                                <option value="{{ $grade->id }}"
                                                    {{ $grade->id == $grade_subject->grade_id ? 'selected' : '' }}>
                                                    {{ $grade->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <input type="hidden" name="grade_id" value="{{ $grade_subject->grade_id }}">
                                    <div class="form-hint warning">
                                        <i class="bx bx-lock-alt"></i> Grade changes are locked for existing subject
                                        allocations. Create a new allocation in the target grade instead.
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="subject_id">Subject <span class="required">*</span></label>
                                    <select name="subject_id" id="subject_id" class="form-select">
                                        <option value="">Choose...</option>
                                        @foreach ($subjects as $subject)
                                            @if (!is_null($subject->subject))
                                                <option value="{{ $subject->subject->id }}"
                                                    {{ $grade_subject->id == $subject->id ? 'selected' : '' }}>
                                                    {{ $subject->subject->name }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="department_id">Department <span class="required">*</span></label>
                                    <select name="department_id" id="department_id" class="form-select">
                                        <option value="">Choose...</option>
                                        @foreach ($departments as $department)
                                            <option value="{{ $department->id }}"
                                                {{ trim(strtolower($grade_subject->department_id)) == trim(strtolower($department->id)) ? 'selected' : '' }}>
                                                {{ $department->name ?? '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="type">Type <span class="required">*</span></label>
                                    @can('view-system-admin')
                                        <select name="type" id="type" class="form-select">
                                            <option value="">Choose...</option>
                                            <option value="1" {{ $grade_subject->type == true ? 'selected' : '' }}>Core
                                            </option>
                                            <option value="0" {{ $grade_subject->type == false ? 'selected' : '' }}>
                                                Optional</option>
                                        </select>
                                        <div class="form-hint warning">
                                            <i class="bx bx-error-circle"></i> Changing type may delete existing test records
                                        </div>
                                    @else
                                        <select name="type" class="form-select" disabled>
                                            <option value="">Choose...</option>
                                            <option value="1" {{ $grade_subject->type == true ? 'selected' : '' }}>Core
                                            </option>
                                            <option value="0" {{ $grade_subject->type == false ? 'selected' : '' }}>
                                                Optional</option>
                                        </select>
                                        <input type="hidden" name="type" value="{{ $grade_subject->type ? '1' : '0' }}">
                                    @endcan
                                </div>

                                <div class="form-group">
                                    <label for="mandatory">Mandatory <span class="required">*</span></label>
                                    @can('view-system-admin')
                                        <select name="mandatory" id="mandatory" class="form-select">
                                            <option value="">Choose...</option>
                                            <option value="1" {{ $grade_subject->mandatory == true ? 'selected' : '' }}>
                                                Yes</option>
                                            <option value="0" {{ $grade_subject->mandatory == false ? 'selected' : '' }}>
                                                No</option>
                                        </select>
                                    @else
                                        <select name="mandatory" class="form-select" disabled>
                                            <option value="">Choose...</option>
                                            <option value="1" {{ $grade_subject->mandatory == true ? 'selected' : '' }}>
                                                Yes</option>
                                            <option value="0" {{ $grade_subject->mandatory == false ? 'selected' : '' }}>
                                                No</option>
                                        </select>
                                        <input type="hidden" name="mandatory"
                                            value="{{ $grade_subject->mandatory ? '1' : '0' }}">
                                    @endcan
                                </div>

                                <div class="form-group">
                                    <label for="active">Active <span class="required">*</span></label>
                                    <select name="active" id="active" class="form-select">
                                        <option value="">Choose...</option>
                                        <option value="1" {{ $grade_subject->active == true ? 'selected' : '' }}>Yes
                                        </option>
                                        <option value="0" {{ $grade_subject->active == false ? 'selected' : '' }}>No
                                        </option>
                                    </select>
                                    <div class="form-hint warning">
                                        <i class="bx bx-info-circle"></i> Inactive subjects won't appear in assessments
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions">
                                <a href="{{ route('subjects.index') }}" class="btn-back">
                                    <i class="bx bx-arrow-back"></i> Back to Subjects
                                </a>
                                @if (!session('is_past_term'))
                                    <button type="submit" class="btn-save">
                                        <span class="btn-text"><i class="fas fa-save"></i> Update Subject</span>
                                        <span class="btn-spinner d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status"
                                                aria-hidden="true"></span>
                                            Updating...
                                        </span>
                                    </button>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Form submission loading state
            const form = document.getElementById('editSubjectForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (!form.checkValidity()) {
                        e.preventDefault();
                        e.stopPropagation();
                    } else {
                        const btn = form.querySelector('.btn-save');
                        if (btn) {
                            btn.classList.add('loading');
                            btn.disabled = true;
                        }
                    }
                    form.classList.add('was-validated');
                }, false);
            }

            @can('view-system-admin')
                // Type change warning
                const typeSelect = document.querySelector('select[name="type"]');
                const originalType = "{{ $grade_subject->type ? '1' : '0' }}";

                if (typeSelect) {
                    typeSelect.addEventListener('change', function() {
                        let warningHint = this.parentNode.querySelector('.form-hint.warning');

                        if (this.value !== originalType && this.value !== '') {
                            if (warningHint) {
                                warningHint.innerHTML =
                                    '<i class="bx bx-error-circle"></i> <strong>Warning:</strong> Changing type will delete existing data!';
                                warningHint.style.color = '#dc2626';
                            }
                        } else {
                            if (warningHint) {
                                warningHint.innerHTML =
                                    '<i class="bx bx-error-circle"></i> Changing type may delete existing test records';
                                warningHint.style.color = '#d97706';
                            }
                        }
                    });
                }
            @endcan
        });
    </script>
@endsection
