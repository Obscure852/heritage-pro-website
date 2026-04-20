@extends('layouts.master')
@section('title', $student->full_name.' – Final Year Student')
@section('css')
<style>
    .student-header {
        background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        color: white;
        padding: 28px;
        border-radius: 3px;
        margin-bottom: 1.5rem;
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

    .card {
        border-radius: 3px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: none;
    }

    .card-header {
        background: #f8f9fa;
        font-weight: 600;
        padding: 12px 16px;
        border-bottom: 1px solid #e5e7eb;
    }

    .btn {
        padding: 10px 20px;
        border-radius: 3px;
        font-size: 14px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        color: white;
    }

    .btn-info {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        color: white;
    }

    .btn-info:hover {
        background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
        color: white;
    }

    .grade-badge {
        width: 2.5rem;
        height: 2.5rem;
        font-weight: 700;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .grade-merit { background: #3b82f6; color: #fff; }
    .grade-a, .grade-b, .grade-c { background: #10b981; color: #fff; }
    .grade-d { background: #f59e0b; color: #fff; }
    .grade-e, .grade-u { background: #ef4444; color: #fff; }

    .subject-card {
        transition: all 0.2s ease;
        border-radius: 3px;
    }

    .subject-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .subject-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 1rem;
    }

    .nav-tabs .nav-link {
        border: none;
        border-bottom: 3px solid transparent;
        color: #6b7280;
        font-weight: 500;
        padding: 12px 20px;
        transition: all 0.2s;
    }

    .nav-tabs .nav-link:hover {
        border-color: transparent;
        color: #374151;
        background: #f9fafb;
    }

    .nav-tabs .nav-link.active {
        color: #3b82f6;
        background: none;
        border-bottom-color: #3b82f6;
    }

    .btn-loading.loading .btn-text {
        display: none;
    }

    .btn-loading.loading .btn-spinner {
        display: inline-flex !important;
        align-items: center;
    }

    .btn-loading:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    @media (max-width: 768px) {
        .student-header {
            padding: 16px;
        }
    }
</style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') <a href="{{ route('finals.students.index') }}">Back</a> @endslot
        @slot('li_2') <a href="{{ route('finals.students.index') }}">Students</a> @endslot
        @slot('title') {{ $student->full_name }} @endslot
    @endcomponent

    {{-- ▸ Student header --}}
    <div class="student-header">
        <div class="row g-3 align-items-center">
            <div class="col-auto">
                @if($student->photo_path)
                    <img src="{{ asset('storage/'.$student->photo_path) }}"
                         class="rounded-circle border border-white border-3 shadow-sm" style="width:100px;height:100px;object-fit:cover">
                @else
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-white bg-opacity-25"
                         style="width:100px;height:100px">
                        <i class="bx bx-user fs-2"></i>
                    </div>
                @endif
            </div>

            <div class="col">
                <h4 class="mb-1 text-white">{{ $student->full_name }}</h4>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    <span class="badge {{ $student->exam_number ? 'bg-white bg-opacity-25' : 'bg-secondary' }}">
                        {{ $student->exam_number ? 'Candidate #' . $student->exam_number : 'No Candidate #' }}
                    </span>
                    @isset($student->formatted_id_number)
                        <span class="badge bg-white bg-opacity-25">ID: {{ $student->formatted_id_number }}</span>
                    @endisset
                    <span class="badge bg-white bg-opacity-25">{{ $student->graduation_year }}</span>
                </div>

                @if($latestExamResult?->overall_grade)
                    <span class="opacity-75">Overall Grade:</span>
                    <span class="fs-3 fw-bold">{{ $latestExamResult->overall_grade }}</span>
                @endif
            </div>

        </div>
    </div>

    {{-- ▸ Tabs --}}
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#overview"><i class="bx bx-user me-1"></i>Overview</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#academic"><i class="bx bx-bar-chart-alt-2 me-1"></i>Academic Results</a></li>
    </ul>

    <div class="tab-content">
        {{-- ▶ Overview --}}
        <div class="tab-pane fade show active" id="overview">
            <div class="row g-3">
                {{-- personal --}}
                <div class="col-lg-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light fw-semibold">
                            <span><i class="bx bx-user text-primary me-1"></i>Personal Information</span>
                        </div>
                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger py-2">
                                    <ul class="mb-0 ps-3">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form action="{{ route('finals.students.update', $student) }}" method="POST" id="personalInfoForm">
                                @csrf
                                @method('PUT')

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">First Name</label>
                                        <input type="text" name="first_name" class="form-control"
                                               value="{{ old('first_name', $student->first_name) }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Last Name</label>
                                        <input type="text" name="last_name" class="form-control"
                                               value="{{ old('last_name', $student->last_name) }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Candidate Number</label>
                                        <input type="text" name="exam_number" class="form-control"
                                               value="{{ old('exam_number', $student->exam_number) }}"
                                               placeholder="Enter candidate number">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Gender</label>
                                        <select name="gender" class="form-select" required>
                                            <option value="">Select gender</option>
                                            <option value="M" {{ old('gender', $student->gender) === 'M' ? 'selected' : '' }}>Male</option>
                                            <option value="F" {{ old('gender', $student->gender) === 'F' ? 'selected' : '' }}>Female</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Date of Birth</label>
                                        <input type="date" name="date_of_birth" class="form-control"
                                               value="{{ old('date_of_birth', $student->date_of_birth?->format('Y-m-d')) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">ID Number</label>
                                        <input type="text" name="id_number" class="form-control"
                                               value="{{ old('id_number', $student->id_number) }}">
                                    </div>
                                </div>

                                <div class="mt-3 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary btn-loading">
                                        <span class="btn-text"><i class="fas fa-save"></i> Save Personal Information</span>
                                        <span class="btn-spinner d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            Saving...
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- academic --}}
                <div class="col-lg-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light fw-semibold">
                            <i class="bx bx-school text-primary me-1"></i>Academic Information
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Graduation Year</span>
                                <span class="badge bg-primary">{{ $student->graduation_year }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Grade</span>
                                <span>{{ $student->graduationGrade->name ?? 'Unknown' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Class</span>
                                <span>
                                    @forelse($student->finalKlasses as $klass)
                                        <span class="badge bg-light text-dark">{{ $klass->name }}</span>
                                    @empty <span class="text-muted">Not assigned</span>
                                    @endforelse
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Status</span>
                                <span>
                                    <span class="badge bg-info">{{ $student->status }}</span>
                                    @if($student->parent_is_staff)<span class="badge bg-warning ms-1">Staff Child</span>@endif
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- ▶ Academic --}}
        <div class="tab-pane fade" id="academic">
            {{-- Overall Result Card --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bx bx-trophy text-primary me-1"></i>Overall Result</span>
                    @if(!$latestExamResult || !$latestExamResult->overall_grade)
                        <button class="btn btn-sm btn-primary" id="toggleOverallForm">
                            <i class="bx bx-edit me-1"></i>Set Overall Result
                        </button>
                    @else
                        <button class="btn btn-sm btn-outline-primary" id="toggleOverallForm">
                            <i class="bx bx-edit me-1"></i>Edit
                        </button>
                    @endif
                </div>
                <div class="card-body">
                    {{-- Display current overall result --}}
                    <div id="overallResultDisplay" class="d-flex align-items-center gap-4">
                        @if($latestExamResult && $latestExamResult->overall_grade)
                            <div class="text-center">
                                <small class="text-muted d-block mb-1">Overall Grade</small>
                                <span class="badge bg-{{ $latestExamResult->grade_color }} fs-5 px-3 py-2" id="displayOverallGrade">{{ $latestExamResult->overall_grade }}</span>
                            </div>
                            <div class="text-center">
                                <small class="text-muted d-block mb-1">Total Points</small>
                                <span class="fs-5 fw-bold" id="displayOverallPoints">{{ $latestExamResult->overall_points ?? '—' }}</span>
                                <small class="text-muted">/ 63</small>
                            </div>
                            <div class="text-center">
                                <small class="text-muted d-block mb-1">Status</small>
                                <span class="badge bg-{{ $latestExamResult->is_pass ? 'success' : 'danger' }}" id="displayOverallStatus">
                                    <i class="bx {{ $latestExamResult->is_pass ? 'bx-check-circle' : 'bx-x-circle' }} me-1"></i>
                                    {{ $latestExamResult->is_pass ? 'Passed' : 'Failed' }}
                                </span>
                            </div>
                        @else
                            <div class="text-muted">
                                <i class="bx bx-info-circle me-1"></i>No overall result set. Click "Set Overall Result" to enter total points and overall grade.
                            </div>
                        @endif
                    </div>

                    {{-- Edit form (hidden by default) --}}
                    <div id="overallResultForm" class="d-none mt-3">
                        <div class="row g-3 align-items-end">
                            <div class="col-auto">
                                <label class="form-label fw-semibold">Total Points <small class="text-muted">(Max 63)</small></label>
                                <input type="number" class="form-control" id="overallPointsInput" min="0" max="63" step="0.5"
                                    value="{{ $latestExamResult->overall_points ?? '' }}" placeholder="0 - 63" style="width:120px">
                            </div>
                            <div class="col-auto">
                                <label class="form-label fw-semibold">Overall Grade</label>
                                <select class="form-select" id="overallGradeInput" style="width:120px">
                                    <option value="">Select</option>
                                    <option value="Merit" {{ ($latestExamResult->overall_grade ?? '') === 'Merit' ? 'selected' : '' }}>Merit</option>
                                    <option value="A" {{ ($latestExamResult->overall_grade ?? '') === 'A' ? 'selected' : '' }}>A</option>
                                    <option value="B" {{ ($latestExamResult->overall_grade ?? '') === 'B' ? 'selected' : '' }}>B</option>
                                    <option value="C" {{ ($latestExamResult->overall_grade ?? '') === 'C' ? 'selected' : '' }}>C</option>
                                    <option value="D" {{ ($latestExamResult->overall_grade ?? '') === 'D' ? 'selected' : '' }}>D</option>
                                    <option value="E" {{ ($latestExamResult->overall_grade ?? '') === 'E' ? 'selected' : '' }}>E</option>
                                    <option value="U" {{ ($latestExamResult->overall_grade ?? '') === 'U' ? 'selected' : '' }}>U</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-success" id="saveOverallBtn">
                                    <span class="btn-text"><i class="bx bx-check me-1"></i>Save</span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-1"></span>Saving...
                                    </span>
                                </button>
                                <button class="btn btn-light" id="cancelOverallBtn">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    @if($subjectResults->isNotEmpty())
                        {{-- Subject Results Grid --}}
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-semibold mb-0"><i class="bx bx-book-open text-primary me-1"></i>Subject Results</h5>
                            <div class="btn-group  btn-group-sm">
                                <button class="btn btn-secondary" onclick="filterSubjects('all')">All</button>
                                <button class="btn btn-success" onclick="filterSubjects('with-results')">With Results</button>
                                <button class="btn btn-warning" onclick="filterSubjects('pending')">Pending</button>
                                <button class="btn btn-primary" onclick="filterSubjects('mandatory')">Mandatory</button>
                                <button class="btn btn-info" onclick="filterSubjects('optional')">Optional</button>
                            </div>
                        </div>
        
                        <div class="subject-grid">
                            @foreach($subjectResults as $result)
                                <div class="card subject-card shadow-sm" data-subject-type="{{ $result->type }}" data-has-result="{{ $result->has_result ? 'true' : 'false' }}" data-fgs-id="{{ $result->final_grade_subject_id }}">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div class="flex-grow-1">
                                                <h6 class="fw-semibold mb-0">{{ $result->subject_name }}</h6>
                                                <div class="d-flex align-items-center gap-2">
                                                    <small class="text-muted">Code: {{ $result->subject_code }}</small>
                                                    <span class="badge badge-sm bg-{{ $result->type === 'mandatory' ? 'primary' : 'info' }}">
                                                        {{ ucfirst($result->type) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                @if($result->has_result)
                                                    <div class="grade-badge grade-{{ strtolower($result->grade) }}">{{ $result->grade }}</div>
                                                @else
                                                    <div class="grade-badge bg-light text-muted border">—</div>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center small">
                                            @if($result->has_result)
                                                <span class="{{ $result->is_pass ? 'text-success' : 'text-danger' }}">
                                                    <i class="bx {{ $result->is_pass ? 'bx-check-circle' : 'bx-x-circle' }} me-1"></i>
                                                    {{ $result->is_pass ? 'Passed' : 'Failed' }}
                                                </span>
                                                <span class="text-muted">{{ $result->grade_points }} pts</span>
                                            @else
                                                <div class="add-grade-row w-100 d-flex justify-content-between align-items-center">
                                                    <span class="text-muted awaiting-label">
                                                        <i class="bx bx-time-five me-1"></i>Awaiting Result
                                                    </span>
                                                    <div class="d-flex align-items-center gap-1">
                                                        <div class="grade-input-group d-none">
                                                            <select class="form-select form-select-sm grade-select" style="width:60px;height:38px;font-size:13px;">
                                                                <option value="">-</option>
                                                                <option value="A">A</option>
                                                                <option value="B">B</option>
                                                                <option value="C">C</option>
                                                                <option value="D">D</option>
                                                                <option value="E">E</option>
                                                                <option value="U">U</option>
                                                            </select>
                                                            <button class="btn btn-xs btn-success save-grade-btn" title="Save"><i class="bx bx-check"></i></button>
                                                            <button class="btn btn-xs btn-light cancel-grade-btn" title="Cancel"><i class="bx bx-x"></i></button>
                                                        </div>
                                                        <button class="btn btn-xs btn-primary toggle-grade-btn" title="Add Grade"><i class="bx bx-plus"></i></button>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
        
                        @if($stats['subjects_without_results'] > 0)
                            <div class="alert alert-info mt-4">
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-info-circle me-2"></i>
                                    <div class="flex-grow-1">
                                        <strong>{{ $stats['subjects_without_results'] }}</strong> subject(s) are still pending results.
                                        You can add manual results or wait for external exam import.
                                    </div>
                                    <button class="btn btn-sm btn-primary" onclick="showAllGradeInputs()">
                                        <i class="bx bx-plus me-1"></i>Add Results
                                    </button>
                                </div>
                            </div>
                        @endif
        
                    @else
                        {{-- No subjects found --}}
                        <div class="text-center py-5">
                            <i class="bx bx-book-open display-4 opacity-50 mb-3"></i>
                            <h5>No Subjects Found</h5>
                            <p class="text-muted">This student doesn't appear to be enrolled in any subjects for their graduation year.</p>
                            <button class="btn btn-sm btn-primary" onclick="showAllGradeInputs()">
                                <i class="bx bx-plus me-1"></i>Add Manual Results
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

<script>
    function filterSubjects(filterType) {
        const cards = document.querySelectorAll('.subject-card');

        cards.forEach(card => {
            const subjectType = card.getAttribute('data-subject-type');
            const hasResult = card.getAttribute('data-has-result') === 'true';
            let show = false;

            switch(filterType) {
                case 'all': show = true; break;
                case 'with-results': show = hasResult; break;
                case 'pending': show = !hasResult; break;
                case 'mandatory': show = subjectType === 'mandatory'; break;
                case 'optional': show = subjectType === 'optional'; break;
            }

            card.style.display = show ? 'block' : 'none';
        });

        document.querySelectorAll('.btn-group .btn').forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');
    }

    function showAllGradeInputs() {
        document.querySelectorAll('.subject-card[data-has-result="false"] .toggle-grade-btn').forEach(btn => btn.click());
    }

    document.addEventListener('DOMContentLoaded', function() {
        const allButton = document.querySelector('.btn-group .btn');
        if (allButton) allButton.classList.add('active');

        const personalInfoForm = document.getElementById('personalInfoForm');
        if (personalInfoForm) {
            personalInfoForm.addEventListener('submit', function() {
                const submitBtn = personalInfoForm.querySelector('button[type="submit"].btn-loading');
                if (submitBtn) {
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                }
            });
        }

        // Toggle grade input visibility
        document.querySelectorAll('.toggle-grade-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const card = this.closest('.subject-card');
                card.querySelector('.grade-input-group').classList.remove('d-none');
                card.querySelector('.awaiting-label').classList.add('d-none');
                this.classList.add('d-none');
            });
        });

        // Cancel grade input
        document.querySelectorAll('.cancel-grade-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const card = this.closest('.subject-card');
                card.querySelector('.grade-input-group').classList.add('d-none');
                card.querySelector('.awaiting-label').classList.remove('d-none');
                card.querySelector('.toggle-grade-btn').classList.remove('d-none');
                card.querySelector('.grade-select').value = '';
            });
        });

        // Save grade
        document.querySelectorAll('.save-grade-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const card = this.closest('.subject-card');
                const fgsId = card.getAttribute('data-fgs-id');
                const grade = card.querySelector('.grade-select').value;

                if (!grade) {
                    alert('Please select a grade.');
                    return;
                }

                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                fetch("{{ url('finals/students') }}/{{ $student->id }}/add-subject-result", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        final_grade_subject_id: fgsId,
                        grade: grade
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Update card inline
                        card.setAttribute('data-has-result', 'true');

                        const gradeBadge = card.querySelector('.grade-badge');
                        gradeBadge.className = 'grade-badge grade-' + data.grade.toLowerCase();
                        gradeBadge.textContent = data.grade;

                        const bottomRow = card.querySelector('.d-flex.justify-content-between.align-items-center.small');
                        bottomRow.innerHTML =
                            '<span class="' + (data.is_pass ? 'text-success' : 'text-danger') + '">' +
                                '<i class="bx ' + (data.is_pass ? 'bx-check-circle' : 'bx-x-circle') + ' me-1"></i>' +
                                (data.is_pass ? 'Passed' : 'Failed') +
                            '</span>' +
                            '<span class="text-muted">' + data.grade_points + ' pts</span>';
                    } else {
                        alert(data.message || 'Failed to save grade.');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bx bx-check"></i>';
                    }
                })
                .catch(err => {
                    alert('An error occurred. Please try again.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bx bx-check"></i>';
                });
            });
        });

        // Overall Result form toggle
        const toggleOverallBtn = document.getElementById('toggleOverallForm');
        const overallForm = document.getElementById('overallResultForm');
        const overallDisplay = document.getElementById('overallResultDisplay');
        const cancelOverallBtn = document.getElementById('cancelOverallBtn');

        if (toggleOverallBtn) {
            toggleOverallBtn.addEventListener('click', function() {
                overallForm.classList.remove('d-none');
                overallDisplay.classList.add('d-none');
                this.classList.add('d-none');
            });
        }

        if (cancelOverallBtn) {
            cancelOverallBtn.addEventListener('click', function() {
                overallForm.classList.add('d-none');
                overallDisplay.classList.remove('d-none');
                toggleOverallBtn.classList.remove('d-none');
            });
        }

        // Save overall result
        const saveOverallBtn = document.getElementById('saveOverallBtn');
        if (saveOverallBtn) {
            saveOverallBtn.addEventListener('click', function() {
                const points = document.getElementById('overallPointsInput').value;
                const grade = document.getElementById('overallGradeInput').value;

                if (!points || !grade) {
                    alert('Please enter both total points and overall grade.');
                    return;
                }

                if (parseFloat(points) < 0 || parseFloat(points) > 63) {
                    alert('Total points must be between 0 and 63.');
                    return;
                }

                this.querySelector('.btn-text').classList.add('d-none');
                this.querySelector('.btn-spinner').classList.remove('d-none');
                this.disabled = true;

                fetch("{{ url('finals/students') }}/{{ $student->id }}/update-overall-result", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        overall_points: parseFloat(points),
                        overall_grade: grade
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const gradeColors = {
                            'Merit': 'primary', 'A': 'success', 'B': 'success', 'C': 'success',
                            'D': 'warning', 'E': 'danger', 'U': 'danger'
                        };
                        const color = gradeColors[data.overall_grade] || 'secondary';
                        const isPass = data.is_pass;

                        overallDisplay.innerHTML =
                            '<div class="text-center">' +
                                '<small class="text-muted d-block mb-1">Overall Grade</small>' +
                                '<span class="badge bg-' + color + ' fs-5 px-3 py-2" id="displayOverallGrade">' + data.overall_grade + '</span>' +
                            '</div>' +
                            '<div class="text-center">' +
                                '<small class="text-muted d-block mb-1">Total Points</small>' +
                                '<span class="fs-5 fw-bold" id="displayOverallPoints">' + data.overall_points + '</span>' +
                                '<small class="text-muted"> / 63</small>' +
                            '</div>' +
                            '<div class="text-center">' +
                                '<small class="text-muted d-block mb-1">Status</small>' +
                                '<span class="badge bg-' + (isPass ? 'success' : 'danger') + '" id="displayOverallStatus">' +
                                    '<i class="bx ' + (isPass ? 'bx-check-circle' : 'bx-x-circle') + ' me-1"></i>' +
                                    (isPass ? 'Passed' : 'Failed') +
                                '</span>' +
                            '</div>';

                        overallForm.classList.add('d-none');
                        overallDisplay.classList.remove('d-none');
                        toggleOverallBtn.classList.remove('d-none');
                        toggleOverallBtn.innerHTML = '<i class="bx bx-edit me-1"></i>Edit';
                        toggleOverallBtn.className = 'btn btn-sm btn-outline-primary';
                    } else {
                        alert(data.message || 'Failed to save overall result.');
                    }

                    saveOverallBtn.querySelector('.btn-text').classList.remove('d-none');
                    saveOverallBtn.querySelector('.btn-spinner').classList.add('d-none');
                    saveOverallBtn.disabled = false;
                })
                .catch(err => {
                    alert('An error occurred. Please try again.');
                    saveOverallBtn.querySelector('.btn-text').classList.remove('d-none');
                    saveOverallBtn.querySelector('.btn-spinner').classList.add('d-none');
                    saveOverallBtn.disabled = false;
                });
            });
        }
    });
</script>
    
    <style>
    .badge-sm {
        font-size: 0.65em;
        padding: 0.2em 0.4em;
    }

    .btn-xs {
        padding: 0.1rem 0.3rem;
        font-size: 0.7rem;
        line-height: 1.2;
    }

    .grade-input-group {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
</style>
