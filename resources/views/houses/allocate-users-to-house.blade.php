@extends('layouts.master')

@section('title')
    Allocate Users to House
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
            Allocate Users
        @endslot
    @endcomponent

    @include('houses.partials.alerts')

    @php
        $positions = $users->pluck('position')->filter()->unique()->sort()->values();
        $areas = $users->pluck('area_of_work')->filter()->unique()->sort()->values();
        $maleCount = $users->where('gender', 'M')->count();
        $femaleCount = $users->where('gender', 'F')->count();
    @endphp

    <div class="form-container house-page-accent"
        style="--house-color: {{ $house->color_code }}; --house-color-soft: {{ $house->colorWithAlpha(0.14) }};">
        <div class="page-header">
            <div>
                <div class="house-title-row">
                    <span class="house-color-swatch" style="background: {{ $house->color_code }};"></span>
                    <div>
                        <h1 class="page-title">{{ $house->name }} User Allocation</h1>
                        <p class="page-subtitle mb-0">Allocate current staff or users to this house without changing house leadership.</p>
                    </div>
                </div>
            </div>
            <div class="summary-chip-group">
                <span class="summary-chip house-chip"
                    style="--house-color: {{ $house->color_code }}; --house-color-soft: {{ $house->colorWithAlpha(0.14) }};">
                    {{ strtoupper($house->color_code) }}
                </span>
                <span class="summary-chip pill-muted"><i class="fas fa-user-tie"></i> {{ $house->houseAssistant->fullName ?? 'No assistant assigned' }}</span>
            </div>
        </div>

        @include('houses.partials.module-nav', ['current' => 'manager'])

        <div class="help-text">
            <div class="help-title">User Allocation</div>
            <div class="help-content">
                This screen allocates current users as house members. Head and assistant remain separate leadership fields and are not auto-synced from these selections.
            </div>
        </div>

        <div class="house-summary-grid">
            <div class="house-summary-card">
                <div class="house-summary-label">Available Users</div>
                <div class="house-summary-value" id="totalCount">{{ $users->count() }}</div>
                <div class="house-summary-meta">Current users without a house in this term.</div>
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
            <a href="{{ route('house.open-house', ['id' => $house->id]) }}" class="btn btn-light">
                <i class="bx bx-layer"></i> Allocate Students
            </a>
        </div>

        <form method="POST" action="{{ route('house.move-users', $house->id) }}" id="allocateUsersForm">
            @csrf

            <div class="card-shell">
                <div class="card-body p-4">
                    <div class="house-section-header">
                        <div>
                            <h5 class="house-section-title">Available Users</h5>
                            <p class="house-section-subtitle">Filter by name, gender, position, or area of work before allocating in bulk.</p>
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
                        <select class="form-select" id="positionFilter">
                            <option value="">All Positions</option>
                            @foreach ($positions as $position)
                                <option value="{{ strtolower($position) }}">{{ $position }}</option>
                            @endforeach
                        </select>
                        <select class="form-select" id="areaFilter">
                            <option value="">All Areas</option>
                            @foreach ($areas as $area)
                                <option value="{{ strtolower($area) }}">{{ $area }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-light" id="resetFilters">Reset</button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 48px;">
                                        <input type="checkbox" class="form-check-input" id="selectAll">
                                    </th>
                                    <th>User</th>
                                    <th>Gender</th>
                                    <th>Position</th>
                                    <th>Area</th>
                                    <th>Email</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $user)
                                    <tr class="user-row"
                                        data-name="{{ strtolower($user->fullName) }}"
                                        data-gender="{{ strtolower($user->gender ?? '') }}"
                                        data-position="{{ strtolower($user->position ?? '') }}"
                                        data-area="{{ strtolower($user->area_of_work ?? '') }}">
                                        <td>
                                            <input type="checkbox" class="form-check-input user-checkbox" name="users[]"
                                                value="{{ $user->id }}">
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $user->fullName }}</div>
                                            <div class="house-table-meta">{{ $user->phone ?: 'No phone on record' }}</div>
                                        </td>
                                        <td>{{ $user->gender ?? '-' }}</td>
                                        <td>{{ $user->position ?? '-' }}</td>
                                        <td>{{ $user->area_of_work ?? '-' }}</td>
                                        <td>{{ $user->email ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr id="emptyRow">
                                        <td colspan="6">
                                            <div class="empty-state">
                                                <div><i class="fas fa-user-check empty-state-icon"></i></div>
                                                <p class="mb-0">All current users already have house allocations for this term.</p>
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
                            <span id="filtered-count">0</span> users
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
            const positionFilter = document.getElementById('positionFilter').value.toLowerCase();
            const areaFilter = document.getElementById('areaFilter').value.toLowerCase();

            const allRows = document.querySelectorAll('.user-row');
            const filteredRows = [];
            let maleCount = 0;
            let femaleCount = 0;

            allRows.forEach(function(row) {
                const matchesSearch = !searchTerm || row.dataset.name.includes(searchTerm);
                const matchesGender = !genderFilter || row.dataset.gender === genderFilter;
                const matchesPosition = !positionFilter || row.dataset.position === positionFilter;
                const matchesArea = !areaFilter || row.dataset.area === areaFilter;

                if (matchesSearch && matchesGender && matchesPosition && matchesArea) {
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
            document.getElementById('positionFilter').value = '';
            document.getElementById('areaFilter').value = '';
            filterAndPaginate(true);
        }

        document.addEventListener('DOMContentLoaded', function() {
            filterAndPaginate(true);

            document.getElementById('searchInput').addEventListener('input', () => filterAndPaginate(true));
            document.getElementById('genderFilter').addEventListener('change', () => filterAndPaginate(true));
            document.getElementById('positionFilter').addEventListener('change', () => filterAndPaginate(true));
            document.getElementById('areaFilter').addEventListener('change', () => filterAndPaginate(true));
            document.getElementById('resetFilters').addEventListener('click', resetFilters);

            document.getElementById('selectAll').addEventListener('change', function() {
                document.querySelectorAll('.user-row:not([style*="display: none"]) .user-checkbox').forEach(function(checkbox) {
                    checkbox.checked = document.getElementById('selectAll').checked;
                });
            });

            document.getElementById('allocateUsersForm').addEventListener('submit', function(event) {
                if (!document.querySelectorAll('.user-checkbox:checked').length) {
                    event.preventDefault();
                    alert('Select at least one user to allocate.');
                }
            });
        });
    </script>
@endsection
