# Client Setup Wizard — Phase 0 Synthetic UAT Submission

## Purpose

This is a synthetic, non-production submission for testing the Phase 0 field catalogue, academic readiness rules, conditional behavior and CRM review presentation. It contains no real person, institution or student information.

## Expected outcome

```text
academic_ready = true
academic_status = submitted
overall_status = academic_submitted
finance_status = deferred
migration_status = no_migration
```

The sample exercises repeatable campuses, contacts, departments, programmes, periods, modules, assessment components, grade bands, progression rules, classifications and attachments. It also exercises practical hours, deferred finance, excluded migration and a later integration.

## Core submission data

| Field | Synthetic value |
|---|---|
| `institution_legal_name` | Francistown College of Applied Studies (Synthetic) |
| `institution_common_name` | FCAS Synthetic |
| `registration_number` | SYN-REG-0001 |
| `accreditation_body` | Synthetic Higher Education Council |
| `provider_number` | SYN-PROVIDER-01 |
| `ownership_type` | private_not_for_profit |
| `institution_categories` | professional_training_college |
| `awards_offered` | certificate, diploma |
| `target_go_live` | 2027-01 |
| `academic_year_pattern` | calendar_year |
| `academic_year_naming` | 2027 |
| `semesters_per_year` | 2 |
| `primary_intakes` | January and July |
| `delivery_modes` | full_time, on_campus, online |
| `structure_varies_by_campus` | false |
| `authorized_submitter_confirmed` | true |
| `privacy_requirements_acknowledged` | true |

## Repeatable groups

### Campuses

| Code | Name | Location | Programmes | Active |
|---|---|---|---|---|
| FCAS-MAIN | Synthetic Main Campus | Francistown test district | DIP-IS | Yes |
| FCAS-ONLINE | Synthetic Online Delivery | Online delivery | DIP-IS | Yes |

### Responsible contacts

| Role | Name | Position | Email | Telephone |
|---|---|---|---|---|
| Registrar | Kago Example | Synthetic Registrar | registrar@example.invalid | +267 7000 0001 |
| Academic lead | Mpho Example | Synthetic Academic Lead | academic@example.invalid | +267 7000 0002 |

### Faculties and departments

| Faculty code | Faculty name | Department code | Department name | Owner |
|---|---|---|---|---|
| FAS | Faculty of Applied Studies | DSE | Department of Synthetic Information Systems | Academic Lead |

### Academic periods

| Period | Label | Start | End | Teaching weeks | Exam weeks | Results release |
|---|---|---|---|---:|---:|---|
| SEM-1 | Semester 1 | 2027-01-18 | 2027-06-25 | 16 | 2 | 2027-07-16 |
| SEM-2 | Semester 2 | 2027-07-19 | 2027-12-03 | 16 | 2 | 2027-12-17 |

## Programme and curriculum

### Programme

| Field | Synthetic value |
|---|---|
| Code | `DIP-IS` |
| Name | Diploma in Synthetic Information Systems |
| Award type | Diploma |
| NQF level | 6 |
| Duration | 2 years / 4 semesters |
| Campuses | FCAS-MAIN, FCAS-ONLINE |
| Faculty/department | FAS / DSE |
| Active intakes | January, July |
| Entry requirements | Senior secondary completion with English and Mathematics, or approved equivalent; mature entry by interview. |
| Completion requirements | Compulsory modules, 240 credits, CGPA at least 2.00, supervised practical requirement and no unresolved academic hold. |

### Curriculum version and modules

| Curriculum | Effective from | Total credits |
|---|---|---:|
| `DIP-IS-2027` — Diploma in Synthetic Information Systems 2027 | 2027-01-01 | 240 |

| Code | Title | Year | Semester | Credits | Core/elective | Prerequisite |
|---|---|---|---|---:|---|---|
| SIS101 | Synthetic Information Systems Foundations | Year 1 | SEM-1 | 60 | Core | None |
| DAT101 | Responsible Data Practice | Year 1 | SEM-1 | 60 | Core | None |
| SYS201 | Applied Systems Operations | Year 2 | SEM-1 | 60 | Core | SIS101 |
| ELE201 | Applied Systems Elective | Year 2 | SEM-1 | 60 | Elective | None |

Elective pool `SIS-ELECTIVE-1` requires Year 2 students to select one approved 60-credit elective.

Practical requirement: 120-hour internship, supervisor and academic assessor sign-off, does not block progression but does block graduation.

## Assessment, grading and GPA

### Assessment pattern

| Component | Marked out of | Weight | Minimum | Compulsory | Result slip |
|---|---:|---:|---:|---|---|
| Continuous assessment | 100 | 40% | 40 | Yes | Yes |
| Final examination | 100 | 60% | 40 | Yes | Yes |

Module formula: `Final mark = CA mark * 0.40 + examination mark * 0.60`; round to the nearest whole number using standard half-up rounding.

Pass conditions: final mark at least 50, examination mark at least 40 and CA mark at least 40. Missing marks become `incomplete`. Results follow lecturer entry, department check, examinations moderation, academic board approval and registrar publication.

### GPA and grade bands

| Field | Synthetic value |
|---|---|
| GPA maximum | 4.0 |
| Decimal places | 2 |
| SGPA | Yes |
| CGPA | Yes |
| Method | Credit-weighted grade points |
| Timing | Per semester |
| Rounding | Standard |
| SGPA formula | Sum(grade points × module credits) / sum(included credits) |
| CGPA formula | Sum(all included grade points × credits) / sum(all included credits) |
| Failed attempts | Count in GPA |
| Transferred credits | Excluded from GPA |
| Incomplete/deferred | Excluded until resolved |
| Repeated credits | Awarded once |

| Minimum | Maximum | Grade | Points | Status |
|---:|---:|---|---:|---|
| 80 | 100 | A | 4.0 | Distinction |
| 70 | 79 | B | 3.0 | Pass |
| 60 | 69 | C | 2.0 | Pass |
| 50 | 59 | D | 1.0 | Pass |
| 0 | 49 | F | 0.0 | Fail |

## Progression and graduation

Progression is evaluated after every semester. Rule precedence is: unresolved incomplete results; unmet prerequisites/practicals; maximum attempts; compulsory failures; GPA/credit rules; normal progression.

| Code | Condition | Student label | May register next semester? |
|---|---|---|---|
| `INCOMPLETE` | At least one unresolved incomplete result | Academic decision pending | No |
| `PROCEED` | At least 60 credits and CGPA at least 2.00 | Proceed | Yes |
| `REVIEW` | Fewer than 60 credits or CGPA below 2.00 | Academic review required | No |

Retake policy: maximum three attempts per module; academic board approval after the second failure; no retake after a pass; supplementary assessment may be approved by the examinations lead; maximum two carried modules.

Graduation gates: all compulsory modules, elective requirements, 240 credits, CGPA at least 2.00, practical hours, no unresolved deferred results and academic board approval.

| Code | Label | Minimum CGPA | Maximum CGPA |
|---|---|---:|---:|
| DISTINCTION | Distinction | 3.50 | 4.00 |
| MERIT | Merit | 3.00 | 3.49 |
| PASS | Pass | 2.00 | 2.99 |

## Results and student lifecycle

Result-slip requirements include institution, campus, student, programme, level, year/semester, modules, credits, CA mark, examination mark, final mark, grade, grade points, status, GPA, progression, grade legend, issue date, signature and verification code.

Transcript requirements include all semesters, every attempt, repeated attempts, transferred credits, semester GPA, CGPA, credits attempted/earned, final classification, award title/date, grading legend, signature and verification feature.

Admissions flow: `draft -> submitted -> verified -> screened -> offered -> accepted -> enrolled`.

Student number format: `SYN-YYYY-NNNN`. Applicant number format: `APP-SYN-YYYY-NNNN`. Duplicate detection uses ID number, email or telephone, then name plus date of birth when those identifiers are unavailable.

Lifecycle statuses: applicant, admitted, registered, active, deferred, withdrawn, completed and graduated.

## Migration, integrations and finance

| Area | Synthetic choice | Expected readiness behavior |
|---|---|---|
| Migration | No migration | Do not require staff/student uploads |
| Email | Synthetic Mail Service, initial priority | Save and show to CRM; no academic block |
| LMS | Synthetic Learning Platform, later priority | Save and show to CRM; no academic block |
| User roles | Registrar and lecturer roles supplied | Informational for academic readiness |
| Finance | Defer all finance configuration | Require deferred owner and target date only |

Finance deferred owner: Synthetic Finance Lead. Target finance discovery: 2027-03.

## Evidence and sign-off

Required synthetic evidence:

- `synthetic-academic-calendar.pdf`
- `synthetic-assessment-policy.pdf`
- `synthetic-grading-policy.pdf`
- `synthetic-progression-policy.pdf`
- `synthetic-result-slip.pdf`
- `synthetic-transcript.pdf`

Sign-off values:

| Field | Value |
|---|---|
| Authorized representative | Synthetic Authorized Representative |
| Position | Synthetic Principal |
| Confirmation date | 2026-08-11 |
| Sign-off confirmation | `true` |

## UAT assertions

- [ ] All blocking academic gates return complete.
- [ ] `academic_ready` becomes true.
- [ ] Finance can be deferred without readiness errors.
- [ ] No-migration selection removes staff/student upload blockers.
- [ ] Integration records save without blocking academic submission.
- [ ] Programme, curriculum, module, grade-band and progression references resolve.
- [ ] Assessment weights total 100%.
- [ ] Grade-band ranges do not overlap.
- [ ] Progression rule codes are unique.
- [ ] Classification ranges do not overlap.
- [ ] Required evidence is shown to the CRM reviewer.
- [ ] Academic submission changes status to `academic_submitted`.
- [ ] Optional finance and integration work remains editable after academic submission.

