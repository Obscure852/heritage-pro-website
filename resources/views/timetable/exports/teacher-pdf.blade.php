<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Teacher Timetable - {{ $teacherName }}</title>
    @include('timetable.exports._pdf-grid-styles')
</head>
<body>
    <div class="header">
        @if($logoBase64)
            <img src="{{ $logoBase64 }}" style="height:60px;margin-bottom:5px;">
        @endif
        <h1>{{ $school_data->school_name }}</h1>
        <p>{{ $school_data->physical_address }}</p>
        <div class="report-title">{{ $teacherName }} - Teacher Timetable</div>
        <p>{{ $timetableName }} | Generated: {{ now()->format('d M Y') }}</p>
    </div>

    @php
        $COLORS = ['#3b82f6','#ef4444','#10b981','#f59e0b','#8b5cf6','#ec4899','#14b8a6','#f97316','#6366f1','#84cc16','#06b6d4','#e11d48','#a855f7','#22c55e','#eab308','#0ea5e9','#d946ef','#64748b','#f43f5e','#2dd4bf'];
        $colorMap = [];
        $colorIndex = 0;
    @endphp

    <table class="timetable-grid">
        <thead>
            <tr>
                <th style="min-width: 50px;">Day</th>
                @foreach ($daySchedule as $item)
                    @if ($item['type'] === 'period')
                        <th>P{{ $item['period'] }}<br><span style="font-size:7px;">{{ $item['start_time'] }}-{{ $item['end_time'] }}</span></th>
                    @elseif ($item['type'] === 'break')
                        <th class="slot-break">{{ $item['label'] ?? 'Break' }}</th>
                    @endif
                @endforeach
            </tr>
        </thead>
        <tbody>
            @for ($day = 1; $day <= 6; $day++)
                <tr>
                    <td class="day-label">Day {{ $day }}</td>
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
                        @php $slot = $gridData[$day][$period] ?? null; @endphp
                        @if ($slot)
                            @php
                                $duration = $slot['duration'] ?? 1;
                                $color = getViewColor($slot['klass_subject_id'] ?? 0, $colorMap, $colorIndex, $COLORS);
                                $rgb = hexToRgb($color);
                                $colspan = $duration > 1 ? getActualColspan($daySchedule, $si, $duration) : 1;
                                $skipUntil = $period + $duration;
                            @endphp
                            <td @if ($colspan > 1) colspan="{{ $colspan }}" @endif
                                style="background-color: rgba({{ $rgb['r'] }},{{ $rgb['g'] }},{{ $rgb['b'] }}, 0.12); border-left: 3px solid {{ $color }};">
                                <div class="slot-subject">{{ Str::limit($slot['subject_name'] ?? '?', 12) }}</div>
                                <div class="slot-detail" style="color: #4e73df; font-weight: 500;">{{ $slot['class_name'] ?? '' }}</div>
                                @if (!empty($slot['venue_name']))
                                    <div class="slot-detail" style="font-size:6px;color:#9ca3af;">{{ $slot['venue_name'] }}</div>
                                @endif
                            </td>
                        @else
                            <td></td>
                        @endif
                    @endforeach
                </tr>
            @endfor
        </tbody>
    </table>

    <div class="footer">{{ $school_data->school_name }} - Timetable System | {{ now()->format('d M Y H:i') }}</div>
</body>
</html>
