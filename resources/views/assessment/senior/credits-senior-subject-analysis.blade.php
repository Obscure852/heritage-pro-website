@extends('layouts.master')
@section('title')
    Subjects Analysis
@endsection
@section('css')
    <style>
        .report-card {
            margin-top: 0mm;
            margin-bottom: 20mm;
        }

        body {
            font-size: 14px;
        }

        .table th,
        .table td {
            padding: 0.5rem;
            font-size: 12px;
        }

        @media print {
            @page {
                size: landscape;
                margin: 15px;
            }

            body {
                width: 100%;
                margin: 0;
                padding: 0;
                font-size: 10px;
                line-height: normal;
            }

            .printable {
                position: absolute;
                left: 50%;
                top: 0;
                transform: translateX(-50%);
                width: 350mm;
                height: auto;
                margin-left: 0;
                margin-top: 0;
                padding: 20mm;
                page-break-after: always;
            }

            .card {
                box-shadow: none;
            }

            .no-print {
                display: none;
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
            Grade Credits Analysis
        @endslot
    @endcomponent

    <div class="row no-print">
        <div class="col-md-12 col-lg-12 d-flex justify-content-end">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}">
                <i style="font-size: 20px;" class="bx bx-download text-muted me-2"></i>
            </a>
            <i onclick="printContent()" style="font-size: 20px;margin-bottom:10px;cursor:pointer;"
                class="bx bx-printer text-muted"></i>
        </div>
    </div>
    <div class="row printable">
        <div class="col-md-12 col-lg-12">
            <div class="card">
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
                <div class="card-body">
                    @if ($test?->type == 'CA')
                        <h6>{{ $test?->grade->name ?? 'Grade' }} - End of {{ $test?->name ?? 'Month' }} Grade Credits
                            Analysis</h6>
                    @else
                        <h6>{{ $test?->grade->name ?? 'Grade' }} - End of Term Grade Credits Analysis</h6>
                    @endif
                    @if (!empty($summary))
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Class</th>
                                        <th>Students</th>
                                        <th>>=6 A-C</th>
                                        <th>%</th>
                                        <th>Male %</th>
                                        <th>Female %</th>
                                        <th>>=5 A-C</th>
                                        <th>%</th>
                                        <th>Male %</th>
                                        <th>Female %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($summary as $index => $data)
                                        <tr
                                            @if ($loop->last && $data['name'] === 'Overall') class="table-secondary font-weight-bold" @endif>
                                            <td>{{ $data['name'] ?? 'N/A' }}</td>
                                            <td>{{ $data['students'] ?? 0 }}</td>
                                            <td>{{ $data['gte_6_credits'] ?? 0 }}</td>
                                            <td>{{ isset($data['students']) && $data['students'] > 0 ? number_format(($data['gte_6_credits'] / $data['students']) * 100, 1) : '0.0' }}%
                                            </td>
                                            <td>{{ isset($data['male_count']) && $data['male_count'] > 0 ? number_format(($data['male_gte_6'] / $data['male_count']) * 100, 1) : '0.0' }}%
                                            </td>
                                            <td>{{ isset($data['female_count']) && $data['female_count'] > 0 ? number_format(($data['female_gte_6'] / $data['female_count']) * 100, 1) : '0.0' }}%
                                            </td>
                                            <td>{{ $data['gte_5_credits'] ?? 0 }}</td>
                                            <td>{{ isset($data['students']) && $data['students'] > 0 ? number_format(($data['gte_5_credits'] / $data['students']) * 100, 1) : '0.0' }}%
                                            </td>
                                            <td>{{ isset($data['male_count']) && $data['male_count'] > 0 ? number_format(($data['male_gte_5'] / $data['male_count']) * 100, 1) : '0.0' }}%
                                            </td>
                                            <td>{{ isset($data['female_count']) && $data['female_count'] > 0 ? number_format(($data['female_gte_5'] / $data['female_count']) * 100, 1) : '0.0' }}%
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-5 no-print">
                            <h5>Class Performance Visualization</h5>
                            <canvas id="creditsSummaryChart"></canvas>
                        </div>
                    @else
                        <div class="alert alert-info">No data available for subjects analysis.</div>
                    @endif
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

        function refreshData() {
            location.reload();
        }

        document.addEventListener('DOMContentLoaded', function() {
            var summaryData = @json($summary);
            if (summaryData && summaryData.length > 0) {
                var ctx = document.getElementById('creditsSummaryChart').getContext('2d');

                var classData = summaryData.filter(item => item.name !== 'Overall');

                var labels = classData.map(item => item.name);
                var data6Credits = classData.map(item => {
                    return item.students > 0 ? (item.gte_6_credits / item.students) * 100 : 0;
                });
                var data5Credits = classData.map(item => {
                    return item.students > 0 ? (item.gte_5_credits / item.students) * 100 : 0;
                });

                var chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                                label: '>=6 A-C (%)',
                                data: data6Credits,
                                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1
                            },
                            {
                                label: '>=5 A-C (%)',
                                data: data5Credits,
                                backgroundColor: 'rgba(255, 159, 64, 0.6)',
                                borderColor: 'rgba(255, 159, 64, 1)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
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
                                    text: 'Classes'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            title: {
                                display: true,
                                text: 'Class Performance Comparison'
                            }
                        }
                    }
                });
            } else {
                console.log('No data available for chart');
            }
        });
    </script>
@endsection
