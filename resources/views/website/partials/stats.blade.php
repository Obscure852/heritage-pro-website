<section class="stats">
    <div class="container">
        <div class="stats-grid">
            @foreach ($site['stats'] as $stat)
                <div>
                    <div class="stats-num">{{ $stat['value'] }}</div>
                    <div class="stats-label">{{ $stat['label'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>
