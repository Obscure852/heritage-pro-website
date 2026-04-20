@extends('layouts.master')
@section('title')
    Criteria Based Tests
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

        /* Action Button */
        .btn-add-new {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 16px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            border-radius: 3px;
            color: white;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-add-new:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
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
            <a class="text-muted" href="{{ route('assessment.test-list') }}">Assessment</a>
        @endslot
        @slot('title')
            Criteria Based Tests
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
            <h3><i class="fas fa-clipboard-check me-2"></i>Criteria Based Tests</h3>
            <p>Manage continuous assessment tests and examinations</p>
        </div>

        <div class="settings-body">
            <div class="help-text">
                <div class="help-title">About Criteria Based Tests</div>
                <div class="help-content">
                    Create and manage tests that are assessed based on specific criteria. This includes continuous
                    assessment tests and examinations.
                    Select a term and grade to view or create tests.
                </div>
            </div>

            <input type="hidden" id="selectedGradeId" value="{{ session('selectedGradeId') }}">

            <div class="controls-row">
                <div class="d-flex align-items-center gap-4 flex-wrap">
                    <div class="filter-selector">
                        @if (!empty($grades))
                            <select name="gradeId" id="gradeId" class="form-select">
                                @foreach ($grades as $index => $grade)
                                    <option value="{{ $grade->id }}" {{ $index == 0 ? 'selected' : '' }}>
                                        {{ $grade->name }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                </div>

                @if (!session('is_past_term'))
                    <a href="{{ route('reception.create-test') }}" class="btn-add-new">
                        <i class="bx bx-plus"></i> New Test
                    </a>
                @endif
            </div>

            <!-- Tests List -->
            <div id="tests_list"></div>
        </div>
    </div>
@endsection

@section('script')
    <script>
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

        function updateClassLists(gradeId) {
            var baseUrl = "{{ route('reception.criteria-tests-list', ['gradeId' => 'tempGradeId']) }}";
            var criteriaBasedTestsUrl = baseUrl.replace('tempGradeId', gradeId);

            $.get(criteriaBasedTestsUrl, function(data) {
                $('#tests_list').html(data);
                $('#criteria-based-tests-table').DataTable();

                // Initialize tabs and copy modal after AJAX load
                if (typeof window.initializeCriteriaTabs === 'function') {
                    window.initializeCriteriaTabs();
                }
                if (typeof window.initializeCopyCriteriaTestModal === 'function') {
                    window.initializeCopyCriteriaTestModal();
                }
            });
        }

        function updateGrades() {
            var termId = $('#termId').val();
            var selectedGradeId = $('#selectedGradeId').val();
            var getGradesForTerm = "{{ route('klasses.get-grades-for-term') }}";

            $.ajax({
                url: getGradesForTerm,
                type: 'GET',
                data: {
                    'term_id': termId,
                },
                success: function(data) {
                    var $gradeSelect = $('#gradeId');
                    $gradeSelect.empty();

                    var isSelectedSet = false;
                    $.each(data, function(index, grade) {
                        var $option = $('<option></option>').val(grade.id).text(grade.name);
                        if (grade.id == selectedGradeId) {
                            $option.prop('selected', true);
                            isSelectedSet = true;
                        }
                        $gradeSelect.append($option);
                    });

                    if (!isSelectedSet && $gradeSelect.find('option').length > 0) {
                        $gradeSelect.find('option:first').prop('selected', true);
                        selectedGradeId = $gradeSelect.find('option:first').val();
                    }
                    updateClassLists(selectedGradeId);
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                }
            });
        }

        $('#gradeId').change(function() {
            var gradeId = $(this).val();
            storeInSession(gradeId);
            updateClassLists(gradeId);
        });

        $('#termId').trigger('change');

        function storeInSession(gradeId) {
            var storeSelectedClassUrl = "{{ route('academic.store-selected-class') }}";
            $.ajax({
                url: storeSelectedClassUrl,
                type: 'POST',
                data: {
                    gradeId: gradeId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    console.log("Selection saved", response);
                },
                error: function(xhr, status, error) {
                    console.error("Error saving selection");
                }
            });
        }
    </script>
@endsection
