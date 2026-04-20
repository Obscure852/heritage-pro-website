@extends('layouts.master')
@section('title')
    Class List Analysis
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ $gradebookBackUrl }}">Back</a>
        @endslot
        @slot('title')
            Grade Analysis - {{ $gradeName }}
        @endslot
    @endcomponent
@section('css')
    <style>
        .equal-width-table th,
        .equal-width-table td {
            width: 1%;
            white-space: nowrap;
        }

        .table th,
        .table td {
            text-align: center;
            vertical-align: middle;
        }

        .table th {
            background-color: #f8f9fa;
        }

        .grade-analysis-table {
            font-size: 0.9rem;
        }

        .grade-analysis-table th,
        .grade-analysis-table td {
            text-align: center;
            vertical-align: middle !important;
        }

        .grade-analysis-table .grade-header {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .grade-analysis-table .sub-header {
            background-color: #e9ecef;
            font-weight: normal;
        }

        .grade-analysis-table .total-row {
            font-weight: bold;
            background-color: #e9ecef;
        }

        .grade-analysis-table .highlight {
            background-color: #fff3cd;
        }
    </style>
@endsection
<div class="row">
    <div class="col-md-12 col-lg-12 d-flex justify-content-end">
        <i onclick="alert(0)" style="font-size: 20px;margin-bottom:10px;cursor:pointer;margin-right:5px;"
            class="bx bx-sync"></i>
        <i onclick="printContent()" style="font-size: 20px;margin-bottom:10px;cursor:pointer;" class="bx bx-printer"></i>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="container-fluid">
            <div class="container-fluid">
                <h5 class="mt-5 mb-3">JCE Subjects Grade Analysis</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered grade-analysis-table">
                        <thead>
                            <tr class="grade-header">
                                <th rowspan="2">Grade</th>
                                @foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $grade)
                                    <th colspan="3">{{ $grade }}</th>
                                @endforeach
                                <th rowspan="2">AB%</th>
                                <th rowspan="2">ABC%</th>
                                <th rowspan="2">Total</th>
                            </tr>
                            <tr class="sub-header">
                                @foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $grade)
                                    <th>Total</th>
                                    <th>%</th>
                                    <th>M/F</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="grade-header">Count</td>
                                @foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $grade)
                                    <td>{{ $jceSubjectAnalysis[$grade]['Total'] ?? 0 }}</td>
                                    <td>{{ round($jceSubjectAnalysis[$grade]['Percentage']) }}%</td>
                                    <td>{{ $jceSubjectAnalysis[$grade]['Male'] }}/{{ $jceSubjectAnalysis[$grade]['Female'] }}
                                    </td>
                                @endforeach
                                <td class="highlight">{{ round($jceSubjectAnalysis['AB%']) }}%</td>
                                <td class="highlight">{{ round($jceSubjectAnalysis['ABC%']) }}%</td>
                                <td class="total-row">{{ $jceSubjectAnalysis['Total'] ?? 0 }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h5 class="mt-5 mb-3">Grade Subjects Analysis</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered grade-analysis-table">
                        <thead>
                            <tr class="grade-header">
                                <th rowspan="2">Grade</th>
                                @foreach (['A*', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'U'] as $grade)
                                    <th colspan="3">{{ $grade }}</th>
                                @endforeach
                                <th rowspan="2">A*AB%</th>
                                <th rowspan="2">A*ABC%</th>
                                <th colspan="3">Total</th>
                            </tr>
                            <tr class="sub-header">
                                @foreach (['A*', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'U'] as $grade)
                                    <th>Total</th>
                                    <th>%</th>
                                    <th>M/F</th>
                                @endforeach
                                <th>Total</th>
                                <th>%</th>
                                <th>M/F</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="grade-header">Count</td>
                                @foreach (['A*', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'U'] as $grade)
                                    <td>{{ $subjectAnalysis[$grade]['Total'] ?? 0 }}</td>
                                    <td>{{ isset($subjectAnalysis[$grade]['Percentage']) ? round($subjectAnalysis[$grade]['Percentage']) : 0 }}%
                                    </td>
                                    <td>{{ $subjectAnalysis[$grade]['Male'] }}/{{ $subjectAnalysis[$grade]['Female'] }}
                                    </td>
                                @endforeach
                                <td class="highlight">
                                    {{ isset($subjectAnalysis['A*AB']['Percentage']) ? round($subjectAnalysis['A*AB']['Percentage']) : 0 }}%
                                </td>
                                <td class="highlight">
                                    {{ isset($subjectAnalysis['A*ABC']['Percentage']) ? round($subjectAnalysis['A*ABC']['Percentage']) : 0 }}%
                                </td>
                                <td class="total-row">{{ $subjectAnalysis['Total'] ?? 0 }}</td>
                                <td class="total-row">100%</td>
                                <td class="total-row">
                                    {{ $subjectAnalysis['A*']['Male'] + $subjectAnalysis['A']['Male'] + $subjectAnalysis['B']['Male'] + $subjectAnalysis['C']['Male'] + $subjectAnalysis['D']['Male'] + $subjectAnalysis['E']['Male'] + $subjectAnalysis['F']['Male'] + $subjectAnalysis['G']['Male'] + $subjectAnalysis['U']['Male'] }}/
                                    {{ $subjectAnalysis['A*']['Female'] + $subjectAnalysis['A']['Female'] + $subjectAnalysis['B']['Female'] + $subjectAnalysis['C']['Female'] + $subjectAnalysis['D']['Female'] + $subjectAnalysis['E']['Female'] + $subjectAnalysis['F']['Female'] + $subjectAnalysis['G']['Female'] + $subjectAnalysis['U']['Female'] }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h5 class="mt-5 mb-3">Students Grade Performance</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-striped">
                        <thead>
                            <tr>
                                <th rowspan="2" style="text-align: left;">Name</th>
                                <th rowspan="2">Class</th>
                                <th rowspan="2">Gender</th>
                                <th rowspan="2">JCE</th>
                                @foreach ($allSubjects as $subject)
                                    <th colspan="2" title="{{ $subject['name'] }}">{{ $subject['abbrev'] }}</th>
                                @endforeach
                                <th rowspan="2">Points</th>
                                <th rowspan="2">CRD</th>
                                <th rowspan="2">Pos</th>
                            </tr>
                            <tr>
                                @foreach ($allSubjects as $subject)
                                    <th title="{{ $subject['name'] }} Percentage">%</th>
                                    <th title="{{ $subject['name'] }} Grade">G</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($students as $student)
                                <tr>
                                    <td style="text-align: left;">{{ $student['name'] }}</td>
                                    <td>{{ $student['class'] }}</td>
                                    <td>{{ $student['gender'] }}</td>
                                    <td>{{ $student['jce'] }}</td>
                                    @foreach ($allSubjects as $subject)
                                        @php
                                            $subjectScore = $student['subjects'][$subject['name']] ?? [
                                                'percentage' => '-',
                                                'grade' => '-',
                                            ];
                                        @endphp
                                        <td>{{ $subjectScore['percentage'] }}</td>
                                        <td>{{ $subjectScore['grade'] }}</td>
                                    @endforeach
                                    <td>{{ $student['totalPoints'] }}</td>
                                    <td>{{ $student['creditCount'] }}</td>
                                    <td>{{ $student['position'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
