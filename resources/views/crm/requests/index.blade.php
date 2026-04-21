@extends('layouts.crm')

@section('title', 'CRM Requests')
@section('crm_heading', 'Requests')
@section('crm_subheading', 'Track both the sales journey from cold call to purchase and post-sale support work in one shared request module.')

@section('crm_actions')
    <a href="{{ route('crm.requests.create') }}" class="btn btn-primary">
        <i class="bx bx-plus-circle"></i> New request
    </a>
@endsection

@section('content')
    <div class="crm-stack">
        <section class="crm-card crm-filter-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Filters</p>
                    <h2>Find requests</h2>
                </div>
            </div>

            <form method="GET" action="{{ route('crm.requests.index') }}" class="crm-filter-form">
                <div class="crm-filter-grid">
                    <div class="crm-field">
                        <label for="q">Search</label>
                        <input id="q" name="q" value="{{ $filters['q'] }}" placeholder="Title, description, next action">
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
                        <label for="type">Type</label>
                        <select id="type" name="type">
                            <option value="">All types</option>
                            @foreach ($requestTypes as $value => $label)
                                <option value="{{ $value }}" @selected($filters['type'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="crm-field">
                        <label for="sales_stage_id">Sales stage</label>
                        <select id="sales_stage_id" name="sales_stage_id">
                            <option value="">All sales stages</option>
                            @foreach ($salesStages as $stage)
                                <option value="{{ $stage->id }}" @selected($filters['sales_stage_id'] !== '' && (int) $filters['sales_stage_id'] === $stage->id)>{{ $stage->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="crm-field">
                        <label for="support_status">Support status</label>
                        <select id="support_status" name="support_status">
                            <option value="">All support statuses</option>
                            @foreach ($supportStatuses as $value => $label)
                                <option value="{{ $value }}" @selected($filters['support_status'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="crm-field">
                        <label for="outcome">Outcome</label>
                        <select id="outcome" name="outcome">
                            <option value="">All outcomes</option>
                            @foreach ($requestOutcomes as $value => $label)
                                <option value="{{ $value }}" @selected($filters['outcome'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('crm.requests.index') }}" class="btn btn-light crm-btn-light"><i class="bx bx-reset"></i> Reset</a>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-filter-alt"></i> Apply filters</button>
                </div>
            </form>
        </section>

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Request queue</p>
                    <h2>Active sales and support work</h2>
                </div>
            </div>

            @if ($requests->isEmpty())
                <div class="crm-empty">No request records match the current filters.</div>
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
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($requests as $request)
                                <tr>
                                    <td>
                                        <strong><a href="{{ route('crm.requests.show', $request) }}">{{ $request->title }}</a></strong>
                                        <span class="crm-muted">{{ $requestTypes[$request->type] ?? ucfirst($request->type) }}</span>
                                    </td>
                                    <td>{{ $request->customer?->company_name ?: $request->lead?->company_name ?: 'Unassigned' }}</td>
                                    <td>{{ $request->owner?->name ?: 'Unassigned' }}</td>
                                    <td>
                                        @if ($request->type === 'sales')
                                            <span class="crm-pill primary">{{ $request->salesStage?->name ?: 'No stage' }}</span>
                                        @else
                                            <span class="crm-pill muted">{{ $supportStatuses[$request->support_status] ?? ucfirst(str_replace('_', ' ', $request->support_status ?: 'open')) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $request->next_action ?: 'No next action set' }}</td>
                                    <td class="crm-table-actions">
                                        <div class="crm-action-row">
                                            <a href="{{ route('crm.requests.edit', $request) }}" class="btn btn-secondary">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            @include('crm.partials.delete-button', [
                                                'action' => route('crm.requests.destroy', $request),
                                                'message' => 'Are you sure you want to permanently delete this request?',
                                                'label' => 'Delete',
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
