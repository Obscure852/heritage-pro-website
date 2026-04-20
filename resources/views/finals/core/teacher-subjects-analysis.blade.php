@extends('layouts.master')
@section('title')
    Teacher Subject Analysis Report (with PSLE Comparison)
@endsection

@section('css')
    <style>
        body {
            font-size: 14px;
        }

        .table {
            width: 100%;
            margin-bottom: 3mm;
            margin-top: 10px;
            page-break-inside: avoid;
            font-size: 12px;
        }

        .table th,
        .table td {
            padding: 0.2rem;
            white-space: nowrap;
            vertical-align: middle;
            text-align: center;
            border: 1px solid #dee2e6;
        }

        .table thead th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .table-sm td,
        .table-sm th {
            padding: 0.2rem;
        }

        .teacher-header {
            background-color: #343a40;
            color: white;
            font-weight: bold;
            font-size: 12px;
            text-align: left;
            padding: 8px !important;
        }

        .class-col {
            text-align: left !important;
            font-weight: 500;
            background-color: #f8f9fa;
            min-width: 120px;
            max-width: 150px;
            word-wrap: break-word;
            font-size: 12px;
        }

        .subject-col {
            text-align: center !important;
            font-weight: 500;
            background-color: #f8f9fa;
            min-width: 100px;
            max-width: 120px;
            word-wrap: break-word;
            font-size: 12px;
        }

        .row-type-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: 600;
            margin-right: 4px;
            vertical-align: middle;
        }

        .row-type-badge.jce {
            background-color: #3b82f6;
            color: #fff;
        }

        .row-type-badge.psle {
            background-color: #f59e0b;
            color: #fff;
        }

        .psle-row {
            background-color: #e3f2fd;
        }

        .output-row {
            background-color: #fff3e0;
        }

        .grade-header {
            font-weight: bold;
            font-size: 12px;
            background-color: #e9ecef;
        }

        .summary-box {
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .teacher-summary {
            background-color: #f8f9fa;
            padding: 0.5rem;
            margin-bottom: 0.5rem;
            font-size: 14px;
        }

        .report-guide {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #3b82f6;
            border-radius: 0 6px 6px 0;
            padding: 20px 24px;
            margin-bottom: 20px;
            font-size: 13px;
            line-height: 1.6;
            color: #374151;
        }

        .report-guide-title {
            font-weight: 700;
            font-size: 14px;
            color: #1e3a5f;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .report-guide p {
            margin-bottom: 8px;
        }

        .report-guide-section {
            font-weight: 600;
            color: #1f2937;
            margin-top: 10px;
            margin-bottom: 4px;
        }

        @media print {
            .report-guide {
                display: none !important;
            }
        }

        .department-tags {
            font-size: 8px;
        }

        .department-tag {
            display: inline-block;
            background-color: #6c757d;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            margin-right: 3px;
            margin-bottom: 2px;
        }

        @media print {
            @page {
                size: landscape;
                margin: 5mm;
            }

            body {
                font-size: 12px;
            }

            .no-print {
                display: none !important;
            }

            .card {
                box-shadow: none;
                border: none;
            }

            .table {
                font-size: 12px;
            }

            .table th,
            .table td {
                padding: 0.1mm;
                font-size: 12px;
            }

            .class-col,
            .subject-col {
                max-width: 80px;
                font-size: 12px;
            }

            .teacher-header {
                font-size: 12px;
            }

            .grade-header {
                font-size: 12px;
            }

            .department-tags {
                font-size: 5px;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="#"
                onclick="event.preventDefault();
            if (document.referrer) {
            history.back();
            } else {
            window.location = '{{ route('finals.students.index') }}';
            }
     ">Back</a>
        @endslot
        @slot('title')
            Teacher Subject Analysis Report
        @endslot
    @endcomponent

    <div class="row no-print mb-3">
        <div class="col-12 d-flex justify-content-end">
            <a href="#" onclick="printContent()" class="me-2 text-muted">
                <i style="font-size: 20px;" class="bx bx-printer me-1"></i>
            </a>
        </div>
    </div>

    <div class="row printable">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div style="font-size:14px;" class="col-md-6">
                            <div class="d-flex flex-column">
                                <h5 class="mb-0">{{ $school_data->school_name ?? 'School Name' }}</h5>
                                <p class="mb-0">{{ $school_data->physical_address ?? 'Physical Address' }}</p>
                                <p class="mb-0">{{ $school_data->postal_address ?? 'Postal Address' }}</p>
                                <p class="mb-0">Tel: {{ $school_data->telephone ?? 'Tel' }} Fax:
                                    {{ $school_data->fax ?? 'Fax' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex justify-content-end">
                            @if (isset($school_data->logo_path))
                                <img height="80" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <h6 class="text-start mb-3">
                        Teacher Subject Analysis Report with PSLE Comparison - {{ $year }}
                    </h6>

                    <div class="report-guide no-print">
                        <div class="report-guide-title">
                            <i class="bx bx-info-circle"></i> How to Read This Report
                        </div>
                        <p>
                            This report compares each teacher's <strong>JCE examination results</strong> against the
                            <strong>PSLE grades</strong> that students achieved when they entered junior secondary school.
                            This comparison helps measure the value each teacher has added to student performance over the
                            three-year JCE programme.
                        </p>

                        <div class="report-guide-section">Understanding the Rows</div>
                        <p>
                            Each class-subject combination has two rows:
                            the <span class="row-type-badge jce">JCE</span> row shows the actual examination output
                            grades, while the <span class="row-type-badge psle">PSLE</span> row shows the PSLE input
                            grades for the same group of students. By comparing the two, you can see whether students
                            improved, maintained, or declined from their PSLE baseline.
                        </p>

                        <div class="report-guide-section">Understanding the Columns</div>
                        <p>
                            <strong>Grade Distribution (A&ndash;U):</strong> Shows the number of students who achieved
                            each grade, broken down by Male (M), Female (F), and Total (T).<br>
                            <strong>AB%:</strong> Percentage of students who achieved an A or B (top performers).<br>
                            <strong>ABC%:</strong> Percentage of students who achieved an A, B, or C (overall pass rate).<br>
                            <strong>DEU%:</strong> Percentage of students who scored D, E, or U (underperformers &mdash; lower is better).
                        </p>

                        <div class="report-guide-section">Tabs</div>
                        <p style="margin-bottom: 0;">
                            <strong>All Teachers</strong> &mdash; shows every teacher and all their subjects.<br>
                            <strong>Mandatory Subject Teachers</strong> &mdash; shows only teachers of mandatory/core subjects.
                        </p>
                    </div>

                    <ul class="nav nav-tabs" id="teacherAnalysisTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="all-teachers-tab" data-bs-toggle="tab"
                                data-bs-target="#all-teachers" type="button" role="tab" aria-controls="all-teachers"
                                aria-selected="true">
                                <i class="bx bx-user me-1"></i>All Teachers ({{ $all_teachers_summary['total_teachers'] }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="mandatory-teachers-tab" data-bs-toggle="tab"
                                data-bs-target="#mandatory-teachers" type="button" role="tab"
                                aria-controls="mandatory-teachers" aria-selected="false">
                                <i class="bx bx-star me-1"></i>Mandatory Subject Teachers
                                ({{ $mandatory_teachers_summary['total_teachers'] }})
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="teacherAnalysisTabsContent">
                        <div class="tab-pane fade show active" id="all-teachers" role="tabpanel"
                            aria-labelledby="all-teachers-tab">
                            <div class="mt-3">
                                <div class="summary-box">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>Total Teachers:</strong>
                                            {{ $all_teachers_summary['total_teachers'] }}<br>
                                            <strong>Total Classes:</strong> {{ $all_teachers_summary['total_classes'] }}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Academic Year:</strong> {{ $year }}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Generated:</strong> {{ $generated_at->format('Y-m-d H:i:s') }}<br>
                                            <strong>Report Type:</strong> Teachers with PSLE Comparison
                                        </div>
                                    </div>
                                </div>

                                @foreach ($all_teachers_analysis as $teacherName => $teacherData)
                                    <div class="teacher-summary">
                                        <strong>{{ $teacherName }}</strong> -
                                        Classes: {{ $teacherData['total_classes'] }},
                                        Students: {{ $teacherData['total_students'] }}
                                        @if ($teacherData['departments']->isNotEmpty())
                                            <br>
                                            <div style="font-size: 10px;" class="department-tags">
                                                <strong>Departments:</strong>
                                                @foreach ($teacherData['departments'] as $department)
                                                    <span style="font-size: 10px;"
                                                        class="department-tag">{{ $department }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    <div class="table-responsive mb-4">
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                                <tr>
                                                    <th rowspan="3" class="teacher-header">{{ $teacherName }}</th>
                                                    <th rowspan="3" class="grade-header">Class</th>
                                                    <th rowspan="3" class="grade-header">Subject</th>
                                                    <th colspan="18" class="grade-header">Grade Distribution</th>
                                                    <th colspan="9" class="performance-header">Performance Categories
                                                    </th>
                                                </tr>
                                                <tr>
                                                    <th colspan="3" class="grade-header grade-a">A</th>
                                                    <th colspan="3" class="grade-header grade-b">B</th>
                                                    <th colspan="3" class="grade-header grade-c">C</th>
                                                    <th colspan="3" class="grade-header grade-d">D</th>
                                                    <th colspan="3" class="grade-header grade-e">E</th>
                                                    <th colspan="3" class="grade-header grade-u">U</th>
                                                    <th colspan="3" class="performance-header">AB%</th>
                                                    <th colspan="3" class="performance-header">ABC%</th>
                                                    <th colspan="3" class="performance-header">DEU%</th>
                                                </tr>
                                                <tr>
                                                    @for ($i = 0; $i < 9; $i++)
                                                        <th class="gender-subheader">M</th>
                                                        <th class="gender-subheader">F</th>
                                                        <th class="gender-subheader">T</th>
                                                    @endfor
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($teacherData['teacher_subjects'] as $index => $subjectData)
                                                    <tr>
                                                        <td></td>
                                                        <td class="class-col">
                                                            @if (isset($subjectData['row_type']) && $subjectData['row_type'] === 'PSLE')
                                                                <span class="row-type-badge psle">PSLE</span>
                                                                {{ $subjectData['class_name'] }}
                                                            @else
                                                                <span class="row-type-badge jce">JCE</span>
                                                                <strong>{{ $subjectData['class_name'] }}
                                                                    ({{ $subjectData['total_students'] }}
                                                                    students)</strong>
                                                            @endif
                                                        </td>

                                                        <td class="subject-col">
                                                            <strong>{{ $subjectData['subject_name'] }}</strong>
                                                        </td>

                                                        <!-- Grade Distribution -->
                                                        @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $grade)
                                                            <td class="male-cell grade-{{ strtolower($grade) }}">
                                                                {{ $subjectData['grade_analysis'][$grade]['M'] }}</td>
                                                            <td class="female-cell grade-{{ strtolower($grade) }}">
                                                                {{ $subjectData['grade_analysis'][$grade]['F'] }}</td>
                                                            <td class="total-cell grade-{{ strtolower($grade) }}">
                                                                {{ $subjectData['grade_analysis'][$grade]['T'] }}</td>
                                                        @endforeach

                                                        @php
                                                            $perfMap = [
                                                                'AB' => [40, 25, 15],
                                                                'ABC' => [80, 65, 50],
                                                                'DEU' => [15, 25, 40],
                                                            ];
                                                        @endphp

                                                        <!-- Performance Categories -->
                                                        @foreach (['AB', 'ABC', 'DEU'] as $cat)
                                                            @foreach (['M', 'F', 'T'] as $sex)
                                                                @php
                                                                    $val =
                                                                        $subjectData['performance_categories'][$cat][
                                                                            $sex
                                                                        ];
                                                                    [$hi, $mid, $low] = $perfMap[$cat];
                                                                    $class =
                                                                        $cat === 'DEU'
                                                                            ? ($val <= $hi
                                                                                ? 'percent-excellent'
                                                                                : ($val <= $mid
                                                                                    ? 'percent-good'
                                                                                    : ($val <= $low
                                                                                        ? 'percent-fair'
                                                                                        : 'percent-poor')))
                                                                            : ($val >= $hi
                                                                                ? 'percent-excellent'
                                                                                : ($val >= $mid
                                                                                    ? 'percent-good'
                                                                                    : ($val >= $low
                                                                                        ? 'percent-fair'
                                                                                        : 'percent-poor')));
                                                                    $cellClass =
                                                                        $sex === 'M'
                                                                            ? 'male-cell'
                                                                            : ($sex === 'F'
                                                                                ? 'female-cell'
                                                                                : 'total-cell');
                                                                @endphp
                                                                <td class="{{ $cellClass }} {{ $class }}">
                                                                    {{ $val }}%</td>
                                                            @endforeach
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="tab-pane fade" id="mandatory-teachers" role="tabpanel"
                            aria-labelledby="mandatory-teachers-tab">
                            <div class="mt-3">
                                <div class="summary-box">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>Teachers with Mandatory Subjects:</strong>
                                            {{ $mandatory_teachers_summary['total_teachers'] }}<br>
                                            <strong>Total Classes:</strong>
                                            {{ $mandatory_teachers_summary['total_classes'] }}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Academic Year:</strong> {{ $year }}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Generated:</strong> {{ $generated_at->format('Y-m-d H:i:s') }}<br>
                                            <strong>Report Type:</strong> Mandatory Subject Teachers with PSLE Comparison
                                        </div>
                                    </div>
                                </div>

                                @foreach ($mandatory_teachers_analysis as $teacherName => $teacherData)
                                    <div class="teacher-summary">
                                        <strong>{{ $teacherName }}</strong> -
                                        Classes: {{ $teacherData['total_classes'] }},
                                        Students: {{ $teacherData['total_students'] }}
                                        @if ($teacherData['departments']->isNotEmpty())
                                            <br>
                                            <div style="font-size: 10px;" class="department-tags">
                                                <strong>Departments:</strong>
                                                @foreach ($teacherData['departments'] as $department)
                                                    <span style="font-size: 10px;"
                                                        class="department-tag">{{ $department }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    <div class="table-responsive mb-4">
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                                <tr>
                                                    <th rowspan="3" class="teacher-header">{{ $teacherName }}</th>
                                                    <th rowspan="3" class="grade-header">Class</th>
                                                    <th rowspan="3" class="grade-header">Subject</th>
                                                    <th colspan="18" class="grade-header">Grade Distribution</th>
                                                    <th colspan="9" class="performance-header">Performance Categories
                                                    </th>
                                                </tr>
                                                <tr>
                                                    <th colspan="3" class="grade-header grade-a">A</th>
                                                    <th colspan="3" class="grade-header grade-b">B</th>
                                                    <th colspan="3" class="grade-header grade-c">C</th>
                                                    <th colspan="3" class="grade-header grade-d">D</th>
                                                    <th colspan="3" class="grade-header grade-e">E</th>
                                                    <th colspan="3" class="grade-header grade-u">U</th>
                                                    <th colspan="3" class="performance-header">AB%</th>
                                                    <th colspan="3" class="performance-header">ABC%</th>
                                                    <th colspan="3" class="performance-header">DEU%</th>
                                                </tr>
                                                <tr>
                                                    @for ($i = 0; $i < 9; $i++)
                                                        <th class="gender-subheader">M</th>
                                                        <th class="gender-subheader">F</th>
                                                        <th class="gender-subheader">T</th>
                                                    @endfor
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($teacherData['teacher_subjects'] as $index => $subjectData)
                                                    <tr>
                                                        <td></td>
                                                        <td class="class-col">
                                                            @if (isset($subjectData['row_type']) && $subjectData['row_type'] === 'PSLE')
                                                                <span class="row-type-badge psle">PSLE</span>
                                                                {{ $subjectData['class_name'] }}
                                                            @else
                                                                <span class="row-type-badge jce">JCE</span>
                                                                <strong>{{ $subjectData['class_name'] }}
                                                                    ({{ $subjectData['total_students'] }}
                                                                    students)</strong>
                                                            @endif
                                                        </td>

                                                        <td class="subject-col">
                                                            <strong>{{ $subjectData['subject_name'] }}</strong>
                                                        </td>

                                                        <!-- Grade Distribution -->
                                                        @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $grade)
                                                            <td class="male-cell grade-{{ strtolower($grade) }}">
                                                                {{ $subjectData['grade_analysis'][$grade]['M'] }}</td>
                                                            <td class="female-cell grade-{{ strtolower($grade) }}">
                                                                {{ $subjectData['grade_analysis'][$grade]['F'] }}</td>
                                                            <td class="total-cell grade-{{ strtolower($grade) }}">
                                                                {{ $subjectData['grade_analysis'][$grade]['T'] }}</td>
                                                        @endforeach

                                                        @php
                                                            $perfMap = [
                                                                'AB' => [40, 25, 15],
                                                                'ABC' => [80, 65, 50],
                                                                'DEU' => [15, 25, 40],
                                                            ];
                                                        @endphp

                                                        <!-- Performance Categories -->
                                                        @foreach (['AB', 'ABC', 'DEU'] as $cat)
                                                            @foreach (['M', 'F', 'T'] as $sex)
                                                                @php
                                                                    $val =
                                                                        $subjectData['performance_categories'][$cat][
                                                                            $sex
                                                                        ];
                                                                    [$hi, $mid, $low] = $perfMap[$cat];
                                                                    $class =
                                                                        $cat === 'DEU'
                                                                            ? ($val <= $hi
                                                                                ? 'percent-excellent'
                                                                                : ($val <= $mid
                                                                                    ? 'percent-good'
                                                                                    : ($val <= $low
                                                                                        ? 'percent-fair'
                                                                                        : 'percent-poor')))
                                                                            : ($val >= $hi
                                                                                ? 'percent-excellent'
                                                                                : ($val >= $mid
                                                                                    ? 'percent-good'
                                                                                    : ($val >= $low
                                                                                        ? 'percent-fair'
                                                                                        : 'percent-poor')));
                                                                    $cellClass =
                                                                        $sex === 'M'
                                                                            ? 'male-cell'
                                                                            : ($sex === 'F'
                                                                                ? 'female-cell'
                                                                                : 'total-cell');
                                                                @endphp
                                                                <td class="{{ $cellClass }} {{ $class }}">
                                                                    {{ $val }}%</td>
                                                            @endforeach
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function printContent() {
            window.print();
        }
    </script>
@endsection
