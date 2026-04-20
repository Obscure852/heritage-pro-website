@extends('layouts.master')
@section('title')
    Leave Types
@endsection
@section('css')
    <style>
        .leave-types-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .leave-types-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .leave-types-body {
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

        .status-active {
            background: #d1fae5;
            color: #065f46;
        }

        .status-inactive {
            background: #f3f4f6;
            color: #4b5563;
        }

        .gender-badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .gender-male {
            background: #dbeafe;
            color: #1e40af;
        }

        .gender-female {
            background: #fce7f3;
            color: #be185d;
        }

        .gender-all {
            background: #f3f4f6;
            color: #374151;
        }

        .color-indicator {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            display: inline-block;
            vertical-align: middle;
            border: 1px solid rgba(0, 0, 0, 0.1);
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

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 48px;
            opacity: 0.3;
            margin-bottom: 16px;
        }

        .empty-state h4 {
            color: #374151;
            margin-bottom: 8px;
        }

        .empty-state p {
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .stat-item h4 {
                font-size: 1.25rem;
            }

            .stat-item small {
                font-size: 0.75rem;
            }

            .leave-types-header {
                padding: 20px;
            }

            .leave-types-body {
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

    <div class="leave-types-container">
        <div class="leave-types-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;">Leave Types</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Manage leave type definitions and settings</p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $totalCount }}</h4>
                                <small class="opacity-75">Total</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $activeCount }}</h4>
                                <small class="opacity-75">Active</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $inactiveCount }}</h4>
                                <small class="opacity-75">Inactive</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="leave-types-body">
            <div class="help-text">
                <div class="help-title">Leave Types Management</div>
                <div class="help-content">
                    Define and manage leave types available to staff. Each leave type has configurable settings for
                    entitlement, documentation requirements, and restrictions. Only active leave types are available
                    for staff to request.
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div></div>
                <a href="{{ route('leave.types.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> New Leave Type
                </a>
            </div>

            @if($leaveTypes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Entitlement</th>
                                <th>Gender</th>
                                <th>Half-Day</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($leaveTypes as $index => $leaveType)
                                <tr data-id="{{ $leaveType->id }}">
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        @if($leaveType->color)
                                            <span class="color-indicator me-2" style="background-color: {{ $leaveType->color }};"></span>
                                        @endif
                                        <strong>{{ $leaveType->code }}</strong>
                                    </td>
                                    <td>{{ $leaveType->name }}</td>
                                    <td>{{ number_format($leaveType->default_entitlement, 1) }} days</td>
                                    <td>
                                        @if($leaveType->gender_restriction === 'male')
                                            <span class="gender-badge gender-male">Male Only</span>
                                        @elseif($leaveType->gender_restriction === 'female')
                                            <span class="gender-badge gender-female">Female Only</span>
                                        @else
                                            <span class="gender-badge gender-all">All</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($leaveType->allow_half_day)
                                            <i class="fas fa-check text-success"></i>
                                        @else
                                            <i class="fas fa-times text-muted"></i>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="status-badge {{ $leaveType->is_active ? 'status-active' : 'status-inactive' }}">
                                            {{ $leaveType->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="action-buttons">
                                            <a href="{{ route('leave.types.edit', $leaveType) }}"
                                                class="btn btn-sm btn-outline-info"
                                                title="Edit Leave Type">
                                                <i class="bx bx-edit-alt"></i>
                                            </a>
                                            <button type="button"
                                                class="btn btn-sm btn-outline-warning toggle-status-btn"
                                                data-id="{{ $leaveType->id }}"
                                                data-status="{{ $leaveType->is_active ? '1' : '0' }}"
                                                title="{{ $leaveType->is_active ? 'Deactivate' : 'Activate' }}">
                                                <i class="bx {{ $leaveType->is_active ? 'bx-pause' : 'bx-play' }}"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-calendar-alt"></i>
                    <h4>No Leave Types Defined</h4>
                    <p>Get started by creating your first leave type.</p>
                    <a href="{{ route('leave.types.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Create Leave Type
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle toggle status buttons
            const toggleButtons = document.querySelectorAll('.toggle-status-btn');

            toggleButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    const leaveTypeId = this.dataset.id;
                    const currentStatus = this.dataset.status === '1';
                    const action = currentStatus ? 'deactivate' : 'activate';

                    if (!confirm(`Are you sure you want to ${action} this leave type?`)) {
                        return;
                    }

                    // Disable button during request
                    button.disabled = true;

                    fetch(`{{ url('leave/types') }}/${leaveTypeId}/toggle-status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update UI
                            const row = button.closest('tr');
                            const statusBadge = row.querySelector('.status-badge');
                            const icon = button.querySelector('i');

                            if (data.is_active) {
                                statusBadge.className = 'status-badge status-active';
                                statusBadge.textContent = 'Active';
                                icon.className = 'bx bx-pause';
                                button.title = 'Deactivate';
                                button.dataset.status = '1';
                            } else {
                                statusBadge.className = 'status-badge status-inactive';
                                statusBadge.textContent = 'Inactive';
                                icon.className = 'bx bx-play';
                                button.title = 'Activate';
                                button.dataset.status = '0';
                            }

                            // Show success message
                            showAlert('success', data.message);
                        } else {
                            showAlert('danger', data.message || 'An error occurred.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showAlert('danger', 'An error occurred while updating the status.');
                    })
                    .finally(() => {
                        button.disabled = false;
                    });
                });
            });

            function showAlert(type, message) {
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
                alertDiv.role = 'alert';
                alertDiv.innerHTML = `
                    <strong>${message}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;

                const container = document.querySelector('.leave-types-container');
                container.parentNode.insertBefore(alertDiv, container);

                // Auto-dismiss after 3 seconds
                setTimeout(() => {
                    alertDiv.classList.remove('show');
                    setTimeout(() => alertDiv.remove(), 150);
                }, 3000);
            }
        });
    </script>
@endsection
