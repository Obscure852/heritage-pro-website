@extends('layouts.crm')

@section('title', 'Support Requests')
@section('crm_heading', 'Support Requests')
@section('crm_subheading', 'Track post-sale customer support work such as incidents, enhancement asks, operational follow-up, and account requests.')

@section('crm_header_stats')
    @foreach ($supportStats as $stat)
        @include('crm.partials.header-stat', [
            'value' => number_format($stat['value']),
            'label' => $stat['label'],
        ])
    @endforeach
@endsection

@section('content')
    <div class="crm-stack">
        @include('crm.partials.helper-text', [
            'title' => 'Support Requests Workspace',
            'content' => 'Use the filters below to narrow the queue by owner, customer, or support status, then open the record you need for follow-up.',
        ])

        <section class="crm-card crm-filter-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Filters</p>
                    <h2>Find support work</h2>
                </div>
            </div>

            <form method="GET" action="{{ route('crm.requests.support.index') }}" class="crm-filter-form">
                <div class="crm-filter-grid">
                    <div class="crm-field">
                        <label for="q">Search</label>
                        <input id="q" name="q" value="{{ $filters['q'] }}" placeholder="Title, notes, next action">
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
                        <label for="customer_id">Customer</label>
                        <select id="customer_id" name="customer_id">
                            <option value="">All customers</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}" @selected($filters['customer_id'] !== '' && (int) $filters['customer_id'] === $customer->id)>{{ $customer->company_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="crm-field">
                        <label for="support_request_status">Support status</label>
                        <select id="support_request_status" name="support_status">
                            <option value="">All support statuses</option>
                            @foreach ($supportStatuses as $value => $label)
                                <option value="{{ $value }}" @selected($filters['support_status'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('crm.requests.support.index') }}" class="btn btn-light crm-btn-light"><i class="bx bx-reset"></i> Reset</a>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-filter-alt"></i> Apply filters</button>
                    <a href="{{ route('crm.requests.sales.index') }}" class="btn btn-light crm-btn-light">
                        <i class="bx bx-line-chart"></i> View sales calls
                    </a>
                    <a href="{{ route('crm.requests.support.create') }}" class="btn btn-primary">
                        <i class="bx bx-support"></i> New support request
                    </a>
                </div>
            </form>
        </section>

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Support queue</p>
                    <h2>Current customer support work</h2>
                    <p>Every post-sale support request currently tracked in the CRM.</p>
                </div>
            </div>

            @if ($requests->isEmpty())
                <div class="crm-empty">No support request records match the current filters.</div>
            @else
                <div class="crm-table-wrap">
                    <table class="crm-table">
                        <thead>
                            <tr>
                                <th>Support request</th>
                                <th>Customer</th>
                                <th>Owner</th>
                                <th>Status</th>
                                <th>Next action</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($requests as $request)
                                <tr>
                                    <td>
                                        <strong><a href="{{ route('crm.requests.show', $request) }}">{{ $request->title }}</a></strong>
                                        <span class="crm-muted">{{ $request->description ?: 'No notes yet' }}</span>
                                    </td>
                                    <td>{{ $request->customer?->company_name ?: 'No customer linked' }}</td>
                                    <td>{{ $request->owner?->name ?: 'Unassigned' }}</td>
                                    <td><span class="crm-pill muted">{{ $supportStatuses[$request->support_status ?? 'open'] ?? ucfirst(str_replace('_', ' ', (string) $request->support_status)) }}</span></td>
                                    <td>{{ $request->next_action ?: 'No next action set' }}</td>
                                    <td class="crm-table-actions">
                                        <div class="crm-action-row">
                                            @include('crm.partials.view-button', [
                                                'url' => route('crm.requests.show', $request),
                                                'label' => 'View support request',
                                            ])
                                            <a href="{{ route('crm.requests.edit', $request) }}" class="btn crm-icon-action" title="Edit support request" aria-label="Edit support request">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @include('crm.partials.delete-button', [
                                                'action' => route('crm.requests.destroy', $request),
                                                'message' => 'Are you sure you want to permanently delete this support request?',
                                                'label' => 'Delete support request',
                                                'iconOnly' => true,
                                            ])
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @include('crm.partials.pager', ['paginator' => $requests])
            @endif
        </section>
    </div>
@endsection
