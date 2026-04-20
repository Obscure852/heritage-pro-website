{{-- Primary Exam Table: Shows Possible Marks and Actual Marks (no Points) --}}
@php
    $gradeClass = function($grade) {
        $grade = strtoupper(trim($grade ?? ''));
        return match($grade) {
            'A', 'A+', 'A-' => 'success',
            'B', 'B+', 'B-' => 'primary',
            'C', 'C+', 'C-' => 'info',
            'D', 'D+', 'D-' => 'warning',
            default => 'danger',
        };
    };

    $totalScore = $termData['totalScore'] ?? 0;
    $totalOutOf = $termData['totalOutOf'] ?? 0;
    $averagePercentage = $termData['averagePercentage'] ?? 0;
    $overallGrade = $termData['overallGrade'] ?? null;
@endphp

<div class="subject-table-container">
    <table class="table table-striped align-middle mb-0">
        <thead>
            <tr>
                <th style="width: 50px;">#</th>
                <th>Subject</th>
                <th class="text-center" style="width: 110px;">Possible Marks</th>
                <th class="text-center" style="width: 100px;">Actual Marks</th>
                <th class="text-center" style="width: 80px;">%</th>
                <th class="text-center" style="width: 80px;">Grade</th>
                <th>Comments</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($examTests as $loopIndex => $test)
                @php
                    $comment = $child->getSubjectComment($selectedTermId, $test->grade_subject_id ?? 0)->first();
                    $grade = $test->pivot->grade ?? null;
                    $gradeBg = $gradeClass($grade);
                    $subjectName = ($test->subject && $test->subject->subject)
                        ? $test->subject->subject->name
                        : 'Unknown Subject';
                @endphp
                <tr>
                    <td class="fw-medium">{{ $loopIndex + 1 }}</td>
                    <td>
                        <div class="subject-name">
                            <span class="subject-icon">
                                <i class="bx bx-book-open"></i>
                            </span>
                            {{ $subjectName }}
                        </div>
                    </td>
                    <td class="text-center">{{ $test->out_of ?? 100 }}</td>
                    <td class="text-center fw-medium">{{ $test->pivot->score ?? 0 }}</td>
                    <td class="text-center">{{ $test->pivot->percentage ?? 0 }}%</td>
                    <td class="text-center">
                        <span class="badge bg-{{ $gradeBg }}">{{ $grade ?? '-' }}</span>
                    </td>
                    <td class="text-muted small">{{ Str::limit($comment->remarks ?? '-', 50) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="table-light fw-bold">
                <td colspan="2" class="text-end">Total:</td>
                <td class="text-center">{{ $totalOutOf }}</td>
                <td class="text-center">{{ $totalScore }}</td>
                <td class="text-center">{{ $averagePercentage }}%</td>
                <td class="text-center">
                    @if($overallGrade)
                        @php
                            $overallGradeBg = $gradeClass($overallGrade->grade ?? '');
                        @endphp
                        <span class="badge bg-{{ $overallGradeBg }}">{{ $overallGrade->grade ?? '-' }}</span>
                    @else
                        <span class="badge bg-secondary">-</span>
                    @endif
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>
