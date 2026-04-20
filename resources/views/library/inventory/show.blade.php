@extends('layouts.master')
@section('title')
    Inventory Scanning
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

        /* Progress Section */
        .progress-section {
            margin-bottom: 24px;
        }

        .progress-stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            font-size: 14px;
            color: #374151;
        }

        .progress-stats .count {
            font-weight: 700;
            font-size: 16px;
        }

        .progress-bar-container {
            background: #e5e7eb;
            border-radius: 10px;
            height: 12px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            border-radius: 10px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            transition: width 0.5s ease;
        }

        .progress-bar-fill.complete {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .progress-percentage {
            text-align: center;
            font-size: 13px;
            color: #6b7280;
            margin-top: 6px;
        }

        /* Scan Input */
        .scan-section {
            margin-bottom: 24px;
        }

        .scan-input-group {
            display: flex;
            gap: 8px;
        }

        .scan-input-group .form-control {
            flex: 1;
            padding: 12px 16px;
            font-size: 16px;
            font-family: 'Courier New', monospace;
            border: 2px solid #d1d5db;
            border-radius: 3px;
            transition: all 0.2s;
        }

        .scan-input-group .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .scan-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 18px;
        }

        .scan-input-wrapper {
            position: relative;
            flex: 1;
        }

        .scan-input-wrapper .form-control {
            padding-left: 40px;
        }

        /* Scan Feedback */
        .scan-feedback {
            display: none;
            padding: 12px 16px;
            border-radius: 3px;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .scan-feedback.success {
            background: #d1fae5;
            border: 1px solid #6ee7b7;
            color: #065f46;
            display: block;
        }

        .scan-feedback.error {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            color: #991b1b;
            display: block;
        }

        .scan-feedback i {
            margin-right: 6px;
        }

        /* Recently Scanned Table */
        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin: 0 0 16px 0;
            color: #1f2937;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
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

        /* Status Badge */
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

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
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

        .session-actions {
            display: flex;
            gap: 12px;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
        }

        /* Info line */
        .info-line {
            font-size: 13px;
            color: #6b7280;
            padding: 4px 0 12px 0;
        }

        @media (max-width: 768px) {
            .library-header {
                padding: 20px;
            }

            .library-body {
                padding: 16px;
            }

            .scan-input-group {
                flex-direction: column;
            }

            .session-actions {
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
            Scanning
        @endslot
    @endcomponent

    <div class="library-container">
        <div class="library-header">
            <h4 class="mb-1 text-white"><i class="bx bx-scan me-2"></i>Inventory Scanning</h4>
            <p class="mb-0 opacity-75">{{ $session->scope_display }} &mdash; Started {{ $session->started_at->format('d M Y H:i') }}</p>
        </div>
        <div class="library-body">
            {{-- Progress Bar --}}
            <div class="progress-section">
                <div class="progress-stats">
                    <span>Scanned: <span class="count" id="scanned-count">{{ $session->scanned_count }}</span></span>
                    <span>Expected: <span class="count">{{ $session->expected_count }}</span></span>
                </div>
                <div class="progress-bar-container">
                    @php
                        $percentage = $session->expected_count > 0
                            ? round(($session->scanned_count / $session->expected_count) * 100)
                            : 0;
                    @endphp
                    <div class="progress-bar-fill {{ $percentage >= 100 ? 'complete' : '' }}"
                         id="progress-fill"
                         style="width: {{ min($percentage, 100) }}%"></div>
                </div>
                <div class="progress-percentage" id="progress-text">{{ $percentage }}% complete</div>
            </div>

            {{-- Scan Input --}}
            <div class="scan-section">
                <label class="form-label" style="font-weight: 500; color: #374151;">Scan or Enter Accession Number</label>
                <div class="scan-input-group">
                    <div class="scan-input-wrapper">
                        <i class="bx bx-barcode scan-icon"></i>
                        <input type="text" class="form-control" id="scan-input"
                               placeholder="Scan barcode or type accession number..."
                               autocomplete="off" autofocus>
                    </div>
                    <button type="button" class="btn btn-primary" id="scan-btn">
                        <i class="bx bx-check"></i> Verify
                    </button>
                </div>
                <div class="info-line">
                    <i class="bx bx-info-circle"></i>
                    Use a barcode scanner or type the accession number and press Enter
                </div>
            </div>

            {{-- Scan Feedback --}}
            <div class="scan-feedback" id="scan-feedback"></div>

            {{-- Recently Scanned --}}
            <div class="section-title">Recently Scanned (last 50)</div>

            @if ($recentItems->count() > 0)
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
                        <tbody id="scanned-table-body">
                            @foreach ($recentItems as $item)
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
                                    <td>{{ $item->scanned_at->format('H:i:s') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state" id="empty-state">
                    <i class="bx bx-scan"></i>
                    <h5>No Items Scanned Yet</h5>
                    <p>Start scanning book barcodes or entering accession numbers above.</p>
                </div>
            @endif

            {{-- Session Actions --}}
            <div class="session-actions">
                <a href="{{ route('library.inventory.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back"></i> Back to Index
                </a>
                <div class="ms-auto d-flex gap-2">
                    <button type="button" class="btn btn-danger" id="cancel-btn">
                        <i class="bx bx-x"></i> Cancel Session
                    </button>
                    <button type="button" class="btn btn-success" id="complete-btn">
                        <i class="bx bx-check-double"></i> Complete Inventory
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/onscan.js@1.5.1/onscan.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ====================================
            // CSRF Token Setup for jQuery AJAX
            // ====================================
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var scanInput = document.getElementById('scan-input');
            var scanBtn = document.getElementById('scan-btn');
            var scanFeedback = document.getElementById('scan-feedback');
            var scannedCount = {{ $session->scanned_count }};
            var expectedCount = {{ $session->expected_count }};
            var scanUrl = '{{ route("library.inventory.scan", $session) }}';

            // ====================================
            // Utility Functions
            // ====================================
            function escapeHtml(text) {
                var div = document.createElement('div');
                div.appendChild(document.createTextNode(text));
                return div.innerHTML;
            }

            function showFeedback(type, message) {
                scanFeedback.className = 'scan-feedback ' + type;
                var icon = type === 'success' ? 'bx-check-circle' : 'bx-error-circle';
                scanFeedback.innerHTML = '<i class="bx ' + icon + '"></i>' + escapeHtml(message);
                // Auto-hide after 5 seconds
                setTimeout(function() {
                    scanFeedback.className = 'scan-feedback';
                }, 5000);
            }

            function updateProgress() {
                var percentage = expectedCount > 0 ? Math.round((scannedCount / expectedCount) * 100) : 0;
                document.getElementById('scanned-count').textContent = scannedCount;
                var fill = document.getElementById('progress-fill');
                fill.style.width = Math.min(percentage, 100) + '%';
                if (percentage >= 100) {
                    fill.classList.add('complete');
                }
                document.getElementById('progress-text').textContent = percentage + '% complete';
            }

            function addRowToTable(data) {
                // Remove empty state if present
                var emptyState = document.getElementById('empty-state');
                if (emptyState) {
                    emptyState.remove();
                }

                var tbody = document.getElementById('scanned-table-body');
                // If table doesn't exist yet (was showing empty state), need to create it
                if (!tbody) {
                    var tableContainer = document.querySelector('.section-title').parentNode;
                    var existingTable = tableContainer.querySelector('.table-responsive');
                    if (!existingTable) {
                        var tableHtml = '<div class="table-responsive"><table class="table table-hover mb-0">';
                        tableHtml += '<thead><tr><th>Accession #</th><th>Book Title</th><th>Status</th><th>Scanned By</th><th>Scanned At</th></tr></thead>';
                        tableHtml += '<tbody id="scanned-table-body"></tbody></table></div>';
                        tableContainer.querySelector('.section-title').insertAdjacentHTML('afterend', tableHtml);
                        tbody = document.getElementById('scanned-table-body');
                    }
                }

                var statusClass = 'badge-' + data.status.replace('_', '-');
                var statusLabel = data.status.charAt(0).toUpperCase() + data.status.slice(1).replace('_', ' ');
                var now = new Date();
                var time = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0') + ':' + now.getSeconds().toString().padStart(2, '0');

                var row = '<tr style="animation: fadeIn 0.3s ease;">';
                row += '<td><span class="accession-code">' + escapeHtml(data.accession_number) + '</span></td>';
                row += '<td>' + escapeHtml(data.book_title) + '</td>';
                row += '<td><span class="badge-status ' + statusClass + '">' + escapeHtml(statusLabel) + '</span></td>';
                row += '<td>{{ auth()->user()->name }}</td>';
                row += '<td>' + time + '</td>';
                row += '</tr>';

                if (tbody) {
                    tbody.insertAdjacentHTML('afterbegin', row);

                    // Keep only last 50 rows
                    var rows = tbody.querySelectorAll('tr');
                    if (rows.length > 50) {
                        rows[rows.length - 1].remove();
                    }
                }
            }

            // ====================================
            // Scan Handler
            // ====================================
            function performScan() {
                var accession = scanInput.value.trim();
                if (!accession) return;

                scanBtn.disabled = true;

                $.ajax({
                    url: scanUrl,
                    method: 'POST',
                    data: {
                        accession_number: accession
                    },
                    success: function(response) {
                        scanBtn.disabled = false;
                        if (response.success) {
                            scannedCount = response.scanned_count;
                            showFeedback('success', response.message);
                            updateProgress();
                            addRowToTable(response.data);
                        }
                        scanInput.value = '';
                        scanInput.focus();
                    },
                    error: function(xhr) {
                        scanBtn.disabled = false;
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred.';
                        showFeedback('error', msg);
                        scanInput.value = '';
                        scanInput.focus();
                    }
                });
            }

            scanBtn.addEventListener('click', performScan);

            scanInput.addEventListener('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    performScan();
                }
            });

            // ====================================
            // onScan.js Barcode Scanner Detection
            // ====================================
            if (typeof onScan !== 'undefined') {
                onScan.attachTo(document, {
                    avgTimeByChar: 30,
                    suffixKeyCodes: [13],
                    onScan: function(sCode) {
                        scanInput.value = sCode;
                        performScan();
                    }
                });
            }

            // ====================================
            // Complete / Cancel Actions
            // ====================================
            document.getElementById('complete-btn').addEventListener('click', function() {
                Swal.fire({
                    title: 'Complete Inventory?',
                    html: 'You have scanned <strong>' + scannedCount + '</strong> of <strong>' + expectedCount + '</strong> expected items.<br>Unscanned items can be reviewed and marked as missing.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    confirmButtonText: 'Yes, Complete',
                    cancelButtonText: 'Continue Scanning'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        var form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ route("library.inventory.complete", $session) }}';
                        var csrf = document.createElement('input');
                        csrf.type = 'hidden';
                        csrf.name = '_token';
                        csrf.value = $('meta[name="csrf-token"]').attr('content');
                        form.appendChild(csrf);
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });

            document.getElementById('cancel-btn').addEventListener('click', function() {
                Swal.fire({
                    title: 'Cancel Inventory?',
                    text: 'This will cancel the session. All scan data will be preserved but the session will be marked as cancelled.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'Yes, Cancel Session',
                    cancelButtonText: 'Keep Scanning'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        var form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ route("library.inventory.cancel", $session) }}';
                        var csrf = document.createElement('input');
                        csrf.type = 'hidden';
                        csrf.name = '_token';
                        csrf.value = $('meta[name="csrf-token"]').attr('content');
                        form.appendChild(csrf);
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });

            // Focus scan input
            scanInput.focus();
        });
    </script>
@endsection
