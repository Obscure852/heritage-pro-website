@extends('layouts.master')
@section('title')
    Exam House Performance Analysis
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ $gradebookBackUrl }}">Back</a>
        @endslot
        @slot('title')
            Exam House Performance Analysis
        @endslot
    @endcomponent
@section('css')
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .content-wrapper {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            padding: 20px;
        }

        .card {
            width: 100%;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 4px;
            text-align: center;
            font-size: 10px;
        }

        .table th {
            background-color: #f2f2f2;
        }

        @media screen {
            body {
                font-size: 14px;
            }
        }

        @media print {
            @page {
                size: landscape;
                margin: 15px;
            }

            body {
                font-size: 10pt;
            }

            .no-print {
                display: none !important;
            }

            .card {
                box-shadow: none;
            }

            .table {
                font-size: 9pt;
            }

            @page {
                size: landscape;
                margin: 0.5cm;
            }
        }
    </style>
@endsection
<div class="row no-print">
    <div class="col-md-12 d-flex justify-content-end">
        <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" title="Export to Excel">
            <i style="font-size: 20px;" class="bx bx-download text-muted me-2"></i>
        </a>
        <i onclick="printContent()" style="font-size: 20px;margin-bottom:10px;cursor:pointer;"
            class="bx bx-printer text-muted"></i>
    </div>
</div>

<div class="row printable">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6 col-lg-6 align-items-start">
                        <div style="font-size:14px;" class="form-group">
                            <strong>{{ $school_data->school_name }}</strong>
                            <br>
                            <span style="maring:0;padding:0;"> {{ $school_data->physical_address }}</span>
                            <br>
                            <span style="maring:0;padding:0;"> {{ $school_data->postal_address }}</span>
                            <br>
                            <span>Tel: {{ $school_data->telephone . ' Fax: ' . $school_data->fax }}</span>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-6 d-flex justify-content-end">
                        <img height="80" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                    </div>
                </div>

            </div>
            <div class="card-body">
                <div class="report-card">
                    <div class="row">
                        <div class="col-md-12">
                            <h6>Exam House Performance Analysis -
                                {{ strtolower($test->type) === 'exam' ? 'End of Term' : 'End of ' . ($test->name ?? '') }}
                                - Term
                                {{ $term->term ?? '' }}, {{ $term->year ?? '' }}</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        {{-- ───────── 1st header line ───────── --}}
                                        <tr>
                                            <th rowspan="3">House</th>
                                            <th colspan="21">Grade&nbsp;Counts</th>
                                            <th colspan="12">Percentages</th>
                                            <th colspan="3" rowspan="2">Total&nbsp;Students</th>
                                        </tr>

                                        {{-- ───────── 2nd header line ───────── --}}
                                        <tr>
                                            @foreach (['Merit', 'A', 'B', 'C', 'D', 'E', 'U'] as $grade)
                                                <th colspan="3">{{ $grade }}</th>
                                            @endforeach
                                            @foreach (['MAB%', 'MABC%', 'MABCD%', 'DEU%'] as $pct)
                                                <th colspan="3">{{ $pct }}</th>
                                            @endforeach
                                        </tr>

                                        {{-- ───────── 3rd header line ───────── --}}
                                        <tr>
                                            {{-- 21 cells for grade counts (M/F/T) --}}
                                            @for ($i = 0; $i < 7; $i++)
                                                <th>M</th>
                                                <th>F</th>
                                                <th>T</th>
                                            @endfor

                                            {{-- 12 cells for percentage cols (M/F/T) --}}
                                            @for ($i = 0; $i < 4; $i++)
                                                <th>M</th>
                                                <th>F</th>
                                                <th>T</th>
                                            @endfor

                                            {{-- 3 cells for total students (M/F/T) --}}
                                            <th>M</th>
                                            <th>F</th>
                                            <th>T</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        {{-- ───────── rows per house ───────── --}}
                                        @foreach ($housePerformance as $houseName => $hp)
                                            @php
                                                $totalStudents = $hp['totalMale'] + $hp['totalFemale'];
                                                $pct = fn($num, $den) => $den ? round($num / $den * 100, 2) : 0;

                                                // Calculate combined percentages for this house
                                                $mabTotal = $hp['gradeCounts']['Merit']['M'] + $hp['gradeCounts']['Merit']['F']
                                                          + $hp['gradeCounts']['A']['M'] + $hp['gradeCounts']['A']['F']
                                                          + $hp['gradeCounts']['B']['M'] + $hp['gradeCounts']['B']['F'];
                                                $mabcTotal = $mabTotal + $hp['gradeCounts']['C']['M'] + $hp['gradeCounts']['C']['F'];
                                                $mabcdTotal = $mabcTotal + $hp['gradeCounts']['D']['M'] + $hp['gradeCounts']['D']['F'];
                                                $deuTotal = $hp['gradeCounts']['D']['M'] + $hp['gradeCounts']['D']['F']
                                                          + $hp['gradeCounts']['E']['M'] + $hp['gradeCounts']['E']['F']
                                                          + $hp['gradeCounts']['U']['M'] + $hp['gradeCounts']['U']['F'];

                                                $mabPercentageT = $pct($mabTotal, $totalStudents);
                                                $mabcPercentageT = $pct($mabcTotal, $totalStudents);
                                                $mabcdPercentageT = $pct($mabcdTotal, $totalStudents);
                                                $deuPercentageT = $pct($deuTotal, $totalStudents);
                                            @endphp
                                            <tr>
                                                <td>{{ $houseName }}</td>

                                                {{-- raw counts with totals --}}
                                                @foreach (['Merit', 'A', 'B', 'C', 'D', 'E', 'U'] as $g)
                                                    <td>{{ $hp['gradeCounts'][$g]['M'] }}</td>
                                                    <td>{{ $hp['gradeCounts'][$g]['F'] }}</td>
                                                    <td>{{ $hp['gradeCounts'][$g]['M'] + $hp['gradeCounts'][$g]['F'] }}</td>
                                                @endforeach

                                                {{-- percentage columns with M/F/T --}}
                                                <td>{{ $hp['mabPercentageM'] }}%</td>
                                                <td>{{ $hp['mabPercentageF'] }}%</td>
                                                <td>{{ $mabPercentageT }}%</td>
                                                <td>{{ $hp['mabcPercentageM'] }}%</td>
                                                <td>{{ $hp['mabcPercentageF'] }}%</td>
                                                <td>{{ $mabcPercentageT }}%</td>
                                                <td>{{ $hp['mabcdPercentageM'] }}%</td>
                                                <td>{{ $hp['mabcdPercentageF'] }}%</td>
                                                <td>{{ $mabcdPercentageT }}%</td>
                                                <td>{{ $hp['deuPercentageM'] }}%</td>
                                                <td>{{ $hp['deuPercentageF'] }}%</td>
                                                <td>{{ $deuPercentageT }}%</td>

                                                {{-- totals --}}
                                                <td>{{ $hp['totalMale'] }}</td>
                                                <td>{{ $hp['totalFemale'] }}</td>
                                                <td>{{ $totalStudents }}</td>
                                            </tr>
                                        @endforeach

                                        {{-- ───────── grand‑totals row ───────── --}}
                                        @php
                                            $grandTotal = $overallTotals['totalMale'] + $overallTotals['totalFemale'];
                                            $pctG = fn($num, $den) => $den ? round($num / $den * 100, 2) : 0;

                                            // Calculate combined percentages for totals row
                                            $mabTotalAll = $overallTotals['grades']['Merit']['M'] + $overallTotals['grades']['Merit']['F']
                                                         + $overallTotals['grades']['A']['M'] + $overallTotals['grades']['A']['F']
                                                         + $overallTotals['grades']['B']['M'] + $overallTotals['grades']['B']['F'];
                                            $mabcTotalAll = $mabTotalAll + $overallTotals['grades']['C']['M'] + $overallTotals['grades']['C']['F'];
                                            $mabcdTotalAll = $mabcTotalAll + $overallTotals['grades']['D']['M'] + $overallTotals['grades']['D']['F'];
                                            $deuTotalAll = $overallTotals['grades']['D']['M'] + $overallTotals['grades']['D']['F']
                                                         + $overallTotals['grades']['E']['M'] + $overallTotals['grades']['E']['F']
                                                         + $overallTotals['grades']['U']['M'] + $overallTotals['grades']['U']['F'];

                                            $mabPercentageTotals = $pctG($mabTotalAll, $grandTotal);
                                            $mabcPercentageTotals = $pctG($mabcTotalAll, $grandTotal);
                                            $mabcdPercentageTotals = $pctG($mabcdTotalAll, $grandTotal);
                                            $deuPercentageTotals = $pctG($deuTotalAll, $grandTotal);
                                        @endphp
                                        <tr style="font-weight:600; background:#f3f3f3;">
                                            <td>Totals</td>

                                            @foreach (['Merit', 'A', 'B', 'C', 'D', 'E', 'U'] as $g)
                                                <td>{{ $overallTotals['grades'][$g]['M'] }}</td>
                                                <td>{{ $overallTotals['grades'][$g]['F'] }}</td>
                                                <td>{{ $overallTotals['grades'][$g]['M'] + $overallTotals['grades'][$g]['F'] }}</td>
                                            @endforeach

                                            <td>{{ $overallTotals['MAB%']['M'] }}%</td>
                                            <td>{{ $overallTotals['MAB%']['F'] }}%</td>
                                            <td>{{ $mabPercentageTotals }}%</td>
                                            <td>{{ $overallTotals['MABC%']['M'] }}%</td>
                                            <td>{{ $overallTotals['MABC%']['F'] }}%</td>
                                            <td>{{ $mabcPercentageTotals }}%</td>
                                            <td>{{ $overallTotals['MABCD%']['M'] }}%</td>
                                            <td>{{ $overallTotals['MABCD%']['F'] }}%</td>
                                            <td>{{ $mabcdPercentageTotals }}%</td>
                                            <td>{{ $overallTotals['DEU%']['M'] }}%</td>
                                            <td>{{ $overallTotals['DEU%']['F'] }}%</td>
                                            <td>{{ $deuPercentageTotals }}%</td>

                                            <td>{{ $overallTotals['totalMale'] }}</td>
                                            <td>{{ $overallTotals['totalFemale'] }}</td>
                                            <td>{{ $grandTotal }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Graphs Section -->
                    <div class="row no-print mt-4">
                        <div class="col-md-12">
                            <h5 class="text-center">Overall Grade Distribution by Gender</h5>
                            <canvas id="gradeDistributionChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- end col -->
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    var housePerformance = @json($housePerformance);
    var houseNames = Object.keys(housePerformance);

    var grades = ['Merit', 'A', 'B', 'C', 'D', 'E', 'U'];
    var maleGradeCounts = {};
    var femaleGradeCounts = {};

    grades.forEach(function(grade) {
        maleGradeCounts[grade] = houseNames.map(function(house) {
            return housePerformance[house]['gradeCounts'][grade]['M'];
        });
        femaleGradeCounts[grade] = houseNames.map(function(house) {
            return housePerformance[house]['gradeCounts'][grade]['F'];
        });
    });

    var datasets = [];
    var colors = ['#4caf50', '#2196f3', '#ffc107', '#ff5722', '#9c27b0', '#e91e63', '#795548'];

    grades.forEach(function(grade, index) {
        datasets.push({
            label: grade + ' (M)',
            data: maleGradeCounts[grade],
            borderColor: colors[index],
            backgroundColor: colors[index],
            fill: false,
            tension: 0.1
        }, {
            label: grade + ' (F)',
            data: femaleGradeCounts[grade],
            borderColor: colors[index],
            backgroundColor: colors[index],
            borderDash: [5, 5],
            fill: false,
            tension: 0.1
        });
    });

    var ctx = document.getElementById('gradeDistributionChart').getContext('2d');
    var gradeDistributionChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: houseNames,
            datasets: datasets
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'House'
                    }
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Students'
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Overall Grade Distribution by House and Gender'
                }
            }
        }
    });

    // Prepare data for Subject Grade Distribution by Gender (remains a bar chart)
    var subjects = @json($allSubjects);
    var subjectSelect = document.getElementById('subjectSelect');
    var subjectGradeChart;

    function updateSubjectChart(subjectName) {
        var subjectGrades = ['A', 'B', 'C', 'D', 'E', 'U'];
        var maleSubjectData = {};
        var femaleSubjectData = {};

        subjectGrades.forEach(function(grade) {
            maleSubjectData[grade] = houseNames.map(function(house) {
                return housePerformance[house]['subjectGradeCounts'][subjectName][grade]['M'];
            });
            femaleSubjectData[grade] = houseNames.map(function(house) {
                return housePerformance[house]['subjectGradeCounts'][subjectName][grade]['F'];
            });
        });

        var data = {
            labels: houseNames,
            datasets: subjectGrades.flatMap(function(grade, index) {
                var backgroundColors = ['#ff6384', '#36a2eb', '#ffcd56', '#4bc0c0', '#9966ff', '#ff9f40'];
                return [{
                        label: grade + ' (M)',
                        data: maleSubjectData[grade],
                        backgroundColor: backgroundColors[index],
                        stack: 'Male'
                    },
                    {
                        label: grade + ' (F)',
                        data: femaleSubjectData[grade],
                        backgroundColor: backgroundColors[index],
                        borderColor: '#000',
                        borderWidth: 1,
                        stack: 'Female'
                    }
                ];
            })
        };

        if (subjectGradeChart) {
            subjectGradeChart.data = data;
            subjectGradeChart.options.plugins.title.text = 'Grade Distribution for ' + subjectName + ' by Gender';
            subjectGradeChart.update();
        } else {
            var ctx2 = document.getElementById('subjectGradeChart').getContext('2d');
            subjectGradeChart = new Chart(ctx2, {
                type: 'bar',
                data: data,
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            stacked: true,
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Number of Students'
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Grade Distribution for ' + subjectName + ' by Gender'
                        }
                    }
                }
            });
        }
    }

    // Initialize the chart with the first subject
    updateSubjectChart(subjects[0]);

    // Update the chart when a different subject is selected
    subjectSelect.addEventListener('change', function() {
        var selectedSubject = this.value;
        updateSubjectChart(selectedSubject);
    });

    // Prepare data for Total Students per House by Gender
    var totalMaleStudents = houseNames.map(function(house) {
        return housePerformance[house]['totalMale'];
    });
    var totalFemaleStudents = houseNames.map(function(house) {
        return housePerformance[house]['totalFemale'];
    });

    var ctx3 = document.getElementById('totalStudentsChart').getContext('2d');
    var totalStudentsChart = new Chart(ctx3, {
        type: 'bar',
        data: {
            labels: houseNames,
            datasets: [{
                    label: 'Male',
                    data: totalMaleStudents,
                    backgroundColor: '#42a5f5',
                },
                {
                    label: 'Female',
                    data: totalFemaleStudents,
                    backgroundColor: '#ef5350',
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    stacked: false,
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Students'
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Total Students per House by Gender'
                }
            }
        }
    });

    // Prepare data for Mixed Chart (MAB%, MABC%, DEU% by House and Gender)
    var mabPercentageM = houseNames.map(function(house) {
        return housePerformance[house]['mabPercentageM'];
    });
    var mabPercentageF = houseNames.map(function(house) {
        return housePerformance[house]['mabPercentageF'];
    });
    var deuPercentageM = houseNames.map(function(house) {
        return housePerformance[house]['deuPercentageM'];
    });
    var deuPercentageF = houseNames.map(function(house) {
        return housePerformance[house]['deuPercentageF'];
    });

    var ctxMixed = document.getElementById('mixedChart').getContext('2d');
    var mixedChart = new Chart(ctxMixed, {
        data: {
            labels: houseNames,
            datasets: [{
                    type: 'bar',
                    label: 'MAB% (M)',
                    data: mabPercentageM,
                    backgroundColor: '#42a5f5',
                    yAxisID: 'y',
                },
                {
                    type: 'bar',
                    label: 'MAB% (F)',
                    data: mabPercentageF,
                    backgroundColor: '#ef5350',
                    yAxisID: 'y',
                },
                {
                    type: 'line',
                    label: 'DEU% (M)',
                    data: deuPercentageM,
                    borderColor: '#1e88e5',
                    backgroundColor: '#1e88e5',
                    fill: false,
                    yAxisID: 'y1',
                },
                {
                    type: 'line',
                    label: 'DEU% (F)',
                    data: deuPercentageF,
                    borderColor: '#d81b60',
                    backgroundColor: '#d81b60',
                    fill: false,
                    yAxisID: 'y1',
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    type: 'linear',
                    position: 'left',
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    },
                    title: {
                        display: true,
                        text: 'MAB% (Bar)'
                    }
                },
                y1: {
                    type: 'linear',
                    position: 'right',
                    beginAtZero: true,
                    max: 100,
                    grid: {
                        drawOnChartArea: false,
                    },
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    },
                    title: {
                        display: true,
                        text: 'DEU% (Line)'
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'MAB% vs DEU% by House and Gender'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            var label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += context.parsed.y + '%';
                            return label;
                        }
                    }
                }
            }
        }
    });

    function printContent() {
        window.print();
    }
</script>
@endsection
