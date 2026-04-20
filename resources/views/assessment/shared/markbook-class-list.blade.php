<style>
    .table-responsive {
        overflow-x: auto;
    }

    #markbook-class {
        min-width: 1200px;
        table-layout: auto;
        border-collapse: collapse;
    }

    #markbook-class thead th {
        background: #f9fafb;
        border-bottom: 2px solid #e5e7eb;
        font-weight: 600;
        color: #374151;
        font-size: 13px;
        padding: 12px 10px;
        vertical-align: middle;
    }

    #markbook-class tbody td {
        padding: 10px;
        color: #4b5563;
        border-bottom: 1px solid #e5e7eb;
        vertical-align: middle;
        font-size: 14px;
    }

    #markbook-class tbody tr:hover {
        background-color: #f9fafb;
    }

    #markbook-class th,
    #markbook-class td {
        white-space: nowrap;
        min-width: 55px;
        z-index: 1;
    }

    #markbook-class th:nth-child(-n+5),
    #markbook-class td:nth-child(-n+5) {
        position: sticky;
        left: 0;
        background-color: #fff;
        z-index: 2;
    }

    #markbook-class tbody tr:hover td:nth-child(-n+5) {
        background-color: #f9fafb;
    }

    #markbook-class th:nth-child(1),
    #markbook-class td:nth-child(1) {
        left: -4px;
    }

    #markbook-class th:nth-child(2),
    #markbook-class td:nth-child(2) {
        left: 40px;
    }

    #markbook-class th:nth-child(3),
    #markbook-class td:nth-child(3) {
        left: 115px;
    }

    #markbook-class th:nth-child(4),
    #markbook-class td:nth-child(4) {
        left: 190px;
    }

    #markbook-class th:nth-child(5),
    #markbook-class td:nth-child(5) {
        left: 225px;
    }

    /* Student cell with avatar */
    .student-cell {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .student-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
    }

    .student-avatar-placeholder {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 12px;
        flex-shrink: 0;
    }

    .student-avatar-placeholder.male {
        background: #dbeafe;
        color: #1e40af;
    }

    .student-avatar-placeholder.female {
        background: #fce7f3;
        color: #be185d;
    }

    .gender-male {
        color: #007bff;
    }

    .gender-female {
        color: #e83e8c;
    }

    .score-cell {
        width: 50px;
        min-width: 45px;
        text-align: center;
    }

    .grade-cell {
        width: 35px;
        min-width: 35px;
        text-align: center;
    }

    .grade-cell input[type="text"],
    .grade-cell span {
        width: 100%;
        padding: 0;
        margin: 0;
        box-sizing: border-box;
    }

    #markbook-class input[type="text"] {
        width: 100%;
        min-width: 45px;
        font-size: 13px;
        padding: 6px 8px;
        height: auto;
        box-sizing: border-box;
        border: 1px solid #d1d5db;
        border-radius: 3px;
        transition: all 0.2s ease;
    }

    #markbook-class input[type="text"]:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
        outline: none;
    }

    input.invalid {
        border-color: #dc3545 !important;
        background-color: #fef2f2;
    }

    /* Spinner overlay */
    #spinner-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(248, 249, 250, 0.92);
        backdrop-filter: blur(3px);
        -webkit-backdrop-filter: blur(3px);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease-in-out;
        z-index: 9999;
    }

    #spinner-overlay.show {
        opacity: 1;
        visibility: visible;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    @keyframes pulse {
        0% {
            transform: scale(0.85);
            opacity: 0.7;
        }

        50% {
            transform: scale(1);
            opacity: 1;
        }

        100% {
            transform: scale(0.85);
            opacity: 0.7;
        }
    }

    @keyframes gradient {
        0% {
            background-position: 0% 50%;
        }

        50% {
            background-position: 100% 50%;
        }

        100% {
            background-position: 0% 50%;
        }
    }

    @keyframes dots {

        0%,
        20% {
            content: '.';
        }

        40% {
            content: '..';
        }

        60%,
        80% {
            content: '...';
        }

        100% {
            content: '.';
        }
    }

    .btn-loading .bx {
        animation: spin 0.8s linear infinite;
    }

    .spinner-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #ffffff, #f8f9fa, #ffffff);
        background-size: 200% 200%;
        animation: gradient 5s ease infinite;
        padding: 20px 20px;
        border-radius: 16px;
        box-shadow: 0 10px 25px rgba(81, 86, 190, 0.15), 0 5px 10px rgba(0, 0, 0, 0.05);
        border: 1px solid rgba(81, 86, 190, 0.1);
    }

    .spinner-text {
        position: relative;
        margin-top: 10px;
        color: #5156BE;
        font-weight: 400;
        letter-spacing: 0.5px;
        font-size: 0.8rem;
    }

    .spinner-text::after {
        content: '.';
        animation: dots 1.5s infinite steps(1);
    }

    .custom-spinner {
        display: inline-block;
        width: 1.5rem;
        height: 1.5rem;
        position: relative;
    }

    .custom-spinner::before,
    .custom-spinner::after {
        content: '';
        position: absolute;
        border-radius: 50%;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

    .custom-spinner::before {
        border: 4px solid rgba(81, 86, 190, 0.2);
    }

    .custom-spinner::after {
        border: 4px solid transparent;
        border-top-color: #5156BE;
        animation: spin 0.8s linear infinite;
    }

    /* Progress bar styling */
    #progressBar {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #progressBarText {
        position: absolute;
        width: 100%;
        text-align: center;
        color: white;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        font-size: 14px;
        line-height: 25px;
    }

    /* Minimized indicator hover effect */
    #minimizedProgressIndicator:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
    }

    /* Option card for recalculate modal */
    .option-card:hover {
        border-color: #5156BE !important;
        background: #f8f9fa !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(81, 86, 190, 0.15);
    }

    .option-card.selected {
        border-color: #5156BE !important;
        background: rgba(81, 86, 190, 0.05) !important;
    }

    /* Comment link styling */
    .comment-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .comment-link:hover {
        background: rgba(59, 130, 246, 0.1);
        transform: scale(1.1);
    }

    .comment-link:hover i {
        color: #3b82f6 !important;
    }
</style>

<div class="col-12">
    <div id="spinner-overlay">
        <div class="spinner-container">
            <div class="custom-spinner" role="status">
                <span class="visually-hidden">Saving...</span>
            </div>
            <div class="spinner-text">Saving changes</div>
        </div>
    </div>

    <div class="modal fade" id="recalculateModal" tabindex="-1" aria-labelledby="recalculateModalLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="recalculateModalLabel">
                        Recalculate Marks for Gradesss
                    </h5>
                    <button type="button" class="btn-close text-dark" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div
                        style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 6px; padding: 0.5rem; color: #856404;">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong> Note:</strong><span>This operation will recalculate all marks for Core Subjects or
                            Optional Subjects in the selected grade.</span>
                    </div>

                    <form id="recalculateForm" method="POST"
                        action="{{ route('assessment.recalculate-scores', $klass->klass->id) }}">
                        @csrf
                        <input type="hidden" name="subject_type" id="selectedSubjectType" value="">

                        <div class="row mt-2">
                            <div class="col-md-6">
                                <div class="option-card" data-value="klass_subjects"
                                    style="border: 2px solid #e9ecef; border-radius: 12px; padding: 1.5rem; margin-bottom: 1rem; cursor: pointer; transition: all 0.3s ease; background: #f8f9fa;">
                                    <div
                                        style="width: 48px; height: 48px; border-radius: 20%; display: flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 1rem; background: rgba(81, 86, 190, 0.1); color: #5156BE;">
                                        <i class="bx bx-book-content"></i>
                                    </div>
                                    <div
                                        style="font-weight: 600; font-size: 1.1rem; margin-bottom: 0.5rem; color: #2d3748;">
                                        Class Subjects</div>
                                    <div style="color: #6c757d; font-size: 0.8rem; line-height: 1.4;">
                                        Recalculate marks for regular class subjects assigned to this grade.
                                        This includes core subjects taught in regular class periods.
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="option-card" data-value="optional_subjects"
                                    style="border: 2px solid #e9ecef; border-radius: 12px; padding: 1.5rem; margin-bottom: 1rem; cursor: pointer; transition: all 0.3s ease; background: #f8f9fa;">
                                    <div
                                        style="width: 48px; height: 48px; border-radius: 20%; display: flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 1rem; background: rgba(40, 167, 69, 0.1); color: #28a745;">
                                        <i class="bx bx-book-open"></i>
                                    </div>
                                    <div
                                        style="font-weight: 600; font-size: 1.1rem; margin-bottom: 0.5rem; color: #2d3748;">
                                        Optional Subjects</div>
                                    <div style="color: #6c757d; font-size: 0.8rem; line-height: 1.4;">
                                        Recalculate marks for optional subjects that students can choose from.
                                        This includes elective courses and specialized subjects.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-sm btn-primary" id="proceedBtn" disabled>
                        <i class="bx bx-play me-1"></i>Proceed with Recalculation
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Modal -->
    <div class="modal fade" id="progressModal" tabindex="-1" aria-labelledby="progressModalLabel" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" id="progressModalLabel">
                        <i class="bx bx-loader-circle bx-spin me-2" id="progressSpinner"></i>Grade Recalculation
                    </h5>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="minimizeProgressBtn">
                        <i class="bx bx-minus"></i> Run in Background
                    </button>
                </div>
                <div class="modal-body pt-3">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small" id="progressMessage">Initializing...</span>
                            <span class="fw-bold text-primary" id="progressPercentage">0%</span>
                        </div>
                        <div class="progress" style="height: 25px; border-radius: 8px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                role="progressbar" id="progressBar" style="width: 0%;" aria-valuenow="0"
                                aria-valuemin="0" aria-valuemax="100">
                                <span id="progressBarText" class="fw-bold">0%</span>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info border-0 mb-0" style="background-color: #e7f3ff;">
                        <i class="bx bx-info-circle me-2"></i>
                        <small>The recalculation is running in the background. You can close this window and continue
                            working.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Minimized Progress Indicator -->
    <div id="minimizedProgressIndicator"
        style="display: none; position: fixed; bottom: 20px; right: 20px; z-index: 1050; background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); padding: 15px 20px; min-width: 300px; cursor: pointer;">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="d-flex align-items-center">
                <i class="bx bx-loader-circle bx-spin me-2 text-primary" style="font-size: 20px;"></i>
                <span class="fw-bold">Recalculating Grades</span>
            </div>
            <span class="badge bg-primary" id="minimizedPercentage">0%</span>
        </div>
        <div class="progress" style="height: 8px;">
            <div class="progress-bar bg-primary" id="minimizedProgressBar" style="width: 0%;"></div>
        </div>
        <small class="text-muted d-block mt-1" id="minimizedMessage">Initializing...</small>
    </div>



    @if (!empty($klass))
        <div style="background: #4e73df; border-radius: 3px; color: white; padding: 0.5rem; margin: 2px;"
            class="row">
            <div class="col-md-6">
                <p style="margin: 0; font-size: 0.85rem;" class="class-info-text">
                    ({{ $klass->klass->name ?? '' }}), Teacher: @if($klass->teacher){{ $klass->teacher->fullName }}@else N/A @endif
                    @if($klass->assistantTeacher)| Asst: {{ $klass->assistantTeacher->fullName }}@endif, Subject:
                    {{ $klass->subject->subject->name ?? '' }} ({{ $klass->klass->students->count() ?? '' }})</p>
            </div>
            <div class="col-md-6 d-flex justify-content-end">
                {{-- @if ($schoolType->type === 'Senior')
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="toggleExercises">
                        <label class="form-check-label" for="toggleExercises">Show Exercises</label>
                    </div>
                @endif --}}

                @can('view-system-admin')
                    <button type="button" class="btn btn-sm btn-link text-info text-decoration-none"
                        data-bs-toggle="modal" data-bs-target="#recalculateModal" id="recalculateBtn"
                        title="Recalculate Marks for This Grade">
                        <i class="fa-solid fa-person-digging text-white font-size-18"></i>
                    </button>
                @endcan
            </div>
        </div>

        <form method="POST" action="{{ route('assessment.update-marks') }}">
            @csrf
            <input name="term" type="hidden" value="{{ $klass->term->id }}">
            <input name="year" type="hidden" value="{{ $klass->term->year }}">
            <input name="subject" type="hidden" value="{{ $klass->subject->id }}">

            @if (Auth::user()->can('manage-academic') || !session('is_past_term'))
                <div class="row">
                    <div class="col-md-12 mb-2 d-flex justify-content-end">
                        <button style="margin-right: 6px;" type="submit"
                            class="btn btn-primary btn-sm waves-effect waves-light">
                            <i class="fas fa-save font-size-18 align-middle"></i>
                        </button>
                    </div>
                </div>
            @endif

            <div class="table-responsive">
                <table id="markbook-class" class="table align-middle">
                    @php
                        $weeklyTests = $klass->subject->tests
                            ->where('grade_id', $klass->grade_id)
                            ->where('type', 'Exercise')
                            ->sortBy('sequence');

                        $caTests = $klass->subject->tests
                            ->where('grade_id', $klass->grade_id)
                            ->where('type', 'CA')
                            ->sortBy('sequence');

                        $examTests = $klass->subject->tests
                            ->where('grade_id', $klass->grade_id)
                            ->where('type', 'Exam')
                            ->sortBy('sequence');
                    @endphp
                    <thead>
                        <tr>
                            <th style="width: 30px;text-align:left;" scope="col">#</th>
                            <th style="width: 80px;text-align:left;cursor:pointer" scope="col">
                                Firstname <i class="bx bx-sort"></i>
                            </th>
                            <th style="width: 80px;text-align:left;cursor:pointer" scope="col">
                                Lastname <i class="bx bx-sort"></i>
                            </th>
                            <th style="width: 30px;text-align:left;cursor:pointer" scope="col">
                                Gender <i class="bx bx-sort"></i>
                            </th>
                            <th style="width: 30px;text-align:left;cursor:pointer" scope="col">
                                Class <i class="bx bx-sort"></i>
                            </th>
                            {{-- @if ($schoolType->type === 'Senior' && $weeklyTests->isNotEmpty())
                                @foreach ($weeklyTests as $test)
                                    <th class="score-cell exercise-column"
                                        style="background-color: #03CED2FF;color:#fff;" scope="col"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $test->name }}">
                                        {{ strtoupper(substr($test->abbrev, 0, 3)) }}
                                    </th>
                                    <th colspan="2" class="exercise-column"
                                        style="background-color: #03CED2FF;color:#fff;text-align:center;"
                                        scope="col">Grade
                                    </th>
                                @endforeach
                                <th colspan="2" class="exercise-column"
                                    style="text-align: center;background-color: #03CED2FF;color:#fff;text-align:center;"scope="col">
                                    Avg
                                </th>
                            @endif --}}
                            @if ($caTests->isNotEmpty())
                                @foreach ($caTests as $test)
                                    <th class="score-cell" style="background-color: #5156BE;color:#fff;"
                                        scope="col" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="{{ $test->name }}">
                                        {{ strtoupper(substr($test->abbrev, 0, 3)) }}
                                    </th>
                                    <th colspan="2" style="background-color: #5156BE;color:#fff;text-align:center;"
                                        scope="col">
                                        Grade
                                    </th>
                                @endforeach
                                <th colspan="2" style="text-align: center;background-color: #5156BE;color:#fff;"
                                    scope="col">Avg</th>
                            @endif
                            @if ($examTests->isNotEmpty())
                                @foreach ($examTests as $test)
                                    <th class="score-cell" style="background-color: #EBBD15FF;color:#fff;"
                                        scope="col" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="{{ $test->name }}">
                                        {{ strtoupper(substr($test->abbrev, 0, 3)) }}
                                    </th>
                                    <th colspan="2"
                                        style="background-color: #EBBD15FF;color:#fff;text-align:center;"
                                        scope="col">
                                        Grade
                                    </th>
                                @endforeach
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $studentIds = $klass->klass
                                ->students()
                                ->orderBy('first_name', 'asc')
                                ->pluck('students.id')
                                ->toArray();
                        @endphp
                        @foreach ($klass->klass->students as $index => $student)
                            <tr>
                                <td>
                                    @php
                                        $hasComments = $student
                                            ->getSubjectComment($klass->term->id, $klass->subject->id)
                                            ->exists();
                                        $initials = strtoupper(
                                            substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1),
                                        );
                                        $genderClass = $student->gender == 'M' ? 'male' : 'female';
                                    @endphp
                                    <a class="comment-link"
                                        href="{{ route('assessment.core-subject-remarks', ['studentId' => $student->id, 'id' => $klass->id, 'studentIds' => implode(',', $studentIds), 'index' => $index]) }}">
                                        <i style="color:{{ $hasComments ? '#3b82f6' : '#9ca3af' }}; font-size:16px;"
                                            data-bs-toggle="tooltip" title="Comments" class="bx bxs-note"></i>
                                    </a>
                                </td>
                                <td>
                                    <div class="student-cell">
                                        @if ($student->photo_path)
                                            <img src="{{ URL::asset($student->photo_path) }}" alt="{{ $student->full_name }}" class="student-avatar">
                                        @else
                                            <div class="student-avatar-placeholder {{ $genderClass }}">{{ $initials }}</div>
                                        @endif
                                        <span>{{ $student->first_name }}</span>
                                    </div>
                                </td>
                                <td>{{ $student->last_name }}</td>
                                <td>
                                    @if ($student->gender == 'M')
                                        <span class="gender-male"><i class="bx bx-male-sign"></i> M</span>
                                    @else
                                        <span class="gender-female"><i class="bx bx-female-sign"></i> F</span>
                                    @endif
                                </td>
                                <td>{{ $student->currentClass()->name }}</td>
                                {{-- @if ($schoolType->type === 'Senior' && $weeklyTests->isNotEmpty())
                                    @foreach ($weeklyTests as $test)
                                        @php
                                            $studentTest = \App\Models\StudentTest::where('student_id', $student->id)
                                                ->where('test_id', $test->id)
                                                ->first();
                                            $score = $studentTest ? $studentTest->score : '';
                                            $grade = $studentTest ? $studentTest->grade : '';
                                            $percentage = $studentTest ? $studentTest->percentage : '';
                                        @endphp
                                        <td class="exercise-column">
                                            <input type="hidden"
                                                name="students[{{ $student->id }}][tests][{{ $test->id }}][out_of]"
                                                value="{{ $test->out_of }}">
                                            <input type="text" class="form-control form-control-sm"
                                                name="students[{{ $student->id }}][tests][{{ $test->id }}][score]"
                                                value="{{ $score }}" style="width: 40px;"
                                                placeholder="{{ $test->out_of }}">
                                        </td>
                                        <td class="grade-cell exercise-column">{{ $percentage }}%</td>
                                        <td class="grade-cell exercise-column">{{ $grade }}</td>
                                    @endforeach
                                    @php
                                        $weeklyTestWithAvg = \App\Models\StudentTest::where('student_id', $student->id)
                                            ->whereHas('test', function ($query) use ($klass) {
                                                $query
                                                    ->where('type', 'Exercise')
                                                    ->where('grade_subject_id', $klass->subject->id)
                                                    ->where('term_id', $klass->term->id)
                                                    ->where('year', $klass->term->year);
                                            })
                                            ->whereNotNull('avg_score')
                                            ->first();

                                        $weekly_average_score = $weeklyTestWithAvg ? $weeklyTestWithAvg->avg_score : '';
                                        $weekly_average_grade = $weeklyTestWithAvg ? $weeklyTestWithAvg->avg_grade : '';
                                    @endphp

                                    <td class="grade-cell exercise-column">{{ $weekly_average_score }}%</td>
                                    <td class="grade-cell exercise-column">{{ $weekly_average_grade }}</td>
                                @endif --}}
                                @if ($caTests->isNotEmpty())
                                    @foreach ($caTests as $test)
                                        @php
                                            $studentTest = \App\Models\StudentTest::where('student_id', $student->id)
                                                ->where('test_id', $test->id)
                                                ->first();
                                            $score = $studentTest ? $studentTest->score : '';
                                            $grade = $studentTest ? $studentTest->grade : '';
                                            $percentage = $studentTest ? $studentTest->percentage : '';
                                        @endphp
                                        <td>
                                            <input type="hidden"
                                                name="students[{{ $student->id }}][tests][{{ $test->id }}][out_of]"
                                                value="{{ $test->out_of }}">
                                            <input type="text" class="form-control form-control-sm score-input"
                                                name="students[{{ $student->id }}][tests][{{ $test->id }}][score]"
                                                data-row="{{ $index }}"
                                                data-col="{{ $loop->index }}"
                                                value="{{ $score }}" style="width: 50px;"
                                                placeholder="{{ $test->out_of }}">
                                        </td>
                                        <td class="grade-cell">{{ $percentage }}%</td>
                                        <td class="grade-cell">{{ $grade }}</td>
                                    @endforeach
                                    @php
                                        $caTestWithAvg = \App\Models\StudentTest::where('student_id', $student->id)
                                            ->whereHas('test', function ($query) use ($klass) {
                                                $query
                                                    ->where('type', 'CA')
                                                    ->where('grade_subject_id', $klass->subject->id)
                                                    ->where('term_id', $klass->term->id)
                                                    ->where('year', $klass->term->year);
                                            })
                                            ->whereNotNull('avg_score')
                                            ->first();

                                        $ca_average_score = $caTestWithAvg ? $caTestWithAvg->avg_score : '';
                                        $ca_average_grade = $caTestWithAvg ? $caTestWithAvg->avg_grade : '';
                                    @endphp
                                    <td class="grade-cell">{{ $ca_average_score }}%</td>
                                    <td class="grade-cell">{{ $ca_average_grade }}</td>
                                @endif
                                @if ($examTests->isNotEmpty())
                                    @foreach ($examTests as $examIndex => $test)
                                        @php
                                            $studentTest = \App\Models\StudentTest::where('student_id', $student->id)
                                                ->where('test_id', $test->id)
                                                ->first();
                                            $score = $studentTest ? $studentTest->score : '';
                                            $grade = $studentTest ? $studentTest->grade : '';
                                            $percentage = $studentTest ? $studentTest->percentage : '';
                                            $points = $studentTest ? $studentTest->points : '';
                                        @endphp
                                        <td>
                                            <input type="hidden"
                                                name="students[{{ $student->id }}][tests][{{ $test->id }}][out_of]"
                                                value="{{ $test->out_of }}">
                                            <input type="text" class="form-control form-control-sm score-input"
                                                name="students[{{ $student->id }}][tests][{{ $test->id }}][score]"
                                                data-row="{{ $index }}"
                                                data-col="{{ $caTests->count() + $examIndex }}"
                                                value="{{ $score }}" style="width: 50px;"
                                                placeholder="{{ $test->out_of }}">
                                        </td>
                                        <td class="grade-cell">{{ $percentage }}%</td>
                                        <td class="grade-cell">
                                            {{ $grade }}{{ !empty($grade) ? ' (' . $points . ' .pts)' : '' }}
                                        </td>
                                    @endforeach
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if (Auth::user()->can('manage-academic') || !session('is_past_term'))
                <div class="row">
                    <div class="col-md-12 mb-4 d-flex justify-content-end">
                        <button style="margin-right: 6px;" type="submit"
                            class="btn btn-primary btn-sm waves-effect waves-light">
                            <i class="fas fa-save font-size-18 align-middle"></i>
                        </button>
                    </div>
                </div>
            @endif
        </form>
    @endif
</div>

<script>
    $(document).ready(function() {
        const classInfoElement = document.querySelector('.class-info-text');
        if (classInfoElement) {
            const originalHTML = classInfoElement.innerHTML;
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = originalHTML;

            const fullText = tempDiv.textContent || tempDiv.innerText;

            classInfoElement.innerHTML = '<strong></strong>';
            const strongElement = classInfoElement.querySelector('strong');

            const typingSpeed = 12.5;
            let charIndex = 0;

            function typeWriter() {
                if (charIndex < fullText.length) {
                    strongElement.textContent += fullText.charAt(charIndex);
                    charIndex++;
                    setTimeout(typeWriter, typingSpeed);
                } else {
                    addBlinkingCursor();
                }
            }

            function addBlinkingCursor() {
                const cursor = document.createElement('span');
                cursor.className = 'typing-cursor';
                cursor.innerHTML = '_';
                cursor.style.animation = 'blink 1s infinite';
                strongElement.appendChild(cursor);

                const style = document.createElement('style');
                style.textContent = `
                        @keyframes blink {
                            0%, 100% { opacity: 1; }
                            50% { opacity: 0; }
                        }
                    `;
                document.head.appendChild(style);

                setTimeout(() => {
                    cursor.remove();
                }, 3000);
            }

            setTimeout(typeWriter, 500);
        }

        // Multi-column sort: [{col: index, dir: 1|-1}, ...]
        let sortColumns = [];

        // Vertical Tab navigation for score inputs - using event delegation for reliability
        (function() {
            function getGridDimensions() {
                const inputs = document.querySelectorAll('.score-input[data-row][data-col]');
                let maxRow = -1;
                let maxCol = -1;
                inputs.forEach(function(inp) {
                    const row = parseInt(inp.dataset.row, 10);
                    const col = parseInt(inp.dataset.col, 10);
                    if (!isNaN(row) && row > maxRow) maxRow = row;
                    if (!isNaN(col) && col > maxCol) maxCol = col;
                });
                return { totalRows: maxRow + 1, totalCols: maxCol + 1 };
            }

            document.addEventListener('keydown', function(e) {
                if (e.key !== 'Tab') return;

                var target = document.activeElement;
                if (!target || !target.classList.contains('score-input')) return;

                var currentRow = parseInt(target.dataset.row, 10);
                var currentCol = parseInt(target.dataset.col, 10);

                if (isNaN(currentRow) || isNaN(currentCol)) return;

                e.preventDefault();
                e.stopPropagation();

                var dims = getGridDimensions();
                var totalRows = dims.totalRows;
                var totalCols = dims.totalCols;

                var nextRow, nextCol;

                if (e.shiftKey) {
                    nextRow = currentRow - 1;
                    nextCol = currentCol;
                    if (nextRow < 0) {
                        nextRow = totalRows - 1;
                        nextCol = currentCol - 1;
                        if (nextCol < 0) nextCol = totalCols - 1;
                    }
                } else {
                    nextRow = currentRow + 1;
                    nextCol = currentCol;
                    if (nextRow >= totalRows) {
                        nextRow = 0;
                        nextCol = currentCol + 1;
                        if (nextCol >= totalCols) nextCol = 0;
                    }
                }

                var nextInput = document.querySelector(
                    '.score-input[data-row="' + nextRow + '"][data-col="' + nextCol + '"]'
                );
                if (nextInput) {
                    nextInput.focus();
                    nextInput.select();
                }
            }, true);
        })();

        // Multi-column sorting: Click to sort by one column, Shift+Click to add secondary/tertiary sort
        $('th:nth-child(2), th:nth-child(3), th:nth-child(4), th:nth-child(5)').css('cursor', 'pointer').click(
            function(e) {
                const columnIndex = $(this).index();
                const existing = sortColumns.findIndex(s => s.col === columnIndex);

                if (e.shiftKey) {
                    // Shift+Click: add or toggle this column in the sort stack
                    if (existing !== -1) {
                        sortColumns[existing].dir *= -1;
                    } else {
                        sortColumns.push({ col: columnIndex, dir: 1 });
                    }
                } else {
                    // Normal click: sort by this column only (or toggle direction if already sole sort)
                    if (sortColumns.length === 1 && sortColumns[0].col === columnIndex) {
                        sortColumns[0].dir *= -1;
                    } else {
                        sortColumns = [{ col: columnIndex, dir: 1 }];
                    }
                }

                // Update sort icons
                $('th:nth-child(2) .bx, th:nth-child(3) .bx, th:nth-child(4) .bx, th:nth-child(5) .bx')
                    .removeClass('bx-sort-up bx-sort-down').addClass('bx-sort');
                sortColumns.forEach(function(s, i) {
                    const $icon = $('th').eq(s.col).find('.bx');
                    $icon.removeClass('bx-sort').addClass(s.dir === 1 ? 'bx-sort-up' : 'bx-sort-down');
                });

                const rows = $('#markbook-class tbody tr').get();
                rows.sort(function(a, b) {
                    for (let i = 0; i < sortColumns.length; i++) {
                        const aValue = $(a).children('td').eq(sortColumns[i].col).text().trim();
                        const bValue = $(b).children('td').eq(sortColumns[i].col).text().trim();
                        const cmp = aValue.localeCompare(bValue) * sortColumns[i].dir;
                        if (cmp !== 0) return cmp;
                    }
                    return 0;
                });
                $('#markbook-class tbody').empty().append(rows);
            });

        setTimeout(function() {
            const modal = document.getElementById('recalculateModal');
            const optionCards = document.querySelectorAll('.option-card');
            const proceedBtn = document.getElementById('proceedBtn');
            const selectedSubjectType = document.getElementById('selectedSubjectType');
            const recalculateForm = document.getElementById('recalculateForm');

            console.log('Modal elements found:', {
                modal: !!modal,
                optionCards: optionCards.length,
                proceedBtn: !!proceedBtn,
                selectedSubjectType: !!selectedSubjectType,
                recalculateForm: !!recalculateForm
            });

            if (optionCards.length === 0 || !proceedBtn || !selectedSubjectType || !recalculateForm) {
                return;
            }

            optionCards.forEach((card, index) => {
                card.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    optionCards.forEach(c => c.classList.remove('selected'));
                    this.classList.add('selected');

                    const value = this.getAttribute('data-value');
                    selectedSubjectType.value = value;

                    proceedBtn.disabled = false;
                    proceedBtn.classList.remove('btn-secondary');
                    proceedBtn.classList.add('btn-primary');
                });
            });

            if (proceedBtn) {
                proceedBtn.addEventListener('click', function(e) {
                    e.preventDefault();

                    if (!selectedSubjectType.value) {
                        alert('Please select a subject type to recalculate.');
                        return;
                    }

                    const selectedOption = document.querySelector('.option-card.selected');
                    if (!selectedOption) {
                        alert('Please select a subject type to recalculate.');
                        return;
                    }

                    const titleElement = selectedOption.children[1];
                    const subjectTypeName = titleElement ? titleElement.textContent.trim() :
                        'selected subjects';
                    console.log('Subject type name:', subjectTypeName);
                    const confirmMessage =
                        `This will recalculate all marks for ${subjectTypeName.toLowerCase()} in this grade. This operation may take some time. Continue?`;
                    if (confirm(confirmMessage)) {
                        $('#spinner-overlay').addClass('show');
                        $('#recalculateModal').modal('hide');

                        $.ajax({
                            url: recalculateForm.action,
                            method: 'POST',
                            data: $(recalculateForm).serialize(),
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            success: function(response) {
                                $('#spinner-overlay').removeClass('show');

                                if (response.success) {
                                    // Show progress modal and start polling
                                    $('#progressModal').modal('show');
                                    startProgressPolling(selectedSubjectType.value);
                                }
                            },
                            error: function(xhr) {
                                $('#spinner-overlay').removeClass('show');

                                let errorMessage =
                                    'An error occurred. Please try again.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }
                                const alertHtml = `
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                                                    <i class="mdi mdi-block-helper label-icon"></i><strong>${errorMessage}</strong>
                                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                                </div>
                                            </div>
                                        </div>
                                    `;
                                $('.row').first().after(alertHtml);
                                $('html, body').animate({
                                    scrollTop: 0
                                }, 300);
                            }
                        });
                    }
                });
            }

            $('#recalculateModal').on('show.bs.modal', function() {
                console.log('Modal showing - resetting form');
                optionCards.forEach(card => card.classList.remove('selected'));
                selectedSubjectType.value = '';
                proceedBtn.disabled = true;
                proceedBtn.classList.remove('btn-primary');
                proceedBtn.classList.add('btn-secondary');
            });

            $('#recalculateModal').on('hidden.bs.modal', function() {
                console.log('Modal hidden - resetting form');
                optionCards.forEach(card => card.classList.remove('selected'));
                selectedSubjectType.value = '';
                proceedBtn.disabled = true;
                proceedBtn.classList.remove('btn-primary');
                proceedBtn.classList.add('btn-secondary');
            });

        }, 100);
    });

    $('form').on('submit', function() {
        $('#spinner-overlay').addClass('show');
        $(this).find('button[type="submit"]')
            .prop('disabled', true)
            .addClass('btn-loading');
    });

    $(window).on('load', function() {
        $('#spinner-overlay').removeClass('show');
    });

    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Progress polling functionality
    let progressInterval = null;
    let isMinimized = false;

    function startProgressPolling(subjectType) {
        const classId = '{{ $klass->klass->id ?? '' }}';

        if (!classId) {
            console.error('Class ID not found');
            return;
        }

        const progressUrl = '{{ route('assessment.recalculate-progress', ['id' => ':id']) }}'.replace(':id', classId);

        console.log('Starting progress polling:', {
            classId: classId,
            subjectType: subjectType,
            progressUrl: progressUrl
        });

        // Reset progress UI
        resetProgressUI();

        // Initial poll
        pollProgress(progressUrl, subjectType);

        // Poll every 2 seconds
        if (progressInterval) {
            clearInterval(progressInterval);
        }
        progressInterval = setInterval(function() {
            pollProgress(progressUrl, subjectType);
        }, 2000);
    }

    function resetProgressUI() {
        $('#progressPercentage').text('0%');
        $('#progressMessage').text('Initializing...');
        $('#progressBar').css('width', '0%').attr('aria-valuenow', 0);
        $('#progressBarText').text('0%');
        $('#progressBar').removeClass('bg-success bg-danger').addClass('bg-primary');
        $('#progressBar').addClass('progress-bar-animated');
        $('#progressSpinner').show();
    }

    function pollProgress(url, subjectType) {
        $.ajax({
            url: url,
            method: 'GET',
            data: {
                subject_type: subjectType
            },
            success: function(progress) {
                console.log('Progress received:', progress);
                updateProgressUI(progress);

                // Stop polling if completed or failed
                if (progress.status === 'completed' || progress.status === 'failed') {
                    clearInterval(progressInterval);
                    progressInterval = null;

                    setTimeout(function() {
                        handleCompletion(progress);
                    }, 2000);
                }
            },
            error: function(xhr) {
                console.error('Failed to fetch progress:', xhr);
                // Don't stop polling on error, might be temporary
            }
        });
    }

    function updateProgressUI(progress) {
        const percentage = progress.percentage || 0;
        const message = progress.message || 'Processing...';

        // Update modal
        $('#progressPercentage').text(percentage + '%');
        $('#progressMessage').text(message);
        $('#progressBar').css('width', percentage + '%').attr('aria-valuenow', percentage);
        $('#progressBarText').text(percentage + '%');

        // Update minimized indicator
        $('#minimizedPercentage').text(percentage + '%');
        $('#minimizedMessage').text(message);
        $('#minimizedProgressBar').css('width', percentage + '%');

        // Change progress bar color based on status
        if (progress.status === 'completed') {
            $('#progressBar').removeClass('bg-primary bg-danger').addClass('bg-success');
            $('#progressBar').removeClass('progress-bar-animated');
            $('#minimizedProgressBar').removeClass('bg-primary bg-danger').addClass('bg-success');
            $('#progressSpinner').removeClass('bx-spin').addClass('bx-check-circle');
            $('#minimizedProgressIndicator .bx-loader-circle').removeClass('bx-spin bx-loader-circle').addClass(
                'bx-check-circle');
        } else if (progress.status === 'failed') {
            $('#progressBar').removeClass('bg-primary bg-success').addClass('bg-danger');
            $('#progressBar').removeClass('progress-bar-animated');
            $('#minimizedProgressBar').removeClass('bg-primary bg-success').addClass('bg-danger');
            $('#progressSpinner').removeClass('bx-spin').addClass('bx-error-circle');
            $('#minimizedProgressIndicator .bx-loader-circle').removeClass('bx-spin bx-loader-circle').addClass(
                'bx-error-circle');
        }
    }

    function handleCompletion(progress) {
        if (progress.status === 'completed') {
            // Hide modals and indicators
            $('#progressModal').modal('hide');
            $('#minimizedProgressIndicator').fadeOut();
            isMinimized = false;

            // Show success message
            if ($('.alert-success').length) {
                $('.alert-success').remove();
            }

            const alertHtml = `
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                                <i class="mdi mdi-check-all label-icon"></i><strong>${progress.message}</strong>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                    </div>
                `;

            $('.row').first().after(alertHtml);
            $('html, body').animate({
                scrollTop: 0
            }, 300);

            // Reload the page after a short delay
            setTimeout(() => {
                console.log('Reloading page...');
                window.location.reload();
            }, 2000);

        } else if (progress.status === 'failed') {
            // Hide modals and indicators
            $('#progressModal').modal('hide');
            $('#minimizedProgressIndicator').fadeOut();
            isMinimized = false;

            // Show error message
            const alertHtml = `
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                                <i class="mdi mdi-block-helper label-icon"></i><strong>${progress.message}</strong>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                    </div>
                `;
            $('.row').first().after(alertHtml);
            $('html, body').animate({
                scrollTop: 0
            }, 300);
        }
    }

    // Minimize button handler
    $('#minimizeProgressBtn').on('click', function() {
        $('#progressModal').modal('hide');
        $('#minimizedProgressIndicator').fadeIn();
        isMinimized = true;
    });

    // Click minimized indicator to restore modal
    $('#minimizedProgressIndicator').on('click', function() {
        if (!isMinimized) return;
        $('#minimizedProgressIndicator').fadeOut();
        $('#progressModal').modal('show');
        isMinimized = false;
    });

    // Clean up on page unload
    $(window).on('beforeunload', function() {
        if (progressInterval) {
            // Don't prevent navigation, just inform
            return undefined;
        }
    });


</script>
