<!DOCTYPE html>
<html>

<head>
    <title>Class Performance Analysis</title>
</head>

<body>
    <h5>{{ $klass->name . ' Class Monthly Analysis' }}</h5>
    <table>
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Gender</th>
                @foreach ($subjects as $subject)
                    <th style="text-align: center;" colspan="2">{{ $subject->subject->subject->name }}</th>
                @endforeach
                <th>Total Score</th>
                <th>Average</th>
                <th>Grade</th>
                <th>Position</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($allStudentData as $student)
                <tr>
                    <td>{{ $student['studentName'] }}</td>
                    <td>{{ $student['gender'] }}</td>
                    @foreach ($student['scores'] as $score)
                        <td>{{ $score['score'] }}</td>
                        <td>{{ $score['grade'] }}</td>
                    @endforeach
                    <td>{{ $student['totalScore'] }}</td>
                    <td>{{ number_format($student['averageScore'], 2) }}</td>
                    <td>{{ $student['overallGrade'] }}</td>
                    <td>{{ $student['position'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <br>
    <h5>Grade Distribution</h5>
    <table>
        <thead>
            <tr>
                <th rowspan="2">Grade</th>
                <th colspan="2">A</th>
                <th colspan="2">B</th>
                <th colspan="2">C</th>
                <th rowspan="2">ABC(%)</th>
                <th rowspan="2">ABCD(%)</th>
            </tr>
            <tr>
                <th>M</th>
                <th>F</th>
                <th>M</th>
                <th>F</th>
                <th>M</th>
                <th>F</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalStudents = count($allStudentData);
                $abcTotal =
                    ($gradeCombinationsCounts['ABC']['M'] ?? 0) +
                    ($gradeCombinationsCounts['ABC']['F'] ?? 0);
                $abcdTotal =
                    ($gradeCombinationsCounts['ABCD']['M'] ?? 0) +
                    ($gradeCombinationsCounts['ABCD']['F'] ?? 0);
            @endphp
            <tr>
                <td>Total</td>
                <td>{{ $gradeCounts['A']['M'] ?? 0 }}</td>
                <td>{{ $gradeCounts['A']['F'] ?? 0 }}</td>
                <td>{{ $gradeCounts['B']['M'] ?? 0 }}</td>
                <td>{{ $gradeCounts['B']['F'] ?? 0 }}</td>
                <td>{{ $gradeCounts['C']['M'] ?? 0 }}</td>
                <td>{{ $gradeCounts['C']['F'] ?? 0 }}</td>
                <td>{{ number_format(($abcTotal / max($totalStudents, 1)) * 100, 2) }}%</td>
                <td>{{ number_format(($abcdTotal / max($totalStudents, 1)) * 100, 2) }}%</td>
            </tr>
        </tbody>
    </table>
</body>

</html>
