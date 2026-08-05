@php
    $editions = [
        [
            'kicker' => 'Edition one',
            'name' => 'Heritage Pro — Junior Schools',
            'body' => 'Class-based registers, continuous assessment and parent communication for primary teaching teams.',
            'items' => [
                'Daily registers and lateness logs',
                'Continuous assessment records',
                'Parent portal and SMS updates',
                'Termly report card templates',
            ],
            'featured' => false,
        ],
        [
            'kicker' => 'Edition two',
            'name' => 'Heritage Pro — Senior Schools',
            'body' => 'Subject sets, examinations and national reporting for secondary schools running multiple streams.',
            'items' => [
                'Subject-set timetabling',
                'Examination and grade entry',
                'Ministry-ready return formats',
                'Fees, invoicing and receipts',
            ],
            'featured' => true,
        ],
        [
            'kicker' => 'Edition three',
            'name' => 'Heritage Pro — Colleges & Institutes',
            'body' => 'Semester structures, credit accumulation and digital learning for tertiary registrars.',
            'items' => [
                'Course registration and credits',
                'GPA, transcripts, clearance',
                'Faculty workload planning',
                'Integrated learning management',
            ],
            'featured' => false,
        ],
    ];
@endphp
<section id="editions" class="hp-section hp-band">
    <div class="hp-intro">
        <p class="hp-eyebrow">I. Editions</p>
        <h2 class="hp-h2">Purpose-built for junior schools, senior schools, and tertiary institutions.</h2>
        <p class="hp-lead">Three editions on one platform, each carrying the workflows, reporting formats and permissions its stage of education actually requires.</p>
    </div>
    <div class="hp-grid-3 hp-editions__grid">
        @foreach ($editions as $edition)
            <article @class(['hp-card', 'hp-card--navy' => $edition['featured']])>
                <p @class(['hp-kicker', 'hp-kicker--onnavy' => $edition['featured']])>{{ $edition['kicker'] }}</p>
                <h3 class="hp-edition__title">{{ $edition['name'] }}</h3>
                <p class="hp-edition__body">{{ $edition['body'] }}</p>
                <div class="hp-ruled">
                    @foreach ($edition['items'] as $item)
                        <div>{{ $item }}</div>
                    @endforeach
                </div>
                <a href="{{ route('website.products') }}" @class(['hp-link', 'hp-link--onnavy' => $edition['featured']])>Explore edition</a>
            </article>
        @endforeach
    </div>
</section>
