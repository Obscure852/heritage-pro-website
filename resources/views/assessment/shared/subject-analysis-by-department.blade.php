@extends('layouts.master')
@section('title')
    Subject Analysis
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ $gradebookBackUrl }}">Back</a>
        @endslot
        @slot('title')
            Analysis
        @endslot
    @endcomponent

    <style>
        .card {
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
        }

        .report-card {
            margin-top: 0mm;
            margin-bottom: 20mm;
        }

        textarea {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #333;
            padding: 5px;
            margin: 10px 0;
        }

        @media print {
            @page {
                size: landscape;
                margin: 15mm;
            }

            body {
                margin: 0;
                padding: 0;
                line-height: normal;
            }

            body * {
                visibility: hidden;
            }

            .printable,
            .printable * {
                visibility: visible;
            }

            .printable {
                width: 90%;
                margin: 0 auto;
                padding: 0;
                position: static;
                transform: none;
                page-break-after: avoid;
            }

            .card-header,
            .card-body {
                text-align: center;
            }

            .table {
                margin: 0 auto;
            }

            textarea {
                border: none;
            }

            .card {
                box-shadow: none;
            }
        }
    </style>

    <div class="row">
        <div class="col-md-12 d-flex justify-content-end">
            <i onclick="window.location.href='{{ request()->fullUrlWithQuery(['export' => 'true']) }}'"
                style="font-size: 20px; margin-bottom:10px; cursor:pointer; margin-right:5px;" class="bx bx-sync">
            </i>
            <i onclick="printContent()" style="font-size: 20px;margin-bottom:10px;cursor:pointer;" class="bx bx-printer"></i>
        </div>
    </div>

    <div class="row printable">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6 align-items-start">
                            <div class="form-group">
                                <strong>{{ $school_data->school_name }}</strong><br>
                                <span>{{ $school_data->physical_address }}</span><br>
                                <span>{{ $school_data->postal_address }}</span><br>
                                <span>Tel: {{ $school_data->telephone }} Fax: {{ $school_data->fax }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex justify-content-end">
                            <img height="80" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <h5>Performance Analysis by Department</h5>
                    <!-- Nav tabs for each test type -->
                    <ul class="nav nav-tabs" role="tablist">
                        @foreach ($tests as $testKey => $departments)
                            <li class="nav-item">
                                <a class="nav-link @if ($loop->first) active @endif"
                                    id="tab-{{ $testKey }}" data-bs-toggle="tab" href="#{{ $testKey }}"
                                    role="tab">
                                    {{ $testKey }}
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    <!-- Tab content for each test -->
                    <div class="tab-content">
                        @foreach ($tests as $testKey => $departments)
                            <div class="tab-pane fade @if ($loop->first) show active @endif"
                                id="{{ $testKey }}" role="tabpanel">
                                <div class="pt-3">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                                <tr>
                                                    <th rowspan="2">Department</th>
                                                    <th rowspan="2">Department Head</th>
                                                    <th rowspan="2">Subjects</th>
                                                    <!-- Grades -->
                                                    <th colspan="2">A</th>
                                                    <th colspan="2">B</th>
                                                    <th colspan="2">C</th>
                                                    <th colspan="2">D</th>
                                                    <th colspan="2">E</th>
                                                    <th colspan="2">U</th>
                                                    <!-- Percentages -->
                                                    <th colspan="2">AB%</th>
                                                    <th colspan="2">ABC%</th>
                                                    <th colspan="2">ABCD%</th>
                                                    <th colspan="2">DEU%</th>
                                                    <th rowspan="2">Total Students</th>
                                                </tr>
                                                <tr>
                                                    <!-- Sub columns for M and F under each category -->
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>M</th>
                                                    <th>F</th>

                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>M</th>
                                                    <th>F</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($departments as $department => $data)
                                                    <tr>
                                                        <td>{{ $department }}</td>
                                                        <td>{{ $data['departmentHead'] }}</td>
                                                        <td>
                                                            @foreach ($data['subjects'] as $subjectId => $subjectName)
                                                                {{ $subjectName }}<br>
                                                            @endforeach
                                                        </td>
                                                        <!-- Grades A,B,C,D,E,U -->
                                                        <td>{{ $data['A']['M'] }}</td>
                                                        <td>{{ $data['A']['F'] }}</td>
                                                        <td>{{ $data['B']['M'] }}</td>
                                                        <td>{{ $data['B']['F'] }}</td>
                                                        <td>{{ $data['C']['M'] }}</td>
                                                        <td>{{ $data['C']['F'] }}</td>
                                                        <td>{{ $data['D']['M'] }}</td>
                                                        <td>{{ $data['D']['F'] }}</td>
                                                        <td>{{ $data['E']['M'] }}</td>
                                                        <td>{{ $data['E']['F'] }}</td>
                                                        <td>{{ $data['U']['M'] }}</td>
                                                        <td>{{ $data['U']['F'] }}</td>

                                                        <!-- Percentages AB%, ABC%, ABCD%, DEU% each with M,F -->
                                                        <td>{{ number_format($data['AB%']['M'], 2) }}%</td>
                                                        <td>{{ number_format($data['AB%']['F'], 2) }}%</td>
                                                        <td>{{ number_format($data['ABC%']['M'], 2) }}%</td>
                                                        <td>{{ number_format($data['ABC%']['F'], 2) }}%</td>
                                                        <td>{{ number_format($data['ABCD%']['M'], 2) }}%</td>
                                                        <td>{{ number_format($data['ABCD%']['F'], 2) }}%</td>
                                                        <td>{{ number_format($data['DEU%']['M'], 2) }}%</td>
                                                        <td>{{ number_format($data['DEU%']['F'], 2) }}%</td>

                                                        <td>{{ $data['total_students'] }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div> <!-- table-responsive -->
                                </div> <!-- pt-3 -->
                            </div> <!-- tab-pane -->
                        @endforeach
                    </div> <!-- tab-content -->
                </div> <!-- card-body -->
            </div> <!-- card -->
        </div> <!-- col -->
    </div>
@endsection

@section('script')
    <script>
        function printContent() {
            window.print();
        }
    </script>
@endsection
