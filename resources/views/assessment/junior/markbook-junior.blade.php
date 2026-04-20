@extends('layouts.master')
@section('title')
    Markbook
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

        .settings-body {
            padding: 24px;
        }

        /* Stats */
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

        /* Controls Row */
        .controls-row {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: center;
            margin-bottom: 20px;
        }

        .controls-row .form-select {
            border: 1px solid #d1d5db;
            border-radius: 3px;
            padding: 8px 12px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .controls-row .form-select:focus {
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

        /* Section Title */
        .section-title {
            font-size: 15px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title i {
            color: #3b82f6;
        }

        /* Tiles Container */
        .tiles-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        /* Class/Subject Tiles - Attendance Pattern */
        .class-tile {
            width: 240px;
            height: 36px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 8px 12px;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            transition: all 0.2s ease;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
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

        /* Optional Subject Tiles */
        .option-tile {
            width: 240px;
            height: 36px;
            background: white;
            border: 1px solid #10b981;
            border-radius: 3px;
            padding: 8px 12px;
            font-size: 14px;
            font-weight: 500;
            color: #059669;
            transition: all 0.2s ease;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .option-tile:hover {
            border-color: #059669;
            background: #ecfdf5;
            color: #047857;
        }

        .option-tile.active {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-color: transparent;
            color: white;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
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

        /* Invalid Input */
        input.invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
            background-color: #fff5f5 !important;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .stat-item h4 {
                font-size: 1.25rem;
            }

            .stat-item small {
                font-size: 0.75rem;
            }

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

            .class-tile,
            .option-tile {
                width: 100%;
            }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Assessment
        @endslot
        @slot('title')
            Markbook
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
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;"><i class="fas fa-book-open me-2"></i>Markbook</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Enter and manage student assessment scores</p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white" id="totalSubjects">-</h4>
                                <small class="opacity-75">Core Subject Classes</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white" id="totalOptional">
                                    {{ $optional_subjects->flatten()->count() ?? 0 }}</h4>
                                <small class="opacity-75">Optional Subject Classes</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white" id="selectedCount">-</h4>
                                <small class="opacity-75">Students</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="settings-body">
            <div class="help-text">
                <div class="help-title">Assessment Entry</div>
                <div class="help-content">
                    Select a class subject to enter or edit student marks. Use the tiles view for quick navigation
                    or switch to dropdown view for a compact list. Changes are saved when you click the save button.
                </div>
            </div>

            <div class="row">
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
            <div id="classSubjectsSection">
                <div class="section-title">
                    <i class="bx bx-book-content"></i> Class Subjects
                </div>
                <div id="classSubjectsContainer" class="tiles-container">
                    <!-- Class subject tiles will be dynamically added here -->
                </div>
            </div>

            <!-- Optional Subjects Section -->
            @if ($schoolType->type !== 'Primary')
                <div id="optionalSubjectsSection">
                    <div class="section-title">
                        <i class="bx bx-book-open"></i> Optional Subjects
                    </div>
                    <div id="optionalSubjectsContainer" class="tiles-container">
                        <!-- Optional subject tiles will be rendered here -->
                    </div>
                </div>
            @endif

            <!-- Student Test List -->
            <div id="studentTestList" class="mt-4">
                <!-- Markbook table will be loaded here -->
            </div>

            <!-- Loading Placeholder -->
            <div id="loadingPlaceholder" class="mt-4">
                <div class="placeholder-card">
                    <!-- Tiles Placeholder -->
                    <div class="d-flex flex-wrap mb-4" style="gap: 10px;">
                        @for ($i = 0; $i < 6; $i++)
                            <div class="shimmer-bg" style="width: 200px; height: 45px;"></div>
                        @endfor
                    </div>

                    <!-- Header Placeholder -->
                    <div class="shimmer-bg mb-3" style="width: 100%; height: 50px;"></div>

                    <!-- Table Placeholder -->
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
            $('#termId').change(function() {
                var termId = $(this).val();
                var updateTermUrl = "{{ route('assessment.update-term') }}";

                localStorage.removeItem('selectedClassId');
                localStorage.removeItem('selectedOptionId');
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
                        updateSubjectLists();
                    }
                });
            });

            $('.view-toggle-buttons .view-btn').click(function() {
                $('.view-toggle-buttons .view-btn').removeClass('active');
                $(this).addClass('active');
                var viewMode = $(this).data('view') === 'tiles' ? 'button' : 'dropdown';
                localStorage.setItem('viewMode', viewMode);
                updateSubjectLists();
            });

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

        function isButtonView() {
            return $('.view-btn[data-view="tiles"]').hasClass('active');
        }

        function updateSubjectLists() {
            var fetchClassesUrl = withMarkbookContext("{{ route('assessment.fetch-classes') }}");
            $.get(fetchClassesUrl)
                .done(function(data) {
                    // Update stats
                    $('#totalSubjects').text(data.length || 0);

                    var isTilesView = isButtonView();
                    updateClassSubjects(data, isTilesView);
                    updateOptionalSubjects(isTilesView);

                    selectDefaultSubject();
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    console.error('Failed to update subject list:', textStatus, errorThrown);
                    $('#loadingPlaceholder').hide();
                    $('#classSubjectsContainer').html(
                        '<div class="alert alert-warning"><i class="bx bx-error me-2"></i>Failed to load subjects. Please try again.</div>'
                    );
                });
        }

        function updateClassSubjects(data, isButtonView) {
            var $container = $('#classSubjectsContainer');
            var $section = $('#classSubjectsSection');
            $container.empty();

            if (!data || data.length === 0) {
                // Hide the entire Class Subjects section if no data
                $section.hide();
                return;
            }

            // Show section and student list when there's data
            $section.show();
            $('#studentTestList').show();

            if (isButtonView) {
                createClassButtons(data, $container);
            } else {
                createClassDropdown(data, $container);
            }
        }

        function updateOptionalSubjects(isButtonView) {
            var $container = $('#optionalSubjectsContainer');
            if (!$container.length) return;

            $container.empty();

            if (isButtonView) {
                createOptionalButtons($container);
            } else {
                createOptionalDropdown($container);
            }
        }

        function decodeHtml(text) {
            if (!text) return '';
            return text.replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"');
        }

        function formatName(name) {
            if (!name) return 'N/A';
            // Extract lastname (last word) and format it properly
            var parts = name.trim().split(/\s+/);
            var lastname = parts[parts.length - 1];
            return lastname.charAt(0).toUpperCase() + lastname.slice(1).toLowerCase();
        }

        function createClassButtons(data, $container) {
            var storedClassId = localStorage.getItem('selectedClassId');

            data.forEach(function(item) {
                var tile = $('<button></button>')
                    .addClass('class-tile')
                    .attr('data-id', item.id)
                    .attr('data-count', item.student_count || 0)
                    .text(formatName(item.subject_teacher) + ' - ' + item.klass_name + ' - ' + decodeHtml(item
                        .subject_name))
                    .click(function() {
                        handleClassSelection($(this));
                    });

                if (item.id == storedClassId) {
                    tile.addClass('active');
                }
                $container.append(tile);
            });
        }

        function createClassDropdown(data, $container) {
            var storedClassId = localStorage.getItem('selectedClassId');
            var select = $('<select></select>')
                .addClass('form-select')
                .attr('id', 'subjectId')
                .css('max-width', '400px')
                .change(function() {
                    handleClassSelection($(this));
                });

            select.append($('<option></option>').val('').text('Select class subject...'));

            data.forEach(function(item) {
                var option = $('<option></option>')
                    .val(item.id)
                    .attr('data-count', item.student_count || 0)
                    .text(formatName(item.subject_teacher) + ' - ' + item.klass_name + ' - ' + decodeHtml(item
                        .subject_name) + ' (' + item.student_count + ')');

                if (item.id == storedClassId) {
                    option.prop('selected', true);
                }

                select.append(option);
            });

            $container.append(select);
        }

        function createOptionalButtons($container) {
            @if ($schoolType->type !== 'Primary')
                var storedOptionId = localStorage.getItem('selectedOptionId');
                var $section = $('#optionalSubjectsSection');

                @foreach ($optional_subjects->flatten() as $option)
                    @can('assessOptions', $option)
                        var tile = $('<button></button>')
                            .addClass('option-tile')
                            .attr('data-id', '{{ $option->id }}')
                            .attr('data-count', '{{ $option->students->count() ?? 0 }}')
                            .text('{!! ucfirst(strtolower($option->teacher->lastname ?? 'N/A')) !!} - {!! html_entity_decode($option->name) !!}')
                            .click(function() {
                                handleOptionalSelection($(this));
                            });

                        if ('{{ $option->id }}' == storedOptionId) {
                            tile.addClass('active');
                        }

                        $container.append(tile);
                    @endcan
                @endforeach

                // Hide the entire Optional Subjects section if no optional subjects
                if ($container.children().length === 0) {
                    $section.hide();
                } else {
                    $section.show();
                }
            @endif
        }

        function createOptionalDropdown($container) {
            @if ($schoolType->type !== 'Primary')
                var storedOptionId = localStorage.getItem('selectedOptionId');
                var $section = $('#optionalSubjectsSection');
                var select = $('<select></select>')
                    .addClass('form-select')
                    .attr('id', 'optionId')
                    .css('max-width', '400px')
                    .change(function() {
                        handleOptionalSelection($(this));
                    });

                select.append($('<option></option>').val('').text('Select optional subject...'));

                var optionCount = 0;
                @foreach ($optional_subjects->flatten() as $option)
                    @can('assessOptions', $option)
                        var option = $('<option></option>')
                            .val('{{ $option->id }}')
                            .attr('data-count', '{{ $option->students->count() ?? 0 }}')
                            .text('{!! ucfirst(strtolower($option->teacher->lastname ?? 'N/A')) !!} - {!! html_entity_decode($option->name) !!}');

                        if ('{{ $option->id }}' == storedOptionId) {
                            option.prop('selected', true);
                        }

                        select.append(option);
                        optionCount++;
                    @endcan
                @endforeach

                // Hide the entire Optional Subjects section if no optional subjects
                if (optionCount === 0) {
                    $section.hide();
                } else {
                    $section.show();
                    $container.append(select);
                }
            @endif
        }

        function selectDefaultSubject() {
            var storedClassId = localStorage.getItem('selectedClassId');
            var storedOptionId = localStorage.getItem('selectedOptionId');

            if (storedOptionId) {
                selectOptionalSubject(storedOptionId);
            } else if (storedClassId) {
                selectClassSubject(storedClassId);
            } else {
                selectFirstClassSubject();
            }
        }

        function selectOptionalSubject(optionId) {
            if (isButtonView()) {
                $('.option-tile[data-id="' + optionId + '"]').click();
            } else {
                $('#optionId').val(optionId).change();
            }
        }

        function selectClassSubject(classId) {
            if (isButtonView()) {
                $('.class-tile[data-id="' + classId + '"]').click();
            } else {
                $('#subjectId').val(classId).change();
            }
        }

        function selectFirstClassSubject() {
            if (isButtonView()) {
                // Try class subjects first, then fall back to optional subjects
                if ($('.class-tile').length > 0) {
                    $('.class-tile:first').click();
                } else if ($('.option-tile').length > 0) {
                    $('.option-tile:first').click();
                }
            } else {
                var $firstClassOption = $('#subjectId option:not(:first)').first();
                if ($firstClassOption.length) {
                    $('#subjectId').val($firstClassOption.val()).change();
                } else {
                    // Fall back to optional subjects if no class subjects
                    var $firstOptionalOption = $('#optionId option:not(:first)').first();
                    if ($firstOptionalOption.length) {
                        $('#optionId').val($firstOptionalOption.val()).change();
                    }
                }
            }
        }

        function handleClassSelection(element) {
            var selectedId = element.is('select') ? element.val() : element.data('id');
            var studentCount = element.is('select') ?
                element.find('option:selected').data('count') :
                element.data('count');

            if (selectedId) {
                $('.class-tile').removeClass('active');
                $('.option-tile').removeClass('active');
                $('#subjectId').val(selectedId);
                $('#optionId').val('');
                element.addClass('active');

                localStorage.setItem('selectedClassId', selectedId);
                localStorage.removeItem('selectedOptionId');
                $('#selectedCount').text(studentCount || '-');

                updateClassLists(selectedId);
            } else {
                localStorage.removeItem('selectedClassId');
            }
        }

        function handleOptionalSelection(element) {
            var selectedId = element.is('select') ? element.val() : element.data('id');
            var studentCount = element.is('select') ?
                element.find('option:selected').data('count') :
                element.data('count');

            if (selectedId) {
                $('.option-tile').removeClass('active');
                $('.class-tile').removeClass('active');
                $('#optionId').val(selectedId);
                $('#subjectId').val('');
                element.addClass('active');

                localStorage.setItem('selectedOptionId', selectedId);
                localStorage.removeItem('selectedClassId');
                $('#selectedCount').text(studentCount || '-');

                updateOptionLists(selectedId);
            } else {
                localStorage.removeItem('selectedOptionId');
            }
        }

        function updateClassLists(subjectId) {
            var baseUrl = "{{ route('assessment.selected-subject', ['subjectId' => 'tempSubjectId']) }}";
            var klassUrl = withMarkbookContext(baseUrl.replace('tempSubjectId', subjectId));

            $('#studentTestList').html(
                '<div class="text-center py-4"><i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> Loading markbook...</div>'
            );

            $.ajax({
                url: klassUrl,
                type: 'GET',
                success: function(data) {
                    $('#loadingPlaceholder').hide();
                    reinitializeTable(data);
                },
                error: function(xhr, status, error) {
                    console.error("Error loading class data:", error);
                    $('#loadingPlaceholder').hide();

                    var isForbidden = xhr && xhr.status === 403;
                    var message = isForbidden ?
                        'You are not authorized to access this class markbook.' :
                        'Failed to load class data. Please try again.';

                    if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }

                    if (isForbidden) {
                        localStorage.removeItem('selectedClassId');
                        $('.class-tile').removeClass('active');
                        $('#subjectId').val('');
                        $('#selectedCount').text('-');
                    }

                    $('#studentTestList').html(
                        '<div class="alert alert-' + (isForbidden ? 'warning' : 'danger') +
                        '"><i class="bx bx-error-circle me-2"></i>' + message + '</div>'
                    );
                }
            });
        }

        function updateOptionLists(optionId) {
            var baseOptionUrl = "{{ route('assessment.option-markbook', ['subjectId' => 'tempSubjectId']) }}";
            var optionKlassUrl = withMarkbookContext(baseOptionUrl.replace('tempSubjectId', optionId));

            $('#studentTestList').html(
                '<div class="text-center py-4"><i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> Loading markbook...</div>'
            );

            $.ajax({
                url: optionKlassUrl,
                type: 'GET',
                success: function(data) {
                    $('#loadingPlaceholder').hide();
                    reinitializeTable(data);
                },
                error: function(xhr, status, error) {
                    console.error("Error loading optional subject data:", error);
                    $('#loadingPlaceholder').hide();

                    var isForbidden = xhr && xhr.status === 403;
                    var message = isForbidden ?
                        'You are not authorized to access this optional subject markbook.' :
                        'Failed to load optional subject data. Please try again.';

                    if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }

                    if (isForbidden) {
                        localStorage.removeItem('selectedOptionId');
                        $('.option-tile').removeClass('active');
                        $('#optionId').val('');
                        $('#selectedCount').text('-');
                    }

                    $('#studentTestList').html(
                        '<div class="alert alert-' + (isForbidden ? 'warning' : 'danger') +
                        '"><i class="bx bx-error-circle me-2"></i>' + message + '</div>'
                    );
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

            $('[data-bs-toggle="tooltip"]').tooltip();

            // Initialize scroll progress bar after DataTables has modified the DOM
            // Multiple calls to ensure it catches the right moment
            if (typeof window.initScrollProgress === 'function') {
                window.initScrollProgress();
                setTimeout(window.initScrollProgress, 100);
                setTimeout(window.initScrollProgress, 300);
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
