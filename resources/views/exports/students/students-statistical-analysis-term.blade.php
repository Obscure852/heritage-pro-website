<!DOCTYPE html>
<html>

<head>
    <title>Students Statistical Analysis</title>
</head>

<body>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Class</th>
                <th>Class Teacher</th>
                <th>Boys Count</th>
                <th>Girls Count</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $boys = 0;
                $girls = 0;
            @endphp
            @foreach ($data['klasses'] as $index => $klass)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $klass->name }}</td>
                    <td>{{ $klass->teacher->full_name ?? 'N/A' }}</td>
                    @php
                        $boys += $klass->boys_count;
                        $girls += $klass->girls_count;
                    @endphp
                    <td>{{ $klass->boys_count }}</td>
                    <td>{{ $klass->girls_count }}</td>
                    <td>{{ intval($klass->boys_count) + intval($klass->girls_count) }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="3" style="text-align: end"><strong>Totals: </strong></td>
                <td>{{ intval($boys) }}</td>
                <td>{{ intval($girls) }}</td>
                <td>{{ intval($boys) + intval($girls) }}</td>
            </tr>
        </tbody>
    </table>
</body>

</html>
