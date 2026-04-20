{{-- External Exams Partial (PSLE/JCE) --}}
@if($examType === 'psle')
    <div class="help-text mb-4">
        <div class="help-title">Primary School Leaving Examination (PSLE)</div>
        <div class="help-content">
            View your child's PSLE results. These grades were achieved at the end of primary school education.
        </div>
    </div>

    @if($student->psle)
        {{-- Overall Grade Highlight --}}
        @php
            $overallGrade = $student->psle->overall_grade ?? null;
            $gradeBg = match(strtoupper($overallGrade ?? '')) {
                'MERIT' => 'purple',
                'A' => 'success',
                'B' => 'primary',
                'C' => 'info',
                'D' => 'warning',
                default => 'secondary',
            };
        @endphp
        <div class="d-flex align-items-center justify-content-center p-4 mb-4 bg-light rounded">
            <div class="text-center">
                <p class="text-muted mb-2">Overall Grade</p>
                <span class="badge bg-{{ $gradeBg }} fs-1 px-4 py-2">{{ $overallGrade ?? 'N/A' }}</span>
            </div>
        </div>

        {{-- Subject Grades Grid --}}
        <div class="row g-3">
            @php
                $subjects = [
                    'mathematics_grade' => 'Mathematics',
                    'english_grade' => 'English',
                    'science_grade' => 'Science',
                    'setswana_grade' => 'Setswana',
                    'agriculture_grade' => 'Agriculture',
                    'social_studies_grade' => 'Social Studies',
                ];
            @endphp
            @foreach($subjects as $key => $subject)
                @php
                    $grade = $student->psle->$key ?? null;
                    $subjectGradeBg = match(strtoupper($grade ?? '')) {
                        'A' => 'success',
                        'B' => 'primary',
                        'C' => 'info',
                        'D' => 'warning',
                        default => 'secondary',
                    };
                @endphp
                <div class="col-md-4 col-sm-6">
                    <div class="card border h-100">
                        <div class="card-body text-center p-3">
                            <p class="text-muted small mb-2">{{ $subject }}</p>
                            <span class="badge bg-{{ $subjectGradeBg }} fs-5">{{ $grade ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="bx bx-medal"></i>
            </div>
            <h5>No PSLE Results</h5>
            <p>PSLE results have not been recorded for this student.</p>
        </div>
    @endif

@elseif($examType === 'jce')
    <div class="help-text mb-4">
        <div class="help-title">Junior Certificate Examination (JCE)</div>
        <div class="help-content">
            View your child's JCE results. These grades were achieved at the end of junior secondary school education.
        </div>
    </div>

    @if($student->jce)
        {{-- Overall Grade Highlight --}}
        @php
            $overall = $student->jce->overall ?? null;
        @endphp
        <div class="d-flex align-items-center justify-content-center p-4 mb-4 bg-light rounded">
            <div class="text-center">
                <p class="text-muted mb-2">Overall Points</p>
                <span class="badge bg-primary fs-1 px-4 py-2">{{ $overall ?? 'N/A' }}</span>
            </div>
        </div>

        {{-- Subject Grades Grid --}}
        <div class="row g-3">
            @php
                $subjects = [
                    'mathematics' => 'Mathematics',
                    'english' => 'English',
                    'science' => 'Science',
                    'setswana' => 'Setswana',
                    'design_and_technology' => 'Design & Technology',
                    'home_economics' => 'Home Economics',
                    'agriculture' => 'Agriculture',
                    'social_studies' => 'Social Studies',
                    'moral_education' => 'Moral Education',
                    'music' => 'Music',
                    'physical_education' => 'Physical Education',
                    'art' => 'Art',
                    'office_procedures' => 'Office Procedures',
                    'accounting' => 'Accounting',
                    'french' => 'French',
                ];
            @endphp
            @foreach($subjects as $key => $subject)
                @php
                    $grade = $student->jce->$key ?? null;
                    if ($grade === null || $grade === '') continue;
                    $subjectGradeBg = match(strtoupper($grade ?? '')) {
                        'A' => 'success',
                        'B' => 'primary',
                        'C' => 'info',
                        'D' => 'warning',
                        default => 'secondary',
                    };
                @endphp
                <div class="col-md-3 col-sm-4 col-6">
                    <div class="card border h-100">
                        <div class="card-body text-center p-3">
                            <p class="text-muted small mb-2" style="font-size: 11px;">{{ $subject }}</p>
                            <span class="badge bg-{{ $subjectGradeBg }} fs-6">{{ $grade ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="bx bx-medal"></i>
            </div>
            <h5>No JCE Results</h5>
            <p>JCE results have not been recorded for this student.</p>
        </div>
    @endif
@endif
