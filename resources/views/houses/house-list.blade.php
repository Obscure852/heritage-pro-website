@extends('layouts.master')

@section('title')
    Allocate Students to House
@endsection

@section('css')
    @include('houses.partials.theme')
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="{{ route('house.index') }}">Houses</a>
        @endslot
        @slot('title')
            Allocate Students
        @endslot
    @endcomponent

    @include('houses.partials.alerts')

    @php
        $grades = $students->map(fn($student) => $student->currentGrade?->name)->filter()->unique()->sort()->values();
        $classes = $students->map(fn($student) => $student->class?->name)->filter()->unique()->sort()->values();
        $maleCount = $students->where('gender', 'M')->count();
        $femaleCount = $students->where('gender', 'F')->count();
    @endphp

    <div class="form-container house-page-accent"
        style="--house-color: {{ $house->color_code }}; --house-color-soft: {{ $house->colorWithAlpha(0.14) }};">
        <div class="page-header">
            <div>
                <div class="house-title-row">
                    <span class="house-color-swatch" style="background: {{ $house->color_code }};"></span>
                    <div>
                        <h1 class="page-title">{{ $house->name }} Student Allocation</h1>
                        <p class="page-subtitle mb-0">
                            Add unallocated students to this house for the selected term.
                        </p>
                    </div>
                </div>
            </div>
            <div class="summary-chip-group">
                <span class="summary-chip house-chip"
                    style="--house-color: {{ $house->color_code }}; --house-color-soft: {{ $house->colorWithAlpha(0.14) }};">
                    {{ strtoupper($house->color_code) }}
                </span>
                <span class="summary-chip pill-muted"><i class="fas fa-user-shield"></i> {{ $house->houseHead->fullName ?? 'No head assigned' }}</span>
            </div>
        </div>

        @include('houses.partials.module-nav', ['current' => 'manager'])

        <div class="help-text">
            <div class="help-title">Student Allocation</div>
            <div class="help-content">
                Only students with no current house allocation are shown here. Use the filters to narrow the list, then allocate in bulk.
            </div>
        </div>

        <div class="house-summary-grid">
            <div class="house-summary-card">
                <div class="house-summary-label">Unallocated Students</div>
                <div class="house-summary-value" id="totalCount">{{ $students->count() }}</div>
                <div class="house-summary-meta">Ready for allocation to {{ $house->name }}.</div>
            </div>
            <div class="house-summary-card">
                <div class="house-summary-label">Male</div>
                <div class="house-summary-value" id="maleCount">{{ $maleCount }}</div>
                <div class="house-summary-meta">Filtered count updates live.</div>
            </div>
            <div class="house-summary-card">
                <div class="house-summary-label">Female</div>
                <div class="house-summary-value" id="femaleCount">{{ $femaleCount }}</div>
                <div class="house-summary-meta">Filtered count updates live.</div>
            </div>
        </div>

        <div class="activities-actions mb-4">
            <a href="{{ route('house.house-view', ['houseId' => $house->id]) }}" class="btn btn-light">
                <i class="bx bx-show"></i> View House Members
            </a>
            <a href="{{ route('house.open-house-users', ['id' => $house->id]) }}" class="btn btn-light">
                <i class="bx bx-user-plus"></i> Allocate Users
            </a>
        </div>

        <form method="POST" action="{{ route('house.move-students', $house->id) }}" id="allocateStudentsForm">
            @csrf

            <div class="card-shell">
                <div class="card-body p-4">
                    <div class="house-section-header">
                        <div>
                            <h5 class="house-section-title">Available Students</h5>
                            <p class="house-section-subtitle">Filter by name, gender, grade, or class before selecting rows.</p>
                        </div>
                        @if (!session('is_past_term'))
                            <button type="submit" class="btn btn-primary btn-loading">
                                <span class="btn-text"><i class="fas fa-user-plus me-1"></i> Allocate Selected</span>
                                <span class="btn-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Allocating...
                                </span>
                            </button>
                        @endif
                    </div>

                    <div class="house-filter-grid">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" placeholder="Search by name..." id="searchInput">
                        </div>
                        <select class="form-select" id="genderFilter">
                            <option value="">All Gender</option>
                            <option value="m">Male</option>
                            <option value="f">Female</option>
                        </select>
                        <select class="form-select" id="gradeFilter">
                            <option value="">All Grades</option>
                            @foreach ($grades as $grade)
                                <option value="{{ strtolower($grade) }}">{{ $grade }}</option>
                            @endforeach
                        </select>
                        <select class="form-select" id="classFilter">
                            <option value="">All Classes</option>
                            @foreach ($classes as $class)
                                <option value="{{ strtolower($class) }}">{{ $class }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-light" id="resetFilters">Reset</button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0" id="studentsTable">
                            <thead>
                                <tr>
                                    <th style="width: 48px;">
                                        <input type="checkbox" class="form-check-input" id="selectAll">
                                    </th>
                                    <th>Student</th>
                                    <th>Gender</th>
                                    <th>ID Number</th>
                                    <th>Class</th>
                                    <th>Grade</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($students as $student)
                                    <tr class="student-row"
                                        data-name="{{ strtolower($student->fullName) }}"
                                        data-gender="{{ strtolower($student->gender) }}"
                                        data-grade="{{ strtolower($student->currentGrade?->name ?? '') }}"
                                        data-class="{{ strtolower($student->class?->name ?? '') }}">
                                        <td>
                                            <input type="checkbox" class="form-check-input student-checkbox" name="students[]"
                                                value="{{ $student->id }}">
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $student->fullName }}</div>
                                            <div class="house-table-meta">{{ $student->email ?: 'No email on record' }}</div>
                                        </td>
                                        <td>{{ $student->gender ?? '-' }}</td>
                                        <td>{{ $student->formatted_id_number ?? $student->id_number ?? '-' }}</td>
                                        <td>{{ $student->class?->name ?? '-' }}</td>
                                        <td>{{ $student->currentGrade?->name ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr id="emptyRow">
                                        <td colspan="6">
                                            <div class="empty-state">
                                                <div><i class="fas fa-check-circle empty-state-icon"></i></div>
                                                <p class="mb-0">All eligible students are already allocated to houses.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="pagination-info">
                            Showing <span id="showing-from">0</span> to <span id="showing-to">0</span> of
                            <span id="filtered-count">0</span> students
                        </div>
                        <nav id="paginationContainer">
                            <ul class="pagination pagination-rounded mb-0"></ul>
                        </nav>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('script')
    @include('houses.partials.form-script')
    <script>
        let currentPage = 1;
        const itemsPerPage = 20;

        function visibleRowsSelector() {
            return '.student-row:not([style*="display: none"]) .student-checkbox';
        }

        function createPageItem(page, current) {
            const li = document.createElement('li');
            li.className = `page-item ${page === current ? 'active' : ''}`;
            li.innerHTML = `<a class="page-link" href="#" onclick="goToPage(${page}); return false;">${page}</a>`;
            return li;
        }

        function generatePagination(totalPages, current) {
            const container = document.querySelector('#paginationContainer .pagination');
            container.innerHTML = '';

            if (totalPages <= 1) {
                return;
            }

            const prevLi = document.createElement('li');
            prevLi.className = `page-item ${current === 1 ? 'disabled' : ''}`;
            prevLi.innerHTML = `<a class="page-link" href="#" onclick="goToPage(${current - 1}); return false;"><i class="fas fa-chevron-left"></i></a>`;
            container.appendChild(prevLi);

            const startPage = Math.max(1, current - 2);
            const endPage = Math.min(totalPages, current + 2);

            for (let page = startPage; page <= endPage; page++) {
                container.appendChild(createPageItem(page, current));
            }

            const nextLi = document.createElement('li');
            nextLi.className = `page-item ${current === totalPages ? 'disabled' : ''}`;
            nextLi.innerHTML = `<a class="page-link" href="#" onclick="goToPage(${current + 1}); return false;"><i class="fas fa-chevron-right"></i></a>`;
            container.appendChild(nextLi);
        }

        function filterAndPaginate(resetPage = true) {
            if (resetPage) {
                currentPage = 1;
            }

            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const genderFilter = document.getElementById('genderFilter').value.toLowerCase();
            const gradeFilter = document.getElementById('gradeFilter').value.toLowerCase();
            const classFilter = document.getElementById('classFilter').value.toLowerCase();

            const allRows = document.querySelectorAll('.student-row');
            const filteredRows = [];
            let maleCount = 0;
            let femaleCount = 0;

            allRows.forEach(function(row) {
                const matchesSearch = !searchTerm || row.dataset.name.includes(searchTerm);
                const matchesGender = !genderFilter || row.dataset.gender === genderFilter;
                const matchesGrade = !gradeFilter || row.dataset.grade === gradeFilter;
                const matchesClass = !classFilter || row.dataset.class === classFilter;

                if (matchesSearch && matchesGender && matchesGrade && matchesClass) {
                    filteredRows.push(row);
                    if (row.dataset.gender === 'm') maleCount++;
                    if (row.dataset.gender === 'f') femaleCount++;
                }
            });

            const totalFiltered = filteredRows.length;
            const totalPages = Math.ceil(totalFiltered / itemsPerPage) || 1;
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;

            allRows.forEach(row => row.style.display = 'none');
            filteredRows.forEach(function(row, index) {
                if (index >= startIndex && index < endIndex) {
                    row.style.display = '';
                }
            });

            document.getElementById('totalCount').textContent = totalFiltered;
            document.getElementById('maleCount').textContent = maleCount;
            document.getElementById('femaleCount').textContent = femaleCount;
            document.getElementById('showing-from').textContent = totalFiltered ? startIndex + 1 : 0;
            document.getElementById('showing-to').textContent = Math.min(endIndex, totalFiltered);
            document.getElementById('filtered-count').textContent = totalFiltered;

            const emptyRow = document.getElementById('emptyRow');
            if (emptyRow) {
                emptyRow.style.display = totalFiltered === 0 ? '' : 'none';
            }

            document.getElementById('selectAll').checked = false;
            generatePagination(totalPages, currentPage);
        }

        function goToPage(page) {
            currentPage = page;
            filterAndPaginate(false);
        }

        function resetFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('genderFilter').value = '';
            document.getElementById('gradeFilter').value = '';
            document.getElementById('classFilter').value = '';
            filterAndPaginate(true);
        }

        document.addEventListener('DOMContentLoaded', function() {
            filterAndPaginate(true);

            document.getElementById('searchInput').addEventListener('input', () => filterAndPaginate(true));
            document.getElementById('genderFilter').addEventListener('change', () => filterAndPaginate(true));
            document.getElementById('gradeFilter').addEventListener('change', () => filterAndPaginate(true));
            document.getElementById('classFilter').addEventListener('change', () => filterAndPaginate(true));
            document.getElementById('resetFilters').addEventListener('click', resetFilters);

            document.getElementById('selectAll').addEventListener('change', function() {
                document.querySelectorAll(visibleRowsSelector()).forEach(function(checkbox) {
                    checkbox.checked = document.getElementById('selectAll').checked;
                });
            });

            document.getElementById('allocateStudentsForm').addEventListener('submit', function(event) {
                if (!document.querySelectorAll('.student-checkbox:checked').length) {
                    event.preventDefault();
                    alert('Select at least one student to allocate.');
                }
            });
        });
    </script>
@endsection
