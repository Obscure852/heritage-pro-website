@extends('layouts.master')
@section('title')
    School-wide JCE vs PSLE Performance Comparison
@endsection

@section('css')
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
        }

        .chart-container {
            position: relative;
            height: 400px;
            margin-bottom: 2rem;
        }

        .chart-container-small {
            position: relative;
            height: 300px;
            margin-bottom: 1.5rem;
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

        .summary-box {
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .statistics-card {
            background: #f8f9fa;
            color: #495057;
            border-radius: 3px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border: 1px solid #dee2e6;
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: bold;
            line-height: 1;
        }

        .stats-label {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-top: 0.5rem;
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

            .chart-container,
            .chart-container-small {
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
            JCE vs PSLE Performance Comparison
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
                        JCE vs PSLE Performance Comparison - {{ $year }}
                    </h6>
                    <!-- School Summary -->
                    <div class="summary-box">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Academic Year:</strong> {{ $year }}<br>
                                <strong>Total Students with Both Results:</strong> {{ $students_analyzed }}<br>
                                <strong>Total Classes Analyzed:</strong> {{ $school_info['total_classes_analyzed'] }}
                            </div>
                            <div class="col-md-6">
                                <strong>Male Students:</strong> {{ $school_info['gender_totals']['M'] }}
                                ({{ round(($school_info['gender_totals']['M'] / $school_info['gender_totals']['T']) * 100, 1) }}%)<br>
                                <strong>Female Students:</strong> {{ $school_info['gender_totals']['F'] }}
                                ({{ round(($school_info['gender_totals']['F'] / $school_info['gender_totals']['T']) * 100, 1) }}%)<br>
                                <strong>Report Generated:</strong> {{ $generated_at->format('d/m/Y H:i') }}
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
                                    <th colspan="3" class="performance-header">DEU%</th>
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
                                    <td class="male-cell grade-merit">
                                        {{ $school_info['jce_grade_analysis']['Merit']['M'] }}</td>
                                    <td class="female-cell grade-merit">
                                        {{ $school_info['jce_grade_analysis']['Merit']['F'] }}</td>
                                    <td class="total-cell grade-merit">
                                        {{ $school_info['jce_grade_analysis']['Merit']['T'] }}</td>

                                    <!-- A Grade -->
                                    <td class="male-cell grade-a">{{ $school_info['jce_grade_analysis']['A']['M'] }}</td>
                                    <td class="female-cell grade-a">{{ $school_info['jce_grade_analysis']['A']['F'] }}</td>
                                    <td class="total-cell grade-a">{{ $school_info['jce_grade_analysis']['A']['T'] }}</td>

                                    <!-- Grade B -->
                                    <td class="male-cell grade-b">{{ $school_info['jce_grade_analysis']['B']['M'] }}</td>
                                    <td class="female-cell grade-b">{{ $school_info['jce_grade_analysis']['B']['F'] }}</td>
                                    <td class="total-cell grade-b">{{ $school_info['jce_grade_analysis']['B']['T'] }}</td>

                                    <!-- Grade C -->
                                    <td class="male-cell grade-c">{{ $school_info['jce_grade_analysis']['C']['M'] }}</td>
                                    <td class="female-cell grade-c">{{ $school_info['jce_grade_analysis']['C']['F'] }}</td>
                                    <td class="total-cell grade-c">{{ $school_info['jce_grade_analysis']['C']['T'] }}</td>

                                    <!-- Grade D -->
                                    <td class="male-cell grade-d">{{ $school_info['jce_grade_analysis']['D']['M'] }}</td>
                                    <td class="female-cell grade-d">{{ $school_info['jce_grade_analysis']['D']['F'] }}
                                    </td>
                                    <td class="total-cell grade-d">{{ $school_info['jce_grade_analysis']['D']['T'] }}</td>

                                    <!-- Grade E -->
                                    <td class="male-cell grade-e">{{ $school_info['jce_grade_analysis']['E']['M'] }}</td>
                                    <td class="female-cell grade-e">{{ $school_info['jce_grade_analysis']['E']['F'] }}
                                    </td>
                                    <td class="total-cell grade-e">{{ $school_info['jce_grade_analysis']['E']['T'] }}</td>

                                    <!-- Grade U -->
                                    <td class="male-cell grade-u">{{ $school_info['jce_grade_analysis']['U']['M'] }}</td>
                                    <td class="female-cell grade-u">{{ $school_info['jce_grade_analysis']['U']['F'] }}
                                    </td>
                                    <td class="total-cell grade-u">{{ $school_info['jce_grade_analysis']['U']['T'] }}</td>

                                    <!-- High Achievement -->
                                    <td
                                        class="male-cell
                                        @if ($school_info['jce_categories']['High_Achievement']['M'] >= 40) percent-excellent
                                        @elseif($school_info['jce_categories']['High_Achievement']['M'] >= 25) percent-good
                                        @elseif($school_info['jce_categories']['High_Achievement']['M'] >= 15) percent-fair
                                        @else percent-poor @endif
                                    ">
                                        {{ $school_info['jce_categories']['High_Achievement']['M'] }}%</td>
                                    <td
                                        class="female-cell
                                        @if ($school_info['jce_categories']['High_Achievement']['F'] >= 40) percent-excellent
                                        @elseif($school_info['jce_categories']['High_Achievement']['F'] >= 25) percent-good
                                        @elseif($school_info['jce_categories']['High_Achievement']['F'] >= 15) percent-fair
                                        @else percent-poor @endif
                                    ">
                                        {{ $school_info['jce_categories']['High_Achievement']['F'] }}%</td>
                                    <td
                                        class="total-cell
                                        @if ($school_info['jce_categories']['High_Achievement']['T'] >= 40) percent-excellent
                                        @elseif($school_info['jce_categories']['High_Achievement']['T'] >= 25) percent-good
                                        @elseif($school_info['jce_categories']['High_Achievement']['T'] >= 15) percent-fair
                                        @else percent-poor @endif
                                    ">
                                        {{ $school_info['jce_categories']['High_Achievement']['T'] }}%</td>

                                    <!-- Pass Rate -->
                                    <td
                                        class="male-cell
                                        @if ($school_info['jce_categories']['Pass_Rate']['M'] >= 80) percent-excellent
                                        @elseif($school_info['jce_categories']['Pass_Rate']['M'] >= 65) percent-good
                                        @elseif($school_info['jce_categories']['Pass_Rate']['M'] >= 50) percent-fair
                                        @else percent-poor @endif
                                    ">
                                        {{ $school_info['jce_categories']['Pass_Rate']['M'] }}%</td>
                                    <td
                                        class="female-cell
                                        @if ($school_info['jce_categories']['Pass_Rate']['F'] >= 80) percent-excellent
                                        @elseif($school_info['jce_categories']['Pass_Rate']['F'] >= 65) percent-good
                                        @elseif($school_info['jce_categories']['Pass_Rate']['F'] >= 50) percent-fair
                                        @else percent-poor @endif
                                    ">
                                        {{ $school_info['jce_categories']['Pass_Rate']['F'] }}%</td>
                                    <td
                                        class="total-cell
                                        @if ($school_info['jce_categories']['Pass_Rate']['T'] >= 80) percent-excellent
                                        @elseif($school_info['jce_categories']['Pass_Rate']['T'] >= 65) percent-good
                                        @elseif($school_info['jce_categories']['Pass_Rate']['T'] >= 50) percent-fair
                                        @else percent-poor @endif
                                    ">
                                        {{ $school_info['jce_categories']['Pass_Rate']['T'] }}%</td>

                                    <!-- Failure Rate -->
                                    <td
                                        class="male-cell
                                        @if ($school_info['jce_categories']['Failure_Rate']['M'] <= 15) percent-excellent
                                        @elseif($school_info['jce_categories']['Failure_Rate']['M'] <= 25) percent-good
                                        @elseif($school_info['jce_categories']['Failure_Rate']['M'] <= 40) percent-fair
                                        @else percent-poor @endif
                                    ">
                                        {{ $school_info['jce_categories']['Failure_Rate']['M'] }}%</td>
                                    <td
                                        class="female-cell
                                        @if ($school_info['jce_categories']['Failure_Rate']['F'] <= 15) percent-excellent
                                        @elseif($school_info['jce_categories']['Failure_Rate']['F'] <= 25) percent-good
                                        @elseif($school_info['jce_categories']['Failure_Rate']['F'] <= 40) percent-fair
                                        @else percent-poor @endif
                                    ">
                                        {{ $school_info['jce_categories']['Failure_Rate']['F'] }}%</td>
                                    <td
                                        class="total-cell
                                        @if ($school_info['jce_categories']['Failure_Rate']['T'] <= 15) percent-excellent
                                        @elseif($school_info['jce_categories']['Failure_Rate']['T'] <= 25) percent-good
                                        @elseif($school_info['jce_categories']['Failure_Rate']['T'] <= 40) percent-fair
                                        @else percent-poor @endif
                                    ">
                                        {{ $school_info['jce_categories']['Failure_Rate']['T'] }}%</td>
                                </tr>

                                <!-- PSLE Results Row -->
                                <tr>
                                    <td class="exam-type psle-header">PSLE Results</td>

                                    <!-- No Merit in PSLE -->
                                    <td class="male-cell text-muted">-</td>
                                    <td class="female-cell text-muted">-</td>
                                    <td class="total-cell text-muted">-</td>

                                    <!-- Grade A -->
                                    <td class="male-cell grade-a">{{ $school_info['psle_grade_analysis']['A']['M'] }}</td>
                                    <td class="female-cell grade-a">{{ $school_info['psle_grade_analysis']['A']['F'] }}
                                    </td>
                                    <td class="total-cell grade-a">{{ $school_info['psle_grade_analysis']['A']['T'] }}
                                    </td>

                                    <!-- Grade B -->
                                    <td class="male-cell grade-b">{{ $school_info['psle_grade_analysis']['B']['M'] }}</td>
                                    <td class="female-cell grade-b">{{ $school_info['psle_grade_analysis']['B']['F'] }}
                                    </td>
                                    <td class="total-cell grade-b">{{ $school_info['psle_grade_analysis']['B']['T'] }}
                                    </td>

                                    <!-- Grade C -->
                                    <td class="male-cell grade-c">{{ $school_info['psle_grade_analysis']['C']['M'] }}</td>
                                    <td class="female-cell grade-c">{{ $school_info['psle_grade_analysis']['C']['F'] }}
                                    </td>
                                    <td class="total-cell grade-c">{{ $school_info['psle_grade_analysis']['C']['T'] }}
                                    </td>

                                    <!-- Grade D -->
                                    <td class="male-cell grade-d">{{ $school_info['psle_grade_analysis']['D']['M'] }}</td>
                                    <td class="female-cell grade-d">{{ $school_info['psle_grade_analysis']['D']['F'] }}
                                    </td>
                                    <td class="total-cell grade-d">{{ $school_info['psle_grade_analysis']['D']['T'] }}
                                    </td>

                                    <!-- Grade E -->
                                    <td class="male-cell grade-e">{{ $school_info['psle_grade_analysis']['E']['M'] }}</td>
                                    <td class="female-cell grade-e">{{ $school_info['psle_grade_analysis']['E']['F'] }}
                                    </td>
                                    <td class="total-cell grade-e">{{ $school_info['psle_grade_analysis']['E']['T'] }}
                                    </td>

                                    <!-- No U grade in PSLE -->
                                    <td class="male-cell text-muted">-</td>
                                    <td class="female-cell text-muted">-</td>
                                    <td class="total-cell text-muted">-</td>

                                    <!-- High Achievement -->
                                    <td
                                        class="male-cell
                                        @if ($school_info['psle_categories']['High_Achievement']['M'] >= 40) percent-excellent
                                        @elseif($school_info['psle_categories']['High_Achievement']['M'] >= 25) percent-good
                                        @elseif($school_info['psle_categories']['High_Achievement']['M'] >= 15) percent-fair
                                        @else percent-poor @endif
                                    ">
                                        {{ $school_info['psle_categories']['High_Achievement']['M'] }}%</td>
                                    <td
                                        class="female-cell
                                        @if ($school_info['psle_categories']['High_Achievement']['F'] >= 40) percent-excellent
                                        @elseif($school_info['psle_categories']['High_Achievement']['F'] >= 25) percent-good
                                        @elseif($school_info['psle_categories']['High_Achievement']['F'] >= 15) percent-fair
                                        @else percent-poor @endif
                                    ">
                                        {{ $school_info['psle_categories']['High_Achievement']['F'] }}%</td>
                                    <td
                                        class="total-cell
                                        @if ($school_info['psle_categories']['High_Achievement']['T'] >= 40) percent-excellent
                                        @elseif($school_info['psle_categories']['High_Achievement']['T'] >= 25) percent-good
                                        @elseif($school_info['psle_categories']['High_Achievement']['T'] >= 15) percent-fair
                                        @else percent-poor @endif
                                    ">
                                        {{ $school_info['psle_categories']['High_Achievement']['T'] }}%</td>

                                    <!-- Pass Rate -->
                                    <td
                                        class="male-cell
                                        @if ($school_info['psle_categories']['Pass_Rate']['M'] >= 80) percent-excellent
                                        @elseif($school_info['psle_categories']['Pass_Rate']['M'] >= 65) percent-good
                                        @elseif($school_info['psle_categories']['Pass_Rate']['M'] >= 50) percent-fair
                                        @else percent-poor @endif
                                    ">
                                        {{ $school_info['psle_categories']['Pass_Rate']['M'] }}%</td>
                                    <td
                                        class="female-cell
                                        @if ($school_info['psle_categories']['Pass_Rate']['F'] >= 80) percent-excellent
                                        @elseif($school_info['psle_categories']['Pass_Rate']['F'] >= 65) percent-good
                                        @elseif($school_info['psle_categories']['Pass_Rate']['F'] >= 50) percent-fair
                                        @else percent-poor @endif
                                    ">
                                        {{ $school_info['psle_categories']['Pass_Rate']['F'] }}%</td>
                                    <td
                                        class="total-cell
                                        @if ($school_info['psle_categories']['Pass_Rate']['T'] >= 80) percent-excellent
                                        @elseif($school_info['psle_categories']['Pass_Rate']['T'] >= 65) percent-good
                                        @elseif($school_info['psle_categories']['Pass_Rate']['T'] >= 50) percent-fair
                                        @else percent-poor @endif
                                    ">
                                        {{ $school_info['psle_categories']['Pass_Rate']['T'] }}%</td>

                                    <!-- Failure Rate -->
                                    <td
                                        class="male-cell
                                        @if ($school_info['psle_categories']['Failure_Rate']['M'] <= 15) percent-excellent
                                        @elseif($school_info['psle_categories']['Failure_Rate']['M'] <= 25) percent-good
                                        @elseif($school_info['psle_categories']['Failure_Rate']['M'] <= 40) percent-fair
                                        @else percent-poor @endif
                                    ">
                                        {{ $school_info['psle_categories']['Failure_Rate']['M'] }}%</td>
                                    <td
                                        class="female-cell
                                        @if ($school_info['psle_categories']['Failure_Rate']['F'] <= 15) percent-excellent
                                        @elseif($school_info['psle_categories']['Failure_Rate']['F'] <= 25) percent-good
                                        @elseif($school_info['psle_categories']['Failure_Rate']['F'] <= 40) percent-fair
                                        @else percent-poor @endif
                                    ">
                                        {{ $school_info['psle_categories']['Failure_Rate']['F'] }}%</td>
                                    <td
                                        class="total-cell
                                        @if ($school_info['psle_categories']['Failure_Rate']['T'] <= 15) percent-excellent
                                        @elseif($school_info['psle_categories']['Failure_Rate']['T'] <= 25) percent-good
                                        @elseif($school_info['psle_categories']['Failure_Rate']['T'] <= 40) percent-fair
                                        @else percent-poor @endif
                                    ">
                                        {{ $school_info['psle_categories']['Failure_Rate']['T'] }}%</td>
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
                                        class="{{ $school_info['performance_comparison']['High_Achievement']['M'] > 0 ? 'improvement' : ($school_info['performance_comparison']['High_Achievement']['M'] < 0 ? 'decline' : 'stable') }}">
                                        {{ $school_info['performance_comparison']['High_Achievement']['M'] > 0 ? '+' : '' }}{{ $school_info['performance_comparison']['High_Achievement']['M'] }}%
                                    </td>
                                    <td
                                        class="{{ $school_info['performance_comparison']['High_Achievement']['F'] > 0 ? 'improvement' : ($school_info['performance_comparison']['High_Achievement']['F'] < 0 ? 'decline' : 'stable') }}">
                                        {{ $school_info['performance_comparison']['High_Achievement']['F'] > 0 ? '+' : '' }}{{ $school_info['performance_comparison']['High_Achievement']['F'] }}%
                                    </td>
                                    <td
                                        class="{{ $school_info['performance_comparison']['High_Achievement']['T'] > 0 ? 'improvement' : ($school_info['performance_comparison']['High_Achievement']['T'] < 0 ? 'decline' : 'stable') }}">
                                        {{ $school_info['performance_comparison']['High_Achievement']['T'] > 0 ? '+' : '' }}{{ $school_info['performance_comparison']['High_Achievement']['T'] }}%
                                    </td>

                                    <!-- Pass Rate Change -->
                                    <td
                                        class="{{ $school_info['performance_comparison']['Pass_Rate']['M'] > 0 ? 'improvement' : ($school_info['performance_comparison']['Pass_Rate']['M'] < 0 ? 'decline' : 'stable') }}">
                                        {{ $school_info['performance_comparison']['Pass_Rate']['M'] > 0 ? '+' : '' }}{{ $school_info['performance_comparison']['Pass_Rate']['M'] }}%
                                    </td>
                                    <td
                                        class="{{ $school_info['performance_comparison']['Pass_Rate']['F'] > 0 ? 'improvement' : ($school_info['performance_comparison']['Pass_Rate']['F'] < 0 ? 'decline' : 'stable') }}">
                                        {{ $school_info['performance_comparison']['Pass_Rate']['F'] > 0 ? '+' : '' }}{{ $school_info['performance_comparison']['Pass_Rate']['F'] }}%
                                    </td>
                                    <td
                                        class="{{ $school_info['performance_comparison']['Pass_Rate']['T'] > 0 ? 'improvement' : ($school_info['performance_comparison']['Pass_Rate']['T'] < 0 ? 'decline' : 'stable') }}">
                                        {{ $school_info['performance_comparison']['Pass_Rate']['T'] > 0 ? '+' : '' }}{{ $school_info['performance_comparison']['Pass_Rate']['T'] }}%
                                    </td>

                                    <!-- Failure Rate Change -->
                                    <td
                                        class="{{ $school_info['performance_comparison']['Failure_Rate']['M'] < 0 ? 'improvement' : ($school_info['performance_comparison']['Failure_Rate']['M'] > 0 ? 'decline' : 'stable') }}">
                                        {{ $school_info['performance_comparison']['Failure_Rate']['M'] > 0 ? '+' : '' }}{{ $school_info['performance_comparison']['Failure_Rate']['M'] }}%
                                    </td>
                                    <td
                                        class="{{ $school_info['performance_comparison']['Failure_Rate']['F'] < 0 ? 'improvement' : ($school_info['performance_comparison']['Failure_Rate']['F'] > 0 ? 'decline' : 'stable') }}">
                                        {{ $school_info['performance_comparison']['Failure_Rate']['F'] > 0 ? '+' : '' }}{{ $school_info['performance_comparison']['Failure_Rate']['F'] }}%
                                    </td>
                                    <td
                                        class="{{ $school_info['performance_comparison']['Failure_Rate']['T'] < 0 ? 'improvement' : ($school_info['performance_comparison']['Failure_Rate']['T'] > 0 ? 'decline' : 'stable') }}">
                                        {{ $school_info['performance_comparison']['Failure_Rate']['T'] > 0 ? '+' : '' }}{{ $school_info['performance_comparison']['Failure_Rate']['T'] }}%
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Class-by-Class Analysis Summary -->
                    @if (count($class_by_class_analysis) > 0)
                        <h6 class="text-start mb-3">Class-by-Class Performance Summary</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Class</th>
                                        <th>Teacher</th>
                                        <th>Students</th>
                                        <th>JCE Pass Rate</th>
                                        <th>PSLE Pass Rate</th>
                                        <th>Change</th>
                                        <th>JCE MAB%</th>
                                        <th>PSLE MAB%</th>
                                        <th>Change</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($class_by_class_analysis as $classData)
                                        <tr>
                                            <td><strong>{{ $classData['name'] }}</strong></td>
                                            <td>{{ $classData['teacher'] }}</td>
                                            <td>{{ $classData['total_students'] }}</td>
                                            <td
                                                class="
                                        @if ($classData['jce_categories']['Pass_Rate']['T'] >= 80) percent-excellent
                                        @elseif($classData['jce_categories']['Pass_Rate']['T'] >= 65) percent-good
                                        @elseif($classData['jce_categories']['Pass_Rate']['T'] >= 50) percent-fair
                                        @else percent-poor @endif
                                    ">
                                                {{ $classData['jce_categories']['Pass_Rate']['T'] }}%</td>
                                            <td
                                                class="
                                        @if ($classData['psle_categories']['Pass_Rate']['T'] >= 80) percent-excellent
                                        @elseif($classData['psle_categories']['Pass_Rate']['T'] >= 65) percent-good
                                        @elseif($classData['psle_categories']['Pass_Rate']['T'] >= 50) percent-fair
                                        @else percent-poor @endif
                                    ">
                                                {{ $classData['psle_categories']['Pass_Rate']['T'] }}%</td>
                                            <td
                                                class="{{ $classData['performance_comparison']['Pass_Rate']['T'] > 0 ? 'improvement' : ($classData['performance_comparison']['Pass_Rate']['T'] < 0 ? 'decline' : 'stable') }}">
                                                {{ $classData['performance_comparison']['Pass_Rate']['T'] > 0 ? '+' : '' }}{{ $classData['performance_comparison']['Pass_Rate']['T'] }}%
                                            </td>
                                            <td
                                                class="
                                        @if ($classData['jce_categories']['High_Achievement']['T'] >= 40) percent-excellent
                                        @elseif($classData['jce_categories']['High_Achievement']['T'] >= 25) percent-good
                                        @elseif($classData['jce_categories']['High_Achievement']['T'] >= 15) percent-fair
                                        @else percent-poor @endif
                                    ">
                                                {{ $classData['jce_categories']['High_Achievement']['T'] }}%</td>
                                            <td
                                                class="
                                        @if ($classData['psle_categories']['High_Achievement']['T'] >= 40) percent-excellent
                                        @elseif($classData['psle_categories']['High_Achievement']['T'] >= 25) percent-good
                                        @elseif($classData['psle_categories']['High_Achievement']['T'] >= 15) percent-fair
                                        @else percent-poor @endif
                                    ">
                                                {{ $classData['psle_categories']['High_Achievement']['T'] }}%</td>
                                            <td
                                                class="{{ $classData['performance_comparison']['High_Achievement']['T'] > 0 ? 'improvement' : ($classData['performance_comparison']['High_Achievement']['T'] < 0 ? 'decline' : 'stable') }}">
                                                {{ $classData['performance_comparison']['High_Achievement']['T'] > 0 ? '+' : '' }}{{ $classData['performance_comparison']['High_Achievement']['T'] }}%
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <!-- Charts Section -->
                    <div class="row no-print mt-5">
                        <div class="col-12">
                            <h5 class="text-start mb-4">Visual Analytics & Charts</h5>
                        </div>

                        <!-- Grade Distribution Comparison Chart -->
                        <div class="col-md-6 mb-4">
                            <h6 class="mb-3">Grade Distribution Comparison</h6>
                            <div class="chart-container">
                                <canvas id="gradeDistributionChart"></canvas>
                            </div>
                        </div>

                        <!-- Performance Categories Comparison Chart -->
                        <div class="col-md-6 mb-4">
                            <h6 class="mb-3">Performance Categories Comparison</h6>
                            <div class="chart-container">
                                <canvas id="performanceCategoriesChart"></canvas>
                            </div>
                        </div>

                        <!-- Gender Performance Comparison Chart -->
                        <div class="col-md-6 mb-4">
                            <h6 class="mb-3">Gender Performance Comparison</h6>
                            <div class="chart-container">
                                <canvas id="genderPerformanceChart"></canvas>
                            </div>
                        </div>

                        <!-- Class Performance Comparison Chart -->
                        <div class="col-md-6 mb-4">
                            <h6 class="mb-3">Class-by-Class Pass Rate Comparison</h6>
                            <div class="chart-container">
                                <canvas id="classPerformanceChart"></canvas>
                            </div>
                        </div>
                    </div>
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

        const chartData = @json($chart_data);
        const gradeDistCtx = document.getElementById('gradeDistributionChart').getContext('2d');
        new Chart(gradeDistCtx, {
            type: 'bar',
            data: {
                labels: chartData.grade_distribution.labels,
                datasets: [{
                    label: 'JCE Results',
                    data: chartData.grade_distribution.jce,
                    backgroundColor: '#28a745',
                    borderColor: '#1e7e34',
                    borderWidth: 1
                }, {
                    label: 'PSLE Results',
                    data: chartData.grade_distribution.psle,
                    backgroundColor: '#ffc107',
                    borderColor: '#e0a800',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Grade Distribution: JCE vs PSLE (Common Grades)'
                    },
                    legend: {
                        position: 'top',
                    }
                },
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
                            text: 'Grade'
                        }
                    }
                }
            }
        });

        // Performance Categories Comparison Chart
        const perfCatCtx = document.getElementById('performanceCategoriesChart').getContext('2d');
        new Chart(perfCatCtx, {
            type: 'radar',
            data: {
                labels: chartData.performance_categories.categories,
                datasets: [{
                    label: 'JCE Performance',
                    data: chartData.performance_categories.jce,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.2)',
                    pointBackgroundColor: '#28a745',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#28a745'
                }, {
                    label: 'PSLE Performance',
                    data: chartData.performance_categories.psle,
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.2)',
                    pointBackgroundColor: '#ffc107',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#ffc107'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Performance Categories Comparison (%)'
                    }
                },
                scales: {
                    r: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            stepSize: 20
                        }
                    }
                }
            }
        });

        // Gender Performance Comparison Chart
        const genderPerfCtx = document.getElementById('genderPerformanceChart').getContext('2d');
        new Chart(genderPerfCtx, {
            type: 'bar',
            data: {
                labels: chartData.gender_performance.categories,
                datasets: [{
                    label: 'JCE Male',
                    data: chartData.gender_performance.jce_male,
                    backgroundColor: '#007bff',
                    borderColor: '#0056b3',
                    borderWidth: 1
                }, {
                    label: 'JCE Female',
                    data: chartData.gender_performance.jce_female,
                    backgroundColor: '#e83e8c',
                    borderColor: '#c1406c',
                    borderWidth: 1
                }, {
                    label: 'PSLE Male',
                    data: chartData.gender_performance.psle_male,
                    backgroundColor: '#17a2b8',
                    borderColor: '#117a8b',
                    borderWidth: 1
                }, {
                    label: 'PSLE Female',
                    data: chartData.gender_performance.psle_female,
                    backgroundColor: '#fd7e14',
                    borderColor: '#e05d00',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Gender Performance Comparison (%)'
                    },
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Percentage'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Performance Category'
                        }
                    }
                }
            }
        });

        // Class Performance Comparison Chart
        const classPerfCtx = document.getElementById('classPerformanceChart').getContext('2d');
        new Chart(classPerfCtx, {
            type: 'line',
            data: {
                labels: chartData.class_performance.class_names,
                datasets: [{
                    label: 'JCE Pass Rate',
                    data: chartData.class_performance.jce_pass_rates,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.1,
                    fill: false
                }, {
                    label: 'PSLE Pass Rate',
                    data: chartData.class_performance.psle_pass_rates,
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    tension: 0.1,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Class-by-Class Pass Rate Comparison (%)'
                    },
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Pass Rate (%)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Class'
                        }
                    }
                }
            }
        });
    </script>
@endsection
