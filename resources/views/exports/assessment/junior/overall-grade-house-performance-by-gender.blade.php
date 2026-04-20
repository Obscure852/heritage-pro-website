<!DOCTYPE html>
<html>

<head>
    <title>Grade House By Gender</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #d9d9d9;
            padding: 4px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
            font-weight: 700;
        }
    </style>
</head>

<body>
    @php
        $reportPeriod = strtolower($test->type ?? '') === 'exam' ? 'End of Term' : 'End of ' . ($test->name ?? '');
    @endphp

    <h3>{{ $school_data->school_name ?? '' }}</h3>
    <p>{{ $grade->name ?? '' }} Grade House Performance Analysis (By Gender) - {{ $reportPeriod }} - Term
        {{ $term->term ?? '' }}, {{ $term->year ?? '' }}</p>

    <table>
        <thead>
            <tr>
                <th rowspan="3">House</th>
                <th colspan="21">Grade Counts</th>
                <th colspan="12">Percentages</th>
                <th colspan="3" rowspan="2">Total Students</th>
            </tr>
            <tr>
                @foreach (['Merit', 'A', 'B', 'C', 'D', 'E', 'U'] as $gradeBand)
                    <th colspan="3">{{ $gradeBand }}</th>
                @endforeach
                @foreach (['MAB%', 'MABC%', 'MABCD%', 'DEU%'] as $percentage)
                    <th colspan="3">{{ $percentage }}</th>
                @endforeach
            </tr>
            <tr>
                @for ($i = 0; $i < 7; $i++)
                    <th>M</th>
                    <th>F</th>
                    <th>T</th>
                @endfor
                @for ($i = 0; $i < 4; $i++)
                    <th>M</th>
                    <th>F</th>
                    <th>T</th>
                @endfor
                <th>M</th>
                <th>F</th>
                <th>T</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($housePerformance as $houseName => $hp)
                @php
                    $totalStudents = $hp['totalMale'] + $hp['totalFemale'];
                    $pct = fn($num, $den) => $den ? round(($num / $den) * 100, 2) : 0;

                    $mabTotal = $hp['gradeCounts']['Merit']['M'] +
                        $hp['gradeCounts']['Merit']['F'] +
                        $hp['gradeCounts']['A']['M'] +
                        $hp['gradeCounts']['A']['F'] +
                        $hp['gradeCounts']['B']['M'] +
                        $hp['gradeCounts']['B']['F'];
                    $mabcTotal = $mabTotal + $hp['gradeCounts']['C']['M'] + $hp['gradeCounts']['C']['F'];
                    $mabcdTotal = $mabcTotal + $hp['gradeCounts']['D']['M'] + $hp['gradeCounts']['D']['F'];
                    $deuTotal = $hp['gradeCounts']['D']['M'] +
                        $hp['gradeCounts']['D']['F'] +
                        $hp['gradeCounts']['E']['M'] +
                        $hp['gradeCounts']['E']['F'] +
                        $hp['gradeCounts']['U']['M'] +
                        $hp['gradeCounts']['U']['F'];
                @endphp
                <tr>
                    <td>{{ $houseName }}</td>
                    @foreach (['Merit', 'A', 'B', 'C', 'D', 'E', 'U'] as $gradeBand)
                        <td>{{ $hp['gradeCounts'][$gradeBand]['M'] }}</td>
                        <td>{{ $hp['gradeCounts'][$gradeBand]['F'] }}</td>
                        <td>{{ $hp['gradeCounts'][$gradeBand]['M'] + $hp['gradeCounts'][$gradeBand]['F'] }}</td>
                    @endforeach
                    <td>{{ $hp['mabPercentageM'] }}%</td>
                    <td>{{ $hp['mabPercentageF'] }}%</td>
                    <td>{{ $pct($mabTotal, $totalStudents) }}%</td>
                    <td>{{ $hp['mabcPercentageM'] }}%</td>
                    <td>{{ $hp['mabcPercentageF'] }}%</td>
                    <td>{{ $pct($mabcTotal, $totalStudents) }}%</td>
                    <td>{{ $hp['mabcdPercentageM'] }}%</td>
                    <td>{{ $hp['mabcdPercentageF'] }}%</td>
                    <td>{{ $pct($mabcdTotal, $totalStudents) }}%</td>
                    <td>{{ $hp['deuPercentageM'] }}%</td>
                    <td>{{ $hp['deuPercentageF'] }}%</td>
                    <td>{{ $pct($deuTotal, $totalStudents) }}%</td>
                    <td>{{ $hp['totalMale'] }}</td>
                    <td>{{ $hp['totalFemale'] }}</td>
                    <td>{{ $totalStudents }}</td>
                </tr>
            @endforeach

            @php
                $grandTotal = $overallTotals['totalMale'] + $overallTotals['totalFemale'];
                $pctG = fn($num, $den) => $den ? round(($num / $den) * 100, 2) : 0;

                $mabTotalAll = $overallTotals['grades']['Merit']['M'] +
                    $overallTotals['grades']['Merit']['F'] +
                    $overallTotals['grades']['A']['M'] +
                    $overallTotals['grades']['A']['F'] +
                    $overallTotals['grades']['B']['M'] +
                    $overallTotals['grades']['B']['F'];
                $mabcTotalAll = $mabTotalAll + $overallTotals['grades']['C']['M'] + $overallTotals['grades']['C']['F'];
                $mabcdTotalAll = $mabcTotalAll + $overallTotals['grades']['D']['M'] + $overallTotals['grades']['D']['F'];
                $deuTotalAll = $overallTotals['grades']['D']['M'] +
                    $overallTotals['grades']['D']['F'] +
                    $overallTotals['grades']['E']['M'] +
                    $overallTotals['grades']['E']['F'] +
                    $overallTotals['grades']['U']['M'] +
                    $overallTotals['grades']['U']['F'];
            @endphp
            <tr>
                <td><strong>Totals</strong></td>
                @foreach (['Merit', 'A', 'B', 'C', 'D', 'E', 'U'] as $gradeBand)
                    <td><strong>{{ $overallTotals['grades'][$gradeBand]['M'] }}</strong></td>
                    <td><strong>{{ $overallTotals['grades'][$gradeBand]['F'] }}</strong></td>
                    <td><strong>{{ $overallTotals['grades'][$gradeBand]['M'] + $overallTotals['grades'][$gradeBand]['F'] }}</strong></td>
                @endforeach
                <td><strong>{{ $overallTotals['MAB%']['M'] }}%</strong></td>
                <td><strong>{{ $overallTotals['MAB%']['F'] }}%</strong></td>
                <td><strong>{{ $pctG($mabTotalAll, $grandTotal) }}%</strong></td>
                <td><strong>{{ $overallTotals['MABC%']['M'] }}%</strong></td>
                <td><strong>{{ $overallTotals['MABC%']['F'] }}%</strong></td>
                <td><strong>{{ $pctG($mabcTotalAll, $grandTotal) }}%</strong></td>
                <td><strong>{{ $overallTotals['MABCD%']['M'] }}%</strong></td>
                <td><strong>{{ $overallTotals['MABCD%']['F'] }}%</strong></td>
                <td><strong>{{ $pctG($mabcdTotalAll, $grandTotal) }}%</strong></td>
                <td><strong>{{ $overallTotals['DEU%']['M'] }}%</strong></td>
                <td><strong>{{ $overallTotals['DEU%']['F'] }}%</strong></td>
                <td><strong>{{ $pctG($deuTotalAll, $grandTotal) }}%</strong></td>
                <td><strong>{{ $overallTotals['totalMale'] }}</strong></td>
                <td><strong>{{ $overallTotals['totalFemale'] }}</strong></td>
                <td><strong>{{ $grandTotal }}</strong></td>
            </tr>
        </tbody>
    </table>
</body>

</html>
