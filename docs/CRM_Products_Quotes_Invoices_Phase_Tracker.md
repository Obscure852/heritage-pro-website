# CRM Products, Quotes, and Invoices Phase Tracker

**Reference PRD:** [CRM_Products_Quotes_Invoices_PRD.md](./CRM_Products_Quotes_Invoices_PRD.md)  
**Module Status:** PHASES 2-6 IMPLEMENTED, PHASE 7 HARDENING IN PROGRESS, MANUAL VERIFICATION PENDING  
**Last Updated:** April 21, 2026

---

## 1. Purpose

This document is the working implementation tracker for the CRM `Products` module rollout. It breaks the PRD into execution phases and is the source of truth for whether a phase is `NOT STARTED`, `IN PROGRESS`, `BLOCKED`, or `COMPLETE`.

A phase is only complete when:
- all scoped work for that phase is done
- required automated verification has passed where applicable
- required manual verification has passed where applicable
- completion evidence is recorded in this tracker
- the header status, phase overview, detailed phase section, completion log, and `Last Updated` date are updated in the same change set

Current repo state when this tracker was created:
- the CRM foundation already exists for leads, customers, contacts, requests, discussions, integrations, search, and settings
- the CRM currently supports `admin`, `manager`, and `rep` roles only
- there is no operational products catalog
- there are no quote or invoice tables, models, routes, or views
- there is no finance-owned commercial settings area
- private CRM document storage already exists through request attachments and the `documents` disk

---

## 2. How To Use This Tracker

1. Move a phase to `IN PROGRESS` when implementation on that phase begins.
2. Check scope items off as work lands.
3. Run the verification items listed for that phase.
4. Record exact verification evidence in the completion log.
5. Only then mark the phase `COMPLETE`.

This tracker must be updated every time a phase starts or completes.

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
| Phase 1 | PRD and tracker contract freeze | COMPLETE | None |
| Phase 2 | Commercial foundation and permissions | IN PROGRESS | Phase 1 |
| Phase 3 | Catalog and commercial settings | IN PROGRESS | Phase 2 |
| Phase 4 | Quote workflow | IN PROGRESS | Phase 3 |
| Phase 5 | Invoice workflow | IN PROGRESS | Phase 3 |
| Phase 6 | PDF delivery and CRM integrations | IN PROGRESS | Phase 4, Phase 5 |
| Phase 7 | QA, hardening, and rollout closure | IN PROGRESS | Phase 6 |

---

## 5. Detailed Phases

### Phase 1: PRD and Tracker Contract Freeze

**Status:** COMPLETE  
**Completed On:** April 21, 2026  
**Goal:** Lock the module scope, phase order, acceptance rules, and execution tracker before implementation starts.

**Implementation targets**
- `docs/CRM_Products_Quotes_Invoices_PRD.md`
- `docs/CRM_Products_Quotes_Invoices_Phase_Tracker.md`

**Scope**
- [x] Create the products, quotes, and invoices PRD for the live CRM repo
- [x] Lock the v1 scope around B2B SaaS catalog, quotes, invoices, discounts, and currencies
- [x] Lock the independent quote and invoice model with no mandatory conversion flow
- [x] Lock the lead-or-customer XOR account-context rule with required `contact_id`
- [x] Lock the new `finance` role and its permissions boundary
- [x] Break implementation into ordered delivery phases with dependencies

**Phase 1 completion gate**
- [x] PRD exists under `docs/`
- [x] Phase tracker exists under `docs/`
- [x] PRD and phase tracker reference each other
- [x] Phase names and dependencies are stable enough to begin execution

**Suggested verification commands**
```bash
test -f docs/CRM_Products_Quotes_Invoices_PRD.md
test -f docs/CRM_Products_Quotes_Invoices_Phase_Tracker.md
rg -n "Implementation Tracker" docs/CRM_Products_Quotes_Invoices_PRD.md
rg -n "^## 4\\. Phase Overview|^## 6\\. Phase Completion Log" docs/CRM_Products_Quotes_Invoices_Phase_Tracker.md
```

**Notes**
- This phase is documentation-only by design.
- No application code is required for this phase.

---

### Phase 2: Commercial Foundation and Permissions

**Status:** IN PROGRESS  
**Goal:** Establish the schema, roles, services, policies, and calculation rules that every commercial surface depends on.

**Implementation targets**
- `database/migrations/`
- `app/Models/`
- `app/Services/Crm/`
- `app/Http/Requests/Crm/`
- `app/Http/Controllers/Crm/`
- `config/heritage_crm.php`
- `app/Models/User.php`
- `tests/Feature/Crm/`
- `tests/Unit/`

**Scope**
- [x] Add the `finance` CRM role to configuration, user management, and authorization helpers
- [ ] Create schema for:
  - [x] `crm_products`
  - [x] `crm_quotes`
  - [x] `crm_quote_items`
  - [x] `crm_invoices`
  - [x] `crm_invoice_items`
  - [x] commercial settings storage
  - [x] commercial document artifact metadata if persisted separately
- [x] Create Eloquent models and relationships for products, quotes, quote items, invoices, and invoice items
- [x] Enforce the XOR account rule: exactly one of `lead_id` or `customer_id`
- [x] Enforce required `contact_id` and optional `request_id`
- [x] Create commercial calculation service(s) for line totals, document discounts, tax, and grand totals
- [x] Create numbering service(s) for quote and invoice sequences
- [x] Add base validation and policy coverage for finance-owned operations

**Phase 2 completion gate**
- [x] Automated: migrations run cleanly on a fresh database
- [x] Automated: model relationship tests pass
- [x] Automated: calculation tests cover discounts, tax, and snapshot persistence rules
- [x] Automated: authorization tests cover `finance`, `admin`, `manager`, and `rep`
- [ ] Manual: verify a finance user can access commercial foundations while non-finance users cannot manage them

**Suggested verification commands**
```bash
php artisan test --filter=CommercialSchema
php artisan test --filter=CommercialCalculation
php artisan test --filter=CommercialAuthorization
```

**Notes**
- This phase must complete before any catalog, quote, or invoice UI work is treated as stable.
- Snapshot and numbering rules should be fully centralized here to avoid duplicated logic later.
- Implementation landed on April 21, 2026 with commercial currencies/settings schema, product/quote/invoice models, finance-role plumbing, calculation and numbering services, and base commercial validation.
- Remaining gap before completion: manual QA for finance-role access and end-to-end schema inspection outside automated tests.

---

### Phase 3: Catalog and Commercial Settings

**Status:** IN PROGRESS  
**Goal:** Deliver the `Products` module shell, catalog CRUD, and finance-owned commercial settings UI.

**Implementation targets**
- `routes/crm/`
- `app/Http/Controllers/Crm/`
- `resources/views/crm/`
- `app/Services/Crm/CrmModuleRegistry.php`
- `config/heritage_crm.php`
- `tests/Feature/Crm/`

**Scope**
- [x] Register the `Products` workspace in CRM launcher and sidebar configuration
- [x] Add child navigation for `Catalog`, `Quotes`, and `Invoices`
- [x] Add catalog routes, controller actions, requests, and views
- [x] Add product create, list, show, edit, and deactivate flows
- [x] Add commercial settings routes, controller actions, and views
- [x] Add supported-currency management UI and default-currency selection
- [x] Add quote and invoice numbering settings UI
- [x] Add discount-policy and default-tax settings UI
- [x] Restrict catalog and settings management to finance and admin users

**Phase 3 completion gate**
- [x] Automated: product catalog CRUD tests pass
- [x] Automated: commercial settings authorization tests pass
- [x] Automated: module visibility and route-access tests pass
- [ ] Manual: finance can manage catalog and settings end to end
- [ ] Manual: reps and managers can browse catalog but cannot manage settings

**Suggested verification commands**
```bash
php artisan test --filter=ProductCatalog
php artisan test --filter=CommercialSettings
php artisan test --filter=CrmCommercialNavigation
```

**Notes**
- This phase should not yet generate quotes or invoices; it provides the module shell and source data required by later phases.
- Implementation landed on April 21, 2026 with the CRM `Products` module in the launcher/sidebar, role-aware child navigation, catalog CRUD, finance-owned commercial settings and currency UI, and read-only quote/invoice workspace shells.
- Remaining gap before completion: manual role-based QA across the new catalog and commercial settings flows.

---

### Phase 4: Quote Workflow

**Status:** IN PROGRESS  
**Goal:** Deliver full quote authoring, editing, lifecycle management, and quote-specific document behavior.

**Implementation targets**
- `routes/crm/`
- `app/Http/Controllers/Crm/`
- `app/Http/Requests/Crm/`
- `app/Services/Crm/`
- `resources/views/crm/`
- `tests/Feature/Crm/`

**Scope**
- [x] Add quote routes, controller actions, and requests
- [x] Build quote list, create, show, and edit views
- [x] Add account-context selection for either lead or customer
- [x] Add linked contact selection and optional sales-request linking
- [x] Add quote line-item editing with catalog and custom rows
- [x] Persist snapshot fields for header and line items
- [x] Implement quote status transitions:
  - [x] `draft`
  - [x] `sent`
  - [x] `accepted`
  - [x] `rejected`
  - [x] `expired`
  - [x] `cancelled`
- [x] Add quote totals preview and saved calculation consistency checks

**Phase 4 completion gate**
- [x] Automated: quote CRUD and validation tests pass
- [x] Automated: quote calculation and snapshot tests pass
- [x] Automated: quote lifecycle transition tests pass
- [ ] Manual: a rep can create and edit a quote for both a lead and a customer
- [ ] Manual: a quote can mix catalog lines with custom lines and render correct totals

**Suggested verification commands**
```bash
php artisan test --filter=QuoteWorkflow
php artisan test --filter=QuoteCalculation
php artisan test --filter=QuoteAuthorization
```

**Notes**
- Quote and invoice flows remain intentionally separate in v1.
- Do not implement quote-to-invoice conversion in this phase.
- Implementation landed on April 21, 2026 with full quote routes, authoring views, quote header and line-item validation, catalog/custom line support, lifecycle actions, snapshotted commercial fields, and client-side totals preview backed by server-side recalculation.
- Remaining gap before completion: manual rep QA across lead-based and customer-based quote flows.

---

### Phase 5: Invoice Workflow

**Status:** IN PROGRESS  
**Goal:** Deliver invoice authoring, finance-only issuance controls, and invoice lifecycle management.

**Implementation targets**
- `routes/crm/`
- `app/Http/Controllers/Crm/`
- `app/Http/Requests/Crm/`
- `app/Services/Crm/`
- `resources/views/crm/`
- `tests/Feature/Crm/`

**Scope**
- [x] Add invoice routes, controller actions, and requests
- [x] Build invoice list, create, show, and edit views
- [x] Add invoice line-item editing with catalog and custom rows
- [x] Support manual license-renewal invoices as normal invoice authoring
- [x] Persist header and line-item snapshots
- [x] Implement finance-only invoice status transitions:
  - [x] `draft`
  - [x] `issued`
  - [x] `sent`
  - [x] `cancelled`
  - [x] `void`
- [x] Enforce invoice issuance, cancel, and void permissions

**Phase 5 completion gate**
- [x] Automated: invoice CRUD and validation tests pass
- [x] Automated: invoice calculation and snapshot tests pass
- [x] Automated: invoice issuance and void/cancel authorization tests pass
- [ ] Manual: finance can create invoices for both leads and customers
- [ ] Manual: managers and reps can view accessible invoices but cannot issue or void them

**Suggested verification commands**
```bash
php artisan test --filter=InvoiceWorkflow
php artisan test --filter=InvoiceCalculation
php artisan test --filter=InvoiceAuthorization
```

**Notes**
- This phase must not introduce payment capture, receipts, or recurring billing.
- Manual renewal billing should be represented through normal invoice lines and metadata only.
- Implementation landed on April 21, 2026 with finance-owned invoice routes, authoring views, invoice header and line-item validation, catalog/custom billing lines, lifecycle actions for draft/issued/sent/cancelled/void, snapshotted commercial fields, and client-side totals preview backed by server-side recalculation.
- Remaining gap before completion: manual finance QA for lead-based and customer-based invoice authoring plus manager/rep read-only verification in the browser.

---

### Phase 6: PDF Delivery and CRM Integrations

**Status:** IN PROGRESS  
**Goal:** Attach the commercial module to the rest of the CRM through document generation, secure storage, search, and related-record visibility.

**Implementation targets**
- `app/Http/Controllers/Crm/`
- `app/Services/Crm/`
- `resources/views/crm/`
- `resources/views/pdf/` or equivalent document views
- `routes/crm/`
- `tests/Feature/Crm/`

**Scope**
- [x] Generate branded PDFs for quotes
- [x] Generate branded PDFs for invoices
- [x] Store generated files privately on the CRM documents storage path
- [x] Add guarded open/download routes for quote and invoice PDFs
- [x] Add share actions aligned with CRM discussion and email-style delivery patterns
- [x] Surface related quotes and invoices on lead detail pages
- [x] Surface related quotes and invoices on customer detail pages
- [x] Surface related quotes and invoices on request detail pages when linked
- [x] Expand CRM global search to include products, quotes, and invoices

**Phase 6 completion gate**
- [x] Automated: PDF open/download authorization tests pass
- [x] Automated: global-search integration tests pass
- [x] Automated: lead/customer/request related-record rendering tests pass
- [ ] Manual: generated PDFs are branded, readable, and downloadable by authorized users only
- [ ] Manual: commercial documents appear in the correct linked CRM records

**Suggested verification commands**
```bash
php artisan test tests/Feature/Crm/CrmCommercialDocumentDeliveryTest.php
php artisan test tests/Feature/Crm/CrmCommercialIntegrationTest.php
php artisan test tests/Feature/Crm/CrmGlobalSearchTest.php
```

**Notes**
- Reuse the existing private-document storage model already established by request attachments.
- Keep delivery authenticated and CRM-native; do not add public customer document links in v1.
- Implementation landed on April 21, 2026 with branded quote and invoice PDFs, private artifact persistence on the CRM `documents` disk, guarded open/download endpoints, discussion-backed share flows, related-document panels on lead/customer/request records, and CRM global search coverage for products, quotes, and invoices.
- Remaining gap before completion: manual QA for PDF readability/branding, authorized browser delivery, and linked-record visibility in the live CRM UI.

---

### Phase 7: QA, Hardening, and Rollout Closure

**Status:** IN PROGRESS  
**Goal:** Close the feature with regression coverage, manual QA, and documentation updates that make rollout safe.

**Implementation targets**
- `tests/Feature/Crm/`
- `tests/Unit/`
- `docs/`

**Scope**
- [x] Add regression coverage for role access, calculations, numbering, and snapshot stability
- [x] Add edge-case coverage for invalid account context, missing contacts, and inactive products
- [x] Verify new role handling in CRM user management and access control
- [x] Verify old records and existing CRM modules still behave correctly after commercial module wiring
- [x] Update PRD and tracker status based on landed implementation state
- [ ] Record automated and manual verification evidence in this tracker

**Phase 7 completion gate**
- [x] Automated: targeted commercial test suite passes
- [x] Automated: existing CRM regression suite passes for touched modules
- [ ] Manual: end-to-end smoke test passes for catalog, quote, invoice, PDF, and integration flows
- [x] Documentation: PRD and tracker are updated to reflect actual shipped scope

**Suggested verification commands**
```bash
php artisan test tests/Feature/Crm tests/Unit/Crm/CommercialDocumentCalculatorTest.php
php artisan test tests/Feature/Crm/CrmCommercialHardeningTest.php
```

**Notes**
- This phase is not complete until both the tracker and PRD reflect the final implementation state.
- Implementation landed on April 21, 2026 with Phase 7 hardening for inactive catalog products on fresh commercial documents, preservation of historical inactive lines on draft edits, feature-level invalid-context coverage, finance-role user-update coverage, and a broad CRM regression sweep across commercial and adjacent CRM modules.
- Remaining gap before completion: manual end-to-end browser QA for catalog, quote, invoice, PDF, and linked-record smoke flows.

---

## 6. Phase Completion Log

### Phase 1 Completion Evidence

**Date:** April 21, 2026

**Automated verification evidence**
- `test -f docs/CRM_Products_Quotes_Invoices_PRD.md`
- `test -f docs/CRM_Products_Quotes_Invoices_Phase_Tracker.md`
- `rg -n "Implementation Tracker" docs/CRM_Products_Quotes_Invoices_PRD.md`
- `rg -n "^## 4\\. Phase Overview|^## 6\\. Phase Completion Log" docs/CRM_Products_Quotes_Invoices_Phase_Tracker.md`

**Manual verification evidence**
- Confirmed the PRD covers scope, permissions, commercial rules, storage, integrations, and acceptance scenarios for the live CRM repo.
- Confirmed the phase tracker aligns with the PRD and provides an ordered delivery plan from foundation through rollout closure.

**Notes**
- Phase 1 was completed as a documentation-only change set.
- No application code or tests were added in this phase.

### Phase 2 Verification Evidence

**Date:** April 21, 2026

**Automated verification evidence**
- `php artisan test tests/Feature/Crm/CrmCommercialFoundationTest.php`
- Result: 3 passed covering finance-user creation, finance dashboard access restrictions, default commercial settings seeding, and core product/quote/invoice relationships.
- `php artisan test tests/Feature/Crm/CrmCommercialServicesTest.php`
- Result: 3 passed covering numbering-sequence persistence and commercial document validation for valid and invalid account-context payloads.
- `php artisan test tests/Unit/Crm/CommercialDocumentCalculatorTest.php`
- Result: 2 passed covering line discounts, prorated document discounts, VAT-ready tax calculation, and unsupported discount-type rejection.
- `php artisan test tests/Feature/Crm/CrmUsersAndSettingsTest.php`
- Result: 6 passed confirming the finance-role expansion did not break existing CRM user/settings administration behavior.
- `php artisan test tests/Feature/Crm/CrmImportTest.php`
- Result: 8 passed confirming user import validation and import flows still work after role validation was widened to use configured CRM roles.

**Manual verification evidence**
- Pending.

**Notes**
- Phase 2 remains `IN PROGRESS` until manual verification is completed.

### Phase 3 Verification Evidence

**Date:** April 21, 2026

**Automated verification evidence**
- `php artisan test tests/Feature/Crm/CrmProductCatalogTest.php`
- Result: 2 passed covering finance-managed catalog create/update/deactivate flows and manager browse-only restrictions.
- `php artisan test tests/Feature/Crm/CrmCommercialSettingsTest.php`
- Result: 3 passed covering finance commercial settings updates, supported-currency management, default-currency protection, and manager access denial.
- `php artisan test tests/Feature/Crm/CrmCommercialNavigationTest.php`
- Result: 2 passed covering launcher/sidebar visibility for `Products`, finance commercial-settings visibility, and rep-scoped quote/invoice browsing.
- `php artisan test tests/Feature/Crm/CrmCommercialFoundationTest.php`
- Result: 3 passed confirming the Phase 2 finance-role expectations still hold after exposing the commercial settings tab and products module shell.
- `php artisan test tests/Feature/Crm/CrmPageRenderTest.php`
- Result: 2 passed confirming the new product catalog, quote shell, invoice shell, and commercial settings pages render successfully in the CRM shell.
- `php artisan test tests/Feature/Crm/CrmUsersAndSettingsTest.php`
- Result: 6 passed confirming existing admin-only settings and user-management behavior remains intact for non-finance roles.
- `php artisan test tests/Feature/Crm/CrmShellUtilitiesTest.php`
- Result: 4 passed confirming the launcher remains role-aware after the new products module and finance commercial settings entry were added.

**Manual verification evidence**
- Pending.

**Notes**
- Phase 3 remains `IN PROGRESS` until manual catalog and finance-settings QA is completed.
- Quote and invoice authoring are intentionally deferred to Phases 4 and 5; this phase only establishes the CRM workspace shell and source-data management surfaces.

### Phase 4 Verification Evidence

**Date:** April 21, 2026

**Automated verification evidence**
- `php artisan test tests/Feature/Crm/CrmQuoteWorkflowTest.php`
- Result: 4 passed covering rep quote creation for leads, customer-quote editing, catalog/custom line mixing, snapshot stability after catalog price changes, lifecycle transitions, and owner-scoped access control.
- `php artisan test tests/Feature/Crm/CrmPageRenderTest.php`
- Result: 2 passed confirming the quote create, edit, and show pages render successfully inside the CRM shell.
- `php artisan test tests/Feature/Crm/CrmCommercialServicesTest.php`
- Result: 4 passed confirming quote validation accepts the Phase 4 payload contract, invoice validation coverage remains healthy, and invalid account-context links are still rejected.
- `php artisan test tests/Feature/Crm/CrmCommercialNavigationTest.php`
- Result: 2 passed confirming the quotes workspace remains visible and ownership-based quote browsing rules still hold after replacing the placeholder quote shell with the real workflow.
- `php artisan test tests/Feature/Crm/CrmCommercialFoundationTest.php`
- Result: 3 passed confirming the commercial foundation and finance-role behavior still hold after adding quote workflow surfaces.
- `php artisan test tests/Feature/Crm/CrmProductCatalogTest.php`
- Result: 2 passed confirming catalog behavior remains stable for quote line-item source data.
- `php artisan test tests/Feature/Crm/CrmCommercialSettingsTest.php`
- Result: 3 passed confirming finance-owned currency, numbering, and discount settings remain stable for quote authoring dependencies.
- `php artisan test tests/Feature/Crm/CrmUsersAndSettingsTest.php`
- Result: 6 passed confirming the shared CRM controller changes did not break existing admin-only user and settings behavior.
- `php artisan test tests/Feature/Crm/CrmShellUtilitiesTest.php`
- Result: 4 passed confirming the CRM shell, launcher, and scoped search utilities still render and behave correctly after the quote workflow wiring.

**Manual verification evidence**
- Pending.

**Notes**
- Phase 4 remains `IN PROGRESS` until manual rep QA is completed for both lead-based and customer-based quote authoring.

### Phase 5 Verification Evidence

**Date:** April 21, 2026

**Automated verification evidence**
- `php artisan test tests/Feature/Crm/CrmInvoiceWorkflowTest.php`
- Result: 5 passed covering finance invoice creation for leads, customer-invoice editing, catalog/custom line mixing, snapshot stability after catalog price changes, finance-only lifecycle transitions, and manager/rep read-only permission boundaries.
- `php artisan test tests/Feature/Crm/CrmPageRenderTest.php`
- Result: 2 passed confirming the invoice create, edit, and show pages render successfully inside the CRM shell.
- `php artisan test tests/Feature/Crm/CrmCommercialServicesTest.php`
- Result: 4 passed confirming invoice validation accepts the Phase 5 payload contract while quote validation and invalid account-context rejection remain intact.
- `php artisan test tests/Feature/Crm/CrmCommercialNavigationTest.php`
- Result: 2 passed confirming invoice index ownership scoping still holds after replacing the placeholder invoice shell with the real workflow.
- `php artisan test tests/Feature/Crm/CrmQuoteWorkflowTest.php`
- Result: 4 passed confirming the existing quote workflow still behaves correctly after the invoice routes and views were added.
- `php artisan test tests/Feature/Crm/CrmCommercialFoundationTest.php`
- Result: 3 passed confirming the commercial foundation and finance-role behavior still hold after adding invoice workflow surfaces.
- `php artisan test tests/Feature/Crm/CrmCommercialSettingsTest.php`
- Result: 3 passed confirming finance-owned currency, numbering, and discount settings remain stable for invoice authoring dependencies.
- `php artisan test tests/Feature/Crm/CrmProductCatalogTest.php`
- Result: 2 passed confirming catalog behavior remains stable for invoice line-item source data.
- `php artisan test tests/Feature/Crm/CrmUsersAndSettingsTest.php`
- Result: 6 passed confirming the shared CRM controller changes did not break existing admin-only user and settings behavior.
- `php artisan test tests/Feature/Crm/CrmShellUtilitiesTest.php`
- Result: 4 passed confirming the CRM shell, launcher, and scoped search utilities still render and behave correctly after the invoice workflow wiring.

**Manual verification evidence**
- Pending.

**Notes**
- Phase 5 remains `IN PROGRESS` until manual finance QA is completed for both lead-based and customer-based invoice authoring and non-finance browser verification.

### Phase 6 Verification Evidence

**Date:** April 21, 2026

**Automated verification evidence**
- `php artisan test tests/Feature/Crm/CrmCommercialDocumentDeliveryTest.php`
- Result: 4 passed covering quote/invoice PDF generation, private artifact persistence, guarded open/download access, quote sharing, invoice sharing, and artifact refresh after share-driven status changes.
- `php artisan test tests/Feature/Crm/CrmCommercialIntegrationTest.php`
- Result: 1 passed covering related commercial-document rendering on lead, customer, and request detail pages with CRM-native PDF action links.
- `php artisan test tests/Feature/Crm/CrmGlobalSearchTest.php`
- Result: 1 passed covering products/quotes/invoices search grouping and rep ownership scoping for commercial documents.
- `php artisan test tests/Feature/Crm/CrmPageRenderTest.php`
- Result: 2 passed confirming the CRM shell still renders the touched module and record pages after Phase 6 delivery wiring.
- `php artisan test tests/Feature/Crm/CrmShellUtilitiesTest.php`
- Result: 4 passed confirming existing launcher and global-search shell utilities remain healthy after expanding commercial search sections.
- `php artisan test tests/Feature/Crm/CrmQuoteWorkflowTest.php`
- Result: 4 passed confirming the quote workflow still behaves correctly after the share-service update and PDF delivery integration.
- `php artisan test tests/Feature/Crm/CrmInvoiceWorkflowTest.php`
- Result: 5 passed confirming invoice workflow permissions and lifecycle behavior remain stable after Phase 6 delivery integration.

**Manual verification evidence**
- Pending.

**Notes**
- Phase 6 remains `IN PROGRESS` until manual verification confirms branded PDF readability, authorized browser delivery, and linked-record visibility in the live CRM UI.

### Phase 7 Verification Evidence

**Date:** April 21, 2026

**Automated verification evidence**
- `php artisan test tests/Feature/Crm/CrmCommercialHardeningTest.php`
- Result: 4 passed covering missing-contact validation, XOR account-context validation at the HTTP layer, inactive-product rejection for fresh commercial documents, and preservation of historical inactive catalog lines on quote and invoice edits.
- `php artisan test tests/Feature/Crm/CrmUsersAndSettingsTest.php`
- Result: 7 passed confirming admin user management still works and now explicitly covers promoting an existing CRM user into the `finance` role.
- `php artisan test tests/Feature/Crm tests/Unit/Crm/CommercialDocumentCalculatorTest.php`
- Result: 77 passed across the CRM feature suite plus the commercial calculator unit coverage, confirming the commercial module and adjacent CRM modules still behave correctly after the Phase 7 hardening changes.

**Manual verification evidence**
- Pending.

**Notes**
- Phase 7 remains `IN PROGRESS` until manual end-to-end smoke testing is completed for catalog, quote, invoice, PDF delivery, and linked CRM record visibility.
