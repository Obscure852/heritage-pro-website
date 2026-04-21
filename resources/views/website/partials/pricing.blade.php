<section id="pricing" class="section pricing">
    <div class="container">
        <div class="center" style="max-width: 640px; margin: 0 auto;">
            <span class="eyebrow">Pricing</span>
            <h2 style="margin-top: 14px;">Fair, per-learner pricing — no surprise invoices.</h2>
            <p class="lead" style="margin-top: 16px;">All plans include unlimited staff accounts, unlimited parent logins, WhatsApp, SMS allowances, and on-site training.</p>
        </div>
        <div class="pricing-grid">
            @foreach ($site['pricing_cards'] as $card)
                <div @class(['price-card', 'highlight' => $card['highlight']])>
                    @if (!empty($card['ribbon']))
                        <div class="ribbon">{{ $card['ribbon'] }}</div>
                    @endif
                    <div class="price-name">{{ $card['name'] }}</div>
                    <div class="price-amount">{{ $card['amount'] }}</div>
                    <div class="price-unit">{{ $card['unit'] }}</div>
                    <p class="price-desc">{{ $card['description'] }}</p>
                    <ul>
                        @foreach ($card['items'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                    <a href="{{ $card['href'] }}" @class(['btn', 'btn-primary' => $card['highlight'], 'btn-secondary' => !$card['highlight'], 'price-cta'])>{{ $card['cta'] }}</a>
                </div>
            @endforeach
        </div>
    </div>
</section>
