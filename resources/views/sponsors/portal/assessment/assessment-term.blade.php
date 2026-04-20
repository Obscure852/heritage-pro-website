@include('sponsors.portal.partials.sponsor-portal-styles')

<div class="container-fluid px-0">
    {{-- $selectedTermId is now passed from controller --}}

    @forelse($children as $child)
        @php
            $childCATests = $child->tests->where('type', 'CA')->sortBy('sequence');
            $childExamTests = $child->tests->where('type', 'Exam')->sortBy('sequence');

            $hasCATests = $childCATests->isNotEmpty();
            $hasExamTests = $childExamTests->isNotEmpty();
            $hasAnyContent = $hasCATests || $hasExamTests;

            // Get initials for avatar
            $nameParts = explode(' ', $child->full_name);
            $initials = '';
            foreach (array_slice($nameParts, 0, 2) as $part) {
                $initials .= strtoupper(substr($part, 0, 1));
            }
        @endphp

        <div class="sponsor-container">
            <!-- Gradient Header with Child Profile -->
            <div class="sponsor-header">
                <div class="child-profile-header">
                    <div class="child-avatar">
                        @if($child->photo)
                            <img src="{{ asset('storage/' . $child->photo) }}" alt="{{ $child->full_name }}">
                        @else
                            {{ $initials }}
                        @endif
                    </div>
                    <div class="child-info">
                        <h5>{{ $child->full_name }}</h5>
                        <div class="child-class">
                            <i class="bx bx-buildings me-1"></i>
                            {{ $child->currentClass->name ?? 'No Class Assigned' }}
                        </div>
                    </div>
                </div>
                <i class="bx bx-bar-chart-alt-2 header-icon"></i>
            </div>

            <div class="sponsor-body">
                @if($hasAnyContent)
                    <!-- Tests/Exams Submenu Tabs -->
                    <ul class="nav sponsor-nav-tabs" id="assessmentTab-{{ $child->id }}" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $hasCATests ? 'active' : '' }}"
                                    id="tests-tab-{{ $child->id }}"
                                    data-bs-toggle="tab"
                                    data-bs-target="#tests-{{ $child->id }}"
                                    type="button"
                                    role="tab"
                                    aria-controls="tests-{{ $child->id }}"
                                    aria-selected="{{ $hasCATests ? 'true' : 'false' }}"
                                    {{ !$hasCATests ? 'disabled' : '' }}>
                                <i class="bx bx-edit-alt"></i>
                                Continuous Assessments
                                @if($hasCATests)
                                    <span class="badge bg-light text-primary ms-1">{{ $childCATests->groupBy('grade_subject_id')->count() }}</span>
                                @endif
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ !$hasCATests && $hasExamTests ? 'active' : '' }}"
                                    id="exams-tab-{{ $child->id }}"
                                    data-bs-toggle="tab"
                                    data-bs-target="#exams-{{ $child->id }}"
                                    type="button"
                                    role="tab"
                                    aria-controls="exams-{{ $child->id }}"
                                    aria-selected="{{ !$hasCATests && $hasExamTests ? 'true' : 'false' }}"
                                    {{ !$hasExamTests ? 'disabled' : '' }}>
                                <i class="bx bx-file"></i>
                                Examinations
                                @if($hasExamTests)
                                    <span class="badge bg-light text-primary ms-1">{{ $childExamTests->groupBy('grade_subject_id')->count() }}</span>
                                @endif
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="assessmentTabContent-{{ $child->id }}">
                        <!-- Tests (CA) Tab -->
                        <div class="tab-pane fade {{ $hasCATests ? 'show active' : '' }}"
                             id="tests-{{ $child->id }}"
                             role="tabpanel"
                             aria-labelledby="tests-tab-{{ $child->id }}">
                            @if($hasCATests)
                                @include('sponsors.portal.assessment.partials.assessment-content', [
                                    'child' => $child,
                                    'testType' => 'CA',
                                    'tests' => $childCATests,
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
                             id="exams-{{ $child->id }}"
                             role="tabpanel"
                             aria-labelledby="exams-tab-{{ $child->id }}">
                            @if($hasExamTests)
                                @php
                                    $termData = $childrenTermData[$child->id] ?? [];
                                    $reportDriver = $termData['driver'] ?? 'junior';
                                @endphp

                                {{-- School-type-specific exam tables --}}
                                @if($reportDriver === 'junior')
                                    @include('sponsors.portal.assessment.partials.exam-table-junior', [
                                        'child' => $child,
                                        'examTests' => $childExamTests,
                                        'termData' => $termData,
                                        'selectedTermId' => $selectedTermId
                                    ])
                                @elseif($reportDriver === 'senior')
                                    @include('sponsors.portal.assessment.partials.exam-table-senior', [
                                        'child' => $child,
                                        'examTests' => $childExamTests,
                                        'termData' => $termData,
                                        'selectedTermId' => $selectedTermId
                                    ])
                                @elseif($reportDriver === 'primary')
                                    @include('sponsors.portal.assessment.partials.exam-table-primary', [
                                        'child' => $child,
                                        'examTests' => $childExamTests,
                                        'termData' => $termData,
                                        'selectedTermId' => $selectedTermId
                                    ])
                                @endif

                                {{-- Report Card Preview Button --}}
                                <div class="mt-3 text-end">
                                    <button type="button" class="btn btn-outline-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#reportCardModal-{{ $child->id }}">
                                        <i class="bx bx-file-blank me-1"></i> View Report Card Preview
                                    </button>
                                </div>

                                {{-- Report Card Preview Modal --}}
                                @include('sponsors.portal.assessment.partials.report-card-preview', [
                                    'child' => $child,
                                    'examTests' => $childExamTests,
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

            <!-- Footer -->
            <div class="sponsor-footer">
                <i class="bx bx-calendar"></i>
                Term {{ $currentTerm->term }}, {{ $currentTerm->year }}
            </div>
        </div>
    @empty
        <div class="sponsor-container">
            <div class="sponsor-body">
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="bx bx-user-x"></i>
                    </div>
                    <h5>No Students Found</h5>
                    <p>No students are linked to your account for the selected term.</p>
                </div>
            </div>
        </div>
    @endforelse
</div>
