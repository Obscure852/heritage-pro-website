@if ($students->count() > 0)
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ $finalsDefinition->examLabel ?? 'Finals' }} Students List
                    <span class="text-muted fw-normal ms-2">({{ $students->count() }} students)</span>
                </h5>
            </div>
            <div class="card-body">
                <div style="padding-right: 10px;" class="table-responsive mb-4">
                    <table class="table table-striped table-sm rounded  w-100" id="finals-table">
                        <thead>
                            <tr>
                                <th scope="col">Students</th>
                                <th scope="col">Candidate Number</th>
                                <th scope="col">Gender</th>
                                <th scope="col">Class</th>
                                <th scope="col">Graduation</th>
                                <th scope="col">Exam Results</th>
                                <th scope="col">Overall Grade</th>
                                <th style="width: 80px; min-width: 80px;" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($students as $student)
                                @php
                                    $latestResult = $student->externalExamResults->first();
                                    $hasResults = $latestResult !== null;
                                    $currentClass = $student->finalKlasses->first();
                                @endphp
                                <tr>
                                    <td>
                                        <div class="student-cell">
                                            @php
                                                $initials = strtoupper(
                                                    substr($student->first_name ?? '', 0, 1) .
                                                        substr($student->last_name ?? '', 0, 1),
                                                );
                                                $genderClass = $student->gender == 'M' ? 'male' : 'female';
                                            @endphp
                                            @if ($student->photo_path)
                                                <img src="{{ asset('storage/' . $student->photo_path) }}"
                                                    alt="{{ $student->full_name }}" class="rounded-circle"
                                                    width="40" height="40" style="object-fit: cover;">
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
                                        @if ($student->exam_number)
                                            <span class="badge bg-light text-dark">{{ $student->exam_number }}</span>
                                        @else
                                            <span class="badge bg-secondary text-white">Not entered</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($student->gender == 'M')
                                            <span class="gender-male">
                                                <i class="bx bx-male-sign me-1"></i>Male
                                            </span>
                                        @elseif($student->gender == 'F')
                                            <span class="gender-female">
                                                <i class="bx bx-female-sign me-1"></i>Female
                                            </span>
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
                                        <div>
                                            <div class="fw-medium">{{ $student->graduation_year }}</div>
                                            <small
                                                class="text-muted">{{ $student->graduationTerm->name ?? '' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        @if ($hasResults)
                                            <div class="d-flex align-items-center">
                                                <i class="bx bxs-check-circle text-success me-1"></i>
                                                <span class="text-success fw-medium">Available</span>
                                            </div>
                                            <small class="text-muted">
                                                {{ $latestResult->total_subjects }} subjects
                                            </small>
                                        @else
                                            <div class="d-flex align-items-center">
                                                <i class="bx bxs-time text-warning me-1"></i>
                                                <span class="text-warning fw-medium">Pending</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            // Use calculated_overall_grade as fallback when overall_grade is null/empty
                                            $overallGrade = $hasResults
                                                ? ($latestResult->overall_grade ?:
                                                $latestResult->calculated_overall_grade)
                                                : null;
                                        @endphp
                                        @if ($hasResults && $overallGrade)
                                            @php
                                                $gradeColor = match ($overallGrade) {
                                                    'A' => 'success',
                                                    'B' => 'success',
                                                    'C' => 'success',
                                                    'Merit' => 'primary',
                                                    'D' => 'warning',
                                                    'E' => 'danger',
                                                    'U' => 'danger',
                                                    default => 'secondary',
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $gradeColor }} fs-6">{{ $overallGrade }}</span>
                                        @else
                                            <span class="badge bg-secondary text-white">Not entered</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="action-buttons">
                                            <a href="{{ route('finals.students.show', ['student' => $student, 'finals_context' => $finalsDefinition->context]) }}"
                                                class="btn btn-sm btn-outline-info" title="View Student">
                                                <i class="bx bx-show"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
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
                    <i class="bx bx-group display-1 text-muted" style="opacity: 0.5;"></i>
                </div>
                <h5 class="text-muted">Year rollover not done yet.</h5>
                <p class="text-muted mb-4">
                    There are no final year students for the selected year. Students are moved to the finals module
                    during year rollover.
                </p>
            </div>
        </div>
    </div>
@endif

<script>
    $(document).ready(function() {
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>
