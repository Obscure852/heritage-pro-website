@extends('layouts.crm')

@section('title', 'CRM Dashboard')
@section('crm_heading', 'CRM Dashboard')
@section('crm_subheading', 'Monitor sales pipeline health, open support and sales requests, recent activity, and follow-up pressure from a single Heritage-branded workspace.')

@section('crm_header_stats')
    @foreach ($metrics as $metric)
        @include('crm.partials.header-stat', [
            'value' => number_format($metric['value']),
            'label' => $metric['label'],
        ])
    @endforeach
@endsection

@section('content')
    <div class="crm-stack">
        @include('crm.partials.helper-text', [
            'title' => 'CRM Dashboard Overview',
            'content' => 'Start here to spot bottlenecks, recent movement, and workload pressure, then jump into the module that needs attention.',
        ])

        <div class="crm-grid cols-2">
            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Pipeline</p>
                        <h2>Sales stage distribution</h2>
                    </div>
                </div>

                <div class="crm-grid cols-4">
                    @foreach ($stageBreakdown as $item)
                        <div class="crm-stage-card">
                            <span class="crm-pill {{ $item['stage']->is_won ? 'success' : ($item['stage']->is_lost ? 'danger' : 'primary') }}">
                                {{ $item['stage']->name }}
                            </span>
                            <strong>{{ $item['count'] }}</strong>
                            <div class="crm-muted-copy">Open requests currently sitting in this stage.</div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Recent activity</p>
                        <h2>Latest customer-facing interactions</h2>
                    </div>
                </div>

                @if ($recentActivities->isEmpty())
                    <div class="crm-empty">No activity has been logged yet.</div>
                @else
                    <div class="crm-timeline">
                        @foreach ($recentActivities as $activity)
                            <div class="crm-timeline-item">
                                <div class="crm-timeline-time">
                                    {{ $activity->occurred_at->format('d M') }}<br>
                                    {{ $activity->occurred_at->format('H:i') }}
                                </div>
                                <div class="crm-timeline-card">
                                    <div class="crm-inline" style="justify-content: space-between; margin-bottom: 8px;">
                                        <h4>{{ $activity->subject ?: ucfirst($activity->activity_type) }}</h4>
                                        <span class="crm-pill muted">{{ config('heritage_crm.activity_types.' . $activity->activity_type) }}</span>
                                    </div>
                                    <p>{{ $activity->body }}</p>
                                    <div class="crm-inline" style="margin-top: 10px;">
                                        <span class="crm-muted-copy">
                                            {{ $activity->request->customer?->company_name ?: $activity->request->lead?->company_name ?: 'Unassigned account' }}
                                        </span>
                                        <span class="crm-muted-copy">•</span>
                                        <span class="crm-muted-copy">{{ $activity->user?->name ?: 'System' }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Open queue</p>
                    <h2>Recently updated requests</h2>
                    <p>Sales and support work currently flowing through the CRM foundation.</p>
                </div>
            </div>

            @if ($recentRequests->isEmpty())
                <div class="crm-empty">No CRM requests have been created yet.</div>
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
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentRequests as $request)
                                <tr>
                                    <td>
                                        <strong><a href="{{ route('crm.requests.show', $request) }}">{{ $request->title }}</a></strong>
                                        <span class="crm-muted">{{ ucfirst($request->type) }} request</span>
                                    </td>
                                    <td>{{ $request->customer?->company_name ?: $request->lead?->company_name ?: 'Unassigned' }}</td>
                                    <td>{{ $request->owner?->name ?: 'Not assigned' }}</td>
                                    <td>
                                        @if ($request->type === 'sales')
                                            <span class="crm-pill primary">{{ $request->salesStage?->name ?: 'No stage' }}</span>
                                        @else
                                            <span class="crm-pill muted">{{ str_replace('_', ' ', $request->support_status ?: 'open') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $request->next_action ?: 'No follow-up scheduled' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
@endsection
