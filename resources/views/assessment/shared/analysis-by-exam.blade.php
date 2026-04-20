<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $klass->name .' Overall performance report' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <h5>{{ $school_setup->school_name }}</h5>
                <p>Subject Performance Report</p>
                <p><strong>{{ 'Term '.$klass->term->term .','. $klass->term->year }}</strong></p>
                <p><strong>Class: {{ $klass->name }} Teacher: {{ $klass->teacher->full_name }}</strong></p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                
<p style="margin-top:10px;"><strong>Class Exam Performance Analysis</strong></p>
<table style="border-collapse: collapse; width: 100%;margin-top:10px;">
    <thead>
        <tr>
            <th rowspan="2" style="border: 1px solid black; padding: 5px;">Student</th>
            <th rowspan="2" style="border: 1px solid black; padding: 5px;">Gender</th>
            <th rowspan="2" style="border: 1px solid black; padding: 5px;">Class</th>
            @foreach ($klass->students->first()->tests as $test)
                <th colspan="3" style="border: 1px solid black; padding: 5px;">{{ $test->subject->name }} (Out of: {{ $test->out_of }})</th>
            @endforeach
            <th colspan="3" style="border: 1px solid black; padding: 5px;">Summary</th>
        </tr>
        <tr>
            @foreach ($klass->students->first()->tests as $test)
                <th style="border: 1px solid black; padding: 5px;">Mark</th>
                <th style="border: 1px solid black; padding: 5px;">%</th>
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
                <td style="border: 1px solid black; padding: 5px;">
                    @php
                        echo $totalPossiblePoints != 0 ? number_format(($totalScore / $totalPossiblePoints) * 100, 1).'%a' : 0;
                    @endphp
                </td>
                <td style="border: 1px solid black; padding: 5px;">
                    @php
                    try{
                        $average_mark = $totalPossiblePoints != 0 ? number_format(($totalScore / $totalPossiblePoints) * 100, 0) : 0;
                        echo App\Http\Controllers\AssessmentController::getOverallGrade($klass->grade->id,$average_mark)->grade;
                    }catch(\Exception $e){
                        echo 'Error: ' . $e->getMessage();
                    }
                    @endphp
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
