<section class="crm-card">
    <div class="crm-card-title">
        <div>
            <p class="crm-kicker">Quotes</p>
            <h2>How this period's quotes are resolving</h2>
            <p>Every quote dated inside {{ strtolower($period->label) }}, grouped by its current status.</p>
        </div>
    </div>

    @if ($quoteConversion['total'] === 0)
        <div class="crm-empty">No quotes carry a date inside this period.</div>
    @else
        <div id="crm-quote-conversion" class="crm-chart"></div>
    @endif
</section>
