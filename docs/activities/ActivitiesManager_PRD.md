# Activities Manager - Product Requirements Document

**Version:** 1.0  
**Date:** April 3, 2026  
**Author:** Codex  
**Status:** Draft for Implementation  
**Phase Tracker:** [ActivitiesManager_Phase_Tracker.txt](/Users/thatoobuseng/Sites/Junior/docs/activities/ActivitiesManager_Phase_Tracker.txt)  
**System:** Junior School Management Platform (`/Users/thatoobuseng/Sites/Junior`)

---

## 1. Executive Summary

### 1.1 Purpose
Build a standalone `Activities Manager` module for internal school staff to manage student activities across recurring programs and one-off events. The module must support:

- activity setup and lifecycle management
- staff supervision and role assignment
- student roster management
- attendance tracking for sessions
- event, fixture, showcase, and competition management
- results, awards, and achievement capture
- activity fee integration through the existing Fee Administration module
- reporting and exports

The module must fit the existing Laravel architecture, term-aware data model, and UI/navigation conventions already used across Students, Houses, LMS, Fees, and other operational modules.

### 1.2 Current Repo Reality
The current application already contains adjacent capabilities but does not have a dedicated activities domain.

Current repo facts that shape this PRD:

- There is no standalone `Activities` module, route group, model family, or sidebar entry today.
- Student records are term-aware and already integrate with classes, grades, houses, attendance, fees, and sponsors.
- House membership is already owned by the existing Houses module through `houses`, `student_house`, `App\Models\House`, and `App\Models\Student`.
- The LMS module already has a reusable calendar/event layer through `App\Models\Lms\CalendarEvent` and `App\Http\Controllers\Lms\CalendarController`, but it is LMS-centric and must not become the source of truth for extracurricular operations.
- Module visibility is centrally managed by `App\Services\ModuleVisibilityService`, with a standard pattern for sidebar integration and role-based launcher visibility.
- Fee Administration already supports fee types, invoices, invoice items, payments, and audit trails through `fee_types`, `student_invoices`, `student_invoice_items`, and related services such as `App\Services\Fee\InvoiceService`.

### 1.3 Core Requirement
The first release must introduce a full operational Activities Manager for staff-only use. It must cover both recurring activities and one-off events, while staying within these boundaries:

- no student or parent self-service
- no transport, vendor, consent, or excursion logistics
- no standalone collections or billing engine
- no takeover of house membership ownership from the Houses module

### 1.4 Success Criteria

- Staff can create and manage activities for a selected term and year.
- Staff can assign supervisors, define eligibility, and manage rosters without duplicate enrollment or silent over-capacity allocation.
- Staff can manage both recurring schedules and one-off events from the same module.
- Staff can record attendance and competition outcomes, including placements, points, and awards.
- Activity fees can be generated into the existing fee workflow without duplicate charge creation.
- Student profiles and reports can show activity participation history without Activities redefining core student, house, or fee ownership.
- The module appears as a standalone, visibility-controlled entry in the main application navigation.

---

## 2. Scope

### 2.1 In Scope

- Standalone Activities module with its own route file, navigation entry, dashboard, list pages, detail pages, settings, and reports
- Recurring activities such as clubs, sports teams, societies, arts groups, service groups, and academic support programs
- One-off activity events such as fixtures, competitions, showcases, exhibitions, and assemblies that are managed inside the activities domain
- Staff assignment to activities with scoped roles such as coordinator, patron, coach, assistant, or scorer
- Eligibility targeting by grade, class, house, and optional student grouping/filter constructs already present in the platform
- Manual and bulk roster management by staff
- Session generation and attendance capture
- Event result capture for students, houses, and activity teams
- Awards and achievement recording linked to events
- Activity charge generation and billing linkage into the existing Fee Administration module
- Reporting, exports, search/filtering, and student-profile summaries
- Auditability, authorization, and module-visibility control

### 2.2 Out of Scope

- Student self-enrollment, student applications, or parent approval flows
- Parent or student-facing portal access for activities in v1
- Excursion planning, transport allocation, bus tracking, or vendor contracting
- Medical consent workflows, trip consent collection, or risk-assessment administration
- Standalone activity payments, receipts, refunds, or cash collection workflows
- Full competition-bracket management or advanced sports-statistics engines
- Replacing or relocating the Houses module

---

## 3. Primary Users

| User Type | Description | Responsibility in v1 |
| --- | --- | --- |
| Activities Admin | School leader or senior administrator over activities | Full module setup, lifecycle control, reporting, and fee-charge governance |
| Activities Edit | Staff member with operational responsibility | Create activities, manage rosters, schedules, sessions, and events |
| Activities Staff | Coach, patron, or supervisor assigned to specific activities | Mark attendance, update event details, and record outcomes for assigned activities |
| Activities View | Read-only operational user | View activities, rosters, schedules, summaries, and reports |
| Fee Admin / Bursar | Existing fee team | Consume approved activity charges through the Fee Administration workflow |

**Access rule:** v1 remains staff-only. Students and parents are not direct users of the Activities Manager.

---

## 4. Product Principles

1. **Activities own activity operations**  
   The Activities module is the source of truth for activities, schedules, sessions, attendance, events, and results.

2. **Houses stay authoritative for house membership**  
   Activities may reference houses for eligibility and competition results, but house membership continues to be managed only by the Houses module.

3. **Fees integrate, not duplicate**  
   Activity charges must use the existing fee engine for invoicing, payment, balance, and audit behavior.

4. **Staff control first**  
   Roster changes, fee-triggering actions, and result recording are staff actions in v1. No self-service is introduced.

5. **Term-aware by default**  
   Activities must follow the existing selected-term pattern already used by Students and related modules.

6. **Recurring and one-off are both first-class**  
   The module must handle long-running activity programs and standalone event operations without splitting them into unrelated systems.

7. **Operational history must remain explainable**  
   Reports and detail views must preserve enough context to explain who joined, who attended, what result was recorded, and what fee charge was generated.

---

## 5. Activity Domain Model

### 5.1 Activity
An activity is the top-level operational record for a club, sport, society, arts group, service group, or a one-off event program.

Required v1 fields:

- name
- code
- category
- delivery mode
- status
- term
- year

Supported behavior:

- recurring-only activities
- one-off-only activities
- hybrid activities that have both regular sessions and special events

Recommended v1 enums:

- `category`: `club`, `sport`, `society`, `arts`, `service`, `academic`, `event_program`, `other`
- `delivery_mode`: `recurring`, `one_off`, `hybrid`
- `status`: `draft`, `active`, `paused`, `closed`, `archived`
- `participation_mode`: `individual`, `team`, `mixed`
- `result_mode`: `attendance_only`, `placements`, `points`, `awards`, `mixed`

Optional v1 fields:

- description
- default location
- capacity
- gender policy
- attendance required flag
- house linkage flag
- linked fee type and default fee amount

### 5.2 Staff Assignment
Each activity can have many staff assignments.

Required behavior:

- one activity can have multiple staff members
- at least one active lead role should exist before an activity is activated
- activity staff can be permission-scoped to assigned records only

Supported staff roles:

- coordinator
- patron
- coach
- assistant
- scorer
- viewer

### 5.3 Eligibility Targets
Eligibility is not stored as free text. It is stored as structured targets against existing school structures.

Supported target types in v1:

- grade
- class
- house
- student filter

Behavior:

- if no eligibility targets are defined, the activity is treated as manually managed and open to staff-selected students in the selected term
- eligibility targets support fast bulk rostering, not automatic self-enrollment

### 5.4 Enrollment
An enrollment links a student to an activity for a specific term and year.

Required behavior:

- a student may only have one active enrollment per activity per term
- enrollment supports active, withdrawn, completed, and suspended states
- enrollment stores who added or removed the student
- enrollment can trigger an activity fee charge when configured

### 5.5 Schedules and Sessions
Recurring activities need reusable schedules and concrete attendance-bearing sessions.

Required behavior:

- schedules define recurring meeting patterns
- sessions represent actual dated occurrences
- sessions may be generated from schedules or created manually
- attendance is recorded against sessions, not directly against schedules

### 5.6 Events
Activity events represent fixtures, competitions, showcases, and other dated occurrences that may or may not be attendance-bearing.

Required behavior:

- events belong to an activity
- events can be standalone or attached to a recurring activity
- events can optionally be published to a calendar surface
- events can be linked to house-based or student-based outcomes

### 5.7 Results and Awards
Results are recorded against activity events.

Supported result subjects in v1:

- student
- house
- activity team

Supported result outputs:

- placement
- score
- points
- award
- achievement note

### 5.8 Fee Charge Linkage
Activities do not collect money directly. They create charge records that integrate with Fees.

Required behavior:

- store whether a student/activity charge was generated
- store amount, fee type, and billing status
- reference the resulting invoice and invoice item when one is created
- prevent duplicate charge creation for the same intended billing action

---

## 6. Functional Requirements

### 6.1 Activity Catalog and Lifecycle

The module must support:

- activity list with search and filters
- create, edit, show, activate, pause, close, and archive flows
- filtering by category, delivery mode, status, term, and staff owner
- dashboard metrics such as active activities, sessions due today, upcoming events, and pending fee charges

### 6.2 Staff Assignment Management

Staff can:

- assign one or more staff members to an activity
- mark one lead coordinator or primary supervisor
- retire assignments without deleting historical activity ownership
- filter activities by assigned staff member

### 6.3 Eligibility and Capacity Control

The system must support:

- eligibility by grade
- eligibility by class
- eligibility by house
- capacity limits at activity level

Rules:

- capacity checks must be transaction-safe
- staff may override eligibility only if they have elevated activity-management permissions
- house eligibility references existing house records only and never writes house membership

### 6.4 Roster Management

Required behavior:

- add students individually
- bulk-add students from eligible filters
- withdraw, suspend, or complete enrollments
- capture join date, exit reason, and operator
- prevent duplicate active enrollment
- show roster counts and roster history

### 6.5 Recurring Scheduling

Recurring activities must support:

- weekly or biweekly schedule blocks
- meeting day and time
- start and end date range
- location
- optional notes

Sessions can be:

- generated in bulk from a schedule
- manually created or edited
- cancelled or postponed with reason

### 6.6 Session Attendance

Attendance must be captured per session.

Required attendance states:

- present
- absent
- excused
- late
- injured

Rules:

- only enrolled students for that activity can be marked
- attendance records become read-only after explicit lock/finalize action by authorized staff
- withdrawn students remain visible in historical sessions that occurred before withdrawal

### 6.7 One-Off Events and Fixtures

Events must support:

- fixture or competition scheduling
- showcase or exhibition scheduling
- internal event setup
- external opponent or partner naming as free text in v1
- event status tracking: scheduled, completed, postponed, cancelled

Required event fields:

- title
- event type
- activity
- start and end date/time
- location
- event status

Optional event fields:

- description
- opponent or partner name
- house-linked flag
- publish-to-calendar flag

### 6.8 Results, Awards, and Achievement Capture

The module must support:

- result entry after event completion
- student placements
- house points or placements
- activity-team results
- award labels such as medal, certificate, best performer, or coach recognition

Rules:

- result records are tied to an event
- results are immutable to view users
- result edits should remain auditable

### 6.9 Fee Integration

Activity fees are supported only through the existing Fees module.

Required flow:

1. An activity optionally defines a fee type and default amount.
2. Enrollment or another approved billing action creates an `activity_fee_charges` record.
3. A fee-generation action creates or attaches a line item through existing invoice flows.
4. The charge record stores invoice linkage and billing state.
5. Payment, receipts, adjustments, and balance remain owned by Fees.

Rules:

- no duplicate billing for the same enrollment charge
- waived or cancelled charges must remain auditable
- Activities may show billing state, but not collect or void payments

### 6.10 Reports and Exports

The module must provide:

- activity register report
- enrollment/roster report
- attendance summary report
- event and result summary report
- house-linked performance report
- student activity history report
- unpaid or uninvoiced activity charges report
- Excel/CSV export support

---

## 7. Data Model and Public Interfaces

### 7.1 New Tables

#### `activities`

- `id`
- `name`
- `code`
- `category`
- `delivery_mode`
- `participation_mode`
- `result_mode`
- `description` nullable
- `default_location` nullable
- `capacity` nullable
- `gender_policy` nullable (`boys`, `girls`, `mixed`)
- `attendance_required` boolean
- `allow_house_linkage` boolean
- `fee_type_id` nullable FK to `fee_types`
- `default_fee_amount` nullable decimal
- `status`
- `term_id`
- `year`
- `created_by`
- `updated_by` nullable
- timestamps
- soft deletes

Constraints:

- unique activity code per year
- indexes on `term_id`, `year`, `status`, `category`

#### `activity_staff_assignments`

- `id`
- `activity_id`
- `user_id`
- `role`
- `is_primary`
- `active`
- `assigned_at`
- `removed_at` nullable
- `notes` nullable
- timestamps

Constraints:

- one active assignment per activity/user/role
- index on `activity_id`, `user_id`, `active`

#### `activity_eligibility_targets`

- `id`
- `activity_id`
- `target_type`
- `target_id`
- timestamps

Supported `target_type` values:

- `grade`
- `class`
- `house`
- `student_filter`

Constraints:

- unique `activity_id + target_type + target_id`

#### `activity_enrollments`

- `id`
- `activity_id`
- `student_id`
- `term_id`
- `year`
- `status` (`active`, `withdrawn`, `completed`, `suspended`)
- `joined_at`
- `left_at` nullable
- `joined_by`
- `left_by` nullable
- `exit_reason` nullable
- `source` (`manual`, `bulk_filter`, `carry_forward`)
- `grade_id_snapshot` nullable
- `klass_id_snapshot` nullable
- `house_id_snapshot` nullable
- timestamps
- soft deletes

Constraints:

- unique active enrollment per activity/student/term
- indexes on `student_id`, `activity_id`, `term_id`, `status`

#### `activity_schedules`

- `id`
- `activity_id`
- `frequency` (`weekly`, `biweekly`)
- `day_of_week`
- `start_time`
- `end_time`
- `start_date`
- `end_date` nullable
- `location` nullable
- `notes` nullable
- `active`
- timestamps
- soft deletes

#### `activity_sessions`

- `id`
- `activity_id`
- `activity_schedule_id` nullable
- `session_type` (`meeting`, `practice`, `training`, `rehearsal`, `other`)
- `session_date`
- `start_datetime`
- `end_datetime` nullable
- `location` nullable
- `status` (`planned`, `completed`, `cancelled`, `postponed`)
- `attendance_locked` boolean
- `notes` nullable
- `created_by`
- timestamps
- soft deletes

Constraints:

- index on `activity_id`, `session_date`, `status`

#### `activity_session_attendance`

- `id`
- `activity_session_id`
- `activity_enrollment_id`
- `student_id`
- `status` (`present`, `absent`, `excused`, `late`, `injured`)
- `remarks` nullable
- `marked_by`
- `marked_at`
- timestamps

Constraints:

- unique attendance row per session/student
- index on `activity_session_id`, `student_id`

#### `activity_events`

- `id`
- `activity_id`
- `title`
- `event_type` (`fixture`, `competition`, `showcase`, `exhibition`, `meeting`, `other`)
- `description` nullable
- `start_datetime`
- `end_datetime` nullable
- `location` nullable
- `opponent_or_partner_name` nullable
- `house_linked` boolean
- `publish_to_calendar` boolean
- `calendar_sync_status` (`not_published`, `published`, `sync_failed`)
- `status` (`scheduled`, `completed`, `postponed`, `cancelled`)
- `created_by`
- timestamps
- soft deletes

Constraints:

- index on `activity_id`, `start_datetime`, `status`

#### `activity_results`

- `id`
- `activity_event_id`
- `participant_type`
- `participant_id`
- `metric_type` (`placement`, `points`, `score`, `award`, `achievement`)
- `score_value` nullable
- `placement` nullable
- `points` nullable
- `award_name` nullable
- `result_label` nullable
- `notes` nullable
- `recorded_by`
- timestamps

Supported `participant_type` values:

- `student`
- `house`
- `activity`

Constraints:

- index on `activity_event_id`, `participant_type`, `participant_id`

#### `activity_fee_charges`

- `id`
- `activity_id`
- `activity_enrollment_id`
- `activity_event_id` nullable
- `student_id`
- `fee_type_id`
- `term_id`
- `year`
- `charge_type` (`enrollment`, `event`, `manual`)
- `amount`
- `billing_status` (`pending`, `invoiced`, `waived`, `cancelled`)
- `student_invoice_id` nullable
- `student_invoice_item_id` nullable
- `generated_by`
- `generated_at`
- `notes` nullable
- timestamps
- soft deletes

Constraints:

- duplicate prevention on intended charge identity
- indexes on `student_id`, `activity_id`, `billing_status`, `term_id`, `year`

#### `activity_audit_logs`

- `id`
- `user_id`
- `entity_type`
- `entity_id`
- `action`
- `old_values` nullable JSON
- `new_values` nullable JSON
- `notes` nullable
- `ip_address` nullable
- `user_agent` nullable
- `created_at`

### 7.2 Changed Tables and Existing Interfaces

No ownership-changing migration is required for `students` or `houses`. The Activities module integrates with those domains through foreign keys, model relationships, and service-level coordination.

Expected interface changes:

- `Student` model gains activity relationships and summary helpers.
- `User` model gains assigned/supervised activity relationships.
- `ModuleVisibilityService` gains an `activities` entry.
- Sidebar/navigation gains an Activities menu item.
- `StudentInvoiceItem` gains a dedicated activity-fee linkage path.
- Fee services gain a safe integration path for activity-charge invoice creation and invoice-total recalculation.
- No house schema changes are introduced; house integration remains read/reference only.

Required changed-table contract for Fees:

#### `student_invoice_items`

For activity-fee integration to be implementation-safe in this repo, extend the table with:

- nullable `activity_fee_charge_id` FK to `activity_fee_charges`
- new `item_type` value: `activity_fee`

Rules:

- `fee_structure_id` stays nullable for activity-generated items
- activity-fee invoice items must use `item_type = activity_fee`
- activity-fee invoice items must point back to exactly one `activity_fee_charge`
- one `activity_fee_charge` may create at most one live invoice item

Required model/interface changes for Fees:

- `App\Models\Fee\StudentInvoiceItem` gains `TYPE_ACTIVITY_FEE`
- `App\Models\Fee\StudentInvoiceItem` gains `activityFeeCharge()` relationship
- fee recalculation paths must treat `activity_fee` items as normal billable invoice rows
- discount logic must explicitly skip activity-fee rows unless a later release introduces activity-fee discount rules

Activities calendar contract in v1:

- do not write activity records into `lms_calendar_events` by default in v1
- use Activities-owned tables as the source for the calendar view inside the Activities module
- any later calendar unification must be additive and must not move ownership away from `activity_events` or `activity_sessions`

### 7.3 Route and UI Contract

Route prefix: `/activities`

Required route families:

- `/activities` - module dashboard or activity index landing
- `/activities/list` - list view when dashboard is separate
- `/activities/create`
- `/activities/{activity}`
- `/activities/{activity}/edit`
- `/activities/{activity}/staff`
- `/activities/{activity}/eligibility`
- `/activities/{activity}/roster`
- `/activities/{activity}/schedules`
- `/activities/{activity}/sessions`
- `/activities/sessions/{session}/attendance`
- `/activities/{activity}/events`
- `/activities/events/{event}/results`
- `/activities/reports`
- `/activities/settings`

UI screens required in v1:

- dashboard
- activity list
- activity create/edit form
- activity detail page with tabs
- roster management page
- schedules/sessions page
- attendance page
- events and results page
- reporting page
- settings/configuration page

---

## 8. Integration Contracts

### 8.1 Students Module

The Activities module must integrate with `Student` without redefining student ownership.

Required student-facing data surfaces for staff:

- current activities summary on student profile
- participation history by term
- activity attendance summary
- activity achievements/results summary
- linked activity charges status

Expected student model additions:

- `activityEnrollments()`
- `activities()`
- `activityAttendances()`
- `activityResults()`

### 8.2 Houses Module

Houses remain the authority for house records and student-house membership.

Activities may:

- reference house eligibility targets
- record house-based event results
- produce house-linked performance reports

Activities must not:

- create, edit, or assign house membership
- replace house reports that belong to the Houses module

### 8.3 LMS Calendar

The LMS calendar remains secondary to Activities.

Contract:

- `activity_events` and `activity_sessions` remain authoritative
- the Activities module may render a calendar UI using its own tables
- calendar displays must reference the activity domain, not replace it
- LMS calendar synchronization is explicitly deferred in v1

Preferred v1 approach:

- build the Activities calendar screen directly from `activity_sessions` and `activity_events`
- reuse the existing FullCalendar-style frontend pattern, but not the LMS event tables
- keep all create, edit, cancel, postpone, and result workflows inside Activities screens

Reason for this decision:

- the current LMS calendar is student-audience-oriented
- the current LMS calendar notification path is wired for LMS/student event delivery
- v1 for Activities is staff-only, so syncing into LMS calendar tables would create avoidable audience and notification ambiguity

Future-safe rule for a later integration phase:

- if a unified calendar is added later, it must treat `ActivityEvent` as the source record
- any mirror row in `lms_calendar_events` must use `eventable_type` and `eventable_id` to point back to the activity record
- no activity event may be editable from LMS if that would bypass Activities validation and audit rules

### 8.4 Fee Administration

Activities must use the existing invoice engine without pretending activity charges are grade fee structures.

Contract:

- activities reference `fee_types`
- charges are tracked in `activity_fee_charges`
- invoice generation creates or links `student_invoices` and `student_invoice_items`
- payment state is read-only in Activities and authoritative in Fees

Required behavior:

- idempotent charge generation
- duplicate protection using transactional checks and unique identities
- audit trail for waived, cancelled, or invoiced activity charges

Implementation-aligned billing rules:

1. Activities may only use fee types that are active and optional:
   - `fee_types.is_active = true`
   - `fee_types.category = optional`
   - `fee_types.is_optional = true`
2. Activities must not auto-create grade-based `fee_structures` for each activity.
3. Activity-generated invoice items must use:
   - `student_invoice_items.item_type = activity_fee`
   - `student_invoice_items.fee_structure_id = null`
   - `student_invoice_items.activity_fee_charge_id = {charge id}`
4. `activity_fee_charges` stores the authoritative `fee_type_id`, amount, activity reference, enrollment reference, and billing state.
5. One student has one annual invoice per year in the current fee architecture, so activity charges are appended to that annual invoice rather than creating parallel activity invoices.

Required invoice-generation flow:

1. Staff action creates or approves an `activity_fee_charges` row in `pending` state.
2. `ActivityFeeService` locks the charge row and checks whether an invoice item is already linked.
3. The service locks the student's annual invoice for the selected year:
   - if an active invoice exists, append a new activity-fee item
   - if no invoice exists, create one annual invoice and add the activity-fee item
   - if the only invoice for that year is cancelled, stop and require fee-admin intervention
4. The service recalculates invoice subtotal, total, balance, and status using existing invoice recalculation patterns.
5. The charge row moves to `invoiced` and stores the `student_invoice_id` and `student_invoice_item_id`.

Operational constraints:

- adding a new activity charge to a fully paid invoice is allowed and reopens the invoice with a positive balance
- waiving or cancelling an uninvoiced charge happens inside Activities
- reversing a charge that has already been invoiced must go through fee-safe adjustment or credit-note paths; Activities records the intent, but Fees remains the financial system of record

### 8.5 Notifications

v1 notification scope is staff-operational only.

Supported uses:

- session cancellation or postponement notice to assigned staff
- activity event schedule changes to assigned staff
- activity charge generation confirmation to authorized staff

Student-facing push or portal notification workflows are out of scope for v1.

---

## 9. Authorization and Governance

### 9.1 Module Visibility

Register Activities in `ModuleVisibilityService` with:

- key: `modules.activities_visible`
- name: `Activities Manager`
- icon: `bx bx-run`
- roles:
  - `Activities Admin`
  - `Activities Edit`
  - `Activities View`
  - `Activities Staff`

### 9.2 Gates

Required gates:

- `access-activities`
- `manage-activities`
- `manage-activity-setup`
- `manage-activity-rosters`
- `manage-activity-schedules`
- `mark-activity-attendance`
- `record-activity-results`
- `generate-activity-fees`
- `view-activity-reports`
- `view-activity-audit`

### 9.3 Policy Expectations

Create policy coverage so that:

- admins manage all activities
- edit users manage activities within granted scope
- assigned staff can update attendance and outcomes only for activities they are assigned to
- view users can access lists, details, and reports only

### 9.4 Auditability

The following actions must create audit entries:

- activity creation, activation, pause, closure, and archive
- supervisor assignment changes
- roster add/remove/suspend actions
- attendance finalization
- result edits
- activity fee generation, waiver, and cancellation

---

## 10. Routing, Navigation, and UI Standards

### 10.1 Route Registration

- Create `routes/activities/activities.php`
- Register it from the central route bootstrap
- Apply middleware baseline:
  - `auth`
  - `throttle:auth`
  - `block.non.african`
  - `check.license`
  - `can:access-activities`

### 10.2 Sidebar and Launcher

- Add a top-level Activities entry in `resources/views/layouts/sidebar.blade.php`
- Respect module visibility and role-based launcher behavior
- Use the same menu visibility conventions as Contacts, Fees, LMS, and Schemes

### 10.3 UI Pattern

All pages must follow the current repo theming standards:

- list/index pages align with `resources/views/admissions/index.blade.php`
- form pages align with `resources/views/admissions/admission-new.blade.php`
- save buttons follow the loading-button pattern defined in repo guidance
- tabs on detail pages should mirror existing setup/detail screens

### 10.4 Required Screen Layouts

1. **Dashboard**
   - active activities
   - sessions due today
   - upcoming events
   - pending attendance capture
   - pending activity fee charges

2. **Activity Detail**
   - overview
   - staff
   - eligibility
   - roster
   - schedules/sessions
   - events/results
   - fee charges
   - audit trail

3. **Reports**
   - filterable report index
   - export buttons
   - summary cards
   - tabular details

---

## 11. Business Rules and Non-Functional Requirements

### 11.1 Core Business Rules

1. Student must be active in the selected term before enrollment.
2. Student cannot be actively enrolled twice in the same activity and term.
3. Capacity enforcement must be transaction-safe.
4. Activity fee generation must be idempotent.
5. Attendance can only be recorded against concrete sessions.
6. Result entry requires a completed or explicitly result-ready event.
7. House-linked results must reference existing house records only.
8. Withdrawn students must remain visible in historical activity records where relevant.

### 11.2 Performance

- List screens must avoid N+1 queries for staff, roster, attendance, and charge summaries.
- Report queries must filter by term/year and use indexed fields.
- Large roster operations should be chunked or queued when necessary.

### 11.3 Concurrency

Use transactions and row locking for:

- capacity-checked bulk enrollment
- roster transfers or withdrawal actions that affect fee charge state
- activity fee generation
- attendance finalization

### 11.4 Security

- all state-changing actions use validated request objects
- authorization is enforced at controller and policy layers
- fee integration actions require explicit fee-generation permission

---

## 12. Reporting and Export Requirements

### 12.1 Core Reports

- Activity register by term, category, status, and supervisor
- Roster report by activity, grade, class, and house
- Session attendance summary by activity and date range
- Student activity history by student and term
- Event results and awards report
- House-linked performance report for activity events
- Activity fee charge status report

### 12.2 Export Expectations

Exports should reuse the existing export approach already common in the platform.

Required formats in v1:

- Excel
- CSV where lightweight export is sufficient

Export rules:

- exported reports must respect authorization filters
- exports must preserve selected term and year context
- exports must not silently include inactive or deleted records unless explicitly requested

---

## 13. Technical Implementation Map

### 13.1 Planned Directory Structure

```text
app/
├── Exports/
│   └── Activities/
│       ├── ActivityRegisterExport.php
│       ├── ActivityRosterExport.php
│       ├── ActivityAttendanceExport.php
│       ├── ActivityResultsExport.php
│       └── ActivityFeeChargesExport.php
├── Http/
│   ├── Controllers/
│   │   └── Activities/
│   │       ├── ActivityController.php
│   │       ├── ActivityDashboardController.php
│   │       ├── ActivityStaffController.php
│   │       ├── ActivityRosterController.php
│   │       ├── ActivityScheduleController.php
│   │       ├── ActivitySessionController.php
│   │       ├── ActivityAttendanceController.php
│   │       ├── ActivityEventController.php
│   │       ├── ActivityResultController.php
│   │       ├── ActivityFeeController.php
│   │       ├── ActivityReportController.php
│   │       └── ActivitySettingsController.php
│   └── Requests/
│       └── Activities/
│           ├── StoreActivityRequest.php
│           ├── UpdateActivityRequest.php
│           ├── AssignActivityStaffRequest.php
│           ├── StoreActivityEligibilityRequest.php
│           ├── BulkEnrollStudentsRequest.php
│           ├── UpdateActivityEnrollmentRequest.php
│           ├── StoreActivityScheduleRequest.php
│           ├── GenerateActivitySessionsRequest.php
│           ├── StoreActivitySessionRequest.php
│           ├── MarkActivityAttendanceRequest.php
│           ├── StoreActivityEventRequest.php
│           ├── StoreActivityResultRequest.php
│           ├── GenerateActivityChargesRequest.php
│           └── UpdateActivitySettingsRequest.php
├── Models/
│   └── Activities/
│       ├── Activity.php
│       ├── ActivityStaffAssignment.php
│       ├── ActivityEligibilityTarget.php
│       ├── ActivityEnrollment.php
│       ├── ActivitySchedule.php
│       ├── ActivitySession.php
│       ├── ActivitySessionAttendance.php
│       ├── ActivityEvent.php
│       ├── ActivityResult.php
│       ├── ActivityFeeCharge.php
│       └── ActivityAuditLog.php
├── Policies/
│   └── Activities/
│       ├── ActivityPolicy.php
│       ├── ActivityEnrollmentPolicy.php
│       ├── ActivitySessionPolicy.php
│       ├── ActivityEventPolicy.php
│       ├── ActivityResultPolicy.php
│       └── ActivityFeeChargePolicy.php
└── Services/
    └── Activities/
        ├── ActivityService.php
        ├── ActivityRosterService.php
        ├── ActivityScheduleService.php
        ├── ActivityAttendanceService.php
        ├── ActivityEventService.php
        ├── ActivityResultService.php
        ├── ActivityFeeService.php
        ├── ActivityReportService.php
        ├── ActivityDashboardService.php
        └── ActivityAuditService.php

database/migrations/
├── xxxx_xx_xx_xxxxxx_create_activities_table.php
├── xxxx_xx_xx_xxxxxx_create_activity_staff_assignments_table.php
├── xxxx_xx_xx_xxxxxx_create_activity_eligibility_targets_table.php
├── xxxx_xx_xx_xxxxxx_create_activity_enrollments_table.php
├── xxxx_xx_xx_xxxxxx_create_activity_schedules_table.php
├── xxxx_xx_xx_xxxxxx_create_activity_sessions_table.php
├── xxxx_xx_xx_xxxxxx_create_activity_session_attendance_table.php
├── xxxx_xx_xx_xxxxxx_create_activity_events_table.php
├── xxxx_xx_xx_xxxxxx_create_activity_results_table.php
├── xxxx_xx_xx_xxxxxx_create_activity_fee_charges_table.php
├── xxxx_xx_xx_xxxxxx_create_activity_audit_logs_table.php
└── xxxx_xx_xx_xxxxxx_add_activity_fee_link_to_student_invoice_items.php

resources/views/activities/
├── dashboard.blade.php
├── index.blade.php
├── create.blade.php
├── edit.blade.php
├── show.blade.php
├── roster/
├── schedules/
├── sessions/
├── attendance/
├── events/
├── fees/
├── reports/
└── settings/

routes/
└── activities/
    └── activities.php
```

### 13.2 Migration Order and Schema Notes

Recommended migration order:

1. `activities`
2. `activity_staff_assignments`
3. `activity_eligibility_targets`
4. `activity_enrollments`
5. `activity_schedules`
6. `activity_sessions`
7. `activity_session_attendance`
8. `activity_events`
9. `activity_results`
10. `activity_fee_charges`
11. `activity_audit_logs`
12. `student_invoice_items` extension for activity-fee linkage

Implementation rules:

- all main operational tables except attendance rows and audit rows should support soft deletes
- every operational table that represents school-year behavior must store `term_id` and `year` where applicable
- index all FK columns plus high-frequency report filters
- use restrictive foreign keys for core ownership and nullable FKs where history must survive related-record retirement

### 13.3 Model Responsibilities

- `Activity`: lifecycle state, default fee settings, summary relationships
- `ActivityStaffAssignment`: scoped staff responsibility and primary coordinator flag
- `ActivityEligibilityTarget`: structured eligibility rules by target type
- `ActivityEnrollment`: student membership state, source, and snapshots
- `ActivitySchedule`: recurrence definition only
- `ActivitySession`: concrete dated occurrence for attendance
- `ActivitySessionAttendance`: per-session student attendance row
- `ActivityEvent`: fixture, competition, showcase, or special event
- `ActivityResult`: event outcome row for student, house, or activity
- `ActivityFeeCharge`: billing intent and invoice linkage
- `ActivityAuditLog`: activity-domain audit trail

### 13.4 Controller Responsibilities

- `ActivityDashboardController`: landing metrics, upcoming sessions/events, pending operational work
- `ActivityController`: activity list, create, store, show, edit, update, activate, pause, close, archive
- `ActivityStaffController`: assign, update, retire, and list supervisors
- `ActivityRosterController`: enrollment add, bulk-add, withdraw, suspend, complete, and roster exports
- `ActivityScheduleController`: recurrence CRUD and bulk session generation
- `ActivitySessionController`: session list, manual session create/update/cancel/postpone
- `ActivityAttendanceController`: attendance entry, lock/finalize, re-open by privileged users
- `ActivityEventController`: event CRUD and status transitions
- `ActivityResultController`: result entry, update, and audit-aware correction flow
- `ActivityFeeController`: pending charges, generate invoice linkage, waive/cancel uninvoiced charges
- `ActivityReportController`: all reporting pages and export endpoints
- `ActivitySettingsController`: activity categories, defaults, and module settings if v1 keeps a local settings screen

### 13.5 Request Classes

Each write-heavy workflow should use dedicated FormRequest validation rather than generic controller validation.

Required request classes:

- `StoreActivityRequest`
- `UpdateActivityRequest`
- `AssignActivityStaffRequest`
- `StoreActivityEligibilityRequest`
- `BulkEnrollStudentsRequest`
- `UpdateActivityEnrollmentRequest`
- `StoreActivityScheduleRequest`
- `GenerateActivitySessionsRequest`
- `StoreActivitySessionRequest`
- `MarkActivityAttendanceRequest`
- `StoreActivityEventRequest`
- `StoreActivityResultRequest`
- `GenerateActivityChargesRequest`
- `UpdateActivitySettingsRequest`

### 13.6 Policies, Gates, and Roles

Required role family:

- `Activities Admin`
- `Activities Edit`
- `Activities View`
- `Activities Staff`

Required gate and policy wiring lives in:

- `app/Providers/AuthServiceProvider.php`
- `app/Policies/Activities/*`

Authorization pattern:

- module access gate controls entry
- model policies control CRUD
- staff-assignment checks limit attendance/result actions for scoped operators

### 13.7 Service Layer Responsibilities

- `ActivityService`
  - activity CRUD
  - lifecycle transitions
  - activation precondition checks
- `ActivityRosterService`
  - eligibility resolution
  - single and bulk enrollment
  - capacity enforcement
  - withdrawal/suspension/completion logic
- `ActivityScheduleService`
  - recurrence persistence
  - bulk session generation
  - duplicate-date protection
- `ActivityAttendanceService`
  - attendance writes
  - finalization and reopening rules
  - attendance summary aggregation
- `ActivityEventService`
  - event lifecycle and status transitions
  - house-linked setup validation
- `ActivityResultService`
  - result validation
  - house/student/activity participant resolution
  - award and points recording
- `ActivityFeeService`
  - charge creation
  - invoice-item generation
  - fee-safe waiver/cancel prechecks
- `ActivityReportService`
  - filtered report datasets
  - export-ready query builders
- `ActivityDashboardService`
  - dashboard cards, counters, and operational queues
- `ActivityAuditService`
  - normalized audit writes for high-risk actions

### 13.8 Fee Integration Implementation Notes

The fee path should be implemented as an append-only integration into the annual invoice model already in the repo.

Required changes:

- extend `App\Models\Fee\StudentInvoiceItem` with `TYPE_ACTIVITY_FEE`
- add nullable `activity_fee_charge_id` to invoice items
- add `activityFeeCharge()` relationship
- extend any invoice-item display logic to recognize activity-fee rows

Recommended service sequence:

1. `ActivityFeeService` validates fee type and charge uniqueness.
2. It locks the `activity_fee_charges` row.
3. It locks or creates the student's annual invoice for the selected year.
4. It inserts a single `student_invoice_items` row for the charge.
5. It recalculates invoice totals using existing money-safe patterns.
6. It writes an audit record in both Activities and Fees contexts where appropriate.

### 13.9 Cross-Module File Updates

Required non-Activities-domain updates:

- `app/Services/ModuleVisibilityService.php`
- `resources/views/layouts/sidebar.blade.php`
- `app/Providers/AuthServiceProvider.php`
- `app/Models/Student.php`
- `app/Models/User.php`
- `app/Models/Fee/StudentInvoiceItem.php`
- fee services/controllers that expose invoice items or manual invoice generation
- optional search integration files if global search is extended in v1

### 13.10 Tests

Recommended test layout:

```text
tests/
├── Feature/
│   └── Activities/
│       ├── ActivityCrudTest.php
│       ├── ActivityAuthorizationTest.php
│       ├── ActivityRosterTest.php
│       ├── ActivityCapacityTest.php
│       ├── ActivityScheduleSessionTest.php
│       ├── ActivityAttendanceTest.php
│       ├── ActivityEventResultTest.php
│       ├── ActivityHouseLinkTest.php
│       ├── ActivityFeeIntegrationTest.php
│       ├── ActivityStudentProfileSummaryTest.php
│       └── ActivityModuleVisibilityTest.php
└── Unit/
    └── Activities/
        ├── ActivityRosterServiceTest.php
        ├── ActivityAttendanceServiceTest.php
        ├── ActivityResultServiceTest.php
        ├── ActivityFeeServiceTest.php
        └── ActivityReportServiceTest.php
```

---

## 14. Implementation Phases

### Phase 0: Contract Freeze and Module Shell

**Goal:** Lock the implementation contract before schema or UI work starts.

**Implementation targets**

- `docs/activities/ActivitiesManager_PRD.md`
- `docs/activities/ActivitiesManager_Phase_Tracker.txt`
- `routes/activities/activities.php`
- `app/Providers/AuthServiceProvider.php`
- `app/Services/ModuleVisibilityService.php`
- `resources/views/layouts/sidebar.blade.php`

**Scope**

- treat this PRD as canonical
- register route include
- add module visibility entry
- add gate and role scaffolding
- add placeholder sidebar and launcher integration points

**Phase completion gate**

- Activities route file exists and is included
- gates and module-visibility entry are defined
- sidebar/menu wiring contract is clear
- no unresolved ownership ambiguity remains around Houses, LMS calendar, or Fees

### Phase 1: Core Schema and Activity CRUD Foundation

**Goal:** Create the activities domain foundation and working CRUD shell.

**Implementation targets**

- core migrations for `activities`, staff assignments, eligibility targets, enrollments, schedules, sessions, events, results, fee charges, and audit logs
- `App\Models\Activities\*`
- `ActivityController`
- base views for list/create/edit/show

**Scope**

- create all core tables and indexes
- add activity lifecycle fields and status handling
- build list/create/edit/show flows
- support activity activation, pause, close, and archive transitions

**Completion gate**

- migrations run cleanly
- activity CRUD works with authorization
- activity status transitions are validated and audited
- automated CRUD and authorization tests exist

### Phase 2: Staff Assignment and Eligibility Rules

**Goal:** Make activities operationally ownable and targetable.

**Implementation targets**

- `ActivityStaffController`
- `AssignActivityStaffRequest`
- eligibility target persistence and UI
- policy checks for assigned staff

**Scope**

- assign and retire supervisors
- enforce single primary coordinator behavior
- add structured eligibility by grade, class, house, and student filter
- expose activity ownership and eligibility summaries on show pages

**Completion gate**

- assigned staff can be added and removed without losing history
- eligibility targets save and render correctly
- scoped operators can access only assigned activity actions where intended
- automated staff-assignment and eligibility tests exist

### Phase 3: Roster Management and Capacity Controls

**Goal:** Deliver safe student membership workflows.

**Implementation targets**

- `ActivityRosterController`
- `ActivityRosterService`
- bulk enrollment request handling
- roster screens and exports

**Scope**

- single-student enrollment
- bulk enrollment from eligibility filters
- withdrawal, suspension, completion flows
- capacity protection and duplicate-enrollment blocking
- enrollment snapshots for grade/class/house context

**Completion gate**

- manual and bulk enrollment work for active-term students
- duplicate active enrollment is blocked
- over-capacity writes are blocked in both normal and concurrent paths
- roster history is visible and auditable

### Phase 4: Scheduling, Sessions, and Attendance

**Goal:** Turn activities into dated operational workflows.

**Implementation targets**

- `ActivityScheduleController`
- `ActivitySessionController`
- `ActivityAttendanceController`
- `ActivityScheduleService`
- `ActivityAttendanceService`

**Scope**

- recurring schedule CRUD
- bulk session generation
- manual session creation/editing
- cancel/postpone flows
- attendance marking, locking, and reopening controls
- dashboard cards for attendance work queues

**Completion gate**

- sessions can be generated without duplicate-date collisions
- attendance can only be recorded for enrolled students
- finalized attendance becomes read-only except for privileged correction flows
- feature and unit tests cover session generation and attendance rules

### Phase 5: Events, Results, Awards, and House-Linked Outputs

**Goal:** Support competitions, showcases, and outcomes.

**Implementation targets**

- `ActivityEventController`
- `ActivityResultController`
- `ActivityEventService`
- `ActivityResultService`

**Scope**

- one-off event CRUD
- fixture and showcase management
- result entry for students, houses, and activity teams
- points, placements, awards, and achievement notes
- house-linked performance views that read from existing house records

**Completion gate**

- completed events can accept results
- house-linked results never mutate house membership
- award and points records appear in event detail and reports
- event/result feature tests exist

### Phase 6: Fee Integration and Student Summary Surfaces

**Goal:** Link operational activity charges into the existing financial engine.

**Implementation targets**

- `activity_fee_charges` workflows
- `student_invoice_items` extension for activity-fee linkage
- `ActivityFeeController`
- `ActivityFeeService`
- student profile summary integration

**Scope**

- create pending activity charges
- generate or append annual invoice items safely
- block duplicate billing
- handle uninvoiced waive/cancel actions
- expose fee status on activity and student summary views

**Completion gate**

- one charge creates at most one invoice item
- invoice totals recalculate correctly after charge posting
- cancelled annual invoice edge case is handled explicitly
- student profile shows activity participation plus charge state
- fee-integration tests cover duplicate prevention and invoice linkage

### Phase 7: Reports, Exports, Audit Views, and Hardening

**Goal:** Complete the operational reporting layer and production hardening work.

**Implementation targets**

- `ActivityReportController`
- `App\Exports\Activities\*`
- audit log views
- search integration if approved for v1

**Scope**

- activity register, roster, attendance, results, house-linked, student-history, and fee-charge reports
- Excel/CSV exports
- audit screens
- N+1 hardening and eager-loading review
- role/visibility regression coverage
- end-to-end manual QA

**Completion gate**

- all core reports render with filters and exports
- audit history is visible for privileged users
- targeted regression suites pass
- browser/manual QA confirms CRUD, roster, attendance, event/result, fee, and report flows

### Phase Dependency Rules

- Do not start fee posting before roster identity and student-term rules are stable.
- Do not start result reporting before event ownership and house-link validation are complete.
- Do not sync to LMS calendar in v1; any calendar UI should read Activities-owned tables only.
- Do not mark rollout complete until Phase 7 verification is done.

---

## 15. Acceptance Criteria

1. Staff can create a recurring activity with eligibility rules, supervisors, capacity, and optional fee settings.
2. Staff can add and remove students manually or in bulk without duplicate active enrollment.
3. Capacity enforcement blocks over-allocation in normal and concurrent write flows.
4. Staff can define recurring schedules and create actual sessions for attendance.
5. Staff can mark and finalize attendance for a session.
6. Staff can create a one-off event or fixture under an activity and record results, placements, and awards.
7. House-linked events can produce house results without Activities taking over house membership management.
8. Activity fees can be generated into the existing fee workflow without duplicate billing records.
9. Student profile summaries can show activity participation and linked charge state.
10. Module visibility, sidebar behavior, authorization, and reporting work within the current platform conventions.

---

## 16. Verification and Testing Guidance

### 16.1 Feature Test Scenarios

- activity CRUD authorization and validation
- activity lifecycle transitions and audit logging
- staff assignment rules
- assigned-staff scoped access rules
- eligibility-target setup
- roster add/remove/withdraw flows
- bulk roster add with capacity limits
- session creation and attendance entry
- attendance lock and reopen rules
- event creation and results capture
- house-linked result recording
- activity fee charge generation and duplicate prevention
- annual invoice append behavior for activity fees
- cancelled annual invoice edge case handling
- student profile activity summary rendering
- module visibility behavior
- sidebar access behavior
- activities calendar view reads from activity tables rather than LMS tables

### 16.2 Unit Test Targets

- roster service duplicate and capacity protection
- activity service lifecycle precondition checks
- attendance service finalization rules
- result service validation rules
- fee integration idempotency logic
- invoice-item creation logic for `activity_fee`
- dashboard/report query aggregations

### 16.3 Concurrency Tests

- two staff members attempting to add the final available slot at the same time
- repeated fee-generation request for the same enrollment
- overlapping attendance finalization actions
- concurrent bulk-enrollment request against the same activity capacity

### 16.4 Regression Risks to Cover

- student profile performance
- fee invoice item linkage
- invoice status recalculation after activity fee posting
- house-linked reporting correctness
- assigned-staff authorization leakage
- route/menu visibility under role combinations

---

## 17. Explicit Defaults and Locked Decisions

- v1 is staff-only.
- v1 includes both recurring activities and one-off events.
- v1 includes attendance and results.
- v1 supports activity fees only through the existing Fees module.
- v1 excludes transport, vendors, consent, and excursion logistics.
- Houses remain linked only and keep ownership of house membership.
- Activities are term-aware and must respect selected term/year context across CRUD, reports, and exports.

---

## 18. Summary

The Activities Manager is a standalone operational module for school activities, not a thin calendar wrapper and not a replacement for Houses or Fees. It owns activity operations end to end, while integrating cleanly with Students, Houses, LMS calendar surfaces, notifications, module visibility, and the existing fee engine.

This PRD intentionally fixes the scope for a practical v1: internal staff control, strong term-aware operations, auditable roster and attendance management, event/result tracking, and fee linkage without reopening billing or student self-service concerns.
