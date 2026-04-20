@extends('layouts.master')
@section('title')
    Students Without Candidate Numbers
@endsection
@section('css')
    <style>
        .admissions-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .admissions-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .admissions-body {
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
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e2e8f0;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }

        .student-avatar-placeholder.male {
            background: #dbeafe;
            color: #1e40af;
        }

        .student-avatar-placeholder.female {
            background: #fce7f3;
            color: #be185d;
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

        .btn-back {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 6px 14px;
            border-radius: 3px;
            font-size: 13px;
            transition: all 0.2s ease;
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.25);
            color: white;
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('finals.students.index') }}">Finals</a>
        @endslot
        @slot('title')
            Remove Students
        @endslot
    @endcomponent

    <div class="admissions-container">
        <div class="admissions-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center gap-3">
                        <div>
                            <h3 style="margin:0;">Students Without Candidate Numbers</h3>
                            <p style="margin:6px 0 0 0; opacity:.9;">{{ $students->count() }} student(s) for
                                {{ $selectedYear }} &mdash; Select and remove students who should not be in the finals
                                module</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                </div>
            </div>
        </div>
        <div class="admissions-body">
            <div class="help-text">
                <div class="help-title">Remove Unregistered Students</div>
                <div class="help-content">
                    These students do not have a candidate/exam number assigned. Select the ones you want to remove from the
                    finals module.
                    Deleting a student will also remove their classes, subjects, and any exam results.
                </div>
            </div>

            <div class="row">
                <div class="col-10"></div>
                <div class="col-2 d-flex justify-content-end">
                    <button class="btn btn-danger d-none" id="bulkDeleteBtn"
                        style="border-radius:3px; padding: 10px 16px; font-weight: 500;">
                        <i class="bx bx-trash me-1"></i>Delete Selected (<span id="selectedCount">0</span>)
                    </button>
                </div>
            </div>

            @if ($students->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-sm align-middle" id="no-candidate-table">
                        <thead>
                            <tr>
                                <th style="width: 40px; min-width: 40px;" class="text-center">
                                    <input type="checkbox" id="selectAllStudents" title="Select All">
                                </th>
                                <th scope="col">Student</th>
                                <th scope="col">Gender</th>
                                <th scope="col">Class</th>
                                <th scope="col">Graduation Year</th>
                                <th scope="col" class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($students as $student)
                                @php
                                    $currentClass = $student->finalKlasses->first();
                                    $initials = strtoupper(
                                        substr($student->first_name ?? '', 0, 1) .
                                            substr($student->last_name ?? '', 0, 1),
                                    );
                                    $genderClass = $student->gender == 'M' ? 'male' : 'female';
                                @endphp
                                <tr data-student-id="{{ $student->id }}">
                                    <td class="text-center">
                                        <input type="checkbox" class="student-checkbox" value="{{ $student->id }}">
                                    </td>
                                    <td>
                                        <div class="student-cell">
                                            @if ($student->photo_path)
                                                <img src="{{ asset('storage/' . $student->photo_path) }}"
                                                    alt="{{ $student->full_name }}" class="rounded-circle" width="40"
                                                    height="40" style="object-fit: cover;">
                                            @else
                                                <div class="student-avatar-placeholder {{ $genderClass }}">
                                                    {{ $initials ?: 'ST' }}</div>
                                            @endif
                                            <div>
                                                <div class="fw-medium">{{ $student->full_name }}</div>
                                                @if ($student->formatted_id_number)
                                                    <small class="text-muted">ID:
                                                        {{ $student->formatted_id_number }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if ($student->gender == 'M')
                                            <span class="gender-male"><i class="bx bx-male-sign me-1"></i>Male</span>
                                        @elseif($student->gender == 'F')
                                            <span class="gender-female"><i class="bx bx-female-sign me-1"></i>Female</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($currentClass)
                                            <div>
                                                <div class="fw-medium">{{ $currentClass->name }}</div>
                                                <small
                                                    class="text-muted">{{ $student->graduationGrade->name ?? '' }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">No Class</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-medium">{{ $student->graduation_year }}</div>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('finals.students.show', $student) }}" class="btn btn-sm btn-primary">
                                            <i class="bx bx-edit-alt me-1"></i>Set Candidate #
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="bx bx-check-circle display-1 text-success" style="opacity: 0.5;"></i>
                    </div>
                    <h5 class="text-muted">All students have candidate numbers</h5>
                    <p class="text-muted mb-4">There are no students without candidate numbers for {{ $selectedYear }}.</p>
                    <a href="{{ route('finals.students.index') }}" class="btn btn-primary" style="border-radius:3px;">
                        <i class="bx bx-arrow-back me-1"></i>Back to Students
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            if ($('#no-candidate-table').length) {
                if ($.fn.DataTable.isDataTable('#no-candidate-table')) {
                    $('#no-candidate-table').DataTable().destroy();
                }
                $('#no-candidate-table').DataTable({
                    pageLength: 25,
                    dom: 'rtip',
                    columnDefs: [{
                        orderable: false,
                        targets: [0, 5]
                    }],
                    language: {
                        paginate: {
                            previous: "<i class='mdi mdi-chevron-left'></i>",
                            next: "<i class='mdi mdi-chevron-right'></i>"
                        }
                    },
                    drawCallback: function() {
                        $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
                    }
                });
            }

            // Select All checkbox
            $(document).on('change', '#selectAllStudents', function() {
                var checked = $(this).is(':checked');
                $('.student-checkbox:visible').prop('checked', checked);
                updateBulkDeleteBtn();
            });

            // Individual checkbox change
            $(document).on('change', '.student-checkbox', function() {
                updateBulkDeleteBtn();
                var allChecked = $('.student-checkbox:visible').length === $(
                    '.student-checkbox:visible:checked').length;
                $('#selectAllStudents').prop('checked', allChecked);
            });

            function updateBulkDeleteBtn() {
                var count = $('.student-checkbox:checked').length;
                $('#selectedCount').text(count);
                if (count > 0) {
                    $('#bulkDeleteBtn').removeClass('d-none');
                } else {
                    $('#bulkDeleteBtn').addClass('d-none');
                }
            }

            // Bulk delete
            $('#bulkDeleteBtn').on('click', function() {
                var ids = [];
                $('.student-checkbox:checked').each(function() {
                    ids.push($(this).val());
                });

                if (ids.length === 0) return;
                if (!confirm('Are you sure you want to delete ' + ids.length +
                        ' student(s)? This will remove all their classes, subjects, and exam results.'))
                    return;

                var btn = $(this);
                btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-1"></span>Deleting...');

                $.ajax({
                    url: "{{ route('finals.students.bulk-destroy') }}",
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    contentType: 'application/json',
                    data: JSON.stringify({
                        student_ids: ids
                    }),
                    success: function(res) {
                        if (res.success) {
                            // Remove deleted rows from the table
                            ids.forEach(function(id) {
                                var row = $('tr[data-student-id="' + id + '"]');
                                if ($.fn.DataTable.isDataTable('#no-candidate-table')) {
                                    $('#no-candidate-table').DataTable().row(row)
                                        .remove();
                                }
                            });

                            if ($.fn.DataTable.isDataTable('#no-candidate-table')) {
                                $('#no-candidate-table').DataTable().draw();
                            }

                            // Check if all students have been deleted
                            var remaining = $('#no-candidate-table tbody tr').length;
                            if (remaining === 0) {
                                window.location.href = "{{ route('finals.students.index') }}";
                                return;
                            }

                            // Update header count
                            $('.admissions-header p').text(remaining +
                                ' student(s) for {{ $selectedYear }} — Select and remove students who should not be in the finals module'
                            );

                            btn.prop('disabled', false).html(
                                '<i class="bx bx-trash me-1"></i>Delete Selected (<span id="selectedCount">0</span>)'
                            );
                            $('#bulkDeleteBtn').addClass('d-none');
                            $('#selectAllStudents').prop('checked', false);

                            // Show success alert
                            var alertHtml =
                                '<div class="alert alert-success alert-dismissible fade show mb-3" role="alert">' +
                                '<i class="mdi mdi-check-all me-2"></i><strong>' + res.message +
                                '</strong>' +
                                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
                            $('.admissions-body').prepend(alertHtml);

                            setTimeout(function() {
                                $('.alert').alert('close');
                            }, 5000);
                        } else {
                            btn.prop('disabled', false).html(
                                '<i class="bx bx-trash me-1"></i>Delete Selected (<span id="selectedCount">' +
                                ids.length + '</span>)');
                            alert(res.message || 'Failed to delete students.');
                        }
                    },
                    error: function() {
                        btn.prop('disabled', false).html(
                            '<i class="bx bx-trash me-1"></i>Delete Selected (<span id="selectedCount">' +
                            ids.length + '</span>)');
                        alert('An error occurred while deleting students.');
                    }
                });
            });
        });
    </script>
@endsection
