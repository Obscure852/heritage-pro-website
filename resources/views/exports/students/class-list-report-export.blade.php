<!DOCTYPE html>
<html>
<head>
    <title>Class List Report</title>
</head>
<body>
    <table>
        <tr>
            <td colspan="10"><strong>{{ $grade_name }} - {{ $list_name }}</strong></td>
        </tr>
        <tr>
            <td><strong>Total:</strong> {{ $statistics['total'] }}</td>
            <td><strong>Male:</strong> {{ $statistics['male'] }}</td>
            <td><strong>Female:</strong> {{ $statistics['female'] }}</td>
            @if($statistics['show_boarding'] ?? false)
                <td><strong>Boarding:</strong> {{ $statistics['boarding'] }}</td>
                <td><strong>Day:</strong> {{ $statistics['day'] }}</td>
            @endif
        </tr>
    </table>
    <br>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Gender</th>
                <th>PSLE</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($students as $index => $student)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $student->first_name }}</td>
                    <td>{{ $student->last_name }}</td>
                    <td>{{ $student->gender }}</td>
                    <td>{{ optional($student->psle)->overall_grade ?? '-' }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
