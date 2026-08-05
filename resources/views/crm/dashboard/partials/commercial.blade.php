<section class="crm-card">
    <div class="crm-card-title">
        <div>
            <p class="crm-kicker">Commercial</p>
            <h2>Pipeline and revenue</h2>
            <p>Quote and invoice value for {{ strtolower($period->label) }}, measured against the preceding window.</p>
        </div>
    </div>

    @if ($mixedCurrencies)
        <div class="crm-empty-inline" style="margin-bottom: 16px;">
            <i class="bx bx-error-circle"></i>
            Quotes and invoices exist in more than one currency. The totals below add them together and should not be relied on until documents share a currency.
        </div>
    @endif

    <div class="crm-grid cols-4">
        @foreach ($commercialMetrics as $commercialMetric)
            @include('crm.dashboard.partials.flow-metric', [
                'metric' => $commercialMetric,
                'hideEmptyDelta' => in_array($commercialMetric['key'], ['pipeline_value', 'quotes_awaiting'], true),
            ])
        @endforeach
    </div>
</section>
