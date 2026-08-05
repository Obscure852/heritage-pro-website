<?php

/*
|--------------------------------------------------------------------------
| Heritage Pro — The Journal
|--------------------------------------------------------------------------
|
| Long-form articles published by Heritage Pro. Newest first: the homepage
| shows the first three, /journal lists them all, and /journal/{slug} renders
| one. Bodies are block arrays rather than HTML so the templates keep control
| of markup — supported block types are 'heading', 'paragraph', 'list' and
| 'pull'.
|
*/

return [
    'meta' => [
        'title' => 'Heritage Pro — The Journal',
        'eyebrow' => 'The journal',
        'heading' => 'Ideas and playbooks for school leaders.',
        'lead' => 'Writing from the team who build and implement Heritage Pro — on reporting cycles, admissions, procurement and the administrative work that runs a school year.',
    ],

    'articles' => [

        [
            'slug' => 'running-admissions-season-without-a-single-lost-application',
            'kind' => 'Playbook',
            'reading_time' => '11 min',
            'published_at' => '2026-07-22',
            'author' => 'The Heritage Pro team',
            'title' => 'Running admissions season without a single lost application',
            'standfirst' => 'Applications rarely disappear. They sit in an inbox nobody owns, on a form nobody logged, in a pile nobody scored. Here is how the Institute of Health Sciences ran an intake end to end — application, ranking, offer, acceptance, enrolment — on one record.',
            'image' => null,
            'image_alt' => null,
            'body' => [
                ['type' => 'paragraph', 'text' => 'Ask an admissions officer what went wrong last intake and you will rarely hear “we lost applications”. You will hear that a form arrived by email and never made it onto the spreadsheet. That two people scored the same candidate differently. That an offer went out to someone who had already accepted elsewhere, because the list used to draft the letters was four days old.'],
                ['type' => 'paragraph', 'text' => 'None of those is a filing failure. They are all the same failure: the application changed hands, and every hand it passed through kept its own copy. This playbook describes the alternative — one record per applicant, from the moment they apply to the moment they appear on a class list.'],

                ['type' => 'heading', 'text' => 'The four gaps applications fall into'],
                ['type' => 'paragraph', 'text' => 'Before changing anything, it is worth naming where the losses actually happen. In our experience of running intakes with institutions, there are four:'],
                ['type' => 'list', 'items' => [
                    'Between the applicant and the register. A form arrives through a channel nobody owns — a personal inbox, a WhatsApp message, a walk-in — and is never entered anywhere central.',
                    'Between the register and the assessment. The applicant is recorded but never scored, because the scoring happens in a separate spreadsheet built by one person.',
                    'Between the assessment and the offer. The ranked list is exported, then edited by hand while offers are drafted, so the list that was approved is not the list that was written to.',
                    'Between the offer and enrolment. An applicant accepts, but nobody converts them into a student, so they arrive at registration as a stranger and are keyed in from scratch.',
                ]],
                ['type' => 'paragraph', 'text' => 'Every one of those gaps is a handover between systems. Close the handovers and the losses close with them.'],

                ['type' => 'heading', 'text' => 'One front door for every application'],
                ['type' => 'paragraph', 'text' => 'The first rule is unglamorous: there is exactly one way to apply, and it writes directly to the admissions register. Not a form that generates an email that someone transcribes — a form that creates the record.'],
                ['type' => 'paragraph', 'text' => 'This matters most for the applications that do not arrive online. Walk-ins, phone enquiries and paper forms still happen, particularly for mature and sponsored candidates. The answer is not to refuse them; it is to have the person taking the paper form enter it into the same register, immediately, so that a paper application and an online one are indistinguishable an hour later.'],
                ['type' => 'paragraph', 'text' => 'Once that holds, a question that used to take a morning — “how many applications do we have?” — takes a glance, and it is the same number for everybody who asks.'],

                ['type' => 'heading', 'text' => 'Rank on the criteria you published'],
                ['type' => 'paragraph', 'text' => 'This is where the Institute of Health Sciences intake is instructive. The Institute did not just use Heritage Pro to collect applications; they used it to rank them.'],
                ['type' => 'paragraph', 'text' => 'The distinction matters. Collecting applications in a system and then exporting them to a spreadsheet for scoring reintroduces every gap you just closed — and worse, it moves the decision-making into a file with no audit trail. Ranking inside the register means the criteria are applied uniformly, the score sits on the applicant record next to the evidence for it, and the order the committee sees is the order the system produced.'],
                ['type' => 'pull', 'text' => 'A ranked list is a decision, not a spreadsheet. It should be reproducible months later, when somebody asks why one candidate was placed above another.'],
                ['type' => 'paragraph', 'text' => 'It also survives scrutiny. Admissions decisions get questioned — by parents, by sponsors, occasionally by a ministry. An institution that can show the criteria, the scores against them and the resulting order, all recorded at the time, is in a far stronger position than one reconstructing a decision from an old export.'],

                ['type' => 'heading', 'text' => 'Send offers from the record, not from a copy of it'],
                ['type' => 'paragraph', 'text' => 'Offers are where the ranked list traditionally gets stale. The list is approved on Monday, the letters are prepared on Wednesday, and in between two candidates withdraw and one is moved up — but only on the copy the registrar is working from.'],
                ['type' => 'paragraph', 'text' => 'When offers are issued from the applicant record itself, the record knows an offer has gone out and when. Nobody is offered a place twice. Nobody who withdrew receives one. The waiting list moves when a place is released, rather than when somebody remembers to redo the mail merge.'],
                ['type' => 'paragraph', 'text' => 'The Institute of Health Sciences ran offers this way, out of the same register that held the ranking. The practical effect is that “who has been offered a place?” and “who has accepted?” are two fields on a record rather than two documents to reconcile.'],

                ['type' => 'heading', 'text' => 'Acceptance is a state, not an email'],
                ['type' => 'paragraph', 'text' => 'An acceptance sitting in an inbox is not an acceptance the institution can act on. It becomes actionable when it is recorded against the applicant — which means the response has to land back on the record the offer came from.'],
                ['type' => 'paragraph', 'text' => 'Once acceptance is a state on the record, the numbers everyone actually wants become available continuously rather than at the end: places offered, places accepted, places still open, and how far down the ranked list you have had to go to fill them. Those four figures are what let a registrar decide whether to release the next tranche of offers this week or next.'],

                ['type' => 'heading', 'text' => 'Enrolment without a single re-entry'],
                ['type' => 'paragraph', 'text' => 'The final gap is the one that costs the most staff hours and produces the most errors: turning an accepted applicant into an enrolled student. Done conventionally, it means someone reads the application file and types its contents into the student system, introducing fresh transcription errors into records that will follow the student for years.'],
                ['type' => 'paragraph', 'text' => 'It should be a status change. The applicant record becomes the student record; the name, contact details, guardians, documents and qualifications that were verified at application are the ones that carry forward. The Institute of Health Sciences enrolled their accepted candidates this way — the same record, promoted, rather than a new one keyed in.'],
                ['type' => 'paragraph', 'text' => 'The benefit shows up long after admissions season. When a student queries a fee, a sponsorship or an entry qualification in their second year, the evidence is still attached to the record it arrived on.'],

                ['type' => 'heading', 'text' => 'Running your own intake this way'],
                ['type' => 'paragraph', 'text' => 'If you take one intake and change one thing, make it the handovers. Concretely:'],
                ['type' => 'list', 'ordered' => true, 'items' => [
                    'Publish one application route and make every other route feed it the same day.',
                    'Write your ranking criteria down before applications open, and score inside the register rather than outside it.',
                    'Issue offers from the applicant record so the list cannot go stale between approval and dispatch.',
                    'Record acceptance as a state on the record, not as correspondence.',
                    'Enrol by promoting the applicant record, never by re-keying it.',
                ]],
                ['type' => 'paragraph', 'text' => 'None of these steps requires a bigger admissions team. They require that the application stops changing hands. If you would like to see this walked through against your own intake calendar and entry criteria, book a demonstration and bring your registrar.'],
            ],
        ],

        [
            'slug' => 'cutting-your-term-reporting-cycle-from-three-weeks-to-one-day',
            'kind' => 'Guide',
            'reading_time' => '8 min',
            'published_at' => '2026-06-30',
            'author' => 'The Heritage Pro team',
            'title' => 'Cutting your term reporting cycle from three weeks to one day',
            'standfirst' => 'Most schools do not have a reporting problem. They have a collection problem that only becomes visible in the last fortnight of term, when three weeks of evenings disappear into work that was never really about reporting at all.',
            'image' => 'images/website/students-classroom.webp',
            'image_alt' => 'Learners raising their hands in a classroom',
            'body' => [
                ['type' => 'paragraph', 'text' => 'Ask a head of school how long reporting takes and you will get an answer in weeks. Ask what actually happens in those weeks and almost none of it is reporting. It is chasing marks, retyping them, recalculating totals somebody has already calculated, and correcting the errors that all of that introduced.'],
                ['type' => 'paragraph', 'text' => 'The reporting itself — the professional judgement, the comment that tells a parent something useful, the decision about whether a set of results is fit to publish — is perhaps a day of work. Everything else is logistics. This guide is about removing the logistics.'],

                ['type' => 'heading', 'text' => 'Where the three weeks actually go'],
                ['type' => 'paragraph', 'text' => 'Time a conventional reporting cycle and it divides fairly consistently into four activities:'],
                ['type' => 'list', 'items' => [
                    'Collection. Marks exist, but on paper, in personal spreadsheets, and in the heads of teachers who are also teaching. Someone has to gather them.',
                    'Transcription. Collected marks are typed into a second place — a master sheet, a template, a report card. Each retyping is an opportunity for error.',
                    'Calculation. Weightings, totals, averages, grades and positions are worked out, often more than once, sometimes differently by different people.',
                    'Correction. The errors introduced during transcription and calculation are found — usually by a parent, sometimes after publication.',
                ]],
                ['type' => 'paragraph', 'text' => 'Only the first is unavoidable, and even it is only unavoidable at the end of term because it was avoidable all term.'],

                ['type' => 'heading', 'text' => 'Fix the calendar before you fix the software'],
                ['type' => 'paragraph', 'text' => 'The single biggest lever is not a system feature. It is deciding that marks are entered when the work is marked, not when the deadline arrives.'],
                ['type' => 'paragraph', 'text' => 'A teacher who records a test the week it is sat spends a few minutes. The same teacher recording a term of tests in the last week spends an evening, under pressure, from notes — which is precisely the condition under which mistakes are made. Moving mark entry to the point of marking does not add work; it redistributes work that was always going to happen, out of the fortnight that cannot absorb it.'],
                ['type' => 'pull', 'text' => 'End-of-term crunch is not a reporting problem. It is a collection problem that has been deferred for eleven weeks.'],
                ['type' => 'paragraph', 'text' => 'This is a management decision, not a technical one. It needs the head of department to say that continuous entry is expected, and it needs mark entry to be quick enough on the day that the expectation is reasonable — including on a phone, in a classroom, without a reliable connection.'],

                ['type' => 'heading', 'text' => 'Compute what can be computed'],
                ['type' => 'paragraph', 'text' => 'Weightings, grade boundaries, subject averages and class positions are arithmetic. They are also where most published errors originate, because arithmetic done by hand across hundreds of learners will produce mistakes no matter how careful the person is.'],
                ['type' => 'paragraph', 'text' => 'Configure the rules once — per subject, per stage — and let them run. The rules are the part that deserves scrutiny at the start of the year; the arithmetic afterwards does not need reviewing, only the inputs do. A school that reviews its grade boundaries carefully in January and never recalculates a total by hand is in a better position than one that checks every total in November.'],

                ['type' => 'heading', 'text' => 'Keep the sign-off, drop the transcription'],
                ['type' => 'paragraph', 'text' => 'Automating the arithmetic sometimes raises a fear that professional judgement is being automated too. It is not, and it should not be. Two things must stay human:'],
                ['type' => 'list', 'items' => [
                    'The comment. A generated remark that says nothing is worse than no remark. The value of a report card to a parent lives almost entirely in the sentence a form tutor writes.',
                    'The release. Results should be held until a named person has looked at them and signed them off. A system that publishes automatically will eventually publish something that should have been questioned.',
                ]],
                ['type' => 'paragraph', 'text' => 'What disappears is the retyping between the mark book and the report card — the step that produced errors without adding judgement.'],

                ['type' => 'heading', 'text' => 'Print in your own format, in one batch'],
                ['type' => 'paragraph', 'text' => 'Schools are rightly attached to their report card format. It carries the crest, the house system, the subject ordering and the language the community recognises. A reporting cycle that requires abandoning it is trading one problem for another.'],
                ['type' => 'paragraph', 'text' => 'The format should be configured once and then generated for the whole cohort in a single run. If producing report cards involves opening a document per learner, the cycle cannot compress below a week regardless of what else is fixed.'],

                ['type' => 'heading', 'text' => 'What a one-day cycle looks like'],
                ['type' => 'paragraph', 'text' => 'When collection has happened continuously and calculation is automatic, the end of term looks like this: marks are already in; the head of department reviews subject results and queries anything anomalous; form tutors write comments; the head of school reviews and signs off; the batch is generated and released.'],
                ['type' => 'paragraph', 'text' => 'That is a day of concentrated, senior work — which is what reporting was supposed to be. The three weeks were never the reporting. They were the cost of not collecting as you went.'],

                ['type' => 'heading', 'text' => 'What to do this term'],
                ['type' => 'paragraph', 'text' => 'You do not need a new system to start. Pick one department and require entry within a week of marking. Write down your grade boundaries and weightings and check they are what you actually use. Count how many times a mark is typed between the script and the report card, and remove one of those steps. Then measure the next cycle against this one.'],
            ],
        ],

        [
            'slug' => 'what-every-board-should-ask-before-approving-an-sis',
            'kind' => 'Checklist',
            'reading_time' => '5 min',
            'published_at' => '2026-06-04',
            'author' => 'The Heritage Pro team',
            'title' => 'What every board should ask before approving an SIS',
            'standfirst' => 'A student information system is a ten-year commitment approved in a forty-minute agenda item. These are the questions that separate a sound decision from an expensive one — including the ones we would rather you asked us.',
            'image' => 'images/website/students-laptop.webp',
            'image_alt' => 'Two learners working together on a laptop',
            'body' => [
                ['type' => 'paragraph', 'text' => 'Boards approve school information systems on the strength of a demonstration and a price. Both are the wrong things to weigh heavily. A demonstration shows the software working on the vendor\'s data, and the price shown is rarely the price paid in year three.'],
                ['type' => 'paragraph', 'text' => 'These are the questions worth the agenda time. We sell an SIS, so treat this as interested advice — but a vendor who cannot answer these plainly is telling you something.'],

                ['type' => 'heading', 'text' => '1. Who owns the data, and how do we get it out?'],
                ['type' => 'paragraph', 'text' => 'The institution should own its records outright, and should be able to export them in full — including historical results and financial history — at any time, without a fee and without asking. Ask specifically what happens on the day you leave. An answer involving a support ticket and a quotation is a warning.'],

                ['type' => 'heading', 'text' => '2. What happens at the end of term?'],
                ['type' => 'paragraph', 'text' => 'Term end is when a school system either earns its keep or costs three weeks of evenings. Ask to see reporting done on a full cohort, in the school\'s own report card format, from mark entry through to release — not a single sample record.'],

                ['type' => 'heading', 'text' => '3. Which of our current systems does this replace?'],
                ['type' => 'paragraph', 'text' => 'Name them. If the answer is “none, it sits alongside”, the school is buying a new system and keeping the old ones, and the reconciliation work between them stays. Consolidation is where the return on an SIS comes from; a system that adds a database rather than removing several rarely pays for itself.'],

                ['type' => 'heading', 'text' => '4. Who does the migration, and what happens to history?'],
                ['type' => 'paragraph', 'text' => 'Migration is the single most underestimated line in any implementation. Establish who does the work, whether historical results and outstanding balances come across or only current-year data, and who signs off that the migrated figures reconcile. The bursar should be the one to confirm the opening balances, before go-live, in writing.'],

                ['type' => 'heading', 'text' => '5. What does this cost in year three?'],
                ['type' => 'paragraph', 'text' => 'Ask for the full three-year figure, including implementation, training, hosting, support and the cost of adding learners as the school grows. Then ask what is not included. Modules licensed separately, per-message charges and paid upgrades are legitimate — but they belong in the board paper, not in a later invoice.'],

                ['type' => 'heading', 'text' => '6. Who answers the phone in examination week?'],
                ['type' => 'paragraph', 'text' => 'Support quality is invisible during procurement and decisive afterwards. Ask whether the institution gets a named contact, what the hours are during term start and examinations, and who is on the other end — the people who built the system, or a tier that will forward the question to them.'],

                ['type' => 'heading', 'text' => '7. Does it work when the connection does not?'],
                ['type' => 'paragraph', 'text' => 'A system that assumes reliable connectivity will fail in the places and at the moments that matter — a register in a classroom, mark entry during a power cut. Ask what is usable offline, what happens to work done while disconnected, and how conflicts are resolved when the connection returns.'],

                ['type' => 'heading', 'text' => '8. What does the board itself see?'],
                ['type' => 'paragraph', 'text' => 'Governors need enrolment, attainment, attendance and fee collection, current and comparable term to term, without asking a member of staff to prepare it. If producing the board pack is a manual job, the board will keep receiving numbers that are a fortnight old and impossible to interrogate in the meeting.'],

                ['type' => 'pull', 'text' => 'The decisive question is not what the system can do. It is what the school will stop doing once it is in place.'],

                ['type' => 'heading', 'text' => 'One last test'],
                ['type' => 'paragraph', 'text' => 'Ask for two institutions of comparable size and type, and speak to them without the vendor present. Ask those schools what the implementation was like, what broke, and what they would do differently. Vendors choose their references carefully, but the conversation is still worth more than the demonstration.'],
            ],
        ],

    ],
];
