<table>
    <thead>
        <tr>
            <th>Student Name</th>
            <th>Email</th>
            <th>Course</th>
            <th>Enrolled</th>
            <th>Progress</th>
            <th>Status</th>
            <th>Completed</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $row)
            <tr>
                <td>{{ $row['student_name'] ?? 'N/A' }}</td>
                <td>{{ $row['email'] ?? 'N/A' }}</td>
                <td>{{ $row['course'] ?? 'N/A' }}</td>
                <td>{{ $row['enrolled_at'] ?? 'N/A' }}</td>
                <td><span class="badge {{ (int)($row['progress'] ?? 0) >= 80 ? 'badge-success' : ((int)($row['progress'] ?? 0) >= 50 ? 'badge-warning' : 'badge-danger') }}">{{ $row['progress'] ?? '0%' }}</span></td>
                <td><span class="badge {{ strtolower($row['status'] ?? '') === 'completed' ? 'badge-success' : 'badge-info' }}">{{ $row['status'] ?? 'Pending' }}</span></td>
                <td>{{ $row['completed_at'] ?? 'N/A' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
