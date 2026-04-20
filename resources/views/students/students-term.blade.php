<style>
    .student-cell {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .student-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        background: #e2e8f0;
    }

    .student-avatar-placeholder {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #e2e8f0;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 16px;
    }

    .student-type-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        color: #fff;
        margin-left: 6px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .student-type-indicator {
        position: relative;
        padding-left: 8px;
    }

    .student-type-indicator::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 4px;
        height: 70%;
        border-radius: 2px;
    }

    .action-buttons {
        display: flex;
        gap: 4px;
        justify-content: flex-end;
    }

    .action-buttons .btn {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 3px;
        transition: all 0.2s ease;
    }

    .action-buttons .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .action-buttons .btn i {
        font-size: 16px;
    }

    .gender-male {
        color: #007bff;
    }

    .gender-female {
        color: #e83e8c;
    }

    .badge-class {
        background: #e0f2fe;
        color: #0369a1;
        font-weight: 500;
        padding: 4px 8px;
        border-radius: 3px;
        font-size: 12px;
    }

    .badge-grade {
        background: #f0fdf4;
        color: #166534;
        font-weight: 500;
        padding: 4px 8px;
        border-radius: 3px;
        font-size: 12px;
    }
</style>
@if (!empty($students) && $students->count() > 0)
    <div class="table-responsive">
        <table id="d-tables" class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th>Gender</th>
                    <th>Date of Birth</th>
                    <th>Class</th>
                    <th>Grade</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($students as $index => $student)
                    <tr class="student-row"
                        data-name="{{ strtolower($student->full_name) }}"
                        data-gender="{{ strtolower($student->gender ?? '') }}"
                        data-class="{{ strtolower($student->currentClass()->name ?? '') }}"
                        data-grade="{{ strtolower($student->currentGrade->name ?? '') }}"
                        style="--i: {{ $index }}; {{ $student->type && $student->type->color ? 'border-left: 4px solid ' . $student->type->color . '; background-color: ' . $student->type->color . '10;' : '' }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="student-cell">
                                @if ($student->photo_path)
                                    <img src="{{ URL::asset($student->photo_path) }}"
                                        alt="{{ $student->full_name }}"
                                        class="student-avatar"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="student-avatar-placeholder" style="display: none;">
                                        {{ strtoupper(substr($student->first_name ?? '', 0, 1) . substr($student->last_name ?? '', 0, 1)) }}
                                    </div>
                                @else
                                    <div class="student-avatar-placeholder">
                                        {{ strtoupper(substr($student->first_name ?? '', 0, 1) . substr($student->last_name ?? '', 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="d-flex align-items-center flex-wrap gap-1">
                                        <a href="{{ route('students.show', $student->id) }}" class="text-dark fw-medium">
                                            {{ $student->full_name }}
                                        </a>
                                        @if($student->type)
                                            <span class="student-type-badge" style="background-color: {{ $student->type->color ?? '#6c757d' }};">
                                                <i class="fas fa-universal-access" style="font-size: 9px;"></i>
                                                {{ $student->type->type }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-muted" style="font-size: 12px;">
                                        ID: {{ $student->formatted_id_number ?? '—' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if ($student->gender == 'M')
                                <span class="gender-male"><i class="bx bx-male-sign"></i> Male</span>
                            @else
                                <span class="gender-female"><i class="bx bx-female-sign"></i> Female</span>
                            @endif
                        </td>
                        <td>{{ $student->formatted_date_of_birth }}</td>
                        <td>
                            @if($student->currentClass()->name ?? null)
                                <span class="badge-class">{{ $student->currentClass()->name }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($student->currentGrade->name ?? null)
                                <span class="badge-grade">{{ $student->currentGrade->name }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="action-buttons">
                                @can('students-view')
                                    <a href="{{ route('students.show', $student->id) }}"
                                        class="btn btn-sm btn-outline-info"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        title="View & Edit">
                                        <i class="bx bx-edit-alt"></i>
                                    </a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="text-center text-muted py-5">
        <i class="bx bx-user-x" style="font-size: 48px; opacity: 0.5;"></i>
        <p class="mt-3">No students found matching your criteria.</p>
    </div>
@endif

<script>
    $(document).ready(function() {
        initializeStudentsTooltips();
    });

    function initializeStudentsTooltips() {
        var existingTooltips = document.querySelectorAll('#d-tables [data-bs-toggle="tooltip"]');
        existingTooltips.forEach(function(el) {
            var tooltip = bootstrap.Tooltip.getInstance(el);
            if (tooltip) {
                tooltip.dispose();
            }
        });

        var tooltipTriggerList = [].slice.call(document.querySelectorAll('#d-tables [data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
</script>
