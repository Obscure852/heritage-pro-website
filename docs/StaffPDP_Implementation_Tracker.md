# Staff PDP Implementation Tracker

**Reference PRD:** [StaffPDP_PRD.md](/Users/thatoobuseng/Sites/Junior/docs/StaffPDP_PRD.md)  
**Module Status:** PHASE 5 IN PROGRESS  
**Last Updated:** March 12, 2026

---

## 1. Purpose

This document is the working tracker for the Staff PDP implementation. It breaks the work into execution phases and defines the conditions for marking each phase complete.

A phase is only complete when:
- all scoped tasks for that phase are done
- the required automated tests pass
- the listed manual verification checks pass
- the phase completion row in this document is updated

Current repo state at the time this tracker was created:
- The PRD exists at [StaffPDP_PRD.md](/Users/thatoobuseng/Sites/Junior/docs/StaffPDP_PRD.md)
- There is no PDP implementation in `app/`, `database/`, `resources/`, or `routes/` yet

---

## 2. How To Use This Tracker

1. Move a phase to `IN PROGRESS` when implementation starts.
2. Check off tasks as they are completed.
3. Run the verification items for that phase.
4. Only then mark the phase `COMPLETE`.
5. Record the completion date and a short note in the completion log.

Status values to use:
- `NOT STARTED`
- `IN PROGRESS`
- `BLOCKED`
- `COMPLETE`

---

## 3. Phase Overview

| Phase | Focus | Status | Depends On |
|------|-------|--------|------------|
| Phase 1 | Template foundation and persistence | COMPLETE | None |
| Phase 2 | Plan instances and mapped data resolution | COMPLETE | Phase 1 |
| Phase 3 | Generic PDP UI and section entry CRUD | IN PROGRESS | Phase 2 |
| Phase 4 | Reviews, scoring, locking, and signatures | IN PROGRESS | Phase 3 |
| Phase 5 | PDF output, template admin, reporting, and rollout hardening | IN PROGRESS | Phase 4 |

---

## 4. Detailed Phases

### Phase 1: Template Foundation and Persistence

**Status:** COMPLETE  
**Goal:** Build the reusable template/configuration layer that makes the PDP engine non-hardcoded.

**Implementation targets**
- `database/migrations/` for PDP template tables
- `app/Models/Pdp/`
- `app/Services/Pdp/`
- `database/seeders/`
- `tests/Feature/Pdp/`
- `tests/Unit/Pdp/`

**Scope**
- [x] Create migrations for:
  - [x] `pdp_templates`
  - [x] `pdp_template_sections`
  - [x] `pdp_template_fields`
  - [x] `pdp_template_periods`
  - [x] `pdp_template_rating_schemes`
  - [x] `pdp_template_approval_steps`
- [x] Add reusable staff profile metadata support for mapped PDP fields
- [x] Add a PDP settings mechanism if needed for active template and module defaults
- [x] Create Eloquent models, casts, and relationships for all template tables
- [x] Create `PdpTemplateService`
- [x] Seed the default school PDP template
- [x] Seed the alternate official DPSM template
- [x] Enforce template version immutability after publish
- [x] Create tests for template creation, publish rules, activation rules, and seeding

**Phase 1 completion gate**
- [x] Automated: template migrations run cleanly
- [x] Automated: template seeders run cleanly
- [x] Automated: PDP template feature/unit tests pass
- [x] Manual: confirm both seeded templates exist and the school template is the active default
- [x] Manual: confirm no hardcoded school-only values are stored outside template/config seed data

**Suggested verification commands**
```bash
php artisan test --filter=PdpTemplate
php artisan test --filter=PdpSettings
```

**Notes**
- Do not add one-off PDP-only columns to `users` unless the field is broadly needed outside PDP.

---

### Phase 2: Plan Instances and Mapped Data Resolution

**Status:** COMPLETE  
**Goal:** Build the data structures and services for plans created from published template versions.

**Implementation targets**
- `database/migrations/`
- `app/Models/Pdp/`
- `app/Services/Pdp/`
- `app/Http/Controllers/Pdp/`
- `tests/Feature/Pdp/`
- `tests/Unit/Pdp/`

**Scope**
- [x] Create migrations for:
  - [x] `pdp_plans`
  - [x] `pdp_plan_reviews`
  - [x] `pdp_plan_section_entries`
  - [x] `pdp_plan_signatures`
- [x] Create plan/review/entry/signature models and relationships
- [x] Create `PdpPlanService`
- [x] Create mapped value resolution for:
  - [x] user fields
  - [x] settings
  - [x] staff profile metadata
  - [x] computed values
- [x] Create plan creation flow from a published template version
- [x] Bind each created plan to one immutable template version
- [x] Add generic plan status handling: `draft`, `active`, `completed`, `cancelled`
- [x] Add tests for plan creation, template binding, overlap rules, and mapped field hydration

**Phase 2 completion gate**
- [x] Automated: plan migrations run cleanly
- [x] Automated: plan creation tests pass
- [x] Automated: mapped field resolution tests pass
- [x] Manual: create a plan and verify the right template version, periods, and mapped staff values are attached
- [x] Manual: verify an older plan remains bound to its template after seeding or activating a new template version

**Suggested verification commands**
```bash
php artisan test --filter=PdpPlan
php artisan test --filter=PdpMapping
```

**Notes**
- `current_period_key` belongs on the plan; period-specific state belongs on `pdp_plan_reviews`.
- Local verification for this phase used `DYLD_FALLBACK_LIBRARY_PATH=/tmp/pdp-php-libs DB_CONNECTION=sqlite DB_DATABASE=/tmp/pdp-phase2.sqlite php vendor/bin/phpunit tests/Feature/Pdp tests/Unit/Pdp` because the machine's Homebrew PHP linkage still breaks the default `php artisan test` path.

---

### Phase 3: Generic PDP UI and Section Entry CRUD

**Status:** IN PROGRESS  
**Goal:** Render PDP plans from template definitions and support generic entry CRUD without section-specific hardcoding.

**Implementation targets**
- `routes/staff/pdp.php`
- `routes/web.php`
- `app/Http/Controllers/Pdp/`
- `app/Services/Pdp/`
- `resources/views/pdp/`
- `tests/Feature/Pdp/`

**Scope**
- [x] Add PDP route file include to `routes/web.php`
- [x] Create generic PDP controllers for:
  - [x] template-backed plan CRUD
  - [x] section entry CRUD
  - [x] employee "My PDP" view
- [x] Create generic renderer/view-model service for template sections and fields
- [x] Create reusable field components for:
  - [x] text
  - [x] textarea/rich text
  - [x] number/date/select
  - [x] rating scale
  - [x] structured table
  - [x] metric pair
  - [x] comments
  - [x] attachments
  - [x] signature display
- [x] Create reusable section components for:
  - [x] profile summary
  - [x] repeatable sections
  - [x] review summary
  - [x] comments block
  - [x] signature block
- [x] Support add/edit/delete for repeatable section entries using section keys, not hardcoded objective/attribute endpoints
- [x] Add validation messaging for template-driven required fields
- [x] Add feature tests for create/edit/show flows and section entry CRUD

**Phase 3 completion gate**
- [x] Automated: plan CRUD and section entry feature tests pass
- [ ] Manual: create and edit a school-template PDP from the browser
- [ ] Manual: verify repeatable entries work for objectives, coaching, attributes, and development goals through the same generic mechanism
- [ ] Manual: verify mapped fields display correctly and read-only/computed fields are protected

**Suggested verification commands**
```bash
php artisan test --filter=PdpCrud
php artisan test --filter=PdpSectionEntry
```

**Notes**
- Do not create separate permanent route families for objectives, coaching, attributes, and goals if the same generic section-entry pattern covers them.
- Automated verification for this phase used `DYLD_FALLBACK_LIBRARY_PATH=/tmp/pdp-php-libs DB_CONNECTION=sqlite DB_DATABASE=/tmp/pdp-phase3.sqlite php vendor/bin/phpunit tests/Feature/Pdp tests/Unit/Pdp`.
- PDP currently renders through a lightweight module layout instead of the full application sidebar; sidebar/navigation integration remains part of Phase 5.

---

### Phase 4: Reviews, Scoring, Locking, and Signatures

**Status:** IN PROGRESS  
**Goal:** Implement the configurable review engine, calculations, role-based editability, and approval workflow.

**Implementation targets**
- `app/Services/Pdp/`
- `app/Policies/` or existing authorization layer
- `app/Http/Controllers/Pdp/`
- `resources/views/pdp/`
- `tests/Feature/Pdp/`
- `tests/Unit/Pdp/`

**Scope**
- [x] Create `PdpReviewService`
- [x] Create `PdpScoringService`
- [x] Implement period open/close rules from template period definitions
- [x] Implement template-driven section/field editability by role and stage
- [x] Implement score calculation for:
  - [x] direct percentage input
  - [x] intensity scale conversion
  - [x] weighted summaries
  - [x] rating band lookup
- [x] Store computed summaries on reviews/plans as designed
- [x] Implement approval step signing flow from template approval definitions
- [x] Enforce signing order and locking rules
- [x] Add authorization coverage for employee, supervisor, authorized official, and admin actions
- [x] Add tests for scoring correctness, period transitions, locks, signatures, and permissions

**Phase 4 completion gate**
- [x] Automated: scoring tests pass for school and DPSM templates
- [x] Automated: review transition and signature tests pass
- [x] Automated: authorization tests pass
- [ ] Manual: complete a school-template mid-year and year-end review
- [ ] Manual: confirm fields lock correctly after review closure/sign-off
- [ ] Manual: confirm the alternate DPSM template can use its own period cadence and approval chain without code changes

**Suggested verification commands**
```bash
php artisan test --filter=PdpReview
php artisan test --filter=PdpScoring
php artisan test --filter=PdpSignature
php artisan test --filter=PdpAuthorization
```

**Notes**
- Hardcoded weights, labels, and signature order are not allowed in service code.
- Reuse the existing user signature source on `users.signature_path`; PDP signature records should store signer, step, status, timestamp, and comments, not duplicate signature image storage.
- Automated verification for this phase used `DYLD_FALLBACK_LIBRARY_PATH=/tmp/pdp-php-libs DB_CONNECTION=sqlite DB_DATABASE=/tmp/pdp-phase4.sqlite php vendor/bin/phpunit tests/Feature/Pdp tests/Unit/Pdp`.
- Plan completion now waits for plan-level sign-off; the last review close computes and locks period data, but final completion occurs after the required plan-level approval steps are signed.

---

### Phase 5: PDF Output, Template Admin, Reporting, and Rollout Hardening

**Status:** IN PROGRESS  
**Goal:** Finish the module for production use with template administration, printable output, reporting, and implementation hardening.

**Implementation targets**
- `app/Http/Controllers/Pdp/`
- `app/Services/Pdp/`
- `resources/views/pdp/`
- `resources/views/layouts/` or sidebar partials
- `tests/Feature/Pdp/`

**Scope**
- [ ] Create `PdpPdfService`
- [ ] Implement template-driven PDF rendering using the plan's template version
- [ ] Implement template admin flows for:
  - [x] create draft template
  - [x] clone template version
  - [x] publish template
  - [x] activate template
  - [x] archive template
- [x] Add template management views
- [x] Add PDP listing/history/reporting views
- [x] Add sidebar/menu integration
- [x] Add audit-friendly display of template version used by each plan
- [x] Add tests for PDF access, template admin actions, and reporting access
- [ ] Run end-to-end smoke test using the default school template

**Phase 5 completion gate**
- [x] Automated: PDF/template admin/reporting tests pass
- [ ] Manual: export a school-template PDP PDF and verify the layout reflects template configuration
- [ ] Manual: export or preview the alternate DPSM template and verify section order, rating summary style, and signature flow differ by template only
- [ ] Manual: activate a new template version and confirm old plans remain unchanged
- [ ] Manual: verify PDP navigation is available only to the right roles

**Suggested verification commands**
```bash
php artisan test --filter=PdpPdf
php artisan test --filter=PdpTemplateAdmin
php artisan test --filter=PdpReporting
```

**Notes**
- No phase should be marked complete until at least one browser-level smoke test is done against seeded data.
- Automated verification for this phase used `touch /tmp/pdp-phase5.sqlite && DYLD_FALLBACK_LIBRARY_PATH=/tmp/pdp-php-libs DB_CONNECTION=sqlite DB_DATABASE=/tmp/pdp-phase5.sqlite php vendor/bin/phpunit tests/Feature/Pdp tests/Unit/Pdp`.

---

## 5. Cross-Phase Rules

- [ ] Every phase must add or update tests in `tests/Feature/Pdp/` or `tests/Unit/Pdp/`
- [ ] Every new service/model/controller should live in PDP-specific namespaces/folders
- [ ] No phase may introduce a hardcoded dependency on the school template structure
- [ ] Old plans must remain version-stable when templates change
- [ ] All completion updates in this tracker must include a date and short evidence note

---

## 6. Phase Completion Log

| Phase | Status | Completed On | Evidence / Notes |
|------|--------|--------------|------------------|
| Phase 1 | COMPLETE | March 12, 2026 | Added Phase 1 schema/models/services/seeders/tests. Verified with `DYLD_FALLBACK_LIBRARY_PATH=/tmp/pdp-php-libs DB_CONNECTION=sqlite DB_DATABASE=/tmp/pdp-phase1.sqlite php vendor/bin/phpunit tests/Feature/Pdp/PdpTemplateServiceTest.php tests/Unit/Pdp/PdpSettingsServiceTest.php tests/Unit/Pdp/UserProfileMetadataTest.php` after working around the local broken Homebrew PHP linkage for `php artisan test`. |
| Phase 2 | COMPLETE | March 12, 2026 | Added plan/review/section-entry/signature schema, PDP plan service, school/settings/profile/computed mapping resolution, overlap protection, immutable template binding, and signature reuse via `users.signature_path`. Verified with `DYLD_FALLBACK_LIBRARY_PATH=/tmp/pdp-php-libs DB_CONNECTION=sqlite DB_DATABASE=/tmp/pdp-phase2.sqlite php vendor/bin/phpunit tests/Feature/Pdp tests/Unit/Pdp`. |
| Phase 3 | IN PROGRESS |  | Code and automated tests are in place; browser-level smoke checks remain before this phase can be marked complete. |
| Phase 4 | IN PROGRESS |  | Code and automated tests are in place; browser/manual scoring and approval smoke checks remain before this phase can be marked complete. |
| Phase 5 | IN PROGRESS |  | Phase 5 routes, template admin workflow, reporting dashboard, PDF preview/download, sidebar integration, and automated coverage are in place. Manual/browser smoke checks still need to be completed before marking this phase complete. |

---

## 7. Immediate Next Step

Finish the remaining **manual smoke checks** across Phases 3 to 5:
- create and edit a school-template PDP from the browser and verify mapped/read-only protection
- complete mid-year and year-end review, locking, and sign-off flows
- export/preview both the school and DPSM template versions and confirm template-driven differences without code changes
- activate a new template version and confirm existing plans remain bound to their original template version
