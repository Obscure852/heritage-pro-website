<style>
    /* Timetable Read-Only Grid Styles */
    .timetable-grid { width: 100%; border-collapse: collapse; }
    .timetable-grid th { padding: 6px 8px; font-size: 12px; background: #f8f9fa; border: 1px solid #e5e7eb; text-align: center; font-weight: 500; white-space: nowrap; }
    .timetable-grid td { padding: 4px 6px; border: 1px solid #e5e7eb; text-align: center; vertical-align: middle; min-width: 80px; }
    .slot-cell { cursor: default; position: relative; }
    .slot-filled { border-left-width: 3px; }
    .slot-filled .slot-subject { font-weight: 600; font-size: 11px; line-height: 1.3; }
    .slot-filled .slot-teacher { font-size: 10px; color: #6b7280; }
    .slot-filled .slot-venue { font-size: 9px; color: #9ca3af; line-height: 1.2; }
    .slot-empty { background: #fafbfc; }
    .slot-break { background: #fef3c7; border-left: 1px dashed #f59e0b; border-right: 1px dashed #f59e0b; min-width: 30px; font-size: 9px; color: #92400e; text-align: center; }
    .day-label-cell { font-weight: 500; font-size: 12px; background: #fafbfc; white-space: nowrap; min-width: 55px; }

    /* Page container styles */
    .view-container { background: white; border-radius: 3px; padding: 0; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    .view-header { background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%); color: white; padding: 28px; border-radius: 3px 3px 0 0; }
    .view-body { padding: 24px; }
    .view-selector { display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap; }
    .view-selector .form-group { margin-bottom: 0; }
    .view-selector select { min-width: 200px; }
    .view-selector .form-control,
    .view-selector .form-select {
        font-size: 0.9rem;
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        padding: 8px 12px;
        color: #374151;
        background-color: #fff;
        transition: all 0.2s ease;
    }
    .view-selector .form-control:focus,
    .view-selector .form-select:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    .view-selector .btn-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        padding: 10px 20px;
        border-radius: 3px;
        font-size: 14px;
        font-weight: 500;
        border: none;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .view-selector .btn-primary:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        color: white;
    }
    .btn-loading.loading .btn-text { display: none; }
    .btn-loading.loading .btn-spinner { display: inline-flex !important; align-items: center; }
    .btn-loading:disabled { opacity: 0.7; cursor: not-allowed; }

    .help-text { background: #f8f9fa; padding: 12px; border-left: 4px solid #3b82f6; border-radius: 0 3px 3px 0; margin-bottom: 20px; }
    .help-text .help-title { font-weight: 600; color: #374151; margin-bottom: 4px; }
    .help-text .help-content { color: #6b7280; font-size: 13px; line-height: 1.4; }

    .status-badge { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 500; text-transform: capitalize; }
    .status-draft { background: #fef3c7; color: #92400e; }
    .status-published { background: #d1fae5; color: #065f46; }
    .status-archived { background: #f3f4f6; color: #4b5563; }

    /* Term selector */
    .term-select {
        max-width: 200px;
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        padding: 8px 12px;
        font-size: 14px;
        color: #374151;
        background-color: #fff;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .term-select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        outline: none;
    }

    /* Reports Dropdown Styling */
    .reports-dropdown .dropdown-toggle {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border: none;
        color: white;
        font-weight: 500;
        padding: 10px 16px;
        border-radius: 3px;
        transition: all 0.2s ease;
    }
    .reports-dropdown .dropdown-toggle:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        color: white;
    }
    .reports-dropdown .dropdown-toggle:focus {
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
        color: white;
    }
    .reports-dropdown .dropdown-menu {
        border: none;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        border-radius: 3px;
        padding: 8px 0;
        min-width: 220px;
        margin-top: 4px;
    }
    .reports-dropdown .dropdown-item {
        padding: 8px 16px;
        font-size: 14px;
        color: #374151;
    }
    .reports-dropdown .dropdown-item:hover {
        background: #f3f4f6;
        color: #1f2937;
    }
    .reports-dropdown .dropdown-item i {
        width: 20px;
        margin-right: 8px;
    }
    .reports-dropdown .dropdown-divider {
        margin: 8px 0;
    }
    .reports-dropdown .dropdown-header {
        font-size: 12px;
        font-weight: 600;
        color: #6b7280;
        padding: 8px 16px;
    }

    /* Print styles */
    @media print {
        .vertical-menu, #page-topbar, .footer, .view-selector, .page-title-box, .back-link-wrap { display: none !important; }
        .main-content { margin-left: 0 !important; }
        .view-container { box-shadow: none; }
        .view-header { border-radius: 0; }
        .timetable-grid { font-size: 10px; }
        .timetable-grid th, .timetable-grid td { padding: 3px 4px; }
    }
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
