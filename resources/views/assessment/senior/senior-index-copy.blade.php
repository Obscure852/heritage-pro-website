@extends('layouts.master')
@section('title')
    Assessment Module
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

        .placeholder-icon {
            width: 24px;
            height: 24px;
            background-color: #e9ecef;
            border-radius: 50%;
            display: inline-block;
        }

        .class-button {
            margin-right: 5px;
            margin-bottom: 5px;
            width: 250px;
            height: 32px;
            text-align: center;
        }

        .class-button.active {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.slider {
            background-color: #2196F3;
        }

        input:checked+.slider:before {
            transform: translateX(26px);
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
    <div class="row">
        <div class="col-md-12 d-flex justify-content-end">
            <div class="form-check form-switch" style="padding: 5px;">
                <input class="form-check-input" type="checkbox" role="switch" id="viewToggle" checked>
                <label class="form-check-label" for="flexSwitchCheckChecked"><span id="viewLabel">Tiles View</span></label>
            </div>

            <div class="d-flex flex-wrap align-items-center justify-content-end gap-2 mb-3">
                <div>
                    <select name="term" id="termId" onchange="updateKlasses()" class="form-select form-select-sm">
                        @if (!empty($terms))
                            @foreach ($terms as $term)
                                <option data-year="{{ $term->year }}"
                                    value="{{ $term->id }}"{{ $term->id == session('selected_term_id', $currentTerm->id) ? 'selected' : '' }}>
                                    {{ 'Term ' . $term->term . ',' . $term->year }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>
        </div>
    </div>
    @if (session('message'))
        <div class="row">
            <div class="col-10">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif
    @if ($errors->any())
        <div class="row">
            <div class="col-10">
                <ul style="list-style-type: none;">
                    <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        @foreach ($errors->all() as $error)
                            <li> <i class="mdi mdi-block-helper label-icon"></i><strong>{{ $error }}</strong>
                            </li>
                        @endforeach
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </ul>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="dropdown d-flex justify-content-end">
                <a class="btn btn-link text-muted py-1 font-size-16 shadow-none dropdown-toggle" href="#"
                    role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bx bx-dots-vertical-rounded"></i>
                </a>
                <ul class="fit-some-more dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" onclick="openSeniorClassReportCards($('#classId').val()); return false;"
                            href="">Class Report Cards By Term </a>
                    </li>

                    @if (!$tests->isEmpty())
                        @foreach ($tests as $test)
                            @php
                                $startDate = \Carbon\Carbon::parse($test->start_date);
                                $endDate = \Carbon\Carbon::parse($test->end_date);
                                $startMonth = $startDate->format('F'); // Full month name
                                $endMonth = $endDate->format('F'); // Full month name
                            @endphp

                            <li>
                                <a class="dropdown-item" onclick="openCAAnalysis({{ $test->sequence }}); return false;"
                                    href="">Class Performance - End Of {{ $endMonth }}</a>
                            </li>
                        @endforeach
                    @endif

                    <li><a class="dropdown-item" onclick="openExamAnalysis(); return false;" href="">Class
                            Performance - End of Term (Exam)</a>
                    </li>

                    @if (!$tests->isEmpty())
                        @foreach ($tests as $test)
                            @php
                                $startDate = \Carbon\Carbon::parse($test->start_date);
                                $endDate = \Carbon\Carbon::parse($test->end_date);
                                $startMonth = $startDate->format('F');
                                $endMonth = $endDate->format('F');
                            @endphp

                            <li>
                                <a class="dropdown-item"
                                    onclick="openSubjectsCAAnalysis({{ $test->sequence }}); return false;"
                                    href="">Class Subjects Performance - End Of {{ $endMonth }}</a>
                            </li>
                        @endforeach
                    @endif

                    <li><a class="dropdown-item" onclick="openSubjectsExamAnalysis(); return false;" href="">Class
                            Subjects Performance - End of Term (Exam)</a>
                    </li>

                    @if (!$tests->isEmpty())
                        @foreach ($tests as $test)
                            @php
                                $startDate = \Carbon\Carbon::parse($test->start_date);
                                $endDate = \Carbon\Carbon::parse($test->end_date);
                                $startMonth = $startDate->format('F');
                                $endMonth = $endDate->format('F');
                            @endphp

                            <li>
                                <a class="dropdown-item"
                                    onclick="openGradeSubjectsCAAnalysis({{ $test->sequence }}); return false;"
                                    href="">Grade Subjects Performance - End Of {{ $endMonth }}</a>
                            </li>
                        @endforeach
                    @endif

                    <li><a class="dropdown-item" onclick="openGradeSubjectsExamAnalysis(); return false;"
                            href="">Grade
                            Subjects Performance - End of Term (Exam)</a>
                    </li>

                    @if (!$tests->isEmpty())
                        @foreach ($tests as $test)
                            @php
                                $startDate = \Carbon\Carbon::parse($test->start_date);
                                $endDate = \Carbon\Carbon::parse($test->end_date);
                                $startMonth = $startDate->format('F');
                                $endMonth = $endDate->format('F');
                            @endphp

                            <li>
                                <a class="dropdown-item"
                                    onclick="openCAGradeAnalysisSenior({{ $test->sequence }},'CA'); return false;"
                                    href="">Overall Grade Performance for {{ $endMonth }}
                                </a>
                            </li>
                        @endforeach
                    @endif

                    <li>
                        <a class="dropdown-item" onclick="openCAGradeAnalysisSenior(1,'Exam'); return false;"
                            href="">Overall Grade Performance (Exam)</a>
                    </li>

                    @if (!$tests->isEmpty())
                        @foreach ($tests as $test)
                            @php
                                $startDate = \Carbon\Carbon::parse($test->start_date);
                                $endDate = \Carbon\Carbon::parse($test->end_date);
                                $startMonth = $startDate->format('F'); // Full month name
                                $endMonth = $endDate->format('F'); // Full month name
                            @endphp

                            <li>
                                <a class="dropdown-item"
                                    onclick="openCASubjectGradeAnalysis($('#classId').val(),{{ $test->sequence }}); return false;"
                                    href="">Overall Grade Subject Performance Analysis for {{ $endMonth }}</a>
                            </li>
                        @endforeach
                    @endif
                    <li>
                        <a class="dropdown-item"
                            onclick="openExamSubjectGradeAnalysis($('#classId').val(),'Exam'); return false;"
                            href="">Overall Grade Subject Performance Analysis (Exam)</a>
                    </li>

                    @if (!$tests->isEmpty())
                        @foreach ($tests as $test)
                            @php
                                $startDate = \Carbon\Carbon::parse($test->start_date);
                                $endDate = \Carbon\Carbon::parse($test->end_date);
                                $startMonth = $startDate->format('F'); // Full month name
                                $endMonth = $endDate->format('F'); // Full month name
                            @endphp

                            <li>
                                <a class="dropdown-item"
                                    onclick="openCAHouseAnalysis({{ $test->sequence }}); return false;"
                                    href="">Subjects Performance Analysis By House for {{ $endMonth }}</a>
                            </li>
                        @endforeach
                    @endif
                    <li>
                        <a class="dropdown-item" onclick="openExamHouseAnalysis(); return false;" href="">Subjects
                            Performance Analysis By House (Exam)</a>
                    </li>


                    @if (!$tests->isEmpty())
                        @foreach ($tests as $test)
                            @php
                                $startDate = \Carbon\Carbon::parse($test->start_date);
                                $endDate = \Carbon\Carbon::parse($test->end_date);
                                $startMonth = $startDate->format('F'); // Full month name
                                $endMonth = $endDate->format('F'); // Full month name
                            @endphp

                            <li>
                                <a class="dropdown-item"
                                    onclick="openCAGradeHouseAnalysisSenior({{ $test->sequence }}); return false;"
                                    href="">Class Performance Analysis By House for {{ $endMonth }}</a>
                            </li>
                        @endforeach
                    @endif
                    <li>
                        <a class="dropdown-item" onclick="openExamHouseAnalysis(); return false;" href="">Class
                            Performance Analysis By House (Exam)</a>
                    </li>

                    <li>
                        <a class="dropdown-item" onclick="openAnalysisByDepartment($('#classId').val()); return false;"
                            href="">Subjects Grade Analysis By Departments</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!-- Class selection section -->
    <div class="row mt-4">
        <div class="col-12">
            <div id="classSelection">
                <!-- Class selection (buttons or dropdown) will be dynamically added here -->
            </div>
        </div>
    </div>

    <!-- Student list section -->
    <div class="row mt-4">
        <div id="studentList" class="col-10">
            <!-- Student list will be loaded here -->
        </div>
    </div>
    <div id="loadingPlaceholder">
        <div class="row">
            <div class="col-10">
                <div style="background-color: #5156BE; border-radius: 5px; color: white; padding: 10px; margin: 2px;"
                    class="row">
                    <div class="col-md-6">
                        <span class="placeholder-item" style="width: 70%; height: 20px;"></span>
                    </div>
                    <div class="col-md-6 d-flex justify-content-end">
                        <span class="placeholder-icon me-2"></span>
                    </div>
                </div>
                <br>
                <table class="table table-stripped rounded table-sm">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Id</th>
                            <th scope="col">Firstname</th>
                            <th scope="col">Lastname</th>
                            <th scope="col">Class</th>
                            <th scope="col">Report</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 0; $i < 10; $i++)
                            <tr class="placeholder-glow">
                                <td style="width: 30px;">
                                    <span class="placeholder-icon"></span>
                                </td>
                                <td><span class="placeholder-item" style="width: 20px; height: 16px;"></span></td>
                                <td><span class="placeholder-item" style="width: 80px; height: 16px;"></span></td>
                                <td><span class="placeholder-item" style="width: 80px; height: 16px;"></span></td>
                                <td><span class="placeholder-item" style="width: 60px; height: 16px;"></span></td>
                                <td style="width:100px;">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <span class="placeholder-icon"></span>
                                        </div>
                                        <div class="col-md-3">
                                            <span class="placeholder-icon"></span>
                                        </div>
                                        <div class="col-md-3">
                                            <span class="placeholder-icon"></span>
                                        </div>
                                    </div>
                                </td>
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
            $('#termId').change(function() {
                updateKlasses();
            });

            $('#viewToggle').change(function() {
                updateViewMode();
            });

            // Set initial view mode from localStorage or default to button view
            var storedViewMode = localStorage.getItem('viewMode') || 'button';
            $('#viewToggle').prop('checked', storedViewMode === 'button');
            $('#viewLabel').text(storedViewMode === 'button' ? 'Tiles View' : 'Dropdown View');

            updateKlasses();
        });

        function updateViewMode() {
            var isButtonView = $('#viewToggle').is(':checked');
            $('#viewLabel').text(isButtonView ? 'Tiles View' : 'Dropdown View');
            localStorage.setItem('viewMode', isButtonView ? 'button' : 'dropdown');
            updateKlasses();
        }

        function updateKlasses() {
            var termId = $('#termId').val();
            var isButtonView = $('#viewToggle').is(':checked');
            var classForTermUrl = "{{ route('assessment.klasses-for-term') }}";

            $.ajax({
                url: classForTermUrl,
                type: 'GET',
                data: {
                    'term_id': termId
                },
                success: function(data) {
                    var $classSelection = $('#classSelection');
                    $classSelection.empty();

                    if (data && data.length > 0) {
                        if (isButtonView) {
                            createButtonView(data, $classSelection);
                        } else {
                            createDropdownView(data, $classSelection);
                        }
                    } else {
                        $classSelection.append('<p>No classes available</p>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                }
            });
        }

        function createButtonView(data, $container) {
            $container.removeClass('col-6').addClass('col-12');
            var storedClassId = localStorage.getItem('selectedClassId');
            $.each(data, function(index, klass) {
                var gradeName = klass.grade && klass.grade.name ? klass.grade.name : 'Unknown';
                var button = $('<button></button>')
                    .addClass('btn btn-outline-primary class-button')
                    .attr('data-class-id', klass.id)
                    .attr('data-grade', gradeName) // Correctly set data-grade
                    .text(klass.name + ' - ' + klass.teacher.firstname + ' ' + klass.teacher.lastname + ' (' + klass
                        .students_count + ')')
                    .click(function() {
                        $('.class-button').removeClass('active btn-primary').addClass('btn-outline-primary');
                        $(this).removeClass('btn-outline-primary').addClass('active btn-primary');
                        localStorage.setItem('selectedClassId', klass.id);
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
                .addClass('form-select form-select-sm')
                .attr('id', 'classDropdown')
                .change(function() {
                    var selectedClassId = $(this).val();
                    localStorage.setItem('selectedClassId', selectedClassId);
                    updateClassLists(selectedClassId);
                });

            var storedClassId = localStorage.getItem('selectedClassId');
            $.each(data, function(index, klass) {
                var gradeName = klass.grade && klass.grade.name ? klass.grade.name : 'Unknown';
                var option = $('<option></option>')
                    .attr('value', klass.id)
                    .attr('data-grade', gradeName) // Correctly set data-grade
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
            var isButtonView = $('#viewToggle').is(':checked');
            if (isButtonView) {
                return $('.class-button.active').data('grade');
            } else {
                return $('#classDropdown option:selected').data('grade');
            }
        }

        function updateClassLists(classId) {
            var termId = $('#termId').val();
            var baseUrl = "{{ route('assessment.class-lists', ['classId' => 'tempClassId', 'termId' => 'tempTermId']) }}";
            var klassUrl = baseUrl.replace('tempClassId', classId).replace('tempTermId', termId);

            $.ajax({
                url: klassUrl,
                type: 'GET',
                success: function(data) {
                    $('#loadingPlaceholder').hide();
                    $('#studentList').html(data).fadeIn(200, function() {
                        initializeDataTable();
                        $('[data-bs-toggle="tooltip"]').tooltip();
                    });
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

        function initializeDataTable() {
            if ($.fn.DataTable.isDataTable('#studentTable')) {
                $('#studentTable').DataTable().destroy();
            }

            $('#studentTable').DataTable({
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
                }
            });
        }

        function getCurrentClassId() {
            var isButtonView = $('#viewToggle').is(':checked');
            if (isButtonView) {
                return $('.class-button.active').data('class-id');
            } else {
                return $('#classDropdown').val();
            }
        }

        // Also update in the change event for dropdown view
        $('#classDropdown').change(function() {
            var selectedClassId = $(this).val();
            localStorage.setItem('selectedClassId', selectedClassId);
            updateClassLists(selectedClassId);
        });

        function openSeniorClassReportCards() {
            var classId = getCurrentClassId();
            var baseUrl = "{{ route('assessment.generate-class-report-cards', ['classId' => 'tempClassId']) }}";
            var finalUrl = baseUrl.replace('tempClassId', classId);
            window.open(finalUrl, 'PDFWindow', 'width=800,height=1000');
        }

        function openExamAnalysis1() {
            var classId = getCurrentClassId();
            var baseUrl = "{{ route('assessment.generate-exam-analysis', ['classId' => 'tempClassId']) }}";
            var finalUrl = baseUrl.replace('tempClassId', classId);
            window.location.href = finalUrl;
        }

        function openCAAnalysis(sequenceId) {
            var classId = getCurrentClassId();
            var baseUrl =
                "{{ route('assessment.generateCAAnalysisSenior', ['classId' => 'tempClassId', 'sequenceId' => 'tempSequenceId', 'type' => 'tempType']) }}";
            var finalUrl = baseUrl.replace('tempClassId', classId).replace('tempSequenceId', sequenceId).replace('tempType',
                'CA');
            window.location.href = finalUrl;
        }


        function openExamAnalysis() {
            var classId = getCurrentClassId();
            var baseUrl =
                "{{ route('assessment.generateCAAnalysisSenior', ['classId' => 'tempClassId', 'sequenceId' => 'tempSequenceId', 'type' => 'tempType']) }}";
            var finalUrl = baseUrl.replace('tempClassId', classId).replace('tempSequenceId', 1).replace('tempType',
                'Exam');
            window.location.href = finalUrl;
        }



        function openSubjectsCAAnalysis(sequenceId) {
            var classId = getCurrentClassId();
            var baseUrl =
                "{{ route('assessment.generate-subjects-ca-analysis-senior', ['classId' => 'tempClassId', 'sequenceId' => 'tempSequenceId', 'type' => 'tempType']) }}";
            var finalUrl = baseUrl.replace('tempClassId', classId).replace('tempSequenceId', sequenceId).replace('tempType',
                'CA');
            window.location.href = finalUrl;
        }

        function openSubjectsExamAnalysis() {
            var classId = getCurrentClassId();
            var baseUrl =
                "{{ route('assessment.generate-subjects-ca-analysis-senior', ['classId' => 'tempClassId', 'sequenceId' => 'tempSequenceId', 'type' => 'tempType']) }}";
            var finalUrl = baseUrl.replace('tempClassId', classId).replace('tempSequenceId', 1).replace('tempType',
                'Exam');
            window.location.href = finalUrl;
        }


        function openGradeSubjectsCAAnalysis(sequenceId) {
            var classId = getCurrentClassId();
            var baseUrl =
                "{{ route('assessment.generate-grade-subjects-ca-analysis-senior', ['classId' => 'tempClassId', 'sequenceId' => 'tempSequenceId', 'type' => 'tempType']) }}";
            var finalUrl = baseUrl.replace('tempClassId', classId).replace('tempSequenceId', sequenceId).replace('tempType',
                'CA');
            window.location.href = finalUrl;
        }

        function openGradeSubjectsExamAnalysis() {
            var classId = getCurrentClassId();
            var baseUrl =
                "{{ route('assessment.generate-grade-subjects-ca-analysis-senior', ['classId' => 'tempClassId', 'sequenceId' => 'tempSequenceId', 'type' => 'tempType']) }}";
            var finalUrl = baseUrl.replace('tempClassId', classId).replace('tempSequenceId', 1).replace('tempType',
                'Exam');
            window.location.href = finalUrl;
        }

        function openCAGradeAnalysis(sequenceId) {
            var classId = getCurrentClassId();
            var baseUrl =
                "{{ route('assessment.overall-ca-grade-analysis', ['classId' => 'tempClassId', 'sequenceId' => 'tempSequenceId']) }}";
            var finalUrl = baseUrl.replace('tempClassId', classId).replace('tempSequenceId', sequenceId);
            window.location.href = finalUrl;
        }


        function openCAGradeAnalysisSenior(sequenceId, type) {
            var classId = getCurrentClassId();
            var baseUrl =
                "{{ route('assessment.overall-ca-senior-grade-analysis', ['classId' => 'tempClassId', 'type' => 'tempType', 'sequenceId' => 'tempSequenceId']) }}";
            var finalUrl = baseUrl.replace('tempClassId', classId).replace('tempType', type).replace('tempSequenceId',
                sequenceId);
            window.location.href = finalUrl;
        }


        function openCAGradeHouseAnalysisSenior(type, sequenceId) {
            var classId = getCurrentClassId();
            var baseUrl =
                "{{ route('assessment.house-analysis-senior-grade-analysis') }}";
            window.location.href = baseUrl;
        }

        // function openCAGradeHouseAnalysisSenior(type, sequenceId) {
        //     var classId = getCurrentClassId();
        //     var baseUrl =
        //         "{{ route('assessment.house-analysis-senior-grade-analysis', ['classId' => 'tempClassId', 'type' => 'tempType', 'sequenceId' => 'tempSequenceId']) }}";
        //     var finalUrl = baseUrl.replace('tempClassId', classId).replace('tempType', type).replace('tempSequenceId',
        //         sequenceId);
        //     window.location.href = finalUrl;
        // }

        function openCAHouseAnalysis(sequenceId) {
            var baseUrl = "{{ route('assessment.ca-house-analysis', ['sequenceId' => 'tempSequenceId']) }}";
            var finalUrl = baseUrl.replace('tempSequenceId', sequenceId);
            window.location.href = finalUrl;
        }

        function openExamHouseAnalysis() {
            var baseUrl = "{{ route('assessment.exam-house-analysis') }}";
            window.location.href = baseUrl;
        }

        function openAnalysisByDepartment() {
            var classId = getCurrentClassId();
            var baseUrl = "{{ route('assessment.analysis-by-department', ['classId' => 'tempClassId']) }}";
            var finalUrl = baseUrl.replace('tempClassId', classId);
            window.location.href = finalUrl;
        }

        function openOverallGradeAnalysisExam() {
            var classId = getCurrentClassId();
            var baseUrl = "{{ route('assessment.overall-exam-grade-analysis', ['classId' => 'tempClassId']) }}";
            var finalUrl = baseUrl.replace('tempClassId', classId);
            window.location.href = finalUrl;
        }

        function openCASubjectGradeAnalysis(sequenceId) {
            var classId = getCurrentClassId();
            var baseUrl =
                "{{ route('assessment.all-subjects-ca', ['classId' => 'tempClassId', 'sequenceId' => 'tempSequenceId']) }}";
            var finalUrl = baseUrl.replace('tempClassId', classId).replace('tempSequenceId', sequenceId);
            window.location.href = finalUrl;
        }

        function openExamSubjectGradeAnalysis() {
            var classId = getCurrentClassId();
            var baseUrl = "{{ route('assessment.all-subjects-exam', ['classId' => 'tempClassId']) }}";
            var finalUrl = baseUrl.replace('tempClassId', classId);
            window.location.href = finalUrl;
        }
    </script>
@endsection
