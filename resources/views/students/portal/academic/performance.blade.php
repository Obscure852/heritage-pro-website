<style>
    .grade-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .grade-a,
    .grade-a-plus,
    .grade-a-minus {
        background: #d1fae5;
        color: #065f46;
    }

    .grade-b,
    .grade-b-plus,
    .grade-b-minus {
        background: #dbeafe;
        color: #1e40af;
    }

    .grade-c,
    .grade-c-plus,
    .grade-c-minus {
        background: #fef3c7;
        color: #92400e;
    }

    .grade-d,
    .grade-d-plus,
    .grade-d-minus {
        background: #fed7aa;
        color: #c2410c;
    }

    .grade-e,
    .grade-f,
    .grade-u {
        background: #fee2e2;
        color: #991b1b;
    }

    .subject-header {
        background: #f8fafc;
        padding: 12px 16px;
        border-radius: 3px;
        margin-bottom: 12px;
        border-left: 4px solid #3b82f6;
    }

    .test-row:hover {
        background: #f9fafb;
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

    .section-title {
        font-size: 16px;
        font-weight: 600;
        color: #374151;
        border-bottom: 2px solid #e5e7eb;
        padding-bottom: 10px;
        margin-bottom: 16px;
    }

    .section-title i {
        color: #3b82f6;
    }

    .card {
        border-radius: 3px;
    }

    .table-responsive {
        border-radius: 3px;
    }

    .print-icon {
        font-size: 22px;
        color: #6b7280;
        cursor: pointer;
        transition: color 0.2s ease;
    }

    .print-icon:hover {
        color: #3b82f6;
    }

    @media print {
        .print-icon {
            display: none !important;
        }
    }
</style>

<div class="d-flex justify-content-between align-items-start mb-3">
    <div class="help-text mb-0" style="flex: 1;">
        <div class="help-title">Academic Performance</div>
        <div class="help-content">
            View your term marks including Continuous Assessment (CA) tests and examination results.
            Scores are displayed per subject with grades and percentages.
        </div>
    </div>
</div>
<div class="row mb-2">
    <div class="col-10"></div>
    <div class="col-2 d-flex justify-content-end">
        <i class="bx bx-printer print-icon ms-3" onclick="window.print()" title="Print"></i>
    </div>
</div>
@php
    function getGradeClass($grade)
    {
        if (!$grade) {
            return '';
        }
        $grade = strtolower(str_replace(['+', '-'], ['-plus', '-minus'], $grade));
        return 'grade-' . $grade;
    }
@endphp

<!-- CA Tests Section -->
<div class="card mb-4">
    <div class="card-body">
        <h5 class="section-title">
            <i class="bx bx-task me-2"></i> Continuous Assessment (CA) Tests
        </h5>

        @if ($caTests->count() > 0)
            @foreach ($caTests as $subjectName => $tests)
                <div class="subject-header">
                    <h6 class="mb-0">
                        <i class="bx bx-book-alt me-1"></i> {{ $subjectName }}
                    </h6>
                </div>
                <div class="table-responsive mb-4">
                    <table class="table table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Test Name</th>
                                <th class="text-center">Score</th>
                                <th class="text-center">Out Of</th>
                                <th class="text-center">Percentage</th>
                                <th class="text-center">Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tests as $test)
                                <tr class="test-row">
                                    <td>{{ $test->name }}</td>
                                    <td class="text-center">{{ $test->pivot->score ?? '-' }}</td>
                                    <td class="text-center">{{ $test->out_of }}</td>
                                    <td class="text-center">
                                        @if ($test->pivot->percentage)
                                            {{ number_format($test->pivot->percentage, 1) }}%
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($test->pivot->grade)
                                            <span class="grade-badge {{ getGradeClass($test->pivot->grade) }}">
                                                {{ $test->pivot->grade }}
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        @else
            <div class="text-center py-4">
                <i class="bx bx-clipboard text-muted display-4"></i>
                <p class="text-muted mt-2">No CA test results for this term</p>
            </div>
        @endif
    </div>
</div>

<!-- Exam Results Section -->
<div class="card">
    <div class="card-body">
        <h5 class="section-title">
            <i class="bx bx-edit me-2"></i> Examination Results
        </h5>

        @if ($examTests->count() > 0)
            @foreach ($examTests as $subjectName => $tests)
                <div class="subject-header">
                    <h6 class="mb-0">
                        <i class="bx bx-book-alt me-1"></i> {{ $subjectName }}
                    </h6>
                </div>
                <div class="table-responsive mb-4">
                    <table class="table table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Exam Name</th>
                                <th class="text-center">Score</th>
                                <th class="text-center">Out Of</th>
                                <th class="text-center">Percentage</th>
                                <th class="text-center">Grade</th>
                                <th class="text-center">Points</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tests as $test)
                                <tr class="test-row">
                                    <td>{{ $test->name }}</td>
                                    <td class="text-center">{{ $test->pivot->score ?? '-' }}</td>
                                    <td class="text-center">{{ $test->out_of }}</td>
                                    <td class="text-center">
                                        @if ($test->pivot->percentage)
                                            {{ number_format($test->pivot->percentage, 1) }}%
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($test->pivot->grade)
                                            <span class="grade-badge {{ getGradeClass($test->pivot->grade) }}">
                                                {{ $test->pivot->grade }}
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($test->pivot->points)
                                            <span class="badge bg-secondary">{{ $test->pivot->points }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        @else
            <div class="text-center py-4">
                <i class="bx bx-file text-muted display-4"></i>
                <p class="text-muted mt-2">No examination results for this term</p>
            </div>
        @endif
    </div>
</div>
