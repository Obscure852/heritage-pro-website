@extends('layouts.master')
@section('title')
    Public Holidays
@endsection
@section('css')
    <style>
        .holidays-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .holidays-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .holidays-body {
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

        .status-active {
            background: #d1fae5;
            color: #065f46;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-inactive {
            background: #f3f4f6;
            color: #4b5563;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-recurring {
            background: #dbeafe;
            color: #1e40af;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
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

        .view-toggle {
            display: flex;
            gap: 8px;
        }

        .view-toggle .btn {
            padding: 8px 16px;
            font-size: 13px;
            border-radius: 3px;
        }

        .view-toggle .btn-outline-secondary {
            border: 1px solid #d1d5db;
            color: #374151;
            background: white;
        }

        .view-toggle .btn-outline-secondary:hover,
        .view-toggle .btn-outline-secondary.active {
            background: #f3f4f6;
            border-color: #9ca3af;
        }

        .year-select {
            max-width: 200px;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 8px 12px;
            font-size: 14px;
            color: #374151;
            background-color: #fff;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .year-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .calendar-container {
            display: none;
        }

        .calendar-container.active {
            display: block;
        }

        .list-container {
            display: block;
        }

        .list-container.hidden {
            display: none;
        }

        @media (max-width: 768px) {
            .stat-item h4 {
                font-size: 1.25rem;
            }

            .stat-item small {
                font-size: 0.75rem;
            }

            .holidays-header {
                padding: 20px;
            }

            .holidays-body {
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

    <div class="mb-3 d-flex justify-content-end">
        <select id="yearSelector" class="form-select year-select" onchange="changeYear(this.value)">
            @foreach ($availableYears as $y)
                <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
        </select>
    </div>

    <div class="holidays-container">
        <div class="holidays-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;">Public Holidays</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Manage public holidays for leave calculations</p>
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
                                <h4 class="mb-0 fw-bold text-white">{{ $recurringCount }}</h4>
                                <small class="opacity-75">Recurring</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="holidays-body">
            <div class="help-text">
                <div class="help-title">Public Holidays Management</div>
                <div class="help-content">
                    Manage public holidays that will be excluded from leave day calculations.
                    Recurring holidays repeat on the same date every year (e.g., Christmas on Dec 25).
                    One-time holidays apply only to their specific year.
                </div>
            </div>

            <div class="row align-items-center mb-3">
                <div class="col-md-6">
                    <div class="view-toggle">
                        <button type="button" class="btn btn-outline-secondary active" id="listViewBtn" onclick="showListView()">
                            <i class="fas fa-list me-1"></i> List View
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="calendarViewBtn" onclick="showCalendarView()">
                            <i class="fas fa-calendar-alt me-1"></i> Calendar View
                        </button>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <a href="{{ route('leave.holidays.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> New Holiday
                    </a>
                </div>
            </div>

            <div class="list-container" id="listContainer">
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Name</th>
                                <th>Date</th>
                                <th>Recurring</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($holidays as $index => $holiday)
                                <tr id="holiday-row-{{ $holiday->id }}">
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $holiday->name }}</strong>
                                        @if ($holiday->description)
                                            <br><small class="text-muted">{{ Str::limit($holiday->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        {{ optional($holiday->display_date ?? $holiday->date)->format('M d, Y') }}
                                    </td>
                                    <td>
                                        @if ($holiday->is_recurring)
                                            <span class="badge-recurring"><i class="fas fa-sync-alt me-1"></i> Recurring</span>
                                        @else
                                            <span class="text-muted">One-time</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="{{ $holiday->is_active ? 'status-active' : 'status-inactive' }}" id="status-badge-{{ $holiday->id }}">
                                            {{ $holiday->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="action-buttons">
                                            <a href="{{ route('leave.holidays.edit', $holiday->id) }}"
                                                class="btn btn-sm btn-outline-info"
                                                title="Edit Holiday">
                                                <i class="bx bx-edit-alt"></i>
                                            </a>
                                            <button type="button"
                                                class="btn btn-sm btn-outline-warning"
                                                title="Toggle Status"
                                                onclick="toggleStatus({{ $holiday->id }})">
                                                <i class="bx bx-power-off"></i>
                                            </button>
                                            <button type="button"
                                                class="btn btn-sm btn-outline-danger"
                                                title="Delete Holiday"
                                                onclick="confirmDelete({{ $holiday->id }})">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr id="no-holidays-row">
                                    <td colspan="6">
                                        <div class="text-center text-muted" style="padding: 40px 0;">
                                            <i class="fas fa-calendar-times" style="font-size: 48px; opacity: 0.3;"></i>
                                            <p class="mt-3 mb-0" style="font-size: 15px;">No public holidays for {{ $year }}</p>
                                            <a href="{{ route('leave.holidays.create') }}" class="btn btn-sm btn-primary mt-3">
                                                <i class="fas fa-plus me-1"></i> Add Holiday
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="calendar-container" id="calendarContainer">
                @include('leave.holidays._calendar', ['holidaysByMonth' => $holidaysByMonth, 'year' => $year])
            </div>
        </div>
    </div>

    <form id="deleteForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endsection
@section('script')
    <script>
        function changeYear(year) {
            window.location.href = '{{ route('leave.holidays.index') }}?year=' + year;
        }

        function showListView() {
            document.getElementById('listContainer').classList.remove('hidden');
            document.getElementById('calendarContainer').classList.remove('active');
            document.getElementById('listViewBtn').classList.add('active');
            document.getElementById('calendarViewBtn').classList.remove('active');
        }

        function showCalendarView() {
            document.getElementById('listContainer').classList.add('hidden');
            document.getElementById('calendarContainer').classList.add('active');
            document.getElementById('listViewBtn').classList.remove('active');
            document.getElementById('calendarViewBtn').classList.add('active');
        }

        function toggleStatus(holidayId) {
            fetch(`/leave/holidays/${holidayId}/toggle-status`, {
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
                    const badge = document.getElementById(`status-badge-${holidayId}`);
                    if (data.is_active) {
                        badge.className = 'status-active';
                        badge.textContent = 'Active';
                    } else {
                        badge.className = 'status-inactive';
                        badge.textContent = 'Inactive';
                    }

                    // Show success notification
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Status Updated',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to update status. Please try again.'
                    });
                }
            });
        }

        function confirmDelete(holidayId) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Delete Holiday?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.getElementById('deleteForm');
                        form.action = `/leave/holidays/${holidayId}`;
                        form.submit();
                    }
                });
            } else if (confirm('Are you sure you want to delete this holiday? This action cannot be undone.')) {
                const form = document.getElementById('deleteForm');
                form.action = `/leave/holidays/${holidayId}`;
                form.submit();
            }
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
