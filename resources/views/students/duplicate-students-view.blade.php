@extends('layouts.master')
@section('title')
    Duplicate Students
@endsection
@section('css')
    <style>
        .students-container {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 0;
            box-shadow: none;
        }

        .students-header {
            background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .students-body {
            padding: 24px;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px 16px;
            border-left: 4px solid #dc3545;
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
            line-height: 1.5;
            margin: 0;
        }

        .gender-male {
            color: #007bff;
        }

        .gender-female {
            color: #e83e8c;
        }

        .student-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .student-avatar-placeholder {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #fee2e2;
            color: #dc3545;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 12px;
        }

        .table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
        }

        .table tbody tr:hover {
            background-color: #f9fafb;
        }

        .table tbody tr.selected {
            background-color: #fef2f2 !important;
        }

        .table tbody tr.cannot-delete {
            background-color: #fafafa;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .bulk-actions {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 12px 16px;
            margin-bottom: 16px;
        }

        .badge-class {
            background: #dcfce7;
            color: #166534;
            font-weight: 500;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
        }

        .badge-no-class {
            background: #fef3c7;
            color: #92400e;
            font-weight: 500;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
        }

        .badge-grade {
            background: #e0f2fe;
            color: #0369a1;
            font-weight: 500;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
        }

        .badge-safe {
            background: #dcfce7;
            color: #166534;
            font-weight: 500;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
        }

        .badge-unsafe {
            background: #fee2e2;
            color: #991b1b;
            font-weight: 500;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
        }

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

        .term-select {
            max-width: 200px;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 8px 12px;
            font-size: 14px;
            color: #374151;
            background-color: #fff;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .term-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('students.index') }}">Students</a>
        @endslot
        @slot('title')
            Duplicate Students
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

    <div class="row  mb-3">
        <div class="col-12 d-flex justify-content-end">
            <select name="term" id="termId" class="form-select term-select">
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

    <div class="students-container">
        <div class="students-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="mb-1 text-white"><i class="fas fa-copy me-2"></i>Duplicate Students</h4>
                    <p class="mb-0 opacity-75">Students with identical names that may be duplicate entries</p>
                </div>
                <div class="col-md-6">
                    @php
                        $totalCount = $duplicateStudents ? $duplicateStudents->count() : 0;
                        $maleCount = $duplicateStudents ? $duplicateStudents->where('gender', 'M')->count() : 0;
                        $femaleCount = $duplicateStudents ? $duplicateStudents->where('gender', 'F')->count() : 0;
                    @endphp
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $totalCount }}</h4>
                                <small class="opacity-75">Total</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $maleCount }}</h4>
                                <small class="opacity-75">Male</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $femaleCount }}</h4>
                                <small class="opacity-75">Female</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="students-body">
            <div class="help-text">
                <div class="help-title">Duplicate Detection</div>
                <p class="help-content">
                    These students have identical names and may be duplicate entries. Students without class assignments can
                    be safely deleted.
                    Review carefully before taking action.
                </p>
            </div>

            @if ($duplicateStudents && $duplicateStudents->count() > 0)
                @php
                    $deletableCount = $duplicateStudents
                        ->filter(function ($student) {
                            return !$student->currentClassRelation->first();
                        })
                        ->count();
                @endphp

                <div class="bulk-actions">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-3">
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" id="selectAllCheckbox">
                                    <label class="form-check-label fw-medium" for="selectAllCheckbox">
                                        Select All Eligible
                                    </label>
                                </div>
                                <span class="badge bg-danger">Total: {{ $duplicateStudents->count() }}</span>
                                <span class="badge bg-success">Deletable: {{ $deletableCount }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 text-md-end mt-3 mt-md-0">
                            <button type="button" id="deleteSelectedStudents" class="btn btn-danger btn-sm" disabled>
                                <i class="fas fa-trash me-1"></i> Delete Selected (<span id="selectedCount">0</span>)
                            </button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 40px;">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" id="tableSelectAllCheckbox">
                                    </div>
                                </th>
                                <th>Student</th>
                                <th>Gender</th>
                                <th>ID Number</th>
                                <th>Date of Birth</th>
                                <th>Class</th>
                                <th>Grade</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($duplicateStudents as $student)
                                @php
                                    $currentClass = $student->currentClassRelation->first();
                                    $canDelete = !$currentClass;
                                @endphp
                                <tr class="student-row {{ $canDelete ? '' : 'cannot-delete' }}"
                                    data-student-id="{{ $student->id }}">
                                    <td>
                                        <div class="form-check mb-0">
                                            <input class="form-check-input student-checkbox" type="checkbox"
                                                value="{{ $student->id }}" id="student_{{ $student->id }}"
                                                {{ $canDelete ? '' : 'disabled' }}>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="student-cell">
                                            <div class="student-avatar-placeholder">
                                                {{ strtoupper(substr($student->first_name, 0, 1)) }}{{ strtoupper(substr($student->last_name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-medium">{{ $student->fullName }}</div>
                                                <small
                                                    class="text-muted">{{ $student->exam_number ?? 'No exam number' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if ($student->gender === 'M')
                                            <span class="gender-male"><i class="bx bx-male-sign me-1"></i>Male</span>
                                        @else
                                            <span class="gender-female"><i class="bx bx-female-sign me-1"></i>Female</span>
                                        @endif
                                    </td>
                                    <td class="text-muted">{{ $student->formatted_id_number ?? '—' }}</td>
                                    <td class="text-muted">
                                        {{ $student->formatted_date_of_birth ?: '—' }}
                                    </td>
                                    <td>
                                        @if ($currentClass)
                                            <span class="badge-class">{{ $currentClass->name }}</span>
                                        @else
                                            <span class="badge-no-class">No Class</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($student->currentGrade)
                                            <span class="badge-grade">{{ $student->currentGrade->name }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($canDelete)
                                            <span class="badge-safe">Safe to Delete</span>
                                        @else
                                            <span class="badge-unsafe">Has Class</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-check-circle text-success" style="font-size: 48px; opacity: 0.5;"></i>
                    <h5 class="mt-3 text-success">No Duplicate Students Found</h5>
                    <p class="text-muted mb-0">All students in the current term appear to be unique.</p>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            $('#termId').change(function() {
                let term = $(this).val();
                let termSessionUrl = "{{ route('students.term-session') }}";

                $.ajax({
                    url: termSessionUrl,
                    method: 'POST',
                    data: {
                        term_id: term,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function() {
                        window.location.reload();
                    },
                    error: function(xhr, status, error) {
                        console.error("Term change error:", error);
                    }
                });
            });

            function updateSelectedCount() {
                let selectedCount = $('.student-checkbox:checked').length;
                $('#selectedCount').text(selectedCount);
                $('#deleteSelectedStudents').prop('disabled', selectedCount === 0);
            }

            function updateSelectAllCheckboxes() {
                let totalCheckboxes = $('.student-checkbox:not(:disabled)').length;
                let checkedCheckboxes = $('.student-checkbox:checked').length;
                let allSelected = totalCheckboxes > 0 && checkedCheckboxes === totalCheckboxes;
                $('#selectAllCheckbox, #tableSelectAllCheckbox').prop('checked', allSelected);
            }

            $(document).on('change', '.student-checkbox', function() {
                updateSelectedCount();
                updateSelectAllCheckboxes();

                let row = $(this).closest('tr');
                if ($(this).is(':checked')) {
                    row.addClass('selected');
                } else {
                    row.removeClass('selected');
                }
            });

            $(document).on('change', '#selectAllCheckbox, #tableSelectAllCheckbox', function() {
                let isChecked = $(this).is(':checked');
                $('.student-checkbox:not(:disabled)').prop('checked', isChecked);
                $('#selectAllCheckbox, #tableSelectAllCheckbox').prop('checked', isChecked);

                $('.student-row').each(function() {
                    let checkbox = $(this).find('.student-checkbox');
                    if (!checkbox.is(':disabled')) {
                        $(this).toggleClass('selected', isChecked);
                    }
                });

                updateSelectedCount();
            });

            $('#deleteSelectedStudents').click(function() {
                let selectedIds = [];
                $('.student-checkbox:checked').each(function() {
                    selectedIds.push($(this).val());
                });

                if (selectedIds.length === 0) return;

                let confirmMessage =
                    `Are you sure you want to delete ${selectedIds.length} selected duplicate student${selectedIds.length > 1 ? 's' : ''}? This action cannot be undone.`;

                if (confirm(confirmMessage)) {
                    $('#deleteSelectedStudents').prop('disabled', true).html(
                        '<i class="fas fa-spinner fa-spin me-1"></i> Deleting...'
                    );

                    $.ajax({
                        url: '{{ route('students.delete-multiple') }}',
                        method: 'POST',
                        data: {
                            student_ids: selectedIds,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                window.location.reload();
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = 'An error occurred while deleting students.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            alert('Error: ' + errorMessage);
                            $('#deleteSelectedStudents').prop('disabled', false).html(
                                '<i class="fas fa-trash me-1"></i> Delete Selected (<span id="selectedCount">0</span>)'
                            );
                        }
                    });
                }
            });

            updateSelectedCount();
        });
    </script>
@endsection
