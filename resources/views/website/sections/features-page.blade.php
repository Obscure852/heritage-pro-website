<div id="features-page-content">
    <section class="section heritage-features-page">
        <div class="container">
            <div class="center heritage-section-intro">
                <span class="eyebrow">Operational coverage</span>
                <h2 class="heritage-section-intro-title">Features that connect academics, administration, and learning in one workflow.</h2>
                <p class="lead">Heritage Pro is built for junior schools, senior schools, and tertiary institutions that need reliable control across admissions, teaching, assessments, attendance, finance, people, and reporting.</p>
            </div>

            <div class="features-row">
                <div class="feature-copy">
                    <span class="eyebrow">Academic operations</span>
                    <h3>Organise learners, classes, programmes, subjects, and teaching activity without splitting work across systems.</h3>
                    <p>From admissions and student records to curriculum setup, teacher allocation, scheme of work tracking, and timetable control, Heritage Pro keeps academic administration visible and current.</p>
                    <ul>
                        <li><b>Admissions and enrollment</b> — application capture, document review, and onboarding.</li>
                        <li><b>Student lifecycle</b> — profiles, sponsor links, progression, and transfer history.</li>
                        <li><b>Academics Manager</b> — class structures, subject allocations, lecturer or teacher assignments, and grade setup.</li>
                        <li><b>Scheme of Work and Timetable</b> — planning coverage and daily teaching organisation in one view.</li>
                    </ul>
                    <a href="{{ route('website.products') }}" class="btn btn-secondary">View deployments <span>→</span></a>
                </div>
                <div class="feature-mock">
                    @include('website.partials.window-chrome', ['extraClass' => 'heritage-window-chrome', 'dotClass' => 'heritage-window-dot', 'urlClass' => 'heritage-window-url'])
                    @include('website.partials.student-record-mock')
                </div>
            </div>

            <div class="features-row reverse">
                <div class="feature-copy">
                    <span class="eyebrow">Assessment and learning</span>
                    <h3>Run assessments, invigilation, report publishing, and digital learning from one platform.</h3>
                    <p>Heritage Pro supports classroom assessment, exam cycles, invigilation planning, report generation, online tasks, and learning delivery so institutions do not need separate academic and LMS tools.</p>
                    <ul>
                        <li><b>Assessments</b> — coursework, exams, moderation, analysis, and publishing.</li>
                        <li><b>Invigilation Roster</b> — allocate exam sessions, venues, and staff coverage with less manual coordination.</li>
                        <li><b>Learning Space</b> — resources, assignments, tests, and learner progress in one digital environment.</li>
                        <li><b>Report cards and transcripts</b> — issue polished outputs aligned to institutional requirements.</li>
                    </ul>
                    <a href="{{ route('website.customers') }}" class="btn btn-secondary">See live institutions <span>→</span></a>
                </div>
                <div class="feature-mock">
                    <div class="feature-surface">
                        <div class="feature-panel">
                            <h4>Assessment cycle</h4>
                            <p>Move from continuous assessment and invigilation planning into result publishing and parent or student communication without restarting the process in another tool.</p>
                        </div>
                        <div class="feature-mini-list">
                            <div class="feature-panel">
                                <h4>Exam session planning</h4>
                                <p>Venue setup, invigilator allocation, and readiness checks.</p>
                            </div>
                            <div class="feature-panel">
                                <h4>Learning progression</h4>
                                <p>Assignments, tests, course content, and student engagement tracking.</p>
                            </div>
                        </div>
                        <div class="feature-chip-row">
                            <span class="feature-chip">Assessments</span>
                            <span class="feature-chip alt">Invigilation Roster</span>
                            <span class="feature-chip">Learning Space</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="features-row">
                <div class="feature-copy">
                    <span class="eyebrow">Institution services</span>
                    <h3>Keep finance, staff administration, communication, and institutional support services aligned with academics.</h3>
                    <p>Fees administration, staff records, attendance, communications, documents, library services, sponsorships, and analytics stay close to the academic record, which makes reporting faster and operational follow-up simpler.</p>
                    <ul>
                        <li><b>Fees Administration</b> — charges, receipts, statements, and collection analysis.</li>
                        <li><b>Human Resources</b> — qualifications, work history, leave, and staff development records.</li>
                        <li><b>Communications</b> — notices, reminders, SMS, email, and targeted messaging.</li>
                        <li><b>Analytics and security</b> — dashboards, access control, backups, and decision-ready reporting.</li>
                    </ul>
                    <a href="{{ route('website.about') }}" class="btn btn-secondary">About Heritage Pro <span>→</span></a>
                </div>
                <div class="feature-mock">
                    <div class="feature-surface">
                        <div class="feature-panel">
                            <h4>Operations overview</h4>
                            <p>Finance, people, attendance, and communication signals remain visible beside academic performance and learner status.</p>
                        </div>
                        <div class="feature-stat-grid">
                            <div class="feature-stat"><b>BWP</b><span>Fees control</span></div>
                            <div class="feature-stat"><b>HR</b><span>People records</span></div>
                            <div class="feature-stat"><b>24/7</b><span>Reporting access</span></div>
                        </div>
                        <div class="feature-chip-row">
                            <span class="feature-chip alt">Fees Administration</span>
                            <span class="feature-chip">Human Resources</span>
                            <span class="feature-chip">Communications</span>
                        </div>
                    </div>
                </div>
            </div>

            <h3 class="center heritage-modules-intro-title">Connected modules across the institution.</h3>
            <p class="lead center heritage-modules-intro-copy">A practical operating system for schools and tertiary institutions, with modules that support daily work instead of fragmenting it.</p>

            <div class="modules-grid">
                @foreach ($site['modules'] as $module)
                    <div class="module-tile">
                        <div class="icon">@include('website.partials.icon', ['name' => $module['icon'] ?? 'users', 'size' => 22])</div>
                        <h4>{{ $module['title'] }}</h4>
                        <p>{{ $module['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</div>
