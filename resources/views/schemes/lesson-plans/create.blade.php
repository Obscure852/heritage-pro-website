@extends('layouts.master')
@section('title')
    Create Lesson Plan
@endsection
@section('css')
    @include('schemes.partials.schemes-styles')
    <style>
        .schemes-container {
            box-shadow: none;
        }

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

        .ck-editor-field + .invalid-feedback { display: block; }

        .field-hint {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 4px;
        }

        @media (max-width: 768px) {
            .schemes-header { padding: 20px; }
            .form-container { padding: 20px; }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ $scheme ? route('schemes.show', $scheme) : route('schemes.index') }}">Schemes of Work</a>
        @endslot
        @slot('title')
            Create Lesson Plan
        @endslot
    @endcomponent

    <div class="schemes-container">
        <div class="schemes-header">
            <div class="row align-items-center">
                <div class="col-12">
                    <h3 style="margin: 0;">New Lesson Plan</h3>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        @if ($scheme)
                            <span class="header-pill">
                                <i class="fas fa-link me-1"></i>
                                {{ $scheme->klassSubject?->gradeSubject?->subject?->name ?? $scheme->optionalSubject?->gradeSubject?->subject?->name ?? 'Scheme' }}
                            </span>
                            <span class="header-pill">
                                <i class="fas fa-calendar-week me-1"></i> Week {{ $entry?->week_number }}
                            </span>
                        @else
                            <span class="header-pill">
                                <i class="fas fa-file-alt me-1"></i> Standalone lesson plan
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="form-container">
            @if ($entry)
                <div class="help-text">
                    <div class="help-title"><i class="fas fa-info-circle me-1"></i> Pre-filled from Scheme</div>
                    <div class="help-content">
                        Topic, sub-topic, and learning objectives have been pre-filled from Week {{ $entry->week_number }}. You can modify any field before saving.
                    </div>
                </div>
            @endif

            <form action="{{ route('lesson-plans.store') }}" method="POST" id="create-lesson-plan-form">
                @csrf
                <input type="hidden" name="scheme_of_work_id" value="{{ $entry?->scheme_of_work_id }}">
                <input type="hidden" name="scheme_of_work_entry_id" value="{{ $entry?->id }}">

                {{-- Section: Lesson Details --}}
                <div class="form-section">
                    <div class="section-title">Lesson Details</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Day <span class="text-danger">*</span></label>
                            <input type="date"
                                   name="date"
                                   class="form-control @error('date') is-invalid @enderror"
                                   value="{{ old('date', date('Y-m-d')) }}"
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
                                   value="{{ old('period') }}"
                                   placeholder="e.g. Period 1">
                            @error('period')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Section: Topic & Objectives --}}
                <div class="form-section">
                    <div class="section-title">Topic &amp; Objectives</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Topic <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="topic"
                                   class="form-control @error('topic') is-invalid @enderror"
                                   value="{{ old('topic', $entry?->topic) }}"
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
                                   value="{{ old('sub_topic', $entry?->sub_topic) }}"
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
                                      placeholder="What students will learn by the end of this lesson...">{{ old('learning_objectives', $entry?->learning_objectives) }}</textarea>
                            @error('learning_objectives')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Section: Lesson Plan --}}
                <div class="form-section">
                    <div class="section-title">Lesson Plan</div>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Content</label>
                            <textarea name="content"
                                      class="form-control ck-editor-field @error('content') is-invalid @enderror"
                                      rows="3"
                                      placeholder="Lesson content...">{{ old('content') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Activities</label>
                            <textarea name="activities"
                                      class="form-control ck-editor-field @error('activities') is-invalid @enderror"
                                      rows="3"
                                      placeholder="Teaching and learning activities...">{{ old('activities') }}</textarea>
                            @error('activities')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Teaching/Learning Aids</label>
                            <textarea name="teaching_learning_aids"
                                      class="form-control ck-editor-field @error('teaching_learning_aids') is-invalid @enderror"
                                      rows="3"
                                      placeholder="Teaching and learning aids...">{{ old('teaching_learning_aids') }}</textarea>
                            @error('teaching_learning_aids')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Lesson Evaluation</label>
                            <textarea name="lesson_evaluation"
                                      class="form-control ck-editor-field @error('lesson_evaluation') is-invalid @enderror"
                                      rows="3"
                                      placeholder="How will you evaluate the lesson?">{{ old('lesson_evaluation') }}</textarea>
                            @error('lesson_evaluation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Section: Resources & Homework --}}
                <div class="form-section">
                    <div class="section-title">Resources &amp; Homework</div>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Reference Materials</label>
                            <textarea name="resources"
                                      class="form-control ck-editor-field @error('resources') is-invalid @enderror"
                                      rows="2"
                                      placeholder="Textbooks, materials, worksheets, technology...">{{ old('resources') }}</textarea>
                            @error('resources')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Homework</label>
                            <textarea name="homework"
                                      class="form-control ck-editor-field @error('homework') is-invalid @enderror"
                                      rows="2"
                                      placeholder="Homework assignment (if any)...">{{ old('homework') }}</textarea>
                            @error('homework')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <div>
                        @if ($scheme)
                            <a href="{{ route('schemes.show', $scheme) }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                        @else
                            <a href="{{ route('schemes.teacher.dashboard') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                        @endif
                    </div>
                    <button type="submit" class="btn btn-primary btn-loading">
                        <span class="btn-text"><i class="fas fa-save"></i> Create Lesson Plan</span>
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

        var form = document.getElementById('create-lesson-plan-form');
        if (form) {
            form.addEventListener('submit', function () {
                Object.keys(ckEditors).forEach(function (name) {
                    var textarea = form.querySelector('textarea[name="' + name + '"]');
                    if (textarea) {
                        textarea.value = ckEditors[name].getData();
                    }
                });

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
