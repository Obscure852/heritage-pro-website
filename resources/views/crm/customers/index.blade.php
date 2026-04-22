@extends('layouts.crm')

@section('title', 'Customers Workspace - Customers')
@section('crm_heading', 'Customers Workspace')
@section('crm_subheading', 'Manage converted institutions, track onboarding state, and keep customer records tied to their originating lead history.')

@section('crm_header_stats')
    @foreach ($customerStats as $stat)
        @include('crm.partials.header-stat', [
            'value' => number_format($stat['value']),
            'label' => $stat['label'],
        ])
    @endforeach
@endsection

@section('content')
    <div class="crm-stack">
        @include('crm.partials.customer-workspace-tabs')

        @include('crm.partials.helper-text', [
            'title' => 'Customer Directory',
            'content' => 'Use the filters below to find the account you need, then open it to review onboarding, linked activity, and related commercial records.',
        ])

        <section class="crm-card crm-filter-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Filters</p>
                    <h2>Find customers</h2>
                </div>
            </div>

            <form method="GET" action="{{ route('crm.customers.index') }}" class="crm-filter-form">
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
                        <label for="customer_status_filter">Status</label>
                        <select id="customer_status_filter" name="status">
                            <option value="">All statuses</option>
                            @foreach ($customerStatuses as $value => $label)
                                <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('crm.customers.index') }}" class="btn btn-light crm-btn-light"><i class="bx bx-reset"></i> Reset</a>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-filter-alt"></i> Apply filters</button>
                    @if ($canOnboardCustomer)
                        <a href="{{ route('crm.customers.onboarding.create') }}" class="btn btn-primary">
                            <i class="bx bx-import"></i> Import customer
                        </a>
                    @endif
                </div>
            </form>
        </section>

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Customer base</p>
                    <h2>Live customer records</h2>
                </div>
            </div>

            @if ($customers->isEmpty())
                <div class="crm-empty">No customer records match the current filters.</div>
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
                            @foreach ($customers as $customer)
                                <tr>
                                    <td>
                                        <strong><a href="{{ route('crm.customers.show', $customer) }}">{{ $customer->company_name }}</a></strong>
                                        <span class="crm-muted">{{ $customer->lead?->company_name ? 'Source lead: ' . $customer->lead->company_name : 'Source lead unavailable' }}</span>
                                    </td>
                                    <td>{{ $customer->owner?->name ?: 'Unassigned' }}</td>
                                    <td><span class="crm-pill {{ $customer->status === 'active' ? 'success' : ($customer->status === 'onboarding' ? 'primary' : 'muted') }}">{{ $customerStatuses[$customer->status] ?? ucfirst($customer->status) }}</span></td>
                                    <td>{{ $customer->contacts_count }}</td>
                                    <td>{{ $customer->requests_count }}</td>
                                    <td class="crm-table-actions">
                                        <div class="crm-action-row">
                                            @include('crm.partials.view-button', [
                                                'url' => route('crm.customers.show', $customer),
                                                'label' => 'View customer',
                                            ])
                                            <a href="{{ route('crm.customers.edit', $customer) }}" class="btn crm-icon-action" title="Edit customer" aria-label="Edit customer">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @include('crm.partials.delete-button', [
                                                'action' => route('crm.customers.destroy', $customer),
                                                'message' => 'Are you sure you want to permanently delete this customer?',
                                                'label' => 'Delete customer',
                                                'iconOnly' => true,
                                            ])
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @include('crm.partials.pager', ['paginator' => $customers])
            @endif
        </section>
    </div>
@endsection
