@if(count($data) > 0)
    @php $columns = array_keys($data[0] ?? []); @endphp
    <table>
        <thead>
            <tr>
                @foreach($columns as $column)
                    <th>{{ ucwords(str_replace('_', ' ', $column)) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                <tr>
                    @foreach($columns as $column)
                        <td>{{ is_array($row[$column] ?? '') ? implode(', ', $row[$column]) : ($row[$column] ?? 'N/A') }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <div class="no-data">No data available for this custom report.</div>
@endif
