@php
    $reportRows = [
        ['subject' => 'Mathematics', 'mark' => 78, 'grade' => 'A', 'position' => '4 / 32'],
        ['subject' => 'English Language', 'mark' => 71, 'grade' => 'B+', 'position' => '7 / 32'],
        ['subject' => 'Physics', 'mark' => 82, 'grade' => 'A', 'position' => '2 / 28'],
        ['subject' => 'Setswana', 'mark' => 69, 'grade' => 'B', 'position' => '11 / 32'],
    ];

    $transcriptRows = [
        ['code' => 'CMP211', 'course' => 'Data Structures', 'credits' => 4, 'grade' => 'A'],
        ['code' => 'CMP223', 'course' => 'Database Systems', 'credits' => 4, 'grade' => 'B+'],
        ['code' => 'MTH204', 'course' => 'Discrete Mathematics', 'credits' => 3, 'grade' => 'A−'],
        ['code' => 'BUS110', 'course' => 'Professional Practice', 'credits' => 2, 'grade' => 'B'],
    ];

    // Head the sample report card with a live senior-school client.
    $reportSchool = collect($site['clients'])
        ->first(fn (array $client) => str_contains($client['type'], 'Senior'))['label'] ?? 'Heritage Pro';
@endphp
<section id="capabilities" class="hp-section hp-band">
    <div class="hp-intro">
        <p class="hp-eyebrow">II. Capabilities</p>
        <h2 class="hp-h2">End-to-end modules for academics, administration, and learning.</h2>
        <p class="hp-lead">One database behind every screen. Nothing is exported, re-keyed or reconciled by hand at the end of term.</p>
    </div>

    {{-- Student information --}}
    <div class="hp-split">
        <div class="hp-split__copy">
            <p class="hp-kicker">Student information</p>
            <h3 class="hp-h3">Run admissions, student records, attendance, and academic records from one place.</h3>
            <p>An applicant becomes a learner without a single re-entry. Every register, transfer and grade lands on the same permanent record — and stays auditable for as long as you keep it.</p>
            <div class="hp-points">
                <div><strong>Admissions pipeline.</strong> <span>Application, offer, acceptance and enrolment in one tracked flow.</span></div>
                <div><strong>Permanent learner record.</strong> <span>Attendance, conduct, health and results on one timeline.</span></div>
                <div><strong>Role-based access.</strong> <span>Teachers, bursars and registrars see only what their role allows.</span></div>
            </div>
        </div>
        <figure class="hp-panel">
            <figcaption class="hp-panel__bar">Learner record — K. Mothibi <em>Admitted January 2026</em></figcaption>
            <div class="hp-panel__tabs"><span class="is-active">Overview</span><span>Attendance</span><span>Results</span><span>Finance</span><span>Documents</span></div>
            <div class="hp-panel__body">
                <dl class="hp-facts hp-facts--divided">
                    <div><dt>Form</dt><dd>4B</dd></div>
                    <div><dt>Attendance</dt><dd>97.2%</dd></div>
                    <div><dt>Fees</dt><dd>Settled</dd></div>
                </dl>
                <p class="hp-timeline__title">Record timeline</p>
                <ul class="hp-timeline">
                    <li><span>Term 3 results published</span><time>28 July</time></li>
                    <li><span>Fee receipt #40218 issued</span><time>14 July</time></li>
                    <li><span>Subject set changed to Physics</span><time>2 July</time></li>
                    <li><span>Enrolled from admissions offer</span><time>11 January</time></li>
                </ul>
            </div>
        </figure>
    </div>

    {{-- Assessment & reporting --}}
    <div class="hp-split hp-split--flip">
        <figure class="hp-panel">
            <figcaption class="hp-panel__bar">Report card — Michaelmas Term <em class="is-flagged">Held for sign-off</em></figcaption>
            <div class="hp-panel__body">
                <div class="hp-report__head">
                    <div class="hp-report__school">{{ $reportSchool }}</div>
                    <div class="hp-report__kind">Statement of attainment</div>
                </div>
                <table class="hp-table">
                    <thead>
                        <tr>
                            <th scope="col">Subject</th>
                            <th scope="col" class="hp-table__num">Mark</th>
                            <th scope="col" class="hp-table__num">Grade</th>
                            <th scope="col" class="hp-table__num">Position</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reportRows as $row)
                            <tr>
                                <td>{{ $row['subject'] }}</td>
                                <td class="hp-table__num">{{ $row['mark'] }}</td>
                                <td class="hp-table__num hp-table__grade">{{ $row['grade'] }}</td>
                                <td class="hp-table__num hp-table__soft">{{ $row['position'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <p class="hp-report__total"><span>Term average</span><strong>75.0</strong></p>
                <p class="hp-report__comment">“A steady term. Kagiso should now push for consistency in written work.” — Form tutor</p>
            </div>
        </figure>
        <div class="hp-split__copy">
            <p class="hp-kicker">Assessment &amp; reporting</p>
            <h3 class="hp-h3">Capture scores, automate report cards, and publish results with less manual work.</h3>
            <p>Teachers enter marks once. Weightings, grade boundaries, positions and comments compute themselves — then the report card renders in your school's own established format.</p>
            <div class="hp-points">
                <div><strong>Your grading rules.</strong> <span>Weightings and boundaries configured per subject and stage.</span></div>
                <div><strong>Your template.</strong> <span>Print-ready report cards generated in a single batch.</span></div>
                <div><strong>Released when ready.</strong> <span>Results held until the head of school signs off.</span></div>
            </div>
        </div>
    </div>

    {{-- Tertiary --}}
    <div class="hp-split">
        <div class="hp-split__copy">
            <p class="hp-kicker">Tertiary</p>
            <h3 class="hp-h3">Manage higher education administration, digital learning, and academic records end-to-end.</h3>
            <p>Semester registration, credit accumulation, faculty workload and graduation clearance — with the learning platform attached to the very same student record.</p>
            <div class="hp-points">
                <div><strong>Registration &amp; credits.</strong> <span>Prerequisites and caps enforced at the point of registration.</span></div>
                <div><strong>Transcripts &amp; clearance.</strong> <span>GPA, transcripts and graduation lists on demand.</span></div>
                <div><strong>Learning built in.</strong> <span>Course materials, submissions and online assessment.</span></div>
            </div>
        </div>
        <figure class="hp-panel">
            <figcaption class="hp-panel__bar">Academic transcript — BSc Computing <em>CGPA 3.42</em></figcaption>
            <div class="hp-panel__body">
                <table class="hp-table">
                    <thead>
                        <tr>
                            <th scope="col">Code</th>
                            <th scope="col">Course</th>
                            <th scope="col" class="hp-table__num">Cr</th>
                            <th scope="col" class="hp-table__num">Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transcriptRows as $row)
                            <tr>
                                <td class="hp-table__soft">{{ $row['code'] }}</td>
                                <td>{{ $row['course'] }}</td>
                                <td class="hp-table__num">{{ $row['credits'] }}</td>
                                <td class="hp-table__num hp-table__grade">{{ $row['grade'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <dl class="hp-facts hp-facts--capped">
                    <div><dt>Credits</dt><dd>96 / 120</dd></div>
                    <div><dt>Standing</dt><dd>Good</dd></div>
                    <div><dt>Clearance</dt><dd>Pending</dd></div>
                </dl>
            </div>
        </figure>
    </div>
</section>
