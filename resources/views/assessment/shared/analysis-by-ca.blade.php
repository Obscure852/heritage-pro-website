<table style="border-collapse: collapse; width: 100%;">
    <thead>
        <tr>
            <th rowspan="2" style="border: 1px solid black; padding: 5px;">Student Name</th>
            <th rowspan="2" style="border: 1px solid black; padding: 5px;">Gender</th>
            <th rowspan="2" style="border: 1px solid black; padding: 5px;">Class</th>
            @foreach ($klass->students->first()->tests as $test)
                <th colspan="3" style="border: 1px solid black; padding: 5px;">{{ $test->subject->name }} (Out of: {{ $test->out_of }})</th>
            @endforeach
            <th colspan="3" style="border: 1px solid black; padding: 5px;">Summary</th>
        </tr>
        <tr>
            @foreach ($klass->students->first()->tests as $test)
                <th style="border: 1px solid black; padding: 5px;">Score</th>
                <th style="border: 1px solid black; padding: 5px;">Percentage</th>
                <th style="border: 1px solid black; padding: 5px;">Grade</th>
            @endforeach
            <th style="border: 1px solid black; padding: 5px;">Total</th>
            <th style="border: 1px solid black; padding: 5px;">Average</th>
            <th style="border: 1px solid black; padding: 5px;">Grade</th>
        </tr>
    </thead>
    <tbody>
        @php
            $sortedStudents = $klass->students->sortByDesc(function ($student) {
                $totalScore = 0;
                $totalPossiblePoints = 0;
                foreach ($student->tests as $test) {
                    $totalScore += $test->pivot->score;
                    $totalPossiblePoints += $test->out_of;
                }
                return $totalPossiblePoints ? ($totalScore / $totalPossiblePoints) * 100 : 0;
            })->values();
        @endphp
        @foreach ($sortedStudents as $student)
            <tr>
                <td style="border: 1px solid black; padding: 5px;">{{ $student->full_name }}</td>
                <td style="border: 1px solid black; padding: 5px;">{{ $student->gender }}</td>
                <td style="border: 1px solid black; padding: 5px;">{{ $student->class->name }}</td>
                @php
                $totalScore = 0;
                $totalPossiblePoints = 0;
                @endphp
                @foreach ($student->tests as $test)
                @php
                    $percentage = ($test->pivot->score / $test->out_of) * 100;
                    $totalScore += $test->pivot->score;
                    $totalPossiblePoints += $test->out_of;
                @endphp
                    <td style="border: 1px solid black; padding: 5px;">{{ $test->pivot->score ?? 0 }}</td>
                    <td style="border: 1px solid black; padding: 5px;">{{ number_format($percentage, 1) }}%</td>
                    <td style="border: 1px solid black; padding: 5px;">{{ $test->pivot->grade }}</td>
                @endforeach
                <td style="border: 1px solid black; padding: 5px;">{{ $totalScore }}</td>
                <td style="border: 1px solid black; padding: 5px;">{{ number_format(($totalScore / $totalPossiblePoints) * 100, 1) }}%</td>
                <td style="border: 1px solid black; padding: 5px;"><!-- Grade based on average can be calculated here --></td>
            </tr>
        @endforeach
    </tbody>
</table>
