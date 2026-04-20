@extends('layouts.master')
@section('title')
    Attendance | Dashboard
@endsection

@section('css')
    <style>
        /* Term Selector Bar */
        .term-selector-bar {
            background: white;
            border-radius: 3px;
            padding: 12px 20px;
            margin-bottom: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .term-selector-bar .term-label {
            font-weight: 600;
            color: #374151;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .term-selector-bar .term-label i {
            color: #4e73df;
        }

        .term-selector-bar .form-select {
            min-width: 200px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            padding: 8px 12px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .term-selector-bar .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

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

        /* View Toggle - Segmented Control */
        .view-toggle {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .view-toggle .view-label {
            font-size: 14px;
            font-weight: 500;
            color: #374151;
        }

        .view-buttons {
            display: inline-flex;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            overflow: hidden;
        }

        .view-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            background: white;
            border: none;
            color: #6b7280;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .view-btn:not(:last-child) {
            border-right: 1px solid #d1d5db;
        }

        .view-btn:hover:not(.active) {
            background: #f3f4f6;
            color: #374151;
        }

        .view-btn.active {
            background: #3b82f6;
            color: white;
        }

        .view-btn i {
            font-size: 14px;
        }

        /* Reports Dropdown Styling - matching admissions */
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

        /* Class Buttons Styling */
        .button-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        .class-button {
            flex: 0 0 auto;
            min-width: 200px;
            height: 45px;
            white-space: normal;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px 16px;
            font-size: 13px;
            overflow: hidden;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            color: #374151;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .class-button:hover {
            border-color: #3b82f6;
            background: #f0f9ff;
            color: #1e40af;
        }

        .class-button.active {
            background: #3b82f6;
            border-color: #3b82f6;
            color: white;
        }

        /* Dropdown styling */
        #gradeId {
            border: 1px solid #d1d5db;
            border-radius: 3px;
            padding: 10px 12px;
            font-size: 14px;
        }

        #gradeId:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Quick Actions */
        .quick-actions {
            display: flex;
            gap: 12px;
        }

        .quick-action-btn {
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
            text-decoration: none;
        }

        .quick-action-btn:hover {
            border-color: #3b82f6;
            color: #1e40af;
            background: #f0f9ff;
        }

        .quick-action-btn i {
            font-size: 14px;
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

        .placeholder-input {
            width: 35px;
            height: 35px;
            background-color: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
        }

        .loading-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            border-radius: 3px;
            padding: 12px 16px;
            margin-bottom: 16px;
        }

        .loading-table {
            background: white;
            border-radius: 3px;
            padding: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .loading-table thead th {
            background: #f9fafb;
            padding: 10px 8px;
        }

        .loading-table tbody td {
            padding: 8px;
        }

        @media (max-width: 768px) {
            .settings-header {
                padding: 20px;
            }

            .settings-body {
                padding: 16px;
            }

            .term-selector-bar {
                flex-direction: column;
                gap: 12px;
                align-items: stretch;
            }

            .term-selector-bar .form-select {
                min-width: 100%;
            }

            .class-button {
                min-width: 100%;
            }

            .controls-row {
                flex-direction: column;
                gap: 12px;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Dashboard
        @endslot
        @slot('title')
            Attendance Register
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

    <!-- Term Selector Bar - Outside Header -->
    <div class="row">
        <div class="col-10"></div>
        <div class="col-2">
            <div class="d-flex flex-wrap align-items-center justify-content-end gap-2 mb-3">
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
            <h3><i class="fas fa-calendar-check me-2"></i>Attendance Management</h3>
            <p>Track and manage daily student attendance by class</p>
        </div>

        <div class="settings-body">
            <div class="help-text">
                <div class="help-title">About Attendance Tracking</div>
                <div class="help-content">
                    Select a class to view and record daily attendance. Click on attendance cells to toggle between
                    configured attendance codes.
                    Click column headers to set all students at once.
                </div>
            </div>

            <div class="d-flex justify-content-end align-items-center flex-wrap gap-3 mb-4 controls-row">
                <div class="d-flex align-items-center gap-3">
                    <!-- View Toggle - Segmented Control -->
                    <div class="view-toggle">
                        <div class="view-buttons">
                            <button type="button" class="view-btn active" data-view="tiles" title="Tiles View">
                                <i class="fas fa-th"></i>
                            </button>
                            <button type="button" class="view-btn" data-view="dropdown" title="Dropdown View">
                                <i class="fas fa-list"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Reports Dropdown - styled like admissions -->
                    <div class="btn-group reports-dropdown">
                        <button type="button" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-chart-bar me-2"></i>Reports<i class="fas fa-chevron-down ms-2"
                                style="font-size: 10px;"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" onclick="classAttendanceReport(); return false;" href="#">
                                    <i class="fas fa-calendar-check text-primary"></i> Termly Attendance by Codes
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" onclick="classAttendanceSummaryReport(); return false;"
                                    href="#">
                                    <i class="fas fa-clipboard-list text-purple"></i> Class Attendance Summary
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Class Container -->
            <div id="classContainer" class="mb-4">
                <!-- Class buttons or dropdown will be dynamically added here -->
            </div>

            <!-- Attendance List -->
            <div id="classList">
                <!-- Class list will be loaded here dynamically -->
            </div>

            <!-- Loading Placeholder -->
            <div id="loadingPlaceholder">
                <div class="loading-header placeholder-glow">
                    <span class="placeholder-item" style="width: 60%; height: 20px;"></span>
                </div>
                <div class="loading-table">
                    <table class="table table-striped table-sm mb-0" style="border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th colspan="6"></th>
                                <th><span class="placeholder-item" style="width: 20px; height: 20px;"></span></th>
                                <th colspan="5"><span class="placeholder-item" style="width: 100%; height: 20px;"></span>
                                </th>
                                <th colspan="5"><span class="placeholder-item" style="width: 100%; height: 20px;"></span>
                                </th>
                                <th><span class="placeholder-item" style="width: 20px; height: 20px;"></span></th>
                            </tr>
                            <tr>
                                <th>#</th>
                                <th>Entry</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Gender</th>
                                <th>Absent</th>
                                <th></th>
                                @for ($i = 0; $i < 10; $i++)
                                    <th><span class="placeholder-item" style="width: 20px; height: 20px;"></span></th>
                                @endfor
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @for ($i = 0; $i < 5; $i++)
                                <tr class="placeholder-glow">
                                    <td><span class="placeholder-item" style="width: 20px; height: 20px;"></span></td>
                                    <td><span class="placeholder-item" style="width: 20px; height: 20px;"></span></td>
                                    <td><span class="placeholder-item" style="width: 80px; height: 20px;"></span></td>
                                    <td><span class="placeholder-item" style="width: 80px; height: 20px;"></span></td>
                                    <td><span class="placeholder-item" style="width: 40px; height: 20px;"></span></td>
                                    <td><span class="placeholder-item" style="width: 60px; height: 20px;"></span></td>
                                    <td></td>
                                    @for ($j = 0; $j < 10; $j++)
                                        <td>
                                            <div class="placeholder-input"></div>
                                        </td>
                                    @endfor
                                    <td></td>
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
        // Dynamic attendance codes from database
        var attendanceCodesArray = @json($attendanceCodes->pluck('code')->toArray());
        var attendanceCodeColors = @json($attendanceCodes->pluck('color', 'code')->toArray());
        var attendanceCodesWithEmpty = attendanceCodesArray.concat(['']);

        // Pre-selected values from session (when redirected from direct class-list URL)
        var preSelectedClassId = @json($preSelectedClassId ?? null);
        var preSelectedWeekStart = @json($preSelectedWeekStart ?? null);

        // Apply color to attendance input based on value
        function applyAttendanceColor(input) {
            var value = $(input).val();
            if (value && attendanceCodeColors[value]) {
                $(input).css({
                    'background-color': attendanceCodeColors[value],
                    'color': 'white',
                    'font-weight': '600'
                });
            } else {
                $(input).css({
                    'background-color': '',
                    'color': '',
                    'font-weight': ''
                });
            }
        }

        // Apply colors to all attendance inputs
        function applyAllAttendanceColors() {
            $('.attendance-input').each(function() {
                applyAttendanceColor(this);
            });
        }

        $(document).ready(function() {
            initializeAttendance();

            $(document).on('click', '#prevWeek', function() {
                navigateWeek(-1);
            });

            $(document).on('click', '#nextWeek', function() {
                navigateWeek(1);
            });

            // Toggle attendance code - use namespace to prevent duplicate handlers
            $(document).off('click.attendanceInput').on('click.attendanceInput', '.attendance-input', function() {
                var currentOption = $(this).val();
                var nextIndex = (attendanceCodesWithEmpty.indexOf(currentOption) + 1) % attendanceCodesWithEmpty
                    .length;
                $(this).val(attendanceCodesWithEmpty[nextIndex]);
                applyAttendanceColor(this);
            });

            // Toggle attendance code for a specific day - use namespace to prevent duplicate handlers
            $(document).off('click.dayHeader').on('click.dayHeader', '.day-header', function() {
                var day = $(this).data('day');
                toggleAttendanceCode(day);
            });

            // Apply attendance colors after AJAX loads content
            $(document).ajaxComplete(function() {
                applyAllAttendanceColors();
                // Note: initAttendanceView() is already called in the AJAX success callback
                // Don't call it here to avoid timing issues with button state updates
            });
        });

        function initializeAttendance() {
            $('#termId').change(updateGrades);

            // View toggle button click handler
            $('.view-btn').click(function() {
                $('.view-btn').removeClass('active');
                $(this).addClass('active');
                var viewMode = $(this).data('view') === 'tiles' ? 'button' : 'dropdown';
                localStorage.setItem('viewMode', viewMode);
                updateGrades();
            });

            // Restore saved view mode
            var storedViewMode = localStorage.getItem('viewMode') || 'button';
            if (storedViewMode === 'button') {
                $('.view-btn[data-view="tiles"]').addClass('active');
                $('.view-btn[data-view="dropdown"]').removeClass('active');
            } else {
                $('.view-btn[data-view="dropdown"]').addClass('active');
                $('.view-btn[data-view="tiles"]').removeClass('active');
            }

            updateGrades();
        }

        function isTilesView() {
            return $('.view-btn[data-view="tiles"]').hasClass('active');
        }

        function updateGrades() {
            var termId = $('#termId').val();
            var getGradesUrl = "{{ route('attendance.get-grades-list') }}";

            $.ajax({
                url: getGradesUrl,
                type: 'GET',
                data: {
                    'term_id': termId
                },
                success: function(data) {
                    var $container = $('#classContainer');
                    $container.empty();

                    if (isTilesView()) {
                        createClassButtons(data, $container);
                    } else {
                        createClassDropdown(data, $container);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                }
            });
        }

        function createClassButtons(data, $container) {
            $container.removeClass('col-4').addClass('col-md-12');

            // Check if there are any classes
            var hasClasses = data && data.length > 0 && data.some(function(grade) {
                return grade.klasses && grade.klasses.length > 0;
            });

            if (!hasClasses) {
                $('#loadingPlaceholder').hide();
                $('#classList').html(`
                    <div class="text-center text-muted" style="padding: 40px 0;">
                        <i class="fas fa-chalkboard" style="font-size: 48px; opacity: 0.3;"></i>
                        <p class="mt-3 mb-0" style="font-size: 15px;">No Classes Available</p>
                    </div>
                `);
                return;
            }

            // Use pre-selected class ID from session if available, otherwise use localStorage
            var storedClassId = preSelectedClassId || localStorage.getItem('selectedClassIdA');
            var buttonContainer = $('<div class="button-container"></div>');
            var activeButtonSet = false;
            var firstButton = null;

            $.each(data, function(index, grade) {
                $.each(grade.klasses, function(index, klass) {
                    var button = $('<button></button>')
                        .addClass('class-button')
                        .attr('data-id', klass.id)
                        .html(wrapButtonText(klass.name + ' - ' + klass.teacher))
                        .click(function() {
                            $('.class-button').removeClass('active');
                            $(this).addClass('active');
                            localStorage.setItem('selectedClassIdA', klass.id);
                            // Use pre-selected week if this is the first load with pre-selected class
                            var weekToLoad = (preSelectedClassId == klass.id && preSelectedWeekStart) ?
                                preSelectedWeekStart : null;
                            updateClassAttendance(klass.id, weekToLoad);
                            // Clear pre-selected values after first use
                            preSelectedClassId = null;
                            preSelectedWeekStart = null;
                        });

                    if (klass.id == storedClassId) {
                        button.addClass('active');
                        activeButtonSet = true;
                    }

                    if (!firstButton) {
                        firstButton = button;
                    }

                    buttonContainer.append(button);
                });
            });

            $container.append(buttonContainer);

            if (!activeButtonSet && firstButton) {
                firstButton.addClass('active');
                localStorage.setItem('selectedClassIdA', firstButton.data('id'));
            }

            $('.class-button.active').trigger('click');
        }

        function updateClassAttendance(classId = null, weekStart = null) {
            var termId = $('#termId').val();
            var currentWeekStart = weekStart || $('#currentWeekStart').val();
            var currentClassId = classId || $('#classContainer .active').data('id') || $('#gradeId').val();

            var baseUrl =
                "{{ route('attendance.class-list', ['classId' => 'tempClassId', 'termId' => 'tempTermId', 'weekStart' => 'tempWeekStart']) }}";
            var attendanceClassUrl = baseUrl.replace('tempClassId', currentClassId)
                .replace('tempTermId', termId)
                .replace('tempWeekStart', encodeURIComponent(currentWeekStart));

            $.ajax({
                url: attendanceClassUrl,
                type: 'GET',
                success: function(data) {
                    $('#loadingPlaceholder').hide();
                    $('#classList').html(data).fadeIn(200, function() {
                        var tooltipTriggerList = [].slice.call(document.querySelectorAll(
                            '[data-bs-toggle="tooltip"]'));
                        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                            return new bootstrap.Tooltip(tooltipTriggerEl);
                        });
                        $('#currentWeekStart').val(currentWeekStart);

                        // Initialize attendance view (colors and button states)
                        if (typeof window.initAttendanceView === 'function') {
                            window.initAttendanceView();
                        }
                    });
                },
                error: function(xhr, status, error) {
                    $('#loadingPlaceholder').hide();
                    $('#classList').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            Failed to load attendance data. Please try again.
                        </div>
                    `);
                    console.error("Error loading attendance data:", error);
                }
            });
        }

        function createClassDropdown(data, $container) {
            // Check if there are any classes
            var hasClasses = data && data.length > 0 && data.some(function(grade) {
                return grade.klasses && grade.klasses.length > 0;
            });

            if (!hasClasses) {
                $('#loadingPlaceholder').hide();
                $('#classList').html(`
                    <div class="text-center text-muted" style="padding: 40px 0;">
                        <i class="fas fa-chalkboard" style="font-size: 48px; opacity: 0.3;"></i>
                        <p class="mt-3 mb-0" style="font-size: 15px;">No Classes Available</p>
                    </div>
                `);
                return;
            }

            // Use pre-selected class ID from session if available, otherwise use localStorage
            var storedClassId = preSelectedClassId || localStorage.getItem('selectedClassIdA');
            $container.removeClass('col-md-12').addClass('col-4');
            var select = $('<select></select>')
                .addClass('form-select')
                .attr('id', 'gradeId')
                .change(function() {
                    var selectedId = $(this).val();
                    localStorage.setItem('selectedClassIdA', selectedId);
                    // Use pre-selected week if this is the first load with pre-selected class
                    var weekToLoad = (preSelectedClassId == selectedId && preSelectedWeekStart) ? preSelectedWeekStart :
                        null;
                    updateClassAttendance(selectedId, weekToLoad);
                    // Clear pre-selected values after first use
                    preSelectedClassId = null;
                    preSelectedWeekStart = null;
                });

            $.each(data, function(index, grade) {
                var $optgroup = $('<optgroup>').attr('label', grade.name);
                $.each(grade.klasses, function(index, klass) {
                    var $option = $('<option>').val(klass.id).text(klass.name + ' - ' + klass.teacher);
                    if (klass.id == storedClassId) {
                        $option.prop('selected', true);
                    }
                    $optgroup.append($option);
                });
                select.append($optgroup);
            });
            $container.append(select);
            if (!select.val() && select.find('option').length > 0) {
                select.find('option:first').prop('selected', true);
            }
            select.trigger('change');
        }

        function wrapButtonText(text) {
            return '<div style="width: 100%; overflow: hidden; text-align: center;">' + text + '</div>';
        }

        function classAttendanceReport() {
            var classId = localStorage.getItem('selectedClassIdA');
            var baseUrl = "{{ route('attendance.class-attendance-report', ['classId' => '__CLASS_ID__']) }}";
            var classAttendanceUrl = baseUrl.replace('__CLASS_ID__', classId);
            window.location.href = classAttendanceUrl;
        }

        function classAttendanceSummaryReport() {
            var classId = localStorage.getItem('selectedClassIdA');
            var baseUrl = "{{ route('attendance.class-summary', ['classId' => '__CLASS_ID__']) }}";
            var classAttendanceUrl = baseUrl.replace('__CLASS_ID__', classId);
            window.location.href = classAttendanceUrl;
        }

        function toggleAttendanceCode(day) {
            var currentCodeIndex = 0;

            $('.day-' + day + '.attendance-input').each(function() {
                var $input = $(this);
                var currentValue = $input.val();

                if (currentValue === '') {
                    $input.val(attendanceCodesWithEmpty[0]);
                } else {
                    currentCodeIndex = attendanceCodesWithEmpty.indexOf(currentValue) + 1;
                    if (currentCodeIndex >= attendanceCodesWithEmpty.length) {
                        currentCodeIndex = 0;
                    }
                    $input.val(attendanceCodesWithEmpty[currentCodeIndex]);
                }
                applyAttendanceColor(this);
            });
        }

        function navigateWeek(direction) {
            var currentWeekStart = $('#currentWeekStart').val();
            var currentClassId = $('#classContainer .active').data('id') || $('#gradeId').val();

            $.ajax({
                url: "{{ route('attendance.navigate-week') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    currentWeekStart: currentWeekStart,
                    direction: direction,
                    classId: currentClassId,
                    is_ajax: true
                },
                success: function(response) {
                    if (response.success) {
                        updateClassAttendance(currentClassId, response.newWeekStart);
                    } else {
                        console.error('Error: Success flag is false', response);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                }
            });
        }
    </script>
@endsection
