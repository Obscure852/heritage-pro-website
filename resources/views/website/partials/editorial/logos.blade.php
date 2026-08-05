<section class="hp-logos hp-band" aria-label="Institutions running Heritage Pro">
    <p class="hp-logos__label">In service at</p>
    <div class="hp-logos__row">
        @foreach ($site['clients'] as $client)
            <span>{{ $client['label'] }}</span>
        @endforeach
    </div>
</section>
