@extends('layouts.master')

@section('title')
    Clock In/Out
@endsection

@section('content')
    <style>
        .page-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
            margin-bottom: 0;
        }

        .header h4 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .header p {
            margin: 8px 0 0 0;
            opacity: 0.9;
            font-size: 0.875rem;
        }

        .content-wrapper {
            background: white;
            border-radius: 0 0 3px 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 24px;
        }

        .quick-links {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
        }

        .quick-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: #f3f4f6;
            color: #374151;
            border-radius: 3px;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        .quick-link:hover {
            background: #e5e7eb;
            color: #1f2937;
            text-decoration: none;
        }

        .quick-link i {
            font-size: 1rem;
            color: #6b7280;
        }
    </style>

    <div class="page-content">
        <div class="container-fluid">
            <div class="page-container">
                <div class="header">
                    <h4><i class="bx bx-fingerprint me-2"></i>Self-Service Attendance</h4>
                    <p>Clock in and out for your daily attendance</p>
                </div>

                <div class="content-wrapper">
                    {{-- Include the clock widget --}}
                    @include('staff-attendance.self-service.clock-widget')

                    {{-- Quick Links --}}
                    <div class="quick-links">
                        <a href="{{ route('staff-attendance.manager.dashboard') }}" class="quick-link">
                            <i class="bx bx-group"></i>
                            My Team
                        </a>
                        @can('view-attendance-reports')
                            <a href="{{ route('staff-attendance.reports.daily') }}" class="quick-link">
                                <i class="bx bx-bar-chart-alt-2"></i>
                                Reports
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
