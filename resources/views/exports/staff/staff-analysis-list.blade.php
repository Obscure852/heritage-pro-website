<!DOCTYPE html>
<html>

<head>
    <title>Staff Analysis List</title>
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
                <th>ID Number</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Position</th>
                <th>Nationality</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->firstname }}</td>
                    <td>{{ $user->lastname }}</td>
                    <td>{{ $user->gender }}</td>
                    <td>{{ $user->date_of_birth }}</td>
                    <td>{{ $user->id_number }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->phone }}</td>
                    <td>{{ $user->position }}</td>
                    <td>{{ $user->nationality }}</td>
                    <td>{{ $user->status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
