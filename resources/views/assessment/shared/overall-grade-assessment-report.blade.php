@extends('layouts.master')
@section('title')
    Analysis
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ $gradebookBackUrl }}">Student Grade Analysis</a>
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
        <div class="col-md-11 d-flex justify-content-end">
            <i onclick="alert(0)" style="font-size: 20px;margin-bottom:10px;cursor:pointer;margin-right:5px;"
                class="bx bx-download text-muted me-2"></i>
            <i onclick="printContent()" style="font-size: 20px;margin-bottom:10px;cursor:pointer;" class="bx bx-printer text-muted"></i>
        </div>
    </div>

    {{-- Print from here to the bottom only --}}
    <div class="row printable">
        <div class="col-md-11">
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
                                <h5>Grade Term Analysis Report</h5>
                                @if (!empty($reportData['reportData']))
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Student Name</th>
                                                @if (!empty($reportData['reportData'][0]['subjects']))
                                                    @foreach (array_keys($reportData['reportData'][0]['subjects']) as $subject)
                                                        <th>{{ $subject }}</th>
                                                        <th>Grade</th>
                                                    @endforeach
                                                @endif
                                                <th>Overall Average</th>
                                                <th>Overall Grade</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($reportData['reportData'] as $studentData)
                                                <tr>
                                                    <td>{{ $studentData['name'] }}</td>
                                                    @if (!empty($studentData['subjects']))
                                                        @foreach ($studentData['subjects'] as $subjectData)
                                                            <td>{{ $subjectData['score'] ?? 'N/A' }}</td>
                                                            <td>{{ $subjectData['grade'] ?? 'N/A' }}</td>
                                                        @endforeach
                                                    @endif
                                                    <td>{{ number_format($studentData['averageScore'], 1) }}</td>
                                                    <td>{{ $studentData['overallGrade'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <p>No data available.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="report-card">
                        <h5>Overall Performance by Gender</h5>
                        @if (!empty($reportData['gradeCounts']))
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Grade</th>
                                        <th>M</th>
                                        <th>F</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($reportData['gradeCounts'] as $grade => $counts)
                                        <tr>
                                            <td>{{ $grade }}</td>
                                            <td>{{ $counts['M'] ?? 0 }}</td>
                                            <td>{{ $counts['F'] ?? 0 }}</td>
                                            <td>{{ ($counts['M'] ?? 0) + ($counts['F'] ?? 0) }}</td>
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <td>Quality (ABC %)</td>
                                        <td colspan="3">{{ $reportData['quality'] ?? 'N/A' }}%</td>
                                    </tr>
                                    <tr>
                                        <td>Quantity (DE %)</td>
                                        <td colspan="3">{{ $reportData['quantity'] ?? 'N/A' }}%</td>
                                    </tr>
                                </tbody>
                            </table>
                        @else
                            <p>No gender data available.</p>
                        @endif
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <canvas id="genderPerformanceChart"></canvas>
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
            const gradeCounts = @json($reportData['gradeCounts'] ?? []);
            const ctx = document.getElementById('genderPerformanceChart').getContext('2d');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: Object.keys(gradeCounts),
                    datasets: [{
                        label: 'Boys',
                        data: Object.values(gradeCounts).map(count => count.M ?? 0),
                        backgroundColor: 'rgba(54, 162, 235, 0.5)'
                    }, {
                        label: 'Girls',
                        data: Object.values(gradeCounts).map(count => count.F ?? 0),
                        backgroundColor: 'rgba(255, 99, 132, 0.5)'
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
