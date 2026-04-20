<!DOCTYPE html>
<html>

<head>
    <title>Boarding Analysis Report</title>
</head>

<body>
    <table>
        <thead>
            <tr>
                <th colspan="9"><strong>Grade Summary</strong></th>
            </tr>
            <tr>
                <th>#</th>
                <th>Grade</th>
                <th>Boarding Boys</th>
                <th>Boarding Girls</th>
                <th>Boarding Total</th>
                <th>Day Boys</th>
                <th>Day Girls</th>
                <th>Day Total</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['gradeData'] as $index => $grade)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $grade['grade_name'] }}</td>
                    <td>{{ $grade['boarding_boys'] }}</td>
                    <td>{{ $grade['boarding_girls'] }}</td>
                    <td>{{ $grade['boarding_total'] }}</td>
                    <td>{{ $grade['day_boys'] }}</td>
                    <td>{{ $grade['day_girls'] }}</td>
                    <td>{{ $grade['day_total'] }}</td>
                    <td>{{ $grade['total'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <br>

    <table>
        <thead>
            <tr>
                <th colspan="10"><strong>Class Detail</strong></th>
            </tr>
            <tr>
                <th>#</th>
                <th>Class</th>
                <th>Teacher</th>
                <th>Boarding Boys</th>
                <th>Boarding Girls</th>
                <th>Boarding Total</th>
                <th>Day Boys</th>
                <th>Day Girls</th>
                <th>Day Total</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalBoardingBoys = 0;
                $totalBoardingGirls = 0;
                $totalDayBoys = 0;
                $totalDayGirls = 0;
            @endphp
            @foreach($data['classData'] as $index => $class)
                @php
                    $totalBoardingBoys += $class['boarding_boys'];
                    $totalBoardingGirls += $class['boarding_girls'];
                    $totalDayBoys += $class['day_boys'];
                    $totalDayGirls += $class['day_girls'];
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $class['name'] }}</td>
                    <td>{{ $class['teacher'] }}</td>
                    <td>{{ $class['boarding_boys'] }}</td>
                    <td>{{ $class['boarding_girls'] }}</td>
                    <td>{{ $class['boarding_total'] }}</td>
                    <td>{{ $class['day_boys'] }}</td>
                    <td>{{ $class['day_girls'] }}</td>
                    <td>{{ $class['day_total'] }}</td>
                    <td>{{ $class['total'] }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="3"><strong>Totals</strong></td>
                <td><strong>{{ $totalBoardingBoys }}</strong></td>
                <td><strong>{{ $totalBoardingGirls }}</strong></td>
                <td><strong>{{ $totalBoardingBoys + $totalBoardingGirls }}</strong></td>
                <td><strong>{{ $totalDayBoys }}</strong></td>
                <td><strong>{{ $totalDayGirls }}</strong></td>
                <td><strong>{{ $totalDayBoys + $totalDayGirls }}</strong></td>
                <td><strong>{{ $totalBoardingBoys + $totalBoardingGirls + $totalDayBoys + $totalDayGirls }}</strong></td>
            </tr>
        </tbody>
    </table>
</body>

</html>
