@extends('layouts.master')
@section('title')
    Subjects | Academic Management
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

        /* Action Buttons */
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

        /* Delete Confirmation Box */
        .delete-confirmation-box {
            display: none;
            margin-bottom: 24px;
        }

        .delete-confirmation-box .confirmation-card {
            border: 1px solid #dc2626;
            border-radius: 3px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.15);
        }

        .delete-confirmation-box .confirmation-header {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            padding: 16px 20px;
            position: relative;
        }

        .delete-confirmation-box .confirmation-header h5 {
            margin: 0;
            font-weight: 600;
        }

        .delete-confirmation-box .close-btn {
            position: absolute;
            top: 12px;
            right: 16px;
            color: rgba(255, 255, 255, 0.8);
            cursor: pointer;
            font-size: 1.5rem;
            line-height: 1;
            transition: color 0.15s ease-in-out;
            background: none;
            border: none;
        }

        .delete-confirmation-box .close-btn:hover {
            color: white;
        }

        .delete-confirmation-box .confirmation-body {
            padding: 20px;
            background: white;
        }

        .delete-confirmation-box .avatar-title {
            background-color: #fee2e2;
            color: #dc2626;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            margin: 0 auto;
        }

        .delete-confirmation-box .items-card {
            border: 1px solid #fecaca;
            border-radius: 3px;
            overflow: hidden;
        }

        .delete-confirmation-box .items-header {
            background: #fef2f2;
            padding: 12px 16px;
            border-bottom: 1px solid #fecaca;
        }

        .delete-confirmation-box .items-header h6 {
            margin: 0;
            color: #991b1b;
            font-size: 14px;
            font-weight: 600;
        }

        .delete-confirmation-box .list-group-item {
            border-left: none;
            border-right: none;
            padding: 10px 16px;
        }

        .delete-confirmation-box .list-group-item:first-child {
            border-top: none;
        }

        .delete-confirmation-box .list-group-item-danger {
            background: #fef2f2;
        }

        .fancy-checkbox {
            position: relative;
            padding: 12px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            background: #f9fafb;
            transition: all 0.2s ease-in-out;
        }

        .fancy-checkbox:hover {
            background: white;
            border-color: #dc2626;
        }

        .fancy-checkbox.checked {
            background: #fef2f2;
            border-color: #dc2626;
        }

        .btn-cancel {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            color: #374151;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-cancel:hover {
            background: #f3f4f6;
            color: #1f2937;
        }

        .btn-delete-confirm {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border: none;
            border-radius: 3px;
            color: white;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-delete-confirm:hover:not(:disabled) {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
            color: white;
        }

        .btn-delete-confirm:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Shimmer Loading */
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

        /* Content Container */
        #subjectByGrade .card {
            background: white;
            border-radius: 3px;
            border: 1px solid #e5e7eb;
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
            <a class="text-muted" href="{{ route('academic.index') }}">Academic</a>
        @endslot
        @slot('title')
            Subject Allocations
        @endslot
    @endcomponent

    @if (session('message'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{!! session('message') !!}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>{!! session('error') !!}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Box -->
    <div id="deleteConfirmationBox" class="delete-confirmation-box">
        <div class="confirmation-card">
            <div class="confirmation-header">
                <h5><i class="bx bx-trash-alt me-2"></i>Confirm Subject Deletion</h5>
                <button type="button" class="close-btn" onclick="hideDeleteConfirmation()" title="Close">&times;</button>
            </div>
            <div class="confirmation-body">
                <div class="text-center mb-4">
                    <div class="avatar-title mb-3">
                        <i class="bx bx-error-circle font-size-24"></i>
                    </div>
                    <h5 id="subjectNameToDelete" class="mb-1"></h5>
                    <p class="text-muted">This action will permanently delete the subject and all related data.</p>
                </div>

                <div class="items-card mb-4">
                    <div class="items-header">
                        <h6>The following data will be permanently deleted:</h6>
                    </div>
                    <ul class="list-group list-group-flush" id="itemsToDeleteList">
                        <!-- Items will be added here dynamically -->
                    </ul>
                </div>

                <div class="alert alert-warning d-flex mb-3">
                    <div class="me-3">
                        <i class="bx bx-info-circle font-size-20"></i>
                    </div>
                    <div>
                        <strong>Warning:</strong> This action cannot be undone. All data related to this subject will be
                        permanently deleted from the system.
                    </div>
                </div>

                <div class="fancy-checkbox mb-4" id="confirmCheckboxContainer">
                    <div class="form-check mb-0">
                        <input type="checkbox" class="form-check-input" id="confirmDeleteCheckbox">
                        <label class="form-check-label fw-medium" for="confirmDeleteCheckbox">
                            I understand that this action is irreversible
                        </label>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn-cancel" onclick="hideDeleteConfirmation()">
                        <i class="bx bx-x"></i> Cancel
                    </button>
                    <button type="button" id="confirmDeleteBtn" class="btn-delete-confirm" disabled
                        onclick="proceedWithDeletion()">
                        <i class="bx bx-trash"></i> Delete Subject
                    </button>
                </div>
            </div>
        </div>
    </div>

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
            <h3><i class="fas fa-book me-2"></i>Subject Allocations</h3>
            <p>Manage subject assignments for each grade level</p>
        </div>

        <div class="settings-body">
            <div class="help-text">
                <div class="help-title">About Subject Allocations</div>
                <div class="help-content">
                    Assign subjects to grade levels and configure their properties. You can set subjects as mandatory or
                    optional,
                    add grading scales, and manage subject components.
                </div>
            </div>

            <input type="hidden" id="selectedSubjectGradeId" value="{{ session('selectedSubjectGradeId') }}">

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

                @can('manage-academic')
                    @if (!session('is_past_term'))
                        <div class="d-flex gap-2">
                            <a href="{{ route('subjects.create') }}" class="btn-add-new">
                                <i class="bx bx-plus"></i> New Subject
                            </a>
                            @if ($school_type->type === 'Primary')
                                <a href="{{ route('subject.create-grade-option') }}" class="btn-add-new">
                                    <i class="bx bx-plus"></i> New Grade Option
                                </a>
                            @endif
                        </div>
                    @endif
                @endcan
            </div>

            <!-- Subjects List -->
            <div id="subjectByGrade">
                <!-- Subject list will be loaded here via AJAX -->
            </div>

            <!-- Loading Placeholder -->
            <div id="loadingPlaceholder" class="placeholder-card">
                <table class="placeholder-table">
                    <thead>
                        <tr>
                            <th>
                                <div class="shimmer-bg" style="height: 16px; width: 30px;"></div>
                            </th>
                            <th>
                                <div class="shimmer-bg" style="height: 16px; width: 100%;"></div>
                            </th>
                            <th>
                                <div class="shimmer-bg" style="height: 16px; width: 100%;"></div>
                            </th>
                            <th>
                                <div class="shimmer-bg" style="height: 16px; width: 100%;"></div>
                            </th>
                            <th>
                                <div class="shimmer-bg" style="height: 16px; width: 100%;"></div>
                            </th>
                            <th>
                                <div class="shimmer-bg" style="height: 16px; width: 100%;"></div>
                            </th>
                            <th>
                                <div class="shimmer-bg" style="height: 16px; width: 100%;"></div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 0; $i < 5; $i++)
                            <tr>
                                <td>
                                    <div class="shimmer-bg" style="height: 14px; width: 20px;"></div>
                                </td>
                                <td>
                                    <div class="shimmer-bg" style="height: 14px; width: 120px;"></div>
                                </td>
                                <td>
                                    <div class="shimmer-bg" style="height: 14px; width: 60px;"></div>
                                </td>
                                <td>
                                    <div class="shimmer-bg" style="height: 14px; width: 50px;"></div>
                                </td>
                                <td>
                                    <div class="shimmer-bg" style="height: 14px; width: 80px;"></div>
                                </td>
                                <td>
                                    <div class="shimmer-bg" style="height: 14px; width: 50px;"></div>
                                </td>
                                <td>
                                    <div class="shimmer-bg" style="height: 14px; width: 100px;"></div>
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
        let deleteUrl = '';

        $(document).ready(function() {
            function storeInLocalStorage(gradeId) {
                localStorage.setItem('selectedSubjectGradeId', gradeId);
            }

            function getFromLocalStorage() {
                return localStorage.getItem('selectedSubjectGradeId');
            }

            function updateClassLists(gradeId) {
                var baseUrl = "{{ route('subjects.subject-by-grade', ['gradeId' => 'tempGradeId']) }}";
                var subjectUrl = baseUrl.replace('tempGradeId', gradeId);

                $.get(subjectUrl, function(data) {
                    $('#loadingPlaceholder').hide();
                    $('#subjectByGrade').html(data);
                    if (window.initializeSubjectGradeSyllabusViewer) {
                        window.initializeSubjectGradeSyllabusViewer();
                    }
                }).fail(function() {
                    $('#loadingPlaceholder').hide();
                    $('#subjectByGrade').html(`
                        <div class="alert alert-danger">
                            <i class="bx bx-error-circle me-2"></i>
                            Failed to load subjects. Please try again.
                        </div>
                    `);
                });
            }

            function updateGrades() {
                var termId = $('#termId').val();
                var selectedSubjectGradeId = getFromLocalStorage();
                var getGradesForTerm = "{{ route('klasses.get-grades-for-term') }}";

                $.ajax({
                    url: getGradesForTerm,
                    type: 'GET',
                    data: {
                        term_id: termId,
                    },
                    success: function(data) {
                        var $gradeSelect = $('#gradeId');
                        $gradeSelect.empty();

                        var isSelectedSet = false;
                        $.each(data, function(index, grade) {
                            var $option = $('<option></option>').val(grade.id).text(grade.name);
                            if (grade.id == selectedSubjectGradeId) {
                                $option.prop('selected', true);
                                isSelectedSet = true;
                            }
                            $gradeSelect.append($option);
                        });

                        if (!isSelectedSet && $gradeSelect.find('option').length > 0) {
                            $gradeSelect.find('option:first').prop('selected', true);
                            selectedSubjectGradeId = $gradeSelect.find('option:first').val();
                        }
                        $('#selectedSubjectGradeId').val(selectedSubjectGradeId);
                        updateClassLists(selectedSubjectGradeId);
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
                    success: function() {
                        updateGrades();
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", xhr.status, xhr.statusText);
                    }
                });
            });

            $('#gradeId').change(function() {
                var gradeId = $(this).val();
                $('#selectedSubjectGradeId').val(gradeId);
                storeInLocalStorage(gradeId);
                updateClassLists(gradeId);
            });

            var initialGradeId = getFromLocalStorage();
            if (initialGradeId) {
                updateGrades();
            } else {
                $('#termId').trigger('change');
            }

            $('#confirmDeleteCheckbox').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#confirmCheckboxContainer').addClass('checked');
                    $('#confirmDeleteBtn').prop('disabled', false);
                } else {
                    $('#confirmCheckboxContainer').removeClass('checked');
                    $('#confirmDeleteBtn').prop('disabled', true);
                }
            });
        });

        function confirmDelete(subjectId, subjectName, testsCount, criteriaBasedTestsCount, componentsCount, isOptional) {
            hideDeleteConfirmation();
            $('#itemsToDeleteList').empty();
            $('#subjectNameToDelete').text(subjectName);

            const items = [{
                    name: 'Tests',
                    count: testsCount,
                    icon: 'bx-clipboard'
                },
                {
                    name: 'Criteria-based Tests',
                    count: criteriaBasedTestsCount,
                    icon: 'bx-list-check'
                },
                {
                    name: 'Components',
                    count: componentsCount,
                    icon: 'bx-cube'
                },
                {
                    name: 'Student Test Records',
                    count: 'All',
                    icon: 'bx-user-check'
                },
                {
                    name: 'Grading Scales',
                    count: 'All',
                    icon: 'bx-bar-chart-alt-2'
                },
                {
                    name: 'Subject Comments',
                    count: 'All',
                    icon: 'bx-message-detail'
                }
            ];

            if (isOptional === 'Yes') {
                items.push({
                    name: 'Optional Subject Allocations',
                    count: 'All',
                    icon: 'bx-select-multiple'
                });
            } else {
                items.push({
                    name: 'Class Subject Assignments',
                    count: 'All',
                    icon: 'bx-book-content'
                });
            }

            items.forEach(item => {
                const hasData = item.count === 'All' || item.count > 0;
                const listItem = $('<li>').addClass(
                    'list-group-item d-flex justify-content-between align-items-center');

                if (hasData) {
                    listItem.addClass('list-group-item-danger');
                }

                const leftDiv = $('<div>').addClass('d-flex align-items-center');
                const iconSpan = $('<span>').addClass('me-2').html(`<i class="bx ${item.icon}"></i>`);
                const textSpan = $('<span>').text(item.name);

                leftDiv.append(iconSpan).append(textSpan);

                const countBadge = $('<span>')
                    .addClass(hasData ? 'badge bg-danger rounded-pill' : 'badge bg-secondary rounded-pill')
                    .text(item.count === 'All' ? 'All' : item.count);

                listItem.append(leftDiv).append(countBadge);
                $('#itemsToDeleteList').append(listItem);
            });

            var baseUrl = "{{ route('subject.delete', ['id' => 'tempSubjectId']) }}";
            deleteUrl = baseUrl.replace('tempSubjectId', subjectId);

            $('#confirmDeleteCheckbox').prop('checked', false).change();
            $('#confirmCheckboxContainer').removeClass('checked');
            $('#confirmDeleteBtn').prop('disabled', true);

            $('#deleteConfirmationBox').fadeIn(300);

            $('html, body').animate({
                scrollTop: $('#deleteConfirmationBox').offset().top - 100
            }, 500);
        }

        function hideDeleteConfirmation() {
            $('#deleteConfirmationBox').fadeOut(200);
        }

        function proceedWithDeletion() {
            if ($('#confirmDeleteCheckbox').is(':checked')) {
                const $btn = $('#confirmDeleteBtn');
                $btn.html(
                    '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Deleting...'
                );
                $btn.prop('disabled', true);

                setTimeout(function() {
                    window.location.href = deleteUrl;
                }, 500);
            }
        }
    </script>
@endsection
