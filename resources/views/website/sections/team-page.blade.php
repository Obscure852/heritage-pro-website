<div id="team-page-content">
    <section class="section heritage-team-page">
        <div class="container">
            <div class="center heritage-section-intro">
                <span class="eyebrow">Our team</span>
                <h2 class="heritage-section-intro-title">A focused team behind Heritage Pro’s delivery, development, and operations.</h2>
                <p class="lead">Heritage Pro is supported by a compact team that covers leadership, operations, backend systems, platform architecture, and frontend product experience.</p>
            </div>

            <div class="team-grid">
                @foreach ($site['team_members'] as $member)
                    <article class="team-card {{ $member['theme'] }}">
                        <div class="team-card-head">
                            <div class="team-avatar">{{ $member['short_name'] }}</div>
                            <div class="team-meta">
                                <span>{{ $member['role'] }}</span>
                                <h3>{{ $member['name'] }}</h3>
                            </div>
                        </div>
                        <p>{{ $member['bio'] }}</p>
                        <div class="team-focus">
                            @foreach ($member['focus'] as $focus)
                                <span class="team-pill">{{ $focus }}</span>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
</div>
