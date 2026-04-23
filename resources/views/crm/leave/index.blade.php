@extends('layouts.crm')

@section('title', 'My Leave')
@section('crm_heading', 'My Leave')
@section('crm_subheading', 'View your leave balances, recent requests, and apply for new leave.')

@section('crm_header_stats')
    @php
        $totalAvailable = collect($balances)->sum(fn ($b) => $b->available_days);
        $totalPending = collect($balances)->sum(fn ($b) => (float) $b->pending_days);
    @endphp
    @include('crm.partials.header-stat', ['value' => number_format($totalAvailable, 1), 'label' => 'Days Available'])
    @include('crm.partials.header-stat', ['value' => number_format($totalPending, 1), 'label' => 'Days Pending'])
    @include('crm.partials.header-stat', ['value' => $pendingCount, 'label' => 'To Approve'])
@endsection

@section('content')
    <div class="crm-stack">
        @include('crm.partials.helper-text', [
            'title' => 'Leave Dashboard',
            'content' => 'Your leave balances for ' . $year . '. Apply for new leave, track pending requests, or view your full history.',
        ])

        {{-- Balance Cards --}}
        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">{{ $year }} Balances</p>
                    <h2>Leave entitlements</h2>
                </div>
                <a href="{{ route('crm.leave.apply') }}" class="btn btn-primary" style="margin-left: auto;">
                    <i class="bx bx-plus"></i> Apply for Leave
                </a>
            </div>

            <div class="crm-grid cols-4">
                @foreach ($balances as $balance)
                    <div class="crm-stage-card" style="border-left: 3px solid {{ $balance->leaveType->color }};">
                        <span class="crm-pill" style="background: {{ $balance->leaveType->color }}20; color: {{ $balance->leaveType->color }};">
                            {{ $balance->leaveType->name }}
                        </span>
                        <strong>{{ number_format($balance->available_days, 1) }}</strong>
                        <div class="crm-muted-copy">
                            {{ number_format((float) $balance->entitled_days + (float) $balance->carried_over_days + (float) $balance->adjustment_days, 1) }} entitled
                            &middot; {{ number_format((float) $balance->used_days, 1) }} used
                            @if ((float) $balance->pending_days > 0)
                                &middot; {{ number_format((float) $balance->pending_days, 1) }} pending
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Recent Requests --}}
        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Recent</p>
                    <h2>Leave requests</h2>
                </div>
                <a href="{{ route('crm.leave.history') }}" class="btn btn-light crm-btn-light" style="margin-left: auto;">
                    <i class="bx bx-history"></i> Full History
                </a>
            </div>

            @if ($recentRequests->isEmpty())
                <p class="crm-muted-copy" style="padding: 16px 0;">No leave requests yet.</p>
            @else
                <div class="crm-table-wrap">
                    <table class="crm-table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Dates</th>
                                <th>Days</th>
                                <th>Status</th>
                                <th>Approver</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentRequests as $req)
                                <tr>
                                    <td>
                                        <span class="crm-pill" style="background: {{ $req->leaveType->color }}20; color: {{ $req->leaveType->color }};">
                                            {{ $req->leaveType->name }}
                                        </span>
                                    </td>
                                    <td>{{ $req->start_date->format('d M Y') }}@if (!$req->start_date->isSameDay($req->end_date)) &ndash; {{ $req->end_date->format('d M Y') }}@endif</td>
                                    <td>{{ number_format((float) $req->total_days, 1) }}</td>
                                    <td>
                                        @include('crm.leave.partials.status-badge', ['status' => $req->status])
                                    </td>
                                    <td>{{ $req->currentApprover?->name ?? $req->approver?->name ?? '—' }}</td>
                                    <td><a href="{{ route('crm.leave.show', $req) }}" class="crm-link">View</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
@endsection
