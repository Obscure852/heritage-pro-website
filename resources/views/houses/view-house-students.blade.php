@extends('layouts.master')

@section('title')
    House Members
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
            {{ $house->name }}
        @endslot
    @endcomponent

    @include('houses.partials.alerts')

    @php
        $studentGrades = $house->students->map(fn($student) => $student->class?->grade?->name)->filter()->unique()->sort()->values();
        $studentClasses = $house->students->map(fn($student) => $student->class?->name)->filter()->unique()->sort()->values();
        $userPositions = $house->users->pluck('position')->filter()->unique()->sort()->values();
        $userAreas = $house->users->pluck('area_of_work')->filter()->unique()->sort()->values();
        $canManageMemberships = auth()->user()?->can('manage-houses') && !session('is_past_term');
        $studentTableColspan = $canManageMemberships ? 6 : 4;
        $userTableColspan = $canManageMemberships ? 6 : 4;
    @endphp

    <div class="form-container house-page-accent printable"
        style="--house-color: {{ $house->color_code }}; --house-color-soft: {{ $house->colorWithAlpha(0.14) }};">
        <div class="page-header">
            <div>
                <div class="house-title-row">
                    <span class="house-color-swatch" style="background: {{ $house->color_code }};"></span>
                    <div>
                        <h1 class="page-title">{{ $house->name }}</h1>
                        <p class="page-subtitle mb-0">
                            House head: {{ $house->houseHead->fullName ?? 'Not assigned' }}.
                            Assistant: {{ $house->houseAssistant->fullName ?? 'Not assigned' }}.
                        </p>
                    </div>
                </div>
            </div>
            <div class="summary-chip-group">
                <span class="summary-chip house-chip"
                    style="--house-color: {{ $house->color_code }}; --house-color-soft: {{ $house->colorWithAlpha(0.14) }};">
                    {{ strtoupper($house->color_code) }}
                </span>
                <span class="summary-chip pill-muted"><i class="fas fa-user-graduate"></i> {{ $house->students_count }} students</span>
                <span class="summary-chip pill-muted"><i class="fas fa-user-tie"></i> {{ $house->users_count }} users</span>
            </div>
        </div>

        @include('houses.partials.module-nav', ['current' => 'manager'])

        <div class="help-text">
            <div class="help-title">House Membership</div>
            <div class="help-content">
                Review both student and user allocations for this house. Leadership remains separate from user membership, and removals only affect the selected term.
            </div>
        </div>

        <div class="house-summary-grid">
            <div class="house-summary-card">
                <div class="house-summary-label">Students</div>
                <div class="house-summary-value" id="studentTotalCount">{{ $house->students->count() }}</div>
                <div class="house-summary-meta">Current student members in this house.</div>
            </div>
            <div class="house-summary-card">
                <div class="house-summary-label">Users</div>
                <div class="house-summary-value" id="userTotalCount">{{ $house->users->count() }}</div>
                <div class="house-summary-meta">Current users allocated independently of leadership.</div>
            </div>
            <div class="house-summary-card">
                <div class="house-summary-label">Active Classes</div>
                <div class="house-summary-value">{{ $studentClasses->count() }}</div>
                <div class="house-summary-meta">Classes represented by current members.</div>
            </div>
        </div>

        <div class="activities-actions mb-4">
            @if (!session('is_past_term'))
                <a href="{{ route('house.open-house', ['id' => $house->id]) }}" class="btn btn-light">
                    <i class="bx bx-layer"></i> Allocate Students
                </a>
                <a href="{{ route('house.open-house-users', ['id' => $house->id]) }}" class="btn btn-light">
                    <i class="bx bx-user-plus"></i> Allocate Users
                </a>
            @endif
            <button type="button" class="btn btn-light" onclick="window.print()">
                <i class="bx bx-printer"></i> Print
            </button>
        </div>

        <div class="section-stack">
            <div class="card-shell">
                <div class="card-body p-4">
                    <div class="house-section-header">
                        <div>
                            <h5 class="house-section-title">Student Members</h5>
                            <p class="house-section-subtitle">Filter, review, and remove student allocations from this house.</p>
                        </div>
                        @can('manage-houses')
                            @if (!session('is_past_term'))
                                <button type="submit" form="deleteStudentsForm" class="btn btn-danger" id="deleteSelectedStudentsBtn" style="display: none;">
                                    <i class="fas fa-trash me-1"></i> Remove Selected
                                </button>
                            @endif
                        @endcan
                    </div>

                    <div class="house-filter-grid">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" placeholder="Search students..." id="studentSearchInput">
                        </div>
                        <select class="form-select" id="studentGenderFilter">
                            <option value="">All Gender</option>
                            <option value="m">Male</option>
                            <option value="f">Female</option>
                        </select>
                        <select class="form-select" id="studentGradeFilter">
                            <option value="">All Grades</option>
                            @foreach ($studentGrades as $grade)
                                <option value="{{ strtolower($grade) }}">{{ $grade }}</option>
                            @endforeach
                        </select>
                        <select class="form-select" id="studentClassFilter">
                            <option value="">All Classes</option>
                            @foreach ($studentClasses as $class)
                                <option value="{{ strtolower($class) }}">{{ $class }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-light" id="resetStudentFilters">Reset</button>
                    </div>

                    <form action="{{ route('house.delete-multiple-students', $house->id) }}" method="POST" id="deleteStudentsForm">
                        @csrf
                        @method('DELETE')

                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0">
                                <thead>
                                    <tr>
                                        @can('manage-houses')
                                            @if (!session('is_past_term'))
                                                <th style="width: 48px;">
                                                    <input type="checkbox" class="form-check-input" id="selectAllStudents">
                                                </th>
                                            @endif
                                        @endcan
                                        <th>Student</th>
                                        <th>Gender</th>
                                        <th>Class</th>
                                        <th>Grade</th>
                                        @can('manage-houses')
                                            @if (!session('is_past_term'))
                                                <th class="text-end" style="width: 120px;">Action</th>
                                            @endif
                                        @endcan
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($house->students as $student)
                                        <tr class="student-row"
                                            data-name="{{ strtolower($student->fullName) }}"
                                            data-gender="{{ strtolower($student->gender) }}"
                                            data-grade="{{ strtolower($student->class?->grade?->name ?? '') }}"
                                            data-class="{{ strtolower($student->class?->name ?? '') }}">
                                            @can('manage-houses')
                                                @if (!session('is_past_term'))
                                                    <td>
                                                        <input type="checkbox" class="form-check-input student-checkbox" name="students[]"
                                                            value="{{ $student->id }}">
                                                    </td>
                                                @endif
                                            @endcan
                                            <td>
                                                <div class="fw-semibold">{{ $student->fullName }}</div>
                                                <div class="house-table-meta">{{ $student->id_number ?? 'No ID number' }}</div>
                                            </td>
                                            <td>{{ $student->gender ?? '-' }}</td>
                                            <td>{{ $student->class?->name ?? '-' }}</td>
                                            <td>{{ $student->class?->grade?->name ?? '-' }}</td>
                                            @can('manage-houses')
                                                @if (!session('is_past_term'))
                                                    <td class="text-end">
                                                        <form method="POST" action="{{ route('house.delete-student', [$house->id, $student->id]) }}"
                                                            class="house-inline-form">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger btn-sm"
                                                                onclick="return confirm('Remove {{ addslashes($student->fullName) }} from {{ addslashes($house->name) }}?')">
                                                                <i class="fas fa-user-minus me-1"></i> Remove
                                                            </button>
                                                        </form>
                                                    </td>
                                                @endif
                                            @endcan
                                        </tr>
                                    @empty
                                        <tr id="emptyStudentRow">
                                            <td colspan="{{ $studentTableColspan }}">
                                                <div class="empty-state">
                                                    <div><i class="fas fa-user-graduate empty-state-icon"></i></div>
                                                    <p class="mb-0">No students are allocated to this house.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </form>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="pagination-info">
                            Showing <span id="student-showing-from">0</span> to <span id="student-showing-to">0</span> of
                            <span id="student-filtered-count">0</span> students
                        </div>
                        <nav id="studentPaginationContainer">
                            <ul class="pagination pagination-rounded mb-0"></ul>
                        </nav>
                    </div>
                </div>
            </div>

            <div class="card-shell">
                <div class="card-body p-4">
                    <div class="house-section-header">
                        <div>
                            <h5 class="house-section-title">User Members</h5>
                            <p class="house-section-subtitle">Manage general user allocations separately from head and assistant assignments.</p>
                        </div>
                        @can('manage-houses')
                            @if (!session('is_past_term'))
                                <button type="submit" form="deleteUsersForm" class="btn btn-danger" id="deleteSelectedUsersBtn" style="display: none;">
                                    <i class="fas fa-trash me-1"></i> Remove Selected
                                </button>
                            @endif
                        @endcan
                    </div>

                    <div class="house-filter-grid">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" placeholder="Search users..." id="userSearchInput">
                        </div>
                        <select class="form-select" id="userGenderFilter">
                            <option value="">All Gender</option>
                            <option value="m">Male</option>
                            <option value="f">Female</option>
                        </select>
                        <select class="form-select" id="userPositionFilter">
                            <option value="">All Positions</option>
                            @foreach ($userPositions as $position)
                                <option value="{{ strtolower($position) }}">{{ $position }}</option>
                            @endforeach
                        </select>
                        <select class="form-select" id="userAreaFilter">
                            <option value="">All Areas</option>
                            @foreach ($userAreas as $area)
                                <option value="{{ strtolower($area) }}">{{ $area }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-light" id="resetUserFilters">Reset</button>
                    </div>

                    <form action="{{ route('house.delete-multiple-users', $house->id) }}" method="POST" id="deleteUsersForm">
                        @csrf
                        @method('DELETE')

                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0">
                                <thead>
                                    <tr>
                                        @can('manage-houses')
                                            @if (!session('is_past_term'))
                                                <th style="width: 48px;">
                                                    <input type="checkbox" class="form-check-input" id="selectAllUsers">
                                                </th>
                                            @endif
                                        @endcan
                                        <th>User</th>
                                        <th>Gender</th>
                                        <th>Position</th>
                                        <th>Area</th>
                                        @can('manage-houses')
                                            @if (!session('is_past_term'))
                                                <th class="text-end" style="width: 120px;">Action</th>
                                            @endif
                                        @endcan
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($house->users as $user)
                                        <tr class="user-row"
                                            data-name="{{ strtolower($user->fullName) }}"
                                            data-gender="{{ strtolower($user->gender ?? '') }}"
                                            data-position="{{ strtolower($user->position ?? '') }}"
                                            data-area="{{ strtolower($user->area_of_work ?? '') }}">
                                            @can('manage-houses')
                                                @if (!session('is_past_term'))
                                                    <td>
                                                        <input type="checkbox" class="form-check-input user-checkbox" name="users[]"
                                                            value="{{ $user->id }}">
                                                    </td>
                                                @endif
                                            @endcan
                                            <td>
                                                <div class="fw-semibold">{{ $user->fullName }}</div>
                                                <div class="house-table-meta">{{ $user->email ?: 'No email on record' }}</div>
                                            </td>
                                            <td>{{ $user->gender ?? '-' }}</td>
                                            <td>{{ $user->position ?? '-' }}</td>
                                            <td>{{ $user->area_of_work ?? '-' }}</td>
                                            @can('manage-houses')
                                                @if (!session('is_past_term'))
                                                    <td class="text-end">
                                                        <form method="POST" action="{{ route('house.delete-user', [$house->id, $user->id]) }}"
                                                            class="house-inline-form">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger btn-sm"
                                                                onclick="return confirm('Remove {{ addslashes($user->fullName) }} from {{ addslashes($house->name) }}?')">
                                                                <i class="fas fa-user-minus me-1"></i> Remove
                                                            </button>
                                                        </form>
                                                    </td>
                                                @endif
                                            @endcan
                                        </tr>
                                    @empty
                                        <tr id="emptyUserRow">
                                            <td colspan="{{ $userTableColspan }}">
                                                <div class="empty-state">
                                                    <div><i class="fas fa-user-tie empty-state-icon"></i></div>
                                                    <p class="mb-0">No users are allocated to this house.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </form>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="pagination-info">
                            Showing <span id="user-showing-from">0</span> to <span id="user-showing-to">0</span> of
                            <span id="user-filtered-count">0</span> users
                        </div>
                        <nav id="userPaginationContainer">
                            <ul class="pagination pagination-rounded mb-0"></ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        function createPaginationItem(page, current, callbackName) {
            const li = document.createElement('li');
            li.className = `page-item ${page === current ? 'active' : ''}`;
            li.innerHTML = `<a class="page-link" href="#" onclick="${callbackName}(${page}); return false;">${page}</a>`;
            return li;
        }

        function buildSectionController(config) {
            const state = {
                page: 1,
                itemsPerPage: 10,
            };

            function updateBulkButton() {
                if (!config.bulkButton) {
                    return;
                }

                const checkedCount = document.querySelectorAll(`${config.checkboxSelector}:checked`).length;
                config.bulkButton.style.display = checkedCount > 0 ? '' : 'none';
                config.bulkButton.innerHTML = `<i class="fas fa-trash me-1"></i> Remove Selected (${checkedCount})`;
            }

            function renderPagination(totalPages) {
                if (!config.paginationContainer) {
                    return;
                }

                config.paginationContainer.innerHTML = '';

                if (totalPages <= 1) {
                    return;
                }

                const prevLi = document.createElement('li');
                prevLi.className = `page-item ${state.page === 1 ? 'disabled' : ''}`;
                prevLi.innerHTML = `<a class="page-link" href="#"><i class="fas fa-chevron-left"></i></a>`;
                prevLi.addEventListener('click', function(event) {
                    event.preventDefault();
                    if (state.page > 1) {
                        state.page--;
                        filter(false);
                    }
                });
                config.paginationContainer.appendChild(prevLi);

                const startPage = Math.max(1, state.page - 2);
                const endPage = Math.min(totalPages, state.page + 2);

                for (let page = startPage; page <= endPage; page++) {
                    const item = createPaginationItem(page, state.page, 'void');
                    item.querySelector('a').addEventListener('click', function(event) {
                        event.preventDefault();
                        state.page = page;
                        filter(false);
                    });
                    config.paginationContainer.appendChild(item);
                }

                const nextLi = document.createElement('li');
                nextLi.className = `page-item ${state.page === totalPages ? 'disabled' : ''}`;
                nextLi.innerHTML = `<a class="page-link" href="#"><i class="fas fa-chevron-right"></i></a>`;
                nextLi.addEventListener('click', function(event) {
                    event.preventDefault();
                    if (state.page < totalPages) {
                        state.page++;
                        filter(false);
                    }
                });
                config.paginationContainer.appendChild(nextLi);
            }

            function filter(resetPage = true) {
                if (resetPage) {
                    state.page = 1;
                }

                const search = config.searchInput ? config.searchInput.value.toLowerCase() : '';
                const primary = config.primaryFilter ? config.primaryFilter.value.toLowerCase() : '';
                const secondary = config.secondaryFilter ? config.secondaryFilter.value.toLowerCase() : '';
                const tertiary = config.tertiaryFilter ? config.tertiaryFilter.value.toLowerCase() : '';

                const rows = Array.from(document.querySelectorAll(config.rowSelector));
                const filteredRows = rows.filter(function(row) {
                    const matchesSearch = !search || (row.dataset.name || '').includes(search);
                    const matchesPrimary = !primary || (row.dataset[config.primaryKey] || '') === primary;
                    const matchesSecondary = !secondary || (row.dataset[config.secondaryKey] || '') === secondary;
                    const matchesTertiary = !tertiary || (row.dataset[config.tertiaryKey] || '') === tertiary;

                    return matchesSearch && matchesPrimary && matchesSecondary && matchesTertiary;
                });

                const totalFiltered = filteredRows.length;
                const totalPages = Math.ceil(totalFiltered / state.itemsPerPage) || 1;
                const startIndex = (state.page - 1) * state.itemsPerPage;
                const endIndex = startIndex + state.itemsPerPage;

                rows.forEach(row => row.style.display = 'none');
                filteredRows.forEach(function(row, index) {
                    if (index >= startIndex && index < endIndex) {
                        row.style.display = '';
                    }
                });

                if (config.totalCounter) config.totalCounter.textContent = totalFiltered;
                if (config.showingFrom) config.showingFrom.textContent = totalFiltered ? startIndex + 1 : 0;
                if (config.showingTo) config.showingTo.textContent = Math.min(endIndex, totalFiltered);
                if (config.filteredCounter) config.filteredCounter.textContent = totalFiltered;
                if (config.emptyRow) config.emptyRow.style.display = totalFiltered === 0 ? '' : 'none';
                if (config.selectAll) config.selectAll.checked = false;

                renderPagination(totalPages);
                updateBulkButton();
            }

            function resetFilters() {
                if (config.searchInput) config.searchInput.value = '';
                if (config.primaryFilter) config.primaryFilter.value = '';
                if (config.secondaryFilter) config.secondaryFilter.value = '';
                if (config.tertiaryFilter) config.tertiaryFilter.value = '';
                filter(true);
            }

            if (config.searchInput) config.searchInput.addEventListener('input', () => filter(true));
            if (config.primaryFilter) config.primaryFilter.addEventListener('change', () => filter(true));
            if (config.secondaryFilter) config.secondaryFilter.addEventListener('change', () => filter(true));
            if (config.tertiaryFilter) config.tertiaryFilter.addEventListener('change', () => filter(true));
            if (config.resetButton) config.resetButton.addEventListener('click', resetFilters);

            if (config.selectAll) {
                config.selectAll.addEventListener('change', function() {
                    document.querySelectorAll(`${config.rowSelector}:not([style*="display: none"]) ${config.checkboxSelector}`)
                        .forEach(function(checkbox) {
                            checkbox.checked = config.selectAll.checked;
                        });
                    updateBulkButton();
                });
            }

            document.querySelectorAll(config.checkboxSelector).forEach(function(checkbox) {
                checkbox.addEventListener('change', updateBulkButton);
            });

            if (config.form) {
                config.form.addEventListener('submit', function(event) {
                    if (!document.querySelectorAll(`${config.checkboxSelector}:checked`).length) {
                        event.preventDefault();
                        alert(config.emptySelectionMessage);
                    }
                });
            }

            filter(true);
        }

        document.addEventListener('DOMContentLoaded', function() {
            buildSectionController({
                rowSelector: '.student-row',
                checkboxSelector: '.student-checkbox',
                searchInput: document.getElementById('studentSearchInput'),
                primaryFilter: document.getElementById('studentGenderFilter'),
                secondaryFilter: document.getElementById('studentGradeFilter'),
                tertiaryFilter: document.getElementById('studentClassFilter'),
                primaryKey: 'gender',
                secondaryKey: 'grade',
                tertiaryKey: 'class',
                totalCounter: document.getElementById('studentTotalCount'),
                filteredCounter: document.getElementById('student-filtered-count'),
                showingFrom: document.getElementById('student-showing-from'),
                showingTo: document.getElementById('student-showing-to'),
                resetButton: document.getElementById('resetStudentFilters'),
                selectAll: document.getElementById('selectAllStudents'),
                bulkButton: document.getElementById('deleteSelectedStudentsBtn'),
                paginationContainer: document.querySelector('#studentPaginationContainer .pagination'),
                emptyRow: document.getElementById('emptyStudentRow'),
                form: document.getElementById('deleteStudentsForm'),
                emptySelectionMessage: 'Select at least one student to remove.'
            });

            buildSectionController({
                rowSelector: '.user-row',
                checkboxSelector: '.user-checkbox',
                searchInput: document.getElementById('userSearchInput'),
                primaryFilter: document.getElementById('userGenderFilter'),
                secondaryFilter: document.getElementById('userPositionFilter'),
                tertiaryFilter: document.getElementById('userAreaFilter'),
                primaryKey: 'gender',
                secondaryKey: 'position',
                tertiaryKey: 'area',
                totalCounter: document.getElementById('userTotalCount'),
                filteredCounter: document.getElementById('user-filtered-count'),
                showingFrom: document.getElementById('user-showing-from'),
                showingTo: document.getElementById('user-showing-to'),
                resetButton: document.getElementById('resetUserFilters'),
                selectAll: document.getElementById('selectAllUsers'),
                bulkButton: document.getElementById('deleteSelectedUsersBtn'),
                paginationContainer: document.querySelector('#userPaginationContainer .pagination'),
                emptyRow: document.getElementById('emptyUserRow'),
                form: document.getElementById('deleteUsersForm'),
                emptySelectionMessage: 'Select at least one user to remove.'
            });
        });
    </script>
@endsection
