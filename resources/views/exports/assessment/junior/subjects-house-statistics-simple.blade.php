<!DOCTYPE html>
<html>

<head>
    <title>Subjects House CA</title>
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
                    <th colspan="2">{{ $grade }}</th>
                @endforeach
                @foreach (['AB%', 'ABC%', 'ABCD%', 'DEU%'] as $percentage)
                    <th colspan="2">{{ $percentage }}</th>
                @endforeach
                <th colspan="2">Total</th>
            </tr>
            <tr>
                @for ($i = 0; $i < 10; $i++)
                    <th>M</th>
                    <th>F</th>
                @endfor
                <th>M</th>
                <th>F</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($housePerformance as $houseName => $data)
                <tr>
                    <td>{{ $houseName }}</td>
                    @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $grade)
                        <td>{{ $data['grades'][$grade]['M'] }}</td>
                        <td>{{ $data['grades'][$grade]['F'] }}</td>
                    @endforeach
                    @foreach (['AB%', 'ABC%', 'ABCD%', 'DEU%'] as $percentage)
                        <td>{{ $data[$percentage]['M'] }}%</td>
                        <td>{{ $data[$percentage]['F'] }}%</td>
                    @endforeach
                    <td>{{ $data['totalMale'] }}</td>
                    <td>{{ $data['totalFemale'] }}</td>
                </tr>
            @endforeach
            <tr>
                <td><strong>Totals</strong></td>
                @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $grade)
                    <td><strong>{{ $overallTotals['grades'][$grade]['M'] }}</strong></td>
                    <td><strong>{{ $overallTotals['grades'][$grade]['F'] }}</strong></td>
                @endforeach
                @foreach (['AB%', 'ABC%', 'ABCD%', 'DEU%'] as $percentage)
                    <td><strong>{{ $overallTotals[$percentage]['M'] }}%</strong></td>
                    <td><strong>{{ $overallTotals[$percentage]['F'] }}%</strong></td>
                @endforeach
                <td><strong>{{ $overallTotals['totalMale'] }}</strong></td>
                <td><strong>{{ $overallTotals['totalFemale'] }}</strong></td>
            </tr>
        </tbody>
    </table>
</body>

</html>
