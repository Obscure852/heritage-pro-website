# Contacts Module Product Requirements Document

**Version:** 1.0  
**Date:** March 21, 2026  
**Author:** Codex  
**Status:** Implementation Landed, QA Pending  
**Implementation Tracker:** [Contacts_Implementation_Tracker.md](/Users/thatoobuseng/Sites/Junior/docs/Contacts_Implementation_Tracker.md)

---

## 1. Executive Summary

### 1.1 Purpose
Build a standalone `Contacts` module to manage external business contacts used by the school, starting with vendor replacement inside the Assets module. The module must replace the current `asset_vendors` feature with a reusable business-contact domain that supports:
- one business record
- many related contact people
- admin-managed category tags describing the kind of business done by that contact

### 1.2 Current Repo Reality
The current application stores vendors in `asset_vendors` and links them directly from:
- `assets.vendor_id`
- `asset_maintenances.vendor_id`
- asset settings vendor CRUD inside the Assets module
- asset import auto-create vendor behavior
- maintenance reports and filters that assume a flat vendor list

This PRD replaces that implementation with a reusable `contacts` domain while preserving current asset and maintenance workflows.

### 1.3 Core Requirement
The first release must support external business contacts for Assets without turning into a generic CRM. A business contact must support at least three people in the UI, but validation only requires one primary contact person. Tags must be centrally configured by admins only.

### 1.4 Success Criteria
- Assets no longer depend on `asset_vendors`
- Assets and maintenance link to `contact_id` only
- Users can manage a business and multiple people under it from a standalone Contacts module
- Admins can configure the approved tag catalog
- Bulk asset import can auto-create missing business contacts with the default vendor tag
- Existing asset and maintenance reporting continues to work after the cutover

---

## 2. Scope

### 2.1 In Scope
- Standalone Contacts module with list, create, edit, show, and tag settings surfaces
- Business contact records for external organizations
- Repeatable contact people under each business
- Admin-managed tags that classify business type
- Module visibility registration and sidebar entry
- Replacement of asset vendor CRUD and selectors with business contacts
- Asset and maintenance schema migration from `vendor_id` to `contact_id`
- Asset import changes for missing-contact auto-creation
- Report and filter updates where vendor data is currently shown

### 2.2 Out of Scope
- Full CRM behavior such as pipelines, tasks, reminders, or interaction history
- Internal staff, student, parent, or sponsor directory unification
- Free-form end-user tag creation
- Person-level foreign keys on assets or maintenance records in v1
- Configurable arbitrary custom fields in v1

---

## 3. Primary Users

| User Type | Description | Responsibility in v1 |
| --- | --- | --- |
| Asset Management Admin | Owns asset setup and master data | Create and manage business contacts, people, and tags |
| Asset Management Edit | Uses assets and maintenance workflows | Select business contacts on assets and maintenance records |
| Asset Management View | Reads reports and detail views | View business contacts and related people in assets flows |
| Setup Administrator | Manages system-level visibility | Enable or disable the Contacts module |

**Access rule:** Reuse existing asset roles for Contacts in v1. No separate Contacts role family is introduced in this release.

---

## 4. Product Principles

1. **Business first, person second**  
   Assets and maintenance link to the business record. Contact people exist under the business for reference and operational follow-up.

2. **Standalone module, not hidden settings**  
   Contacts is a reusable module with its own navigation and settings, not a tab buried inside Assets.

3. **Controlled taxonomy**  
   Tags are admin-managed and selectable from a defined catalog. Users do not create free-form tags.

4. **Minimal but reusable v1**  
   The design must support future reuse by other modules, but v1 only solves the external business-contact use case needed for Assets.

5. **Migration without operational loss**  
   Existing asset and maintenance data must migrate cleanly, with preserved business names and existing linked history.

---

## 5. Domain Model

### 5.1 Business Contact
A business contact represents the external organization the school works with, such as a supplier, vendor, maintenance provider, or contractor.

Required v1 fields:
- business name
- active status

Optional v1 fields:
- email
- phone
- address
- notes

### 5.2 Contact People
Each business contact can have many people attached to it.

Required behavior:
- support many people per business
- show three person rows by default in the create/edit UI
- require at least one saved person marked as primary before the business record is considered valid

Supported person fields:
- full name
- job title or role
- email
- phone
- `is_primary`

### 5.3 Tags
Tags describe what kind of business the contact does with the school.

Examples:
- Vendor
- Supplier
- Maintenance Provider
- Service Provider
- Contractor

Required behavior:
- a business can have many tags
- tags are selected from an admin-managed catalog
- tags can be activated or deactivated
- tags support sort order and color for display
- tags expose usage flags so Assets can filter eligible contacts

---

## 6. Functional Requirements

### 6.1 Contacts List
The Contacts module list page must support:
- search by business name, phone, email, and person name
- filter by active/inactive status
- filter by tag
- count of related people
- quick visibility of primary person and main contact channels

### 6.2 Business CRUD
Users with asset-admin or asset-edit access can:
- create a business contact
- edit a business contact
- view a business contact
- activate or deactivate a business contact

Deletion rules:
- a business contact cannot be deleted if linked to assets
- a business contact cannot be deleted if linked to maintenance records
- inactive status is the preferred way to retire a contact that has historical usage

### 6.3 Contact People CRUD
Create/edit flows must allow inline management of related people.

Rules:
- at least one person must exist when saving a business
- exactly one person should be primary in normal UI behavior
- users can add more than three people
- blank unused person rows should not be saved

### 6.4 Tag Settings
Only admins with appropriate setup access can manage tags.

Each tag must support:
- name
- slug
- description
- color
- active/inactive state
- sort order
- `usable_in_assets`
- `usable_in_maintenance`

### 6.5 Module Visibility
Contacts must be registered in the module visibility system as a standalone module with:
- a visibility key
- a display name
- an icon
- reuse of asset role visibility

### 6.6 Search and Selection
Asset and maintenance selectors must:
- show eligible business contacts only
- display business name first
- optionally surface the primary person in supporting text
- exclude inactive contacts from normal pickers

---

## 7. Assets and Maintenance Integration

### 7.1 Asset Link Model
Assets must link to the business contact only.

Rules:
- replace `assets.vendor_id` with `assets.contact_id`
- asset create/edit forms show `Business Contact`
- asset detail screens show business name and primary person summary

### 7.2 Maintenance Link Model
Maintenance must also link to the business contact only.

Rules:
- replace `asset_maintenances.vendor_id` with `asset_maintenances.contact_id`
- maintenance create/edit/report filters use business contacts
- maintenance detail/report views may show the primary person for context, but do not store a person foreign key in v1

### 7.3 Asset Settings Cutover
The current vendor CRUD inside asset settings must be removed from the long-term product flow.

Replacement behavior:
- category settings remain in Assets
- vendor settings move to Contacts
- any remaining asset settings text must be updated to refer to Contacts where appropriate

### 7.4 Reporting
All current vendor-driven asset reporting must continue after cutover.

Expected updates:
- replace vendor filters with contact filters
- replace vendor performance groupings with business contact groupings
- show business name consistently
- optionally show primary person beneath the business name where the report currently shows `contact_person`

---

## 8. Data Model and Public Interfaces

### 8.1 New Tables

#### `contacts`
- `id`
- `name`
- `email`
- `phone`
- `address`
- `notes`
- `is_active`
- timestamps
- soft deletes

#### `contact_people`
- `id`
- `contact_id`
- `name`
- `title`
- `email`
- `phone`
- `is_primary`
- `sort_order`
- timestamps

#### `contact_tags`
- `id`
- `name`
- `slug`
- `description`
- `color`
- `is_active`
- `usable_in_assets`
- `usable_in_maintenance`
- `sort_order`
- timestamps

#### `contact_contact_tag`
- `contact_id`
- `contact_tag_id`
- timestamps

### 8.2 Changed Tables

#### `assets`
- add `contact_id`
- backfill from existing vendor mapping
- remove dependency on `vendor_id` after cutover

#### `asset_maintenances`
- add `contact_id`
- backfill from existing vendor mapping
- remove dependency on `vendor_id` after cutover

### 8.3 Route and UI Contract
The Contacts module must expose:
- contacts list
- create business contact
- edit business contact
- show business contact
- contact tag settings

Assets and maintenance must consume the Contacts module through:
- business contact selectors
- business contact detail display
- filtered reporting

---

## 9. Migration and Backward Compatibility

### 9.1 Legacy Vendor Migration
Existing `asset_vendors` records must be migrated into `contacts`.

Mapping rules:
- `asset_vendors.name` -> `contacts.name`
- `asset_vendors.email` -> `contacts.email`
- `asset_vendors.phone` -> `contacts.phone`
- `asset_vendors.address` -> `contacts.address`
- `asset_vendors.notes` -> `contacts.notes`
- `asset_vendors.is_active` -> `contacts.is_active`

### 9.2 Legacy Contact Person Migration
If `asset_vendors.contact_person` is present:
- create the first `contact_people` row from that value
- mark it as primary
- leave email and phone blank unless the future migration design explicitly derives them from legacy fields

If `contact_person` is absent:
- create a placeholder-free business contact with no derived person
- implementation must still ensure post-cutover UI requires at least one primary person for new or edited records

### 9.3 Default Tag Assignment
All migrated legacy vendors must receive the `Vendor` tag.

### 9.4 Foreign Key Backfill
Assets and maintenance records must:
- preserve historical linkage
- be backfilled to the new `contact_id`
- keep reporting continuity during rollout

### 9.5 Cleanup Policy
Legacy vendor-only code paths must be removed only after:
- new schema exists
- data is backfilled
- controllers, views, imports, and reports read from Contacts
- regression verification passes

---

## 10. Configuration and Governance

### 10.1 What Is Configurable in v1
- tag catalog
- tag active state
- tag color
- tag sort order
- tag usage flags for Assets and Maintenance
- module visibility

### 10.2 What Is Fixed in v1
- core business fields
- core person fields
- asset and maintenance business-only linking model
- no free-form tag creation by end users

### 10.3 Tracking Rule
Implementation phase status is governed by [Contacts_Implementation_Tracker.md](/Users/thatoobuseng/Sites/Junior/docs/Contacts_Implementation_Tracker.md). The tracker is the execution source of truth. The PRD defines scope and acceptance; the tracker defines whether a phase is `NOT STARTED`, `IN PROGRESS`, `BLOCKED`, or `COMPLETE`.

---

## 11. Acceptance Criteria

The module is acceptable for v1 only if all of the following are true:
- a standalone Contacts module exists and is visible through module settings and navigation
- a business contact can store many people and requires one primary person to save
- the UI makes it easy to enter at least three people on create/edit
- assets and maintenance use `contact_id` rather than vendor foreign keys
- asset and maintenance selectors do not require person selection
- admins can manage the controlled tag catalog
- bulk asset import can auto-create missing business contacts with the default `Vendor` tag
- vendor reports, filters, and detail surfaces are updated to show business contacts without losing current information
- legacy vendor records migrate without breaking linked asset or maintenance history

---

## 12. Implementation Phases

### Phase 1: Documentation and Replacement Contract
- finalize this PRD
- create the implementation tracker
- lock naming, scope, migration rules, non-goals, and acceptance criteria

### Phase 2: Contacts Domain Foundation
- create schema for contacts, contact people, tags, and pivots
- define models and relationships
- implement legacy vendor migration and active/inactive behavior
- define tag usage filtering for assets and maintenance

### Phase 3: Standalone Contacts Module CRUD
- build module routes, controllers, views, and navigation
- build business contact CRUD
- build repeatable people management
- build admin tag settings
- register module visibility

### Phase 4: Assets and Maintenance Cutover
- replace asset vendor selectors and labels
- replace maintenance vendor selectors and labels
- move vendor usage to `contact_id`
- remove vendor CRUD dependency from asset settings

### Phase 5: Import, Reports, and Cleanup
- update asset import missing-vendor behavior to missing-contact behavior
- update reporting and filters
- update delete guards and service logic
- retire legacy vendor-only code paths

### Phase 6: QA, Rollout, and Documentation Closure
- run regression and manual QA
- record verification evidence in the tracker
- finalize rollout notes
- close remaining open phases in the tracker

---

## 13. Future Considerations

- Allow other modules to reuse Contacts after the Assets rollout is stable
- Add richer business metadata such as tax number, contract reference, or service regions
- Add person-level interaction history if a later CRM-like requirement emerges
- Add import/export for contacts and people as a separate feature

---

## 14. Summary

This PRD defines a controlled, reusable Contacts module that replaces the current asset vendor implementation without expanding into a generic CRM. The v1 design is deliberately narrow: external business contacts, many people per business, admin-managed tags, and business-only asset and maintenance linkage through `contact_id`. Execution status, phase gates, and completion evidence are managed in the companion implementation tracker.
