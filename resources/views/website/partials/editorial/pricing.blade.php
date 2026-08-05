{{--
    NOTE: config/heritage_website.php still prices Starter/Professional at
    "BWP 28 / BWP 42 per learner / month". The supplied design bills the same
    figures per learner, per YEAR. Confirm which is correct — the other public
    pages still render the config values.
--}}
@php
    $plans = [
        [
            'name' => 'Essential',
            'price' => 'BWP 28',
            'unit' => 'per learner, per year',
            'body' => 'For junior and single-campus schools establishing one record system.',
            'items' => [
                'Student records & admissions',
                'Attendance & registers',
                'Continuous assessment',
                'Parent portal & SMS',
                'Email support, 1 business day',
            ],
            'cta' => 'Request a quote',
            'featured' => false,
        ],
        [
            'name' => 'Institution',
            'badge' => 'Most chosen',
            'price' => 'BWP 42',
            'unit' => 'per learner, per year',
            'body' => 'For senior schools and groups running examinations, finance and reporting together.',
            'items' => [
                'Everything in Essential',
                'Examinations & report cards',
                'Fees, invoicing & receipts',
                'Timetabling & subject sets',
                'Ministry reporting formats',
                'Named implementation lead',
            ],
            'cta' => 'Book a 30-minute demo',
            'featured' => true,
        ],
        [
            'name' => 'Custom',
            'price' => 'By arrangement',
            'unit' => 'multi-campus and tertiary',
            'body' => 'For colleges, universities and school groups with integration requirements.',
            'items' => [
                'Everything in Institution',
                'Semesters, credits & transcripts',
                'Learning management',
                'Single sign-on & API access',
                'Data migration service',
                'Service level agreement',
            ],
            'cta' => 'Talk to us',
            'featured' => false,
        ],
    ];
@endphp
<section id="pricing" class="hp-pricing hp-band hp-band--tint">
    <div class="hp-intro">
        <p class="hp-eyebrow">VI. Fees</p>
        <h2 class="hp-h2">Fair, per-learner pricing — no surprise invoices.</h2>
        <p class="hp-lead">Billed annually per enrolled learner. Implementation, training, hosting and support are included; the price on this page is the price on your invoice.</p>
    </div>
    <div class="hp-grid-3 hp-pricing__grid">
        @foreach ($plans as $plan)
            <article @class(['hp-plan', 'hp-plan--featured' => $plan['featured']])>
                <div class="hp-plan__head">
                    <h3 class="hp-plan__name">{{ $plan['name'] }}</h3>
                    @isset($plan['badge'])
                        <span class="hp-plan__badge">{{ $plan['badge'] }}</span>
                    @endisset
                </div>
                <p class="hp-plan__price">{{ $plan['price'] }}</p>
                <p class="hp-plan__unit">{{ $plan['unit'] }}</p>
                <p class="hp-plan__body">{{ $plan['body'] }}</p>
                <div class="hp-ruled">
                    @foreach ($plan['items'] as $item)
                        <div>{{ $item }}</div>
                    @endforeach
                </div>
                <a href="#demo" @class(['hp-btn hp-btn--block', 'hp-btn--invert' => $plan['featured'], 'hp-btn--outline' => ! $plan['featured']])>{{ $plan['cta'] }}</a>
            </article>
        @endforeach
    </div>
</section>
