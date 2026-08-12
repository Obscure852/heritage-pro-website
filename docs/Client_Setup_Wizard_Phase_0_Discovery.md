# Client Setup Wizard — Phase 0 Discovery and Field Mapping

## Phase status

| Item | Result |
|---|---|
| Phase | P0 — Discovery and field mapping |
| Status | Complete — baseline ready for stakeholder validation |
| Completed on | 2026-08-11 |
| Source questionnaire | `College_System_Initial_Setup_Questionnaire.docx` |
| Source staff workbook | `import-staff-colleges.xlsx` |
| Source student workbook | `import-students-colleges.xlsx` |
| Primary implementation plan | `docs/Client_Setup_Wizard_Implementation_Plan.md` |

This document is the Phase 0 output. It converts the Word questionnaire and the two college import workbooks into an implementable wizard baseline.

The baseline is intentionally explicit about the recommended defaults. Items that still need the product or implementation owner’s confirmation are listed in the final decision register; they do not prevent engineering from beginning the foundation work.

## 1. Evidence reviewed

### 1.1 Questionnaire structure

The questionnaire contains:

- 15 numbered discovery sections.
- Institution identity, scope and submission guidance.
- An overall readiness tracker.
- Repeatable campus, contact, department, programme, curriculum, module, assessment, progression, classification, migration, integration, user-role and attachment sections.
- Required academic policies and redacted result/transcript samples.
- Optional or deferable finance configuration.
- Open decisions, assumptions and authorized sign-off.

The web workflow must preserve the questionnaire’s information coverage without preserving its Word-document interaction model. In particular, “duplicate Section 5” must become an “Add programme” interaction and blank response tables must become validated repeatable rows.

### 1.2 Staff workbook

Source sheet: `Staff Import`

Source columns:

```text
firstname
middlename
lastname
email
date_of_birth
gender
position
phone
id_number
nationality
city
address
active
status
department_name
roles
reporting_to_email
start_year
username
```

The source contains 30 populated synthetic rows. The populated workbook should be treated as a sample/test fixture, not as the production download. Production delivery requires a clean, versioned template with instructions and no test identities.

### 1.3 Student workbook

Source sheet: `Students Import`

Source columns:

```text
first_name
last_name
other_names
student_number
gender
date_of_birth
nationality
id_number
email
phone
status
program_code
level
next_of_kin_name
next_of_kin_email
next_of_kin_phone
```

The source contains 40 populated synthetic rows. It should also be converted into a clean versioned template before being made available to clients.

## 2. Requirement classification

Every wizard field must have one of these requirement classes:

| Code | Meaning | Client behavior | Submission behavior |
|---|---|---|---|
| `R` | Required | Must be completed to progress or submit the relevant stage | Blocks the relevant submission |
| `C` | Conditional | Becomes required only when its parent feature/answer is selected | Blocks only when the condition applies |
| `O` | Optional | Can be left empty | Never blocks submission |
| `E` | Evidence | Attachment or supporting document requested for verification | Blocks only when the related feature is selected as in-scope |
| `D` | Deferred choice | Client explicitly chooses to defer or exclude the feature | The explicit defer/exclude answer satisfies the requirement |
| `I` | Informational | Helps implementation but is not required for submission | Never blocks submission |

The application must store the requirement class with the field definition, not only in the Blade template. This allows the readiness service and CRM review workspace to use the same rules as the public wizard.

## 3. Final stage map

| Stage key | Client-facing label | Source sections | Default class | Academic submission impact |
|---|---|---|---|---|
| `scope` | Welcome and scope | Scope, completion method and submission package | `R` | Required to create a meaningful draft |
| `institution` | Institution and academic foundation | Sections 1–3 | `R` | Blocking |
| `programmes` | Programme portfolio | Sections 4–5 | `R` | Blocking |
| `curriculum` | Curriculum and modules | Section 6 | `R/C` | Blocking when curriculum data is in scope |
| `assessment` | Assessment, grading and GPA | Sections 7–8 | `R` | Blocking |
| `progression` | Progression, retakes and graduation | Sections 9–10 | `R` | Blocking |
| `results_lifecycle` | Results, transcripts and student lifecycle | Sections 11–12 | `R/C` | Blocking for the selected student lifecycle scope |
| `migration` | Migration and data readiness | Section 13.1 | `C/O` | Does not block if “no migration” is selected |
| `integrations_access` | Integrations, users and access | Sections 13.2–13.3 | `C/O` | Does not block academic submission |
| `finance` | Finance | Section 14 | `D/O` | Never blocks academic submission |
| `evidence_signoff` | Evidence and sign-off | Section 15 | `R/C/E` | Required evidence blocks the related scope; sign-off blocks final completion |

The wizard should use these stable keys internally even if the labels or display order change later.

## 4. Field catalogue

The tables below define the Phase 0 baseline. Field keys use lower snake case and should become the canonical keys in the saved payload. Repeated groups are represented as arrays of objects.

### 4.1 `scope`

| Field key | Label | Type | Class | Validation/behavior |
|---|---|---|---|---|
| `institution_legal_name` | Legal institution name | Text | R | 2–180 characters |
| `institution_common_name` | Trading/common name | Text | O | 2–180 characters |
| `response_version` | Response version | Text | I | Default `1.0` |
| `prepared_by_name` | Prepared by | Text | R | Must identify the submitting person |
| `prepared_by_position` | Prepared by position | Text | R |  |
| `submission_date` | Submission date | Date | R | Cannot be in the future |
| `target_go_live` | Target go-live period | Month/date | R | Month and year acceptable |
| `submission_method` | Submission method | Enum/text | I | Online wizard is the default; retain for migration history |
| `secure_transfer_method` | Secure file-transfer method | Text | C | Required only if the client will send files outside the wizard |
| `authorized_submitter_confirmed` | Authorized submitter confirmation | Boolean | R | Must be true |
| `privacy_requirements_acknowledged` | Privacy and redaction confirmation | Boolean | R | Must be true before attachments or data extracts are uploaded |

### 4.2 `institution`

| Field key | Label | Type | Class | Validation/behavior |
|---|---|---|---|---|
| `registration_number` | Registration number | Text | R | Preserve leading zeros |
| `accreditation_body` | Accreditation body | Text | R |  |
| `provider_number` | Provider number | Text | R |  |
| `ownership_type` | Ownership | Enum | R | Public, private for profit, private not for profit, faith-based, community/trust, other |
| `ownership_other` | Other ownership description | Text | C | Required when ownership is `other` |
| `institution_categories` | Institution category | Multi-select | R | At least one selection |
| `awards_offered` | Awards offered | Multi-select | R | Certificate, diploma, degree, short course and other options |
| `mandate_description` | Mandate, specialization and target population | Long text | R | Minimum 20 characters |
| `campuses` | Campuses and delivery sites | Repeatable group | R | At least one active campus |
| `campuses[].code` | Campus/site code | Text | R | Unique within submission |
| `campuses[].name` | Campus/site name | Text | R |  |
| `campuses[].physical_location` | Physical location | Text | R |  |
| `campuses[].programmes_offered` | Programmes offered | Text/list | C | Required when campus differs by programme |
| `campuses[].active` | Active campus | Boolean | R | At least one active campus |
| `branding.slogan` | Official slogan | Text | O |  |
| `branding.primary_colour` | Primary brand colour | Text | O | HEX/RGB/CMYK accepted |
| `branding.secondary_colour` | Secondary brand colour | Text | O | HEX/RGB/CMYK accepted |
| `branding.sms_sender` | SMS sender/signature | Text | O |  |
| `branding.email_signature` | Email signature wording | Long text | O |  |
| `branding.academic_board_wording` | Academic board wording | Text | O |  |

`campuses` is the first required repeatable group. The UI must provide an initial row, “Add campus”, edit, remove and duplicate actions.

### 4.3 Academic contacts and organisation

| Field key | Label | Type | Class | Validation/behavior |
|---|---|---|---|---|
| `responsible_contacts` | Responsible contacts | Repeatable group | R | At least one primary contact and one academic owner |
| `responsible_contacts[].role` | Contact role | Enum/text | R | Executive sponsor, registrar, academic lead, examinations lead, admissions lead, IT/data lead, finance lead, other |
| `responsible_contacts[].name` | Name | Text | R |  |
| `responsible_contacts[].position` | Position | Text | R |  |
| `responsible_contacts[].email` | Email | Email | R | Valid email |
| `responsible_contacts[].telephone` | Telephone | Text | R | Preserve country code |
| `academic_structure` | Academic structure description | Long text | R | Describe faculty/school → department → programme → level → semester → module |
| `structure_varies_by_campus` | Structure varies by campus | Boolean | R | Controls exception fields |
| `campus_structure_differences` | Campus-specific differences | Long text | C | Required when structure varies |
| `campus_difference_approval_authority` | Approving authority | Text | C | Required when structure varies |
| `faculties_departments` | Faculties and departments | Repeatable group | R | At least one row when the institution uses departments |
| `faculties_departments[].faculty_code` | Faculty/school code | Text | R | Unique within submission |
| `faculties_departments[].faculty_name` | Faculty/school name | Text | R |  |
| `faculties_departments[].department_code` | Department code | C | C | Required if departments are coded |
| `faculties_departments[].department_name` | Department name | C | C | Required if departments are used |
| `faculties_departments[].head_owner` | Head/owner | Text | O |  |

### 4.4 `institution` — academic calendar and delivery

| Field key | Label | Type | Class | Validation/behavior |
|---|---|---|---|---|
| `academic_year_pattern` | Academic year pattern | Enum | R | Calendar, split year, rolling intake, block/trimester, other |
| `academic_year_pattern_other` | Other year pattern | Text | C | Required when other is selected |
| `academic_year_naming` | Academic year naming | Text | R | Example: `2026` or `2026/2027` |
| `academic_year_start` | Academic year start | Date/day | R |  |
| `academic_year_end` | Academic year end | Date/day | R | Must follow start |
| `semesters_per_year` | Semesters per year | Integer | R | Positive integer |
| `primary_intakes` | Primary intakes | Text/list | R | Months or dates |
| `markbook_lock_rule` | Markbook lock rule | Long text | R | Date or days after semester |
| `academic_periods` | Academic periods | Repeatable group | R | At least one period |
| `academic_periods[].period` | Period number/code | Text | R |  |
| `academic_periods[].official_label` | Official label | Text | R |  |
| `academic_periods[].start_date` | Start date | Date | R |  |
| `academic_periods[].end_date` | End date | Date | R | After start date |
| `academic_periods[].teaching_weeks` | Teaching weeks | Integer | R | Non-negative integer |
| `academic_periods[].exam_weeks` | Exam weeks | Integer | R | Non-negative integer |
| `academic_periods[].results_release` | Results release | Date/text | R | Date or rule |
| `delivery_modes` | Teaching/attendance modes | Multi-select | R | Full-time, part-time, campus, online, blended, distance, evening/weekend, block, clinical/practical |
| `registration_rules` | Add/drop and registration rules | Long text | R | Include late registration, extension and closure |

### 4.5 `programmes`

| Field key | Label | Type | Class | Validation/behavior |
|---|---|---|---|---|
| `portfolio_rules_shared` | Portfolio-wide rules | Multi-select | R | Grading, GPA, progression, retake, assessment, calendar, result slip, transcript |
| `portfolio_exceptions` | Programme-specific exceptions | Repeatable group | C | Required when not all rules are shared |
| `portfolio_exceptions[].programme_code` | Programme code | Text | C | Required with exception |
| `portfolio_exceptions[].rule_area` | Rule area | Enum/text | C |  |
| `portfolio_exceptions[].exception` | Exception | Long text | C |  |
| `portfolio_exceptions[].effective_date` | Effective date | Date | C |  |
| `portfolio_exceptions[].approving_authority` | Approving authority | Text | C |  |
| `programmes` | Programme register | Repeatable group | R | At least one active programme |
| `programmes[].code` | Programme code | Text | R | Unique within submission |
| `programmes[].name` | Programme name | Text | R |  |
| `programmes[].approved_title` | Full approved title | Text | R |  |
| `programmes[].award_type` | Award/type | Enum/text | R | Certificate, diploma, degree, other |
| `programmes[].nqf_level` | NQF level | Text/number | R |  |
| `programmes[].duration` | Duration | Number/text | R | Years and/or semesters |
| `programmes[].mode` | Mode | Enum/text | R | Full-time, part-time, online, blended, etc. |
| `programmes[].campus` | Campus/site | Text/list | R | Must reference an active campus |
| `programmes[].active_intakes` | Active/intake | Text/list | R |  |
| `programmes[].faculty` | Faculty/school owner | Text | R | Must reference declared structure where applicable |
| `programmes[].department` | Department owner | Text | C | Required when departments are used |
| `programmes[].purpose` | Programme purpose and outcomes | Long text | R |  |
| `programmes[].entry_requirements` | Minimum entry requirements | Long text | R | Qualifications, subjects, grades/points, age, experience, portfolio, interview, language and exemptions |
| `programmes[].completion_requirements` | Graduation/completion requirements | Long text | R | Credits, compulsory modules, GPA, practical work, holds and maximum duration |

### 4.6 `curriculum`

| Field key | Label | Type | Class | Validation/behavior |
|---|---|---|---|---|
| `curriculum_in_scope` | Curriculum configuration in scope | Boolean | R | If false, require migration/implementation explanation |
| `curriculum_versions` | Curriculum versions | Repeatable group | C | Required when curriculum is in scope |
| `curriculum_versions[].programme_code` | Programme code | Text | C | Must match programme register |
| `curriculum_versions[].code` | Curriculum version code | Text | C | Unique per programme |
| `curriculum_versions[].name` | Curriculum version name | Text | C |  |
| `curriculum_versions[].effective_from` | Effective from | Date | C |  |
| `curriculum_versions[].effective_to` | Effective to | Date | O | Must follow effective from |
| `curriculum_versions[].total_credits` | Total credits | Number | C | Non-negative |
| `curriculum_versions[].modules` | Modules | Repeatable nested group | C | At least one module when in scope |
| `modules[].code` | Module code | Text | C | Unique within curriculum version |
| `modules[].title` | Module title | Text | C |  |
| `modules[].year_level` | Year/level | Text | C |  |
| `modules[].semester` | Semester | Text | C |  |
| `modules[].credits` | Credits | Number | C | Positive or zero for non-credit modules |
| `modules[].core_elective` | Core/elective | Enum | C | Core, elective, compulsory non-credit, other |
| `modules[].prerequisite` | Prerequisite/co-requisite | Text/list | O |  |
| `modules[].theory_practical_hours` | Theory/practical hours | Text/number | O |  |
| `elective_pools` | Elective pools | Repeatable group | C | Required when electives are used |
| `elective_pools[].code` | Pool code | Text | C |  |
| `elective_pools[].name` | Pool name | Text | C |  |
| `elective_pools[].eligible_students` | Eligible students | Long text | C |  |
| `elective_pools[].modules` | Modules in pool | Text/list | C |  |
| `elective_pools[].selection_rule` | Selection rule | Long text | C | Number/credits and restrictions |
| `practical_requirements` | Practical/clinical requirements | Repeatable group | C | Required when practical activity exists |
| `practical_requirements[].type` | Requirement type | Enum/text | C | Laboratory, clinical, internship, teaching practice, community service, research, other |
| `practical_requirements[].hours_credits` | Required hours/credits | Number/text | C |  |
| `practical_requirements[].completion_point` | When completed | Text | C |  |
| `practical_requirements[].pass_rule` | Pass/sign-off rule | Long text | C |  |
| `practical_requirements[].blocks_progression` | Blocks progression | Boolean | C |  |
| `practical_requirements[].blocks_graduation` | Blocks graduation | Boolean | C |  |

### 4.7 `assessment`

| Field key | Label | Type | Class | Validation/behavior |
|---|---|---|---|---|
| `assessment_types` | Assessment categories used | Multi-select | R | CA, assignments, tests, quizzes, projects, practical, clinical, portfolio, presentation, exam and other |
| `assessment_components` | Assessment components | Repeatable group | R | At least one component |
| `assessment_components[].category` | Category | Text | R |  |
| `assessment_components[].sequence` | Typical component/sequence | Text | R |  |
| `assessment_components[].marked_out_of` | Marked out of | Number | R | Positive number |
| `assessment_components[].weight_percent` | Weight percentage | Number | R | Total weights must equal 100 within configured tolerance |
| `assessment_components[].minimum_mark` | Minimum component mark | Number/text | O |  |
| `assessment_components[].compulsory` | Compulsory | Boolean | R |  |
| `assessment_components[].shown_on_result_slip` | Shown on result slip | Boolean | R |  |
| `module_mark_formula` | Final module mark formula | Long text | R | Must explicitly describe weights and inputs |
| `module_pass_conditions` | Module pass conditions | Multi-select/long text | R | Overall, exam, CA, component, practical and attendance rules |
| `default_module_pass_mark` | Default module pass mark | Number | R | 0–100 |
| `examination_subminimum` | Examination sub-minimum | Number/text | O |  |
| `ca_subminimum` | CA sub-minimum | Number/text | O |  |
| `rounding_rule` | Rounding precision and method | Text | R |  |
| `attendance_threshold` | Attendance threshold | Number/text | C | Required when attendance is a pass condition |
| `missing_mark_outcome` | Missing mark outcome | Enum/text | R | Incomplete, zero, deferred, other |
| `result_approval_mode` | Official semester decision mode | Enum | R | Automatic, draft then board, manual, varies by programme |
| `mark_workflow` | Mark entry/moderation/approval workflow | Long text | R | Roles, order, deadlines and reopen rules |

### 4.8 `gpa`

| Field key | Label | Type | Class | Validation/behavior |
|---|---|---|---|---|
| `gpa_scale_maximum` | GPA scale maximum | Number | R | Positive number |
| `gpa_decimal_places` | GPA decimal places | Integer | R | 0–4 recommended |
| `semester_gpa_calculated` | Semester GPA calculated | Boolean | R |  |
| `cumulative_gpa_calculated` | Cumulative GPA calculated | Boolean | R |  |
| `gpa_calculation_timing` | Calculation timing | Enum/text | R | Semester, year, on demand, other |
| `gpa_rounding_method` | GPA rounding method | Enum/text | R | Standard, floor, ceiling, other |
| `gpa_method` | GPA method | Enum/text | R | Credit-weighted, unweighted, percentage, selected credits, other |
| `semester_gpa_formula` | SGPA formula | Long text | R | Explicit numerator, denominator, weighting and exclusions |
| `cumulative_gpa_formula` | CGPA formula | Long text | R | Explain semesters, repeats, transfers and historical results |
| `grade_bands` | Grade bands | Repeatable group | R | At least one band |
| `grade_bands[].minimum_mark` | Minimum mark | Number | R | 0–100 or configured scale |
| `grade_bands[].maximum_mark` | Maximum mark | Number | R | Must not overlap another band |
| `grade_bands[].letter_grade` | Letter/grade | Text | R |  |
| `grade_bands[].grade_points` | Grade points | Number/text | R |  |
| `grade_bands[].result_status` | Result status | Text | R |  |
| `grade_bands[].description` | Meaning | Text | O |  |
| `gpa_inclusion_rules` | GPA inclusion/exclusion rules | Multi-select/long text | R | Failed, exempted, transferred, pass/fail, incomplete, deferred, withdrawn and non-credit treatment |
| `gpa_exceptions` | GPA exceptions | Long text | O | Programme-specific rules |

### 4.9 `progression`

| Field key | Label | Type | Class | Validation/behavior |
|---|---|---|---|---|
| `progression_evaluation_timing` | When progression is evaluated | Enum/text | R | Semester, year, programme level, board, varies |
| `progression_inputs` | Progression inputs | Multi-select | R | GPA, credits, failures, practicals, prerequisites, attendance, attempts and other |
| `progression_rules` | Progression decision matrix | Repeatable group | R | At least one rule |
| `progression_rules[].condition` | Condition/trigger | Text | R |  |
| `progression_rules[].threshold` | Exact threshold/rule | Long text | R |  |
| `progression_rules[].decision_code` | Decision code | Text | R | Unique |
| `progression_rules[].student_label` | Student-facing label | Text | R |  |
| `progression_rules[].may_register_next` | May register next semester | Boolean | R |  |
| `progression_rules[].approval_action` | Approval/action | Long text | R |  |
| `progression_rule_precedence` | Rule precedence | Long text | R | Required when rules can overlap |
| `maximum_attempts` | Maximum total attempts | Number/text | R |  |
| `approval_after_attempts` | Approval required after | Number/text | O |  |
| `retake_after_pass` | Retake after pass | Boolean/text | R | Conditions if allowed |
| `retake_mark_cap` | Retake mark cap | Boolean/text | R | Cap value if applicable |
| `supplementary_assessment` | Supplementary assessment | Boolean/text | R | Eligibility |
| `maximum_carried_modules` | Maximum carried modules | Number/text | R |  |
| `repeat_gpa_rule` | Repeat effect on GPA | Enum/long text | R | All, latest, best, first pass, other |
| `retake_operational_rules` | Retake fees/registration/attendance/transcript | Long text | O | Finance details may be deferred |
| `appeal_override_rules` | Appeals and overrides | Long text | R | Authority, reasons, evidence, workflow and audit |

### 4.10 `graduation`

| Field key | Label | Type | Class | Validation/behavior |
|---|---|---|---|---|
| `graduation_gates` | Graduation gates | Multi-select | R | Compulsory/core modules, electives, credits, CGPA, practicals, project, holds, duration, board approval |
| `minimum_graduation_cgpa` | Minimum graduation CGPA | Number/text | R | Value or N/A |
| `required_graduation_credits` | Required credits | Number/text | R | Institution default or programme-specific |
| `maximum_completion_duration` | Maximum completion duration | Text | R |  |
| `award_approval_authority` | Award approval authority | Text | R | Board, senate, registrar or other |
| `classification_bands` | Award classifications | Repeatable group | R | At least one when classifications are used |
| `classification_bands[].code` | Classification code | Text | R |  |
| `classification_bands[].label` | Student-facing label | Text | R |  |
| `classification_bands[].minimum` | Minimum CGPA/mark | Number | R |  |
| `classification_bands[].maximum` | Maximum | Number | R |  |
| `classification_bands[].additional_conditions` | Additional conditions | Long text | O |  |
| `classification_exceptions` | Classification exceptions | Long text | O | First-attempt credits, dissertation, time limit, unresolved failures or board discretion |

### 4.11 `results_lifecycle`

| Field key | Label | Type | Class | Validation/behavior |
|---|---|---|---|---|
| `result_slip_fields` | Result-slip information shown | Multi-select | R | Institution, campus, student, programme, modules, marks, grades, GPA, credits, progression, signatures and security features |
| `result_slip_layout_requirements` | Result-slip layout and wording | Long text | R |  |
| `transcript_fields` | Transcript information shown | Multi-select | R | Attempts, repeats, transfers, exemptions, GPA/CGPA, credits, classification, award, signatures and security features |
| `transcript_requirements` | Transcript wording and security | Long text | R |  |
| `document_availability_rules` | Publication/withholding rules | Multi-select/long text | R | Student view/download, approval, watermark, finance/library holds, corrections and verification |
| `document_retention_reissue_rules` | Correction/reissue/retention rules | Long text | R |  |
| `applicant_routes` | Applicant routes | Multi-select | R | Online, staff-captured, direct, transfer, RPL, international, sponsored, returning |
| `admissions_stages` | Admissions stages/statuses | Long text | R | Include draft through enrolled flow |
| `admission_scoring_rules` | Entry scoring/ranking | Long text | R | Subjects, points, interviews, quotas and tie-breaks |
| `student_number_format` | Student number format | Text | R | Pattern and example |
| `applicant_number_format` | Applicant number format | Text | O | Pattern and example |
| `primary_identity_document` | Primary identity document | Enum/text | R | National ID, passport or other |
| `duplicate_detection_rule` | Duplicate detection rule | Long text | R | ID, email, telephone, name/DOB or combination |
| `official_name_order` | Official name order | Text | R |  |
| `preferred_date_format` | Preferred date format | Text | R |  |
| `student_lifecycle_statuses` | Student lifecycle statuses | Multi-select | R | Applicant, admitted, registered, active, leave, deferred, suspended, withdrawn, discontinued, completed, graduated, alumni, deceased, other |
| `registration_rules` | Registration/module selection rules | Long text | R | Semester registration, add/drop and withdrawal |

### 4.12 `migration`

| Field key | Label | Type | Class | Validation/behavior |
|---|---|---|---|---|
| `migration_scope` | Data to migrate | Multi-select | C | Current applicants, current students, alumni, catalogue, registrations, historical marks, GPA, progression, staff, documents, finance or no migration |
| `migration_datasets` | Migration dataset register | Repeatable group | C | Required unless no migration is selected |
| `migration_datasets[].dataset` | Dataset | Text | C |  |
| `migration_datasets[].source_system` | Source system/file | Text | C |  |
| `migration_datasets[].approximate_records` | Approximate records | Integer/text | C |  |
| `migration_datasets[].years_covered` | Years covered | Text | C |  |
| `migration_datasets[].data_owner` | Data owner | Text | C |  |
| `migration_datasets[].ready_date` | Ready date | Date | O |  |
| `migration_data_quality_issues` | Known data-quality issues | Long text | O | Duplicates, missing codes and historical rules |
| `staff_template_version` | Staff template version used | Text | C | Required when staff file uploaded |
| `student_template_version` | Student template version used | Text | C | Required when student file uploaded |
| `staff_import_file` | Completed staff template | File | C/E | Required when staff migration selected |
| `student_import_file` | Completed student template | File | C/E | Required when student migration selected |

### 4.13 `integrations_access`

| Field key | Label | Type | Class | Validation/behavior |
|---|---|---|---|---|
| `integration_scope` | Integration needs | Multi-select | O | Email, SMS, SSO, LMS, accounting, payment, regulator, library, biometric, none |
| `integrations` | Integration register | Repeatable group | C | Required when any integration is selected |
| `integrations[].system_name` | System name | Text | C |  |
| `integrations[].owner` | System owner | Text | C |  |
| `integrations[].purpose` | Purpose | Long text | C |  |
| `integrations[].method` | Method/API | Text | C |  |
| `integrations[].go_live_priority` | Go-live priority | Enum | C | Initial, later, discovery only |
| `user_roles` | User roles and approvals | Repeatable group | O | Required when user setup is in scope |
| `user_roles[].role` | Role | Text | O |  |
| `user_roles[].approximate_users` | Approximate users | Integer | O | Non-negative |
| `user_roles[].permissions` | Key permissions | Long text | O |  |
| `user_roles[].approval_authority` | Approval authority | Text | O |  |
| `user_roles[].scope` | Campus/programme scope | Text | O |  |
| `access_controls` | Access-control requirements | Multi-select/long text | O | MFA, password policy, campus restrictions, maker-checker, audit retention, auditor role |

### 4.14 `finance`

| Field key | Label | Type | Class | Validation/behavior |
|---|---|---|---|---|
| `finance_scope_decision` | Finance scope decision | Enum | D | Initial go-live, basic fees, defer, external integration |
| `finance_base_currency` | Base currency | Text | C | Required unless finance is deferred |
| `finance_year` | Financial year | Text | C | Required unless finance is deferred |
| `billing_basis` | Billing basis | Enum/text | C | Programme, module, semester, year |
| `invoice_timing` | Invoice timing | Text | C |  |
| `payment_gateway` | Payment gateway | Text | O |  |
| `external_accounting_system` | External accounting system | Text | O |  |
| `finance_deferred_owner` | Deferred discovery owner | Text | C | Required when finance is deferred |
| `finance_deferred_date` | Target finance discovery date | Month/date | C | Required when finance is deferred |
| `finance_capabilities` | Finance capabilities | Multi-select | O | Fees, repeats, instalments, scholarships, sponsors, invoicing, refunds, holds, procurement, GL, reconciliation |
| `finance_registration_result_rules` | Finance rules affecting academic activity | Long text | O | Registration, results or transcripts |

### 4.15 `evidence_signoff`

| Field key | Label | Type | Class | Validation/behavior |
|---|---|---|---|---|
| `attachments` | Attachment register | Repeatable group | R/C/E | Each required attachment has status and file metadata |
| `attachments[].type` | Attachment type | Enum | R/C/E | Based on the source attachment register |
| `attachments[].requirement` | Requirement level | Enum | R/C/E | Required, optional, if migrating, if applicable |
| `attachments[].file` | File | File | C/E | Private storage, type/size/scan validation |
| `attachments[].file_name` | File name/version | Text | I | Auto-populated for upload; editable note only |
| `attachments[].notes` | Notes/owner | Text | O |  |
| `open_decisions` | Open decisions and assumptions | Repeatable group | O |  |
| `open_decisions[].decision` | Decision/assumption | Long text | O |  |
| `open_decisions[].owner` | Owner | Text | O |  |
| `open_decisions[].due_date` | Due date | Date | O |  |
| `open_decisions[].status` | Status | Enum | O | Open, agreed, deferred, closed |
| `authorized_representative_name` | Authorized representative | Text | R |  |
| `authorized_representative_position` | Position | Text | R |  |
| `confirmation_date` | Confirmation date | Date | R |  |
| `signoff_confirmation` | Accuracy/redaction confirmation | Boolean | R | Must be true for final submission |
| `final_comments` | Final comments or conditions | Long text | O |  |

## 5. Repeatable group rules

The following groups must be implemented as structured arrays, not as a single text box:

| Group | Minimum rows | Maximum | Uniqueness/reference rule |
|---|---:|---:|---|
| Campuses | 1 | Configurable | Campus code unique |
| Responsible contacts | 1 | Configurable | At least one primary/academic owner |
| Faculties/departments | 0 or 1 depending on structure | Configurable | Codes unique where used |
| Academic periods | 1 | Configurable | Period labels/codes unique |
| Portfolio exceptions | 0 | Configurable | Programme + rule area unique |
| Programmes | 1 | Configurable | Programme code unique |
| Programme levels | 1 per programme where used | Configurable | Programme + level + semester unique |
| Curriculum versions | 1 per in-scope programme | Configurable | Programme + version code unique |
| Modules | 1 per in-scope curriculum | Configurable | Curriculum + module code unique |
| Elective pools | 0 | Configurable | Pool code unique within curriculum |
| Practical requirements | 0 | Configurable | Programme + requirement type unique where appropriate |
| Assessment components | 1 | Configurable | Component sequence should be stable |
| Grade bands | 1 | Configurable | Mark ranges cannot overlap |
| Progression rules | 1 | Configurable | Decision code unique |
| Classification bands | 0/1 | Configurable | Classification code unique; ranges cannot overlap |
| Migration datasets | 0 unless migration selected | Configurable | Dataset name unique within submission |
| Integrations | 0 unless integration selected | Configurable | System name + purpose should be unique |
| User roles | 0 unless user setup is in scope | Configurable | Role + scope should not duplicate |
| Attachments | Source register | Source register | Attachment type unique |
| Open decisions | 0 | Configurable | No uniqueness requirement |

The application should support safe row deletion with a confirmation step. If a row is referenced by another group, the user must be shown the dependency before deletion.

## 6. Conditional rules

| Rule ID | Trigger | Required response |
|---|---|---|
| C-01 | Ownership is `other` | `ownership_other` |
| C-02 | Academic year pattern is `other` | `academic_year_pattern_other` |
| C-03 | Structure varies by campus | Campus differences and approving authority |
| C-04 | Departments are used | Department names and codes where applicable |
| C-05 | Portfolio rules differ | Programme-specific exception rows |
| C-06 | Curriculum is in scope | Curriculum versions and module register |
| C-07 | Electives are used | Elective pools and selection rules |
| C-08 | Practical/clinical activity exists | Practical requirement rows and evidence where applicable |
| C-09 | Attendance is a pass condition | Attendance threshold |
| C-10 | Rules vary by programme | Programme-specific rule overrides |
| C-11 | Migration is selected | Dataset register and relevant upload/evidence |
| C-12 | Staff migration is selected | Staff template version and staff workbook |
| C-13 | Student migration is selected | Student template version and student workbook |
| C-14 | An integration is selected | Integration register |
| C-15 | User setup is in scope | User-role register |
| C-16 | Finance is deferred | Deferred owner and target discovery date |
| C-17 | Finance is included | Finance scope details |
| C-18 | A feature requires policy evidence | Attachment required before related review/approval |
| C-19 | Final submission is selected | Sign-off confirmation and authorized representative |

Conditional fields must be re-evaluated on every stage save. If a parent answer changes, previously saved dependent answers should be retained as inactive history but excluded from current readiness calculations until the parent is re-enabled.

## 7. Academic readiness definition

The academic readiness service should calculate `academic_ready` only when all applicable gates below pass.

### Gate A — Institution and structure

- [ ] Institution legal identity is complete.
- [ ] Ownership and institution category are complete.
- [ ] Accreditation/provider details are supplied or explicitly marked not applicable with an explanation.
- [ ] At least one active campus exists.
- [ ] At least one responsible academic contact exists.
- [ ] Academic organisation is described.
- [ ] Academic year and periods are complete.
- [ ] Delivery modes and registration rules are complete.

### Gate B — Programme catalogue

- [ ] At least one active programme exists.
- [ ] Every programme has a unique code and approved title.
- [ ] Every programme has award type, level, duration, mode and campus.
- [ ] Entry requirements are supplied.
- [ ] Completion requirements are supplied.
- [ ] Programme-specific exceptions are complete when portfolio rules are not shared.

### Gate C — Curriculum

- [ ] Curriculum is either supplied or explicitly excluded from the initial implementation scope.
- [ ] Every in-scope programme has a curriculum version.
- [ ] Every in-scope curriculum has the required module rows.
- [ ] Core/elective and prerequisite rules are complete where used.
- [ ] Practical/clinical requirements are complete where used.

### Gate D — Assessment and GPA

- [ ] Assessment components exist.
- [ ] Component weights total 100% for each relevant pattern.
- [ ] Module formula is explicit.
- [ ] Module pass conditions are explicit.
- [ ] Missing-mark behavior is explicit.
- [ ] Result approval workflow is described.
- [ ] GPA scale and calculation method are complete.
- [ ] SGPA and CGPA formulas are supplied where those calculations are used.
- [ ] Grade bands do not overlap and cover the configured result range.

### Gate E — Progression and graduation

- [ ] Progression timing and inputs are complete.
- [ ] At least one progression decision rule exists.
- [ ] Rule precedence is supplied where rules overlap.
- [ ] Retake and repeat rules are complete.
- [ ] Graduation gates are complete.
- [ ] Classification bands are complete where classifications are used.

### Gate F — Results and lifecycle

- [ ] Result-slip requirements are complete.
- [ ] Transcript requirements are complete.
- [ ] Publication, withholding, correction and reissue rules are complete.
- [ ] Student/application identifier rules are complete.
- [ ] Admission and lifecycle statuses are complete.
- [ ] Registration and withdrawal rules are complete.

### Non-blocking warnings

The readiness service should show warnings without blocking academic submission for:

- Finance not yet configured.
- Integrations not yet configured.
- Staff/student files not yet uploaded when migration is deferred.
- Optional branding files not yet supplied.
- Open decisions that do not affect the academic configuration.

## 8. Academic and overall status transitions

### 8.1 Academic status

```text
not_started
in_progress
ready
submitted
changes_requested
approved
```

Allowed transitions:

| Current | Action | Next | Actor |
|---|---|---|---|
| `not_started` | Save Stage 0 | `in_progress` | Client |
| `in_progress` | Complete academic gates | `ready` | System |
| `ready` | Submit academic configuration | `submitted` | Client |
| `submitted` | Request academic changes | `changes_requested` | CRM reviewer |
| `changes_requested` | Save requested changes | `in_progress` | Client |
| `submitted` | Approve academic configuration | `approved` | Authorized CRM reviewer |
| `approved` | Reopen with reason | `changes_requested` | Authorized CRM admin |

### 8.2 Overall status

```text
draft
academic_submitted
supplemental_in_progress
complete_submission
under_review
changes_requested
approved
archived
```

The overall status must never be used as a proxy for academic readiness. Both values must be displayed separately in CRM.

## 9. Attachment register baseline

The source questionnaire identifies the following attachment categories:

| Attachment | Default class | Blocks academic submission? | Notes |
|---|---|---:|---|
| Institution registration/licence | E | Yes when required by implementation scope |  |
| Accreditation/provider approval | E | Yes |  |
| Programme accreditation approvals | E | Yes for regulated programmes |  |
| Prospectus/programme handbook | E | Recommended; implementation review may waive |  |
| Programme/module curriculum inventory | E | Yes when curriculum is in scope |  |
| Academic calendar | E | Yes |  |
| Assessment/moderation policy | E | Yes |  |
| Grading scale/GPA policy | E | Yes |  |
| Progression/retake/graduation policy | E | Yes |  |
| Blank/redacted result slip | E | Yes for results setup | Must not contain live student data |
| Blank/redacted complete transcript | E | Yes for transcript setup | Must not contain live student data |
| Official logo and letterhead | O/E | No | Required before branded document production |
| Student/application data extract | C/E | Only when migration selected | Synthetic or protected transfer required |
| Staff/user list and roles | C/E | Only when staff migration selected | Use staff template |
| Fee schedule/finance policy | O/E | No | Required if finance is included |
| Integration/API documentation | C/E | No | Required when integration discovery needs it |

Attachment metadata must include stage, attachment type, original name, stored path, checksum, file size, MIME type, uploader, scan status, review status and notes.

## 10. Template mapping

### 10.1 Staff template

The client-facing template should retain the supplied college-oriented columns but add an instructions sheet and version metadata.

| Source column | Canonical key | Required | Validation/mapping note |
|---|---|---:|---|
| `firstname` | `first_name` | Yes | Text |
| `middlename` | `middle_name` | No | Text |
| `lastname` | `last_name` | Yes | Text |
| `email` | `email` | Yes | Unique valid email |
| `date_of_birth` | `date_of_birth` | No | Accept native Excel date or `DD/MM/YYYY` |
| `gender` | `gender` | No | Controlled value; define accepted codes |
| `position` | `position` | Yes | Text or controlled position |
| `phone` | `phone` | No | Preserve country code |
| `id_number` | `id_number` | No | Preserve leading zeros; sensitive field |
| `nationality` | `nationality` | No | Controlled country value where possible |
| `city` | `city` | No | Text |
| `address` | `address` | No | Text |
| `active` | `active` | Yes | Boolean values `Yes/No` |
| `status` | `employment_status` | Yes | Controlled value; define allowed values |
| `department_name` | `department` | No | Must match submitted academic/user structure where applicable |
| `roles` | `roles` | No | One or more roles; define separator |
| `reporting_to_email` | `reports_to_email` | No | Must resolve to another staff email when used |
| `start_year` | `start_year` | No | Four-digit year |
| `username` | `username` | No | Unique if system account creation is requested |

Important: this workbook is not a direct match for the current CRM `users` import definition. The implementation must use a separate college staff template definition or an explicit adapter. It must not silently map `firstname`/`lastname`, `department_name`, `roles` and `start_year` into the existing CRM users importer without a reviewed mapping.

### 10.2 Student template

| Source column | Canonical key | Required | Validation/mapping note |
|---|---|---:|---|
| `first_name` | `first_name` | Yes | Text |
| `last_name` | `last_name` | Yes | Text |
| `other_names` | `other_names` | No | Text |
| `student_number` | `student_number` | Yes | Unique within uploaded file |
| `gender` | `gender` | No | Controlled value; define accepted codes |
| `date_of_birth` | `date_of_birth` | No | Accept native Excel date or `DD/MM/YYYY` |
| `nationality` | `nationality` | No | Controlled country value where possible |
| `id_number` | `id_number` | No | Preserve leading zeros; sensitive field |
| `email` | `email` | No | Valid email when present |
| `phone` | `phone` | No | Preserve country code |
| `status` | `status` | Yes | Controlled student lifecycle value |
| `program_code` | `program_code` | Yes | Must resolve to programme register |
| `level` | `level` | Yes | Must resolve to programme level where configured |
| `next_of_kin_name` | `next_of_kin_name` | No | Text |
| `next_of_kin_email` | `next_of_kin_email` | No | Valid email when present |
| `next_of_kin_phone` | `next_of_kin_phone` | No | Preserve country code |

The student workbook must not be imported into this CRM as a CRM entity. It should be staged as an implementation migration file until the college project’s academic data model and import process are confirmed.

### 10.3 Template download requirements

Each public template download must include:

- `Instructions` sheet.
- `Data` sheet with exact canonical headers.
- `Allowed Values` sheet where controlled values exist.
- `Version` and template date.
- A note that the workbook must not contain unredacted sample student information unless it is the client’s own authorized data upload.
- Date-format guidance.
- Duplicate-key guidance.
- File upload limits and submission instructions.

## 11. Validation baseline

### Common validation

- All state-changing requests use Form Requests or dedicated validation services.
- Empty strings are normalized consistently.
- Dates are normalized to the application’s canonical format.
- Emails are normalized to lowercase for matching while preserving display values where required.
- Repeatable rows receive stable client-side identifiers before persistence.
- Duplicate codes, duplicate emails and duplicate keys are reported at row/field level.
- Numeric totals use explicit tolerances and never rely on display formatting.

### Academic validation

- Academic period end dates follow start dates.
- Programme codes are unique.
- Curriculum references resolve to declared programmes.
- Module codes are unique within a curriculum version.
- Assessment weights total 100% per assessment pattern.
- Grade bands do not overlap.
- Grade bands have valid minimum/maximum ranges.
- Progression decision codes are unique.
- Classification ranges do not overlap.
- Required formulas cannot be blank when the related calculation is enabled.

### Upload validation

- Accept only configured spreadsheet/document formats.
- Enforce maximum file size.
- Store files privately.
- Scan where infrastructure supports malware scanning.
- Validate required headers before accepting a workbook as “ready for review”.
- Store failed validation results without exposing the uploaded file publicly.
- Never auto-import uploaded workbooks into production.

## 12. Phase 0 completion record

The following exit criteria are complete in this baseline:

- [x] Source questionnaire sections catalogued.
- [x] Source workbook headers captured.
- [x] Fields mapped to wizard stages.
- [x] Repeatable groups identified.
- [x] Required, conditional, optional, evidence and deferred classes defined.
- [x] Conditional rules documented.
- [x] Academic readiness gates documented.
- [x] Academic and overall status transitions documented.
- [x] Attachment register baseline documented.
- [x] Staff template mapping documented.
- [x] Student template mapping documented.
- [x] Validation baseline documented.
- [x] Remaining business decisions recorded.

Phase 1 can begin using this document as the initial implementation contract. Stakeholder validation should be recorded as amendments rather than recreating the discovery work.

## 13. Remaining stakeholder decisions

These are not blockers for building the draft foundation, but they must be confirmed before production release:

| Decision | Baseline used for engineering | Required confirmation |
|---|---|---|
| Client collaborators | One primary client contact in first release | Confirm whether multiple named collaborators are required |
| Resume authentication | Email magic link plus optional one-time code | Confirm preferred client experience |
| Academic evidence strictness | Required where the related academic feature is in scope | Confirm any waivers |
| Staff/student upload | Validate and stage; CRM approval before import | Confirm final import ownership |
| Staff controlled values | Define during template implementation | Confirm accepted gender/status/role values |
| Student controlled values | Define during template implementation | Confirm status, programme and level values |
| Attachment retention | Private storage with configurable retention | Confirm retention period |
| Finance | Explicit defer option; never blocks academic submission | Confirm default finance scope |
| Pilot client | One college implementation | Select pilot institution |

