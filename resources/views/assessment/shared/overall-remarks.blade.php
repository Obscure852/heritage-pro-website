@extends('layouts.master')
@section('title')
    Overall Remarks | Assessment Premium
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

        .settings-body {
            padding: 24px;
        }

        .form-label {
            font-weight: 600;
            color: #374151;
            font-size: 13px;
        }

        /* Form Controls */
        .form-control,
        .form-select {
            border: 1px solid #d1d5db;
            border-radius: 3px;
            padding: 8px 12px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        /* Score Badges */
        .score-badge {
            display: inline-flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 6px 12px;
            border-radius: 3px;
            font-size: 13px;
            font-weight: 500;
            margin-left: 8px;
        }

        /* Tab Styling */
        .nav-tabs {
            border-bottom: 2px solid #e5e7eb;
            margin-bottom: 20px;
        }

        .nav-tabs .nav-link {
            border: none;
            color: #6b7280;
            font-weight: 500;
            padding: 12px 20px;
            transition: all 0.2s ease;
            border-radius: 3px 3px 0 0;
            margin-right: 4px;
        }

        .nav-tabs .nav-link:hover {
            color: #3b82f6;
            background: #f0f9ff;
        }

        .nav-tabs .nav-link.active {
            color: #3b82f6;
            background: white;
            border-bottom: 2px solid #3b82f6;
            margin-bottom: -2px;
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

        .btn-next {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            background: #6b7280;
            border: none;
            border-radius: 3px;
            color: white;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-next:hover {
            background: #4b5563;
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

        .btn-save:disabled,
        .btn-next:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .btn-save.loading .btn-text,
        .btn-next.loading .btn-text {
            display: none !important;
        }

        .btn-save.loading .btn-spinner,
        .btn-next.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }

        .btn-save.loading .btn-spinner.d-none,
        .btn-next.loading .btn-spinner.d-none {
            display: inline-flex !important;
        }

        .grade-options {
            width: 6%;
            white-space: nowrap;
            padding: 4px 36px !important;
            font-size: 12px;
        }

        #assessment .grade-options {
            width: 6%;
            padding: 4px 12px !important;
        }

        #assessment .grade-options .form-select {
            min-width: 45px;
            font-size: 12px;
            padding: 4px 6px;
        }

        .table-fixed-layout {
            table-layout: fixed;
            width: 100%;
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="{{ $gradebookBackUrl }}">Back</a>
        @endslot
        @if (!empty($student))
            @slot('title')
                {{ $student->fullName . ' Overall Remarks' }}
            @endslot
        @endif
    @endcomponent
    <div class="row">
        <div class="col-12">
            @if (session('message'))
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        <i class="mdi mdi-block-helper label-icon"></i><strong>{{ $error }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="settings-container">
                <div class="settings-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3><i class="fas fa-comments me-2"></i>Overall Remarks</h3>
                            <p>Add class teacher and head teacher remarks for {{ $student->fullName ?? 'student' }}</p>
                        </div>
                        <div class="d-flex">
                            @if ($driver === 'primary')
                                <span class="score-badge">{{ round($averagePercentage, 1) ?? '' }}%</span>
                                <span class="score-badge">{{ $overallGrade->grade ?? '' }}</span>
                            @else
                                <span class="score-badge">{{ $totalPoints ?? '' }} pts</span>
                                @if ($driver === 'junior')
                                    <span class="score-badge">{{ $grade ?? '' }}</span>
                                @endif
                            @endif
                            @php $studentNumber = $currentIndex + 1; @endphp
                            <span class="score-badge">{{ $studentNumber }}/{{ $totalStudents }}</span>
                        </div>
                    </div>
                </div>
                <div class="settings-body">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#comments" role="tab">
                                <i class="fas fa-comment-alt me-1"></i> Comments
                            </a>
                        </li>
                        @if ($driver === 'primary')
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#assessment" role="tab">
                                    <i class="fas fa-tasks me-1"></i> Assessment
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#photo" role="tab">
                                    <i class="fas fa-chart-bar me-1"></i> CA
                                </a>
                            </li>
                        @endif
                    </ul>

                    <!-- Tab panes -->
                    <div class="tab-content text-muted">
                        <div class="tab-pane active" id="comments" role="tabpanel">
                            <form class="needs-validation" method="post"
                                action="{{ route('assessment.new-comment', $student->id) }}" novalidate>
                                @csrf

                                <input type="hidden" name="nextStudentId" id="next_student_id"
                                    value="{{ $nextStudentId }}">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group mb-3">
                                            <label style="font-size:14px;" for="class_teacher_header">Class Teacher's
                                                Remarks</label>
                                            <select class="form-select form-select-sm" data-trigger
                                                name="class_teacher_header" id="choices-single-groups-1">
                                                <option style="font-size:14px;" value="">Select from comment back
                                                    ...</option>
                                                @if (!empty($comments))
                                                    @foreach ($comments as $comment)
                                                        <option style="font-size:14px;" value="{{ $comment->body }}">
                                                            {{ $comment->body }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            <textarea name="class_teacher" id="class_teacher" cols="20" rows="5" class="form-control mt-2">{{ $class_teacher_comment ?? '' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group mb-3">
                                            <label style="font-size:14px;" for="head_teacher_header">Head Teacher's
                                                Remarks</label>
                                            <select class="form-select form-select-sm" name="head_teacher_header"
                                                id="choices-single-groups-2" data-trigger>
                                                <option value="">Select from comment bank ...</option>
                                                @if (!empty($comments))
                                                    @foreach ($comments as $comment)
                                                        <option value="{{ $comment->body }}">{{ $comment->body }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            <textarea name="head_teacher" id="head_teacher" cols="20" rows="5" class="form-control mt-2">{{ $school_head_comment ?? '' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                @if ($student->current_class)
                                    @can('class-teacher', $student->current_class)
                                        <div class="form-actions">
                                            <a href="{{ $gradebookBackUrl }}" class="btn-back">
                                                <i class="bx bx-arrow-back"></i> Back
                                            </a>
                                            @if (!session('is_past_term'))
                                                @if ($nextStudentId)
                                                    <button type="submit" name="submitType" value="saveNext"
                                                        class="btn-next">
                                                        <span class="btn-text"><i class="fas fa-arrow-right"></i> Save &
                                                            Next</span>
                                                        <span class="btn-spinner d-none">
                                                            <span class="spinner-border spinner-border-sm me-2" role="status"
                                                                aria-hidden="true"></span>
                                                            Saving...
                                                        </span>
                                                    </button>
                                                @endif
                                                <button type="submit" name="submitType" value="save" class="btn-save">
                                                    <span class="btn-text"><i class="fas fa-save"></i> Save</span>
                                                    <span class="btn-spinner d-none">
                                                        <span class="spinner-border spinner-border-sm me-2" role="status"
                                                            aria-hidden="true"></span>
                                                        Saving...
                                                    </span>
                                                </button>
                                            @endif
                                        </div>
                                    @endcan
                                @endif
                            </form>
                        </div>

                        @if ($driver === 'primary')
                            <div class="tab-pane" id="assessment" role="tabpanel">
                                <form action="{{ route('reception.store-criteria-test-assessment') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="student_id" value="{{ $student->id }}">
                                    <input type="hidden" name="term_id" value="{{ $klass->term_id }}">
                                    <input type="hidden" name="klass_id" value="{{ $klass->id }}">
                                    <input type="hidden" name="grade_id" value="{{ $klass->grade_id }}">
                                    @if ($klassSubjects->count() > 0)
                                        @foreach ($klassSubjects as $klassSubject)
                                            @if ($klassSubject->subject->criteriaBasedTests->count() > 0)
                                                <h6>{{ $klassSubject->subject->subject->name }}</h6>
                                                <table
                                                    class="table table-fixed-layout table-sm table-bordered table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th class="text-muted">Components</th>
                                                            @foreach ($klassSubject->subject->criteriaBasedTests->sortBy('sequence') as $test)
                                                                <th class="grade-options">
                                                                    {{ substr($test->abbrev, 0, 3) }}
                                                                    ({{ $test->sequence }})
                                                                </th>
                                                            @endforeach
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($klassSubject->subject->components as $component)
                                                            <tr>
                                                                <td>{{ $component->name }}</td>
                                                                @foreach ($klassSubject->subject->criteriaBasedTests->sortBy('sequence') as $test)
                                                                    <td class="grade-options">
                                                                        @php
                                                                            $gradeOptionSet = $klassSubject->subject->gradeOptionSets->first();
                                                                        @endphp
                                                                        @if ($gradeOptionSet && $gradeOptionSet->gradeOptions->count() > 0)
                                                                            <select
                                                                                name="scores[{{ $klassSubject->subject->id }}][{{ $component->id }}][{{ $test->id }}]"
                                                                                class="form-select form-select-sm">
                                                                                @foreach ($gradeOptionSet->gradeOptions as $option)
                                                                                    <option value="{{ $option->id }}"
                                                                                        {{ isset($assessments[$klassSubject->subject->id . '-' . $component->id . '-' . $test->id]) && $assessments[$klassSubject->subject->id . '-' . $component->id . '-' . $test->id]->grade_option_id == $option->id ? 'selected' : '' }}>
                                                                                        {{ $option->label }}
                                                                                    </option>
                                                                                @endforeach
                                                                            </select>
                                                                        @else
                                                                            <p class="text-warning">No grading options.</p>
                                                                        @endif
                                                                    </td>
                                                                @endforeach
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            @endif
                                        @endforeach
                                    @endif

                                    @can('class-teacher', $student->current_class)
                                        <div class="form-actions">
                                            <a href="{{ $gradebookBackUrl }}" class="btn-back">
                                                <i class="bx bx-arrow-back"></i> Back
                                            </a>
                                            @if (!session('is_past_term'))
                                                <button type="submit" class="btn-save">
                                                    <span class="btn-text"><i class="fas fa-save"></i> Save</span>
                                                    <span class="btn-spinner d-none">
                                                        <span class="spinner-border spinner-border-sm me-2" role="status"
                                                            aria-hidden="true"></span>
                                                        Saving...
                                                    </span>
                                                </button>
                                            @endif
                                        </div>
                                    @endcan
                                </form>
                            </div>
                            <div class="tab-pane" id="photo" role="tabpanel">
                                <div class="row">
                                    <div class="col-12">
                                        <h6>Report Card for {{ $student->fullName ?? '' }}</h6>
                                        @foreach ($gradeSubjects as $gradeSubject)
                                            @if ($gradeSubject->components->count() > 0 && $gradeSubject->criteriaBasedTests->count() > 0)
                                                <h6 class="text-muted">{{ $gradeSubject->subject->name }}</h6>
                                                <table class="table table-sm table-bordered table-striped"
                                                    style="width: 100%;">
                                                    <thead>
                                                        <tr>
                                                            <th></th>
                                                            @foreach ($gradeSubject->criteriaBasedTests->sortBy('sequence') as $test)
                                                                <th class="text-center" colspan="5">
                                                                    {{ $test->abbrev }}
                                                                    ({{ $test->sequence }})
                                                                </th>
                                                            @endforeach
                                                        </tr>
                                                        <tr>
                                                            <th>Components</th>
                                                            @foreach ($gradeSubject->criteriaBasedTests->sortBy('sequence') as $test)
                                                                @php
                                                                    $gradeOptionSet = $gradeSubject->gradeOptionSets->first();
                                                                @endphp
                                                                @if ($gradeOptionSet && $gradeOptionSet->gradeOptions->count() > 0)
                                                                    @foreach ($gradeOptionSet->gradeOptions as $option)
                                                                        <th class="text-center grade-options">
                                                                            {{ $option->label }}</th>
                                                                    @endforeach
                                                                @else
                                                                    <th class="text-center" colspan="5">No grading
                                                                        options
                                                                    </th>
                                                                @endif
                                                            @endforeach
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($gradeSubject->components as $component)
                                                            <tr>
                                                                <td>{{ $component->name }}</td>
                                                                @foreach ($gradeSubject->criteriaBasedTests->sortBy('sequence') as $test)
                                                                    @php
                                                                        $gradeOptionSet = $gradeSubject->gradeOptionSets->first();
                                                                    @endphp
                                                                    @if ($gradeOptionSet && $gradeOptionSet->gradeOptions->count() > 0)
                                                                        @foreach ($gradeOptionSet->gradeOptions as $option)
                                                                            <td class="text-center grade-options">
                                                                                @php
                                                                                    $assessment = $student->criteriaBasedStudentTests
                                                                                        ->where(
                                                                                            'grade_subject_id',
                                                                                            $gradeSubject->id,
                                                                                        )
                                                                                        ->where(
                                                                                            'component_id',
                                                                                            $component->id,
                                                                                        )
                                                                                        ->where(
                                                                                            'criteria_based_test_id',
                                                                                            $test->id,
                                                                                        )
                                                                                        ->where(
                                                                                            'grade_option_id',
                                                                                            $option->id,
                                                                                        )
                                                                                        ->first();
                                                                                @endphp
                                                                                @if ($assessment)
                                                                                    <span class="tick">√</span>
                                                                                @else
                                                                                    <span>&nbsp;</span>
                                                                                @endif
                                                                            </td>
                                                                        @endforeach
                                                                    @else
                                                                        <td class="text-center" colspan="5">No grading
                                                                            options</td>
                                                                    @endif
                                                                @endforeach
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.getElementById('choices-single-groups-1').addEventListener('change', function() {
            var selectedValue = this.options[this.selectedIndex].value;
            document.getElementById('class_teacher').value = selectedValue;
        });

        document.getElementById('choices-single-groups-2').addEventListener('change', function() {
            var selectedValue = this.options[this.selectedIndex].value;
            document.getElementById('head_teacher').value = selectedValue;
        });

        // Sticky tabs - persist active tab across page reloads
        (function() {
            var storageKey = 'overall-remarks-active-tab';
            var savedTab = localStorage.getItem(storageKey);

            if (savedTab) {
                var tabLink = document.querySelector('.nav-tabs a[href="' + savedTab + '"]');
                if (tabLink) {
                    document.querySelectorAll('.nav-tabs .nav-link').forEach(function(el) {
                        el.classList.remove('active');
                    });
                    document.querySelectorAll('.tab-content .tab-pane').forEach(function(el) {
                        el.classList.remove('active', 'show');
                    });
                    tabLink.classList.add('active');
                    var pane = document.querySelector(savedTab);
                    if (pane) pane.classList.add('active', 'show');
                }
            }

            document.querySelectorAll('.nav-tabs a[data-bs-toggle="tab"]').forEach(function(tab) {
                tab.addEventListener('shown.bs.tab', function(e) {
                    localStorage.setItem(storageKey, e.target.getAttribute('href'));
                });
            });
        })();

        // Form submission loading animation
        (function() {
            const forms = document.querySelectorAll('form');

            forms.forEach(function(form) {
                let clickedButton = null;

                // Track which button was clicked
                form.querySelectorAll('button[type="submit"]').forEach(function(button) {
                    button.addEventListener('click', function(e) {
                        clickedButton = this;
                    });
                });

                form.addEventListener('submit', function(e) {
                    const submitter = e.submitter || clickedButton;

                    if (submitter && submitter.name) {
                        let submitMirror = form.querySelector('input[type="hidden"][data-submit-mirror="true"]');

                        if (!submitMirror) {
                            submitMirror = document.createElement('input');
                            submitMirror.type = 'hidden';
                            submitMirror.setAttribute('data-submit-mirror', 'true');
                            form.appendChild(submitMirror);
                        }

                        submitMirror.name = submitter.name;
                        submitMirror.value = submitter.value;
                    }

                    if (submitter) {
                        submitter.classList.add('loading');
                        submitter.disabled = true;

                        // Remove d-none from spinner
                        const spinner = submitter.querySelector('.btn-spinner');
                        if (spinner) {
                            spinner.classList.remove('d-none');
                        }
                    }
                });
            });
        })();
    </script>
@endsection
