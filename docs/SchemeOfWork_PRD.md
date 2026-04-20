# Scheme of Work, Lesson Planning, and Assessment Alignment PRD

## Document Control

| Field | Value |
|---|---|
| Version | 1.2 (Implementation-Aligned) |
| Date | March 22, 2026 |
| Status | Draft - Implementation Plan Updated |
| Owner | Academic/Product Team |
| Scope | Junior first (F1-F3), architecture supports all school types |

---

## 1. Executive Summary

This module introduces a full curriculum planning pipeline:

`Syllabus -> Scheme of Work -> Lesson Plan -> Test/Markbook Coverage`

It reuses existing platform capabilities:

1. **Documents module** for central syllabus storage, versioning, workflow, and permissions.
2. **Assessment/markbook module** (`tests`, `student_tests`) for scoring and reports.
3. **Academic assignment models** (`KlassSubject` and `OptionalSubject`) to anchor schemes to real teaching allocations.

---

## 2. Problem Statement

Current gaps:

1. No structured in-system workflow from syllabus to scheme and lesson plans.
2. Teachers retype objectives repeatedly.
3. HODs cannot reliably track what is planned, taught, and assessed.
4. Markbook scores are not explicitly tied to syllabus objectives and topic coverage.

---

## 3. Planning Hierarchy and Assignment Model

## 3.1 Hierarchy

```
Syllabus (DMS document + structured topics/objectives)
  -> Scheme of Work (per teacher assignment, per term)
    -> Scheme Entry (weekly plan row)
      -> Lesson Plan (daily/period teaching plan)
        -> Linked Tests (objective/topic coverage evidence)
```

## 3.2 Teacher Assignment Sources (Mandatory Support)

The system must support both existing assignment models:

1. `klass_subject` via `App\Models\KlassSubject` (core/mandatory class-subject assignment).
2. `optional_subjects` via `App\Models\OptionalSubject` (elective grouping assignment).

A scheme belongs to exactly one source:

1. `klass_subject_id` set and `optional_subject_id` null, or
2. `optional_subject_id` set and `klass_subject_id` null.

Never both. Never neither.

---

## 4. Vision, Goals, and Non-Goals

## 4.1 Vision

Give teachers and HODs a reliable, auditable planning workflow tied directly to assessment evidence.

## 4.2 Goals

1. Centralize syllabus content and make objectives reusable.
2. Enable quick scheme creation with weekly structure.
3. Enable detailed lesson plans with prefill from scheme entries.
4. Link tests to planned/taught objectives.
5. Provide dashboards for teacher, HOD, and admin coverage oversight.

## 4.3 Non-Goals (Phase 1)

1. Replace existing assessment module.
2. Fully automatic curriculum authoring.
3. Parent-facing curriculum portal.

---

## 5. Functional Scope

## 5.1 In Scope

1. Syllabus management and objective structuring.
2. Scheme of work CRUD + workflow.
3. Lesson plan CRUD + taught-state tracking.
4. Objective copy/reference tooling.
5. Test linkage to scheme entries and objectives.
6. Coverage dashboards and reports.

## 5.2 Out of Scope (Initial)

1. Perfect OCR for low-quality scans.
2. Mandatory timetable auto-sync for period assignment.

## 5.3 Implementation Alignment Decisions (March 22, 2026)

The current codebase already contains substantial scheme functionality. The remaining work must follow these product and engineering decisions:

1. This PRD is the canonical planning document. `docs/SoW2.md` is retained for historical context only.
2. Approved schemes lock structural planning fields. Teachers may still create lesson plans and update delivery progress, but structural edits require admin unlock or a return-to-revision flow.
3. Schemes must be pinned to a specific syllabus revision. Do not resolve scheme content from whichever syllabus is currently active at view time.
4. `syllabi.grades` is the authoritative model for grade coverage. A syllabus may cover multiple grades, but a grade may only be covered by one active syllabus for the same subject and level.
5. Assessment linkage must work from both test create and test edit flows, and for both `klass_subject` and `optional_subject` assignments.
6. Phase 1 lesson plans remain scheme-linked only. Standalone lesson plans are deferred until they have their own assignment context and reporting rules.
7. Phase 1 cloning is limited to cloning the teacher's own scheme into another term. Approved-scheme libraries and HOD templates move to a later phase.
8. The simplified weekly-entry model remains intentional for Phase 1. Detailed pedagogy stays in lesson plans rather than being duplicated on scheme entries.

---

## 6. Module A - Syllabi Management (Central via Documents)

## 6.1 DMS Integration

Reuse existing documents module; no new storage subsystem.

Required setup:

1. Seed category `Syllabi` (or keep under existing `Curriculum` taxonomy with clear mapping).
2. Seed institutional folders by level:
   - `Primary Syllabi`
   - `Junior Syllabi`
   - `Senior Syllabi`
3. Upload through existing `/documents/create`.
4. Use internal visibility and existing approval/version controls.

## 6.2 Data Model (Syllabi)

### `syllabi`

- `id`
- `document_id` (FK `documents.id`, nullable)
- `subject_id` (FK `subjects.id`)
- `grades` (JSON array of grade names; e.g. `["F1", "F2"]`)
- `level` (`Primary`, `Junior`, `Senior`, `Pre-primary`)
- `description` (nullable)
- `is_active` (bool)
- `source_url` (nullable; remote JSON syllabus source)
- `cached_structure` (nullable JSON)
- `cached_at` (nullable)
- timestamps + soft deletes

Constraints:

1. A given grade may only be covered by one active syllabus for the same `subject_id + level`.
2. Once a syllabus is referenced by submitted/approved schemes or linked tests, revisions must be published as a new syllabus row rather than destructive edits to the in-use structure.

### `syllabus_topics`

- `id`
- `syllabus_id` (FK `syllabi.id`)
- `sequence`
- `name`
- `description` (nullable)
- `suggested_weeks` (nullable)
- `suggested_term` (nullable 1..3)
- timestamps

### `syllabus_objectives`

- `id`
- `syllabus_topic_id` (FK `syllabus_topics.id`)
- `sequence`
- `code` (nullable)
- `objective_text`
- `cognitive_level` (nullable)
- timestamps

---

## 7. Module B - Scheme of Work

## 7.1 Data Model

### `schemes_of_work`

- `id`
- `klass_subject_id` (FK `klass_subject.id`, nullable)
- `optional_subject_id` (FK `optional_subjects.id`, nullable)
- `syllabus_id` (FK `syllabi.id`, nullable during rollout, required once pinning migration lands)
- `teacher_id` (FK `users.id`)
- `term_id` (FK `terms.id`)
- `status` (`draft`, `submitted`, `supervisor_reviewed`, `under_review`, `approved`, `revision_required`)
- `total_weeks`
- `reviewed_at` (nullable)
- `reviewed_by` (FK `users.id`, nullable)
- `review_comments` (nullable)
- `supervisor_reviewed_at` (nullable)
- `supervisor_reviewed_by` (FK `users.id`, nullable)
- `supervisor_comments` (nullable)
- `cloned_from_id` (self FK, nullable)
- timestamps + soft deletes

Constraints:

1. Exactly one of `klass_subject_id` or `optional_subject_id` must be set.
2. Uniqueness is per assignment + term and is enforced at the application/service layer so soft-deleted rows do not block re-creation.
3. `grade_subject`, `grade`, and school year are derived from the selected assignment and term rather than duplicated on the scheme row in Phase 1.

### `scheme_of_work_entries`

- `id`
- `scheme_of_work_id` (FK `schemes_of_work.id`)
- `week_number`
- `topic`
- `sub_topic` (nullable)
- `syllabus_topic_id` (FK `syllabus_topics.id`, nullable)
- `learning_objectives` (nullable text)
- `duration` (nullable)
- `status` (`planned`, `in_progress`, `completed`, `skipped`)
- timestamps

### `scheme_entry_objectives` (pivot)

- `id`
- `scheme_entry_id` (FK `scheme_of_work_entries.id`)
- `syllabus_objective_id` (FK `syllabus_objectives.id`)
- `created_at`

Unique:

1. `scheme_entry_id + syllabus_objective_id`

## 7.2 Scheme Workflow

1. Teacher creates scheme from assignment picker (core + optional grouped options).
2. Weekly entries generated from term weeks (editable).
3. Objectives copied from syllabus topic browser into entry.
4. Submit for review.
5. If the teacher has a reporting-line supervisor who is not also the department reviewer, the scheme goes through a supervisor review step first.
6. HOD reviews and either:
   - Approves, or
   - Requests revision with comments.
7. Once approved, structural planning fields lock. Lesson plans and delivery progress continue.

Status transitions:

`draft -> submitted -> (supervisor_reviewed?) -> under_review -> approved`

`submitted -> revision_required`

`under_review -> revision_required -> submitted`

## 7.3 Cloning

Teachers can clone:

1. Their own previous term/year scheme into a new term.
2. Approved-scheme libraries and HOD templates are deferred until after syllabus pinning and approval locking are complete.

Clone behavior:

1. Copy entries and objective links.
2. New scheme starts at `draft`.
3. Track lineage via `cloned_from_id`.

---

## 8. Module C - Lesson Plans

## 8.1 Data Model

### `lesson_plans`

- `id`
- `scheme_entry_id` (FK `scheme_of_work_entries.id`, nullable)
- `teacher_id` (FK `users.id`)
- `scheme_of_work_id` (FK `schemes_of_work.id`, nullable)
- `date`
- `period` (nullable)
- `topic`
- `sub_topic` (nullable)
- `learning_objectives`
- `content` (nullable)
- `activities` (nullable)
- `teaching_learning_aids` (nullable)
- `lesson_evaluation` (nullable)
- `resources` (nullable)
- `homework` (nullable)
- `reflection_notes` (nullable)
- `status` (`planned`, `taught`, `cancelled`)
- `taught_at` (nullable)
- timestamps + soft deletes

## 8.2 Lesson Workflow

1. Teacher opens current week scheme entry.
2. Clicks `Create Lesson Plan`; topic/objectives prefill.
3. Adds lesson-specific details.
4. Marks lesson as taught after delivery.
5. Adds reflection and follow-up notes.
6. Multiple lessons can map to one scheme entry.

Phase 1 supports scheme-linked lesson plans only. Standalone lesson plans are deferred until they have explicit assignment, term, and reporting context.

---

## 9. Module D - Assessment/Markbook Integration

## 9.1 New Pivots

### `test_scheme_entries`

- `id`
- `test_id` (FK `tests.id`)
- `scheme_entry_id` (FK `scheme_of_work_entries.id`)
- `created_at`

### `test_syllabus_objectives`

- `id`
- `test_id` (FK `tests.id`)
- `syllabus_objective_id` (FK `syllabus_objectives.id`)
- `created_at`

## 9.2 Test Model Enhancements

Add relationships to `App\Models\Test`:

1. `schemeEntries()`
2. `syllabusObjectives()`

## 9.3 Test Creation/Update Integration

In existing assessment test setup flow:

1. Add optional scheme entry selector filtered by same subject + term context.
2. Add optional objective selector.
3. Persist mappings to both pivots.

## 9.4 Coverage Outputs

1. Objectives planned vs objectives assessed.
2. Scheme entry status vs test linkage.
3. Per-objective performance proxy from linked test scores.

---

## 10. Dashboards and Reports

## 10.1 Teacher Dashboard (`/schemes`)

1. My schemes this term.
2. Current week highlight.
3. Upcoming lesson plans.
4. Coverage snapshot.

## 10.2 HOD Dashboard (`/schemes/dashboard/hod`)

1. Pending reviews.
2. Department submission status.
3. Coverage by teacher/subject.

## 10.3 Admin Dashboard (`/schemes/dashboard/admin`)

1. School-wide completion.
2. Missing schemes by department.
3. Approval timeline metrics.

## 10.4 Reports

1. Scheme completion rate.
2. Lesson plan completion summary.
3. Assessment-objective alignment.
4. Topic coverage by class/subject.

---

## 10.5 UI/Theme Standards (Mandatory)

All new Scheme/Syllabus/Lesson Plan views must match Admissions module styling patterns from:

1. `resources/views/admissions/index.blade.php` (index/list pages)
2. `resources/views/admissions/admission-new.blade.php` (create/edit form pages)

### List/Index Page Pattern (Required)

1. Container and sections:
   - `.admissions-container` style equivalent (white card, radius `3px`, subtle shadow)
   - gradient header style equivalent to `.admissions-header`
   - `.admissions-body` spacing pattern
2. Help text style equivalent to `.help-text`, `.help-title`, `.help-content`.
3. Table header/row behavior must match Admissions:
   - light gray header background
   - hover row background
4. Primary action buttons must use same gradient/button behavior as Admissions.
5. Action icon buttons must follow same compact size behavior (`32px` square style pattern).

### Form Page Pattern (Required)

1. Use `.form-container`, `.page-header`, `.page-title`, `.section-title` visual structure.
2. Use the same responsive `.form-grid` pattern:
   - 3 columns desktop
   - 2 columns tablet
   - 1 column mobile
3. Inputs/selects must use the same sizing and spacing as Admissions:
   - `padding: 10px 12px`
   - consistent border radius `3px`
   - same focus state and border/shadow behavior
4. Every text-capable field must provide a meaningful placeholder.
5. Keep consistent control height/size across all input and select fields.

### Button Icons and Loading State (Required)

Use the same icon approach and loading animation behavior as Admissions:

1. Cancel button pattern with icon (e.g. `<i class="bx bx-x"></i>`).
2. Save/Create button with icon (e.g. `<i class="fas fa-save"></i>`).
3. Mandatory loading-button structure:

```html
<button type="submit" class="btn btn-primary btn-loading">
    <span class="btn-text"><i class="fas fa-save"></i> Save</span>
    <span class="btn-spinner d-none">
        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
        Saving...
    </span>
</button>
```

4. Mandatory loading CSS behavior:
   - `.btn-loading.loading .btn-text { display: none; }`
   - `.btn-loading.loading .btn-spinner { display: inline-flex !important; align-items: center; }`
   - disable button on submit while loading.
5. Form submit JS must toggle `loading` class and `disabled = true` on valid submit.

### Icon Libraries and Consistency

1. Use the same icon families already used in Admissions (`Font Awesome`, `Boxicons`).
2. Do not introduce a different icon set for Scheme module primary actions.

---

## 11. Authorization and Governance

## 11.1 Policy

`SchemeOfWorkPolicy`:

1. `viewAny`, `view`
2. `create`, `update`, `delete`
3. `submit`
4. `supervisorReview`
5. `review` (HOD/admin only)

HOD resolution source:

1. Subject department from `grade_subject.department_id`.
2. Reviewer roles via `Department.department_head` and `Department.assistant`.

## 11.2 Gates (Proposed)

1. `access-schemes`
2. `manage-syllabi`
3. `review-schemes`

## 11.3 Auditability

Track create/edit/submit/review/approve/revision/test-link actions in audit logs.

---

## 12. Technical Implementation Map

## 12.1 New Models

1. `Syllabus`
2. `SyllabusTopic`
3. `SyllabusObjective`
4. `SchemeOfWork`
5. `SchemeOfWorkEntry`
6. `LessonPlan`

## 12.2 Modified Model

1. `Test` (new relationships for pivots)

## 12.3 New Controllers

1. `Schemes/SyllabusController`
2. `Schemes/SchemeController`
3. `Schemes/SchemeEntryController`
4. `Schemes/LessonPlanController`
5. `Schemes/SchemeDashboardController`

## 12.4 New Services

1. `Schemes/SchemeService`
2. `Schemes/CoverageService`
3. `Schemes/SyllabusImportService`

## 12.5 Routing

1. Add `routes/schemes/schemes.php`.
2. Include in `routes/web.php`.
3. Protect with auth + `can:access-schemes`.

## 12.6 Platform Integration Files

1. `app/Providers/AuthServiceProvider.php` (gates + policy registration)
2. `app/Services/ModuleVisibilityService.php` (add `schemes` module key)
3. `resources/views/layouts/sidebar.blade.php` (menu entry)

## 12.7 Authoritative Schema Notes

Where this document differs from older drafts, follow these implementation-aligned rules:

1. `syllabi.grades` replaces single `grade_name`.
2. Remote JSON-backed syllabi are first-class via `source_url`, `cached_structure`, and `cached_at`.
3. `schemes_of_work` includes supervisor review metadata and must gain `syllabus_id` pinning as part of hardening.
4. `scheme_of_work_entries` remain intentionally slim in Phase 1: topic, sub-topic, objectives, duration, syllabus-topic link, and delivery status.
5. Lesson plans use the simplified content/activity/aids/evaluation structure already present in the codebase.

---

## 13. AI Assist (Optional, Manual-First, Fail-Safe)

AI is optional and must never block core workflows.

Rules:

1. All core flows work fully without AI.
2. AI only suggests; never auto-approves or auto-publishes.
3. If AI fails/timeouts, user continues manually with no data loss.
4. AI output is editable, labeled, and audit logged.

Recommended runtime order for reliability and cost:

1. Rule-based extraction first.
2. Local open model (free) second.
3. External fallback (e.g. OpenRouter) only for non-critical suggestions.

---

## 14. Rollout Plan

This rollout assumes the current codebase already contains the foundation for schemes, syllabus management, lesson plans, workflow, and assessment pivots. The next work should prioritize integrity and scope alignment before adding more surface area.

## Phase 0 - Freeze Scope and Make the Contract Explicit

1. Treat this PRD as canonical and mark older drafts as historical.
2. Update workflow diagrams and UI copy to include the supervisor review path where applicable.
3. Confirm and document the Phase 1 scope decisions:
   - approved schemes lock structural planning fields,
   - lesson plans are scheme-linked only,
   - cloning is own-scheme-to-new-term only,
   - weekly entries stay intentionally slim.
4. Move deferred items into a Phase 2 backlog:
   - standalone lesson plans,
   - approved-scheme libraries,
   - HOD templates,
   - richer weekly-entry fields,
   - timetable auto-linking.

## Phase 1 - Workflow Integrity Hardening

1. Enforce approved-lock semantics in policy, controller, and UI layers.
2. Allow delivery progress updates after approval, but block structural edits to planning content.
3. Normalize the teacher, supervisor, HOD, and admin review paths.
4. Ensure audit logs cover submit, supervisor review, HOD review, approval, revision, and clone actions.
5. Add feature tests for lock behavior, supervisor flow, resubmission, and optional-subject parity.

## Phase 2 - Pin Schemes to a Syllabus Revision

1. Add `schemes_of_work.syllabus_id` and persist it at scheme creation time.
2. Resolve show pages, objective validation, objective browsing, and assessment linkage from the pinned syllabus rather than the current active syllabus.
3. Introduce syllabus revision governance:
   - once a syllabus is used by submitted/approved schemes or linked tests, do not destructively rewrite its structure,
   - publish revisions as new syllabus rows and switch active status to the new revision.
4. Backfill existing schemes and flag ambiguous matches for manual review.

## Phase 3 - Complete Assessment Linking

1. Add AJAX endpoints for loading scheme entries and syllabus objectives by `grade_subject_id + term_id`.
2. Make the test create form dynamically populate linkable entries/objectives after subject selection.
3. Fix the test edit flow so it includes both `klass_subject` and `optional_subject` scheme paths.
4. Validate that linked entries and objectives belong to the selected subject/term context.
5. Add feature tests for create and edit linking, especially optional-subject coverage.

## Phase 4 - Reporting and Coverage Completeness

1. Extend missing-scheme reporting to `optional_subjects` as well as `klass_subject`.
2. Ensure teacher, HOD, and admin dashboards count optional-subject schemes in submission and coverage summaries.
3. Add mixed core/optional dataset tests for dashboard and report calculations.
4. Review dashboard copy so "coverage" consistently means planned vs taught vs assessed.

## Phase 5 - Adoption Readiness

1. Remove or correct any UI copy that still promises unsupported behavior.
2. Run end-to-end manual QA with teacher, supervisor, HOD, and admin personas.
3. Validate syllabus import and remote-cache flows against real curriculum sources.
4. Prepare short staff guidance for scheme creation, review, lesson planning, and assessment linking.

## Phase 6 - Phase 2 Backlog

1. Approved-scheme libraries and HOD templates.
2. Standalone lesson plans with explicit assignment context.
3. Richer weekly-entry pedagogy fields if departments still require them after rollout.
4. Timetable period auto-linking.

---

## 15. Acceptance Criteria

1. Admin can create or publish an active syllabus for one or more grades for the same subject and level.
2. Teacher can create a scheme from either a core or optional assignment, and the scheme is pinned to a specific syllabus revision.
3. Supervisor review works when required by reporting lines; otherwise the scheme routes directly to HOD review.
4. Once a scheme is approved, structural planning fields are locked while lesson plans and delivery progress remain usable.
5. Teachers can create lesson plans from scheme entries and mark them as taught with reflections.
6. Tests can link to scheme entries and syllabus objectives from both create and edit flows for both core and optional contexts.
7. Teacher, HOD, and admin coverage/reporting views include both core and optional assignments.
8. Historical schemes continue to resolve the same syllabus/objective structure even after a new syllabus revision is published.
9. Authorization and audit rules prevent unauthorized edits or reviews and record all workflow transitions.

---

## 16. Verification Plan

## 16.1 Manual Verification

1. Syllabus creation, publishing, and objective browsing work for a multi-grade syllabus.
2. Core-subject scheme flow works end-to-end, including pinned syllabus resolution.
3. Optional-subject scheme flow works end-to-end, including HOD/admin visibility.
4. Approved schemes block structural edits but still allow lesson plan creation and delivery-status updates.
5. Lesson plan prefill, taught marking, and reflection capture work from linked scheme entries.
6. Publishing a new syllabus revision does not change the syllabus seen by an older submitted/approved scheme.
7. Test create and test edit flows can link entries/objectives and update coverage metrics for both core and optional subjects.
8. Admin missing-scheme reporting shows missing core and optional assignments.
9. Deferred-scope check: standalone lesson plan creation is not exposed in Phase 1.

## 16.2 Technical Verification

1. `php artisan migrate` clean.
2. Route list includes schemes routes.
3. Feature tests for:
   - auth policy rules,
   - supervisor and HOD status transitions,
   - approved-lock semantics,
   - syllabus pinning and revision stability,
   - linkage integrity (`test_scheme_entries`, `test_syllabus_objectives`),
   - optional-subject reporting parity,
   - coverage calculations.

---

## 17. KPIs

1. >90% of assigned teachers submit schemes by mid-term.
2. >80% of lesson plans linked to scheme entries.
3. >70% of tests linked to at least one syllabus objective.
4. <48 hours median review turnaround.
5. 30% reduction in taught-but-unassessed objectives after two terms.

---

## 18. Risks and Mitigation

| Risk | Impact | Mitigation |
|---|---|---|
| Poor-quality syllabus scans | Weak extraction | Manual objective editor + import templates |
| Low adoption | Planning remains off-system | Clone templates, fast forms, onboarding |
| Overly strict workflow | Teachers blocked | Draft flexibility + admin override |
| Assessment linking ignored | Weak analytics | Test-form nudges and report visibility |
| AI instability | Workflow interruption | AI optional, manual-first fallback design |
| Doc/code drift | Incorrect implementation decisions | Keep this PRD canonical and update it when scope changes |
| Unpinned syllabus revisions | Historical schemes silently change | Pin schemes to syllabus revisions and publish changes as new syllabus rows |
| Optional-subject gaps | Elective coverage is invisible | Require optional-subject parity in create, edit, reporting, and tests |

---

## 19. Open Decisions

1. After rollout feedback, should standalone lesson plans return as a Phase 2 feature?
2. Should approved-scheme libraries and templates be school-wide, department-scoped, or both?
3. Should syllabus revision publishing be admin-only or shared with HODs under governance rules?
