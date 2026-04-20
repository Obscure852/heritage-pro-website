@extends('layouts.master')
@section('title')
    Overall Grading | Academic Management
@endsection

@section('css')
    <style>
        /* Main Container */
        .settings-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .settings-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .settings-header h3 {
            margin: 0;
            font-weight: 600;
        }

        .settings-header p {
            margin: 6px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .settings-body {
            padding: 24px;
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

        /* Controls Row */
        .controls-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 20px;
        }

        /* Selectors */
        .filter-selector {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .filter-selector label {
            font-weight: 500;
            color: #374151;
            font-size: 14px;
            white-space: nowrap;
        }

        .filter-selector .form-select {
            min-width: 180px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            padding: 8px 12px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .filter-selector .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Shimmer Loading Animation */
        .shimmer-bg {
            background: #f6f7f8;
            background-image: linear-gradient(to right, #f6f7f8 0%, #edeef1 20%, #f6f7f8 40%, #f6f7f8 100%);
            background-repeat: no-repeat;
            background-size: 800px 104px;
            display: inline-block;
            position: relative;
            animation: shimmer 1s linear infinite;
            border-radius: 3px;
        }

        @keyframes shimmer {
            0% {
                background-position: -468px 0;
            }

            100% {
                background-position: 468px 0;
            }
        }

        .placeholder-card {
            background: white;
            border-radius: 3px;
            border: 1px solid #e5e7eb;
            padding: 20px;
        }

        .placeholder-table {
            width: 100%;
            border-collapse: collapse;
        }

        .placeholder-table th,
        .placeholder-table td {
            padding: 12px 16px;
            border-bottom: 1px solid #e5e7eb;
        }

        .placeholder-table th {
            background: #f9fafb;
        }

        /* Content Container */
        #tab-nav .card {
            background: white;
            border-radius: 3px;
            border: 1px solid #e5e7eb;
        }

        #tab-nav .card-body {
            padding: 20px;
        }

        /* Table Styling inside tab-nav */
        #tab-nav table {
            width: 100%;
            border-collapse: collapse;
        }

        #tab-nav table thead th {
            background: #f9fafb;
            padding: 12px 16px;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e5e7eb;
        }

        #tab-nav table tbody td {
            padding: 12px 16px;
            color: #4b5563;
            font-size: 14px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }

        #tab-nav table tbody tr:hover {
            background: #f9fafb;
        }

        #tab-nav table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Button Styling */
        .btn {
            padding: 10px 20px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-1px);
            color: white;
        }

        /* Loading Button Animation */
        .btn-loading.loading .btn-text {
            display: none;
        }

        .btn-loading.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }

        .btn-loading:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .settings-header {
                padding: 20px;
            }

            .settings-body {
                padding: 16px;
            }

            .controls-row {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-selector {
                width: 100%;
            }

            .filter-selector .form-select {
                min-width: 100%;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="{{ route('academic.index') }}">Classes</a>
        @endslot
        @slot('title')
            Overall Grading
        @endslot
    @endcomponent

    <div class="row mb-2">
        <div class="col-10"></div>
        <div class="col-2">
            <div class="filter-selector">
                <select name="term" id="termId" class="form-select">
                    @if (!empty($terms))
                        @foreach ($terms as $term)
                            <option data-year="{{ $term->year }}"
                                value="{{ $term->id }}"{{ $term->id == session('selected_term_id', $currentTerm->id) ? 'selected' : '' }}>
                                {{ 'Term ' . $term->term . ', ' . $term->year }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
        </div>
    </div>

    <div class="settings-container">
        <div class="settings-header">
            <h3><i class="fas fa-chart-line me-2"></i>Overall Grading Configuration</h3>
            <p>Configure grading scales and points matrices for each grade level</p>
        </div>

        <div class="settings-body">
            <div class="help-text">
                <div class="help-title">About Grading Configuration</div>
                <div class="help-content">
                    Set up the grading criteria for each grade level. This includes point ranges, grade symbols,
                    and corresponding remarks that will be used in student assessments and reports.
                </div>
            </div>

            <div class="controls-row">
                <div class="d-flex align-items-center gap-4 flex-wrap">
                    <div class="filter-selector">
                        <select name="grade_lists" id="gradeId" class="form-select">
                            @if (!empty($grades))
                                @foreach ($grades as $index => $grade)
                                    <option value="{{ $grade->id }}" {{ $index == 0 ? 'selected' : '' }}>
                                        {{ $grade->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
            </div>

            <!-- Grading Content -->
            <div id="tab-nav">
                <div id="grade-list-placeholder" class="placeholder-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="shimmer-bg" style="width: 150px; height: 24px;"></div>
                        <div class="d-flex gap-2">
                            <div class="shimmer-bg" style="width: 100px; height: 36px;"></div>
                            <div class="shimmer-bg" style="width: 100px; height: 36px;"></div>
                        </div>
                    </div>
                    <table class="placeholder-table">
                        <thead>
                            <tr>
                                <th>
                                    <div class="shimmer-bg" style="height: 18px; width: 100%;"></div>
                                </th>
                                <th>
                                    <div class="shimmer-bg" style="height: 18px; width: 100%;"></div>
                                </th>
                                <th>
                                    <div class="shimmer-bg" style="height: 18px; width: 100%;"></div>
                                </th>
                                <th>
                                    <div class="shimmer-bg" style="height: 18px; width: 100%;"></div>
                                </th>
                                <th>
                                    <div class="shimmer-bg" style="height: 18px; width: 100%;"></div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @for ($i = 1; $i <= 5; $i++)
                                <tr>
                                    <td>
                                        <div class="shimmer-bg" style="height: 16px; width: 100%;"></div>
                                    </td>
                                    <td>
                                        <div class="shimmer-bg" style="height: 16px; width: 100%;"></div>
                                    </td>
                                    <td>
                                        <div class="shimmer-bg" style="height: 16px; width: 100%;"></div>
                                    </td>
                                    <td>
                                        <div class="shimmer-bg" style="height: 16px; width: 100%;"></div>
                                    </td>
                                    <td>
                                        <div class="shimmer-bg" style="height: 16px; width: 100%;"></div>
                                    </td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
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
                var studentsSessionUrl = "{{ route('students.term-session') }}";
                $.ajax({
                    url: studentsSessionUrl,
                    method: 'POST',
                    data: {
                        term_id: term,
                        _token: '{{ csrf_token() }}'
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", xhr.status, xhr.statusText);
                        console.error("Detailed error:", error);
                        console.error("Response:", xhr.responseText);
                    },
                    success: function() {
                        updateGrades();
                    }
                });
            });

            $('#gradeId').change(function() {
                storeGradeIdInLocalStorage();
                showNavigation();
            });

            var initialGradeId = getGradeIdFromLocalStorage();
            if (initialGradeId) {
                $('#gradeId').val(initialGradeId);
            }

            updateGrades();

            function storeGradeIdInLocalStorage() {
                var selectedGradingId = $('#gradeId').val();
                localStorage.setItem('selectedGradingId', selectedGradingId);
            }

            function getGradeIdFromLocalStorage() {
                return localStorage.getItem('selectedGradingId');
            }

            function showNavigation() {
                var gradeId = $('#gradeId').val();
                var overallGradingUrl = "{{ route('academic.overall-grading-list', ['gradeId' => ':gradeId']) }}";
                overallGradingUrl = overallGradingUrl.replace(':gradeId', encodeURIComponent(gradeId));

                fetch(overallGradingUrl)
                    .then(response => {
                        if (response.redirected) {
                            throw new Error('No data available for the selected grade');
                        }

                        if (!response.ok) {
                            if (response.headers.get('content-type')?.includes('application/json')) {
                                return response.json().then(data => {
                                    throw new Error(data.error || 'Failed to load data');
                                });
                            } else {
                                throw new Error('Network response was not ok: ' + response.status);
                            }
                        }
                        return response.text();
                    })
                    .then(data => {
                        if (!data || !data.trim()) {
                            throw new Error('No grading data available for the selected grade');
                        }

                        if (data.includes('<html') || data.includes('layouts.master')) {
                            throw new Error(
                                'The system returned a full page instead of partial content. Please contact the administrator.'
                            );
                        }

                        document.getElementById('tab-nav').innerHTML = '<div class="col-12">' + data + '</div>';
                    })
                    .catch(error => {
                        console.error('Error fetching data:', error);

                        let userMessage = error.message;
                        if (error.message.includes('No data') || error.message.includes('No grading')) {
                            var schoolType = document.getElementById('schoolTypeIndicator')?.value || 'Primary';

                            var addButtonText = 'Add Grading Data';
                            var noDataMessage = 'No Grading Data Available';
                            var detailMessage = 'There are no grading records for the selected grade.';
                            var addRoute = "{{ route('academic.add-overall-grading') }}";

                            if (schoolType === 'Junior') {
                                addButtonText = 'Add Points Matrix';
                                noDataMessage = 'No Points Matrix Available';
                                detailMessage = 'There are no points matrix records for the selected form.';
                            }

                            userMessage = `
                                <div class="text-center py-5">
                                    <i class="bx bx-info-circle" style="font-size: 48px; color: #d1d5db;"></i>
                                    <h5 class="mt-3" style="color: #374151;">${noDataMessage}</h5>
                                    <p style="color: #6b7280;">${detailMessage}</p>
                                    <a href="${addRoute}" class="btn btn-primary btn-sm mt-2">
                                        <i class="bx bx-plus me-1"></i> ${addButtonText}
                                    </a>
                                </div>
                            `;
                        }

                        document.getElementById('tab-nav').innerHTML = '<div class="col-12">' + userMessage +
                            '</div>';
                    });
            }

            function updateGrades() {
                var termId = $('#termId').val();
                var getGradesForTerm = "{{ route('klasses.get-grades-for-term') }}";
                $.ajax({
                    url: getGradesForTerm,
                    type: 'GET',
                    data: {
                        'term_id': termId,
                    },
                    success: function(data) {
                        var $gradeSelect = $('#gradeId');
                        $gradeSelect.empty();

                        $.each(data, function(index, grade) {
                            $gradeSelect.append($('<option></option>').val(grade.id).text(grade
                                .name));
                        });

                        var initialGradeId = getGradeIdFromLocalStorage();
                        if (initialGradeId) {
                            $gradeSelect.val(initialGradeId);
                        } else if (data.length > 0) {
                            $gradeSelect.val(data[0].id);
                        }
                        showNavigation();
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                    }
                });
            }

            $('#termId').trigger('change');
        });
    </script>
@endsection
