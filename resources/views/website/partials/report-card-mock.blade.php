<div style="padding: 22px; background: #fff;">
    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px; padding-bottom: 14px; border-bottom: 2px solid var(--brand-indigo-500);">
        <div style="width: 40px; height: 40px; border-radius: 8px; background: var(--brand-gradient);"></div>
        <div>
            <div style="font-family: var(--font-display); font-size: 14px; font-weight: 700;">Thornhill Preparatory School</div>
            <div style="font-size: 10px; color: var(--fg-3);">End of Term 2 Report · Form 3R · 2025</div>
        </div>
        <div style="margin-left: auto; font-size: 10px; color: var(--fg-3); text-align: right;">
            <div style="font-weight: 700; color: var(--fg-1); font-size: 11px;">Boitumelo Mosadi</div>
            STU-2025-0482
        </div>
    </div>
    <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 2fr; gap: 0; font-size: 11px;">
        @foreach (['Subject', 'CA', 'Exam', 'Grade', 'Teacher\'s comment'] as $heading)
            <div style="padding: 8px 10px; background: var(--bg-subtle); font-weight: 700; color: var(--fg-2); text-transform: uppercase; font-size: 9px; letter-spacing: 0.06em; border-bottom: 1px solid var(--border-1);">{{ $heading }}</div>
        @endforeach
        @foreach ([['English Language', '84', '78', 'A', 'Articulate and consistent.'], ['Setswana', '92', '88', 'A*', 'Exemplary literary analysis.'], ['Mathematics', '76', '82', 'B+', 'Strong effort on geometry.'], ['Double Science', '88', '84', 'A', 'Outstanding practical work.'], ['Geography', '80', '74', 'B+', 'Clear structured essays.'], ['Commerce', '85', '86', 'A', 'Leadership shown in groups.']] as $row)
            @foreach ($row as $cellIndex => $cell)
                <div style="padding: 9px 10px; border-bottom: 1px solid var(--border-1); color: {{ in_array($cellIndex, [0], true) ? 'var(--fg-1)' : 'var(--fg-2)' }}; font-weight: {{ in_array($cellIndex, [0, 3], true) ? 600 : 400 }}; font-size: {{ $cellIndex === 4 ? '10px' : '11px' }};">{{ $cell }}</div>
            @endforeach
        @endforeach
    </div>
    <div style="margin-top: 14px; padding: 12px 14px; background: var(--brand-indigo-50); border-radius: 10px; display: grid; grid-template-columns: repeat(4,1fr); gap: 10px;">
        @foreach ([['Average', '83.5%'], ['Position', '4 of 32'], ['Attendance', '98%'], ['Conduct', 'Excellent']] as $metric)
            <div>
                <div style="font-size: 9px; color: var(--fg-3); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 2px;">{{ $metric[0] }}</div>
                <div style="font-family: var(--font-display); font-weight: 700; font-size: 15px; color: var(--brand-indigo-600);">{{ $metric[1] }}</div>
            </div>
        @endforeach
    </div>
</div>
