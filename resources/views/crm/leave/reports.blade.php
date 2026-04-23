@extends('layouts.crm')

@section('title', 'Leave Reports')
@section('crm_heading', 'Leave Reports')
@section('crm_subheading', 'Organisation-wide leave usage statistics and trends.')

@section('content')
    <div class="crm-stack">
        {{-- Summary Cards --}}
        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">{{ $year }} Summary</p>
                    <h2>Leave usage by type</h2>
                </div>
            </div>

            <div class="crm-grid cols-4">
                @foreach ($leaveTypes as $type)
                    @php
                        $s = $summary->get($type->id);
                    @endphp
                    <div class="crm-stage-card" style="border-left: 3px solid {{ $type->color }};">
                        <span class="crm-pill" style="background: {{ $type->color }}20; color: {{ $type->color }};">
                            {{ $type->name }}
                        </span>
                        <strong>{{ $s ? number_format((float) $s->total_days_taken, 1) : '0.0' }}</strong>
                        <div class="crm-muted-copy">
                            {{ $s ? $s->total_requests : 0 }} request(s)
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Filters --}}
        <section class="crm-card crm-filter-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Filters</p>
                    <h2>Filter approved leave</h2>
                </div>
            </div>
            <form method="GET" action="{{ route('crm.leave.reports') }}" class="crm-filter-form">
                <div class="crm-filter-grid">
                    <div class="crm-field">
                        <label for="year">Year</label>
                        <input type="number" id="year" name="year" value="{{ $year }}" min="2020" max="2030">
                    </div>
                    <div class="crm-field">
                        <label for="department_id">Department</label>
                        <select id="department_id" name="department_id">
                            <option value="">All departments</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}" @selected(request('department_id') == $dept->id)>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="crm-field">
                        <label for="leave_type_id">Leave Type</label>
                        <select id="leave_type_id" name="leave_type_id">
                            <option value="">All types</option>
                            @foreach ($leaveTypes as $type)
                                <option value="{{ $type->id }}" @selected(request('leave_type_id') == $type->id)>{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-actions">
                    <a href="{{ route('crm.leave.reports') }}" class="btn btn-light crm-btn-light"><i class="bx bx-reset"></i> Reset</a>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-filter-alt"></i> Apply</button>
                </div>
            </form>
        </section>

        {{-- Detailed Table --}}
        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Detail</p>
                    <h2>Approved leave requests</h2>
                </div>
            </div>

            <div class="crm-table-wrap">
                <table class="crm-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Type</th>
                            <th>Dates</th>
                            <th>Days</th>
                            <th>Approved By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($approvedRequests as $req)
                            <tr>
                                <td><strong>{{ $req->user->name }}</strong></td>
                                <td>
                                    <span class="crm-pill" style="background: {{ $req->leaveType->color }}20; color: {{ $req->leaveType->color }};">
                                        {{ $req->leaveType->name }}
                                    </span>
                                </td>
                                <td>{{ $req->start_date->format('d M Y') }}@if (!$req->start_date->isSameDay($req->end_date)) &ndash; {{ $req->end_date->format('d M Y') }}@endif</td>
                                <td>{{ number_format((float) $req->total_days, 1) }}</td>
                                <td>{{ $req->approver?->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="crm-muted-copy" style="text-align: center; padding: 24px;">No approved leave requests for this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 16px;">
                {{ $approvedRequests->withQueryString()->links() }}
            </div>
        </section>
    </div>
@endsection
