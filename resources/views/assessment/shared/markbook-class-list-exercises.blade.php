<style>
    .table-responsive {
        overflow-x: auto;
        position: relative;
        z-index: 1;
    }

    #markbook-class {
        min-width: 1200px;
        table-layout: auto;
        border-collapse: collapse;
        font-size: 0.75rem;
        position: relative;
    }


    #markbook-class th,
    #markbook-class td {
        white-space: nowrap;
        min-width: 55px;
        vertical-align: middle;
        padding: 0.3rem 0.2rem;
    }

    #markbook-class th:nth-child(-n+5),
    #markbook-class td:nth-child(-n+5) {
        position: sticky;
        background-color: #fff;
        z-index: 2;
    }

    #markbook-class th:nth-child(1),
    #markbook-class td:nth-child(1) {
        left: -4px;
        width: 30px;
    }

    #markbook-class th:nth-child(2),
    #markbook-class td:nth-child(2) {
        left: 45px;
        width: 80px;
    }

    #markbook-class th:nth-child(3),
    #markbook-class td:nth-child(3) {
        left: 108px;
        width: 80px;
    }

    #markbook-class th:nth-child(4),
    #markbook-class td:nth-child(4) {
        left: 173px;
        width: 30px;
    }

    .dataTables_wrapper .dataTables_scroll {
        overflow: visible !important;
    }

    .dataTables_wrapper .dataTables_scrollBody {
        overflow: visible !important;
        overflow-x: auto !important;
    }

    #markbook-class th:nth-child(-n+5),
    #markbook-class td:nth-child(-n+5) {
        position: sticky;
        background-color: #fff;
        z-index: 10;
    }

    .dataTables_filter {
        margin-bottom: 15px;
        text-align: right;
    }

    .dataTables_filter input {
        width: 300px !important;
        margin-left: 10px;
    }

    .dataTables_paginate {
        margin-top: 15px;
        padding-top: 10px;
        border-top: 1px solid #dee2e6;
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

    #markbook-class input[type="text"] {
        width: 100%;
        min-width: 40px;
        font-size: 0.7rem;
        padding: 0.1rem 0.2rem;
        height: auto;
        box-sizing: border-box;
    }

    input.invalid {
        border-color: #dc3545;
    }

    .dataTables_filter {
        margin-bottom: 10px;
    }

    .dataTables_filter input {
        width: 300px !important;
    }

    .dataTables_wrapper {
        overflow: visible !important;
    }

    .dataTables_scrollBody {
        overflow: visible !important;
    }
</style>
<div class="col-12">
    @if (!empty($klass))
        <div style="background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%); border-radius: 3px; color: white; padding: 0.5rem; margin: 2px;"
            class="row">
            <div class="col-md-6">
                <p style="margin: 0; font-size: 0.85rem;"><strong>
                        ({{ $klass->klass->name ?? '' }}), Teacher: {{ $klass->teacher->fullName ?? '' }}, Subject:
                        {{ $klass->subject->subject->name ?? '' }} ({{ $klass->klass->students->count() ?? '' }})
                    </strong></p>
            </div>
            <div class="col-md-6 d-flex justify-content-end">
                {{-- @if ($schoolType->type === 'Senior')
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="toggleExercises">
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
            <input name="subject" type="hidden" value="{{ $klass->subject->id }}">

            @if (!session('is_past_term'))
                <div class="row">
                    <div class="col-md-12 mb-2 d-flex justify-content-end">
                        <button style="margin-right: 6px;" type="submit"
                            class="btn btn-primary btn-sm waves-effect waves-light">
                            <i class="bx bx-save font-size-14 align-middle"></i>
                        </button>
                    </div>
                </div>
            @endif

            <div class="table-responsive">
                <table id="markbook-class" class="table table-sm rounded table-striped table-bordered dt-responsive">
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
                                    <th class="score-cell" style="background-color: #5156BE;color:#fff;" scope="col"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $test->name }}">
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
                                    <th colspan="2" style="background-color: #EBBD15FF;color:#fff;text-align:center;"
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
                                    @endphp
                                    <a class="comment-link"
                                        href="{{ route('assessment.core-subject-remarks', ['studentId' => $student->id, 'id' => $klass->id, 'studentIds' => implode(',', $studentIds), 'index' => $index]) }}">
                                        <i style="color:{{ $hasComments ? '#5156BE' : 'rgb(7, 7, 7)' }}; font-size:16px;"
                                            data-bs-toggle="tooltip" title="Comments" class="bx bxs-note"></i>
                                    </a>
                                </td>
                                <td>{{ $student->first_name }}</td>
                                <td>{{ $student->last_name }}</td>
                                <td>
                                    @if ($student->gender == 'M')
                                        <span style="color: #007bff;"><i class="bx bx-male-sign"></i>
                                            {{ $student->gender }}</span>
                                    @else
                                        <span style="color: #e83e8c;"><i class="bx bx-female-sign"></i>
                                            {{ $student->gender }}</span>
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
                                            <input type="text" class="form-control form-control-sm"
                                                name="students[{{ $student->id }}][tests][{{ $test->id }}][score]"
                                                value="{{ $score }}" style="width: 40px;"
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
                                    @foreach ($examTests as $test)
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
                                            <input type="text" class="form-control form-control-sm"
                                                name="students[{{ $student->id }}][tests][{{ $test->id }}][score]"
                                                value="{{ $score }}" style="width: 40px;"
                                                placeholder="{{ $test->out_of }}">
                                        </td>
                                        <td class="grade-cell">{{ $percentage }}%</td>
                                        <td class="grade-cell">{{ $grade }}</td>
                                    @endforeach
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if (!session('is_past_term'))
                <div class="row">
                    <div class="col-md-12 mb-4 d-flex justify-content-end">
                        <button style="margin-right: 6px;" type="submit"
                            class="btn btn-primary btn-sm waves-effect waves-light">
                            <i class="bx bx-save font-size-16 align-middle"></i>
                        </button>
                    </div>
                </div>
            @endif
        </form>
    @endif
</div>
<script>
    $(document).ready(function() {
        let sortField = null;
        let sortDirection = 1;

        $('th:nth-child(2), th:nth-child(3), th:nth-child(4), th:nth-child(5)').css('cursor', 'pointer').click(
            function() {
                const columnIndex = $(this).index();

                if (sortField === columnIndex) {
                    sortDirection *= -1;
                } else {
                    sortField = columnIndex;
                    sortDirection = 1;
                }

                const rows = $('#markbook-class tbody tr').get();
                rows.sort(function(a, b) {
                    const aValue = $(a).children('td').eq(columnIndex).text().trim();
                    const bValue = $(b).children('td').eq(columnIndex).text().trim();
                    return aValue.localeCompare(bValue) * sortDirection;
                });
                $('#markbook-class tbody').empty().append(rows);
            });
    });

    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
</script>
