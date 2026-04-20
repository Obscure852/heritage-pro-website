<table class="table table-sm table-bordered">
    <thead>
        <tr>
            <td>#</td>
            <th>Name</th>
            <th>Class</th>
            <th>Sex</th>
            <th>PSLE</th>
            @foreach ($allSubjects as $subject)
                <th title="{{ $subject }}" colspan="2" style="text-align:center">
                    {{ substr($subject, 0, 3) }}
                </th>
            @endforeach
            <th>TP</th>
            <th>OG</th>
            <th>Pos</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($reportCards as $index => $reportCard)
            @php
                $studentClass = $reportCard['student']->currentClass();
                $studentClassSubjects =
                    $studentClass && $studentClass->subjectClasses
                        ? $studentClass->subjectClasses->pluck('subject.subject.name')->toArray()
                        : [];

                $studentOptionalSubjects = $reportCard['student']->optionalSubjects
                    ? $reportCard['student']->optionalSubjects->pluck('gradeSubject.subject.name')->toArray()
                    : [];

                $studentSubjects = array_merge($studentClassSubjects, $studentOptionalSubjects);
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $reportCard['student']->fullName ?? '' }}</td>
                <td>{{ $reportCard['class_name'] ?? '' }}</td>
                <td>{{ $reportCard['student']->gender ?? '' }}</td>
                <td>{{ optional($reportCard['student']->psle)->overall_grade ?? '' }}
                </td>

                @foreach ($allSubjects as $subject)
                    @php
                        $subjectData = $reportCard['scores'][$subject] ?? null;
                        $subjectScore = $subjectData['percentage'] ?? null;
                        $subjectGrade = $subjectData['grade'] ?? 'X';
                    @endphp

                    @if (in_array($subject, $studentSubjects))
                        @if (is_null($subjectScore) && $subjectGrade === 'X')
                            <td>X</td>
                            <td>X</td>
                        @else
                            <td>{{ is_numeric($subjectScore) ? round($subjectScore) : $subjectScore }}
                            </td>
                            <td>{{ $subjectGrade }}</td>
                        @endif
                    @else
                        <td></td>
                        <td></td>
                    @endif
                @endforeach

                <td>{{ is_numeric($reportCard['totalPoints']) ? $reportCard['totalPoints'] : 'X' }}
                </td>
                <td>{{ $reportCard['grade'] ?? 'X' }}</td>
                <td>{{ $reportCard['position'] ?? '' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="{{ count($allSubjects) * 2 + 6 }}" style="text-align:center">No Students Found</td>
            </tr>
        @endforelse
    </tbody>
</table>
