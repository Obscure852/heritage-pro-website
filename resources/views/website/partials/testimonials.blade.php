<section class="section testimonials">
    <div class="container">
        <div class="center" style="max-width: 680px; margin: 0 auto;">
            <span class="eyebrow">Live deployments</span>
            <h2 style="margin-top: 14px;">Selected institutions already running Heritage Pro.</h2>
        </div>
        <div class="testimonial-grid">
            @foreach ($site['deployment_highlights'] as $index => $highlight)
                <div @class(['testimonial', 'featured' => $index === 1])>
                    <div class="stars">LIVE SITE</div>
                    <p>{{ $highlight['body'] }}</p>
                    <div class="author">
                        <div class="avatar">{{ $highlight['avatar'] }}</div>
                        <div><b>{{ $highlight['title'] }}</b><span>{{ $highlight['subtitle'] }}</span></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
