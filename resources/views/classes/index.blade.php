@extends('layouts.master')
@section('title')
    Class Lists | Academic Management
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

        /* Term Selector */
        .term-selector {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .term-selector label {
            font-weight: 500;
            color: #374151;
            font-size: 14px;
            white-space: nowrap;
        }

        .term-selector .form-select {
            min-width: 180px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            padding: 8px 12px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .term-selector .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Grade Selector */
        .grade-selector {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .grade-selector label {
            font-weight: 500;
            color: #374151;
            font-size: 14px;
            white-space: nowrap;
        }

        .grade-selector .form-select {
            min-width: 180px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            padding: 8px 12px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .grade-selector .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn-add-new {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 16px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            border-radius: 3px;
            color: white;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-add-new:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        /* Reports Dropdown */
        .reports-dropdown .dropdown-toggle {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 16px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            color: #374151;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .reports-dropdown .dropdown-toggle:hover {
            border-color: #3b82f6;
            color: #1e40af;
            background: #f0f9ff;
        }

        .reports-dropdown .dropdown-toggle::after {
            display: none;
        }

        .reports-dropdown .dropdown-menu {
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-radius: 3px;
            padding: 8px 0;
            min-width: 260px;
            margin-top: 4px;
        }

        .reports-dropdown .dropdown-item {
            padding: 10px 16px;
            font-size: 14px;
            color: #374151;
            transition: all 0.2s ease;
        }

        .reports-dropdown .dropdown-item:hover {
            background: #f3f4f6;
            color: #1f2937;
        }

        .reports-dropdown .dropdown-item i {
            width: 20px;
            margin-right: 8px;
        }

        /* Table Styling */
        .classes-table {
            width: 100%;
            border-collapse: collapse;
        }

        .classes-table thead th {
            background: #f9fafb;
            padding: 12px 16px;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e5e7eb;
        }

        .classes-table tbody td {
            padding: 12px 16px;
            color: #4b5563;
            font-size: 14px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }

        .classes-table tbody tr:hover {
            background: #f9fafb;
        }

        .classes-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Action Button Styles for Table */
        .table-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 3px;
            border: none;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .table-action-btn.view {
            background: #e0f2fe;
            color: #0284c7;
        }

        .table-action-btn.view:hover {
            background: #0284c7;
            color: white;
        }

        .table-action-btn.allocate {
            background: #dbeafe;
            color: #2563eb;
        }

        .table-action-btn.allocate:hover {
            background: #2563eb;
            color: white;
        }

        .table-action-btn.edit {
            background: #fef3c7;
            color: #d97706;
        }

        .table-action-btn.edit:hover {
            background: #d97706;
            color: white;
        }

        .table-action-btn.delete {
            background: #fee2e2;
            color: #dc2626;
        }

        .table-action-btn.delete:hover {
            background: #dc2626;
            color: white;
        }

        /* Loading Placeholder */
        .placeholder-glow {
            animation: placeholder-glow 2s ease-in-out infinite;
        }

        @keyframes placeholder-glow {
            50% {
                opacity: 0.5;
            }
        }

        .placeholder-item {
            display: inline-block;
            background-color: #e5e7eb;
            border-radius: 3px;
        }

        .loading-table {
            background: white;
            border-radius: 3px;
        }

        .loading-table thead th {
            background: #f9fafb;
            padding: 12px 16px;
        }

        .loading-table tbody td {
            padding: 12px 16px;
        }

        /* Text Muted Style */
        .text-muted-custom {
            color: #9ca3af;
            font-style: italic;
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

            .term-selector,
            .grade-selector {
                width: 100%;
            }

            .term-selector .form-select,
            .grade-selector .form-select {
                min-width: 100%;
            }

            .action-buttons {
                justify-content: flex-end;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="#">Classes</a>
        @endslot
        @slot('title')
            Class Lists
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

    <div class="row">
        <div class="col-10"></div>
        <div class="col-2">
            <div class="term-selector mb-2">
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
            <h3><i class="fas fa-chalkboard me-2"></i>Academic Class Management</h3>
            <p>Manage classes, allocate students, and assign class teachers</p>
        </div>

        <div class="settings-body">
            <div class="help-text">
                <div class="help-title">About Class Management</div>
                <div class="help-content">
                    Create and manage classes for each academic term. Assign class teachers, allocate students,
                    and configure class monitors/monitresses. Use the filters below to view classes by term and grade.
                </div>
            </div>

            <div class="controls-row">
                <div class="d-flex align-items-center gap-4 flex-wrap">
                    <div class="grade-selector">
                        <input type="hidden" id="selectedClassGradeId" value="{{ session('selectedClassGradeId') }}">
                        @if (!empty($grades))
                            <select name="gradeId" id="gradeId" class="form-select">
                                @foreach ($grades as $index => $grade)
                                    <option value="{{ $grade->id }}" {{ $index == 0 ? 'selected' : '' }}>
                                        {{ $grade->name }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                </div>

                <div class="action-buttons">
                    @can('manage-academic')
                        @if (!session('is_past_term'))
                            <a href="{{ route('academic.create') }}" class="btn-add-new">
                                <i class="bx bx-plus"></i> New Class
                            </a>
                        @endif
                    @endcan

                    @can('manage-academic')
                        <div class="btn-group reports-dropdown">
                            <button type="button" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-chart-bar"></i> Analysis Reports <i class="fas fa-chevron-down ms-1"
                                    style="font-size: 10px;"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ route('academic.class-teacher-analysis') }}">
                                        <i class="fas fa-list-alt text-primary"></i> Classes List Analysis
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('academic.teacher-commitments-analysis') }}">
                                        <i class="fas fa-user-tie text-purple"></i> Teachers Commitments Analysis
                                    </a>
                                </li>
                            </ul>
                        </div>
                    @endcan
                </div>
            </div>

            <!-- Class Lists -->
            <div id="class_lists"></div>

            <!-- Loading Placeholder -->
            <div id="loadingPlaceholder" class="loading-table">
                <table class="classes-table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Name</th>
                            <th scope="col">Class Teacher</th>
                            <th scope="col">No. of Students</th>
                            <th scope="col">Monitor</th>
                            <th scope="col">Monitress</th>
                            <th scope="col">Grade</th>
                            <th scope="col">Year</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 0; $i < 5; $i++)
                            <tr class="placeholder-glow">
                                <td><span class="placeholder-item" style="width: 20px; height: 20px;"></span></td>
                                <td><span class="placeholder-item" style="width: 100px; height: 20px;"></span></td>
                                <td><span class="placeholder-item" style="width: 130px; height: 20px;"></span></td>
                                <td><span class="placeholder-item" style="width: 40px; height: 20px;"></span></td>
                                <td><span class="placeholder-item" style="width: 100px; height: 20px;"></span></td>
                                <td><span class="placeholder-item" style="width: 100px; height: 20px;"></span></td>
                                <td><span class="placeholder-item" style="width: 70px; height: 20px;"></span></td>
                                <td><span class="placeholder-item" style="width: 50px; height: 20px;"></span></td>
                                <td><span class="placeholder-item" style="width: 120px; height: 20px;"></span></td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            function storeInLocalStorage(gradeId) {
                localStorage.setItem('selectedClassGradeId', gradeId);
            }

            function getFromLocalStorage() {
                return localStorage.getItem('selectedClassGradeId');
            }

            function updateClassLists(gradeId) {
                var baseUrl =
                    "{{ route('academic.class-lists', ['termId' => 'tempTermId', 'gradeId' => 'tempGradeId']) }}";
                var klassUrl = baseUrl.replace('tempTermId', $('#termId').val()).replace('tempGradeId', gradeId);

                $.get(klassUrl, function(data) {
                    $('#loadingPlaceholder').hide();
                    $('#class_lists').html(data);
                }).fail(function() {
                    $('#loadingPlaceholder').hide();
                    $('#class_lists').html(`
                        <div class="alert alert-danger">
                            <i class="bx bx-error-circle me-2"></i>
                            Failed to load classes. Please try again.
                        </div>
                    `);
                });
            }

            function updateGrades() {
                var termId = $('#termId').val();
                var selectedClassGradeId = getFromLocalStorage();
                var getGradesForTerm = "{{ route('klasses.get-grades-for-term') }}";

                $.ajax({
                    url: getGradesForTerm,
                    type: 'GET',
                    data: {
                        term_id: termId,
                    },
                    success: function(data) {
                        var $gradeSelect = $('#gradeId');
                        $gradeSelect.empty();

                        var isSelectedSet = false;
                        $.each(data, function(index, grade) {
                            var $option = $('<option></option>').val(grade.id).text(grade.name);
                            if (grade.id == selectedClassGradeId) {
                                $option.prop('selected', true);
                                isSelectedSet = true;
                            }
                            $gradeSelect.append($option);
                        });

                        if (!isSelectedSet && $gradeSelect.find('option').length > 0) {
                            $gradeSelect.find('option:first').prop('selected', true);
                            selectedClassGradeId = $gradeSelect.find('option:first').val();
                        }
                        $('#selectedClassGradeId').val(
                            selectedClassGradeId);
                        updateClassLists(selectedClassGradeId);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                    }
                });
            }

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
                    success: function() {
                        updateGrades();
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", xhr.status, xhr.statusText);
                        console.error("Detailed error:", error);
                        console.error("Response:", xhr.responseText);
                    }
                });
            });

            $('#gradeId').change(function() {
                var gradeId = $(this).val();
                $('#selectedClassGradeId').val(gradeId);
                storeInLocalStorage(gradeId);
                updateClassLists(gradeId);
            });

            var initialGradeId = getFromLocalStorage();
            if (initialGradeId) {
                updateGrades();
            } else {
                $('#termId').trigger('change');
            }
        });

        function confirmDeleteClass() {
            return confirm('Are you sure you want to delete this class? This action cannot be undone.');
        }
    </script>
@endsection
