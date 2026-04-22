@extends('layouts.crm')

@section('title', 'Sales Calls')
@section('crm_heading', 'Sales Calls')
@section('crm_subheading', 'Track lead-stage sales work such as cold calls, demos, proposals, procurement follow-up, and purchase conversations.')

@section('crm_header_stats')
    @foreach ($salesStats as $stat)
        @include('crm.partials.header-stat', [
            'value' => number_format($stat['value']),
            'label' => $stat['label'],
        ])
    @endforeach
@endsection

@section('content')
    <div class="crm-stack">
        @include('crm.partials.helper-text', [
            'title' => 'Sales Calls Workspace',
            'content' => 'Use the filters below to narrow the queue by owner, lead, stage, or outcome, then open a record to review notes and next actions.',
        ])

        <section class="crm-card crm-filter-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Filters</p>
                    <h2>Find sales work</h2>
                </div>
            </div>

            <form method="GET" action="{{ route('crm.requests.sales.index') }}" class="crm-filter-form crm-filter-form-sales">
                <div class="crm-filter-grid crm-filter-grid-sales">
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
                        <label for="lead_id">Lead</label>
                        <select id="lead_id" name="lead_id">
                            <option value="">All leads</option>
                            @foreach ($leads as $lead)
                                <option value="{{ $lead->id }}" @selected($filters['lead_id'] !== '' && (int) $filters['lead_id'] === $lead->id)>{{ $lead->company_name }}</option>
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
                    <a href="{{ route('crm.requests.sales.index') }}" class="btn btn-light crm-btn-light"><i class="bx bx-reset"></i> Reset</a>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-filter-alt"></i> Apply filters</button>
                    <a href="{{ route('crm.requests.support.index') }}" class="btn btn-light crm-btn-light">
                        <i class="bx bx-support"></i> View support requests
                    </a>
                    <a href="{{ route('crm.requests.sales.create') }}" class="btn btn-primary">
                        <i class="bx bx-line-chart"></i> New sales request
                    </a>
                </div>
            </form>
        </section>

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Sales queue</p>
                    <h2>Current sales calls and pipeline work</h2>
                    <p>Every lead-stage sales action currently tracked in the CRM.</p>
                </div>
            </div>

            @if ($requests->isEmpty())
                <div class="crm-empty">No sales request records match the current filters.</div>
            @else
                <div class="crm-table-wrap">
                    <table class="crm-table">
                        <thead>
                            <tr>
                                <th>Sales item</th>
                                <th>Lead</th>
                                <th>Owner</th>
                                <th>Stage</th>
                                <th>Outcome</th>
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
                                    <td>{{ $request->lead?->company_name ?: 'No lead linked' }}</td>
                                    <td>{{ $request->owner?->name ?: 'Unassigned' }}</td>
                                    <td><span class="crm-pill primary">{{ $request->salesStage?->name ?: 'No stage' }}</span></td>
                                    <td><span class="crm-pill {{ $request->outcome === 'won' ? 'success' : ($request->outcome === 'lost' ? 'danger' : 'muted') }}">{{ $requestOutcomes[$request->outcome ?? 'pending'] ?? ucfirst((string) $request->outcome) }}</span></td>
                                    <td>{{ $request->next_action ?: 'No next action set' }}</td>
                                    <td class="crm-table-actions">
                                        <div class="crm-action-row">
                                            @include('crm.partials.view-button', [
                                                'url' => route('crm.requests.show', $request),
                                                'label' => 'View sales request',
                                            ])
                                            <a href="{{ route('crm.requests.edit', $request) }}" class="btn crm-icon-action" title="Edit sales request" aria-label="Edit sales request">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @include('crm.partials.delete-button', [
                                                'action' => route('crm.requests.destroy', $request),
                                                'message' => 'Are you sure you want to permanently delete this sales request?',
                                                'label' => 'Delete sales request',
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
