<style>
    .table-scroll-wrapper {
        position: relative;
        overflow-x: hidden;
    }

    .scroll-progress {
        height: 6px;
        background: #e0e7ff;
        border-radius: 999px;
        margin: 6px 0;
        overflow: hidden;
    }

    .scroll-progress-bar {
        height: 100%;
        width: 0%;
        background: linear-gradient(90deg, #5156BE 0%, #6366f1 100%);
        border-radius: inherit;
        transition: width 120ms ease-out;
    }

    .table-responsive {
        overflow-x: auto;
        overflow-y: visible;
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    .table-responsive::-webkit-scrollbar {
        display: none;
        height: 0;
        width: 0;
    }

    #markbook-optional-classes {
        min-width: 1200px;
        table-layout: auto;
        border-collapse: separate;
        border-spacing: 0;
    }

    #markbook-optional-classes thead th {
        background: #f9fafb;
        border-bottom: 2px solid #e5e7eb;
        font-weight: 600;
        color: #374151;
        font-size: 13px;
        padding: 12px 10px;
        vertical-align: middle;
    }

    #markbook-optional-classes tbody td {
        padding: 10px;
        color: #4b5563;
        border-bottom: 1px solid #e5e7eb;
        vertical-align: middle;
        font-size: 14px;
    }

    #markbook-optional-classes tbody tr:hover {
        background-color: #f9fafb;
    }

    #markbook-optional-classes th,
    #markbook-optional-classes td {
        white-space: nowrap;
        min-width: 55px;
        z-index: 1;
    }

    #markbook-optional-classes th:nth-child(-n+5),
    #markbook-optional-classes td:nth-child(-n+5) {
        position: sticky;
        left: 0;
        background-color: #fff;
        z-index: 2;
    }

    #markbook-optional-classes tbody tr:hover td:nth-child(-n+5) {
        background-color: #f9fafb;
    }

    #markbook-optional-classes th:nth-child(1),
    #markbook-optional-classes td:nth-child(1) {
        left: -4px;
    }

    #markbook-optional-classes th:nth-child(2),
    #markbook-optional-classes td:nth-child(2) {
        left: 40px;
    }

    #markbook-optional-classes th:nth-child(3),
    #markbook-optional-classes td:nth-child(3) {
        left: 115px;
    }

    #markbook-optional-classes th:nth-child(4),
    #markbook-optional-classes td:nth-child(4) {
        left: 190px;
    }

    #markbook-optional-classes th:nth-child(5),
    #markbook-optional-classes td:nth-child(5) {
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
        width: 80px;
        min-width: 75px;
        text-align: center;
        vertical-align: middle;
    }

    .grade-cell {
        width: 35px;
        min-width: 35px;
        text-align: center;
    }

    /* Sortable header styling */
    .sortable-header {
        cursor: pointer;
        user-select: none;
        transition: background-color 0.2s ease;
    }

    .sortable-header:hover {
        background-color: #e5e7eb !important;
    }

    .sort-icon {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        margin-left: 4px;
        font-size: 9px;
        line-height: 0.7;
        vertical-align: middle;
        color: #9ca3af;
    }

    .sort-icon .fa-caret-up,
    .sort-icon .fa-caret-down {
        display: block;
    }

    .sort-icon.asc .fa-caret-up {
        color: #3b82f6;
    }

    .sort-icon.desc .fa-caret-down {
        color: #3b82f6;
    }

    /* Test section styling - CA tests (vertical borders only) */
    #markbook-optional-classes thead th.ca-cell,
    #markbook-optional-classes thead th.exam-cell {
        font-size: 12px !important;
    }

    .ca-cell {
        border-left: 1px solid #d1d5db;
        vertical-align: middle;
        text-align: center;
    }

    .ca-cell-first {
        border-left: 1px solid #d1d5db;
    }

    .ca-cell-last {
        border-right: 1px solid #d1d5db;
    }

    /* CA header corners */
    th.ca-cell-first {
        border-top: 1px solid #d1d5db;
        border-top-left-radius: 3px !important;
        overflow: hidden;
        background-clip: padding-box;
    }

    th.ca-cell-last {
        border-top: 1px solid #d1d5db;
        border-top-right-radius: 3px !important;
        overflow: hidden;
        background-clip: padding-box;
    }

    th.ca-cell {
        border-top: 1px solid #d1d5db;
    }

    /* CA body last row corners */
    #markbook-optional-classes tbody tr:last-child td.ca-cell-first {
        border-bottom: 1px solid #d1d5db;
        border-bottom-left-radius: 3px;
    }

    #markbook-optional-classes tbody tr:last-child td.ca-cell-last {
        border-bottom: 1px solid #d1d5db;
        border-bottom-right-radius: 3px;
    }

    #markbook-optional-classes tbody tr:last-child td.ca-cell {
        border-bottom: 1px solid #d1d5db;
    }

    /* Test section styling - Exam tests (vertical borders only) */
    .exam-cell {
        border-left: 1px solid #d1d5db;
        vertical-align: middle;
        text-align: center;
    }

    .exam-cell-first {
        border-left: 1px solid #d1d5db;
    }

    .exam-cell-last {
        border-right: 1px solid #d1d5db;
    }

    /* Exam header corners */
    th.exam-cell-first {
        border-top: 1px solid #d1d5db;
        border-top-left-radius: 3px !important;
        overflow: hidden;
        background-clip: padding-box;
    }

    th.exam-cell-last {
        border-top: 1px solid #d1d5db;
        border-top-right-radius: 3px !important;
        overflow: hidden;
        background-clip: padding-box;
    }

    th.exam-cell {
        border-top: 1px solid #d1d5db;
    }

    /* Exam body last row corners */
    #markbook-optional-classes tbody tr:last-child td.exam-cell-first {
        border-bottom: 1px solid #d1d5db;
        border-bottom-left-radius: 3px;
    }

    #markbook-optional-classes tbody tr:last-child td.exam-cell-last {
        border-bottom: 1px solid #d1d5db;
        border-bottom-right-radius: 3px;
    }

    #markbook-optional-classes tbody tr:last-child td.exam-cell {
        border-bottom: 1px solid #d1d5db;
    }

    /* Spacing between test groups (CA and Exam) */
    .test-group-spacer {
        width: 2px !important;
        min-width: 2px !important;
        max-width: 2px !important;
        padding: 0 !important;
        background-color: #f9fafb !important;
        border: none !important;
    }

    thead th.test-group-spacer {
        background-color: #f9fafb !important;
    }

    .grade-cell input[type="text"],
    .grade-cell span {
        width: 100%;
        padding: 0;
        margin: 0;
        box-sizing: border-box;
    }

    #markbook-optional-classes input[type="text"] {
        width: 100%;
        min-width: 75px;
        font-size: 13px;
        padding: 6px 8px;
        height: auto;
        box-sizing: border-box;
        border: 1px solid #d1d5db;
        border-radius: 3px;
        transition: all 0.2s ease;
        text-align: center;
    }

    #markbook-optional-classes input[type="text"]::placeholder {
        text-align: center;
        color: #9ca3af;
    }

    #markbook-optional-classes input[type="text"]:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
        outline: none;
    }

    .weekly-header {
        background-color: #4CAF50;
    }

    .ca-header {
        background-color: #5156BE;
    }

    .exam-header {
        background-color: #FFEB3B;
    }

    @keyframes shake {

        0%,
        100% {
            transform: translateX(0);
        }

        10%,
        30%,
        50%,
        70%,
        90% {
            transform: translateX(-3px);
        }

        20%,
        40%,
        60%,
        80% {
            transform: translateX(3px);
        }
    }

    input.invalid {
        border-color: #b91c1c !important;
        background-color: #fca5a5 !important;
        animation: shake 0.5s ease-in-out;
    }

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

    .student-type-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 600;
        color: #fff;
        margin-left: 6px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
</style>

{{-- Threshold Settings Modal --}}
@include('components.threshold-settings-modal', ['thresholdSettings' => $thresholdSettings ?? null])

<div class="col-12">
    <!-- full-page spinner overlay -->
    <div id="spinner-overlay">
        <div class="spinner-container">
            <div class="custom-spinner" role="status">
                <span class="visually-hidden">Saving...</span>
            </div>
            <div class="spinner-text">Saving changes</div>
        </div>
    </div>
    @if (!empty($klass))
        <div style="background: #4e73df; border-radius: 3px; color: white; padding: 0.5rem; margin: 2px;"
            class="row">
            <div class="col-md-6">
                <p style="margin: 0; font-size: 0.85rem;" class="option-info-text"><strong>
                        ({{ $klass->name ?? '' }}) Teacher: @if($klass->teacher){{ $klass->teacher->fullName }}@else N/A @endif
                        @if($klass->assistantTeacher)| Asst: {{ $klass->assistantTeacher->fullName }}@endif Subject:
                        {{ $klass->gradeSubject->subject->name ?? '' }} ({{ $klass->students->count() ?? '' }})
                    </strong></p>
            </div>
            <div class="col-md-6 d-flex justify-content-end">
                {{-- Threshold Settings Button --}}
                <button type="button" class="btn btn-sm btn-link text-decoration-none" data-bs-toggle="modal"
                    data-bs-target="#thresholdSettingsModal" title="Passing Threshold Settings">
                    <i class="fa-solid fa-sliders text-white font-size-18"></i>
                </button>
                {{-- @if ($schoolType->type === 'Senior')
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" checked="false" id="toggleOptionalExercises">
                        <label class="form-check-label" for="toggleExercises">Show Exercises</label>
                    </div>
                @endif --}}
            </div>
        </div>
        <br>
        <form method="POST" action="{{ route('assessment.update-marks') }}">
            @csrf
            <input name="term" type="hidden" value="{{ $klass->term->id }}">
            <input name="year" type="hidden" value="{{ $klass->term->year }}">
            <input name="subject" type="hidden" value="{{ $klass->gradeSubject->id }}">
            <input name="scope_type" type="hidden" value="optional_subject">
            <input name="scope_id" type="hidden" value="{{ $klass->id }}">

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

            <div class="table-scroll-wrapper">
                <div class="scroll-progress scroll-progress-top">
                    <div class="scroll-progress-bar"></div>
                </div>
                <div class="table-responsive">
                    <table id="markbook-optional-classes" class="table align-middle">
                        @php
                            $weeklyTests = $klass->gradeSubject->tests
                                ->where('grade_id', $klass->grade_id)
                                ->where('type', 'Exercise')
                                ->sortBy('sequence');

                            $caTests = $klass->gradeSubject->tests
                                ->where('grade_id', $klass->grade_id)
                                ->where('type', 'CA')
                                ->sortBy('sequence');

                            $examTests = $klass->gradeSubject->tests
                                ->where('grade_id', $klass->grade_id)
                                ->where('type', 'Exam')
                                ->sortBy('sequence');
                        @endphp
                        <thead>
                            <tr>
                                <th style="width: 30px;text-align:left;" scope="col">#</th>
                                <th class="sortable-header" data-sort-col="1" style="width: 80px;text-align:left;"
                                    scope="col">
                                    Firstname
                                    <span class="sort-icon">
                                        <i class="fas fa-caret-up"></i>
                                        <i class="fas fa-caret-down"></i>
                                    </span>
                                </th>
                                <th class="sortable-header" data-sort-col="2" style="width: 80px;text-align:left;"
                                    scope="col">
                                    Lastname
                                    <span class="sort-icon">
                                        <i class="fas fa-caret-up"></i>
                                        <i class="fas fa-caret-down"></i>
                                    </span>
                                </th>
                                <th class="sortable-header" data-sort-col="3" style="width: 30px;text-align:left;"
                                    scope="col">
                                    Gender
                                    <span class="sort-icon">
                                        <i class="fas fa-caret-up"></i>
                                        <i class="fas fa-caret-down"></i>
                                    </span>
                                </th>
                                <th class="sortable-header" data-sort-col="4" style="width: 30px;text-align:left;"
                                    scope="col">
                                    Class
                                    <span class="sort-icon">
                                        <i class="fas fa-caret-up"></i>
                                        <i class="fas fa-caret-down"></i>
                                    </span>
                                </th>

                                {{-- @if ($schoolType->type === 'Senior' && $weeklyTests->isNotEmpty())
                                @foreach ($weeklyTests as $test)
                                    <th class="score-cell exercise-column"
                                        style="background-color: #03CED2FF;color:#fff;" scope="col"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $test->name }}">
                                        {{ strtoupper(substr($test->abbrev, 0, 3)) }}
                                    </th>
                                    <th colspan="2" class="exercise-column"
                                        style="background-color: #03CED2FF;color:#fff;" scope="col">Grade</th>
                                @endforeach
                                <th colspan="2" class="exercise-column"
                                    style="text-align: center;background-color: #03CED2FF;color:#fff;"scope="col">Avg
                                </th>
                            @endif --}}

                                @if ($caTests->isNotEmpty())
                                    @foreach ($caTests as $index => $test)
                                        <th class="score-cell ca-cell {{ $index === 0 ? 'ca-cell-first' : '' }}"
                                            style="background-color: #5156BE;color:#fff;" scope="col"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="{{ $test->name }}">
                                            {{ strtoupper(substr($test->abbrev, 0, 3)) }}
                                        </th>
                                        <th colspan="2" class="ca-cell" style="background-color: #5156BE;color:#fff;"
                                            scope="col">
                                            Grade</th>
                                    @endforeach
                                    <th colspan="2" style="background-color: #5156BE;text-align: center;color:#fff;"
                                        class="ca-cell ca-cell-last" scope="col">Avg</th>
                                @endif
                                @if ($caTests->isNotEmpty() && $examTests->isNotEmpty())
                                    <th class="test-group-spacer"></th>
                                @endif
                                @if ($examTests->isNotEmpty())
                                    @foreach ($examTests as $index => $test)
                                        <th class="score-cell exam-cell {{ $index === 0 ? 'exam-cell-first' : '' }}"
                                            style="background-color: #EBBD15FF;color:#fff;" scope="col"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="{{ $test->name }}">
                                            {{ strtoupper(substr($test->abbrev, 0, 3)) }}
                                        </th>
                                        <th colspan="2" class="exam-cell {{ $loop->last ? 'exam-cell-last' : '' }}"
                                            style="background-color: #EBBD15FF;color:#fff;text-align:center;"
                                            scope="col">
                                            Grade</th>
                                    @endforeach
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $studentIds = $klass->students->pluck('id')->toArray();
                                $rowIndex = 0;
                            @endphp
                            @foreach ($klass->students as $index => $student)
                                <tr
                                    style="{{ $student->type && $student->type->color ? 'border-left: 4px solid ' . $student->type->color . '; background-color: ' . $student->type->color . '10;' : '' }}">
                                    <td>
                                        @php
                                            $hasComments = $student
                                                ->getSubjectComment($klass->term->id, $klass->gradeSubject->id)
                                                ->exists();
                                            $initials = strtoupper(
                                                substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1),
                                            );
                                            $genderClass = $student->gender == 'M' ? 'male' : 'female';
                                        @endphp
                                        <a class="comment-link"
                                            href="{{ route('assessment.optional-subject-remarks', ['studentId' => $student->id, 'id' => $klass->id, 'studentIds' => implode(',', $studentIds), 'index' => $rowIndex, 'context' => $markbookCurrentContext]) }}">
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
                                            @if ($student->type)
                                                <span class="student-type-badge"
                                                    style="background-color: {{ $student->type->color ?? '#6c757d' }};">
                                                    {{ $student->type->type }}
                                                </span>
                                            @endif
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
                                    {{-- 
                                @if ($schoolType->type === 'Senior' && $weeklyTests->isNotEmpty())
                                    @foreach ($weeklyTests as $test)
                                        @php
                                            $studentTest = \App\Models\StudentTest::where('student_id', $student->id)
                                                ->where('test_id', $test->id)
                                                ->first();
                                            $score = $studentTest ? $studentTest->score : '';
                                            $grade = $studentTest ? $studentTest->grade : '';
                                            $percentage = $studentTest ? $studentTest->percentage : '';
                                        @endphp
                                        <td class="score-cell exercise-column">
                                            <input type="hidden"
                                                name="students[{{ $student->id }}][tests][{{ $test->id }}][out_of]"
                                                value="{{ $test->out_of }}">
                                            <input type="text" class="form-control form-control-sm"
                                                name="students[{{ $student->id }}][tests][{{ $test->id }}][score]"
                                                value="{{ $score }}" style="width: 100%;"
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
                                                    ->where('grade_subject_id', $klass->gradeSubject->id)
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
                                        @foreach ($caTests as $caIndex => $test)
                                            @php
                                                $studentTest = \App\Models\StudentTest::where(
                                                    'student_id',
                                                    $student->id,
                                                )
                                                    ->where('test_id', $test->id)
                                                    ->first();
                                                $score = $studentTest ? $studentTest->score : '';
                                                $grade = $studentTest ? $studentTest->grade : '';
                                                $percentage = $studentTest ? $studentTest->percentage : '';
                                            @endphp
                                            <td class="score-cell ca-cell {{ $loop->first ? 'ca-cell-first' : '' }}">
                                                <input type="hidden"
                                                    name="students[{{ $student->id }}][tests][{{ $test->id }}][out_of]"
                                                    value="{{ $test->out_of }}">
                                                <input type="text" class="form-control form-control-sm score-input"
                                                    name="students[{{ $student->id }}][tests][{{ $test->id }}][score]"
                                                    data-row="{{ $rowIndex }}" data-col="{{ $loop->index }}"
                                                    value="{{ $score }}" style="width: 100%;"
                                                    placeholder="{{ $test->out_of }}">
                                            </td>
                                            <td class="grade-cell ca-cell">{{ $percentage }}%</td>
                                            <td class="grade-cell ca-cell">{{ $grade }}</td>
                                        @endforeach
                                        @php
                                            $caTestWithAvg = \App\Models\StudentTest::where('student_id', $student->id)
                                                ->whereHas('test', function ($query) use ($klass) {
                                                    $query
                                                        ->where('type', 'CA')
                                                        ->where('grade_subject_id', $klass->gradeSubject->id)
                                                        ->where('term_id', $klass->term->id)
                                                        ->where('year', $klass->term->year);
                                                })
                                                ->whereNotNull('avg_score')
                                                ->first();

                                            $ca_average_score = $caTestWithAvg ? $caTestWithAvg->avg_score : '';
                                            $ca_average_grade = $caTestWithAvg ? $caTestWithAvg->avg_grade : '';
                                        @endphp
                                        <td class="grade-cell ca-cell">{{ $ca_average_score }}%</td>
                                        <td class="grade-cell ca-cell ca-cell-last">{{ $ca_average_grade }}</td>
                                    @endif
                                    @if ($caTests->isNotEmpty() && $examTests->isNotEmpty())
                                        <td class="test-group-spacer"></td>
                                    @endif
                                    @if ($examTests->isNotEmpty())
                                        @foreach ($examTests as $examIndex => $test)
                                            @php
                                                $studentTest = \App\Models\StudentTest::where(
                                                    'student_id',
                                                    $student->id,
                                                )
                                                    ->where('test_id', $test->id)
                                                    ->first();
                                                $score = $studentTest ? $studentTest->score : '';
                                                $grade = $studentTest ? $studentTest->grade : '';
                                                $percentage = $studentTest ? $studentTest->percentage : '';
                                                $points = $studentTest ? $studentTest->points : '';
                                            @endphp
                                            <td
                                                class="score-cell exam-cell {{ $loop->first ? 'exam-cell-first' : '' }}">
                                                <input type="hidden"
                                                    name="students[{{ $student->id }}][tests][{{ $test->id }}][out_of]"
                                                    value="{{ $test->out_of }}">
                                                <input type="text" class="form-control form-control-sm score-input"
                                                    name="students[{{ $student->id }}][tests][{{ $test->id }}][score]"
                                                    data-row="{{ $rowIndex }}"
                                                    data-col="{{ $caTests->count() + $loop->index }}"
                                                    value="{{ $score }}" style="width: 100%;"
                                                    placeholder="{{ $test->out_of }}">
                                            </td>
                                            <td class="grade-cell exam-cell">{{ $percentage }}%</td>
                                            <td
                                                class="grade-cell exam-cell {{ $loop->last ? 'exam-cell-last' : '' }}">
                                                {{ $grade }}{{ !empty($grade) ? ' (' . $points . ' .pts)' : '' }}
                                            </td>
                                        @endforeach
                                    @endif
                                </tr>
                                @php $rowIndex++; @endphp
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="scroll-progress scroll-progress-bottom">
                    <div class="scroll-progress-bar"></div>
                </div>
            </div>
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
        </form>
    @endif
</div>
<script>
    $('form').on('submit', function() {
        $('#spinner-overlay').addClass('show');
        $(this).find('button[type="submit"]')
            .prop('disabled', true)
            .addClass('btn-loading');
    });

    $(window).on('load', function() {
        $('#spinner-overlay').removeClass('show');
    });

    // Initialize scroll progress bar - generic, works with any/multiple wrappers
    (function() {
        function bindProgressForWrapper(wrapper) {
            var scroller = wrapper.querySelector('.table-responsive');
            var bars = wrapper.querySelectorAll('.scroll-progress-bar');
            if (!scroller || !bars.length) return;

            // Remove previous handler on this scroller (if any)
            if (scroller._progressHandler) {
                scroller.removeEventListener('scroll', scroller._progressHandler);
                window.removeEventListener('resize', scroller._progressHandler);
            }

            var handler = function() {
                var maxScroll = scroller.scrollWidth - scroller.clientWidth;
                var percent = maxScroll > 0 ? (scroller.scrollLeft / maxScroll) * 100 : 0;
                bars.forEach(function(bar) {
                    bar.style.width = percent + '%';
                });
            };

            scroller._progressHandler = handler;

            scroller.addEventListener('scroll', handler, {
                passive: true
            });
            window.addEventListener('resize', handler);

            handler();
        }

        window.initScrollProgress = function() {
            document.querySelectorAll('.table-scroll-wrapper').forEach(bindProgressForWrapper);
        };

        // Run now + after full load (covers late layout changes)
        window.initScrollProgress();
        window.addEventListener('load', window.initScrollProgress);
    })();

    $(document).ready(function() {

        const classInfoElement = document.querySelector('.option-info-text');

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

        let sortField = null;
        let sortDirection = 1;

        // Vertical Tab navigation for score inputs - remove any existing handler first to prevent duplicates
        if (window._markbookTabHandler) {
            document.removeEventListener('keydown', window._markbookTabHandler, true);
        }

        window._markbookTabHandler = function(e) {
            if (e.key !== 'Tab') return;

            var target = document.activeElement;
            if (!target || !target.classList.contains('score-input')) return;

            var currentRow = parseInt(target.dataset.row, 10);
            var currentCol = parseInt(target.dataset.col, 10);

            if (isNaN(currentRow) || isNaN(currentCol)) return;

            e.preventDefault();
            e.stopPropagation();

            // Calculate grid dimensions
            var inputs = document.querySelectorAll('.score-input[data-row][data-col]');
            var maxRow = -1;
            var maxCol = -1;
            inputs.forEach(function(inp) {
                var row = parseInt(inp.dataset.row, 10);
                var col = parseInt(inp.dataset.col, 10);
                if (!isNaN(row) && row > maxRow) maxRow = row;
                if (!isNaN(col) && col > maxCol) maxCol = col;
            });
            var totalRows = maxRow + 1;
            var totalCols = maxCol + 1;

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
        };

        document.addEventListener('keydown', window._markbookTabHandler, true);

        // Score input validation - numeric only with max value check
        const scoreInputs = document.querySelectorAll('input[name*="[score]"]');

        scoreInputs.forEach(function(input) {
            // Numeric-only validation on keypress
            input.addEventListener('keypress', function(e) {
                const allowedKeys = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '.'];
                if (!allowedKeys.includes(e.key) &&
                    e.key !== 'Backspace' &&
                    e.key !== 'Delete' &&
                    e.key !== 'Tab' &&
                    e.key !== 'ArrowLeft' &&
                    e.key !== 'ArrowRight') {
                    e.preventDefault();
                }
                // Prevent multiple decimal points
                if (e.key === '.' && this.value.includes('.')) {
                    e.preventDefault();
                }
            });

            // Validate on input (handles paste + max value check)
            input.addEventListener('input', function(e) {
                // Remove non-numeric characters except decimal
                let value = this.value.replace(/[^0-9.]/g, '');

                // Handle multiple decimals
                const parts = value.split('.');
                if (parts.length > 2) {
                    value = parts[0] + '.' + parts.slice(1).join('');
                }

                this.value = value;

                // Get out_of value from sibling hidden input
                const outOfInput = this.parentElement.querySelector('input[name*="[out_of]"]');
                const outOf = outOfInput ? parseFloat(outOfInput.value) : null;

                // Validate against out_of
                if (outOf !== null && value !== '') {
                    const numValue = parseFloat(value);
                    if (numValue > outOf) {
                        this.classList.add('invalid');
                        // Re-trigger animation
                        this.style.animation = 'none';
                        this.offsetHeight; // Trigger reflow
                        this.style.animation = '';
                    } else {
                        this.classList.remove('invalid');
                    }
                } else {
                    this.classList.remove('invalid');
                }
            });

            // Validate on blur for edge cases
            input.addEventListener('blur', function() {
                const outOfInput = this.parentElement.querySelector('input[name*="[out_of]"]');
                const outOf = outOfInput ? parseFloat(outOfInput.value) : null;

                if (outOf !== null && this.value !== '') {
                    const numValue = parseFloat(this.value);
                    if (numValue > outOf) {
                        this.classList.add('invalid');
                    } else {
                        this.classList.remove('invalid');
                    }
                }
            });
        });

        // Sortable headers with visual feedback
        $('.sortable-header').click(function() {
            const columnIndex = $(this).index();
            const $sortIcon = $(this).find('.sort-icon');

            // Reset all sort icons
            $('.sortable-header .sort-icon').removeClass('asc desc');

            if (sortField === columnIndex) {
                sortDirection *= -1;
            } else {
                sortField = columnIndex;
                sortDirection = 1;
            }

            // Update sort icon
            if (sortDirection === 1) {
                $sortIcon.addClass('asc');
            } else {
                $sortIcon.addClass('desc');
            }

            const rows = $('#markbook-optional-classes tbody tr').get();
            rows.sort(function(a, b) {
                const aValue = $(a).children('td').eq(columnIndex).text().trim();
                const bValue = $(b).children('td').eq(columnIndex).text().trim();
                return aValue.localeCompare(bValue) * sortDirection;
            });

            $('#markbook-optional-classes tbody').empty().append(rows);
        });
    });

    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
</script>
