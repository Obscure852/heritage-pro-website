@extends('layouts.master')
@section('title')
    Analysis
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
                                <h5><strong>Class Overall Statistical Report</strong></h5>
                                <p>{{ 'Term ' . $klass->term->term . ',' . $klass->term->year }}</p>
                                <p>Class: {{ $klass->name }} Teacher: {{ $klass->teacher->full_name }}</p>
                                <table class="table table-bordered">
                                    <thead>
                                        <th>Overall</th>
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
                                        <th>AB</th>
                                        <th>%</th>
                                        <th>ABC</th>
                                        <th>%</th>
                                        <th>DE</th>
                                        <th>%</th>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Totals</td>
                                            @foreach (['A', 'B', 'C', 'D', 'E', 'AB', 'ABC', 'DE'] as $grade)
                                                <td>{{ $gradeCounts[$grade] }}</td>
                                                <td>{{ $gradePercentages[$grade] . '%' }}</td>
                                            @endforeach
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <canvas id="overallPerformanceChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end card -->
        </div> <!-- end col -->
    </div>
@endsection
@section('script')
    <script>
        function printContent() {
            window.print();
        }
        document.addEventListener('DOMContentLoaded', function() {
            const gradeCounts = @json($gradeCounts);
            const gradePercentages = @json($gradePercentages);

            const ctx = document.getElementById('overallPerformanceChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: Object.keys(gradeCounts),
                    datasets: [{
                        label: 'Number of Students',
                        data: Object.values(gradeCounts),
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }, {
                        label: 'Percentage',
                        data: Object.values(gradePercentages),
                        backgroundColor: 'rgba(255, 99, 132, 0.5)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1,
                        type: 'line'
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        });
    </script>
@endsection
