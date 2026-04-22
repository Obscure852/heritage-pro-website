@extends('layouts.crm')

@section('title', 'CRM Dev')
@section('crm_heading', 'Dev')
@section('crm_subheading', 'Capture new development requests and product improvements coming from schools, colleges, and internal customer feedback.')

@section('crm_header_stats')
    @foreach ($devStats as $stat)
        @include('crm.partials.header-stat', [
            'value' => number_format($stat['value']),
            'label' => $stat['label'],
        ])
    @endforeach
@endsection

@section('content')
    <div class="crm-stack">
        @include('crm.partials.helper-text', [
            'title' => 'Development Backlog',
            'content' => 'Use the filters below to narrow the backlog by owner, status, or priority, then open an item to review delivery context or next steps.',
        ])

        <section class="crm-card crm-filter-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Filters</p>
                    <h2>Find backlog items</h2>
                </div>
            </div>

            <form method="GET" action="{{ route('crm.dev.index') }}" class="crm-filter-form">
                <div class="crm-filter-grid">
                    <div class="crm-field">
                        <label for="q">Search</label>
                        <input id="q" name="q" value="{{ $filters['q'] }}" placeholder="Title, requester, module, description">
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
                        <label for="dev_status_filter">Status</label>
                        <select id="dev_status_filter" name="status">
                            <option value="">All statuses</option>
                            @foreach ($developmentStatuses as $value => $label)
                                <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="crm-field">
                        <label for="priority">Priority</label>
                        <select id="priority" name="priority">
                            <option value="">All priorities</option>
                            @foreach ($developmentPriorities as $value => $label)
                                <option value="{{ $value }}" @selected($filters['priority'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('crm.dev.index') }}" class="btn btn-light crm-btn-light"><i class="bx bx-reset"></i> Reset</a>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-filter-alt"></i> Apply filters</button>
                    <a href="{{ route('crm.dev.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus-circle"></i> New dev item
                    </a>
                </div>
            </form>
        </section>

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Backlog</p>
                    <h2>Improvement queue</h2>
                </div>
            </div>

            @if ($items->isEmpty())
                <div class="crm-empty">No development request records match the current filters.</div>
            @else
                <div class="crm-table-wrap">
                    <table class="crm-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Account</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Owner</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                                <tr>
                                    <td>
                                        <strong><a href="{{ route('crm.dev.show', $item) }}">{{ $item->title }}</a></strong>
                                        <span class="crm-muted">{{ $item->target_module ?: 'General platform improvement' }}</span>
                                    </td>
                                    <td>{{ $item->customer?->company_name ?: $item->lead?->company_name ?: 'Internal / general' }}</td>
                                    <td><span class="crm-pill {{ $item->priority === 'critical' ? 'danger' : ($item->priority === 'high' ? 'warning' : 'muted') }}">{{ $developmentPriorities[$item->priority] ?? ucfirst($item->priority) }}</span></td>
                                    <td><span class="crm-pill primary">{{ $developmentStatuses[$item->status] ?? ucfirst($item->status) }}</span></td>
                                    <td>{{ $item->owner?->name ?: 'Unassigned' }}</td>
                                    <td class="crm-table-actions">
                                        <div class="crm-action-row">
                                            @include('crm.partials.view-button', [
                                                'url' => route('crm.dev.show', $item),
                                                'label' => 'View development request',
                                            ])
                                            <a href="{{ route('crm.dev.edit', $item) }}" class="btn crm-icon-action" title="Edit development request" aria-label="Edit development request">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @include('crm.partials.delete-button', [
                                                'action' => route('crm.dev.destroy', $item),
                                                'message' => 'Are you sure you want to permanently delete this development request?',
                                                'label' => 'Delete development request',
                                                'iconOnly' => true,
                                            ])
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @include('crm.partials.pager', ['paginator' => $items])
            @endif
        </section>
    </div>
@endsection
