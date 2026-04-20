# Combined School Modes Phase Tracker

**Reference Plan:** [COMBINED_SCHOOL_MODES_IMPLEMENTATION_PLAN.md](/Users/thatoobuseng/Sites/Junior/docs/COMBINED_SCHOOL_MODES_IMPLEMENTATION_PLAN.md)  
**Program Status:** NOT STARTED  
**Last Updated:** March 27, 2026

---

## 1. Status Rules

Use only these phase statuses:

- `NOT STARTED`
- `IN PROGRESS`
- `BLOCKED`
- `COMPLETE`

A phase is only `COMPLETE` when:

- all scoped tasks are done
- required tests or checks pass
- manual verification is complete
- notes and evidence are recorded in this tracker

---

## 2. Phase Overview

| Phase | Focus | Status | Depends On |
| --- | --- | --- | --- |
| Phase 1 | Mode foundation and regression guardrails | NOT STARTED | None |
| Phase 2 | Provisioning and canonical baseline data | NOT STARTED | Phase 1 |
| Phase 3 | Shared UI, navigation, and core workflow refactor | NOT STARTED | Phase 2 |
| Phase 4 | Assessment, finals, admissions, and portal dispatch | NOT STARTED | Phase 3 |
| Phase 5 | Data consolidation pipeline | NOT STARTED | Phases 2 and 4 |
| Phase 6 | Hardening, UAT, and production cutover | NOT STARTED | Phase 5 |

---

## 3. Detailed Phase Checklist

### Phase 1: Mode Foundation And Regression Guardrails

**Status:** NOT STARTED  
**Goal:** establish the shared mode/capability layer and protect current single-mode behavior.

**Tasks**

- [ ] Add runtime support for `PRE_F3` and `K12`
- [ ] Create a central mode resolver or capability service
- [ ] Define capability matrix for all five modes
- [ ] Add read-time normalization for legacy `Unified`
- [ ] Audit direct `school_setup.type` checks across app, routes, services, views, and tests
- [ ] Replace critical boot, auth, and navigation checks with capability calls
- [ ] Add regression tests for `Primary`, `Junior`, and `Senior`

**Completion Gate**

- [ ] Existing single-mode flows have baseline coverage
- [ ] Critical raw mode checks have been migrated
- [ ] Capability matrix is documented and referenced by implementation code

**Verification Notes**

- Automated:
- Manual:
- Evidence:

### Phase 2: Provisioning And Canonical Baseline Data

**Status:** NOT STARTED  
**Goal:** make combined-mode setup and upgrade repeatable and safe.

**Tasks**

- [ ] Create canonical grade matrix for `PRE_F3`
- [ ] Create canonical grade matrix for `K12`
- [ ] Create canonical subject matrix by level
- [ ] Build idempotent provisioning command/service
- [ ] Backfill grade-subject links for combined modes
- [ ] Backfill default tests and other level-specific defaults
- [ ] Update cache keys for combined mode and selected level
- [ ] Validate rerun safety on existing installations

**Completion Gate**

- [ ] Fresh combined-mode provisioning succeeds
- [ ] Existing installs can be upgraded without duplicates
- [ ] Cache behavior is mode-aware and level-aware

**Verification Notes**

- Automated:
- Manual:
- Evidence:

### Phase 3: Shared UI, Navigation, And Core Workflow Refactor

**Status:** NOT STARTED  
**Goal:** make the admin application usable in `PRE_F3` and `K12`.

**Tasks**

- [ ] Add combined-mode level filter to high-volume admin pages
- [ ] Update sidebar visibility rules for five-mode support
- [ ] Update dashboards for mixed-level counts and summaries
- [ ] Update classes and subject-allocation screens for combined modes
- [ ] Update optional-subject screens to operate only on valid levels
- [ ] Update setup and import-template screens for combined modes
- [ ] Update sponsor and student overview pages for mixed-level rendering

**Completion Gate**

- [ ] `PRE_F3` works for pre-primary, primary, and junior data
- [ ] `K12` works for pre-primary, primary, junior, and senior data
- [ ] Invalid senior-only behavior does not leak into `PRE_F3`

**Verification Notes**

- Automated:
- Manual:
- Evidence:

### Phase 4: Assessment, Finals, Admissions, And Portal Dispatch

**Status:** NOT STARTED  
**Goal:** route the most school-sensitive flows from actual grade level.

**Tasks**

- [ ] Make assessment landing and class pages grade-aware
- [ ] Make report-card generation grade-aware
- [ ] Keep junior finals active in `Junior`, `PRE_F3`, and `K12`
- [ ] Keep senior finals active in `Senior` and `K12`
- [ ] Keep senior admissions active only where senior levels exist
- [ ] Update student portal report-card preview/download dispatch
- [ ] Update sponsor portal report-card preview/download dispatch
- [ ] Refactor rollovers to execute level-specific behavior per grade

**Completion Gate**

- [ ] Mixed-grade report cards render correctly
- [ ] Finals behavior matches the capability matrix
- [ ] Senior admissions is available only in `Senior` and `K12`
- [ ] Rollovers succeed for mixed-level terms

**Verification Notes**

- Automated:
- Manual:
- Evidence:

### Phase 5: Data Consolidation Pipeline

**Status:** NOT STARTED  
**Goal:** safely merge existing deployments into combined targets.

**Tasks**

- [ ] Add migration batch table
- [ ] Add migration ID map table
- [ ] Add migration conflict table
- [ ] Implement preview command
- [ ] Implement final-run command
- [ ] Define term mapping rules
- [ ] Define grade mapping rules
- [ ] Define user, sponsor, and student identity rules
- [ ] Add deterministic FK remapping for imported records
- [ ] Produce preview conflict reports for legacy Primary, Junior, and Senior datasets

**Completion Gate**

- [ ] Preview reports are stable and repeatable
- [ ] Final import blocks on unresolved identity conflicts
- [ ] Imported data preserves referential integrity

**Verification Notes**

- Automated:
- Manual:
- Evidence:

### Phase 6: Hardening, UAT, And Production Cutover

**Status:** NOT STARTED  
**Goal:** complete readiness checks and execute safe rollout.

**Tasks**

- [ ] Run single-mode regression suite
- [ ] Run `PRE_F3` smoke test suite
- [ ] Run `K12` smoke test suite
- [ ] Validate performance and cache invalidation under mixed-level data
- [ ] Prepare backup and rollback runbook
- [ ] Prepare cutover checklist
- [ ] Execute migration preview rehearsal
- [ ] Complete user acceptance testing
- [ ] Record final go-live evidence

**Completion Gate**

- [ ] Existing modes remain stable
- [ ] Combined modes pass UAT
- [ ] Cutover and rollback instructions are complete

**Verification Notes**

- Automated:
- Manual:
- Evidence:

---

## 4. Completion Log

| Date | Phase | Status Change | Notes |
| --- | --- | --- | --- |
| 2026-03-27 | Program | Created | Initial tracker created alongside combined school modes implementation plan |
