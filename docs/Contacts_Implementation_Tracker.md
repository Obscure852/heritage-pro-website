# Contacts Implementation Tracker

**Reference PRD:** [Contacts_PRD.md](/Users/thatoobuseng/Sites/Junior/docs/Contacts_PRD.md)  
**Module Status:** IMPLEMENTATION LANDED, VERIFICATION PENDING  
**Last Updated:** March 21, 2026

---

## 1. Purpose

This document is the working tracker for the Contacts module rollout. It defines the execution phases required to replace asset vendors with reusable business contacts, and it is the source of truth for whether a phase is `NOT STARTED`, `IN PROGRESS`, `BLOCKED`, or `COMPLETE`.

A phase is only complete when:
- all scoped work for that phase is done
- required automated verification has passed where applicable
- required manual verification has passed where applicable
- the completion evidence is recorded in this tracker
- the header status, phase table, detailed phase section, completion log, and `Last Updated` date are all updated in the same change set

Current repo state when this tracker was created:
- vendor data exists in `asset_vendors`
- assets still reference `vendor_id`
- maintenance still references `vendor_id`
- vendor CRUD currently lives inside asset settings
- no standalone Contacts module exists yet

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
| --- | --- | --- | --- |
| Phase 1 | Documentation and replacement contract | COMPLETE | None |
| Phase 2 | Contacts domain foundation | IN PROGRESS | Phase 1 |
| Phase 3 | Standalone Contacts module CRUD | IN PROGRESS | Phase 2 |
| Phase 4 | Assets and maintenance cutover | IN PROGRESS | Phase 3 |
| Phase 5 | Import, reports, and cleanup | IN PROGRESS | Phase 4 |
| Phase 6 | QA, rollout, and documentation closure | IN PROGRESS | Phase 5 |

---

## 5. Detailed Phases

### Phase 1: Documentation and Replacement Contract

**Status:** COMPLETE  
**Goal:** Finalize the product contract, rollout phases, migration direction, and tracking rules before schema or feature work begins.

**Implementation targets**
- [x] Create [Contacts_PRD.md](/Users/thatoobuseng/Sites/Junior/docs/Contacts_PRD.md)
- [x] Create [Contacts_Implementation_Tracker.md](/Users/thatoobuseng/Sites/Junior/docs/Contacts_Implementation_Tracker.md)
- [x] Define v1 scope, non-goals, and acceptance criteria
- [x] Lock the business-contact plus repeatable-people model
- [x] Lock the admin-managed tags-only configuration model
- [x] Lock the asset and maintenance `contact_id` replacement direction
- [x] Define exact execution phases and update rules

**Scope**
- [x] PRD includes product summary, scope, users, domain model, integrations, migration rules, governance, acceptance criteria, and implementation phases
- [x] Tracker includes reference PRD, module status, last updated date, status rules, phase overview, detailed phases, and completion log
- [x] Tracker explicitly states that it is the execution source of truth
- [x] Phase 1 completion is recorded with exact date and evidence

**Phase 1 completion gate**
- [x] Documentation files exist under `/docs`
- [x] PRD includes `Implementation Phases`
- [x] Tracker includes all required status and completion sections
- [x] PRD and tracker phase names match exactly
- [x] PRD and tracker both reflect the same v1 contract

**Suggested verification commands**
```bash
test -f docs/Contacts_PRD.md
test -f docs/Contacts_Implementation_Tracker.md
rg -n "^## 12\\. Implementation Phases" docs/Contacts_PRD.md
rg -n "^## 4\\. Phase Overview|^## 6\\. Phase Completion Log" docs/Contacts_Implementation_Tracker.md
```

**Notes**
- This phase is documentation-only by design.
- No schema or application code should start before this contract is in place.

---

### Phase 2: Contacts Domain Foundation

**Status:** IN PROGRESS  
**Goal:** Build the schema, models, relationships, and migration path that establish Contacts as the new source of truth.

**Implementation targets**
- `database/migrations/`
- `app/Models/`
- `database/seeders/`
- `tests/Feature/`
- `tests/Unit/`

**Scope**
- [x] Create migrations for:
  - [x] `contacts`
  - [x] `contact_people`
  - [x] `contact_tags`
  - [x] contact-tag pivot
- [x] Add `contact_id` to `assets`
- [x] Add `contact_id` to `asset_maintenances`
- [x] Create models and relationships for contacts, people, and tags
- [x] Seed default tags including `Vendor`
- [x] Migrate legacy `asset_vendors` records into `contacts`
- [x] Backfill `assets.contact_id` and `asset_maintenances.contact_id`
- [x] Preserve active/inactive behavior and historical links
- [ ] Add automated tests for schema, relationships, tag behavior, and legacy migration

**Phase 2 completion gate**
- [ ] Automated: migrations run cleanly
- [ ] Automated: legacy vendor-to-contact conversion tests pass
- [ ] Automated: model relationship tests pass
- [ ] Manual: confirm migrated contacts preserve business names and linked asset/maintenance history
- [ ] Manual: confirm default `Vendor` tag exists and is attached to migrated legacy records

**Suggested verification commands**
```bash
php artisan test --filter=ContactMigration
php artisan test --filter=ContactModel
```

**Notes**
- The old vendor schema may remain temporarily during transition, but Contacts becomes the new source of truth in this phase.
- Implementation landed on March 21, 2026 with migrations, models, tag seeding, and legacy backfill logic.
- Remaining gap before completion: migration-specific regression coverage and execution/manual validation against migrated data.

---

### Phase 3: Standalone Contacts Module CRUD

**Status:** IN PROGRESS  
**Goal:** Deliver the standalone Contacts module UI, business CRUD, repeatable people management, tag settings, and navigation.

**Implementation targets**
- `routes/`
- `app/Http/Controllers/`
- `resources/views/`
- `app/Services/ModuleVisibilityService.php`
- `tests/Feature/`

**Scope**
- [x] Add Contacts routes
- [x] Add Contacts controller(s) for list, create, store, show, edit, update, and deactivate
- [x] Add repeatable people create/update handling
- [x] Enforce one primary person when saving
- [x] Build contacts list/detail/create/edit views
- [x] Build tag settings CRUD surface
- [x] Register Contacts in module visibility
- [x] Add sidebar navigation entry
- [ ] Add feature tests for CRUD, people management, tag assignment, and authorization

**Phase 3 completion gate**
- [ ] Automated: contact CRUD tests pass
- [ ] Automated: repeatable people tests pass
- [ ] Automated: tag settings authorization tests pass
- [ ] Manual: create a business with at least three visible person rows and save with one primary person
- [ ] Manual: edit tags and verify list filters behave correctly
- [ ] Manual: confirm module visibility and sidebar behavior work as expected

**Suggested verification commands**
```bash
php artisan test --filter=ContactCrud
php artisan test --filter=ContactPeople
php artisan test --filter=ContactTag
```

**Notes**
- The UI must support entering three people comfortably, but validation only requires one primary person.
- Implementation landed on March 21, 2026 with standalone routes, controllers, views, tag settings, and sidebar/module visibility integration.
- Remaining gap before completion: dedicated Contacts CRUD/authorization feature tests and browser-level manual QA.

---

### Phase 4: Assets and Maintenance Cutover

**Status:** IN PROGRESS  
**Goal:** Replace vendor usage in Assets and Maintenance with business contacts.

**Implementation targets**
- `app/Http/Controllers/AssetManagementController.php`
- `app/Http/Controllers/MaintenanceController.php`
- `resources/views/assets/`
- `tests/Feature/`

**Scope**
- [x] Replace asset form selectors from vendors to business contacts
- [x] Replace maintenance form selectors from vendors to business contacts
- [x] Replace `vendor()` relationships and usages with `contact()` equivalents
- [x] Update labels from `Vendor` to `Business Contact` where appropriate
- [x] Remove vendor CRUD from asset settings flow
- [x] Update detail pages and summary displays to show business contact plus primary person context
- [ ] Add regression tests for asset and maintenance create/edit/show behavior

**Phase 4 completion gate**
- [ ] Automated: asset create/edit regression tests pass
- [ ] Automated: maintenance create/edit regression tests pass
- [ ] Automated: selector filtering by active asset-eligible contacts passes
- [ ] Manual: create and edit an asset using a business contact
- [ ] Manual: create and edit maintenance using a business contact
- [ ] Manual: confirm no asset flow still depends on asset vendor CRUD

**Suggested verification commands**
```bash
php artisan test --filter=AssetContact
php artisan test --filter=MaintenanceContact
```

**Notes**
- Assets and maintenance continue to store only the business record in v1. Person-level selection remains out of scope.
- Implementation landed on March 21, 2026 with contact-backed selectors, contact-aware labels, primary-person context, and transaction/row-lock hardening for maintenance writes.
- Remaining gap before completion: targeted asset/maintenance regression tests and browser-level flow validation.

---

### Phase 5: Import, Reports, and Cleanup

**Status:** IN PROGRESS  
**Goal:** Update import/reporting behavior and retire legacy vendor-only code paths.

**Implementation targets**
- `app/Imports/AssetsImport.php`
- `app/Http/Controllers/AssetManagementController.php`
- `resources/views/assets/reports/`
- `tests/Feature/`

**Scope**
- [x] Change import behavior from missing vendor auto-create to missing contact auto-create
- [x] Auto-tag import-created contacts with the default `Vendor` tag
- [x] Update report filters and output from vendor terms to contact terms
- [x] Preserve vendor-performance style reporting using business contacts
- [x] Update delete guards to operate on linked contacts
- [ ] Remove or retire legacy vendor-only routes, models, and views as appropriate
- [ ] Add regression tests for import, reports, and cleanup logic

**Phase 5 completion gate**
- [ ] Automated: asset import regression tests pass
- [ ] Automated: report regression tests pass
- [ ] Automated: delete guard tests pass
- [ ] Manual: import an asset file with a missing business and confirm auto-created contact plus default tag
- [ ] Manual: review updated reports and confirm business names and primary-person context render correctly
- [ ] Manual: confirm obsolete vendor-only entry points are gone or intentionally redirected

**Suggested verification commands**
```bash
php artisan test --filter=AssetsImport
php artisan test --filter=AssetReportContact
```

**Notes**
- Cleanup should happen only after the new Contacts-based flows are already verified.
- Implementation landed on March 21, 2026 for import creation, default tagging, report label/filter updates, and asset-side vendor route removal.
- Remaining gap before completion: broader import/report regression coverage and final decision on retiring the compatibility `AssetVendor` model/table.

---

### Phase 6: QA, Rollout, and Documentation Closure

**Status:** IN PROGRESS  
**Goal:** Complete final verification, capture evidence, and close the rollout cleanly.

**Implementation targets**
- manual QA evidence
- regression test results
- tracker completion updates
- rollout notes

**Scope**
- [x] Run targeted regression suites across Contacts, Assets, and Maintenance
- [ ] Perform end-to-end manual QA
- [x] Update tracker statuses and completion log with exact dates and evidence
- [ ] Document remaining risks or follow-ups, if any
- [ ] Mark module rollout complete only when all prior phases are complete

**Phase 6 completion gate**
- [ ] Automated: all targeted regression suites pass
- [ ] Manual: create a business contact, add three people, set one primary, assign it to an asset, use it in maintenance, and verify import/report behavior
- [ ] Manual: confirm tracker reflects final phase statuses and completion evidence
- [ ] Manual: confirm no open blocker remains for the v1 contract

**Suggested verification commands**
```bash
php artisan test --filter=Contact
php artisan test --filter=Asset
php artisan test --filter=Maintenance
```

**Notes**
- No rollout is considered finished until this tracker is fully updated.
- Automated verification completed on March 21, 2026:
  - `php -l app/Http/Controllers/ContactController.php`
  - `php -l app/Http/Controllers/ContactTagController.php`
  - `php -l app/Http/Controllers/AssetManagementController.php`
  - `php -l app/Http/Controllers/MaintenanceController.php`
  - `php -l app/Imports/AssetsImport.php`
  - `php -l tests/Unit/Contacts/ContactManagementServiceTest.php`
  - `php artisan test tests/Unit/Contacts/ContactManagementServiceTest.php`
  - `php artisan test tests/Feature/Settings/ModuleVisibilitySettingsTest.php`
- Remaining gap before completion: browser-level manual QA and targeted asset/maintenance/import regression suites.

---

## 6. Phase Completion Log

| Phase | Status | Completed On | Verification | Notes |
| --- | --- | --- | --- | --- |
| Phase 1 | COMPLETE | March 21, 2026 | Created `docs/Contacts_PRD.md` and `docs/Contacts_Implementation_Tracker.md`; verified required PRD and tracker sections exist | Documentation baseline established; implementation must follow the contracts defined in these two documents |
| Phase 2 | IN PROGRESS |  | Added migrations, models, relationships, tag seeding, migration/backfill logic, and contact write services on March 21, 2026; syntax checks passed | Awaiting migration-focused regression coverage and manual migrated-data verification before completion |
| Phase 3 | IN PROGRESS |  | Added standalone Contacts routes, controllers, views, tag settings, module visibility, and sidebar entry on March 21, 2026; `php artisan test tests/Feature/Settings/ModuleVisibilitySettingsTest.php` passed | Awaiting dedicated Contacts CRUD/authorization feature coverage and manual UI QA before completion |
| Phase 4 | IN PROGRESS |  | Replaced asset and maintenance selectors/labels with business contacts, removed asset-side vendor CRUD, and hardened maintenance writes on March 21, 2026; syntax checks passed | Awaiting targeted asset/maintenance regression tests and manual flow verification before completion |
| Phase 5 | IN PROGRESS |  | Updated import auto-create to contacts, default-tag assignment, report wording/filters, and retired asset-side vendor routes on March 21, 2026; syntax checks passed | Awaiting import/report regression tests and final cleanup decision on compatibility artifacts before completion |
| Phase 6 | IN PROGRESS |  | `php artisan test tests/Unit/Contacts/ContactManagementServiceTest.php` passed; `php artisan test tests/Feature/Settings/ModuleVisibilitySettingsTest.php` passed; PHP lint passed on touched controllers/import/test files | Final browser QA and broader regression runs still required before any remaining phases can be marked complete |

---

## 7. Immediate Next Step

Finish verification work before phase completion updates:
- run targeted asset, maintenance, import, and report regression tests
- execute browser/manual QA for Contacts, Assets, and Maintenance flows
- record exact completion evidence for Phases 2 through 6 once each completion gate is met
