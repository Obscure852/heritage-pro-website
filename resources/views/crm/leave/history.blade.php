@extends('layouts.crm')

@section('title', 'Leave History')
@section('crm_heading', 'Leave History')
@section('crm_subheading', 'Complete history of all your leave requests.')

@section('content')
    <div class="crm-stack">
        <section class="crm-card crm-filter-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Filters</p>
                    <h2>Filter requests</h2>
                </div>
            </div>
            <form method="GET" action="{{ route('crm.leave.history') }}" class="crm-filter-form">
                <div class="crm-filter-grid">
                    <div class="crm-field">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="">All statuses</option>
                            @foreach (['draft', 'pending', 'approved', 'rejected', 'cancelled'] as $s)
                                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="crm-field">
                        <label for="type">Leave Type</label>
                        <select id="type" name="type">
                            <option value="">All types</option>
                            @foreach ($leaveTypes as $type)
                                <option value="{{ $type->id }}" @selected(request('type') == $type->id)>{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-actions">
                    <a href="{{ route('crm.leave.history') }}" class="btn btn-light crm-btn-light"><i class="bx bx-reset"></i> Reset</a>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-filter-alt"></i> Apply</button>
                </div>
            </form>
        </section>

        <section class="crm-card">
            <div class="crm-table-wrap">
                <table class="crm-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Dates</th>
                            <th>Days</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($requests as $req)
                            <tr>
                                <td>
                                    <span class="crm-pill" style="background: {{ $req->leaveType->color }}20; color: {{ $req->leaveType->color }};">
                                        {{ $req->leaveType->name }}
                                    </span>
                                </td>
                                <td>{{ $req->start_date->format('d M Y') }}@if (!$req->start_date->isSameDay($req->end_date)) &ndash; {{ $req->end_date->format('d M Y') }}@endif</td>
                                <td>{{ number_format((float) $req->total_days, 1) }}</td>
                                <td>@include('crm.leave.partials.status-badge', ['status' => $req->status])</td>
                                <td>{{ $req->submitted_at?->format('d M Y') ?? '—' }}</td>
                                <td><a href="{{ route('crm.leave.show', $req) }}" class="crm-link">View</a></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="crm-muted-copy" style="text-align: center; padding: 24px;">No leave requests found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 16px;">
                {{ $requests->withQueryString()->links() }}
            </div>
        </section>
    </div>
@endsection
