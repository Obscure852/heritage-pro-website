@extends('layouts.master')
@section('title', 'Edit CA / Exam')

@section('css')
    <style>
        /* Scheme Linking Panel */
        .scheme-link-panel {
            margin-top: 24px;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
        }

        .scheme-link-header {
            background: #f8f9fa;
            padding: 12px 16px;
            border-radius: 3px 3px 0 0;
            cursor: pointer;
            user-select: none;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #e5e7eb;
        }

        .scheme-link-header:hover {
            background: #f0f9ff;
        }

        .scheme-link-header .header-title {
            font-weight: 600;
            color: #1f2937;
            font-size: 14px;
        }

        .scheme-link-header .chevron {
            transition: transform 0.2s ease;
            color: #6b7280;
        }

        .scheme-link-header[aria-expanded="true"] .chevron {
            transform: rotate(180deg);
        }

        .scheme-link-body {
            padding: 16px;
        }

        .help-text {
            background: #f8f9fa;
            padding: 10px 12px;
            border-left: 4px solid #3b82f6;
            border-radius: 0 3px 3px 0;
            margin-bottom: 12px;
        }

        .help-text .help-content {
            color: #6b7280;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
        }

        .link-section-title {
            font-size: 13px;
            font-weight: 600;
            margin: 0 0 8px 0;
            color: #374151;
        }

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

        .required::after {
            content: '*';
            color: #dc3545;
            margin-left: .25rem;
        }

        /* Form Controls */
        .form-control, .form-select {
            border: 1px solid #d1d5db;
            border-radius: 3px;
            padding: 8px 12px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
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

        .btn-delete {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            background: white;
            border: 1px solid #ef4444;
            border-radius: 3px;
            color: #ef4444;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-delete:hover {
            background: #fef2f2;
            color: #dc2626;
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

        .btn-save:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .btn-save.loading .btn-text {
            display: none;
        }

        .btn-save.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ $gradebookBackUrl }}">
                Back
            </a>
        @endslot
        @slot('title')
            Edit Test
        @endslot
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
                    <h3><i class="fas fa-edit me-2"></i>Edit Test</h3>
                    <p>Update test details and configuration</p>
                </div>
                <div class="settings-body">
                    <form class="needs-validation" method="POST"
                        action="{{ route('assessment.ca-exam-update', $test->id) }}" novalidate>
                        @csrf
                        <input type="hidden" name="term_id" value="{{ $currentTerm->id ?? 0 }}">
                        <input type="hidden" name="year" value="{{ $currentTerm->year ?? 0 }}">

                        <div class="row g-3">
                            {{-- Sequence --}}
                            <div class="col-md-6">
                                <label class="form-label required">Sequence</label>
                                <select name="sequence" class="form-select form-select-sm" required>
                                    <option value="" disabled selected>Select …</option>
                                    @for ($i = 1; $i < 10; $i++)
                                        <option value="{{ $i }}" {{ $i == $test->sequence ? 'selected' : '' }}>
                                            {{ $i }}</option>
                                    @endfor
                                </select>
                            </div>

                            {{-- Name / Abbrev --}}
                            <div class="col-md-6">
                                <label class="form-label required">Name / Abbrev</label>
                                <input type="text" name="abbrev" class="form-control form-control-sm"
                                    placeholder="e.g. August Exam" value="{{ old('abbrev', $test->abbrev) }}" required>
                            </div>

                            {{-- Subject --}}
                            <div class="col-md-6">
                                <label class="form-label required">Subject</label>
                                <select name="grade_subject_id" class="form-select form-select-sm" required>
                                    <option value="" disabled selected>Select Subject …</option>
                                    @foreach ($subjects as $subject)
                                        @if ($subject->subject)
                                            <option value="{{ $subject->id }}"
                                                {{ $test->grade_subject_id == $subject->id ? 'selected' : '' }}>
                                                {{ $subject->grade->name }} | {{ $subject->subject->name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            {{-- Type --}}
                            <div class="col-md-6">
                                <label class="form-label required">Type</label>
                                <select name="type" class="form-select form-select-sm" required>
                                    <option value="" disabled>Select Type …</option>
                                    @foreach (['Exercise', 'CA', 'Exam'] as $option)
                                        <option value="{{ $option }}"
                                            {{ $test->type === $option ? 'selected' : '' }}>{{ $option }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Out Of --}}
                            <div class="col-md-6">
                                <label class="form-label required">Out Of</label>
                                <input type="number" name="out_of" class="form-control form-control-sm"
                                    placeholder="e.g. 50" value="{{ old('out_of', $test->out_of) }}" required>
                            </div>

                            {{-- Grade --}}
                            <div class="col-md-6">
                                <label class="form-label required">Grade</label>
                                <select name="grade_id" class="form-select form-select-sm" required>
                                    <option value="" disabled selected>Select Grade …</option>
                                    @foreach ($grades as $grade)
                                        <option value="{{ $grade->id }}"
                                            {{ $grade->id == $test->grade_id ? 'selected' : '' }}>{{ $grade->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Start Date --}}
                            <div class="col-md-6">
                                <label class="form-label required">Start Date</label>
                                <input type="date" name="start_date" class="form-control form-control-sm"
                                    value="{{ old('start_date', $test->start_date) }}" required>
                            </div>

                            {{-- End Date --}}
                            <div class="col-md-6">
                                <label class="form-label required">End Date</label>
                                <input type="date" name="end_date" class="form-control form-control-sm"
                                    value="{{ old('end_date', $test->end_date) }}" required>
                            </div>
                        </div>

                        @if(isset($schemeEntries))
                            @php
                                $schemeEntries = $schemeEntries ?? collect();
                                $syllabusObjectives = $syllabusObjectives ?? collect();
                                $selectedEntryIds = $selectedEntryIds ?? [];
                                $selectedObjectiveIds = $selectedObjectiveIds ?? [];
                                $editHasLinks = count($selectedEntryIds) > 0 || count($selectedObjectiveIds) > 0;
                            @endphp
                            <div class="scheme-link-panel mt-4">
                                <button type="button"
                                    class="scheme-link-header w-100 border-0 text-start"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#edit-scheme-collapse"
                                    aria-expanded="{{ $editHasLinks ? 'true' : 'false' }}"
                                    aria-controls="edit-scheme-collapse">
                                    <span class="header-title">
                                        <i class="fas fa-link me-2 text-primary"></i>Link to Scheme of Work
                                    </span>
                                    <i class="fas fa-chevron-down chevron"></i>
                                </button>
                                <div class="collapse {{ $editHasLinks ? 'show' : '' }}" id="edit-scheme-collapse">
                                    <div class="scheme-link-body">
                                        {{-- Scheme Entries --}}
                                        <div class="mb-4">
                                            <label class="link-section-title">Scheme Entries</label>
                                            <div class="help-text mb-2">
                                                <p class="help-content">Link this test to weekly scheme entries to track assessment coverage.</p>
                                            </div>
                                            @if($schemeEntries->isEmpty())
                                                <p class="text-muted small">No scheme entries available for this subject and term.</p>
                                            @else
                                                <select name="scheme_entry_ids[]" multiple
                                                    class="form-select"
                                                    style="min-height: 120px;">
                                                    @foreach($schemeEntries as $entry)
                                                        <option value="{{ $entry->id }}"
                                                            {{ in_array($entry->id, $selectedEntryIds) ? 'selected' : '' }}>
                                                            Week {{ $entry->week_number }}: {{ $entry->topic }}{{ $entry->sub_topic ? ' — ' . $entry->sub_topic : '' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <small class="text-muted d-block mt-1">Hold Ctrl / Cmd to select multiple entries.</small>
                                            @endif
                                        </div>

                                        {{-- Syllabus Objectives --}}
                                        <div>
                                            <label class="link-section-title">Syllabus Objectives</label>
                                            <div class="help-text mb-2">
                                                <p class="help-content">Link this test to syllabus objectives to track objective assessment coverage.</p>
                                            </div>
                                            @if($syllabusObjectives->isEmpty())
                                                <p class="text-muted small">No syllabus objectives available for this subject and grade.</p>
                                            @else
                                                <select name="syllabus_objective_ids[]" multiple
                                                    class="form-select"
                                                    style="min-height: 120px;">
                                                    @foreach($syllabusObjectives->groupBy(fn($obj) => $obj->topic?->name ?? 'General') as $topicName => $objectives)
                                                        <optgroup label="{{ $topicName }}">
                                                            @foreach($objectives as $objective)
                                                                <option value="{{ $objective->id }}"
                                                                    {{ in_array($objective->id, $selectedObjectiveIds) ? 'selected' : '' }}>
                                                                    {{ $objective->code }}: {{ \Illuminate\Support\Str::limit($objective->objective_text, 80) }}
                                                                </option>
                                                            @endforeach
                                                        </optgroup>
                                                    @endforeach
                                                </select>
                                                <small class="text-muted d-block mt-1">Hold Ctrl / Cmd to select multiple objectives.</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="form-actions">
                            <a href="{{ route('assessment.test-list') }}" class="btn-back">
                                <i class="bx bx-arrow-back"></i> Back
                            </a>

                            <a href="#" id="delete-student-btn" class="btn-delete">
                                <i class="bx bx-trash"></i> Delete
                            </a>

                            @if (!session('is_past_term') || Gate::check('view-system-admin'))
                                <button type="submit" class="btn-save">
                                    <span class="btn-text"><i class="fas fa-save"></i> Update</span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
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
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form.needs-validation');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const btn = form.querySelector('.btn-save');
                    if (btn) {
                        btn.classList.add('loading');
                        btn.disabled = true;
                    }
                });
            }
        });
    </script>
@endsection
