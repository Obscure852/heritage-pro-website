@extends('layouts.master')
@section('title')
    Subject Performance Analysis
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

        .subject-name {
            text-align: left !important;
            font-weight: 500;
            background-color: #f8f9fa;
        }

        .grade-header {
            font-weight: bold;
            font-size: 10px;
        }

        .gender-header {
            font-size: 9px;
            font-weight: normal;
        }

        .percentage-header {
            font-weight: bold;
            font-size: 10px;
        }

        .grade-a {
            background-color: #d5f5d5;
        }

        .grade-b {
            background-color: #e5f3ff;
        }

        .grade-c {
            background-color: #fff3e5;
        }

        .grade-d {
            background-color: #ffffd5;
        }

        .grade-e {
            background-color: #ffe5e5;
        }

        .grade-f {
            background-color: #ffcccc;
        }

        .grade-u {
            background-color: #ffd5d5;
        }

        .percent-high {
            background-color: #d5f5d5;
            font-weight: bold;
        }

        .percent-medium {
            background-color: #ffffd5;
            font-weight: bold;
        }

        .percent-low {
            background-color: #ffd5d5;
        }

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

        .no-results {
            color: #888;
            font-style: italic;
        }

        .report-class-tabs-wrapper {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 10px 12px;
            background: #f9fafb;
        }

        .report-class-tabs {
            gap: 8px;
        }

        .report-class-tabs .nav-link {
            border-radius: 3px;
            border: 1px solid #d1d5db;
            color: #374151;
            background: white;
            font-size: 13px;
            padding: 6px 10px;
        }

        .report-class-tabs .nav-link.active {
            background: #2563eb;
            border-color: #2563eb;
            color: white;
        }

        .report-class-tabs .nav-link .badge {
            font-size: 10px;
            font-weight: 500;
        }

        .report-class-tabs .nav-link.active .badge {
            background: rgba(255, 255, 255, 0.2) !important;
            color: white !important;
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

            .subject-name {
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
            window.location = '{{ $gradebookBackUrl }}';
            }
     ">Back</a>
        @endslot
        @slot('title')
            Subject Performance Analysis - {{ $reportData['class_info']['name'] }}
        @endslot
    @endcomponent

    <div class="row no-print mb-3">
        <div class="col-12 d-flex justify-content-end">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="me-2 text-muted">
                <i style="font-size: 20px;" class="bx bx-download"></i>
            </a>

            <a href="#" onclick="printContent()" class="me-2 text-muted">
                <i style="font-size: 20px;" class="bx bx-printer me-1"></i>
            </a>
        </div>
    </div>

    <div class="row no-print mb-3">
        <div class="col-12">
            <div class="report-class-tabs-wrapper">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="text-muted fw-semibold me-2">Class:</span>
                    <ul class="nav nav-pills report-class-tabs">
                        @forelse ($reportClasses as $klass)
                            <li class="nav-item">
                                <a class="nav-link {{ (int) $reportData['class_info']['id'] === (int) $klass['id'] ? 'active' : '' }}"
                                    href="{{ route('finals.class.subjects-summary-analyis', ['classId' => $klass['id']]) }}">
                                    {{ $klass['name'] }}
                                    <span class="badge bg-light text-dark ms-1">{{ $klass['grade_name'] }}</span>
                                </a>
                            </li>
                        @empty
                            <li class="nav-item">
                                <span class="nav-link disabled">No classes found for this year.</span>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row printable">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div style="font-size:14px;" class="col-md-6">
                            <div class="d-flex flex-column">
                                <h5 class="mb-0">{{ $school_data->school_name }}</h5>
                                <p class="mb-0">{{ $school_data->physical_address }}</p>
                                <p class="mb-0">{{ $school_data->postal_address }}</p>
                                <p class="mb-0">Tel: {{ $school_data->telephone }} Fax: {{ $school_data->fax }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex justify-content-end">
                            <img height="80" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <h6 class="text-start mb-3">
                        Subject Performance Analysis - {{ $reportData['class_info']['name'] }}
                        ({{ $reportData['class_info']['grade'] }}) - {{ $reportData['exam_year'] }}
                    </h6>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <!-- Main Header Row -->
                                <tr>
                                    <th rowspan="2" class="subject-name">Subject</th>
                                    <th colspan="3" class="grade-header">A</th>
                                    <th colspan="3" class="grade-header">B</th>
                                    <th colspan="3" class="grade-header">C</th>
                                    <th colspan="3" class="grade-header">D</th>
                                    <th colspan="3" class="grade-header">E</th>
                                    <th colspan="3" class="grade-header">F</th>
                                    <th colspan="3" class="grade-header">U</th>
                                    <th colspan="3" class="percentage-header">AB%</th>
                                    <th colspan="3" class="percentage-header">ABC%</th>
                                    <th colspan="3" class="percentage-header">DEU%</th>
                                </tr>
                                <!-- Sub Header Row -->
                                <tr>
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
                                    <!-- Grade F -->
                                    <th class="gender-header">M</th>
                                    <th class="gender-header">F</th>
                                    <th class="gender-header">T</th>
                                    <!-- Grade U -->
                                    <th class="gender-header">M</th>
                                    <th class="gender-header">F</th>
                                    <th class="gender-header">T</th>
                                    <!-- AB% -->
                                    <th class="gender-header">M</th>
                                    <th class="gender-header">F</th>
                                    <th class="gender-header">T</th>
                                    <!-- ABC% -->
                                    <th class="gender-header">M</th>
                                    <th class="gender-header">F</th>
                                    <th class="gender-header">T</th>
                                    <!-- DEU% -->
                                    <th class="gender-header">M</th>
                                    <th class="gender-header">F</th>
                                    <th class="gender-header">T</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reportData['subjects'] as $index => $subject)
                                    @php
                                        $subjectAbbr = $subject;
                                        $data = $reportData['subject_data'][$subject] ?? null;
                                    @endphp
                                    <tr @if ($index % 2 == 0) class="table-light" @endif>
                                        <td class="subject-name" title="{{ $subject }}">{{ $subjectAbbr }}</td>

                                        @if ($data)
                                            <!-- Grade A -->
                                            <td class="male-cell grade-a">{{ $data['grades']['A']['M'] }}</td>
                                            <td class="female-cell grade-a">{{ $data['grades']['A']['F'] }}</td>
                                            <td class="total-cell grade-a">{{ $data['grades']['A']['T'] }}</td>

                                            <!-- Grade B -->
                                            <td class="male-cell grade-b">{{ $data['grades']['B']['M'] }}</td>
                                            <td class="female-cell grade-b">{{ $data['grades']['B']['F'] }}</td>
                                            <td class="total-cell grade-b">{{ $data['grades']['B']['T'] }}</td>

                                            <!-- Grade C -->
                                            <td class="male-cell grade-c">{{ $data['grades']['C']['M'] }}</td>
                                            <td class="female-cell grade-c">{{ $data['grades']['C']['F'] }}</td>
                                            <td class="total-cell grade-c">{{ $data['grades']['C']['T'] }}</td>

                                            <!-- Grade D -->
                                            <td class="male-cell grade-d">{{ $data['grades']['D']['M'] }}</td>
                                            <td class="female-cell grade-d">{{ $data['grades']['D']['F'] }}</td>
                                            <td class="total-cell grade-d">{{ $data['grades']['D']['T'] }}</td>

                                            <!-- Grade E -->
                                            <td class="male-cell grade-e">{{ $data['grades']['E']['M'] }}</td>
                                            <td class="female-cell grade-e">{{ $data['grades']['E']['F'] }}</td>
                                            <td class="total-cell grade-e">{{ $data['grades']['E']['T'] }}</td>

                                            <!-- Grade F -->
                                            <td class="male-cell grade-f">{{ $data['grades']['F']['M'] ?? 0 }}</td>
                                            <td class="female-cell grade-f">{{ $data['grades']['F']['F'] ?? 0 }}</td>
                                            <td class="total-cell grade-f">{{ $data['grades']['F']['T'] ?? 0 }}</td>

                                            <!-- Grade U -->
                                            <td class="male-cell grade-u">{{ $data['grades']['U']['M'] }}</td>
                                            <td class="female-cell grade-u">{{ $data['grades']['U']['F'] }}</td>
                                            <td class="total-cell grade-u">{{ $data['grades']['U']['T'] }}</td>

                                            <!-- AB% -->
                                            <td
                                                class="male-cell 
                                                @if ($data['percentages']['AB']['M'] >= 50) percent-high
                                                @elseif($data['percentages']['AB']['M'] >= 30) percent-medium
                                                @else percent-low @endif
                                            ">
                                                {{ $data['percentages']['AB']['M'] }}%</td>
                                            <td
                                                class="female-cell
                                                @if ($data['percentages']['AB']['F'] >= 50) percent-high
                                                @elseif($data['percentages']['AB']['F'] >= 30) percent-medium
                                                @else percent-low @endif
                                            ">
                                                {{ $data['percentages']['AB']['F'] }}%</td>
                                            <td
                                                class="total-cell
                                                @if ($data['percentages']['AB']['T'] >= 50) percent-high
                                                @elseif($data['percentages']['AB']['T'] >= 30) percent-medium
                                                @else percent-low @endif
                                            ">
                                                {{ $data['percentages']['AB']['T'] }}%</td>

                                            <!-- ABC% -->
                                            <td
                                                class="male-cell
                                                @if ($data['percentages']['ABC']['M'] >= 70) percent-high
                                                @elseif($data['percentages']['ABC']['M'] >= 50) percent-medium
                                                @else percent-low @endif
                                            ">
                                                {{ $data['percentages']['ABC']['M'] }}%</td>
                                            <td
                                                class="female-cell
                                                @if ($data['percentages']['ABC']['F'] >= 70) percent-high
                                                @elseif($data['percentages']['ABC']['F'] >= 50) percent-medium
                                                @else percent-low @endif
                                            ">
                                                {{ $data['percentages']['ABC']['F'] }}%</td>
                                            <td
                                                class="total-cell
                                                @if ($data['percentages']['ABC']['T'] >= 70) percent-high
                                                @elseif($data['percentages']['ABC']['T'] >= 50) percent-medium
                                                @else percent-low @endif
                                            ">
                                                {{ $data['percentages']['ABC']['T'] }}%</td>

                                            <!-- DEU% -->
                                            <td
                                                class="male-cell
                                                @if ($data['percentages']['DEU']['M'] <= 20) percent-high
                                                @elseif($data['percentages']['DEU']['M'] <= 40) percent-medium
                                                @else percent-low @endif
                                            ">
                                                {{ $data['percentages']['DEU']['M'] }}%</td>
                                            <td
                                                class="female-cell
                                                @if ($data['percentages']['DEU']['F'] <= 20) percent-high
                                                @elseif($data['percentages']['DEU']['F'] <= 40) percent-medium
                                                @else percent-low @endif
                                            ">
                                                {{ $data['percentages']['DEU']['F'] }}%</td>
                                            <td
                                                class="total-cell
                                                @if ($data['percentages']['DEU']['T'] <= 20) percent-high
                                                @elseif($data['percentages']['DEU']['T'] <= 40) percent-medium
                                                @else percent-low @endif
                                            ">
                                                {{ $data['percentages']['DEU']['T'] }}%</td>
                                        @else
                                            <td colspan="30" class="text-center no-results">No data available</td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="31" class="text-center no-results">
                                            No subjects found for this class
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Summary Legend -->
                    <div class="mt-4">
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="text-start mb-3">Performance Indicators</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="p-2 border rounded">
                                            <strong>AB%:</strong> Students with grades A or B<br>
                                            <small class="text-muted">Target: ≥50% (Good), ≥30% (Fair)</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-2 border rounded">
                                            <strong>ABC%:</strong> Students with grades A, B or C (Pass Rate)<br>
                                            <small class="text-muted">Target: ≥70% (Good), ≥50% (Fair)</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-2 border rounded">
                                            <strong>DEU%:</strong> Students with grades D, E or U (Failure Rate)<br>
                                            <small class="text-muted">Target: ≤20% (Good), ≤40% (Fair)</small>
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
