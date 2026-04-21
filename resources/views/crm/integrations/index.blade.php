@extends('layouts.crm')

@section('title', 'CRM Integrations')
@section('crm_heading', 'Integrations')
@section('crm_subheading', 'Manage API, email, WhatsApp, and webhook integrations used to connect Heritage Pro with schools and external communication providers.')

@section('crm_actions')
    @if (auth()->user()->canManageCrmSettings())
        <a href="{{ route('crm.integrations.create') }}" class="btn btn-primary">
            <i class="bx bx-plus-circle"></i> New integration
        </a>
    @endif
@endsection

@section('content')
    <div class="crm-stack">
        <section class="crm-card crm-filter-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Filters</p>
                    <h2>Find integrations</h2>
                </div>
            </div>

            <form method="GET" action="{{ route('crm.integrations.index') }}" class="crm-filter-form">
                <div class="crm-filter-grid">
                    <div class="crm-field">
                        <label for="q">Search</label>
                        <input id="q" name="q" value="{{ $filters['q'] }}" placeholder="Name, school code, base URL">
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
                        <label for="kind">Kind</label>
                        <select id="kind" name="kind">
                            <option value="">All kinds</option>
                            @foreach ($integrationKinds as $value => $label)
                                <option value="{{ $value }}" @selected($filters['kind'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="crm-field">
                        <label for="integration_status_filter">Status</label>
                        <select id="integration_status_filter" name="status">
                            <option value="">All statuses</option>
                            @foreach ($integrationStatuses as $value => $label)
                                <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('crm.integrations.index') }}" class="btn btn-light crm-btn-light"><i class="bx bx-reset"></i> Reset</a>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-filter-alt"></i> Apply filters</button>
                </div>
            </form>
        </section>

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Connections</p>
                    <h2>Configured integrations</h2>
                </div>
            </div>

            @if ($integrations->isEmpty())
                <div class="crm-empty">No integration records match the current filters.</div>
            @else
                <div class="crm-list">
                    @foreach ($integrations as $integration)
                        <div class="crm-list-item">
                            <div class="crm-inline" style="justify-content: space-between;">
                                <h4><a href="{{ route('crm.integrations.show', $integration) }}">{{ $integration->name }}</a></h4>
                                <span class="crm-pill {{ $integration->status === 'active' ? 'success' : ($integration->status === 'testing' ? 'primary' : 'muted') }}">{{ $integrationStatuses[$integration->status] ?? ucfirst($integration->status) }}</span>
                            </div>
                            <p>{{ $integrationKinds[$integration->kind] ?? ucfirst($integration->kind) }} · {{ $integration->school_code ?: 'No school code set' }}</p>
                                <div class="crm-inline" style="margin-top: 10px; justify-content: space-between;">
                                    <div class="crm-inline">
                                        <span class="crm-muted-copy">{{ $integration->base_url ?: 'No base URL' }}</span>
                                        <span class="crm-muted-copy">•</span>
                                        <span class="crm-muted-copy">{{ $integration->owner?->name ?: 'Unassigned' }}</span>
                                    </div>
                                    @if (auth()->user()->canManageCrmSettings())
                                        <div class="crm-action-row">
                                            <a href="{{ route('crm.integrations.edit', $integration) }}" class="btn btn-secondary">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            @include('crm.partials.delete-button', [
                                                'action' => route('crm.integrations.destroy', $integration),
                                                'message' => 'Are you sure you want to permanently delete this integration?',
                                                'label' => 'Delete',
                                            ])
                                        </div>
                                    @endif
                                </div>
                            </div>
                    @endforeach
                </div>

                @include('crm.partials.pager', ['paginator' => $integrations])
            @endif
        </section>
    </div>
@endsection
