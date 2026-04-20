<style>
    #markbook-class {
        font-size: 0.75rem;
        /* Reduce overall table font size */
    }

    #markbook-class input[type="text"] {
        font-size: 0.7rem;
        /* Reduce input font size */
        padding: 0.1rem 0.2rem;
        /* Reduce input padding */
        height: auto;
        /* Allow height to adjust based on font size and padding */
    }

    #markbook-class th {
        font-size: 0.75rem;
        /* Reduce header font size */
        padding: 0.3rem 0.2rem;
        /* Reduce header padding */
        text-align: center;
    }

    #markbook-class td {
        padding: 0.3rem 0.2rem;
        /* Reduce cell padding */
    }

    .score-cell {
        width: 30px;
        text-align: center;
    }

    .grade-cell {
        width: 25px;
        text-align: center;
    }
</style>
<div class="col-12">
    @if (!empty($klass))
        <div style="background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%); border-radius: 3px; color: white; padding: 0.5rem; margin: 2px;"
            class="row">
            <div class="col-md-6">
                <p style="margin: auto 0;"><strong>
                        ({{ $klass->klass->name ?? '' }}), Teacher: {{ $klass->teacher->fullName ?? '' }}, Subject:
                        {{ $klass->subject->subject->name ?? '' }} ({{ $klass->klass->students->count() ?? '' }})
                    </strong></p>
            </div>
            <div class="col-md-6 d-flex justify-content-end">
            </div>
        </div>
        <br>
        <form method="POST" action="{{ route('assessment.update-marks') }}">
            @csrf
            <input name="term" type="hidden" value="{{ $klass->term->id }}">
            <input name="year" type="hidden" value="{{ $klass->term->year }}">
            <input name="subject" type="hidden" value="{{ $klass->subject->id }}">

            @can('manage-assessment')
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
            @endcan

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
                        <th style="width: 80px;text-align:left" scope="col">Firstname</th>
                        <th style="width: 80px;text-align:left" scope="col">Lastname</th>
                        <th style="width: 30px;text-align:left" scope="col">Gender</th>
                        <th style="width: 30px;text-align:left" scope="col">Class</th>
                        @if ($schoolType->type === 'Senior' && $weeklyTests->isNotEmpty())
                            @foreach ($weeklyTests as $test)
                                <th class="score-cell" style="background-color: #03CED2FF;color:#fff;" scope="col"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $test->name }}">
                                    {{ strtoupper(substr($test->abbrev, 0, 3)) }}
                                </th>
                                <th colspan="2" style="background-color: #03CED2FF;color:#fff;" scope="col">Grade
                                </th>
                            @endforeach
                            <th colspan="2"
                                style="text-align: center;background-color: #03CED2FF;color:#fff;"scope="col">Avg
                            </th>
                        @endif
                        @if ($caTests->isNotEmpty())
                            @foreach ($caTests as $test)
                                <th class="score-cell" style="background-color: #5156BE;color:#fff;" scope="col"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $test->name }}">
                                    {{ strtoupper(substr($test->abbrev, 0, 3)) }}
                                </th>
                                <th colspan="2" style="background-color: #5156BE;color:#fff;" scope="col">Grade
                                </th>
                            @endforeach
                            <th colspan="2" style="text-align: center;background-color: #5156BE;color:#fff;"
                                scope="col">Avg</th>
                        @endif
                        @if ($examTests->isNotEmpty())
                            @foreach ($examTests as $test)
                                <th class="score-cell" style="background-color: #EBBD15FF;color:#fff;" scope="col"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $test->name }}">
                                    {{ strtoupper(substr($test->abbrev, 0, 3)) }}
                                </th>
                                <th colspan="2" style="background-color: #EBBD15FF;color:#fff;" scope="col">Grade
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
                                    <i style="color:{{ $hasComments ? '#5156BE' : 'rgb(7, 7, 7)' }}; font-size:18px;"
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
                                <td class="grade-cell">{{ $weekly_average_score }}%</td>
                                <td class="grade-cell">{{ $weekly_average_grade }}</td>
                            @endif
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
            @can('manage-assessment')
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
            @endcan
        </form>
    @endif
</div>
<script>
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
</script>
