@php
    $moduleGroups = [
        'Academics' => ['Admissions', 'Student records', 'Attendance', 'Timetabling', 'Assessments', 'Report cards', 'Transcripts', 'Curriculum mapping'],
        'Administration' => ['Conduct', 'Health records', 'Staff & HR', 'Payroll', 'Library', 'Hostel & boarding', 'Transport', 'Inventory & assets'],
        'Finance' => ['Fees & invoicing', 'Payments', 'Reconciliation', 'Arrears', 'Budgets', 'Procurement'],
        'Learning' => ['Learning management', 'Lesson planning', 'Online assessment', 'Question banks', 'Homework', 'Parent portal'],
        'Higher education' => ['Course registration', 'Credits & GPA', 'Faculty workload', 'Graduation clearance', 'Research records', 'Alumni'],
        'Platform' => ['Analytics', 'Ministry reporting', 'Messaging & SMS', 'Access control', 'Integrations & API', 'Mobile app'],
    ];
@endphp
<section id="modules" class="hp-modules hp-band hp-band--tint">
    <div class="hp-modules__inner">
        <div>
            <p class="hp-eyebrow">III. Index of modules</p>
            <h2 class="hp-h2 hp-h2--sm">Every module, working in concert.</h2>
            <p class="hp-lead">Forty modules, one data model. Licensed by edition — nothing you don't need, nothing you must bolt on later.</p>
            <a href="{{ route('website.features') }}" class="hp-link">Full module documentation</a>
        </div>
        <div class="hp-modules__cols">
            @foreach ($moduleGroups as $groupName => $modules)
                <div class="hp-modgroup">
                    <p class="hp-modgroup__title">{{ $groupName }}</p>
                    <ul>
                        @foreach ($modules as $module)
                            <li>{{ $module }}</li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
</section>
