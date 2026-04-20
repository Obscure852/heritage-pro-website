<table>
    <thead>
        <tr>
            <th>Student Name</th>
            <th>Email</th>
            <th>Total Time</th>
            <th>Avg Session</th>
            <th>Active Days</th>
            <th>Last Access</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $row)
            <tr>
                <td>{{ $row['student_name'] ?? 'N/A' }}</td>
                <td>{{ $row['email'] ?? 'N/A' }}</td>
                <td class="text-center">{{ $row['total_time'] ?? '0 min' }}</td>
                <td class="text-center">{{ $row['avg_session'] ?? '0 min' }}</td>
                <td class="text-center">{{ $row['active_days'] ?? 0 }}</td>
                <td>{{ $row['last_access'] ?? 'Never' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
