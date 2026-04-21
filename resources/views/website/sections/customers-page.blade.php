<div id="customers-page-content">
    <section class="section heritage-customers-page">
        <div class="container">
            <div class="center heritage-section-intro">
                <span class="eyebrow">Selected live institutions</span>
                <h2 class="heritage-section-intro-title">Selected live institutions running Heritage Pro.</h2>
                <p class="lead">A focused view of live deployments already using Heritage Pro for daily operations, academic administration, assessments, learning, and reporting.</p>
            </div>

            @include('website.partials.customer-marquee')

            @include('website.partials.deployment-cards', ['extraClass' => 'customer-showcase-grid'])
        </div>
    </section>
</div>
