@php
    $questions = [
        [
            'q' => 'Who owns and hosts our data?',
            'a' => 'Your institution owns its data outright. It is hosted in an ISO 27001 certified region, encrypted at rest and in transit, and exportable in full at any time — including after you leave.',
        ],
        [
            'q' => 'How long does implementation take?',
            'a' => 'A single school is typically live in two to four weeks, including migration and staff training. Groups run a phased rollout, one campus at a time, timed to term boundaries.',
        ],
        [
            'q' => 'Can we migrate our existing records?',
            'a' => 'Yes. Learners, guardians, historical results and outstanding balances are migrated from spreadsheets or an existing SIS, then reconciled with your bursar before go-live.',
        ],
        [
            'q' => 'Does it work where connectivity is poor?',
            'a' => 'Registers and mark entry work offline on the mobile app and sync when a connection returns. The web application is built to remain usable on low-bandwidth links.',
        ],
        [
            'q' => 'What does support look like after go-live?',
            'a' => 'Every institution keeps a named contact. Term-start and examination periods are covered by extended hours, and release notes are written for administrators, not engineers.',
        ],
        [
            'q' => 'Can the board see its own reporting?',
            'a' => 'Governors receive a read-only board view: enrolment, attainment, attendance and fee collection, updated live and exportable as a signed PDF pack for meetings.',
        ],
    ];
@endphp
<section id="faq" class="hp-section hp-band">
    <div class="hp-intro hp-intro--narrow hp-faq__intro">
        <p class="hp-eyebrow">VII. Due diligence</p>
        <h2 class="hp-h2 hp-h2--sm">Answers for cautious administrators.</h2>
    </div>
    <div class="hp-faq">
        @foreach ($questions as $question)
            <div>
                <h3 class="hp-h4">{{ $question['q'] }}</h3>
                <p>{{ $question['a'] }}</p>
            </div>
        @endforeach
    </div>
</section>
