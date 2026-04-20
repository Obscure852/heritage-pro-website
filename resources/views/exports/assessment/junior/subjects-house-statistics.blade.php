<!DOCTYPE html>
<html>

<head>
    <title>Subjects House Exam</title>
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
    <p>Subjects by House - {{ $reportPeriod }} - Term {{ $term->term ?? '' }}, {{ $term->year ?? '' }}</p>

    <table>
        <thead>
            <tr>
                <th rowspan="2">House</th>
                @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $grade)
                    <th colspan="3">{{ $grade }}</th>
                @endforeach
                <th colspan="3">Total</th>
                <th colspan="3">AB%</th>
                <th colspan="3">ABC%</th>
                <th colspan="3">ABCD%</th>
                <th colspan="3">DEU%</th>
            </tr>
            <tr>
                @for ($i = 0; $i < 11; $i++)
                    <th>M</th>
                    <th>F</th>
                    <th>T</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @foreach ($housePerformance as $house => $data)
                @php
                    $totM = $data['grades']['total']['M'];
                    $totF = $data['grades']['total']['F'];
                    $totT = $totM + $totF;
                    $pct = fn($num, $den) => $den ? round(($num / $den) * 100, 2) : 0;

                    $abM = $data['grades']['A']['M'] + $data['grades']['B']['M'];
                    $abF = $data['grades']['A']['F'] + $data['grades']['B']['F'];
                    $abcM = $abM + $data['grades']['C']['M'];
                    $abcF = $abF + $data['grades']['C']['F'];
                    $abcdM = $abcM + $data['grades']['D']['M'];
                    $abcdF = $abcF + $data['grades']['D']['F'];
                    $deuM = $data['grades']['D']['M'] + $data['grades']['E']['M'] + $data['grades']['U']['M'];
                    $deuF = $data['grades']['D']['F'] + $data['grades']['E']['F'] + $data['grades']['U']['F'];
                @endphp
                <tr>
                    <td>{{ $house }}</td>
                    @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $grade)
                        <td>{{ $data['grades'][$grade]['M'] }}</td>
                        <td>{{ $data['grades'][$grade]['F'] }}</td>
                        <td>{{ $data['grades'][$grade]['M'] + $data['grades'][$grade]['F'] }}</td>
                    @endforeach
                    <td>{{ $totM }}</td>
                    <td>{{ $totF }}</td>
                    <td>{{ $totT }}</td>
                    <td>{{ $pct($abM, $totM) }}%</td>
                    <td>{{ $pct($abF, $totF) }}%</td>
                    <td>{{ $data['AB%'] }}%</td>
                    <td>{{ $pct($abcM, $totM) }}%</td>
                    <td>{{ $pct($abcF, $totF) }}%</td>
                    <td>{{ $data['ABC%'] }}%</td>
                    <td>{{ $pct($abcdM, $totM) }}%</td>
                    <td>{{ $pct($abcdF, $totF) }}%</td>
                    <td>{{ $data['ABCD%'] }}%</td>
                    <td>{{ $pct($deuM, $totM) }}%</td>
                    <td>{{ $pct($deuF, $totF) }}%</td>
                    <td>{{ $data['DEU%'] }}%</td>
                </tr>
            @endforeach

            @php
                $oTotM = $overallTotals['grades']['total']['M'];
                $oTotF = $overallTotals['grades']['total']['F'];
                $oTotT = $oTotM + $oTotF;
                $pctO = fn($num, $den) => $den ? round(($num / $den) * 100, 2) : 0;

                $oAbM = $overallTotals['grades']['A']['M'] + $overallTotals['grades']['B']['M'];
                $oAbF = $overallTotals['grades']['A']['F'] + $overallTotals['grades']['B']['F'];
                $oAbcM = $oAbM + $overallTotals['grades']['C']['M'];
                $oAbcF = $oAbF + $overallTotals['grades']['C']['F'];
                $oAbcdM = $oAbcM + $overallTotals['grades']['D']['M'];
                $oAbcdF = $oAbcF + $overallTotals['grades']['D']['F'];
                $oDeuM = $overallTotals['grades']['D']['M'] + $overallTotals['grades']['E']['M'] + $overallTotals['grades']['U']['M'];
                $oDeuF = $overallTotals['grades']['D']['F'] + $overallTotals['grades']['E']['F'] + $overallTotals['grades']['U']['F'];
            @endphp
            <tr>
                <td><strong>Totals</strong></td>
                @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $grade)
                    <td><strong>{{ $overallTotals['grades'][$grade]['M'] }}</strong></td>
                    <td><strong>{{ $overallTotals['grades'][$grade]['F'] }}</strong></td>
                    <td><strong>{{ $overallTotals['grades'][$grade]['M'] + $overallTotals['grades'][$grade]['F'] }}</strong></td>
                @endforeach
                <td><strong>{{ $oTotM }}</strong></td>
                <td><strong>{{ $oTotF }}</strong></td>
                <td><strong>{{ $oTotT }}</strong></td>
                <td><strong>{{ $pctO($oAbM, $oTotM) }}%</strong></td>
                <td><strong>{{ $pctO($oAbF, $oTotF) }}%</strong></td>
                <td><strong>{{ $overallTotals['AB%'] }}%</strong></td>
                <td><strong>{{ $pctO($oAbcM, $oTotM) }}%</strong></td>
                <td><strong>{{ $pctO($oAbcF, $oTotF) }}%</strong></td>
                <td><strong>{{ $overallTotals['ABC%'] }}%</strong></td>
                <td><strong>{{ $pctO($oAbcdM, $oTotM) }}%</strong></td>
                <td><strong>{{ $pctO($oAbcdF, $oTotF) }}%</strong></td>
                <td><strong>{{ $overallTotals['ABCD%'] }}%</strong></td>
                <td><strong>{{ $pctO($oDeuM, $oTotM) }}%</strong></td>
                <td><strong>{{ $pctO($oDeuF, $oTotF) }}%</strong></td>
                <td><strong>{{ $overallTotals['DEU%'] }}%</strong></td>
            </tr>
        </tbody>
    </table>
</body>

</html>
