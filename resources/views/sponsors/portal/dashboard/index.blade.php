@extends('layouts.master-sponsor-portal')
@section('title')
    Dashboard - Sponsor Portal
@endsection

@section('css')
    @include('sponsors.portal.partials.sponsor-portal-styles')
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Sponsor Portal
        @endslot
        @slot('title')
            Dashboard
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

    @if (session('error'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <!-- Term Selector -->
    <div class="mb-3 text-end">
        <select name="term" id="termId" class="form-select d-inline-block" style="width: 200px;">
            @if (!empty($terms))
                @foreach ($terms as $term)
                    <option data-year="{{ $term->year }}" value="{{ $term->id }}"
                        {{ $term->id == session('selected_term_id', $currentTerm->id) ? 'selected' : '' }}>
                        Term {{ $term->term }}, {{ $term->year }}
                    </option>
                @endforeach
            @endif
        </select>
    </div>

    <!-- Page Container -->
    <div class="sponsor-container">
        <!-- Page Header -->
        <div class="sponsor-header">
            <h3>Dashboard</h3>
            <p>Monitor your children's academic progress and school activities</p>
        </div>

        <div class="sponsor-body">
            <!-- Dashboard Content (AJAX Loaded) -->
            <div id="ChildrenTermList">
                <div class="loading-container">
                    <div class="loading-spinner mx-auto"></div>
                    <p>Loading dashboard...</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            $('#termId').change(function() {
                var term = $(this).val();
                var studentTermUrl = "{{ route('sponsor.term-session') }}";

                // Show loading state
                $('#ChildrenTermList').html(`
                <div class="loading-container">
                    <div class="loading-spinner mx-auto"></div>
                    <p>Loading dashboard...</p>
                </div>
            `);

                $.ajax({
                    url: studentTermUrl,
                    method: 'POST',
                    data: {
                        term_id: term,
                        _token: '{{ csrf_token() }}'
                    },
                    error: function(xhr, status, error) {
                        console.error("Response:", xhr.responseText);
                        $('#ChildrenTermList').html(`
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="bx bx-error-circle"></i>
                            </div>
                            <h5>Connection Error</h5>
                            <p>Unable to load dashboard data. Please try again.</p>
                        </div>
                    `);
                    },
                    success: function() {
                        fetchTermData();
                    }
                });
            });

            function fetchTermData() {
                var termDataUrl = "{{ route('sponsor.dashboard-term') }}";
                $.ajax({
                    url: termDataUrl,
                    method: 'GET',
                    success: function(response) {
                        $('#ChildrenTermList').html(response);
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching term data:", xhr.status, xhr.statusText);
                        $('#ChildrenTermList').html(`
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="bx bx-error-circle"></i>
                            </div>
                            <h5>Error Loading Data</h5>
                            <p>Unable to fetch dashboard data. Please refresh and try again.</p>
                        </div>
                    `);
                    }
                });
            }

            // Initial load
            $('#termId').trigger('change');
        });
    </script>
@endsection
