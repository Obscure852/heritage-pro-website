@extends('layouts.master')
@section('title', $klass->name.' – Class Details')
@section('css')
    <style>
        .class-header {
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

        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .subject-grid, .student-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 1rem;
        }

        .progress-bar-lg {
            height: 8px;
            border-radius: 4px;
            background: #e9ecef;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            border-radius: 4px;
        }

        .rate-excellent { background: #10b981; }
        .rate-good { background: #f59e0b; }
        .rate-poor { background: #ef4444; }

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

        .gender-male { color: #007bff; }
        .gender-female { color: #e83e8c; }

        @media (max-width: 768px) {
            .class-header {
                padding: 16px;
            }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1') <a href="{{ route('finals.classes.index') }}">Back</a> @endslot
        @slot('li_2') <a href="{{ route('finals.classes.index') }}">Classes</a> @endslot
        @slot('title') {{ $klass->name }} @endslot
    @endcomponent

    @if (session('message') || session('success'))
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show"
                    role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') ?? session('success') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show"
                    role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('warning'))
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-warning alert-dismissible alert-label-icon label-arrow fade show"
                    role="alert">
                    <i class="mdi mdi-alert label-icon"></i><strong>{{ session('warning') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <div class="class-header">
        <div class="row g-3 align-items-center">
            <div class="col-md-8">
                <h3 class="mb-1 text-white" style="margin:0;">{{ $klass->name }}</h3>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="badge bg-white bg-opacity-25">{{ $klass->grade->name ?? 'Unknown Grade' }}</span>
                    <span class="badge bg-white bg-opacity-25">{{ $klass->graduation_year }}</span>
                </div>
                <div class="d-flex flex-wrap gap-4">
                    <span><i class="bx bx-user me-1"></i>Teacher: {{ $klass->teacher->full_name ?? 'Not Assigned' }}</span>
                    <span><i class="bx bx-group me-1"></i>{{ $stats['total_students'] }} Students</span>
                </div>
            </div>
            <div class="col-md-4 text-md-end">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 d-flex justify-content-end">
            <div class="btn-group dropstart me-2">
                <!-- Main Button -->
                <button type="button" class="btn btn-sm btn-primary">
                    <i class="bx bx-sort-down me-1"></i> Class Analysis Reports
                </button>
                <!-- Split Toggle Button -->
                <button type="button" class="btn btn-sm btn-primary dropdown-toggle dropdown-toggle-split"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="visually-hidden">Toggle Dropstart</span>
                    <i class="bx bx-chevron-left"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-compact shadow-lg">
                    <!-- Class Report -->
                    <li>
                        <a class="dropdown-item" href="{{ route('finals.class.overall-analysis', $klass->id) }}">
                            <i class="bx bx-line-chart me-2 text-info"></i> Class Overall Analysis Report
                        </a>
                    </li>
                    
                    <!-- Subjects Performance Analysis -->
                    <li>
                        <a class="dropdown-item" href="{{ route('finals.class.subjects-summary-analyis',$klass->id) }}">
                            <i class=" bx bx-chart me-2 text-info"></i> Class Subjects Summary Report
                        </a>
                    </li>
                    
                    <!-- Teacher Performance Analysis -->
                    <li>
                        <a class="dropdown-item" href="{{ route('finals.classes.jce-psle-comparison',$klass->id) }}">
                            <i class="bx bx-bar-chart me-2 text-info"></i> Class Value Addition Report
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <ul class="nav nav-tabs mb-4" id="classTabs">
        @foreach([
            ['overview','bx-info-circle','Overview'],
            ['subjects','bx-book-open','Subject Performance'],
            ['manage-students','bx-user-plus','Manage Students']
        ] as $index => [$id,$icon,$label])
            <li class="nav-item">
                <a class="nav-link {{ $index === 0 ? 'active' : '' }}" 
                   data-bs-toggle="tab" 
                   href="#{{ $id }}" 
                   id="{{ $id }}-tab"
                   data-tab="{{ $id }}">
                    <i class="bx {{ $icon }} me-1"></i>{{ $label }}
                </a>
            </li>
        @endforeach
    </ul>

    <div class="tab-content" id="classTabsContent">
        <!-- Overview Tab -->
        <div class="tab-pane fade show active" id="overview" role="tabpanel">
            <div class="row g-3">
                <div class="col-lg-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light fw-semibold">
                            <i class="bx bx-info-circle text-primary me-1"></i>Class Information
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between"><span>Name</span><span>{{ $klass->name }}</span></li>
                            <li class="list-group-item d-flex justify-content-between"><span>Grade</span><span class="badge bg-primary">{{ $klass->grade->name ?? 'Unknown' }}</span></li>
                            <li class="list-group-item d-flex justify-content-between"><span>Teacher</span><span>{{ $klass->teacher->full_name ?? 'Not Assigned' }}</span></li>
                            <li class="list-group-item d-flex justify-content-between"><span>Graduation Year</span><span>{{ $klass->graduation_year }}</span></li>
                            <li class="list-group-item d-flex justify-content-between"><span>Graduation Term</span><span>{{ $klass->graduationTerm->name ?? 'Unknown' }}</span></li>
                            <li class="list-group-item d-flex justify-content-between"><span>Total Students</span><span class="fw-bold">{{ $stats['total_students'] }}</span></li>
                        </ul>
                    </div>
                </div>
                {{-- performance summary --}}
                <div class="col-lg-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light fw-semibold">
                            <i class="bx bx-chart-alt text-primary me-1"></i>Performance Summary
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between"><span>With Results</span><span class="badge bg-success">{{ $stats['students_with_results'] }}</span></li>
                            <li class="list-group-item d-flex justify-content-between"><span>Pending Results</span><span class="badge bg-warning">{{ $stats['students_pending'] }}</span></li>
                            <li class="list-group-item d-flex justify-content-between"><span>Passed Students</span><span class="badge bg-success">{{ $stats['passed_students'] }}</span></li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Pass Rate</span>
                                <span class="d-flex align-items-center gap-2">
                                    <span class="fw-bold">{{ $stats['pass_rate'] }}%</span>
                                    <div class="progress-bar-lg" style="width:100px">
                                        <div class="progress-fill {{ $stats['pass_rate']>=80?'rate-excellent':($stats['pass_rate']>=60?'rate-good':'rate-poor') }}" style="width:{{ $stats['pass_rate'] }}%"></div>
                                    </div>
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between"><span>Average Points</span><span class="fw-bold">{{ $stats['average_points'] }}</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subject Performance Tab -->
        <div class="tab-pane fade" id="subjects" role="tabpanel">
            <h5 class="fw-semibold mb-3"><i class="bx bx-book-open text-primary me-1"></i>Subject Performance Analysis</h5>
            @if($subjectPerformance->isNotEmpty())
                <div class="subject-grid">
                    @foreach($subjectPerformance as $subject)
                        <div class="card hover-lift shadow-sm">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="fw-semibold mb-0">{{ $subject['subject_name'] }}</h6>
                                        <small class="text-muted">Code: {{ $subject['subject_code'] }}</small>
                                    </div>
                                    <span class="badge {{ $subject['pass_rate']>=80?'bg-success':($subject['pass_rate']>=60?'bg-warning':'bg-danger') }}">{{ $subject['pass_rate'] }}%</span>
                                </div>
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span>Pass Rate</span><span>{{ $subject['passed_students'] }}/{{ $subject['total_students'] }}</span>
                                    </div>
                                    <div class="progress-bar-lg">
                                        <div class="progress-fill {{ $subject['pass_rate']>=80?'rate-excellent':($subject['pass_rate']>=60?'rate-good':'rate-poor') }}" style="width:{{ $subject['pass_rate'] }}%"></div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between small">
                                    <span class="text-muted">Avg Points: {{ $subject['average_points'] }}</span>
                                    <span class="text-muted">{{ $subject['total_students'] }} students</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bx bx-book-open display-4 opacity-50 mb-3"></i>
                    <h6 class="text-muted">No Subject Performance Data</h6>
                </div>
            @endif
        </div>

        <!-- Manage Students Tab -->
        <div class="tab-pane fade" id="manage-students" role="tabpanel">
            <div class="rounded-2 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-semibold mb-0">
                        <i class="bx bx-group text-primary me-1"></i>
                        Current Students ({{ $klass->finalStudents->count() }})
                    </h5>
                    @if($klass->finalStudents->isEmpty())
                        <button class="btn btn-primary btn-sm" onclick="alert('Add students functionality would go here')">
                            <i class="bx bx-plus me-1"></i>Add Students
                        </button>
                    @endif
                </div>

                @if($klass->finalStudents->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-sm align-middle table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 36px">#</th>
                                    <th style="width: 60px">Photo</th>
                                    <th>Full&nbsp;Name</th>
                                    <th>Center&nbsp;No.</th>
                                    <th style="width: 70px">Gender</th>
                                    <th style="width: 110px">Results</th>
                                    <th style="width: 62px">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($klass->finalStudents as $i => $student)
                                    @php
                                        $hasResults = $student->externalExamResults->isNotEmpty();
                                    @endphp
                                    <tr>
                                        <td>{{ $i + 1 }}</td>

                                        {{-- avatar --}}
                                        <td>
                                            @if($student->photo_path)
                                                <img src="{{ asset('storage/'.$student->photo_path) }}"
                                                     class="rounded-circle"
                                                     style="width:30px;height:30px;object-fit:cover">
                                            @else
                                                <span class="d-inline-flex rounded-circle bg-secondary text-white
                                                             justify-content-center align-items-center"
                                                      style="width:30px;height:30px">
                                                    <i class="bx bxs-graduation font-size-16"></i>
                                                </span>
                                            @endif
                                        </td>

                                        <td>
                                            <a href="{{ route('finals.students.show', $student->id) }}" 
                                               class="text-decoration-none fw-medium">
                                                {{ $student->full_name }}
                                            </a>
                                        </td>
                                        <td>
                                            @if ($student->exam_number)
                                                <span class="badge bg-light text-dark">{{ $student->exam_number }}</span>
                                            @else
                                                <span class="badge bg-secondary">Not entered</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $student->gender=='M' ? 'primary' : 'success' }}">
                                                {{ $student->gender=='M' ? 'M' : 'F' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($hasResults)
                                                <span class="badge bg-success">Has Results</span>
                                            @else
                                                <span class="badge bg-secondary">No Results</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <form method="POST" 
                                                  action="{{ route('finals.classes.students.remove', ['klassId' => $klass->id, 'studentId' => $student->id]) }}"
                                                  onsubmit="return confirm('This action will remove the student with the results.⚠️')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-danger">
                                                    <i class="bx bx-trash font-size-16"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bx bx-group display-4 opacity-50 mb-3"></i>
                        <h6 class="text-muted">No students assigned to this class yet.</h6>
                        <p class="text-muted small">Students will be assigned during the finals rollover process or manually added.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        function initTabPersistence() {
            const urlHash = window.location.hash.replace('#', '');
            const savedTab = localStorage.getItem('activeClassTab');
            const targetTab = urlHash || savedTab || 'overview';
            
            const tabElement = document.querySelector(`[data-tab="${targetTab}"]`);
            if (tabElement) {
                const tab = new bootstrap.Tab(tabElement);
                tab.show();
            }
            
            document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tabEl => {
                tabEl.addEventListener('shown.bs.tab', function(event) {
                    const activeTab = event.target.getAttribute('data-tab');
                    localStorage.setItem('activeClassTab', activeTab);
                    history.replaceState(null, null, '#' + activeTab);
                });
            });
        }

        function manageStudents() {
            const manageTab = document.querySelector('[data-tab="manage-students"]');
            if (manageTab) {
                const tab = new bootstrap.Tab(manageTab);
                tab.show();
            }
        }

        function viewSubjects() {
            const subjectsTab = document.querySelector('[data-tab="subjects"]');
            if (subjectsTab) {
                const tab = new bootstrap.Tab(subjectsTab);
                tab.show();
            }
        }

        function printClassProfile() {
            window.print();
        }

        $(document).ready(function() {
            initTabPersistence();
            window.addEventListener('popstate', function(event) {
                const hash = window.location.hash.replace('#', '');
                if (hash) {
                    const tabElement = document.querySelector(`[data-tab="${hash}"]`);
                    if (tabElement) {
                        const tab = new bootstrap.Tab(tabElement);
                        tab.show();
                    }
                }
            });
        });
    </script>
@endsection