<!DOCTYPE html>
<html>

<head>
    <title>Grade Subjects Performance Analysis</title>

</head>

<body>
    @foreach ($subjectPerformance as $subjectName => $data)
        <tr>
            <td>{{ $subjectName }}</td>
            @foreach (['A', 'B', 'C', 'D', 'E', 'AB', 'ABC', 'DE'] as $grade)
                <td>{{ $data[$grade]['M'] }}</td>
                <td>{{ $data[$grade]['F'] }}</td>
                <td>{{ round($data[$grade . '%']['M'], 1) }}% / {{ round($data[$grade . '%']['F'], 1) }}%</td>
            @endforeach
        </tr>
    @endforeach
</body>

</html>
