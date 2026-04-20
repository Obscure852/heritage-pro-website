<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Master Timetable Overview</title>
    <style>
        /* Override compact sizes for master grid */
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #1f2937; }
        .timetable-grid th { font-size: 7px; padding: 2px 3px; }
        .timetable-grid td { font-size: 7px; padding: 1px 2px; min-width: 50px; }
        .grade-section { page-break-before: always; }
        .grade-section:first-child { page-break-before: auto; }
        .grade-heading { font-size: 13px; font-weight: 700; color: #1f2937; padding-bottom: 4px; border-bottom: 2px solid #4e73df; margin: 10px 0 8px 0; }
        .class-heading { font-size: 10px; font-weight: 600; color: #374151; margin: 8px 0 4px 0; }
        .class-grid { page-break-inside: avoid; margin-bottom: 12px; }
    </style>
    @include('timetable.exports._pdf-grid-styles')
</head>
<body>
    <div class="header">
        @if($logoBase64)
            <img src="{{ $logoBase64 }}" style="height:60px;margin-bottom:5px;">
        @endif
        <h1>{{ $school_data->school_name }}</h1>
        <p>{{ $school_data->physical_address }}</p>
        <div class="report-title">Master Timetable Overview</div>
        <p>{{ $timetableName }} | Generated: {{ now()->format('d M Y') }}</p>
    </div>

    @php
        $COLORS = ['#3b82f6','#ef4444','#10b981','#f59e0b','#8b5cf6','#ec4899','#14b8a6','#f97316','#6366f1','#84cc16','#06b6d4','#e11d48','#a855f7','#22c55e','#eab308','#0ea5e9','#d946ef','#64748b','#f43f5e','#2dd4bf'];
        $colorMap = [];
        $colorIndex = 0;

        // Group classes by grade
        $byGrade = [];
        foreach ($masterData['classes'] as $klassId => $info) {
            $gId = $info['grade_id'];
            if ($gradeFilter && $gId != $gradeFilter) continue;
            $byGrade[$gId]['classes'][$klassId] = $info;
            $byGrade[$gId]['name'] = $info['grade_name'];
            $byGrade[$gId]['sequence'] = $info['grade_sequence'];
        }
        uasort($byGrade, fn($a, $b) => $a['sequence'] <=> $b['sequence']);
    @endphp

    @foreach ($byGrade as $gradeId => $gradeGroup)
        <div class="grade-section">
            <div class="grade-heading">{{ $gradeGroup['name'] }}</div>

            @php $sortedClasses = collect($gradeGroup['classes'])->sortBy('name'); @endphp

            @foreach ($sortedClasses as $klassId => $classInfo)
                <div class="class-grid">
                    <div class="class-heading">{{ $classInfo['name'] }}</div>
                    <table class="timetable-grid">
                        <thead>
                            <tr>
                                <th style="min-width: 35px;">Day</th>
                                @foreach ($daySchedule as $item)
                                    @if ($item['type'] === 'period')
                                        <th>P{{ $item['period'] }}</th>
                                    @elseif ($item['type'] === 'break')
                                        <th class="slot-break"></th>
                                    @endif
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @for ($day = 1; $day <= 6; $day++)
                                <tr>
                                    <td class="day-label" style="font-size:7px;">D{{ $day }}</td>
                                    @php $skipUntil = 0; @endphp
                                    @foreach ($daySchedule as $si => $schedItem)
                                        @if ($schedItem['type'] === 'break')
                                            <td class="slot-break"></td>
                                            @continue
                                        @endif
                                        @php $period = (int) $schedItem['period']; @endphp
                                        @if ($period < $skipUntil)
                                            @continue
                                        @endif
                                        @php $slot = $masterData['grids'][$klassId][$day][$period] ?? null; @endphp
                                        @if ($slot)
                                            @php
                                                $duration = $slot['duration'] ?? 1;
                                                $color = getViewColor($slot['klass_subject_id'] ?? 0, $colorMap, $colorIndex, $COLORS);
                                                $rgb = hexToRgb($color);
                                                $colspan = $duration > 1 ? getActualColspan($daySchedule, $si, $duration) : 1;
                                                $skipUntil = $period + $duration;
                                            @endphp
                                            <td @if ($colspan > 1) colspan="{{ $colspan }}" @endif
                                                style="background-color: rgba({{ $rgb['r'] }},{{ $rgb['g'] }},{{ $rgb['b'] }}, 0.12); border-left: 2px solid {{ $color }};">
                                                <span style="font-weight:600;">{{ $slot['subject_abbrev'] }}</span>
                                                <span style="font-size:6px; color:#6b7280;">{{ $slot['teacher_initials'] }}</span>
                                            </td>
                                        @else
                                            <td></td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
    @endforeach

    <div class="footer">{{ $school_data->school_name }} - Timetable System | {{ now()->format('d M Y H:i') }}</div>
</body>
</html>
