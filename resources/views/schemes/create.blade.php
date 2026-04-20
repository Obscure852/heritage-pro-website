@extends('layouts.master')
@section('title')
    Create Scheme of Work
@endsection
@section('css')
    @include('schemes.partials.schemes-styles')
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('schemes.index') }}">Schemes of Work</a>
        @endslot
        @slot('title')
            Create Scheme
        @endslot
    @endcomponent

    <div class="schemes-container">
        <div class="header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 style="margin: 0;">Create Scheme of Work</h3>
                    <p style="margin: 6px 0 0 0; opacity: .9;">Set up a new scheme with weekly entry rows</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('schemes.index') }}" class="btn-outline-white">
                        <i class="fas fa-arrow-left"></i> Back to Schemes
                    </a>
                </div>
            </div>
        </div>

        <div class="form-container">
            <div class="help-text">
                <div class="help-title">Create a New Scheme</div>
                <div class="help-content">
                    Select your class assignment or optional subject, choose the term, and set the number of weeks. Weekly entry rows will be automatically generated.
                </div>
            </div>

            <div class="alert alert-info d-none" id="standard-scheme-notice" style="font-size: 13px;">
                <i class="fas fa-layer-group me-2"></i>
                A <strong>standard scheme</strong> may exist for this subject and grade. Individual schemes will be distributed automatically when the standard scheme is approved. You may not need to create one manually.
                <a href="{{ route('standard-schemes.index') }}" class="alert-link">View Standard Schemes</a>
            </div>

            <form action="{{ route('schemes.store') }}" method="POST" id="create-scheme-form">
                @csrf

                @error('subject')
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ $message }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @enderror

                <div class="section-title">Subject</div>

                <div class="mb-3">
                    <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                    <select name="subject" id="subject" class="form-select @error('subject') is-invalid @enderror" required>
                        <option value="">Select Subject...</option>
                        @if ($klassSubjects->isNotEmpty())
                            <optgroup label="Class Subjects">
                                @foreach ($klassSubjects as $ks)
                                    <option value="class_{{ $ks->id }}" {{ old('subject') === "class_{$ks->id}" ? 'selected' : '' }}>
                                        {{ $ks->gradeSubject?->subject?->name }} - {{ $ks->klass?->name }} ({{ $ks->teacher?->full_name }})
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                        @if ($optionalSubjects->isNotEmpty())
                            <optgroup label="Optional Subjects">
                                @foreach ($optionalSubjects as $os)
                                    <option value="optional_{{ $os->id }}" {{ old('subject') === "optional_{$os->id}" ? 'selected' : '' }}>
                                        {{ ($os->gradeSubject?->subject?->name ?? 'Unknown Subject') . ' - ' . ($os->name ?? 'Unnamed Class') . ' (' . ($os->teacher?->full_name ?? 'No Teacher Assigned') . ')' }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                    @if ($klassSubjects->isEmpty() && $optionalSubjects->isEmpty())
                        <div class="text-muted mt-1" style="font-size: 13px;">
                            No subjects found. You may not be assigned to any classes yet.
                        </div>
                    @endif
                </div>

                <div class="section-title">Term &amp; Duration</div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="term_id" class="form-label">Term <span class="text-danger">*</span></label>
                        <select name="term_id" id="term_id" class="form-select @error('term_id') is-invalid @enderror" required>
                            <option value="">Select Term...</option>
                            @foreach ($terms as $term)
                                <option value="{{ $term->id }}"
                                    {{ old('term_id', $currentTerm?->id) == $term->id ? 'selected' : '' }}>
                                    Term {{ $term->term }},{{ $term->year }}
                                </option>
                            @endforeach
                        </select>
                        @error('term_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="total_weeks" class="form-label">Total Weeks <span class="text-danger">*</span></label>
                        <input type="number"
                               name="total_weeks"
                               id="total_weeks"
                               class="form-control @error('total_weeks') is-invalid @enderror"
                               value="{{ old('total_weeks', 10) }}"
                               min="1"
                               max="52"
                               required>
                        <div class="text-muted mt-1" style="font-size: 12px;">
                            This determines how many weekly entry rows are generated (1–52).
                        </div>
                        @error('total_weeks')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('schemes.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                    <button type="submit" class="btn btn-primary btn-loading">
                        <span class="btn-text"><i class="fas fa-save"></i> Create Scheme</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Creating...
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

            var textareas = document.querySelectorAll('textarea.ck-editor-field, textarea[name="learning_objectives"]');

            textareas.forEach(function (textarea, index) {
                var editorKey = textarea.name || textarea.id || ('editor_' + index);

                if (ckEditors[editorKey]) {
                    return;
                }

                ClassicEditor
                    .create(textarea, {
                        toolbar: ['heading', '|', 'bold', 'italic', 'bulletedList', 'numberedList', '|', 'undo', 'redo'],
                    })
                    .then(function (editor) {
                        ckEditors[editorKey] = {
                            editor: editor,
                            textarea: textarea
                        };

                        if (textarea.name === 'learning_objectives') {
                            var editableElement = editor.ui.view.editable.element;
                            if (editableElement) {
                                editableElement.style.minHeight = '260px';
                            }
                        }
                    })
                    .catch(function (error) {
                        console.error('CKEditor init error for ' + editorKey + ':', error);
                    });
            });
        }

        if (typeof ClassicEditor !== 'undefined') {
            initEditors();
        } else {
            window.addEventListener('load', initEditors);
        }

        const form = document.getElementById('create-scheme-form');
        form.addEventListener('submit', function () {
            Object.keys(ckEditors).forEach(function (key) {
                var editorRef = ckEditors[key];
                if (editorRef && editorRef.textarea) {
                    editorRef.textarea.value = editorRef.editor.getData();
                }
            });

            const submitBtn = form.querySelector('button[type="submit"].btn-loading');
            if (submitBtn) {
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
            }
        });
    })();
    </script>
@endsection
