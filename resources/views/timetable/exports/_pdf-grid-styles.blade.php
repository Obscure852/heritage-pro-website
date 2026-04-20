<style>
    /* PDF Reset */
    * { margin: 0; padding: 0; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; }

    /* Header */
    .header { text-align: center; padding-bottom: 12px; border-bottom: 2px solid #4e73df; margin-bottom: 15px; }
    .header h1 { font-size: 18px; font-weight: 700; color: #1f2937; margin: 5px 0 2px 0; }
    .header p { font-size: 10px; color: #6b7280; margin: 2px 0; }
    .report-title { font-size: 14px; font-weight: 700; color: #4e73df; margin: 8px 0 4px 0; }

    /* Timetable Grid */
    .timetable-grid { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .timetable-grid th {
        font-size: 9px;
        background-color: #343a40;
        color: #ffffff;
        padding: 4px 6px;
        border: 1px solid #343a40;
        text-align: center;
        font-weight: 600;
    }
    .timetable-grid td {
        font-size: 9px;
        padding: 3px 4px;
        border: 1px solid #dee2e6;
        text-align: center;
        vertical-align: middle;
    }

    /* Break columns */
    .slot-break {
        background-color: #fef3c7;
        font-size: 8px;
        color: #92400e;
        min-width: 20px;
    }

    /* Day label */
    .day-label {
        font-weight: 600;
        background-color: #f8f9fa;
        white-space: nowrap;
        min-width: 50px;
        text-align: left;
        padding-left: 6px;
    }

    /* Slot content */
    .slot-subject { font-weight: 600; font-size: 9px; line-height: 1.3; }
    .slot-detail { font-size: 8px; color: #374151; }

    /* Footer */
    .footer { margin-top: 20px; padding-top: 8px; border-top: 1px solid #dee2e6; text-align: center; font-size: 8px; color: #666666; }
</style>

@php
    if (!function_exists('getViewColor')) {
        function getViewColor($ksId, &$colorMap, &$colorIndex, $COLORS) {
            if (!$ksId) return '#94a3b8';
            if (!isset($colorMap[$ksId])) {
                $colorMap[$ksId] = $COLORS[$colorIndex % count($COLORS)];
                $colorIndex++;
            }
            return $colorMap[$ksId];
        }
    }

    if (!function_exists('hexToRgb')) {
        function hexToRgb($hex) {
            $hex = ltrim($hex, '#');
            return [
                'r' => hexdec(substr($hex, 0, 2)),
                'g' => hexdec(substr($hex, 2, 2)),
                'b' => hexdec(substr($hex, 4, 2)),
            ];
        }
    }

    if (!function_exists('viewGetInitials')) {
        function viewGetInitials($name) {
            $parts = preg_split('/\s+/', trim($name));
            if (empty($parts) || $parts[0] === '') return '?';
            $first = mb_strtoupper(mb_substr($parts[0], 0, 1));
            $last = count($parts) > 1 ? mb_strtoupper(mb_substr(end($parts), 0, 1)) : '';
            return $first . $last;
        }
    }

    if (!function_exists('getActualColspan')) {
        function getActualColspan($daySchedule, $startIdx, $duration) {
            $colspan = 0;
            $periodsFound = 0;
            for ($i = $startIdx; $i < count($daySchedule) && $periodsFound < $duration; $i++) {
                $colspan++;
                if ($daySchedule[$i]['type'] === 'period') {
                    $periodsFound++;
                }
            }
            return $colspan;
        }
    }
@endphp
