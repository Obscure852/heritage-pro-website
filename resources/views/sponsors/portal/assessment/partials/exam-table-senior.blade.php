{{-- Senior Exam Table: Shows Points column with Best 6 Subjects highlighting --}}
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

    $bestSubjects = $termData['bestSubjects'] ?? [];
@endphp

<div class="subject-table-container">
    <table class="table table-striped align-middle mb-0">
        <thead>
            <tr>
                <th style="width: 50px;">#</th>
                <th>Subject</th>
                <th class="text-center" style="width: 100px;">Percentage</th>
                <th class="text-center" style="width: 80px;">Points</th>
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

                    // Check if this subject is in the best 6
                    $isInBest6 = collect($bestSubjects)->contains(function ($best) use ($subjectName) {
                        return strtolower($best['subject']) === strtolower($subjectName);
                    });
                @endphp
                <tr class="{{ $isInBest6 ? 'table-success' : '' }}">
                    <td class="fw-medium">{{ $loopIndex + 1 }}</td>
                    <td>
                        <div class="subject-name">
                            <span class="subject-icon">
                                <i class="bx bx-book-open"></i>
                            </span>
                            {{ $subjectName }}
                            @if ($isInBest6)
                                <i class="bx bxs-star text-warning ms-1" title="Best 6"></i>
                            @endif
                        </div>
                    </td>
                    <td class="text-center">{{ $test->pivot->percentage ?? 0 }}%</td>
                    <td class="text-center fw-medium">{{ $test->pivot->points ?? 0 }}</td>
                    <td class="text-center">
                        <span class="badge bg-{{ $gradeBg }}">{{ $grade ?? '-' }}</span>
                    </td>
                    <td class="text-muted small">{{ Str::limit($comment->remarks ?? '-', 50) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="table-light fw-bold">
                <td colspan="3" class="text-end">
                    <i class="bx bxs-star text-warning me-1"></i>
                    Best 6 Subjects Total Points:
                </td>
                <td class="text-center">{{ $termData['totalPoints'] ?? 0 }}</td>
                <td></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>

@if(count($bestSubjects) > 0)
<div class="mt-2">
    <small class="text-muted">
        <i class="bx bxs-star text-warning"></i>
        Highlighted subjects contribute to the Best 6 calculation
    </small>
</div>
@endif
