@extends('layouts.master')
@section('title') Exam Analysis @endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1') <a href="{{ $gradebookBackUrl }}">Back</a> @endslot
        @slot('title') Analysis @endslot
    @endcomponent
    <style>
        .card{
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19); 
        }

        .report-card {
                margin-top: 0mm;
                margin-bottom: 20mm;
        }

        body{
            font-size: 10px;
        }

        textarea {
            width: 100%; 
            box-sizing: border-box; 
            border: 1px solid #333; 
            padding: 5px; 
            margin: 10px 0; 
        }
        @media print {

            body {
                width: 100%;
                margin: 0;
                padding: 0;
                font-size: 10px;
                line-height: normal;
            }
            
            body * {
                visibility: hidden;
            }
            .printable, .printable * {
                visibility: visible;
            }
            body * {
                visibility: hidden;
            }

            .printable, .printable * {
                visibility: visible;
            }

            .printable {
                position: absolute;
                left: 50%;
                top: 0;
                transform: translateX(-50%); 
                width: 350mm;
                height: 297mm;
                margin-left: 250px;
                margin-top: 80px;
                padding: 0;
                page-break-after: avoid;
            }


            .card-header {
                display: flex;
                justify-content: space-between; 
                align-items: center;
                padding: 0 10mm; 
            }

            .card-header img {
                width: 300px; 
                height: 120px; 
            }

            .table { 
                width: 100%;
                table-layout: fixed; 
            }

            .table th, .table td {
                width: auto; 
                overflow: visible; 
                word-wrap: break-word; 
            }
            
            textarea {
                border: none; 
            }

            .card{
                box-shadow: none;
            }
        }
    </style>
    <div class="row">
        <div class="col-md-12 col-lg-12 d-flex justify-content-end">
            <i onclick="alert(0)" style="font-size: 20px;margin-bottom:10px;cursor:pointer;margin-right:5px;" class="bx bx-sync"></i>
            <i onclick="printContent()" style="font-size: 20px;margin-bottom:10px;cursor:pointer;" class="bx bx-printer"></i>
        </div>
    </div>

    {{-- Print from here to the bottom only --}}
    <div class="row printable">
        <div class="col-md-12 col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6 col-lg-6 align-items-start">
                        <div style="font-size:12px;" class="form-group">
                        <strong>{{ $school_data->school_name }}</strong>
                        <br>
                        <span style="maring:0;padding:0;"> {{ $school_data->physical_address }}</span>
                        <br>
                        <span style="maring:0;padding:0;"> {{ $school_data->postal_address }}</span>
                        <br>
                        <span>Tel: {{ $school_data->telephone .' Fax: '. $school_data->fax }}</span>
                        </div>
                        </div>
                        <div  class="col-md-6 col-lg-6 d-flex justify-content-end">
                            <img height="80" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                        </div>
                    </div>
                    
                </div>
                <div class="card-body">
                    <div class="report-card">
                        <div class="row">
                            <h6>Student Exam Analysis Statistics</h6>
                            <div class="col-md-12 col-lg-12">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <td>#</td>
                                            <th>Name</th>
                                            <th>Class</th>
                                            <th>Sex</th>
                                            <th>PSLE</th>
                                            @foreach($allSubjects as $subject)
                                                <th style="width:40px;">{{ substr($subject, 0, 3) }}</th>
                                            @endforeach
                                            <th>TP</th>
                                            <th>OG</th>
                                            <th>Pos</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($reportCards as $index => $reportCard)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $reportCard['student']->fullName ?? '' }}</td>
                                                <td>{{ $reportCard['class_name'] ?? '' }}</td>
                                                <td>{{ $reportCard['student']->gender ?? '' }}</td>
                                                <td>{{ $reportCard['student']->psle->grade ?? '' }}</td>
                                                @foreach ($allSubjects as $subject)
                                                    @php
                                                         $subjectScore = isset($reportCard['scores'][$subject]['percentage']) ? round($reportCard['scores'][$subject]['percentage']) : '/';
                                                        $subjectGrade = $reportCard['scores'][$subject]['grade'] ?? '';
                                                    @endphp
                                                    <td>{{ $subjectScore }} {{ $subjectGrade }}</td>
                                                @endforeach
                                                
                                                <td>{{ $reportCard['totalPoints'] ?? '' }}</td>
                                                <td>{{ $reportCard['grade'] ?? '' }}</td>
                                                <td>{{ $reportCard['position'] ?? '' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="row">
                                    <h6>Overall Exam Grades Statistics</h6>
                                    @php
                                        $aCount = $gradeCounts['A']['M'] + $gradeCounts['A']['F'];
                                        $bCount = $gradeCounts['B']['M'] + $gradeCounts['B']['F'];

                                        $cCount = $gradeCounts['C']['M'] + $gradeCounts['C']['F'];
                                        $dCount = $gradeCounts['D']['M'] + $gradeCounts['D']['F'];

                                        $aPercentage = $totalStudents > 0 ? round(($aCount / $totalStudents)*100,2):0;
                                        $bPercentage = $totalStudents > 0 ? round(($bCount / $totalStudents)*100,2):0;
                                        $cPercentage = $totalStudents > 0 ? round(($cCount / $totalStudents)*100,2):0;
                                        $dPercentage = $totalStudents > 0 ? round(($dCount / $totalStudents)*100,2):0;
                                    @endphp
                                    <div class="col-md-12 col-lg-12">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th rowspan="2">Grade</th>
                                                    <th style="text-align:center;" colspan="2">A ({{ $aPercentage.'%' }})</th>
                                                    <th style="text-align:center;" colspan="2">B ({{ $bPercentage.'%' }})</th>
                                                    <th style="text-align:center;" colspan="2">C ({{ $cPercentage.'%' }})</th>
                                                    <th style="text-align:center;" colspan="2">D ({{ $dPercentage.'%' }})</th>
                                                    <th style="text-align:center;" rowspan="2">ABC%</th>
                                                    <th style="text-align:center;" rowspan="2">ABCD%</th>
                                                </tr>
                                                <tr>
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>M</th>
                                                    <th>F</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Total</td>
                                                    <!-- Grade counts for boys and girls -->
                                                    <td>{{ $gradeCounts['A']['M'] }}</td>
                                                    <td>{{ $gradeCounts['A']['F'] }}</td>
                                                    <td>{{ $gradeCounts['B']['M'] }}</td>
                                                    <td>{{ $gradeCounts['B']['F'] }}</td>
                                                    <td>{{ $gradeCounts['C']['M'] }}</td>
                                                    <td>{{ $gradeCounts['C']['F'] }}</td>
                                                    <td>{{ $gradeCounts['D']['M'] }}</td>
                                                    <td>{{ $gradeCounts['D']['F'] }}</td>
                                                    <!-- Percentages -->
                                                    <td>{{ $abcPercentage }}%</td>
                                                    <td>{{ $abcdPercentage }}%</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12 col-md-12">
                                        <canvas id="gradeDistributionLineChart" width="400" height="100"></canvas>
                                    </div>
                                </div>

                                <div class="row">
                                    <h5>PSLE Performance Analysis</h5>
                                    <div class="col-md-12 col-lg-12">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Grade</th>
                                                    <th colspan="2">A</th>
                                                    <th colspan="2">B</th>
                                                    <th colspan="2">C</th>
                                                    <th colspan="2">D</th>
                                                    <th>ABC%</th>
                                                    <th>ABCD%</th>
                                                </tr>
                                                <tr>
                                                    <!-- Gender distinctions for each grade -->
                                                    <th></th> <!-- Placeholder for the "Grade" column header -->
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>M</th>
                                                    <th>F</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Total</td>
                                                    <td>{{ $psleGradeCounts['A']['M'] }}</td>
                                                    <td>{{ $psleGradeCounts['A']['F'] }}</td>
                                                    <td>{{ $psleGradeCounts['B']['M'] }}</td>
                                                    <td>{{ $psleGradeCounts['B']['F'] }}</td>
                                                    <td>{{ $psleGradeCounts['C']['M'] }}</td>
                                                    <td>{{ $psleGradeCounts['C']['F'] }}</td>
                                                    <td>{{ $psleGradeCounts['D']['M'] }}</td>
                                                    <td>{{ $psleGradeCounts['D']['F'] }}</td>
                                                    <!-- Calculate and display ABC% and ABCD% for the total -->
                                                    <td>{{ $abcPercentage }}%</td>
                                                    <td>{{ $abcdPercentage }}%</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12 col-lg-12">
                                        <canvas id="psleGradeLineChart" width="400" height="100"></canvas>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12 col-md-12">
                                        <h6>Exam Subjects Analysis Statistics</h6>
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Subject</th>
                                                    <th colspan="2">A</th>
                                                    <th colspan="2">B</th>
                                                    <th colspan="2">C</th>
                                                    <th colspan="2">D</th>
                                                    <th colspan="2">ABC%</th>
                                                    <th colspan="2">ABCD%</th>
                                                </tr>
                                                <tr>
                                                    <!-- Second row for gender distinctions -->
                                                    <th></th>
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>M</th>
                                                    <th>F</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($subjectGradeCounts as $subject => $counts)
                                                    <tr>
                                                        <td>{{ $subject }}</td>
                                                        <td>{{ $counts['A']['M'] }}</td>
                                                        <td>{{ $counts['A']['F'] }}</td>
                                                        <td>{{ $counts['B']['M'] }}</td>
                                                        <td>{{ $counts['B']['F'] }}</td>
                                                        <td>{{ $counts['C']['M'] }}</td>
                                                        <td>{{ $counts['C']['F'] }}</td>
                                                        <td>{{ $counts['D']['M'] }}</td>
                                                        <td>{{ $counts['D']['F'] }}</td>
                                                        <td>{{ $counts['ABC%']['M'] }}%</td>
                                                        <td>{{ $counts['ABC%']['F'] }}%</td>
                                                        <td>{{ $counts['ABCD%']['M'] }}%</td>
                                                        <td>{{ $counts['ABCD%']['F'] }}%</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12 col-md-12">
                                        <canvas id="gradeDistributionChart" width="400" height="100"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end card -->
        </div> <!-- end col -->
    </div>
@endsection
@section('script')
    <script>
        var subjectGradeCounts = @json($subjectGradeCounts);
        var gradeCounts = @json($gradeCounts);

        function printContent() {
            window.print();
        }

        var ctx = document.getElementById('gradeDistributionChart').getContext('2d');
        var cty = document.getElementById('gradeDistributionLineChart').getContext('2d');

        var gradeDistributionChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: Object.keys(subjectGradeCounts),
                datasets: [
                {
                    label: 'Male - A Grades',
                    data: Object.values(subjectGradeCounts).map(counts => counts['A']['M']),
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                },
                {
                    label: 'Female - A Grades',
                    data: Object.values(subjectGradeCounts).map(counts => counts['A']['F']),
                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                },
                {
                    label: 'Male - B Grades',
                    data: Object.values(subjectGradeCounts).map(counts => counts['B']['M']),
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                },
                {
                    label: 'Female - B Grades',
                    data: Object.values(subjectGradeCounts).map(counts => counts['B']['F']),
                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                },
                {
                    label: 'Male - C Grades',
                    data: Object.values(subjectGradeCounts).map(counts => counts['C']['M']),
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                },
                {
                    label: 'Female - C Grades',
                    data: Object.values(subjectGradeCounts).map(counts => counts['C']['F']),
                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                },
                {
                    label: 'Male - D Grades',
                    data: Object.values(subjectGradeCounts).map(counts => counts['D']['M']),
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                },
                {
                    label: 'Female - D Grades',
                    data: Object.values(subjectGradeCounts).map(counts => counts['D']['F']),
                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                }
            ]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        var gradeDistributionLineChart = new Chart(cty, {
            type: 'line',
            data: {
                labels: ['A', 'B', 'C', 'D'], // The grades
                datasets: [{
                    label: 'Male',
                    data: [
                        gradeCounts['A']['M'],
                        gradeCounts['B']['M'],
                        gradeCounts['C']['M'],
                        gradeCounts['D']['M']
                    ],
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    fill: false
                }, {
                    label: 'Female',
                    data: [
                        gradeCounts['A']['F'],
                        gradeCounts['B']['F'],
                        gradeCounts['C']['F'],
                        gradeCounts['D']['F']
                    ],
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 2,
                    fill: false
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });


        var psleGradeCounts = @json($psleGradeCounts);
        var grades = ['A', 'B', 'C', 'D'];
        var maleCounts = grades.map(grade => psleGradeCounts[grade]['M']);
        var femaleCounts = grades.map(grade => psleGradeCounts[grade]['F']);

        const ctz = document.getElementById('psleGradeLineChart').getContext('2d');
        const psleGradeLineChart = new Chart(ctz, {
            type: 'line',
            data: {
                labels: grades, // X-axis labels for each grade
                datasets: [{
                    label: 'Male Students',
                    data: maleCounts,
                    fill: false,
                    borderColor: 'rgb(54, 162, 235)',
                    tension: 0.1
                }, {
                    label: 'Female Students',
                    data: femaleCounts,
                    fill: false,
                    borderColor: 'rgb(255, 99, 132)',
                    tension: 0.1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Students'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'PSLE Grade'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'PSLE Grade Distribution by Gender'
                    }
                }
            }
        });


    </script>
@endsection
