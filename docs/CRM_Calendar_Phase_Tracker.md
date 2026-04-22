# CRM Calendar Phase Tracker

**Reference PRD:** [CRM_Calendar_PRD.md](./CRM_Calendar_PRD.md)  
**Module Status:** PHASES 1-3 IMPLEMENTED, PHASE 5 MANUAL QA PENDING  
**Last Updated:** April 21, 2026

---

## 1. Purpose

This tracker is the execution record for the CRM calendar rollout in:

`/Users/thatoobuseng/Sites/Heritage Website`

It exists so the module can be delivered in explicit phases and so implementation status is not inferred from code alone.

---

## 2. Status Rules

Allowed statuses:
- `NOT STARTED`
- `IN PROGRESS`
- `BLOCKED`
- `COMPLETE`

A phase is only `COMPLETE` when:
- the scoped code or documentation is present
- the relevant automated verification has passed where applicable
- any remaining manual QA gap is explicitly noted

---

## 3. Phase Overview

| Phase | Focus | Status | Depends On |
| --- | --- | --- | --- |
| Phase 1 | PRD and tracker contract freeze | COMPLETE | None |
| Phase 2 | Calendar foundation and visibility rules | COMPLETE | Phase 1 |
| Phase 3 | Interactive workspace and CRUD flows | COMPLETE | Phase 2 |
| Phase 4 | Recurrence, reminders, and workflow expansion | NOT STARTED | Phase 3 |
| Phase 5 | QA, manual verification, and rollout closure | IN PROGRESS | Phase 3 |

---

## 4. Detailed Phases

### Phase 1: PRD and Tracker Contract Freeze

**Status:** COMPLETE  
**Completed On:** April 21, 2026

**Scope**
- [x] Create the CRM calendar PRD in the live CRM repo
- [x] Create the CRM calendar tracker in the live CRM repo
- [x] Lock the initial phase sequence and boundaries

**Completion Evidence**
- `docs/CRM_Calendar_PRD.md`
- `docs/CRM_Calendar_Phase_Tracker.md`

---

### Phase 2: Calendar Foundation and Visibility Rules

**Status:** COMPLETE  
**Completed On:** April 21, 2026

**Scope**
- [x] Add CRM calendar schema
- [x] Add calendar, membership, event, and attendee models
- [x] Add service-layer access and privacy rules
- [x] Add personal-calendar bootstrap
- [x] Add shared calendar creation support
- [x] Add event validation rules

**Implementation Targets**
- `database/migrations/2026_04_21_190000_create_crm_calendar_tables.php`
- `app/Models/CrmCalendar.php`
- `app/Models/CrmCalendarMembership.php`
- `app/Models/CrmCalendarEvent.php`
- `app/Models/CrmCalendarEventAttendee.php`
- `app/Services/Crm/CrmCalendarService.php`
- `app/Http/Requests/Crm/CrmCalendarStoreRequest.php`
- `app/Http/Requests/Crm/CrmCalendarEventUpsertRequest.php`

**Completion Evidence**
- schema exists for calendars, memberships, events, and attendees
- privacy rules are centralized in the calendar service
- automated feature tests cover bootstrap, owner isolation, and busy-only redaction

---

### Phase 3: Interactive Workspace and CRUD Flows

**Status:** COMPLETE  
**Completed On:** April 21, 2026

**Scope**
- [x] Register `Calendar` in the CRM shell
- [x] Add the calendar route include
- [x] Add the calendar controller
- [x] Build the calendar workspace view
- [x] Add day, week, month, and agenda presentation modes
- [x] Add modal create/edit/delete flows
- [x] Add drag/drop and resize updates
- [x] Add shared calendar modal flow

**Implementation Targets**
- `config/heritage_crm.php`
- `routes/web.php`
- `routes/crm/calendar.php`
- `app/Http/Controllers/Crm/CalendarController.php`
- `resources/views/layouts/crm.blade.php`
- `resources/views/crm/calendar/index.blade.php`

**Completion Evidence**
- `/crm/calendar` renders inside the CRM shell
- event feed and CRUD endpoints respond successfully
- focused feature tests pass

---

### Phase 4: Recurrence, Reminders, and Workflow Expansion

**Status:** NOT STARTED

**Planned Scope**
- [ ] recurring series support
- [ ] reminder dispatch jobs and notifications
- [ ] stronger attendee response workflow
- [ ] external calendar sync evaluation

**Notes**
- The current implementation stores reminder values but does not dispatch reminder jobs.
- Recurrence is intentionally deferred to avoid locking the wrong domain model too early.

---

### Phase 5: QA, Manual Verification, and Rollout Closure

**Status:** IN PROGRESS

**Scope**
- [x] Automated test coverage for the calendar module
- [x] Calendar page render coverage in the broader CRM page-render suite
- [ ] Manual QA as:
  - [ ] admin
  - [ ] finance
  - [ ] manager
  - [ ] rep
- [ ] Manual browser verification of drag/drop, resize, and agenda interactions

**Verification Commands**
```bash
php artisan test --filter=CrmCalendarTest
php artisan test --filter=CrmPageRenderTest
```

**Notes**
- Automated verification is complete.
- Remaining gap is manual browser QA across the supported CRM roles.

---

## 5. Completion Log

### April 21, 2026
- Phase 1 completed by creating the PRD and tracker in the correct CRM repo.
- Phase 2 completed with calendar schema, models, requests, and the service-layer privacy/access model.
- Phase 3 completed with module registration, page rendering, feed endpoints, shared calendar creation, and event CRUD plus drag/drop/resize updates.
- Phase 5 moved to `IN PROGRESS` after automated verification passed with:
  - `php artisan test --filter=CrmCalendarTest`
  - `php artisan test --filter=CrmPageRenderTest`
