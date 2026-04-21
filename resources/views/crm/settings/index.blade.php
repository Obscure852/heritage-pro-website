@extends('layouts.crm')

@section('title', 'CRM Settings')
@section('crm_heading', 'Settings')
@section('crm_subheading', 'Manage CRM-wide sales pipeline stages and the operational configuration that governs the foundation modules.')

@section('crm_actions')
    @if ($activeSection === 'sales-stages')
        <a href="{{ route('crm.settings.sales-stages.create') }}" class="btn btn-primary">
            <i class="bx bx-plus-circle"></i> New sales stage
        </a>
    @endif
@endsection

@section('content')
    <div class="crm-stack">
        <div class="crm-tabs">
            <a href="{{ route('crm.settings.index') }}" @class(['crm-tab', 'is-active' => $activeSection === 'overview'])>Overview</a>
            <a href="{{ route('crm.settings.sales-stages') }}" @class(['crm-tab', 'is-active' => $activeSection === 'sales-stages'])>Sales stages</a>
        </div>

        <div class="crm-grid cols-3">
            <div class="crm-metric">
                <span>Total stages</span>
                <strong>{{ $stages->count() }}</strong>
            </div>
            <div class="crm-metric">
                <span>Active stages</span>
                <strong>{{ $stages->where('is_active', true)->count() }}</strong>
            </div>
            <div class="crm-metric">
                <span>Requests mapped</span>
                <strong>{{ $stages->sum('requests_count') }}</strong>
            </div>
        </div>

        <section class="crm-card crm-filter-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Filters</p>
                    <h2>Find sales stages</h2>
                </div>
            </div>

            <form method="GET" action="{{ route('crm.settings.sales-stages') }}" class="crm-filter-form">
                <div class="crm-filter-grid">
                    <div class="crm-field">
                        <label for="q">Search</label>
                        <input id="q" name="q" value="{{ $filters['q'] }}" placeholder="Stage name">
                    </div>
                    <div class="crm-field">
                        <label for="active">Active</label>
                        <select id="active" name="active">
                            <option value="">All stages</option>
                            <option value="1" @selected($filters['active'] === '1')>Active</option>
                            <option value="0" @selected($filters['active'] === '0')>Inactive</option>
                        </select>
                    </div>
                    <div class="crm-field">
                        <label for="terminal">Terminal</label>
                        <select id="terminal" name="terminal">
                            <option value="">All stages</option>
                            <option value="won" @selected($filters['terminal'] === 'won')>Won stages</option>
                            <option value="lost" @selected($filters['terminal'] === 'lost')>Lost stages</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('crm.settings.sales-stages') }}" class="btn btn-light crm-btn-light"><i class="bx bx-reset"></i> Reset</a>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-filter-alt"></i> Apply filters</button>
                </div>
            </form>
        </section>

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Sales stage management</p>
                    <h2>Current pipeline</h2>
                </div>
            </div>

            @if ($stages->isEmpty())
                <div class="crm-empty">No sales stage records match the current filters.</div>
            @else
                <div class="crm-table-wrap">
                    <table class="crm-table">
                        <thead>
                            <tr>
                                <th>Stage</th>
                                <th>Position</th>
                                <th>Flags</th>
                                <th>Requests</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($stages as $stage)
                                <tr>
                                    <td>{{ $stage->name }}</td>
                                    <td>{{ $stage->position }}</td>
                                    <td>
                                        <div class="crm-inline">
                                            <span class="crm-pill {{ $stage->is_active ? 'success' : 'muted' }}">{{ $stage->is_active ? 'Active' : 'Inactive' }}</span>
                                            @if ($stage->is_won)
                                                <span class="crm-pill primary">Won</span>
                                            @endif
                                            @if ($stage->is_lost)
                                                <span class="crm-pill danger">Lost</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ $stage->requests_count }}</td>
                                    <td class="crm-table-actions">
                                        <div class="crm-action-row">
                                            <a href="{{ route('crm.settings.sales-stages.edit', $stage) }}" class="btn btn-secondary">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            @include('crm.partials.delete-button', [
                                                'action' => route('crm.settings.sales-stages.destroy', $stage),
                                                'message' => 'Are you sure you want to permanently delete this sales stage?',
                                                'label' => 'Delete',
                                            ])
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
@endsection
