@php
    $stats = $teamToday['stats'];
@endphp

<section class="crm-card">
    <div class="crm-card-title">
        <div>
            <p class="crm-kicker">Team today</p>
            <h2>Who is in</h2>
            <p>Attendance recorded for {{ now()->format('d F') }}, and anyone away on approved leave.</p>
        </div>
        @if ($teamToday['url'])
            <a href="{{ $teamToday['url'] }}" class="btn btn-light crm-btn-light">
                <i class="bx bx-grid-alt"></i> Team grid
            </a>
        @endif
    </div>

    <div class="crm-grid cols-4">
        <div class="crm-team-stat">
            <span>Present</span>
            <strong>{{ number_format($stats['present'] ?? 0) }}</strong>
        </div>
        <div class="crm-team-stat{{ ($stats['late'] ?? 0) > 0 ? ' is-warning' : '' }}">
            <span>Late</span>
            <strong>{{ number_format($stats['late'] ?? 0) }}</strong>
        </div>
        <div class="crm-team-stat{{ ($stats['absent'] ?? 0) > 0 ? ' is-danger' : '' }}">
            <span>Absent</span>
            <strong>{{ number_format($stats['absent'] ?? 0) }}</strong>
        </div>
        <div class="crm-team-stat">
            <span>Records</span>
            <strong>{{ number_format($stats['total'] ?? 0) }}</strong>
        </div>
    </div>

    <div class="crm-team-leave">
        <p class="crm-kicker">Away on leave</p>

        @if ($teamToday['on_leave']->isEmpty())
            <div class="crm-empty-inline">Nobody is on approved leave today.</div>
        @else
            <div class="crm-stack-sm">
                @foreach ($teamToday['on_leave'] as $leave)
                    <div class="crm-file-row">
                        <div>
                            <strong>{{ $leave->user?->name ?: 'Unknown user' }}</strong>
                            <span class="crm-muted">{{ $leave->leaveType?->name ?: 'Leave' }}</span>
                        </div>
                        <span class="crm-pill muted">Back {{ $leave->end_date->copy()->addDay()->format('d M') }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
