<section id="stories" class="section cases">
    <div class="container">
        <div style="display: flex; align-items: flex-end; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
            <div style="max-width: 580px;">
                <span class="eyebrow">Selected client sites</span>
                <h2 style="margin-top: 14px;">A few live Heritage Pro deployments.</h2>
            </div>
            <a href="{{ route('website.about') }}" class="btn btn-secondary">About Heritage Pro</a>
        </div>
        @include('website.partials.deployment-cards')
    </div>
</section>
