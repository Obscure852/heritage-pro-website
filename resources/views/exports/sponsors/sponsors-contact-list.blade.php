<!DOCTYPE html>
<html>

<head>
    <title>Sponsors Contact List</title>
</head>

<body>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Sponsor Names</th>
                <th>Gender</th>
                <th>Nationality</th>
                <th>Phone</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $index => $sponsor)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $sponsor->fullName ?? '' }}</td>
                    <td>{{ $sponsor->gender ?? '' }}</td>
                    <td>{{ $sponsor->nationality ?? '' }}</td>
                    <td>{{ $sponsor->phone ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
