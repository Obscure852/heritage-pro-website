@if ($groupedData->count() > 0)
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ $finalsDefinition->examLabel ?? 'Finals' }} Grade Subjects
                    <span
                        class="text-muted fw-normal ms-2">({{ $groupedData->sum(function ($group) {return $group['grade_subjects']->count();}) }}
                        subjects)</span>
                </h5>
            </div>
            <div class="card-body">
                <div style="padding-right: 10px;" class="table-responsive mb-4">
                    <table class="table table-striped table-sm rounded w-100" id="grade-subjects-table">
                        <thead>
                            <tr>
                                <th scope="col">Subject</th>
                                <th scope="col">Grade</th>
                                <th scope="col">Department</th>
                                <th scope="col">Type</th>
                                <th scope="col">Classes</th>
                                <th scope="col">Optional</th>
                                <th scope="col">Graduation</th>
                                <th style="width: 80px; min-width: 80px;" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($groupedData as $gradeGroup)
                                @foreach ($gradeGroup['grade_subjects'] as $gradeSubject)
                                    <tr>
                                        <td>
                                            <div class="subject-cell">
                                                @php
                                                    $initials = strtoupper(
                                                        substr($gradeSubject['subject_name'] ?? '', 0, 2),
                                                    );
                                                @endphp
                                                <div class="subject-avatar-placeholder">{{ $initials ?: 'SB' }}</div>
                                                <div>
                                                    <span class="fw-medium">{{ $gradeSubject['subject_name'] }}</span>
                                                    @if ($gradeSubject['subject_code'])
                                                        <small class="text-muted d-block"
                                                            style="font-size: 10px;">{{ $gradeSubject['subject_code'] }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $gradeGroup['grade_name'] }}</span>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $gradeSubject['department'] }}</span>
                                        </td>
                                        <td>
                                            @if ($gradeSubject['mandatory'])
                                                <span class="badge bg-success" style="font-size: 10px;">Mandatory</span>
                                            @else
                                                <span class="badge bg-warning" style="font-size: 10px;">Optional</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($gradeSubject['total_classes'] > 0)
                                                <span
                                                    class="text-success fw-medium">{{ $gradeSubject['total_classes'] }}</span>
                                                @if ($gradeSubject['classes']->count() > 0)
                                                    <small class="text-muted d-block" style="font-size: 10px;">
                                                        {{ $gradeSubject['classes']->first()['klass_name'] }}
                                                        @if ($gradeSubject['classes']->count() > 1)
                                                            +{{ $gradeSubject['classes']->count() - 1 }}
                                                        @endif
                                                    </small>
                                                @endif
                                            @else
                                                <span class="text-warning" style="font-size: 11px;">None</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($gradeSubject['type'])
                                                <span class="badge bg-success" style="font-size: 10px;">Core</span>
                                            @else
                                                <span class="badge bg-warning" style="font-size: 10px;">Option</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span>{{ $gradeSubject['graduation_year'] }}</span>
                                            <small class="text-muted d-block"
                                                style="font-size: 10px;">{{ $gradeSubject['graduation_term'] }}</small>
                                        </td>
                                        <td class="text-end">
                                            <div class="action-buttons">
                                                <a href="{{ route('finals.subjects.edit', ['finalGradeSubject' => $gradeSubject['id'], 'finals_context' => $finalsDefinition->context]) }}"
                                                    class="btn btn-sm btn-outline-info" title="Edit Subject">
                                                    <i class="bx bx-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="col-md-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="mb-3">
                    <i class="bx bx-book-content display-1 text-muted" style="opacity: 0.5;"></i>
                </div>
                <h5 class="text-muted">No Grade Subjects Found</h5>
                <p class="text-muted mb-4">
                    There are no grade subjects for the selected year. Grade subjects are created during year rollover.
                </p>
                <button type="button" class="btn btn-sm btn-primary" disabled>
                    <i class="bx bx-refresh me-1"></i>View All Years
                </button>
            </div>
        </div>
    </div>
@endif

<script>
    $(document).ready(function() {
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>
