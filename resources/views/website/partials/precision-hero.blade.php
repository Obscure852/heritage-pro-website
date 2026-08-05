<section class="precision-hero" aria-labelledby="precision-hero-title">
    <div class="precision-hero-grid" aria-hidden="true"></div>
    <div class="precision-container precision-hero-layout">
        <div class="precision-hero-copy">
            <div class="precision-signal">
                <span aria-hidden="true"></span>
                Built in Botswana. Ready for every institution.
            </div>
            <h1 id="precision-hero-title">{{ $hero['title'] }}</h1>
            <p>{{ $hero['lead'] }}</p>

            <div class="precision-hero-actions">
                <a href="#contact" class="precision-button precision-button-primary">
                    Book a demo
                    @include('website.partials.icon', ['name' => 'arrow', 'size' => 16])
                </a>
                <a href="#products" class="precision-button precision-button-secondary">
                    See the product
                    @include('website.partials.icon', ['name' => 'play', 'size' => 14])
                </a>
            </div>

            <dl class="precision-proof" aria-label="Heritage Pro platform reach">
                @foreach ($site['hero_trust'] as $trust)
                    <div>
                        <dt>{{ $trust['value'] }}</dt>
                        <dd>{{ $trust['label'] }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

        <div class="precision-product-stage" aria-label="Heritage Pro product preview">
            <div class="precision-stage-status">
                <span><i aria-hidden="true"></i> All systems operational</span>
                <span>Term 2 · 2026</span>
            </div>

            <div class="precision-dashboard-frame browser-light-surface">
                @include('website.partials.window-chrome', [
                    'url' => 'app.heritagepro.net/dashboard',
                    'extraClass' => 'precision-window-chrome',
                ])
                @include('website.partials.hero-mini-dash')
            </div>

            <figure class="precision-photo-anchor">
                <img src="{{ asset('images/website/students-laptop.webp') }}"
                     alt="Two students using a laptop together in a classroom"
                     width="1200"
                     height="801"
                     fetchpriority="high">
                <figcaption>
                    <span>Built for learning wherever the school day happens.</span>
                    <small>Representative student photography</small>
                </figcaption>
            </figure>
        </div>
    </div>
</section>
