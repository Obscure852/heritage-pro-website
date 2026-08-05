<?php

/*
|--------------------------------------------------------------------------
| Heritage Pro — Prospectus
|--------------------------------------------------------------------------
|
| The formal document a head, registrar, bursar or board reads before
| committing to Heritage Pro: what the platform is, what it replaces, how it
| is implemented, who owns the data, and on what commercial terms.
|
| Body blocks use the same vocabulary as config/heritage_journal.php —
| 'heading', 'paragraph', 'list' and 'pull' — and render through
| website/partials/editorial/article-body. Headings become the contents index
| on the page, so keep them short and descriptive.
|
| Figures quoted here must stay consistent with config/heritage_website.php.
|
*/

return [
    'meta' => [
        'title' => 'Heritage Pro — Prospectus',
        'eyebrow' => 'Prospectus',
        'heading' => 'The Heritage Pro prospectus',
        'standfirst' => 'What the platform covers, what it replaces, how an institution is brought live on it, and the terms on which it is licensed. Written for the people who have to approve the decision.',
        'edition_note' => 'Current edition · Michaelmas 2026',
        'contents_label' => 'Contents',
    ],

    'body' => [
        ['type' => 'paragraph', 'text' => 'Heritage Pro is a school information system: one record per learner, carrying admissions, attendance, assessment, finance and reporting on a single database. It is used by junior schools, senior schools and tertiary institutions, and it is built and supported in Botswana by the team that writes it.'],
        ['type' => 'paragraph', 'text' => 'This document is intended to answer, in one place, the questions a governing board asks before approving a system of record. If something here is not clear enough to act on, that is a fault in the document — tell us and we will fix it.'],

        ['type' => 'heading', 'text' => 'What the platform is'],
        ['type' => 'paragraph', 'text' => 'Most institutions do not run one system. They run a student database, a spreadsheet for marks, a second spreadsheet for fees, a messaging tool, and a filing cabinet — and spend the end of every term reconciling them. The cost is not the licences. It is the reconciliation, and the confidence lost when two systems disagree in front of a parent.'],
        ['type' => 'paragraph', 'text' => 'Heritage Pro replaces that arrangement with a single record. An applicant becomes a learner without re-entry. A mark entered by a teacher is the mark on the report card, the mark in the analytics and the mark in the ministry return. A payment recorded by the bursar is immediately visible to the person chasing arrears.'],
        ['type' => 'pull', 'text' => 'The test of a system of record is not what it can display. It is whether two people in different offices, asked the same question, give the same answer.'],

        ['type' => 'heading', 'text' => 'Who it is for'],
        ['type' => 'paragraph', 'text' => 'The platform is licensed in three editions. They share one data model; what differs is the workflow, the reporting formats and the permissions each stage of education requires.'],
        ['type' => 'list', 'items' => [
            'Junior Schools — class-based registers, continuous assessment, parent communication and termly report cards for primary teaching teams.',
            'Senior Schools — subject-set timetabling, examinations and grade entry, ministry-ready return formats, and fees, invoicing and receipts.',
            'Colleges & Institutes — semester registration, credit accumulation and GPA, faculty workload, graduation clearance, and an integrated learning platform.',
        ]],
        ['type' => 'paragraph', 'text' => 'A school group running more than one stage of education is licensed for the editions it needs and administers them from one place. Institutions currently on the platform range from single-campus junior schools to a college running semester registration for its whole intake.'],

        ['type' => 'heading', 'text' => 'What it replaces'],
        ['type' => 'paragraph', 'text' => 'This is the question worth being concrete about, because it is where the return comes from. A system that sits alongside your existing tools adds a database; it does not remove the reconciliation. In a typical deployment Heritage Pro absorbs:'],
        ['type' => 'list', 'items' => [
            'The student database or admissions register, including the paper file.',
            'Departmental mark spreadsheets and the master sheet they feed.',
            'The report card template and the manual process of filling it.',
            'The fee ledger, invoice book and receipt pad.',
            'Bulk SMS or messaging handled through a separate account.',
            'The end-of-term ministry return, assembled by hand.',
        ]],
        ['type' => 'paragraph', 'text' => 'What it does not replace is your accounting package, your payroll bureau if you use one, or your bank. Heritage Pro records what is owed, what is paid and by whom; it is not a general ledger.'],

        ['type' => 'heading', 'text' => 'The modules'],
        ['type' => 'paragraph', 'text' => 'Forty modules across six groups, licensed by edition. Academics covers admissions, student records, attendance, timetabling, assessments, report cards, transcripts and curriculum mapping. Administration covers conduct, health records, staff and HR, payroll, library, hostel and boarding, transport, and inventory and assets.'],
        ['type' => 'paragraph', 'text' => 'Finance covers fees and invoicing, payments, reconciliation, arrears, budgets and procurement. Learning covers the learning management platform, lesson planning, online assessment, question banks, homework and the parent portal. Higher education adds course registration, credits and GPA, faculty workload, graduation clearance, research records and alumni. Platform covers analytics, ministry reporting, messaging and SMS, access control, integrations and API, and the mobile application.'],
        ['type' => 'paragraph', 'text' => 'Nothing in that list is an add-on product from a third party. Each module writes to the same record, which is the reason the reporting reconciles.'],

        ['type' => 'heading', 'text' => 'Implementation'],
        ['type' => 'paragraph', 'text' => 'A single school is typically live in two to four weeks. Groups run a phased rollout, one campus at a time, timed to term boundaries so no institution changes system mid-term.'],
        ['type' => 'paragraph', 'text' => 'The sequence is consistent: a working session to configure your term structure, grading rules and fee model; migration of learners, guardians, historical results and outstanding balances; reconciliation of the migrated figures with your bursar, signed off in writing before go-live; staff training by role; then go-live with your named implementation lead present.'],
        ['type' => 'paragraph', 'text' => 'Migration is the step institutions underestimate. We do the work, but the reconciliation is a joint exercise — your bursar confirms the opening balances, because nobody else can.'],

        ['type' => 'heading', 'text' => 'Data ownership, security and continuity'],
        ['type' => 'paragraph', 'text' => 'Your institution owns its data outright. It is hosted in an ISO 27001 certified region, encrypted at rest and in transit, and exportable in full at any time — including after you leave, without a fee and without a support ticket.'],
        ['type' => 'paragraph', 'text' => 'Access is role-based: teachers, bursars and registrars see only what their role allows, and changes to a record are attributable. Governors receive a read-only board view of enrolment, attainment, attendance and fee collection, updated live and exportable as a signed PDF pack for meetings.'],
        ['type' => 'paragraph', 'text' => 'Connectivity is treated as unreliable by design. Registers and mark entry work offline on the mobile application and sync when a connection returns; the web application is built to remain usable on low-bandwidth links. Platform uptime over the trailing twelve months has been 99.95%.'],

        ['type' => 'heading', 'text' => 'Support after go-live'],
        ['type' => 'paragraph', 'text' => 'Every institution keeps a named contact. Term-start and examination periods are covered by extended hours, because that is when a system either holds or fails publicly.'],
        ['type' => 'paragraph', 'text' => 'Support is provided by the people who build the modules. There is no outsourced tier between an institution and the engineer who wrote the code in question, and release notes are written for administrators rather than engineers.'],

        ['type' => 'heading', 'text' => 'Commercial terms'],
        ['type' => 'paragraph', 'text' => 'Pricing is per enrolled learner, billed annually. Implementation, training, hosting and support are included — the figure quoted is the figure invoiced.'],
        ['type' => 'list', 'items' => [
            'Essential — BWP 28 per learner per year. Junior and single-campus schools establishing one record system: student records and admissions, attendance and registers, continuous assessment, parent portal and SMS, email support within one business day.',
            'Institution — BWP 42 per learner per year. Senior schools and groups running examinations, finance and reporting together: everything in Essential, plus examinations and report cards, fees and invoicing, timetabling and subject sets, ministry reporting formats and a named implementation lead.',
            'Custom — by arrangement, for multi-campus groups and tertiary institutions: everything in Institution, plus semesters, credits and transcripts, learning management, single sign-on and API access, data migration service and a service level agreement.',
        ]],
        ['type' => 'paragraph', 'text' => 'Heritage Pro is also sold and implemented by accredited resellers, each holding current product certification and working to the same implementation standard as our own team. Buying through a reseller does not change the software, the hosting or the data ownership terms.'],

        ['type' => 'heading', 'text' => 'How to proceed'],
        ['type' => 'paragraph', 'text' => 'The next step is a thirty-minute demonstration run against your own term structure, grading rules and fee model — not a generic tour. Bring your registrar and your bursar; they will ask the questions that matter, and a written quote follows within two business days.'],
        ['type' => 'paragraph', 'text' => 'If your board wants to interrogate this document first, we will send two reference institutions of comparable size and type and step out of the conversation.'],
    ],
];
