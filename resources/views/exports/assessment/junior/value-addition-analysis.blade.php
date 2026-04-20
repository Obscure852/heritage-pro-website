<table>
    <thead>
        <tr>
            <th>Grade</th>
            @foreach ($jcSubjects as $subject)
                <th colspan="2" class="text-center">{{ $subject }}</th>
            @endforeach
        </tr>
        <tr>
            <th></th>
            @foreach ($jcSubjects as $subject)
                <th class="text-center">PSLE</th>
                <th class="text-center">JC</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $grade)
            <tr>
                <td>{{ $grade }}</td>
                @foreach ($jcSubjects as $subject)
                    <td>{{ $gradeCounts[$subject]['PSLE'][$grade] ?? 0 }}</td>
                    <td>{{ $gradeCounts[$subject]['JC'][$grade] ?? 0 }}</td>
                @endforeach
            </tr>
        @endforeach
        <tr>
            <td>Quality</td>
            @foreach ($jcSubjects as $subject)
                <td>{{ $gradeCounts[$subject]['qualityPSLE'] }}%</td>
                <td>{{ $gradeCounts[$subject]['qualityJC'] }}%</td>
            @endforeach
        </tr>
        <tr>
            <td>Quantity</td>
            @foreach ($jcSubjects as $subject)
                <td>{{ $gradeCounts[$subject]['quantityPSLE'] }}%</td>
                <td>{{ $gradeCounts[$subject]['quantityJC'] }}%</td>
            @endforeach
        </tr>
        <tr>
            <td>Value Addition</td>
            @foreach ($jcSubjects as $subject)
                <td colspan="2" class="text-center">
                    {{ $gradeCounts[$subject]['valueAddition'] }}
                </td>
            @endforeach
        </tr>
    </tbody>
</table>

<h5>PSLE Overall Grade Distribution</h5>
<table>
    <thead>
        <tr>
            @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $grade)
                <th>{{ $grade }}</th>
            @endforeach
            <th>Total</th>
            <th>AB%</th>
            <th>ABC%</th>
            <th>DEU%</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $grade)
                <td>{{ $psleGradeCounts[$grade] ?? 0 }}</td>
            @endforeach
            @php
                $totalPSLE = array_sum($psleGradeCounts);
                $psleAB = (($psleGradeCounts['A'] ?? 0) + ($psleGradeCounts['B'] ?? 0)) / max($totalPSLE, 1) * 100;
                $psleABC = (($psleGradeCounts['A'] ?? 0) + ($psleGradeCounts['B'] ?? 0) + ($psleGradeCounts['C'] ?? 0)) / max($totalPSLE, 1) * 100;
                $psleDEU = (($psleGradeCounts['D'] ?? 0) + ($psleGradeCounts['E'] ?? 0) + ($psleGradeCounts['U'] ?? 0)) / max($totalPSLE, 1) * 100;
            @endphp
            <td>{{ $totalPSLE }}</td>
            <td>{{ round($psleAB, 2) }}%</td>
            <td>{{ round($psleABC, 2) }}%</td>
            <td>{{ round($psleDEU, 2) }}%</td>
        </tr>
    </tbody>
</table>

<h5>JC Overall Grade Distribution</h5>
<table>
    <thead>
        <tr>
            @foreach (['M', 'A', 'B', 'C', 'D', 'E', 'U'] as $grade)
                <th>{{ $grade }}</th>
            @endforeach
            <th>Total</th>
            <th>MAB%</th>
            <th>MABC%</th>
            <th>DEU%</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            @foreach (['M', 'A', 'B', 'C', 'D', 'E', 'U'] as $grade)
                <td>{{ $jcGradeCounts[$grade] ?? 0 }}</td>
            @endforeach
            @php
                $totalJC = array_sum($jcGradeCounts);
                $jcMAB = (($jcGradeCounts['M'] ?? 0) + ($jcGradeCounts['A'] ?? 0) + ($jcGradeCounts['B'] ?? 0)) / max($totalJC, 1) * 100;
                $jcMABC = (($jcGradeCounts['M'] ?? 0) + ($jcGradeCounts['A'] ?? 0) + ($jcGradeCounts['B'] ?? 0) + ($jcGradeCounts['C'] ?? 0)) / max($totalJC, 1) * 100;
                $jcDEU = (($jcGradeCounts['D'] ?? 0) + ($jcGradeCounts['E'] ?? 0) + ($jcGradeCounts['U'] ?? 0)) / max($totalJC, 1) * 100;
            @endphp
            <td>{{ $totalJC }}</td>
            <td>{{ round($jcMAB, 2) }}%</td>
            <td>{{ round($jcMABC, 2) }}%</td>
            <td>{{ round($jcDEU, 2) }}%</td>
        </tr>
    </tbody>
</table>
