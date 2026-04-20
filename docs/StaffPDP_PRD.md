# Product Requirements Document (PRD)
# Staff Performance Development Plan (PDP) Platform

**Version:** 2.0
**Date:** March 12, 2026
**Author:** Codex
**Status:** Revised Draft
**Default Seed Template:** School-adapted PDP based on `PART B PERFORMANCE OBJECTIVES copy.pdf`
**Alternate Supported Template:** Official DPSM Form 6 based on `PDP.docx`

---

## 1. Executive Summary

### 1.1 Purpose
Build a template-driven Staff Performance Development Plan (PDP) platform inside the existing School Management System. The platform must digitize staff planning, review, scoring, comments, sign-off, and PDF output without hardcoding one permanent form shape into the database, routes, or UI.

The initial release ships with two supported template versions:
- A **school-adapted half-yearly PDP template** seeded from the scanned real-world form and evidence examples
- An **official DPSM Form 6 template** seeded from the Word document for compliance/reference use

### 1.2 Core Requirement
Both source document versions must be representable through template configuration and seeded data only. Supporting a new PDP form revision must not require schema changes or new section-specific code paths.

### 1.3 Scope
- **In Scope:** Template-driven PDP creation, configurable sections and fields, configurable review periods, flexible evidence capture, configurable rating schemes, configurable signature workflow, template-driven PDF rendering, historical versioning, seeded school and DPSM templates
- **Out of Scope:** Payroll processing, sync to an external government system, fully generic enterprise form-builder behavior outside PDP, multi-school consolidated PDP reporting

### 1.4 Primary Users
| User Type | Description | Platform Responsibility |
|-----------|-------------|-------------------------|
| Employee/Staff | Person being appraised | View own plan, enter allowed responses, comments, evidence, and signatures |
| Supervisor/Reporting Officer | Immediate supervisor | Create/manage subordinate plans, review submissions, rate applicable sections, sign |
| Authorized Official / Indirect Supervisor | Secondary approver where required | Review/sign according to the active template |
| HR/Admin | Module administrator | Manage templates, defaults, activation, reporting, and policy settings |

---

## 2. Product Principles

1. **Template first, form second**  
   The platform owns workflow, rendering, persistence, calculations, and permissions. The template owns section labels, field shape, period cadence, scoring configuration, and approval order.

2. **No hardcoded section model**  
   Labels such as "Part A" through "Part F", section order, repeatability, and applicability are seeded defaults, not permanent code assumptions.

3. **Flexible evidence capture**  
   PDP evidence must support narrative text, structured tables, metric pairs, issues/actions, and optional attachments because the scanned school example uses all of these within the same objective.

4. **Versioned historical integrity**  
   A plan must stay bound to the exact published template version used when it was created, even after a new template version becomes active.

5. **Mapped profile data over schema sprawl**  
   Staff identity fields should come from existing user fields, school settings, or extensible profile metadata wherever possible, rather than adding one-off PDP columns to `users`.

6. **Same engine, different templates**  
   The school variant and official DPSM variant must use the same platform engine, data model, rendering layer, and PDF pipeline.

---

## 3. Source Documents and Reconciliation

### 3.1 Source Documents
1. `PDP.docx`  
   Official DPSM Form 6 document containing the canonical government structure, standard guidance, quarterly review summary, and summary/recommendation page.
2. `PART B PERFORMANCE OBJECTIVES copy.pdf`  
   A completed school-adapted example showing how staff actually capture detailed evidence such as class roll tables, attendance, retention, transfers, fee collections, issues, interventions, comments, ratings, and half-yearly review totals.

### 3.2 Reconciliation of Differences

| Topic | Official DPSM Word Form | School-Adapted Example | PRD Decision |
|------|--------------------------|------------------------|--------------|
| Review cadence | Quarterly summary page | Half-yearly review summary page | Review cadence becomes a template-defined period model |
| Section structure | Formal DPSM layout, ending with summary/recommendations | School-specific layout with Part E personal development goals and Part F half-yearly summary | Section order and naming become template-defined |
| Attribute library | Uses one set of personal attributes such as Knowledge of Work, Output Accuracy/Reliability/Speed, Initiative | Uses an adapted attribute set with school-specific descriptors such as Teamwork, Work Ethic, Customer Focus, and Effective Communication | Attribute definitions become template seed data, not code constants |
| Rating expression | Rating bands and final annual rating | Weighted performance and attribute totals shown in half-yearly summary | Rating scales, weights, conversion rules, and summary formulas become template-configured |
| Evidence shape | Mostly form/table-oriented | Rich operational evidence with embedded tables, metric summaries, issues, and actions | Evidence blocks must support multiple field types within repeatable objective entries |
| Signature flow | Includes employee, supervisor, authorized official, and permanent secretary areas | Includes employee, supervisor, and authorized official sign-off in school workflow | Approval steps become template-defined with role mappings |

### 3.3 Source Hierarchy
- The **school-adapted form** is the default seed template because it reflects the working process currently used and the richer evidence requirements.
- The **official DPSM form** remains a supported alternate template version for compliance or future activation.
- The platform must not force a hybrid merged form. Each version is a separately published template.

---

## 4. Platform vs Template Responsibilities

| Concern | Platform Responsibility | Template Responsibility |
|---------|--------------------------|-------------------------|
| Plan lifecycle | Create, persist, validate, lock, archive, authorize | Define which periods and sign-off steps exist |
| Rendering | Generic section/field renderer, repeatable entry handling, PDF engine | Section order, labels, help text, print rules, layout hints |
| Scoring | Execute configured calculations and store results | Define schemes, conversion scales, weights, and final bands |
| Data mapping | Resolve user, setting, metadata, manual, and computed values | Declare which fields use which source |
| Evidence capture | Support rich text, tables, metrics, attachments, comments | Declare which evidence blocks appear in each repeatable item |
| Access/editability | Enforce user role authorization | Declare which template roles can edit each section/field at each stage |

### 4.1 Non-Hardcoding Acceptance Rule
If a future PDP template revision changes section labels, adds a new review period, changes the weight from `80/20`, swaps the approval order, or replaces one attribute library with another, the system must support it by updating template data and settings only.

---

## 5. Functional Requirements

### 5.1 Template Model
Each PDP template is a versioned, publishable definition made up of:
- Template metadata
- Ordered sections
- Ordered fields within each section
- Period definitions
- Rating schemes
- Approval steps
- Default mappings and seeded content

Templates move through:
`draft -> published -> archived`

Only published templates may be used to create plans. Once published and in use, a template version is immutable.

### 5.2 Section Definitions
Sections are template rows, not hardcoded code modules. Each section definition must support:
- `key` and display `label`
- `sequence`
- `section_type`
- `is_repeatable`
- `min_items` and `max_items`
- `applies_when` rules
- `editable_by` rules per workflow stage and template role
- `visible_in_pdf`
- `layout_config`

Supported section types in v1:
- `profile_summary`
- `repeatable_objectives`
- `repeatable_development`
- `repeatable_attributes`
- `review_summary`
- `comments_block`
- `signature_block`

### 5.3 Field Definitions
Fields are driven by template configuration. Each field definition must support:
- `key`
- `label`
- `field_type`
- `data_type`
- `input_mode`
- `required`
- `validation_rules`
- `default_value`
- `options`
- `mapping_source`
- `mapping_key`
- `help_text`
- `period_scope`
- `print_config`
- `sort_order`

Supported `input_mode` values:
- `mapped_user_field`
- `mapped_setting`
- `mapped_profile_metadata`
- `manual_entry`
- `computed`

Supported `field_type` values in v1:
- `text`
- `textarea`
- `rich_text`
- `number`
- `date`
- `select`
- `radio_scale`
- `computed_value`
- `repeatable_group`
- `metric_pair`
- `structured_table`
- `comment`
- `attachment`
- `signature`

### 5.4 Evidence Blocks
Objective evidence must be composable. A repeatable objective entry may contain one or more evidence blocks such as:
- Rich narrative text
- Structured tables for class roll, attendance, transfer, fee, or similar tabular evidence
- Metric pairs or metric lists such as `possible attendance` vs `actual attendance`
- Issue and action blocks
- Period-specific comments
- Optional attachments or linked documents

The system must allow a template to define which evidence blocks appear in each review period. It must not assume evidence is stored in one `longText` column only.

### 5.5 Data Population Rules
Each displayed field must clearly state how it is populated:
- **System-mapped:** existing user record fields such as employee name or position
- **Settings-mapped:** school-level values such as school name, ministry/department label, or logo
- **Profile-mapped:** extensible staff metadata such as payroll number, grade, or posting date
- **Manual:** user-entered values such as comments, ratings, evidence, issues, and actions
- **Computed:** calculated totals, weighted scores, rating bands, and summary values

### 5.6 Review Period Model
Review periods are configured per template through `PeriodDefinition` records. Each period definition must support:
- `key`
- `label`
- `sequence`
- `window_type`
- `due_rule`
- `open_rule`
- `close_rule`
- `include_in_final_score`
- `summary_label`

Seeded examples:
- School template: `mid_year`, `year_end`
- Official DPSM template: `quarter_1`, `quarter_2`, `quarter_3`, `quarter_4`

The platform must not use statuses such as `mid_year_review` and `year_end_review` as global constants.

### 5.7 Approval and Signature Model
Approval steps are template-defined `ApprovalStep` records. Each step supports:
- `key`
- `label`
- `sequence`
- `role_type`
- `required`
- `period_scope`
- `comment_required`
- `sign_before_lock`

Examples of `role_type`:
- `employee`
- `reporting_officer`
- `authorized_official`
- `permanent_secretary`
- `hr_delegate`

The platform authorizes actual users through role mapping, but the existence and order of approval steps come from the template.

---

## 6. Seeded Templates for Initial Release

### 6.1 Seeded Template A: School Half-Yearly PDP
This is the default active template at launch.

| Section Key | Default Label | Repeatable | Notes |
|-------------|---------------|------------|-------|
| `employee_information` | Part A: Employee Information | no | Mostly mapped fields from user, settings, and profile metadata |
| `performance_objectives` | Part B: Performance Objectives | yes | Repeatable objectives with nested evidence blocks, result/comments, and weighted performance scoring |
| `coaching` | Part C: Coaching / Development Objectives | yes | Repeatable development/coaching items |
| `behavioural_attributes` | Part D: Behavioural Attributes | yes | Seeded school attribute library with configurable scale and applicability |
| `personal_development_goals` | Part E: Personal Development Goals | yes | Repeatable gap/action/time-frame/results entries |
| `review_summary` | Part F: Half-Yearly Review Rating Summary | no | Computed totals, comments, and signatures |

Default period model:
- `mid_year`
- `year_end`

Default scoring model:
- Performance objectives weighted at `0.80`
- Behavioural attributes weighted at `0.20`
- Final rating bands seeded from current school practice and kept editable in template configuration

### 6.2 Seeded Template B: Official DPSM Form 6
This is seeded as a published alternate template and may be activated later without code changes.

| Section Key | Default Label | Repeatable | Notes |
|-------------|---------------|------------|-------|
| `employee_information` | Part A: Employee Information | no | Mapped profile and settings data |
| `performance_objectives` | Part B: Performance Objectives | yes | Objectives with measures, targets, result/comments |
| `development_objectives` | Part C: Development Objectives | yes | Repeatable development items |
| `personal_attributes` | Part D: Assessment for Personal Attributes | yes | DPSM attribute library and rating bands |
| `quarterly_summary` | Quarterly Review Rating Summary | no | Period-by-period calculated table |
| `final_summary` | Part E: Summary and Recommendation(s) | no | Final rating, recommendations, comments, signatures |

Default period model:
- `quarter_1`
- `quarter_2`
- `quarter_3`
- `quarter_4`

The platform must treat the above section names as seed data, not as permanent route, migration, or view names.

---

## 7. Data Model

### 7.1 Template Definition Tables

#### `pdp_templates`
One row per publishable template version.

Recommended fields:
- `id`
- `template_family_key`
- `version`
- `code`
- `name`
- `source_reference`
- `description`
- `status`
- `is_default`
- `settings_json`
- `published_at`
- `created_by`
- `timestamps`

Notes:
- `template_family_key` groups versions of the same form family
- Published template versions are immutable

#### `pdp_template_sections`
Defines ordered sections for a template version.

Recommended fields:
- `id`
- `pdp_template_id`
- `key`
- `label`
- `section_type`
- `sequence`
- `is_repeatable`
- `min_items`
- `max_items`
- `applies_when_json`
- `editable_by_json`
- `layout_config_json`
- `print_config_json`
- `timestamps`

#### `pdp_template_fields`
Defines fields inside a section, including nested repeatable/evidence structures.

Recommended fields:
- `id`
- `pdp_template_section_id`
- `parent_field_id`
- `key`
- `label`
- `field_type`
- `data_type`
- `input_mode`
- `required`
- `validation_rules_json`
- `mapping_source`
- `mapping_key`
- `default_value_json`
- `options_json`
- `period_scope`
- `rating_scheme_key`
- `sort_order`
- `timestamps`

#### `pdp_template_periods`
Defines review cadence and review windows.

Recommended fields:
- `id`
- `pdp_template_id`
- `key`
- `label`
- `sequence`
- `window_type`
- `due_rule_json`
- `open_rule_json`
- `close_rule_json`
- `include_in_final_score`
- `summary_label`
- `timestamps`

#### `pdp_template_rating_schemes`
Defines scoring logic, conversion, bands, and weights.

Recommended fields:
- `id`
- `pdp_template_id`
- `key`
- `label`
- `input_type`
- `scale_config_json`
- `conversion_config_json`
- `weight`
- `rounding_rule`
- `formula_config_json`
- `band_config_json`
- `timestamps`

#### `pdp_template_approval_steps`
Defines signature workflow.

Recommended fields:
- `id`
- `pdp_template_id`
- `key`
- `label`
- `sequence`
- `role_type`
- `required`
- `period_scope`
- `comment_required`
- `timestamps`

### 7.2 Plan Instance Tables

#### `pdp_plans`
Master record for one employee plan created from one published template version.

Recommended fields:
- `id`
- `pdp_template_id`
- `user_id`
- `supervisor_id`
- `plan_period_start`
- `plan_period_end`
- `status`
- `current_period_key`
- `calculated_summary_json`
- `created_by`
- `timestamps`
- `soft_deletes`

Notes:
- `status` should remain generic, for example `draft`, `active`, `completed`, `cancelled`
- A plan always points to a specific template version

#### `pdp_plan_reviews`
Stores one review instance per configured period.

Recommended fields:
- `id`
- `pdp_plan_id`
- `period_key`
- `status`
- `opened_at`
- `closed_at`
- `score_summary_json`
- `narrative_summary`
- `timestamps`

#### `pdp_plan_section_entries`
Stores section data in a template-driven way.

Recommended fields:
- `id`
- `pdp_plan_id`
- `pdp_plan_review_id`
- `section_key`
- `entry_group_key`
- `parent_entry_id`
- `sort_order`
- `values_json`
- `computed_values_json`
- `timestamps`

Notes:
- Use one row per repeatable item or section instance
- `values_json` stores field values keyed by template field key
- `pdp_plan_review_id` is nullable for plan-level sections that do not vary by period

#### `pdp_plan_signatures`
Stores sign-off actions independently from section rows.

Recommended fields:
- `id`
- `pdp_plan_id`
- `pdp_plan_review_id`
- `approval_step_key`
- `role_type`
- `signer_user_id`
- `signed_at`
- `comment`
- `status`
- `timestamps`

### 7.3 Supporting Staff Metadata
To avoid pushing PDP-specific columns into `users`, the PRD assumes a generic staff profile metadata capability is available or added for broader reuse.

Recommended supporting structure:
- `user_profile_metadata`
  - `id`
  - `user_id`
  - `key`
  - `value`
  - `timestamps`

Use this for values such as:
- Payroll number
- DPSM/personal file number
- Grade
- Date of appointment
- Date of posting/transfer

If any of these later become globally important to multiple modules, they may be promoted into first-class user profile fields. The PDP module must not depend on that promotion.

### 7.4 Settings Pattern
Module defaults such as active template, school labels, ministry label overrides, upload limits, or PDF branding may use a small PDP settings store following the same key/value pattern already used elsewhere in the project.

---

## 8. Scoring and Calculation Requirements

### 8.1 Template-Configured Scoring
Scoring must be fully template-configured. The platform must not hardcode:
- Input type
- Scale labels
- Conversion rules
- Weight percentages
- Rounding precision
- Rating band labels
- Summary table rows
- Final score formula

### 8.2 Supported Scoring Modes in v1
- **Direct percentage input**  
  Example: objective result entered as `67.8`
- **Intensity scale conversion**  
  Example: attribute score `1-5` converted to a percentage using template rules
- **Band lookup**  
  Example: final score maps to `Outstanding`, `Very Good`, `Satisfactory`, `Fair`, or `Unsatisfactory`

### 8.3 Formula Engine Requirement
The platform should support a bounded formula/config engine sufficient for:
- `average`
- `sum`
- `multiply`
- `divide`
- `round`
- weighted totals
- conditional band lookup

This must be configuration-driven, not arbitrary code execution.

### 8.4 Default Seed Formulas
The school template may seed formulas equivalent to:
- `performance_weighted = average(objective_scores_for_period) * 0.80`
- `attributes_weighted = average(attribute_scores_for_period_as_percent) * 0.20`
- `period_total = performance_weighted + attributes_weighted`

These formulas are defaults only and must remain editable through template version data.

---

## 9. Rendering, Routes, and Services

### 9.1 Rendering Model
Replace permanent section partials such as `_part-a` through `_part-f` with a generic rendering stack:
- Layout resolves active template version
- Page builds ordered sections from `pdp_template_sections`
- Each section renders by `section_type`
- Each field renders by `field_type`
- Repeatable sections use generic add/edit/remove entry components

Recommended reusable view partials:
- `pdp/fields/text.blade.php`
- `pdp/fields/rich-text.blade.php`
- `pdp/fields/structured-table.blade.php`
- `pdp/fields/rating-scale.blade.php`
- `pdp/fields/signature.blade.php`
- `pdp/sections/repeatable.blade.php`
- `pdp/sections/review-summary.blade.php`

### 9.2 Template-Driven Routes
Routes should be generic and section-key driven instead of objective-specific or attribute-specific.

Recommended examples:
- `GET /pdp`
- `GET /pdp/create`
- `POST /pdp`
- `GET /pdp/{plan}`
- `PUT /pdp/{plan}`
- `POST /pdp/{plan}/reviews/{periodKey}/open`
- `POST /pdp/{plan}/reviews/{periodKey}/close`
- `POST /pdp/{plan}/sections/{sectionKey}/entries`
- `PUT /pdp/{plan}/sections/{sectionKey}/entries/{entryId}`
- `DELETE /pdp/{plan}/sections/{sectionKey}/entries/{entryId}`
- `POST /pdp/{plan}/reviews/{periodKey}/calculate`
- `POST /pdp/{plan}/reviews/{periodKey}/signatures/{approvalStepKey}`
- `GET /pdp/{plan}/pdf`
- `GET /pdp/templates`
- `POST /pdp/templates`
- `POST /pdp/templates/{template}/publish`
- `POST /pdp/templates/{template}/activate`

### 9.3 Service Layer
Recommended core services:
- `PdpTemplateService`
  - create draft template
  - clone template version
  - publish template
  - activate template
  - seed school and DPSM templates
- `PdpPlanService`
  - create plan from template version
  - resolve mapped values
  - persist repeatable section entries
- `PdpReviewService`
  - open/close configured periods
  - validate required inputs
  - enforce period-level locks
- `PdpScoringService`
  - execute configured rating schemes and formulas
  - persist computed summaries
- `PdpRenderService`
  - build section/field view models from template definitions
- `PdpPdfService`
  - render a print-ready PDF using the same template definition and print configuration

### 9.4 PDF Export
PDF generation must use the active plan template version and not a hardcoded one-form layout.

Required PDF capabilities:
- Match the selected template's labels, order, and print visibility rules
- Render structured evidence tables cleanly
- Render computed totals and comments
- Render signature blocks defined by approval steps
- Preserve historical fidelity for older plans created under older template versions

---

## 10. Authorization and Business Rules

### 10.1 Authorization Model
Platform roles determine who may act at all. Template rules determine where and when they may act.

Default role expectations:
- Employees can view their own plans and edit only fields/sections allowed for the current stage
- Supervisors can manage plans for their subordinates
- Authorized officials can complete configured approval steps
- HR/Admin can manage templates, settings, reports, and exceptions

### 10.2 Core Rules
- Only published templates can be used to create plans
- One plan references one immutable template version
- Required repeatable sections must meet template minimums before a review can close
- Only configured approval steps can collect signatures
- Required ratings or comments must block review closure if the template marks them mandatory
- Computed values are recalculated from template configuration, not edited directly unless explicitly allowed

### 10.3 Plan Status Rules
Use generic platform statuses:
- `draft`
- `active`
- `completed`
- `cancelled`

Period states such as `mid_year`, `year_end`, or `quarter_2` belong in `pdp_plan_reviews`, not in the global plan status enum.

### 10.4 Overlap Rule
By default, one employee should not have overlapping active PDP plans for the same template family and date range. This is a policy rule and may later be made configurable at the module level if the school needs parallel appraisal tracks.

---

## 11. Acceptance Criteria and Validation

### 11.1 Flexibility Acceptance Criteria
The revised implementation is acceptable only if:
1. The school-adapted form can be represented as one published template version
2. The official DPSM form can be represented as a second published template version
3. Switching between the two does not require schema changes
4. Adding a third variant would require only template/config work and field-type reuse

### 11.2 Mapping Acceptance Criteria
The PRD must define how every display field is populated:
- system-mapped
- settings-mapped
- profile-mapped
- manual
- computed

### 11.3 Evidence Acceptance Criteria
The system must support:
- rich narrative evidence
- structured evidence tables
- metric pairs/lists
- issue/action capture
- optional attachments

The scanned school example must be modelable without flattening all evidence into a single text area.

### 11.4 Scoring Acceptance Criteria
The system must support configuration of:
- rating scale type
- conversion logic
- weights
- rating bands
- rounding
- review summary formulas

None of the above may be embedded as unchangeable code constants tied to one form.

### 11.5 PDF Acceptance Criteria
PDF export must:
- render the selected template version accurately
- preserve the correct section names and order
- show the right summary model for the selected template
- show the correct approval chain for the selected template

### 11.6 Historical Integrity Criteria
If a new template version is published later:
- existing plans keep their original template layout, formulas, and labels
- new plans use the newly activated version only

---

## 12. Implementation Phases

### Phase 1: Template Foundation
- Create template, section, field, period, rating scheme, and approval step tables
- Add or confirm generic staff profile metadata support
- Add module settings support using the existing key/value settings pattern
- Seed school and official DPSM templates

### Phase 2: Plan Instance and Renderer
- Create plan, review, section entry, and signature tables
- Build generic template-driven form rendering
- Implement mapped value resolution for user fields, settings, and profile metadata

### Phase 3: Reviews, Scoring, and Locking
- Implement period management and locking rules
- Implement scoring configuration and formula execution
- Implement template-driven validation and signature flow

### Phase 4: PDF and Administration
- Implement template-driven PDF generation
- Build template administration screens for draft, publish, clone, and activate workflows
- Add reporting and history views bound to template version data

---

## 13. Future Considerations

- Import/export template definitions as JSON for easier rollout of new government form versions
- Link evidence attachments to the document management module for richer audit history
- Add reminders based on configured review periods
- Add side-by-side comparison of scores across template versions for longitudinal reporting
- Add department-level bulk plan generation using the active template

---

## 14. Summary

This PRD deliberately moves the PDP module away from a fixed "Part A to Part F" schema and toward a versioned template platform. The launch deliverable is still a school-ready PDP workflow, but the platform must support that workflow and the official DPSM form through configuration, seeded template data, and reusable rendering/scoring services rather than hardcoded tables, routes, and columns.
