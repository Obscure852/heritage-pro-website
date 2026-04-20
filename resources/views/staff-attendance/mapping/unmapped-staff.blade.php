@extends('layouts.master')
@section('title')
    Staff Without Mapping
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

        .stat-item {
            padding: 10px 0;
        }

        .stat-item h4 {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .stat-item small {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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

        .nav-tabs-custom {
            border-bottom: 2px solid #e5e7eb;
            margin-bottom: 20px;
        }

        .nav-tabs-custom .nav-link {
            border: none;
            color: #6b7280;
            padding: 10px 20px;
            font-weight: 500;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
        }

        .nav-tabs-custom .nav-link:hover {
            color: #3b82f6;
            border-color: transparent;
        }

        .nav-tabs-custom .nav-link.active {
            color: #3b82f6;
            border-bottom-color: #3b82f6;
            background: transparent;
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

        .staff-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .staff-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e2e8f0;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .stat-item h4 {
                font-size: 1.25rem;
            }

            .stat-item small {
                font-size: 0.75rem;
            }

            .admissions-header {
                padding: 20px;
            }

            .admissions-body {
                padding: 16px;
            }
        }
    </style>
@endsection
@section('content')
    @if (session('message'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <div class="admissions-container">
        <div class="admissions-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;">Staff Without Biometric Mapping</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Staff members not yet linked to a biometric device ID</p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['unmapped'] }}</h4>
                                <small class="opacity-75">Unmapped IDs</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['total_mapped'] }}</h4>
                                <small class="opacity-75">Mapped</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['staff_without'] }}</h4>
                                <small class="opacity-75">Staff Without</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="admissions-body">
            <div class="help-text">
                <div class="help-title">Staff Without Biometric Mapping</div>
                <div class="help-content">
                    These staff members don't have a biometric ID mapped to their account yet. They will appear
                    in the "Unmapped IDs" list once they clock in on a biometric device, at which point you can
                    manually link their biometric ID to their staff record.
                </div>
            </div>

            <ul class="nav nav-tabs-custom">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('staff-attendance.mapping.index') }}">
                        Unmapped IDs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('staff-attendance.mapping.unmapped-staff') }}">
                        Staff Without Mapping
                    </a>
                </li>
            </ul>

            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Staff Name</th>
                            <th>ID Number</th>
                            <th>Position</th>
                            <th>Department</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($staff as $index => $member)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="staff-cell">
                                        <div class="staff-avatar">
                                            {{ strtoupper(substr($member->firstname ?? '', 0, 1)) }}{{ strtoupper(substr($member->lastname ?? '', 0, 1)) }}
                                        </div>
                                        <div>
                                            <strong>{{ $member->full_name }}</strong>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $member->id_number ?? '-' }}</td>
                                <td>{{ $member->position ?? '-' }}</td>
                                <td>{{ $member->department->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="text-center text-muted" style="padding: 40px 0;">
                                        <i class="bx bx-check-circle" style="font-size: 48px; opacity: 0.3;"></i>
                                        <p class="mt-3 mb-0" style="font-size: 15px;">All staff members have biometric mappings</p>
                                        <p class="text-muted" style="font-size: 13px;">
                                            Every current staff member is linked to a biometric device ID.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        // Auto-dismiss alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const dismissButton = alert.querySelector('.btn-close');
                    if (dismissButton) {
                        dismissButton.click();
                    }
                }, 5000);
            });
        });
    </script>
@endsection
