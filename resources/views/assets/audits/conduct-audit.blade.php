@extends('layouts.master')
@section('title')
    Conduct Audit: {{ $audit->audit_code }}
@endsection

@push('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('css')
    <style>
        .audit-container {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
        }

        .audit-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .audit-header h4 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }

        .audit-header p {
            margin: 8px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .header-badges {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 12px;
        }

        .header-badge {
            padding: 6px 12px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
        }

        .header-badge.status {
            background: rgba(14, 165, 233, 0.2);
            color: #e0f2fe;
        }

        .header-badge.count {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .header-actions {
            display: flex;
            gap: 8px;
            margin-top: 16px;
        }

        .btn-header {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 8px 16px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-header:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
        }

        .btn-header.success {
            background: rgba(16, 185, 129, 0.3);
            border-color: rgba(16, 185, 129, 0.5);
        }

        .btn-header.success:hover {
            background: rgba(16, 185, 129, 0.4);
        }

        .audit-body {
            padding: 24px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: #f8f9fa;
            border: 1px solid #e5e7eb;
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

        .stat-icon.success {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            color: #10b981;
        }

        .stat-icon.danger {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            color: #ef4444;
        }

        .stat-icon.warning {
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            color: #f59e0b;
        }

        .stat-content h5 {
            margin: 0;
            color: #374151;
            font-weight: 700;
            font-size: 22px;
        }

        .stat-content p {
            margin: 4px 0 0 0;
            color: #6b7280;
            font-size: 13px;
        }

        .progress-section {
            background: #f8f9fa;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 16px;
            margin-bottom: 24px;
        }

        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .progress-header span {
            color: #6b7280;
            font-size: 13px;
        }

        .progress {
            height: 8px;
            background: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-bar {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            transition: width 0.3s ease;
        }

        .filter-section {
            background: #f8f9fa;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 16px;
            margin-bottom: 24px;
        }

        .filter-section .row {
            align-items: center;
        }

        .search-input {
            position: relative;
        }

        .search-input input {
            padding-left: 36px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
        }

        .search-input i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }

        .filter-buttons .btn-check:checked + .btn {
            background: #4e73df;
            border-color: #4e73df;
            color: white;
        }

        .assets-section h5 {
            font-weight: 600;
            color: #374151;
            margin-bottom: 16px;
        }

        .assets-table {
            margin-bottom: 0;
        }

        .assets-table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
            padding: 12px 10px;
        }

        .assets-table tbody td {
            padding: 14px 10px;
            vertical-align: middle;
            font-size: 14px;
            border-bottom: 1px solid #f3f4f6;
        }

        .assets-table tbody tr:hover {
            background-color: #f9fafb;
        }

        .asset-image {
            width: 50px;
            height: 50px;
            border-radius: 3px;
            object-fit: cover;
            border: 1px solid #e5e7eb;
        }

        .asset-placeholder {
            width: 50px;
            height: 50px;
            border-radius: 3px;
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            font-size: 20px;
        }

        .asset-details h6 {
            margin: 0 0 4px 0;
            font-weight: 600;
            color: #374151;
            font-size: 14px;
        }

        .asset-details p {
            margin: 0;
            color: #6b7280;
            font-size: 12px;
        }

        .asset-details .badges {
            display: flex;
            gap: 6px;
            margin-top: 6px;
        }

        .asset-details .badge {
            font-size: 11px;
            padding: 3px 8px;
        }

        .audit-status {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-block;
        }

        .status-badge.pending {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .status-badge.present {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .status-badge.missing {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .status-badge.maintenance {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            color: white;
        }

        .condition-select {
            border: 1px solid #d1d5db;
            border-radius: 3px;
            padding: 6px 10px;
            font-size: 13px;
            min-width: 100px;
        }

        .condition-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .action-buttons-cell .btn-group {
            display: flex;
            gap: 2px;
        }

        .action-buttons-cell .btn {
            width: 36px;
            height: 36px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 3px;
            font-size: 16px;
        }

        .notes-btn {
            position: relative;
        }

        .notes-btn .badge {
            position: absolute;
            top: -4px;
            right: -4px;
            font-size: 9px;
            padding: 2px 5px;
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

        .modal-body h6 {
            font-weight: 600;
            color: #4e73df;
            margin-bottom: 12px;
        }

        .modal-body .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
            font-size: 14px;
        }

        .modal-body .form-control {
            border: 1px solid #d1d5db;
            border-radius: 3px;
            padding: 10px 12px;
            font-size: 14px;
        }

        .modal-body .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .modal-footer {
            border-top: 1px solid #e5e7eb;
            padding: 16px 20px;
        }

        .dropdown-menu {
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-radius: 3px;
        }

        .dropdown-item {
            padding: 10px 16px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .dropdown-item:hover {
            background: #f3f4f6;
        }

        .dropdown-item i {
            width: 18px;
            text-align: center;
        }

        @media (max-width: 992px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .audit-header {
                padding: 20px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .header-actions {
                flex-direction: column;
            }

            .filter-section .row > div {
                margin-bottom: 12px;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('assets.index') }}">Asset Management</a>
        @endslot
        @slot('li_2')
            <a href="{{ route('audits.index') }}">Audits</a>
        @endslot
        @slot('title')
            Conduct Audit
        @endslot
    @endcomponent

    @if (session('message'))
        <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
            <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
            <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="audit-container">
        <div class="audit-header">
            <div class="d-flex justify-content-between align-items-start flex-wrap">
                <div>
                    <h4><i class="bx bx-check-square me-2"></i>{{ $audit->audit_code }}</h4>
                    <p><i class="bx bx-calendar me-1"></i> Audit Date: {{ $audit->audit_date->format('M d, Y') }}</p>
                    <div class="header-badges">
                        @if ($audit->status == 'Pending')
                            <span class="header-badge status">Pending</span>
                        @elseif($audit->status == 'In Progress')
                            <span class="header-badge status">In Progress</span>
                        @elseif($audit->status == 'Completed')
                            <span class="header-badge status">Completed</span>
                        @endif
                        <span class="header-badge count">{{ $audit->auditItems->count() }} Assets</span>
                        <span class="header-badge count">By: {{ $audit->conductedByUser->name ?? 'System' }}</span>
                    </div>
                </div>
                <div class="header-actions">
                    @if ($audit->isPending())
                        <button type="button" class="btn-header success" onclick="startAudit()">
                            <i class="bx bx-play me-1"></i> Start Audit
                        </button>
                    @elseif($audit->isInProgress())
                        <button type="button" class="btn-header success" onclick="completeAudit()">
                            <i class="bx bx-check me-1"></i> Complete Audit
                        </button>
                    @endif

                    <div class="dropdown">
                        <button type="button" class="btn-header dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="#" onclick="exportAuditReport()">
                                    <i class="bx bx-export"></i> Export Report
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" onclick="printAuditReport()">
                                    <i class="bx bx-printer"></i> Print Report
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="{{ route('audits.cancel', $audit->id) }}"
                                   onclick="return confirm('Are you sure you want to cancel this audit?')">
                                    <i class="bx bx-x"></i> Cancel Audit
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="audit-body">
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="bx bx-package"></i>
                    </div>
                    <div class="stat-content">
                        <h5 id="totalAssets">{{ $audit->auditItems->count() }}</h5>
                        <p>Total Assets</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="bx bx-check"></i>
                    </div>
                    <div class="stat-content">
                        <h5 id="checkedAssets">{{ $audit->auditItems->filter(function($item) { return $item->is_present !== null; })->count() }}</h5>
                        <p>Checked</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon danger">
                        <i class="bx bx-x"></i>
                    </div>
                    <div class="stat-content">
                        <h5 id="missingAssets">{{ $audit->auditItems->where('is_present', false)->count() }}</h5>
                        <p>Missing</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="bx bx-wrench"></i>
                    </div>
                    <div class="stat-content">
                        <h5 id="maintenanceAssets">{{ $audit->auditItems->where('needs_maintenance', true)->count() }}</h5>
                        <p>Need Maintenance</p>
                    </div>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="progress-section">
                <div class="progress-header">
                    <span>Audit Progress</span>
                    <span id="progressText">
                        {{ $audit->auditItems->filter(function($item) { return $item->is_present !== null; })->count() }} of {{ $audit->auditItems->count() }} completed
                    </span>
                </div>
                <div class="progress">
                    <div class="progress-bar" role="progressbar"
                         style="width: {{ $audit->auditItems->count() > 0 ? ($audit->auditItems->filter(function($item) { return $item->is_present !== null; })->count() / $audit->auditItems->count()) * 100 : 0 }}%"
                         id="progressBar">
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <div class="row align-items-center">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="search-input">
                            <i class="bx bx-search"></i>
                            <input type="text" class="form-control form-control-sm" id="searchAssets"
                                   placeholder="Search assets..." onkeyup="filterAssets()">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="btn-group btn-group-sm filter-buttons" role="group">
                            <input type="radio" class="btn-check" name="statusFilter" id="filterAll"
                                   value="all" checked onchange="filterByStatus('all')">
                            <label class="btn btn-outline-primary" for="filterAll">All Assets</label>

                            <input type="radio" class="btn-check" name="statusFilter" id="filterPending"
                                   value="pending" onchange="filterByStatus('pending')">
                            <label class="btn btn-outline-warning" for="filterPending">Pending</label>

                            <input type="radio" class="btn-check" name="statusFilter" id="filterPresent"
                                   value="present" onchange="filterByStatus('present')">
                            <label class="btn btn-outline-success" for="filterPresent">Present</label>

                            <input type="radio" class="btn-check" name="statusFilter" id="filterMissing"
                                   value="missing" onchange="filterByStatus('missing')">
                            <label class="btn btn-outline-danger" for="filterMissing">Missing</label>

                            <input type="radio" class="btn-check" name="statusFilter" id="filterMaintenance"
                                   value="maintenance" onchange="filterByStatus('maintenance')">
                            <label class="btn btn-outline-info" for="filterMaintenance">Maintenance</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assets Table -->
            <div class="assets-section">
                <h5><i class="bx bx-list-check me-2"></i>Assets to Audit</h5>

                <div class="table-responsive">
                    <table class="table assets-table" id="assetsTable">
                        <thead>
                            <tr>
                                <th style="width: 60px;">Image</th>
                                <th>Asset Details</th>
                                <th style="width: 120px;">Status</th>
                                <th style="width: 120px;">Condition</th>
                                <th style="width: 150px;">Actions</th>
                                <th style="width: 80px;">Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($audit->auditItems as $auditItem)
                                <tr class="asset-row"
                                    data-asset-id="{{ $auditItem->asset_id }}"
                                    data-audit-item-id="{{ $auditItem->id }}"
                                    data-status="{{ $auditItem->is_present === null ? 'pending' : ($auditItem->is_present ? 'present' : 'missing') }}"
                                    data-maintenance="{{ $auditItem->needs_maintenance ? 'yes' : 'no' }}">
                                    <td class="text-center">
                                        @if ($auditItem->asset && $auditItem->asset->image_path)
                                            <img src="{{ asset('storage/' . $auditItem->asset->image_path) }}"
                                                 alt="{{ $auditItem->asset->name }}" class="asset-image">
                                        @else
                                            <div class="asset-placeholder">
                                                <i class="bx bx-package"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="asset-details">
                                            <h6>{{ $auditItem->asset->name ?? 'Unknown Asset' }}</h6>
                                            <p>{{ $auditItem->asset->asset_code ?? 'N/A' }}</p>
                                            <div class="badges">
                                                @if($auditItem->asset && $auditItem->asset->category)
                                                    <span class="badge bg-info">{{ $auditItem->asset->category->name }}</span>
                                                @endif
                                                @if($auditItem->asset && $auditItem->asset->venue)
                                                    <span class="badge bg-secondary">{{ $auditItem->asset->venue->name }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="audit-status">
                                            @if($auditItem->is_present === null)
                                                <span class="status-badge pending">Pending</span>
                                            @elseif($auditItem->is_present)
                                                <span class="status-badge present">Present</span>
                                            @else
                                                <span class="status-badge missing">Missing</span>
                                            @endif

                                            @if($auditItem->needs_maintenance)
                                                <span class="status-badge maintenance">Maintenance</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <select class="form-select form-select-sm condition-select"
                                                data-audit-item-id="{{ $auditItem->id }}"
                                                {{ $audit->isCompleted() ? 'disabled' : '' }}>
                                            <option value="">Select</option>
                                            <option value="New" {{ $auditItem->condition == 'New' ? 'selected' : '' }}>New</option>
                                            <option value="Good" {{ $auditItem->condition == 'Good' ? 'selected' : '' }}>Good</option>
                                            <option value="Fair" {{ $auditItem->condition == 'Fair' ? 'selected' : '' }}>Fair</option>
                                            <option value="Poor" {{ $auditItem->condition == 'Poor' ? 'selected' : '' }}>Poor</option>
                                        </select>
                                    </td>
                                    <td class="action-buttons-cell">
                                        @if(!$audit->isCompleted())
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-success mark-present-btn"
                                                        data-audit-item-id="{{ $auditItem->id }}"
                                                        data-bs-toggle="tooltip" title="Mark Present"
                                                        {{ $auditItem->is_present === true ? 'disabled' : '' }}>
                                                    <i class="bx bx-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger mark-missing-btn"
                                                        data-audit-item-id="{{ $auditItem->id }}"
                                                        data-bs-toggle="tooltip" title="Mark Missing"
                                                        {{ $auditItem->is_present === false ? 'disabled' : '' }}>
                                                    <i class="bx bx-x"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-warning flag-maintenance-btn"
                                                        data-audit-item-id="{{ $auditItem->id }}"
                                                        data-bs-toggle="tooltip" title="Flag for Maintenance"
                                                        {{ $auditItem->needs_maintenance ? 'disabled' : '' }}>
                                                    <i class="bx bx-wrench"></i>
                                                </button>
                                            </div>
                                        @else
                                            <span class="text-muted small">Completed</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-secondary notes-btn"
                                                data-bs-toggle="modal" data-bs-target="#notesModal"
                                                data-audit-item-id="{{ $auditItem->id }}"
                                                data-asset-name="{{ $auditItem->asset->name ?? 'Unknown' }}"
                                                data-notes="{{ $auditItem->notes }}">
                                            <i class="bx bx-note"></i>
                                            @if($auditItem->notes)
                                                <span class="badge bg-primary rounded-pill">1</span>
                                            @endif
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Notes Modal -->
    <div class="modal fade" id="notesModal" tabindex="-1" aria-labelledby="notesModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="notesModalLabel"><i class="bx bx-note me-2"></i>Asset Notes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6 id="assetNameInModal">Asset Name</h6>
                    <form id="notesForm">
                        <div class="mb-3">
                            <label for="auditNotes" class="form-label">Notes</label>
                            <textarea class="form-control" id="auditNotes" rows="4"
                                      placeholder="Add any observations or issues..."></textarea>
                        </div>
                        <input type="hidden" id="auditItemIdForNotes">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="saveNotes()">
                        <i class="bx bx-save me-1"></i> Save Notes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="confirmationMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmActionBtn">Confirm</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        let auditId = {{ $audit->id }};
        let auditStatus = '{{ $audit->status }}';

        const routes = {
            updateAuditItem: "{{ route('audits.update-audit-item', ['auditItemId' => 'TEMP_AUDIT_ITEM_ID']) }}",
            getProgress: "{{ route('audits.get-progress', ['auditId' => 'TEMP_AUDIT_ID']) }}",
            completeAudit: "{{ route('audits.complete', ['id' => 'TEMP_AUDIT_ID']) }}",
            exportReport: "{{ route('audits.export-report', ['auditId' => 'TEMP_AUDIT_ID']) }}",
            printReport: "{{ route('audits.print-report', ['auditId' => 'TEMP_AUDIT_ID']) }}"
        };

        function getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
        }

        document.addEventListener('DOMContentLoaded', function() {
            initializeEventListeners();
            initializeTooltips();
        });

        function initializeTooltips() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }

        function initializeEventListeners() {
            document.querySelectorAll('.mark-present-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (auditStatus === 'Completed') return;
                    const auditItemId = this.dataset.auditItemId;
                    markAsPresent(auditItemId);
                });
            });

            document.querySelectorAll('.mark-missing-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (auditStatus === 'Completed') return;
                    const auditItemId = this.dataset.auditItemId;
                    markAsMissing(auditItemId);
                });
            });

            document.querySelectorAll('.flag-maintenance-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (auditStatus === 'Completed') return;
                    const auditItemId = this.dataset.auditItemId;
                    flagForMaintenance(auditItemId);
                });
            });

            document.querySelectorAll('.condition-select').forEach(select => {
                select.addEventListener('change', function() {
                    if (auditStatus === 'Completed') return;
                    const auditItemId = this.dataset.auditItemId;
                    const condition = this.value;
                    if (condition) updateCondition(auditItemId, condition);
                });
            });

            document.querySelectorAll('.notes-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const auditItemId = this.dataset.auditItemId;
                    const assetName = this.dataset.assetName;
                    const notes = this.dataset.notes || '';

                    document.getElementById('assetNameInModal').textContent = assetName;
                    document.getElementById('auditNotes').value = notes;
                    document.getElementById('auditItemIdForNotes').value = auditItemId;
                });
            });
        }

        function markAsPresent(auditItemId) {
            updateAssetStatus(auditItemId, { is_present: true }, 'Asset marked as present');
        }

        function markAsMissing(auditItemId) {
            updateAssetStatus(auditItemId, { is_present: false }, 'Asset marked as missing');
        }

        function flagForMaintenance(auditItemId) {
            updateAssetStatus(auditItemId, { needs_maintenance: true }, 'Asset flagged for maintenance');
        }

        function updateCondition(auditItemId, condition) {
            updateAssetStatus(auditItemId, { condition: condition }, 'Asset condition updated');
        }

        function updateAssetStatus(auditItemId, data, successMessage) {
            const url = routes.updateAuditItem.replace('TEMP_AUDIT_ITEM_ID', auditItemId);

            fetch(url, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    showAlert(successMessage, 'success');
                    updateProgress();
                    updateRowStatus(auditItemId, data);
                } else {
                    showAlert(result.message || 'Error updating asset status', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Network error occurred. Please try again.', 'error');
            });
        }

        function updateRowStatus(auditItemId, data) {
            const row = document.querySelector(`tr[data-audit-item-id="${auditItemId}"]`);
            if (!row) return;

            const statusCell = row.querySelector('.audit-status');

            if (data.is_present !== undefined) {
                const existingBadges = statusCell.querySelectorAll('.status-badge:not(.maintenance)');
                existingBadges.forEach(badge => badge.remove());

                const statusBadge = document.createElement('span');
                statusBadge.className = data.is_present ? 'status-badge present' : 'status-badge missing';
                statusBadge.textContent = data.is_present ? 'Present' : 'Missing';
                statusCell.insertBefore(statusBadge, statusCell.firstChild);

                row.dataset.status = data.is_present ? 'present' : 'missing';
                const presentBtn = row.querySelector('.mark-present-btn');
                const missingBtn = row.querySelector('.mark-missing-btn');

                if (presentBtn && missingBtn) {
                    if (data.is_present) {
                        presentBtn.disabled = true;
                        missingBtn.disabled = false;
                    } else {
                        presentBtn.disabled = false;
                        missingBtn.disabled = true;
                    }
                }
            }

            if (data.needs_maintenance) {
                if (!statusCell.querySelector('.maintenance')) {
                    const maintenanceBadge = document.createElement('span');
                    maintenanceBadge.className = 'status-badge maintenance';
                    maintenanceBadge.textContent = 'Maintenance';
                    statusCell.appendChild(maintenanceBadge);
                }

                row.dataset.maintenance = 'yes';
                const maintenanceBtn = row.querySelector('.flag-maintenance-btn');
                if (maintenanceBtn) maintenanceBtn.disabled = true;
            }
        }

        function updateProgress() {
            const url = routes.getProgress.replace('TEMP_AUDIT_ID', auditId);

            fetch(url)
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    const data = result.data;

                    updateElementText('checkedAssets', data.checked);
                    updateElementText('missingAssets', data.missing);
                    updateElementText('maintenanceAssets', data.maintenance);

                    const progressBar = document.getElementById('progressBar');
                    const progressText = document.getElementById('progressText');

                    if (progressBar) progressBar.style.width = data.progress_percentage + '%';
                    if (progressText) progressText.textContent = `${data.checked} of ${data.total} completed`;
                }
            })
            .catch(error => {
                console.error('Error updating progress:', error);
            });
        }

        function updateElementText(elementId, text) {
            const element = document.getElementById(elementId);
            if (element) element.textContent = text;
        }

        function saveNotes() {
            const auditItemId = document.getElementById('auditItemIdForNotes').value;
            const notes = document.getElementById('auditNotes').value;

            updateAssetStatus(auditItemId, { notes: notes }, 'Notes saved successfully');
            const notesBtn = document.querySelector(`[data-audit-item-id="${auditItemId}"].notes-btn`);
            if (notesBtn) {
                notesBtn.dataset.notes = notes;

                const badge = notesBtn.querySelector('.badge');
                if (notes && !badge) {
                    const newBadge = document.createElement('span');
                    newBadge.className = 'badge bg-primary rounded-pill';
                    newBadge.textContent = '1';
                    notesBtn.appendChild(newBadge);
                } else if (!notes && badge) {
                    badge.remove();
                }
            }

            const modal = bootstrap.Modal.getInstance(document.getElementById('notesModal'));
            if (modal) modal.hide();
        }

        function filterAssets() {
            const searchTerm = document.getElementById('searchAssets').value.toLowerCase();
            const rows = document.querySelectorAll('.asset-row');

            rows.forEach(row => {
                const assetDetails = row.querySelector('.asset-details');
                const assetName = assetDetails?.querySelector('h6')?.textContent.toLowerCase() || '';
                const assetCode = assetDetails?.querySelector('p')?.textContent.toLowerCase() || '';

                const shouldShow = assetName.includes(searchTerm) || assetCode.includes(searchTerm);
                row.style.display = shouldShow ? '' : 'none';
            });
        }

        function filterByStatus(status) {
            const rows = document.querySelectorAll('.asset-row');

            rows.forEach(row => {
                let shouldShow = false;

                switch(status) {
                    case 'all':
                        shouldShow = true;
                        break;
                    case 'pending':
                        shouldShow = row.dataset.status === 'pending';
                        break;
                    case 'present':
                        shouldShow = row.dataset.status === 'present';
                        break;
                    case 'missing':
                        shouldShow = row.dataset.status === 'missing';
                        break;
                    case 'maintenance':
                        shouldShow = row.dataset.maintenance === 'yes';
                        break;
                }

                row.style.display = shouldShow ? '' : 'none';
            });
        }

        function completeAudit() {
            showConfirmation('Are you sure you want to complete this audit? This action cannot be undone.', () => {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = routes.completeAudit.replace('TEMP_AUDIT_ID', auditId);

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = getCsrfToken();
                form.appendChild(csrfToken);

                showAlert('Completing audit...', 'success');
                document.body.appendChild(form);
                form.submit();
            });
        }

        function exportAuditReport() {
            const url = routes.exportReport.replace('TEMP_AUDIT_ID', auditId);
            window.open(url, '_blank');
        }

        function printAuditReport() {
            const url = routes.printReport.replace('TEMP_AUDIT_ID', auditId);
            window.open(url, '_blank');
        }

        function showConfirmation(message, callback) {
            const confirmationMessage = document.getElementById('confirmationMessage');
            const confirmActionBtn = document.getElementById('confirmActionBtn');
            const confirmationModal = document.getElementById('confirmationModal');

            if (confirmationMessage) confirmationMessage.textContent = message;
            if (confirmActionBtn) {
                confirmActionBtn.onclick = () => {
                    callback();
                    const modal = bootstrap.Modal.getInstance(confirmationModal);
                    if (modal) modal.hide();
                };
            }

            if (confirmationModal) {
                new bootstrap.Modal(confirmationModal).show();
            }
        }

        function showAlert(message, type) {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const iconClass = type === 'success' ? 'mdi-check-all' : 'mdi-block-helper';

            const alertHtml = `
                <div class="alert ${alertClass} alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi ${iconClass} label-icon"></i><strong>${message}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;

            const container = document.querySelector('.audit-body');
            if (container) {
                container.insertAdjacentHTML('afterbegin', alertHtml);
                if (type === 'success') {
                    setTimeout(() => {
                        const alert = container.querySelector(`.${alertClass}`);
                        if (alert) alert.remove();
                    }, 5000);
                }
            }
        }
    </script>
@endsection
