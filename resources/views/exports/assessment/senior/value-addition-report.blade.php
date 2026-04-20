<table>
    <tr>
        <td colspan="16"><strong>{{ $school_data->school_name }} - Value Addition Report</strong></td>
    </tr>
    <tr>
        <td colspan="16"><strong>{{ $gradeName }} - {{ $year }}</strong></td>
    </tr>
    <tr><td></td></tr>
</table>

@foreach ($subjects as $subject)
    <table>
        <tr>
            <td colspan="16"><strong>{{ $subject['subjectName'] }}</strong> (JCE Source: {{ $subject['jceInput']['label'] }})</td>
        </tr>
    </table>

    {{-- JCE Input --}}
    <table>
        <tr>
            <td><strong>JC INPUT</strong></td>
            @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $grade)
                <td><strong>{{ $grade }}</strong></td>
            @endforeach
            <td><strong>Total</strong></td>
            <td><strong>%(A-C)</strong></td>
        </tr>
        <tr>
            <td></td>
            @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $grade)
                <td>{{ $subject['jceInput']['grades'][$grade] }}</td>
            @endforeach
            <td>{{ $subject['jceInput']['total'] }}</td>
            <td>{{ $subject['jceInput']['percentAC'] }}%</td>
        </tr>
    </table>

    {{-- Performance Table --}}
    <table>
        <tr>
            <td><strong>Test/Exam</strong></td>
            @foreach (['A*', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'U', 'X'] as $grade)
                <td><strong>{{ $grade }}</strong></td>
            @endforeach
            <td><strong>Total</strong></td>
            <td><strong>%(A-C)</strong></td>
            <td><strong>%(A-E)</strong></td>
            <td><strong>JC %(A-C)</strong></td>
            <td><strong>VA</strong></td>
        </tr>
        @foreach ($subject['termGroups'] as $group)
            <tr>
                <td colspan="16"><strong>{{ $group['termLabel'] }}</strong></td>
            </tr>
            @foreach ($group['testRows'] as $row)
                <tr>
                    <td>{{ $row['testName'] }}</td>
                    @foreach (['A*', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'U', 'X'] as $grade)
                        <td>{{ $row['grades'][$grade] }}</td>
                    @endforeach
                    <td>{{ $row['total'] }}</td>
                    <td>{{ $row['percentAC'] }}%</td>
                    <td>{{ $row['percentAE'] }}%</td>
                    <td>{{ $row['jcePercentAC'] }}%</td>
                    <td>{{ $row['va'] }}</td>
                </tr>
            @endforeach
        @endforeach
    </table>

    <table><tr><td></td></tr></table>
@endforeach
