<!DOCTYPE html>
<html>

<head>
    <title>House Students Allocations</title>
</head>

<body>
    @foreach ($data as $house)
        <h4>{{ $house->name ?? '' }} ({{ strtoupper($house->color_code ?? '#2563EB') }})</h4>
        <p>
            Head: {{ $house->houseHead->fullName ?? 'Not assigned' }} |
            Assistant: {{ $house->houseAssistant->fullName ?? 'Not assigned' }} |
            Students: {{ $house->students_count ?? $house->students->count() }} |
            Users: {{ $house->users_count ?? 0 }}
        </p>
        <table>
            <thead>
                <tr>
                    <td>#</td>
                    <th>Name</th>
                    <th>Sex</th>
                    <th>Status</th>
                    <th>Class</th>
                    <th>Grade</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($house->students as $index => $student)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $student->fullName ?? '' }}</td>
                        <td>{{ $student->gender ?? '' }}</td>
                        <td>{{ $student->status ?? '' }}</td>
                        <td>{{ $student->class?->name ?? '' }}</td>
                        <td>{{ $student->class?->grade?->name ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
</body>

</html>
