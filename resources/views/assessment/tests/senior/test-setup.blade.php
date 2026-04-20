@extends('layouts.master')
@section('title')
    Assessment Module
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

        .required::after {
            content: "*";
            color: #dc3545;
            margin-left: 4px;
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
            <a href="{{ route('assessment.test-list') }}">Back</a>
        @endslot
        @slot('title')
            Tests Setup
        @endslot
    @endcomponent

    @if (session('message'))
        <div class="row">
            <div class="col-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="row">
            <div class="col-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        <i class="mdi mdi-block-helper label-icon"></i><strong>{{ $error }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

    <div class="row">
        <div class="col-12">
            <div class="settings-container">
                <div class="settings-header">
                    <h3><i class="fas fa-clipboard-list me-2"></i>Tests Setup</h3>
                    <p>Create and configure assessment tests for core and optional subjects</p>
                </div>
                <div class="settings-body">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#core-subjects" role="tab">
                                <i class="fas fa-book me-1"></i> Core Subject Test
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#optional-subjects" role="tab">
                                <i class="fas fa-puzzle-piece me-1"></i> Optional Subject Test
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#settings" role="tab">
                                <i class="fas fa-cog me-1"></i> Settings
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="core-subjects" role="tabpanel">
                            <form class="needs-validation" method="post" action="{{ route('assessment.store') }}"
                                novalidate>
                                @csrf
                                <input type="hidden" name="term" value="{{ old('term', $currentTerm->id ?? 0) }}">
                                <input type="hidden" name="year" value="{{ old('year', $currentTerm->year ?? 0) }}">

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label required" for="sequence">Seq</label>
                                        <select name="sequence" class="form-select form-select-sm" required>
                                            <option value="">Select Number ...</option>
                                            @for ($i = 1; $i < 10; $i++)
                                                <option value="{{ $i }}"
                                                    {{ old('sequence') == $i ? 'selected' : '' }}>{{ $i }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label required" for="name">Name</label>
                                        <input type="text" name="name" class="form-control form-control-sm"
                                            placeholder="e.g. Exam" value="{{ old('name') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label required" for="abbrev">Abbrev</label>
                                        <input type="text" name="abbrev" class="form-control form-control-sm"
                                            placeholder="e.g. Aug" value="{{ old('abbrev') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label required" for="assessment">Include in Report Card</label>
                                        <select name="assessment" class="form-select form-select-sm"
                                            required>
                                            <option value="">Select Assessment ...</option>
                                            <option value="1" {{ old('assessment') == '1' ? 'selected' : '' }}>Yes
                                            </option>
                                            <option value="0" {{ old('assessment') == '0' ? 'selected' : '' }}>No
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label required" for="grade">Grade</label>
                                        <select name="grade" class="form-select form-select-sm" required>
                                            <option value="">Select Grade ...</option>
                                            @if (!empty($grades))
                                                @foreach ($grades as $grade)
                                                    <option value="{{ $grade->id }}"
                                                        {{ old('grade') == $grade->id ? 'selected' : '' }}>
                                                        {{ $grade->name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label required" for="subject">Subject</label>
                                        <small style="color: red;margin-left:2px;"></small>
                                        <select name="subject" class="form-select form-select-sm" required>
                                            <option value="">Select Class Subject ...</option>
                                            <!-- Options will be populated by JavaScript when grade is selected -->
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label required" for="type">Type</label>
                                        <select name="type" class="form-select form-select-sm" required>
                                            <option value="">Select type ...</option>
                                            <option value="Exercise" {{ old('type') == 'Exercise' ? 'selected' : '' }}>
                                                Exercise</option>
                                            <option value="CA" {{ old('type') == 'CA' ? 'selected' : '' }}>CA</option>
                                            <option value="Exam" {{ old('type') == 'Exam' ? 'selected' : '' }}>Exam
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label required" for="out_of">Out Of</label>
                                        <input type="number" name="out_of" class="form-control form-select-sm"
                                            placeholder="e.g. 100" value="{{ old('out_of') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label required" for="start_date">Start Date</label>
                                        <input type="date" name="start_date" class="form-control form-control-sm"
                                            value="{{ old('start_date') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label required" for="end_date">End Date</label>
                                        <input type="date" name="end_date" class="form-control form-control-sm"
                                            value="{{ old('end_date') }}" required>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <a href="{{ route('assessment.test-list') }}" class="btn-back">
                                        <i class="bx bx-arrow-back"></i> Back
                                    </a>
                                    <button type="submit" class="btn-save">
                                        <span class="btn-text"><i class="fas fa-save"></i> Create Test</span>
                                        <span class="btn-spinner d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            Creating...
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane" id="optional-subjects" role="tabpanel">
                            <form class="needs-validation" method="post"
                                action="{{ route('assessment.optional-store') }}" novalidate>
                                @csrf
                                <input type="hidden" name="term" value="{{ old('term', $currentTerm->id ?? 0) }}">
                                <input type="hidden" name="year" value="{{ old('year', $currentTerm->year ?? 0) }}">

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label required" for="sequence">Seq</label>
                                        <select name="sequence" class="form-select form-select-sm" required>
                                            <option value="">Select Number ...</option>
                                            @for ($i = 1; $i < 10; $i++)
                                                <option value="{{ $i }}"
                                                    {{ old('sequence') == $i ? 'selected' : '' }}>{{ $i }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label required" for="name">Name</label>
                                        <input type="text" name="name" class="form-control form-control-sm"
                                            placeholder="e.g. July" value="{{ old('name') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label required" for="abbrev">Abbrev</label>
                                        <input type="text" name="abbrev" class="form-control form-control-sm"
                                            placeholder="e.g. Aug" value="{{ old('abbrev') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label required" for="assessment">Include in Report Card</label>
                                        <select name="assessment" class="form-select form-select-sm"
                                            required>
                                            <option value="">Select Assessment ...</option>
                                            <option value="1" {{ old('assessment') == '1' ? 'selected' : '' }}>Yes
                                            </option>
                                            <option value="0" {{ old('assessment') == '0' ? 'selected' : '' }}>No
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label required" for="grade">Grade</label>
                                        <select name="grade" class="form-select form-select-sm" required>
                                            <option value="">Select Grade ...</option>
                                            @if (!empty($grades))
                                                @foreach ($grades as $grade)
                                                    <option value="{{ $grade->id }}"
                                                        {{ old('grade') == $grade->id ? 'selected' : '' }}>
                                                        {{ $grade->name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label required" for="subject">Subject</label>
                                        <small style="color: red;margin-left:2px;"></small>
                                        <select name="subject" class="form-select form-select-sm" required>
                                            <option value="">Select Optional Subject ...</option>
                                            <!-- Options will be populated by JavaScript when grade is selected -->
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label required" for="type">Type</label>
                                        <select name="type" class="form-select form-select-sm" required>
                                            <option value="">Select type ...</option>
                                            <option value="Exercise" {{ old('type') == 'Exercise' ? 'selected' : '' }}>
                                                Exercise</option>
                                            <option value="CA" {{ old('type') == 'CA' ? 'selected' : '' }}>CA</option>
                                            <option value="Exam" {{ old('type') == 'Exam' ? 'selected' : '' }}>Exam
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label required" for="out_of">Out Of</label>
                                        <input type="number" name="out_of" class="form-control form-control-sm"
                                            placeholder="e.g. 100" value="{{ old('out_of') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label required" for="start_date">Start Date</label>
                                        <input type="date" name="start_date" class="form-control form-control-sm"
                                            value="{{ old('start_date') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label required" for="end_date">End Date</label>
                                        <input type="date" name="end_date" class="form-control form-control-sm"
                                            value="{{ old('end_date') }}" required>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <a href="{{ route('assessment.test-list') }}" class="btn-back">
                                        <i class="bx bx-arrow-back"></i> Back
                                    </a>
                                    <button type="submit" class="btn-save">
                                        <span class="btn-text"><i class="fas fa-save"></i> Create Test</span>
                                        <span class="btn-spinner d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            Creating...
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane" id="settings" role="tabpanel">
                            <div class="text-center py-5">
                                <i class="fas fa-cog fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No configuration options available</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tabList = document.querySelectorAll('[data-bs-toggle="tab"]');
            tabList.forEach(function(tabEl) {
                new bootstrap.Tab(tabEl);
            });

            const activeTabId = localStorage.getItem('activeTab') || 'core-subjects';
            const activeTabEl = document.querySelector(`a[href="#${activeTabId}"]`);
            if (activeTabEl) {
                const tab = new bootstrap.Tab(activeTabEl);
                tab.show();
            }

            document.querySelectorAll('.nav-link').forEach(tab => {
                tab.addEventListener('click', (e) => {
                    const tabId = e.target.getAttribute('href').substring(1);
                    localStorage.setItem('activeTab', tabId);
                });
            });

            const coreGradeSelect = document.querySelector('#core-subjects select[name="grade"]');
            const coreSubjectSelect = document.querySelector('#core-subjects select[name="subject"]');
            
            if (coreGradeSelect && coreSubjectSelect) {
                coreGradeSelect.addEventListener('change', function() {
                    loadSubjectsForGrade(this.value, coreSubjectSelect, 'core');
                });
            }

            const optionalGradeSelect = document.querySelector('#optional-subjects select[name="grade"]');
            const optionalSubjectSelect = document.querySelector('#optional-subjects select[name="subject"]');
            
            if (optionalGradeSelect && optionalSubjectSelect) {
                optionalGradeSelect.addEventListener('change', function() {
                    console.log('Optional grade changed to:', this.value);
                    loadSubjectsForGrade(this.value, optionalSubjectSelect, 'optional');
                });
            }

            function loadSubjectsForGrade(gradeId, subjectSelect, type) {
                console.log('Loading subjects for grade:', gradeId, 'type:', type);
                
                if (!gradeId) {
                    resetSubjectSelect(subjectSelect, type);
                    return;
                }

                showLoadingState(subjectSelect);
                let url;
                if (type === 'core') {
                    url = "{{ route('optional.grade-core-subjects-by-grade', ['gradeId' => ':gradeId']) }}";
                } else {
                    url = "{{ route('optional.grade-subjects-by-grade', ['gradeId' => ':gradeId']) }}";
                }
                
                url = url.replace(':gradeId', gradeId);
                console.log('Fetching from URL:', url);

                fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    hideLoadingState(subjectSelect);
                    
                    if (data.success && data.data) {
                        populateSubjects(subjectSelect, data.data, type);
                    } else {
                        showErrorState(subjectSelect, 'Error loading subjects');
                        console.error('Server response error:', data.message || 'Unknown error');
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    hideLoadingState(subjectSelect);
                    showErrorState(subjectSelect, 'Failed to load subjects');
                });
            }

            function showLoadingState(subjectSelect) {
                subjectSelect.disabled = true;
                subjectSelect.innerHTML = '<option value="">Loading subjects...</option>';
            }

            function hideLoadingState(subjectSelect) {
                subjectSelect.disabled = false;
            }

            function resetSubjectSelect(subjectSelect, type) {
                subjectSelect.disabled = false;
                const placeholder = type === 'core' ? 'Select Class Subject ...' : 'Select Optional Subject ...';
                subjectSelect.innerHTML = `<option value="">${placeholder}</option>`;
            }

            function showErrorState(subjectSelect, message) {
                subjectSelect.innerHTML = `<option value="">${message}</option>`;
            }

            function populateSubjects(subjectSelect, subjects, type) {
                console.log('Populating subjects:', subjects, 'for type:', type);
                
                const placeholder = type === 'core' ? 'Select Class Subject ...' : 'Select Optional Subject ...';
                subjectSelect.innerHTML = `<option value="">${placeholder}</option>`;

                if (!subjects || subjects.length === 0) {
                    subjectSelect.innerHTML += '<option value="">No subjects found for this grade</option>';
                    console.log('No subjects found');
                    return;
                }

                subjects.forEach(gradeSubject => {
                    console.log('Processing subject:', gradeSubject);
                    
                    const subjectName = gradeSubject.subject ? gradeSubject.subject.name : 'Unknown Subject';
                    
                    const option = document.createElement('option');
                    option.value = gradeSubject.id;
                    option.textContent = subjectName;
                    subjectSelect.appendChild(option);
                    
                    console.log('Added option:', option.value, option.textContent);
                });
                
                console.log('Finished populating subjects. Total options:', subjectSelect.options.length);
            }

            document.querySelectorAll('form.needs-validation').forEach(form => {
                form.addEventListener('submit', function(e) {
                    const subjectSelect = form.querySelector('select[name="subject"]');
                    if (subjectSelect && !subjectSelect.value) {
                        e.preventDefault();
                        e.stopPropagation();

                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                        alertDiv.innerHTML = `
                            <i class="mdi mdi-block-helper label-icon"></i>
                            <strong>Please select a subject before creating the test.</strong>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        `;

                        form.insertBefore(alertDiv, form.firstChild);
                        setTimeout(() => {
                            if (alertDiv.parentNode) {
                                alertDiv.remove();
                            }
                        }, 5000);
                        return false;
                    }

                    // Show loading state on button
                    const btn = form.querySelector('.btn-save');
                    if (btn) {
                        btn.classList.add('loading');
                        btn.disabled = true;
                    }
                });
            });
        });
    </script>
@endsection