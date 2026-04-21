<section id="products" class="section products">
    <div class="container">
        <div class="center" style="max-width: 720px; margin: 0 auto 12px;">
            <span class="eyebrow">One platform, three focused deployments</span>
            <h2 style="margin-top: 14px;">Purpose-built for junior schools, senior schools, and tertiary institutions.</h2>
            <p class="lead" style="margin-top: 16px;">Heritage Pro provides tailored deployments for school administration, senior school academic management, and college or Institute of Health Sciences operations while keeping one secure platform underneath.</p>
        </div>
        <div class="products-grid">
            @foreach ($site['products'] as $product)
                <div class="product-card {{ $product['theme'] }}">
                    <span class="product-badge">{{ $product['badge'] }}</span>
                    <div class="tile">@include('website.partials.icon', ['name' => $product['icon'], 'size' => 28])</div>
                    <h3>{{ $product['title'] }}</h3>
                    <p>{{ $product['description'] }}</p>
                    <ul>
                        @foreach ($product['items'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                    <a href="{{ route($product['route']) }}" class="btn btn-secondary">{{ $product['cta'] }}</a>
                </div>
            @endforeach
        </div>
    </div>
</section>
