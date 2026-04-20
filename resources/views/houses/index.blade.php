@extends('layouts.master')

@section('title')
    House Module
@endsection

@section('css')
    @include('houses.partials.theme')
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Houses
        @endslot
        @slot('title')
            Houses Manager
        @endslot
    @endcomponent

    @include('houses.partials.alerts')

    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-end">
            <select name="term" id="termId" class="form-select term-select">
                @foreach ($terms as $term)
                    <option value="{{ $term->id }}"
                        {{ $term->id == session('selected_term_id', $currentTerm->id) ? 'selected' : '' }}>
                        {{ 'Term ' . $term->term . ', ' . $term->year }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="houses-container">
        <div class="houses-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 class="mb-1 text-white">Houses Manager</h3>
                    <p class="mb-0 opacity-75">
                        Review house structure for the selected term, manage leadership, and coordinate student and staff allocations.
                    </p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white" id="totalHousesCount">0</h4>
                                <small class="opacity-75">Houses</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white" id="totalStudentsCount">0</h4>
                                <small class="opacity-75">Students</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white" id="totalUsersCount">0</h4>
                                <small class="opacity-75">Users</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="houses-body">
            <div class="help-text">
                <div class="help-title">Module Overview</div>
                <div class="help-content">
                    Keep house records aligned to the selected term, track both student and staff membership, and use the report links for distribution summaries.
                </div>
            </div>

            @include('houses.partials.module-nav', ['current' => 'manager'])

            @can('manage-houses')
                @if (!session('is_past_term'))
                    <div class="d-flex flex-wrap align-items-center justify-content-end gap-2 mb-3">
                        <a href="{{ route('house.show') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> New House
                        </a>
                    </div>
                @endif
            @endcan

            <div id="HouseTermList">
                <div id="housesPlaceholder" class="card-shell">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;">#</th>
                                        <th>House</th>
                                        <th>Leadership</th>
                                        <th>Counts</th>
                                        <th>Classes</th>
                                        <th class="text-end" style="width: 220px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @for ($i = 0; $i < 3; $i++)
                                        <tr class="skeleton-row">
                                            <td>
                                                <span class="skeleton skeleton-text skeleton-sm" style="width: 18px;"></span>
                                            </td>
                                            <td>
                                                <div class="house-table-name">
                                                    <span class="skeleton skeleton-swatch"></span>
                                                    <div class="house-table-name-copy">
                                                        <span class="skeleton skeleton-text" style="width: 120px;"></span>
                                                        <div class="activity-meta-pills">
                                                            <span class="skeleton skeleton-chip" style="width: 72px;"></span>
                                                            <span class="skeleton skeleton-chip" style="width: 58px;"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="skeleton skeleton-text" style="width: 140px;"></span>
                                                <span class="skeleton skeleton-text skeleton-sm mt-1" style="width: 180px;"></span>
                                            </td>
                                            <td>
                                                <span class="skeleton skeleton-text skeleton-sm" style="width: 80px;"></span>
                                                <span class="skeleton skeleton-text skeleton-sm mt-1" style="width: 72px;"></span>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-2">
                                                    <span class="skeleton skeleton-chip" style="width: 44px;"></span>
                                                    <span class="skeleton skeleton-chip" style="width: 44px;"></span>
                                                    <span class="skeleton skeleton-chip" style="width: 44px;"></span>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <div class="d-flex justify-content-end gap-1">
                                                    <span class="skeleton skeleton-btn"></span>
                                                    <span class="skeleton skeleton-btn"></span>
                                                    <span class="skeleton skeleton-btn"></span>
                                                    <span class="skeleton skeleton-btn"></span>
                                                </div>
                                            </td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        function showPlaceholder() {
            const placeholder = document.getElementById('housesPlaceholder');
            if (placeholder) {
                placeholder.style.display = '';
            }
        }

        function hidePlaceholder() {
            const placeholder = document.getElementById('housesPlaceholder');
            if (placeholder) {
                placeholder.style.display = 'none';
            }
        }

        function updateStats() {
            const summary = document.getElementById('housesTableSummary');

            document.getElementById('totalHousesCount').textContent = summary?.dataset.houseCount ?? '0';
            document.getElementById('totalStudentsCount').textContent = summary?.dataset.studentCount ?? '0';
            document.getElementById('totalUsersCount').textContent = summary?.dataset.userCount ?? '0';
        }

        function initializeTable() {
            if (typeof $ === 'undefined' || !$.fn.DataTable || !document.getElementById('houses')) {
                return;
            }

            if ($.fn.DataTable.isDataTable('#houses')) {
                $('#houses').DataTable().destroy();
            }

            $('#houses').DataTable({
                searching: false,
                lengthChange: false,
                ordering: false,
                pageLength: 10,
                language: {
                    paginate: {
                        previous: "<i class='fas fa-chevron-left'></i>",
                        next: "<i class='fas fa-chevron-right'></i>"
                    }
                },
                drawCallback: function() {
                    $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
                }
            });
        }

        function fetchTermData() {
            $.ajax({
                url: "{{ route('houses.get-term-data') }}",
                method: 'GET',
                success: function(response) {
                    $('#HouseTermList').html(response);
                    hidePlaceholder();
                    updateStats();
                    initializeTable();
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    hidePlaceholder();
                }
            });
        }

        function confirmDeleteHouse() {
            return confirm('Delete this house? This action cannot be undone.');
        }

        $(document).ready(function() {
            fetchTermData();

            $('#termId').on('change', function() {
                showPlaceholder();

                $.ajax({
                    url: "{{ route('students.term-session') }}",
                    method: 'POST',
                    data: {
                        term_id: $(this).val(),
                        _token: '{{ csrf_token() }}'
                    },
                    success: function() {
                        fetchTermData();
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        hidePlaceholder();
                    }
                });
            });
        });
    </script>
@endsection
