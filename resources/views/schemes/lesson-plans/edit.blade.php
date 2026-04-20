@extends('layouts.master')
@section('title')
    Edit Lesson Plan
@endsection
@section('css')
    @include('schemes.partials.schemes-styles')
    <style>
        .schemes-header {
            position: relative;
            overflow: hidden;
            padding: 32px 32px 28px;
        }

        .schemes-header::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(circle at 1px 1px, rgba(255,255,255,0.07) 1px, transparent 0);
            background-size: 24px 24px;
            pointer-events: none;
        }

        .schemes-header::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
            pointer-events: none;
        }

        .schemes-header > * { position: relative; z-index: 1; }

        .schemes-header h3 {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            line-height: 1.2;
        }

        .form-container { padding: 32px; }

        .form-section {
            margin-bottom: 8px;
        }

        .form-section-icon {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: white;
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            margin-right: 10px;
            flex-shrink: 0;
        }

        .section-title {
            display: flex;
            align-items: center;
        }

        .ck-editor-field + .invalid-feedback { display: block; }

        .reflection-section {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            padding: 24px;
            margin-bottom: 24px;
        }

        .reflection-section .section-title {
            margin-top: 0;
            border-image: none;
            border-left: 3px solid #22c55e;
            border-bottom-color: #bbf7d0;
        }

        .reflection-section .form-section-icon {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        }

        .taught-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,0.14);
            border: 1px solid rgba(255,255,255,0.18);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            color: white;
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 12.5px;
            font-weight: 600;
        }

        .taught-badge i { font-size: 11px; }

        @media (max-width: 768px) {
            .schemes-header { padding: 20px; }
            .form-container { padding: 20px; }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('schemes.index') }}">Schemes of Work</a>
        @endslot
        @slot('title')
            Edit Lesson Plan
        @endslot
    @endcomponent

    @php
        $subjectName = $lessonPlan->entry?->scheme?->klassSubject?->gradeSubject?->subject?->name
            ?? $lessonPlan->entry?->scheme?->optionalSubject?->gradeSubject?->subject?->name
            ?? null;
    @endphp

    <div class="schemes-container">
        <div class="schemes-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 style="margin: 0;">Edit Lesson Plan</h3>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <span class="header-pill">
                            <i class="fas fa-pen me-1"></i> {{ $lessonPlan->topic }}
                        </span>
                        @if ($subjectName)
                            <span class="header-pill">
                                <i class="fas fa-book me-1"></i> {{ $subjectName }}
                            </span>
                        @endif
                        @if ($lessonPlan->status === 'taught')
                            <span class="taught-badge">
                                <i class="fas fa-check-circle"></i> Taught {{ $lessonPlan->taught_at?->format('d M Y') }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('lesson-plans.show', $lessonPlan) }}" class="btn-outline-white">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>

        <div class="form-container">
            {{-- Reflection Notes Section (taught lessons only) --}}
            @if ($lessonPlan->status === 'taught')
                <div class="reflection-section">
                    <div class="section-title">
                        <span class="form-section-icon"><i class="fas fa-journal-whills"></i></span>
                        Reflection Notes
                    </div>
                    <div class="help-text" style="border-left-color: #22c55e; background: #ecfdf5; margin-bottom: 16px;">
                        <div class="help-content" style="color: #166534;">
                            This lesson was taught on <strong>{{ $lessonPlan->taught_at?->format('d M Y') }}</strong>. Capture your reflections below &mdash; what went well, what you'd change, and how students responded.
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Reflection Notes</label>
                        <textarea name="reflection_notes"
                                  form="edit-lesson-plan-form"
                                  class="form-control ck-editor-field @error('reflection_notes') is-invalid @enderror"
                                  rows="5"
                                  placeholder="What went well? What would you do differently? Student engagement observations...">{{ old('reflection_notes', $lessonPlan->reflection_notes) }}</textarea>
                        @error('reflection_notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            @endif

            <form action="{{ route('lesson-plans.update', $lessonPlan) }}" method="POST" id="edit-lesson-plan-form">
                @csrf
                @method('PUT')

                @if ($lessonPlan->status === 'taught')
                    <textarea name="reflection_notes" style="display:none;" aria-hidden="true">{{ old('reflection_notes', $lessonPlan->reflection_notes) }}</textarea>
                @endif

                {{-- Section: Lesson Details --}}
                <div class="form-section">
                    <div class="section-title">
                        <span class="form-section-icon"><i class="fas fa-calendar-day"></i></span>
                        Lesson Details
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Day <span class="text-danger">*</span></label>
                            <input type="date"
                                   name="date"
                                   class="form-control @error('date') is-invalid @enderror"
                                   value="{{ old('date', $lessonPlan->date?->format('Y-m-d')) }}"
                                   required>
                            @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Periods/Week</label>
                            <input type="text"
                                   name="period"
                                   class="form-control @error('period') is-invalid @enderror"
                                   value="{{ old('period', $lessonPlan->period) }}"
                                   placeholder="e.g. Period 1">
                            @error('period')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Section: Topic & Objectives --}}
                <div class="form-section">
                    <div class="section-title">
                        <span class="form-section-icon"><i class="fas fa-bullseye"></i></span>
                        Topic &amp; Objectives
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Topic <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="topic"
                                   class="form-control @error('topic') is-invalid @enderror"
                                   value="{{ old('topic', $lessonPlan->topic) }}"
                                   required
                                   placeholder="Lesson topic">
                            @error('topic')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sub-topic</label>
                            <input type="text"
                                   name="sub_topic"
                                   class="form-control @error('sub_topic') is-invalid @enderror"
                                   value="{{ old('sub_topic', $lessonPlan->sub_topic) }}"
                                   placeholder="Sub-topic (optional)">
                            @error('sub_topic')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-12 learning-objectives-editor">
                            <label class="form-label">Learning Objectives</label>
                            <textarea name="learning_objectives"
                                      class="form-control ck-editor-field @error('learning_objectives') is-invalid @enderror"
                                      rows="7"
                                      placeholder="What students will learn by the end of this lesson...">{{ old('learning_objectives', $lessonPlan->learning_objectives) }}</textarea>
                            @error('learning_objectives')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Section: Lesson Plan --}}
                <div class="form-section">
                    <div class="section-title">
                        <span class="form-section-icon"><i class="fas fa-chalkboard-teacher"></i></span>
                        Lesson Plan
                    </div>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Content</label>
                            <textarea name="content"
                                      class="form-control ck-editor-field @error('content') is-invalid @enderror"
                                      rows="3"
                                      placeholder="Lesson content...">{{ old('content', $lessonPlan->content) }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Activities</label>
                            <textarea name="activities"
                                      class="form-control ck-editor-field @error('activities') is-invalid @enderror"
                                      rows="3"
                                      placeholder="Teaching and learning activities...">{{ old('activities', $lessonPlan->activities) }}</textarea>
                            @error('activities')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Teaching/Learning Aids</label>
                            <textarea name="teaching_learning_aids"
                                      class="form-control ck-editor-field @error('teaching_learning_aids') is-invalid @enderror"
                                      rows="3"
                                      placeholder="Teaching and learning aids...">{{ old('teaching_learning_aids', $lessonPlan->teaching_learning_aids) }}</textarea>
                            @error('teaching_learning_aids')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Lesson Evaluation</label>
                            <textarea name="lesson_evaluation"
                                      class="form-control ck-editor-field @error('lesson_evaluation') is-invalid @enderror"
                                      rows="3"
                                      placeholder="How will you evaluate the lesson?">{{ old('lesson_evaluation', $lessonPlan->lesson_evaluation) }}</textarea>
                            @error('lesson_evaluation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Section: Resources & Homework --}}
                <div class="form-section">
                    <div class="section-title">
                        <span class="form-section-icon"><i class="fas fa-book-reader"></i></span>
                        Resources &amp; Homework
                    </div>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Reference Materials</label>
                            <textarea name="resources"
                                      class="form-control ck-editor-field @error('resources') is-invalid @enderror"
                                      rows="2"
                                      placeholder="Textbooks, materials, worksheets, technology...">{{ old('resources', $lessonPlan->resources) }}</textarea>
                            @error('resources')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Homework</label>
                            <textarea name="homework"
                                      class="form-control ck-editor-field @error('homework') is-invalid @enderror"
                                      rows="2"
                                      placeholder="Homework assignment (if any)...">{{ old('homework', $lessonPlan->homework) }}</textarea>
                            @error('homework')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <div>
                        <a href="{{ route('lesson-plans.show', $lessonPlan) }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                    </div>
                    <button type="submit" class="btn btn-primary btn-loading">
                        <span class="btn-text"><i class="fas fa-save"></i> Save Changes</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Saving...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('script')
    <script>
    (function () {
        'use strict';

        var ckEditors = {};

        function initEditors() {
            if (typeof ClassicEditor === 'undefined') {
                console.warn('CKEditor not loaded — falling back to plain textareas');
                return;
            }

            document.querySelectorAll('.ck-editor-field').forEach(function (textarea) {
                ClassicEditor
                    .create(textarea, {
                        toolbar: ['heading', '|', 'bold', 'italic', 'bulletedList', 'numberedList', '|', 'undo', 'redo'],
                    })
                    .then(function (editor) {
                        ckEditors[textarea.name] = editor;

                        @if ($lessonPlan->status === 'taught')
                        if (textarea.name === 'reflection_notes') {
                            var hiddenTextarea = document.querySelector('textarea[name="reflection_notes"][style*="display:none"]');
                            if (hiddenTextarea) {
                                editor.model.document.on('change:data', function () {
                                    hiddenTextarea.value = editor.getData();
                                });
                            }
                        }
                        @endif
                    })
                    .catch(function (error) {
                        console.error('CKEditor init error for ' + textarea.name + ':', error);
                    });
            });
        }

        if (typeof ClassicEditor !== 'undefined') {
            initEditors();
        } else {
            window.addEventListener('load', initEditors);
        }

        var form = document.getElementById('edit-lesson-plan-form');
        if (form) {
            form.addEventListener('submit', function () {
                Object.keys(ckEditors).forEach(function (name) {
                    var textarea = form.querySelector('textarea[name="' + name + '"]');
                    if (textarea) {
                        textarea.value = ckEditors[name].getData();
                    }
                });

                @if ($lessonPlan->status === 'taught')
                if (ckEditors['reflection_notes']) {
                    var hiddenTextarea = document.querySelector('textarea[name="reflection_notes"][style*="display:none"]');
                    if (hiddenTextarea) {
                        hiddenTextarea.value = ckEditors['reflection_notes'].getData();
                    }
                }
                @endif

                var submitBtn = form.querySelector('button[type="submit"].btn-loading');
                if (submitBtn) {
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                }
            });
        }
    })();
    </script>
@endsection
