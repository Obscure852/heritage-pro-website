@extends('layouts.master')

@section('title')
    Users
@endsection

@section('css')
    <style>
        .admissions-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .admissions-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .admissions-body {
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

        .controls .form-control,
        .controls .form-select {
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .stat-item h4 {
                font-size: 1.25rem;
            }

            .stat-item small {
                font-size: 0.75rem;
            }

            .controls .form-control,
            .controls .form-select {
                font-size: 0.85rem;
            }
        }

        .badge-status {
            text-transform: capitalize;
        }

        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 16px;
        }

        .student-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .student-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            background: #e2e8f0;
        }

        .student-avatar-placeholder {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e2e8f0;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .action-buttons {
            display: flex;
            gap: 4px;
            justify-content: flex-end;
        }

        .action-buttons .btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .action-buttons .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .action-buttons .btn i {
            font-size: 16px;
        }

        /* Modal Text Sizing */
        #smsModal .modal-body,
        #emailModal .modal-body {
            font-size: 0.9rem;
        }

        #smsModal .form-label,
        #emailModal .form-label {
            font-size: 0.85rem;
        }

        #smsModal .modal-title,
        #emailModal .modal-title {
            font-size: 1.1rem;
        }

        #smsModal .text-muted,
        #emailModal .text-muted {
            font-size: 0.8rem;
        }

        #smsModal small,
        #emailModal small {
            font-size: 0.75rem;
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
    </style>
@endsection

@section('content')
    <div class="admissions-container">
        <div class="admissions-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;">Users</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Browse and manage staff users</p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['current'] ?? 0 }}</h4>
                                <small class="opacity-75">Current</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['male'] ?? 0 }}</h4>
                                <small class="opacity-75">Male</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['female'] ?? 0 }}</h4>
                                <small class="opacity-75">Female</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="admissions-body">
            <div class="help-text">
                <div class="help-title">Staff Directory</div>
                <div class="help-content">
                    Browse and manage all staff members. Use filters to search by name, department, position, or status.
                    Click on a user to view their full profile, or use the action buttons to edit, email, or SMS them
                    directly.
                </div>
            </div>

            <div class="row align-items-center mb-3">
                <div class="col-lg-8 col-md-12">
                    <div class="controls">
                        <div class="row g-2 align-items-center">
                            <div class="col-lg-4 col-md-4 col-sm-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" name="search" value="{{ request('search') }}"
                                        class="form-control" placeholder="Search staff name..." id="searchInput">
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-4 col-sm-6">
                                <select name="position" class="form-select" id="positionFilter">
                                    <option value="">All Positions</option>
                                    @foreach ($positions as $p)
                                        <option value="{{ $p->id }}"
                                            {{ (string) request('position') === (string) $p->id ? 'selected' : '' }}>
                                            {{ $p->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-4 col-sm-6">
                                <select name="status" class="form-select" id="statusFilter">
                                    <option value="">Any Status</option>
                                    @foreach ($statuses as $s)
                                        <option value="{{ $s->id }}"
                                            {{ (string) request('status') === (string) $s->id ? 'selected' : '' }}>
                                            {{ $s->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-12">
                                <div class="d-flex gap-2">
                                    <a href="{{ route('staff.index') }}" class="btn btn-light">Reset</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fas fa-chart-bar"></i> Reports <i class="fas fa-caret-down ms-1"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="{{ route('staff.analysis-by-role') }}">
                                    <i class="fas fa-user-shield me-2"></i> Roles mix
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('staff.analysis-area-of-work') }}">
                                    <i class="fas fa-briefcase me-2"></i> Area of work
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('staff.analysis-department') }}">
                                    <i class="fas fa-sitemap me-2"></i> Departments
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('staff.staff-by-filters') }}">
                                    <i class="fas fa-filter me-2"></i> Staff by filters
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('staff.staff-custom-analysis') }}">
                                    <i class="fas fa-sliders-h me-2"></i> Custom analysis
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li class="dropdown-header">Exports</li>
                            <li>
                                <a class="dropdown-item" href="{{ route('staff.export-list-analysis') }}">
                                    <i class="fas fa-file-export me-2"></i> Export list analysis
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('staff.export-analysis-department') }}">
                                    <i class="fas fa-file-export me-2"></i> Export by department
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('staff.export-analysis-qualifications') }}">
                                    <i class="fas fa-file-export me-2"></i> Export qualifications
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Contact</th>
                            <th>Position</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $index => $user)
                            <tr class="staff-row"
                                data-name="{{ strtolower($user->full_name ?? trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? ''))) }}"
                                data-email="{{ strtolower($user->email ?? '') }}"
                                data-phone="{{ strtolower($user->phone ?? '') }}"
                                data-position="{{ strtolower($user->position ?? '') }}"
                                data-status="{{ strtolower($user->status ?? '') }}"
                                data-gender="{{ strtolower($user->gender ?? '') }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="student-cell">
                                        @if (!empty($user->avatar))
                                            <img src="{{ asset('storage/' . $user->avatar) }}"
                                                alt="{{ $user->full_name ?? ($user->firstname ?? '') . ' ' . ($user->lastname ?? '') }}"
                                                class="student-avatar">
                                        @else
                                            @php
                                                $fi = (string) mb_substr($user->firstname ?? '', 0, 1);
                                                $li = (string) mb_substr($user->lastname ?? '', 0, 1);
                                            @endphp
                                            <div class="student-avatar-placeholder">{{ strtoupper($fi . $li) ?: 'ST' }}
                                            </div>
                                        @endif
                                        <div>
                                            <div>
                                                <a href="{{ route('staff.staff-edit', $user->id) }}">
                                                    {{ $user->full_name ?? trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? '')) }}
                                                </a>
                                            </div>
                                            <div class="text-muted" style="font-size: 12px;">ID:
                                                {{ $user->id_number ?? '—' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>{{ $user->email ?? '—' }}</div>
                                    <div class="text-muted" style="font-size: 12px;">{{ $user->phone ?? '—' }}</div>
                                </td>
                                <td>{{ $user->position ?? '—' }}</td>
                                <td>
                                    <span class="badge bg-info badge-status">{{ $user->status ?? '—' }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="action-buttons">
                                        <a href="{{ route('staff.staff-edit', $user->id) }}"
                                            class="btn btn-sm btn-outline-info" title="View & Edit Staff">
                                            <i class="bx bx-edit-alt"></i>
                                        </a>
                                        @can('staff.edit')
                                            <button type="button" class="btn btn-sm btn-outline-primary" title="Send SMS"
                                                onclick="openSmsModal({{ $user->id }}, '{{ $user->full_name ?? trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? '')) }}', '{{ $user->phone }}')">
                                                <i class="bx bx-message-rounded-dots"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-success" title="Send Email"
                                                onclick="openEmailModal({{ $user->id }}, '{{ $user->full_name ?? trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? '')) }}', '{{ $user->email }}')">
                                                <i class="bx bx-mail-send"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="no-results-row">
                                <td colspan="6">
                                    <div class="text-center text-muted" style="padding: 40px 0;">
                                        <i class="bx bx-user-x" style="font-size: 32px; opacity: 0.3;"></i>
                                        <p class="mt-2 mb-0">No staff found.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pagination-container mt-3"
                style="display: flex; justify-content: space-between; align-items: center;">
                <div class="text-muted" id="results-info">
                    Showing <span id="showing-from">0</span> to <span id="showing-to">0</span> of <span
                        id="total-count">{{ $users->count() }}</span> Users
                </div>
                <nav id="pagination-nav">
                    <!-- Pagination will be inserted here by JavaScript -->
                </nav>
            </div>

        </div>
    </div>

    <!-- SMS Modal -->
    <div class="modal fade" id="smsModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bx bx-message-rounded-dots me-2"></i>Send SMS Message
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="smsForm">
                        @csrf
                        <input type="hidden" id="smsUserId" name="user_id">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Staff Member</label>
                            <div id="smsRecipientName" class="text-muted"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Phone Number</label>
                            <div id="smsRecipientPhone" class="text-muted"></div>
                        </div>

                        <div class="mb-3">
                            <label for="smsMessage" class="form-label fw-bold">Message</label>
                            <textarea class="form-control" id="smsMessage" name="message" rows="5" maxlength="1000"
                                placeholder="Type your SMS message here... Keep it concise and clear." required></textarea>
                            <div class="d-flex justify-content-between mt-2">
                                <small class="text-muted">
                                    <span id="charCount">0</span> / 1000 characters
                                </small>
                                <small class="text-muted fw-bold">
                                    <i class="fas fa-info-circle"></i>
                                    <span id="smsCount">0</span> SMS(s)
                                </small>
                            </div>
                            <small class="text-muted d-block mt-1">
                                <i class="fas fa-lightbulb"></i> Each SMS = 160 characters
                            </small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="sendSms()">
                        <i class="fas fa-paper-plane me-2"></i>Send SMS
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Email Modal -->
    <div class="modal fade" id="emailModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bx bx-mail-send me-2"></i>Send Email Message
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="emailForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="emailUserId" name="user_id">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Staff Member</label>
                            <div id="emailRecipientName" class="text-muted"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Email Address</label>
                            <div id="emailRecipientEmail" class="text-muted"></div>
                        </div>

                        <div class="mb-3">
                            <label for="emailSubject" class="form-label fw-bold">Subject</label>
                            <input type="text" class="form-control" id="emailSubject" name="subject" maxlength="200"
                                placeholder="Enter email subject..." required>
                        </div>

                        <div class="mb-3">
                            <label for="emailMessage" class="form-label fw-bold">Message</label>
                            <textarea class="form-control" id="emailMessage" name="message" rows="8" maxlength="5000"
                                placeholder="Compose your email message here...&#10;&#10;You can write multiple paragraphs and format your message as needed."
                                required></textarea>
                            <small class="text-muted">
                                <span id="emailCharCount">0</span> / 5000 characters
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="emailAttachment" class="form-label fw-bold">
                                Attachment (Optional)
                            </label>
                            <input type="file" class="form-control" id="emailAttachment" name="attachment"
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            <small class="text-muted">
                                Max 10MB. Allowed: PDF, Word, Images
                            </small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="sendEmail()">
                        <i class="fas fa-paper-plane me-2"></i>Send Email
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        // Client-side filtering and pagination
        let currentPage = 1;
        const itemsPerPage = 20;

        function filterAndPaginateStaff(resetPage = true) {
            if (resetPage) currentPage = 1;

            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const positionFilter = document.getElementById('positionFilter').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value.toLowerCase();

            const allRows = document.querySelectorAll('.staff-row');
            let filteredRows = [];
            let currentCount = 0;
            let maleCount = 0;
            let femaleCount = 0;

            // First pass: filter rows and collect stats
            allRows.forEach(row => {
                const name = row.dataset.name || '';
                const email = row.dataset.email || '';
                const phone = row.dataset.phone || '';
                const position = row.dataset.position || '';
                const status = row.dataset.status || '';
                const gender = row.dataset.gender || '';

                // Check if row matches all filters
                const matchesSearch = !searchTerm ||
                    name.includes(searchTerm) ||
                    email.includes(searchTerm) ||
                    phone.includes(searchTerm) ||
                    position.includes(searchTerm);

                const matchesPosition = !positionFilter || position === positionFilter;
                const matchesStatus = !statusFilter || status === statusFilter;

                if (matchesSearch && matchesPosition && matchesStatus) {
                    filteredRows.push(row);

                    // Count for stats
                    if (status === 'current') currentCount++;
                    if (gender === 'm' || gender === 'male') maleCount++;
                    if (gender === 'f' || gender === 'female') femaleCount++;
                }
            });

            // Calculate pagination
            const totalFiltered = filteredRows.length;
            const totalPages = Math.ceil(totalFiltered / itemsPerPage);
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;

            // Second pass: show/hide based on pagination
            allRows.forEach(row => row.style.display = 'none');
            filteredRows.forEach((row, index) => {
                if (index >= startIndex && index < endIndex) {
                    row.style.display = '';
                }
            });

            // Update stats in header
            const statElements = document.querySelectorAll('.stat-item h4');
            if (statElements.length >= 3) {
                statElements[0].textContent = currentCount;
                statElements[1].textContent = maleCount;
                statElements[2].textContent = femaleCount;
            }

            // Update showing info
            const showingFrom = totalFiltered > 0 ? startIndex + 1 : 0;
            const showingTo = Math.min(endIndex, totalFiltered);
            document.getElementById('showing-from').textContent = showingFrom;
            document.getElementById('showing-to').textContent = showingTo;
            document.getElementById('total-count').textContent = totalFiltered;

            // Show/hide no results message
            const noResultsRow = document.getElementById('no-results-row');
            if (noResultsRow) {
                noResultsRow.style.display = totalFiltered === 0 ? '' : 'none';
            }

            // Generate pagination controls
            generatePagination(totalPages, currentPage);
        }

        function generatePagination(totalPages, current) {
            const paginationNav = document.getElementById('pagination-nav');

            if (totalPages <= 1) {
                paginationNav.innerHTML = '';
                return;
            }

            let html = '<ul class="pagination mb-0">';

            // Previous button
            html += `<li class="page-item ${current === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="goToPage(${current - 1}); return false;">Previous</a>
            </li>`;

            // Page numbers
            const maxVisible = 5;
            let startPage = Math.max(1, current - Math.floor(maxVisible / 2));
            let endPage = Math.min(totalPages, startPage + maxVisible - 1);

            if (endPage - startPage < maxVisible - 1) {
                startPage = Math.max(1, endPage - maxVisible + 1);
            }

            if (startPage > 1) {
                html += `<li class="page-item">
                    <a class="page-link" href="#" onclick="goToPage(1); return false;">1</a>
                </li>`;
                if (startPage > 2) {
                    html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
            }

            for (let i = startPage; i <= endPage; i++) {
                html += `<li class="page-item ${i === current ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="goToPage(${i}); return false;">${i}</a>
                </li>`;
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
                html += `<li class="page-item">
                    <a class="page-link" href="#" onclick="goToPage(${totalPages}); return false;">${totalPages}</a>
                </li>`;
            }

            // Next button
            html += `<li class="page-item ${current === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="goToPage(${current + 1}); return false;">Next</a>
            </li>`;

            html += '</ul>';
            paginationNav.innerHTML = html;
        }

        function goToPage(page) {
            currentPage = page;
            filterAndPaginateStaff(false);
        }

        // Real-time search as you type (resets to page 1)
        document.getElementById('searchInput').addEventListener('input', () => filterAndPaginateStaff(true));

        // Filter dropdowns (reset to page 1)
        document.getElementById('positionFilter').addEventListener('change', () => filterAndPaginateStaff(true));
        document.getElementById('statusFilter').addEventListener('change', () => filterAndPaginateStaff(true));

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', () => filterAndPaginateStaff(true));

        // SMS Modal Functions
        function openSmsModal(userId, userName, phone) {
            document.getElementById('smsUserId').value = userId;
            document.getElementById('smsRecipientName').textContent = userName;
            document.getElementById('smsRecipientPhone').textContent = phone || 'No phone number';
            document.getElementById('smsMessage').value = '';
            document.getElementById('charCount').textContent = '0';
            document.getElementById('smsCount').textContent = '0';
            new bootstrap.Modal(document.getElementById('smsModal')).show();
        }

        // Email Modal Functions
        function openEmailModal(userId, userName, email) {
            document.getElementById('emailUserId').value = userId;
            document.getElementById('emailRecipientName').textContent = userName;
            document.getElementById('emailRecipientEmail').textContent = email || 'No email address';
            document.getElementById('emailSubject').value = '';
            document.getElementById('emailMessage').value = '';
            document.getElementById('emailAttachment').value = '';
            document.getElementById('emailCharCount').textContent = '0';
            new bootstrap.Modal(document.getElementById('emailModal')).show();
        }

        // Character counter for SMS
        document.getElementById('smsMessage')?.addEventListener('input', function() {
            const length = this.value.length;
            const smsCount = Math.ceil(length / 160) || 0;
            document.getElementById('charCount').textContent = length;
            document.getElementById('smsCount').textContent = smsCount;
        });

        // Character count for Email
        document.getElementById('emailMessage')?.addEventListener('input', function() {
            document.getElementById('emailCharCount').textContent = this.value.length;
        });

        // Send SMS
        async function sendSms() {
            const userId = document.getElementById('smsUserId').value;
            const message = document.getElementById('smsMessage').value.trim();
            if (!message) {
                alert('Please enter a message');
                return;
            }

            const btn = event.target;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';

            try {
                const url = "{{ route('staff.send-sms', 'tempUserId') }}".replace('tempUserId', userId);
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify({
                        message
                    })
                });

                const data = await response.json();
                if (data.success) {
                    alert(data.message);
                    bootstrap.Modal.getInstance(document.getElementById('smsModal')).hide();
                } else {
                    alert(data.message || 'Failed to send SMS');
                }
            } catch (error) {
                alert('An error occurred. Please try again.');
                console.error(error);
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Send SMS';
            }
        }

        // Send Email
        async function sendEmail() {
            const userId = document.getElementById('emailUserId').value;
            const subject = document.getElementById('emailSubject').value.trim();
            const message = document.getElementById('emailMessage').value.trim();
            const attachment = document.getElementById('emailAttachment').files[0];

            if (!subject || !message) {
                alert('Please fill in subject and message');
                return;
            }

            const formData = new FormData();
            formData.append('_token', document.querySelector('input[name="_token"]').value);
            formData.append('subject', subject);
            formData.append('message', message);
            if (attachment) {
                formData.append('attachment', attachment);
            }

            const btn = event.target;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';

            try {
                const url = "{{ route('staff.send-email', 'tempUserId') }}".replace('tempUserId', userId);
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.message);
                    bootstrap.Modal.getInstance(document.getElementById('emailModal')).hide();
                } else {
                    alert(data.message || 'Failed to send email');
                }
            } catch (error) {
                alert('An error occurred. Please try again.');
                console.error(error);
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Send Email';
            }
        }
    </script>
@endsection
