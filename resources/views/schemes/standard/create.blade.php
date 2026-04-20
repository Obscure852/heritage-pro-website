@extends('layouts.master')
@section('title')
    Create Standard Scheme
@endsection
@section('css')
    @include('schemes.partials.schemes-styles')
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('standard-schemes.index') }}">Standard Schemes</a>
        @endslot
        @slot('title')
            Create Standard Scheme
        @endslot
    @endcomponent

    <div class="schemes-container">
        <div class="header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 style="margin: 0;">Create Standard Scheme</h3>
                    <p style="margin: 6px 0 0 0; opacity: .9;">Define a subject-level scheme for all teachers in a grade</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('standard-schemes.index') }}" class="btn-outline-white">
                        <i class="fas fa-arrow-left"></i> Back to Standard Schemes
                    </a>
                </div>
            </div>
        </div>

        <div class="form-container">
            <div class="help-text">
                <div class="help-title">Create a Standard Scheme</div>
                <div class="help-content">
                    Select the subject, grade, and term. Weekly entry rows will be auto-generated. Teachers for this subject+grade will be added as viewers automatically.
                </div>
            </div>

            <form action="{{ route('standard-schemes.store') }}" method="POST" id="create-standard-scheme-form">
                @csrf

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="section-title">Subject & Grade</div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="grade_id" class="form-label">Grade <span class="text-danger">*</span></label>
                        <select name="grade_id" id="grade_id" class="form-select @error('grade_id') is-invalid @enderror" required>
                            <option value="">Select Grade...</option>
                            @foreach ($grades as $grade)
                                <option value="{{ $grade->id }}" {{ old('grade_id') == $grade->id ? 'selected' : '' }}>
                                    {{ $grade->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('grade_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="subject_id" class="form-label">Subject <span class="text-danger">*</span></label>
                        <select name="subject_id" id="subject_id" class="form-select @error('subject_id') is-invalid @enderror" required>
                            <option value="">Select Subject...</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->label ?? $subject->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('subject_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="section-title">Term & Duration</div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="term_id" class="form-label">Term <span class="text-danger">*</span></label>
                        <select name="term_id" id="term_id" class="form-select @error('term_id') is-invalid @enderror" required>
                            <option value="">Select Term...</option>
                            @foreach ($terms as $term)
                                <option value="{{ $term->id }}"
                                    {{ old('term_id', $currentTerm?->id) == $term->id ? 'selected' : '' }}>
                                    Term {{ $term->term }}, {{ $term->year }}
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
                    <a href="{{ route('standard-schemes.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                    <button type="submit" class="btn btn-primary btn-loading">
                        <span class="btn-text"><i class="fas fa-save"></i> Create Standard Scheme</span>
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
        var form = document.getElementById('create-standard-scheme-form');
        var termSelect = document.getElementById('term_id');
        var gradeSelect = document.getElementById('grade_id');
        var subjectSelect = document.getElementById('subject_id');
        var gradesUrl = @json(route('standard-schemes.grades-for-term'));
        var subjectsUrl = @json(route('standard-schemes.subjects-for-context'));
        var initialGradeId = @json(old('grade_id'));
        var initialSubjectId = @json(old('subject_id'));

        function rebuildGrades(grades, selectedGradeId) {
            if (!gradeSelect) {
                return;
            }

            gradeSelect.innerHTML = '';

            var placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = 'Select Grade...';
            gradeSelect.appendChild(placeholder);

            grades.forEach(function (grade, index) {
                var option = document.createElement('option');
                option.value = grade.id;
                option.textContent = grade.name;

                if (String(selectedGradeId || '') === String(grade.id)) {
                    option.selected = true;
                } else if (!selectedGradeId && index === 0) {
                    option.selected = true;
                }

                gradeSelect.appendChild(option);
            });
        }

        function rebuildSubjects(subjects, selectedSubjectId) {
            if (!subjectSelect) {
                return;
            }

            subjectSelect.innerHTML = '';

            var placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = 'Select Subject...';
            subjectSelect.appendChild(placeholder);

            subjects.forEach(function (subject, index) {
                var option = document.createElement('option');
                option.value = subject.id;
                option.textContent = subject.label || subject.name;

                if (String(selectedSubjectId || '') === String(subject.id)) {
                    option.selected = true;
                } else if (!selectedSubjectId && index === 0) {
                    option.selected = true;
                }

                subjectSelect.appendChild(option);
            });
        }

        function loadGradesForTerm(termId, selectedGradeId) {
            if (!termId || !gradeSelect) {
                rebuildGrades([], null);
                return Promise.resolve([]);
            }

            gradeSelect.disabled = true;

            return fetch(gradesUrl + '?term_id=' + encodeURIComponent(termId), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Failed to load grades');
                    }

                    return response.json();
                })
                .then(function (grades) {
                    rebuildGrades(grades, selectedGradeId);
                    return grades;
                })
                .catch(function () {
                    rebuildGrades([], null);
                    return [];
                })
                .finally(function () {
                    gradeSelect.disabled = false;
                });
        }

        function loadSubjectsForContext(termId, gradeId, selectedSubjectId) {
            if (!termId || !gradeId || !subjectSelect) {
                rebuildSubjects([], null);
                return;
            }

            subjectSelect.disabled = true;

            fetch(
                subjectsUrl + '?term_id=' + encodeURIComponent(termId) + '&grade_id=' + encodeURIComponent(gradeId),
                {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                }
            )
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Failed to load subjects');
                    }

                    return response.json();
                })
                .then(function (subjects) {
                    rebuildSubjects(subjects, selectedSubjectId);
                })
                .catch(function () {
                    rebuildSubjects([], null);
                })
                .finally(function () {
                    subjectSelect.disabled = false;
                });
        }

        if (termSelect && gradeSelect && subjectSelect) {
            termSelect.addEventListener('change', function () {
                loadGradesForTerm(termSelect.value, null).then(function () {
                    loadSubjectsForContext(termSelect.value, gradeSelect.value, null);
                });
            });

            gradeSelect.addEventListener('change', function () {
                loadSubjectsForContext(termSelect.value, gradeSelect.value, null);
            });

            loadGradesForTerm(termSelect.value, initialGradeId).then(function () {
                loadSubjectsForContext(termSelect.value, gradeSelect.value, initialSubjectId);
            });
        }

        form.addEventListener('submit', function () {
            var submitBtn = form.querySelector('button[type="submit"].btn-loading');
            if (submitBtn) {
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
            }
        });
    })();
    </script>
@endsection
