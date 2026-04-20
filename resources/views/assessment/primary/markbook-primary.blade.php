@extends('layouts.master')
@section('title')
    Primary School Markbook
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

        /* Stats in Header */
        .header-stats {
            display: flex;
            gap: 24px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 700;
            line-height: 1;
        }

        .stat-label {
            font-size: 12px;
            opacity: 0.85;
            margin-top: 4px;
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

        /* View Toggle */
        .view-controls {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
        }

        .view-toggle {
            display: flex;
            align-items: center;
            gap: 8px;
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

        /* Term Selector */
        .term-selector select {
            min-width: 180px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            padding: 8px 12px;
            font-size: 14px;
        }

        .term-selector select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        /* Section Headers */
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title i {
            color: #3b82f6;
        }

        /* Class/Subject Tiles - Attendance Pattern */
        .tiles-container {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
        }

        .class-tile {
            min-width: 200px;
            height: 45px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 500;
            color: #374151;
            transition: all 0.2s ease;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .class-tile:hover {
            border-color: #3b82f6;
            background: #f0f9ff;
            color: #1e40af;
        }

        .class-tile.active {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border-color: transparent;
            color: white;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
        }

        /* Dropdown Container */
        .dropdown-container {
            max-width: 400px;
            margin-bottom: 20px;
        }

        .dropdown-container select {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            padding: 10px 14px;
            font-size: 14px;
        }

        .dropdown-container select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        /* Content Container */
        .content-container {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            min-height: 200px;
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
            padding: 20px;
        }

        .placeholder-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 12px 16px;
            border-radius: 3px;
            margin-bottom: 16px;
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

        /* Form validation */
        input.invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
            background-color: #fff5f5 !important;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .settings-header {
                padding: 20px;
            }

            .settings-body {
                padding: 16px;
            }

            .header-stats {
                flex-wrap: wrap;
                gap: 16px;
            }

            .view-controls {
                flex-direction: column;
                align-items: flex-start;
            }

            .class-tile {
                min-width: 100%;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Markbook & Analysis reports
        @endslot
        @slot('title')
            Markbook
        @endslot
    @endcomponent

    @if ($errors->any())
        <div class="row mb-3">
            <div class="col-12">
                @foreach ($errors->all() as $error)
                    <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        <i class="mdi mdi-block-helper label-icon"></i><strong>{{ $error }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="row mb-2">
        <div class="col-10"></div>
        <div class="col-2">
            <div class="term-selector">
                <select name="term" id="termId" class="form-select">
                    @if (!empty($terms))
                        @foreach ($terms as $term)
                            <option data-year="{{ $term->year }}"
                                value="{{ $term->id }}"{{ $term->id == session('selected_term_id', $currentTerm->id) ? 'selected' : '' }}>
                                {{ 'Term ' . $term->term . ', ' . $term->year }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="settings-container">
                <div class="settings-header">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h3><i class="fas fa-book-open me-2"></i>Primary Markbook</h3>
                            <p>Enter and manage student assessment scores</p>
                        </div>
                        <div class="col-md-6 d-flex justify-content-end">
                            <div class="header-stats">
                                <div class="stat-item">
                                    <div class="stat-value" id="subjectCount">-</div>
                                    <div class="stat-label">Allocations</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-value" id="studentCount">-</div>
                                    <div class="stat-label">Students</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="settings-body">
                    <div class="help-text">
                        <div class="help-title">Assessment Entry</div>
                        <div class="help-content">
                            Select a class subject to view and enter student marks. Use the view toggle to switch between
                            tiles and dropdown view.
                        </div>
                    </div>

                    <!-- View Controls -->
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

                    <!-- Class Subjects Section -->
                    <div class="section-header">
                        <div class="section-title">
                            <i class="bx bx-book-content"></i>
                            Class Subjects
                        </div>
                    </div>

                    <div id="classSubjectsContainer">
                        <!-- Class Subjects buttons or dropdown will be dynamically added here -->
                    </div>

                    <!-- Markbook Content -->
                    <div class="content-container">
                        <div id="studentTestList"></div>

                        <!-- Loading Placeholder -->
                        <div id="loadingPlaceholder" class="placeholder-card">
                            <div class="placeholder-header d-flex justify-content-between align-items-center">
                                <div class="shimmer-bg"
                                    style="width: 300px; height: 20px; background: rgba(255,255,255,0.3);"></div>
                            </div>
                            <div class="d-flex justify-content-end mb-3">
                                <div class="shimmer-bg" style="width: 80px; height: 36px;"></div>
                            </div>
                            <table class="placeholder-table">
                                <thead>
                                    <tr>
                                        <th>
                                            <div class="shimmer-bg" style="height: 18px; width: 30px;"></div>
                                        </th>
                                        <th>
                                            <div class="shimmer-bg" style="height: 18px; width: 80px;"></div>
                                        </th>
                                        <th>
                                            <div class="shimmer-bg" style="height: 18px; width: 80px;"></div>
                                        </th>
                                        <th>
                                            <div class="shimmer-bg" style="height: 18px; width: 40px;"></div>
                                        </th>
                                        <th>
                                            <div class="shimmer-bg" style="height: 18px; width: 60px;"></div>
                                        </th>
                                        <th>
                                            <div class="shimmer-bg" style="height: 18px; width: 60px;"></div>
                                        </th>
                                        <th>
                                            <div class="shimmer-bg" style="height: 18px; width: 60px;"></div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @for ($i = 0; $i < 8; $i++)
                                        <tr>
                                            <td>
                                                <div class="shimmer-bg" style="height: 16px; width: 20px;"></div>
                                            </td>
                                            <td>
                                                <div class="shimmer-bg" style="height: 16px; width: 80px;"></div>
                                            </td>
                                            <td>
                                                <div class="shimmer-bg" style="height: 16px; width: 80px;"></div>
                                            </td>
                                            <td>
                                                <div class="shimmer-bg" style="height: 16px; width: 30px;"></div>
                                            </td>
                                            <td>
                                                <div class="shimmer-bg" style="height: 16px; width: 50px;"></div>
                                            </td>
                                            <td>
                                                <div class="shimmer-bg" style="height: 16px; width: 50px;"></div>
                                            </td>
                                            <td>
                                                <div class="shimmer-bg" style="height: 16px; width: 50px;"></div>
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
        const markbookContext = @json($assessmentContext ?? $markbookCurrentContext ?? $schoolModeResolver->defaultAssessmentContext($resolvedSchoolMode));

        function withMarkbookContext(url) {
            if (!markbookContext) {
                return url;
            }

            const separator = url.includes('?') ? '&' : '?';
            return url + separator + 'context=' + encodeURIComponent(markbookContext);
        }

        $(document).ready(function() {
            initializeMarkbook();

            function validateScoreInputs() {
                let hasInvalidInputs = false;
                $('input[name*="[score]"]').each(function() {
                    const $input = $(this);
                    const value = $input.val().trim();
                    const outOf = parseFloat($input.attr('placeholder')) || 0;

                    $input.removeClass('invalid');
                    if (value === '') return;

                    const score = parseFloat(value);
                    if (isNaN(score) || score < 0 || score > outOf) {
                        $input.addClass('invalid');
                        hasInvalidInputs = true;
                    }
                });

                $('button[type="submit"]').toggle(!hasInvalidInputs);
                return !hasInvalidInputs;
            }

            $(document).on('input keyup blur', 'input[name*="[score]"]', function() {
                clearTimeout(window.validationTimeout);
                window.validationTimeout = setTimeout(validateScoreInputs, 200);
            });

            $(document).on('input', 'input[name*="[score]"]', function() {
                let value = $(this).val();

                value = value.replace(/[^0-9.]/g, '');
                const parts = value.split('.');
                if (parts.length > 2) {
                    value = parts[0] + '.' + parts.slice(1).join('');
                }

                if (parts[1] && parts[1].length > 2) {
                    value = parts[0] + '.' + parts[1].substring(0, 2);
                }

                $(this).val(value);
            });

            setTimeout(validateScoreInputs, 1000);
        });

        function initializeMarkbook() {
            $('#termId').on('change', function() {
                var termId = $(this).val();
                var updateTermUrl = "{{ route('assessment.update-term') }}";

                // Clear localStorage selections when term changes (IDs are term-specific)
                localStorage.removeItem('selectedClassId');
                localStorage.removeItem('selectedOptionId');

                // Update the session term first, then fetch subjects
                $.ajax({
                    url: updateTermUrl,
                    type: 'POST',
                    data: {
                        termId: termId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        updateSubjectLists();
                    },
                    error: function(xhr, status, error) {
                        console.error('Failed to update term session:', error);
                        // Still try to update the list even if session update fails
                        updateSubjectLists();
                    }
                });
            });

            // View toggle button click handler
            $('.view-toggle-buttons .view-btn').on('click', function() {
                $('.view-toggle-buttons .view-btn').removeClass('active');
                $(this).addClass('active');
                var viewMode = $(this).data('view') === 'tiles' ? 'button' : 'dropdown';
                localStorage.setItem('viewMode', viewMode);
                updateSubjectLists();
            });

            // Restore saved view mode
            var storedViewMode = localStorage.getItem('viewMode') || 'button';
            if (storedViewMode === 'button') {
                $('.view-btn[data-view="tiles"]').addClass('active');
                $('.view-btn[data-view="list"]').removeClass('active');
            } else {
                $('.view-btn[data-view="list"]').addClass('active');
                $('.view-btn[data-view="tiles"]').removeClass('active');
            }

            updateSubjectLists();
        }

        function isTilesView() {
            return $('.view-btn[data-view="tiles"]').hasClass('active');
        }

        function updateSubjectLists() {
            var fetchClassesUrl = withMarkbookContext("{{ route('assessment.fetch-classes') }}");
            $.ajax({
                url: fetchClassesUrl,
                method: 'GET',
                success: function(data) {
                    // Update subject count
                    $('#subjectCount').text(data.length);

                    var isButtonView = isTilesView();
                    updateClassSubjects(data, isButtonView);
                    selectDefaultSubject();
                },
                error: function(xhr, status, error) {
                    console.error('Failed to update subject list:', status, error);
                    alert('Failed to update subject list. Please try again.');
                }
            });
        }

        function updateClassSubjects(data, isButtonView) {
            var $container = $('#classSubjectsContainer');
            $container.empty();

            if (!data || data.length === 0) {
                $('#loadingPlaceholder').hide();
                $('.content-container').hide();
                $container.html(`
                    <div class="text-center text-muted" style="padding: 40px 0;">
                        <i class="fas fa-book-open" style="font-size: 48px; opacity: 0.3;"></i>
                        <p class="mt-3 mb-0" style="font-size: 15px;">No Classes Available</p>
                    </div>
                `);
                return;
            }

            $('.content-container').show();

            if (isButtonView) {
                createClassButtons(data, $container);
            } else {
                createClassDropdown(data, $container);
            }
        }

        function createClassButtons(data, $container) {
            var storedClassId = localStorage.getItem('selectedClassId');
            var tilesContainer = $('<div class="tiles-container"></div>');

            var firstItemSelected = false;
            var selectedItemId = null;

            data.forEach(function(item, index) {
                var button = $('<button></button>')
                    .addClass('class-tile')
                    .attr('data-id', item.id)
                    .attr('data-student-count', item.student_count || 0)
                    .html(item.klass_name + ' - ' + item.subject_name + ' - ' + item.subject_teacher)
                    .on('click', function() {
                        handleClassSelection($(this));
                    });

                if (item.id == storedClassId || (!storedClassId && index === 0)) {
                    button.addClass('active');
                    localStorage.setItem('selectedClassId', item.id);
                    firstItemSelected = true;
                    selectedItemId = item.id;
                    // Update student count
                    $('#studentCount').text(item.student_count || '-');
                }
                tilesContainer.append(button);
            });

            $container.append(tilesContainer);
            if (!firstItemSelected && data.length > 0) {
                var firstButton = tilesContainer.find('.class-tile:first');
                firstButton.addClass('active');
                selectedItemId = data[0].id;
                localStorage.setItem('selectedClassId', selectedItemId);
                $('#studentCount').text(data[0].student_count || '-');
            }

            if (selectedItemId) {
                updateClassLists(selectedItemId);
            }
        }

        function createClassDropdown(data, $container) {
            var storedClassId = localStorage.getItem('selectedClassId');
            var dropdownContainer = $('<div class="dropdown-container"></div>');
            var select = $('<select></select>')
                .addClass('form-select')
                .attr('id', 'subjectId')
                .on('change', function() {
                    handleClassSelection($(this));
                });

            select.append($('<option></option>').val('').text('Select class subject...'));

            var firstItemSelected = false;
            var selectedItemId = null;

            data.forEach(function(item, index) {
                var option = $('<option></option>')
                    .val(item.id)
                    .attr('data-student-count', item.student_count || 0)
                    .text(item.klass_name + ' - ' + item.subject_name + ' - ' + item.subject_teacher + ' (' + item
                        .student_count + ')');

                if (item.id == storedClassId || (!storedClassId && index === 0)) {
                    option.prop('selected', true);
                    localStorage.setItem('selectedClassId', item.id);
                    firstItemSelected = true;
                    selectedItemId = item.id;
                    $('#studentCount').text(item.student_count || '-');
                }
                select.append(option);
            });

            dropdownContainer.append(select);
            $container.append(dropdownContainer);

            if (!firstItemSelected && data.length > 0) {
                var firstOption = select.find('option:not(:first)').first();
                firstOption.prop('selected', true);
                selectedItemId = firstOption.val();
                localStorage.setItem('selectedClassId', selectedItemId);
                $('#studentCount').text(firstOption.data('student-count') || '-');
            }

            if (selectedItemId) {
                updateClassLists(selectedItemId);
            }
        }

        function selectDefaultSubject(data) {
            var storedClassId = localStorage.getItem('selectedClassId');
            if (storedClassId && data.some(item => item.id == storedClassId)) {
                selectClassSubject(storedClassId);
            } else if (data.length > 0) {
                localStorage.setItem('selectedClassId', data[0].id);
                selectClassSubject(data[0].id);
            }
        }

        function selectClassSubject(classId) {
            if (isTilesView()) {
                $('.class-tile[data-id="' + classId + '"]').addClass('active');
                updateClassLists(classId);
            } else {
                $('#subjectId').val(classId).change();
            }
        }

        function selectFirstClassSubject() {
            if (isTilesView()) {
                $('.class-tile:first').click();
            } else {
                var $firstOption = $('#subjectId option:not(:first)').first();
                if ($firstOption.length) {
                    $('#subjectId').val($firstOption.val()).change();
                }
            }
        }

        function handleClassSelection(element) {
            var selectedId = element.is('select') ? element.val() : element.data('id');
            var studentCount = element.is('select') ?
                element.find('option:selected').data('student-count') :
                element.data('student-count');

            if (selectedId) {
                $('.class-tile').removeClass('active');
                $('#subjectId').val(selectedId);
                element.addClass('active');

                localStorage.setItem('selectedClassId', selectedId);
                $('#studentCount').text(studentCount || '-');

                updateClassLists(selectedId);
            } else {
                localStorage.removeItem('selectedClassId');
                selectFirstClassSubject();
            }
        }

        function updateClassLists(subjectId) {
            var baseUrl = "{{ route('assessment.selected-subject', ['subjectId' => 'tempSubjectId']) }}";
            var klassUrl = withMarkbookContext(baseUrl.replace('tempSubjectId', subjectId));
            $.ajax({
                url: klassUrl,
                type: 'GET',
                success: function(data) {
                    $('#loadingPlaceholder').hide();
                    reinitializeTable(data);
                    localStorage.setItem('selectedClassId', subjectId);
                },
                error: function(xhr, status, error) {
                    console.error("Error loading class data:", error);
                    $('#loadingPlaceholder').fadeOut(200, function() {
                        $('#studentTestList').html(`
                        <div class="alert alert-danger m-3">
                            <i class="bx bx-error-circle me-2"></i>
                            Failed to load class data. Please try again.
                        </div>
                    `).fadeIn(200);
                    });
                }
            });
        }

        function initializeDataTable() {
            if ($.fn.DataTable.isDataTable('#markbook-class')) {
                $('#markbook-class').DataTable().destroy();
            }
            $('#markbook-class').DataTable({
                "paging": false,
                "ordering": true,
                "order": [
                    [1, 'asc']
                ],
                "info": false,
                "searching": false,
                "columnDefs": [{
                    "orderable": false,
                    "targets": '_all'
                }],
                "language": {
                    "emptyTable": "No data available"
                },
            });
        }

        function reinitializeTable(data) {
            var $container = $('#studentTestList');
            $container.empty();
            $container.html(data);

            if ($('#markbook-class').length) {
                initializeDataTable();
            }
        }

        function openPopup(id) {
            try {
                const sanitizedId = encodeURIComponent(id);
                const urlTemplate = "{{ route('assessment.testing', ':id') }}";
                const url = withMarkbookContext(urlTemplate.replace(':id', sanitizedId));
                window.location.href = url;
            } catch (error) {
                console.error("An error occurred while opening the popup:", error);
            }
        }

        function openPopupAll(id) {
            try {
                const sanitizedId = encodeURIComponent(id);
                const urlTemplate = "{{ route('assessment.grade-wide-assessment', ':id') }}";
                const url = withMarkbookContext(urlTemplate.replace(':id', sanitizedId));
                window.location.href = url;
            } catch (error) {
                console.error("An error occurred while opening the popup:", error);
            }
        }

        function openPopupGradeSubject(id) {
            try {
                const sanitizedId = encodeURIComponent(id);
                const urlTemplate = "{{ route('assessment.grade-subject-wide-assessment', ':id') }}";
                const url = withMarkbookContext(urlTemplate.replace(':id', sanitizedId));
                window.location.href = url;
            } catch (error) {
                console.error("An error occurred while opening the popup:", error);
            }
        }
    </script>
@endsection
