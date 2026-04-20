<table>
    <thead>
        <tr>
            <th>Module</th>
            <th>Content</th>
            <th>Type</th>
            <th>Views</th>
            <th>Unique</th>
            <th>Avg Time</th>
            <th>Completion</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $row)
            <tr>
                <td>{{ $row['module'] ?? 'N/A' }}</td>
                <td>{{ $row['content_title'] ?? 'N/A' }}</td>
                <td><span class="badge badge-secondary">{{ $row['content_type'] ?? 'Unknown' }}</span></td>
                <td class="text-center">{{ $row['total_views'] ?? 0 }}</td>
                <td class="text-center">{{ $row['unique_viewers'] ?? 0 }}</td>
                <td class="text-center">{{ $row['avg_time'] ?? '0 min' }}</td>
                <td class="text-center"><span class="badge {{ (int)($row['completion_rate'] ?? 0) >= 70 ? 'badge-success' : 'badge-warning' }}">{{ $row['completion_rate'] ?? '0%' }}</span></td>
            </tr>
        @endforeach
    </tbody>
</table>
