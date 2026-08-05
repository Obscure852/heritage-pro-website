@php
    $isClockedIn = $clockStatus['state'] === 'clocked_in';
    $elapsed = $clockStatus['elapsed_minutes'] ?? 0;
    $hours = intdiv($elapsed, 60);
    $mins = $elapsed % 60;
@endphp
<section class="crm-card" style="border-top: 3px solid {{ $isClockedIn ? '#0ab39c' : '#e5e7eb' }};">
    <div style="text-align: center; padding: 8px 0;">
        <div style="margin-bottom: 16px;">
            <i class="bx {{ $isClockedIn ? 'bx-log-out-circle' : 'bx-log-in-circle' }}" style="font-size: 36px; color: {{ $isClockedIn ? '#0ab39c' : '#94a3b8' }};"></i>
        </div>

        <p class="crm-kicker" style="margin-bottom: 6px;">{{ now()->format('l') }}</p>
        <div style="font-size: 28px; font-weight: 700; color: #0f172a; letter-spacing: -0.5px;" id="crm-dashboard-clock">
            {{ now()->format('H:i') }}
        </div>
        <div style="font-size: 12px; color: #64748b; margin-top: 2px;">{{ now()->format('d F Y') }}</div>

        @if ($isClockedIn)
            @php
                $elapsedSeconds = (int) $clockStatus['clocked_in_at']->diffInSeconds(now());
                $eH = intdiv($elapsedSeconds, 3600);
                $eM = intdiv($elapsedSeconds % 3600, 60);
                $eS = $elapsedSeconds % 60;
            @endphp
            <div style="margin-top: 18px; padding: 12px; background: rgba(10, 179, 156, 0.06); border-radius: 3px;">
                <div style="font-size: 11px; color: #0ab39c; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Clocked In</div>
                <div style="font-size: 26px; font-weight: 700; color: #0ab39c; margin: 6px 0; font-variant-numeric: tabular-nums;" id="crm-dashboard-elapsed">
                    {{ str_pad($eH, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($eM, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($eS, 2, '0', STR_PAD_LEFT) }}
                </div>
                <div style="font-size: 12px; color: #64748b;">Since {{ $clockStatus['clocked_in_at']->format('H:i') }}</div>
            </div>
        @elseif ($clockStatus['record'] && $clockStatus['record']->clocked_out_at)
            <div style="margin-top: 18px; padding: 12px; background: #f8fafc; border-radius: 3px;">
                <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Completed</div>
                <div style="font-size: 14px; color: #334155; margin: 6px 0;">
                    {{ $clockStatus['record']->clocked_in_at->format('H:i') }} — {{ $clockStatus['record']->clocked_out_at->format('H:i') }}
                </div>
                <div style="font-size: 12px; color: #64748b;">Total: {{ intdiv($clockStatus['record']->total_minutes, 60) }}h {{ $clockStatus['record']->total_minutes % 60 }}m</div>
            </div>
        @else
            <div style="margin-top: 18px; padding: 12px; background: #f8fafc; border-radius: 3px;">
                <div style="font-size: 11px; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Not Clocked In</div>
                <div style="font-size: 12px; color: #64748b; margin-top: 6px;">Tap below to start your day</div>
            </div>
        @endif

        <button type="button" id="crm-dashboard-clock-btn"
                class="btn {{ $isClockedIn ? 'btn-light crm-btn-light' : 'btn-primary' }}"
                style="width: 100%; margin-top: 16px; padding: 12px; font-size: 14px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 6px;">
            <i class="bx {{ $isClockedIn ? 'bx-log-out-circle' : 'bx-log-in-circle' }}" style="font-size: 18px;"></i>
            <span>{{ $isClockedIn ? 'Clock Out' : 'Clock In' }}</span>
        </button>
    </div>
</section>
