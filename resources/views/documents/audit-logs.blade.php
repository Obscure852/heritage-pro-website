@extends('layouts.master')
@section('title')
    Audit Logs - Documents
@endsection
@section('css')
    <style>
        .audit-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .audit-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .audit-header h4 {
            font-weight: 700;
            margin-bottom: 4px;
        }

        .audit-header p {
            opacity: 0.9;
            margin-bottom: 0;
            font-size: 14px;
        }

        .btn-header-light {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 8px 16px;
            border-radius: 3px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .btn-header-light:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            text-decoration: none;
        }

        .audit-body {
            padding: 24px;
        }

        /* Filter bar */
        .filter-bar {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 3px;
            margin-bottom: 20px;
            border: 1px solid #e5e7eb;
        }

        .filter-bar .form-label {
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .filter-bar .form-control,
        .filter-bar .form-select {
            font-size: 13px;
        }

        .filter-bar .form-control:focus,
        .filter-bar .form-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .filter-actions {
            display: flex;
            gap: 8px;
            align-items: flex-end;
        }

        .btn-filter {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            padding: 7px 16px;
            border-radius: 3px;
            font-size: 13px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-filter:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
        }

        .btn-clear {
            background: white;
            color: #6b7280;
            border: 1px solid #d1d5db;
            padding: 7px 16px;
            border-radius: 3px;
            font-size: 13px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-clear:hover {
            background: #f3f4f6;
            color: #374151;
            text-decoration: none;
        }

        /* Active filter chips */
        .active-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 12px;
        }

        .filter-chip {
            background: #e0e7ff;
            color: #3730a3;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .filter-chip a {
            color: #3730a3;
            font-weight: 700;
            text-decoration: none;
            margin-left: 2px;
        }

        .filter-chip a:hover {
            color: #1e1b4b;
        }

        /* User avatar */
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 600;
            color: white;
            flex-shrink: 0;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .user-info .user-name {
            font-size: 13px;
            font-weight: 500;
            color: #374151;
        }

        /* Table */
        .audit-table th {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            border-bottom: 2px solid #e5e7eb;
            padding: 12px;
        }

        .audit-table td {
            padding: 12px;
            vertical-align: middle;
            font-size: 13px;
            border-bottom: 1px solid #f3f4f6;
        }

        .audit-table tbody tr:hover {
            background: #f9fafb;
        }

        /* Detail toggle */
        .btn-detail-toggle {
            background: none;
            border: none;
            color: #9ca3af;
            padding: 4px 8px;
            cursor: pointer;
            transition: color 0.2s;
        }

        .btn-detail-toggle:hover {
            color: #3b82f6;
        }

        .btn-detail-toggle.expanded {
            color: #3b82f6;
        }

        /* Expandable detail row */
        .audit-detail-row {
            display: none;
        }

        .audit-detail-content {
            background: #f8f9fa;
            padding: 16px 20px;
            border-left: 3px solid #3b82f6;
            margin: 0;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 12px;
        }

        .detail-item {
            font-size: 12px;
        }

        .detail-item .detail-label {
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 2px;
        }

        .detail-item .detail-value {
            color: #374151;
            word-break: break-all;
        }

        .metadata-list {
            margin-top: 10px;
        }

        .metadata-list dt {
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
        }

        .metadata-list dd {
            font-size: 12px;
            color: #374151;
            margin-bottom: 6px;
        }

        /* Document link */
        .doc-link {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
        }

        .doc-link:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }

        /* Date column */
        .audit-date {
            font-size: 13px;
            color: #374151;
        }

        .audit-date-relative {
            font-size: 11px;
            color: #9ca3af;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state i {
            font-size: 48px;
            color: #d1d5db;
            margin-bottom: 16px;
        }

        .empty-state h5 {
            color: #6b7280;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: #9ca3af;
            font-size: 14px;
        }

        /* Pagination */
        .pagination-wrapper {
            padding: 16px 0 0;
        }
    </style>
@endsection
@section('content')
    <div class="container-fluid py-4">
        <div class="audit-container">
            {{-- Header --}}
            <div class="audit-header">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h4><i class="fas fa-history me-2"></i>Document Audit Logs</h4>
                        <p>{{ number_format($audits->total()) }} total audit entries</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('documents.audit-logs.export', request()->query()) }}" class="btn-header-light">
                            <i class="fas fa-file-excel"></i> Export
                        </a>
                        <a href="{{ route('documents.index') }}" class="btn-header-light">
                            <i class="fas fa-arrow-left"></i> Documents
                        </a>
                    </div>
                </div>
            </div>

            {{-- Body --}}
            <div class="audit-body">
                {{-- Filter Bar --}}
                <form action="{{ route('documents.audit-logs.index') }}" method="GET" class="filter-bar">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Date From</label>
                            <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date To</label>
                            <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">User</label>
                            <select name="user_id" class="form-select">
                                <option value="">All Users</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" {{ ($filters['user_id'] ?? '') == $user->id ? 'selected' : '' }}>
                                        {{ $user->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                <option value="">All Categories</option>
                                @foreach ($categories as $key => $label)
                                    <option value="{{ $key }}" {{ ($filters['category'] ?? '') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mt-0">
                        <div class="col-md-6">
                            <label class="form-label">Document</label>
                            <input type="text" name="document_search" class="form-control"
                                   placeholder="Search by document title..."
                                   value="{{ $filters['document_search'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">&nbsp;</label>
                            <div class="filter-actions">
                                <button type="submit" class="btn-filter">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('documents.audit-logs.index') }}" class="btn-clear">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Active filter chips --}}
                    @php
                        $hasActiveFilters = !empty($filters['date_from']) || !empty($filters['date_to'])
                            || !empty($filters['user_id']) || !empty($filters['category'])
                            || !empty($filters['document_search']);
                    @endphp
                    @if ($hasActiveFilters)
                        <div class="active-filters">
                            @if (!empty($filters['date_from']))
                                @php
                                    $chipParams = request()->query();
                                    unset($chipParams['date_from']);
                                @endphp
                                <span class="filter-chip">
                                    From: {{ $filters['date_from'] }}
                                    <a href="{{ route('documents.audit-logs.index', $chipParams) }}">&times;</a>
                                </span>
                            @endif
                            @if (!empty($filters['date_to']))
                                @php
                                    $chipParams = request()->query();
                                    unset($chipParams['date_to']);
                                @endphp
                                <span class="filter-chip">
                                    To: {{ $filters['date_to'] }}
                                    <a href="{{ route('documents.audit-logs.index', $chipParams) }}">&times;</a>
                                </span>
                            @endif
                            @if (!empty($filters['user_id']))
                                @php
                                    $chipParams = request()->query();
                                    unset($chipParams['user_id']);
                                    $filterUser = $users->firstWhere('id', $filters['user_id']);
                                @endphp
                                <span class="filter-chip">
                                    User: {{ $filterUser?->name ?? 'Unknown' }}
                                    <a href="{{ route('documents.audit-logs.index', $chipParams) }}">&times;</a>
                                </span>
                            @endif
                            @if (!empty($filters['category']))
                                @php
                                    $chipParams = request()->query();
                                    unset($chipParams['category']);
                                @endphp
                                <span class="filter-chip">
                                    Category: {{ $categories[$filters['category']] ?? $filters['category'] }}
                                    <a href="{{ route('documents.audit-logs.index', $chipParams) }}">&times;</a>
                                </span>
                            @endif
                            @if (!empty($filters['document_search']))
                                @php
                                    $chipParams = request()->query();
                                    unset($chipParams['document_search']);
                                @endphp
                                <span class="filter-chip">
                                    Document: "{{ $filters['document_search'] }}"
                                    <a href="{{ route('documents.audit-logs.index', $chipParams) }}">&times;</a>
                                </span>
                            @endif
                        </div>
                    @endif
                </form>

                {{-- Audit Table --}}
                @if ($audits->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover audit-table mb-0">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Document</th>
                                    <th>Date</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($audits as $audit)
                                    @php
                                        $avatarColors = ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4', '#f97316'];
                                        $userName = $audit->user?->name ?? 'Anonymous';
                                        $initial = strtoupper(substr($userName, 0, 1));
                                        $colorIndex = $audit->user_id ? ($audit->user_id % count($avatarColors)) : 0;
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="user-info">
                                                <div class="user-avatar" style="background-color: {{ $avatarColors[$colorIndex] }};">
                                                    {{ $initial }}
                                                </div>
                                                <span class="user-name">{{ $userName }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $auditService->getActionColor($audit->action) }}">
                                                {{ $auditService->getActionLabel($audit->action) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($audit->document)
                                                <a href="{{ route('documents.show', $audit->document) }}" class="doc-link">
                                                    {{ $audit->document->title }}
                                                </a>
                                            @else
                                                <span class="text-muted">Deleted Document</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="audit-date" title="{{ $audit->created_at->diffForHumans() }}">
                                                {{ $audit->created_at->format('d M Y H:i') }}
                                            </div>
                                            <div class="audit-date-relative">
                                                {{ $audit->created_at->diffForHumans() }}
                                            </div>
                                        </td>
                                        <td>
                                            <button class="btn-detail-toggle" data-target="detail-{{ $audit->id }}">
                                                <i class="fas fa-chevron-down"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="audit-detail-row" id="detail-{{ $audit->id }}">
                                        <td colspan="5" style="padding: 0; border: none;">
                                            <div class="audit-detail-content">
                                                <div class="detail-grid">
                                                    <div class="detail-item">
                                                        <div class="detail-label">IP Address</div>
                                                        <div class="detail-value">{{ $audit->ip_address ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="detail-item">
                                                        <div class="detail-label">User Agent</div>
                                                        <div class="detail-value">{{ Str::limit($audit->user_agent ?? 'N/A', 80) }}</div>
                                                    </div>
                                                </div>

                                                @if (!empty($audit->metadata) && is_array($audit->metadata))
                                                    <div class="metadata-list">
                                                        <div class="detail-label mb-2">Metadata</div>
                                                        <dl class="row mb-0">
                                                            @foreach ($audit->metadata as $key => $value)
                                                                <dt class="col-sm-3">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                                                                <dd class="col-sm-9">
                                                                    @if (is_array($value))
                                                                        <code>{{ json_encode($value) }}</code>
                                                                    @else
                                                                        {{ $value }}
                                                                    @endif
                                                                </dd>
                                                            @endforeach
                                                        </dl>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="pagination-wrapper d-flex justify-content-center">
                        {{ $audits->links() }}
                    </div>
                @else
                    {{-- Empty State --}}
                    <div class="empty-state">
                        <i class="fas fa-clipboard-list d-block"></i>
                        <h5>No audit logs found</h5>
                        <p>
                            @if ($hasActiveFilters)
                                No audit entries match your current filters. Try adjusting or clearing your filters.
                            @else
                                There are no audit log entries recorded yet. Activity will appear here as documents are used.
                            @endif
                        </p>
                        @if ($hasActiveFilters)
                            <a href="{{ route('documents.audit-logs.index') }}" class="btn-clear mt-2">
                                <i class="fas fa-times"></i> Clear Filters
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        $(function() {
            // Toggle expandable detail rows
            $('.btn-detail-toggle').on('click', function() {
                var targetId = $(this).data('target');
                var $detailRow = $('#' + targetId);
                var $icon = $(this).find('i');
                var $btn = $(this);

                $detailRow.toggle();
                $btn.toggleClass('expanded');

                if ($btn.hasClass('expanded')) {
                    $icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
                } else {
                    $icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                }
            });
        });
    </script>
@endsection
