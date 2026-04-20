@extends('layouts.master')
@section('title')
    Assessment Module
@endsection
@section('css')
@endsection
@section('content')
    <!-- start page title -->
    @component('components.breadcrumb')
        @slot('li_1')
            Assessment Premium
        @endslot
        @slot('title')
            AP List
        @endslot
    @endcomponent
    <div class="row align-items-center">
        <div class="col-md-6">
        </div>
        <div class="col-md-6">
            <div class="d-flex flex-wrap align-items-center justify-content-end gap-2 mb-3">
                <div>
                    <select name="term" id="termId" onchange="updateKlasses()" class="form-select form-select-sm">
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
    </div>
    <!-- end row -->
    @if (session('message'))
        <div class="row">
            <div class="col-md-8">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="row">
            <div class="col-md-8">
                <ul style="list-style-type: none;">
                    <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        @foreach ($errors->all() as $error)
                            <li>
                                <i class="mdi mdi-block-helper label-icon"></i><strong>{{ $error }}</strong>
                            </li>
                        @endforeach
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </ul>
            </div>
        </div>
    @endif
    <div class="col-md-12">
        <div class="dropdown d-flex justify-content-end">
            <a class="btn btn-link text-muted py-1 font-size-16 shadow-none dropdown-toggle" href="#" role="button"
                data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bx bx-dots-vertical-rounded"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" onclick="openClassReportCards($('#classId').val()); return false;"
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
                            <a class="dropdown-item"
                                onclick="openClassPrimaryAnalysis($('#classId').val(),'CA',{{ $test->sequence }}); return false;"
                                href="">Class Performance - End Of Month({{ $test->sequence }}) -
                                {{ $startMonth }}
                                to {{ $endMonth }} (CA)</a>
                        </li>
                    @endforeach
                @endif

                <li>
                    <a class="dropdown-item" onclick="openClassPrimaryAnalysis($('#classId').val(),'Exam',1); return false;"
                        href="">Class Performance - End of Term (Exam)
                    </a>
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
                                onclick="openGradePrimaryAnalysis($('#classId').val(),'CA',{{ $test->sequence }}); return false;"
                                href="">Overall Grade Performance - {{ $startMonth }} to {{ $endMonth }}
                                (CA)
                            </a>
                        </li>
                    @endforeach
                @endif

                <li>
                    <a class="dropdown-item" onclick="openGradePrimaryAnalysis($('#classId').val(),'Exam',1); return false;"
                        href="">Overall Grade Performance (Exam)</a>
                </li>

                <li>
                    <a class="dropdown-item" onclick="openRegionalGradePrimaryAnalysis($('#classId').val()); return false;"
                        href="">Overall Subject Grade Performance (Exam) - For Region</a>
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
                                onclick="openTestSubjectGradeAnalysis($('#classId').val(),'CA',{{ $test->sequence }}); return false;"
                                href="">Overall Grade Subject Performance Analysis({{ $test->sequence }})-
                                {{ $startMonth }} to {{ $endMonth }} (CA)</a>
                        </li>
                    @endforeach
                @endif
                <li>
                    <a class="dropdown-item"
                        onclick="openTestSubjectGradeAnalysis($('#classId').val(),'Exam',1); return false;"
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
                                onclick="openOverallSubjectGradeAnalysis($('#classId').val(),'CA',{{ $test->sequence }}); return false;"
                                href="">Overall Subjects Performance Analysis By Grade ({{ $test->sequence }})-
                                {{ $startMonth }} to {{ $endMonth }} (CA)</a>
                        </li>
                    @endforeach
                @endif
                <li>
                    <a class="dropdown-item"
                        onclick="openOverallSubjectGradeAnalysis($('#classId').val(),'Exam',1); return false;"
                        href="">Overall
                        Subjects Performance Analysis By Grade (Exam)</a>
                </li>
            </ul>
        </div>
    </div>

    <div class="row">
        <form action="#">
            <input type="hidden" id="selectedClassId" value="{{ session('selectedClassId') }}">
        </form>
        <div class="col-md-3">
            @if (!empty($classes))
                <label for="assessment">Classes {{ '(' . $classes->count() . ')' }}</label>
                <select name="assessment" id="classId" class="form-select form-select-sm">
                    @foreach ($classes as $index => $class)
                        @can('classTeacherAccess', $class)
                            <option data-grade="{{ $class->grade->id }}" value="{{ $class->id }}"
                                {{ $index == 0 ? 'selected' : '' }}>
                                {{ $class->name . ' - ' . $class->teacher->firstname . ' ' . $class->teacher->lastname . ' (' . $class->students->count() . ')' }}
                            </option>
                        @endcan
                    @endforeach
                </select>
            @endif
        </div>
    </div>
    <div class="row mt-4">
        <div id="studentList" class="col-md-10">
        </div>
    </div>
    <!-- end table responsive -->
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            $('#termId').change(function() {
                updateKlasses();
            });

            $('#classId').change(function() {
                storeInLocalStorage();
                updateClassLists();
            });

            // Trigger initial load
            $('#termId').trigger('change');

            // Load the initial selectedClassId from local storage
            var initialClassId = getFromLocalStorage();
            if (initialClassId) {
                $('#selectedClassId').val(initialClassId);
                updateClassLists();
            }
        });

        function storeInLocalStorage() {
            var selectedClassId = $('#classId').val();
            localStorage.setItem('selectedClassId', selectedClassId);
        }

        function getFromLocalStorage() {
            return localStorage.getItem('selectedClassId');
        }

        function updateKlasses() {
            var termId = $('#termId').val();
            var selectedClassId = getFromLocalStorage();
            var classForTermUrl = "{{ route('assessment.klasses-for-term') }}";

            $.ajax({
                url: classForTermUrl,
                type: 'GET',
                data: {
                    'term_id': termId
                },
                success: function(data) {
                    var $classSelect = $('#classId');
                    $classSelect.empty();

                    if (data && data.length > 0) {
                        $.each(data, function(index, klass) {
                            var $option = $('<option></option>').val(klass.id).text(klass.name + ' - ' +
                                klass.teacher['firstname'] + " " + klass.teacher['lastname'] +
                                " (" + klass.students_count + ")");
                            $classSelect.append($option);
                        });

                        var isValidSelectedClassId = $classSelect.find('option[value="' + selectedClassId +
                            '"]').length > 0;
                        if (selectedClassId && isValidSelectedClassId) {
                            $classSelect.val(selectedClassId).trigger('change');
                        } else {
                            $classSelect.find('option:first').prop('selected', true).trigger('change');
                        }
                    } else {
                        $classSelect.append($('<option></option>').val('').text('No classes available'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                }
            });
        }

        function updateClassLists() {
            var classId = $('#classId').val();
            var termId = $('#termId').val();

            var baseUrl = "{{ route('assessment.class-lists', ['classId' => 'tempClassId', 'termId' => 'tempTermId']) }}";
            var klassUrl = baseUrl.replace('tempClassId', classId).replace('tempTermId', termId);

            $.get(klassUrl, function(data) {
                $('#studentList').html(data);
            });
        }

        function openClassReportCards(classId) {
            try {
                const sanitizedClassId = encodeURIComponent(classId);
                var baseUrl2 = "{{ route('assessment.all-students-primary-reports-pdf', ['classId' => 'tempClassId']) }}";
                var finalUrl = baseUrl2.replace('tempClassId', sanitizedClassId);
                window.open(finalUrl, 'PDFWindow', 'width=800,height=1000');
            } catch (error) {
                console.error("An error occurred while opening the popup:", error);
            }
        }

        function openExamAnalysis(classId) {
            try {
                const sanitizedClassId = encodeURIComponent(classId);
                var baseUrl4 = "{{ route('assessment.generate-exam-analysis', ['classId' => 'tempClassId']) }}";
                var finalBase3 = baseUrl4.replace('tempClassId', sanitizedClassId);
                window.location.href = finalBase3;
            } catch (error) {
                console.error("An error occurred while opening the popup:", error);
            }
        }

        function openPopup(id) {
            try {
                const sanitizedId = encodeURIComponent(id);
                const url = `/assessment/classes/${sanitizedId}`;
                window.location.href = url;
            } catch (error) {
                console.error("An error occurred while opening the popup:", error);
            }
        }

        function openClassPrimaryAnalysis(klassId, type, sequenceId) {
            try {
                const sanitizedClassId = encodeURIComponent(klassId);
                const sanitizedTypeId = encodeURIComponent(type);
                const sanitizedSequenceId = encodeURIComponent(sequenceId);

                var baseUrl3 =
                    "{{ route('assessment.primary-tests-class-analysis', ['classId' => 'tempClassId', 'type' => 'tempType', 'sequenceId' => 'tempSequenceId']) }}";
                var finalBase2 = baseUrl3.replace('tempClassId', sanitizedClassId).replace('tempType', sanitizedTypeId)
                    .replace('tempSequenceId', sanitizedSequenceId);
                window.location.href = finalBase2;

            } catch (error) {
                console.error("An error occurred while opening the popup:", error);
            }
        }

        function openGradePrimaryAnalysis(klassId, type, sequenceId) {
            try {
                const sanitizedClassId = encodeURIComponent(klassId);
                const sanitizedTypeId = encodeURIComponent(type);
                const sanitizedSequenceId = encodeURIComponent(sequenceId);

                var baseUrl3 =
                    "{{ route('assessment.primary-tests-grade-analysis', ['classId' => 'tempClassId', 'type' => 'tempType', 'sequenceId' => 'tempSequenceId']) }}";
                var finalBase2 = baseUrl3.replace('tempClassId', sanitizedClassId).replace('tempType', sanitizedTypeId)
                    .replace('tempSequenceId', sanitizedSequenceId);
                window.location.href = finalBase2;

            } catch (error) {
                console.error("An error occurred while opening the popup:", error);
            }
        }

        function openRegionalGradePrimaryAnalysis(klassId) {
            try {
                const sanitizedClassId = encodeURIComponent(klassId);
                var baseUrl22 =
                    "{{ route('assessment.regional-test-primary-grade-subject-analysis', ['classId' => 'tempClassId']) }}";
                var finalBase23 = baseUrl22.replace('tempClassId', sanitizedClassId);
                window.location.href = finalBase23;

            } catch (error) {
                console.error("An error occurred while opening the popup:", error);
            }
        }

        function openCAGradeAnalysis(klassId, sequenceId) {
            try {
                const sanitizedClassId = encodeURIComponent(klassId);
                const sanitizedSequenceId = encodeURIComponent(sequenceId);

                var baseUrl5 =
                    "{{ route('assessment.overall-ca-grade-analysis', ['classId' => 'tempClassId', 'sequenceId' => 'tempSequenceId']) }}";
                var finalBase4 = baseUrl5.replace('tempClassId', sanitizedClassId).replace('tempSequenceId',
                    sanitizedSequenceId);
                window.location.href = finalBase4;

            } catch (error) {
                console.error("An error occurred while opening the popup:", error);
            }
        }

        function openOverallSubjectGradeAnalysis(classId, type, sequenceId) {

            try {
                const sanitizedClassId = encodeURIComponent(classId);
                const sanitizedType = encodeURIComponent(type);
                const sanitizedSequenceId = encodeURIComponent(sequenceId);

                var baseUrl9 =
                    "{{ route('assessment.assessment-overall-grade-subject-analysis', ['classId' => 'tempClassId', 'type' => 'tempType', 'sequenceId' => 'tempSequenceId']) }}";

                var finalBase8 = baseUrl9.replace('tempClassId', sanitizedClassId).replace('tempType', sanitizedType)
                    .replace('tempSequenceId', sanitizedSequenceId);
                window.location.href = finalBase8;
            } catch (error) {
                console.error("An error occurred while opening the popup:", error);
            }
        }

        function openExamHouseAnalysis() {
            try {
                var baseUrl10 = "{{ route('assessment.exam-house-analysis') }}";
                window.location.href = baseUrl10;
            } catch (error) {
                console.error("An error occurred while opening the popup:", error);
            }
        }

        function openOverallStats(id) {
            try {
                const sanitizedId = encodeURIComponent(id);
                const url = `assessment/analysis/overall/${sanitizedId}`;
                window.location.href = url;

            } catch (error) {
                console.error('An error occurred:', error);
            }
        }

        function openGradeAnalysis(classId) {
            try {
                const sanitizedClassValue = encodeURIComponent(classId);
                const url = `/assessment/grade/${sanitizedClassValue}`;
                window.location.href = url;

            } catch (error) {
                console.error('An error occurred:', error);
            }
        }

        function openAnalysisByDepartment(classId) {
            try {
                const sanitizedClassId = encodeURIComponent(classId);
                var baseUrl11 = "{{ route('assessment.analysis-by-department', ['classId' => 'tempClassId']) }}";
                var finalBase9 = baseUrl11.replace('tempClassId', sanitizedClassId);
                window.location.href = finalBase9;

            } catch (error) {
                console.error('An error occurred:', error);
            }
        }

        function openOverallGradeAnalysisExam(classId) {
            try {
                const sanitizedClassValue = encodeURIComponent(classId);
                var baseUrl6 = "{{ route('assessment.overall-exam-grade-analysis', ['classId' => 'tempClassId']) }}";
                var finalBase5 = baseUrl6.replace('tempClassId', sanitizedClassValue);
                window.location.href = finalBase5;

            } catch (error) {
                console.error('An error occurred:', error);
            }
        }

        function openCASubjectGradeAnalysis(classId, sequenceId) {
            try {
                const sanitizedClassId = encodeURIComponent(classId);
                const sanitizedSequenceId = encodeURIComponent(sequenceId);

                var baseUrl7 =
                    "{{ route('assessment.all-subjects-ca', ['classId' => 'tempClassId', 'sequenceId' => 'tempSequenceId']) }}";
                var finalBase6 = baseUrl7.replace('tempClassId', sanitizedClassId).replace('tempSequenceId',
                    sanitizedSequenceId);
                window.location.href = finalBase6;
            } catch (error) {
                console.error('An error occurred:', error);
            }
        }

        function openTestSubjectGradeAnalysis(classId, type, sequenceId) {
            try {
                const sanitizedClassId = encodeURIComponent(classId);
                const sanitizedType = encodeURIComponent(type);
                const sanitizedSequenceId = encodeURIComponent(sequenceId);

                var baseUrl8 =
                    "{{ route('assessment.test-primary-grade-subject-analysis', ['classId' => 'tempClassId', 'type' => 'tempType', 'sequenceId' => 'tempSequenceId']) }}";
                var finalBase12 = baseUrl8.replace('tempClassId', sanitizedClassId).replace('tempType', sanitizedType)
                    .replace('tempSequenceId', sanitizedSequenceId);
                window.location.href = finalBase12;
            } catch (error) {
                console.error('An error occurred:', error);
            }
        }

        function openExamSubjectGradeAnalysis(classId) {
            try {
                const sanitizedClassId = encodeURIComponent(classId);

                var baseUrl8 = "{{ route('assessment.all-subjects-exam', ['classId' => 'tempClassId']) }}";
                var finalBase7 = baseUrl8.replace('tempClassId', sanitizedClassId);
                window.location.href = finalBase7;
            } catch (error) {
                console.error('An error occurred:', error);
            }
        }
    </script>
@endsection
