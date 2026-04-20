<!DOCTYPE html>
<html>

<head>
    <title>Staff Analysis By Department</title>
</head>

<body>
    @if (!empty($data))
        <h5>User Qualifications Report</h5>
        @foreach ($data as $qualification)
            @if (!$qualification->users->isEmpty())
                <h6>{{ $qualification->qualification }} ({{ $qualification->qualification_code }})</h6>
                <table class="table table-bordered table-sm">
                    <thead>
                        <th>Firstname</th>
                        <th>Lastname</th>
                        <th>Gender</th>
                        <th>ID Number</th>
                        <th>Phone</th>
                    </thead>
                    <tbody>
                        @foreach ($qualification->users as $user)
                            <tr>
                                <td>{{ $user->firstname }}</td>
                                <td>{{ $user->lastname }}</td>
                                <td>{{ $user->gender }}</td>
                                <td>{{ $user->id_number }}</td>
                                <td>{{ $user->phone }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @endforeach
    @endif
</body>

</html>
