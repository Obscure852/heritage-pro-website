@extends('layouts.master')
@section('title', 'Audit Details')

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
            margin-top: 12px;
        }

        .header-badge {
            padding: 6px 12px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .header-badge.pending {
            background: rgba(245, 158, 11, 0.2);
            color: #fef3c7;
        }

        .header-badge.in-progress {
            background: rgba(14, 165, 233, 0.2);
            color: #e0f2fe;
        }

        .header-badge.completed {
            background: rgba(16, 185, 129, 0.2);
            color: #d1fae5;
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
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 20px;
            display: flex;
            align-items: center;
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 16px;
            font-size: 24px;
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
            font-size: 24px;
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
            padding: 20px;
            margin-bottom: 24px;
        }

        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .progress-header h6 {
            margin: 0;
            font-weight: 600;
            color: #374151;
        }

        .progress-header span {
            color: #6b7280;
            font-size: 14px;
        }

        .progress {
            height: 10px;
            background: #e5e7eb;
            border-radius: 5px;
            overflow: hidden;
        }

        .progress-bar {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        }

        .info-section {
            margin-bottom: 24px;
        }

        .info-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            height: 100%;
        }

        .info-card-header {
            background: #f9fafb;
            padding: 14px 20px;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 15px;
        }

        .info-card-body {
            padding: 20px;
        }

        .info-row {
            display: flex;
            padding: 10px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            width: 40%;
            font-weight: 500;
            color: #6b7280;
            font-size: 14px;
        }

        .info-value {
            width: 60%;
            color: #374151;
            font-size: 14px;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-badge.pending {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .status-badge.in-progress {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            color: white;
        }

        .status-badge.completed {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .next-audit-badge {
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 3px;
            margin-left: 6px;
        }

        .next-audit-badge.overdue {
            background: #fee2e2;
            color: #dc2626;
        }

        .next-audit-badge.soon {
            background: #fef3c7;
            color: #b45309;
        }

        .notes-content {
            color: #6b7280;
            font-size: 14px;
            line-height: 1.6;
            font-style: italic;
        }

        .assets-section {
            margin-top: 24px;
        }

        .assets-section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .assets-section-header h5 {
            margin: 0;
            font-weight: 600;
            color: #374151;
        }

        .filter-buttons .btn {
            font-size: 12px;
            padding: 6px 12px;
        }

        .filter-buttons .btn.active {
            background: #4e73df;
            border-color: #4e73df;
            color: white;
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

        .asset-cell {
            display: flex;
            align-items: center;
        }

        .asset-image {
            width: 40px;
            height: 40px;
            border-radius: 3px;
            object-fit: cover;
            margin-right: 12px;
            border: 1px solid #e5e7eb;
        }

        .asset-placeholder {
            width: 40px;
            height: 40px;
            border-radius: 3px;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            color: #3b82f6;
            font-size: 18px;
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

        .empty-assets {
            text-align: center;
            padding: 48px 20px;
        }

        .empty-assets-icon {
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

        .empty-assets h5 {
            color: #374151;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .empty-assets p {
            color: #6b7280;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .btn-back {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
            padding: 10px 20px;
            border-radius: 3px;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .btn-back:hover {
            background: #e5e7eb;
            color: #1f2937;
        }

        .btn-action {
            padding: 10px 20px;
            border-radius: 3px;
            font-weight: 500;
            font-size: 14px;
            border: none;
            transition: all 0.2s ease;
        }

        .btn-action.start {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .btn-action.start:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            color: white;
        }

        .btn-action.continue {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .btn-action.continue:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
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

            .info-row {
                flex-direction: column;
            }

            .info-label,
            .info-value {
                width: 100%;
            }

            .info-label {
                margin-bottom: 4px;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('audits.index') }}">Back</a>
        @endslot
        @slot('li_2')
            <a href="{{ route('audits.index') }}">Audits</a>
        @endslot
        @slot('title')
            Audit Details
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

    @if (session('audit_summary'))
        <div class="alert alert-info alert-dismissible alert-label-icon label-arrow fade show" role="alert">
            <i class="mdi mdi-information label-icon"></i>
            <strong>Audit Completed!</strong>
            {{ session('audit_summary.present_assets') }}/{{ session('audit_summary.total_assets') }} assets found
            ({{ session('audit_summary.completion_rate') }}% completion rate)
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="audit-container">
        <div class="audit-header">
            <div class="d-flex justify-content-between align-items-start flex-wrap">
                <div>
                    <h4><i class="bx bx-clipboard me-2"></i>{{ $audit->audit_code }}</h4>
                    <p>Audit Date: {{ $audit->audit_date->format('M d, Y') }}</p>
                    <div class="header-badges">
                        @if ($audit->status === 'Pending')
                            <span class="header-badge pending">Pending</span>
                        @elseif($audit->status === 'In Progress')
                            <span class="header-badge in-progress">In Progress</span>
                        @elseif($audit->status === 'Completed')
                            <span class="header-badge completed">Completed</span>
                        @endif
                    </div>
                </div>
                <div class="header-actions">
                    @if ($audit->status === 'Pending')
                        <a href="{{ route('audits.start', $audit->id) }}" class="btn-header success"
                           onclick="return confirm('Are you sure you want to start this audit?')">
                            <i class="bx bx-play me-1"></i> Start Audit
                        </a>
                        <a href="{{ route('audits.edit', $audit->id) }}" class="btn-header">
                            <i class="bx bx-edit me-1"></i> Edit
                        </a>
                    @elseif($audit->status === 'In Progress')
                        <a href="{{ route('audits.conduct', $audit->id) }}" class="btn-header success">
                            <i class="bx bx-check-square me-1"></i> Continue Audit
                        </a>
                    @endif

                    <div class="dropdown">
                        <button type="button" class="btn-header dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @if ($audit->status === 'Completed')
                                <li>
                                    <a class="dropdown-item" href="{{ route('audits.missing-report', $audit->id) }}">
                                        <i class="bx bx-error-circle text-danger"></i> Missing Assets Report
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('audits.maintenance-report', $audit->id) }}">
                                        <i class="bx bx-wrench text-warning"></i> Maintenance Report
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('audits.condition-report', $audit->id) }}">
                                        <i class="fas fa-heart text-info"></i> Asset Condition Report
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('audits.location-report', $audit->id) }}">
                                        <i class="fas fa-map-marker-alt text-success"></i> Location Analysis Report
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('audits.financial-report', $audit->id) }}">
                                        <i class="fas fa-dollar-sign text-secondary"></i> Financial Impact Report
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                            @endif
                            <li>
                                <a class="dropdown-item" href="{{ route('audits.summary', $audit->id) }}">
                                    <i class="bx bx-printer"></i> Print Summary
                                </a>
                            </li>
                            @if ($audit->status !== 'In Progress')
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('audits.destroy', $audit->id) }}" method="POST" id="delete-form-{{ $audit->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="dropdown-item text-danger delete-audit" data-audit-id="{{ $audit->id }}">
                                            <i class="bx bx-trash"></i> Delete Audit
                                        </button>
                                    </form>
                                </li>
                            @endif
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
                        <h5>{{ $totalAssets }}</h5>
                        <p>Total Assets</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="bx bx-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h5>{{ $presentAssets }}</h5>
                        <p>Present</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon danger">
                        <i class="bx bx-x-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h5>{{ $missingAssets }}</h5>
                        <p>Missing</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="bx bx-wrench"></i>
                    </div>
                    <div class="stat-content">
                        <h5>{{ $maintenanceNeeded }}</h5>
                        <p>Need Maintenance</p>
                    </div>
                </div>
            </div>

            <!-- Progress Bar -->
            @if ($audit->status !== 'Pending')
                <div class="progress-section">
                    <div class="progress-header">
                        <h6>Audit Progress</h6>
                        <span>{{ $completionPercentage }}% Complete</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar"
                             style="width: {{ $completionPercentage }}%;"
                             aria-valuenow="{{ $completionPercentage }}"
                             aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            @endif

            <!-- Audit Details -->
            <div class="info-section">
                <div class="row">
                    <div class="col-md-6 mb-4 mb-md-0">
                        <div class="info-card">
                            <div class="info-card-header"><i class="bx bx-info-circle me-2"></i>Audit Information</div>
                            <div class="info-card-body">
                                <div class="info-row">
                                    <span class="info-label">Audit Code:</span>
                                    <span class="info-value">{{ $audit->audit_code }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Audit Date:</span>
                                    <span class="info-value">{{ $audit->audit_date->format('M d, Y') }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Status:</span>
                                    <span class="info-value">
                                        @if ($audit->status === 'Pending')
                                            <span class="status-badge pending">Pending</span>
                                        @elseif($audit->status === 'In Progress')
                                            <span class="status-badge in-progress">In Progress</span>
                                        @elseif($audit->status === 'Completed')
                                            <span class="status-badge completed">Completed</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $audit->status }}</span>
                                        @endif
                                    </span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Conducted By:</span>
                                    <span class="info-value">{{ $audit->conductedByUser->firstname ?? '' }} {{ $audit->conductedByUser->lastname ?? '' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Next Audit Date:</span>
                                    <span class="info-value">
                                        @if ($audit->next_audit_date)
                                            {{ $audit->next_audit_date->format('M d, Y') }}
                                            @if ($audit->next_audit_date->isPast())
                                                <span class="next-audit-badge overdue">Overdue</span>
                                            @elseif($audit->next_audit_date->diffInDays(now()) <= 30)
                                                <span class="next-audit-badge soon">Soon</span>
                                            @endif
                                        @else
                                            <span class="text-muted">Not scheduled</span>
                                        @endif
                                    </span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Created:</span>
                                    <span class="info-value">{{ $audit->created_at->format('M d, Y H:i') }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Last Updated:</span>
                                    <span class="info-value">{{ $audit->updated_at->format('M d, Y H:i') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-card">
                            <div class="info-card-header"><i class="bx bx-note me-2"></i>Audit Notes</div>
                            <div class="info-card-body">
                                <p class="notes-content">
                                    {{ $audit->notes ?: 'No notes provided for this audit.' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assets List -->
            <div class="assets-section">
                <div class="assets-section-header">
                    <h5><i class="bx bx-list-ul me-2"></i>Assets in Audit</h5>
                    <div class="btn-group filter-buttons">
                        <button class="btn btn-sm btn-outline-primary active" onclick="filterAssets('all')" id="filter-all">
                            <i class="fas fa-list me-1"></i>All
                        </button>
                        <button class="btn btn-sm btn-outline-success" onclick="filterAssets('present')" id="filter-present">
                            <i class="fas fa-check-circle me-1"></i>Present
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="filterAssets('missing')" id="filter-missing">
                            <i class="fas fa-times-circle me-1"></i>Missing
                        </button>
                        <button class="btn btn-sm btn-outline-warning" onclick="filterAssets('maintenance')" id="filter-maintenance">
                            <i class="fas fa-tools me-1"></i>Maintenance
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table assets-table">
                        <thead>
                            <tr>
                                <th>Asset</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Condition</th>
                                <th>Maintenance</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($audit->auditItems as $item)
                                <tr class="audit-item"
                                    data-status="{{ $item->is_present ? 'present' : 'missing' }}"
                                    data-maintenance="{{ $item->needs_maintenance ? 'true' : 'false' }}">
                                    <td>
                                        <div class="asset-cell">
                                            @if($item->asset && $item->asset->image_path)
                                                <img src="{{ asset('storage/' . $item->asset->image_path) }}"
                                                     alt="" class="asset-image">
                                            @else
                                                <div class="asset-placeholder">
                                                    <i class="bx bx-package"></i>
                                                </div>
                                            @endif
                                            <div class="asset-info">
                                                <h6>
                                                    <a href="{{ route('assets.show', $item->asset_id) }}">
                                                        {{ $item->asset->name ?? 'Unknown Asset' }}
                                                    </a>
                                                </h6>
                                                <span class="asset-code">{{ $item->asset->asset_code ?? '' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $item->asset->category->name ?? 'Uncategorized' }}</td>
                                    <td>
                                        @if ($item->is_present)
                                            <span class="status-badge completed">Present</span>
                                        @else
                                            <span class="status-badge pending" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">Missing</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($item->condition)
                                            @php $conditionClass = strtolower($item->condition); @endphp
                                            <span class="condition-badge {{ $conditionClass }}">
                                                {{ $item->condition }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($item->needs_maintenance)
                                            <span class="status-badge pending">Required</span>
                                        @else
                                            <span class="text-muted">No</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($item->notes)
                                            <small>{{ Str::limit($item->notes, 50) }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-assets">
                                            <div class="empty-assets-icon">
                                                <i class="bx bx-package"></i>
                                            </div>
                                            <h5>No Assets</h5>
                                            <p>No assets have been added to this audit.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="{{ route('audits.index') }}" class="btn-back">
                    <i class="bx bx-arrow-back me-1"></i> Back
                </a>

                @if ($audit->status === 'Pending')
                    <a href="{{ route('audits.start', $audit->id) }}" class="btn-action start"
                       onclick="return confirm('Are you sure you want to start this audit?')">
                        <i class="bx bx-play me-1"></i> Start Audit
                    </a>
                @elseif($audit->status === 'In Progress')
                    <a href="{{ route('audits.conduct', $audit->id) }}" class="btn-action continue">
                        <i class="bx bx-check-square me-1"></i> Continue Audit
                    </a>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.delete-audit');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const auditId = this.getAttribute('data-audit-id');

                    if (confirm('Are you sure you want to delete this audit? This action cannot be undone.')) {
                        document.getElementById('delete-form-' + auditId).submit();
                    }
                });
            });

            filterAssets('all');
        });

        function filterAssets(filter) {
            const items = document.querySelectorAll('.audit-item');
            const buttons = document.querySelectorAll('.filter-buttons .btn');

            buttons.forEach(btn => {
                btn.classList.remove('active');
                btn.classList.add('btn-outline-primary', 'btn-outline-success', 'btn-outline-danger', 'btn-outline-warning');
            });

            const activeButton = document.getElementById('filter-' + filter);
            if (activeButton) {
                activeButton.classList.add('active');
            }

            items.forEach(item => {
                let show = true;

                switch(filter) {
                    case 'present':
                        show = item.dataset.status === 'present';
                        break;
                    case 'missing':
                        show = item.dataset.status === 'missing';
                        break;
                    case 'maintenance':
                        show = item.dataset.maintenance === 'true';
                        break;
                    case 'all':
                    default:
                        show = true;
                        break;
                }

                item.style.display = show ? 'table-row' : 'none';
            });
        }
    </script>
@endsection
