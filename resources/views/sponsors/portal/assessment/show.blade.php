@extends('layouts.master-sponsor-portal')
@section('title')
    {{ $student->full_name }} - Assessment - Sponsor Portal
@endsection

@section('css')
@include('sponsors.portal.partials.sponsor-portal-styles')
<style>
    .student-assessment-header {
        background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        border-radius: 0;
        padding: 20px 24px;
        color: white;
    }

    .student-avatar-assessment {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        border: 3px solid rgba(255,255,255,0.3);
        overflow: hidden;
        background: rgba(255,255,255,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        font-weight: 600;
        color: white;
    }

    .student-avatar-assessment img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
</style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Sponsor Portal
        @endslot
        @slot('li_2')
            Assessment
        @endslot
        @slot('title')
            {{ $student->full_name }}
        @endslot
    @endcomponent

    @php
        $nameParts = explode(' ', $student->full_name);
        $initials = '';
        foreach (array_slice($nameParts, 0, 2) as $part) {
            $initials .= strtoupper(substr($part, 0, 1));
        }
        $currentClass = $student->currentClassRelation ? $student->currentClassRelation->first() : null;
    @endphp

    <!-- Term Selector -->
    <div class="mb-3 text-end">
        <select name="term" id="termId" class="form-select d-inline-block" style="width: 200px;">
            @foreach ($terms as $term)
                <option data-year="{{ $term->year }}" value="{{ $term->id }}"
                    {{ $term->id == session('selected_term_id', $currentTerm->id) ? 'selected' : '' }}>
                    Term {{ $term->term }}, {{ $term->year }}
                </option>
            @endforeach
        </select>
    </div>

    <!-- Page Container -->
    <div class="sponsor-container">
        <!-- Student Header -->
        <div class="student-assessment-header">
            <div class="d-flex align-items-center gap-3">
                <div class="student-avatar-assessment">
                    @if($student->photo_path)
                        <img src="{{ asset($student->photo_path) }}" alt="{{ $student->full_name }}">
                    @else
                        {{ $initials }}
                    @endif
                </div>
                <div>
                    <h5 class="mb-1">{{ $student->full_name }}</h5>
                    <p class="mb-0" style="opacity: 0.9; font-size: 14px;">
                        <i class="bx bx-buildings me-1"></i>
                        {{ $currentClass ? $currentClass->name : 'Class not assigned' }}
                        @if($currentClass && $currentClass->grade)
                            <span class="mx-2">|</span>
                            {{ $currentClass->grade->name }}
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <div class="sponsor-body">
            <!-- Help Text -->
            <div class="help-text">
                <div class="help-title">Assessment Overview</div>
                <div class="help-content">
                    Select a term from the dropdown above. Use the <strong>Continuous Assessments</strong> tab to view monthly test scores,
                    or the <strong>Examinations</strong> tab to view end-of-term exam results with detailed subject performance.
                </div>
            </div>

            <!-- Assessment Content (AJAX Loaded) -->
            <div id="StudentTermTestsList">
                <div class="loading-container">
                    <div class="loading-spinner mx-auto"></div>
                    <p>Loading assessment data...</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="sponsor-footer">
            <i class="bx bx-calendar"></i>
            <span id="currentTermDisplay">Term {{ $currentTerm->term }}, {{ $currentTerm->year }}</span>
        </div>
    </div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        var studentId = {{ $student->id }};

        $('#termId').change(function() {
            var term = $(this).val();
            var termText = $(this).find('option:selected').text();
            var setSessionUrl = "{{ route('sponsor.term-session') }}";

            // Update footer term display
            $('#currentTermDisplay').text(termText);

            // Show loading state
            $('#StudentTermTestsList').html(`
                <div class="loading-container">
                    <div class="loading-spinner mx-auto"></div>
                    <p>Loading assessment data...</p>
                </div>
            `);

            $.ajax({
                url: setSessionUrl,
                method: 'POST',
                data: {
                    term_id: term,
                    _token: '{{ csrf_token() }}'
                },
                error: function(xhr, status, error) {
                    console.error("Response:", xhr.responseText);
                    $('#StudentTermTestsList').html(`
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="bx bx-error-circle"></i>
                            </div>
                            <h5>Connection Error</h5>
                            <p>Unable to load assessment data. Please try again.</p>
                        </div>
                    `);
                },
                success: function() {
                    fetchStudentTermData();
                }
            });
        });

        function fetchStudentTermData() {
            var termDataUrl = "{{ route('sponsor.assessment.student.term', $student->id) }}";
            $.ajax({
                url: termDataUrl,
                method: 'GET',
                success: function(response) {
                    $('#StudentTermTestsList').html(response);
                    // Re-initialize Bootstrap tabs after AJAX load
                    initializeTabs();
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching term data:", xhr.status, xhr.statusText);
                    $('#StudentTermTestsList').html(`
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="bx bx-error-circle"></i>
                            </div>
                            <h5>Error Loading Data</h5>
                            <p>Unable to fetch assessment data. Please refresh and try again.</p>
                        </div>
                    `);
                }
            });
        }

        function initializeTabs() {
            // Re-initialize Bootstrap tabs after AJAX content load
            var tabElements = document.querySelectorAll('[data-bs-toggle="tab"], [data-bs-toggle="pill"]');
            tabElements.forEach(function(el) {
                if (!el._bsTab) {
                    new bootstrap.Tab(el);
                }
            });
        }

        // Initial load
        $('#termId').trigger('change');
    });
</script>
@endsection
