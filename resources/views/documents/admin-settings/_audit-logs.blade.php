<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">
        <i class="fas fa-history me-2 text-primary"></i>Audit Logs
        <small class="text-muted fw-normal">({{ $audits->total() }} entries)</small>
    </h5>
    <a href="{{ route('documents.audit-logs.export', request()->only(['date_from', 'date_to', 'user_id', 'category', 'document_search'])) }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-file-excel"></i> Export
    </a>
</div>

{{-- Filter Bar --}}
<form action="{{ route('documents.settings') }}" method="GET" class="filter-bar">
    <input type="hidden" name="tab" value="audit">
    <div class="row g-3">
        <div class="col-md-3">
            <label class="form-label">Date From</label>
            <input type="date" name="date_from" class="form-control" value="{{ $auditFilters['date_from'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Date To</label>
            <input type="date" name="date_to" class="form-control" value="{{ $auditFilters['date_to'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">User</label>
            <select name="user_id" class="form-select">
                <option value="">All Users</option>
                @foreach ($auditUsers as $user)
                    <option value="{{ $user->id }}" {{ ($auditFilters['user_id'] ?? '') == $user->id ? 'selected' : '' }}>
                        {{ $user->full_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Category</label>
            <select name="category" class="form-select">
                <option value="">All Categories</option>
                @foreach ($auditCategories as $key => $label)
                    <option value="{{ $key }}" {{ ($auditFilters['category'] ?? '') == $key ? 'selected' : '' }}>
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
                   value="{{ $auditFilters['document_search'] ?? '' }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">&nbsp;</label>
            <div class="filter-actions">
                <button type="submit" class="btn-filter">
                    <i class="fas fa-search"></i> Filter
                </button>
                <a href="{{ route('documents.settings', ['tab' => 'audit']) }}" class="btn-clear">
                    <i class="fas fa-times"></i> Clear
                </a>
            </div>
        </div>
    </div>

    {{-- Active filter chips --}}
    @php
        $hasActiveFilters = !empty($auditFilters['date_from']) || !empty($auditFilters['date_to'])
            || !empty($auditFilters['user_id']) || !empty($auditFilters['category'])
            || !empty($auditFilters['document_search']);
    @endphp
    @if ($hasActiveFilters)
        <div class="active-filters">
            @if (!empty($auditFilters['date_from']))
                @php
                    $chipParams = request()->query();
                    unset($chipParams['date_from']);
                    $chipParams['tab'] = 'audit';
                @endphp
                <span class="filter-chip">
                    From: {{ $auditFilters['date_from'] }}
                    <a href="{{ route('documents.settings', $chipParams) }}">&times;</a>
                </span>
            @endif
            @if (!empty($auditFilters['date_to']))
                @php
                    $chipParams = request()->query();
                    unset($chipParams['date_to']);
                    $chipParams['tab'] = 'audit';
                @endphp
                <span class="filter-chip">
                    To: {{ $auditFilters['date_to'] }}
                    <a href="{{ route('documents.settings', $chipParams) }}">&times;</a>
                </span>
            @endif
            @if (!empty($auditFilters['user_id']))
                @php
                    $chipParams = request()->query();
                    unset($chipParams['user_id']);
                    $chipParams['tab'] = 'audit';
                    $filterUser = $auditUsers->firstWhere('id', $auditFilters['user_id']);
                @endphp
                <span class="filter-chip">
                    User: {{ $filterUser ? $filterUser->full_name : 'Unknown' }}
                    <a href="{{ route('documents.settings', $chipParams) }}">&times;</a>
                </span>
            @endif
            @if (!empty($auditFilters['category']))
                @php
                    $chipParams = request()->query();
                    unset($chipParams['category']);
                    $chipParams['tab'] = 'audit';
                @endphp
                <span class="filter-chip">
                    Category: {{ $auditCategories[$auditFilters['category']] ?? $auditFilters['category'] }}
                    <a href="{{ route('documents.settings', $chipParams) }}">&times;</a>
                </span>
            @endif
            @if (!empty($auditFilters['document_search']))
                @php
                    $chipParams = request()->query();
                    unset($chipParams['document_search']);
                    $chipParams['tab'] = 'audit';
                @endphp
                <span class="filter-chip">
                    Document: "{{ $auditFilters['document_search'] }}"
                    <a href="{{ route('documents.settings', $chipParams) }}">&times;</a>
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
                        $userName = $audit->user ? $audit->user->full_name : 'Anonymous';
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
            <a href="{{ route('documents.settings', ['tab' => 'audit']) }}" class="btn-clear mt-2">
                <i class="fas fa-times"></i> Clear Filters
            </a>
        @endif
    </div>
@endif
