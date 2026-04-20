<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Performance Report</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6">
                <h4><strong>{{ $school_information->school_name }}</strong></h4>
                <p><strong>Class Performance Report</strong></p>
                <p>Class : {{ $stats[0]->klass->name }} Class Teacher : {{ $stats[0]->klass->teacher->full_name }}</p>
            </div>
            <div class="col-md-6 d-flex justify-content-end">
                <a href="#">Print</a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-10">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Subjects</th>
                            <th>Roll</th>
                            <th>A</th>
                            <th>%</th>
                            <th>B</th>
                            <th>%</th>
                            <th>C</th>
                            <th>%</th>
                            <th>D</th>
                            <th>%</th>
                            <th>E</th>
                            <th>%</th>
                            <th>AB</th>
                            <th>%</th>
                            <th>ABC</th>
                            <th>%</th>
                            <th>DE</th>
                            <th>%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stats as $klassSubject)
                            @foreach($klassSubject->subject->tests as $test)
                                <tr>
                                    <td>{{ $klassSubject->subject->name }}</td>
                                    <td>{{ $students_count }}</td>
                                    <td>{{ $test->grade_A_count }}</td>
                                    <td>
                                        @php
                                            $a_students = intval($test->grade_A_count);
                                            $total_students = intval($test->students_count);
                                            echo $total_students ? number_format($a_students / $total_students * 100, 1).'%' : '0.0';
                                        @endphp
                                    </td>
                                    <td>{{ $test->grade_B_count }}</td>
                                    <td>
                                        @php
                                            $b_students = intval($test->grade_B_count);
                                            $total_students = intval($test->students_count);
                                            echo $total_students ? number_format($b_students / $total_students * 100, 1).'%' : '0.0';
                                        @endphp
                                    </td>
                                    <td>{{ $test->grade_C_count }}</td>
                                    <td>
                                        @php
                                            $c_students = intval($test->grade_C_count);
                                            $total_students = intval($test->students_count);
                                            echo $total_students ? number_format($c_students / $total_students * 100, 1).'%' : '0.0';
                                        @endphp
                                    </td>
                                    <td>{{ $test->grade_D_count }}</td>
                                    <td>
                                        @php
                                            $d_students = intval($test->grade_D_count);
                                            $total_students = intval($test->students_count);
                                            echo $total_students ? number_format($d_students / $total_students * 100, 1).'%' : '0.0';
                                        @endphp
                                    </td>
                                    <td>{{ $test->grade_E_count }}</td>
                                    <td>
                                        @php
                                            $e_students = intval($test->grade_E_count);
                                            $total_students = intval($test->students_count);
                                            echo $total_students ? number_format($e_students / $total_students * 100, 1).'%' : '0.0';
                                        @endphp
                                    </td>
                                    <td>
                                        @php
                                            $total = intval($test->grade_A_count) + intval($test->grade_B_count);
                                            echo $total ?? 0;
                                        @endphp
                                    </td>
                                    <td>
                                        @php
                                            $total = intval($test->grade_A_count) + intval($test->grade_B_count);
                                            echo $total ? number_format($total / intval($test->students_count) * 100,1).'%' : 0;
        
                                        @endphp
                                    </td>
                                    <td>
                                        @php
                                            $abc = intval($test->grade_A_count) + intval($test->grade_B_count) + intval($test->grade_C_count);
                                            echo $abc ?? 0;
                                        @endphp
                                    </td>
                                    <td>
                                        @php
                                            $abc = intval($test->grade_A_count) + intval($test->grade_B_count) + intval($test->grade_C_count);
                                            $total_students = intval($test->students_count);
                                            echo $abc ? number_format($abc / $total_students * 100,1).'%' : 0;
                                        @endphp
                                    </td>
                                    <td>
                                        @php
                                            $de = intval($test->grade_D_count) + intval($test->grade_E_count);
                                            echo $de ?? 0;
                                        @endphp
                                    </td>
                                    <td>
                                        @php
                                            $de = intval($test->grade_D_count) + intval($test->grade_E_count);
                                            $total_students = intval($test->students_count);
                                            echo $de ? number_format($de / $total_students * 100,1).'%' : 0;
                                        @endphp
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>

                <table class="table table-bordered">
                    <thead>
                        <th>Overall</th>
                        <th>Roll</th>
                        <th>A</th>
                        <th>%</th>
                        <th>B</th>
                        <th>%</th>
                        <th>C</th>
                        <th>%</th>
                        <th>D</th>
                        <th>%</th>
                        <th>E</th>
                        <th>%</th>
                        <th>AB</th>
                        <th>%</th>
                        <th>ABC</th>
                        <th>%</th>
                        <th>DE</th>
                        <th>%</th>
                    </thead>
                    <tbody>
                            <tr>
                                <td></td>
                                <td>
                                    @php
                                        $sumAll = intval($grades_counts['A']) + intval($grades_counts['B']) + intval($grades_counts['C']) + intval($grades_counts['D']) + intval($grades_counts['E']);
                                        echo $sumAll;
                                    @endphp
                                </td>
                                <td>{{ $grades_counts['A'] }}</td>
                                <td>
                                    @php
                                        $a = intval($grades_counts['A']) ?? 0;
                                        $total = $students_total ?? 0;
                                        echo $total != 0 ? number_format($a / $total * 100).'%' : 0;
                                    @endphp
                                </td>
                                <td>{{ $grades_counts['B'] }}</td>
                                <td>
                                    @php
                                        $b = intval($grades_counts['B']) ?? 0;
                                        $total = $students_total ?? 0;
                                        echo $total != 0 ? number_format($b / $total * 100).'%' : 0;
                                    @endphp
                                </td>
                                <td>{{ $grades_counts['C'] }} </td>
                                <td>
                                    @php
                                        $c = intval($grades_counts['C']) ?? 0;
                                        $total = $students_total ?? 0;
                                        echo $total != 0 ? number_format($c / $total * 100).'%' : 0;
                                    @endphp
                                </td>
                                <td>{{ $grades_counts['D'] }}</td>
                                <td>
                                    @php
                                        $d = intval($grades_counts['D']) ?? 0;
                                        $total = $students_total ?? 0;
                                        echo $total != 0 ? number_format($d / $total * 100).'%' : 0;
                                    @endphp
                                </td>
                                <td>{{ $grades_counts['E'] }}</td>
                                <td>
                                    @php
                                        $e = intval($grades_counts['E']) ?? 0;
                                        $total = $students_total ?? 0;
                                        echo $total != 0 ? number_format($e / $total * 100).'%' : 0;
                                    @endphp
                                </td>
                                <td>
                                    @php
                                        $sumAB = intval($grades_counts['A'] ) + intval($grades_counts['B'] );
                                        echo $sumAB;
                                    @endphp
                                </td>
                                <td>
                                    @php
                                        $sumAB = intval($grades_counts['A']) + intval($grades_counts['B']);
                                        $total = $students_total ?? 0;
                                        echo $total != 0 ? number_format($sumAB / $total * 100).'%' : 0;
                                    @endphp
                                </td>
                                <td>
                                    @php
                                        $sumABC = intval($grades_counts['A']) + intval($grades_counts['C']) + intval($grades_counts['C']);
                                        echo $sumABC;
                                    @endphp
                                </td>
                                <td>
                                    @php
                                        $sumABC = intval($grades_counts['A']) + intval($grades_counts['C']) + intval($grades_counts['C']);
                                        $total = $students_total ?? 0;
                                        echo $total =! 0 ? number_format($sumABC / $total * 100).'%' : 0;
                                    @endphp
                                </td>
                                <td>
                                    @php
                                        echo intval($grades_counts['D']) + intval($grades_counts['E']);
                                    @endphp
                                </td>
                                <td>
                                    @php
                                        $sumDE = intval($grades_counts['D']) + intval($grades_counts['E']);
                                        $total = $students_total ?? 0;
                                        echo $total =! 0 ? number_format($sumDE / $total * 100).'%' : 0;
                                    @endphp
                                </td>
                            </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
