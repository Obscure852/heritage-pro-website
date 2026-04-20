<table>
    <thead>
        <tr>
            <th>Student Name</th>
            <th>Email</th>
            <th>Content Views</th>
            <th>Quiz Attempts</th>
            <th>Total Time</th>
            <th>Last Activity</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $row)
            <tr>
                <td>{{ $row['student_name'] ?? 'N/A' }}</td>
                <td>{{ $row['email'] ?? 'N/A' }}</td>
                <td class="text-center">{{ $row['content_views'] ?? 0 }}</td>
                <td class="text-center">{{ $row['quiz_attempts'] ?? 0 }}</td>
                <td>{{ $row['total_time'] ?? '0 min' }}</td>
                <td>{{ $row['last_activity'] ?? 'Never' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
