@extends('layouts.master')
@section('title')
    Attendance Codes Report | Attendance
@endsection

@section('css')
    <style>
        /* Screen Styles */
        .report-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .report-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .report-header h3 {
            margin: 0;
            font-weight: 600;
        }

        .report-header p {
            margin: 6px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .report-body {
            padding: 24px;
        }

        .btn-print {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 8px 16px;
            border-radius: 3px;
            font-weight: 500;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .btn-print:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
        }

        .school-header {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .school-name {
            font-weight: 600;
            color: #374151;
            font-size: 16px;
            margin-bottom: 4px;
        }

        .school-info {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.6;
        }

        .school-logo {
            max-height: 80px;
            object-fit: contain;
        }

        .report-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .summary-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 16px;
            margin-bottom: 24px;
        }

        .summary-card h6 {
            font-weight: 600;
            color: #374151;
            margin-bottom: 12px;
        }

        .table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 12px;
            padding: 10px 8px;
            white-space: nowrap;
            text-align: center;
        }

        .table thead th:first-child {
            text-align: left;
        }

        .table tbody td {
            padding: 8px;
            vertical-align: middle;
            color: #4b5563;
            font-size: 13px;
            text-align: center;
        }

        .table tbody td:first-child {
            text-align: left;
            font-weight: 500;
        }

        .table tbody tr:hover {
            background-color: #f8fafc;
        }

        /* Print Styles - Must be preserved exactly */
        @media print {
            body {
                margin: 0;
                padding: 0;
                line-height: normal;
            }

            .card {
                box-shadow: none;
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
                left: 0;
                top: 0;
                margin: 0 auto;
                width: 100%;
                max-width: 100%;
                overflow-x: auto;
            }

            .printable .card {
                margin: 0;
                border: none;
                width: 100%;
            }

            .printable .card-body {
                margin: 0 auto;
                padding: 0;
            }

            .printable .table {
                width: 750px;
                overflow-x: auto;
            }

            .printable .table th,
            .printable .table td {
                padding: 8px;
                text-align: left;
            }

            .report-container,
            .report-header,
            .btn-print {
                display: none !important;
            }
        }

        @media (max-width: 768px) {
            .report-header {
                padding: 20px;
            }

            .report-body {
                padding: 16px;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('attendance.index') }}">Attendance</a>
        @endslot
        @slot('title')
            Attendance Codes Report
        @endslot
    @endcomponent

    <!-- Screen View -->
    <div class="report-container d-print-none">
        <div class="report-header">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h3><i class="fas fa-tags me-2"></i>Attendance Codes Report</h3>
                    <p>{{ $klass->name ?? 'Class' }} - Term: {{ $term->name ?? '' }} ({{ $term->start_date ?? '' }} to {{ $term->end_date ?? '' }})</p>
                </div>
                <button type="button" class="btn-print" onclick="printContent()">
                    <i class="fas fa-print me-1"></i> Print Report
                </button>
            </div>
        </div>

        <div class="report-body">
            <div class="school-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="school-name">{{ $school_data->school_name }}</div>
                        <div class="school-info">
                            {{ $school_data->physical_address }}<br>
                            {{ $school_data->postal_address }}<br>
                            Tel: {{ $school_data->telephone }} | Fax: {{ $school_data->fax }}
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        @if($school_data->logo_path)
                            <img src="{{ asset($school_data->logo_path) }}" alt="School Logo" class="school-logo">
                        @endif
                    </div>
                </div>
            </div>

            @if (!empty($klass))
                <div class="summary-card">
                    <h6><i class="fas fa-chart-pie me-2"></i>Attendance Code Summary</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Attendance Code</th>
                                    <th style="width: 120px;">Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($attendanceCounts as $code => $count)
                                    <tr>
                                        <td>{{ $code }}</td>
                                        <td>{{ $count }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <h5 class="report-title"><i class="fas fa-table me-2"></i>Detailed Attendance by Date</h5>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-striped mb-0">
                        <thead>
                            <tr>
                                <th style="min-width: 150px;">Student</th>
                                @foreach ($dates as $date)
                                    <th style="width: 35px;">{{ $date->format('d') }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($attendanceData as $studentData)
                                <tr>
                                    <td>{{ $students[$studentData['student_id']]->fullname ?? '' }}</td>
                                    @foreach ($dates as $date)
                                        <td>{{ $studentData['attendance'][$date->format('Y-m-d')] ?? '' }}</td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($dates) + 1 }}" class="text-center text-muted py-4">
                                        <i class="fas fa-info-circle fa-2x mb-2 d-block opacity-50"></i>
                                        No attendance data available.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center text-muted py-4">
                    <i class="fas fa-info-circle fa-2x mb-2 d-block opacity-50"></i>
                    No class data available.
                </div>
            @endif
        </div>
    </div>

    <!-- Print View (hidden on screen, shown when printing) -->
    <div class="row printable table-responsive d-none d-print-block">
        <div class="col-md-12">
            <div class="card">
                <div style="height: 120px;" class="card-header">
                    <div class="row">
                        <div class="col-md-6 align-items-start">
                            <div class="form-group">
                                <strong>{{ $school_data->school_name }}</strong>
                                <br>
                                <span style="margin:0;padding:0;"> {{ $school_data->physical_address }}</span>
                                <br>
                                <span style="margin:0;padding:0;"> {{ $school_data->postal_address }}</span>
                                <br>
                                <span>Tel: {{ $school_data->telephone . ' Fax: ' . $school_data->fax }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex justify-content-end">
                            <img height="80" src="{{ public_path($school_data->logo_path) }}" alt="School Logo">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <h5>Attendance Report for {{ $klass->name }} -
                        Term: {{ $term->name }} ({{ $term->start_date }} to {{ $term->end_date }})</h5>
                    @if (!empty($klass))
                        <div class="row mt-4">
                            <div class="col-12">
                                <h6>Attendance Summary:</h6>
                                <table class="table table-bordered table-responsive table-striped">
                                    <thead>
                                        <tr>
                                            <th>Attendance Code</th>
                                            <th>Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($attendanceCounts as $code => $count)
                                            <tr>
                                                <td>{{ $code }}</td>
                                                <td>{{ $count }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <h6>Detailed Attendance:</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-responsive table-striped">
                                        <thead>
                                            <tr>
                                                <th>Student</th>
                                                @foreach ($dates as $date)
                                                    <th>{{ $date->format('d') }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($attendanceData as $studentData)
                                                <tr>
                                                    <td>{{ $students[$studentData['student_id']]->fullname ?? '' }}</td>
                                                    @foreach ($dates as $date)
                                                        <td>{{ $studentData['attendance'][$date->format('Y-m-d')] ?? '' }}</td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
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
    </script>
@endsection
