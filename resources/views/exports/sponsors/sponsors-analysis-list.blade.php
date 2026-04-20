<!DOCTYPE html>
<html>

<head>
    <title>Sponsors Analysis List</title>
</head>

<body>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Firstname</th>
                <th>Lastname</th>
                <th>Gender</th>
                <th>Date of Birth</th>
                <th>Nationality</th>
                <th>Relation</th>
                <th>Profession</th>
                <th>Phone</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $sponsor)
                <tr>
                    <td>{{ $sponsor->id }}</td>
                    <td>{{ $sponsor->first_name }}</td>
                    <td>{{ $sponsor->last_name }}</td>
                    <td>{{ $sponsor->gender }}</td>
                    <td>{{ $sponsor->date_of_birth }}</td>
                    <td>{{ $sponsor->nationality }}</td>
                    <td>{{ $sponsor->relation }}</td>
                    <td>{{ $sponsor->profession }}</td>
                    <td>{{ $sponsor->phone }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
