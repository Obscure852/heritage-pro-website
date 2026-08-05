@php
    $hasRevenue = collect($revenueTrend['series'])->flatMap->data->sum() > 0;
@endphp

<section class="crm-card">
    <div class="crm-card-title">
        <div>
            <p class="crm-kicker">Revenue</p>
            <h2>Invoice value by month</h2>
            <p>The trailing twelve months, separating issued invoices from drafts.</p>
        </div>
    </div>

    <div id="crm-revenue-trend" class="crm-chart" data-empty="{{ $hasRevenue ? 'false' : 'true' }}"></div>

    @unless ($hasRevenue)
        <div class="crm-empty">No invoices have been raised in the last twelve months.</div>
    @endunless
</section>
