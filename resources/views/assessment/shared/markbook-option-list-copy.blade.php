<div class="col-md-11">
    @if (!empty($klass))
        <div style="background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%); border-radius: 3px; color: white; padding: 0.5rem; margin: 2px;"
            class="row">
            <div class="col-md-6">
                <p style="margin: auto 0;"><strong>
                        ({{ $klass->name ?? '' }}), Teacher: {{ $klass->teacher->fullName ?? '' }}, Subject:
                        {{ $klass->gradeSubject->subject->name ?? '' }} ({{ $klass->students->count() ?? '' }})
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
            <input name="subject" type="hidden" value="{{ $klass->gradeSubject->id }}">

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

            <table id="markbook-class" class="table table-sm rounded datatables table-bordered dt-responsive">
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
                        <th style="width: 50px;" scope="col">#</th>
                        <th scope="col">Firstname</th>
                        <th scope="col">Lastname</th>
                        <th style="width: 50px;" scope="col">Gender</th>
                        <th style="width: 80px; text-align:left;" scope="col">Class</th>
                        <!-- Weekly Tests Headers -->
                        @if ($schoolType->type === 'Senior' && $weeklyTests->isNotEmpty())
                            @foreach ($weeklyTests as $test)
                                <th style="width: 80px; text-align: left; background-color: #4CAF50; color: white;"
                                    scope="col" data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="{{ $test->name }}">
                                    {{ strtoupper(substr($test->abbrev, 0, 3)) }} ({{ $test->out_of }})
                                </th>
                                <th style="width: 30px; text-align: left;" scope="col">%</th>
                                <th style="width: 35px; text-align: left;" scope="col">Grade</th>
                            @endforeach
                            <!-- Add columns for Weekly Overall Average and Grade -->
                            <th colspan="2" style="text-align: center;">Weekly Overall</th>
                        @endif
                        <!-- CA Tests Headers -->
                        @if ($caTests->isNotEmpty())
                            @foreach ($caTests as $test)
                                <th style="width: 80px; text-align: left; background-color: #5156BE; color: white;"
                                    scope="col" data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="{{ $test->name }}">
                                    {{ strtoupper(substr($test->abbrev, 0, 3)) }} ({{ $test->out_of }})
                                </th>
                                <th style="width: 30px; text-align: center;" scope="col">%</th>
                                <th style="width: 35px; text-align: center;" scope="col">Grade</th>
                            @endforeach
                            <th colspan="2" style="text-align: center;">Overall</th>
                        @endif
                        <!-- Exam Tests Headers -->
                        @if ($examTests->isNotEmpty())
                            @foreach ($examTests as $test)
                                <th style="width: 80px; text-align: left; background-color: #D4F809;" scope="col"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $test->name }}">
                                    {{ strtoupper(substr($test->abbrev, 0, 3)) }} ({{ $test->out_of }})
                                </th>
                                <th style="width: 30px; text-align: center;" scope="col">%</th>
                                <th style="width: 35px; text-align: center;" scope="col">Grade</th>
                            @endforeach
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @php
                        $studentIds = $klass->students->pluck('id')->toArray();
                    @endphp
                    @foreach ($klass->students as $index => $student)
                        <tr>
                            <td>
                                @php
                                    $hasComments = $student
                                        ->getSubjectComment($klass->term->id, $klass->gradeSubject->id)
                                        ->exists();
                                @endphp
                                <a
                                    href="{{ route('assessment.optional-subject-remarks', ['studentId' => $student->id, 'id' => $klass->id, 'studentIds' => implode(',', $studentIds), 'index' => $index]) }}">
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
                            <!-- Weekly Tests Scores -->
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
                                    <td>{{ $percentage }}</td>
                                    <td>{{ $grade }}</td>
                                @endforeach
                                @php
                                    $studentWeeklyTests = \App\Models\StudentTest::where('student_id', $student->id)
                                        ->whereHas('test', function ($query) use ($klass) {
                                            $query
                                                ->where('type', 'Exercise')
                                                ->where('grade_subject_id', $klass->gradeSubject->id)
                                                ->where('term_id', $klass->term->id)
                                                ->where('year', $klass->term->year);
                                        })
                                        ->whereNotNull('score')
                                        ->get();

                                    if ($studentWeeklyTests->count() > 0) {
                                        $weekly_average_percentage = round($studentWeeklyTests->avg('percentage'));
                                        $weeklyAverageGradeObj = $klass->gradeSubject->getGradePerSubject(
                                            $weekly_average_percentage,
                                        );
                                        $weekly_average_score = $weekly_average_percentage;
                                        $weekly_average_grade = $weeklyAverageGradeObj
                                            ? $weeklyAverageGradeObj->grade
                                            : '';
                                    } else {
                                        $weekly_average_score = '';
                                        $weekly_average_grade = '';
                                    }
                                @endphp
                                <td style="width: 40px;">{{ $weekly_average_score }}</td>
                                <td style="width: 40px;">{{ $weekly_average_grade }}</td>
                            @endif
                            <!-- CA Tests Scores -->
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
                                    <td>{{ $percentage }}</td>
                                    <td>{{ $grade }}</td>
                                @endforeach
                                <!-- Retrieve Stored avg_score and avg_grade -->
                                @php
                                    $studentTestWithAvg = \App\Models\StudentTest::where('student_id', $student->id)
                                        ->whereHas('test', function ($query) use ($klass) {
                                            $query
                                                ->where('type', 'CA')
                                                ->where('grade_subject_id', $klass->gradeSubject->id)
                                                ->where('term_id', $klass->term->id)
                                                ->where('year', $klass->term->year);
                                        })
                                        ->whereNotNull('avg_score')
                                        ->first();

                                    $average_score = $studentTestWithAvg ? $studentTestWithAvg->avg_score : '';
                                    $average_grade = $studentTestWithAvg ? $studentTestWithAvg->avg_grade : '';
                                @endphp
                                <!-- Display Overall Average Score and Grade -->
                                <td style="width: 40px;">{{ $average_score }}</td>
                                <td style="width: 40px;">{{ $average_grade }}</td>
                            @endif
                            <!-- Exam Tests Scores -->
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
                                    <td>{{ $percentage }}</td>
                                    <td>{{ $grade }}</td>
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
