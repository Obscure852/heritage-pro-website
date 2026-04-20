@extends('layouts.master')
@section('title')
    Assessment Module
@endsection
@section('css')
    <style>
        /* Main Container */
        .assessment-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .assessment-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .assessment-body {
            padding: 24px;
        }

        /* Stats */
        .stat-item {
            padding: 10px 0;
        }

        .stat-item h4 {
            font-size: 1.8rem !important;
            font-weight: 700;
        }

        .stat-item small {
            font-size: 1rem !important;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Help Text */
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

        /* Controls */
        .controls-row {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: center;
            margin-bottom: 20px;
        }

        .controls-row .form-select,
        .controls-row .form-control {
            border: 1px solid #d1d5db;
            border-radius: 3px;
            padding: 8px 12px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .controls-row .form-select:focus,
        .controls-row .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        /* View Toggle */
        .view-toggle {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .view-toggle-label {
            font-size: 13px;
            color: #6b7280;
            margin: 0;
        }

        .view-toggle-buttons {
            display: inline-flex;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            overflow: hidden;
        }

        .view-toggle-buttons .view-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 28px;
            border: none;
            background: #fff;
            color: #6b7280;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .view-toggle-buttons .view-btn:first-child {
            border-right: 1px solid #d1d5db;
        }

        .view-toggle-buttons .view-btn:hover {
            background: #f3f4f6;
        }

        .view-toggle-buttons .view-btn.active {
            background: #3b82f6;
            color: #fff;
        }

        .view-toggle-buttons .view-btn i {
            font-size: 14px;
        }

        /* Class Buttons */
        .class-button {
            margin-right: 8px;
            margin-bottom: 8px;
            min-width: 240px;
            height: 38px;
            text-align: center;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .class-button.btn-outline-primary {
            border: 1px solid #d1d5db;
            color: #374151;
            background: white;
        }

        .class-button.btn-outline-primary:hover {
            background: #f3f4f6;
            border-color: #3b82f6;
            color: #3b82f6;
        }

        .class-button.active {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border-color: transparent;
            color: white;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
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

        /* Dropdown Menus */
        .first-dropdown li,
        .second-dropdown li {
            position: relative;
        }

        .first-dropdown .dropdown-submenu,
        .second-dropdown .dropdown-submenu {
            display: none;
            position: absolute;
            left: 100%;
            top: -7px;
        }

        .first-dropdown .dropdown-submenu-left,
        .second-dropdown .dropdown-submenu-left {
            right: 100%;
            left: auto;
        }

        .first-dropdown li:hover>.dropdown-submenu,
        .second-dropdown li:hover>.dropdown-submenu,
        .dropdown-submenu li:hover>.dropdown-submenu {
            display: block;
        }

        .first-dropdown,
        .second-dropdown,
        .first-dropdown .dropdown-submenu,
        .second-dropdown .dropdown-submenu {
            border-radius: 3px;
            border: none;
            min-width: 380px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .first-dropdown .dropdown-item,
        .second-dropdown .dropdown-item,
        .dropdown-submenu .dropdown-item {
            padding: 8px 16px;
            font-size: 14px;
            white-space: nowrap;
            transition: background-color 0.2s;
            color: #374151;
        }

        .first-dropdown .dropdown-item:hover,
        .second-dropdown .dropdown-item:hover,
        .dropdown-submenu .dropdown-item:hover {
            background-color: #f3f4f6;
            color: #1f2937;
        }

        .first-dropdown .dropdown-divider,
        .second-dropdown .dropdown-divider,
        .dropdown-submenu .dropdown-divider {
            margin: 0.3rem 0;
        }

        .first-dropdown,
        .second-dropdown,
        .dropdown-submenu {
            animation: dropdownFadeIn 0.15s ease-in-out;
        }

        @keyframes dropdownFadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dropdown-submenu {
            z-index: 1001;
        }

        .dropdown-submenu .dropdown-submenu {
            top: -7px;
            right: 100%;
            left: auto;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .stat-item h4 {
                font-size: 1.25rem;
            }

            .stat-item small {
                font-size: 0.75rem;
            }

            .assessment-header {
                padding: 20px;
            }

            .assessment-body {
                padding: 16px;
            }

            .controls-row {
                flex-direction: column;
                align-items: stretch;
            }

            .class-button {
                min-width: 100%;
            }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Assessment Premium
        @endslot
        @slot('title')
            Gradebook
        @endslot
    @endcomponent

    @if (session('message'))
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i>
                    @foreach ($errors->all() as $error)
                        <strong>{{ $error }}</strong><br>
                    @endforeach
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <div class="row mb-2">
        <div class="col-10"></div>
        <div class="col-2 d-flex justify-content-end">
            <select name="term" id="termId" onchange="updateKlasses()" class="form-select"
                style="width: auto; min-width: 180px;">
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

    <div class="assessment-container">
        <div class="assessment-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;"><i class="fas fa-clipboard-check me-2"></i>Gradebook</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Manage student assessments and generate reports</p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white" id="totalClasses">-</h4>
                                <small class="opacity-75">Classes</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white" id="totalStudents">-</h4>
                                <small class="opacity-75">Students</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white" id="selectedClass">-</h4>
                                <small class="opacity-75">Selected</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="assessment-body">
            <div class="help-text">
                <div class="help-title">Assessment Management</div>
                <div class="help-content">
                    Select a class to view and manage student assessments. Use the tiles or dropdown view to navigate
                    between classes.
                    Click on a class to load student data and access reports.
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-10"></div>
                <div class="col-2 d-flex justify-content-end">
                    <div class="view-toggle">
                        <div class="view-toggle-buttons">
                            <button type="button" class="view-btn active" data-view="tiles" title="Tiles View">
                                <i class="bx bx-grid-alt"></i>
                            </button>
                            <button type="button" class="view-btn" data-view="list" title="List View">
                                <i class="bx bx-list-ul"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Class selection section -->
            <div id="classSelection">
                <!-- Class selection (buttons or dropdown) will be dynamically added here -->
            </div>

            <!-- Student list section -->
            <div id="studentList" class="mt-4">
                <!-- Student list will be loaded here -->
            </div>

            <div id="loadingPlaceholder" class="mt-4">
                <div class="placeholder-card">
                    <!-- Class Buttons Placeholder -->
                    <div class="d-flex flex-wrap mb-4">
                        @for ($i = 0; $i < 6; $i++)
                            <div class="shimmer-bg" style="width: 240px; height: 38px; margin: 4px;"></div>
                        @endfor
                    </div>

                    <!-- Class Header Placeholder -->
                    <div class="shimmer-bg mb-3" style="width: 100%; height: 50px;"></div>

                    <!-- Student Table Placeholder -->
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
                                <th>
                                    <div class="shimmer-bg" style="height: 18px; width: 100%;"></div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @for ($i = 0; $i < 10; $i++)
                                <tr>
                                    <td>
                                        <div class="shimmer-bg" style="width: 30px; height: 16px;"></div>
                                    </td>
                                    <td>
                                        <div class="shimmer-bg" style="width: 100px; height: 16px;"></div>
                                    </td>
                                    <td>
                                        <div class="shimmer-bg" style="width: 100px; height: 16px;"></div>
                                    </td>
                                    <td>
                                        <div class="shimmer-bg" style="width: 60px; height: 16px;"></div>
                                    </td>
                                    <td>
                                        <div class="shimmer-bg" style="width: 50px; height: 16px;"></div>
                                    </td>
                                    <td>
                                        <div class="shimmer-bg" style="width: 80px; height: 16px;"></div>
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
        const assessmentContext = @json($assessmentContext ?? $schoolModeResolver->defaultAssessmentContext($resolvedSchoolMode));

        function withAssessmentContext(url) {
            var separator = url.indexOf('?') === -1 ? '?' : '&';
            return url + separator + 'context=' + encodeURIComponent(assessmentContext);
        }

        // Helper function to check if tiles view is active
        function isButtonView() {
            return $('.view-btn[data-view="tiles"]').hasClass('active');
        }

        $(document).ready(function() {
            $('#termId').change(function() {
                var termId = $(this).val();

                $.ajax({
                    url: "{{ route('assessment.update-term') }}",
                    method: "POST",
                    data: {
                        termId: termId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function() {
                        updateKlasses();
                    },
                    error: function(xhr, status, error) {
                        console.error("Error updating term:", error);
                        alert('Failed to update term. Please try again.');
                    }
                });
            });

            // View toggle button click handlers
            $('.view-btn').click(function() {
                $('.view-btn').removeClass('active');
                $(this).addClass('active');
                var viewMode = $(this).data('view');
                localStorage.setItem('viewMode', viewMode === 'tiles' ? 'button' : 'dropdown');
                updateKlasses();
            });

            // Initialize view from localStorage
            var storedViewMode = localStorage.getItem('viewMode') || 'button';
            var isTilesView = storedViewMode === 'button';
            $('.view-btn').removeClass('active');
            if (isTilesView) {
                $('.view-btn[data-view="tiles"]').addClass('active');
            } else {
                $('.view-btn[data-view="list"]').addClass('active');
            }

            updateKlasses();

        });

        function updateKlasses() {
            var termId = $('#termId').val();
            var isTilesView = isButtonView();
            var classForTermUrl = "{{ route('assessment.klasses-for-term') }}";

            $('#loadingPlaceholder').show();
            $('#studentList').empty();
            $('#classSelection').empty().append(
                '<div class="text-center py-3"><i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> Loading classes...</div>'
            );

            $.ajax({
                url: classForTermUrl,
                type: 'GET',
                data: {
                    'term_id': termId,
                    'context': assessmentContext
                },
                success: function(data) {
                    var $classSelection = $('#classSelection');
                    $classSelection.empty();

                    if (data && data.length > 0) {
                        // Update stats
                        var totalStudents = data.reduce((sum, klass) => sum + (klass.students_count || 0), 0);
                        $('#totalClasses').text(data.length);
                        $('#totalStudents').text(totalStudents);

                        if (isTilesView) {
                            createButtonView(data, $classSelection);
                        } else {
                            createDropdownView(data, $classSelection);
                        }
                    } else {
                        $('#loadingPlaceholder').hide();
                        $('#totalClasses').text('0');
                        $('#totalStudents').text('0');
                        $('#selectedClass').text('-');
                        $classSelection.html(`
                            <div class="text-center text-muted" style="padding: 40px 0;">
                                <i class="fas fa-chalkboard" style="font-size: 48px; opacity: 0.3;"></i>
                                <p class="mt-3 mb-0" style="font-size: 15px;">No Classes Available</p>
                            </div>
                        `);
                        $('#studentList').empty();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    $('#loadingPlaceholder').hide();
                    $('#classSelection').html(
                        '<div class="alert alert-warning d-flex align-items-center" role="alert"><i class="bx bxs-error-alt me-2 fs-4"></i><div><strong>Oops! Something went wrong.</strong><br>We couldn\'t load the classes. Please check your internet connection and try reloading the page. If the problem persists, please contact support.</div></div>'
                    );
                    $('#studentList').empty();
                }
            });
        }

        function createButtonView(data, $container) {
            $container.removeClass('col-6').addClass('col-12');
            var storedClassId = localStorage.getItem('selectedClassIdIndex');
            $.each(data, function(index, klass) {
                var gradeName = klass.grade && klass.grade.name ? klass.grade.name : 'Unknown';
                var button = $('<button></button>')
                    .addClass('btn btn-outline-primary class-button')
                    .attr('data-class-id', klass.id)
                    .attr('data-grade', gradeName)
                    .attr('data-students', klass.students_count)
                    .text(
                        klass.name + ' - ' +
                        (klass.teacher.firstname ? klass.teacher.firstname.charAt(0) + '. ' : '') +
                        klass.teacher.lastname + ' (' +
                        klass.students_count + ')'
                    )
                    .click(function() {
                        $('.class-button').removeClass('active btn-primary').addClass('btn-outline-primary');
                        $(this).removeClass('btn-outline-primary').addClass('active btn-primary');
                        localStorage.setItem('selectedClassIdIndex', klass.id);
                        $('#selectedClass').text(klass.students_count);
                        updateClassLists(klass.id);
                    });
                if (klass.id == storedClassId) {
                    button.removeClass('btn-outline-primary').addClass('active btn-primary');
                }
                $container.append(button);
            });
            if (!$('.class-button.active').length) {
                $container.find('button:first').removeClass('btn-outline-primary').addClass('active btn-primary');
            }
            $container.find('.class-button.active').trigger('click');
        }

        function createDropdownView(data, $container) {
            $container.removeClass('col-12').addClass('col-4');
            var select = $('<select></select>')
                .addClass('form-select')
                .attr('id', 'classDropdown')
                .change(function() {
                    var selectedClassId = $(this).val();
                    var selectedOption = $(this).find('option:selected');
                    localStorage.setItem('selectedClassIdIndex', selectedClassId);
                    $('#selectedClass').text(selectedOption.data('students') || '-');
                    updateClassLists(selectedClassId);
                });

            var storedClassId = localStorage.getItem('selectedClassIdIndex');
            $.each(data, function(index, klass) {
                var gradeName = klass.grade && klass.grade.name ? klass.grade.name : 'Unknown';
                var option = $('<option></option>')
                    .attr('value', klass.id)
                    .attr('data-grade', gradeName)
                    .attr('data-students', klass.students_count)
                    .text(klass.name + ' - ' + klass.teacher.firstname + ' ' + klass.teacher.lastname + ' (' + klass
                        .students_count + ')');
                if (klass.id == storedClassId) {
                    option.attr('selected', 'selected');
                }
                select.append(option);
            });

            $container.append(select);
            if (!select.val()) {
                select.find('option:first').prop('selected', true);
            }
            select.trigger('change');
        }

        function getCurrentGradeName() {
            if (isButtonView()) {
                return $('.class-button.active').data('grade');
            } else {
                return $('#classDropdown option:selected').data('grade');
            }
        }

        function updateClassLists(classId) {
            var termId = $('#termId').val();
            var baseUrl = "{{ route('assessment.class-lists', ['classId' => 'tempClassId', 'termId' => 'tempTermId']) }}";
            var klassUrl = withAssessmentContext(baseUrl.replace('tempClassId', classId).replace('tempTermId', termId));

            // Show loading indicator
            $('#studentList').html(
                '<div class="text-center mt-4"><i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> Loading student data...</div>'
            );

            $.ajax({
                url: klassUrl,
                type: 'GET',
                success: function(data) {
                    $('#loadingPlaceholder').hide();
                    if (data && data.trim() !== '') {
                        $('#studentList').html(data).fadeIn(200, function() {
                            initializeDataTable();
                            $('[data-bs-toggle="tooltip"]').tooltip();
                        });
                    } else {
                        $('#studentList').html(`
                            <div class="alert alert-info mt-4">
                                <i class="bx bx-info-circle me-2"></i>
                                No student data available for this class in the selected term.
                            </div>
                        `).fadeIn(200);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error loading class data:", error);
                    $('#loadingPlaceholder').hide();
                    $('#studentList').html(`
                        <div class="alert alert-danger mt-4">
                            <i class="bx bx-error-circle me-2"></i>
                            Failed to load class data. ${xhr.status === 404 ? 'No data found for this term.' : 'Please try again or contact your administrator.'}
                        </div>
                    `).fadeIn(200);
                }
            });
        }

        function initializeDataTable() {
            if ($.fn.DataTable.isDataTable('#studentTable')) {
                $('#studentTable').DataTable().destroy();
            }
            $('#studentTable').DataTable({
                "pageLength": 40,
                "ordering": true,
                "searching": true,
                "columnDefs": [{
                        "orderable": true,
                        "targets": [1, 2, 3, 4]
                    },
                    {
                        "orderable": false,
                        "targets": [0, 5]
                    },
                    {
                        "searchable": false,
                        "targets": [0, 5]
                    }
                ],
                "dom": '<"top"f>rt<"bottom"ip>',
                language: {
                    search: "Search students:",
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    },
                    info: "Showing _START_ to _END_ of _TOTAL_ students"
                },
                drawCallback: function() {
                    $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
                    $('[data-bs-toggle="tooltip"]').tooltip();
                }
            });
        }

        function getCurrentClassId() {
            if (isButtonView()) {
                return $('.class-button.active').data('class-id');
            } else {
                return $('#classDropdown').val();
            }
        }

        $('#classDropdown').change(function() {
            var selectedClassId = $(this).val();
            localStorage.setItem('selectedClassIdIndex', selectedClassId);
            updateClassLists(selectedClassId);
        });

        function openJuniorClassReportCards() {
            var classId = getCurrentClassId();
            var baseUrl = "{{ route('assessment.generate-grades-report-cards', ['classId' => 'tempClassId']) }}";
            var finalUrl = withAssessmentContext(baseUrl.replace('tempClassId', classId));
            window.open(finalUrl, 'PDFWindow', 'width=800,height=1000');
        }

        function openClassAnalysis(type, sequence) {
            var classId = getCurrentClassId();
            var baseUrl =
                "{{ route('assessment.generate-exam-analysis', ['classId' => 'tempClassId', 'type' => 'tempType', 'sequence' => 'tempSequence']) }}";
            var finalUrl = withAssessmentContext(baseUrl.replace('tempClassId', classId).replace('tempType', type).replace('tempSequence',
                sequence));
            window.location.href = finalUrl;
        }

        function openGradeStreamPSLEAnalysis(sequenceId, type) {
            var classId = getCurrentClassId();
            var baseUrl =
                "{{ route('assessment.grade-stream-psle-analysis', ['classId' => 'tempClassId', 'sequenceId' => 'tempSequenceId', 'type' => 'tempType']) }}";
            var finalUrl = withAssessmentContext(baseUrl.replace('tempClassId', classId).replace('tempSequenceId', sequenceId).replace('tempType',
                type));
            window.location.href = finalUrl;
        }

        function openSpecialNeedsAnalysis(sequenceId, type) {
            var classId = getCurrentClassId();
            var baseUrl =
                "{{ route('assessment.special-needs-analysis', ['classId' => 'tempClassId', 'sequenceId' => 'tempSequenceId', 'type' => 'tempType']) }}";
            var finalUrl = withAssessmentContext(baseUrl.replace('tempClassId', classId).replace('tempSequenceId', sequenceId).replace('tempType',
                type));
            window.location.href = finalUrl;
        }

        function openCAValueAdditionAnalysis(type, sequenceId) {
            var classId = getCurrentClassId();
            var baseUrl =
                "{{ route('assessment.generateValueAdditionAnalysis', ['classId' => 'tempClassId', 'type' => 'tempType', 'sequenceId' => 'tempSequenceId']) }}";
            var finalUrl = withAssessmentContext(baseUrl.replace('tempClassId', classId).replace('tempType', type).replace('tempSequenceId',
                sequenceId));
            window.location.href = finalUrl;
        }

        function openCompareValueAdditionAnalysis(type, sequenceId) {
            var classId = getCurrentClassId();
            var baseUrl =
                "{{ route('assessment.generateTestComparisonAnalysis', ['classId' => 'tempClassId', 'type' => 'tempType', 'sequenceId' => 'tempSequenceId']) }}";
            var finalUrl = withAssessmentContext(baseUrl.replace('tempClassId', classId).replace('tempType', type).replace('tempSequenceId',
                sequenceId));
            window.location.href = finalUrl;
        }

        function openCAGradeAnalysis(sequenceId) {
            var classId = getCurrentClassId();
            var baseUrl =
                "{{ route('assessment.overall-ca-grade-analysis', ['classId' => 'tempClassId', 'sequenceId' => 'tempSequenceId']) }}";
            var finalUrl = withAssessmentContext(baseUrl.replace('tempClassId', classId).replace('tempSequenceId', sequenceId));
            window.location.href = finalUrl;
        }

        function openCAValueAdditionGradeAnalysis(type, sequenceId) {
            var classId = getCurrentClassId();
            var baseUrl =
                "{{ route('assessment.generateValueAdditionAnalysisForGrade', ['classId' => 'tempClassId', 'type' => 'tempType', 'sequenceId' => 'tempSequenceId']) }}";
            var finalUrl = withAssessmentContext(baseUrl.replace('tempClassId', classId).replace('tempType', type).replace('tempSequenceId',
                sequenceId));
            window.location.href = finalUrl;
        }

        function openTestComparisonGradeAnalysis(type, sequenceId) {
            var classId = getCurrentClassId();
            var baseUrl =
                "{{ route('assessment.generateTestComparisonAnalysisForGrade', ['classId' => 'tempClassId', 'type' => 'tempType', 'sequenceId' => 'tempSequenceId']) }}";
            var finalUrl = withAssessmentContext(baseUrl.replace('tempClassId', classId).replace('tempType', type).replace('tempSequenceId',
                sequenceId));
            window.location.href = finalUrl;
        }

        function openExamHousesOverallAnalysisSimple(type, sequenceId) {
            try {
                var baseUrl10 =
                    "{{ route('assessment.exam-houses-overall-analysis-simple', ['type' => 'tempType', 'sequenceId' => 'tempSequenceId']) }}";
                var finalUrl = withAssessmentContext(baseUrl10.replace('tempType', type).replace('tempSequenceId', sequenceId));
                window.location.href = finalUrl;
            } catch (error) {
                console.error("An error occurred while opening the popup:", error);
            }
        }

        function openCAClassesAnalysis(sequenceId) {
            var classId = getCurrentClassId();
            var baseUrl =
                "{{ route('assessment.ca-classes-overall-analysis', ['classId' => 'tempClassId', 'sequence' => 'tempSequenceId']) }}";
            var finalUrl = withAssessmentContext(baseUrl.replace('tempClassId', classId).replace('tempSequenceId', sequenceId));

            window.location.href = finalUrl;
        }

        function openExamClassesAnalysis() {
            var classId = getCurrentClassId();
            var baseUrl =
                "{{ route('assessment.exam-classes-overall-analysis', ['classId' => 'tempClassId']) }}";
            var finalUrl = withAssessmentContext(baseUrl.replace('tempClassId', classId));
            window.location.href = finalUrl;
        }

        function openCAClassesAnalysisII(sequenceId) {
            var classId = getCurrentClassId();
            var baseUrl =
                "{{ route('assessment.ca-classes-overall-analysis-ii', ['classId' => 'tempClassId', 'sequence' => 'tempSequenceId']) }}";
            var finalUrl = withAssessmentContext(baseUrl.replace('tempClassId', classId).replace('tempSequenceId', sequenceId));
            window.location.href = finalUrl;
        }

        function openExamClassesAnalysisII() {
            var classId = getCurrentClassId();
            var baseUrl =
                "{{ route('assessment.exam-classes-overall-analysis-ii', ['classId' => 'tempClassId']) }}";
            var finalUrl = withAssessmentContext(baseUrl.replace('tempClassId', classId));
            window.location.href = finalUrl;
        }

        function openCAHouseAnalysis(sequenceId) {
            var baseUrl =
                "{{ route('assessment.ca-house-junior-analysis', ['sequenceId' => 'tempSequenceId']) }}";
            var finalUrl = withAssessmentContext(baseUrl.replace('tempSequenceId', sequenceId));
            window.location.href = finalUrl;
        }

        function openExamHouseAnalysis() {
            var baseUrl = withAssessmentContext("{{ route('assessment.exam-house-analysis') }}");
            window.location.href = baseUrl;
        }


        function openOverallExamHouseAnalysis() {
            var baseUrl = withAssessmentContext("{{ route('assessment.exam-houses-overall-analysis') }}");
            window.location.href = baseUrl;
        }

        function openOverallCAHouseAnalysis(sequenceId) {
            var baseUrl =
                "{{ route('assessment.ca-houses-overall-analysis', ['sequence' => 'tempSequence']) }}";
            var finalUrl = withAssessmentContext(baseUrl.replace('tempSequence', sequenceId));
            window.location.href = finalUrl;
        }

        function openGradeHouseAnalysis(type, sequenceId) {
            var classId = getCurrentClassId();
            var baseUrl =
                "{{ route('assessment.grade-houses-overall-analysis', ['classId' => 'tempClassId', 'type' => 'tempType', 'sequence' => 'tempSequenceId']) }}";
            var finalUrl = withAssessmentContext(baseUrl.replace('tempClassId', classId).replace('tempType', type).replace('tempSequenceId',
                sequenceId));
            window.location.href = finalUrl;
        }

        function openGradeHouseAnalysisSimple(type, sequenceId) {
            var classId = getCurrentClassId();
            var baseUrl =
                "{{ route('assessment.grade-houses-overall-analysis-simple', ['classId' => 'tempClassId', 'type' => 'tempType', 'sequence' => 'tempSequenceId']) }}";
            var finalUrl = withAssessmentContext(baseUrl.replace('tempClassId', classId).replace('tempType', type).replace('tempSequenceId',
                sequenceId));
            window.location.href = finalUrl;
        }

        function openGradeDepartmentAnalysis(sequenceId, type) {
            var classId = getCurrentClassId();
            var baseUrl =
                "{{ route('assessment.department-by-year-analysis', ['classId' => 'tempClassId', 'sequenceId' => 'tempSequenceId', 'type' => 'tempType']) }}";
            var finalUrl = withAssessmentContext(baseUrl.replace('tempClassId', classId).replace('tempSequenceId', sequenceId).replace('tempType',
                type));
            window.location.href = finalUrl;
        }

        function openCATeachersAnalysis(typeId, sequenceId) {
            var classId = getCurrentClassId();
            var baseUrl =
                "{{ route('assessment.subjects-ca-teachers-analysis', ['classId' => ':classId', 'type' => ':typeId', 'sequence' => ':sequenceId']) }}";
            var finalUrl = baseUrl
                .replace(':classId', classId)
                .replace(':typeId', typeId)
                .replace(':sequenceId', sequenceId);

            window.location.href = withAssessmentContext(finalUrl);
        }

        function openSubjectGradeDistributionAnalysis(type, sequence) {
            var classId = getCurrentClassId();
            var baseUrl =
                "{{ route('assessment.subject-grade-distribution-by-class', ['classId' => ':classId', 'type' => ':typeId', 'sequence' => ':sequenceId']) }}";
            var finalUrl = withAssessmentContext(baseUrl.replace(':classId', classId).replace(':typeId', type).replace(':sequenceId', sequence));
            window.location.href = finalUrl;
        }

        function openOverallGradeAnalysisExam() {
            var classId = getCurrentClassId();
            var baseUrl = "{{ route('assessment.overall-exam-grade-analysis', ['classId' => 'tempClassId']) }}";
            var finalUrl = withAssessmentContext(baseUrl.replace('tempClassId', classId));
            window.location.href = finalUrl;
        }

        function openOverallTeacherPerformanceByGrade(type, sequence) {
            var classId = getCurrentClassId();
            var baseUrl =
                "{{ route('assessment.subjects-overall-teachers-analysis', ['classId' => 'tempClassId', 'type' => 'tempType', 'sequence' => 'tempSequence']) }}";
            var finalUrl = withAssessmentContext(baseUrl.replace('tempClassId', classId).replace('tempType', type).replace('tempSequence',
                sequence));
            window.location.href = finalUrl;
        }

        function openGradeDistributionByGender(sequence, type) {
            var classId = getCurrentClassId();
            var baseUrl =
                "{{ route('assessment.grade-distribution-by-gender', ['classId' => 'tempClassId', 'sequence' => 'tempSequence', 'type' => 'tempType']) }}";
            var finalUrl = withAssessmentContext(baseUrl.replace('tempClassId', classId).replace('tempSequence', sequence).replace('tempType',
                type));
            window.location.href = finalUrl;
        }

        function openExamSubjectGradeAnalysis(type, sequence) {
            var classId = getCurrentClassId();
            var baseUrl =
                "{{ route('assessment.all-subjects-exam', ['classId' => 'tempClassId', 'type' => 'tempType', 'sequence' => 'tempSequence']) }}";
            var finalUrl = withAssessmentContext(baseUrl.replace('tempClassId', classId).replace('tempType', type).replace('tempSequence',
                sequence));
            window.location.href = finalUrl;
        }
    </script>
@endsection
