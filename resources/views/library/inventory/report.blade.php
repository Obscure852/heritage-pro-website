@extends('layouts.master')
@section('title')
    Inventory Report
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

        /* Summary Cards */
        .summary-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .summary-card {
            background: #f9fafb;
            border-radius: 8px;
            padding: 16px 20px;
            text-align: center;
            border: 1px solid #e5e7eb;
        }

        .summary-card .value {
            font-size: 1.75rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 4px;
        }

        .summary-card .label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
        }

        .summary-card.found .value { color: #059669; }
        .summary-card.not-found .value { color: #dc2626; }
        .summary-card.rate .value { color: #2563eb; }
        .summary-card.expected .value { color: #374151; }

        /* Session Details Bar */
        .session-details {
            display: flex;
            flex-wrap: wrap;
            gap: 24px;
            padding: 14px 20px;
            background: #f9fafb;
            border-radius: 3px;
            margin-bottom: 24px;
            font-size: 14px;
            color: #374151;
        }

        .session-details .detail-item strong {
            color: #1f2937;
        }

        /* Section Title */
        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin: 24px 0 16px 0;
            color: #1f2937;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
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

        .accession-code {
            font-family: 'Courier New', monospace;
            font-weight: 600;
            color: #374151;
        }

        /* Status Badges */
        .badge-status {
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 500;
        }

        .badge-available {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-in-repair {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-checked-out {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-lost {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-missing {
            background: #f3f4f6;
            color: #6b7280;
        }

        .badge-on-hold {
            background: #ede9fe;
            color: #5b21b6;
        }

        /* All Verified State */
        .all-verified {
            text-align: center;
            padding: 48px 24px;
        }

        .all-verified i {
            font-size: 3rem;
            color: #059669;
            margin-bottom: 16px;
        }

        .all-verified h5 {
            font-weight: 600;
            color: #065f46;
            margin-bottom: 8px;
        }

        .all-verified p {
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

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            color: white;
        }

        .btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .btn-warning:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
            color: white;
        }

        .btn-outline-secondary {
            background: transparent;
            border: 1px solid #d1d5db;
            color: #6b7280;
        }

        .btn-outline-secondary:hover {
            background: #f9fafb;
            border-color: #9ca3af;
            color: #374151;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }

        .action-bar {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }

        /* Collapsible */
        .collapsible-header {
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            user-select: none;
        }

        .collapsible-header i.toggle-icon {
            transition: transform 0.2s;
        }

        .collapsible-header.collapsed i.toggle-icon {
            transform: rotate(-90deg);
        }

        @media (max-width: 768px) {
            .library-header {
                padding: 20px;
            }

            .library-body {
                padding: 16px;
            }

            .summary-row {
                grid-template-columns: repeat(2, 1fr);
            }

            .session-details {
                flex-direction: column;
                gap: 8px;
            }

            .action-bar {
                flex-direction: column;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('library.inventory.index') }}">Inventory</a>
        @endslot
        @slot('title')
            Report
        @endslot
    @endcomponent

    <div class="library-container">
        <div class="library-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h4 class="mb-1 text-white"><i class="bx bx-file me-2"></i>Inventory Report</h4>
                    <p class="mb-0 opacity-75">{{ $session->scope_display }} &mdash; {{ ucfirst($session->status) }}</p>
                </div>
                <div class="d-flex gap-4 text-center">
                    <div class="stat-item text-white">
                        <h4 class="text-white mb-0">{{ $session->expected_count }}</h4>
                        <small class="text-white opacity-75">Expected</small>
                    </div>
                    <div class="stat-item text-white">
                        <h4 class="text-white mb-0">{{ $session->scanned_count }}</h4>
                        <small class="text-white opacity-75">Scanned</small>
                    </div>
                    <div class="stat-item text-white">
                        <h4 class="text-white mb-0">{{ $discrepancies->count() }}</h4>
                        <small class="text-white opacity-75">Discrepancies</small>
                    </div>
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

            @if (session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Summary Cards --}}
            @php
                $verificationRate = $session->expected_count > 0
                    ? round(($session->scanned_count / $session->expected_count) * 100, 1)
                    : 0;
            @endphp
            <div class="summary-row">
                <div class="summary-card expected">
                    <div class="value">{{ $session->expected_count }}</div>
                    <div class="label">Expected</div>
                </div>
                <div class="summary-card found">
                    <div class="value">{{ $session->scanned_count }}</div>
                    <div class="label">Found</div>
                </div>
                <div class="summary-card not-found">
                    <div class="value">{{ $discrepancies->count() }}</div>
                    <div class="label">Not Found</div>
                </div>
                <div class="summary-card rate">
                    <div class="value">{{ $verificationRate }}%</div>
                    <div class="label">Verification Rate</div>
                </div>
            </div>

            {{-- Session Details --}}
            <div class="session-details">
                <div class="detail-item">
                    <strong>Scope:</strong> {{ $session->scope_display }}
                </div>
                <div class="detail-item">
                    <strong>Started:</strong> {{ $session->started_at->format('d M Y H:i') }}
                </div>
                <div class="detail-item">
                    <strong>Started By:</strong> {{ $session->startedByUser->name ?? '-' }}
                </div>
                @if ($session->completed_at)
                    <div class="detail-item">
                        <strong>Completed:</strong> {{ $session->completed_at->format('d M Y H:i') }}
                    </div>
                @endif
            </div>

            {{-- Action Bar --}}
            <div class="action-bar">
                @if ($discrepancies->count() > 0)
                    <a href="{{ route('library.inventory.export', $session) }}" class="btn btn-success btn-sm">
                        <i class="bx bx-download"></i> Export Discrepancies
                    </a>
                    <button type="button" class="btn btn-warning btn-sm" id="mark-missing-btn" disabled>
                        <i class="bx bx-error-circle"></i> Mark Selected as Missing
                    </button>
                @endif
            </div>

            {{-- Discrepancies Section --}}
            <div class="section-title">Discrepancies (Not Found)</div>

            @if ($discrepancies->count() > 0)
                <form id="mark-missing-form" action="{{ route('library.inventory.mark-missing', $session) }}" method="POST">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 40px;">
                                        <input type="checkbox" id="select-all" class="form-check-input">
                                    </th>
                                    <th>Accession #</th>
                                    <th>Book Title</th>
                                    <th>Location</th>
                                    <th>Genre</th>
                                    <th>Current Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($discrepancies as $copy)
                                    @php $isMissing = $copy->status === 'missing'; @endphp
                                    <tr>
                                        <td>
                                            <input type="checkbox"
                                                   name="copy_ids[]"
                                                   value="{{ $copy->id }}"
                                                   class="form-check-input copy-checkbox"
                                                   {{ $isMissing ? 'disabled' : '' }}>
                                        </td>
                                        <td><span class="accession-code">{{ $copy->accession_number }}</span></td>
                                        <td>{{ $copy->book->title ?? '-' }}</td>
                                        <td>{{ $copy->book->location ?? '-' }}</td>
                                        <td>{{ $copy->book->genre ?? '-' }}</td>
                                        <td>
                                            <span class="badge-status badge-{{ str_replace('_', '-', $copy->status) }}">
                                                {{ ucfirst(str_replace('_', ' ', $copy->status)) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>
            @else
                <div class="all-verified">
                    <i class="bx bx-check-circle d-block"></i>
                    <h5>All Copies Verified</h5>
                    <p>Every expected copy was scanned during this inventory session. No discrepancies found.</p>
                </div>
            @endif

            {{-- Scanned Items (Collapsible) --}}
            <div class="section-title collapsible-header collapsed" data-bs-toggle="collapse" data-bs-target="#scanned-section">
                <i class="bx bx-chevron-down toggle-icon"></i>
                Scanned Items ({{ $scannedItems->count() }})
            </div>

            <div class="collapse" id="scanned-section">
                @if ($scannedItems->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Accession #</th>
                                    <th>Book Title</th>
                                    <th>Status</th>
                                    <th>Scanned By</th>
                                    <th>Scanned At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($scannedItems as $item)
                                    <tr>
                                        <td><span class="accession-code">{{ $item->copy->accession_number ?? '-' }}</span></td>
                                        <td>{{ $item->copy->book->title ?? '-' }}</td>
                                        <td>
                                            @php $copyStatus = $item->copy->status ?? 'unknown'; @endphp
                                            <span class="badge-status badge-{{ str_replace('_', '-', $copyStatus) }}">
                                                {{ ucfirst(str_replace('_', ' ', $copyStatus)) }}
                                            </span>
                                        </td>
                                        <td>{{ $item->scannedByUser->name ?? '-' }}</td>
                                        <td>{{ $item->scanned_at->format('d M Y H:i:s') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted px-3 py-2">No items were scanned during this session.</p>
                @endif
            </div>

            {{-- Back Link --}}
            <div class="mt-4 pt-3" style="border-top: 1px solid #e5e7eb;">
                <a href="{{ route('library.inventory.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back"></i> Back to Inventory
                </a>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var selectAll = document.getElementById('select-all');
            var checkboxes = document.querySelectorAll('.copy-checkbox:not(:disabled)');
            var markMissingBtn = document.getElementById('mark-missing-btn');
            var form = document.getElementById('mark-missing-form');

            function updateMarkMissingBtn() {
                if (!markMissingBtn) return;
                var checked = document.querySelectorAll('.copy-checkbox:checked');
                markMissingBtn.disabled = checked.length === 0;
            }

            // Select All
            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    checkboxes.forEach(function(cb) {
                        cb.checked = selectAll.checked;
                    });
                    updateMarkMissingBtn();
                });
            }

            // Individual checkbox change
            checkboxes.forEach(function(cb) {
                cb.addEventListener('change', function() {
                    // Update select-all state
                    if (selectAll) {
                        var allChecked = document.querySelectorAll('.copy-checkbox:not(:disabled):not(:checked)').length === 0;
                        selectAll.checked = allChecked && checkboxes.length > 0;
                    }
                    updateMarkMissingBtn();
                });
            });

            // Mark Missing button with SweetAlert confirmation
            if (markMissingBtn && form) {
                markMissingBtn.addEventListener('click', function() {
                    var checkedCount = document.querySelectorAll('.copy-checkbox:checked').length;

                    if (checkedCount === 0) return;

                    Swal.fire({
                        title: 'Mark as Missing?',
                        html: 'You are about to mark <strong>' + checkedCount + '</strong> ' +
                              (checkedCount === 1 ? 'copy' : 'copies') + ' as missing. This will update their status.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#f59e0b',
                        confirmButtonText: 'Yes, Mark Missing',
                        cancelButtonText: 'Cancel'
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            }

            // Collapsible toggle
            var collapsibleHeader = document.querySelector('.collapsible-header');
            if (collapsibleHeader) {
                var collapseTarget = document.getElementById('scanned-section');
                if (collapseTarget) {
                    collapseTarget.addEventListener('show.bs.collapse', function() {
                        collapsibleHeader.classList.remove('collapsed');
                    });
                    collapseTarget.addEventListener('hide.bs.collapse', function() {
                        collapsibleHeader.classList.add('collapsed');
                    });
                }
            }
        });
    </script>
@endsection
