@extends('layouts.master')
@section('title')
    Attendance Module
@endsection
@section('css')
    <!-- DataTables -->
    <link href="{{ URL::asset('/assets/libs/datatables/datatables.min.css') }}" rel="stylesheet" type="text/css" />
@endsection
@section('content')
    <!-- start page title -->
    @component('components.breadcrumb')
        @slot('li_1')
            Attendance
        @endslot
        @slot('title')
            Attendance Register
        @endslot
    @endcomponent
    <!-- term toggle -->
    <div class="row align-items-center">
        <div class="col-md-6">
        </div>
        <div class="col-md-6">
            <div class="d-flex flex-wrap align-items-center justify-content-end gap-2 mb-3">
                <div>
                    <select name="term" id="termId" onchange="updateGrades()" class="form-select form-select-sm">
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
            <div class="alert alert-success alert-border-left alert-dismissible fade show" role="alert">
                <i class="mdi mdi-check-all me-3 align-middle"></i><strong>{{ session('message') }}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif
    <div class="row">
        <div class="col-md-3">
            <form action="#">
                <input type="hidden" id="selectedClassId" value="{{ session('selectedClassId') }}">
            </form>
            <label for="assessment">Select Class</label>
            @if (!empty($grades))
                <select class="form-select form-select-sm" name="grade" id="gradeId">
                    @foreach ($grades as $grade)
                        @if ($grade->klasses->isNotEmpty())
                            <optgroup label="{{ $grade->name }}">
                                @foreach ($grade->klasses as $klass)
                                    <option value="{{ $klass->id }}"
                                        {{ session('selectedClassId') == $klass->id ? 'selected' : '' }}>
                                        {{ $klass->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                    @endforeach
                </select>
            @endif
        </div>
        <div class="col-md-9">
            <div class="dropdown d-flex justify-content-end">
                <a class="btn btn-link text-muted py-1 font-size-16 shadow-none dropdown-toggle" href="#"
                    role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bx bx-dots-vertical-rounded"></i>
                </a>

                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" onclick="classAttendanceReport(); return false;" href="">Termly
                            Attendance by Att Codes</a>
                    </li>
                    {{-- <li>
                        <a class="dropdown-item" onclick="classMontlhyAttendanceReport(); return false;"
                            href="">Class
                            Attendance By Att Codes</a>
                    </li> --}}
                </ul>
            </div>
        </div>
    </div>
    <div class="row mt-4">
        <div id="classList" class="col-12">
            <!-- Class list will be loaded here dynamically -->
        </div>
    </div>
@endsection
@section('script')
    <script src="{{ URL::asset('/assets/libs/datatables/datatables.min.js') }}"></script>
    <script src="{{ URL::asset('/assets/js/pages/datatable-pages.init.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#termId').change(function() {
                updateGrades();
            });

            $('#gradeId').change(function() {
                storeInLocalStorage();
                updateClassAttendance();
            });

            // Load the initial selectedClassId and trigger initial load
            var initialClassId = getFromLocalStorage();
            if (initialClassId) {
                $('#selectedClassId').val(initialClassId);
                updateGrades();
            } else {
                $('#termId').trigger('change');
            }

            // Attach event listeners for dynamically added elements using event delegation
            $(document).on('click', '#prevWeek', function() {
                navigateWeek(-1);
            });

            $(document).on('click', '#nextWeek', function() {
                navigateWeek(1);
            });

            // Toggle attendance code
            $(document).on('click', '.attendance-input', function() {
                var options = ['√', 'A1', 'A2', 'A3', 'A4', 'A5', 'A6'];
                var currentOption = $(this).val();
                var nextIndex = (options.indexOf(currentOption) + 1) % options.length;
                $(this).val(options[nextIndex]);
            });

            // Toggle attendance code for a specific day
            $(document).on('click', '.day-header', function() {
                var day = $(this).data('day');
                toggleAttendanceCode(day);
            });
        });

        function storeInLocalStorage() {
            var selectedClassId = $('#gradeId').val();
            localStorage.setItem('selectedClassId', selectedClassId);
        }

        function getFromLocalStorage() {
            return localStorage.getItem('selectedClassId');
        }

        function updateClassAttendance(weekStart = null) {
            var classId = $('#gradeId').val();
            var termId = $('#termId').val();
            var currentWeekStart = weekStart || $('#currentWeekStart').val();

            var baseUrl =
                "{{ route('attendance.class-list', ['classId' => 'tempClassId', 'termId' => 'tempTermId', 'weekStart' => 'tempWeekStart']) }}";
            var attendanceClassUrl = baseUrl.replace('tempClassId', classId).replace('tempTermId', termId).replace(
                'tempWeekStart', encodeURIComponent(currentWeekStart));

            $.get(attendanceClassUrl, function(data) {
                $('#classList').html(data);
            });
        }

        function updateGrades() {
            var termId = $('#termId').val();
            var selectedClassId = getFromLocalStorage();
            var getGradesUrl = "{{ route('klasses.get-grades-list') }}";

            $.ajax({
                url: getGradesUrl,
                type: 'GET',
                data: {
                    'term_id': termId,
                },
                success: function(data) {
                    var $gradeSelect = $('#gradeId');
                    $gradeSelect.empty();

                    $.each(data, function(index, grade) {
                        var $optgroup = $('<optgroup>').attr('label', grade.name);
                        $.each(grade.klasses, function(index, klass) {
                            var $option = $('<option>').val(klass.id).text(klass.name);
                            if (klass.id == selectedClassId) {
                                $option.prop('selected', true);
                            }
                            $optgroup.append($option);
                        });
                        $gradeSelect.append($optgroup);
                    });

                    // If no selectedClassId is provided, select the first option by default
                    if (!selectedClassId) {
                        $gradeSelect.find('option:first').prop('selected', true);
                    }
                    // Trigger change event to update dependent lists or perform other actions
                    $gradeSelect.change();
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                }
            });
        }

        function toggleAttendanceCode(day) {
            var attendanceCodes = ['√', 'A1', 'A2', 'A3', 'A4', 'A5', 'A6', ''];
            var currentCodeIndex = 0;

            $('.' + day + '.attendance-input').each(function() {
                var $input = $(this);
                var currentValue = $input.val();

                if (currentValue === '') {
                    $input.val(attendanceCodes[0]);
                } else {
                    currentCodeIndex = attendanceCodes.indexOf(currentValue) + 1;
                    if (currentCodeIndex >= attendanceCodes.length) {
                        currentCodeIndex = 0;
                    }
                    $input.val(attendanceCodes[currentCodeIndex]);
                }
            });
        }

        function navigateWeek(direction) {
            var currentWeekStart = $('#currentWeekStart').val();
            $.ajax({
                url: "{{ route('attendance.navigate-week') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    currentWeekStart: currentWeekStart,
                    direction: direction,
                    is_ajax: true
                },
                success: function(response) {
                    console.log('Response:', response); // Log the response
                    if (response.success) {
                        $('#currentWeekStart').val(response.newWeekStart);
                        updateClassAttendance(response.newWeekStart);
                    } else {
                        console.error('Error: Success flag is false', response);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                }
            });
        }

        function classAttendanceReport() {
            var classId = $('#gradeId').val();
            var baseUrl = "{{ route('attendance.class-attendance-report', ['classId' => '__CLASS_ID__']) }}";
            var classAttendanceUrl = baseUrl.replace('__CLASS_ID__', classId);
            window.location.href = classAttendanceUrl;
        }

        function classMontlhyAttendanceReport() {
            var classId = $('#gradeId').val();
            var baseUrl89 = "{{ route('attendance.class-termly-attendance-report', ['classId' => 'tempClassId']) }}";
            var classMonthlyAttendanceUrl = baseUrl89.replace('tempClassId', classId);
            window.location.href = classMonthlyAttendanceUrl;
        }
    </script>
@endsection
