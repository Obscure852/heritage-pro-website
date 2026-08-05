<section class="precision-story" aria-labelledby="precision-story-title">
    <div class="precision-container precision-story-layout">
        <figure class="precision-story-photo" data-reveal>
            <img src="{{ asset('images/website/students-classroom.webp') }}"
                 alt="Students raising their hands during a classroom lesson"
                 width="1600"
                 height="1067"
                 loading="lazy"
                 decoding="async">
            <figcaption>Representative classroom photography · Pexels</figcaption>
        </figure>

        <div class="precision-story-copy" data-reveal>
            <span class="precision-kicker">Technology grounded in school life</span>
            <h2 id="precision-story-title">One system, from first bell to final report.</h2>
            <p>Heritage Pro gives school leaders, teachers, finance teams, learners, and families one reliable source of truth—without forcing every institution into the same workflow.</p>

            <div class="precision-story-list" role="list">
                <div role="listitem">
                    <strong>Connected records</strong>
                    <span>Admissions, attendance, marks, fees, and communication stay aligned.</span>
                </div>
                <div role="listitem">
                    <strong>Built for local operations</strong>
                    <span>Flexible school modes, terms, reporting structures, and deployment support.</span>
                </div>
                <div role="listitem">
                    <strong>Ready beyond the office</strong>
                    <span>Responsive workflows and a focused mobile experience for work on the move.</span>
                </div>
            </div>

            <a href="{{ route('website.about') }}" class="precision-text-link">
                Why institutions choose Heritage Pro
                @include('website.partials.icon', ['name' => 'arrow', 'size' => 15])
            </a>
        </div>
    </div>
</section>
