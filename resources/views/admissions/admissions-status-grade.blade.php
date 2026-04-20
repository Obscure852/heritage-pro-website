@extends('layouts.master')
@section('title')
    Admissions Status Report
@endsection
@section('css')
    <style>
        .admissions-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .admissions-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .admissions-body {
            padding: 24px;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px;
            border-left: 4px solid #3b82f6;
            border-radius: 0 3px 3px 0;
            margin-bottom: 20px;
        }

        .help-text .help-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }

        .help-text .help-content {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.4;
        }

        .school-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 20px;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 24px;
            border-radius: 3px;
        }

        .school-info {
            font-size: 14px;
            color: #374151;
        }

        .school-info strong {
            font-size: 16px;
            color: #1f2937;
        }

        .school-logo img {
            max-height: 80px;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
        }

        .action-buttons .btn {
            padding: 8px 16px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .action-buttons .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .status-section {
            margin-bottom: 24px;
        }

        .status-title {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e7eb;
        }

        .table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
        }

        .table tbody tr:hover {
            background-color: #f9fafb;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
                line-height: normal;
            }

            .admissions-container {
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
                position: relative;
                margin: 0 auto;
                width: 100%;
                max-width: 100%;
            }

            .action-buttons,
            .admissions-header,
            .help-text {
                display: none !important;
            }

            .printable .table {
                width: 100%;
            }

            .printable .table th,
            .printable .table td {
                padding: 8px;
                text-align: left;
            }
        }

        @media (max-width: 768px) {
            .admissions-header {
                padding: 20px;
            }

            .admissions-body {
                padding: 16px;
            }

            .school-header {
                flex-direction: column;
                gap: 16px;
            }
        }
    </style>
@endsection
@section('content')
    <div class="admissions-container">
        <div class="admissions-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 style="margin:0;">Admissions Analysis By Status</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">View students grouped by their admission status</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('admissions.index') }}" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back"></i> Back to Admissions
                    </a>
                </div>
            </div>
        </div>
        <div class="admissions-body">
            <div class="help-text">
                <div class="help-title">Status Report</div>
                <div class="help-content">
                    This report shows all students grouped by their admission status. Use the export or print buttons to save a copy.
                </div>
            </div>

            <div class="action-buttons justify-content-end">
                <a href="{{ route('admissions.admission-export') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bx bx-export"></i> Export
                </a>
                <button onclick="printContent()" class="btn btn-outline-secondary btn-sm">
                    <i class="bx bx-printer"></i> Print
                </button>
            </div>

            <div class="printable">
                <div class="school-header">
                    <div class="school-info">
                        <strong>{{ $school_data->school_name }}</strong><br>
                        <span>{{ $school_data->physical_address }}</span><br>
                        <span>{{ $school_data->postal_address }}</span><br>
                        <span>Tel: {{ $school_data->telephone }} | Fax: {{ $school_data->fax }}</span>
                    </div>
                    <div class="school-logo">
                        <img src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                    </div>
                </div>

                <h5 class="mb-4" style="font-weight: 600; color: #1f2937;">Students by Admission Status</h5>

                @foreach ($admissionsByStatus as $status => $admissions)
                    <div class="status-section">
                        <h6 class="status-title">{{ $status }}</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Gender</th>
                                        <th>Date Of Birth</th>
                                        <th>Year</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($admissions as $admission)
                                        <tr>
                                            <td>{{ $admission->first_name }}</td>
                                            <td>{{ $admission->last_name }}</td>
                                            <td>{{ $admission->gender }}</td>
                                            <td>{{ $admission->formatted_date_of_birth }}</td>
                                            <td>{{ $admission->year }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="{{ URL::asset('/assets/libs/pristinejs/pristinejs.min.js') }}"></script>
    <script src="{{ URL::asset('/assets/js/pages/form-validation.init.js') }}"></script>
    <script>
        function printContent() {
            window.print();
        }
    </script>
@endsection
