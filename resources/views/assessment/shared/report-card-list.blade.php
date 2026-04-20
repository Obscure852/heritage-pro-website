<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $klass->name ? $klass->name.' Report Cards' : 'Report Cards' }}</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
            /* Styles for screen */
        body {
            color: #333;
        }

        row {
            margin: 0; /* Reset the negative margins of Bootstrap rows */
        }

        .row > div > p {
            margin: 5px 0; /* Reduce the top and bottom margins of the paragraphs */
        }

        /* Styles for print */
        @media print {
            /* Hide unnecessary elements */
            [name='printer'], [name='file-pdf'] {
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
            body, table, tr, td, th {
                background-color: transparent !important;
            }

            /* Optimize table borders and widths */
            table {
                width: 100%;
                max-width: 100%;
                border-collapse: collapse;
                page-break-inside: avoid;
            }

            table, th, td {
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
        width: 100%; /* Make them responsive */
        box-sizing: border-box; /* Ensure padding and border are included in width */
        border: 1px solid #333; /* Add a light border */
        padding: 5px; /* Add some padding */
        margin: 10px 0; /* Add vertical margin */
    }
    </style>
</head>
<body>
    <div class="row">
        <div class="col-md-10"></div>
        <div class="col-md-2 d-flex justify-content-end">
            <box-icon id="printButton" style="cursor: pointer;" class="my-2 text-muted" size="sm" color="gray" name='printer'></box-icon>
            <box-icon class="my-2 text-muted" size="sm" color="gray" name='file-pdf' type='solid' ></box-icon>
        </div>
    </div>
    <div class="container">
        <div class="report-card">
        @if (!empty($klass))
            @foreach ($klass->students as $student)
                @php
                    $scoreAvg = 0;
                    $out_of = 0;
                @endphp
                <div class="invoice-title mt-4">
                    <div class="d-flex align-items-start">
                        <div class="flex-grow-1">
                            <div class="mb-2">
                                <img src="assets/images/logo-sm.svg" alt="" height="24"><span
                                    class="logo-txt" style="font-size: 18px;"><strong>{{ $school_setup->school_name ?? 'Platinum Primary School' }}</strong></span>
                            </div>
                        </div>
                    </div>
                    <p style="font-size: 16px;" class="mb-1"><strong>Addres: </strong>{{ $school_setup->physical_address ?? 'GIFP,Gaborone,Botswana' }}</p>
                    <p style="font-size: 16px;" class="mb-1"><i class="mdi mdi-email align-middle me-1"></i> <strong>Email: </strong> {{ $school_setup->email_address ?? 'info@platinum.co.bw' }}</p>
                    <p style="font-size: 16px;"><i class="mdi mdi-phone align-middle me-1"></i><strong>Phone:</strong> {{ $school_setup->telephone ?? '3951299' }} <strong>Fax:</strong> {{ $school_setup->fax }}</p>
                </div>

                <div class="row">
                    <hr class="my-2">
                    <div class="col-md-4">
                      <p> <strong>Firstname:</strong> {{ $student->first_name }} </p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Lastname: </strong>{{ $student->last_name }}</p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Date: </strong>{{ date('Y-m-d') }}</p>
                    </div>
                </div>
               
                <div class="row">
                    <div class="col-md-4">
                      <p> <strong>Attendance:</strong> 5/75 </p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Class : </strong> {{ $student->class->name }}</p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Gender: </strong>{{ $student->gender }}</p>
                      </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                      <p> <strong>Position: 05</strong>  </p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>No. In Class: </strong>{{ $klass->students->count() }}</p>
                    </div>

                    <div class="col-md-4">
                        <p><strong>Class Average: </strong> 76.5% A</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                      <p> <strong>Term Start:</strong> 12/09/2023  </p>
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
            @if ($student->tests->isNotEmpty())
                @php
                    // Fetch unique subjects for which 'CA' tests or Exams have been taken
                    $uniqueSubjects = $student->tests->pluck('subject_id')->unique();
                @endphp

            @foreach ($uniqueSubjects as $subjectId)
                @php
                    $subjectName = $student->tests->firstWhere('subject_id', $subjectId)->subject->name;
                    $examTest = $student->tests->where('subject_id', $subjectId)->where('type', 'Exam')->first();

                    $scoreAvg += $examTest->pivot->score;
                    $out_of += $examTest->out_of;

                    $subjectComment = \App\Models\SubjectComment::where('student_id',$student->id)->where('subject_id',$subjectId)->first();
                    $examScore = $examTest ? $examTest->pivot->score : '-';
                    $examGrade = $examTest ? $examTest->pivot->grade : '-';
                @endphp
                <tr>
                    <td>
                        <p class="font-size-13"> {{ $subjectName }} </p>
                    </td>
                    <td>
                        {{ $examTest->out_of ?? '-' }}
                    </td>
                    <td>
                        <p class="font-size-13"> {{ $examScore }}</p>
                    </td>
                    <td>
                        @php
                            $percentage = $examTest->pivot->percentage ?? 0;
                            echo $percentage != 0 ? number_format($percentage,0).'%' : 0;
                        @endphp
                    </td>
                    <td>
                        <p class="font-size-13">{{ $examGrade }}</p>
                    </td>
                    <td>
                        <p class="font-size-13"> {{ $subjectComment->remarks ?? 'N/A' }} </p>
                    </td>
                </tr>
                
            @endforeach
            @endif
            <tr>
                <td>Grand Total: </td>
                <td>{{ intval($out_of) ?? 0 }}</td>
                <td>{{ intval($scoreAvg) ?? 0 }}</td>
                <td>
                    @php
                         echo  $scoreAvg > 0 ? number_format(intval($scoreAvg) / intval($out_of) * 100,1).'%' : 0;
                    @endphp
                </td>
                <td>
                   @if ($scoreAvg > 0)
                       @php
                           $avgP = number_format(intval($scoreAvg) / intval($out_of) * 100,1);
                           $grade = \App\Http\Controllers\AssessmentController::getOverallGrade($klass->grade->id,$avgP);
                           if($grade){
                                $gradeObject = $grade->toArray();
                                echo $gradeObject['grade'];
                           }
                       @endphp
                      
                   @endif
                </td>
                <td>Average</td>
            </tr>
            </tbody>
            </table>

            <div>
                <p>Class Teacher: .................................</p>
            </div>

            <div>
                <table class="table table-bordered">
                    <thead>
                        <th>Class Teacher's Remarks</th>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <textarea style="border:none;" disabled cols="129" rows="1"></textarea>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div>
                <p>Head Teacher: .................................</p>
            </div>

            <div class="mb-6">
                <table class="table table-bordered">
                    <thead>
                        <th>Head Teacher's Remarks</th>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <textarea style="border:none;" disabled cols="129" rows="1"></textarea>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="row mt-4">
                <div class="col-md-4 d-flex justify-content-start">
                    Class Teacher's signature: .............................
                </div>
                <div class="col-md-4 d-flex justify-content-center">
                    Parent's signature: .........................
                </div>
                <div class="col-md-4 d-flex justify-content-start">
                    Head Teacher's signature: ........................
                </div>
            </div>

            <div class="row mt-4">
                <div style="font-size: 12px;" class="col-md-6">
                    Visit our website: {{ $school_setup->website }}
                </div>
                <div style="font-size: 12px;" class="col-md-6 d-flex justify-content-end">
                    <p>{{ $school_setup->email_address }}</p>
                </div>
            </div>
            @endforeach
        @endif

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
