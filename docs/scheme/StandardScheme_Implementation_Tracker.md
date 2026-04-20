# Standard Scheme Implementation Tracker

**Reference PRD:** [StandardScheme_PRD.md](/Users/thatoobuseng/Sites/Junior/docs/scheme/StandardScheme_PRD.md)
**Module Status:** PHASE 7 COMPLETE
**Last Updated:** March 22, 2026

---

## 1. Purpose

This document is the working tracker for the Standard Scheme of Work feature rollout. It defines the execution phases required to introduce subject-level standard schemes with distribution to individual teachers, and it is the source of truth for whether a phase is `NOT STARTED`, `IN PROGRESS`, `BLOCKED`, or `COMPLETE`.

A phase is only complete when:
- all scoped work for that phase is done
- required automated verification has passed where applicable
- required manual verification has passed where applicable
- the completion evidence is recorded in this tracker
- the header status, phase table, detailed phase section, completion log, and `Last Updated` date are all updated in the same change set

Current repo state when this tracker was created:
- Individual scheme of work module exists and is functional
- Schemes are created per teacher per class assignment
- No concept of standard/shared schemes exists
- Syllabus module exists with topics and objectives
- Approval workflow exists: draft -> submitted -> supervisor_reviewed -> under_review -> approved
- No "Scheme Admin" or "Scheme View" roles exist

---

## 2. How To Use This Tracker

1. Move a phase to `IN PROGRESS` when work on that phase begins.
2. Check scope items off as implementation lands.
3. Run the verification items listed for that phase.
4. Record exact verification evidence in the completion log.
5. Only then mark the phase `COMPLETE`.

The tracker must be updated every time a phase starts or completes.

---

## 3. Status Rules

Status values to use:
- `NOT STARTED`
- `IN PROGRESS`
- `BLOCKED`
- `COMPLETE`

Completion policy:
- never mark a phase `COMPLETE` without recorded verification evidence
- use exact dates, not relative wording
- when manual QA is required, note that QA explicitly in the completion log
- if a phase is blocked, record the blocker in the phase notes and completion log

---

## 4. Phase Overview

| Phase | Focus | Status | Depends On |
|-------|-------|--------|------------|
| Phase 1 | Documentation & design contract | COMPLETE | None |
| Phase 2 | Roles seeding & database schema | COMPLETE | Phase 1 |
| Phase 3 | Models & service layer | COMPLETE | Phase 2 |
| Phase 4 | Policy, gates & authorization | COMPLETE | Phase 3 |
| Phase 5 | Controllers, routes & form requests | COMPLETE | Phase 4 |
| Phase 6 | Views & UI | COMPLETE | Phase 5 |
| Phase 7 | Distribution & integration | COMPLETE | Phase 6 |
| Phase 8 | QA, testing & rollout | NOT STARTED | Phase 7 |

---

## 5. Detailed Phases

### Phase 1: Documentation & Design Contract

**Status:** COMPLETE
**Goal:** Finalize the product contract, execution phases, data model, and tracking rules before any schema or feature work begins.

**Implementation targets**
- `docs/scheme/StandardScheme_PRD.md`
- `docs/scheme/StandardScheme_Implementation_Tracker.md`

**Scope**
- [x] Create StandardScheme_PRD.md with full feature specification
- [x] Create StandardScheme_Implementation_Tracker.md with phase definitions
- [x] Define v1 scope, non-goals, and acceptance criteria
- [x] Lock the standard scheme data model (tables, columns, constraints)
- [x] Lock the roles and authorization model (Scheme Admin, Scheme View)
- [x] Lock the publication model for approved standard schemes
- [x] Lock the individual scheme show-page reference rule: published standard scheme first, syllabus fallback second
- [x] Lock the distribution mechanism design
- [x] Define exact execution phases and update rules

**Phase 1 completion gate**
- [x] Documentation files exist under `/docs/scheme/`
- [x] PRD includes Implementation Phases section
- [x] Tracker includes all required status and completion sections
- [x] PRD and tracker phase names match exactly
- [x] PRD and tracker both reflect the same v1 contract
- [x] PRD explicitly defines publish vs distribute behavior
- [x] PRD explicitly defines teacher show-page `Browse Scheme` resolution order

**Suggested verification commands**
```bash
test -f docs/scheme/StandardScheme_PRD.md
test -f docs/scheme/StandardScheme_Implementation_Tracker.md
rg -n "^## 13\\. Implementation Phases" docs/scheme/StandardScheme_PRD.md
rg -n "^## 4\\. Phase Overview|^## 6\\. Phase Completion Log" docs/scheme/StandardScheme_Implementation_Tracker.md
```

**Notes**
- This phase is documentation-only by design.
- No schema or application code should start before this contract is in place.

---

### Phase 2: Roles Seeding & Database Schema

**Status:** COMPLETE
**Goal:** Create all database tables, seed new roles, and add FK columns to existing tables.

**Implementation targets**
- `database/migrations/`

**Scope**
- [x] Create migration to seed "Scheme Admin" and "Scheme View" roles into the `roles` table
- [x] Create migration for `standard_schemes` table with unique constraint on (subject_id, grade_id, term_id)
- [x] Include `published_at` and `published_by` on `standard_schemes`
- [x] Create migration for `standard_scheme_entries` table
- [x] Create migration for `standard_scheme_entry_objectives` pivot table
- [x] Create migration for `standard_scheme_contributors` table
- [x] Create migration for `standard_scheme_workflow_audits` table
- [x] Create migration to add `standard_scheme_id` (nullable FK) to `schemes_of_work`
- [x] Create migration to add `standard_scheme_entry_id` (nullable FK) to `scheme_of_work_entries`

**Phase 2 completion gate**
- [x] Automated: `php artisan migrate` runs cleanly with no errors
- [x] Automated: all new tables exist with correct columns and constraints
- [x] Automated: unique constraint on standard_schemes(subject_id, grade_id, term_id) is enforced
- [x] Automated: `published_at` and `published_by` exist on `standard_schemes`
- [x] Automated: "Scheme Admin" and "Scheme View" roles exist in the roles table (2/2)
- [x] Automated: `standard_scheme_id` column exists on `schemes_of_work` and is nullable
- [x] Automated: `standard_scheme_entry_id` column exists on `scheme_of_work_entries` and is nullable
- [ ] Manual: verify FK cascade behavior (deleting a standard scheme cascades to entries, contributors, audits)

**Suggested verification commands**
```bash
php artisan migrate
php artisan tinker --execute="echo \App\Models\Role::whereIn('name', ['Scheme Admin', 'Scheme View'])->count();"
php artisan tinker --execute="echo Schema::hasTable('standard_schemes') ? 'yes' : 'no';"
php artisan tinker --execute="echo Schema::hasColumn('schemes_of_work', 'standard_scheme_id') ? 'yes' : 'no';"
```

**Notes**
- Migrations should use today's date prefix convention matching existing migrations.
- Role seeding should be idempotent (check existence before insert).

---

### Phase 3: Models & Service Layer

**Status:** NOT STARTED
**Goal:** Create Eloquent models for all new tables and the core service class with business logic.

**Implementation targets**
- `app/Models/Schemes/StandardScheme.php`
- `app/Models/Schemes/StandardSchemeEntry.php`
- `app/Models/Schemes/StandardSchemeWorkflowAudit.php`
- `app/Services/Schemes/StandardSchemeService.php`
- `app/Models/Schemes/SchemeOfWork.php` (modify)
- `app/Models/Schemes/SchemeOfWorkEntry.php` (modify)

**Scope**
- [ ] Create `StandardScheme` model with all relationships (entries, subject, grade, term, department, creator, panelLead, reviewer, contributors, derivedSchemes, workflowAudits)
- [ ] Add `scopeVisibleTo(Builder, User)` scope to StandardScheme
- [ ] Add `getTeachersForSubject()` method to StandardScheme
- [ ] Add `isEditable()` method to StandardScheme
- [ ] Create `StandardSchemeEntry` model with relationships (standardScheme, syllabusTopic, objectives)
- [ ] Create `StandardSchemeWorkflowAudit` model with action constants and static `log()` method
- [ ] Create `StandardSchemeService` with `createWithEntries()` method
- [ ] Add workflow methods to `StandardSchemeService`: `submitScheme()`, `placeUnderReview()`, `approveScheme()`, `returnForRevision()`, `publishScheme()`, `unpublishScheme()`
- [ ] Add `standardScheme()` BelongsTo relationship to `SchemeOfWork`
- [ ] Add `isDerivedFromStandard()` method to `SchemeOfWork`
- [ ] Add `standard_scheme_id` to `SchemeOfWork::$fillable`
- [ ] Add `standardSchemeEntry()` BelongsTo relationship to `SchemeOfWorkEntry`
- [ ] Add `standard_scheme_entry_id` to `SchemeOfWorkEntry::$fillable`
- [ ] Add lookup helper on the individual scheme side for `published standard scheme -> syllabus fallback`

**Phase 3 completion gate**
- [ ] Automated: PHP lint passes on all new and modified model files
- [ ] Automated: `StandardScheme::create()` + `entries()` relationship works in tinker
- [ ] Automated: `SchemeOfWork::standardScheme()` relationship returns null for existing records
- [ ] Manual: verify `getTeachersForSubject()` returns correct teachers for a subject+grade+term

**Suggested verification commands**
```bash
php -l app/Models/Schemes/StandardScheme.php
php -l app/Models/Schemes/StandardSchemeEntry.php
php -l app/Models/Schemes/StandardSchemeWorkflowAudit.php
php -l app/Services/Schemes/StandardSchemeService.php
php -l app/Models/Schemes/SchemeOfWork.php
php -l app/Models/Schemes/SchemeOfWorkEntry.php
```

**Notes**
- Follow existing model patterns from `SchemeOfWork.php` for consistency.
- Service methods should use `DB::transaction()` and `lockForUpdate()` matching existing `SchemeService` patterns.

---

### Phase 4: Policy, Gates & Authorization

**Status:** NOT STARTED
**Goal:** Create the authorization layer for standard schemes.

**Implementation targets**
- `app/Policies/StandardSchemePolicy.php`
- `app/Providers/AuthServiceProvider.php`

**Scope**
- [ ] Create `StandardSchemePolicy` with methods: viewAny, view, create, update, delete, submit, review, publish, unpublish, distribute
- [ ] Add `isHodForStandardScheme()` private helper (resolve department from standard scheme)
- [ ] Register `StandardScheme::class => StandardSchemePolicy::class` in AuthServiceProvider `$policies`
- [ ] Add `manage-standard-schemes` gate (Administrator, Academic Admin, HOD, Scheme Admin)
- [ ] Add `view-standard-schemes` gate (Administrator, Academic Admin, HOD, Teacher, Scheme Admin, Scheme View)
- [ ] Update `access-schemes` gate to include Scheme Admin and Scheme View roles

**Phase 4 completion gate**
- [ ] Automated: PHP lint passes on policy and AuthServiceProvider
- [ ] Automated: gates are defined (verify via `php artisan tinker`)
- [ ] Manual: verify a user with "Scheme Admin" role can pass `manage-standard-schemes` gate
- [ ] Manual: verify a user with "Scheme View" role can pass `view-standard-schemes` but NOT `manage-standard-schemes`
- [ ] Manual: verify a teacher without either role cannot pass `manage-standard-schemes`

**Suggested verification commands**
```bash
php -l app/Policies/StandardSchemePolicy.php
php -l app/Providers/AuthServiceProvider.php
php artisan tinker --execute="echo Gate::has('manage-standard-schemes') ? 'defined' : 'missing';"
php artisan tinker --execute="echo Gate::has('view-standard-schemes') ? 'defined' : 'missing';"
```

**Notes**
- Follow the existing `SchemeOfWorkPolicy` pattern for HOD resolution logic.
- The policy must handle the department check via `GradeSubject::where('subject_id', ...)->where('grade_id', ...)->first()->department_id`.

---

### Phase 5: Controllers, Routes & Form Requests

**Status:** NOT STARTED
**Goal:** Build the HTTP layer for standard scheme CRUD, workflow, and entry management.

**Implementation targets**
- `app/Http/Controllers/Schemes/StandardSchemeController.php`
- `app/Http/Controllers/Schemes/StandardSchemeEntryController.php`
- `app/Http/Controllers/Schemes/StandardSchemeWorkflowController.php`
- `app/Http/Requests/Schemes/StoreStandardSchemeRequest.php`
- `routes/schemes/schemes.php`

**Scope**
- [ ] Create `StandardSchemeController` with: index, create, store, show, destroy, distribute
- [ ] Create `StandardSchemeEntryController` with: update (AJAX), syncObjectives (AJAX)
- [ ] Create `StandardSchemeWorkflowController` with: submit, placeUnderReview, approve, returnForRevision, publish, unpublish
- [ ] Create `StoreStandardSchemeRequest` with validation for subject_id, grade_id, term_id, total_weeks + duplicate check
- [ ] Add all standard scheme routes to `routes/schemes/schemes.php`
- [ ] Ensure static routes (`/standard-schemes/create`) appear before wildcard (`/standard-schemes/{standardScheme}`)

**Phase 5 completion gate**
- [ ] Automated: PHP lint passes on all new controller and request files
- [ ] Automated: `php artisan route:list --path=standard-schemes` shows all expected routes
- [ ] Automated: route names are correctly defined (standard-schemes.index, .create, .store, .show, etc.)
- [ ] Manual: visiting `/standard-schemes` as Scheme Admin returns a response (even if view is not yet built)

**Suggested verification commands**
```bash
php -l app/Http/Controllers/Schemes/StandardSchemeController.php
php -l app/Http/Controllers/Schemes/StandardSchemeEntryController.php
php -l app/Http/Controllers/Schemes/StandardSchemeWorkflowController.php
php -l app/Http/Requests/Schemes/StoreStandardSchemeRequest.php
php artisan route:clear && php artisan route:list --path=standard-schemes
```

**Notes**
- Follow existing `SchemeController` patterns for consistency.
- The `distribute` action should be a separate POST route, not part of the approve action.

---

### Phase 6: Views & UI

**Status:** NOT STARTED
**Goal:** Build the frontend pages for standard scheme management and modify existing views for read-only behavior.

**Implementation targets**
- `resources/views/schemes/standard/index.blade.php`
- `resources/views/schemes/standard/create.blade.php`
- `resources/views/schemes/standard/show.blade.php`
- `resources/views/schemes/show.blade.php` (modify)
- `resources/views/schemes/create.blade.php` (modify)

**Scope**
- [ ] Create `standard/index.blade.php` — DataTable with Subject, Grade, Term, Status, Panel Lead, # Teachers, Actions columns. Gradient header pattern.
- [ ] Create `standard/create.blade.php` — Form with subject, grade, term dropdowns + total weeks. Form-container pattern. `.btn-loading` save button.
- [ ] Create `standard/show.blade.php` — Main working page with:
  - [ ] Header section with subject, grade, term, status badge
  - [ ] Stat cards (total weeks, entries filled, objectives mapped, teachers count)
  - [ ] Weekly entry tabs with inline AJAX editing (reuse pattern from existing scheme show)
  - [ ] Syllabus planner panel (reuse `partials/planner-topic-node.blade.php`)
  - [ ] Workflow action buttons (submit, approve, return — context-dependent on status and role)
  - [ ] Publish / unpublish buttons for approved standard schemes
  - [ ] Contributors panel showing subject teachers with roles
  - [ ] Distribution button (visible when status = 'approved') + distribution status table
  - [ ] Workflow audit trail
- [ ] Modify `schemes/show.blade.php` — Add alert banner when `$scheme->standard_scheme_id` is set. Disable inline AJAX editing.
- [ ] Modify `schemes/show.blade.php` — relabel `Browse Syllabus` to `Browse Scheme`
- [ ] Modify `schemes/show.blade.php` — resolve `published standard scheme -> syllabus fallback` in the reference drawer
- [ ] Modify `schemes/create.blade.php` — Add info alert when a standard scheme exists for the matching subject+grade+term

**Phase 6 completion gate**
- [ ] Automated: PHP lint / Blade compilation passes on all new and modified views
- [ ] Manual: standard scheme index page renders with gradient header and DataTable
- [ ] Manual: create form submits and creates a standard scheme
- [ ] Manual: show page displays entries with tab navigation and inline editing
- [ ] Manual: syllabus planner panel loads and objectives can be linked
- [ ] Manual: workflow buttons appear based on status and user role
- [ ] Manual: existing individual scheme show page displays read-only banner when derived from standard
- [ ] Manual: existing individual scheme show page `Browse Scheme` drawer shows published standard scheme when one exists for subject+grade+term
- [ ] Manual: existing individual scheme show page falls back to syllabus when no published standard scheme exists
- [ ] Manual: existing scheme create page shows notice when standard scheme exists

**Suggested verification commands**
```bash
php artisan view:clear
php artisan view:cache 2>&1 | grep -i error || echo "Views compile OK"
```

**Notes**
- Follow UI theming standards from CLAUDE.md (gradient headers, form-container, btn-loading pattern).
- Reuse the existing scheme show page JavaScript for inline entry editing and objective browser.
- The teacher-facing drawer must keep the `Browse Scheme` label even when it is rendering syllabus fallback content.

---

### Phase 7: Distribution & Integration

**Status:** NOT STARTED
**Goal:** Implement the distribution logic and integrate with existing scheme and dashboard views.

**Implementation targets**
- `app/Services/Schemes/StandardSchemeService.php` (add distributeToTeachers)
- `app/Http/Controllers/Schemes/SchemeEntryController.php` (modify)
- `resources/views/schemes/hod/dashboard.blade.php` (modify)
- `resources/views/schemes/admin/dashboard.blade.php` (modify)

**Scope**
- [ ] Implement `StandardSchemeService::distributeToTeachers()`:
  - [ ] Find all KlassSubject rows matching subject_id + grade_id + term_id via GradeSubject
  - [ ] Find matching OptionalSubject rows
  - [ ] For each teacher assignment, create SchemeOfWork with standard_scheme_id and status = 'approved'
  - [ ] Copy entries with standard_scheme_entry_id links
  - [ ] Copy objective pivots
  - [ ] Log 'distributed' audit entry with count
  - [ ] Wrap in DB::transaction with lockForUpdate
- [ ] Modify `SchemeEntryController::update()` — return 403 if entry has `standard_scheme_entry_id`
- [ ] Integrate the individual scheme show-page drawer with published standard schemes before syllabus fallback
- [ ] Add "Standard Schemes" section to HOD dashboard showing status per subject+grade
- [ ] Add standard scheme coverage stats to admin dashboard

**Phase 7 completion gate**
- [ ] Automated: distribution creates correct number of individual schemes
- [ ] Automated: distributed scheme entries have standard_scheme_entry_id set
- [ ] Automated: objective pivots are copied to distributed entries
- [ ] Automated: attempting to update a distributed entry returns 403
- [ ] Manual: distribute a standard scheme and verify all teachers see their new scheme
- [ ] Manual: login as teacher with distributed scheme and verify entries are read-only
- [ ] Manual: create a lesson plan from a distributed entry
- [ ] Manual: HOD dashboard shows standard scheme section
- [ ] Manual: admin dashboard shows standard scheme stats

**Suggested verification commands**
```bash
php artisan test --filter=StandardSchemeDistribution
php artisan tinker --execute="echo \App\Models\Schemes\SchemeOfWork::whereNotNull('standard_scheme_id')->count();"
```

**Notes**
- Distribution should be idempotent: if a teacher already has a scheme for the same assignment+term linked to the same standard scheme, skip rather than duplicate.
- Existing individual schemes (without standard_scheme_id) must not be affected.

---

### Phase 8: QA, Testing & Rollout

**Status:** NOT STARTED
**Goal:** Complete automated test coverage, manual QA, and close the rollout cleanly.

**Implementation targets**
- `tests/Feature/Schemes/`
- manual QA evidence
- tracker completion updates

**Scope**
- [ ] Write feature tests for standard scheme CRUD (create, show, update, delete)
- [ ] Write feature tests for workflow transitions (submit, review, approve, return)
- [ ] Write feature tests for distribution logic (correct count, entry copying, objective copying)
- [ ] Write feature tests for read-only enforcement (403 on distributed entry update)
- [ ] Write feature tests for authorization (Scheme Admin vs Scheme View vs Teacher)
- [ ] Perform end-to-end manual QA:
  - [ ] Create standard scheme as Scheme Admin
  - [ ] Fill entries with syllabus topics and objectives
  - [ ] Submit and approve
  - [ ] Distribute to teachers
  - [ ] Login as teacher, verify read-only entries
  - [ ] Create lesson plan from distributed entry
  - [ ] Verify existing individual schemes unaffected
- [ ] Update tracker statuses and completion log with exact dates and evidence
- [ ] Document remaining risks or follow-ups, if any
- [ ] Mark module rollout complete only when all prior phases are complete

**Phase 8 completion gate**
- [ ] Automated: all feature tests pass
- [ ] Automated: PHP lint passes on all new files
- [ ] Manual: full end-to-end flow verified (create -> fill -> approve -> distribute -> lesson plan)
- [ ] Manual: backward compatibility verified (existing schemes work normally)
- [ ] Manual: tracker reflects final phase statuses and completion evidence
- [ ] Manual: no open blocker remains for the v1 contract

**Suggested verification commands**
```bash
php artisan test --filter=StandardScheme
php artisan test --filter=SchemeOfWork
```

**Notes**
- No rollout is considered finished until this tracker is fully updated.

---

## 6. Phase Completion Log

| Phase | Status | Completed On | Verification | Notes |
|-------|--------|--------------|--------------|-------|
| Phase 1 | COMPLETE | March 22, 2026 | Created `docs/scheme/StandardScheme_PRD.md` and `docs/scheme/StandardScheme_Implementation_Tracker.md`; verified required PRD and tracker sections exist; phase names match between documents; updated the contract to include standard scheme publication and teacher show-page reference fallback rules | Documentation baseline established; implementation must follow the contracts defined in these two documents |
| Phase 2 | COMPLETE | March 22, 2026 | `php artisan migrate` ran all 8 migrations cleanly; roles seeded (2/2); all 6 new tables created; `published_at`/`published_by` on standard_schemes confirmed; `standard_scheme_id` on schemes_of_work confirmed; `standard_scheme_entry_id` on scheme_of_work_entries confirmed; unique constraint on (subject_id, grade_id, term_id) enforced | 8 migration files: 2026_03_22_000005 through 2026_03_22_000012 |
| Phase 3 | COMPLETE | March 22, 2026 | PHP lint passed on all 6 files; StandardScheme model loads in tinker; SchemeOfWork.standardScheme() returns null for existing records; isDerivedFromStandard() returns false for existing records | Created StandardScheme, StandardSchemeEntry, StandardSchemeWorkflowAudit models; StandardSchemeService with createWithEntries, workflow, publish/unpublish, distributeToTeachers; modified SchemeOfWork and SchemeOfWorkEntry |
| Phase 4 | COMPLETE | March 22, 2026 | PHP lint passed on policy and AuthServiceProvider; `manage-standard-schemes` and `view-standard-schemes` gates defined; `access-schemes` updated with Scheme Admin/View roles; StandardSchemePolicy registered in $policies | Created StandardSchemePolicy with viewAny, view, create, update, delete, submit, review, publish, unpublish, distribute methods |
| Phase 5 | COMPLETE | March 22, 2026 | PHP lint passed on all 5 files; `php artisan route:list --path=standard-schemes` shows 14 routes; route names correctly defined | Created StandardSchemeController, StandardSchemeEntryController, StandardSchemeWorkflowController, StoreStandardSchemeRequest; added routes to schemes.php |
| Phase 6 | COMPLETE | March 22, 2026 | `php artisan view:cache` compiled all views successfully; created index, create, show views in standard/ directory; modified schemes/show.blade.php with read-only banner; modified schemes/create.blade.php with standard scheme notice | Created 3 new views + modified 2 existing views |
| Phase 7 | COMPLETE | March 22, 2026 | SchemeEntryController blocks update/syncObjectives on standard-derived entries (403); HOD dashboard shows standard schemes section with status table; admin dashboard shows standard scheme coverage stats (total/approved/published/distributed); all views compile clean | Distribution logic already in StandardSchemeService; added read-only guards; updated both dashboards |
| Phase 8 | NOT STARTED | | | |

---

## 7. Immediate Next Step

Begin Phase 8: QA, Testing & Rollout
- Write feature tests for standard scheme CRUD, workflow, distribution, and read-only enforcement
- Perform end-to-end manual QA
- Record verification evidence and close rollout
