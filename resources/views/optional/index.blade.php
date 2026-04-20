@extends('layouts.master')
@section('title')
    Optional Subjects | Academic Management
@endsection

@section('css')
    <style>
        /* Settings Container */
        .settings-container {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .settings-container.body-only .settings-body {
            border-radius: 3px;
        }

        .settings-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 24px 24px 20px 24px;
            border-radius: 3px;
        }

        .settings-header h3 {
            margin: 0;
            font-weight: 600;
            font-size: 18px;
        }

        .settings-header p {
            margin: 4px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        /* Stats in Header */
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

        @media (max-width: 768px) {
            .stat-item h4 {
                font-size: 1.25rem;
            }

            .stat-item small {
                font-size: 0.75rem;
            }
        }

        /* Actions Row - Standalone below header */
        .actions-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            margin-bottom: 20px;
        }

        .control-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .control-group label {
            font-size: 12px;
            font-weight: 500;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .control-group .form-select {
            border: 1px solid #d1d5db;
            border-radius: 3px;
            padding: 8px 12px;
            font-size: 14px;
            min-width: 180px;
            transition: all 0.2s ease;
        }

        .control-group .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .actions-buttons {
            display: inline-flex;
            align-items: center;
            gap: 12px;
        }

        /* Button Styling - matching admissions */
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

        /* Reports Dropdown Styling */
        .reports-dropdown .dropdown-toggle {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .reports-dropdown .dropdown-toggle:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .reports-dropdown .dropdown-toggle:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .reports-dropdown .dropdown-menu {
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-radius: 3px;
            padding: 8px 0;
            min-width: 220px;
            margin-top: 4px;
        }

        .reports-dropdown .dropdown-item {
            padding: 8px 16px;
            font-size: 14px;
            color: #374151;
        }

        .reports-dropdown .dropdown-item:hover {
            background: #f3f4f6;
            color: #1f2937;
        }

        .reports-dropdown .dropdown-item i {
            width: 20px;
            margin-right: 8px;
        }

        /* Content Area */
        .settings-body {
            padding: 24px;
        }

        /* Loading Placeholder Styling */
        .placeholder-glow {
            animation: placeholder-glow 2s ease-in-out infinite;
        }

        @keyframes placeholder-glow {
            50% {
                opacity: 0.5;
            }
        }

        .placeholder-item {
            background: linear-gradient(90deg, #e5e7eb 25%, #f3f4f6 50%, #e5e7eb 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
            border-radius: 3px;
        }

        @keyframes shimmer {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        .placeholder-accordion-button {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 16px 20px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
        }

        .placeholder-card {
            height: 200px;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
            background: white;
        }

        .placeholder-card-body {
            padding: 16px;
        }

        .placeholder-card-footer {
            padding: 12px 16px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 48px;
            color: #d1d5db;
            margin-bottom: 16px;
        }

        .empty-state h5 {
            color: #374151;
            margin-bottom: 8px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .actions-row {
                flex-direction: column;
                align-items: stretch;
                gap: 12px;
            }

            .control-group .form-select {
                min-width: 100%;
            }

            .actions-buttons {
                justify-content: flex-end;
                flex-wrap: wrap;
            }

            .header-stats {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Academics
        @endslot
        @slot('title')
            Optional Subjects
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

    <div class="row mb-2">
        <div class="col-10"></div>
        <div class="col-2">
            <div class="control-group">
                <select name="term" id="termId" onchange="updateGrades()" class="form-select form-select-sm">
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
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;"><i class="bx bx-book-reader me-2"></i>Optional Subjects Management</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Manage optional subject classes and student allocations</p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white" id="statTotalClasses">-</h4>
                                <small class="opacity-75">Total Optional Classes</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white" id="statSubjects">-</h4>
                                <small class="opacity-75">Subjects</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="actions-row">
        <form action="#">
            <input type="hidden" id="selectedOptionalGradeId" value="{{ session('selectedOptionalGradeId') }}">
        </form>
        <div class="row w-100 align-items-start">
            <div class="col-auto control-group" style="padding-left: 0; margin-left: 0;">
                <select name="gradeOption" id="gradeId" class="form-select form-select-sm">
                    @if (!empty($grades))
                        @foreach ($grades as $grade)
                            <option value="{{ $grade->id }}" {{ $grade->id == 1 ? 'selected' : '' }}>
                                {{ $grade->name }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="col text-end">
                <div class="actions-buttons">
                    @can('manage-academic')
                        @if (!session('is_past_term'))
                            <a href="{{ route('optional.create-new-option') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> New Option
                            </a>
                        @endif
                    @endcan

                    @can('manage-academic')
                        <div class="btn-group reports-dropdown">
                            <button type="button" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-chart-bar me-2"></i>Reports<i class="fas fa-chevron-down ms-2"
                                    style="font-size: 10px;"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);" onclick="getOptionalSubjectsAnalysis()">
                                        <i class="fas fa-list-ul text-primary"></i> Optional Subject Analysis Summary
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);" onclick="getOptionalClassLists()">
                                        <i class="fas fa-tasks text-purple"></i> Optional Class Lists
                                    </a>
                                </li>
                            </ul>
                        </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>
    <div class="settings-container body-only">
        <div class="settings-body">
            <div id="class_lists">
            </div>

            <div id="loadingPlaceholder">
                <div class="accordion" id="placeholderAccordion">
                    @for ($i = 0; $i < 3; $i++)
                        <div class="accordion-item mb-3" style="border: none;">
                            <h2 class="accordion-header">
                                <div class="placeholder-accordion-button placeholder-glow">
                                    <div class="placeholder-item" style="width: 150px; height: 24px;"></div>
                                    <div class="placeholder-item ms-2" style="width: 50px; height: 24px;"></div>
                                    <div class="placeholder-item ms-2" style="width: 80px; height: 24px;"></div>
                                </div>
                            </h2>
                            <div class="accordion-collapse collapse show" style="border: none;">
                                <div class="accordion-body" style="padding: 16px 0;">
                                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                                        @for ($j = 0; $j < 3; $j++)
                                            <div class="col">
                                                <div class="placeholder-card placeholder-glow">
                                                    <div class="placeholder-card-body">
                                                        <div class="placeholder-item mb-2"
                                                            style="width: 70%; height: 24px;"></div>
                                                        <div class="placeholder-item mb-2"
                                                            style="width: 100%; height: 16px;"></div>
                                                        <div class="placeholder-item mb-2"
                                                            style="width: 100%; height: 16px;"></div>
                                                        <div class="placeholder-item mb-2"
                                                            style="width: 100%; height: 16px;"></div>
                                                        <div class="placeholder-item" style="width: 60%; height: 16px;">
                                                        </div>
                                                    </div>
                                                    <div class="placeholder-card-footer">
                                                        <div class="placeholder-item" style="width: 80px; height: 30px;">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endfor
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        function updateClassLists() {
            var termId = $('#termId').val();
            var gradeId = $('#gradeId').val();

            var url = "{{ route('optional.grades-junior', [':termId', ':gradeId']) }}";
            url = url.replace(':termId', termId).replace(':gradeId', gradeId);

            $('#class_lists').html($('#loadingPlaceholder').html());

            // Reset stats while loading
            $('#statTotalClasses').text('-');
            $('#statSubjects').text('-');

            $.ajax({
                url: url,
                type: 'GET',
                success: function(data) {
                    $('#loadingPlaceholder').hide();
                    $('#class_lists').fadeOut(200, function() {
                        $(this).html(data).fadeIn(200);

                        // Calculate stats from loaded data
                        updateStats();
                    });
                },
                error: function(xhr, status, error) {
                    $('#loadingPlaceholder').hide();
                    $('#class_lists').html(`
                        <div class="empty-state">
                            <i class="bx bx-error-circle"></i>
                            <h5>Failed to Load Data</h5>
                            <p>Unable to load class data. Please try again.</p>
                        </div>
                    `);
                    console.error("Error loading class data:", error);
                }
            });
        }

        function updateStats() {
            // Count total classes from the loaded cards
            var totalClasses = $('#class_lists .option-card').length;

            // Count unique subjects (accordion sections)
            var totalSubjects = $('#class_lists .accordion-item').length;

            // Update stats display
            $('#statTotalClasses').text(totalClasses);
            $('#statSubjects').text(totalSubjects);
        }

        function updateGrades() {
            var termId = $('#termId').val();
            var selectedOptionalGradeId = localStorage.getItem('selectedOptionalGradeId');

            var url = "{{ route('optional.grades-for-term') }}";

            $.ajax({
                url: url,
                type: 'GET',
                data: {
                    'term_id': termId,
                },
                success: function(data) {
                    var $gradeSelect = $('#gradeId');
                    $gradeSelect.empty();

                    $.each(data, function(index, grade) {
                        var $option = $('<option></option>').val(grade.id).text(grade.name);
                        $gradeSelect.append($option);
                    });

                    if (selectedOptionalGradeId && $gradeSelect.find('option[value="' +
                            selectedOptionalGradeId + '"]').length > 0) {
                        $gradeSelect.val(selectedOptionalGradeId);
                    } else if ($gradeSelect.find('option').length > 0) {
                        $gradeSelect.find('option').first().prop('selected', true);
                    }

                    if ($gradeSelect.find('option').length > 0) {
                        updateClassLists();
                        return;
                    }

                    $('#class_lists').html(`
                        <div class="empty-state">
                            <i class="bx bx-folder-open"></i>
                            <h5>No Optional Grades Available</h5>
                            <p>No junior or senior grades with optional classes are available for the selected term.</p>
                        </div>
                    `);
                    $('#statTotalClasses').text('0');
                    $('#statSubjects').text('0');
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                }
            });
        }

        $(document).ready(function() {
            $('#termId').change(function() {
                updateGrades();
            });

            $('#gradeId').change(function() {
                storeInLocalStorage();
                updateClassLists();
            });

            $('#termId').trigger('change');

            function storeInLocalStorage() {
                var selectedGradeOptionsId = $('#gradeId').val();
                localStorage.setItem('selectedOptionalGradeId', selectedGradeOptionsId);
            }

            $(document).on('click', '.delete-option', function(e) {
                e.preventDefault();
                var optionName = $(this).data('option-name');
                if (confirm('Are you sure you want to delete the optional subject "' + optionName + '"?')) {
                    window.location.href = $(this).attr('href');
                }
            });
        });

        function getOptionalClassLists() {
            try {
                const gradeId = document.getElementById('gradeId').value;
                if (!gradeId) {
                    alert('Please select a grade first');
                    return;
                }

                const urlTemplate = "{{ route('optional.optional-classes-by-name', ':gradeId') }}";
                const url = urlTemplate.replace(':gradeId', gradeId);
                window.location.href = url;
            } catch (error) {
                alert('An error occurred while processing your request');
            }
        }

        function getOptionalSubjectsAnalysis() {
            try {
                const gradeId = document.getElementById('gradeId').value;
                if (!gradeId) {
                    alert('Please select a grade first');
                    return;
                }
                const urlTemplate = "{{ route('optional.optional-subjects-summary', ':gradeId') }}";
                const url = urlTemplate.replace(':gradeId', gradeId);
                window.location.href = url;
            } catch (error) {
                console.error("An error occurred:", error);
                alert('An error occurred while processing your request');
            }
        }
    </script>
@endsection
