@extends('layouts.master')
@section('title')
    Teachers Analysis
@endsection
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

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 4px;
            text-align: center;
            font-size: 12px;
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

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="#"
                onclick="event.preventDefault(); 
            if (document.referrer) {
            history.back();
            } else {
            window.location = '{{ $gradebookBackUrl }}';
            }
        ">Back</a>
        @endslot
        @slot('title')
            Teachers Subjects Analysis
        @endslot
    @endcomponent

    <div class="row no-print">
        <div class="col-12 d-flex justify-content-end">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}">
                <i style="font-size: 20px;" class="bx bx-download text-muted me-2"></i>
            </a>
            <i onclick="printContent()" style="font-size: 20px;margin-bottom:10px;cursor:pointer;"
                class="bx bx-printer text-muted me-2"></i>
        </div>
    </div>

    <div class="row printable">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6 align-items-start">
                            <div class="form-group">
                                <strong>{{ $school_data->school_name }}</strong>
                                <br>
                                <span style="maring:0;padding:0;"> {{ $school_data->physical_address }}</span>
                                <br>
                                <span style="maring:0;padding:0;"> {{ $school_data->postal_address }}</span>
                                <br>
                                <span>Tel: {{ $school_data->telephone . ' Fax: ' . $school_data->fax }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex justify-content-end">
                            <img height="80" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="report-card">
                        <h5>
                            @if (isset($test->type) && strtolower($test->type) === 'exam')
                                End Of Term Exam Performance Analysis
                            @elseif(isset($test->type) && strtolower($test->type) === 'ca')
                                End Of {{ $test->name ?? '' }} Performance Analysis
                            @else
                                End Of {{ $test->name ?? '' }} Performance Analysis
                            @endif
                            Term {{ $test->term->term ?? '' }}, {{ $test->term->year ?? '' }}
                        </h5>
                        @if (isset($isGrouped) && $isGrouped)
                            @foreach ($subjectList as $subject)
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6>
                                            {{ $subject ?? 'Subject' }} Analysis
                                        </h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th style="text-align:left" rowspan="2">Teacher</th>
                                                        <th style="text-align:left" rowspan="2">Class</th>
                                                        <th style="text-align:left" rowspan="2">Subject</th>

                                                        {{-- grade groups --}}
                                                        @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $grade)
                                                            <th colspan="3">{{ $grade }}</th>
                                                        @endforeach
                                                        <th colspan="3">NS</th>
                                                        <th colspan="3">Total w/ Scores</th>
                                                        <th colspan="3">Total Enrolled</th>
                                                        {{-- percentage groups --}}
                                                        @foreach (['AB%', 'ABC%', 'ABCD%', 'DEU%'] as $percentage)
                                                            <th colspan="3">{{ $percentage }}</th>
                                                        @endforeach
                                                    </tr>
                                                    <tr>
                                                        @for ($i = 0; $i < 6 + 3; $i++)
                                                            {{-- 6 grades + NS + Total w/ Scores + Total Enrolled --}}
                                                            <th>M</th>
                                                            <th>F</th>
                                                            <th>T</th>
                                                        @endfor
                                                        @for ($i = 0; $i < 4; $i++)
                                                            {{-- 4 percentage groups --}}
                                                            <th>M</th>
                                                            <th>F</th>
                                                            <th>T</th>
                                                        @endfor
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                    @if (!empty($teacherPerformance[$subject] ?? []))
                                                        {{-- per‑teacher rows --}}
                                                        @foreach ($teacherPerformance[$subject] as $data)
                                                            <tr>
                                                                <td style="text-align:left">{{ $data['teacher_name'] }}
                                                                </td>
                                                                <td style="text-align:left">{{ $data['class_name'] }}</td>
                                                                <td style="text-align:left">{{ $data['subject_name'] }}
                                                                </td>

                                                                {{-- Grade counts --}}
                                                                @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $g)
                                                                    <td>{{ $data['grades'][$g]['M'] }}</td>
                                                                    <td>{{ $data['grades'][$g]['F'] }}</td>
                                                                    <td>{{ $data['grades'][$g]['M'] + $data['grades'][$g]['F'] }}
                                                                    </td>
                                                                @endforeach

                                                                {{-- No Score count --}}
                                                                <td>{{ $data['grades']['NS']['M'] }}</td>
                                                                <td>{{ $data['grades']['NS']['F'] }}</td>
                                                                <td>{{ $data['grades']['NS']['M'] + $data['grades']['NS']['F'] }}
                                                                </td>

                                                                {{-- Total with Scores --}}
                                                                <td>{{ $data['totalMale'] }}</td>
                                                                <td>{{ $data['totalFemale'] }}</td>
                                                                <td>{{ $data['totalMale'] + $data['totalFemale'] }}</td>

                                                                {{-- Total Enrolled --}}
                                                                <td>{{ $data['totalEnrolled']['M'] }}</td>
                                                                <td>{{ $data['totalEnrolled']['F'] }}</td>
                                                                <td>{{ $data['totalEnrolled']['M'] + $data['totalEnrolled']['F'] }}
                                                                </td>

                                                                {{-- Percentages --}}
                                                                @php
                                                                    $totalStudents =
                                                                        $data['totalMale'] + $data['totalFemale'];
                                                                @endphp

                                                                <!-- AB% -->
                                                                <td>{{ $data['AB%']['M'] }}%</td>
                                                                <td>{{ $data['AB%']['F'] }}%</td>
                                                                <td>{{ $totalStudents > 0 ? round((($data['grades']['A']['M'] + $data['grades']['A']['F'] + $data['grades']['B']['M'] + $data['grades']['B']['F']) / $totalStudents) * 100) : 0 }}%
                                                                </td>

                                                                <!-- ABC% -->
                                                                <td>{{ $data['ABC%']['M'] }}%</td>
                                                                <td>{{ $data['ABC%']['F'] }}%</td>
                                                                <td>{{ $totalStudents > 0 ? round((($data['grades']['A']['M'] + $data['grades']['A']['F'] + $data['grades']['B']['M'] + $data['grades']['B']['F'] + $data['grades']['C']['M'] + $data['grades']['C']['F']) / $totalStudents) * 100) : 0 }}%
                                                                </td>

                                                                <!-- ABCD% -->
                                                                <td>{{ $data['ABCD%']['M'] }}%</td>
                                                                <td>{{ $data['ABCD%']['F'] }}%</td>
                                                                <td>{{ $totalStudents > 0 ? round((($data['grades']['A']['M'] + $data['grades']['A']['F'] + $data['grades']['B']['M'] + $data['grades']['B']['F'] + $data['grades']['C']['M'] + $data['grades']['C']['F'] + $data['grades']['D']['M'] + $data['grades']['D']['F']) / $totalStudents) * 100) : 0 }}%
                                                                </td>

                                                                <!-- DEU% -->
                                                                <td>{{ $data['DEU%']['M'] }}%</td>
                                                                <td>{{ $data['DEU%']['F'] }}%</td>
                                                                <td>{{ $totalStudents > 0 ? round((($data['grades']['D']['M'] + $data['grades']['D']['F'] + $data['grades']['E']['M'] + $data['grades']['E']['F'] + $data['grades']['U']['M'] + $data['grades']['U']['F']) / $totalStudents) * 100) : 0 }}%
                                                                </td>
                                                            </tr>
                                                        @endforeach

                                                        {{-- grand‑totals row --}}
                                                        @php
                                                            $tot = $teacherTotals[$subject];
                                                            $totalStudents = $tot['totalMale'] + $tot['totalFemale'];
                                                            $totalEnrolled =
                                                                $tot['totalEnrolled']['M'] + $tot['totalEnrolled']['F'];
                                                        @endphp
                                                        <tr style="font-weight:600;background:#f3f3f3;">
                                                            <td colspan="3" class="text-start">Totals</td>

                                                            {{-- raw grade totals --}}
                                                            @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $g)
                                                                <td>{{ $tot['grades'][$g]['M'] }}</td>
                                                                <td>{{ $tot['grades'][$g]['F'] }}</td>
                                                                <td>{{ $tot['grades'][$g]['M'] + $tot['grades'][$g]['F'] }}
                                                                </td>
                                                            @endforeach

                                                            {{-- No Score totals --}}
                                                            <td>{{ $tot['grades']['NS']['M'] }}</td>
                                                            <td>{{ $tot['grades']['NS']['F'] }}</td>
                                                            <td>{{ $tot['grades']['NS']['M'] + $tot['grades']['NS']['F'] }}
                                                            </td>

                                                            {{-- Total with Scores --}}
                                                            <td>{{ $tot['totalMale'] }}</td>
                                                            <td>{{ $tot['totalFemale'] }}</td>
                                                            <td>{{ $tot['totalMale'] + $tot['totalFemale'] }}</td>

                                                            {{-- Total Enrolled --}}
                                                            <td>{{ $tot['totalEnrolled']['M'] }}</td>
                                                            <td>{{ $tot['totalEnrolled']['F'] }}</td>
                                                            <td>{{ $tot['totalEnrolled']['M'] + $tot['totalEnrolled']['F'] }}
                                                            </td>

                                                            {{-- correct % totals --}}
                                                            <!-- AB% -->
                                                            <td>{{ $tot['AB%']['M'] }}%</td>
                                                            <td>{{ $tot['AB%']['F'] }}%</td>
                                                            <td>{{ $totalStudents > 0 ? round((($tot['grades']['A']['M'] + $tot['grades']['A']['F'] + $tot['grades']['B']['M'] + $tot['grades']['B']['F']) / $totalStudents) * 100) : 0 }}%
                                                            </td>

                                                            <!-- ABC% -->
                                                            <td>{{ $tot['ABC%']['M'] }}%</td>
                                                            <td>{{ $tot['ABC%']['F'] }}%</td>
                                                            <td>{{ $totalStudents > 0 ? round((($tot['grades']['A']['M'] + $tot['grades']['A']['F'] + $tot['grades']['B']['M'] + $tot['grades']['B']['F'] + $tot['grades']['C']['M'] + $tot['grades']['C']['F']) / $totalStudents) * 100) : 0 }}%
                                                            </td>

                                                            <!-- ABCD% -->
                                                            <td>{{ $tot['ABCD%']['M'] }}%</td>
                                                            <td>{{ $tot['ABCD%']['F'] }}%</td>
                                                            <td>{{ $totalStudents > 0 ? round((($tot['grades']['A']['M'] + $tot['grades']['A']['F'] + $tot['grades']['B']['M'] + $tot['grades']['B']['F'] + $tot['grades']['C']['M'] + $tot['grades']['C']['F'] + $tot['grades']['D']['M'] + $tot['grades']['D']['F']) / $totalStudents) * 100) : 0 }}%
                                                            </td>

                                                            <!-- DEU% -->
                                                            <td>{{ $tot['DEU%']['M'] }}%</td>
                                                            <td>{{ $tot['DEU%']['F'] }}%</td>
                                                            <td>{{ $totalStudents > 0 ? round((($tot['grades']['D']['M'] + $tot['grades']['D']['F'] + $tot['grades']['E']['M'] + $tot['grades']['E']['F'] + $tot['grades']['U']['M'] + $tot['grades']['U']['F']) / $totalStudents) * 100) : 0 }}%
                                                            </td>
                                                        </tr>
                                                    @else
                                                        <tr>
                                                            <td colspan="42" class="text-center">
                                                                No data available for {{ $subject }}
                                                            </td>
                                                        </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                @if (isset($teacherPerformance[$subject]) && !empty($teacherPerformance[$subject]))
                                    @php
                                        $chartId = 'gradeDistributionChart_' . Str::slug($subject, '_');
                                    @endphp
                                    <div class="row no-print mt-2 mb-5">
                                        <div class="col-md-12">
                                            <h5 class="text-center">{{ $subject }} - Performance Distribution</h5>
                                            <div id="{{ $chartId }}" class="echart-subject-container"
                                                data-chart-id="{{ $chartId }}"
                                                data-subject-name="{{ $subject }}"
                                                style="width: 100%; height: 400px;">
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @else
                            <div class="row">
                                <div class="col-12">
                                    <h5>Teacher Performance Analysis - {{ ucfirst($type) }}</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                                <tr>
                                                    <th style="text-align:left" rowspan="2">Teacher</th>
                                                    <th style="text-align:left" rowspan="2">Class</th>
                                                    <th style="text-align:left" rowspan="2">Subject</th>

                                                    {{-- grade groups --}}
                                                    @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $grade)
                                                        <th colspan="3">{{ $grade }}</th>
                                                    @endforeach
                                                    <th colspan="3">NS</th>
                                                    <th colspan="3">Total w/ Scores</th>
                                                    <th colspan="3">Total Enrolled</th>
                                                    {{-- percentage groups --}}
                                                    @foreach (['AB%', 'ABC%', 'ABCD%', 'DEU%'] as $percentage)
                                                        <th colspan="3">{{ $percentage }}</th>
                                                    @endforeach
                                                </tr>
                                                <tr>
                                                    @for ($i = 0; $i < 6 + 3; $i++)
                                                        {{-- 6 grades + NS + Total w/ Scores + Total Enrolled --}}
                                                        <th>M</th>
                                                        <th>F</th>
                                                        <th>T</th>
                                                    @endfor
                                                    @for ($i = 0; $i < 4; $i++)
                                                        {{-- 4 percentage groups --}}
                                                        <th>M</th>
                                                        <th>F</th>
                                                        <th>T</th>
                                                    @endfor
                                                </tr>
                                            </thead>

                                            <tbody>
                                                {{-- ───────── per‑teacher rows ───────── --}}
                                                @foreach ($teacherPerformance as $data)
                                                    <tr>
                                                        <td style="text-align:left">{{ $data['teacher_name'] }}</td>
                                                        <td style="text-align:left">{{ $data['class_name'] }}</td>
                                                        <td style="text-align:left">{{ $data['subject_name'] }}</td>

                                                        {{-- raw grades --}}
                                                        @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $g)
                                                            <td>{{ $data['grades'][$g]['M'] }}</td>
                                                            <td>{{ $data['grades'][$g]['F'] }}</td>
                                                            <td>{{ $data['grades'][$g]['M'] + $data['grades'][$g]['F'] }}
                                                            </td>
                                                        @endforeach

                                                        {{-- No Score --}}
                                                        <td>{{ $data['grades']['NS']['M'] }}</td>
                                                        <td>{{ $data['grades']['NS']['F'] }}</td>
                                                        <td>{{ $data['grades']['NS']['M'] + $data['grades']['NS']['F'] }}
                                                        </td>

                                                        {{-- Total with Scores --}}
                                                        <td>{{ $data['totalMale'] }}</td>
                                                        <td>{{ $data['totalFemale'] }}</td>
                                                        <td>{{ $data['totalMale'] + $data['totalFemale'] }}</td>

                                                        {{-- Total Enrolled --}}
                                                        <td>{{ $data['totalEnrolled']['M'] }}</td>
                                                        <td>{{ $data['totalEnrolled']['F'] }}</td>
                                                        <td>{{ $data['totalEnrolled']['M'] + $data['totalEnrolled']['F'] }}
                                                        </td>

                                                        {{-- percentages --}}
                                                        @php
                                                            $totalWithScores =
                                                                $data['totalMale'] + $data['totalFemale'];
                                                        @endphp
                                                        @foreach (['AB%', 'ABC%', 'ABCD%', 'DEU%'] as $p)
                                                            <td>{{ $data[$p]['M'] }}%</td>
                                                            <td>{{ $data[$p]['F'] }}%</td>
                                                            <td>
                                                                @if ($p == 'AB%')
                                                                    {{ $totalWithScores > 0 ? round((($data['grades']['A']['M'] + $data['grades']['A']['F'] + $data['grades']['B']['M'] + $data['grades']['B']['F']) / $totalWithScores) * 100) : 0 }}%
                                                                @elseif($p == 'ABC%')
                                                                    {{ $totalWithScores > 0 ? round((($data['grades']['A']['M'] + $data['grades']['A']['F'] + $data['grades']['B']['M'] + $data['grades']['B']['F'] + $data['grades']['C']['M'] + $data['grades']['C']['F']) / $totalWithScores) * 100) : 0 }}%
                                                                @elseif($p == 'ABCD%')
                                                                    {{ $totalWithScores > 0 ? round((($data['grades']['A']['M'] + $data['grades']['A']['F'] + $data['grades']['B']['M'] + $data['grades']['B']['F'] + $data['grades']['C']['M'] + $data['grades']['C']['F'] + $data['grades']['D']['M'] + $data['grades']['D']['F']) / $totalWithScores) * 100) : 0 }}%
                                                                @elseif($p == 'DEU%')
                                                                    {{ $totalWithScores > 0 ? round((($data['grades']['D']['M'] + $data['grades']['D']['F'] + $data['grades']['E']['M'] + $data['grades']['E']['F'] + $data['grades']['U']['M'] + $data['grades']['U']['F']) / $totalWithScores) * 100) : 0 }}%
                                                                @endif
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach

                                                {{-- ───────── grand‑totals row ───────── --}}
                                                @php
                                                    $tot = $teacherTotals['__overall__'];
                                                    $totalWithScores = $tot['totalMale'] + $tot['totalFemale'];
                                                @endphp
                                                <tr style="font-weight:600;background:#f3f3f3;">
                                                    <td colspan="3" class="text-start">Totals</td>

                                                    {{-- raw grade totals --}}
                                                    @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $g)
                                                        <td>{{ $tot['grades'][$g]['M'] }}</td>
                                                        <td>{{ $tot['grades'][$g]['F'] }}</td>
                                                        <td>{{ $tot['grades'][$g]['M'] + $tot['grades'][$g]['F'] }}</td>
                                                    @endforeach

                                                    {{-- No Score totals --}}
                                                    <td>{{ $tot['grades']['NS']['M'] }}</td>
                                                    <td>{{ $tot['grades']['NS']['F'] }}</td>
                                                    <td>{{ $tot['grades']['NS']['M'] + $tot['grades']['NS']['F'] }}</td>

                                                    {{-- Total with Scores --}}
                                                    <td>{{ $tot['totalMale'] }}</td>
                                                    <td>{{ $tot['totalFemale'] }}</td>
                                                    <td>{{ $tot['totalMale'] + $tot['totalFemale'] }}</td>

                                                    {{-- Total Enrolled --}}
                                                    <td>{{ $tot['totalEnrolled']['M'] }}</td>
                                                    <td>{{ $tot['totalEnrolled']['F'] }}</td>
                                                    <td>{{ $tot['totalEnrolled']['M'] + $tot['totalEnrolled']['F'] }}</td>

                                                    {{-- averaged % totals --}}
                                                    @foreach (['AB%', 'ABC%', 'ABCD%', 'DEU%'] as $p)
                                                        <td>{{ $tot[$p]['M'] }}%</td>
                                                        <td>{{ $tot[$p]['F'] }}%</td>
                                                        <td>
                                                            @if ($p == 'AB%')
                                                                {{ $totalWithScores > 0 ? round((($tot['grades']['A']['M'] + $tot['grades']['A']['F'] + $tot['grades']['B']['M'] + $tot['grades']['B']['F']) / $totalWithScores) * 100) : 0 }}%
                                                            @elseif($p == 'ABC%')
                                                                {{ $totalWithScores > 0 ? round((($tot['grades']['A']['M'] + $tot['grades']['A']['F'] + $tot['grades']['B']['M'] + $tot['grades']['B']['F'] + $tot['grades']['C']['M'] + $tot['grades']['C']['F']) / $totalWithScores) * 100) : 0 }}%
                                                            @elseif($p == 'ABCD%')
                                                                {{ $totalWithScores > 0 ? round((($tot['grades']['A']['M'] + $tot['grades']['A']['F'] + $tot['grades']['B']['M'] + $tot['grades']['B']['F'] + $tot['grades']['C']['M'] + $tot['grades']['C']['F'] + $tot['grades']['D']['M'] + $tot['grades']['D']['F']) / $totalWithScores) * 100) : 0 }}%
                                                            @elseif($p == 'DEU%')
                                                                {{ $totalWithScores > 0 ? round((($tot['grades']['D']['M'] + $tot['grades']['D']['F'] + $tot['grades']['E']['M'] + $tot['grades']['E']['F'] + $tot['grades']['U']['M'] + $tot['grades']['U']['F']) / $totalWithScores) * 100) : 0 }}%
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <!-- Graphs Section for non-grouped view -->
                            <div class="row no-print mt-4">
                                <div class="col-md-12">
                                    <h5 class="text-center">Percentage Performance by Teacher-Class-Subject and Gender</h5>
                                    <div id="percentageChart" style="width: 100%; height: 400px;"></div>
                                </div>
                            </div>
                            <div class="row no-print mt-4">
                                <div class="col-md-12">
                                    <h5 class="text-center">Grade Distribution by Teacher-Class-Subject and Gender</h5>
                                    <div id="gradeDistributionChart" style="width: 100%; height: 400px;"></div>
                                </div>
                            </div>
                        @endif
                    </div> <!-- report-card -->
                </div> <!-- card-body -->
            </div> <!-- card -->
        </div> <!-- col -->
    </div>
@endsection
@section('script')
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
    <script>
        function printContent() {
            window.print();
        }

        document.addEventListener('DOMContentLoaded', function() {
            window.echartsInstances = {};

            const isGrouped = @json($isGrouped ?? false);
            const teacherPerformance = @json($teacherPerformance ?? []);
            const subjectList = @json($subjectList ?? []);
            console.log('Is Grouped:', isGrouped);

            const echartsColors = {
                male: '#5470c6',
                female: '#ee6666',
                total: '#91cc75',
                gradeA: '#91cc75',
                gradeB: '#5470c6',
                gradeC: '#fac858',
                gradeD: '#fc8452',
                gradeE: '#ee6666',
                gradeU: '#9e9e9e',
                gradeNS: '#d3d3d3',
                lineAB: '#5470c6',
                lineABC: '#91cc75',
                lineABCD: '#fac858',
                lineDEU: '#ee6666'
            };

            const gradesForChart = ['A', 'B', 'C', 'D', 'E', 'U', 'NS'];
            const percentagesForChart = ['AB%', 'ABC%', 'ABCD%', 'DEU%'];

            function initChart(domId, option) {
                const chartDom = document.getElementById(domId);
                if (chartDom) {
                    try {
                        const existingInstance = echarts.getInstanceByDom(chartDom);
                        if (existingInstance && !existingInstance.isDisposed()) {
                            existingInstance.dispose();
                        }
                        const chart = echarts.init(chartDom);
                        chart.setOption(option);
                        window.echartsInstances[domId] = chart;
                        const resizeObserver = new ResizeObserver(() => {
                            if (chart && !chart.isDisposed()) {
                                chart.resize();
                            }
                        });
                        resizeObserver.observe(chartDom);
                        window.addEventListener('resize', () => {
                            if (chart && !chart.isDisposed()) {
                                chart.resize();
                            }
                        });
                        return chart;
                    } catch (e) {
                        console.error(`Error initializing chart #${domId}:`, e);
                        chartDom.innerHTML =
                            `<p style="text-align:center; padding:20px; color: red;">Error loading chart.</p>`;
                    }
                }
                return null;
            }

            if (isGrouped) {
                if (!Array.isArray(subjectList) || subjectList.length === 0) {
                    return;
                }

                const chartContainers = document.querySelectorAll('.echart-subject-container');

                chartContainers.forEach(chartDiv => {
                    const chartId = chartDiv.dataset.chartId;
                    const subject = chartDiv.dataset.subjectName;

                    if (!chartId || !subject) {
                        return;
                    }

                    if (!teacherPerformance || !teacherPerformance.hasOwnProperty(subject)) {
                        chartDiv.innerHTML =
                            `<p style="text-align:center; padding:20px;">Data error for ${subject}.</p>`;
                        return;
                    }

                    const subjectData = teacherPerformance[subject];

                    if (!Array.isArray(subjectData) || subjectData.length === 0) {
                        chartDiv.innerHTML =
                            `<p style="text-align:center; padding:20px;">No data available for ${subject}.</p>`;
                        return;
                    }

                    const teacherLabels = subjectData.map(item =>
                        `${item?.teacher_name ?? 'N/A'} - ${item?.class_name ?? 'N/A'}`);
                    const maleGradeCounts = {};
                    const femaleGradeCounts = {};
                    const totalGradeCounts = {};
                    gradesForChart.forEach(g => {
                        maleGradeCounts[g] = [];
                        femaleGradeCounts[g] = [];
                        totalGradeCounts[g] = [];
                    });
                    subjectData.forEach(item => {
                        gradesForChart.forEach(grade => {
                            const maleCount = item?.grades?.[grade]?.['M'] ?? 0;
                            const femaleCount = item?.grades?.[grade]?.['F'] ?? 0;
                            maleGradeCounts[grade].push(maleCount);
                            femaleGradeCounts[grade].push(femaleCount);
                            totalGradeCounts[grade].push(maleCount + femaleCount);
                        });
                    });

                    const gradeDistSeries = gradesForChart.flatMap(grade => ([{
                            name: `${grade} (M)`,
                            type: 'bar',
                            stack: 'Male',
                            emphasis: {
                                focus: 'series'
                            },
                            color: echartsColors['grade' + grade] || '#ccc',
                            data: maleGradeCounts[grade]
                        },
                        {
                            name: `${grade} (F)`,
                            type: 'bar',
                            stack: 'Female',
                            emphasis: {
                                focus: 'series'
                            },
                            color: echartsColors['grade' + grade] || '#ccc',
                            data: femaleGradeCounts[grade],
                            itemStyle: {
                                borderColor: '#555',
                                borderWidth: 0.5
                            }
                        },
                        {
                            name: `${grade} (T)`,
                            type: 'line',
                            symbol: 'circle',
                            emphasis: {
                                focus: 'series'
                            },
                            lineStyle: {
                                type: 'dashed',
                                width: 2
                            },
                            symbolSize: 6,
                            color: echartsColors['grade' + grade] || '#ccc',
                            data: totalGradeCounts[grade]
                        }
                    ]));

                    const gradeDistOption = {
                        title: {
                            text: `${subject} - Grade Distribution`,
                            left: 'center',
                            textStyle: {
                                fontSize: 16
                            }
                        },
                        tooltip: {
                            trigger: 'axis',
                            axisPointer: {
                                type: 'shadow'
                            }
                        },
                        legend: {
                            top: 30,
                            type: 'scroll',
                            data: gradesForChart.flatMap(g => [`${g} (M)`, `${g} (F)`, `${g} (T)`])
                        },
                        grid: {
                            top: 80,
                            bottom: 30,
                            left: '3%',
                            right: '4%',
                            containLabel: true
                        },
                        xAxis: {
                            type: 'category',
                            data: teacherLabels,
                            axisLabel: {
                                interval: 0,
                                rotate: 30
                            }
                        },
                        yAxis: {
                            type: 'value',
                            name: 'Number of Students'
                        },
                        series: gradeDistSeries,
                        toolbox: {
                            right: 20,
                            feature: {
                                saveAsImage: {},
                                dataView: {},
                                magicType: {
                                    type: ['line', 'bar', 'stack']
                                },
                                restore: {}
                            }
                        }
                    };

                    initChart(chartId, gradeDistOption);
                });

            } else {
                if (!Array.isArray(teacherPerformance) || teacherPerformance.length === 0) {
                    const percChartDom = document.getElementById('percentageChart');
                    if (percChartDom) percChartDom.innerHTML =
                        '<p style="text-align:center; padding:20px;">No data available.</p>';

                    const gradeChartDom = document.getElementById('gradeDistributionChart');
                    if (gradeChartDom) gradeChartDom.innerHTML =
                        '<p style="text-align:center; padding:20px;">No data available.</p>';
                    return;
                }
            }
        });
    </script>
@endsection
