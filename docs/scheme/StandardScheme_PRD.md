# Standard Scheme of Work Product Requirements Document

**Version:** 1.1
**Date:** March 22, 2026
**Author:** Codex
**Status:** Phase 1 Complete - Contract Updated
**Implementation Tracker:** [StandardScheme_Implementation_Tracker.md](/Users/thatoobuseng/Sites/Junior/docs/scheme/StandardScheme_Implementation_Tracker.md)

---

## 1. Executive Summary

### 1.1 Purpose
Introduce a "Standard Scheme" concept that allows a group of teachers teaching the same subject to collaboratively agree on which syllabus topics to cover in a given term, break them into weekly plans, and have that standard scheme distributed to all teachers in the subject so they follow a unified curriculum plan.

### 1.2 Current Repo Reality
The current Schemes of Work module treats schemes as individual teacher documents:
- Each teacher creates their own scheme for their class assignment (`KlassSubject` or `OptionalSubject`)
- Teachers individually pick syllabus topics and break them into weeks
- There is no concept of a shared or standard scheme across all teachers of the same subject
- The syllabus covers the full academic year but there is no mechanism to allocate topics to specific terms at the subject level
- Each scheme goes through an individual approval workflow: `draft -> submitted -> supervisor_reviewed -> under_review -> approved`

This does not match how schools actually operate. In practice, a department or subject panel agrees on which topics all teachers will cover each term, and individual teachers create lesson plans based on that agreed standard.

### 1.3 Core Requirement
Build a standard scheme feature where:
- A Scheme Admin or HOD creates ONE standard scheme per subject per grade per term
- Syllabus topics are selected and assigned to weeks for that term
- The standard scheme goes through an approval workflow
- Once approved, the standard scheme can be published as the teacher-facing reference for that subject+grade+term
- Once approved, it is distributed to all teachers teaching that subject+grade, creating read-only individual schemes
- On the individual scheme show page, the current syllabus drawer must prefer the published standard scheme for the same subject+grade+term and only fall back to the syllabus if no published standard scheme exists
- Teachers then create their own lesson plans based on the distributed entries

### 1.4 Success Criteria
- A standard scheme can be created for a subject+grade+term combination
- Syllabus topics can be assigned to weekly entries with linked objectives
- The standard scheme follows an approval workflow
- The standard scheme can be published after approval
- Distribution automatically creates individual `SchemeOfWork` records for each teacher's class assignment
- Distributed scheme entries are fully read-only for teachers
- On the individual teacher scheme show page, the `Browse Scheme` drawer shows the published standard scheme for the same subject+grade+term, and falls back to the syllabus only when no published standard exists
- Teachers can create lesson plans from distributed entries using the existing lesson plan flow
- Two new roles exist: "Scheme Admin" (full management) and "Scheme View" (read-only access)
- Existing individual schemes without a standard scheme link continue to work normally

---

## 2. Scope

### 2.1 In Scope
- New `StandardScheme` domain with entries, objectives, contributors, and workflow audits
- Standard scheme CRUD (create, view, edit, delete) restricted by role
- Weekly entry management with syllabus topic and objective linking
- Approval workflow: draft -> submitted -> under_review -> approved / revision_required
- Publication workflow for approved standard schemes
- Distribution mechanism that auto-creates individual teacher schemes from the standard
- Read-only enforcement on distributed scheme entries
- Two new roles: "Scheme Admin" and "Scheme View"
- New gates and policy for standard scheme authorization
- Standard scheme index, create, and show views
- Modifications to existing scheme views to show standard scheme banners and read-only state
- Modifications to the existing individual scheme show page so `Browse Scheme` resolves to published standard scheme first and syllabus second
- HOD and admin dashboard updates with standard scheme status
- Contributor tracking (which teachers are part of the subject panel)

### 2.2 Out of Scope
- Real-time collaborative editing of entries by multiple teachers simultaneously
- Syllabus-level term allocation (topics are allocated within the standard scheme, not on the syllabus itself)
- Teacher-level customization of distributed entries (entries are fully read-only in v1)
- Automatic rollback or re-distribution when a standard scheme is revised after distribution
- Notification system for distribution events (can be added later)
- Standard scheme cloning across terms (can be added later)
- Printable/PDF standard scheme document view (can be added later)

---

## 3. Primary Users

| User Type | Description | Responsibility in v1 |
|-----------|-------------|----------------------|
| Scheme Admin | Dedicated role for standard scheme management | Create, edit, approve, publish, unpublish, and distribute standard schemes for any subject |
| HOD | Head of Department | Create, edit, approve, publish, unpublish, and distribute standard schemes for their department's subjects |
| Academic Admin | Academic administrator | View, publish, and manage all standard schemes |
| Scheme View | Read-only access role | View standard schemes and their distribution status |
| Teacher | Subject teacher | View published standard scheme as reference, receive distributed read-only schemes, and create lesson plans from read-only entries |

**Access rule:** Two new roles ("Scheme Admin" and "Scheme View") are introduced. HOD and Academic Admin retain their existing capabilities extended to standard schemes. Teachers can reference a published standard scheme from their individual scheme drawer when they teach the matching subject+grade+term, and can fully open standard scheme records only when they are contributors, have an authorized role, or have received a distributed copy.

---

## 4. Product Principles

1. **Subject-level planning, teacher-level execution**
   The standard scheme defines WHAT topics to cover and WHEN. Individual teachers decide HOW through their lesson plans.

2. **One standard per subject+grade+term**
   There can only be one standard scheme for a given subject, grade, and term combination. This prevents conflicting plans.

3. **Read-only distribution**
   Once a standard scheme is distributed, teachers cannot modify the entries. This ensures curriculum consistency across all classes.

4. **Additive, not destructive**
   The new feature adds to the existing scheme infrastructure. Existing individual schemes (without a standard scheme link) continue to work exactly as before.

5. **Workflow before distribution**
   A standard scheme must go through the approval workflow before it can be distributed. This ensures quality control.

6. **Published standard first**
   Teachers should reference the published standard scheme for their subject, grade, and term before they reference the syllabus. The syllabus is the fallback source, not the primary source, once a standard scheme has been published.

---

## 5. Domain Model

### 5.1 Standard Scheme
The central planning document created at the subject+grade+term level.

Required fields:
- subject (FK to subjects)
- grade (FK to grades)
- term (FK to terms)
- department (FK to departments, resolved from GradeSubject)
- created_by (FK to users)
- status (workflow state)
- total_weeks (number of weekly entries)

Optional fields:
- panel_lead_id (designated lead teacher, defaults to creator)
- review_comments
- reviewed_by
- reviewed_at
- published_at
- published_by

### 5.2 Standard Scheme Entry
One entry per week within the standard scheme.

Fields:
- week_number (1 to total_weeks)
- syllabus_topic_id (optional FK to syllabus_topics)
- topic (text, can be auto-filled from syllabus topic)
- sub_topic
- learning_objectives (text)
- status (planned, taught, completed, skipped)

### 5.3 Entry-Objective Pivot
Links standard scheme entries to specific syllabus objectives (many-to-many).

### 5.4 Contributors
Tracks which teachers are part of the subject panel for a standard scheme.

Roles:
- `lead` — can edit the standard scheme (typically HOD or designated teacher)
- `contributor` — can suggest changes (future enhancement)
- `viewer` — can view the standard scheme (auto-added for all subject teachers)

### 5.5 Workflow Audit
Immutable audit trail recording every workflow transition with actor, timestamps, and comments.

Actions: submitted, placed_under_review, approved, revision_required, published, unpublished, distributed

---

## 6. Functional Requirements

### 6.1 Standard Scheme Creation
Users with Scheme Admin role, HOD role, Academic Admin role, or Administrator role can create a standard scheme.

Creation flow:
- Select subject from available subjects (filtered by department for HODs)
- Select grade
- Select term
- Set total weeks (default 10, range 1-52)
- System validates no existing standard scheme for this subject+grade+term
- System auto-generates blank weekly entries
- System auto-identifies teachers for this subject+grade+term and adds them as viewers

### 6.2 Entry Management
The standard scheme show page provides inline AJAX editing of weekly entries (same UX pattern as existing individual scheme show page).

Entry editing supports:
- Assigning a syllabus topic to a week
- Setting topic and sub-topic text
- Writing learning objectives
- Linking specific syllabus objectives from the objective browser
- Changing entry status

### 6.3 Approval Workflow
Standard schemes follow the same workflow pattern as individual schemes:

```
draft -> submitted -> under_review -> approved -> published
                                   -> revision_required -> (resubmit) -> submitted
```

The supervisor review step is NOT included for standard schemes (they go directly to HOD/Scheme Admin review).

Workflow rules:
- Only the panel lead, HOD, or Scheme Admin can submit
- Only the HOD for the department, assistant HOD, or Scheme Admin can approve/return
- Return for revision requires mandatory comments
- All transitions are audit-logged

Publication rules:
- Only approved standard schemes can be published
- Only Scheme Admin, HOD, Academic Admin, or Administrator can publish or unpublish
- Publishing does not change the workflow status; the scheme remains `approved` and publication visibility is tracked by `published_at` and `published_by`
- Publishing makes the standard scheme the teacher-facing reference for the same subject+grade+term
- Teachers only see a standard scheme in the individual scheme drawer when it has been published

### 6.4 Distribution
After approval, and typically after publication, the standard scheme can be distributed to all teachers teaching the subject+grade in that term.

Publication and distribution are separate actions:
- **Publish** = make the standard scheme available as the reference source in the teacher scheme drawer
- **Distribute** = create read-only individual `SchemeOfWork` copies linked back to the standard scheme

Distribution process:
- System finds all `KlassSubject` rows where `grade_subject.subject_id` matches and `grade_subject.grade_id` matches and `klass_subject.term_id` matches
- System also finds matching `OptionalSubject` rows
- For each teacher assignment:
  - Creates a new `SchemeOfWork` with `standard_scheme_id` set and status = 'approved'
  - Copies all entries from standard scheme into `SchemeOfWorkEntry` rows with `standard_scheme_entry_id` links
  - Copies objective pivot records
- Logs a `distributed` audit entry with count of created schemes

### 6.5 Teacher Scheme Reference Resolution
On the normal individual scheme show page, the current `Browse Syllabus` action becomes `Browse Scheme`.

Resolution order:
1. Find a published `StandardScheme` matching the current scheme's `subject + grade + term`
2. If one exists, show that published standard scheme in the drawer
3. If none exists, fall back to the active syllabus for the same subject and grade

Behavior rules:
- The button label should remain `Browse Scheme` even when the drawer is showing syllabus fallback content
- Helper text inside the drawer must explicitly state whether the user is seeing the published standard scheme or syllabus fallback
- The published standard scheme drawer content is read-only reference content
- This behavior applies to both standalone individual schemes and distributed schemes

### 6.6 Read-Only Enforcement
When a `SchemeOfWork` has `standard_scheme_id` set:
- The scheme show page displays a banner: "This scheme follows the [Subject] [Grade] standard scheme. Entries are read-only."
- Inline AJAX entry editing is disabled
- The `SchemeEntryController::update()` returns 403 for entries with `standard_scheme_entry_id`

### 6.7 Teacher Experience
Teachers see their distributed scheme in the normal "My Schemes" list. The scheme appears with an "Approved" status and a "Standard" badge. Teachers can:
- View all weekly entries and their topics/objectives
- Create lesson plans from any entry (existing lesson plan flow, unchanged)
- View the linked standard scheme for full context
- Use `Browse Scheme` on any individual scheme to view the published standard scheme for that subject+grade+term when one exists

Teachers CANNOT:
- Edit any entry fields
- Change entry status
- Submit or modify the workflow

---

## 7. Integration with Existing Modules

### 7.1 Individual Schemes
Existing individual schemes (where `standard_scheme_id` is null) continue to work exactly as before for CRUD, workflow, and lesson planning.

Added behavior:
- Their show page `Browse Scheme` drawer must resolve to the published standard scheme for the same subject+grade+term when one exists
- If no published standard scheme exists, the drawer falls back to the syllabus
- This allows legacy or standalone teacher schemes to still benefit from the department-approved standard plan without forcing redistribution first

### 7.2 Lesson Plans
The lesson plan module requires NO changes. Lesson plans already link to `scheme_of_work_entry_id`. Since distributed entries are standard `SchemeOfWorkEntry` records (just with an additional `standard_scheme_entry_id` FK), the existing lesson plan create/edit/show flow works identically.

### 7.3 Syllabus
The syllabus module requires NO changes. Standard scheme entries link to `syllabus_topic_id` and objectives through the same pattern as existing scheme entries.

### 7.4 Dashboards
- HOD dashboard: add a "Standard Schemes" section showing status per subject+grade
- Admin dashboard: add standard scheme coverage statistics
- Teacher dashboard: no changes needed (distributed schemes appear in the existing scheme list)

---

## 8. Data Model

### 8.1 New Tables

#### `standard_schemes`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| subject_id | FK -> subjects | |
| grade_id | FK -> grades | |
| term_id | FK -> terms | |
| department_id | FK -> departments | Resolved from GradeSubject at creation |
| created_by | FK -> users | |
| panel_lead_id | FK -> users, nullable | Defaults to creator |
| status | varchar(30), default 'draft' | draft, submitted, under_review, approved, revision_required |
| total_weeks | smallint, default 10 | |
| review_comments | text, nullable | |
| reviewed_by | FK -> users, nullable | |
| reviewed_at | timestamp, nullable | |
| published_at | timestamp, nullable | Null until published |
| published_by | FK -> users, nullable | User who published the standard scheme |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp, nullable | Soft delete |

**Unique constraint:** `unique(subject_id, grade_id, term_id)`

#### `standard_scheme_entries`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| standard_scheme_id | FK -> standard_schemes, cascade | |
| week_number | smallint | |
| syllabus_topic_id | FK -> syllabus_topics, nullable | |
| topic | varchar(255), nullable | |
| sub_topic | varchar(255), nullable | |
| learning_objectives | text, nullable | |
| status | varchar(30), default 'planned' | |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp, nullable | |

#### `standard_scheme_entry_objectives`
| Column | Type |
|--------|------|
| id | bigint PK |
| standard_scheme_entry_id | FK -> standard_scheme_entries, cascade |
| syllabus_objective_id | FK -> syllabus_objectives, cascade |

**Unique constraint:** `unique(standard_scheme_entry_id, syllabus_objective_id)`

#### `standard_scheme_contributors`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| standard_scheme_id | FK -> standard_schemes, cascade | |
| user_id | FK -> users, cascade | |
| role | varchar(20) | 'lead', 'contributor', 'viewer' |
| created_at | timestamp | |
| updated_at | timestamp | |

**Unique constraint:** `unique(standard_scheme_id, user_id)`

#### `standard_scheme_workflow_audits`
| Column | Type |
|--------|------|
| id | bigint PK |
| standard_scheme_id | FK -> standard_schemes, cascade |
| actor_id | FK -> users |
| action | varchar(50) |
| from_status | varchar(30) |
| to_status | varchar(30) |
| comments | text, nullable |
| created_at | timestamp |

### 8.2 Changed Tables

#### `schemes_of_work`
- Add `standard_scheme_id` (nullable FK -> standard_schemes, on delete set null)

#### `scheme_of_work_entries`
- Add `standard_scheme_entry_id` (nullable FK -> standard_scheme_entries, on delete set null)

### 8.3 New Roles
Seeded into the `roles` table:
- **Scheme Admin** — full standard scheme management
- **Scheme View** — read-only standard scheme access

---

## 9. Roles & Authorization

### 9.1 New Roles
| Role | Capabilities |
|------|-------------|
| Scheme Admin | Create, edit, delete, submit, approve, publish, unpublish, and distribute standard schemes. Full CRUD on entries. |
| Scheme View | View standard scheme index, show pages, and distribution status. Read-only. |

### 9.2 Gates

```
access-schemes (updated): Administrator, Academic Admin, HOD, Teacher, Scheme Admin, Scheme View
manage-standard-schemes (new): Administrator, Academic Admin, HOD, Scheme Admin
view-standard-schemes (new): Administrator, Academic Admin, HOD, Teacher, Scheme Admin, Scheme View
```

### 9.3 Policy: `StandardSchemePolicy`
| Method | Who | Conditions |
|--------|-----|------------|
| viewAny | Scheme Admin, Scheme View, HOD, Academic Admin, Administrator | Always |
| view | Above + any contributor | Must be contributor or have role |
| create | Scheme Admin, HOD, Academic Admin, Administrator | Always |
| update | Scheme Admin, HOD for department, panel lead | Status must be draft or revision_required |
| delete | Scheme Admin (draft only), Administrator | Status must be draft |
| submit | Scheme Admin, panel lead, HOD | Status must be draft or revision_required |
| review | HOD/assistant for department, Scheme Admin | Status must be submitted or under_review |
| publish | Scheme Admin, HOD, Academic Admin, Administrator | Status must be approved |
| distribute | Scheme Admin, HOD, Administrator | Status must be approved |

---

## 10. Migration & Backward Compatibility

### 10.1 Additive Design
All new tables and columns are added alongside existing structures. No existing data is modified or deleted.

### 10.2 Existing Schemes
Individual schemes where `standard_scheme_id` is null continue to function exactly as before for CRUD, workflow, and lesson planning.

The only additive behavior change is on the show page reference drawer:
- if a published standard scheme exists for the same subject+grade+term, it is used first
- otherwise the syllabus remains the fallback source

### 10.3 No Retroactive Linking
Existing individual schemes are NOT automatically linked to standard schemes. Going forward, new terms will use the standard scheme flow. Existing terms can continue with individual schemes.

### 10.4 Rollback Safety
If the feature needs to be rolled back:
- Remove the new routes and controllers
- The nullable `standard_scheme_id` column on `schemes_of_work` has no impact on existing records
- Individual schemes with `standard_scheme_id` set would lose their link but remain functional as standalone schemes

---

## 11. Configuration & Governance

### 11.1 What Is Configurable in v1
- Role assignment (Scheme Admin, Scheme View) through existing role management
- Number of weeks per standard scheme (1-52)
- Panel lead designation per standard scheme

### 11.2 What Is Fixed in v1
- One standard scheme per subject+grade+term (unique constraint)
- Teachers reference the published standard scheme first on the individual scheme show page
- Fully read-only distributed entries (no teacher customization)
- Approval required before distribution
- Distribution creates copies (not references) of entries

### 11.3 Tracking Rule
Implementation phase status is governed by [StandardScheme_Implementation_Tracker.md](/Users/thatoobuseng/Sites/Junior/docs/scheme/StandardScheme_Implementation_Tracker.md). The tracker is the execution source of truth. The PRD defines scope and acceptance; the tracker defines whether a phase is `NOT STARTED`, `IN PROGRESS`, `BLOCKED`, or `COMPLETE`.

---

## 12. Acceptance Criteria

The feature is acceptable for v1 only if all of the following are true:
- Two new roles ("Scheme Admin" and "Scheme View") exist and are assignable
- A Scheme Admin can create a standard scheme for a subject+grade+term
- Weekly entries can be filled with syllabus topics and objectives
- The standard scheme follows the approval workflow (submit -> review -> approve/return)
- An approved standard scheme can be published
- An approved standard scheme can be distributed to all teachers of that subject+grade+term
- Distribution creates individual `SchemeOfWork` records linked to the standard scheme
- Distributed scheme entries are fully read-only for teachers
- Teachers can create lesson plans from distributed entries using the existing lesson plan flow
- On the individual scheme show page, `Browse Scheme` shows the published standard scheme for the same subject+grade+term, and falls back to the syllabus when no published standard exists
- Existing individual schemes (without standard_scheme_id) continue to work normally
- HOD and admin dashboards show standard scheme status
- The standard scheme show page displays contributors, distribution status, and workflow audit trail

---

## 13. Implementation Phases

### Phase 1: Documentation & Design Contract
- Finalize this PRD
- Create the implementation tracker
- Lock scope, roles, data model, workflow, and acceptance criteria

### Phase 2: Roles Seeding & Database Schema
- Seed "Scheme Admin" and "Scheme View" roles
- Create `standard_schemes` table including publication fields
- Create `standard_scheme_entries` table
- Create `standard_scheme_entry_objectives` pivot table
- Create `standard_scheme_contributors` table
- Create `standard_scheme_workflow_audits` table
- Add `standard_scheme_id` to `schemes_of_work`
- Add `standard_scheme_entry_id` to `scheme_of_work_entries`

### Phase 3: Models & Service Layer
- Create `StandardScheme` model with relationships, scopes, and methods
- Create `StandardSchemeEntry` model
- Create `StandardSchemeWorkflowAudit` model
- Create `StandardSchemeService` with createWithEntries, workflow methods, publish/unpublish methods, and distributeToTeachers
- Modify `SchemeOfWork` and `SchemeOfWorkEntry` models to add standard scheme relationships

### Phase 4: Policy, Gates & Authorization
- Create `StandardSchemePolicy`
- Register policy in `AuthServiceProvider`
- Add `manage-standard-schemes` and `view-standard-schemes` gates
- Add standard scheme publish authorization
- Update `access-schemes` gate to include new roles

### Phase 5: Controllers, Routes & Form Requests
- Create `StandardSchemeController` (index, create, store, show, destroy, distribute)
- Create `StandardSchemeEntryController` (update, syncObjectives)
- Create `StandardSchemeWorkflowController` (submit, placeUnderReview, approve, returnForRevision, publish, unpublish)
- Create `StoreStandardSchemeRequest`
- Add routes to `routes/schemes/schemes.php`

### Phase 6: Views & UI
- Create `standard/index.blade.php` (listing page)
- Create `standard/create.blade.php` (create form)
- Create `standard/show.blade.php` (main working page with entries, syllabus panel, workflow, contributors, distribution)
- Modify `schemes/show.blade.php` (read-only banner for derived schemes)
- Modify `schemes/show.blade.php` so `Browse Scheme` resolves `published standard scheme -> syllabus fallback`
- Modify `schemes/create.blade.php` (standard scheme notice)

### Phase 7: Distribution & Integration
- Implement distribution logic in StandardSchemeService
- Enforce read-only on distributed entries in SchemeEntryController
- Integrate individual scheme show-page reference lookup with published standard schemes
- Update HOD dashboard with standard scheme section
- Update admin dashboard with standard scheme stats

### Phase 8: QA, Testing & Rollout
- Write feature tests for standard scheme CRUD
- Write feature tests for workflow transitions
- Write feature tests for distribution logic
- Write feature tests for read-only enforcement
- Manual QA of full flow
- Record verification evidence in tracker
- Close rollout

---

## 14. Future Considerations

- Real-time collaborative editing of standard scheme entries by multiple panel members
- Teacher-level notes/annotations on read-only entries (non-destructive customization)
- Standard scheme cloning across terms (carry forward topics to next term)
- Notification system when a standard scheme is distributed or updated
- Printable/PDF document view for standard schemes
- Re-distribution mechanism when a standard scheme is revised after initial distribution
- Coverage analytics showing standard scheme adoption across the school
- Integration with timetable module for period-level lesson plan alignment

---

## 15. Summary

This PRD defines a standard scheme of work feature that transforms curriculum planning from an individual teacher activity into a collaborative, department-level process. The v1 design creates a clear separation: subject panels agree on WHAT to teach and WHEN through the standard scheme, while individual teachers decide HOW through their lesson plans. Distribution creates read-only copies that ensure curriculum consistency across all classes of the same subject and grade. Two new roles (Scheme Admin, Scheme View) provide flexible access control, and the existing individual scheme infrastructure remains fully backward-compatible.
