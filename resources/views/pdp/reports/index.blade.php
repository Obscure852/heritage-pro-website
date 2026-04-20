@extends('layouts.master')

@section('title', 'PDP Reports')
@section('page_title', 'PDP Reports')
@section('css')
    @include('pdp.partials.theme-css')
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Staff PDP
        @endslot
        @slot('li_1_url')
            {{ route('staff.pdp.plans.index') }}
        @endslot
        @slot('title')
            PDP Reports
        @endslot
    @endcomponent

    @php
        $metricCount = count($metrics);
        $metricColumnClass = match (true) {
            $metricCount <= 1 => 'col-12',
            $metricCount === 2 => 'col-6',
            $metricCount === 3 => 'col-4',
            default => 'col-6 col-md-3',
        };
    @endphp

    <div class="pdp-theme reports-page">
        <div class="page-shell mb-4">
            <div class="page-shell-header">
                <div class="row align-items-center g-4">
                    <div class="col-lg-6">
                        <div class="page-shell-title">PDP Reports</div>
                        <div class="page-subtitle">
                            Review overall plan volume, workflow backlog, signature queue, and template adoption using the same template-driven data powering the PDP module.
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="row text-center">
                            @foreach ($metrics as $metric)
                                <div class="{{ $metricColumnClass }}">
                                    <div class="stat-item">
                                        <h4 class="mb-0 fw-bold text-white">{{ $metric['value'] }}</h4>
                                        <small class="opacity-75">{{ $metric['label'] }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="section-panel h-100">
                    <div class="section-panel-header">
                        <div class="section-panel-title">Plans by Status</div>
                    </div>
                    <div class="section-panel-body">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Status</th>
                                        <th class="text-end">Count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($plansByStatus as $row)
                                        <tr>
                                            <td>{{ ucfirst($row['status']) }}</td>
                                            <td class="text-end">{{ $row['count'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="section-panel h-100">
                    <div class="section-panel-header">
                        <div class="section-panel-title">Plans by Template</div>
                    </div>
                    <div class="section-panel-body">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Template</th>
                                        <th class="text-end">Plans</th>
                                        <th class="text-end">Completed</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($plansByTemplate as $row)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $row['template']->name }}</div>
                                                <div class="text-muted small">{{ $row['template']->code }} | v{{ $row['template']->version }}</div>
                                            </td>
                                            <td class="text-end">{{ $row['count'] }}</td>
                                            <td class="text-end">{{ $row['completed_count'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="section-panel">
                    <div class="section-panel-header">
                        <div class="section-panel-title">Open or Pending Reviews</div>
                    </div>
                    <div class="section-panel-body">
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Template</th>
                                        <th>Period</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($reviewBacklog as $row)
                                        <tr>
                                            <td>{{ $row['plan']->user->full_name }}</td>
                                            <td>{{ $row['plan']->template->code }}</td>
                                            <td>{{ \Illuminate\Support\Str::headline(str_replace('_', ' ', $row['review']->period_key)) }}</td>
                                            <td>{{ ucfirst($row['review']->status) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">No review backlog.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="section-panel">
                    <div class="section-panel-header">
                        <div class="section-panel-title">Pending Signatures</div>
                    </div>
                    <div class="section-panel-body">
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Step</th>
                                        <th>Scope</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($signatureBacklog as $row)
                                        <tr>
                                            <td>{{ $row['plan']->user->full_name }}</td>
                                            <td>{{ $row['signature']->approval_step_key }}</td>
                                            <td>{{ $row['signature']->review?->period_key ?: 'plan_level' }}</td>
                                            <td>{{ ucfirst($row['signature']->status) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">No pending signatures.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
