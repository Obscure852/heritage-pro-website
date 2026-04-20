@extends('layouts.master')
@section('title')
    Library Catalog
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

        /* New Arrivals Section */
        .new-arrivals-section {
            margin-bottom: 24px;
        }

        .new-arrivals-section h6 {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 12px;
        }

        .new-arrivals-scroll {
            display: flex;
            gap: 16px;
            overflow-x: auto;
            padding-bottom: 8px;
        }

        .new-arrivals-scroll::-webkit-scrollbar {
            height: 6px;
        }

        .new-arrivals-scroll::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .new-arrivals-scroll::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        .new-arrival-card {
            min-width: 140px;
            max-width: 140px;
            text-decoration: none;
            color: inherit;
            transition: transform 0.2s ease;
        }

        .new-arrival-card:hover {
            transform: translateY(-2px);
            color: inherit;
        }

        .new-arrival-cover {
            width: 140px;
            height: 180px;
            border-radius: 3px;
            overflow: hidden;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
        }

        .new-arrival-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .new-arrival-cover .placeholder-icon {
            font-size: 36px;
            color: #9ca3af;
        }

        .new-arrival-card .book-title {
            font-size: 13px;
            font-weight: 600;
            color: #1f2937;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .new-arrival-card .book-author {
            font-size: 12px;
            color: #6b7280;
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Availability Badge */
        .availability-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            white-space: nowrap;
        }

        .availability-available {
            background: #d1fae5;
            color: #065f46;
        }

        .availability-none {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Book Cover Thumbnail */
        .book-cover-thumb {
            width: 40px;
            height: 52px;
            border-radius: 3px;
            overflow: hidden;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .book-cover-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .book-cover-thumb .placeholder-icon {
            font-size: 18px;
            color: #9ca3af;
        }

        .book-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Action Buttons */
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

        /* Controls Row */
        .controls .form-control,
        .controls .form-select {
            font-size: 0.9rem;
        }

        /* Table Styling */
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

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        @media (max-width: 768px) {
            .stat-item h4 {
                font-size: 1.25rem;
            }

            .stat-item small {
                font-size: 0.75rem;
            }

            .library-header {
                padding: 20px;
            }

            .library-body {
                padding: 16px;
            }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('library.dashboard') }}">Library</a>
        @endslot
        @slot('title')
            Catalog
        @endslot
    @endcomponent

    @if (session('message'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <div class="library-container">
        <div class="library-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;"><i class="bx bx-book me-2"></i>Library Catalog</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Browse and search the library collection</p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['total_books'] }}</h4>
                                <small class="opacity-75">Total Items</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['available_copies'] }}</h4>
                                <small class="opacity-75">Available Copies</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['checked_out_copies'] }}</h4>
                                <small class="opacity-75">Checked Out</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="library-body">
            <div class="help-text">
                <div class="help-title">Library Catalog</div>
                <div class="help-content">
                    Search by title, author, ISBN, Dewey Decimal, or keyword. Use filters to narrow results by category, grade level, language, item type, or reading level.
                </div>
            </div>

            {{-- New Arrivals Section --}}
            @if ($newArrivals->isNotEmpty())
                <div class="new-arrivals-section">
                    <h6><i class="fas fa-star text-warning me-1"></i> New Arrivals</h6>
                    <div class="new-arrivals-scroll">
                        @foreach ($newArrivals as $arrival)
                            <a href="{{ route('library.catalog.show', $arrival->id) }}" class="new-arrival-card">
                                <div class="new-arrival-cover">
                                    @if ($arrival->cover_image_url)
                                        <img src="{{ $arrival->cover_image_url }}" alt="{{ $arrival->title }}">
                                    @else
                                        <i class="fas fa-book placeholder-icon"></i>
                                    @endif
                                </div>
                                <div class="book-title">{{ $arrival->title }}</div>
                                <div class="book-author">{{ $arrival->authors_list }}</div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Controls Row --}}
            <div class="row align-items-center mb-3">
                <div class="col-12">
                    <div class="controls">
                        <div class="row g-2 align-items-center">
                            <div class="col-lg-3 col-md-4 col-sm-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" placeholder="Search by title, author, ISBN..." id="searchInput">
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-3 col-sm-6">
                                <select class="form-select" id="categoryFilter">
                                    <option value="">All Categories</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ strtolower($category) }}">{{ $category }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-3 col-sm-6">
                                <select class="form-select" id="gradeFilter">
                                    <option value="">All Grades</option>
                                    @foreach ($grades as $grade)
                                        <option value="{{ strtolower($grade->name) }}">{{ $grade->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-1 col-md-2 col-sm-6">
                                <select class="form-select" id="languageFilter">
                                    <option value="">All Languages</option>
                                    @foreach ($languages as $language)
                                        <option value="{{ strtolower($language) }}">{{ $language }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-1 col-md-2 col-sm-6">
                                <select class="form-select" id="itemTypeFilter">
                                    <option value="">All Types</option>
                                    @foreach ($itemTypes as $type)
                                        <option value="{{ strtolower($type) }}">{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-6">
                                <select class="form-select" id="readingLevelFilter">
                                    <option value="">All Reading Levels</option>
                                    @foreach ($readingLevels as $level)
                                        <option value="{{ strtolower($level) }}">{{ $level }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-1 col-md-2 col-sm-6">
                                <button type="button" class="btn btn-light w-100" id="resetFilters">Reset</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Data Table --}}
            <div class="table-responsive">
                <table id="catalogTable" class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th style="width:60px;">Cover</th>
                            <th>Title</th>
                            <th>Author(s)</th>
                            <th>ISBN</th>
                            <th>Category</th>
                            <th>Grade</th>
                            <th>Availability</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($books as $book)
                            <tr class="book-row"
                                data-title="{{ strtolower($book->title) }}"
                                data-author="{{ strtolower($book->authors_list) }}"
                                data-isbn="{{ strtolower($book->isbn ?? '') }}"
                                data-dewey="{{ strtolower($book->dewey_decimal ?? '') }}"
                                data-keywords="{{ strtolower($book->keywords ?? '') }}"
                                data-genre="{{ strtolower($book->genre ?? '') }}"
                                data-grade="{{ strtolower($book->grade->name ?? '') }}"
                                data-language="{{ strtolower($book->language ?? '') }}"
                                data-format="{{ strtolower($book->format ?? '') }}"
                                data-reading-level="{{ strtolower($book->reading_level ?? '') }}">
                                <td>
                                    <div class="book-cover-thumb">
                                        @if ($book->cover_image_url)
                                            <img src="{{ $book->cover_image_url }}" alt="{{ $book->title }}">
                                        @else
                                            <i class="fas fa-book placeholder-icon"></i>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('library.catalog.show', $book->id) }}" class="fw-semibold text-decoration-none">
                                        {{ $book->title }}
                                    </a>
                                    @if ($book->dewey_decimal)
                                        <div class="text-muted" style="font-size: 12px;">{{ $book->dewey_decimal }}</div>
                                    @endif
                                </td>
                                <td>{{ $book->authors_list }}</td>
                                <td>{{ $book->isbn }}</td>
                                <td>{{ $book->genre ?? '-' }}</td>
                                <td>{{ $book->grade->name ?? '-' }}</td>
                                <td>
                                    @if ($book->available_copies_count > 0)
                                        <span class="availability-badge availability-available">
                                            {{ $book->available_copies_count }} available / {{ $book->copies_count }} total
                                        </span>
                                    @else
                                        <span class="availability-badge availability-none">
                                            0 available / {{ $book->copies_count }} total
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="action-buttons">
                                        <a href="{{ route('library.catalog.show', $book->id) }}"
                                            class="btn btn-sm btn-outline-info"
                                            title="View Details">
                                            <i class="bx bx-show"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="no-books-row">
                                <td colspan="8">
                                    <div class="text-center text-muted" style="padding: 40px 0;">
                                        <i class="fas fa-book" style="font-size: 48px; opacity: 0.3;"></i>
                                        <p class="mt-3 mb-0" style="font-size: 15px;">No items found</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Empty State for Filtered Results --}}
            <div id="no-results" class="text-center text-muted" style="padding: 40px 0; display: none;">
                <i class="fas fa-search" style="font-size: 48px; opacity: 0.3;"></i>
                <p class="mt-3 mb-0" style="font-size: 15px;">No items match your filters</p>
            </div>

            @if ($books->count() > 0)
                <div class="pagination-container mt-3" style="display: flex; justify-content: space-between; align-items: center;">
                    <div class="text-muted" id="results-info">
                        Showing <span id="showing-from">0</span> to <span id="showing-to">0</span> of <span id="total-count">{{ count($books) }}</span> entries
                    </div>
                    <nav id="pagination-nav">
                        <!-- Pagination will be inserted here by JavaScript -->
                    </nav>
                </div>
            @endif
        </div>
    </div>
@endsection
@section('script')
    <script>
        // Client-side filtering and pagination
        let currentPage = 1;
        const itemsPerPage = 15;

        function filterAndPaginateBooks(resetPage = true) {
            if (resetPage) currentPage = 1;

            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const categoryFilter = document.getElementById('categoryFilter').value.toLowerCase();
            const gradeFilter = document.getElementById('gradeFilter').value.toLowerCase();
            const languageFilter = document.getElementById('languageFilter').value.toLowerCase();
            const itemTypeFilter = document.getElementById('itemTypeFilter').value.toLowerCase();
            const readingLevelFilter = document.getElementById('readingLevelFilter').value.toLowerCase();

            const allRows = document.querySelectorAll('.book-row');
            let filteredRows = [];

            // First pass: filter rows
            allRows.forEach(row => {
                const title = row.dataset.title || '';
                const author = row.dataset.author || '';
                const isbn = row.dataset.isbn || '';
                const dewey = row.dataset.dewey || '';
                const keywords = row.dataset.keywords || '';
                const category = row.dataset.genre || '';
                const grade = row.dataset.grade || '';
                const language = row.dataset.language || '';
                const itemType = row.dataset.format || '';
                const readingLevel = row.dataset.readingLevel || '';

                // Text search across multiple fields
                const matchesSearch = !searchTerm ||
                    title.includes(searchTerm) ||
                    author.includes(searchTerm) ||
                    isbn.includes(searchTerm) ||
                    dewey.includes(searchTerm) ||
                    keywords.includes(searchTerm);

                const matchesCategory = !categoryFilter || category === categoryFilter;
                const matchesGrade = !gradeFilter || grade === gradeFilter;
                const matchesLanguage = !languageFilter || language === languageFilter;
                const matchesItemType = !itemTypeFilter || itemType === itemTypeFilter;
                const matchesReadingLevel = !readingLevelFilter || readingLevel === readingLevelFilter;

                if (matchesSearch && matchesCategory && matchesGrade && matchesLanguage && matchesItemType && matchesReadingLevel) {
                    filteredRows.push(row);
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
            if (statElements.length >= 1) {
                statElements[0].textContent = totalFiltered;
            }

            // Update showing info
            const showingFrom = totalFiltered > 0 ? startIndex + 1 : 0;
            const showingTo = Math.min(endIndex, totalFiltered);
            const fromEl = document.getElementById('showing-from');
            const toEl = document.getElementById('showing-to');
            const totalEl = document.getElementById('total-count');
            if (fromEl) fromEl.textContent = showingFrom;
            if (toEl) toEl.textContent = showingTo;
            if (totalEl) totalEl.textContent = totalFiltered;

            // Show/hide no results
            const noResults = document.getElementById('no-results');
            const tableEl = document.getElementById('catalogTable');
            if (totalFiltered === 0 && allRows.length > 0) {
                noResults.style.display = 'block';
                tableEl.style.display = 'none';
            } else {
                noResults.style.display = 'none';
                tableEl.style.display = '';
            }

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
            filterAndPaginateBooks(false);
        }

        function resetFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('categoryFilter').value = '';
            document.getElementById('gradeFilter').value = '';
            document.getElementById('languageFilter').value = '';
            document.getElementById('itemTypeFilter').value = '';
            document.getElementById('readingLevelFilter').value = '';
            filterAndPaginateBooks(true);
        }

        // Real-time search as you type
        document.getElementById('searchInput').addEventListener('input', () => filterAndPaginateBooks(true));

        // Filter dropdowns
        document.getElementById('categoryFilter').addEventListener('change', () => filterAndPaginateBooks(true));
        document.getElementById('gradeFilter').addEventListener('change', () => filterAndPaginateBooks(true));
        document.getElementById('languageFilter').addEventListener('change', () => filterAndPaginateBooks(true));
        document.getElementById('itemTypeFilter').addEventListener('change', () => filterAndPaginateBooks(true));
        document.getElementById('readingLevelFilter').addEventListener('change', () => filterAndPaginateBooks(true));

        // Reset button
        document.getElementById('resetFilters').addEventListener('click', resetFilters);

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', () => filterAndPaginateBooks(true));
    </script>
@endsection
