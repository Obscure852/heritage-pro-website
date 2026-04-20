@extends('layouts.master')
@section('title')
    Inventory Management
@endsection
@section('css')
    <style>
        .library-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .library-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .library-body {
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

        /* Table Styles */
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

        .table th {
            font-size: 13px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border-top: none;
        }

        .table td {
            font-size: 14px;
            vertical-align: middle;
        }

        /* Status Badges */
        .badge-status {
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 500;
        }

        .badge-in-progress {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-completed {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-cancelled {
            background: #f3f4f6;
            color: #6b7280;
        }

        /* Active Session Alert */
        .active-session-alert {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 3px;
            padding: 14px 18px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .active-session-alert .alert-text {
            font-size: 14px;
            color: #1e40af;
        }

        .active-session-alert .alert-text i {
            margin-right: 6px;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 48px 24px;
        }

        .empty-state i {
            font-size: 3rem;
            color: #36b9cc;
            margin-bottom: 16px;
        }

        .empty-state h5 {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: #6b7280;
            margin: 0;
        }

        /* Buttons */
        .btn {
            padding: 10px 16px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }

        .btn-outline-primary {
            background: transparent;
            border: 1px solid #3b82f6;
            color: #3b82f6;
        }

        .btn-outline-primary:hover {
            background: #eff6ff;
            color: #2563eb;
        }

        @media (max-width: 768px) {
            .library-header {
                padding: 20px;
            }

            .library-body {
                padding: 16px;
            }

            .table-responsive {
                font-size: 13px;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="javascript:void(0);">Library</a>
        @endslot
        @slot('title')
            Inventory Management
        @endslot
    @endcomponent

    <div class="library-container">
        <div class="library-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h4 class="mb-1 text-white"><i class="bx bx-clipboard me-2"></i>Inventory Management</h4>
                    <p class="mb-0 opacity-75">Conduct stocktakes to verify physical book inventory</p>
                </div>
                <div class="stat-item text-center text-white">
                    <h4 class="text-white mb-0">{{ $activeSession ? 1 : 0 }}</h4>
                    <small class="text-white opacity-75">Active Session</small>
                </div>
            </div>
        </div>
        <div class="library-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($activeSession)
                <div class="active-session-alert">
                    <div class="alert-text">
                        <i class="bx bx-info-circle"></i>
                        <strong>Active session:</strong> {{ $activeSession->scope_display }} &mdash;
                        {{ $activeSession->scanned_count }}/{{ $activeSession->expected_count }} scanned
                    </div>
                    <a href="{{ route('library.inventory.show', $activeSession) }}" class="btn btn-primary btn-sm">
                        <i class="bx bx-right-arrow-alt"></i> Continue Scanning
                    </a>
                </div>
            @else
                <div class="mb-3">
                    <a href="{{ route('library.inventory.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus"></i> Start New Inventory
                    </a>
                </div>
            @endif

            @if ($sessions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Scope</th>
                                <th>Status</th>
                                <th>Expected</th>
                                <th>Scanned</th>
                                <th>Discrepancies</th>
                                <th>Started By</th>
                                <th>Started At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sessions as $session)
                                <tr>
                                    <td>{{ $session->scope_display }}</td>
                                    <td>
                                        @if ($session->status === 'in_progress')
                                            <span class="badge-status badge-in-progress">In Progress</span>
                                        @elseif ($session->status === 'completed')
                                            <span class="badge-status badge-completed">Completed</span>
                                        @else
                                            <span class="badge-status badge-cancelled">Cancelled</span>
                                        @endif
                                    </td>
                                    <td>{{ $session->expected_count }}</td>
                                    <td>{{ $session->scanned_count }}</td>
                                    <td>{{ $session->discrepancy_count }}</td>
                                    <td>{{ $session->startedByUser->name ?? '-' }}</td>
                                    <td>{{ $session->started_at->format('d M Y H:i') }}</td>
                                    <td>
                                        @if ($session->status === 'in_progress')
                                            <a href="{{ route('library.inventory.show', $session) }}"
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="bx bx-right-arrow-alt"></i> Resume
                                            </a>
                                        @elseif ($session->status === 'completed')
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('library.inventory.report', $session) }}"
                                                   class="btn btn-outline-primary btn-sm">
                                                    <i class="bx bx-file"></i> Report
                                                </a>
                                                <a href="{{ route('library.inventory.export', $session) }}"
                                                   class="btn btn-outline-primary btn-sm">
                                                    <i class="bx bx-download"></i> Export
                                                </a>
                                            </div>
                                        @else
                                            <a href="{{ route('library.inventory.report', $session) }}"
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="bx bx-file"></i> Report
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 d-flex justify-content-center">
                    {{ $sessions->links() }}
                </div>
            @else
                <div class="empty-state">
                    <i class="bx bx-clipboard"></i>
                    <h5>No Inventory Sessions</h5>
                    <p>Start your first inventory session to verify physical book stock.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
