<!DOCTYPE html>
<html>
<head>
    <title>Admissions Analysis By Status</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
    </style>
</head>
<body>
    @foreach ($data as $status => $admissions)
        <h6>{{ $status }}</h6>
        <table>
            <thead>
                <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Gender</th>
                    <th>Date Of Birth</th>
                    <th>Year</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($admissions as $admission)
                    <tr>
                        <td>{{ $admission->first_name }}</td>
                        <td>{{ $admission->last_name }}</td>
                        <td>{{ $admission->gender }}</td>
                        <td>{{ $admission->date_of_birth }}</td>
                        <td>{{ $admission->year }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
</body>
</html>
