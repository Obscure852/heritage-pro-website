@extends('layouts.crm')

@section('title', 'Leave Approvals')
@section('crm_heading', 'Leave Approvals')
@section('crm_subheading', 'Review and action pending leave requests from your team.')

@section('crm_header_stats')
    @include('crm.partials.header-stat', ['value' => $pendingRequests->total(), 'label' => 'Pending'])
@endsection

@section('content')
    <div class="crm-stack">
        @include('crm.partials.helper-text', [
            'title' => 'Pending Approvals',
            'content' => 'Leave requests awaiting your review. Click a request to view details and approve or reject.',
        ])

        {{-- Pending --}}
        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Action Required</p>
                    <h2>Pending requests</h2>
                </div>
            </div>

            @if ($pendingRequests->isEmpty())
                <p class="crm-muted-copy" style="padding: 16px 0;">No pending requests.</p>
            @else
                <div class="crm-table-wrap">
                    <table class="crm-table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Type</th>
                                <th>Dates</th>
                                <th>Days</th>
                                <th>Submitted</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pendingRequests as $req)
                                <tr>
                                    <td><strong>{{ $req->user->name }}</strong></td>
                                    <td>
                                        <span class="crm-pill" style="background: {{ $req->leaveType->color }}20; color: {{ $req->leaveType->color }};">
                                            {{ $req->leaveType->name }}
                                        </span>
                                    </td>
                                    <td>{{ $req->start_date->format('d M Y') }}@if (!$req->start_date->isSameDay($req->end_date)) &ndash; {{ $req->end_date->format('d M Y') }}@endif</td>
                                    <td>{{ number_format((float) $req->total_days, 1) }}</td>
                                    <td>{{ $req->submitted_at->format('d M Y H:i') }}</td>
                                    <td><a href="{{ route('crm.leave.show', $req) }}" class="btn btn-primary" style="padding: 4px 12px; font-size: 12px;"><i class="bx bx-check-circle"></i> Review</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="margin-top: 16px;">{{ $pendingRequests->links() }}</div>
            @endif
        </section>

        {{-- Recently Reviewed --}}
        @if ($recentlyReviewed->isNotEmpty())
            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">History</p>
                        <h2>Recently reviewed</h2>
                    </div>
                </div>
                <div class="crm-table-wrap">
                    <table class="crm-table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Type</th>
                                <th>Days</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentlyReviewed as $req)
                                <tr>
                                    <td>{{ $req->user->name }}</td>
                                    <td>{{ $req->leaveType->name }}</td>
                                    <td>{{ number_format((float) $req->total_days, 1) }}</td>
                                    <td>@include('crm.leave.partials.status-badge', ['status' => $req->status])</td>
                                    <td><a href="{{ route('crm.leave.show', $req) }}" class="crm-link">View</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    </div>
@endsection
