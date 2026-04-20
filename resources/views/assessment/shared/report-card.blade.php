<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Student Report Card</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            color: #333;
        }

        row {
            margin: 0;
            /* Reset the negative margins of Bootstrap rows */
        }

        .row>div>p {
            margin: 5px 0;
            /* Reduce the top and bottom margins of the paragraphs */
        }

        /* Styles for print */
        @media print {

            /* Hide unnecessary elements */
            [name='printer'],
            [name='file-pdf'] {
                display: none;
            }

            /* Optimize font sizes and margins */
            body {
                font-size: 12px;
            }

            .container {
                width: 90%;
                margin: 0;
                padding-left: 20mm;
                padding-right: 20mm;
            }

            /* Use grayscale colors */
            .text-muted {
                color: #333 !important;
            }

            /* Remove background colors */
            body,
            table,
            tr,
            td,
            th {
                background-color: transparent !important;
            }

            /* Optimize table borders and widths */
            table {
                width: 100%;
                max-width: 100%;
                border-collapse: collapse;
                page-break-inside: avoid;
            }

            table,
            th,
            td {
                border: 1px solid #333;
                padding: 3px 5px;
                vertical-align: top;
            }
        }

        .report-card {
            page-break-after: always;
            margin-top: 0mm;
            margin-bottom: 20mm;
        }

        .report-card:last-child {
            page-break-after: auto;
        }

        textarea {
            width: 100%;
            /* Make them responsive */
            box-sizing: border-box;
            /* Ensure padding and border are included in width */
            border: 1px solid #333;
            /* Add a light border */
            padding: 5px;
            /* Add some padding */
            margin: 10px 0;
            /* Add vertical margin */
        }
    </style>
</head>

<body>
    <div class="row">
        <div class="col-md-10"></div>
        <div class="col-md-2 d-flex justify-content-end">
            <box-icon id="printButton" style="cursor: pointer;" class="my-2 text-muted" size="sm" color="gray"
                name='printer'></box-icon>
            <box-icon class="my-2 text-muted" size="sm" color="gray" name='file-pdf' type='solid'></box-icon>
        </div>
    </div>
    <div class="container">
        <div class="report-card">
            @php
                $scoreAvg = 0;
                $out_of = 0;
            @endphp
            <div class="row">
                <hr class="my-2">
                <div class="col-md-4">
                    <p> <strong>Firstname:</strong> {{ $student->first_name ?? '' }} </p>
                </div>
                <div class="col-md-4">
                    <p><strong>Lastname: </strong>{{ $student->last_name ?? '' }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Date: </strong>{{ date('Y-m-d') }}</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <p> <strong>Absent Days:</strong> {{ $student->absentDaysCount() }} </p>
                </div>
                <div class="col-md-4">
                    <p><strong>Class : </strong> {{ $student->currentClass()->name }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Gender: </strong>{{ $student->gender }}</p>
                    <span>{{ session('is_past_term') }}</span>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <p> <strong>Position: 05</strong> </p>
                </div>
                <div class="col-md-4">
                    <p><strong>No. In Class: </strong>###</p>
                </div>

                <div class="col-md-4">
                    <p><strong>Class Average: </strong> 76.5% A</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <p> <strong>Term Start:</strong> 12/09/2023 </p>
                </div>
                <div class="col-md-4">
                    <p><strong>Term End: </strong> 03/12/2023</p>
                </div>
                <div class="col-md-4">
                    <p> <strong>School re-opens:</strong> 12/01/2024 </p>
                </div>
            </div>

            <table class="table table-bordered">
                <thead>
                    <th>Subject</th>
                    <th>Posible Marks</th>
                    <th>Actual Marks</th>
                    <th>%</th>
                    <th>Grades</th>
                    <th>Comments</th>
                </thead>
                <tbody>
                    @php
                        $selectedTermId = session('selected_term_id', App\Helpers\TermHelper::getCurrentTerm()->id);
                        $scoreAvg = 0;
                        $out_of = 0;
                        $uniqueSubjects = $student->tests->pluck('subject')->unique('id');
                    @endphp

                    @foreach ($uniqueSubjects as $subject)
                        @php
                            $examTest = $student->tests
                                ->where('term_id', $selectedTermId)
                                ->where('grade_subject_id', $subject->id)
                                ->where('type', 'Exam')
                                ->first();

                            if ($examTest) {
                                $scoreAvg += $examTest->pivot->score;
                                $out_of += $examTest->out_of;

                                $subjectComment = \App\Models\SubjectComment::where('student_id', $student->id)
                                    ->where('grade_subject_id', $subject->id)
                                    ->first();
                                $examScore = $examTest->pivot->score;
                                $examGrade = $examTest->pivot->grade;
                                $percentage = $examTest->pivot->percentage;
                            }
                        @endphp

                        @if ($examTest)
                            <tr>
                                <td>
                                    <p class="font-size-13">{{ $subject->subject->name }}</p>
                                </td>
                                <td>{{ $examTest->out_of }}</td>
                                <td>
                                    <p class="font-size-13">{{ $examScore }}</p>
                                </td>
                                <td>{{ $percentage ? number_format($percentage, 0) . '%' : '0%' }}</td>
                                <td>
                                    <p class="font-size-13">{{ $examGrade }}</p>
                                </td>
                                <td>
                                    <p class="font-size-13">{{ $subjectComment->remarks ?? 'N/A' }}</p>
                                </td>
                            </tr>
                        @endif
                    @endforeach

                    <tr>
                        <td>Grand Total:</td>
                        <td>{{ $out_of }}</td>
                        <td>{{ $scoreAvg }}</td>
                        <td>{{ $scoreAvg > 0 ? number_format(($scoreAvg / $out_of) * 100, 1) . '%' : '0%' }}</td>
                        <td colspan="2">
                            @php
                                $avgP = $scoreAvg > 0 ? number_format(($scoreAvg / $out_of) * 100, 1) : 0;
                                $grade = \App\Http\Controllers\AssessmentController::getOverallGrade(
                                    $student->currentClass()->grade->id,
                                    $avgP,
                                );
                            @endphp
                            {{ $grade ? $grade->grade : 'N/A' }}

                        </td>
                    </tr>
                </tbody>
            </table>
            <div>
                <p>Class Teacher: {{ $student->class->teacher->full_name ?? '.....................' }}</p>
            </div>
            <div>
                @php

                    $school_head = \App\Models\User::where('position', 'School Head')->first();
                    $currentTerm = \App\Helpers\TermHelper::getCurrentTerm();
                    $termId = $currentTerm ? $currentTerm->id : null;
                    $termYear = $currentTerm ? $currentTerm->year : null;
                    $classTeacherRemarks =
                        $student->overallComments->where('term_id', $termId)->where('year', $termYear)->first()
                            ->class_teacher_remarks ?? '';

                    $headTeachersRemarks =
                        $student->overallComments->where('term_id', $termId)->where('year', $termYear)->first()
                            ->school_head_remarks ?? '';
                @endphp
                <table class="table table-bordered">
                    <thead>
                        <th>Class Teacher's Remarks</th>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <textarea style="border:none;" disabled cols="129" rows="1">{{ $classTeacherRemarks }}</textarea>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div>
                <p>Head Teacher: {{ $school_head->full_name ?? '....................' }}</p>
            </div>

            <div class="mb-6">
                <table class="table table-bordered">
                    <thead>
                        <th>Head Teacher's Remarks</th>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <textarea style="border:none;" disabled cols="129" rows="1">{{ $headTeachersRemarks }}</textarea>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="row mt-4">
                <div class="col-md-4 d-flex justify-content-start">
                    <div class="form-group">
                        <label for="head_teacher_signature">Class Teacher's signature:</label>
                        @if (!empty($student->class->teacher->signature_path))
                            <img height="60px;" src="{{ URL::asset($student->class->teacher->signature_path) }}"
                                alt="{{ $student->class->teacher->full_name . 'signature' }}">
                        @else
                            <p>.....................</p>
                        @endif
                    </div>
                </div>
                <div class="col-md-4 d-flex justify-content-center">
                    Parent's signature: .........................
                </div>
                <div class="col-md-4 d-flex justify-content-start">
                    <div class="form-group">
                        <label for="head_teacher_signature">Head Teacher's signature:</label>
                        @if (!empty($school_head->signature_path))
                            <img height="60px;" src="{{ URL::asset($school_head->signature_path) }}"
                                alt="{{ $school_head->full_name . 'signature' }}">
                        @else
                            <p>.....................</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS (optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
    <script>
        document.getElementById('printButton').addEventListener('click', function() {
            window.print();
        });
    </script>
</body>

</html>
