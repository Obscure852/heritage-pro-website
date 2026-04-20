@extends('layouts.master')
@section('title', 'Asset Assignments')

@section('css')
    <style>
        .assignments-container {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
        }

        .assignments-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .assignments-header h4 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }

        .assignments-header p {
            margin: 8px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .assignments-body {
            padding: 24px;
        }

        .help-text {
            background: #f8f9fa;
            border-left: 4px solid #4e73df;
            padding: 16px 20px;
            margin-bottom: 20px;
            border-radius: 0 3px 3px 0;
        }

        .help-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
            font-size: 14px;
        }

        .help-content {
            color: #6b7280;
            font-size: 13px;
            margin: 0;
        }

        /* Action Buttons */
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

        .reports-dropdown .dropdown-toggle {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .reports-dropdown .dropdown-toggle:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .reports-dropdown .dropdown-toggle:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .reports-dropdown .dropdown-menu {
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-radius: 3px;
            padding: 8px 0;
            min-width: 220px;
            margin-top: 4px;
        }

        .reports-dropdown .dropdown-item {
            padding: 8px 16px;
            font-size: 14px;
            color: #374151;
        }

        .reports-dropdown .dropdown-item:hover {
            background: #f3f4f6;
            color: #1f2937;
        }

        .reports-dropdown .dropdown-item i {
            width: 20px;
            margin-right: 8px;
        }

        /* Filter Controls */
        .controls .form-control,
        .controls .form-select {
            font-size: 0.9rem;
            border: 1px solid #d1d5db;
            border-radius: 3px;
        }

        .controls .form-control:focus,
        .controls .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .controls .input-group-text {
            background: #f9fafb;
            border: 1px solid #d1d5db;
            border-right: none;
            color: #6b7280;
        }

        .controls .input-group .form-control {
            border-left: none;
        }

        .controls .input-group .form-control:focus {
            border-left: none;
        }

        .controls .form-check-label {
            font-size: 13px;
            color: #374151;
        }

        .assignments-table {
            width: 100%;
            border-collapse: collapse;
        }

        .assignments-table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
            padding: 12px 10px;
            text-align: left;
        }

        .assignments-table tbody td {
            padding: 12px 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 14px;
            color: #4b5563;
            vertical-align: middle;
        }

        .assignments-table tbody tr:hover {
            background-color: #f9fafb;
        }

        .assignments-table tbody tr.overdue-row {
            background-color: #fef2f2;
        }

        .assignments-table tbody tr.overdue-row:hover {
            background-color: #fee2e2;
        }

        .assignments-table .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
        }

        .action-buttons {
            display: flex;
            gap: 4px;
            flex-wrap: wrap;
        }

        .action-buttons .btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 3px;
        }

        .action-buttons .btn i {
            font-size: 16px;
        }

        .count-badge {
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
            background: #dbeafe;
            color: #1e40af;
        }

        .empty-state {
            text-align: center;
            padding: 48px 24px;
        }

        .empty-state-icon {
            width: 64px;
            height: 64px;
            background: #f3f4f6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
        }

        .empty-state-icon i {
            font-size: 28px;
            color: #9ca3af;
        }

        .empty-state h5 {
            color: #374151;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: #6b7280;
            margin-bottom: 16px;
        }

        /* Modal Theming */
        .modal-content {
            border: none;
            border-radius: 3px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            border-bottom: 1px solid #e5e7eb;
            padding: 16px 20px;
        }

        .modal-title {
            font-weight: 600;
            color: #374151;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-body .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
            font-size: 14px;
        }

        .modal-body .form-control,
        .modal-body .form-select {
            border: 1px solid #d1d5db;
            border-radius: 3px;
            padding: 10px 12px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .modal-body .form-control:focus,
        .modal-body .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .modal-footer {
            border-top: 1px solid #e5e7eb;
            padding: 16px 20px;
        }

        .modal-footer .btn-primary {
            padding: 8px 16px;
        }

        @media (max-width: 768px) {
            .assignments-header {
                padding: 20px;
            }

            .controls .row > div {
                margin-bottom: 12px;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('assets.index') }}">Back</a>
        @endslot
        @slot('title')
            Asset Assignments
        @endslot
    @endcomponent

    @if (session('message'))
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <div class="assignments-container">
        <div class="assignments-header">
            <h4><i class="bx bx-user-check me-2"></i>Asset Assignments</h4>
            <p>Manage and track all asset assignments across your organization</p>
        </div>

        <div class="assignments-body">
            <div class="help-text">
                <div class="help-title"><i class="fas fa-info-circle me-2"></i>Assignment Management</div>
                <p class="help-content">Track asset assignments to staff members, monitor overdue returns, and manage the complete assignment lifecycle.</p>
            </div>

            <!-- Filters and Action Buttons Row -->
            <div class="row align-items-center mb-3">
                <div class="col-lg-8 col-md-12">
                    <div class="controls">
                        <form id="filterForm" method="GET" action="{{ route('assets.assignments.index') }}">
                            <div class="row g-2 align-items-center">
                                <div class="col-lg-2 col-md-3 col-sm-6">
                                    <select class="form-select" name="status">
                                        <option value="">All Status</option>
                                        <option value="Assigned" {{ request('status') == 'Assigned' ? 'selected' : '' }}>Assigned</option>
                                        <option value="Returned" {{ request('status') == 'Returned' ? 'selected' : '' }}>Returned</option>
                                    </select>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-6">
                                    <select class="form-select" name="asset_id">
                                        <option value="">All Assets</option>
                                        @foreach ($assets as $asset)
                                            <option value="{{ $asset->id }}" {{ request('asset_id') == $asset->id ? 'selected' : '' }}>
                                                {{ $asset->name }} ({{ $asset->asset_code }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-6">
                                    <select class="form-select" name="user_id">
                                        <option value="">All Users</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->lastname }}, {{ $user->firstname }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-2 col-sm-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="overdue" name="overdue" value="1" {{ request('overdue') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="overdue">Overdue</label>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-md-12">
                                    <button type="button" class="btn btn-light w-100" id="resetFilters">Reset</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-lg-end gap-2">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignAssetModal">
                            <i class="fas fa-plus me-1"></i> Assign Asset
                        </button>
                        <div class="btn-group reports-dropdown">
                            <button type="button" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-chart-bar me-2"></i>Reports<i class="fas fa-chevron-down ms-2" style="font-size: 10px;"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ route('assets.current-assignments-report') }}">
                                        <i class="fas fa-clipboard-list text-primary"></i> Current Assignments
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('assets.assignment-history-report') }}">
                                        <i class="fas fa-history text-info"></i> Assignment History
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('assets.assignments-by-user-report') }}">
                                        <i class="fas fa-user text-success"></i> By User
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assignments Table -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 fw-semibold"><i class="bx bx-list-ul me-2"></i>Assignment Records</h6>
                <span class="count-badge">{{ $assignments->total() }} Records</span>
            </div>

            <div class="table-responsive">
                <table class="assignments-table">
                    <thead>
                        <tr>
                            <th>Asset</th>
                            <th>Assigned To</th>
                            <th>
                                <a href="{{ route('assets.assignments.index', array_merge(request()->except(['sort', 'direction']), ['sort' => 'assigned_date', 'direction' => request('sort') == 'assigned_date' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                    Assigned Date
                                    @if (request('sort') == 'assigned_date')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Expected Return</th>
                            <th>Return Date</th>
                            <th>Status</th>
                            <th>Condition</th>
                            <th style="width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignments as $assignment)
                            <tr class="{{ $assignment->isOverdue() ? 'overdue-row' : '' }}">
                                <td>
                                    <a href="{{ route('assets.show', $assignment->asset_id) }}" class="fw-bold text-decoration-none text-primary">
                                        {{ $assignment->asset->name ?? 'N/A' }}
                                    </a>
                                    <small class="d-block text-muted">{{ $assignment->asset->asset_code ?? '' }}</small>
                                </td>
                                <td>
                                    @if ($assignment->assignable_type === 'App\\Models\\User')
                                        {{ $assignment->assignable->lastname ?? '' }}, {{ $assignment->assignable->firstname ?? '' }}
                                    @else
                                        {{ $assignment->assignable_type }} {{ $assignment->assignable_id }}
                                    @endif
                                </td>
                                <td>{{ $assignment->assigned_date->format('M d, Y') }}</td>
                                <td>
                                    @if ($assignment->expected_return_date)
                                        {{ $assignment->expected_return_date->format('M d, Y') }}
                                        @if ($assignment->isOverdue())
                                            <span class="badge bg-danger ms-1">Overdue</span>
                                        @endif
                                    @else
                                        <span class="text-muted">Not specified</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($assignment->actual_return_date)
                                        {{ $assignment->actual_return_date->format('M d, Y') }}
                                    @else
                                        <span class="badge bg-warning">Not returned</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($assignment->status === 'Assigned')
                                        <span class="badge bg-info">Assigned</span>
                                    @elseif($assignment->status === 'Returned')
                                        <span class="badge bg-success">Returned</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $assignment->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($assignment->status === 'Assigned')
                                        {{ $assignment->condition_on_assignment }}
                                    @else
                                        <small>Assigned: {{ $assignment->condition_on_assignment }}</small><br>
                                        <small>Return: {{ $assignment->condition_on_return }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('assets.show', $assignment->asset_id) }}" class="btn btn-sm btn-outline-info" title="View Asset">
                                            <i class="bx bx-package"></i>
                                        </a>
                                        @if ($assignment->status === 'Assigned' && !$assignment->actual_return_date)
                                            <a href="{{ route('assets.return-asset', $assignment->asset->id) }}" class="btn btn-sm btn-outline-primary" title="Process Return">
                                                <i class="bx bx-undo"></i>
                                            </a>
                                        @endif
                                        @if ($assignment->assignable_type === 'App\Models\User')
                                            <a href="{{ route('assets.show-user-assignments', $assignment->assignable_id) }}" class="btn btn-sm btn-outline-secondary" title="User's Assignments">
                                                <i class="bx bx-user"></i>
                                            </a>
                                        @endif
                                        @if ($assignment->isOverdue())
                                            <button type="button" class="btn btn-sm btn-outline-warning send-reminder" title="Send Reminder"
                                                data-assignment-id="{{ $assignment->id }}"
                                                data-user-name="{{ $assignment->assignable_type === 'App\Models\User' ? $assignment->assignable->fullName ?? '' : '' }}"
                                                data-asset-name="{{ $assignment->asset->name ?? 'N/A' }}">
                                                <i class="bx bx-envelope"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="bx bx-clipboard"></i>
                                        </div>
                                        <h5>No Assignment Records Found</h5>
                                        <p>No assignments match your current search criteria.</p>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignAssetModal">
                                            <i class="fas fa-plus me-1"></i> Create New Assignment
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $assignments->links() }}
            </div>
        </div>
    </div>

    <!-- Reminder Modal -->
    <div class="modal fade" id="reminderModal" tabindex="-1" aria-labelledby="reminderModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reminderModalLabel"><i class="bx bx-envelope me-2"></i>Send Return Reminder</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="reminderForm" action="#" method="POST">
                    @csrf
                    <input type="hidden" name="assignment_id" id="reminder_assignment_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Asset</label>
                            <p id="reminder_asset_name" class="form-control-plaintext fw-bold"></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Assigned To</label>
                            <p id="reminder_user_name" class="form-control-plaintext"></p>
                        </div>
                        <div class="mb-3">
                            <label for="reminder_message" class="form-label">Reminder Message</label>
                            <textarea class="form-control" id="reminder_message" name="message" rows="4" required>This is a reminder that the asset assigned to you is now overdue for return. Please return the asset as soon as possible or contact the IT department if you need an extension.</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning"><i class="bx bx-send me-1"></i>Send Reminder</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Asset Assignment Modal -->
    <div class="modal fade" id="assignAssetModal" tabindex="-1" aria-labelledby="assignAssetModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignAssetModalLabel"><i class="bx bx-user-plus me-2"></i>Assign Asset</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="assignAssetForm" action="{{ route('assets.store-assignment') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="user_id" class="form-label">Assign To Staff Member <span class="text-danger">*</span></label>
                            <select class="form-select" id="user_id" name="user_id" required>
                                <option value="">Select Staff Member</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->lastname . ', ' . $user->firstname }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="asset_id" class="form-label">Select Asset <span class="text-danger">*</span></label>
                            <select class="form-select" id="asset_id" name="asset_id" required>
                                <option value="">Select an available asset</option>
                                @foreach ($availableAssets as $asset)
                                    <option value="{{ $asset->id }}" data-condition="{{ $asset->condition }}">
                                        {{ $asset->name }} ({{ $asset->asset_code }}) - {{ $asset->category->name ?? 'Uncategorized' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="assigned_date" class="form-label">Assignment Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="assigned_date" name="assigned_date" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="expected_return_date" class="form-label">Expected Return Date</label>
                                <input type="date" class="form-control" id="expected_return_date" name="expected_return_date">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="condition_on_assignment" class="form-label">Condition <span class="text-danger">*</span></label>
                            <select class="form-select" id="condition_on_assignment" name="condition_on_assignment" required>
                                <option value="New">New</option>
                                <option value="Good">Good</option>
                                <option value="Fair">Fair</option>
                                <option value="Poor">Poor</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="assignment_notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="assignment_notes" name="assignment_notes" rows="3" placeholder="Optional notes about this assignment..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bx bx-x me-1"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-check me-1"></i>Assign Asset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-submit on filter change
            const filterSelects = document.querySelectorAll('#filterForm select');
            filterSelects.forEach(select => {
                select.addEventListener('change', function() {
                    document.getElementById('filterForm').submit();
                });
            });

            // Overdue checkbox auto-submit
            document.getElementById('overdue').addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });

            // Reset filters
            document.getElementById('resetFilters').addEventListener('click', function() {
                window.location.href = '{{ route('assets.assignments.index') }}';
            });

            // Reminder modal
            const reminderButtons = document.querySelectorAll('.send-reminder');
            reminderButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const assignmentId = this.getAttribute('data-assignment-id');
                    const userName = this.getAttribute('data-user-name');
                    const assetName = this.getAttribute('data-asset-name');

                    document.getElementById('reminder_assignment_id').value = assignmentId;
                    document.getElementById('reminder_user_name').textContent = userName;
                    document.getElementById('reminder_asset_name').textContent = assetName;

                    const reminderModal = new bootstrap.Modal(document.getElementById('reminderModal'));
                    reminderModal.show();
                });
            });

            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
@endsection
