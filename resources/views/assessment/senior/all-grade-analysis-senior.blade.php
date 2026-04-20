@extends('layouts.master')
@section('title')
    Overall Grade Analysis
@endsection
@php
    $jsonData = $test ? json_encode($test) : null;
    $test_data = $jsonData ? json_decode($jsonData) : null;
@endphp

@section('css')
    <style>
        .equal-width-table th,
        .equal-width-table td {
            width: 1%;
            white-space: nowrap;
        }

        .printable {
            font-size: 10pt;
        }

        .printable table {
            font-size: 12px;
        }

        @media print {
            @page {
                size: landscape;
                margin: 15px;
            }

            .no-print {
                display: none !important;
            }

            .printable {
                font-size: 10pt;
            }

            .printable table {
                font-size: 10px;
            }

            #studentsTable tbody tr:first-child {
                background-color: #e6ffe6 !important;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="{{ $gradebookBackUrl }}">Back</a>
        @endslot
        @slot('title')
            Overall Grade Analysis
        @endslot
    @endcomponent

    <div class="row no-print">
        <div class="col-md-12 col-lg-12 d-flex justify-content-end">
            <a class="text-muted" href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}">
                <i style="font-size: 20px;color:margin-bottom:10px; margin-right:10px; cursor:pointer;"
                    class="bx bx-download text-muted me-2" title="Export to Excel"></i>
            </a>

            <i onclick="printContent()" style="font-size: 20px; margin-bottom:10px; cursor:pointer;"
                class="bx bx-printer text-muted" title="Print"></i>
        </div>
    </div>

    <div class="row printable">
        <div class="col-12">
            <div style="" class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6 col-lg-6 align-items-start">
                            <div style="font-size:14px;" class="form-group">
                                <strong>{{ $school_data->school_name ?? 'School Name' }}</strong>
                                <br>
                                <span>{{ $school_data->physical_address ?? 'Physical Address' }}</span>
                                <br>
                                <span>{{ $school_data->postal_address ?? 'Postal Address' }}</span>
                                <br>
                                <span>Tel: {{ $school_data->telephone ?? 'N/A' }} Fax:
                                    {{ $school_data->fax ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-6 d-flex justify-content-end">
                            @if (isset($school_data->logo_path))
                                <img height="80" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                            @else
                                <span>Logo Not Available</span>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- Fourth Table: Students' Detailed Analysis -->
                <div class="card-body">
                    @if ($type === 'CA')
                        <h6>{{ $gradeName }} - End of {{ $test->name ?? 'Month' }} Grade Subjects Analysis</h6>
                    @else
                        <h6>{{ $gradeName }} - End of Term Grade Subjects Analysis</h6>
                    @endif

                    <div class="table-responsive">
                        <table id="subjectAnalysisTable"
                            class="table table-sm table-bordered table-striped equal-width-table">
                            <thead>
                                <tr style="text-align: center;">
                                    @foreach (['A*', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'U'] as $grade)
                                        <th colspan="2">{{ $grade }}</th>
                                    @endforeach
                                    <th>A*AB%</th>
                                    <th>A*ABC%</th>
                                    <th colspan="2">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="text-align: center;">
                                    @foreach (['A*', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'U'] as $grade)
                                        <td>{{ $subjectAnalysis[$grade]['Total'] }}</td>
                                        <td>{{ round($subjectAnalysis[$grade]['%']) }}%</td>
                                    @endforeach
                                    <td>{{ round($subjectAnalysis['A*AB%']) }}%</td>
                                    <td>{{ round($subjectAnalysis['A*ABC%']) }}%</td>
                                    <td>{{ $subjectAnalysis['Total'] }}</td>
                                    <td>100%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h6>{{ $gradeName }} - JCE Subjects Grade Analysis</h6>
                    <div class="table-responsive">
                        <table id="jceAnalysisTable" class="table table-sm table-bordered table-striped equal-width-table">
                            <thead>
                                <tr style="text-align: center;">
                                    @foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $grade)
                                        <th colspan="2">{{ $grade }}</th>
                                    @endforeach
                                    <th>AB%</th>
                                    <th>ABC%</th>
                                    <th colspan="2">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="text-align: center;">
                                    @foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $grade)
                                        <td>{{ $jceAnalysis[$grade]['Total'] }}</td>
                                        <td>{{ round($jceAnalysis[$grade]['%']) }}%</td>
                                    @endforeach
                                    <td>{{ round($jceAnalysis['AB%']) }}%</td>
                                    <td>{{ round($jceAnalysis['ABC%']) }}%</td>
                                    <td>{{ $jceAnalysis['Total'] }}</td>
                                    <td>100%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    @if ($type === 'CA')
                        <h6>{{ $gradeName }} - End of {{ $test->name ?? 'Month' }} Grade Analysis</h6>
                    @else
                        <h6>{{ $gradeName }} - End of Term Grade Analysis</h6>
                    @endif


                    <div class="d-flex align-items-start mb-2">
                        <div>
                            <strong class="text-info">Note:</strong> <i>Students showing <strong>'-'</strong> on subjects
                                indicates they are not
                                enrolled in that subject.
                                Students showing <strong>'X'</strong> indicates they are enrolled but do not have a score
                                recorded.</i>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="studentsTable" class="table table-sm table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Class</th>
                                    <th>Gender</th>
                                    <th>JCE</th>
                                    @foreach ($allSubjects as $subject)
                                        @php
                                            $hasScores = false;
                                            foreach ($students as $student) {
                                                if ($student['subjects'][$subject]['score'] !== '-') {
                                                    $hasScores = true;
                                                    break;
                                                }
                                            }
                                        @endphp
                                        @if ($hasScores)
                                            <th title="{{ $subject }}" colspan="2" style="text-align:center">
                                                {{ substr($subject, 0, 3) }}
                                            </th>
                                        @endif
                                    @endforeach
                                    <th>CRE</th>
                                    <th>TP</th>
                                    <th>Pos</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($students as $index => $student)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $student['name'] }}</td>
                                        <td>{{ $student['class'] }}</td>
                                        <td>{{ $student['gender'] }}</td>
                                        <td>{{ $student['jce'] }}</td>
                                        @foreach ($allSubjects as $subject)
                                            @php
                                                $hasScores = false;
                                                foreach ($students as $s) {
                                                    if ($s['subjects'][$subject]['score'] !== '-') {
                                                        $hasScores = true;
                                                        break;
                                                    }
                                                }
                                            @endphp
                                            @if ($hasScores)
                                                <td>{{ $student['subjects'][$subject]['score'] }}</td>
                                                <td>{{ $student['subjects'][$subject]['display_grade'] ?? $student['subjects'][$subject]['grade'] }}</td>
                                            @endif
                                        @endforeach
                                        <td>{{ $student['credits'] }}</td>
                                        <td>{{ $student['totalPoints'] }}</td>
                                        <td>{{ $student['position'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <h6>{{ $gradeName }} - Class Summary</h6>
                    <div class="table-responsive">
                        <table id="classSummaryTable" class="table table-sm table-bordered table-striped equal-width-table">
                            <thead>
                                <tr style="text-align: center;">
                                    <th>Class</th>
                                    <th>Number of Students</th>
                                    <th>Average Credits</th>
                                    <th>Average Points</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalStudents = 0;
                                    $totalCredits = 0;
                                    $totalPoints = 0;
                                @endphp

                                @foreach ($classStats as $className => $stats)
                                    @php
                                        $totalStudents += $stats['count'];
                                        $totalCredits += $stats['count'] * $stats['averageCredits'];
                                        $totalPoints += $stats['count'] * $stats['averagePoints'];
                                    @endphp
                                    <tr style="text-align: center;">
                                        <td>{{ $className }}</td>
                                        <td>{{ $stats['count'] }}</td>
                                        <td>{{ $stats['averageCredits'] }}</td>
                                        <td>{{ $stats['averagePoints'] }}</td>
                                    </tr>
                                @endforeach

                                <tr style="text-align: center; font-weight: bold;">
                                    <td>Grade Average</td>
                                    <td>{{ $totalStudents }}</td>
                                    <td>{{ $totalStudents > 0 ? round($totalCredits / $totalStudents, 2) : 0 }}</td>
                                    <td>{{ $totalStudents > 0 ? round($totalPoints / $totalStudents, 2) : 0 }}</td>
                                </tr>
                            </tbody>
                        </table>
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
