@if(count($data) > 0)
<table>
    <thead>
        <tr>
            <th>Student Name</th>
            <th>Email</th>
            <th>Course</th>
            <th>Risk Type</th>
            <th>Severity</th>
            <th>Description</th>
            <th>Detected</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $row)
            <tr>
                <td>{{ $row['student_name'] ?? 'N/A' }}</td>
                <td>{{ $row['email'] ?? 'N/A' }}</td>
                <td>{{ $row['course'] ?? 'All Courses' }}</td>
                <td><span class="badge badge-warning">{{ $row['risk_type'] ?? 'Unknown' }}</span></td>
                <td><span class="badge {{ strtolower($row['severity'] ?? '') === 'critical' ? 'badge-danger' : 'badge-warning' }}">{{ $row['severity'] ?? 'Medium' }}</span></td>
                <td>{{ $row['description'] ?? 'N/A' }}</td>
                <td>{{ $row['generated_at'] ?? 'N/A' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="summary-box" style="background-color: #d4edda; color: #155724;">
    Great news! No students are currently at risk.
</div>
@endif
