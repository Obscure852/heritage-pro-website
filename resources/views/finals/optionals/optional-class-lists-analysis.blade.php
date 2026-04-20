@extends('layouts.master')
@section('title')
    Optional Subjects PSLE Analysis Report
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
            text-align: start;
            background-color: white;
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

        .class-header {
            background-color: #f9f8fa;
            font-weight: bold;
            font-size: 16px;
            text-align: left;
            padding: 12px !important;
            border-radius: 5px;
            margin-bottom: 10px;
            margin-top: 20px;
        }

        .class-info {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 15px;
        }


        .summary-box {
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            padding: 1rem;
            margin-bottom: 1rem;
            background-color: #f8f9fa;
        }

        .optional-badge {
            background-color: #6f42c1;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            margin-left: 8px;
        }

        .department-tag {
            background-color: #17a2b8;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 12px;
            margin-left: 8px;
        }

        .subject-header {
            font-size: 12px;
            font-weight: bold;
        }

        @media print {
            @page {
                size: A3 landscape;
                margin: 8mm;
            }

            body {
                font-size: 9px;
            }

            .no-print {
                display: none !important;
            }

            .class-header {
                font-size: 12px;
                page-break-before: always;
            }

            .class-header:first-child {
                page-break-before: avoid;
            }

            .table {
                font-size: 12px;
                page-break-inside: avoid;
            }

            .table th,
            .table td {
                padding: 2px;
                font-size: 12px;
            }

            .student-name-col {
                max-width: 120px;
                font-size: 8px;
            }

            .subject-header {
                font-size: 14px;
                padding: 6px 2px !important;
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
            window.location = '{{ route('finals.optionals.index') }}';
            }
     ">Back</a>
        @endslot
        @slot('title')
            Optional Subjects PSLE Analysis Report
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
                        Optional Subjects PSLE Baseline Analysis Report - {{ $year }}
                    </h6>

                    <!-- Report Summary -->
                    <div class="summary-box">
                        <div class="row">
                            <div class="col-3">
                                <strong>Total Optional Classes:</strong> {{ $summary['total_optional_classes'] ?? 0 }}<br>
                                <strong>Total Students:</strong> {{ $summary['total_students'] ?? 0 }}
                            </div>
                            <div class="col-3">
                                <strong>Students with PSLE:</strong> {{ $summary['students_with_psle'] ?? 0 }}<br>
                                <strong>Students without PSLE:</strong> {{ $summary['students_without_psle'] ?? 0 }}
                            </div>
                            <div class="col-3">
                                <strong>PSLE Coverage:</strong> {{ $summary['psle_coverage_percentage'] ?? 0 }}%<br>
                                <strong>Academic Year:</strong> {{ $year }}
                            </div>
                            <div class="col-3">
                                <strong>Generated:</strong> {{ $generated_at->format('Y-m-d H:i:s') }}<br>
                                <strong>Report Type:</strong> PSLE Subject Analysis
                            </div>
                        </div>
                    </div>

                    @if (empty($class_lists))
                        <div class="alert alert-warning" role="alert">
                            <i class="bx bx-info-circle me-2"></i>
                            <strong>No Data Available:</strong> No optional subjects with students were found for the
                            {{ $year }} graduation year.
                        </div>
                    @else
                        <!-- Class Lists -->
                        @foreach ($class_lists as $classList)
                            <div class="class-header">
                                {{ $classList['optional_subject_name'] }} - {{ $classList['subject_name'] }}
                                <span class="optional-badge">Optional</span>
                                <span class="department-tag">{{ $classList['department_name'] }}</span>
                            </div>

                            <div class="class-info">
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>Teacher:</strong> {{ $classList['teacher_name'] }}<br>
                                        <strong>Grade:</strong> {{ $classList['grade_name'] }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Total Students:</strong> {{ $classList['total_students'] }}<br>
                                        <strong>Male:</strong> {{ $classList['male_students'] }} | <strong>Female:</strong>
                                        {{ $classList['female_students'] }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>With PSLE:</strong> {{ $classList['students_with_psle'] }}<br>
                                        <strong>Without PSLE:</strong> {{ $classList['students_without_psle'] }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>PSLE Coverage:</strong>
                                        {{ $classList['total_students'] > 0 ? round(($classList['students_with_psle'] / $classList['total_students']) * 100, 1) : 0 }}%<br>
                                        <strong>Year:</strong> {{ $classList['graduation_year'] }}
                                    </div>
                                </div>
                            </div>

                            @if (!empty($classList['students_list']))
                                <div class="table-responsive mb-4">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-dark">
                                            <tr>
                                                <th class="subject-header text-muted">Exam Number</th>
                                                <th class="subject-header text-muted">Student Name</th>
                                                <th class="subject-header text-muted">Gender</th>
                                                <th class="subject-header text-muted">Class</th>
                                                <th class="subject-header text-muted">PSLE Overall Grade</th>
                                                <th class="subject-header text-muted">Math</th>
                                                <th class="subject-header text-muted">Eng</th>
                                                <th class="subject-header text-muted">Sci</th>
                                                <th class="subject-header text-muted">Set</th>
                                                <th class="subject-header text-muted">Agri</th>
                                                <th class="subject-header text-muted">SS</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($classList['students_list'] as $student)
                                                <tr>
                                                    <td>{{ $student['exam_number'] }}</td>
                                                    <td>{{ $student['full_name'] }}</td>
                                                    <td>{{ $student['gender'] }}</td>
                                                    <td>{{ $student['class_name'] }}</td>
                                                    <td>
                                                        @if ($student['psle_overall_grade'])
                                                            <span
                                                                class="grade-{{ strtolower($student['psle_overall_grade']) }}">{{ $student['psle_overall_grade'] }}</span>
                                                        @else
                                                            <span class="no-psle">No PSLE</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($student['psle_mathematics_grade'])
                                                            <span
                                                                class="grade-{{ strtolower($student['psle_mathematics_grade']) }}">{{ $student['psle_mathematics_grade'] }}</span>
                                                        @else
                                                            <span class="no-psle">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($student['psle_english_grade'])
                                                            <span
                                                                class="grade-{{ strtolower($student['psle_english_grade']) }}">{{ $student['psle_english_grade'] }}</span>
                                                        @else
                                                            <span class="no-psle">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($student['psle_science_grade'])
                                                            <span
                                                                class="grade-{{ strtolower($student['psle_science_grade']) }}">{{ $student['psle_science_grade'] }}</span>
                                                        @else
                                                            <span class="no-psle">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($student['psle_setswana_grade'])
                                                            <span
                                                                class="grade-{{ strtolower($student['psle_setswana_grade']) }}">{{ $student['psle_setswana_grade'] }}</span>
                                                        @else
                                                            <span class="no-psle">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($student['psle_agriculture_grade'])
                                                            <span
                                                                class="grade-{{ strtolower($student['psle_agriculture_grade']) }}">{{ $student['psle_agriculture_grade'] }}</span>
                                                        @else
                                                            <span class="no-psle">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($student['psle_social_studies_grade'])
                                                            <span
                                                                class="grade-{{ strtolower($student['psle_social_studies_grade']) }}">{{ $student['psle_social_studies_grade'] }}</span>
                                                        @else
                                                            <span class="no-psle">-</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        function printContent() {
            window.print();
        }
    </script>
@endsection
