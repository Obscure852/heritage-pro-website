@php
    $attendanceWidgetSettings = \App\Models\CrmAttendanceSetting::resolve();
    if (! $attendanceWidgetSettings->show_topbar_clock) {
        return;
    }

    $clockService = app(\App\Services\Crm\AttendanceClockService::class);
    $clockStatus = $clockService->currentStatus(auth()->user());
    $isClockedIn = $clockStatus['state'] === 'clocked_in';
    $elapsed = $clockStatus['elapsed_minutes'] ?? 0;
    $hours = intdiv($elapsed, 60);
    $mins = $elapsed % 60;
    $timerLabel = ($hours > 0 ? $hours . 'h ' : '') . $mins . 'm';
@endphp
<div class="crm-clock-widget me-2" id="crm-clock-widget">
    <button type="button"
            class="btn header-item crm-clock-btn {{ $isClockedIn ? 'is-in' : 'is-out' }}"
            id="crm-clock-toggle"
            title="{{ $isClockedIn ? 'Clock Out — since ' . $clockStatus['clocked_in_at']->format('H:i') : 'Clock In' }}">
        <i class="bx {{ $isClockedIn ? 'bx-log-out-circle' : 'bx-log-in-circle' }}"></i>
        <span class="crm-clock-label">{{ $isClockedIn ? 'Clock Out' : 'Clock In' }}</span>
        @if ($isClockedIn)
            <span class="crm-clock-timer" id="crm-clock-timer">{{ $timerLabel }}</span>
        @endif
    </button>
</div>
