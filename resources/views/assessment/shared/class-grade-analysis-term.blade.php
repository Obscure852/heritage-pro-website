@extends('layouts.master')
@section('title')
    Analysis
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ $gradebookBackUrl }}">Subject Analysis By Class</a>
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
        <div class="col-md-10 d-flex justify-content-end">
            <i onclick="alert(0)" style="font-size: 20px;margin-bottom:10px;cursor:pointer;margin-right:5px;"
                class="bx bx-sync"></i>
            <i onclick="printContent()" style="font-size: 20px;margin-bottom:10px;cursor:pointer;" class="bx bx-printer"></i>
        </div>
    </div>

    {{-- Print from here to the bottom only --}}
    <div class="row printable">
        <div class="col-md-10">
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
                    <div class="report-card">
                        <div class="row">
                            <div class="col-md-12">
                                <h5>Subject Grades Analysis By Class</h5>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Subject</th>
                                            <th>A</th>
                                            <th>%</th>
                                            <th>B</th>
                                            <th>%</th>
                                            <th>C</th>
                                            <th>%</th>
                                            <th>D</th>
                                            <th>%</th>
                                            <th>E</th>
                                            <th>%</th>
                                            <th>ABC</th>
                                            <th>%</th>
                                            <th>DE</th>
                                            <th>%</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($reportData as $subjectData)
                                            <tr>
                                                <td>{{ $subjectData['name'] }}</td>
                                                @foreach ($subjectData['grades'] as $gradeData)
                                                    <td>{{ $gradeData['count'] }}</td>
                                                    <td>{{ number_format($gradeData['percentage'], 1) }}%</td>
                                                @endforeach
                                                <td>{{ $subjectData['pass']['count'] }}</td>
                                                <td>{{ number_format($subjectData['pass']['percentage'], 1) }}%</td>
                                                <td>{{ $subjectData['fail']['count'] }}</td>
                                                <td>{{ number_format($subjectData['fail']['percentage'], 1) }}%</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <h5>Overall Class Grade Counts by Gender</h5>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Grade</th>
                                            @foreach (['A', 'B', 'C', 'D', 'E'] as $grade)
                                                <th>{{ $grade }} (M)</th>
                                                <th>{{ $grade }} (F)</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Count</td>
                                            @foreach (['A', 'B', 'C', 'D', 'E'] as $grade)
                                                <td>{{ $gradeCounts['M'][$grade] ?? 0 }}</td>
                                                <td>{{ $gradeCounts['F'][$grade] ?? 0 }}</td>
                                            @endforeach
                                        </tr>
                                    </tbody>
                                </table>
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
    </script>
@endsection
