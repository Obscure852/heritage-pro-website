@extends('layouts.master')
@section('title')
    Communications Module
@endsection
@section('css')
    <style>
        .admissions-container {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .admissions-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .admissions-header h4 {
            margin: 0;
            font-weight: 600;
            font-size: 20px;
        }

        .admissions-header p {
            margin: 8px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
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

        .admissions-body {
            padding: 24px;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px 16px;
            border-left: 4px solid #4e73df;
            border-radius: 0 3px 3px 0;
            margin-bottom: 20px;
            font-size: 13px;
            color: #555;
        }

        .term-filter-wrapper {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 12px;
        }

        .term-filter-wrapper .form-select {
            width: auto;
            min-width: 180px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
            padding: 8px 12px;
        }

        .term-filter-wrapper .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            padding: 8px 16px;
            font-weight: 500;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .dropdown-menu {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 8px 0;
        }

        .dropdown-item {
            padding: 10px 16px;
            font-size: 14px;
            transition: all 0.15s ease;
        }

        .dropdown-item:hover {
            background: #f3f4f6;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .section-title span {
            font-weight: 400;
            color: #6b7280;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: #f8f9fa;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            font-size: 13px;
            color: #374151;
            padding: 12px 10px;
        }

        .table tbody td {
            padding: 12px 10px;
            vertical-align: middle;
            font-size: 14px;
            border-bottom: 1px solid #e5e7eb;
        }

        .table tbody tr:hover {
            background: #f9fafb;
        }

        /* Skeleton Loader Styles */
        .skeleton {
            animation: skeleton-loading 1s linear infinite alternate;
            border-radius: 4px;
        }

        .skeleton-text {
            width: 100%;
            height: 14px;
            margin-bottom: 0;
            background-color: #e5e7eb;
        }

        .skeleton-text.skeleton-sm {
            width: 40px;
        }

        .skeleton-text.skeleton-md {
            width: 60%;
        }

        .skeleton-text.skeleton-lg {
            width: 80%;
        }

        .skeleton-badge {
            width: 80px;
            height: 20px;
            display: inline-block;
            margin-left: 8px;
            background-color: #e5e7eb;
        }

        .skeleton-button {
            width: 32px;
            height: 28px;
            display: inline-block;
            margin-right: 4px;
            background-color: #e5e7eb;
        }

        @keyframes skeleton-loading {
            0% {
                background-color: #e5e7eb;
            }
            100% {
                background-color: #f3f4f6;
            }
        }

        /* Shimmer effect */
        .skeleton-shimmer {
            position: relative;
            overflow: hidden;
        }

        .skeleton-shimmer::after {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            transform: translateX(-100%);
            background-image: linear-gradient(90deg,
                    rgba(255, 255, 255, 0) 0,
                    rgba(255, 255, 255, 0.2) 20%,
                    rgba(255, 255, 255, 0.5) 60%,
                    rgba(255, 255, 255, 0));
            animation: shimmer 2s infinite;
            content: '';
        }

        @keyframes shimmer {
            100% {
                transform: translateX(100%);
            }
        }

        .controls .form-control,
        .controls .form-select {
            font-size: 0.9rem;
        }

        .dataTables_length select {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.5rem center;
            background-size: 1em;
            padding-right: 2.5rem;
        }

        .alert {
            border-radius: 3px;
            border: none;
        }

        @media (max-width: 768px) {
            .stat-item h4 {
                font-size: 1.25rem;
            }

            .stat-item small {
                font-size: 0.75rem;
            }

            .admissions-header {
                padding: 20px;
            }

            .admissions-body {
                padding: 16px;
            }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Communications
        @endslot
        @slot('title')
            Notifications
        @endslot
    @endcomponent

    @if (session('message'))
        <div class="alert alert-success alert-border-left alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-all me-3 align-middle"></i><strong>{{ session('message') }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="term-filter-wrapper">
        <select name="term" id="termId" class="form-select">
            @if (!empty($terms))
                @foreach ($terms as $term)
                    <option data-year="{{ $term->year }}"
                        value="{{ $term->id }}"{{ $term->id == session('selected_term_id', $currentTerm->id) ? 'selected' : '' }}>
                        {{ 'Term ' . $term->term . ', ' . $term->year }}</option>
                @endforeach
            @endif
        </select>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="admissions-container">
                <div class="admissions-header">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h4>Notifications</h4>
                            <p>Manage and send notifications to staff and parents/sponsors</p>
                        </div>
                        <div class="col-md-6">
                            @if (!empty($notifications))
                                @php
                                    $totalCount = $notifications->count();
                                    $withAttachments = $notifications->filter(fn($n) => $n->attachments->isNotEmpty())->count();
                                    $withComments = $notifications->filter(fn($n) => $n->notificationComments->count() > 0)->count();
                                @endphp
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="stat-item">
                                            <h4 class="mb-0 fw-bold text-white">{{ $totalCount }}</h4>
                                            <small class="opacity-75">Total</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="stat-item">
                                            <h4 class="mb-0 fw-bold text-white">{{ $withAttachments }}</h4>
                                            <small class="opacity-75">Attachments</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="stat-item">
                                            <h4 class="mb-0 fw-bold text-white">{{ $withComments }}</h4>
                                            <small class="opacity-75">Comments</small>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="admissions-body">
                    <div class="help-text">
                        <i class="fas fa-info-circle me-2"></i>
                        Create and manage notifications for staff members and parents/sponsors. Track notification status, view details, and manage responses.
                    </div>

                    <div class="row align-items-center mb-3">
                        <div class="col-lg-8 col-md-12">
                            <div class="controls">
                                <div class="row g-2 align-items-center">
                                    <div class="col-lg-4 col-md-4 col-sm-6">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                            <input type="text" class="form-control" placeholder="Search by title..." id="searchInput">
                                        </div>
                                    </div>
                                    <div class="col-lg-2 col-md-3 col-sm-6">
                                        <select class="form-select" id="generalFilter">
                                            <option value="">All Type</option>
                                            <option value="yes">General</option>
                                            <option value="no">Targeted</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-2 col-md-3 col-sm-6">
                                        <select class="form-select" id="attachmentFilter">
                                            <option value="">Attachments</option>
                                            <option value="yes">Has Attachments</option>
                                            <option value="no">No Attachments</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-2 col-md-3 col-sm-6">
                                        <select class="form-select" id="commentFilter">
                                            <option value="">Comments</option>
                                            <option value="yes">Has Comments</option>
                                            <option value="no">No Comments</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-2 col-md-12">
                                        <button type="button" class="btn btn-light w-100" id="resetFilters">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                            <div class="d-flex flex-wrap align-items-center justify-content-end gap-2">
                                @if (!session('is_past_term'))
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-plus me-1"></i> Create Notification
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end mt-1">
                                            <a class="dropdown-item" href="{{ route('notifications.staff-create') }}">
                                                <i class="fas fa-users me-2" style="color: #4287f5;"></i> For Staff
                                            </a>
                                            <a class="dropdown-item" href="{{ route('notifications.sponsors-create') }}">
                                                <i class="fas fa-user-friends me-2" style="color: #6a5acd;"></i> For Parents/Sponsors
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div id="notifications-term">
                        <!-- Table Placeholder - This will be replaced when data loads -->
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th scope="col" style="width: 50px;">
                                            <div class="skeleton skeleton-text skeleton-sm skeleton-shimmer"></div>
                                        </th>
                                        <th scope="col">
                                            <div class="skeleton skeleton-text skeleton-md skeleton-shimmer"></div>
                                        </th>
                                        <th scope="col" style="width: 100px;">
                                            <div class="skeleton skeleton-text skeleton-md skeleton-shimmer"></div>
                                        </th>
                                        <th scope="col" style="width: 120px;">
                                            <div class="skeleton skeleton-text skeleton-md skeleton-shimmer"></div>
                                        </th>
                                        <th scope="col" style="width: 120px;">
                                            <div class="skeleton skeleton-text skeleton-md skeleton-shimmer"></div>
                                        </th>
                                        @if (!session('is_past_term'))
                                            <th style="width: 80px; min-width: 80px;">
                                                <div class="skeleton skeleton-text skeleton-md skeleton-shimmer"></div>
                                            </th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @for ($i = 1; $i <= 3; $i++)
                                        <tr>
                                            <td>
                                                <div class="skeleton skeleton-text skeleton-sm skeleton-shimmer"></div>
                                            </td>
                                            <td>
                                                <div class="skeleton skeleton-text skeleton-lg skeleton-shimmer"></div>
                                                <div class="skeleton skeleton-badge mt-2 skeleton-shimmer"></div>
                                            </td>
                                            <td>
                                                <div class="skeleton skeleton-text skeleton-md skeleton-shimmer"></div>
                                            </td>
                                            <td>
                                                <div class="skeleton skeleton-text skeleton-md skeleton-shimmer"></div>
                                            </td>
                                            <td>
                                                <div class="skeleton skeleton-text skeleton-md skeleton-shimmer"></div>
                                            </td>
                                            @if (!session('is_past_term'))
                                                <td>
                                                    <div class="d-flex gap-1">
                                                        <div class="skeleton skeleton-button skeleton-shimmer"></div>
                                                        <div class="skeleton skeleton-button skeleton-shimmer"></div>
                                                    </div>
                                                </td>
                                            @endif
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>
                        <!-- Loading Spinner Alternative (hidden by default, can be used instead of skeleton) -->
                        <div class="text-center py-5 d-none" id="loading-spinner">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div class="mt-3">
                                <h5 class="text-muted">Loading Notifications...</h5>
                                <p class="text-muted mb-0">Please wait while we fetch your notifications</p>
                            </div>
                        </div>
                    </div>
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

        $(document).ready(function() {
            $('#termId').change(function() {
                var term = $(this).val();
                var studentsSessionUrl = "{{ route('students.term-session') }}";

                // Show the skeleton loader when term changes
                showSkeletonLoader();

                $.ajax({
                    url: studentsSessionUrl,
                    method: 'POST',
                    data: {
                        term_id: term,
                        _token: '{{ csrf_token() }}'
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", xhr.status, xhr.statusText);
                    },
                    success: function() {
                        fetchTermNotifications();
                    }
                });
            });

            function showSkeletonLoader() {
                var skeletonHTML = `
                    <div class="table-responsive mb-4">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th>General</th>
                                    <th>Comments</th>
                                    <th>Attachments</th>
                                    @if (!session('is_past_term'))
                                        <th class="text-end">Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                `;

                for (let i = 1; i <= 5; i++) {
                    skeletonHTML += `
                        <tr>
                            <td><div class="skeleton skeleton-text skeleton-sm skeleton-shimmer"></div></td>
                            <td><div class="skeleton skeleton-text skeleton-lg skeleton-shimmer"></div></td>
                            <td><div class="skeleton skeleton-text skeleton-md skeleton-shimmer"></div></td>
                            <td><div class="skeleton skeleton-text skeleton-md skeleton-shimmer"></div></td>
                            <td><div class="skeleton skeleton-text skeleton-md skeleton-shimmer"></div></td>
                            @if (!session('is_past_term'))
                                <td><div class="d-flex gap-1 justify-content-end">
                                    <div class="skeleton skeleton-button skeleton-shimmer"></div>
                                    <div class="skeleton skeleton-button skeleton-shimmer"></div>
                                    <div class="skeleton skeleton-button skeleton-shimmer"></div>
                                </div></td>
                            @endif
                        </tr>
                    `;
                }

                skeletonHTML += `</tbody></table></div>`;
                $('#notifications-term').html(skeletonHTML);
            }

            function fetchTermNotifications() {
                var notificationsByTermUrl = "{{ route('notifications.get-getNotifications') }}";
                $.ajax({
                    url: notificationsByTermUrl,
                    method: 'GET',
                    success: function(response) {
                        $('#notifications-term').html(response);
                        // Initialize filtering after content loads
                        initializeFiltering();
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching term data:", xhr.status, xhr.statusText);
                        $('#notifications-term').html(`
                            <div class="alert alert-danger" role="alert">
                                <i class="mdi mdi-alert-circle-outline me-2"></i>
                                Failed to load notifications. Please try again.
                                <button type="button" class="btn btn-sm btn-link" onclick="$('#termId').trigger('change')">
                                    Retry
                                </button>
                            </div>
                        `);
                    }
                });
            }

            // Trigger initial load
            $('#termId').trigger('change');

            // Filter event listeners
            $('#searchInput').on('input', () => filterAndPaginateNotifications(true));
            $('#generalFilter').on('change', () => filterAndPaginateNotifications(true));
            $('#attachmentFilter').on('change', () => filterAndPaginateNotifications(true));
            $('#commentFilter').on('change', () => filterAndPaginateNotifications(true));
            $('#resetFilters').on('click', resetFilters);
        });

        function initializeFiltering() {
            currentPage = 1;
            filterAndPaginateNotifications(true);
        }

        function filterAndPaginateNotifications(resetPage = true) {
            if (resetPage) currentPage = 1;

            const searchTerm = document.getElementById('searchInput')?.value.toLowerCase() || '';
            const generalFilter = document.getElementById('generalFilter')?.value.toLowerCase() || '';
            const attachmentFilter = document.getElementById('attachmentFilter')?.value.toLowerCase() || '';
            const commentFilter = document.getElementById('commentFilter')?.value.toLowerCase() || '';

            const allRows = document.querySelectorAll('.notification-row');
            let filteredRows = [];
            let totalCount = 0;
            let withAttachments = 0;
            let withComments = 0;

            allRows.forEach(row => {
                const title = row.dataset.title || '';
                const general = row.dataset.general || '';
                const attachments = row.dataset.attachments || '';
                const comments = row.dataset.comments || '';

                const matchesSearch = !searchTerm || title.includes(searchTerm);
                const matchesGeneral = !generalFilter || general === generalFilter;
                const matchesAttachment = !attachmentFilter || attachments === attachmentFilter;
                const matchesComment = !commentFilter || comments === commentFilter;

                if (matchesSearch && matchesGeneral && matchesAttachment && matchesComment) {
                    filteredRows.push(row);
                    totalCount++;
                    if (attachments === 'yes') withAttachments++;
                    if (comments === 'yes') withComments++;
                }
            });

            // Calculate pagination
            const totalFiltered = filteredRows.length;
            const totalPages = Math.ceil(totalFiltered / itemsPerPage);
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;

            // Show/hide based on pagination
            allRows.forEach(row => row.style.display = 'none');
            filteredRows.forEach((row, index) => {
                if (index >= startIndex && index < endIndex) {
                    row.style.display = '';
                }
            });

            // Update stats in header
            const statElements = document.querySelectorAll('.stat-item h4');
            if (statElements.length >= 3) {
                statElements[0].textContent = totalCount;
                statElements[1].textContent = withAttachments;
                statElements[2].textContent = withComments;
            }

            // Update showing info
            const showingFrom = totalFiltered > 0 ? startIndex + 1 : 0;
            const showingTo = Math.min(endIndex, totalFiltered);
            const showingFromEl = document.getElementById('showing-from');
            const showingToEl = document.getElementById('showing-to');
            const totalCountEl = document.getElementById('total-count');

            if (showingFromEl) showingFromEl.textContent = showingFrom;
            if (showingToEl) showingToEl.textContent = showingTo;
            if (totalCountEl) totalCountEl.textContent = totalFiltered;

            // Generate pagination controls
            generatePagination(totalPages, currentPage);
        }

        function generatePagination(totalPages, current) {
            const paginationNav = document.getElementById('pagination-nav');
            if (!paginationNav) return;

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
                html += `<li class="page-item"><a class="page-link" href="#" onclick="goToPage(1); return false;">1</a></li>`;
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
                html += `<li class="page-item"><a class="page-link" href="#" onclick="goToPage(${totalPages}); return false;">${totalPages}</a></li>`;
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
            filterAndPaginateNotifications(false);
        }

        function resetFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('generalFilter').value = '';
            document.getElementById('attachmentFilter').value = '';
            document.getElementById('commentFilter').value = '';
            filterAndPaginateNotifications(true);
        }

        function confirmDeleteNotification() {
            return confirm('Are you sure you want to delete this notification? This action cannot be undone.');
        }
    </script>
@endsection
