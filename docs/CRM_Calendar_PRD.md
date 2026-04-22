# CRM Calendar PRD

**Version:** 1.0  
**Date:** April 21, 2026  
**Author:** Codex  
**Status:** Draft  
**Implementation Tracker:** [CRM_Calendar_Phase_Tracker.md](./CRM_Calendar_Phase_Tracker.md)

---

## 1. Executive Summary

### 1.1 Purpose
This document defines the CRM calendar module for the live Heritage CRM at:

`/Users/thatoobuseng/Sites/Heritage Website`

The module adds a first-class scheduling workspace inside the CRM shell so internal teams can manage meetings, demos, follow-ups, support callbacks, and shared planning windows without leaving the account context they already use for leads, customers, contacts, requests, products, quotes, and invoices.

### 1.2 Why This Module Exists
The current CRM can track ownership, discussions, requests, and commercial documents, but it still lacks a native scheduling layer. Follow-ups and meetings are implied in notes and request fields, yet there is no visual team calendar, no shared scheduling space, no personal calendar workspace, and no controlled privacy model for calendar visibility.

### 1.3 Core Outcome
The CRM must gain a professional scheduling workspace with:
- personal calendars for CRM users
- shared calendars for teams and programs
- week, day, month, and agenda-style planning
- event CRUD with drag/drop and resize behavior
- links back to lead, customer, contact, and request context
- privacy-aware rendering for sensitive events

### 1.4 Current Repo Reality
This PRD assumes the live repo already includes:
- CRM access roles: `admin`, `finance`, `manager`, `rep`
- CRM shell routing via `routes/crm/*.php`
- launcher/sidebar registration via `config/heritage_crm.php`
- account context models: `Lead`, `Customer`, `Contact`, `CrmRequest`
- existing CRM modules for dashboard, customers, contacts, products, requests, dev, discussions, integrations, users, and settings
- bundled FullCalendar assets already available in `public/assets/libs/@fullcalendar`

---

## 2. Problem Statement

The CRM currently depends on scattered follow-up notes, request next actions, and external calendars to coordinate work. That creates several operating gaps:
- no shared team schedule inside CRM
- no visual workload view by owner or customer context
- no structured meeting record tied to a lead or customer
- no privacy-aware way to expose availability without exposing details
- no shared calendar spaces for finance, onboarding, implementation, or leadership work

The result is fragmented scheduling and weak operational visibility.

---

## 3. Goals and Non-Goals

### 3.1 Goals
- Add a `Calendar` module to the CRM shell for all CRM roles.
- Support personal and shared calendars.
- Support event create, edit, delete, move, resize, complete, and cancel flows.
- Support event visibility states for standard, busy-only, and private scheduling.
- Link events optionally to one CRM account context:
  - lead
  - customer
  - contact
  - request
- Reuse existing ownership and access rules so reps remain owner-scoped where appropriate.
- Present the module in a clean, professional week-view oriented workspace inspired by the supplied screenshot.

### 3.2 Non-Goals
- external Google or Outlook sync in v1
- recurring-series editing in v1
- notification dispatch jobs in v1
- room/resource booking
- public booking links
- attendance analytics or utilization reporting

---

## 4. Users and Jobs To Be Done

### 4.1 Sales Representative
- schedule demos and follow-up calls
- keep personal client commitments visible in one place
- link meetings directly to the right lead or request

### 4.2 Manager
- view team scheduling coverage
- coordinate shared calendars without exposing every sensitive detail
- intervene on schedule changes when deals or requests need escalation

### 4.3 Finance User
- maintain their own operational calendar
- participate in shared planning spaces such as invoice runs or renewal windows
- see availability on shared calendars without needing admin access

### 4.4 Administrator
- has full module visibility
- can create shared calendar spaces
- can inspect team schedules and manage access boundaries

---

## 5. Module Shape and Navigation

### 5.1 Workspace Entry
Add a new CRM workspace module:
- Label: `Calendar`
- Caption: `Meetings and follow-ups`

### 5.2 Primary Screen
- `/crm/calendar`

### 5.3 Main Surface Requirements
- left rail with compact month navigator
- visible-calendar toggles grouped into `My Calendars` and `Other Calendars`
- central planning canvas with:
  - week view
  - day view
  - month view
  - agenda mode
- `New Event` action
- modal create/edit workflow

---

## 6. Permissions and Visibility

### 6.1 Role Access
- `admin`: full access, full team visibility, shared-calendar creation
- `manager`: full team visibility, shared-calendar creation, event editing where calendar access allows
- `finance`: own personal calendar plus shared calendars they belong to
- `rep`: own personal calendar plus shared calendars they belong to

### 6.2 Personal Calendar Rules
- every CRM user gets one personal calendar
- reps do not see other reps’ personal calendars
- managers and admins can see team personal calendars

### 6.3 Shared Calendar Rules
- shared calendars are membership-based
- each member has one permission:
  - `view`
  - `edit`
  - `manage`

### 6.4 Event Privacy Rules
- `standard`: full title and metadata visible to authorized viewers
- `busy_only`: non-privileged viewers see occupied time but not details
- `private`: non-privileged viewers see only a protected hold

---

## 7. Domain Model

### 7.1 CrmCalendar
Purpose:
- represents a personal or shared planning space

Core fields:
- `owner_id`
- `name`
- `type`
- `color`
- `description`
- `is_active`
- `is_default`

### 7.2 CrmCalendarMembership
Purpose:
- maps users onto shared or personal calendars with a permission level

Core fields:
- `calendar_id`
- `user_id`
- `permission`
- `is_visible`

### 7.3 CrmCalendarEvent
Purpose:
- represents a scheduled CRM activity or operational block

Core fields:
- `calendar_id`
- `owner_id`
- optional `lead_id`
- optional `customer_id`
- optional `contact_id`
- optional `request_id`
- `title`
- `description`
- `location`
- `starts_at`
- `ends_at`
- `all_day`
- `status`
- `visibility`
- `timezone`
- `reminders`

Rules:
- at most one of `lead_id` or `customer_id`
- `contact_id` must align with the linked lead or customer when present
- `request_id` must align with the linked lead or customer when present

### 7.4 CrmCalendarEventAttendee
Purpose:
- stores internal attendees and linked CRM contact attendance

Core fields:
- `event_id`
- optional `user_id`
- optional `contact_id`
- `display_name`
- `email`
- `role`
- `response_status`

---

## 8. Functional Requirements

### 8.1 Calendar Workspace
- render the module inside the existing CRM layout
- expose summary metrics such as due today and overdue
- allow toggling visible calendars without leaving the page

### 8.2 Event CRUD
- create event
- edit event
- delete event
- mark event complete
- cancel event

### 8.3 Calendar Interactions
- drag event to a new time
- resize event duration
- select a time range to create a new event

### 8.4 Context Linking
- optionally link an event to:
  - lead
  - customer
  - contact
  - request

### 8.5 Shared Spaces
- allow shared calendar creation from the module
- allow adding CRM users as members

### 8.6 Privacy-Aware Rendering
- feed responses must redact titles and metadata when a viewer only has limited visibility
- sidebar and agenda summaries must respect the same redaction rules

---

## 9. Technical Approach

### 9.1 Routing
- add `routes/crm/calendar.php`
- include it from the CRM route group in `routes/web.php`

### 9.2 Controller and Service Layer
- `App\Http\Controllers\Crm\CalendarController`
- `App\Services\Crm\CrmCalendarService`

### 9.3 Validation
- `CrmCalendarStoreRequest`
- `CrmCalendarEventUpsertRequest`

### 9.4 UI
- server-rendered Blade workspace
- FullCalendar from bundled assets
- Bootstrap modal workflows
- fetch-based JSON event CRUD

### 9.5 Testing
- feature tests for:
  - calendar page access
  - personal calendar bootstrap
  - event CRUD
  - owner isolation
  - privacy redaction

---

## 10. Risks and Mitigations

### 10.1 Privacy Leaks
Risk:
- busy-only or private events accidentally expose details in non-calendar UI surfaces

Mitigation:
- centralize redaction decisions in the calendar service
- test feed redaction explicitly

### 10.2 Access Drift
Risk:
- managers, finance users, and reps could end up with inconsistent calendar visibility

Mitigation:
- keep visibility rules derived from the existing CRM role model
- use automated tests for role-specific expectations

### 10.3 Interaction Regressions
Risk:
- drag/drop and resize flows overwrite linked context or attendee state

Mitigation:
- preserve contextual metadata in event payloads
- verify update paths in feature tests

---

## 11. Success Criteria

- CRM users can load `/crm/calendar` successfully.
- Personal calendars bootstrap automatically.
- Shared calendars can be created and used inside the module.
- Users can create and edit calendar events with linked CRM context.
- Reps cannot see or modify another rep’s personal calendar entries.
- Busy-only shared events are redacted for view-only members.
- Automated calendar tests pass in the repo.

---

## 12. Implementation Phases

### Phase 1: PRD and tracker contract
- create this PRD
- create the execution tracker

### Phase 2: Calendar foundation and visibility
- schema
- models
- service layer
- personal-calendar bootstrap
- membership and privacy rules

### Phase 3: Interactive workspace and CRUD
- module registration
- route/controller wiring
- week/day/month/agenda UI
- create/edit/delete/status actions
- drag/drop and resize support

### Phase 4: Advanced workflow expansion
- recurrence
- reminder jobs
- richer attendee workflow
- external sync investigation

### Phase 5: QA and rollout closure
- focused automated tests
- manual verification across roles
- acceptance closeout
