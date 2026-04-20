@extends('layouts.master')
@section('title')
    Subject Analysis
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ $markbookBackUrl }}">Back</a>
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

            body {
                width: 100%;
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

            body * {
                visibility: hidden;
            }

            .printable,
            .printable * {
                visibility: visible;
            }

            .printable {
                position: absolute;
                left: 50%;
                top: 0;
                transform: translateX(-50%);
                width: 350mm;
                height: 297mm;
                margin-left: 250px;
                margin-top: 80px;
                padding: 0;
                page-break-after: avoid;
            }


            .card-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0 10mm;
            }

            .card-header img {
                width: 300px;
                height: 120px;
            }

            .table {
                width: 100%;
                table-layout: fixed;
            }

            .table th,
            .table td {
                width: auto;
                overflow: visible;
                word-wrap: break-word;
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
        <div class="col-12 d-flex justify-content-end">
            <i onclick="alert(0)" style="font-size: 20px;margin-bottom:10px;cursor:pointer;margin-right:5px;"
                class="bx bx-sync"></i>
            <i onclick="printContent()" style="font-size: 20px;margin-bottom:10px;cursor:pointer;" class="bx bx-printer"></i>
        </div>
    </div>

    {{-- Print from here to the bottom only --}}
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
                    <h5>Grade Distribution Report by Teacher</h5>
                    <!-- Nav tabs for teachers -->
                    <ul class="nav nav-tabs" role="tablist">
                        @foreach ($teacherGradeDistributions as $teacherName => $tests)
                            <li class="nav-item">
                                <a class="nav-link @if ($loop->first) active @endif"
                                    id="tab-{{ $loop->index }}" data-bs-toggle="tab" href="#teacher-{{ $loop->index }}"
                                    role="tab">
                                    {{ $teacherName }}
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    <!-- Tab panes for each teacher -->
                    <div class="tab-content">
                        @foreach ($teacherGradeDistributions as $teacherName => $tests)
                            <div class="tab-pane fade @if ($loop->first) show active @endif"
                                id="teacher-{{ $loop->index }}" role="tabpanel">
                                <div class="pt-3">
                                    @foreach ($tests as $testId => $grades)
                                        <h4>Test: {{ $grades['name'] ?? 'h' }}</h4>
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Grade</th>
                                                    <th colspan="2">A</th>
                                                    <th colspan="2">B</th>
                                                    <th colspan="2">C</th>
                                                    <th colspan="2">D</th>
                                                    <th>ABC%</th>
                                                    <th>ABCD%</th>
                                                </tr>
                                                <tr>
                                                    <th>Sex</th>
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>Total</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Counts</td>
                                                    <td>{{ $grades['A']['M'] ?? 0 }}</td>
                                                    <td>{{ $grades['A']['F'] ?? 0 }}</td>
                                                    <td>{{ $grades['B']['M'] ?? 0 }}</td>
                                                    <td>{{ $grades['B']['F'] ?? 0 }}</td>
                                                    <td>{{ $grades['C']['M'] ?? 0 }}</td>
                                                    <td>{{ $grades['C']['F'] ?? 0 }}</td>
                                                    <td>{{ $grades['D']['M'] ?? 0 }}</td>
                                                    <td>{{ $grades['D']['F'] ?? 0 }}</td>
                                                    <td>{{ number_format($grades['ABC%'], 2) }}%</td>
                                                    <td>{{ number_format($grades['ABCD%'], 2) }}%</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div> <!-- end col -->
            </div>
        @endsection
        @section('script')
            <script>
                function printContent() {
                    window.print();
                }
            </script>
        @endsection
