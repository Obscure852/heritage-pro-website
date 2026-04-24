<div id="about-page-content">
    <section id="about" class="section heritage-about-products">
        <div class="container">
            <div class="center heritage-section-intro">
                <span class="eyebrow">About Heritage Pro</span>
                <h2 class="heritage-section-intro-title">One Botswana-built platform across junior schools, senior schools, and tertiary institutions.</h2>
                <p class="lead">Heritage Pro centralises admissions, academics, attendance, assessments, communications, fees, digital learning, HR, documents, library services, and reporting in one secure environment designed for real education institutions.</p>
            </div>
            <div class="products-grid">
                <div class="product-card schools">
                    <span class="product-badge">Junior Schools</span>
                    <div class="tile"></div>
                    <h3>School Management System</h3>
                    <p>Supports admissions, sponsors or parent records, student records, attendance, academic management, assessments, communications, fees administration, and houses or teams.</p>
                    <ul>
                        <li>Online applications and admissions analysis</li>
                        <li>Parent portal, student health, and textbook workflows</li>
                        <li>Attendance, assessments, and report card management</li>
                        <li>Fees administration, HR records, and communication tools</li>
                    </ul>
                </div>
                <div class="product-card k12">
                    <span class="product-badge">Senior Schools</span>
                    <div class="tile"></div>
                    <h3>Senior School Deployment</h3>
                    <p>Extends the school platform for stronger academic control, teacher allocation, detailed reporting, performance analysis, and end-of-term administration.</p>
                    <ul>
                        <li>Academic structures, optional subjects, and grading matrices</li>
                        <li>Assessment capture, automated comments, and score analysis</li>
                        <li>Attendance links to report cards and intervention reporting</li>
                        <li>Bulk communication and fee collection visibility</li>
                    </ul>
                </div>
                <div class="product-card collegiate">
                    <span class="product-badge">College / IHS</span>
                    <div class="tile"></div>
                    <h3>College Management System</h3>
                    <p>Designed for colleges, technical institutes, and multi-campus tertiary institutions needing admissions, academic structure, LMS delivery, assessments, transcripts, attendance, and institutional services in one platform.</p>
                    <ul>
                        <li>Admissions, enrollment, semesters, levels, and progression</li>
                        <li>Assessments, results, transcripts, and GPA records</li>
                        <li>LMS content, tests, assignments, forums, and progress tracking</li>
                        <li>Library, documents, HR, assets, sponsorships, and alumni</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="section heritage-about-features">
        <div class="container">
            <div class="center heritage-section-intro">
                <span class="eyebrow">Operational coverage</span>
                <h2 class="heritage-section-intro-title">Built to serve the full institutional journey.</h2>
                <p class="lead">Whether the institution is a junior school, senior school, or college, Heritage Pro supports the operational workflows that matter most every day.</p>
            </div>
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

    <section class="section heritage-about-cases">
        <div class="container">
            <div class="center heritage-section-intro">
                <span class="eyebrow">Selected live institutions</span>
                <h2 class="heritage-section-intro-title">A few active Heritage Pro deployments.</h2>
            </div>
            @include('website.partials.deployment-cards')
        </div>
    </section>
</div>
