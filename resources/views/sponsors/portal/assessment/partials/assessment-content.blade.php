@php
    // Group tests by month (extracted from test name which is the month name)
    $months = ['January', 'February', 'March', 'April', 'May', 'June',
               'July', 'August', 'September', 'October', 'November', 'December'];

    $testsByMonth = $tests->groupBy(function($test) use ($months) {
        // Test names are month names like "January", "February", etc.
        if (in_array($test->name, $months)) {
            return $test->name;
        }
        // For exams, extract month from the name (e.g., "December Exam" -> "December")
        foreach ($months as $month) {
            if (str_contains($test->name, $month)) {
                return $month;
            }
        }
        // Fallback to start_date
        return $test->start_date ? \Carbon\Carbon::parse($test->start_date)->format('F') : 'Other';
    });

    // Sort months chronologically
    $sortedMonths = $testsByMonth->keys()->sort(function($a, $b) use ($months) {
        $indexA = array_search($a, $months);
        $indexB = array_search($b, $months);
        if ($indexA === false) $indexA = 999;
        if ($indexB === false) $indexB = 999;
        return $indexA - $indexB;
    })->values();

    // Month abbreviations and icons
    $monthAbbrev = [
        'January' => 'Jan', 'February' => 'Feb', 'March' => 'Mar', 'April' => 'Apr',
        'May' => 'May', 'June' => 'Jun', 'July' => 'Jul', 'August' => 'Aug',
        'September' => 'Sep', 'October' => 'Oct', 'November' => 'Nov', 'December' => 'Dec', 'Other' => 'N/A'
    ];

    // Grade color helper
    $gradeClass = function($grade) {
        $grade = strtoupper(trim($grade ?? ''));
        return match($grade) {
            'A', 'A+', 'A-' => 'grade-a',
            'B', 'B+', 'B-' => 'grade-b',
            'C', 'C+', 'C-' => 'grade-c',
            'D', 'D+', 'D-' => 'grade-d',
            'E', 'F', 'U' => 'grade-e',
            default => 'grade-na'
        };
    };

    // Score color helper
    $scoreClass = function($percentage) {
        if (!is_numeric($percentage)) return '';
        $score = floatval($percentage);
        if ($score >= 75) return 'score-excellent';
        if ($score >= 60) return 'score-good';
        if ($score >= 40) return 'score-average';
        return 'score-poor';
    };

    $typeLabel = $testType === 'CA' ? 'Tests' : 'Exams';
@endphp

@if($sortedMonths->isEmpty())
    <div class="empty-state">
        <div class="empty-state-icon">
            <i class="bx {{ $testType === 'CA' ? 'bx-edit-alt' : 'bx-file' }}"></i>
        </div>
        <h5>No {{ $typeLabel }} Available</h5>
        <p>There are no {{ strtolower($typeLabel) }} recorded for this term yet.</p>
    </div>
@else
    <div class="row g-4">
        <!-- Vertical Month Tabs (Left Sidebar) -->
        <div class="col-lg-3 col-md-4">
            <div class="month-tabs-container">
                <div class="nav flex-column month-tabs" id="monthTabs-{{ $child->id }}-{{ $testType }}" role="tablist">
                    @foreach($sortedMonths as $index => $month)
                        <button class="nav-link {{ $index === 0 ? 'active' : '' }}"
                                id="month-{{ $child->id }}-{{ $testType }}-{{ Str::slug($month) }}-tab"
                                data-bs-toggle="pill"
                                data-bs-target="#month-{{ $child->id }}-{{ $testType }}-{{ Str::slug($month) }}"
                                type="button"
                                role="tab"
                                aria-controls="month-{{ $child->id }}-{{ $testType }}-{{ Str::slug($month) }}"
                                aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                            <span class="month-icon">{{ $monthAbbrev[$month] ?? substr($month, 0, 3) }}</span>
                            {{ $month }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Subject Table (Main Content) -->
        <div class="col-lg-9 col-md-8">
            <div class="tab-content" id="monthContent-{{ $child->id }}-{{ $testType }}">
                @foreach($sortedMonths as $index => $month)
                    @php
                        $monthTests = $testsByMonth[$month];
                        $subjectData = $monthTests->groupBy('grade_subject_id');
                    @endphp
                    <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                         id="month-{{ $child->id }}-{{ $testType }}-{{ Str::slug($month) }}"
                         role="tabpanel"
                         aria-labelledby="month-{{ $child->id }}-{{ $testType }}-{{ Str::slug($month) }}-tab">

                        <div class="subject-table-container">
                            <table class="subject-table">
                                <thead>
                                    <tr>
                                        <th style="width: 50%;">Subject</th>
                                        <th class="text-center" style="width: 25%;">Score %</th>
                                        <th class="text-center" style="width: 25%;">Grade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($subjectData as $subjectId => $subjectTests)
                                        @php
                                            $test = $subjectTests->first();
                                            $subjectName = $test->subject->subject->name ?? 'Unknown Subject';
                                            $percentage = $test->pivot->percentage ?? null;
                                            $grade = $test->pivot->grade ?? 'N/A';
                                            $displayPercentage = is_numeric($percentage) ? number_format($percentage, 0) . '%' : 'N/A';
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="subject-name">
                                                    <span class="subject-icon">
                                                        <i class="bx bx-book-open"></i>
                                                    </span>
                                                    {{ $subjectName }}
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="score-cell {{ $scoreClass($percentage) }}">
                                                    {{ $displayPercentage }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="grade-badge {{ $gradeClass($grade) }}">
                                                    {{ $grade }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted">
                                                No subjects recorded for {{ $month }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($testType === 'Exam')
                            @php
                                $hasComments = false;
                                $comments = [];
                                foreach ($subjectData as $subjectId => $testsForSubject) {
                                    $subjectComment = $child->getSubjectComment($selectedTermId ?? $currentTerm->id, $subjectId)->first();
                                    if ($subjectComment && !empty(trim($subjectComment->remarks)) && $subjectComment->remarks !== '-') {
                                        $hasComments = true;
                                        $comments[] = [
                                            'subject' => $testsForSubject->first()->subject->subject->name ?? 'Unknown',
                                            'remark' => $subjectComment->remarks
                                        ];
                                    }
                                }
                            @endphp

                            @if($hasComments)
                                <div class="comments-section">
                                    <div class="comment-title">
                                        <i class="bx bx-comment-detail"></i>
                                        Teacher Comments
                                    </div>
                                    @foreach($comments as $comment)
                                        <div class="comment-item">
                                            <div class="comment-subject">{{ $comment['subject'] }}</div>
                                            <div class="comment-text">{{ $comment['remark'] }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
