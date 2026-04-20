<!DOCTYPE html>
<html>

<head>
    <title>Grade Performance Report</title>
    <style>
        body {
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        th {
            background-color: #4F81BD;
            color: white;
            font-weight: bold;
            padding: 8px;
        }

        td {
            padding: 8px;
        }
    </style>
</head>

<body>
    <h5>{{ $school_data->region ?? '' }} Result Analysis Report - For Year ( {{ $klass->year ?? '' }} )</h5>
    <table>
        <thead>
            <tr>
                <th rowspan="2">Subjects</th>
                <th colspan="3">Candidates Numbers</th>
                <th colspan="3">A</th>
                <th colspan="3">B</th>
                <th colspan="3">C</th>
                <th colspan="3">D</th>
                <th colspan="3">E</th>
                <th colspan="3">U</th>
                <th colspan="3">AB%</th>
                <th colspan="3">ABC%</th>
                <th colspan="3">DEU%</th>
            </tr>
            <tr>
                <!-- Sub-headers for M, F, T under each main column -->
                @for ($i = 0; $i < 10; $i++)
                    <th>M</th>
                    <th>F</th>
                    <th>T</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @foreach ($subjectPerformance as $subject => $data)
                <tr>
                    <td>{{ $subject }}</td>
                    <td>{{ $data['Candidates']['M'] }}</td>
                    <td>{{ $data['Candidates']['F'] }}</td>
                    <td>{{ $data['Candidates']['T'] }}</td>
                    @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $grade)
                        <td>{{ $data[$grade]['M'] }}</td>
                        <td>{{ $data[$grade]['F'] }}</td>
                        <td>{{ $data[$grade]['T'] }}</td>
                    @endforeach
                    @foreach (['AB%', 'ABC%', 'DEU%'] as $percentage)
                        <td>{{ $data[$percentage]['M'] }}</td>
                        <td>{{ $data[$percentage]['F'] }}</td>
                        <td>{{ $data[$percentage]['T'] }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
