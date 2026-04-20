@extends('layouts.master')
@section('title', 'User Assignments')

@section('css')
    <style>
        .user-assignments-container {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
        }

        .user-assignments-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .user-assignments-header h4 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }

        .user-assignments-header p {
            margin: 8px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .user-assignments-body {
            padding: 24px;
        }

        .user-info-card {
            background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);
            border-radius: 3px;
            padding: 24px;
            margin-bottom: 24px;
            color: white;
        }

        .user-contact-info {
            margin-bottom: 0;
        }

        .user-contact-info h6 {
            color: rgba(255, 255, 255, 0.9);
            font-weight: 600;
            margin-bottom: 16px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .user-contact-info p {
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 10px;
            font-size: 14px;
            display: flex;
            align-items: center;
        }

        .user-contact-info p i {
            margin-right: 10px;
            width: 18px;
            text-align: center;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        .stat-card {
            background: white;
            border-radius: 3px;
            padding: 16px;
            display: flex;
            align-items: center;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 14px;
            font-size: 22px;
        }

        .stat-icon.primary {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            color: #3b82f6;
        }

        .stat-icon.danger {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            color: #ef4444;
        }

        .stat-icon.success {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            color: #10b981;
        }

        .stat-content p {
            margin: 0;
            color: #6b7280;
            font-size: 12px;
        }

        .stat-content h5 {
            margin: 4px 0 0 0;
            color: #374151;
            font-weight: 700;
            font-size: 20px;
        }

        .nav-tabs-custom {
            border-bottom: 2px solid #e5e7eb;
            gap: 4px;
        }

        .nav-tabs-custom .nav-link {
            border: none;
            color: #6b7280;
            padding: 12px 20px;
            font-weight: 500;
            border-radius: 3px 3px 0 0;
            background: transparent;
            position: relative;
            transition: all 0.2s ease;
        }

        .nav-tabs-custom .nav-link:hover {
            color: #374151;
            background: #f9fafb;
        }

        .nav-tabs-custom .nav-link.active {
            color: #4e73df;
            background: white;
        }

        .nav-tabs-custom .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        }

        .nav-tabs-custom .badge {
            font-size: 11px;
            padding: 3px 8px;
        }

        .tab-content {
            padding: 20px 0 0 0;
        }

        .assignments-table {
            margin-bottom: 0;
        }

        .assignments-table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
            padding: 12px 10px;
            white-space: nowrap;
        }

        .assignments-table tbody td {
            padding: 14px 10px;
            vertical-align: middle;
            font-size: 14px;
            border-bottom: 1px solid #f3f4f6;
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

        .asset-cell {
            display: flex;
            align-items: center;
        }

        .asset-image {
            width: 48px;
            height: 48px;
            border-radius: 3px;
            object-fit: cover;
            margin-right: 12px;
            border: 1px solid #e5e7eb;
        }

        .asset-placeholder {
            width: 48px;
            height: 48px;
            border-radius: 3px;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            color: #3b82f6;
            font-size: 20px;
        }

        .asset-info h6 {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
        }

        .asset-info h6 a {
            color: inherit;
            text-decoration: none;
        }

        .asset-info h6 a:hover {
            color: #4e73df;
        }

        .asset-info .asset-code {
            color: #6b7280;
            font-size: 12px;
            margin: 2px 0;
        }

        .asset-info .category-badge {
            background: #dbeafe;
            color: #1e40af;
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 3px;
            font-weight: 500;
        }

        .overdue-badge {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            font-size: 10px;
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: 600;
            display: inline-block;
            margin-top: 4px;
        }

        .condition-badge {
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
        }

        .condition-badge.new {
            background: #dcfce7;
            color: #166534;
        }

        .condition-badge.good {
            background: #dbeafe;
            color: #1e40af;
        }

        .condition-badge.fair {
            background: #fef3c7;
            color: #b45309;
        }

        .condition-badge.poor {
            background: #fee2e2;
            color: #dc2626;
        }

        .condition-change {
            display: flex;
            align-items: center;
            gap: 8px;
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

        .empty-state {
            text-align: center;
            padding: 48px 20px;
        }

        .empty-state-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: #9ca3af;
            font-size: 32px;
        }

        .empty-state h5 {
            color: #374151;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: #6b7280;
            margin-bottom: 20px;
        }

        .empty-state .btn {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            padding: 10px 20px;
            font-weight: 500;
        }

        .empty-state .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        /* Modal Styling */
        .modal-content {
            border: none;
            border-radius: 3px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            border-bottom: 1px solid #e5e7eb;
            padding: 16px 20px;
        }

        .modal-header .modal-title {
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
        }

        .modal-body .form-control:focus,
        .modal-body .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .modal-body .form-control-static {
            background: #f9fafb;
            padding: 10px 12px;
            border-radius: 3px;
            color: #374151;
            font-size: 14px;
        }

        .modal-footer {
            border-top: 1px solid #e5e7eb;
            padding: 16px 20px;
        }

        @media (max-width: 992px) {
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            .user-assignments-header {
                padding: 20px;
            }

            .user-info-card {
                padding: 20px;
            }

            .user-info-card .row > div:first-child {
                margin-bottom: 20px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('assets.assignments.index') }}">Back</a>
        @endslot
        @slot('title')
            User Assignments
        @endslot
    @endcomponent

    @if(session('message'))
        <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
            <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="user-assignments-container">
        <div class="user-assignments-header">
            <h4><i class="bx bx-user me-2"></i>{{ $user->full_name }}</h4>
            <p>{{ $user->position ?? 'Staff' }} {{ $user->department ? '- ' . $user->department : '' }}</p>
        </div>

        <div class="user-assignments-body">
            <!-- User Info Card with Stats -->
            <div class="user-info-card">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <div class="user-contact-info">
                            <h6>Contact Information</h6>
                            <p><i class="bx bx-envelope"></i> {{ $user->email ?? 'No email provided' }}</p>
                            <p><i class="bx bx-phone"></i> {{ $user->phone ?? 'No phone provided' }}</p>
                            <p><i class="bx bx-map"></i> {{ $user->address ?? 'No address provided' }}</p>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-icon primary">
                                    <i class="bx bx-package"></i>
                                </div>
                                <div class="stat-content">
                                    <p>Currently Assigned</p>
                                    <h5>{{ $assignments->where('status', 'Assigned')->count() }}</h5>
                                </div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon danger">
                                    <i class="bx bx-time-five"></i>
                                </div>
                                <div class="stat-content">
                                    <p>Overdue</p>
                                    <h5>{{ $assignments->where('status', 'Assigned')->filter(function($a) { return $a->isOverdue(); })->count() }}</h5>
                                </div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon success">
                                    <i class="bx bx-check-circle"></i>
                                </div>
                                <div class="stat-content">
                                    <p>Returned</p>
                                    <h5>{{ $assignments->where('status', 'Returned')->count() }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Nav tabs -->
            <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#current" role="tab">
                        <i class="bx bx-package me-1"></i> Current Assignments
                        <span class="badge bg-primary rounded-pill ms-1">{{ $assignments->where('status', 'Assigned')->count() }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#history" role="tab">
                        <i class="bx bx-history me-1"></i> Assignment History
                        <span class="badge bg-secondary rounded-pill ms-1">{{ $assignments->where('status', 'Returned')->count() }}</span>
                    </a>
                </li>
            </ul>

            <!-- Tab panes -->
            <div class="tab-content">
                <!-- Current Assignments Tab -->
                <div class="tab-pane active" id="current" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table assignments-table align-middle">
                            <thead>
                                <tr>
                                    <th>Asset</th>
                                    <th>Assigned Date</th>
                                    <th>Expected Return</th>
                                    <th>Duration</th>
                                    <th>Condition</th>
                                    <th class="text-end" style="width: 120px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $currentAssignments = $assignments->where('status', 'Assigned'); @endphp
                                @forelse($currentAssignments as $assignment)
                                    <tr class="{{ $assignment->isOverdue() ? 'overdue-row' : '' }}">
                                        <td>
                                            <div class="asset-cell">
                                                @if($assignment->asset && $assignment->asset->image_path)
                                                    <img src="{{ asset('storage/' . $assignment->asset->image_path) }}"
                                                        alt="" class="asset-image">
                                                @else
                                                    <div class="asset-placeholder">
                                                        <i class="bx bx-package"></i>
                                                    </div>
                                                @endif
                                                <div class="asset-info">
                                                    <h6>
                                                        <a href="{{ route('assets.show', $assignment->asset_id) }}">
                                                            {{ $assignment->asset->name ?? 'Unknown Asset' }}
                                                        </a>
                                                    </h6>
                                                    <p class="asset-code">{{ $assignment->asset->asset_code ?? '' }}</p>
                                                    <span class="category-badge">{{ $assignment->asset->category->name ?? 'Uncategorized' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $assignment->assigned_date->format('M d, Y') }}</td>
                                        <td>
                                            @if($assignment->expected_return_date)
                                                {{ $assignment->expected_return_date->format('M d, Y') }}
                                                @if($assignment->isOverdue())
                                                    <span class="overdue-badge">
                                                        Overdue by {{ now()->diffInDays($assignment->expected_return_date) }} days
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-muted">Not specified</span>
                                            @endif
                                        </td>
                                        <td>{{ $assignment->assigned_date->diffInDays(now()) }} days</td>
                                        <td>
                                            @php
                                                $condition = strtolower($assignment->condition_on_assignment ?? 'good');
                                            @endphp
                                            <span class="condition-badge {{ $condition }}">
                                                {{ $assignment->condition_on_assignment }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="{{ route('assets.return-asset', $assignment->asset->id) }}"
                                                    class="btn btn-sm btn-outline-success"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    title="Return Asset">
                                                    <i class="bx bx-undo"></i>
                                                </a>
                                                <a href="{{ route('assets.show', $assignment->asset_id) }}"
                                                    class="btn btn-sm btn-outline-info"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    title="View Asset">
                                                    <i class="bx bx-show"></i>
                                                </a>
                                                @if($assignment->isOverdue())
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-warning send-reminder"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="Send Reminder"
                                                        data-assignment-id="{{ $assignment->id }}"
                                                        data-user-name="{{ $user->firstname }} {{ $user->lastname }}"
                                                        data-asset-name="{{ $assignment->asset->name ?? 'N/A' }}">
                                                        <i class="bx bx-envelope"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            <div class="empty-state">
                                                <div class="empty-state-icon">
                                                    <i class="bx bx-package"></i>
                                                </div>
                                                <h5>No Current Assignments</h5>
                                                <p>This user has no active asset assignments.</p>
                                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignAssetModal">
                                                    <i class="bx bx-plus-circle me-1"></i> Assign New Asset
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Assignment History Tab -->
                <div class="tab-pane" id="history" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table assignments-table align-middle">
                            <thead>
                                <tr>
                                    <th>Asset</th>
                                    <th>Assigned Date</th>
                                    <th>Return Date</th>
                                    <th>Duration</th>
                                    <th>Condition Change</th>
                                    <th class="text-end" style="width: 120px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $pastAssignments = $assignments->where('status', 'Returned'); @endphp

                                @forelse($pastAssignments as $assignment)
                                    <tr>
                                        <td>
                                            <div class="asset-cell">
                                                @if($assignment->asset && $assignment->asset->image_path)
                                                    <img src="{{ asset('storage/' . $assignment->asset->image_path) }}"
                                                        alt="" class="asset-image">
                                                @else
                                                    <div class="asset-placeholder">
                                                        <i class="bx bx-package"></i>
                                                    </div>
                                                @endif
                                                <div class="asset-info">
                                                    <h6>
                                                        <a href="{{ route('assets.show', $assignment->asset_id) }}">
                                                            {{ $assignment->asset->name ?? 'Unknown Asset' }}
                                                        </a>
                                                    </h6>
                                                    <p class="asset-code">{{ $assignment->asset->asset_code ?? '' }}</p>
                                                    <span class="category-badge">{{ $assignment->asset->category->name ?? 'Uncategorized' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $assignment->assigned_date->format('M d, Y') }}</td>
                                        <td>{{ $assignment->actual_return_date ? $assignment->actual_return_date->format('M d, Y') : 'N/A' }}</td>
                                        <td>
                                            @if($assignment->actual_return_date)
                                                {{ $assignment->assigned_date->diffInDays($assignment->actual_return_date) }} days
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $assignCondition = strtolower($assignment->condition_on_assignment ?? 'good');
                                                $returnCondition = strtolower($assignment->condition_on_return ?? 'good');
                                            @endphp
                                            <div class="condition-change">
                                                <span class="condition-badge {{ $assignCondition }}">
                                                    {{ $assignment->condition_on_assignment }}
                                                </span>
                                                <i class="bx bx-right-arrow-alt text-muted"></i>
                                                <span class="condition-badge {{ $returnCondition }}">
                                                    {{ $assignment->condition_on_return }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="{{ route('assets.show-assignment', $assignment->id) }}"
                                                    class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    title="View Assignment">
                                                    <i class="bx bx-show"></i>
                                                </a>
                                                <a href="{{ route('assets.show', $assignment->asset_id) }}"
                                                    class="btn btn-sm btn-outline-info"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    title="View Asset">
                                                    <i class="bx bx-package"></i>
                                                </a>
                                                <a href="{{ route('assets.print-assignment', $assignment->id) }}"
                                                    class="btn btn-sm btn-outline-secondary"
                                                    target="_blank"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    title="Print Assignment">
                                                    <i class="bx bx-printer"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            <div class="empty-state">
                                                <div class="empty-state-icon">
                                                    <i class="bx bx-history"></i>
                                                </div>
                                                <h5>No Assignment History</h5>
                                                <p>This user has not returned any assets yet.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
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
                            <label class="form-label">Recipient</label>
                            <p class="form-control-static">{{ $user->firstname }} {{ $user->lastname }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Asset</label>
                            <p id="reminder_asset_name" class="form-control-static"></p>
                        </div>
                        <div class="mb-3">
                            <label for="reminder_message" class="form-label">Reminder Message</label>
                            <textarea class="form-control" id="reminder_message" name="message" rows="4" required>This is a reminder that the asset assigned to you is now overdue for return. Please return the asset as soon as possible or contact the IT department if you need an extension.</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="bx bx-send me-1"></i> Send Reminder
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
        // Asset select condition sync
        const assetSelect = document.getElementById('asset_id');
        const conditionSelect = document.getElementById('condition_on_assignment');

        if (assetSelect && conditionSelect) {
            assetSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const assetCondition = selectedOption.getAttribute('data-condition');

                if (assetCondition) {
                    for (let i = 0; i < conditionSelect.options.length; i++) {
                        if (conditionSelect.options[i].value === assetCondition) {
                            conditionSelect.selectedIndex = i;
                            break;
                        }
                    }
                }
            });
        }

        // Date validation
        const assignedDateInput = document.getElementById('assigned_date');
        const expectedReturnDateInput = document.getElementById('expected_return_date');

        if (assignedDateInput && expectedReturnDateInput) {
            assignedDateInput.addEventListener('change', function() {
                expectedReturnDateInput.min = this.value;
            });
            expectedReturnDateInput.min = assignedDateInput.value;
        }

        // Reminder modal handling
        const reminderBtns = document.querySelectorAll('.send-reminder');
        if (reminderBtns.length > 0) {
            reminderBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const assignmentId = this.getAttribute('data-assignment-id');
                    const assetName = this.getAttribute('data-asset-name');

                    document.getElementById('reminder_assignment_id').value = assignmentId;
                    document.getElementById('reminder_asset_name').textContent = assetName;

                    const reminderModal = new bootstrap.Modal(document.getElementById('reminderModal'));
                    reminderModal.show();
                });
            });
        }

        // Tab persistence
        const tabLinks = document.querySelectorAll('.nav-link');
        tabLinks.forEach(link => {
            link.addEventListener('shown.bs.tab', function (e) {
                localStorage.setItem('activeUserAssignmentTab', e.target.getAttribute('href'));
            });
        });

        const activeTab = localStorage.getItem('activeUserAssignmentTab');
        if (activeTab) {
            const tab = document.querySelector(`a[href="${activeTab}"]`);
            if (tab) {
                const bsTab = new bootstrap.Tab(tab);
                bsTab.show();
            }
        }

        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Select2 initialization
        if (typeof $.fn.select2 !== 'undefined') {
            $('#asset_id').select2({
                dropdownParent: $('#assignAssetModal'),
                placeholder: 'Search for an asset...',
                allowClear: true
            });
        }
    });
</script>
@endsection
