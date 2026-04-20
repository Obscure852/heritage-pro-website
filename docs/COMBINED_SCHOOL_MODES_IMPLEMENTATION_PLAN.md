# Combined School Modes Implementation Plan

**Document Version:** 1.0  
**Last Updated:** March 27, 2026  
**Status:** Draft  
**Audience:** Product, engineering, QA, migration, and rollout teams

---

## 1. Objective

Add two new aggregate school modes on top of the existing single-school modes:

- `PRE_F3`: pre-primary through Form 3
- `K12`: pre-primary through Form 5

The system must continue to run correctly as:

- `Primary`
- `Junior`
- `Senior`
- `PRE_F3`
- `K12`

This work includes both application support and the data-consolidation path needed to merge the existing Primary, Junior, and Senior deployments into a combined deployment where required.

---

## 2. Mode Catalog

For planning purposes, use the following internal mode keys.

| Mode | Levels Included | Finals Support | Optional Subject Support | Senior Admissions Support |
| --- | --- | --- | --- | --- |
| `Primary` | `REC/Pre-primary`, `Primary` | No | No | No |
| `Junior` | `Junior` (`F1-F3`) | Junior only | Junior only | No |
| `Senior` | `Senior` (`F4-F5`) | Senior only | Senior only | Yes |
| `PRE_F3` | `REC/Pre-primary`, `Primary`, `Junior` | Junior only | Junior only | No |
| `K12` | `REC/Pre-primary`, `Primary`, `Junior`, `Senior` | Junior and Senior | Junior and Senior | Yes |

Notes:

- `PRE_F3` is the combined mode for the existing Primary and Junior product lines.
- `K12` is the combined mode for Primary, Junior, and Senior.
- If legacy data contains `Unified`, normalize it during upgrade:
  - default to `PRE_F3` when no senior grades, senior subjects, or senior finals data exist
  - normalize to `K12` when senior data is present

---

## 3. Current-State Findings

The current codebase is close to a combined system in data shape, but not in runtime behavior.

- Routes are loaded unconditionally, which means feature separation happens in controllers, services, gates, and views rather than in route registration.
- `school_setup.type` is treated as a single global mode in many places, including app bootstrapping, authorization, dashboards, imports, assessment routing, and rollovers.
- Grades and subjects already carry level information (`Pre-primary`, `Primary`, `Junior`, `Senior`), which is the main structural advantage for adding combined modes.
- Historical migrations seeded grades, subjects, grade-subject links, books, comment banks, and tests from the active school type at install time, so they cannot be relied on to provision a new combined mode for existing installs.
- The setup UI already contains a disabled `Unified` option, which indicates earlier intent for a combined mode but does not yet correspond to working backend behavior.
- Student, sponsor, assessment, admissions, and finals flows currently choose behavior largely from global school type instead of from the grade or student record being viewed.

---

## 4. Design Decisions

### 4.1 Mode Resolution

Introduce one authoritative mode and capability layer. All combined-mode behavior must resolve through a single service instead of ad hoc `type === 'Primary'` checks.

Required service responsibilities:

- current mode resolution
- supported levels for the mode
- grade-to-level resolution
- student-to-level resolution
- capability checks for finals, optionals, primary assessment, senior admissions, and related UI
- controller/view dispatch for assessment and portal flows

### 4.2 Combined Modes Are Presets, Not Replacements

Existing single-mode installations remain valid and supported. Combined modes are additive presets that widen the set of active levels inside one installation.

### 4.3 Record-Level Routing Wins

In combined modes, page filters may narrow what an admin sees, but student-facing and record-specific behavior must always follow the actual grade or level attached to the record.

### 4.4 Provisioning Must Be Repeatable

Historical migrations are not the provisioning mechanism for combined modes. Add a repeatable, idempotent provisioning path that can:

- create missing canonical grades
- create missing master subjects
- create missing grade-subject links
- create missing default tests
- create missing level-specific defaults

### 4.5 Migration Must Be Preview-First

Data consolidation must never silently merge live entities. All combined-mode migrations must support:

- dry-run preview
- deterministic ID mapping
- conflict capture
- repeatable reruns
- rollback and backup checkpoints

---

## 5. Public Interface Changes

The implementation should standardize around the following public behavior:

- `school_setup.type` accepts `Primary`, `Junior`, `Senior`, `PRE_F3`, and `K12`
- a central resolver/service exposes mode, supported levels, and capability checks
- admin pages gain a persisted level filter in combined modes
- a provisioning command exists for mode initialization or upgrade
- migration commands exist for preview and final import

Suggested command surface:

- `php artisan school:provision-mode --mode=PRE_F3`
- `php artisan school:provision-mode --mode=K12`
- `php artisan combined:migration:preview`
- `php artisan combined:migration:run`

---

## 6. Implementation Phases

### Phase 1: Mode Foundation And Regression Guardrails

**Goal:** create the shared mode/capability layer and protect current single-mode behavior before feature refactors begin.

**Scope**

- Create the central mode resolver and capability service.
- Update `SchoolSetup` helpers and setup validation to recognize `PRE_F3` and `K12`.
- Replace touched direct school-type checks with capability calls.
- Capture an inventory of affected controllers, views, gates, services, cache keys, imports, and tests.
- Add regression tests for existing `Primary`, `Junior`, and `Senior` modes before combined-mode changes land.

**Deliverables**

- shared mode/capability service
- initial replacement of direct mode branching in core boot and authorization points
- regression baseline for existing single-mode installs

**Exit Criteria**

- no new work depends directly on raw `school_setup.type` comparisons
- existing single-mode behavior is covered by baseline tests
- legacy `Unified` normalization rules are documented and implemented at read time

### Phase 2: Provisioning And Canonical Baseline Data

**Goal:** make it possible to initialize or upgrade a deployment into `PRE_F3` or `K12` without replaying historical migrations.

**Scope**

- Add an idempotent provisioning service/command for combined modes.
- Provision canonical grades:
  - `PRE_F3`: `REC` through `F3`
  - `K12`: `REC` through `F5`
- Provision master subjects, grade-subject links, default tests, and other level-bound defaults from canonical matrices rather than legacy migration branches.
- Make cache keys and lookup services mode-aware and level-aware.
- Support safe reruns so an existing installation can be upgraded without duplicate rows.

**Deliverables**

- combined-mode provisioner
- canonical grade and subject matrices
- idempotent backfill behavior for existing terms

**Exit Criteria**

- a fresh combined-mode install can provision all required levels
- an existing install can be upgraded in place without duplicate canonical data
- cache behavior does not bleed data between mode or level contexts

### Phase 3: Shared UI, Navigation, And Core Workflow Refactor

**Goal:** make combined modes usable in the shared admin application.

**Scope**

- Add combined-mode level filters to high-volume admin pages.
- Update sidebar visibility and menu rules so features appear by capability and level, not only by global mode.
- Update dashboards, classes, subjects, optional-subject screens, and setup pages for mixed-level data.
- Update import template generation and import screens to expose the right template by combined mode and target level.
- Ensure sponsor and student overview screens render correct level-specific sections in combined modes.

**Deliverables**

- combined-mode navigation
- level-filtered list pages
- level-aware import and setup screens

**Exit Criteria**

- admins can navigate primary and junior data inside `PRE_F3`
- admins can navigate primary, junior, and senior data inside `K12`
- invalid flows are still hidden, such as senior-only settings in `PRE_F3`

### Phase 4: Assessment, Finals, Admissions, And Portal Dispatch

**Goal:** refactor the most mode-sensitive workflows to dispatch from grade or student level.

**Scope**

- Update assessment landing pages, markbooks, gradebooks, and report-card generation so they select the correct controller/view per grade level.
- Keep primary assessment logic on primary-grade records, junior assessment logic on junior-grade records, and senior assessment logic on senior-grade records.
- Allow junior finals in `PRE_F3`.
- Allow junior and senior finals in `K12`.
- Keep senior admissions and placement features enabled only where senior levels exist.
- Update student and sponsor portals so report card preview and download use the correct grade-based renderer in combined modes.
- Update rollovers so level-specific behavior runs per grade, not once per global school type.

**Deliverables**

- grade-aware assessment dispatch
- combined-mode finals gating
- senior admissions coexistence inside `K12`
- grade-aware student and sponsor portal flows

**Exit Criteria**

- report cards resolve correctly for mixed-level students in one deployment
- finals and admissions appear only where their grade ranges support them
- rollovers work for mixed-level terms without skipping or over-applying level-specific logic

### Phase 5: Data Consolidation Pipeline

**Goal:** provide a safe path to merge the existing Primary, Junior, and Senior deployments into one combined deployment.

**Scope**

- Add migration batch, ID-map, and conflict tables.
- Add preview and final-run import commands.
- Define the import order for:
  - terms
  - grades
  - classes
  - users
  - sponsors
  - students
  - allocations
  - assessments
  - finals
  - ancillary modules
- Map terms by `year + term`.
- Map grades by canonical `name + level`.
- Merge users by unique email.
- Merge sponsors by unique `connect_id`.
- Merge students only when `connect_id` and core identity fields agree; otherwise create conflicts for manual review.
- Treat the target combined deployment as the authority for final mode selection and setup metadata.

**Deliverables**

- migration tables
- preview and run commands
- conflict reporting workflow
- deterministic FK remapping rules

**Exit Criteria**

- dry-run previews produce stable output
- unresolved identity collisions block final import
- three legacy deployments can be mapped into one `PRE_F3` or `K12` target without orphaned relationships

### Phase 6: Hardening, UAT, And Production Cutover

**Goal:** validate readiness, execute rollout, and protect rollback paths.

**Scope**

- Run single-mode regression suites.
- Run mixed-mode smoke tests for `PRE_F3` and `K12`.
- Validate performance and cache invalidation with multi-level data.
- Prepare runbooks for backup, preview import, final import, validation, and rollback.
- Execute user acceptance testing with representative primary, junior, and senior records.
- Freeze writes during final consolidation cutover where required.

**Deliverables**

- QA evidence
- rollout runbook
- cutover checklist
- rollback instructions

**Exit Criteria**

- existing single-mode installs remain stable
- combined modes pass UAT
- migration cutover can be repeated from documented steps

---

## 7. Risks And Mitigations

| Risk | Impact | Mitigation |
| --- | --- | --- |
| Direct mode checks remain in untouched code paths | Hidden regressions in combined modes | Maintain an audit checklist and block phase completion until critical paths are migrated |
| Historical migrations are assumed to provision combined modes | Missing canonical data on upgrade | Use only the new provisioner for combined-mode setup and upgrade |
| Combined-mode caches return stale or cross-level data | Wrong dashboards, imports, or list results | Convert cache keys to include mode and selected level where applicable |
| Silent identity merges during consolidation | Data corruption | Make preview mandatory and block final import on unresolved conflicts |
| Senior-only logic leaks into `PRE_F3` | Invalid features in the smaller combined mode | Capability matrix must explicitly differ between `PRE_F3` and `K12` |

---

## 8. Acceptance Criteria

The work is complete only when all of the following are true:

- the system supports five valid runtime modes: `Primary`, `Junior`, `Senior`, `PRE_F3`, and `K12`
- `PRE_F3` works from pre-primary through Form 3 with junior finals but without senior-only behavior
- `K12` works from pre-primary through Form 5 with both junior and senior behavior where appropriate
- existing single-mode installs are not broken
- combined-mode provisioning is idempotent
- assessment and portal rendering are driven by actual grade level
- migration preview and final import workflows exist and are repeatable
- rollout documentation is complete enough for production cutover

---

## 9. Recommended Deliverables In This Repo

- `docs/COMBINED_SCHOOL_MODES_IMPLEMENTATION_PLAN.md`
- `docs/COMBINED_SCHOOL_MODES_PHASE_TRACKER.md`

These two documents should be kept in sync. The implementation plan is the narrative source of truth; the tracker is the execution and rollout checklist.
