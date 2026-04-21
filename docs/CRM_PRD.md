# Heritage Pro CRM PRD

## Product Summary
Heritage Pro CRM is a new authenticated back-office workspace inside the existing Laravel application. It shares the Heritage visual language while focusing on internal operations: lead capture, customer management, contact records, sales and support request handling, and sales pipeline administration.

The CRM foundation is intentionally narrow. It creates the durable data model, permissions model, UI shell, and workflow rules needed to expand later without reworking the core structure.

## Goals
- Add a protected `/crm` workspace behind the Laravel web auth guard.
- Redirect authenticated internal users to `/crm` instead of the previous broken dashboard route.
- Support a distinct lead-to-customer lifecycle rather than collapsing both into one record type.
- Keep contact records reusable across pre-sale and post-sale work.
- Use a shared requests module for sales and support, with a timeline for logged activities.
- Make sales stages configurable in settings rather than hardcoded.
- Create a branded CRM shell that matches Heritage Pro visually without reusing the public marketing layout.

## Non-Goals
- No telephony integration.
- No external email inbox sync.
- No reporting module beyond dashboard summaries.
- No quotation, invoicing, or contract module in this phase.
- No self-service public registration for CRM users.
- No advanced workflow automation beyond manual logging and stage/status updates.

## Personas
### Administrator
- Full CRM access.
- Creates and updates internal users.
- Manages sales stage settings.
- Can operate every operational record.

### Manager
- Full access to leads, customers, contacts, and requests.
- Cannot manage users.
- Cannot manage settings.
- Can reassign ownership and update operational records across the team.

### Sales Representative
- Can access only records they own or are assigned to.
- Can create and update leads, customers, contacts, and requests within their scope.
- Can log activities such as calls, emails, meetings, and notes.
- Cannot manage users or settings.

## Module Inventory
### Dashboard
- KPI cards for active leads, live customers, open sales requests, open support requests, overdue follow-ups, and follow-ups due today.
- Sales pipeline stage distribution.
- Recent request activity timeline.
- Recently updated request list.

### Customers Workspace
The navigation label remains `Customers`, but the workspace is split into:
- Leads
- Customers

#### Leads screen
- List all accessible lead records.
- Create a lead.
- Open a lead detail page.
- Convert a lead into a customer.

#### Customers screen
- List all accessible customer records.
- Create a customer directly.
- Open a customer detail page.

### Contacts
- Create contacts linked to either a lead or a customer.
- Mark a contact as primary.
- Move contacts automatically from a lead to the created customer during conversion.

### Requests
- Unified list and detail screens for both request types:
  - `sales`
  - `support`
- Request detail includes timeline logging.
- Supports next action tracking and due dates.

### Users
- Admin-only module.
- Create internal users.
- Set role as `admin`, `manager`, or `rep`.
- Activate or deactivate internal accounts.

### Settings
- Admin-only module.
- Manage sales stages.
- Stage configuration includes:
  - Name
  - Position
  - Active flag
  - Won flag
  - Lost flag

## Screen Inventory
- `/crm`
- `/crm/leads`
- `/crm/leads/{lead}`
- `/crm/customers`
- `/crm/customers/{customer}`
- `/crm/contacts`
- `/crm/contacts/{contact}`
- `/crm/requests`
- `/crm/requests/{crmRequest}`
- `/crm/users`
- `/crm/settings`
- `/crm/settings/sales-stages`

## Entity Definitions
### Users
Purpose:
- Internal CRM authentication and ownership.

Key fields:
- `name`
- `email`
- `password`
- `active`
- `role`

Rules:
- Only active users can log in through the CRM path.
- Valid roles are `admin`, `manager`, `rep`.

### Leads
Purpose:
- Pre-sale institutional records before conversion.

Key fields:
- `owner_id`
- `company_name`
- `industry`
- `website`
- `email`
- `phone`
- `country`
- `status`
- `converted_at`
- `notes`

Allowed statuses:
- `active`
- `qualified`
- `converted`
- `lost`

### Customers
Purpose:
- Post-conversion or directly created institutional records.

Key fields:
- `owner_id`
- `lead_id`
- `company_name`
- `industry`
- `website`
- `email`
- `phone`
- `country`
- `status`
- `purchased_at`
- `notes`

Allowed statuses:
- `active`
- `onboarding`
- `inactive`

### Contacts
Purpose:
- People attached to either a lead or a customer.

Key fields:
- `owner_id`
- `lead_id`
- `customer_id`
- `name`
- `job_title`
- `email`
- `phone`
- `is_primary`
- `notes`

Rules:
- A contact must belong to exactly one parent: a lead or a customer.
- Only one primary contact should exist for a given parent after edits.

### Requests
Purpose:
- Shared sales and support work item.

Key fields:
- `owner_id`
- `lead_id`
- `customer_id`
- `contact_id`
- `sales_stage_id`
- `type`
- `title`
- `description`
- `support_status`
- `outcome`
- `next_action`
- `next_action_at`
- `last_contact_at`
- `closed_at`

Rules:
- A request must belong to a lead or a customer.
- `type = sales` requires a sales stage.
- `type = support` requires a support status.
- Support requests do not use sales stages.

### Request Activities
Purpose:
- Timeline entries for customer-facing work.

Key fields:
- `request_id`
- `user_id`
- `activity_type`
- `subject`
- `body`
- `occurred_at`

Allowed activity types:
- `call`
- `email`
- `meeting`
- `note`

### Sales Stages
Purpose:
- Settings-managed sales pipeline.

Key fields:
- `name`
- `slug`
- `position`
- `is_active`
- `is_won`
- `is_lost`

Rules:
- Stage names become unique slugs.
- A stage cannot be both won and lost.

## Workflow Rules
### Lead Lifecycle
1. Create lead.
2. Assign owner.
3. Add contacts.
4. Log sales requests and activities.
5. Convert to customer when the institution becomes an active client.

### Lead Conversion
When a lead is converted:
- Create a new customer record.
- Copy company identity and contact fields from the lead.
- Preserve the owner assignment.
- Link the new customer back to the source lead.
- Reassign contacts from `lead_id` to `customer_id`.
- Preserve request history by keeping the original `lead_id` while also attaching the new `customer_id`.
- Mark the lead as `converted`.
- Set `converted_at`.

### Sales Request Lifecycle
1. Cold or first-touch request is created as `sales`.
2. Stage is selected from settings-managed sales stages.
3. Rep or manager logs activities.
4. Outcome remains `pending` until won or lost.
5. Request can progress toward a purchase decision and close.

### Support Request Lifecycle
1. Support request is created as `support`.
2. Support status is set to `open`, `in_progress`, `resolved`, or `closed`.
3. Timeline is updated with interactions and notes.
4. Request can be closed separately from sales pipeline logic.

## Permissions
### Administrator
- Access dashboard, leads, customers, contacts, requests, users, and settings.
- Create and update all records.
- Manage stage configuration.

### Manager
- Access dashboard, leads, customers, contacts, and requests.
- Create and update all operational records.
- No access to users or settings.

### Sales Representative
- Access dashboard, leads, customers, contacts, and requests within owned scope.
- Create new operational records owned by themselves unless reassigned by manager/admin.
- No access to users or settings.

## Product Validation Rules
- User email must be unique among non-deleted users.
- User password must be confirmed on create and optional on update.
- Lead status must be one of the defined lead statuses.
- Customer status must be one of the defined customer statuses.
- Contact must link to one and only one of lead or customer.
- Request must link to at least one of lead or customer.
- Sales request must include `sales_stage_id`.
- Support request must include `support_status`.
- Request activity must include body and occurred timestamp.
- Sales stage position must be numeric and bounded.
- Sales stage cannot be both won and lost.

## UX Principles
- Use a dedicated CRM layout instead of the public website layout.
- Keep the same Heritage color language and typography.
- Prioritize dense but readable admin screens.
- Keep create and edit paths obvious from list and detail pages.
- Use tabs inside the Customers workspace rather than separate top-level navigation items for leads.

## Out of Scope for This Foundation
- Quotation builder.
- Task automation engine.
- Bulk imports.
- File attachments.
- Email threading or inbox sync.
- SMS or telephony integration.
- Opportunity value forecasting.
- Reporting exports beyond on-screen summaries.
- Customer success health scoring.

## Phase Plan
### Phase 1
- CRM route foundation
- Auth redirect to `/crm`
- CRM layout shell
- Roles and active flag on users
- Settings backbone

### Phase 2
- Users module
- Sales stage settings management

### Phase 3
- Leads and customers workspace
- Contacts module
- Lead conversion flow

### Phase 4
- Requests module
- Activity timeline
- Cold-call-to-purchase support in the sales workflow
- Support request handling in the same module

### Phase 5
- Hardening
- Test coverage expansion
- Expansion hooks for future CRM modules
