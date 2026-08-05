<div class="crm-grid cols-4">
    @foreach ($pressureMetrics as $pressureMetric)
        @include('crm.dashboard.partials.pressure-metric', [
            'metric' => $pressureMetric,
            'alert' => $pressureMetric['key'] === 'overdue_follow_ups',
        ])
    @endforeach
</div>
