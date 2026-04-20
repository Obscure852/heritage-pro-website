@extends('layouts.master')
@section('title') Storage Quota Management @endsection
@section('css')
    <style>
        .form-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .header h3 {
            margin: 0;
            font-weight: 600;
        }

        .header p {
            margin: 6px 0 0 0;
            opacity: 0.9;
        }

        .content-area {
            padding: 24px;
        }

        .search-bar {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            align-items: center;
        }

        .search-bar .form-control {
            max-width: 300px;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
        }

        .search-bar .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .btn {
            padding: 8px 16px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
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

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            color: white;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 13px;
        }

        .btn-outline-secondary {
            background: transparent;
            border: 1px solid #d1d5db;
            color: #6b7280;
        }

        .btn-outline-secondary:hover {
            background: #f3f4f6;
            color: #374151;
        }

        .btn-loading.loading .btn-text {
            display: none;
        }

        .btn-loading.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }

        .btn-loading:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
            padding: 10px 12px;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 10px 12px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 14px;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background-color: #f9fafb;
        }

        .sort-link {
            color: #374151;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .sort-link:hover {
            color: #3b82f6;
        }

        .sort-link .sort-icon {
            font-size: 10px;
            opacity: 0.4;
        }

        .sort-link.active .sort-icon {
            opacity: 1;
            color: #3b82f6;
        }

        .progress {
            height: 8px;
            background: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
            min-width: 100px;
        }

        .progress-bar {
            height: 100%;
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        .badge {
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-info { background: #dbeafe; color: #1e40af; }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 500;
            color: #1f2937;
        }

        .user-dept {
            font-size: 12px;
            color: #9ca3af;
        }

        .edit-form {
            display: none;
            background: #f8f9fa;
            padding: 12px;
            border-radius: 6px;
            margin-top: 6px;
        }

        .edit-form.show {
            display: block;
        }

        .edit-form .form-group {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        .edit-form .form-control {
            width: 120px;
            padding: 6px 10px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 13px;
        }

        .bulk-bar {
            display: none;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            padding: 10px 16px;
            margin-bottom: 16px;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
        }

        .bulk-bar.show {
            display: flex;
        }

        .bulk-actions {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            .content-area {
                padding: 16px;
            }

            .search-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .search-bar .form-control {
                max-width: 100%;
            }

            .table-responsive {
                overflow-x: auto;
            }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('documents.index') }}">Documents</a>
        @endslot
        @slot('title')
            Storage Quota Management
        @endslot
    @endcomponent

    @if (session('success'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('success') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <div class="form-container">
        <div class="header">
            <h3><i class="fas fa-hdd me-2"></i>Storage Quota Management</h3>
            <p>Manage user storage quotas and monitor usage ({{ $users->total() }} users)</p>
        </div>

        <div class="content-area">
            {{-- Search Bar --}}
            <form method="GET" action="{{ route('documents.quotas.index') }}" class="search-bar">
                <input type="text" name="search" class="form-control" placeholder="Search users by name..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-search"></i> Search
                </button>
                @if(request('search'))
                    <a href="{{ route('documents.quotas.index') }}" class="btn btn-outline-secondary btn-sm">
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
                                <a href="{{ route('documents.quotas.index', ['sort' => 'firstname', 'direction' => $nameDir, 'search' => request('search')]) }}"
                                   class="sort-link {{ $sortBy === 'firstname' ? 'active' : '' }}">
                                    User
                                    <i class="fas fa-{{ $sortBy === 'firstname' ? ($sortDir === 'asc' ? 'arrow-up' : 'arrow-down') : 'sort' }} sort-icon"></i>
                                </a>
                            </th>
                            <th>
                                @php
                                    $usedDir = $sortBy === 'used_bytes' && $sortDir === 'asc' ? 'desc' : 'asc';
                                @endphp
                                <a href="{{ route('documents.quotas.index', ['sort' => 'used_bytes', 'direction' => $usedDir, 'search' => request('search')]) }}"
                                   class="sort-link {{ $sortBy === 'used_bytes' ? 'active' : '' }}">
                                    Used
                                    <i class="fas fa-{{ $sortBy === 'used_bytes' ? ($sortDir === 'asc' ? 'arrow-up' : 'arrow-down') : 'sort' }} sort-icon"></i>
                                </a>
                            </th>
                            <th>
                                @php
                                    $quotaDir = $sortBy === 'quota_bytes' && $sortDir === 'asc' ? 'desc' : 'asc';
                                @endphp
                                <a href="{{ route('documents.quotas.index', ['sort' => 'quota_bytes', 'direction' => $quotaDir, 'search' => request('search')]) }}"
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
                        @forelse($users as $u)
                            @php
                                $quota = $u->documentQuota;
                                $usedBytes = $quota->used_bytes ?? 0;
                                $quotaBytes = $quota->quota_bytes ?? config('documents.quotas.default_bytes', 524288000);
                                $isUnlimited = $quota->is_unlimited ?? false;
                                $usagePercent = $quota ? $quota->usage_percent : 0;

                                // Color coding
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
                {{ $users->links() }}
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        var selectedUserIds = new Set();

        function toggleEditForm(userId) {
            var editRow = document.getElementById('edit-row-' + userId);
            if (editRow) {
                editRow.style.display = editRow.style.display === 'none' ? '' : 'none';
            }
        }

        function toggleUserSelect(checkbox) {
            var userId = parseInt(checkbox.dataset.userId);
            if (checkbox.checked) {
                selectedUserIds.add(userId);
            } else {
                selectedUserIds.delete(userId);
            }
            updateBulkBar();
        }

        function toggleSelectAllUsers(masterCheckbox) {
            document.querySelectorAll('.user-checkbox').forEach(function(cb) {
                cb.checked = masterCheckbox.checked;
                var userId = parseInt(cb.dataset.userId);
                if (masterCheckbox.checked) {
                    selectedUserIds.add(userId);
                } else {
                    selectedUserIds.delete(userId);
                }
            });
            updateBulkBar();
        }

        function updateBulkBar() {
            var bulkBar = document.getElementById('bulk-bar');
            var bulkCount = document.getElementById('bulk-count');
            var bulkUserIds = document.getElementById('bulk-user-ids');

            if (selectedUserIds.size > 0) {
                bulkBar.classList.add('show');
                bulkCount.textContent = selectedUserIds.size;

                // Update hidden inputs
                bulkUserIds.innerHTML = '';
                selectedUserIds.forEach(function(id) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'user_ids[]';
                    input.value = id;
                    bulkUserIds.appendChild(input);
                });
            } else {
                bulkBar.classList.remove('show');
            }
        }

        function confirmBulk(actionLabel) {
            return confirm(actionLabel + ' ' + selectedUserIds.size + ' user(s)?');
        }

        function recalculateUser(userId, btn) {
            var originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            btn.disabled = true;

            $.ajax({
                url: '/documents/quotas/' + userId + '/recalculate',
                method: 'POST',
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Usage recalculated: ' + response.used_formatted,
                        showConfirmButton: false,
                        timer: 2000
                    });
                    setTimeout(function() { location.reload(); }, 1500);
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Recalculation failed.';
                    Swal.fire('Error', msg, 'error');
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }
            });
        }

        // Loading state for edit forms
        document.querySelectorAll('.edit-form-inner').forEach(function(form) {
            form.addEventListener('submit', function() {
                var submitBtn = form.querySelector('button[type="submit"].btn-loading');
                if (submitBtn) {
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                }
            });
        });
    </script>
@endsection
