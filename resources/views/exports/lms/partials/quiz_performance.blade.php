<table>
    <thead>
        <tr>
            <th>Quiz</th>
            <th>Total Attempts</th>
            <th>Completions</th>
            <th>Passes</th>
            <th>Pass Rate</th>
            <th>Avg Score</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $row)
            <tr>
                <td>{{ $row['quiz_title'] ?? 'N/A' }}</td>
                <td class="text-center">{{ $row['total_attempts'] ?? 0 }}</td>
                <td class="text-center">{{ $row['completions'] ?? 0 }}</td>
                <td class="text-center">{{ $row['passes'] ?? 0 }}</td>
                <td class="text-center"><span class="badge {{ (int)($row['pass_rate'] ?? 0) >= 70 ? 'badge-success' : ((int)($row['pass_rate'] ?? 0) >= 50 ? 'badge-warning' : 'badge-danger') }}">{{ $row['pass_rate'] ?? '0%' }}</span></td>
                <td class="text-center">{{ $row['avg_score'] ?? '0%' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
