@extends('layouts.master')
@section('title')
    Sync History
@endsection
@section('css')
    <style>
        .sync-history-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .sync-history-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .sync-history-body {
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

        .status-success { background: #d1fae5; color: #065f46; }
        .status-failed { background: #fee2e2; color: #991b1b; }
        .status-partial { background: #fef3c7; color: #92400e; }
        .status-running { background: #dbeafe; color: #1e40af; }

        .controls .form-control,
        .controls .form-select {
            font-size: 0.9rem;
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

        .error-message {
            color: #991b1b;
            font-size: 12px;
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: pointer;
        }

        .error-message:hover {
            white-space: normal;
            overflow: visible;
        }

        @media (max-width: 768px) {
            .stat-item h4 {
                font-size: 1.25rem;
            }

            .stat-item small {
                font-size: 0.75rem;
            }

            .sync-history-header {
                padding: 20px;
            }

            .sync-history-body {
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

    <div class="sync-history-container">
        <div class="sync-history-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;">Sync History</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">View biometric device synchronization logs</p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['total_syncs'] }}</h4>
                                <small class="opacity-75">Total Syncs</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['successful'] }}</h4>
                                <small class="opacity-75">Successful</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['failed'] }}</h4>
                                <small class="opacity-75">Failed</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ number_format($stats['total_records']) }}</h4>
                                <small class="opacity-75">Records</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="sync-history-body">
            <div class="help-text">
                <div class="help-title">Synchronization Logs</div>
                <div class="help-content">
                    View sync history for the last 30 days. Use filters to find specific sync operations or troubleshoot failures.
                </div>
            </div>

            <!-- Filters -->
            <div class="row align-items-center mb-3">
                <div class="col-lg-8 col-md-12">
                    <form method="GET" action="{{ route('staff-attendance.sync-history.index') }}" class="controls">
                        <div class="row g-2 align-items-center">
                            <div class="col-lg-4 col-md-4 col-sm-6">
                                <select class="form-select" name="device">
                                    <option value="">All Devices</option>
                                    @foreach ($devices as $device)
                                        <option value="{{ $device->id }}" {{ request('device') == $device->id ? 'selected' : '' }}>
                                            {{ $device->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-3 col-sm-6">
                                <select class="form-select" name="status">
                                    <option value="">All Status</option>
                                    @foreach ($statuses as $value => $label)
                                        <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-6">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-6">
                                <a href="{{ route('staff-attendance.sync-history.index') }}" class="btn btn-light w-100">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                    <a href="{{ route('staff-attendance.devices.index') }}" class="btn btn-primary">
                        <i class="fas fa-cog me-1"></i> Manage Devices
                    </a>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Device</th>
                            <th>Sync Type</th>
                            <th>Started</th>
                            <th>Duration</th>
                            <th>Records</th>
                            <th>Status</th>
                            <th>Error</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($syncLogs as $log)
                            <tr>
                                <td>
                                    <strong>{{ $log->device->name ?? 'Unknown Device' }}</strong>
                                    <div class="text-muted" style="font-size: 12px;">{{ $log->device->ip_address ?? '' }}</div>
                                </td>
                                <td>{{ ucwords(str_replace('_', ' ', $log->sync_type)) }}</td>
                                <td>
                                    {{ $log->started_at->format('M d, Y H:i') }}
                                    <div class="text-muted" style="font-size: 12px;">{{ $log->started_at->diffForHumans() }}</div>
                                </td>
                                <td>
                                    @if ($log->completed_at)
                                        {{ $log->started_at->diffInSeconds($log->completed_at) }}s
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-success">{{ $log->records_processed }}</span>
                                    @if ($log->records_failed > 0)
                                        / <span class="text-danger">{{ $log->records_failed }} failed</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="status-badge status-{{ $log->status }}">{{ ucfirst($log->status) }}</span>
                                </td>
                                <td>
                                    @if ($log->error_message)
                                        <span class="error-message" title="{{ $log->error_message }}">
                                            {{ $log->error_message }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="text-center text-muted" style="padding: 40px 0;">
                                        <i class="fas fa-sync-alt" style="font-size: 48px; opacity: 0.3;"></i>
                                        <p class="mt-3 mb-0" style="font-size: 15px;">No sync logs found</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($syncLogs->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        Showing {{ $syncLogs->firstItem() }} to {{ $syncLogs->lastItem() }} of {{ $syncLogs->total() }} logs
                    </div>
                    {{ $syncLogs->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
