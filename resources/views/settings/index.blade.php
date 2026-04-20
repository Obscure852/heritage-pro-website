@extends('layouts.master')
@section('title')
    System Logs
@endsection

@section('css')
    <style>
        .settings-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .settings-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .settings-header h3 {
            margin: 0;
            font-weight: 600;
        }

        .settings-header p {
            margin: 6px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .settings-body {
            padding: 0;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px;
            border-left: 4px solid #3b82f6;
            border-radius: 0 3px 3px 0;
            margin: 20px 24px;
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
            border-bottom: 1px solid #e5e7eb;
            padding: 0 24px;
            background: #f9fafb;
        }

        .nav-tabs-custom .nav-item {
            margin-bottom: -1px;
        }

        .nav-tabs-custom .nav-link {
            border: none;
            border-bottom: 2px solid transparent;
            color: #6b7280;
            padding: 14px 20px;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .nav-tabs-custom .nav-link:hover {
            color: #4e73df;
            border-bottom-color: transparent;
            background: transparent;
        }

        .nav-tabs-custom .nav-link.active {
            color: #4e73df;
            border-bottom-color: #4e73df;
            background: transparent;
        }

        .nav-tabs-custom .nav-link i {
            color: inherit;
        }

        .tab-content {
            padding: 24px;
        }

        .form-control,
        .form-select {
            border: 1px solid #d1d5db;
            border-radius: 3px;
            padding: 8px 12px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .btn-secondary {
            background: #6b7280;
            border: none;
            color: white;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-secondary:hover {
            background: #4b5563;
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
            color: white;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
            padding: 12px 10px;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 12px 10px;
            vertical-align: middle;
            border-bottom: 1px solid #e5e7eb;
            font-size: 14px;
            color: #374151;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .table-hover tbody tr:hover {
            background-color: rgba(78, 115, 223, 0.05);
        }

        .badge {
            font-size: 11px;
            font-weight: 500;
            padding: 4px 8px;
            border-radius: 3px;
        }

        .filters-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .section-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .clear-logs-card {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 3px;
            padding: 16px;
            margin-top: 20px;
        }

        .clear-logs-card .card-title {
            color: #991b1b;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .token-alert {
            background: linear-gradient(135deg, #dbeafe 0%, #e0e7ff 100%);
            border: 1px solid #93c5fd;
            border-radius: 3px;
            padding: 16px;
            margin-bottom: 20px;
        }

        .token-alert .alert-title {
            color: #1e40af;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .token-value {
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            padding: 12px;
            font-family: monospace;
            word-break: break-all;
            cursor: pointer;
        }

        .modal-content {
            border: none;
            border-radius: 3px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-footer {
            padding: 16px 20px;
            border-top: 1px solid #e5e7eb;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 48px;
            color: #d1d5db;
            margin-bottom: 16px;
        }

        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_length {
            display: none;
        }

        .dataTables_wrapper .dataTables_info {
            color: #6b7280;
            font-size: 13px;
            padding-top: 12px;
        }

        .dataTables_wrapper .dataTables_paginate {
            padding-top: 12px;
        }

        .dataTables_wrapper .dataTables_paginate .pagination {
            margin: 0;
            justify-content: flex-end;
        }

        .dataTables_wrapper .dataTables_paginate .page-link {
            border-radius: 3px;
            margin: 0 2px;
            padding: 6px 12px;
            color: #4e73df;
            border: 1px solid #e5e7eb;
        }

        .dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            border-color: transparent;
        }

        @media (max-width: 768px) {
            .settings-header {
                padding: 20px;
            }

            .nav-tabs-custom {
                padding: 0 16px;
            }

            .nav-tabs-custom .nav-link {
                padding: 12px 14px;
                font-size: 13px;
            }

            .tab-content {
                padding: 16px;
            }

            .help-text {
                margin: 16px;
            }

            .filters-row {
                flex-direction: column;
                align-items: stretch;
            }

            .filters-row .form-control {
                width: 100%;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Logs
        @endslot
        @slot('title')
            System Logs
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

    @if (session('results'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-info alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-alert-circle-outline label-icon"></i><strong>{{ session('results') }}</strong>
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

    @if ($errors->any())
        <div class="row mb-3">
            <div class="col-md-12">
                @foreach ($errors->all() as $error)
                    <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        <i class="mdi mdi-block-helper label-icon"></i><strong>{{ $error }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="settings-container">
        <div class="settings-header">
            <h3><i class="fas fa-cog me-2"></i>System Administration</h3>
            <p>View system logs, backup history, and manage API access tokens</p>
        </div>

        <div class="settings-body">
            <ul class="nav nav-tabs nav-tabs-custom" role="tablist" id="systemAdminTabs">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#systemLogs" role="tab" data-tab-id="systemLogs">
                        <i class="fas fa-list-alt me-2"></i>
                        <span>System Logs</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#backupLogs" role="tab" data-tab-id="backupLogs">
                        <i class="fas fa-database me-2"></i>
                        <span>Backup Logs</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#apiTokens" role="tab" data-tab-id="apiTokens">
                        <i class="fas fa-key me-2"></i>
                        <span>API Tokens</span>
                    </a>
                </li>
            </ul>

            @php
                function getActionColor($action)
                {
                    switch ($action) {
                        case 'Login':
                            return 'bg-success';
                        case 'Marks Saved':
                            return 'bg-primary';
                        case 'Created':
                            return 'bg-info';
                        case 'Updated':
                            return 'bg-warning';
                        case 'Deleted':
                            return 'bg-danger';
                        default:
                            return 'bg-secondary';
                    }
                }
            @endphp

            <div class="tab-content">
                <!-- System Logs Tab -->
                <div class="tab-pane active" id="systemLogs" role="tabpanel">
                    <div class="help-text" style="margin: 0 0 20px 0;">
                        <div class="help-title">About System Logs</div>
                        <div class="help-content">
                            Track all user activities including logins, data changes, and system events. Use filters to find specific entries.
                        </div>
                    </div>

                    <form action="{{ route('logs.index') }}" method="GET">
                        <div class="filters-row">
                            <input type="text" name="search" value="{{ request('search') }}"
                                class="form-control" placeholder="Search logs..." style="width: 200px;">
                            <input type="date" name="date_from" value="{{ request('date_from') }}"
                                class="form-control" style="width: 160px;">
                            <input type="date" name="date_to" value="{{ request('date_to') }}"
                                class="form-control" style="width: 160px;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                            <a href="{{ route('logs.index') }}" class="btn btn-secondary">
                                <i class="fas fa-undo me-1"></i> Reset
                            </a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table id="logsTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>IP Address</th>
                                    <th>Action</th>
                                    <th>Changes</th>
                                    <th>Date/Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($logs as $index => $log)
                                    <tr>
                                        <td>{{ $logs->firstItem() + $index }}</td>
                                        <td>
                                            @if ($log->user_id && $log->user)
                                                {{ $log->user->fullName ?? 'User #' . $log->user_id }}
                                            @elseif (isset($log->changes['user_type']) && $log->changes['user_type'] !== 'user')
                                                @if (isset($log->changes['non_user_email']) && $log->changes['non_user_email'])
                                                    <span class="badge bg-info">{{ ucfirst($log->changes['user_type']) }}</span>
                                                    {{ $log->changes['non_user_email'] }}
                                                @elseif (isset($log->changes['non_user_id']) && $log->changes['non_user_id'])
                                                    <span class="badge bg-info">{{ ucfirst($log->changes['user_type']) }}</span>
                                                    #{{ $log->changes['non_user_id'] }}
                                                @else
                                                    <span class="badge bg-info">{{ ucfirst($log->changes['user_type']) }}</span>
                                                @endif
                                            @else
                                                System
                                            @endif
                                        </td>
                                        <td>
                                            @if ($log->ip_address)
                                                <a href="https://ipinfo.io/{{ $log->ip_address }}" target="_blank"
                                                    class="text-primary">{{ $log->ip_address }}</a>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $action = $log->changes['action'] ?? '';
                                                $badgeColor = getActionColor($action);
                                            @endphp
                                            <span class="badge {{ $badgeColor }}">{{ $action }}</span>
                                        </td>
                                        <td>
                                            @if (!empty($log->changes['data_summary']))
                                                @php
                                                    $maxBadges = 6;
                                                    $totalChanges = count($log->changes['data_summary']);
                                                    $visibleChanges = array_slice($log->changes['data_summary'], 0, $maxBadges);
                                                    $hiddenChanges = $totalChanges > $maxBadges
                                                        ? array_slice($log->changes['data_summary'], $maxBadges)
                                                        : [];
                                                    $isLabelSummary = ($log->changes['data_summary_mode'] ?? 'keys') === 'labels';
                                                @endphp

                                                @foreach ($visibleChanges as $field)
                                                    <span class="badge bg-info">{{ $isLabelSummary ? $field : ucfirst($field) }}</span>
                                                @endforeach

                                                @if (count($hiddenChanges) > 0)
                                                    <a href="#" class="badge bg-secondary ms-1"
                                                        data-bs-toggle="popover" data-bs-placement="top"
                                                        data-bs-html="true" data-bs-title="All Changes"
                                                        data-bs-content="@foreach ($log->changes['data_summary'] as $field)<span class='badge bg-info me-1 mb-1'>{{ $isLabelSummary ? $field : ucfirst($field) }}</span> @endforeach">
                                                        +{{ count($hiddenChanges) }} more
                                                    </a>
                                                @endif
                                            @endif
                                        </td>
                                        <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            <div class="empty-state">
                                                <i class="fas fa-clipboard-list d-block"></i>
                                                <p class="mb-0">No logs found</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($logs->hasPages())
                        <div class="d-flex justify-content-end mt-3">
                            {{ $logs->links() }}
                        </div>
                    @endif

                    @can('view-system-admin')
                        <div class="clear-logs-card">
                            <div class="card-title"><i class="fas fa-exclamation-triangle me-2"></i>Clear Old Logs</div>
                            <p class="text-muted mb-3" style="font-size: 13px;">Remove logs older than the selected date. This action cannot be undone.</p>
                            <form action="{{ route('logs.clear') }}" method="POST" class="d-flex align-items-center gap-2"
                                onsubmit="return confirm('Are you sure you want to clear old logs? This action cannot be undone.');">
                                @csrf
                                <input type="date" name="date" value="{{ now()->subMonths(3)->format('Y-m-d') }}"
                                    class="form-control" style="width: 180px;">
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash-alt me-1"></i> Clear Old Logs
                                </button>
                            </form>
                        </div>
                    @endcan
                </div>

                <!-- Backup Logs Tab -->
                <div class="tab-pane" id="backupLogs" role="tabpanel">
                    <div class="help-text" style="margin: 0 0 20px 0;">
                        <div class="help-title">About Backup Logs</div>
                        <div class="help-content">
                            View database backup history. Click on successful backup file names to download them.
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="backupsTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Database Name</th>
                                    <th>File Path</th>
                                    <th>File Size</th>
                                    <th>Backup Time</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($backupLogs as $index => $log)
                                    <tr>
                                        <td>{{ $backupLogs->firstItem() + $index }}</td>
                                        <td>{{ $log->database_name }}</td>
                                        <td>
                                            @if ($log->status == 'success')
                                                <a href="{{ route('setup.download-backup', basename($log->file_path)) }}"
                                                    class="text-primary">
                                                    <i class="fas fa-download me-1"></i>{{ basename($log->file_path) }}
                                                </a>
                                            @else
                                                {{ basename($log->file_path) }}
                                            @endif
                                        </td>
                                        <td>{{ number_format($log->file_size / 1048576, 2) }} MB</td>
                                        <td>{{ $log->backup_time ?? '' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $log->status == 'success' ? 'success' : 'danger' }}">
                                                {{ ucfirst($log->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            <div class="empty-state">
                                                <i class="fas fa-database d-block"></i>
                                                <p class="mb-0">No backup logs found</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($backupLogs->hasPages())
                        <div class="d-flex justify-content-end mt-3">
                            {{ $backupLogs->links() }}
                        </div>
                    @endif
                </div>

                <!-- API Tokens Tab -->
                <div class="tab-pane" id="apiTokens" role="tabpanel">
                    <div class="help-text" style="margin: 0 0 20px 0;">
                        <div class="help-title">About API Tokens</div>
                        <div class="help-content">
                            API tokens allow external applications to access the system. Generate tokens for integrations and revoke them when no longer needed.
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mb-4">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTokenModal">
                            <i class="fas fa-plus me-1"></i> Generate New Token
                        </button>
                    </div>

                    @if (session('token'))
                        <div class="token-alert">
                            <div class="alert-title"><i class="fas fa-check-circle me-2"></i>Token Generated Successfully!</div>
                            <p class="mb-2" style="color: #1e40af; font-size: 13px;">
                                Please copy your new API token now. You won't be able to see it again!
                            </p>
                            <div class="token-value" title="Click to select">
                                {{ session('token') }}
                            </div>
                        </div>
                    @endif

                    @if ($tokens->isEmpty() && !session('token'))
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>No API tokens have been generated yet.
                        </div>
                    @endif

                    <h6 class="section-title"><i class="fas fa-ban me-2"></i>Revoke Tokens</h6>

                    <div class="table-responsive">
                        <table id="tokensTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Token Name</th>
                                    <th>Created At</th>
                                    <th>Last Used</th>
                                    <th style="width: 120px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($tokens as $index => $token)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $token->name }}</td>
                                        <td>{{ $token->created_at->format('Y-m-d H:i:s') }}</td>
                                        <td>{{ $token->last_used_at ? $token->last_used_at->format('Y-m-d H:i:s') : 'Never' }}</td>
                                        <td>
                                            <form action="{{ route('logs.api-tokens.destroy', $token->id) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Are you sure you want to revoke this token?')">
                                                    <i class="fas fa-trash-alt me-1"></i> Revoke
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">
                                            <div class="empty-state">
                                                <i class="fas fa-key d-block"></i>
                                                <p class="mb-0">No tokens to revoke</p>
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

    <!-- Create Token Modal -->
    <div class="modal fade" id="createTokenModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-key me-2"></i>Generate API Token</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('logs.api-tokens.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="token_name" class="form-label">Token Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="token_name" name="token_name"
                                required placeholder="Enter a descriptive name for this token">
                            <small class="text-muted">
                                Give your token a meaningful name to remember its purpose (e.g., "Mobile App Access", "Integration Testing")
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Generate Token
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
            // Token value click to select
            const tokenValue = document.querySelector('.token-value');
            if (tokenValue) {
                tokenValue.addEventListener('click', function() {
                    const range = document.createRange();
                    range.selectNodeContents(this);
                    const selection = window.getSelection();
                    selection.removeAllRanges();
                    selection.addRange(range);
                });
            }

            // Tab persistence
            const tabList = document.getElementById('systemAdminTabs');
            const tabs = tabList.querySelectorAll('.nav-link');
            const tabPanes = document.querySelectorAll('.tab-content .tab-pane');
            const storageKey = 'systemAdminActiveTab';

            function setActiveTab(tabHref) {
                tabs.forEach(tab => tab.classList.remove('active'));
                tabPanes.forEach(pane => {
                    pane.classList.remove('active');
                    pane.classList.remove('show');
                });

                const activeTab = Array.from(tabs).find(tab => tab.getAttribute('href') === tabHref);
                if (activeTab) {
                    activeTab.classList.add('active');
                    const targetPane = document.querySelector(tabHref);
                    if (targetPane) {
                        targetPane.classList.add('active', 'show');
                    }
                } else {
                    tabs[0].classList.add('active');
                    const firstPaneId = tabs[0].getAttribute('href');
                    const firstPane = document.querySelector(firstPaneId);
                    if (firstPane) {
                        firstPane.classList.add('active', 'show');
                    }
                }
            }

            const storedTabHref = localStorage.getItem(storageKey);
            if (storedTabHref && document.querySelector(storedTabHref)) {
                setActiveTab(storedTabHref);
            } else {
                const firstTabHref = tabs[0].getAttribute('href');
                setActiveTab(firstTabHref);
                localStorage.setItem(storageKey, firstTabHref);
            }

            tabs.forEach(tab => {
                tab.addEventListener('click', function(e) {
                    const tabHref = this.getAttribute('href');
                    localStorage.setItem(storageKey, tabHref);
                    setActiveTab(tabHref);
                });
            });

            // Initialize popovers
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });
        });
    </script>
@endsection
