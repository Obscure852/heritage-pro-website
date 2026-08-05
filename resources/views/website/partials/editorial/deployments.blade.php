@php
    $cases = [
        [
            'context' => 'Junior school · Gaborone',
            'title' => 'Daily attendance running in nine minutes.',
            'body' => 'Paper registers replaced across 32 classes. Absence messages reach guardians before the second period begins.',
            'metrics' => [
                ['value' => '9 min', 'caption' => 'Whole-school register'],
                ['value' => '32', 'caption' => 'Classes live'],
            ],
        ],
        [
            'context' => 'Senior school · Francistown',
            'title' => 'A whole exam cycle without a spreadsheet.',
            'body' => 'Mark entry, moderation and 1,100 report cards handled inside one term, with results released the same day.',
            'metrics' => [
                ['value' => '1,100', 'caption' => 'Report cards batched'],
                ['value' => 'Same day', 'caption' => 'Results released'],
            ],
        ],
        [
            'context' => 'College · Maun',
            'title' => 'Semester registration with clean prerequisites.',
            'body' => '2,400 students self-register online. Credit rules, caps and clearance holds are enforced before a place is confirmed.',
            'metrics' => [
                ['value' => '2,400', 'caption' => 'Self-registrations'],
                ['value' => '4 days', 'caption' => 'Registration window'],
            ],
        ],
    ];
@endphp
<section id="deployments" class="hp-section hp-band">
    <div class="hp-headrow">
        <div>
            <p class="hp-eyebrow">V. In the field</p>
            <h2 class="hp-h2 hp-h2--sm">A few live Heritage Pro deployments.</h2>
        </div>
        <a href="{{ route('website.customers') }}" class="hp-link">All case studies</a>
    </div>
    <div class="hp-grid-3 hp-cases">
        @foreach ($cases as $case)
            <article>
                <p class="hp-label">{{ $case['context'] }}</p>
                <h3 class="hp-case__title">{{ $case['title'] }}</h3>
                <p class="hp-body">{{ $case['body'] }}</p>
                <dl class="hp-case__metrics">
                    @foreach ($case['metrics'] as $metric)
                        <div>
                            <dd class="hp-case__value">{{ $metric['value'] }}</dd>
                            <dt class="hp-case__caption">{{ $metric['caption'] }}</dt>
                        </div>
                    @endforeach
                </dl>
            </article>
        @endforeach
    </div>
</section>
