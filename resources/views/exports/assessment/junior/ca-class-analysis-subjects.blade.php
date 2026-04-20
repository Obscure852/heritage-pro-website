<table class="table table-bordered table-sm">
    <thead>
        <tr>
            <th>Subject</th>
            <th style="text-align:center" colspan="3">A</th>
            <th style="text-align:center" colspan="3">B</th>
            <th style="text-align:center" colspan="3">C</th>
            <th style="text-align:center" colspan="3">D</th>
            <th style="text-align:center" colspan="3">E</th>
            <th style="text-align:center" colspan="3">U</th>
            <th style="text-align:center" colspan="3">Total</th>
            <th style="text-align:center" colspan="3">No Scores</th>
            <th style="text-align:center" colspan="3">AB%</th>
            <th style="text-align:center" colspan="3">ABC%</th>
            <th style="text-align:center" colspan="3">ABCD%</th>
            <th style="text-align:center" colspan="3">DEU%</th>
        </tr>
        <tr>
            <th>Sex</th>
            <th>M</th><th>F</th><th>T</th>  {{-- A --}}
            <th>M</th><th>F</th><th>T</th>  {{-- B --}}
            <th>M</th><th>F</th><th>T</th>  {{-- C --}}
            <th>M</th><th>F</th><th>T</th>  {{-- D --}}
            <th>M</th><th>F</th><th>T</th>  {{-- E --}}
            <th>M</th><th>F</th><th>T</th>  {{-- U --}}
            <th>M</th><th>F</th><th>T</th>  {{-- Enrolled --}}
            <th>M</th><th>F</th><th>T</th>  {{-- No Scores --}}

            <th>M</th><th>F</th><th>T</th>  {{-- AB%  --}}
            <th>M</th><th>F</th><th>T</th>  {{-- ABC% --}}
            <th>M</th><th>F</th><th>T</th>  {{-- ABCD% --}}
            <th>M</th><th>F</th><th>T</th>  {{-- DEU% --}}
        </tr>
    </thead>

    <tbody>
        {{-- per‑subject rows --}}
        @foreach ($subjectGradeCounts as $subject => $counts)
            <tr>
                <td>{{ $subject }}</td>
                {{-- Grade counts --}}
                @foreach (['A','B','C','D','E','U'] as $g)
                    <td>{{ $counts[$g]['M'] }}</td>
                    <td>{{ $counts[$g]['F'] }}</td>
                    <td>{{ $counts[$g]['M'] + $counts[$g]['F'] }}</td>
                @endforeach
                
                {{-- Enrolled --}}
                <td>{{ $counts['enrolled']['M'] }}</td>
                <td>{{ $counts['enrolled']['F'] }}</td>
                <td>{{ $counts['enrolled']['M'] + $counts['enrolled']['F'] }}</td>
                
                {{-- No Scores --}}
                <td>{{ $counts['no_scores']['M'] }}</td>
                <td>{{ $counts['no_scores']['F'] }}</td>
                <td>{{ $counts['no_scores']['M'] + $counts['no_scores']['F'] }}</td>
                
                {{-- AB% with total --}}
                <td>{{ $counts['AB%']['M'] }}%</td>
                <td>{{ $counts['AB%']['F'] }}%</td>
                <td>{{ 
                    round(
                        ($counts['AB%']['M'] * ($counts['A']['M'] + $counts['B']['M'])
                        + $counts['AB%']['F'] * ($counts['A']['F'] + $counts['B']['F']))
                        / max(($counts['A']['M'] + $counts['B']['M'] + $counts['A']['F'] + $counts['B']['F']), 1),
                        1
                    )
                }}%</td>
                
                {{-- ABC% with total --}}
                <td>{{ $counts['ABC%']['M'] }}%</td>
                <td>{{ $counts['ABC%']['F'] }}%</td>
                <td>{{ 
                    round(
                        ($counts['ABC%']['M'] * ($counts['A']['M'] + $counts['B']['M'] + $counts['C']['M'])
                        + $counts['ABC%']['F'] * ($counts['A']['F'] + $counts['B']['F'] + $counts['C']['F']))
                        / max(($counts['A']['M'] + $counts['B']['M'] + $counts['C']['M']
                                + $counts['A']['F'] + $counts['B']['F'] + $counts['C']['F']), 1),
                        1
                    )
                }}%</td>
                
                {{-- ABCD% with total --}}
                <td>{{ $counts['ABCD%']['M'] }}%</td>
                <td>{{ $counts['ABCD%']['F'] }}%</td>
                <td>{{ 
                    round(
                        ($counts['ABCD%']['M'] * ($counts['A']['M'] + $counts['B']['M']
                            + $counts['C']['M'] + $counts['D']['M'])
                        + $counts['ABCD%']['F'] * ($counts['A']['F'] + $counts['B']['F']
                            + $counts['C']['F'] + $counts['D']['F']))
                        / max(($counts['A']['M'] + $counts['B']['M'] + $counts['C']['M']
                                + $counts['D']['M'] + $counts['A']['F'] + $counts['B']['F']
                                + $counts['C']['F'] + $counts['D']['F']), 1),
                        1
                    )
                }}%</td>
                
                {{-- DEU% with total --}}
                <td>{{ $counts['DEU%']['M'] }}%</td>
                <td>{{ $counts['DEU%']['F'] }}%</td>
                <td>{{ 
                    round(
                        ($counts['DEU%']['M'] * ($counts['D']['M'] + $counts['E']['M'] + $counts['U']['M'])
                        + $counts['DEU%']['F'] * ($counts['D']['F'] + $counts['E']['F'] + $counts['U']['F']))
                        / max(($counts['D']['M'] + $counts['E']['M'] + $counts['U']['M']
                                + $counts['D']['F'] + $counts['E']['F'] + $counts['U']['F']), 1),
                        1
                    )
                }}%</td>
            </tr>
        @endforeach

        {{-- grand‑totals row --}}
        <tr style="font-weight:600;background:#f3f3f3;">
            <td>Totals</td>
            {{-- Grade totals --}}
            @foreach (['A','B','C','D','E','U'] as $g)
                <td>{{ $subjectTotals[$g]['M'] }}</td>
                <td>{{ $subjectTotals[$g]['F'] }}</td>
                <td>{{ $subjectTotals[$g]['M'] + $subjectTotals[$g]['F'] }}</td>
            @endforeach
            
            {{-- Enrolled totals --}}
            <td>{{ $subjectTotals['enrolled']['M'] }}</td>
            <td>{{ $subjectTotals['enrolled']['F'] }}</td>
            <td>{{ $subjectTotals['enrolled']['M'] + $subjectTotals['enrolled']['F'] }}</td>
            
            {{-- No Scores totals --}}
            <td>{{ $subjectTotals['no_scores']['M'] }}</td>
            <td>{{ $subjectTotals['no_scores']['F'] }}</td>
            <td>{{ $subjectTotals['no_scores']['M'] + $subjectTotals['no_scores']['F'] }}</td>
            
            {{-- AB% totals --}}
            <td>{{ $subjectTotals['AB%']['M'] }}%</td>
            <td>{{ $subjectTotals['AB%']['F'] }}%</td>
            <td>{{ 
                round(
                    (($subjectTotals['AB%']['M'] * ($subjectTotals['A']['M'] + $subjectTotals['B']['M']))
                    + ($subjectTotals['AB%']['F'] * ($subjectTotals['A']['F'] + $subjectTotals['B']['F'])))
                    / max(($subjectTotals['A']['M'] + $subjectTotals['B']['M']
                            + $subjectTotals['A']['F'] + $subjectTotals['B']['F']), 1),
                    1
                )
            }}%</td>
            
            {{-- ABC% totals --}}
            <td>{{ $subjectTotals['ABC%']['M'] }}%</td>
            <td>{{ $subjectTotals['ABC%']['F'] }}%</td>
            <td>{{ 
                round(
                    (($subjectTotals['ABC%']['M'] * ($subjectTotals['A']['M'] + $subjectTotals['B']['M'] + $subjectTotals['C']['M']))
                    + ($subjectTotals['ABC%']['F'] * ($subjectTotals['A']['F'] + $subjectTotals['B']['F'] + $subjectTotals['C']['F'])))
                    / max(($subjectTotals['A']['M'] + $subjectTotals['B']['M'] + $subjectTotals['C']['M']
                            + $subjectTotals['A']['F'] + $subjectTotals['B']['F'] + $subjectTotals['C']['F']), 1),
                    1
                )
            }}%</td>
            
            {{-- ABCD% totals --}}
            <td>{{ $subjectTotals['ABCD%']['M'] }}%</td>
            <td>{{ $subjectTotals['ABCD%']['F'] }}%</td>
            <td>{{ 
                round(
                    (($subjectTotals['ABCD%']['M'] * ($subjectTotals['A']['M'] + $subjectTotals['B']['M']
                        + $subjectTotals['C']['M'] + $subjectTotals['D']['M']))
                    + ($subjectTotals['ABCD%']['F'] * ($subjectTotals['A']['F'] + $subjectTotals['B']['F']
                        + $subjectTotals['C']['F'] + $subjectTotals['D']['F'])))
                    / max(($subjectTotals['A']['M'] + $subjectTotals['B']['M']
                            + $subjectTotals['C']['M'] + $subjectTotals['D']['M']
                            + $subjectTotals['A']['F'] + $subjectTotals['B']['F']
                            + $subjectTotals['C']['F'] + $subjectTotals['D']['F']), 1),
                    1
                )
            }}%</td>
            
            {{-- DEU% totals --}}
            <td>{{ $subjectTotals['DEU%']['M'] }}%</td>
            <td>{{ $subjectTotals['DEU%']['F'] }}%</td>
            <td>{{ 
                round(
                    (($subjectTotals['DEU%']['M'] * ($subjectTotals['D']['M'] + $subjectTotals['E']['M'] + $subjectTotals['U']['M']))
                    + ($subjectTotals['DEU%']['F'] * ($subjectTotals['D']['F'] + $subjectTotals['E']['F'] + $subjectTotals['U']['F'])))
                    / max(($subjectTotals['D']['M'] + $subjectTotals['E']['M'] + $subjectTotals['U']['M']
                            + $subjectTotals['D']['F'] + $subjectTotals['E']['F'] + $subjectTotals['U']['F']), 1),
                    1
                )
            }}%</td>
        </tr>
    </tbody>
</table>