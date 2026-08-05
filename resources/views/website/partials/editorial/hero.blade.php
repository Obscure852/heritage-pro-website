@php
    $bars = [
        ['label' => 'Y7', 'height' => 66, 'current' => false],
        ['label' => 'Y8', 'height' => 82, 'current' => false],
        ['label' => 'Y9', 'height' => 74, 'current' => false],
        ['label' => 'Y10', 'height' => 96, 'current' => true],
        ['label' => 'Y11', 'height' => 88, 'current' => false],
        ['label' => 'Y12', 'height' => 104, 'current' => false],
    ];
@endphp
<section class="hp-hero hp-band">
    <p class="hp-rule-label">Established for institutions</p>
    <h1 class="hp-h1">{{ $hero['title'] }}</h1>
    <p class="hp-hero__lead">One record for every learner. Admissions, attendance, assessment, finance and reporting run on the same system — so the numbers you report to your board are the numbers you can defend.</p>
    <div class="hp-hero__actions">
        <a href="#demo" class="hp-btn hp-btn--solid">Book a 30-minute demo</a>
        <a href="{{ route('website.products') }}" class="hp-btn hp-btn--outline">Read the prospectus</a>
    </div>

    <div class="hp-hero__stage">
        <figure class="hp-board">
            <figcaption class="hp-board__bar">
                <span class="hp-board__title"><span class="hp-board__dot" aria-hidden="true"></span>Registrar's overview — Michaelmas Term</span>
                <span class="hp-board__tabs"><span>Academics</span><span>Finance</span><span class="is-active">Board report</span></span>
            </figcaption>
            <dl class="hp-board__kpis">
                <div class="hp-board__kpi">
                    <dt>Enrolment</dt>
                    <dd>1,284</dd>
                    <p class="hp-board__note hp-board__note--up">+4.2% on last year</p>
                </div>
                <div class="hp-board__kpi">
                    <dt>Attendance</dt>
                    <dd>96.4%</dd>
                    <p class="hp-board__note">Term to date</p>
                </div>
                <div class="hp-board__kpi">
                    <dt>Fees collected</dt>
                    <dd>91%</dd>
                    <p class="hp-board__note">P 214k outstanding</p>
                </div>
                <div class="hp-board__kpi">
                    <dt>Results</dt>
                    <dd>Ready</dd>
                    <p class="hp-board__note hp-board__note--flag">Awaiting sign-off</p>
                </div>
            </dl>
            <div class="hp-board__lower">
                <div class="hp-board__chart">
                    <div class="hp-board__chart-head">
                        <strong>Attainment by year group</strong>
                        <span>Three-year trend</span>
                    </div>
                    <div class="hp-board__bars" role="img" aria-label="Attainment by year group, highest in Year 12">
                        @foreach ($bars as $bar)
                            <div @class(['hp-board__bar-col', 'is-current' => $bar['current']])>
                                <span class="hp-board__bar-fill" style="height: {{ $bar['height'] }}px"></span>
                                <span>{{ $bar['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="hp-board__side">
                    <strong>For the board's attention</strong>
                    <ul class="hp-board__flags">
                        <li>Form 4A attendance below 92% for two weeks</li>
                        <li>14 fee accounts eligible for a payment plan</li>
                        <li>Ministry return due 14 September — draft ready</li>
                        <li>Two staff appraisals outstanding</li>
                    </ul>
                </div>
            </div>
        </figure>
    </div>
</section>
