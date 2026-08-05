<section class="crm-card">
    <div class="crm-card-title">
        <div>
            <p class="crm-kicker">Movement</p>
            <h2>{{ $period->label }}</h2>
            <p>{{ $period->rangeLabel() }} — each figure compared with the equally long window immediately before it.</p>
        </div>
    </div>

    <div class="crm-grid cols-4">
        @foreach ($flowMetrics as $flowMetric)
            @include('crm.dashboard.partials.flow-metric', ['metric' => $flowMetric])
        @endforeach
    </div>
</section>
