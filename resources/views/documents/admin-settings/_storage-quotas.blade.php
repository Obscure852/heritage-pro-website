@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="mdi mdi-check-all me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="fas fa-hdd me-2 text-primary"></i>Storage Quotas ({{ $quotaUsers->total() }} users)</h5>
</div>

{{-- Search Bar --}}
<form method="GET" action="{{ route('documents.settings') }}" class="search-bar">
    <input type="hidden" name="tab" value="quotas">
    <input type="text" name="search" class="form-control" placeholder="Search users by name..." value="{{ request('search') }}">
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-search me-1"></i> Search
    </button>
    @if(request('search'))
        <a href="{{ route('documents.settings', ['tab' => 'quotas']) }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-times"></i> Clear
        </a>
    @endif
</form>

{{-- Bulk Actions Bar --}}
<div class="bulk-bar" id="bulk-bar">
    <span><strong id="bulk-count">0</strong> user(s) selected</span>
    <div class="bulk-actions">
        <form method="POST" action="{{ route('documents.quotas.bulk') }}" id="bulk-form" style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
            @csrf
            <div id="bulk-user-ids"></div>
            <input type="number" name="quota_mb" class="form-control" style="width: 120px;" placeholder="MB" min="1" id="bulk-quota-mb">
            <button type="submit" class="btn btn-primary btn-sm btn-loading" name="action" value="set_quota" onclick="return confirmBulk('Set quota for')">
                <span class="btn-text"><i class="fas fa-save"></i> Set Quota</span>
                <span class="btn-spinner d-none">
                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                    Saving...
                </span>
            </button>
            <button type="submit" class="btn btn-secondary btn-sm" name="action" value="set_unlimited" onclick="return confirmBulk('Set unlimited for')">
                <i class="fas fa-infinity"></i> Unlimited
            </button>
            <button type="submit" class="btn btn-outline-secondary btn-sm" name="action" value="recalculate" onclick="return confirmBulk('Recalculate usage for')">
                <i class="fas fa-sync-alt"></i> Recalculate
            </button>
        </form>
    </div>
</div>

{{-- Users Table --}}
<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th style="width: 30px;">
                    <input type="checkbox" class="form-check-input" id="select-all-users" onchange="toggleSelectAllUsers(this)">
                </th>
                <th>
                    @php
                        $nameDir = $sortBy === 'firstname' && $sortDir === 'asc' ? 'desc' : 'asc';
                    @endphp
                    <a href="{{ route('documents.settings', ['tab' => 'quotas', 'sort' => 'firstname', 'direction' => $nameDir, 'search' => request('search')]) }}"
                       class="sort-link {{ $sortBy === 'firstname' ? 'active' : '' }}">
                        User
                        <i class="fas fa-{{ $sortBy === 'firstname' ? ($sortDir === 'asc' ? 'arrow-up' : 'arrow-down') : 'sort' }} sort-icon"></i>
                    </a>
                </th>
                <th>
                    @php
                        $usedDir = $sortBy === 'used_bytes' && $sortDir === 'asc' ? 'desc' : 'asc';
                    @endphp
                    <a href="{{ route('documents.settings', ['tab' => 'quotas', 'sort' => 'used_bytes', 'direction' => $usedDir, 'search' => request('search')]) }}"
                       class="sort-link {{ $sortBy === 'used_bytes' ? 'active' : '' }}">
                        Used
                        <i class="fas fa-{{ $sortBy === 'used_bytes' ? ($sortDir === 'asc' ? 'arrow-up' : 'arrow-down') : 'sort' }} sort-icon"></i>
                    </a>
                </th>
                <th>
                    @php
                        $quotaDir = $sortBy === 'quota_bytes' && $sortDir === 'asc' ? 'desc' : 'asc';
                    @endphp
                    <a href="{{ route('documents.settings', ['tab' => 'quotas', 'sort' => 'quota_bytes', 'direction' => $quotaDir, 'search' => request('search')]) }}"
                       class="sort-link {{ $sortBy === 'quota_bytes' ? 'active' : '' }}">
                        Quota
                        <i class="fas fa-{{ $sortBy === 'quota_bytes' ? ($sortDir === 'asc' ? 'arrow-up' : 'arrow-down') : 'sort' }} sort-icon"></i>
                    </a>
                </th>
                <th>Usage</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($quotaUsers as $u)
                @php
                    $quota = $u->documentQuota;
                    $usedBytes = $quota->used_bytes ?? 0;
                    $quotaBytes = $quota->quota_bytes ?? config('documents.quotas.default_bytes', 524288000);
                    $isUnlimited = $quota->is_unlimited ?? false;
                    $usagePercent = $quota ? $quota->usage_percent : 0;

                    if ($usagePercent > 100) {
                        $barColor = 'bg-danger';
                        $statusBadge = 'badge-danger';
                        $statusText = 'Over Quota';
                    } elseif ($usagePercent >= 80) {
                        $barColor = 'bg-warning';
                        $statusBadge = 'badge-warning';
                        $statusText = 'Warning';
                    } else {
                        $barColor = 'bg-success';
                        $statusBadge = 'badge-success';
                        $statusText = 'Normal';
                    }

                    if ($isUnlimited) {
                        $statusBadge = 'badge-info';
                        $statusText = 'Unlimited';
                    }

                    $quotaService = app(\App\Services\Documents\QuotaService::class);
                    $usedFormatted = $quotaService->formatBytes($usedBytes);
                    $quotaFormatted = $isUnlimited ? 'Unlimited' : $quotaService->formatBytes($quotaBytes);
                    $quotaMb = round($quotaBytes / (1024 * 1024));
                @endphp
                <tr>
                    <td>
                        <input type="checkbox" class="form-check-input user-checkbox" data-user-id="{{ $u->id }}" onchange="toggleUserSelect(this)">
                    </td>
                    <td>
                        <div class="user-info">
                            <span class="user-name">{{ $u->full_name }}</span>
                            @if($u->department)
                                <span class="user-dept">{{ $u->department }}</span>
                            @endif
                        </div>
                    </td>
                    <td>{{ $usedFormatted }}</td>
                    <td>{{ $quotaFormatted }}</td>
                    <td>
                        @if(!$isUnlimited)
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress" style="flex: 1;">
                                    <div class="progress-bar {{ $barColor }}" style="width: {{ min($usagePercent, 100) }}%"></div>
                                </div>
                                <span style="font-size: 12px; color: #6b7280; min-width: 35px;">{{ number_format($usagePercent, 0) }}%</span>
                            </div>
                        @else
                            <span style="color: #6b7280; font-size: 13px;">--</span>
                        @endif
                    </td>
                    <td><span class="badge {{ $statusBadge }}">{{ $statusText }}</span></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-secondary" onclick="toggleEditForm({{ $u->id }})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="recalculateUser({{ $u->id }}, this)" title="Recalculate usage">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </td>
                </tr>
                <tr id="edit-row-{{ $u->id }}" style="display: none;">
                    <td colspan="7" style="padding: 0 12px 12px;">
                        <div class="edit-form show">
                            <form method="POST" action="{{ route('documents.quotas.update', $u) }}" class="edit-form-inner">
                                @csrf
                                @method('PUT')
                                <div class="form-group">
                                    <label style="font-size: 13px; font-weight: 500; color: #374151;">Quota (MB):</label>
                                    <input type="number" name="quota_mb" class="form-control" value="{{ $quotaMb }}" min="1" {{ $isUnlimited ? 'disabled' : '' }}>
                                    <label style="font-size: 13px; display: flex; align-items: center; gap: 4px; white-space: nowrap;">
                                        <input type="checkbox" name="is_unlimited" value="1" class="form-check-input" {{ $isUnlimited ? 'checked' : '' }}
                                               onchange="this.closest('form').querySelector('input[name=quota_mb]').disabled = this.checked">
                                        Unlimited
                                    </label>
                                    <button type="submit" class="btn btn-primary btn-sm btn-loading">
                                        <span class="btn-text"><i class="fas fa-save"></i> Save</span>
                                        <span class="btn-spinner d-none">
                                            <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                            Saving...
                                        </span>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleEditForm({{ $u->id }})">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">
                        <i class="fas fa-users" style="font-size: 32px; opacity: 0.3; display: block; margin-bottom: 8px;"></i>
                        No users found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
<div class="mt-3 d-flex justify-content-center">
    {{ $quotaUsers->links() }}
</div>
