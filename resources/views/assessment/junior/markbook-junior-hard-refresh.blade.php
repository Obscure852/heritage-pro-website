@extends('layouts.master')
@section('title')
    Markbook
@endsection
@section('css')
    <style>
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
            background-color: #e9ecef;
            border-radius: 4px;
        }

        .placeholder-input {
            width: 40px;
            height: 31px;
            background-color: #e9ecef;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }

        .placeholder-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 0.5rem;
            border-radius: 3px;
            margin: 2px;
        }

        .button-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        .class-button {
            flex: 0 0 auto;
            width: 250px;
            height: 30px;
            white-space: normal;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: flex-center;
            padding: 5px 10px;
            font-size: 0.875rem;
            overflow: hidden;
        }

        .option-button {
            flex: 0 0 auto;
            width: 250px;
            height: 45px;
            white-space: normal;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: flex-center;
            padding: 5px 10px;
            font-size: 0.875rem;
            overflow: hidden;
        }

        .class-button.active {
            background-color: #0056b3;
            border-color: #0056b3;
            color: white;
        }

        .option-button {
            background-color: #FFFFFF;
            border-color: #28a745;
        }

        .option-button.active {
            background-color: #218838;
            border-color: #1e7e34;
            color: white;
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
    <div class="row align-items-center">
        <div class="col-md-12 d-flex justify-content-end">
            <div style="padding-top: 2px;padding-right:5px;" class="d-flex justify-content-end">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="viewToggle" checked>
                    <label class="form-check-label" for="viewToggle"><span id="viewLabel">Tiles View</span></label>
                </div>
            </div>
            <div>
                <select name="term" id="termId" class="form-select form-select-sm">
                    @if (!empty($terms))
                        @foreach ($terms as $term)
                            <option data-year="{{ $term->year }}"
                                value="{{ $term->id }}"{{ $term->id == session('selected_term_id', $currentTerm->id) ? 'selected' : '' }}>
                                {{ 'Term ' . $term->term . ',' . $term->year }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
        </div>
    </div>
    @if (session('message'))
        <div class="row mt-4">
            <div class="col-md-11">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="row mb-4">
            <div class="col-md-10">
                <ul style="list-style-type: none;">
                    <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        @foreach ($errors->all() as $error)
                            <li> <i class="mdi mdi-block-helper label-icon"></i><strong>{{ $error }}</strong></li>
                        @endforeach
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </ul>
            </div>
        </div>
    @endif

    <div class="row">
        <div id="classSubjectsSection">
            <h6>Class Subjects</h6>
            <div class="col-md-12">
                <div id="classSubjectsContainer">
                    <!-- Class Subjects buttons or dropdown will be dynamically added here -->
                </div>
            </div>
        </div>
        @if ($schoolType->type !== 'Primary')
            <div id="optionalSubjectsSection" class="mt-4">
                <h6>Optional Subjects</h6>
                <div class="col-md-12">
                    <div id="optionalSubjectsContainer">
                        <!-- Optional Subjects buttons or dropdown will be dynamically added here -->
                    </div>
                </div>
            </div>
        @endif
    </div>
    <div class="row mt-2">
        <div id="studentTestList" class="col-md-12">
        </div>
    </div>
    <div id="loadingPlaceholder">
        <div class="col-md-11">
            <div class="placeholder-header placeholder-glow">
                <span class="placeholder-item" style="width: 70%; height: 20px;"></span>
            </div>
            <br>
            <form>
                <div class="row mb-4">
                    <div class="col-md-12 d-flex justify-content-end">
                        <span class="placeholder-item" style="width: 40px; height: 31px;"></span>
                    </div>
                </div>
                <table class="table table-sm rounded table-striped table-bordered">
                    <thead>
                        <tr>
                            <th style="width:30px;">#</th>
                            <th style="width:80px;">Firstname</th>
                            <th style="width:80px;">Lastname</th>
                            <th style="width:30px;">Gender</th>
                            <th style="width:30px;">Class</th>
                            @for ($i = 0; $i < 3; $i++)
                                <th style="width: 80px; background-color: #5156BE;"></th>
                                <th style="width: 30px;"></th>
                                <th style="width: 35px;"></th>
                            @endfor
                            <th style="width: 40px;" colspan="2">Overall</th>
                            @for ($i = 0; $i < 2; $i++)
                                <th style="width: 80px; background-color: #D4F809;"></th>
                                <th style="width: 30px;"></th>
                                <th style="width: 40px;"></th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 0; $i < 10; $i++)
                            <tr class="placeholder-glow">
                                <td><span class="placeholder-item" style="width: 20px; height: 20px;"></span></td>
                                <td><span class="placeholder-item" style="width: 20px; height: 20px;"></span></td>
                                <td><span class="placeholder-item" style="width: 30px; height: 20px;"></span></td>
                                <td><span class="placeholder-item" style="width: 80px; height: 20px;"></span></td>
                                <td><span class="placeholder-item" style="width: 80px; height: 20px;"></span></td>
                                <td><span class="placeholder-item" style="width: 60px; height: 20px;"></span></td>
                                @for ($j = 0; $j < 3; $j++)
                                    <td>
                                        <div class="placeholder-input"></div>
                                    </td>
                                    <td><span class="placeholder-item" style="width: 20px; height: 20px;"></span></td>
                                    <td><span class="placeholder-item" style="width: 20px; height: 20px;"></span></td>
                                @endfor
                                <td><span class="placeholder-item" style="width: 30px; height: 20px;"></span></td>
                                <td><span class="placeholder-item" style="width: 30px; height: 20px;"></span></td>
                                @for ($j = 0; $j < 2; $j++)
                                    <td>
                                        <div class="placeholder-input"></div>
                                    </td>
                                    <td><span class="placeholder-item" style="width: 20px; height: 20px;"></span></td>
                                    <td><span class="placeholder-item" style="width: 20px; height: 20px;"></span></td>
                                @endfor
                            </tr>
                        @endfor
                    </tbody>
                </table>
                <div class="row mb-4">
                    <div class="col-md-12 d-flex justify-content-end">
                        <span class="placeholder-item" style="width: 40px; height: 31px;"></span>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            initializeMarkbook();
        });

        function initializeMarkbook() {
            $('#termId').change(updateSubjectLists);
            $('#viewToggle').change(updateViewMode);

            var storedViewMode = localStorage.getItem('viewMode') || 'button';
            $('#viewToggle').prop('checked', storedViewMode === 'button');
            updateViewLabel();

            updateSubjectLists();
        }

        function updateViewMode() {
            updateViewLabel();
            localStorage.setItem('viewMode', $('#viewToggle').is(':checked') ? 'button' : 'dropdown');
            updateSubjectLists();
        }

        function updateViewLabel() {
            $('#viewLabel').text($('#viewToggle').is(':checked') ? 'Tiles View' : 'Dropdown View');
        }

        function updateSubjectLists() {
            var fetchClassesUrl = "{{ route('assessment.fetch-classes') }}";
            $.get(fetchClassesUrl)
                .done(function(data) {
                    var isButtonView = $('#viewToggle').is(':checked');
                    updateClassSubjects(data, isButtonView);
                    updateOptionalSubjects(isButtonView);

                    selectDefaultSubject();
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    console.error('Failed to update subject list:', textStatus, errorThrown);
                    alert('Failed to update subject list. Please try again.');
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

            // Show section when there's data
            $section.show();

            if (isButtonView) {
                createClassButtons(data, $container);
            } else {
                createClassDropdown(data, $container);
            }
        }

        function updateOptionalSubjects(isButtonView) {
            var $container = $('#optionalSubjectsContainer');
            $container.empty();

            if (isButtonView) {
                createOptionalButtons($container);
            } else {
                createOptionalDropdown($container);
            }
        }

        function createClassButtons(data, $container) {
            var storedClassId = localStorage.getItem('selectedClassId');
            $container.removeClass('col-md-4');
            var buttonContainer = $('<div class="button-container col-md-12"></div>');

            data.forEach(function(item) {
                var button = $('<button></button>')
                    .addClass('btn btn-sm btn-outline-primary class-button')
                    .attr('data-id', item.id)
                    .html(wrapButtonText(item.subject_teacher + ' - ' + item.klass_name + ' - ' + item
                        .subject_name))
                    .click(function() {
                        handleClassSelection($(this));
                    });

                if (item.id == storedClassId) {
                    button.addClass('active');
                }
                buttonContainer.append(button);
            });

            $container.append(buttonContainer);
        }

        function createClassDropdown(data, $container) {
            var storedClassId = localStorage.getItem('selectedClassId');
            $container.removeClass('col-md-12').addClass('col-md-4');
            var select = $('<select></select>')
                .addClass('form-select form-select-sm')
                .attr('id', 'subjectId')
                .change(function() {
                    handleClassSelection($(this));
                });

            select.append($('<option></option>').val('').text('Select class subject...'));

            data.forEach(function(item) {
                var option = $('<option></option>')
                    .val(item.id)
                    .text(item.klass_name + ' - ' + item.subject_name + ' - (' + item.student_count + ')');

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
                $container.removeClass('col-md-4');
                var buttonContainer = $('<div class="button-container col-md-12"></div>');

                @foreach ($optional_subjects as $option)
                    @can('assessOptions', $option)
                        var button = $('<button></button>')
                            .addClass('btn btn-sm btn-outline-success option-button')
                            .attr('data-id', '{{ $option->id }}')
                            .html(wrapButtonText(
                                '{{ $option->name . ' - ' . $option->gradeSubject->subject->name . ' - ' . $option->teacher->fullName }}'
                            ))
                            .click(function() {
                                handleOptionalSelection($(this));
                            });

                        if ('{{ $option->id }}' == storedOptionId) {
                            button.addClass('active');
                        }

                        buttonContainer.append(button);
                    @endcan
                @endforeach

                // Hide the entire Optional Subjects section if no optional subjects
                if (buttonContainer.children().length === 0) {
                    $section.hide();
                } else {
                    $section.show();
                    $container.append(buttonContainer);
                }
            @endif
        }

        function createOptionalDropdown($container) {
            @if ($schoolType->type !== 'Primary')
                var storedOptionId = localStorage.getItem('selectedOptionId');
                var $section = $('#optionalSubjectsSection');
                $container.removeClass('col-md-12').addClass('col-md-4');
                var select = $('<select></select>')
                    .addClass('form-select form-select-sm')
                    .attr('id', 'optionId')
                    .change(function() {
                        handleOptionalSelection($(this));
                    });

                select.append($('<option></option>').val('').text('Select optional subject...'));

                var optionCount = 0;
                @foreach ($optional_subjects as $option)
                    @can('assessOptions', $option)
                        var option = $('<option></option>')
                            .val('{{ $option->id }}')
                            .text(
                                '{{ $option->name . ' - ' . $option->gradeSubject->subject->name . ' - ' . $option->teacher->fullName }}'
                            );

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
            if ($('#viewToggle').is(':checked')) {
                $('.option-button[data-id="' + optionId + '"]').click();
            } else {
                $('#optionId').val(optionId).change();
            }
        }

        function selectClassSubject(classId) {
            if ($('#viewToggle').is(':checked')) {
                $('.class-button[data-id="' + classId + '"]').click();
            } else {
                $('#subjectId').val(classId).change();
            }
        }

        function selectFirstClassSubject() {
            if ($('#viewToggle').is(':checked')) {
                // Try class subjects first, then fall back to optional subjects
                if ($('.class-button').length > 0) {
                    $('.class-button:first').click();
                } else if ($('.option-button').length > 0) {
                    $('.option-button:first').click();
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

            if (selectedId) {
                $('.class-button').removeClass('active');
                $('#subjectId').val(selectedId);
                element.addClass('active');

                localStorage.setItem('selectedClassId', selectedId);
                localStorage.removeItem('selectedOptionId');

                // Deselect optional subjects
                $('.option-button').removeClass('active');
                $('#optionId').val('');

                updateClassLists(selectedId);
            } else {
                localStorage.removeItem('selectedClassId');
            }
        }

        function handleOptionalSelection(element) {
            var selectedId = element.is('select') ? element.val() : element.data('id');

            if (selectedId) {
                $('.option-button').removeClass('active');
                $('#optionId').val(selectedId);
                element.addClass('active');

                localStorage.setItem('selectedOptionId', selectedId);
                localStorage.removeItem('selectedClassId');

                // Deselect class subjects
                $('.class-button').removeClass('active');
                $('#subjectId').val('');

                updateOptionLists(selectedId);
            } else {
                localStorage.removeItem('selectedOptionId');
            }
        }

        function updateClassLists(subjectId) {
            var baseUrl = "{{ route('assessment.selected-subject', ['subjectId' => 'tempSubjectId']) }}";
            var klassUrl = baseUrl.replace('tempSubjectId', subjectId);

            $.ajax({
                url: klassUrl,
                type: 'GET',
                success: function(data) {
                    $('#loadingPlaceholder').hide();
                    reinitializeTable(data);
                },
                error: function(xhr, status, error) {
                    console.error("Error loading class data:", error);
                    $('#loadingPlaceholder').fadeOut(200, function() {
                        $('#studentList').html(`
                            <div class="alert alert-danger">
                                <i class="bx bx-error-circle me-2"></i>
                                Failed to load class data. Please try again.
                            </div>
                        `).fadeIn(200);
                    });
                }
            });
        }

        function updateOptionLists(optionId) {
            var baseOptionUrl = "{{ route('assessment.option-markbook', ['subjectId' => 'tempSubjectId']) }}";
            var optionKlassUrl = baseOptionUrl.replace('tempSubjectId', optionId);

            $.ajax({
                url: optionKlassUrl,
                type: 'GET',
                success: function(data) {
                    $('#loadingPlaceholder').hide();
                    reinitializeTable(data);
                },
                error: function(xhr, status, error) {
                    console.error("Error loading class data:", error);
                    $('#loadingPlaceholder').fadeOut(200, function() {
                        $('#studentList').html(`
                            <div class="alert alert-danger">
                                <i class="bx bx-error-circle me-2"></i>
                                Failed to load class data. Please try again.
                            </div>
                        `).fadeIn(200);
                    });
                }
            });
        }

        function wrapButtonText(text) {
            return '<div style="width: 100%; overflow: hidden;">' + text + '</div>';
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
                const url = urlTemplate.replace(':id', sanitizedId);
                window.location.href = url;
            } catch (error) {
                console.error("An error occurred while opening the popup:", error);
            }
        }

        function openPopupAll(id) {
            try {
                const sanitizedId = encodeURIComponent(id);
                const urlTemplate = "{{ route('assessment.grade-wide-assessment', ':id') }}";
                const url = urlTemplate.replace(':id', sanitizedId);
                window.location.href = url;
            } catch (error) {
                console.error("An error occurred while opening the popup:", error);
            }
        }

        function openPopupGradeSubject(id) {
            try {
                const sanitizedId = encodeURIComponent(id);
                const urlTemplate = "{{ route('assessment.grade-subject-wide-assessment', ':id') }}";
                const url = urlTemplate.replace(':id', sanitizedId);
                window.location.href = url;
            } catch (error) {
                console.error("An error occurred while opening the popup:", error);
            }
        }
    </script>
@endsection
