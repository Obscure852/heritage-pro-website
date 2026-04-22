# CRM Products, Quotes, and Invoices PRD

**Version:** 1.0  
**Date:** April 21, 2026  
**Author:** Codex  
**Status:** Phases 2-6 implemented, Phase 7 QA and hardening in progress  
**Implementation Tracker:** [CRM_Products_Quotes_Invoices_Phase_Tracker.md](./CRM_Products_Quotes_Invoices_Phase_Tracker.md)

---

## 1. Executive Summary

### 1.1 Purpose
This document defines the next commercial phase of the CRM that lives in:

`/Users/thatoobuseng/Sites/Heritage Website`

The phase adds a new CRM workspace module named `Products` with three operating areas:
- Catalog
- Quotes
- Invoices

The module is designed for B2B SaaS selling rather than stock or warehouse commerce. It must support software licenses, add-ons, setup and implementation fees, support packages, training, and one-off custom commercial lines.

### 1.2 Current Repo Reality
The live repo now includes a functioning CRM commercial foundation with:
- leads
- customers
- contacts
- sales and support requests
- internal discussions and outbound email-style discussions
- integrations
- settings
- CRM global search
- an operational CRM product catalog
- quote and invoice records with snapshotted totals
- commercial numbering and currency settings
- branded private PDF delivery for quotes and invoices
- the `finance` role
- linked commercial-document visibility on lead, customer, and request detail screens

The remaining rollout work is now concentrated in manual end-to-end QA and final closure rather than core module construction.

The public website already has marketing-facing `Products` and `Pricing` pages, but those are static website content only. They are not the operational CRM catalog and must not be treated as the source of truth for sales documents.

### 1.3 Core Outcome
The CRM must gain a commercial workflow that lets sales prepare branded quotes, lets finance issue invoices, and keeps both document types connected to live CRM account context without introducing a full accounting or inventory system.

### 1.4 Success Criteria
- CRM users can manage a catalog of sellable SaaS products and service offerings.
- Reps and managers can prepare and share quotes for either leads or customers.
- Finance users can create, issue, and share invoices for either leads or customers.
- Every quote or invoice is linked to exactly one account context and one recipient contact.
- Document totals remain historically stable even if catalog pricing changes later.
- New commercial documents are searchable and visible from lead, customer, and optional request detail pages.
- Generated PDFs are stored privately and shared through existing CRM patterns.

---

## 2. Problem Statement

The CRM currently tracks pipeline context and customer communication, but it stops before the commercial transaction layer. Teams can manage leads, customers, contacts, and requests, yet still have to handle products, quotes, invoices, discounts, and currency conventions outside the system.

That creates several gaps:
- no catalog of what Heritage Pro actually sells
- no system-native quote preparation workflow
- no invoice issuance workflow owned by finance
- no consistent numbering or currency rules
- no durable commercial record attached to the account timeline
- no branded PDF output stored inside the CRM

The solution must close those gaps without turning the CRM into a full finance ERP.

---

## 3. Goals and Non-Goals

### 3.1 Goals
- Add an operational `Products` workspace inside the CRM shell.
- Support a reusable product catalog for SaaS and service sales.
- Add quote authoring, sharing, and lifecycle management.
- Add invoice authoring, issuance, sharing, and lifecycle management.
- Add finance-owned commercial settings for currencies, numbering, and discount policy.
- Preserve existing CRM ownership and access patterns while introducing a `finance` role.
- Reuse existing CRM discussions, private-document storage, and search conventions where practical.

### 3.2 Non-Goals
- Inventory or stock tracking
- Warehousing or fulfillment
- Payment capture, receipts, balances, or overdue collections
- External accounting sync
- Customer self-service portal access
- Automated recurring billing
- Exchange-rate automation
- Discount approval workflow
- Public website CMS management of CRM products

---

## 4. Personas and Jobs To Be Done

### 4.1 Sales Representative
- Needs to prepare accurate quotes from a known catalog.
- Needs to add custom lines when a deal includes implementation or special commercial terms.
- Needs to share quote PDFs from the CRM without leaving account context.

### 4.2 Manager
- Needs visibility into quotes and invoices linked to accounts and sales requests.
- Needs to review commercial activity across the team without taking over finance ownership.

### 4.3 Finance User
- Owns invoice issuance and commercial controls.
- Needs to manage numbering, currencies, catalog pricing, and invoice documents centrally.
- Needs to send branded invoice PDFs while preserving account context.

### 4.4 Administrator
- Retains full access across CRM, finance, and setup.
- Controls user-role assignment, including the new `finance` role.

---

## 5. Current-State Integration Baseline

This PRD assumes the commercial module extends the existing CRM foundation rather than replacing it.

### 5.1 Existing CRM Foundations To Reuse
- `config/heritage_crm.php` defines sidebar modules, child navigation, roles, and CRM setting sections.
- `App\Services\Crm\CrmModuleRegistry` drives launcher and sidebar module registration.
- `Lead`, `Customer`, `Contact`, and `CrmRequest` are the current account and opportunity context records.
- `DiscussionThread` provides existing internal and outbound communication flows.
- `RequestAttachment` and the private `documents` filesystem disk already establish a safe private-document storage pattern.
- `App\Services\Crm\CrmGlobalSearchService` already groups CRM results and should expand to include products, quotes, and invoices.

### 5.2 Existing Constraints To Respect
- CRM access is currently role-based through `admin`, `manager`, and `rep`.
- Ownership rules already differentiate between full-team operators and owner-scoped reps.
- Discussions already support app, email, and integration-backed delivery modes.
- The CRM does not yet include any billing or contract subsystem.

### 5.3 Module Placement
The new capability belongs in the authenticated CRM workspace, not the public website. It should appear as a first-class CRM launcher/sidebar module named `Products`.

---

## 6. Module Shape and Navigation

### 6.1 Workspace Entry
Add a new CRM workspace module:
- Label: `Products`
- Caption: `Catalog, quotes, and invoices`

### 6.2 Child Navigation
The module must expose these child sections:
- `Catalog`
- `Quotes`
- `Invoices`

### 6.3 Settings Extension
Extend CRM settings with a finance-owned commercial settings area:
- `Commercial`

This section must cover:
- default currency
- supported currencies
- quote numbering
- invoice numbering
- default tax behavior
- discount policy flags

### 6.4 Screen Inventory
- `/crm/products/catalog`
- `/crm/products/catalog/create`
- `/crm/products/catalog/{product}`
- `/crm/products/catalog/{product}/edit`
- `/crm/products/quotes`
- `/crm/products/quotes/create`
- `/crm/products/quotes/{quote}`
- `/crm/products/quotes/{quote}/edit`
- `/crm/products/invoices`
- `/crm/products/invoices/create`
- `/crm/products/invoices/{invoice}`
- `/crm/products/invoices/{invoice}/edit`
- `/crm/settings/commercial`

---

## 7. Domain Model

### 7.1 Product
Purpose:
- Represents a sellable SaaS or service offering that can be used as a source line on quotes and invoices.

Required fields:
- `name`
- `type`
- `default_unit_label`
- `default_unit_price`
- `active`

Optional fields:
- `code`
- `description`
- `billing_frequency`
- `default_tax_rate`
- `notes`

Allowed `type` values in v1:
- `license`
- `addon`
- `implementation`
- `support`
- `training`
- `service`

Allowed `billing_frequency` values in v1:
- `one_time`
- `monthly`
- `annual`
- `custom`

Rules:
- No stock quantity exists in v1.
- Inactive products remain selectable only for historical display, not for new documents.
- Catalog pricing is a default, not a live backfill for old documents.

### 7.2 Quote
Purpose:
- A commercial proposal prepared by sales against a lead or customer.

Required fields:
- `owner_id`
- `lead_id` or `customer_id`
- `contact_id`
- `quote_number`
- `status`
- `quote_date`
- `valid_until`
- currency snapshot fields
- totals snapshot fields

Optional fields:
- `request_id`
- `subject`
- `notes`
- `terms`
- `shared_at`
- `cancelled_at`
- `accepted_at`
- `rejected_at`
- `expired_at`

Allowed statuses:
- `draft`
- `sent`
- `accepted`
- `rejected`
- `expired`
- `cancelled`

Rules:
- A quote must belong to exactly one account context: `lead_id` xor `customer_id`.
- A quote must always name one recipient contact through `contact_id`.
- A quote may optionally link to one sales request through `request_id`.
- Quotes remain independent documents and do not require invoice conversion.

### 7.3 Quote Item
Purpose:
- Line items stored under a quote using catalog snapshots or custom values.

Required fields:
- `quote_id`
- `source_type`
- item snapshot fields
- quantity and pricing fields
- discount and tax snapshot fields
- line total snapshot fields

Allowed `source_type` values:
- `catalog`
- `custom`

Rules:
- `product_id` is nullable and present only when sourced from the catalog.
- All commercial values are copied onto the line at save time.

### 7.4 Invoice
Purpose:
- A commercial billing document issued by finance against a lead or customer.

Required fields:
- `owner_id`
- `lead_id` or `customer_id`
- `contact_id`
- `invoice_number`
- `status`
- `invoice_date`
- currency snapshot fields
- totals snapshot fields

Optional fields:
- `request_id`
- `subject`
- `notes`
- `terms`
- `shared_at`
- `issued_at`
- `cancelled_at`
- `voided_at`

Allowed statuses:
- `draft`
- `issued`
- `sent`
- `cancelled`
- `void`

Rules:
- An invoice must belong to exactly one account context: `lead_id` xor `customer_id`.
- An invoice must always name one recipient contact through `contact_id`.
- Invoices can be created for both leads and customers in v1.
- Manual license-renewal invoices are supported as standard invoice lines and notes.
- Invoices remain independent from quotes in v1. There is no quote-to-invoice conversion flow.

### 7.5 Invoice Item
Purpose:
- Line items stored under an invoice using catalog snapshots or custom values.

Required fields:
- `invoice_id`
- `source_type`
- item snapshot fields
- quantity and pricing fields
- discount and tax snapshot fields
- line total snapshot fields

Allowed `source_type` values:
- `catalog`
- `custom`

### 7.6 Commercial Settings
Purpose:
- Finance-owned commercial configuration used when creating new documents.

Required capability areas:
- default currency
- supported currencies
- quote number prefix and next sequence
- invoice number prefix and next sequence
- default tax rate
- discount policy flags

Rules:
- Settings affect only newly created documents.
- Existing quotes and invoices retain their stored snapshots.

---

## 8. Commercial Rules

### 8.1 Account Context Rule
Every quote and invoice must satisfy all of the following:
- exactly one of `lead_id` or `customer_id` is present
- `contact_id` is required
- `request_id` is optional

### 8.2 Currency Rule
- One document uses exactly one currency.
- Currency is selected from the configured supported currencies list.
- Each document stores a full currency snapshot:
  - `currency_code`
  - `currency_symbol`
  - `currency_position`
  - `currency_precision`
- No exchange-rate lookup or conversion exists in v1.

### 8.3 Discount Rule
Supported discount types:
- `none`
- `fixed`
- `percent`

Supported discount scopes:
- line-level
- document-level

Calculation order:
1. line gross = `quantity * unit_price`
2. line discount is applied
3. document subtotal is the sum of discounted line bases before tax
4. document-level discount is applied to the discounted pre-tax subtotal
5. tax is calculated on the remaining discounted taxable base
6. document total = discounted subtotal + tax total

For multi-line documents, the document-level discount must be prorated across lines for tax calculation so stored tax totals remain defensible.

### 8.4 Tax Rule
- v1 is VAT-ready, not jurisdiction-specific.
- Each line stores an explicit `tax_rate`.
- A `tax_rate` of `0` is allowed.
- Tax is calculated from stored line and document discount results, not by re-reading product settings later.

### 8.5 Numbering Rule
- Quotes and invoices use separate sequences.
- Each sequence uses a configurable prefix and an incrementing numeric counter.
- Numbering is assigned when a document is first created.
- Existing document numbers are never retroactively changed.

### 8.6 Lifecycle Rule
Quote lifecycle:
- `draft` may be edited
- `sent` indicates external sharing
- `accepted`, `rejected`, `expired`, and `cancelled` are terminal in v1

Invoice lifecycle:
- `draft` may be edited
- `issued` means finance has finalized the invoice
- `sent` means the invoice has been shared externally
- `cancelled` is used for invoices stopped before active use
- `void` is used when an already issued invoice must be invalidated

### 8.7 Historical Snapshot Rule
Quotes and invoices must store historical commercial snapshots for:
- item name
- item description
- quantity
- unit label
- unit price
- tax rate
- line discount type and value
- line discount amount
- line subtotal
- line tax amount
- line total
- document discount type and value
- document discount amount
- document subtotal
- document tax total
- document grand total
- currency details

Editing or deactivating catalog products later must never mutate historical document rows.

---

## 9. Functional Requirements

### 9.1 Catalog Management
Finance and admins must be able to:
- create products
- edit products
- deactivate products
- view product detail
- search and filter products by name, code, type, frequency, and active state

Managers and reps must be able to:
- browse the catalog
- search the catalog when building quote lines
- view current default pricing and product descriptions

### 9.2 Quote Authoring
Reps, managers, finance, and admins must be able to create quotes, subject to role permissions.

Quote authoring must support:
- selecting either a lead or a customer
- selecting one linked contact
- optionally linking a sales request
- adding catalog lines
- adding custom manual lines
- editing quantities, descriptions, price, tax, and discounts
- setting `quote_date` and `valid_until`
- storing internal notes and customer-facing terms
- recalculating totals live before save

### 9.3 Quote Sharing
Quotes must support:
- branded PDF generation
- private storage of generated files
- internal open/download actions
- share logging through CRM communication patterns
- marking a quote as `sent`

Accepted and rejected outcomes must be explicit manual CRM actions in v1. No signature or acceptance portal is included.

### 9.4 Invoice Authoring
Finance and admins must be able to:
- create invoices for a lead or customer
- select one linked contact
- optionally link a sales request
- add catalog lines
- add custom manual lines
- create manual license-renewal invoices without a subscription engine
- edit draft invoices before issuance

Managers and reps may view accessible invoices but do not issue them.

### 9.5 Invoice Issuance and Sharing
Invoices must support:
- branded PDF generation
- internal open/download actions
- private storage of generated files
- manual issuance action that moves status to `issued`
- external share action that moves status to `sent`
- cancel or void actions based on status

### 9.6 Commercial Settings
Finance and admins must be able to manage:
- supported currencies
- default currency
- numbering prefixes
- next numbering sequence values
- default tax rate
- whether line discounts are enabled
- whether document discounts are enabled

### 9.7 Search and Discovery
CRM global search must expand to include:
- products
- quotes
- invoices

Searchable fields must include:
- products: name, code, type
- quotes: number, subject, company name, contact name, status
- invoices: number, subject, company name, contact name, status

### 9.8 Record-Level CRM Integration
Lead detail pages must show:
- related quotes
- related invoices

Customer detail pages must show:
- related quotes
- related invoices

Request detail pages must show:
- related quotes and invoices when `request_id` is set

Document detail pages must show:
- linked lead or customer
- linked contact
- linked request when present
- owner
- document status
- share history summary

---

## 10. Permissions and Authorization

### 10.1 Roles
The CRM role list expands from:
- `admin`
- `manager`
- `rep`

to:
- `admin`
- `finance`
- `manager`
- `rep`

### 10.2 Role Matrix

| Capability | Admin | Finance | Manager | Rep |
| --- | --- | --- | --- | --- |
| View catalog | Yes | Yes | Yes | Yes |
| Manage catalog | Yes | Yes | No | No |
| Create/edit quotes | Yes | Yes | Yes | Yes |
| Share quotes | Yes | Yes | Yes | Yes |
| Mark quote outcome | Yes | Yes | Yes | Yes |
| View invoices | Yes | Yes | Yes | Yes |
| Create/edit draft invoices | Yes | Yes | No | No |
| Issue invoices | Yes | Yes | No | No |
| Void/cancel invoices | Yes | Yes | No | No |
| Manage commercial settings | Yes | Yes | No | No |
| Manage CRM users | Yes | No | No | No |

### 10.3 Ownership Model
- `rep` remains owner-scoped for quotes and invoice visibility.
- `manager` retains team-wide operational visibility.
- `admin` retains full-system visibility.
- `finance` gets team-wide access to products, quotes, invoices, and commercial settings.
- `finance` may read linked lead, customer, contact, and request context needed to issue or review commercial documents, but does not become a general CRM user-management role.

---

## 11. UX and Interaction Requirements

### 11.1 CRM Shell Consistency
The new module must use the existing CRM shell, card patterns, form patterns, and loading-button behavior already used across the workspace.

### 11.2 Catalog UX
Catalog screens must prioritize:
- searchable table or list view
- active/inactive visibility
- clear price display
- product type and billing frequency visibility

### 11.3 Quote and Invoice Editing UX
Document editing screens must prioritize:
- account context selection first
- contact selection second
- item entry grid third
- totals summary pinned or clearly visible
- notes and terms below core totals
- explicit save/share actions

### 11.4 Document Detail UX
Document detail pages must show:
- commercial summary
- line items
- totals block
- linked CRM context
- PDF actions
- lifecycle actions allowed by role and status

### 11.5 Lead and Customer Surface Rules
Lead and customer detail pages should add one commercial card or table area for:
- quotes
- invoices
- status
- amount
- date
- quick open actions

The intent is visibility, not a full embedded editing experience.

---

## 12. Document Storage and Delivery

### 12.1 PDF Generation
Quotes and invoices must generate branded PDFs suitable for customer sharing.

### 12.2 Storage Model
Generated PDFs must be stored privately using the same general protection model already used by CRM request attachments:
- private filesystem storage
- guarded open/download routes
- access checked through CRM role and ownership rules

Suggested storage convention for implementation:
- `crm/commercial/quotes/{quote_id}/...`
- `crm/commercial/invoices/{invoice_id}/...`

### 12.3 Delivery Model
v1 delivery must support:
- internal open/download from the CRM
- outbound sharing logged through CRM communication flows
- email-oriented sharing through the existing discussions model when used

v1 must not require:
- public unauthenticated document links
- customer portal access
- electronic signature flows

---

## 13. Data and Interface Expectations

### 13.1 Product Interface
Minimum product fields:
- `id`
- `name`
- `code`
- `type`
- `description`
- `billing_frequency`
- `default_unit_label`
- `default_unit_price`
- `default_tax_rate`
- `active`
- `notes`
- `created_at`
- `updated_at`

### 13.2 Quote and Invoice Header Interface
Minimum header fields:
- `id`
- `owner_id`
- `lead_id`
- `customer_id`
- `contact_id`
- `request_id`
- document number
- `status`
- document date
- validity or issue metadata
- currency snapshot fields
- document discount fields
- `subtotal_amount`
- `tax_amount`
- `total_amount`
- `notes`
- `terms`
- lifecycle timestamps

### 13.3 Quote and Invoice Item Interface
Minimum item fields:
- `id`
- header foreign key
- `product_id`
- `source_type`
- `position`
- `item_name`
- `item_description`
- `unit_label`
- `quantity`
- `unit_price`
- `gross_amount`
- `discount_type`
- `discount_value`
- `discount_amount`
- `tax_rate`
- `tax_amount`
- `net_amount`

### 13.4 Settings Interface
Minimum settings capabilities:
- supported currency list with code, symbol, precision, and position
- default currency selection
- quote prefix and next sequence
- invoice prefix and next sequence
- default tax rate
- discount policy toggles

---

## 14. Acceptance Test Scenarios

### 14.1 Quote Scenarios
- A rep can create a quote on a lead.
- A rep can create a quote on a customer.
- A quote can contain both catalog lines and custom lines.
- A quote can apply line-level discounts.
- A quote can apply a document-level discount.
- A quote can generate a branded PDF and share it.
- Changing the catalog price later does not alter the stored quote totals.

### 14.2 Invoice Scenarios
- A finance user can create an invoice for a lead.
- A finance user can create an invoice for a customer.
- A finance user can create a manual license-renewal invoice without quote conversion.
- An invoice can contain both catalog lines and custom lines.
- A finance user can issue an invoice and then share it as a PDF.
- A stored invoice remains numerically stable after catalog or settings changes.

### 14.3 Integration Scenarios
- Lead detail pages show linked quotes and invoices.
- Customer detail pages show linked quotes and invoices.
- Request detail pages show related documents when linked.
- Discussions and document storage can support CRM-native sharing flows.
- Global search returns products, quotes, and invoices with appropriate access filtering.

### 14.4 Permission Scenarios
- Reps cannot issue, void, or cancel invoices.
- Managers cannot manage commercial settings.
- Finance users can manage catalog, invoices, and commercial settings.
- Admins retain full access.

### 14.5 Settings Scenarios
- Changing the default currency affects only new documents.
- Renumbering settings affect only future documents.
- Older documents retain their snapped currency, numbering, and totals.

---

## 15. Assumptions

- The implementation extends the current CRM app and does not target the stale school-system path mentioned in legacy instructions.
- Quotes and invoices are independent document types in v1.
- A contact must exist before a quote or invoice can be created.
- Manual renewal billing is handled as standard document authoring, not recurring automation.
- VAT support in v1 means stored tax data and configurable rates, not jurisdiction-specific compliance logic.
- PDF generation follows the same private-document access philosophy already used for CRM attachments.

---

## 16. Explicit Out-of-Scope Confirmation

The following are intentionally excluded from this PRD:
- receipts
- payment tracking
- aging and overdue automation
- customer ledger balances
- refund and credit-note workflows
- external accounting export
- public quote acceptance pages
- stock or inventory deduction
- website-managed catalog synchronization
- auto-renewing or scheduled invoices
