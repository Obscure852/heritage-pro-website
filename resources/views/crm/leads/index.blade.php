@extends('layouts.crm')

@section('title', 'Customers Workspace - Leads')
@section('crm_heading', 'Customers Workspace')
@section('crm_subheading', 'Capture new institutional leads, assign owners, and move viable opportunities toward conversion without leaving the CRM foundation.')

@section('crm_actions')
    <a href="{{ route('crm.leads.create') }}" class="btn btn-primary">
        <i class="bx bx-plus-circle"></i> New lead
    </a>
@endsection

@section('content')
    <div class="crm-stack">
        @include('crm.partials.customer-workspace-tabs')

        <section class="crm-card crm-filter-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Filters</p>
                    <h2>Find leads</h2>
                </div>
            </div>

            <form method="GET" action="{{ route('crm.leads.index') }}" class="crm-filter-form">
                <div class="crm-filter-grid">
                    <div class="crm-field">
                        <label for="q">Search</label>
                        <input id="q" name="q" value="{{ $filters['q'] }}" placeholder="Institution, sector, email, phone">
                    </div>
                    <div class="crm-field">
                        <label for="owner_id">Owner</label>
                        <select id="owner_id" name="owner_id">
                            <option value="">All owners</option>
                            @foreach ($owners as $owner)
                                <option value="{{ $owner->id }}" @selected($filters['owner_id'] !== '' && (int) $filters['owner_id'] === $owner->id)>{{ $owner->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="crm-field">
                        <label for="lead_status_filter">Status</label>
                        <select id="lead_status_filter" name="status">
                            <option value="">All statuses</option>
                            @foreach ($leadStatuses as $value => $label)
                                <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('crm.leads.index') }}" class="btn btn-light crm-btn-light"><i class="bx bx-reset"></i> Reset</a>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-filter-alt"></i> Apply filters</button>
                </div>
            </form>
        </section>

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Lead pipeline</p>
                    <h2>Current lead list</h2>
                    <p>All unconverted sales opportunities currently tracked in the CRM.</p>
                </div>
            </div>

            @if ($leads->isEmpty())
                <div class="crm-empty">No lead records match the current filters.</div>
            @else
                <div class="crm-table-wrap">
                    <table class="crm-table">
                        <thead>
                            <tr>
                                <th>Institution</th>
                                <th>Owner</th>
                                <th>Status</th>
                                <th>Contacts</th>
                                <th>Requests</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($leads as $lead)
                                <tr>
                                    <td>
                                        <strong><a href="{{ route('crm.leads.show', $lead) }}">{{ $lead->company_name }}</a></strong>
                                        <span class="crm-muted">{{ $lead->country ?: 'No country set' }}</span>
                                    </td>
                                    <td>{{ $lead->owner?->name ?: 'Unassigned' }}</td>
                                    <td>
                                        <span class="crm-pill {{ $lead->status === 'qualified' ? 'success' : ($lead->status === 'lost' ? 'danger' : ($lead->status === 'converted' ? 'muted' : 'primary')) }}">
                                            {{ $leadStatuses[$lead->status] ?? ucfirst($lead->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $lead->contacts_count }}</td>
                                    <td>{{ $lead->requests_count }}</td>
                                    <td class="crm-table-actions">
                                        <div class="crm-action-row">
                                            <a href="{{ route('crm.leads.edit', $lead) }}" class="btn btn-secondary">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            @include('crm.partials.delete-button', [
                                                'action' => route('crm.leads.destroy', $lead),
                                                'message' => 'Are you sure you want to permanently delete this lead?',
                                                'label' => 'Delete',
                                            ])
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @include('crm.partials.pager', ['paginator' => $leads])
            @endif
        </section>
    </div>
@endsection
