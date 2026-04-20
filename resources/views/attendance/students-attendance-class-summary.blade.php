@extends('layouts.master')
@section('title')
    Attendance Summary | Attendance
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

        .table thead th:first-child,
        .table thead th:nth-child(2) {
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

        .table tbody td:nth-child(2) {
            text-align: left;
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
            Attendance Summary
        @endslot
    @endcomponent

    <!-- Screen View -->
    <div class="report-container d-print-none">
        <div class="report-header">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h3><i class="fas fa-chart-bar me-2"></i>Attendance Summary</h3>
                    <p>{{ $klass->name ?? 'Class' }} - Term Summary by Attendance Code</p>
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

            <h5 class="report-title"><i class="fas fa-list-ol me-2"></i>Attendance Summary by Student</h5>

            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead>
                        <tr>
                            <th style="min-width: 150px;">Student</th>
                            <th style="width: 80px;">Gender</th>
                            @foreach ($codes as $code)
                                <th style="width: 60px;">{{ $code }}</th>
                            @endforeach
                            <th style="width: 80px; background: #fef2f2; color: #dc2626;">Absent Total</th>
                            <th style="width: 70px; background: #eef2ff; color: #4338ca;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($summary as $record)
                            <tr>
                                <td>{{ $record['student_name'] }}</td>
                                <td>{{ $record['gender'] }}</td>
                                @foreach ($codes as $code)
                                    <td>{{ $record['counts'][$code] }}</td>
                                @endforeach
                                <td style="font-weight: 600; background: #fef2f2; color: #dc2626;">{{ $record['absent_total'] }}</td>
                                <td style="font-weight: 600; background: #f5f3ff;">{{ $record['total'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($codes) + 4 }}" class="text-center text-muted py-4">
                                    <i class="fas fa-info-circle fa-2x mb-2 d-block opacity-50"></i>
                                    No attendance data available.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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
                    <h4>Attendance Summary By Term - {{ $klass->name }}</h4>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Gender</th>
                                    @foreach ($codes as $code)
                                        <th>{{ $code }}</th>
                                    @endforeach
                                    <th style="font-weight: 700;">Absent Total</th>
                                    <th style="font-weight: 700;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($summary as $record)
                                    <tr>
                                        <td>{{ $record['student_name'] }}</td>
                                        <td>{{ $record['gender'] }}</td>
                                        @foreach ($codes as $code)
                                            <td>{{ $record['counts'][$code] }}</td>
                                        @endforeach
                                        <td style="font-weight: 600;">{{ $record['absent_total'] }}</td>
                                        <td style="font-weight: 600;">{{ $record['total'] }}</td>
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

@section('script')
    <script>
        function printContent() {
            window.print();
        }
    </script>
@endsection
