@php
    $cellBg = '';
    if ($cell['is_today']) $cellBg = 'background: rgba(37,99,235,0.06);';
    elseif ($cell['is_holiday']) $cellBg = 'background: rgba(101,89,204,0.06);';
    elseif ($cell['is_weekend']) $cellBg = 'background: #f8fafc;';
@endphp
<td style="text-align: center; vertical-align: middle; padding: 8px 4px; {{ $cellBg }}"
    @if ($cell['record_id'] && ($canEdit ?? false))
        class="crm-attendance-cell"
        data-record-id="{{ $cell['record_id'] }}"
        data-date="{{ $cell['date_string'] }}"
        style="cursor: pointer; {{ $cellBg }}"
    @endif
    @if ($cell['record'])
        title="In: {{ $cell['clocked_in_at']?->format('H:i') ?? '—' }} | Out: {{ $cell['clocked_out_at']?->format('H:i') ?? '—' }}{{ $cell['total_minutes'] ? ' | ' . intdiv($cell['total_minutes'], 60) . 'h ' . ($cell['total_minutes'] % 60) . 'm' : '' }}"
    @endif>
    @if ($cell['code'])
        <span class="crm-pill" style="background: {{ $cell['code']->color }}20; color: {{ $cell['code']->color }}; font-size: 11px; padding: 4px 8px; font-weight: 600; cursor: {{ ($canEdit ?? false) && $cell['record_id'] ? 'pointer' : 'default' }};">
            {{ $cell['code']->code }}
            @if ($cell['status'] === 'pending_correction')
                <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #f7b84b; margin-left: 2px; vertical-align: top;"></span>
            @endif
        </span>
    @elseif ($cell['is_today'] && ! $cell['record'])
        <span style="color: #94a3b8; font-size: 13px;">?</span>
    @elseif (! $cell['is_working_day'] || $cell['is_weekend'])
        <span style="color: #cbd5e1; font-size: 12px;">--</span>
    @elseif ($cell['date_string'] > now()->toDateString())
        <span style="color: #e2e8f0;">·</span>
    @else
        <span style="color: #cbd5e1;">·</span>
    @endif
</td>
