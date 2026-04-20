# Product Requirements Document (PRD)
# Boarding Module

**Version:** 2.0  
**Date:** February 19, 2026  
**System:** Junior School Management Platform (`/Users/thatoobuseng/Sites/Junior`)

## 1. Purpose

Build a Boarding Module that fits the existing architecture and operational model used by the Students module, while adding end-to-end boarding operations: dormitory setup, bed assignments, roll call, leave movement, incidents, sick bay flow, and reporting.

## 2. Context From Current Students Module

The current Students implementation establishes patterns this module must follow:

- Route architecture: module route file included in `routes/web.php`, with middleware guards and gate-based access.
- Term context: most queries are scoped by `session('selected_term_id')` with `TermHelper::getCurrentTerm()` fallback.
- Data model style: term-aware pivot-style records, soft deletes, and explicit indexes.
- Authorization: combination of `Gate::define(...)` in `AuthServiceProvider` and model policies.
- Service layer + transaction usage for critical write operations.
- Cache strategy through `CacheHelper` with targeted invalidation after writes.
- UI pattern: one module entry in sidebar, dashboard + list/detail/report pages, export support.
- Integration baseline: Students already integrates with Welfare (cases/health incidents), Houses, Search, and Setup module settings.

## 3. Product Goals

1. Provide reliable bed allocation and occupancy control per term.
2. Give boarding staff daily operational tools (roll call, leave movement, incident capture).
3. Integrate with Welfare for safeguarding/disciplinary/health escalation.
4. Provide auditable records and exports for compliance and administration.
5. Fit seamlessly into current roles, navigation, term switching, and code conventions.

## 4. Non-Goals (Phase 1)

- Student self-service boarding portal.
- Parent-facing boarding portal.
- Automated billing logic (keep integration-ready only).
- Biometric or hardware attendance integration.

## 5. Primary Users

- Boarding Admin
- Boarding Manager
- House Parent / Matron
- Boarding Staff (limited actions)
- Welfare team (cross-module visibility)
- Administrator (global oversight)

## 6. Functional Scope

### 6.1 Dormitory Structure Management

- Create and manage dormitories, blocks, rooms, and beds.
- Configure gender restrictions, capacity, and active status.
- Track real-time occupancy by dormitory and room.

### 6.2 Bed Assignment Lifecycle

- Assign student to bed for selected term.
- Transfer student between beds.
- End assignment (leave, suspension, term end, or manual release).
- Prevent multiple active bed assignments for same student/term.
- Prevent double occupancy on same bed/term.

### 6.3 Daily Boarding Operations

- Run roll call sessions (morning/evening/night).
- Record statuses (present, absent, excused, leave, sick_bay, hospital).
- Track leave passes (approved exit, return times, overdue tracking).

### 6.4 Incidents and Welfare Escalation

- Capture boarding incidents with severity and location.
- Support optional immediate welfare case creation.
- Link incident to generated/existing welfare case.

### 6.5 Sick Bay Workflow

- Admit student to sick bay from boarding context.
- Record reason, notes, timestamps, and discharge outcome.
- Optionally generate/link welfare health incident.

### 6.6 Reporting and Exports

- Occupancy report (dorm/room/bed).
- Unassigned boarders report.
- Roll call attendance report.
- Leave movement report (including overdue returns).
- Incidents and sick bay utilization reports.
- Excel/CSV export support.

## 7. Data Model Requirements

All operational tables must include `term_id` and `year` for consistency with current module behavior.

### 7.1 Core Tables

1. `boarding_dormitories`
- `id`, `name`, `code`, `gender_policy` (`boys`, `girls`, `mixed`), `capacity`, `active`
- `house_parent_id` (nullable), `matron_id` (nullable)
- timestamps, soft deletes

2. `boarding_rooms`
- `id`, `dormitory_id`, `name_or_number`, `floor`, `room_type`, `capacity`, `active`
- timestamps, soft deletes

3. `boarding_beds`
- `id`, `room_id`, `bed_number`, `bed_type`, `status` (`available`, `occupied`, `maintenance`, `reserved`)
- timestamps, soft deletes

4. `boarding_bed_assignments`
- `id`, `student_id`, `bed_id`, `term_id`, `year`
- `assigned_at`, `ended_at`, `status` (`active`, `ended`, `transferred`, `cancelled`)
- `assigned_by`, `ended_by`, `end_reason`
- timestamps, soft deletes

### 7.2 Operations Tables

1. `boarding_roll_calls`
- `id`, `dormitory_id`, `term_id`, `year`, `roll_call_type`, `roll_call_date`, `taken_by`
- timestamps

2. `boarding_roll_call_entries`
- `id`, `roll_call_id`, `student_id`, `bed_assignment_id`, `status`, `remarks`
- timestamps

3. `boarding_leave_passes`
- `id`, `student_id`, `term_id`, `year`, `leave_type`, `reason`
- `departure_time`, `expected_return_time`, `actual_return_time`
- `status` (`pending`, `approved`, `rejected`, `returned`, `overdue`)
- `approved_by`, `recorded_by`
- timestamps, soft deletes

### 7.3 Welfare-linked Tables

1. `boarding_incidents`
- `id`, `student_id` (nullable), `dormitory_id`, `room_id` (nullable), `bed_id` (nullable)
- `term_id`, `year`, `incident_type`, `severity`, `incident_datetime`
- `description`, `action_taken`, `reported_by`
- `welfare_case_id` (nullable)
- timestamps, soft deletes

2. `boarding_sick_bay_visits`
- `id`, `student_id`, `dormitory_id` (nullable), `term_id`, `year`
- `admitted_at`, `discharged_at`, `status`, `reason`, `notes`
- `health_incident_id` (nullable), `recorded_by`
- timestamps, soft deletes

### 7.4 Audit Table

`boarding_audit_logs`
- `id`, `user_id`, `entity_type`, `entity_id`, `action`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`

### 7.5 Required Constraints and Indexes

- Unique active assignment per student per term.
- Unique active occupancy per bed per term.
- Fast lookup indexes on (`term_id`, `year`, `status`) and (`student_id`, `term_id`).
- Foreign keys with delete behavior aligned to existing style (`restrictOnDelete` or `nullOnDelete` where appropriate).

## 8. Business Rules

1. Student must exist and be active in selected term before assignment.
2. Bed must be available and room/dormitory active.
3. Gender policy must match student gender unless dormitory is mixed.
4. Assignment and transfer actions must use database transactions.
5. Write-critical flows (assign/transfer/end, leave return, sick bay discharge) must use row locking to prevent race conditions.
6. One open leave pass per student at a time.
7. Roll call cannot be finalized without entries for all active boarders in target scope.

## 9. Authorization and Access

### 9.1 New Gates (AuthServiceProvider)

- `access-boarding`
- `manage-boarding-setup`
- `manage-boarding-assignments`
- `conduct-boarding-roll-call`
- `manage-boarding-leave`
- `manage-boarding-incidents`
- `view-boarding-reports`
- `view-boarding-audit`

### 9.2 Policy Requirements

Create `Boarding*Policy` classes mirroring existing module style (`viewAny`, `view`, `create`, `update`, `delete` plus operation-specific methods where needed).

### 9.3 Roles

Add role set aligned to current naming conventions:
- `Boarding Admin`
- `Boarding Edit`
- `Boarding View`
- `Boarding Staff`

## 10. Routing and Navigation

### 10.1 Routing

- New route file: `routes/boarding/boarding.php`
- Register in `routes/web.php`
- Route middleware baseline:
  - `auth`
  - `throttle:auth`
  - `block.non.african`
  - `can:access-boarding`

### 10.2 Sidebar and Visibility

- Add Boarding entry in `resources/views/layouts/sidebar.blade.php`.
- Add module visibility support in `ModuleVisibilityService` with key `modules.boarding_visible`.
- Add setup toggle in module settings UI (same pattern as welfare/lms/fees).

### 10.3 School Setup Linkage

- Respect existing `school_setup.boarding` flag as a prerequisite signal.
- If school is configured as day school, module should either hide or show read-only messaging (final behavior decision required).

## 11. Integration Requirements

### 11.1 Students Module

- Add boarding relationships to `Student` model:
  - `boardingAssignments()`
  - `currentBoardingAssignment()` (term-aware)
  - `boardingIncidents()`
  - `boardingLeavePasses()`
- Surface boarding summary on student profile view.

### 11.2 Welfare Module

- Incident escalation may create a `welfare_cases` record using existing service patterns.
- Sick bay flow may create/link `health_incidents` records.
- Maintain bidirectional linkage IDs for traceability.

### 11.3 Search

- Extend global search (`SearchController`) with `boarding` results for authorized users.

### 11.4 Fees (Integration-ready)

- On assignment create/end events, emit domain events for future fee charging.
- No direct fee posting in Phase 1.

## 12. Non-Functional Requirements

1. Performance
- Dashboard and list pages: target < 2.5s server-side response at normal load.
- Use eager loading and avoid N+1 patterns.

2. Reliability and Data Integrity
- Use transactions on all multi-table writes.
- Use `lockForUpdate()` where concurrent conflicts are possible.

3. Security
- Full gate/policy enforcement.
- Validation via FormRequest classes.
- Audit logging for sensitive operations.

4. Observability
- Structured logs for assignment, transfer, incident creation, and leave state transitions.

## 13. UI/UX Requirements

1. Dashboard
- Total boarders
- Occupied vs available beds
- Pending leave passes
- Active sick bay count
- Recent incidents

2. Key pages
- Dormitories list + detail occupancy matrix
- Bed assignment board (filterable by term/grade/class/dorm)
- Roll call sheet
- Leave pass management
- Incidents list/detail
- Sick bay register
- Reports and exports

3. Consistency
- Use existing Blade + Bootstrap + DataTables patterns.
- Reuse existing term selector behavior (session-based selected term).

## 14. Phased Implementation and Test Plan

### Phase 1: Foundations (Schema, Access, Module Shell)

Scope:
- Create migrations for boarding tables and indexes.
- Add boarding models and relationships.
- Add gates, policies, route registration, and sidebar/module-visibility wiring.
- Build setup CRUD for dormitories, rooms, and beds.

Deliverables:
- Boarding data structures available and term-aware.
- Authorized users can access `/boarding` and setup pages.
- Basic setup pages operational for create/edit/list flows.

Tests for Phase 1:
- Unit tests:
  - Model relationships and casts (`tests/Unit/Boarding/ModelRelationshipsTest.php`).
  - Policy and gate checks for boarding roles (`tests/Unit/Boarding/AuthorizationTest.php`).
- Feature tests:
  - Route protection (`unauthenticated`/`unauthorized`/`authorized`) for setup routes.
  - CRUD validation and persistence for dormitories, rooms, and beds.
- Integration tests:
  - Module visibility toggle hides/shows boarding menu and route entry points.
  - `school_setup.boarding` behavior for allowed/blocked access mode.

Exit criteria:
- All setup routes pass authorization and validation tests.
- Migrations run cleanly and rollback cleanly.
- CI test suite passes with no failing boarding tests.

### Phase 2: Core Operations (Assignments, Roll Call, Leave)

Scope:
- Implement bed assignment, transfer, and assignment end lifecycle.
- Implement roll call sessions and entry capture.
- Implement leave pass workflow (approve/reject/return/overdue).
- Add targeted cache invalidation for boarding operational data.

Deliverables:
- Boarding staff can run daily operations from assignment to roll call and leave tracking.
- Concurrency-safe assignment and transfer flows.

Tests for Phase 2:
- Unit tests:
  - Assignment service business rules (availability, gender policy, term scoping).
  - Leave pass status transition rules.
  - Roll call status aggregation helpers.
- Feature tests:
  - Assignment create/transfer/end endpoint behavior.
  - Roll call submission for full boarder set.
  - Leave approval and return flow, including overdue logic.
- Concurrency/integrity tests:
  - Parallel assignment attempts do not double-occupy a bed.
  - One active bed assignment per student per term is enforced.
  - Duplicate open leave pass attempts are rejected.

Exit criteria:
- No race-condition regressions in assignment flow.
- Operations are auditable and term-scoped in all tested endpoints.
- End-to-end boarding day scenario passes (assign -> roll call -> leave -> return).

### Phase 3: Welfare and Health Integration

Scope:
- Implement boarding incident management.
- Integrate incident escalation to welfare cases.
- Implement sick bay admissions/discharges with optional health incident linkage.
- Add cross-links in student and welfare views.

Deliverables:
- Boarding incidents and sick bay flows connected to Welfare module records.
- Traceable links between boarding and welfare/health artifacts.

Tests for Phase 3:
- Unit tests:
  - Incident escalation service behavior.
  - Sick bay admission/discharge state rules.
- Feature tests:
  - Incident creation with and without welfare case escalation.
  - Sick bay admit/discharge endpoint behavior and validations.
  - Access controls for sensitive incident/health actions.
- Integration tests:
  - Verify welfare case creation uses selected term context.
  - Verify linked IDs (`welfare_case_id`, `health_incident_id`) are persisted correctly.
  - Verify student profile shows boarding + welfare linkage summary.

Exit criteria:
- Boarding-to-welfare link flows are deterministic and audited.
- No orphan linked records in standard and rollback scenarios.
- Sensitive actions are fully gated and tested.

### Phase 4: Reporting, Exports, Hardening, and Regression

Scope:
- Implement boarding dashboard widgets and reports.
- Add export classes (CSV/Excel) for operational and compliance reports.
- Complete audit log browsing/filtering.
- Optimize query performance and finalize cache strategy.

Deliverables:
- Reporting surfaces for occupancy, roll call, leave, incidents, and sick bay.
- Export-ready outputs for administration and audits.
- Stable release candidate with regression coverage.

Tests for Phase 4:
- Unit tests:
  - Report query builders and aggregation correctness.
  - Export mappers/formatters for each report.
- Feature tests:
  - Report filters (term, dormitory, status, date range) return expected records.
  - Export endpoints return correct schema and row counts.
  - Audit log view permissions and filter behavior.
- Regression/performance tests:
  - Cross-module regression (students, welfare, setup sidebar/search).
  - Query count/performance checks on dashboard/report pages.
  - Smoke tests for full boarding workflows across phases 1-4.

Exit criteria:
- Report and export outputs match seeded fixture expectations.
- Performance targets for core list/report pages are met.
- Full boarding regression suite passes before production release.

## 15. Acceptance Criteria

1. Authorized user can create dormitories, rooms, and beds.
2. Bed assignment enforces one active bed per student per term.
3. Two concurrent assignment requests cannot double-assign same bed.
4. Roll call can be completed and later reported/exported.
5. Leave pass states transition correctly, including overdue handling.
6. Boarding incident can create/link a welfare case.
7. Sick bay admission/discharge flow persists correctly and is auditable.
8. Boarding menu visibility respects module visibility settings.
9. Data is term-scoped and follows selected term session behavior.
10. Unauthorized users cannot access boarding routes or actions.

## 16. Risks and Mitigations

1. Concurrency conflicts on assignment/transfer
- Mitigation: transactions + pessimistic locking + unique constraints.

2. Duplicate operational records from retries
- Mitigation: idempotency keys for selected POST actions (optional in phase 1, recommended in phase 2).

3. Role ambiguity between Students/Houses/Boarding responsibilities
- Mitigation: explicit gate matrix and workflow ownership in UAT.

## 17. Open Decisions

1. Should `school_setup.boarding = false` fully hide module or allow admin-only configuration?
2. Should boarding assignment be mandatory before roll call eligibility?
3. Should incidents always create welfare cases, or remain optional per severity?
4. Do we need parent notifications in phase 1 via communications module hooks?

---

## Appendix A: Suggested File Map

- `routes/boarding/boarding.php`
- `app/Http/Controllers/Boarding/*`
- `app/Models/Boarding/*`
- `app/Services/Boarding/*`
- `app/Policies/Boarding/*`
- `app/Http/Requests/Boarding/*`
- `app/Exports/Boarding/*`
- `resources/views/boarding/*`
- `database/migrations/*boarding*`

## Appendix B: Route Examples

- `GET /boarding` - dashboard
- `GET /boarding/dormitories`
- `POST /boarding/dormitories`
- `GET /boarding/assignments`
- `POST /boarding/assignments`
- `POST /boarding/assignments/{assignment}/transfer`
- `POST /boarding/assignments/{assignment}/end`
- `GET /boarding/roll-calls`
- `POST /boarding/roll-calls`
- `GET /boarding/leave-passes`
- `POST /boarding/leave-passes`
- `POST /boarding/leave-passes/{leavePass}/approve`
- `POST /boarding/incidents`
- `POST /boarding/sick-bay/admit`
- `POST /boarding/sick-bay/{visit}/discharge`
- `GET /boarding/reports/*`
