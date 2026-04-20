<style>
    .sms-overview-card {
        border-radius: 3px;
        overflow: hidden;
        transition: all 0.3s ease;
        border: none;
        height: 100%;
    }

    .sms-overview-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .sms-card-gradient-1 {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .sms-card-gradient-2 {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }

    .sms-card-gradient-3 {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .sms-card-body {
        padding: 24px;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .sms-card-body::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 100%;
        height: 200%;
        background: rgba(255, 255, 255, 0.05);
        transform: rotate(30deg);
        pointer-events: none;
    }

    .sms-card-icon {
        width: 56px;
        height: 56px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-bottom: 16px;
        backdrop-filter: blur(10px);
    }

    .sms-card-label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
        opacity: 0.85;
        margin-bottom: 4px;
    }

    .sms-card-value {
        font-size: 28px;
        font-weight: 700;
        line-height: 1.2;
        margin-bottom: 4px;
    }

    .sms-card-subtitle {
        font-size: 13px;
        opacity: 0.85;
    }

    .sms-card-footer {
        padding: 12px 24px;
        background: rgba(0, 0, 0, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 13px;
    }

    .sms-card-footer span {
        opacity: 0.9;
    }

    .sms-progress-bar {
        height: 6px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 3px;
        overflow: hidden;
        margin-top: 16px;
    }

    .sms-progress-fill {
        height: 100%;
        background: rgba(255, 255, 255, 0.9);
        border-radius: 3px;
        transition: width 0.5s ease;
    }

    .sms-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        backdrop-filter: blur(10px);
    }

    .sms-badge i {
        margin-right: 6px;
    }
</style>

@php
    $smsEnabled = $communicationChannels['sms_enabled'] ?? false;
    $whatsappEnabled = $communicationChannels['whatsapp_enabled'] ?? false;
    $usagePercentage = $packageMetrics['amount'] > 0
        ? round(($packageMetrics['amountUsed'] / $packageMetrics['amount']) * 100)
        : 0;
    $remainingPercentage = 100 - $usagePercentage;
@endphp

@if ($smsEnabled)
    <!-- Hidden data attributes for parent page to read -->
    <div id="sms-metrics-data"
        data-balance="{{ number_format($packageMetrics['balance'], 2) }}"
        data-units="{{ number_format($packageMetrics['remainingUnits']) }}"
        data-cost-per-sms="{{ number_format($packageMetrics['costPerSms'], 2) }}"
        style="display: none;">
    </div>

<div class="row g-4 mb-4">
    <!-- Package Card -->
    <div class="col-md-4">
        <div class="sms-overview-card sms-card-gradient-1">
            <div class="sms-card-body">
                <div class="sms-card-icon">
                    <i class="fas fa-cube"></i>
                </div>
                <div class="sms-card-label">Current Package</div>
                <div class="sms-card-value">{{ $packageMetrics['type'] }}</div>
                <div class="sms-card-subtitle">
                    <i class="fas fa-tag me-1"></i> BWP {{ number_format($packageMetrics['amount'], 2) }} allocated
                </div>
            </div>
            <div class="sms-card-footer">
                <span><i class="fas fa-coins me-1"></i> Cost per SMS</span>
                <span class="fw-bold">BWP {{ number_format($packageMetrics['costPerSms'], 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Balance Card -->
    <div class="col-md-4">
        <div class="sms-overview-card sms-card-gradient-2">
            <div class="sms-card-body">
                <div class="sms-card-icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="sms-card-label">Available Balance</div>
                <div class="sms-card-value">BWP {{ number_format($packageMetrics['balance'], 2) }}</div>
                <div class="sms-card-subtitle">
                    <i class="fas fa-chart-pie me-1"></i> {{ $remainingPercentage }}% remaining
                </div>
                <div class="sms-progress-bar">
                    <div class="sms-progress-fill" style="width: {{ $remainingPercentage }}%"></div>
                </div>
            </div>
            <div class="sms-card-footer">
                <span><i class="fas fa-arrow-down me-1"></i> Amount Used</span>
                <span class="fw-bold">BWP {{ number_format($packageMetrics['amountUsed'], 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Units Card -->
    <div class="col-md-4">
        <div class="sms-overview-card sms-card-gradient-3">
            <div class="sms-card-body">
                <div class="sms-card-icon">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <div class="sms-card-label">SMS Units Remaining</div>
                <div class="sms-card-value">{{ number_format($packageMetrics['remainingUnits']) }}</div>
                <div class="sms-card-subtitle">
                    <span class="sms-badge">
                        <i class="fas fa-circle {{ $packageMetrics['alertLevel'] === 'success' ? 'text-success' : ($packageMetrics['alertLevel'] === 'warning' ? 'text-warning' : 'text-danger') }}" style="font-size: 8px;"></i>
                        {{ $packageMetrics['alertLevel'] === 'success' ? 'Healthy' : ($packageMetrics['alertLevel'] === 'warning' ? 'Low Balance' : 'Critical') }}
                    </span>
                </div>
            </div>
            <div class="sms-card-footer">
                <span><i class="fas fa-calculator me-1"></i> Est. messages left</span>
                <span class="fw-bold">{{ number_format($packageMetrics['remainingUnits']) }}</span>
            </div>
        </div>
    </div>
</div>
@else
    <div class="alert alert-info mb-4">
        <i class="fab fa-whatsapp me-2"></i>
        WhatsApp messaging is enabled. Template-driven direct and broadcast sends will appear in the history table below once sent.
    </div>
@endif

<style>
    .messages-table thead th {
        background: #f9fafb;
        border-bottom: 2px solid #e5e7eb;
        font-weight: 600;
        color: #374151;
        font-size: 13px;
        padding: 12px 10px;
    }

    .messages-table tbody td {
        padding: 12px 10px;
        vertical-align: middle;
        font-size: 14px;
    }

    .messages-table tbody tr:hover {
        background-color: #f9fafb;
    }

    .sender-cell {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .sender-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 12px;
    }

    .type-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        text-transform: capitalize;
    }

    .type-bulk { background: #dbeafe; color: #1e40af; }
    .type-single { background: #fef3c7; color: #92400e; }
    .type-system { background: #f3f4f6; color: #4b5563; }

    .cost-cell {
        font-weight: 600;
        color: #059669;
    }

    .recipients-badge {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }

    .message-preview {
        max-width: 250px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: #6b7280;
    }

    .controls .form-control,
    .controls .form-select {
        font-size: 0.9rem;
        border-radius: 3px;
    }
</style>

<!-- Filters Row -->
<div class="row align-items-center mb-3">
    <div class="col-lg-12">
        <div class="controls">
            <div class="row g-2 align-items-center">
                <div class="col-lg-3 col-md-3 col-sm-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search..." id="messageSearchInput">
                    </div>
                </div>
                <div class="col-lg-2 col-md-2 col-sm-6">
                    <select class="form-select" id="typeFilter">
                        <option value="">All Types</option>
                        <option value="bulk">Bulk</option>
                        <option value="single">Single</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-2 col-sm-6">
                    <select class="form-select" id="recipientFilter">
                        <option value="">All Recipients</option>
                        <option value="high">10+ Recipients</option>
                        <option value="medium">5-9 Recipients</option>
                        <option value="low">1-4 Recipients</option>
                    </select>
                </div>
                <div class="col-lg-1 col-md-2 col-sm-6">
                    <button type="button" class="btn btn-light w-100" id="resetMessageFilters">Reset</button>
                </div>
                <div class="col-lg-4 col-md-3 col-sm-12 text-end">
                    @if ($smsEnabled)
                    <a href="{{ route('sms.delivery-log') }}" class="btn btn-outline-primary me-1">
                        <i class="fas fa-chart-line me-1"></i> Delivery Log
                    </a>
                    @endif
                    @can('manage-communications')
                        @if (!session('is_past_term'))
                            @if ($smsEnabled && $whatsappEnabled)
                            <div class="btn-group">
                                <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-paper-plane me-1"></i> Send Bulk Message
                                </button>
                                <div class="dropdown-menu dropdown-menu-end mt-1">
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#sendBulkSMSSponsorModal">
                                        <i class="fas fa-user-friends me-2" style="color: #4287f5;"></i> Sponsors (SMS)
                                    </a>
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#sendBulkSMSUserModal">
                                        <i class="fas fa-users me-2" style="color: #6a5acd;"></i> Staff (SMS)
                                    </a>
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#sendBulkWhatsAppUserModal">
                                        <i class="fab fa-whatsapp me-2 text-success"></i> Staff (WhatsApp)
                                    </a>
                                </div>
                            </div>
                            @elseif ($smsEnabled)
                            <div class="btn-group">
                                <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-paper-plane me-1"></i> Send Bulk SMS
                                </button>
                                <div class="dropdown-menu dropdown-menu-end mt-1">
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#sendBulkSMSSponsorModal">
                                        <i class="fas fa-user-friends me-2" style="color: #4287f5;"></i> Sponsors
                                    </a>
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#sendBulkSMSUserModal">
                                        <i class="fas fa-users me-2" style="color: #6a5acd;"></i> Staff
                                    </a>
                                </div>
                            </div>
                            @elseif ($whatsappEnabled)
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#sendBulkWhatsAppUserModal">
                                <i class="fab fa-whatsapp me-1"></i> Send Bulk WhatsApp
                            </button>
                            @endif
                        @endif
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>

@php
    $allMessages = collect();
    $messageIndex = 0;

    if (!empty($nonBulkMessages)) {
        foreach ($nonBulkMessages as $message) {
            $authorName = 'System';
            if ($message->author) {
                if (is_object($message->author)) {
                    $authorName = $message->author->fullName;
                } else {
                    $author = \App\Models\User::find($message->author);
                    $authorName = $author ? $author->fullName : 'System';
                }
            }
            $allMessages->push([
                'index' => ++$messageIndex,
                'author' => $authorName,
                'channel' => $message->channel ?? 'sms',
                'recipient' => $message->user_id ? ($message->user->phone ?? 'N/A') : ($message->sponsor->phone ?? 'N/A'),
                'body' => $message->body,
                'recipients_count' => $message->num_recipients,
                'details' => ($message->channel ?? 'sms') === 'whatsapp' ? ($message->template_name ?? 'Template') : ($message->sms_count ?? 0),
                'type' => $message->type ?? 'single',
                'is_bulk' => false,
                'sent_at' => $message->created_at,
                'status' => $message->delivery_status ?? $message->status ?? 'pending',
            ]);
        }
    }

    if (!empty($bulkMessages)) {
        foreach ($bulkMessages as $body => $group) {
            $firstMessage = $group->first();
            $authorName = 'System';
            if ($firstMessage->author) {
                if (is_object($firstMessage->author)) {
                    $authorName = $firstMessage->author->fullName;
                } else {
                    $author = \App\Models\User::find($firstMessage->author);
                    $authorName = $author ? $author->fullName : 'System';
                }
            }
            $allMessages->push([
                'index' => ++$messageIndex,
                'author' => $authorName,
                'channel' => $firstMessage->channel ?? 'sms',
                'recipients' => $group,
                'body' => $firstMessage->body,
                'recipients_count' => $firstMessage->num_recipients,
                'details' => ($firstMessage->channel ?? 'sms') === 'whatsapp' ? ($firstMessage->template_name ?? 'Template') : ($firstMessage->sms_count ?? 0),
                'type' => $firstMessage->type ?? 'bulk',
                'is_bulk' => true,
                'sent_at' => $firstMessage->created_at,
                'status' => $firstMessage->delivery_status ?? $firstMessage->status ?? 'pending',
            ]);
        }
    }
@endphp

<div class="table-responsive">
    <table id="messages-table" class="table table-striped messages-table align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>Sender</th>
                <th>Channel</th>
                <th>Message</th>
                <th>Recipients</th>
                <th>Details</th>
                <th>Sent At</th>
                <th>Type</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @if ($allMessages->isEmpty())
                <tr>
                    <td colspan="9" class="text-center text-muted py-5">
                        <i class="fas fa-comments" style="font-size: 48px; opacity: 0.3;"></i>
                        <p class="mt-3 mb-1">No messages to display for this term</p>
                        <small class="text-muted">Messages will appear here once sent</small>
                    </td>
                </tr>
            @else
                @foreach ($allMessages as $msg)
                    @php
                        $recipientLevel = $msg['recipients_count'] >= 10 ? 'high' : ($msg['recipients_count'] >= 5 ? 'medium' : 'low');
                        $typeClass = $msg['is_bulk'] ? 'type-bulk' : 'type-single';
                    @endphp
                    <tr class="message-row"
                        data-sender="{{ strtolower($msg['author']) }}"
                        data-message="{{ strtolower(substr($msg['body'], 0, 100)) }}"
                        data-type="{{ $msg['is_bulk'] ? 'bulk' : 'single' }}"
                        data-recipients="{{ $recipientLevel }}">
                        <td class="text-muted">{{ $msg['index'] }}</td>
                        <td>
                            <div class="sender-cell">
                                <div class="sender-avatar">
                                    {{ strtoupper(substr($msg['author'], 0, 2)) }}
                                </div>
                                <div>
                                    <div class="fw-medium">{{ $msg['author'] }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge {{ ($msg['channel'] ?? 'sms') === 'whatsapp' ? 'bg-success' : 'bg-info' }}">
                                {{ strtoupper($msg['channel'] ?? 'sms') }}
                            </span>
                        </td>
                        <td>
                            <div class="message-preview" title="{{ strip_tags($msg['body']) }}">
                                {{ Str::limit(strip_tags(str_replace([' :From Heritage Pro EMS', ':From Heritage Pro EMS', 'From: Heritage Pro EMS'], '', $msg['body'])), 50) }}
                            </div>
                        </td>
                        <td>
                            @if ($msg['is_bulk'] && isset($msg['recipients']))
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 12px; padding: 4px 10px;">
                                        <span class="recipients-badge">{{ $msg['recipients_count'] }}</span>
                                    </button>
                                    <div class="dropdown-menu" style="max-height: 200px; overflow-y: auto;">
                                        @foreach ($msg['recipients'] as $recipientMessage)
                                            <span class="dropdown-item small" style="cursor: default;">
                                                <i class="fas fa-phone me-2 text-muted"></i>
                                                {{ $recipientMessage->user_id ? ($recipientMessage->user->phone ?? 'N/A') : ($recipientMessage->sponsor->phone ?? 'N/A') }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <span class="recipients-badge">{{ $msg['recipients_count'] }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-light text-dark">{{ $msg['details'] }}</span>
                        </td>
                        <td>
                            <span class="text-muted" title="{{ $msg['sent_at'] ? $msg['sent_at']->format('M d, Y h:i A') : 'N/A' }}">
                                {{ $msg['sent_at'] ? $msg['sent_at']->format('M d, Y') : 'N/A' }}
                                <br>
                                <small>{{ $msg['sent_at'] ? $msg['sent_at']->format('h:i A') : '' }}</small>
                            </span>
                        </td>
                        <td>
                            <span class="type-badge {{ $typeClass }}">
                                {{ $msg['is_bulk'] ? 'Bulk' : ucfirst($msg['type']) }}
                            </span>
                        </td>
                        <td>
                            @php
                                $status = strtolower($msg['status'] ?? 'pending');
                                $statusClass = match ($status) {
                                    'delivered' => 'bg-success',
                                    'failed', 'rejected', 'undelivered' => 'bg-danger',
                                    'queued', 'sent', 'accepted' => 'bg-warning text-dark',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ strtoupper($status) }}</span>
                        </td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>

@if ($allMessages->isNotEmpty())
<div class="pagination-container mt-3" style="display: flex; justify-content: space-between; align-items: center;">
    <div class="text-muted" id="messages-results-info">
        Showing <span id="messages-showing-from">1</span> to <span id="messages-showing-to">{{ min(20, $allMessages->count()) }}</span> of <span id="messages-total-count">{{ $allMessages->count() }}</span> Messages
    </div>
    <nav id="messages-pagination-nav">
        <!-- Pagination will be inserted here by JavaScript -->
    </nav>
</div>

<script>
        // Client-side filtering and pagination for messages
        let messagesCurrentPage = 1;
        const messagesPerPage = 20;

        function filterAndPaginateMessages(resetPage = true) {
            if (resetPage) messagesCurrentPage = 1;

            const searchTerm = document.getElementById('messageSearchInput')?.value.toLowerCase() || '';
            const typeFilter = document.getElementById('typeFilter')?.value.toLowerCase() || '';
            const recipientFilter = document.getElementById('recipientFilter')?.value.toLowerCase() || '';

            const allRows = document.querySelectorAll('.message-row');
            let filteredRows = [];

            allRows.forEach(row => {
                const sender = row.dataset.sender || '';
                const message = row.dataset.message || '';
                const type = row.dataset.type || '';
                const recipients = row.dataset.recipients || '';

                const matchesSearch = !searchTerm || sender.includes(searchTerm) || message.includes(searchTerm);
                const matchesType = !typeFilter || type === typeFilter;
                const matchesRecipients = !recipientFilter || recipients === recipientFilter;

                if (matchesSearch && matchesType && matchesRecipients) {
                    filteredRows.push(row);
                }
            });

            // Calculate pagination
            const totalFiltered = filteredRows.length;
            const totalPages = Math.ceil(totalFiltered / messagesPerPage);
            const startIndex = (messagesCurrentPage - 1) * messagesPerPage;
            const endIndex = startIndex + messagesPerPage;

            // Show/hide based on pagination
            allRows.forEach(row => row.style.display = 'none');
            filteredRows.forEach((row, index) => {
                if (index >= startIndex && index < endIndex) {
                    row.style.display = '';
                }
            });

            // Update showing info
            const showingFrom = totalFiltered > 0 ? startIndex + 1 : 0;
            const showingTo = Math.min(endIndex, totalFiltered);

            const fromEl = document.getElementById('messages-showing-from');
            const toEl = document.getElementById('messages-showing-to');
            const totalEl = document.getElementById('messages-total-count');

            if (fromEl) fromEl.textContent = showingFrom;
            if (toEl) toEl.textContent = showingTo;
            if (totalEl) totalEl.textContent = totalFiltered;

            // Generate pagination controls
            generateMessagesPagination(totalPages, messagesCurrentPage);
        }

        function generateMessagesPagination(totalPages, current) {
            const paginationNav = document.getElementById('messages-pagination-nav');
            if (!paginationNav) return;

            if (totalPages <= 1) {
                paginationNav.innerHTML = '';
                return;
            }

            let html = '<ul class="pagination mb-0">';

            html += `<li class="page-item ${current === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="goToMessagesPage(${current - 1}); return false;">Previous</a>
            </li>`;

            const maxVisible = 5;
            let startPage = Math.max(1, current - Math.floor(maxVisible / 2));
            let endPage = Math.min(totalPages, startPage + maxVisible - 1);

            if (endPage - startPage < maxVisible - 1) {
                startPage = Math.max(1, endPage - maxVisible + 1);
            }

            if (startPage > 1) {
                html += `<li class="page-item"><a class="page-link" href="#" onclick="goToMessagesPage(1); return false;">1</a></li>`;
                if (startPage > 2) {
                    html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
            }

            for (let i = startPage; i <= endPage; i++) {
                html += `<li class="page-item ${i === current ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="goToMessagesPage(${i}); return false;">${i}</a>
                </li>`;
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
                html += `<li class="page-item"><a class="page-link" href="#" onclick="goToMessagesPage(${totalPages}); return false;">${totalPages}</a></li>`;
            }

            html += `<li class="page-item ${current === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="goToMessagesPage(${current + 1}); return false;">Next</a>
            </li>`;

            html += '</ul>';
            paginationNav.innerHTML = html;
        }

        function goToMessagesPage(page) {
            messagesCurrentPage = page;
            filterAndPaginateMessages(false);
        }

        function resetMessageFilters() {
            document.getElementById('messageSearchInput').value = '';
            document.getElementById('typeFilter').value = '';
            document.getElementById('recipientFilter').value = '';
            filterAndPaginateMessages(true);
        }

        // Event listeners
        document.getElementById('messageSearchInput')?.addEventListener('input', () => filterAndPaginateMessages(true));
        document.getElementById('typeFilter')?.addEventListener('change', () => filterAndPaginateMessages(true));
        document.getElementById('recipientFilter')?.addEventListener('change', () => filterAndPaginateMessages(true));
        document.getElementById('resetMessageFilters')?.addEventListener('click', resetMessageFilters);

        // Initialize on load
        document.addEventListener('DOMContentLoaded', () => filterAndPaginateMessages(true));

        // Also initialize immediately in case DOMContentLoaded already fired
        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            setTimeout(() => filterAndPaginateMessages(true), 100);
        }
    </script>
@endif
