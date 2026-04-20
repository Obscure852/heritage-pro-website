@extends('layouts.master')
@section('title')
    Department Subject Analysis Report (with PSLE Comparison)
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

        .department-header {
            font-weight: bold;
            font-size: 14px;
            text-align: left;
            padding: 10px !important;
            background-color: #f8f9fa;
            border-radius: 3px;
        }

        .class-subject-col {
            text-align: left !important;
            font-weight: 500;
            background-color: #f8f9fa;
            min-width: 120px;
            max-width: 150px;
            word-wrap: break-word;
            font-size: 12px;
        }

        .subject-col {
            text-align: left !important;
            font-weight: 500;
            background-color: #f8f9fa;
            min-width: 100px;
            max-width: 120px;
            word-wrap: break-word;
            font-size: 12px;
        }

        .row-type-col {
            text-align: center !important;
            font-weight: bold;
            min-width: 60px;
            font-size: 12px;
        }


        .teacher-col {
            text-align: left !important;
            background-color: #f8f9fa;
            min-width: 100px;
            max-width: 120px;
            word-wrap: break-word;
            font-size: 12px;
        }

        .grade-header {
            font-weight: bold;
            font-size: 12px;
            background-color: #e9ecef;
        }

        .gender-subheader {
            font-size: 12px;
            font-weight: normal;
            background-color: #f1f3f4;
        }

        .performance-header {
            font-weight: bold;
            font-size: 12px;
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .summary-box {
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .department-summary {
            background-color: #f8f9fa;
            padding: 0.5rem;
            margin-bottom: 0.5rem;
            font-size: 14px;
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

            .class-subject-col,
            .subject-col,
            .teacher-col {
                max-width: 60px;
                font-size: 12px;
            }

            .department-header {
                font-size: 12px;
            }

            .grade-header {
                font-size: 12px;
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
            }">Back</a>
        @endslot
        @slot('title')
            Department Subject Analysis Report
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
                        Department Subject Analysis Report with PSLE Comparison - {{ $year }}
                    </h6>

                    <!-- Tabs Navigation -->
                    <ul class="nav nav-tabs" id="departmentAnalysisTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="all-departments-tab" data-bs-toggle="tab"
                                data-bs-target="#all-departments" type="button" role="tab"
                                aria-controls="all-departments" aria-selected="true">
                                <i class="bx bx-buildings me-1"></i>All Departments
                                ({{ $all_departments_summary['total_departments'] }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="mandatory-departments-tab" data-bs-toggle="tab"
                                data-bs-target="#mandatory-departments" type="button" role="tab"
                                aria-controls="mandatory-departments" aria-selected="false">
                                <i class="bx bx-star me-1"></i>Mandatory Subjects by Department
                                ({{ $mandatory_departments_summary['total_departments'] }})
                            </button>
                        </li>
                    </ul>

                    <!-- Tabs Content -->
                    <div class="tab-content" id="departmentAnalysisTabsContent">
                        <!-- All Departments Tab -->
                        <div class="tab-pane fade show active" id="all-departments" role="tabpanel"
                            aria-labelledby="all-departments-tab">
                            <div class="mt-3">
                                <!-- Report Summary for All Departments -->
                                <div class="summary-box">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>Total Departments:</strong>
                                            {{ $all_departments_summary['total_departments'] }}<br>
                                            <strong>Total Classes:</strong> {{ $all_departments_summary['total_classes'] }}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Academic Year:</strong> {{ $year }}</strong>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Generated:</strong> {{ $generated_at->format('Y-m-d H:i:s') }}<br>
                                            <strong>Report Type:</strong> All Subjects by Department with PSLE Comparison
                                        </div>
                                    </div>
                                </div>

                                <!-- All Departments Analysis Tables -->
                                @foreach ($all_departments_analysis as $departmentName => $departmentData)
                                    <div class="department-header">
                                        {{ $departmentName }} - Classes: {{ $departmentData['total_classes'] }}
                                    </div>

                                    <div class="table-responsive mb-4">
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                                <tr>
                                                    <th rowspan="3" class="grade-header">Class</th>
                                                    <th rowspan="3" class="grade-header">Subject</th>
                                                    <th rowspan="3" class="grade-header">Type</th>
                                                    <th rowspan="3" class="grade-header">Teacher</th>
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
                                                @foreach ($departmentData['department_subjects'] as $subjectData)
                                                    <tr>
                                                        <td class="class-subject-col">
                                                            @if ($subjectData['row_type'] === 'OUTPUT')
                                                                <strong>{{ $subjectData['class_name'] }}</strong>
                                                            @else
                                                                {{ $subjectData['class_name'] }}
                                                            @endif
                                                        </td>
                                                        <td class="subject-col">
                                                            <strong>{{ $subjectData['subject_name'] }}</strong>
                                                        </td>
                                                        <td class="row-type-col">
                                                            {{ $subjectData['row_type'] === 'OUTPUT' ? 'JCE' : $subjectData['row_type'] }}
                                                        </td>
                                                        <td class="teacher-col">{{ $subjectData['teacher_name'] }}</td>
                                                        @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $grade)
                                                            <td class="male-cell   grade-{{ strtolower($grade) }}">
                                                                {{ $subjectData['grade_analysis'][$grade]['M'] }}</td>
                                                            <td class="female-cell grade-{{ strtolower($grade) }}">
                                                                {{ $subjectData['grade_analysis'][$grade]['F'] }}</td>
                                                            <td class="total-cell  grade-{{ strtolower($grade) }}">
                                                                {{ $subjectData['grade_analysis'][$grade]['T'] }}</td>
                                                        @endforeach
                                                        @php
                                                            $perfMap = [
                                                                'AB' => [40, 25, 15],
                                                                'ABC' => [80, 65, 50],
                                                                'DEU' => [15, 25, 40],
                                                            ];
                                                        @endphp
                                                        @foreach (['AB', 'ABC', 'DEU'] as $cat)
                                                            @foreach (['M', 'F', 'T'] as $sex)
                                                                @php $val = $subjectData['performance_categories'][$cat][$sex]; @endphp
                                                                @php
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
                                                                @endphp
                                                                <td
                                                                    class="{{ $sex === 'M' ? 'male-cell' : ($sex === 'F' ? 'female-cell' : 'total-cell') }} {{ $class }}">
                                                                    {{ $val }}%
                                                                </td>
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

                        <!-- Mandatory Departments Tab -->
                        <div class="tab-pane fade" id="mandatory-departments" role="tabpanel"
                            aria-labelledby="mandatory-departments-tab">
                            <div class="mt-3">
                                <div class="summary-box">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>Departments with Mandatory Subjects:</strong>
                                            {{ $mandatory_departments_summary['total_departments'] }}<br>
                                            <strong>Total Classes:</strong>
                                            {{ $mandatory_departments_summary['total_classes'] }}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Academic Year:</strong> {{ $year }}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Generated:</strong> {{ $generated_at->format('Y-m-d H:i:s') }}<br>
                                            <strong>Report Type:</strong> Mandatory Subjects by Department with PSLE
                                            Comparison
                                        </div>
                                    </div>
                                </div>

                                @foreach ($mandatory_departments_analysis as $departmentName => $departmentData)
                                    <div class="department-header">
                                        {{ $departmentName }} - Classes: {{ $departmentData['total_classes'] }}
                                    </div>
                                    <div class="table-responsive mb-4">
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                                <tr>
                                                    <th rowspan="3" class="grade-header">Class</th>
                                                    <th rowspan="3" class="grade-header">Subject</th>
                                                    <th rowspan="3" class="grade-header">Type</th>
                                                    <th rowspan="3" class="grade-header">Teacher</th>
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
                                                @foreach ($departmentData['department_subjects'] as $subjectData)
                                                    <tr>
                                                        <td class="class-subject-col">
                                                            @if ($subjectData['row_type'] === 'OUTPUT')
                                                                <strong>{{ $subjectData['class_name'] }}</strong>
                                                            @else
                                                                {{ $subjectData['class_name'] }}
                                                            @endif
                                                        </td>

                                                        <td class="subject-col">
                                                            <strong>{{ $subjectData['subject_name'] }}</strong>
                                                        </td>
                                                        <td class="row-type-col">
                                                            {{ $subjectData['row_type'] === 'OUTPUT' ? 'JCE' : $subjectData['row_type'] }}
                                                        </td>
                                                        <td class="teacher-col">{{ $subjectData['teacher_name'] }}</td>

                                                        @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $grade)
                                                            <td class="male-cell   grade-{{ strtolower($grade) }}">
                                                                {{ $subjectData['grade_analysis'][$grade]['M'] }}</td>
                                                            <td class="female-cell grade-{{ strtolower($grade) }}">
                                                                {{ $subjectData['grade_analysis'][$grade]['F'] }}</td>
                                                            <td class="total-cell  grade-{{ strtolower($grade) }}">
                                                                {{ $subjectData['grade_analysis'][$grade]['T'] }}</td>
                                                        @endforeach

                                                        @php
                                                            $perfMap = [
                                                                'AB' => [40, 25, 15],
                                                                'ABC' => [80, 65, 50],
                                                                'DEU' => [15, 25, 40],
                                                            ];
                                                        @endphp

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
