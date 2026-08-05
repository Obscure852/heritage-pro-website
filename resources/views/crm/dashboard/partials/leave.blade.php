<section class="crm-card">
    <div class="crm-card-title">
        <div>
            <p class="crm-kicker">Leave</p>
            <h2>Awaiting your decision</h2>
            <p>Requests parked with you for approval, soonest start date first.</p>
        </div>
        @if (Route::has('crm.leave.approvals') && auth()->user()->canAccessCrmModule('leave', 'edit'))
            <a href="{{ route('crm.leave.approvals') }}" class="btn btn-light crm-btn-light">
                <i class="bx bx-list-check"></i> All approvals
            </a>
        @endif
    </div>

    @if ($leaveApprovals->isEmpty())
        <div class="crm-empty">No leave requests are waiting on you.</div>
    @else
        <div class="crm-stack-sm">
            @foreach ($leaveApprovals as $request)
                <div class="crm-file-row">
                    <div>
                        <strong>{{ $request->user?->name ?: 'Unknown user' }}</strong>
                        <span class="crm-muted">
                            {{ $request->leaveType?->name ?: 'Leave' }} ·
                            {{ $request->start_date->format('d M') }} – {{ $request->end_date->format('d M') }} ·
                            {{ rtrim(rtrim(number_format((float) $request->total_days, 1), '0'), '.') }} days
                        </span>
                    </div>
                    <span class="crm-pill primary">Pending</span>
                </div>
            @endforeach
        </div>
    @endif

    @if ($myLeaveBalances->isNotEmpty())
        <div class="crm-team-leave">
            <p class="crm-kicker">My balance · {{ $leaveYear }}</p>

            <div class="crm-grid cols-3">
                @foreach ($myLeaveBalances as $balance)
                    <div class="crm-team-stat">
                        <span>{{ $balance->leaveType?->name ?: 'Leave' }}</span>
                        <strong>{{ rtrim(rtrim(number_format($balance->effective_available_days, 1), '0'), '.') }}</strong>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</section>
