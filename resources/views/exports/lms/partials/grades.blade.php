<table>
    <thead>
        <tr>
            <th>Student Name</th>
            <th>Email</th>
            <th>Quiz</th>
            <th>Score</th>
            <th>Max</th>
            <th>%</th>
            <th>Grade</th>
            <th>Passed</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $row)
            <tr>
                <td>{{ $row['student_name'] ?? 'N/A' }}</td>
                <td>{{ $row['email'] ?? 'N/A' }}</td>
                <td>{{ $row['quiz_title'] ?? 'N/A' }}</td>
                <td class="text-center">{{ $row['score'] ?? 0 }}</td>
                <td class="text-center">{{ $row['max_score'] ?? 0 }}</td>
                <td class="text-center">{{ $row['percentage'] ?? '0%' }}</td>
                <td class="text-center"><span class="badge {{ in_array($row['grade_letter'] ?? '', ['A', 'B']) ? 'badge-success' : (($row['grade_letter'] ?? '') === 'C' ? 'badge-warning' : 'badge-danger') }}">{{ $row['grade_letter'] ?? 'F' }}</span></td>
                <td class="text-center"><span class="badge {{ ($row['passed'] ?? 'No') === 'Yes' ? 'badge-success' : 'badge-danger' }}">{{ $row['passed'] ?? 'No' }}</span></td>
                <td>{{ $row['submitted_at'] ?? 'N/A' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
