@extends('layouts.master')
@section('title')
    JCE vs PSLE Performance Comparison
@endsection

@section('css')
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
        }

        .card {
            box-shadow: none;
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
        }

        .table thead th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .table-sm td,
        .table-sm th {
            padding: 0.2rem;
        }

        .exam-type {
            text-align: left !important;
            font-weight: 500;
            background-color: #f8f9fa;
            min-width: 80px;
        }

        .exam-header {
            font-weight: bold;
            font-size: 11px;
            background-color: #e9ecef;
        }

        .jce-header {
            background-color: #d4edda !important;
            color: #155724;
        }

        .psle-header {
            background-color: #fff3cd !important;
            color: #856404;
        }

        .grade-header {
            font-weight: bold;
            font-size: 10px;
        }

        .gender-header {
            font-size: 9px;
            font-weight: normal;
        }

        .performance-header {
            font-weight: bold;
            font-size: 10px;
            background-color: #f1f3f4;
        }

        /* Grade-specific styling */
        .grade-merit {
            background-color: #d5f5d5;
        }

        .grade-a {
            background-color: #e5f3ff;
        }

        .grade-b {
            background-color: #fff3e5;
        }

        .grade-c {
            background-color: #ffffd5;
        }

        .grade-d {
            background-color: #ffe5e5;
        }

        .grade-e {
            background-color: #ffcccc;
        }

        .grade-u {
            background-color: #ffd5d5;
        }

        /* Performance indicators */
        .percent-excellent {
            background-color: #d5f5d5;
            font-weight: bold;
            color: #155724;
        }

        .percent-good {
            background-color: #fff3cd;
            font-weight: bold;
            color: #856404;
        }

        .percent-fair {
            background-color: #f8d7da;
            color: #721c24;
        }

        .percent-poor {
            background-color: #ffd5d5;
            color: #721c24;
            font-weight: bold;
        }

        /* Gender-specific styling */
        .male-cell {
            background-color: #e3f2fd;
        }

        .female-cell {
            background-color: #fce4ec;
        }

        .total-cell {
            background-color: #f3e5f5;
            font-weight: bold;
        }

        /* Change indicators */
        .improvement {
            background-color: #d4edda;
            color: #155724;
            font-weight: bold;
        }

        .decline {
            background-color: #f8d7da;
            color: #721c24;
            font-weight: bold;
        }

        .stable {
            background-color: #e2e3e5;
            color: #383d41;
        }

        .no-results {
            color: #888;
            font-style: italic;
        }

        .summary-box {
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        @media print {
            @page {
                size: landscape;
                margin: 5mm;
            }

            body {
                font-size: 8px;
            }

            .no-print {
                display: none !important;
            }

            .card {
                box-shadow: none;
                border: none;
            }

            .table {
                font-size: 7px;
            }

            .table th,
            .table td {
                padding: 0.2mm;
                font-size: 7px;
            }

            .exam-type {
                max-width: 20mm;
                font-size: 6px;
            }

            .chart-container {
                display: none;
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
            JCE vs PSLE Performance Comparison - {{ $class_info['name'] }}
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
                        JCE vs PSLE Performance Comparison - {{ $class_info['name'] }}
                        ({{ $class_info['grade_name'] }}) - {{ $year }}
                    </h6>

                    <!-- Class Summary -->
                    <div class="summary-box">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Class:</strong> {{ $class_info['name'] }}<br>
                                <strong>Teacher:</strong> {{ $class_info['teacher'] }}<br>
                                <strong>Grade:</strong> {{ $class_info['grade_name'] }}
                            </div>
                            <div class="col-md-6">
                                <strong>Total Students Analyzed:</strong> {{ $students_analyzed }}<br>
                                <strong>Male:</strong> {{ $class_info['gender_totals']['M'] }} |
                                <strong>Female:</strong> {{ $class_info['gender_totals']['F'] }}<br>
                                <strong>Academic Year:</strong> {{ $year }}
                            </div>
                        </div>
                    </div>

                    <!-- Grade Distribution Comparison -->
                    <h6 class="text-start mb-3">Grade Distribution Comparison</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <!-- Main Header Row -->
                                <tr>
                                    <th rowspan="2" class="exam-type">Exam Type</th>
                                    <th colspan="3" class="grade-header">Merit</th>
                                    <th colspan="3" class="grade-header">A</th>
                                    <th colspan="3" class="grade-header">B</th>
                                    <th colspan="3" class="grade-header">C</th>
                                    <th colspan="3" class="grade-header">D</th>
                                    <th colspan="3" class="grade-header">E</th>
                                    <th colspan="3" class="grade-header">U</th>
                                    <th colspan="3" class="performance-header">MAB%</th>
                                    <th colspan="3" class="performance-header">MABC%</th>
                                    <th colspan="3" class="performance-header">MABCD%</th>
                                </tr>
                                <!-- Sub Header Row -->
                                <tr>
                                    <!-- Merit -->
                                    <th class="gender-header">M</th>
                                    <th class="gender-header">F</th>
                                    <th class="gender-header">T</th>
                                    <!-- Grade A -->
                                    <th class="gender-header">M</th>
                                    <th class="gender-header">F</th>
                                    <th class="gender-header">T</th>
                                    <!-- Grade B -->
                                    <th class="gender-header">M</th>
                                    <th class="gender-header">F</th>
                                    <th class="gender-header">T</th>
                                    <!-- Grade C -->
                                    <th class="gender-header">M</th>
                                    <th class="gender-header">F</th>
                                    <th class="gender-header">T</th>
                                    <!-- Grade D -->
                                    <th class="gender-header">M</th>
                                    <th class="gender-header">F</th>
                                    <th class="gender-header">T</th>
                                    <!-- Grade E -->
                                    <th class="gender-header">M</th>
                                    <th class="gender-header">F</th>
                                    <th class="gender-header">T</th>
                                    <!-- Grade U -->
                                    <th class="gender-header">M</th>
                                    <th class="gender-header">F</th>
                                    <th class="gender-header">T</th>
                                    <!-- High Achievement -->
                                    <th class="gender-header">M</th>
                                    <th class="gender-header">F</th>
                                    <th class="gender-header">T</th>
                                    <!-- Pass Rate -->
                                    <th class="gender-header">M</th>
                                    <th class="gender-header">F</th>
                                    <th class="gender-header">T</th>
                                    <!-- Failure Rate -->
                                    <th class="gender-header">M</th>
                                    <th class="gender-header">F</th>
                                    <th class="gender-header">T</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- JCE Results Row -->
                                <tr class="table-light">
                                    <td class="exam-type jce-header">JCE Results</td>

                                    <!-- Merit Grade (JCE only) -->
                                    <td class="male-cell grade-merit">{{ $class_info['jce_grade_analysis']['Merit']['M'] }}
                                    </td>
                                    <td class="female-cell grade-merit">
                                        {{ $class_info['jce_grade_analysis']['Merit']['F'] }}</td>
                                    <td class="total-cell grade-merit">
                                        {{ $class_info['jce_grade_analysis']['Merit']['T'] }}</td>

                                    <!-- A Grade -->
                                    <td class="male-cell grade-a">{{ $class_info['jce_grade_analysis']['A']['M'] }}</td>
                                    <td class="female-cell grade-a">{{ $class_info['jce_grade_analysis']['A']['F'] }}</td>
                                    <td class="total-cell grade-a">{{ $class_info['jce_grade_analysis']['A']['T'] }}</td>

                                    <!-- Grade B -->
                                    <td class="male-cell grade-b">{{ $class_info['jce_grade_analysis']['B']['M'] }}</td>
                                    <td class="female-cell grade-b">{{ $class_info['jce_grade_analysis']['B']['F'] }}</td>
                                    <td class="total-cell grade-b">{{ $class_info['jce_grade_analysis']['B']['T'] }}</td>

                                    <!-- Grade C -->
                                    <td class="male-cell grade-c">{{ $class_info['jce_grade_analysis']['C']['M'] }}</td>
                                    <td class="female-cell grade-c">{{ $class_info['jce_grade_analysis']['C']['F'] }}</td>
                                    <td class="total-cell grade-c">{{ $class_info['jce_grade_analysis']['C']['T'] }}</td>

                                    <!-- Grade D -->
                                    <td class="male-cell grade-d">{{ $class_info['jce_grade_analysis']['D']['M'] }}</td>
                                    <td class="female-cell grade-d">{{ $class_info['jce_grade_analysis']['D']['F'] }}</td>
                                    <td class="total-cell grade-d">{{ $class_info['jce_grade_analysis']['D']['T'] }}</td>

                                    <!-- Grade E -->
                                    <td class="male-cell grade-e">{{ $class_info['jce_grade_analysis']['E']['M'] }}</td>
                                    <td class="female-cell grade-e">{{ $class_info['jce_grade_analysis']['E']['F'] }}</td>
                                    <td class="total-cell grade-e">{{ $class_info['jce_grade_analysis']['E']['T'] }}</td>

                                    <!-- Grade U -->
                                    <td class="male-cell grade-u">{{ $class_info['jce_grade_analysis']['U']['M'] }}</td>
                                    <td class="female-cell grade-u">{{ $class_info['jce_grade_analysis']['U']['F'] }}</td>
                                    <td class="total-cell grade-u">{{ $class_info['jce_grade_analysis']['U']['T'] }}</td>

                                    <!-- High Achievement -->
                                    <td
                                        class="male-cell
                                        @if ($class_info['jce_categories']['High_Achievement']['M'] >= 40) percent-excellent
                                        @elseif($class_info['jce_categories']['High_Achievement']['M'] >= 25) percent-good
                                        @elseif($class_info['jce_categories']['High_Achievement']['M'] >= 15) percent-fair
                                        @else percent-poor @endif
                                    ">
                                        {{ $class_info['jce_categories']['High_Achievement']['M'] }}%</td>
                                    <td
                                        class="female-cell
                                        @if ($class_info['jce_categories']['High_Achievement']['F'] >= 40) percent-excellent
                                        @elseif($class_info['jce_categories']['High_Achievement']['F'] >= 25) percent-good
                                        @elseif($class_info['jce_categories']['High_Achievement']['F'] >= 15) percent-fair
                                        @else percent-poor @endif
                                    ">
                                        {{ $class_info['jce_categories']['High_Achievement']['F'] }}%</td>
                                    <td
                                        class="total-cell
                                        @if ($class_info['jce_categories']['High_Achievement']['T'] >= 40) percent-excellent
                                        @elseif($class_info['jce_categories']['High_Achievement']['T'] >= 25) percent-good
                                        @elseif($class_info['jce_categories']['High_Achievement']['T'] >= 15) percent-fair
                                        @else percent-poor @endif
                                    ">
                                        {{ $class_info['jce_categories']['High_Achievement']['T'] }}%</td>

                                    <!-- Pass Rate -->
                                    <td
                                        class="male-cell
                                        @if ($class_info['jce_categories']['Pass_Rate']['M'] >= 80) percent-excellent
                                        @elseif($class_info['jce_categories']['Pass_Rate']['M'] >= 65) percent-good
                                        @elseif($class_info['jce_categories']['Pass_Rate']['M'] >= 50) percent-fair
                                        @else percent-poor @endif
                                    ">
                                        {{ $class_info['jce_categories']['Pass_Rate']['M'] }}%</td>
                                    <td
                                        class="female-cell
                                        @if ($class_info['jce_categories']['Pass_Rate']['F'] >= 80) percent-excellent
                                        @elseif($class_info['jce_categories']['Pass_Rate']['F'] >= 65) percent-good
                                        @elseif($class_info['jce_categories']['Pass_Rate']['F'] >= 50) percent-fair
                                        @else percent-poor @endif
                                    ">
                                        {{ $class_info['jce_categories']['Pass_Rate']['F'] }}%</td>
                                    <td
                                        class="total-cell
                                        @if ($class_info['jce_categories']['Pass_Rate']['T'] >= 80) percent-excellent
                                        @elseif($class_info['jce_categories']['Pass_Rate']['T'] >= 65) percent-good
                                        @elseif($class_info['jce_categories']['Pass_Rate']['T'] >= 50) percent-fair
                                        @else percent-poor @endif
                                    ">
                                        {{ $class_info['jce_categories']['Pass_Rate']['T'] }}%</td>

                                    <!-- Failure Rate -->
                                    <td
                                        class="male-cell
                                        @if ($class_info['jce_categories']['Failure_Rate']['M'] <= 15) percent-excellent
                                        @elseif($class_info['jce_categories']['Failure_Rate']['M'] <= 25) percent-good
                                        @elseif($class_info['jce_categories']['Failure_Rate']['M'] <= 40) percent-fair
                                        @else percent-poor @endif
                                    ">
                                        {{ $class_info['jce_categories']['Failure_Rate']['M'] }}%</td>
                                    <td
                                        class="female-cell
                                        @if ($class_info['jce_categories']['Failure_Rate']['F'] <= 15) percent-excellent
                                        @elseif($class_info['jce_categories']['Failure_Rate']['F'] <= 25) percent-good
                                        @elseif($class_info['jce_categories']['Failure_Rate']['F'] <= 40) percent-fair
                                        @else percent-poor @endif
                                    ">
                                        {{ $class_info['jce_categories']['Failure_Rate']['F'] }}%</td>
                                    <td
                                        class="total-cell
                                        @if ($class_info['jce_categories']['Failure_Rate']['T'] <= 15) percent-excellent
                                        @elseif($class_info['jce_categories']['Failure_Rate']['T'] <= 25) percent-good
                                        @elseif($class_info['jce_categories']['Failure_Rate']['T'] <= 40) percent-fair
                                        @else percent-poor @endif
                                    ">
                                        {{ $class_info['jce_categories']['Failure_Rate']['T'] }}%</td>
                                </tr>

                                <!-- PSLE Results Row -->
                                <tr>
                                    <td class="exam-type psle-header">PSLE Results</td>

                                    <!-- No Merit in PSLE -->
                                    <td class="male-cell text-muted">-</td>
                                    <td class="female-cell text-muted">-</td>
                                    <td class="total-cell text-muted">-</td>

                                    <!-- Grade A -->
                                    <td class="male-cell grade-a">{{ $class_info['psle_grade_analysis']['A']['M'] }}</td>
                                    <td class="female-cell grade-a">{{ $class_info['psle_grade_analysis']['A']['F'] }}
                                    </td>
                                    <td class="total-cell grade-a">{{ $class_info['psle_grade_analysis']['A']['T'] }}</td>

                                    <!-- Grade B -->
                                    <td class="male-cell grade-b">{{ $class_info['psle_grade_analysis']['B']['M'] }}</td>
                                    <td class="female-cell grade-b">{{ $class_info['psle_grade_analysis']['B']['F'] }}
                                    </td>
                                    <td class="total-cell grade-b">{{ $class_info['psle_grade_analysis']['B']['T'] }}</td>

                                    <!-- Grade C -->
                                    <td class="male-cell grade-c">{{ $class_info['psle_grade_analysis']['C']['M'] }}</td>
                                    <td class="female-cell grade-c">{{ $class_info['psle_grade_analysis']['C']['F'] }}
                                    </td>
                                    <td class="total-cell grade-c">{{ $class_info['psle_grade_analysis']['C']['T'] }}</td>

                                    <!-- Grade D -->
                                    <td class="male-cell grade-d">{{ $class_info['psle_grade_analysis']['D']['M'] }}</td>
                                    <td class="female-cell grade-d">{{ $class_info['psle_grade_analysis']['D']['F'] }}
                                    </td>
                                    <td class="total-cell grade-d">{{ $class_info['psle_grade_analysis']['D']['T'] }}</td>

                                    <!-- Grade E -->
                                    <td class="male-cell grade-e">{{ $class_info['psle_grade_analysis']['E']['M'] }}</td>
                                    <td class="female-cell grade-e">{{ $class_info['psle_grade_analysis']['E']['F'] }}
                                    </td>
                                    <td class="total-cell grade-e">{{ $class_info['psle_grade_analysis']['E']['T'] }}</td>

                                    <!-- No U grade in PSLE -->
                                    <td class="male-cell text-muted">-</td>
                                    <td class="female-cell text-muted">-</td>
                                    <td class="total-cell text-muted">-</td>

                                    <!-- High Achievement -->
                                    <td
                                        class="male-cell
                                        @if ($class_info['psle_categories']['High_Achievement']['M'] >= 40) percent-excellent
                                        @elseif($class_info['psle_categories']['High_Achievement']['M'] >= 25) percent-good
                                        @elseif($class_info['psle_categories']['High_Achievement']['M'] >= 15) percent-fair
                                        @else percent-poor @endif
                                    ">
                                        {{ $class_info['psle_categories']['High_Achievement']['M'] }}%</td>
                                    <td
                                        class="female-cell
                                        @if ($class_info['psle_categories']['High_Achievement']['F'] >= 40) percent-excellent
                                        @elseif($class_info['psle_categories']['High_Achievement']['F'] >= 25) percent-good
                                        @elseif($class_info['psle_categories']['High_Achievement']['F'] >= 15) percent-fair
                                        @else percent-poor @endif
                                    ">
                                        {{ $class_info['psle_categories']['High_Achievement']['F'] }}%</td>
                                    <td
                                        class="total-cell
                                        @if ($class_info['psle_categories']['High_Achievement']['T'] >= 40) percent-excellent
                                        @elseif($class_info['psle_categories']['High_Achievement']['T'] >= 25) percent-good
                                        @elseif($class_info['psle_categories']['High_Achievement']['T'] >= 15) percent-fair
                                        @else percent-poor @endif
                                    ">
                                        {{ $class_info['psle_categories']['High_Achievement']['T'] }}%</td>

                                    <!-- Pass Rate -->
                                    <td
                                        class="male-cell
                                        @if ($class_info['psle_categories']['Pass_Rate']['M'] >= 80) percent-excellent
                                        @elseif($class_info['psle_categories']['Pass_Rate']['M'] >= 65) percent-good
                                        @elseif($class_info['psle_categories']['Pass_Rate']['M'] >= 50) percent-fair
                                        @else percent-poor @endif
                                    ">
                                        {{ $class_info['psle_categories']['Pass_Rate']['M'] }}%</td>
                                    <td
                                        class="female-cell
                                        @if ($class_info['psle_categories']['Pass_Rate']['F'] >= 80) percent-excellent
                                        @elseif($class_info['psle_categories']['Pass_Rate']['F'] >= 65) percent-good
                                        @elseif($class_info['psle_categories']['Pass_Rate']['F'] >= 50) percent-fair
                                        @else percent-poor @endif
                                    ">
                                        {{ $class_info['psle_categories']['Pass_Rate']['F'] }}%</td>
                                    <td
                                        class="total-cell
                                        @if ($class_info['psle_categories']['Pass_Rate']['T'] >= 80) percent-excellent
                                        @elseif($class_info['psle_categories']['Pass_Rate']['T'] >= 65) percent-good
                                        @elseif($class_info['psle_categories']['Pass_Rate']['T'] >= 50) percent-fair
                                        @else percent-poor @endif
                                    ">
                                        {{ $class_info['psle_categories']['Pass_Rate']['T'] }}%</td>

                                    <!-- Failure Rate -->
                                    <td
                                        class="male-cell
                                        @if ($class_info['psle_categories']['Failure_Rate']['M'] <= 15) percent-excellent
                                        @elseif($class_info['psle_categories']['Failure_Rate']['M'] <= 25) percent-good
                                        @elseif($class_info['psle_categories']['Failure_Rate']['M'] <= 40) percent-fair
                                        @else percent-poor @endif
                                    ">
                                        {{ $class_info['psle_categories']['Failure_Rate']['M'] }}%</td>
                                    <td
                                        class="female-cell
                                        @if ($class_info['psle_categories']['Failure_Rate']['F'] <= 15) percent-excellent
                                        @elseif($class_info['psle_categories']['Failure_Rate']['F'] <= 25) percent-good
                                        @elseif($class_info['psle_categories']['Failure_Rate']['F'] <= 40) percent-fair
                                        @else percent-poor @endif
                                    ">
                                        {{ $class_info['psle_categories']['Failure_Rate']['F'] }}%</td>
                                    <td
                                        class="total-cell
                                        @if ($class_info['psle_categories']['Failure_Rate']['T'] <= 15) percent-excellent
                                        @elseif($class_info['psle_categories']['Failure_Rate']['T'] <= 25) percent-good
                                        @elseif($class_info['psle_categories']['Failure_Rate']['T'] <= 40) percent-fair
                                        @else percent-poor @endif
                                    ">
                                        {{ $class_info['psle_categories']['Failure_Rate']['T'] }}%</td>
                                </tr>

                                <!-- Performance Change Row -->
                                <tr class="table-success">
                                    <td class="exam-type" style="background-color: #f8f9fa; font-weight: bold;">
                                        Performance Change</td>

                                    <!-- Merit Change - not applicable since PSLE doesn't have Merit -->
                                    <td class="text-muted">-</td>
                                    <td class="text-muted">-</td>
                                    <td class="text-muted">-</td>

                                    <!-- A Grade Change - not applicable for individual grades -->
                                    <td class="text-muted">-</td>
                                    <td class="text-muted">-</td>
                                    <td class="text-muted">-</td>

                                    <!-- Grade B Change - not applicable -->
                                    <td class="text-muted">-</td>
                                    <td class="text-muted">-</td>
                                    <td class="text-muted">-</td>

                                    <!-- Grade C Change - not applicable -->
                                    <td class="text-muted">-</td>
                                    <td class="text-muted">-</td>
                                    <td class="text-muted">-</td>

                                    <!-- Grade D Change - not applicable -->
                                    <td class="text-muted">-</td>
                                    <td class="text-muted">-</td>
                                    <td class="text-muted">-</td>

                                    <!-- Grade E Change - not applicable -->
                                    <td class="text-muted">-</td>
                                    <td class="text-muted">-</td>
                                    <td class="text-muted">-</td>

                                    <!-- Grade U Change - not applicable -->
                                    <td class="text-muted">-</td>
                                    <td class="text-muted">-</td>
                                    <td class="text-muted">-</td>

                                    <!-- High Achievement Change -->
                                    <td
                                        class="{{ $class_info['performance_comparison']['High_Achievement']['M'] > 0 ? 'improvement' : ($class_info['performance_comparison']['High_Achievement']['M'] < 0 ? 'decline' : 'stable') }}">
                                        {{ $class_info['performance_comparison']['High_Achievement']['M'] > 0 ? '+' : '' }}{{ $class_info['performance_comparison']['High_Achievement']['M'] }}%
                                    </td>
                                    <td
                                        class="{{ $class_info['performance_comparison']['High_Achievement']['F'] > 0 ? 'improvement' : ($class_info['performance_comparison']['High_Achievement']['F'] < 0 ? 'decline' : 'stable') }}">
                                        {{ $class_info['performance_comparison']['High_Achievement']['F'] > 0 ? '+' : '' }}{{ $class_info['performance_comparison']['High_Achievement']['F'] }}%
                                    </td>
                                    <td
                                        class="{{ $class_info['performance_comparison']['High_Achievement']['T'] > 0 ? 'improvement' : ($class_info['performance_comparison']['High_Achievement']['T'] < 0 ? 'decline' : 'stable') }}">
                                        {{ $class_info['performance_comparison']['High_Achievement']['T'] > 0 ? '+' : '' }}{{ $class_info['performance_comparison']['High_Achievement']['T'] }}%
                                    </td>

                                    <!-- Pass Rate Change -->
                                    <td
                                        class="{{ $class_info['performance_comparison']['Pass_Rate']['M'] > 0 ? 'improvement' : ($class_info['performance_comparison']['Pass_Rate']['M'] < 0 ? 'decline' : 'stable') }}">
                                        {{ $class_info['performance_comparison']['Pass_Rate']['M'] > 0 ? '+' : '' }}{{ $class_info['performance_comparison']['Pass_Rate']['M'] }}%
                                    </td>
                                    <td
                                        class="{{ $class_info['performance_comparison']['Pass_Rate']['F'] > 0 ? 'improvement' : ($class_info['performance_comparison']['Pass_Rate']['F'] < 0 ? 'decline' : 'stable') }}">
                                        {{ $class_info['performance_comparison']['Pass_Rate']['F'] > 0 ? '+' : '' }}{{ $class_info['performance_comparison']['Pass_Rate']['F'] }}%
                                    </td>
                                    <td
                                        class="{{ $class_info['performance_comparison']['Pass_Rate']['T'] > 0 ? 'improvement' : ($class_info['performance_comparison']['Pass_Rate']['T'] < 0 ? 'decline' : 'stable') }}">
                                        {{ $class_info['performance_comparison']['Pass_Rate']['T'] > 0 ? '+' : '' }}{{ $class_info['performance_comparison']['Pass_Rate']['T'] }}%
                                    </td>

                                    <!-- Failure Rate Change -->
                                    <td
                                        class="{{ $class_info['performance_comparison']['Failure_Rate']['M'] < 0 ? 'improvement' : ($class_info['performance_comparison']['Failure_Rate']['M'] > 0 ? 'decline' : 'stable') }}">
                                        {{ $class_info['performance_comparison']['Failure_Rate']['M'] > 0 ? '+' : '' }}{{ $class_info['performance_comparison']['Failure_Rate']['M'] }}%
                                    </td>
                                    <td
                                        class="{{ $class_info['performance_comparison']['Failure_Rate']['F'] < 0 ? 'improvement' : ($class_info['performance_comparison']['Failure_Rate']['F'] > 0 ? 'decline' : 'stable') }}">
                                        {{ $class_info['performance_comparison']['Failure_Rate']['F'] > 0 ? '+' : '' }}{{ $class_info['performance_comparison']['Failure_Rate']['F'] }}%
                                    </td>
                                    <td
                                        class="{{ $class_info['performance_comparison']['Failure_Rate']['T'] < 0 ? 'improvement' : ($class_info['performance_comparison']['Failure_Rate']['T'] > 0 ? 'decline' : 'stable') }}">
                                        {{ $class_info['performance_comparison']['Failure_Rate']['T'] > 0 ? '+' : '' }}{{ $class_info['performance_comparison']['Failure_Rate']['T'] }}%
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Performance Analysis Summary -->
                    <div class="mt-4">
                        <h6 class="text-start mb-3">Performance Analysis Summary</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="summary-box">
                                    <h6>Performance Trends</h6>
                                    @php
                                        $haChange = $class_info['performance_comparison']['High_Achievement']['T'];
                                        $prChange = $class_info['performance_comparison']['Pass_Rate']['T'];
                                        $frChange = $class_info['performance_comparison']['Failure_Rate']['T'];

                                        if ($haChange > 0 && $prChange > 0 && $frChange < 0) {
                                            $overallTrend = 'Significantly Improved';
                                            $trendClass = 'improvement';
                                        } elseif ($haChange < 0 && $prChange < 0 && $frChange > 0) {
                                            $overallTrend = 'Significantly Declined';
                                            $trendClass = 'decline';
                                        } elseif ($haChange >= 0 && $prChange >= 0) {
                                            $overallTrend = 'Mostly Improved';
                                            $trendClass = 'improvement';
                                        } elseif ($haChange <= 0 && $prChange <= 0) {
                                            $overallTrend = 'Mostly Declined';
                                            $trendClass = 'decline';
                                        } else {
                                            $overallTrend = 'Mixed Results';
                                            $trendClass = 'stable';
                                        }
                                    @endphp
                                    <p><strong>Overall Trend:</strong> <span
                                            class="{{ $trendClass }}">{{ $overallTrend }}</span></p>
                                    <p><strong>High Achievement Change:</strong>
                                        <span
                                            class="{{ $haChange > 0 ? 'improvement' : ($haChange < 0 ? 'decline' : 'stable') }}">
                                            {{ $haChange > 0 ? '+' : '' }}{{ $haChange }}%
                                        </span>
                                    </p>
                                    <p><strong>Pass Rate Change:</strong>
                                        <span
                                            class="{{ $prChange > 0 ? 'improvement' : ($prChange < 0 ? 'decline' : 'stable') }}">
                                            {{ $prChange > 0 ? '+' : '' }}{{ $prChange }}%
                                        </span>
                                    </p>
                                    <p><strong>Failure Rate Change:</strong>
                                        <span
                                            class="{{ $frChange < 0 ? 'improvement' : ($frChange > 0 ? 'decline' : 'stable') }}">
                                            {{ $frChange > 0 ? '+' : '' }}{{ $frChange }}%
                                        </span>
                                        <small class="text-muted">(Lower is better)</small>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="summary-box">
                                    <h6>Key Insights</h6>
                                    <ul class="list-unstyled">
                                        @if ($haChange > 5)
                                            <li class="improvement">✓ Strong improvement in high-achieving students</li>
                                        @elseif($haChange < -5)
                                            <li class="decline">⚠ Decline in high-achieving students needs attention</li>
                                        @endif

                                        @if ($prChange > 10)
                                            <li class="improvement">✓ Significant improvement in overall pass rate</li>
                                        @elseif($prChange < -10)
                                            <li class="decline">⚠ Concerning decline in pass rate</li>
                                        @endif

                                        @if ($frChange < -5)
                                            <li class="improvement">✓ Good reduction in failure rate</li>
                                        @elseif($frChange > 5)
                                            <li class="decline">⚠ Increase in failure rate requires intervention</li>
                                        @endif

                                        @if ($class_info['jce_categories']['Pass_Rate']['T'] >= 80)
                                            <li class="improvement">✓ Excellent current JCE pass rate
                                                ({{ $class_info['jce_categories']['Pass_Rate']['T'] }}%)</li>
                                        @elseif($class_info['jce_categories']['Pass_Rate']['T'] >= 65)
                                            <li class="percent-good">● Good current JCE pass rate
                                                ({{ $class_info['jce_categories']['Pass_Rate']['T'] }}%)</li>
                                        @else
                                            <li class="decline">⚠ JCE pass rate below target
                                                ({{ $class_info['jce_categories']['Pass_Rate']['T'] }}%)</li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Indicators Legend -->
                    <div class="mt-4">
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="text-start mb-3">Performance Indicators</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="summary-box">
                                            <strong>High Achievement:</strong><br>
                                            <small class="text-muted">
                                                JCE: Merit + A + B grades (Merit is highest)<br>
                                                PSLE: A + B grades (A is highest)<br>
                                                Target: ≥40% (Excellent), ≥25% (Good), ≥15% (Fair)
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="summary-box">
                                            <strong>Pass Rate:</strong><br>
                                            <small class="text-muted">
                                                JCE: Merit + A + B + C grades<br>
                                                PSLE: A + B + C grades<br>
                                                Target: ≥80% (Excellent), ≥65% (Good), ≥50% (Fair)
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="summary-box">
                                            <strong>Failure Rate:</strong><br>
                                            <small class="text-muted">
                                                JCE: D + E + U grades<br>
                                                PSLE: D + E grades<br>
                                                Target: ≤15% (Excellent), ≤25% (Good), ≤40% (Fair)
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="summary-box">
                                            <strong>Grade Distribution:</strong><br>
                                            <small class="text-muted">
                                                Merit and A are shown as separate columns for JCE<br>
                                                PSLE only has A, B, C, D, E grades (no Merit or U)<br>
                                                Performance categories combine grades appropriately
                                            </small>
                                            <br><br>
                                            <strong>Color Coding:</strong>
                                            <span class="percent-excellent">Excellent</span> |
                                            <span class="percent-good">Good</span> |
                                            <span class="percent-fair">Fair</span> |
                                            <span class="percent-poor">Needs Improvement</span>
                                            <br><br>
                                            <strong>Change Indicators:</strong>
                                            <span class="improvement">Improvement</span> |
                                            <span class="decline">Decline</span> |
                                            <span class="stable">No Change</span>
                                        </div>
                                    </div>
                                </div>
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
