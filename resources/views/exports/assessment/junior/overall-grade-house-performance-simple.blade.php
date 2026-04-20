<!DOCTYPE html>
<html>

<head>
    <title>Grade House No Gender</title>
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
    <p>{{ $grade->name ?? '' }} Grade House Performance Analysis (No Gender) - {{ $reportPeriod }} - Term
        {{ $term->term ?? '' }}, {{ $term->year ?? '' }}</p>

    <table>
        <thead>
            <tr>
                <th rowspan="2">House</th>
                <th colspan="7">Grade Counts</th>
                <th colspan="4">Percentages</th>
                <th rowspan="2">Total</th>
            </tr>
            <tr>
                <th>Merit</th>
                <th>A</th>
                <th>B</th>
                <th>C</th>
                <th>D</th>
                <th>E</th>
                <th>U</th>
                <th>MAB%</th>
                <th>MABC%</th>
                <th>MABCD%</th>
                <th>DEU%</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($housePerformance as $houseName => $hp)
                <tr>
                    <td>{{ $houseName }}</td>
                    <td>{{ $hp['gradeCounts']['Merit'] }}</td>
                    <td>{{ $hp['gradeCounts']['A'] }}</td>
                    <td>{{ $hp['gradeCounts']['B'] }}</td>
                    <td>{{ $hp['gradeCounts']['C'] }}</td>
                    <td>{{ $hp['gradeCounts']['D'] }}</td>
                    <td>{{ $hp['gradeCounts']['E'] }}</td>
                    <td>{{ $hp['gradeCounts']['U'] }}</td>
                    <td>{{ $hp['mabPercentage'] }}%</td>
                    <td>{{ $hp['mabcPercentage'] }}%</td>
                    <td>{{ $hp['mabcdPercentage'] }}%</td>
                    <td>{{ $hp['deuPercentage'] }}%</td>
                    <td>{{ $hp['total'] }}</td>
                </tr>
            @endforeach
            <tr>
                <td><strong>Totals</strong></td>
                <td><strong>{{ $overallTotals['grades']['Merit'] }}</strong></td>
                <td><strong>{{ $overallTotals['grades']['A'] }}</strong></td>
                <td><strong>{{ $overallTotals['grades']['B'] }}</strong></td>
                <td><strong>{{ $overallTotals['grades']['C'] }}</strong></td>
                <td><strong>{{ $overallTotals['grades']['D'] }}</strong></td>
                <td><strong>{{ $overallTotals['grades']['E'] }}</strong></td>
                <td><strong>{{ $overallTotals['grades']['U'] }}</strong></td>
                <td><strong>{{ $overallTotals['MAB%'] }}%</strong></td>
                <td><strong>{{ $overallTotals['MABC%'] }}%</strong></td>
                <td><strong>{{ $overallTotals['MABCD%'] }}%</strong></td>
                <td><strong>{{ $overallTotals['DEU%'] }}%</strong></td>
                <td><strong>{{ $overallTotals['total'] }}</strong></td>
            </tr>
        </tbody>
    </table>
</body>

</html>
