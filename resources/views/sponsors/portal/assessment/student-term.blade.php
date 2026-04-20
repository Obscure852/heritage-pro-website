@include('sponsors.portal.partials.sponsor-portal-styles')

@php
    $studentCATests = $student->tests->where('type', 'CA')->sortBy('sequence');
    $studentExamTests = $student->tests->where('type', 'Exam')->sortBy('sequence');
    $reportDriver = $termData['driver'] ?? 'junior';

    $hasCATests = $studentCATests->isNotEmpty();
    $hasExamTests = $studentExamTests->isNotEmpty();
    $hasAnyContent = $hasCATests || $hasExamTests;
@endphp

<div class="container-fluid px-0">
    @if($hasAnyContent)
        <!-- Tests/Exams Submenu Tabs -->
        <ul class="nav sponsor-nav-tabs" id="assessmentTab-{{ $student->id }}" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $hasCATests ? 'active' : '' }}"
                        id="tests-tab-{{ $student->id }}"
                        data-bs-toggle="tab"
                        data-bs-target="#tests-{{ $student->id }}"
                        type="button"
                        role="tab"
                        aria-controls="tests-{{ $student->id }}"
                        aria-selected="{{ $hasCATests ? 'true' : 'false' }}"
                        {{ !$hasCATests ? 'disabled' : '' }}>
                    <i class="bx bx-edit-alt"></i>
                    Continuous Assessments
                    @if($hasCATests)
                        <span class="badge bg-light text-primary ms-1">{{ $studentCATests->groupBy('grade_subject_id')->count() }}</span>
                    @endif
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ !$hasCATests && $hasExamTests ? 'active' : '' }}"
                        id="exams-tab-{{ $student->id }}"
                        data-bs-toggle="tab"
                        data-bs-target="#exams-{{ $student->id }}"
                        type="button"
                        role="tab"
                        aria-controls="exams-{{ $student->id }}"
                        aria-selected="{{ !$hasCATests && $hasExamTests ? 'true' : 'false' }}"
                        {{ !$hasExamTests ? 'disabled' : '' }}>
                    <i class="bx bx-file"></i>
                    Examinations
                    @if($hasExamTests)
                        <span class="badge bg-light text-primary ms-1">{{ $studentExamTests->groupBy('grade_subject_id')->count() }}</span>
                    @endif
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="assessmentTabContent-{{ $student->id }}">
            <!-- Tests (CA) Tab -->
            <div class="tab-pane fade {{ $hasCATests ? 'show active' : '' }}"
                 id="tests-{{ $student->id }}"
                 role="tabpanel"
                 aria-labelledby="tests-tab-{{ $student->id }}">
                @if($hasCATests)
                    @include('sponsors.portal.assessment.partials.assessment-content', [
                        'child' => $student,
                        'testType' => 'CA',
                        'tests' => $studentCATests,
                        'currentTerm' => $currentTerm,
                        'selectedTermId' => $selectedTermId
                    ])
                @else
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="bx bx-edit-alt"></i>
                        </div>
                        <h5>No Tests Available</h5>
                        <p>There are no continuous assessments recorded for this term yet.</p>
                    </div>
                @endif
            </div>

            <!-- Exams Tab -->
            <div class="tab-pane fade {{ !$hasCATests && $hasExamTests ? 'show active' : '' }}"
                 id="exams-{{ $student->id }}"
                 role="tabpanel"
                 aria-labelledby="exams-tab-{{ $student->id }}">
                @if($hasExamTests)
                    {{-- School-type-specific exam tables --}}
                    @if($reportDriver === 'junior')
                        @include('sponsors.portal.assessment.partials.exam-table-junior', [
                            'child' => $student,
                            'examTests' => $studentExamTests,
                            'termData' => $termData,
                            'selectedTermId' => $selectedTermId
                        ])
                    @elseif($reportDriver === 'senior')
                        @include('sponsors.portal.assessment.partials.exam-table-senior', [
                            'child' => $student,
                            'examTests' => $studentExamTests,
                            'termData' => $termData,
                            'selectedTermId' => $selectedTermId
                        ])
                    @elseif($reportDriver === 'primary')
                        @include('sponsors.portal.assessment.partials.exam-table-primary', [
                            'child' => $student,
                            'examTests' => $studentExamTests,
                            'termData' => $termData,
                            'selectedTermId' => $selectedTermId
                        ])
                    @endif

                    {{-- Report Card Preview Button --}}
                    <div class="mt-3 text-end">
                        <button type="button" class="btn btn-outline-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#reportCardModal-{{ $student->id }}">
                            <i class="bx bx-file-blank me-1"></i> View Report Card Preview
                        </button>
                    </div>

                    {{-- Report Card Preview Modal --}}
                    @include('sponsors.portal.assessment.partials.report-card-preview', [
                        'child' => $student,
                        'examTests' => $studentExamTests,
                        'termData' => $termData,
                        'school_data' => $school_data,
                        'currentTerm' => $currentTerm,
                        'selectedTermId' => $selectedTermId
                    ])
                @else
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="bx bx-file"></i>
                        </div>
                        <h5>No Exams Available</h5>
                        <p>There are no examinations recorded for this term yet.</p>
                    </div>
                @endif
            </div>
        </div>
    @else
        <!-- No Content State -->
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="bx bx-info-circle"></i>
            </div>
            <h5>No Assessment Data</h5>
            <p>There are no tests, exams, or assessment records for this term.</p>
        </div>
    @endif
</div>
