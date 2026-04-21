# Heritage Pro CRM Phase Tracker

## Phase 1: CRM Foundation
Status: Implemented

Scope:
- Protected `/crm` route group behind `auth` and CRM access middleware.
- Authenticated landing page changed to `/crm`.
- `dashboard` and `home` legacy paths now redirect to the CRM dashboard for internal users.
- CRM layout and shared Blade shell created.
- `users` table extended with `role` and `active`.

Acceptance notes:
- Guests are redirected to login when attempting to access CRM routes.
- Internal authenticated users land on `/crm`.
- CRM uses a dedicated back-office theme rather than the public marketing layout.

## Phase 2: Users and Settings
Status: Implemented

Scope:
- Admin-only users module created.
- Admin-only settings module created.
- Sales stages are managed as database records.

Acceptance notes:
- Admin can create internal users.
- Admin can update internal users.
- Admin can create and update sales stages.
- Managers and reps are blocked from users and settings.

## Phase 3: Leads, Customers, and Contacts
Status: Implemented

Scope:
- Customers workspace created with separate Leads and Customers tabs.
- Leads can be created, viewed, updated, and converted.
- Customers can be created, viewed, and updated.
- Contacts can be created, viewed, and updated.

Acceptance notes:
- Lead conversion creates a customer.
- Contacts move from lead to customer during conversion.
- Customer records can be created directly without a lead.

## Phase 4: Requests and Activity Timeline
Status: Implemented

Scope:
- Shared Requests module for both sales and support.
- Request detail screen includes activity logging.
- Sales requests use settings-managed stages.
- Support requests use support status flow.

Acceptance notes:
- Sales request validation requires a stage.
- Support request validation requires a support status.
- Activity timeline accepts calls, emails, meetings, and notes.
- Last-contact date is updated from logged activity.

## Phase 5: Hardening and Expansion Hooks
Status: Started

Scope:
- Feature tests for access control, user management, stage management, request validation, and lead conversion.
- PRD documentation added for future expansion.
- Internal data model prepared for additional modules.

Next recommended expansions:
- Opportunity values and forecasting.
- File attachments on requests.
- Customer health and renewal workflows.
- Email inbox sync.
- Reporting dashboards and exports.
