<style>
    .email-overview-card {
        border-radius: 3px;
        overflow: hidden;
        transition: all 0.3s ease;
        border: none;
        height: 100%;
    }

    .email-overview-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .email-card-gradient-1 {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .email-card-gradient-2 {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }

    .email-card-gradient-3 {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .email-card-body {
        padding: 24px;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .email-card-body::before {
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

    .email-card-icon {
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

    .email-card-label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
        opacity: 0.85;
        margin-bottom: 4px;
    }

    .email-card-value {
        font-size: 28px;
        font-weight: 700;
        line-height: 1.2;
        margin-bottom: 4px;
    }

    .email-card-subtitle {
        font-size: 13px;
        opacity: 0.85;
    }

    .email-card-footer {
        padding: 12px 24px;
        background: rgba(0, 0, 0, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 13px;
    }

    .email-card-footer span {
        opacity: 0.9;
    }

    .email-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        backdrop-filter: blur(10px);
    }

    .email-badge i {
        margin-right: 6px;
    }
</style>

<div class="row g-4 mb-4">
    <!-- Total Emails Card -->
    <div class="col-md-4">
        <div class="email-overview-card email-card-gradient-1">
            <div class="email-card-body">
                <div class="email-card-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="email-card-label">Total Emails</div>
                <div class="email-card-value">{{ $messages->count() }}</div>
                <div class="email-card-subtitle">
                    <i class="fas fa-chart-line me-1"></i> This term's communications
                </div>
            </div>
            <div class="email-card-footer">
                <span><i class="fas fa-calendar me-1"></i> Current Term</span>
                <span class="email-badge">
                    <i class="fas fa-circle text-success" style="font-size: 8px;"></i>
                    Active
                </span>
            </div>
        </div>
    </div>

    <!-- Bulk Emails Card -->
    <div class="col-md-4">
        <div class="email-overview-card email-card-gradient-2">
            <div class="email-card-body">
                <div class="email-card-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="email-card-label">Bulk Emails</div>
                <div class="email-card-value">{{ $bulkEmails->count() }}</div>
                <div class="email-card-subtitle">
                    <i class="fas fa-broadcast-tower me-1"></i> Mass communications
                </div>
            </div>
            <div class="email-card-footer">
                <span><i class="fas fa-layer-group me-1"></i> Group Messages</span>
                <span class="fw-bold">{{ $bulkEmails->count() > 0 ? round(($bulkEmails->count() / max($messages->count(), 1)) * 100) : 0 }}%</span>
            </div>
        </div>
    </div>

    <!-- Individual Emails Card -->
    <div class="col-md-4">
        <div class="email-overview-card email-card-gradient-3">
            <div class="email-card-body">
                <div class="email-card-icon">
                    <i class="fas fa-user"></i>
                </div>
                <div class="email-card-label">Individual Emails</div>
                <div class="email-card-value">{{ $nonBulkEmails->count() }}</div>
                <div class="email-card-subtitle">
                    <i class="fas fa-paper-plane me-1"></i> Personal communications
                </div>
            </div>
            <div class="email-card-footer">
                <span><i class="fas fa-user-check me-1"></i> Direct Messages</span>
                <span class="fw-bold">{{ $nonBulkEmails->count() > 0 ? round(($nonBulkEmails->count() / max($messages->count(), 1)) * 100) : 0 }}%</span>
            </div>
        </div>
    </div>
</div>

<style>
    .emails-table thead th {
        background: #f9fafb;
        border-bottom: 2px solid #e5e7eb;
        font-weight: 600;
        color: #374151;
        font-size: 13px;
        padding: 12px 10px;
    }

    .emails-table tbody td {
        padding: 12px 10px;
        vertical-align: middle;
        font-size: 14px;
    }

    .emails-table tbody tr:hover {
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

    .recipients-badge {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }

    .subject-cell {
        max-width: 250px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .attachment-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }

    .attachment-yes {
        background: #d1fae5;
        color: #065f46;
    }

    .attachment-no {
        background: #f3f4f6;
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
                        <input type="text" class="form-control" placeholder="Search..." id="emailSearchInput">
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <select class="form-select" id="emailTypeFilter">
                        <option value="">All Types</option>
                        <option value="bulk">Bulk</option>
                        <option value="single">Individual</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <select class="form-select" id="attachmentFilter">
                        <option value="">All Emails</option>
                        <option value="yes">With Attachment</option>
                        <option value="no">No Attachment</option>
                    </select>
                </div>
                <div class="col-lg-1 col-md-2 col-sm-6">
                    <button type="button" class="btn btn-light w-100" id="resetEmailFilters">Reset</button>
                </div>
                @can('manage-communications')
                    @if (!session('is_past_term'))
                        <div class="col-lg-4 col-md-4 col-sm-6 text-end">
                            <div class="btn-group">
                                <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-paper-plane me-1"></i> Send Bulk Email
                                </button>
                                <div class="dropdown-menu dropdown-menu-end mt-1">
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#sendBulkEmailSponsorModal">
                                        <i class="fas fa-user-friends me-2" style="color: #4287f5;"></i> Sponsors
                                    </a>
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#sendBulkEmailUserModal">
                                        <i class="fas fa-users me-2" style="color: #6a5acd;"></i> Staff
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                @endcan
            </div>
        </div>
    </div>
</div>

@php
    $allEmails = collect();
    $emailIndex = 0;

    // Add non-bulk emails
    foreach ($nonBulkEmails as $message) {
        $recipientName = '';
        if ($message->sponsor_id) {
            $recipientName = $message->sponsor->fullName ?? 'Unknown Sponsor';
        } else {
            $recipientName = $message->user->fullName ?? 'Unknown User';
        }

        $allEmails->push([
            'index' => ++$emailIndex,
            'sender' => $message->sender->fullName ?? 'Unknown Sender',
            'recipient' => $recipientName,
            'subject' => $message->subject ?? 'No Subject',
            'body' => $message->body ?? '',
            'recipients_count' => $message->num_of_recipients ?? 1,
            'type' => 'single',
            'has_attachment' => !empty($message->attachment_path),
            'attachment_path' => $message->attachment_path,
            'is_bulk' => false,
            'recipients' => null,
            'sent_at' => $message->created_at
        ]);
    }

    // Add bulk emails
    foreach ($bulkEmails as $group) {
        if ($group && $group->count() > 0) {
            $message = $group->first();
            $allEmails->push([
                'index' => ++$emailIndex,
                'sender' => $message->sender->fullName ?? 'Unknown Sender',
                'recipient' => $message->num_of_recipients . ' recipients',
                'subject' => $message->subject ?? 'No Subject',
                'body' => $message->body ?? '',
                'recipients_count' => $message->num_of_recipients ?? 0,
                'type' => 'bulk',
                'has_attachment' => !empty($message->attachment_path),
                'attachment_path' => $message->attachment_path,
                'is_bulk' => true,
                'recipients' => $group,
                'sent_at' => $message->created_at
            ]);
        }
    }
@endphp

<div class="table-responsive">
    <table id="emails-table" class="table table-striped emails-table align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>From</th>
                <th>Subject</th>
                <th>To</th>
                <th>Recipients</th>
                <th>Type</th>
                <th>Sent At</th>
                <th>Attachment</th>
            </tr>
        </thead>
        <tbody>
            @if ($allEmails->isEmpty())
                <tr>
                    <td colspan="8" class="text-center text-muted py-5">
                        <i class="fas fa-inbox" style="font-size: 48px; opacity: 0.3;"></i>
                        <p class="mt-3 mb-1">No emails to display for this term</p>
                        <small class="text-muted">Emails will appear here once sent</small>
                    </td>
                </tr>
            @else
                @foreach ($allEmails as $email)
                    <tr class="email-row"
                        data-sender="{{ strtolower($email['sender']) }}"
                        data-subject="{{ strtolower($email['subject']) }}"
                        data-type="{{ $email['type'] }}"
                        data-attachment="{{ $email['has_attachment'] ? 'yes' : 'no' }}">
                        <td class="text-muted">{{ $email['index'] }}</td>
                        <td>
                            <div class="sender-cell">
                                <div class="sender-avatar">
                                    {{ strtoupper(substr($email['sender'], 0, 2)) }}
                                </div>
                                <div>
                                    <div class="fw-medium">{{ $email['sender'] }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="subject-cell" title="{{ strip_tags($email['body']) }}">
                                <span class="fw-medium">{{ Str::limit($email['subject'], 40) }}</span>
                            </div>
                        </td>
                        <td>
                            @if ($email['is_bulk'] && $email['recipients'] && $email['recipients_count'] < 50)
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 12px; padding: 4px 10px;">
                                        View Recipients
                                    </button>
                                    <div class="dropdown-menu" style="max-height: 200px; overflow-y: auto;">
                                        @foreach ($email['recipients'] as $recipientMessage)
                                            <span class="dropdown-item small" style="cursor: default;">
                                                <i class="fas fa-user me-2 text-muted"></i>
                                                @if ($recipientMessage->user_id)
                                                    {{ $recipientMessage->user->firstname ?? 'Unknown User' }}
                                                @else
                                                    {{ $recipientMessage->sponsor->fullName ?? 'Unknown Sponsor' }}
                                                @endif
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <span class="text-muted">{{ $email['recipient'] }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="recipients-badge">{{ $email['recipients_count'] }}</span>
                        </td>
                        <td>
                            <span class="type-badge {{ $email['is_bulk'] ? 'type-bulk' : 'type-single' }}">
                                {{ $email['is_bulk'] ? 'Bulk' : 'Individual' }}
                            </span>
                        </td>
                        <td>
                            <span class="text-muted" title="{{ $email['sent_at'] ? $email['sent_at']->format('M d, Y h:i A') : 'N/A' }}">
                                {{ $email['sent_at'] ? $email['sent_at']->format('M d, Y') : 'N/A' }}
                                <br>
                                <small>{{ $email['sent_at'] ? $email['sent_at']->format('h:i A') : '' }}</small>
                            </span>
                        </td>
                        <td>
                            @if ($email['has_attachment'])
                                <a href="{{ url('storage/' . $email['attachment_path']) }}" download class="attachment-badge attachment-yes">
                                    <i class="fas fa-paperclip"></i> Yes
                                </a>
                            @else
                                <span class="attachment-badge attachment-no">
                                    <i class="fas fa-times"></i> No
                                </span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>

@if ($allEmails->isNotEmpty())
<div class="pagination-container mt-3" style="display: flex; justify-content: space-between; align-items: center;">
    <div class="text-muted" id="emails-results-info">
        Showing <span id="emails-showing-from">1</span> to <span id="emails-showing-to">{{ min(20, $allEmails->count()) }}</span> of <span id="emails-total-count">{{ $allEmails->count() }}</span> Emails
    </div>
    <nav id="emails-pagination-nav">
        <!-- Pagination will be inserted here by JavaScript -->
    </nav>
</div>

<script>
    // Client-side filtering and pagination for emails
    let emailsCurrentPage = 1;
    const emailsPerPage = 20;

    function filterAndPaginateEmails(resetPage = true) {
        if (resetPage) emailsCurrentPage = 1;

        const searchTerm = document.getElementById('emailSearchInput')?.value.toLowerCase() || '';
        const typeFilter = document.getElementById('emailTypeFilter')?.value.toLowerCase() || '';
        const attachmentFilter = document.getElementById('attachmentFilter')?.value.toLowerCase() || '';

        const allRows = document.querySelectorAll('.email-row');
        let filteredRows = [];

        allRows.forEach(row => {
            const sender = row.dataset.sender || '';
            const subject = row.dataset.subject || '';
            const type = row.dataset.type || '';
            const attachment = row.dataset.attachment || '';

            const matchesSearch = !searchTerm || sender.includes(searchTerm) || subject.includes(searchTerm);
            const matchesType = !typeFilter || type === typeFilter;
            const matchesAttachment = !attachmentFilter || attachment === attachmentFilter;

            if (matchesSearch && matchesType && matchesAttachment) {
                filteredRows.push(row);
            }
        });

        // Calculate pagination
        const totalFiltered = filteredRows.length;
        const totalPages = Math.ceil(totalFiltered / emailsPerPage);
        const startIndex = (emailsCurrentPage - 1) * emailsPerPage;
        const endIndex = startIndex + emailsPerPage;

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

        const fromEl = document.getElementById('emails-showing-from');
        const toEl = document.getElementById('emails-showing-to');
        const totalEl = document.getElementById('emails-total-count');

        if (fromEl) fromEl.textContent = showingFrom;
        if (toEl) toEl.textContent = showingTo;
        if (totalEl) totalEl.textContent = totalFiltered;

        // Generate pagination controls
        generateEmailsPagination(totalPages, emailsCurrentPage);
    }

    function generateEmailsPagination(totalPages, current) {
        const paginationNav = document.getElementById('emails-pagination-nav');
        if (!paginationNav) return;

        if (totalPages <= 1) {
            paginationNav.innerHTML = '';
            return;
        }

        let html = '<ul class="pagination mb-0">';

        html += `<li class="page-item ${current === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="goToEmailsPage(${current - 1}); return false;">Previous</a>
        </li>`;

        const maxVisible = 5;
        let startPage = Math.max(1, current - Math.floor(maxVisible / 2));
        let endPage = Math.min(totalPages, startPage + maxVisible - 1);

        if (endPage - startPage < maxVisible - 1) {
            startPage = Math.max(1, endPage - maxVisible + 1);
        }

        if (startPage > 1) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="goToEmailsPage(1); return false;">1</a></li>`;
            if (startPage > 2) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            html += `<li class="page-item ${i === current ? 'active' : ''}">
                <a class="page-link" href="#" onclick="goToEmailsPage(${i}); return false;">${i}</a>
            </li>`;
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
            html += `<li class="page-item"><a class="page-link" href="#" onclick="goToEmailsPage(${totalPages}); return false;">${totalPages}</a></li>`;
        }

        html += `<li class="page-item ${current === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="goToEmailsPage(${current + 1}); return false;">Next</a>
        </li>`;

        html += '</ul>';
        paginationNav.innerHTML = html;
    }

    function goToEmailsPage(page) {
        emailsCurrentPage = page;
        filterAndPaginateEmails(false);
    }

    function resetEmailFilters() {
        document.getElementById('emailSearchInput').value = '';
        document.getElementById('emailTypeFilter').value = '';
        document.getElementById('attachmentFilter').value = '';
        filterAndPaginateEmails(true);
    }

    // Event listeners
    document.getElementById('emailSearchInput')?.addEventListener('input', () => filterAndPaginateEmails(true));
    document.getElementById('emailTypeFilter')?.addEventListener('change', () => filterAndPaginateEmails(true));
    document.getElementById('attachmentFilter')?.addEventListener('change', () => filterAndPaginateEmails(true));
    document.getElementById('resetEmailFilters')?.addEventListener('click', resetEmailFilters);

    // Initialize on load
    document.addEventListener('DOMContentLoaded', () => filterAndPaginateEmails(true));

    // Also initialize immediately in case DOMContentLoaded already fired
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(() => filterAndPaginateEmails(true), 100);
    }
</script>
@endif
