@extends('layouts.crm')

@section('title', 'CRM Dashboard')
@section('crm_heading', 'CRM Dashboard')
@section('crm_subheading', 'Monitor sales pipeline health, open support and sales requests, recent activity, and follow-up pressure from a single Heritage-branded workspace.')

@section('crm_header_stats')
    @foreach ($metrics as $metric)
        @include('crm.partials.header-stat', [
            'value' => number_format($metric['value']),
            'label' => $metric['label'],
        ])
    @endforeach
@endsection

@section('content')
    <div class="crm-stack">
        @include('crm.partials.helper-text', [
            'title' => 'CRM Dashboard Overview',
            'content' => 'Start here to spot bottlenecks, recent movement, and workload pressure, then jump into the module that needs attention.',
        ])

        <div style="display: grid; grid-template-columns: minmax(0, 1fr){{ $showDashboardClock ? ' 280px' : '' }}; gap: 20px; align-items: start;">
            <div class="crm-stack">
                <div class="crm-grid cols-2">
                    <section class="crm-card">
                        <div class="crm-card-title">
                            <div>
                                <p class="crm-kicker">Pipeline</p>
                                <h2>Sales stage distribution</h2>
                            </div>
                        </div>

                        <div class="crm-grid cols-4">
                            @foreach ($stageBreakdown as $item)
                                <div class="crm-stage-card">
                                    <span class="crm-pill {{ $item['stage']->is_won ? 'success' : ($item['stage']->is_lost ? 'danger' : 'primary') }}">
                                        {{ $item['stage']->name }}
                                    </span>
                                    <strong>{{ $item['count'] }}</strong>
                                    <div class="crm-muted-copy">Open requests currently sitting in this stage.</div>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <section class="crm-card">
                        <div class="crm-card-title">
                            <div>
                                <p class="crm-kicker">Recent activity</p>
                                <h2>Latest customer-facing interactions</h2>
                            </div>
                        </div>

                        @if ($recentActivities->isEmpty())
                            <div class="crm-empty">No activity has been logged yet.</div>
                        @else
                            <div class="crm-timeline">
                                @foreach ($recentActivities as $activity)
                                    <div class="crm-timeline-item">
                                        <div class="crm-timeline-time">
                                            {{ $activity->occurred_at->format('d M') }}<br>
                                            {{ $activity->occurred_at->format('H:i') }}
                                        </div>
                                        <div class="crm-timeline-card">
                                            <div class="crm-inline" style="justify-content: space-between; margin-bottom: 8px;">
                                                <h4>{{ $activity->subject ?: ucfirst($activity->activity_type) }}</h4>
                                                <span class="crm-pill muted">{{ config('heritage_crm.activity_types.' . $activity->activity_type) }}</span>
                                            </div>
                                            <p>{{ $activity->body }}</p>
                                            <div class="crm-inline" style="margin-top: 10px;">
                                                <span class="crm-muted-copy">
                                                    {{ $activity->request->customer?->company_name ?: $activity->request->lead?->company_name ?: 'Unassigned account' }}
                                                </span>
                                                <span class="crm-muted-copy">•</span>
                                                <span class="crm-muted-copy">{{ $activity->user?->name ?: 'System' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </section>
                </div>
            </div>

            {{-- Clock In/Out Widget --}}
            @if ($showDashboardClock && $clockStatus)
            <aside>
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
            </aside>
            @endif
        </div>

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Open queue</p>
                    <h2>Recently updated requests</h2>
                    <p>Sales and support work currently flowing through the CRM foundation.</p>
                </div>
            </div>

            @if ($recentRequests->isEmpty())
                <div class="crm-empty">No CRM requests have been created yet.</div>
            @else
                <div class="crm-table-wrap">
                    <table class="crm-table">
                        <thead>
                            <tr>
                                <th>Request</th>
                                <th>Account</th>
                                <th>Owner</th>
                                <th>State</th>
                                <th>Next action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentRequests as $request)
                                <tr>
                                    <td>
                                        <strong><a href="{{ route('crm.requests.show', $request) }}">{{ $request->title }}</a></strong>
                                        <span class="crm-muted">{{ ucfirst($request->type) }} request</span>
                                    </td>
                                    <td>{{ $request->customer?->company_name ?: $request->lead?->company_name ?: 'Unassigned' }}</td>
                                    <td>{{ $request->owner?->name ?: 'Not assigned' }}</td>
                                    <td>
                                        @if ($request->type === 'sales')
                                            <span class="crm-pill primary">{{ $request->salesStage?->name ?: 'No stage' }}</span>
                                        @else
                                            <span class="crm-pill muted">{{ str_replace('_', ' ', $request->support_status ?: 'open') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $request->next_action ?: 'No follow-up scheduled' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Live clock
    var clockEl = document.getElementById('crm-dashboard-clock');
    if (clockEl) {
        setInterval(function () {
            var now = new Date();
            clockEl.textContent = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
        }, 10000);
    }

    // Elapsed counter — ticks every second
    var elapsedEl = document.getElementById('crm-dashboard-elapsed');
    var isClockedIn = {{ ($clockStatus['state'] ?? '') === 'clocked_in' ? 'true' : 'false' }};
    @if (($clockStatus['state'] ?? '') === 'clocked_in' && $clockStatus['clocked_in_at'])
        var elapsedTotalSeconds = {{ (int) $clockStatus['clocked_in_at']->diffInSeconds(now()) }};
    @else
        var elapsedTotalSeconds = 0;
    @endif

    if (elapsedEl && isClockedIn) {
        setInterval(function () {
            elapsedTotalSeconds++;
            var h = Math.floor(elapsedTotalSeconds / 3600);
            var m = Math.floor((elapsedTotalSeconds % 3600) / 60);
            var s = elapsedTotalSeconds % 60;
            elapsedEl.textContent = String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
        }, 1000);
    }

    // Clock button
    var clockBtn = document.getElementById('crm-dashboard-clock-btn');
    if (clockBtn) {
        clockBtn.addEventListener('click', function () {
            clockBtn.disabled = true;
            clockBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing...';

            fetch('{{ route("crm.attendance.clock") }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                credentials: 'same-origin',
                body: JSON.stringify({})
            })
            .then(function (r) { return r.json(); })
            .then(function () { window.location.reload(); })
            .catch(function () {
                clockBtn.disabled = false;
                clockBtn.innerHTML = 'Error — try again';
            });
        });
    }
});
</script>
@endpush
