@if ($classesData->isNotEmpty())
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ $finalsDefinition->examLabel ?? 'Finals' }} Classes List
                    <span class="text-muted fw-normal ms-2">({{ $classesData->count() }} classes)</span>
                </h5>
            </div>
            <div class="card-body">
                <div style="padding-right: 10px;" class="table-responsive mb-4">
                    <table class="table table-striped table-sm rounded w-100" id="classesTable">
                        <thead>
                            <tr>
                                <th scope="col">Class</th>
                                <th scope="col">Grade</th>
                                <th scope="col">Teacher</th>
                                <th scope="col">Students</th>
                                <th scope="col">Results Status</th>
                                <th scope="col">{{ ($finalsDefinition->performanceCategories['pass_rate']['label'] ?? 'Pass') }}</th>
                                <th scope="col">Avg Points</th>
                                <th style="width: 80px; min-width: 80px;" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($classesData as $class)
                                <tr>
                                    <td>
                                        <div class="class-cell">
                                            @php
                                                $initials = strtoupper(substr($class['name'] ?? '', 0, 2));
                                            @endphp
                                            <div class="class-avatar-placeholder">{{ $initials ?: 'CL' }}</div>
                                            <div>
                                                <div class="fw-medium">{{ $class['name'] }}</div>
                                                <small class="text-muted">{{ $class['graduation_year'] }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">{{ $class['grade'] }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-medium">{{ $class['teacher'] ?: 'No Teacher' }}</div>
                                    </td>
                                    <td>
                                        <div>
                                            <div class="fw-medium">{{ $class['total_students'] }}</div>
                                            @if ($class['total_students'] > 0)
                                                <small class="text-muted">
                                                    {{ round(($class['students_with_results'] / $class['total_students']) * 100) }}%
                                                    with results
                                                </small>
                                            @else
                                                <small class="text-muted">No students</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if ($class['students_with_results'] > 0 && $class['students_pending'] == 0)
                                            <div class="d-flex align-items-center">
                                                <i class="bx bxs-check-circle text-success me-1"></i>
                                                <span class="text-success fw-medium">Complete</span>
                                            </div>
                                            <small class="text-muted">
                                                {{ $class['students_with_results'] }} students
                                            </small>
                                        @elseif($class['students_with_results'] > 0)
                                            <div class="d-flex align-items-center">
                                                <i class="bx bxs-time text-warning me-1"></i>
                                                <span class="text-warning fw-medium">Partial</span>
                                            </div>
                                            <small class="text-muted">
                                                {{ $class['students_with_results'] }} done,
                                                {{ $class['students_pending'] }} pending
                                            </small>
                                        @else
                                            <div class="d-flex align-items-center">
                                                <i class="bx bxs-time text-warning me-1"></i>
                                                <span class="text-warning fw-medium">Pending</span>
                                            </div>
                                            <small class="text-muted">
                                                All {{ $class['students_pending'] }} pending
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($class['students_with_results'] > 0)
                                            @php
                                                $passRateColor = match (true) {
                                                    $class['pass_rate'] >= 80 => 'success',
                                                    $class['pass_rate'] >= 60 => 'warning',
                                                    default => 'danger',
                                                };
                                            @endphp
                                            <span
                                                class="badge bg-{{ $passRateColor }} fs-6">{{ $class['pass_rate'] }}%</span>
                                        @else
                                            <span class="badge bg-secondary text-white">Not available</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($class['students_with_results'] > 0)
                                            <div>
                                                <div class="fw-medium">{{ $class['average_points'] }}</div>
                                                <small class="text-muted">average</small>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="action-buttons">
                                            <a href="{{ route('finals.classes.show', ['klass' => $class['id'], 'finals_context' => $finalsDefinition->context]) }}"
                                                class="btn btn-sm btn-outline-info" title="View Class">
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
                    <i class="bx bxs-school display-1 text-muted" style="opacity: 0.5;"></i>
                </div>
                <h5 class="text-muted">No Classes Found</h5>
                <p class="text-muted mb-4">
                    @if (request('year'))
                        No final year classes found for {{ request('year') }}.
                    @else
                        No final year classes have been set up yet. Classes are created during year rollover.
                    @endif
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
