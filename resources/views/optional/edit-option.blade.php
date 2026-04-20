@extends('layouts.master')
@section('title')
    Edit Option | Academic Management
@endsection

@section('css')
    <style>
        /* Main Container */
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

        .settings-header .term-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            margin-top: 8px;
        }

        .settings-header .term-badge.current {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .settings-header .term-badge.past {
            background: #ffc107;
            color: #212529;
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

        .help-text.warning {
            border-left-color: #f59e0b;
            background: #fffbeb;
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

        /* Form Group */
        .form-group {
            margin-bottom: 20px;
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

        .form-group .form-control:read-only {
            background: #f3f4f6;
            color: #6b7280;
        }

        .form-group .form-control::placeholder {
            color: #9ca3af;
        }

        .form-group .text-danger {
            font-size: 13px;
            margin-top: 4px;
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
        }

        /* Responsive */
        @media (max-width: 768px) {
            .settings-header {
                padding: 20px;
            }

            .settings-body {
                padding: 16px;
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
    @php
        $optionGradeLevel = optional($option->grade)->level;
        $groupingOptionsByLevel = $groupingOptionsByLevel ?? [];
        $groupingOptions = $groupingOptionsByLevel[$optionGradeLevel] ?? ['Core', 'Practicals', 'Generals', 'Other'];
    @endphp

    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="{{ route('optional.index') }}">Optional Subjects</a>
        @endslot
        @slot('title')
            Edit Option
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

    <div class="row">
        <div class="col-12">
            <div class="settings-container">
                <div class="settings-header">
                    <h3><i class="fas fa-edit me-2"></i>Edit Optional Subject</h3>
                    <p>Update the details for {{ $option->name ?? 'this optional subject' }}</p>
                    @if (isset($isPastTerm) && $isPastTerm)
                        <span class="term-badge past">
                            <i class="mdi mdi-clock-outline me-1"></i>
                            Past Term: {{ $selectedTerm->term ?? 'Unknown' }}, {{ $selectedTerm->year ?? '' }}
                        </span>
                    @else
                        <span class="term-badge current">
                            <i class="mdi mdi-clock-check-outline me-1"></i>
                            Current Term: {{ $currentTerm->term ?? 'Unknown' }}, {{ $currentTerm->year ?? '' }}
                        </span>
                    @endif
                </div>

                <div class="settings-body">
                    @if (isset($isPastTerm) && $isPastTerm)
                        <div class="help-text warning">
                            <div class="help-title"><i class="mdi mdi-alert me-1"></i>Past Term Warning</div>
                            <div class="help-content">
                                You are editing an optional subject from a previous term (Term {{ $selectedTerm->term ?? 'Unknown' }}, {{ $selectedTerm->year ?? '' }}).
                                Changes to past term data should be made with caution as they may affect historical records.
                            </div>
                        </div>
                    @else
                        <div class="help-text">
                            <div class="help-title">Edit Optional Subject</div>
                            <div class="help-content">
                                Update the details for this optional subject. Grade and Subject cannot be changed after creation.
                            </div>
                        </div>
                    @endif

                    <form class="needs-validation" method="POST"
                        action="{{ route('optional.update-option', ['id' => $option->id]) }}" novalidate>
                        @csrf

                        @if (isset($isPastTerm) && $isPastTerm)
                            <input type="hidden" name="past_term_edit" value="1">
                        @endif

                        <div class="form-group">
                            <label for="optionName">Option Name <span class="required">*</span></label>
                            <input type="text" id="optionName" name="name"
                                class="form-control @error('name') is-invalid @enderror" placeholder="e.g., 1AB*"
                                value="{{ old('name', $option->display_name) }}" maxlength="12" required>
                            @if ($errors->has('name'))
                                <div class="text-danger">{{ $errors->first('name') }}</div>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="gradeDisplay">Grade</label>
                            <input type="text" id="gradeDisplay" class="form-control"
                                value="{{ $option->grade->name ?? 'Unknown' }}" readonly>
                            <input type="hidden" name="grade_id" value="{{ $option->grade_id }}">
                        </div>

                        <div class="form-group">
                            <label for="subjectDisplay">Subject</label>
                            <input type="text" id="subjectDisplay" class="form-control"
                                value="{{ $option->gradeSubject->subject->name ?? 'Unknown' }}" readonly>
                            <input type="hidden" name="grade_subject_id" value="{{ $option->grade_subject_id }}">
                        </div>

                        <div class="form-group">
                            <label for="user_id">Subject Teacher <span class="required">*</span></label>
                            <select name="user_id" id="user_id"
                                class="form-select @error('user_id') is-invalid @enderror" data-trigger required>
                                <option value="">Select Class Teacher ...</option>
                                @if (!empty($teachers))
                                    @foreach ($teachers as $teacher)
                                        <option value="{{ $teacher->id }}"
                                            {{ old('user_id', $option->teacher->id ?? null) == $teacher->id ? 'selected' : '' }}>
                                            {{ $teacher->full_name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @if ($errors->has('user_id'))
                                <div class="text-danger">{{ $errors->first('user_id') }}</div>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="assistant_user_id">Assistant Teacher</label>
                            <select name="assistant_user_id" id="assistant_user_id"
                                class="form-select @error('assistant_user_id') is-invalid @enderror" data-trigger>
                                <option value="">Select Assistant Teacher ...</option>
                                @if (!empty($teachers))
                                    @foreach ($teachers as $teacher)
                                        <option value="{{ $teacher->id }}"
                                            {{ old('assistant_user_id', $option->assistant_user_id) == $teacher->id ? 'selected' : '' }}>
                                            {{ $teacher->full_name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @if ($errors->has('assistant_user_id'))
                                <div class="text-danger">{{ $errors->first('assistant_user_id') }}</div>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="grouping">Grouping</label>
                            <select name="grouping" id="grouping"
                                class="form-select @error('grouping') is-invalid @enderror">
                                <option value="">Select Grouping ...</option>
                                @foreach ($groupingOptions as $group)
                                    <option value="{{ $group }}"
                                        {{ old('grouping', $option->grouping) === $group ? 'selected' : '' }}>
                                        {{ $group }}
                                    </option>
                                @endforeach
                            </select>
                            @if ($errors->has('grouping'))
                                <div class="text-danger">{{ $errors->first('grouping') }}</div>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="venue_id">Venue</label>
                            <select name="venue_id" id="venue_id"
                                class="form-select @error('venue_id') is-invalid @enderror" data-trigger>
                                <option value="">Select Venue ...</option>
                                @if (!empty($venues))
                                    @foreach ($venues as $venue)
                                        <option value="{{ $venue->id }}"
                                            {{ old('venue_id', $option->venue_id) == $venue->id ? 'selected' : '' }}>
                                            {{ $venue->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @if ($errors->has('venue_id'))
                                <div class="text-danger">{{ $errors->first('venue_id') }}</div>
                            @endif
                        </div>

                        <div class="form-actions">
                            <a href="{{ route('optional.index') }}" class="btn-back">
                                <i class="bx bx-arrow-back"></i> Back
                            </a>
                            <button type="submit" class="btn-save"
                                @if (isset($isPastTerm) && $isPastTerm) onclick="return confirmPastTermEdit()" @endif>
                                <span class="btn-text"><i class="fas fa-save"></i> Update Option</span>
                                <span class="btn-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Updating...
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
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form.needs-validation');
            if (form) {
                form.addEventListener('submit', function(e) {
                    // Remove any previous client-side validation alerts
                    document.querySelectorAll('.client-validation-alert').forEach(el => el.remove());

                    // Check required fields
                    const requiredFields = form.querySelectorAll('[required]');
                    const missingFields = [];

                    requiredFields.forEach(function(field) {
                        if (!field.value || field.value.trim() === '') {
                            const label = form.querySelector('label[for="' + field.id + '"]');
                            const fieldName = label ? label.textContent.replace('*', '').trim() : field.name;
                            missingFields.push(fieldName);
                        }
                    });

                    if (missingFields.length > 0) {
                        e.preventDefault();

                        const alertRow = document.createElement('div');
                        alertRow.className = 'row mb-3 client-validation-alert';
                        const alertCol = document.createElement('div');
                        alertCol.className = 'col-md-12';

                        missingFields.forEach(function(fieldName) {
                            const alert = document.createElement('div');
                            alert.className = 'alert alert-danger alert-dismissible alert-label-icon label-arrow fade show';
                            alert.setAttribute('role', 'alert');
                            alert.innerHTML = '<i class="mdi mdi-block-helper label-icon"></i><strong>The ' + fieldName + ' field is required.</strong>' +
                                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                            alertCol.appendChild(alert);
                        });

                        alertRow.appendChild(alertCol);
                        const settingsContainer = document.querySelector('.settings-container');
                        settingsContainer.parentElement.parentElement.insertBefore(alertRow, settingsContainer.parentElement);

                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        return false;
                    }

                    // Only show loading if form is valid
                    const btn = form.querySelector('.btn-save');
                    if (btn && !btn.classList.contains('loading')) {
                        btn.classList.add('loading');
                        btn.disabled = true;
                    }
                });
            }
        });

        @if (isset($isPastTerm) && $isPastTerm)
            function confirmPastTermEdit() {
                return confirm(
                    'Are you sure you want to modify this past term optional subject? This action cannot be easily undone and may affect historical records.'
                );
            }
        @endif
    </script>
@endsection
