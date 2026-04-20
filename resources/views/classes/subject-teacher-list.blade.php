@extends('layouts.master')
@section('title')
    Subject/Teacher Allocations | Academic Management
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

        /* Selectors */
        .filter-selector {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .filter-selector label {
            font-weight: 500;
            color: #374151;
            font-size: 14px;
            white-space: nowrap;
        }

        .filter-selector .form-select {
            min-width: 180px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            padding: 8px 12px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .filter-selector .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Table Styling */
        .subjects-table {
            width: 100%;
            border-collapse: collapse;
        }

        .subjects-table thead th {
            background: #f9fafb;
            padding: 12px 16px;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e5e7eb;
        }

        .subjects-table tbody td {
            padding: 12px 16px;
            color: #4b5563;
            font-size: 14px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }

        .subjects-table tbody tr:hover {
            background: #f9fafb;
        }

        .subjects-table tbody tr:last-child td {
            border-bottom: none;
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

            .filter-selector {
                width: 100%;
            }

            .filter-selector .form-select {
                min-width: 100%;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="{{ route('academic.index') }}">Classes</a>
        @endslot
        @slot('title')
            Subject/Teacher Allocations
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
            <h3><i class="fas fa-book-reader me-2"></i>Subject/Teacher Allocations</h3>
            <p>Manage subject and teacher assignments for each class</p>
        </div>

        <div class="settings-body">
            <div class="help-text">
                <div class="help-title">About Subject/Teacher Assignments</div>
                <div class="help-content">
                    View and manage which teachers are assigned to teach specific subjects in each class.
                    Select a term and class to view the current subject allocations.
                </div>
            </div>

            @if ($grades->isNotEmpty() && !($hasGradesButNoClasses ?? false))
                <div class="controls-row">
                    <div class="d-flex align-items-center gap-4 flex-wrap">
                        <div class="filter-selector">
                            <input type="hidden" id="selectedGradeListId" value="{{ session('selectedGradeListId') }}">
                            <select name="gradeId" id="gradeId" class="form-select">
                                @foreach ($grades as $grade)
                                    <optgroup label="{{ $grade->name }}">
                                        @foreach ($grade->klasses as $index => $klass)
                                            <option value="{{ $klass->id }}" {{ $klass->id == 1 ? 'selected' : '' }}>
                                                {{ $klass->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Subject List -->
                <div id="subjectGradeList"></div>

                <!-- Loading Placeholder -->
                <div id="loadingPlaceholder" class="loading-table">
                    <table class="subjects-table">
                        <thead>
                            <tr>
                                <th scope="col">Id</th>
                                <th scope="col">Subjects</th>
                                <th scope="col">Subject Teacher</th>
                                <th scope="col">Venue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for ($i = 0; $i < 5; $i++)
                                <tr class="placeholder-glow">
                                    <td><span class="placeholder-item" style="width: 20px; height: 20px;"></span></td>
                                    <td><span class="placeholder-item" style="width: 120px; height: 20px;"></span></td>
                                    <td><span class="placeholder-item" style="width: 150px; height: 20px;"></span></td>
                                    <td><span class="placeholder-item" style="width: 100px; height: 20px;"></span></td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            @elseif ($hasGradesButNoClasses ?? false)
                <div class="text-center" style="padding: 60px 20px;">
                    <i class="fas fa-chalkboard" style="font-size: 56px; color: #d1d5db;"></i>
                    <h5 class="mt-3" style="color: #374151; font-weight: 600;">No Classes Allocated Yet</h5>
                    <p style="color: #6b7280; max-width: 420px; margin: 8px auto 24px;">
                        Grades are set up for this term but no classes have been created yet.
                        Allocate classes first, then return here to manage subject and teacher assignments.
                    </p>
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <span class="d-flex align-items-center gap-2" style="font-size: 13px; color: #6b7280;">
                            <i class="fas fa-graduation-cap" style="color: #3b82f6;"></i>
                            {{ $grades->count() }} {{ Str::plural('grade', $grades->count()) }} available:
                            {{ $grades->pluck('name')->join(', ') }}
                        </span>
                    </div>
                    @can('manage-academic')
                        <a href="{{ route('academic.index') }}" class="btn btn-primary mt-4" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); border: none; padding: 10px 20px; border-radius: 3px;">
                            <i class="fas fa-plus-circle me-1"></i> Go to Class Allocations
                        </a>
                    @endcan
                </div>
            @else
                <div class="text-center" style="padding: 60px 20px;">
                    <i class="fas fa-folder-open" style="font-size: 56px; color: #d1d5db;"></i>
                    <h5 class="mt-3" style="color: #374151; font-weight: 600;">No Grades Found</h5>
                    <p style="color: #6b7280; max-width: 420px; margin: 8px auto 0;">
                        There are no active grades for the selected term. Please ensure school mode provisioning has been completed for this term.
                    </p>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            function storeInLocalStorage(gradeId) {
                localStorage.setItem('selectedGradeListId', gradeId);
            }

            function getFromLocalStorage() {
                return localStorage.getItem('selectedGradeListId');
            }

            function updateClassLists() {
                var gradeId = $('#gradeId').val();
                var termId = $('#termId').val();

                var baseUrl =
                    "{{ route('academic.subjects-teachers', ['classId' => 'tempGradeId', 'termId' => 'tempTermId']) }}";
                var klassUrl = baseUrl.replace('tempGradeId', gradeId).replace('tempTermId', termId);

                $.get(klassUrl, function(data) {
                    $('#loadingPlaceholder').hide();
                    $('#subjectGradeList').html(data);
                }).fail(function() {
                    $('#loadingPlaceholder').hide();
                    $('#subjectGradeList').html(`
                        <div class="text-center text-muted" style="padding: 40px 0;">
                            <i class="fas fa-book-open" style="font-size: 48px; opacity: 0.3;"></i>
                            <p class="mt-3 mb-0" style="font-size: 15px;">No data available. Please check that classes have been allocated.</p>
                        </div>
                    `);
                });
            }

            function updateGrades() {
                var termId = $('#termId').val();
                var selectedGradeListId = getFromLocalStorage();

                var gradeClassesUrl = "{{ route('klasses.get-grades-list') }}";
                $.ajax({
                    url: gradeClassesUrl,
                    type: 'GET',
                    data: {
                        term_id: termId
                    },
                    success: function(data) {
                        var $gradeSelect = $('#gradeId');
                        var $controls = $('.controls-row');
                        $gradeSelect.empty();

                        var hasGrades = data && data.length > 0;
                        var hasClasses = hasGrades && data.some(function(grade) {
                            return grade.klasses && grade.klasses.length > 0;
                        });

                        if (!hasGrades) {
                            $controls.hide();
                            $('#loadingPlaceholder').hide();
                            $('#subjectGradeList').html(`
                                <div class="text-center" style="padding: 60px 20px;">
                                    <i class="fas fa-folder-open" style="font-size: 56px; color: #d1d5db;"></i>
                                    <h5 class="mt-3" style="color: #374151; font-weight: 600;">No Grades Found</h5>
                                    <p style="color: #6b7280; max-width: 420px; margin: 8px auto 0;">
                                        There are no active grades for the selected term. Please ensure school mode provisioning has been completed for this term.
                                    </p>
                                </div>
                            `);
                            return;
                        }

                        if (!hasClasses) {
                            var gradeNames = data.map(function(g) { return g.name; }).join(', ');
                            $controls.hide();
                            $('#loadingPlaceholder').hide();
                            $('#subjectGradeList').html(`
                                <div class="text-center" style="padding: 60px 20px;">
                                    <i class="fas fa-chalkboard" style="font-size: 56px; color: #d1d5db;"></i>
                                    <h5 class="mt-3" style="color: #374151; font-weight: 600;">No Classes Allocated Yet</h5>
                                    <p style="color: #6b7280; max-width: 420px; margin: 8px auto 24px;">
                                        Grades are set up for this term but no classes have been created yet.
                                        Allocate classes first, then return here to manage subject and teacher assignments.
                                    </p>
                                    <span style="font-size: 13px; color: #6b7280;">
                                        <i class="fas fa-graduation-cap" style="color: #3b82f6;"></i>
                                        ${data.length} grade(s) available: ${gradeNames}
                                    </span>
                                </div>
                            `);
                            return;
                        }

                        $controls.show();
                        $.each(data, function(index, grade) {
                            var $optgroup = $('<optgroup></optgroup>').attr('label', grade
                                .name);
                            $.each(grade.klasses, function(index, klass) {
                                var $option = $('<option></option>').val(klass.id).text(
                                    klass.name);
                                if (klass.id == selectedGradeListId) {
                                    $option.prop('selected', true);
                                }
                                $optgroup.append($option);
                            });
                            $gradeSelect.append($optgroup);
                        });
                        $gradeSelect.trigger('change');
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
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", xhr.status, xhr.statusText);
                        console.error("Detailed error:", error);
                        console.error("Response:", xhr.responseText);
                    },
                    success: function() {
                        updateGrades();
                    }
                });
            });

            $('#gradeId').change(function() {
                var gradeId = $(this).val();
                storeInLocalStorage(gradeId);
                updateClassLists();
            });

            var initialGradeId = getFromLocalStorage();
            if (initialGradeId) {
                $('#selectedGradeListId').val(initialGradeId);
                updateGrades();
            } else {
                $('#termId').trigger('change');
            }
        });
    </script>
@endsection
