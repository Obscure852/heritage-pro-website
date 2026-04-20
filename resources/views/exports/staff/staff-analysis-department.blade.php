<!DOCTYPE html>
<html>

<head>
    <title>Staff Analysis By Department</title>
</head>

<body>
    @if (!empty($data))
        <h6>Analysis By Department</h6>
        @foreach ($data as $department => $users)
            <h6>{{ $department }}</h6>
            <table class="table table-bordered table-sm">
                <thead>
                    <tr class="text-muted">
                        <th>ID</th>
                        <th>Name</th>
                        <th>Date of Birth</th>
                        <th>Gender</th>
                        <th>Area of Work</th>
                        <th>ID Number</th>
                        <th>Phone</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->firstname . ' ' . $user->middlename . ' ' . $user->lastname }}
                            </td>
                            <td>{{ $user->date_of_birth }}</td>
                            <td>{{ $user->gender }}</td>
                            <td>{{ $user->area_of_work }}</td>
                            <td>{{ $user->id_number }}</td>
                            <td>{{ $user->phone }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach
    @endif
</body>
</html>
