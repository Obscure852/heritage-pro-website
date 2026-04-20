@extends('layouts.master')
@section('title')
    Biometric Mapping
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

        .action-buttons {
            display: flex;
            gap: 4px;
            justify-content: flex-end;
        }

        .action-buttons .btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .action-buttons .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .action-buttons .btn i {
            font-size: 16px;
        }

        .event-count-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            background: #dbeafe;
            color: #1e40af;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
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
                    <h3 style="margin:0;">Biometric ID Mapping</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Match biometric device IDs to staff records</p>
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
                <div class="help-title">Biometric ID Mapping</div>
                <div class="help-content">
                    When biometric devices sync events, each event includes an employee number. This page shows
                    employee numbers that couldn't be automatically matched to staff records. Use the "Map" button
                    to manually link an employee number to the correct staff member.
                </div>
            </div>

            <ul class="nav nav-tabs-custom">
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('staff-attendance.mapping.index') }}">
                        Unmapped IDs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('staff-attendance.mapping.unmapped-staff') }}">
                        Staff Without Mapping
                    </a>
                </li>
            </ul>

            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee Number</th>
                            <th>First Seen</th>
                            <th>Last Seen</th>
                            <th>Event Count</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($unmappedIds as $index => $unmapped)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><strong>{{ $unmapped->employee_number }}</strong></td>
                                <td>{{ $unmapped->first_seen_at->diffForHumans() }}</td>
                                <td>{{ $unmapped->last_seen_at->diffForHumans() }}</td>
                                <td>
                                    <span class="event-count-badge">{{ $unmapped->event_count }} events</span>
                                </td>
                                <td class="text-end">
                                    <button type="button"
                                        class="btn btn-sm btn-primary"
                                        onclick="openMapModal('{{ $unmapped->employee_number }}')">
                                        <i class="bx bx-link me-1"></i> Map
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="text-center text-muted" style="padding: 40px 0;">
                                        <i class="bx bx-scan" style="font-size: 48px; opacity: 0.3;"></i>
                                        <p class="mt-3 mb-0" style="font-size: 15px;">No unmapped biometric IDs</p>
                                        <p class="text-muted" style="font-size: 13px;">
                                            When biometric devices sync events with employee numbers that can't be automatically matched to staff records, they will appear here for manual mapping.
                                        </p>
                                        @if($stats['staff_without'] > 0)
                                            <p class="text-muted" style="font-size: 13px;">
                                                <strong>Note:</strong> {{ $stats['staff_without'] }} staff members don't have biometric mappings yet. Their IDs will appear here once they clock in on a biometric device.
                                            </p>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Map Modal -->
    <div class="modal fade" id="mapModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('staff-attendance.mapping.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="employee_number" id="modal-employee-number-input">
                    <div class="modal-header">
                        <h5 class="modal-title">Map Biometric ID</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Biometric Employee Number</label>
                            <input type="text" class="form-control" id="modal-employee-number-display" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Select Staff Member</label>
                            <select name="user_id" class="form-select" required>
                                <option value="">Select a staff member...</option>
                                @foreach($unmappedStaff as $staff)
                                    <option value="{{ $staff->id }}">
                                        {{ $staff->full_name }} ({{ $staff->id_number ?? 'No ID' }})
                                    </option>
                                @endforeach
                            </select>
                            @if($unmappedStaff->isEmpty())
                                <div class="form-text text-warning">
                                    <i class="bx bx-info-circle"></i> All staff members are already mapped to biometric IDs.
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" {{ $unmappedStaff->isEmpty() ? 'disabled' : '' }}>
                            Map
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        function openMapModal(employeeNumber) {
            // Set the employee number in hidden input and display field
            document.getElementById('modal-employee-number-input').value = employeeNumber;
            document.getElementById('modal-employee-number-display').value = employeeNumber;

            // Reset select to default
            const select = document.querySelector('#mapModal select[name="user_id"]');
            if (select) {
                select.value = '';
            }

            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById('mapModal'));
            modal.show();
        }

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
