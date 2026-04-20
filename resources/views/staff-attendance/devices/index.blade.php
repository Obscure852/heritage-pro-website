@extends('layouts.master')
@section('title')
    Attendance Devices
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

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-active { background: #d1fae5; color: #065f46; }
        .status-inactive { background: #fee2e2; color: #991b1b; }

        .type-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .type-hikvision { background: #dbeafe; color: #1e40af; }
        .type-zkteco { background: #d1fae5; color: #065f46; }

        .mode-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .mode-pull { background: #e0e7ff; color: #3730a3; }
        .mode-push { background: #d1fae5; color: #065f46; }
        .mode-agent { background: #fef3c7; color: #92400e; }

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

        .device-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .device-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e2e8f0;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .device-icon.active {
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
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('staff-attendance.settings.index') }}">Back</a>
        @endslot
        @slot('title')
            Staff Attendance
        @endslot
    @endcomponent

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
                    <h3 style="margin:0;">Attendance Devices</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Configure and manage biometric devices</p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['total'] }}</h4>
                                <small class="opacity-75">Total Devices</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['active'] }}</h4>
                                <small class="opacity-75">Active</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['last_sync'] ? $stats['last_sync']->diffForHumans() : 'Never' }}</h4>
                                <small class="opacity-75">Last Sync</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="admissions-body">
            <div class="help-text">
                <div class="help-title">Device Management</div>
                <div class="help-content">
                    Configure biometric attendance devices. Add device connection details (IP, credentials),
                    test connectivity, and manage active/inactive status for automatic synchronization.
                </div>
            </div>

            <div class="row align-items-center mb-3">
                <div class="col-md-6">
                    <h5 class="mb-0">Configured Devices</h5>
                </div>
                <div class="col-md-6 text-end">
                    <a href="{{ route('staff-attendance.devices.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Add Device
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Device</th>
                            <th>Type</th>
                            <th>Mode</th>
                            <th>IP Address</th>
                            <th>Status</th>
                            <th>Last Sync</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($devices as $index => $device)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="device-cell">
                                        <div class="device-icon {{ $device->is_active ? 'active' : '' }}">
                                            <i class="bx bx-fingerprint"></i>
                                        </div>
                                        <div>
                                            <div><strong>{{ $device->name }}</strong></div>
                                            @if ($device->location)
                                                <div class="text-muted" style="font-size: 12px;">{{ $device->location }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="type-badge type-{{ $device->type }}">{{ ucfirst($device->type) }}</span>
                                </td>
                                <td>
                                    <span class="mode-badge mode-{{ $device->connectivity_mode ?? 'pull' }}">
                                        {{ ucfirst($device->connectivity_mode ?? 'pull') }}
                                    </span>
                                </td>
                                <td>
                                    @if($device->ip_address)
                                        {{ $device->ip_address }}:{{ $device->port }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="status-badge status-{{ $device->is_active ? 'active' : 'inactive' }}">
                                        {{ $device->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    {{ $device->last_sync_at ? $device->last_sync_at->diffForHumans() : 'Never' }}
                                </td>
                                <td class="text-end">
                                    <div class="action-buttons">
                                        <a href="{{ route('staff-attendance.devices.edit', $device) }}"
                                            class="btn btn-sm btn-outline-info"
                                            title="Edit Device">
                                            <i class="bx bx-edit-alt"></i>
                                        </a>
                                        <button type="button"
                                            class="btn btn-sm btn-outline-success"
                                            onclick="testConnection({{ $device->id }})"
                                            title="Test Connection"
                                            id="test-btn-{{ $device->id }}">
                                            <i class="bx bx-wifi"></i>
                                        </button>
                                        <button type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="confirmDelete({{ $device->id }}, '{{ $device->name }}')"
                                            title="Delete Device">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </div>
                                    <form id="delete-form-{{ $device->id }}"
                                        action="{{ route('staff-attendance.devices.destroy', $device) }}"
                                        method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="text-center text-muted" style="padding: 40px 0;">
                                        <i class="bx bx-fingerprint" style="font-size: 48px; opacity: 0.3;"></i>
                                        <p class="mt-3 mb-0" style="font-size: 15px;">No devices configured</p>
                                        <a href="{{ route('staff-attendance.devices.create') }}" class="btn btn-primary mt-3">
                                            <i class="fas fa-plus me-1"></i> Add Your First Device
                                        </a>
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
        function testConnection(deviceId) {
            const btn = document.getElementById('test-btn-' + deviceId);
            const originalContent = btn.innerHTML;

            // Show loading state
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
            btn.disabled = true;

            fetch('{{ url("staff-attendance/devices") }}/' + deviceId + '/test', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Restore button
                btn.innerHTML = originalContent;
                btn.disabled = false;

                // Show result
                Swal.fire({
                    icon: data.success ? 'success' : 'error',
                    title: data.success ? 'Connection Test' : 'Connection Failed',
                    text: data.message,
                    confirmButtonColor: '#3b82f6'
                });
            })
            .catch(error => {
                // Restore button
                btn.innerHTML = originalContent;
                btn.disabled = false;

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An unexpected error occurred. Please try again.',
                    confirmButtonColor: '#3b82f6'
                });
            });
        }

        function confirmDelete(deviceId, deviceName) {
            Swal.fire({
                title: 'Delete Device?',
                text: 'Are you sure you want to delete "' + deviceName + '"? This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + deviceId).submit();
                }
            });
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
