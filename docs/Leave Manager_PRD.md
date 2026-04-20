# Product Requirements Document (PRD)
# Integrated Staff Leave Management System

**Version:** 1.0
**Date:** December 24, 2025
**Author:** Claude Code
**Status:** Draft

---

## 1. Executive Summary

### 1.1 Purpose
Build a comprehensive Staff Leave Management System integrated with the existing College Management System. The system will manage leave requests, approvals, balances, and provide configurable policies for various leave types across the organization.

### 1.2 Scope
- **In Scope:** Leave requests, single-level approvals, balance tracking, configurable policies, leave types management, reporting, notifications
- **Out of Scope:** Student leave (they have attendance excusal), payroll integration, external HR system integration, multi-level approval chains

### 1.3 Target Users
| User Type | Description | Access Level |
|-----------|-------------|--------------|
| Staff/Faculty | Employees who request leave | Submit requests, view own balances/history |
| HOD/Manager | Department heads, direct supervisors | Approve/reject team requests, view team reports |
| HR Admin | Human resources administrators | Full system control, configure policies, manage all leave |
| System Admin | IT administrators | Configure settings, manage leave types |

---

## 2. Problem Statement

### 2.1 Current State
The college lacks a centralized digital leave management system, resulting in:
- Paper-based or email leave requests
- No visibility into leave balances
- Inconsistent approval processes
- Difficulty tracking leave history
- No automated policy enforcement
- Manual calculation of entitlements and carry-overs

### 2.2 Desired State
A fully integrated digital leave system that:
- Automates leave request and approval workflows
- Tracks leave balances in real-time
- Enforces configurable leave policies
- Provides self-service portal for staff
- Generates comprehensive reports
- Integrates with existing authentication and user management

---

## 3. Goals & Objectives

### 3.1 Primary Goals
1. **Digitize Leave Operations** - Replace manual processes with automated workflows
2. **Policy Enforcement** - Automatically apply leave rules and entitlements
3. **Transparency** - Real-time visibility into balances and request status
4. **Compliance** - Audit trail for all leave transactions

### 3.2 Success Metrics
| Metric | Target | Measurement |
|--------|--------|-------------|
| Request processing time | < 24 hours average | Approval timestamp delta |
| Self-service adoption | > 90% within 3 months | Request source analytics |
| Policy compliance | 100% | Audit reports |
| User satisfaction | > 4.0/5.0 rating | Surveys |

---

## 4. User Stories

### 4.1 Staff Stories
| ID | Story | Priority |
|----|-------|----------|
| S1 | As staff, I want to view my leave balances so I can plan time off | P0 |
| S2 | As staff, I want to submit leave requests with date ranges and type | P0 |
| S3 | As staff, I want to attach supporting documents (medical certificate) | P1 |
| S4 | As staff, I want to view my request history and status | P0 |
| S5 | As staff, I want to receive notifications on approval/rejection | P0 |
| S6 | As staff, I want to cancel pending requests | P1 |
| S7 | As staff, I want to see my team's leave calendar (for planning) | P2 |
| S8 | As staff, I want to see public holidays in my leave calendar | P2 |

### 4.2 Manager/HOD Stories
| ID | Story | Priority |
|----|-------|----------|
| M1 | As manager, I want to view pending requests from my team | P0 |
| M2 | As manager, I want to approve or reject requests with comments | P0 |
| M3 | As manager, I want to see team leave calendar for planning | P1 |
| M4 | As manager, I want to be notified of new requests | P0 |
| M5 | As manager, I want to view team leave balances | P1 |
| M6 | As manager, I want to view team leave history/reports | P2 |

### 4.3 HR Admin Stories
| ID | Story | Priority |
|----|-------|----------|
| H1 | As HR, I want to configure leave types with entitlements | P0 |
| H2 | As HR, I want to configure accrual vs allocation policies | P0 |
| H3 | As HR, I want to configure carry-over rules | P0 |
| H4 | As HR, I want to manually adjust leave balances | P1 |
| H5 | As HR, I want to view organization-wide leave reports | P1 |
| H6 | As HR, I want to import/bulk update leave balances | P2 |
| H7 | As HR, I want to configure leave year (fiscal/calendar) | P1 |
| H8 | As HR, I want to manage public holidays | P1 |

---

## 5. Functional Requirements

### 5.1 Leave Types Management (FR-LT)

#### FR-LT-01: Leave Type Configuration
Comprehensive leave types with configurable properties:

| Leave Type | Default Entitlement | Requires Attachment | Notes |
|------------|---------------------|---------------------|-------|
| Annual Leave | 21 days | No | Vacation/holiday |
| Sick Leave | 12 days | Yes (>2 days) | Medical certificate required |
| Compassionate Leave | 5 days | Yes (death cert) | Bereavement |
| Maternity Leave | 90 days | Yes (medical) | Female staff only |
| Paternity Leave | 10 days | Yes (birth cert) | Male staff only |
| Study Leave | 10 days | Yes (enrollment) | Academic purposes |
| Unpaid Leave | Unlimited | No | No balance tracking |
| Sabbatical | 365 days | Yes (application) | Requires special approval |
| Jury Duty | As needed | Yes (summons) | Civic duty |
| Special Leave | As approved | Optional | Administrative discretion |

#### FR-LT-02: Leave Type Properties
Each leave type must support:
- Name, code, description
- Default annual entitlement (days)
- Accrual settings (if using accrual mode)
- Carry-over eligibility and limits
- Document requirement rules
- Gender restriction (if applicable)
- Active/inactive status
- Color code for calendar display

### 5.2 Leave Balance Management (FR-BAL)

#### FR-BAL-01: Balance Tracking
- Track balances per employee per leave type per leave year
- Support both allocation (annual grant) and accrual (monthly) modes
- Real-time calculation: `available = entitled + carried_over - used - pending`

#### FR-BAL-02: Allocation Mode
- Grant full entitlement at start of leave year
- Optionally prorate for new employees based on join date
- Configurable leave year start (January, April, July, or custom)

#### FR-BAL-03: Accrual Mode
- Accrue days monthly (entitlement ÷ 12)
- Optionally cap accrued balance
- Support mid-month join date calculations

#### FR-BAL-04: Carry-Over
Configurable per leave type:
- No carry-over (use it or lose it)
- Limited carry-over (up to X days)
- Full carry-over
- Carry-over expiry (use within X months)

#### FR-BAL-05: Balance Adjustments
- HR can manually adjust balances with reason
- Track all adjustments with audit trail
- Support bulk adjustments via import

### 5.3 Leave Request Workflow (FR-REQ)

#### FR-REQ-01: Request Submission
- Select leave type
- Enter date range (start date, end date)
- Specify if half-day (AM/PM) for single-day requests
- Auto-calculate leave days (excluding weekends, holidays)
- Add reason/comments
- Attach supporting documents (optional/required based on type)
- Validate against balance before submission

#### FR-REQ-02: Request Validation Rules
- Cannot request dates in the past (configurable buffer)
- Cannot overlap with existing approved/pending requests
- Must have sufficient balance (except Unpaid Leave)
- Must meet minimum notice period (configurable per type)
- Attachment required based on leave type and duration

#### FR-REQ-03: Request Status Flow
```
DRAFT → PENDING → {APPROVED | REJECTED | CANCELLED}
                      ↓
                   TAKEN (on leave dates)
```

States:
- `draft`: Saved but not submitted
- `pending`: Awaiting approval
- `approved`: Approved by manager
- `rejected`: Rejected by manager
- `cancelled`: Cancelled by employee or HR
- `taken`: Leave period has passed

#### FR-REQ-04: Approval Workflow
- Single-level approval (direct manager or HOD)
- Approver determined by `reporting_to` field on User model
- Approver can approve, reject, or request more info
- Approval/rejection requires comments
- Fallback approver for when manager is on leave (HR)

### 5.4 Notifications (FR-NOT)

#### FR-NOT-01: Staff Notifications
- Request submitted confirmation
- Request approved (with approver comments)
- Request rejected (with reason)
- Balance running low warning
- Leave starting reminder (1 day before)

#### FR-NOT-02: Manager Notifications
- New request awaiting approval
- Request pending for >24 hours reminder
- Team member on leave today

#### FR-NOT-03: HR Notifications
- Requests pending >48 hours (escalation)
- Balance adjustment confirmations
- Policy change confirmations

### 5.5 Calendar & Scheduling (FR-CAL)

#### FR-CAL-01: Personal Leave Calendar
- View own approved/pending leave
- View public holidays
- Month/week/list views

#### FR-CAL-02: Team Calendar (Manager)
- View team's approved leave
- Color-coded by leave type
- Identify coverage gaps

#### FR-CAL-03: Holiday Management
- Configure public holidays per year
- Holidays excluded from leave day calculations
- Support recurring holidays

### 5.6 Reporting (FR-RPT)

#### FR-RPT-01: Individual Reports
- Leave history by type
- Balance summary
- Attendance record (present days)

#### FR-RPT-02: Team Reports (Manager)
- Team leave summary by type
- Team availability calendar
- Pending requests summary

#### FR-RPT-03: Organization Reports (HR)
- Leave utilization by department
- Leave type distribution
- Trend analysis (monthly/yearly)
- Outstanding balances report
- Carry-over report

#### FR-RPT-04: Export Capabilities
- Export to Excel/PDF
- Scheduled report generation
- Email distribution lists

### 5.7 Settings & Configuration (FR-SET)

#### FR-SET-01: General Settings
- Leave year configuration (calendar/fiscal, start month)
- Default leave policies
- Email notification templates
- Public holiday list

#### FR-SET-02: Policy Settings
- Balance mode (allocation/accrual) - global default, per-type override
- Carry-over rules per leave type
- Minimum notice period per leave type
- Document requirements per leave type
- Weekend definition (Sat-Sun or custom)

#### FR-SET-03: Leave Types CRUD
- Add/edit/deactivate leave types
- Set default entitlements
- Configure gender restrictions
- Set attachment requirements

---

## 6. Non-Functional Requirements

### 6.1 Performance
| Requirement | Target |
|-------------|--------|
| Page load time | < 2 seconds |
| Request submission | < 500ms |
| Balance calculation | < 200ms |
| Report generation | < 10 seconds |

### 6.2 Security
- Authentication via existing User guard
- Role-based access control (Spatie Laravel-Permission)
- Audit logging for all transactions
- Sensitive data (medical docs) encrypted at rest
- HTTPS for all communications

### 6.3 Availability
- 99.5% uptime during business hours
- Graceful degradation if notification services fail
- Automated backups included in system backup

### 6.4 Scalability
- Support 1000+ staff members
- Support 10,000+ requests per year
- Efficient queries with proper indexing

### 6.5 Compliance
- Data retention: 7 years for leave records
- GDPR-compliant data handling
- Audit trail for all changes

---

## 7. Data Model

### 7.1 Entity Relationship Overview

```
┌─────────────────┐     ┌──────────────────┐     ┌─────────────────┐
│      User       │────<│   LeaveRequest   │>────│    LeaveType    │
│  (Staff/Mgr)    │     └──────────────────┘     └─────────────────┘
└─────────────────┘              │                        │
        │                        │                        │
        │              ┌─────────┴───────┐               │
        │              │                 │               │
        ▼              ▼                 ▼               │
┌─────────────────┐ ┌────────────────┐ ┌───────────────┐│
│  LeaveBalance   │ │LeaveAttachment │ │ LeaveHistory  ││
└─────────────────┘ └────────────────┘ └───────────────┘│
        │                                               │
        └───────────────────────────────────────────────┘
                              │
                    ┌─────────┴─────────┐
                    │                   │
                    ▼                   ▼
            ┌─────────────────┐ ┌─────────────────┐
            │  LeavePolicy    │ │  PublicHoliday  │
            │  (per type)     │ │                 │
            └─────────────────┘ └─────────────────┘
```

### 7.2 Core Tables

#### leave_types
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | |
| code | VARCHAR(20) | UNIQUE, NOT NULL | Short code (e.g., ANN, SICK) |
| name | VARCHAR(100) | NOT NULL | Display name |
| description | TEXT | NULL | Detailed description |
| default_entitlement | DECIMAL(5,2) | NOT NULL | Default annual days |
| requires_attachment | BOOLEAN | DEFAULT false | Document required |
| attachment_required_after_days | INT | NULL | Days threshold for attachment |
| gender_restriction | ENUM | NULL | 'male', 'female', NULL |
| is_paid | BOOLEAN | DEFAULT true | Paid or unpaid leave |
| allow_negative_balance | BOOLEAN | DEFAULT false | Allow overdraw |
| allow_half_day | BOOLEAN | DEFAULT true | Half-day requests |
| min_notice_days | INT | DEFAULT 0 | Advance notice required |
| max_consecutive_days | INT | NULL | Max days in single request |
| color | VARCHAR(7) | DEFAULT '#3B82F6' | Calendar color code |
| is_active | BOOLEAN | DEFAULT true | Active status |
| sort_order | INT | DEFAULT 0 | Display order |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

#### leave_balances
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK | |
| user_id | BIGINT | FK → users, NOT NULL | Staff member |
| leave_type_id | BIGINT | FK → leave_types | Leave type |
| leave_year | YEAR | NOT NULL | Leave year (e.g., 2025) |
| entitled | DECIMAL(5,2) | DEFAULT 0 | Annual entitlement |
| carried_over | DECIMAL(5,2) | DEFAULT 0 | From previous year |
| accrued | DECIMAL(5,2) | DEFAULT 0 | Accrued to date |
| used | DECIMAL(5,2) | DEFAULT 0 | Already taken |
| pending | DECIMAL(5,2) | DEFAULT 0 | Pending approval |
| adjusted | DECIMAL(5,2) | DEFAULT 0 | Manual adjustments |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |
| **Unique** | | (user_id, leave_type_id, leave_year) | |

#### leave_requests
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK | |
| ulid | CHAR(26) | UNIQUE | Public identifier |
| user_id | BIGINT | FK → users | Requesting staff |
| leave_type_id | BIGINT | FK → leave_types | Leave type |
| leave_balance_id | BIGINT | FK → leave_balances | Associated balance |
| start_date | DATE | NOT NULL | Leave start |
| end_date | DATE | NOT NULL | Leave end |
| start_half_day | ENUM | NULL | 'am', 'pm' (if half-day) |
| end_half_day | ENUM | NULL | 'am', 'pm' (if half-day) |
| total_days | DECIMAL(5,2) | NOT NULL | Calculated leave days |
| reason | TEXT | NULL | Leave reason |
| status | ENUM | NOT NULL | 'draft','pending','approved','rejected','cancelled' |
| submitted_at | TIMESTAMP | NULL | When submitted |
| approved_by | BIGINT | FK → users, NULL | Approver |
| approved_at | TIMESTAMP | NULL | When approved/rejected |
| approver_comments | TEXT | NULL | Approval/rejection reason |
| cancelled_at | TIMESTAMP | NULL | If cancelled |
| cancelled_by | BIGINT | FK → users, NULL | Who cancelled |
| cancellation_reason | TEXT | NULL | Why cancelled |
| idempotency_key | VARCHAR(64) | UNIQUE, NULL | Prevent duplicates |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

#### leave_attachments
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK | |
| leave_request_id | BIGINT | FK → leave_requests | Parent request |
| file_name | VARCHAR(255) | NOT NULL | Original filename |
| file_path | VARCHAR(500) | NOT NULL | Storage path |
| file_size | INT | NOT NULL | Size in bytes |
| mime_type | VARCHAR(100) | NOT NULL | File type |
| uploaded_by | BIGINT | FK → users | Uploader |
| created_at | TIMESTAMP | | |

#### leave_balance_adjustments
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK | |
| leave_balance_id | BIGINT | FK → leave_balances | Balance affected |
| adjustment_type | ENUM | NOT NULL | 'credit', 'debit', 'correction' |
| days | DECIMAL(5,2) | NOT NULL | Days adjusted |
| reason | TEXT | NOT NULL | Justification |
| adjusted_by | BIGINT | FK → users | HR user |
| created_at | TIMESTAMP | | |

#### leave_settings
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK | |
| key | VARCHAR(100) | UNIQUE | Setting identifier |
| value | JSON | | Setting value |
| description | TEXT | | What it controls |
| updated_by | BIGINT | FK → users | Last modifier |
| updated_at | TIMESTAMP | | |

#### leave_policies
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK | |
| leave_type_id | BIGINT | FK → leave_types | Leave type |
| leave_year | YEAR | NOT NULL | Policy year |
| balance_mode | ENUM | NOT NULL | 'allocation', 'accrual' |
| accrual_rate | DECIMAL(5,2) | NULL | Monthly accrual (if accrual) |
| carry_over_mode | ENUM | NOT NULL | 'none', 'limited', 'full' |
| carry_over_limit | DECIMAL(5,2) | NULL | Max days to carry |
| carry_over_expiry_months | INT | NULL | Months to use carried |
| prorate_new_employees | BOOLEAN | DEFAULT true | Prorate for new hires |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |
| **Unique** | | (leave_type_id, leave_year) | |

#### public_holidays
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK | |
| name | VARCHAR(255) | NOT NULL | Holiday name |
| date | DATE | NOT NULL | Holiday date |
| is_recurring | BOOLEAN | DEFAULT false | Same date each year |
| description | TEXT | NULL | |
| is_active | BOOLEAN | DEFAULT true | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

### 7.3 Indexes

```sql
-- Performance indexes
CREATE INDEX leave_requests_user_status_idx ON leave_requests(user_id, status);
CREATE INDEX leave_requests_dates_idx ON leave_requests(start_date, end_date);
CREATE INDEX leave_requests_approver_idx ON leave_requests(approved_by, status);
CREATE INDEX leave_balances_user_year_idx ON leave_balances(user_id, leave_year);
CREATE INDEX leave_balances_type_year_idx ON leave_balances(leave_type_id, leave_year);
CREATE INDEX public_holidays_date_idx ON public_holidays(date);
```

---

## 8. API Design

### 8.1 RESTful Endpoints

#### Leave Types (Admin)
```
GET    /api/leave/types                      # List active leave types
GET    /api/leave/types/{id}                 # Get leave type details
POST   /api/leave/types                      # Create leave type (Admin)
PUT    /api/leave/types/{id}                 # Update leave type (Admin)
DELETE /api/leave/types/{id}                 # Deactivate leave type (Admin)
```

#### Leave Balances
```
GET    /api/leave/balances                   # My balances for current year
GET    /api/leave/balances/{userId}          # User's balances (Manager/HR)
GET    /api/leave/balances/team              # Team balances (Manager)
POST   /api/leave/balances/{id}/adjust       # Adjust balance (HR)
```

#### Leave Requests
```
GET    /api/leave/requests                   # My requests (paginated)
GET    /api/leave/requests/{ulid}            # Get request details
POST   /api/leave/requests                   # Submit request
POST   /api/leave/requests/{ulid}/cancel     # Cancel request
GET    /api/leave/requests/pending           # Pending for approval (Manager)
POST   /api/leave/requests/{ulid}/approve    # Approve request (Manager)
POST   /api/leave/requests/{ulid}/reject     # Reject request (Manager)
```

#### Calendar
```
GET    /api/leave/calendar                   # My leave calendar
GET    /api/leave/calendar/team              # Team calendar (Manager)
GET    /api/leave/holidays                   # Public holidays
```

#### Reports
```
GET    /api/leave/reports/individual         # My leave report
GET    /api/leave/reports/team               # Team report (Manager)
GET    /api/leave/reports/organization       # Org report (HR)
GET    /api/leave/reports/export             # Export report (Excel/PDF)
```

#### Settings (Admin)
```
GET    /api/leave/settings                   # Get all settings
PUT    /api/leave/settings                   # Update settings
```

---

## 9. User Interface Design

### 9.1 Navigation Structure

```
Leave Management (sidebar menu)
├── Dashboard (my summary)
├── My Leave
│   ├── Submit Request
│   ├── My Requests
│   ├── My Balances
│   └── Leave Calendar
├── Approvals (Manager)
│   ├── Pending Requests
│   └── Team Calendar
├── Team (Manager)
│   ├── Team Balances
│   └── Team Reports
├── Administration (HR)
│   ├── All Requests
│   ├── Balance Adjustments
│   ├── Leave Reports
│   └── Holidays
└── Settings (Admin)
    ├── Leave Types
    ├── Policies
    └── General Settings
```

### 9.2 Key Screens

#### 9.2.1 Leave Dashboard
- Available balances by leave type (cards)
- Upcoming leave (next 30 days)
- Pending requests status
- Quick "Request Leave" button
- Recent activity feed

#### 9.2.2 Submit Leave Request
- Leave type dropdown
- Date range picker (calendar)
- Half-day toggle (if single day)
- Calculated days display (real-time)
- Balance check indicator
- Reason textarea
- Attachment upload (if required)
- Submit button

#### 9.2.3 My Requests List
- Filterable by status, type, date range
- Status badges (color-coded)
- Actions: View, Cancel (if pending)
- Pagination

#### 9.2.4 Approval Queue (Manager)
- Pending requests list
- Employee info, leave type, dates, days
- Quick approve/reject buttons
- Expand for details and comments
- Bulk approval option

#### 9.2.5 Leave Calendar
- Month view with leave events
- Color-coded by leave type
- Holidays highlighted
- Team view toggle (for managers)

#### 9.2.6 Settings Page (Admin)
Tab-based layout (following LibrarySettingsController pattern):
- **Leave Types Tab**: CRUD for leave types
- **Policies Tab**: Balance mode, carry-over rules
- **Holidays Tab**: Public holidays management
- **General Tab**: Leave year, notifications, defaults

---

## 10. Integration Points

### 10.1 User System Integration
| Integration | Type | Details |
|-------------|------|---------|
| Authentication | SSO | Use existing `web` guard |
| User Data | Read | Name, department, position from User model |
| Reporting Structure | Read | `reporting_to` field for approver |
| Roles/Permissions | Extend | Add Leave roles to Spatie system |

### 10.2 Notification System
| Integration | Type | Details |
|-------------|------|---------|
| Email | Send | Via existing mail infrastructure |
| Database | Store | Notification center |
| Queue | Process | Via Horizon for async delivery |

### 10.3 Document System
| Integration | Type | Details |
|-------------|------|---------|
| Storage | Use | Existing storage disks |
| Validation | Apply | Existing file validation patterns |

---

## 11. Implementation Phases

### Phase 1: Foundation
**Goal:** Core leave types, requests, and basic approval

**Deliverables:**
- Database migrations for all tables
- Models with relationships
- Leave Type CRUD
- Leave Request submission
- Basic approval workflow
- Leave Admin/Manager roles & permissions

**User Stories:** S1, S2, S4, S5, M1, M2, M4, H1

### Phase 2: Balance Management
**Goal:** Complete balance tracking with policies

**Deliverables:**
- Balance allocation/accrual logic
- Balance calculation service
- Policy configuration
- Manual adjustments
- Carry-over processing

**User Stories:** S1, H2, H3, H4, H7

### Phase 3: Attachments & Validation
**Goal:** Document handling and business rules

**Deliverables:**
- Attachment upload/storage
- Validation rules per leave type
- Overlap detection
- Notice period enforcement
- Request cancellation

**User Stories:** S3, S6

### Phase 4: Calendar & Notifications
**Goal:** Visual calendar and notification system

**Deliverables:**
- Personal leave calendar
- Team calendar (manager view)
- Public holiday management
- Email notifications
- Reminder jobs

**User Stories:** S5, S7, S8, M3, H8

### Phase 5: Reporting & Polish
**Goal:** Reports, exports, and refinements

**Deliverables:**
- Individual reports
- Team reports
- Organization reports
- Excel/PDF exports
- Dashboard analytics
- Performance optimization

**User Stories:** M5, M6, H5, H6

---

## 12. Technical Architecture

### 12.1 Directory Structure

```
app/
├── DataTransferObjects/
│   └── Leave/
│       ├── CreateLeaveRequestDTO.php
│       ├── ApproveLeaveRequestDTO.php
│       ├── AdjustBalanceDTO.php
│       └── LeaveReportDTO.php
├── Events/
│   └── Leave/
│       ├── LeaveRequestSubmitted.php
│       ├── LeaveRequestApproved.php
│       ├── LeaveRequestRejected.php
│       └── LeaveBalanceAdjusted.php
├── Http/
│   ├── Controllers/
│   │   └── Leave/
│   │       ├── LeaveTypeController.php
│   │       ├── LeaveRequestController.php
│   │       ├── LeaveBalanceController.php
│   │       ├── LeaveApprovalController.php
│   │       ├── LeaveCalendarController.php
│   │       ├── LeaveReportController.php
│   │       ├── LeaveSettingsController.php
│   │       └── PublicHolidayController.php
│   └── Requests/
│       └── Leave/
│           ├── StoreLeaveRequestRequest.php
│           ├── ApproveLeaveRequest.php
│           ├── AdjustBalanceRequest.php
│           └── StoreLeaveTypeRequest.php
├── Jobs/
│   └── Leave/
│       ├── SendLeaveReminderJob.php
│       ├── ProcessLeaveAccrualJob.php
│       └── ProcessCarryOverJob.php
├── Listeners/
│   └── Leave/
│       ├── SendSubmissionNotification.php
│       ├── SendApprovalNotification.php
│       ├── SendRejectionNotification.php
│       └── UpdateLeaveBalance.php
├── Models/
│   └── Leave/
│       ├── LeaveType.php
│       ├── LeaveBalance.php
│       ├── LeaveRequest.php
│       ├── LeaveAttachment.php
│       ├── LeaveBalanceAdjustment.php
│       ├── LeavePolicy.php
│       ├── LeaveSetting.php
│       └── PublicHoliday.php
├── Notifications/
│   └── Leave/
│       ├── LeaveRequestSubmittedNotification.php
│       ├── LeaveRequestApprovedNotification.php
│       ├── LeaveRequestRejectedNotification.php
│       └── LeaveReminderNotification.php
├── Policies/
│   └── Leave/
│       ├── LeaveTypePolicy.php
│       ├── LeaveRequestPolicy.php
│       └── LeaveBalancePolicy.php
└── Services/
    └── Leave/
        ├── LeaveTypeService.php
        ├── LeaveRequestService.php
        ├── LeaveBalanceService.php
        ├── LeaveApprovalService.php
        ├── LeaveCalculationService.php
        ├── LeaveReportService.php
        └── LeaveNotificationService.php

database/migrations/
├── 2025_01_01_000001_create_leave_types_table.php
├── 2025_01_01_000002_create_leave_balances_table.php
├── 2025_01_01_000003_create_leave_requests_table.php
├── 2025_01_01_000004_create_leave_attachments_table.php
├── 2025_01_01_000005_create_leave_balance_adjustments_table.php
├── 2025_01_01_000006_create_leave_policies_table.php
├── 2025_01_01_000007_create_leave_settings_table.php
├── 2025_01_01_000008_create_public_holidays_table.php
└── 2025_01_01_000009_add_leave_roles_permissions.php

resources/views/leave/
├── dashboard.blade.php
├── requests/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── show.blade.php
├── balances/
│   ├── index.blade.php
│   └── adjustments.blade.php
├── approvals/
│   ├── index.blade.php
│   └── show.blade.php
├── calendar/
│   ├── personal.blade.php
│   └── team.blade.php
├── reports/
│   ├── individual.blade.php
│   ├── team.blade.php
│   └── organization.blade.php
└── settings/
    └── index.blade.php (tabbed)

routes/
└── leave/
    └── leave.php
```

### 12.2 Permissions Structure

```php
// Roles
'Leave Admin'    // Full access to all leave functions
'Leave Manager'  // Approve team requests, view team reports
'Leave View'     // View own leave only

// Permissions
'leave.types.view'
'leave.types.manage'
'leave.requests.create'
'leave.requests.view_own'
'leave.requests.view_team'
'leave.requests.view_all'
'leave.requests.approve'
'leave.balances.view_own'
'leave.balances.view_team'
'leave.balances.view_all'
'leave.balances.adjust'
'leave.reports.view_own'
'leave.reports.view_team'
'leave.reports.view_all'
'leave.settings.view'
'leave.settings.manage'
'leave.holidays.manage'
```

### 12.3 Service Layer Pattern

```php
final class LeaveRequestService {
    public function __construct(
        private readonly LeaveBalanceService $balanceService,
        private readonly LeaveCalculationService $calcService,
    ){}

    public function submit(CreateLeaveRequestDTO $dto): LeaveRequest{
        // 1. Idempotency check
        if ($dto->idempotencyKey && $existing = $this->findByIdempotencyKey($dto->idempotencyKey)) {
            return $existing;
        }

        return DB::transaction(function () use ($dto) {
            // 2. Lock balance row
            $balance = LeaveBalance::where('user_id', $dto->userId)
                ->where('leave_type_id', $dto->leaveTypeId)
                ->where('leave_year', $dto->leaveYear)
                ->lockForUpdate()
                ->firstOrFail();

            // 3. Calculate days
            $days = $this->calcService->calculateLeaveDays(
                $dto->startDate,
                $dto->endDate,
                $dto->startHalfDay,
                $dto->endHalfDay
            );

            // 4. Validate balance
            if (!$balance->canDeduct($days)) {
                throw new InsufficientBalanceException($balance, $days);
            }

            // 5. Check overlaps
            $this->validateNoOverlap($dto->userId, $dto->startDate, $dto->endDate);

            // 6. Create request
            $request = LeaveRequest::create([
                'user_id' => $dto->userId,
                'leave_type_id' => $dto->leaveTypeId,
                'leave_balance_id' => $balance->id,
                'start_date' => $dto->startDate,
                'end_date' => $dto->endDate,
                'total_days' => $days,
                'reason' => $dto->reason,
                'status' => 'pending',
                'submitted_at' => now(),
                'idempotency_key' => $dto->idempotencyKey,
            ]);

            // 7. Update pending balance
            $balance->increment('pending', $days);

            // 8. Fire event
            event(new LeaveRequestSubmitted($request));

            return $request;
        });
    }
}
```

---

## 13. Configurable Settings

Following the `LibrarySetting` pattern, the following settings will be configurable:

| Key | Default | Description |
|-----|---------|-------------|
| `leave_year_start_month` | `1` (January) | Month when leave year starts |
| `default_balance_mode` | `'allocation'` | Default: allocation or accrual |
| `weekend_days` | `[0, 6]` | Weekend days (0=Sunday, 6=Saturday) |
| `allow_past_date_requests` | `false` | Allow requesting past dates |
| `past_date_buffer_days` | `0` | Days in past allowed |
| `default_carry_over_mode` | `'limited'` | Default carry-over policy |
| `default_carry_over_limit` | `5` | Default max carry-over days |
| `carry_over_expiry_months` | `3` | Months to use carried-over leave |
| `send_reminder_days_before` | `1` | Days before leave to send reminder |
| `escalate_pending_after_hours` | `48` | Hours before escalating to HR |
| `require_reason` | `true` | Require reason for all requests |
| `prorate_for_new_employees` | `true` | Prorate entitlement for new hires |

---

## 14. Risks & Mitigations

| Risk | Impact | Probability | Mitigation |
|------|--------|-------------|------------|
| Missing reporting_to data | High | Medium | Validate data, fallback to HR |
| Complex carry-over calculations | Medium | Medium | Thorough testing, audit logs |
| Balance synchronization issues | High | Low | Transactions, locks, reconciliation job |
| User adoption resistance | Medium | Low | Training, intuitive UI |
| Backdated requests abuse | Medium | Medium | Configurable restrictions, audit trail |

---

## 15. Reference Files

Files to review before implementation:
- `/app/Models/User.php` - User model with roles, reporting_to
- `/app/Models/Documents/DocumentApprovalRequest.php` - Approval workflow pattern
- `/app/Services/Documents/DocumentWorkflowService.php` - Service pattern
- `/app/Http/Controllers/Library/LibrarySettingsController.php` - Settings pattern
- `/app/Models/Library/LibrarySetting.php` - Settings model pattern
- `/app/Notifications/Library/BookDueReminderNotification.php` - Notification pattern
- `/config/auth.php` - Authentication guards
- `/CLAUDE.md` - Project coding standards

---

## 16. Acceptance Checklist

- [ ] Controllers thin; complex logic in services
- [ ] All writes in transactions where needed
- [ ] Concurrency controlled (locks for balance updates)
- [ ] Idempotency for request submission
- [ ] Policies & FormRequests in place; 403/422 covered
- [ ] DB constraints enforce invariants
- [ ] No secrets logged; `.env.example` updated
- [ ] Feature + unit + concurrency tests pass
- [ ] Structured logs with trace_id
- [ ] README documents ops (migrate, queue, workers)

---

## 17. Production Setup

### 17.1 Scheduled Jobs

The leave management system requires the following scheduled jobs to be configured. These are already registered in `app/Console/Kernel.php`.

| Job | Queue | Schedule | Description |
|-----|-------|----------|-------------|
| `ProcessLeaveAccrualJob` | `leave-accrual` | Monthly on 1st at 01:00 | Processes monthly leave accrual for employees on accrual-based policies |
| `ProcessCarryOverJob` | `leave-carryover` | Yearly on Jan 1st at 00:15 | Processes year-end carry-over of unused leave days to the new leave year |
| `SendLeaveReminderJob` | `leave-notifications` | Daily at 07:00 | Sends reminders to employees about upcoming approved leave |

### 17.2 Queue Configuration

Each leave job uses a dedicated queue to prevent congestion with other system jobs. Configure your queue workers to process these queues.

**Required Queues:**
```
leave-accrual        # Monthly accrual processing (low frequency, long-running)
leave-carryover      # Year-end carry-over (very low frequency, long-running)
leave-notifications  # Daily reminders (high frequency, short-running)
```

### 17.3 Supervisor Configuration

Add the following to your Supervisor configuration (`/etc/supervisor/conf.d/laravel-leave.conf`):

```ini
[program:leave-accrual-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --queue=leave-accrual --sleep=60 --tries=3 --timeout=900
autostart=true
autorestart=true
numprocs=1
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/supervisor/leave-accrual.log

[program:leave-carryover-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --queue=leave-carryover --sleep=60 --tries=3 --timeout=1200
autostart=true
autorestart=true
numprocs=1
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/supervisor/leave-carryover.log

[program:leave-notifications-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --queue=leave-notifications --sleep=10 --tries=3 --timeout=300
autostart=true
autorestart=true
numprocs=2
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/supervisor/leave-notifications.log
```

### 17.4 Horizon Configuration (Alternative)

If using Laravel Horizon, add the following to `config/horizon.php`:

```php
'environments' => [
    'production' => [
        // ... existing supervisors ...

        'leave-accrual' => [
            'connection' => 'redis',
            'queue' => ['leave-accrual'],
            'balance' => 'simple',
            'processes' => 1,
            'tries' => 3,
            'timeout' => 900,
        ],
        'leave-carryover' => [
            'connection' => 'redis',
            'queue' => ['leave-carryover'],
            'balance' => 'simple',
            'processes' => 1,
            'tries' => 3,
            'timeout' => 1200,
        ],
        'leave-notifications' => [
            'connection' => 'redis',
            'queue' => ['leave-notifications'],
            'balance' => 'auto',
            'processes' => 2,
            'tries' => 3,
            'timeout' => 300,
        ],
    ],
],
```

### 17.5 Cron Setup

Ensure Laravel's scheduler is running via cron:

```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

### 17.6 Job Details

#### ProcessLeaveAccrualJob
- **Queue:** `leave-accrual`
- **Schedule:** `->monthlyOn(1, '01:00')` (1st of each month at 1:00 AM)
- **Timeout:** 600 seconds (10 minutes)
- **Retries:** 3
- **Purpose:** For leave types using accrual mode, this job calculates and credits the monthly accrual amount to each eligible employee's balance.

#### ProcessCarryOverJob
- **Queue:** `leave-carryover`
- **Schedule:** `->yearlyOn(1, 1, '00:15')` (January 1st at 12:15 AM)
- **Timeout:** 900 seconds (15 minutes)
- **Retries:** 3
- **Purpose:** At the start of each leave year, this job calculates unused leave balances and carries over the allowed amount (based on policy limits) to the new year.

#### SendLeaveReminderJob
- **Queue:** `leave-notifications`
- **Schedule:** `->dailyAt('07:00')` (Every day at 7:00 AM)
- **Timeout:** 300 seconds (5 minutes)
- **Retries:** 3
- **Purpose:** Sends email reminders to employees whose approved leave is about to start. The reminder period is configurable via `leave_reminder_days_before` setting.

### 17.7 Monitoring

Monitor queue health using:

```bash
# Check queue sizes
php artisan queue:monitor leave-accrual,leave-carryover,leave-notifications --max=100

# View failed jobs
php artisan queue:failed

# Retry failed leave jobs
php artisan queue:retry --queue=leave-accrual
php artisan queue:retry --queue=leave-carryover
php artisan queue:retry --queue=leave-notifications
```

---

## Appendix A: Glossary

| Term | Definition |
|------|------------|
| Leave Year | Annual period for leave entitlement (e.g., Jan-Dec or Apr-Mar) |
| Entitlement | Total leave days allocated per year |
| Accrual | Leave days earned progressively (e.g., monthly) |
| Carry-over | Unused leave transferred to next year |
| Balance | Available leave days (entitled + carried - used - pending) |
| HOD | Head of Department (typical approver) |

---

## Appendix B: Leave Calculation Examples

### Example 1: Annual Leave Request
- Staff member: John Doe
- Leave Type: Annual Leave
- Dates: 2025-01-15 to 2025-01-20
- Weekend days: Saturday (6), Sunday (0)
- Public holidays in range: None
- Calculation: 6 calendar days - 2 weekend days = **4 leave days**

### Example 2: Half-Day Request
- Dates: 2025-01-15 (PM half)
- Calculation: **0.5 leave days**

### Example 3: Balance Calculation
- Entitled: 21 days
- Carried over: 3 days
- Used: 5 days
- Pending: 2 days
- Available: 21 + 3 - 5 - 2 = **17 days**

### Example 4: Accrual Mode
- Annual entitlement: 24 days
- Monthly accrual: 24 ÷ 12 = 2 days/month
- After 6 months: 12 days accrued

---

## Appendix C: Detailed API Documentation

### C.1 Leave Request Endpoints

#### POST /api/leave/requests - Submit Leave Request

**Request Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
X-Idempotency-Key: {uuid}
```

**Request Body:**
```json
{
    "leave_type_id": 1,
    "start_date": "2025-02-15",
    "end_date": "2025-02-20",
    "start_half_day": null,
    "end_half_day": null,
    "reason": "Family vacation",
    "idempotency_key": "req-usr-123-2025-02-15-v1"
}
```

**Success Response (201 Created):**
```json
{
    "data": {
        "ulid": "01JFQX8K1M2N3P4Q5R6S7T8U9V",
        "user": {
            "id": 123,
            "name": "John Doe",
            "department": "Academic Affairs"
        },
        "leave_type": {
            "id": 1,
            "code": "ANN",
            "name": "Annual Leave"
        },
        "start_date": "2025-02-15",
        "end_date": "2025-02-20",
        "total_days": 4.0,
        "reason": "Family vacation",
        "status": "pending",
        "submitted_at": "2025-01-15T10:30:00Z",
        "balance_before": 17.0,
        "balance_after_approval": 13.0
    },
    "message": "Leave request submitted successfully"
}
```

**Validation Error (422 Unprocessable Entity):**
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "start_date": ["The start date must be a future date."],
        "leave_type_id": ["Insufficient balance. Available: 3 days, Requested: 4 days."]
    }
}
```

**Insufficient Balance Error (422):**
```json
{
    "message": "Insufficient leave balance",
    "errors": {
        "balance": ["You have 3 days available but requested 4 days."]
    },
    "data": {
        "available": 3.0,
        "requested": 4.0,
        "leave_type": "Annual Leave"
    }
}
```

#### POST /api/leave/requests/{ulid}/approve - Approve Request

**Request Body:**
```json
{
    "comments": "Approved. Enjoy your vacation!",
    "idempotency_key": "approve-01JFQX8K1M-v1"
}
```

**Success Response (200 OK):**
```json
{
    "data": {
        "ulid": "01JFQX8K1M2N3P4Q5R6S7T8U9V",
        "status": "approved",
        "approved_by": {
            "id": 456,
            "name": "Jane Smith"
        },
        "approved_at": "2025-01-15T14:30:00Z",
        "approver_comments": "Approved. Enjoy your vacation!"
    },
    "message": "Leave request approved successfully"
}
```

**Authorization Error (403 Forbidden):**
```json
{
    "message": "You are not authorized to approve this request.",
    "error": "unauthorized"
}
```

#### POST /api/leave/requests/{ulid}/reject - Reject Request

**Request Body:**
```json
{
    "comments": "Critical project deadline during this period. Please reschedule.",
    "idempotency_key": "reject-01JFQX8K1M-v1"
}
```

**Success Response (200 OK):**
```json
{
    "data": {
        "ulid": "01JFQX8K1M2N3P4Q5R6S7T8U9V",
        "status": "rejected",
        "approved_by": {
            "id": 456,
            "name": "Jane Smith"
        },
        "approved_at": "2025-01-15T14:30:00Z",
        "approver_comments": "Critical project deadline during this period. Please reschedule."
    },
    "message": "Leave request rejected"
}
```

### C.2 Leave Balance Endpoints

#### GET /api/leave/balances - Get My Balances

**Success Response (200 OK):**
```json
{
    "data": {
        "leave_year": 2025,
        "balances": [
            {
                "leave_type": {
                    "id": 1,
                    "code": "ANN",
                    "name": "Annual Leave",
                    "color": "#3B82F6"
                },
                "entitled": 21.0,
                "carried_over": 3.0,
                "accrued": 0.0,
                "used": 5.0,
                "pending": 2.0,
                "adjusted": 0.0,
                "available": 17.0
            },
            {
                "leave_type": {
                    "id": 2,
                    "code": "SICK",
                    "name": "Sick Leave",
                    "color": "#EF4444"
                },
                "entitled": 12.0,
                "carried_over": 0.0,
                "accrued": 0.0,
                "used": 2.0,
                "pending": 0.0,
                "adjusted": 0.0,
                "available": 10.0
            }
        ]
    }
}
```

#### POST /api/leave/balances/{id}/adjust - Adjust Balance (HR Only)

**Request Body:**
```json
{
    "adjustment_type": "credit",
    "days": 5.0,
    "reason": "Correction for service years - entitled to additional 5 days",
    "idempotency_key": "adj-bal-123-v1"
}
```

**Success Response (200 OK):**
```json
{
    "data": {
        "balance_id": 123,
        "adjustment": {
            "type": "credit",
            "days": 5.0,
            "reason": "Correction for service years - entitled to additional 5 days",
            "adjusted_by": {
                "id": 789,
                "name": "HR Admin"
            },
            "created_at": "2025-01-15T10:00:00Z"
        },
        "new_balance": {
            "entitled": 21.0,
            "adjusted": 5.0,
            "available": 22.0
        }
    },
    "message": "Balance adjusted successfully"
}
```

### C.3 Calendar Endpoints

#### GET /api/leave/calendar?year=2025&month=2

**Success Response (200 OK):**
```json
{
    "data": {
        "year": 2025,
        "month": 2,
        "events": [
            {
                "id": "01JFQX8K1M2N3P4Q5R6S7T8U9V",
                "title": "Annual Leave",
                "start": "2025-02-15",
                "end": "2025-02-20",
                "color": "#3B82F6",
                "status": "approved",
                "type": "leave"
            }
        ],
        "holidays": [
            {
                "id": 1,
                "name": "Independence Day",
                "date": "2025-02-30",
                "type": "holiday"
            }
        ]
    }
}
```

### C.4 Error Response Format

All errors follow RFC-7807 problem+json format:

```json
{
    "type": "https://api.college.edu/errors/validation",
    "title": "Validation Error",
    "status": 422,
    "detail": "The given data was invalid.",
    "instance": "/api/leave/requests",
    "trace_id": "550e8400-e29b-41d4-a716-446655440000",
    "errors": {
        "field_name": ["Error message 1", "Error message 2"]
    }
}
```

---

## Appendix D: UI Design Specifications

### D.1 Design System (Matching staff/index & students/edit)

The Leave Management UI must follow the existing design patterns:

#### Container Patterns

**Index/List Pages (like staff/index):**
```css
.leave-container {
    background: white;
    border-radius: 3px;
    padding: 0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.leave-header {
    background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
    color: white;
    padding: 28px;
    border-radius: 3px 3px 0 0;
}

.leave-body {
    padding: 24px;
}
```

**Form/Edit Pages (like students/edit):**
```css
.form-container {
    background: white;
    border-radius: 3px;
    padding: 32px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    padding-bottom: 12px;
    border-bottom: 1px solid #e5e7eb;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}
```

#### Status Badges
```css
.status-pending { background: #fef3c7; color: #92400e; }
.status-approved { background: #d1fae5; color: #065f46; }
.status-rejected { background: #fee2e2; color: #991b1b; }
.status-cancelled { background: #f3f4f6; color: #4b5563; }
.status-draft { background: #e0e7ff; color: #3730a3; }
```

#### Tab Navigation (for settings/edit forms)
```css
.form-tabs .nav-tabs .nav-link {
    border: none;
    border-bottom: 3px solid transparent;
    background: none;
    color: #6b7280;
    font-weight: 500;
    padding: 16px 24px;
}

.form-tabs .nav-tabs .nav-link.active {
    color: #3b82f6;
    border-bottom-color: #3b82f6;
}
```

### D.2 Key Screen Layouts

#### D.2.1 Leave Dashboard (My Leave)
```
┌─────────────────────────────────────────────────────────────────┐
│ [Header: Gradient Background]                                    │
│  My Leave Dashboard              [Quick Stats: Available Days]   │
│  Manage your leave requests       Annual: 17  Sick: 10  Study: 5│
├─────────────────────────────────────────────────────────────────┤
│ [Balance Cards - 3 columns]                                      │
│ ┌──────────────┐ ┌──────────────┐ ┌──────────────┐              │
│ │ Annual Leave │ │ Sick Leave   │ │ Study Leave  │              │
│ │ 17 days      │ │ 10 days      │ │ 5 days       │              │
│ │ [████████░░] │ │ [██████████] │ │ [██████████] │              │
│ │ 17/21 avail  │ │ 10/12 avail  │ │ 5/10 avail   │              │
│ └──────────────┘ └──────────────┘ └──────────────┘              │
├─────────────────────────────────────────────────────────────────┤
│ [Actions]                                                        │
│ [+ Request Leave]  [View Calendar]  [View History]               │
├─────────────────────────────────────────────────────────────────┤
│ [Recent Requests Table]                                          │
│ Type          | Dates           | Days | Status    | Actions    │
│ Annual Leave  | 15-20 Feb 2025  | 4    | Pending   | [Cancel]   │
│ Sick Leave    | 10 Jan 2025     | 1    | Approved  | [View]     │
└─────────────────────────────────────────────────────────────────┘
```

#### D.2.2 Submit Leave Request Form
```
┌─────────────────────────────────────────────────────────────────┐
│ [Page Header]                                                    │
│  Submit Leave Request                            [Draft] [Submit]│
├─────────────────────────────────────────────────────────────────┤
│ [Form Grid - 3 columns]                                          │
│ ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐     │
│ │ Leave Type *    │ │ Start Date *    │ │ End Date *      │     │
│ │ [Dropdown ▼]    │ │ [Date Picker]   │ │ [Date Picker]   │     │
│ └─────────────────┘ └─────────────────┘ └─────────────────┘     │
│ ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐     │
│ │ Half Day Start  │ │ Half Day End    │ │ Total Days      │     │
│ │ [ ] AM  [ ] PM  │ │ [ ] AM  [ ] PM  │ │ 4.0 days        │     │
│ └─────────────────┘ └─────────────────┘ └─────────────────┘     │
├─────────────────────────────────────────────────────────────────┤
│ [Balance Info Card]                                              │
│ ┌───────────────────────────────────────────────────────────┐   │
│ │ Annual Leave Balance: 17 days available                    │   │
│ │ After this request: 13 days remaining                      │   │
│ └───────────────────────────────────────────────────────────┘   │
├─────────────────────────────────────────────────────────────────┤
│ [Full Width]                                                     │
│ Reason *                                                         │
│ ┌───────────────────────────────────────────────────────────┐   │
│ │ [Textarea - min 100px height]                              │   │
│ └───────────────────────────────────────────────────────────┘   │
├─────────────────────────────────────────────────────────────────┤
│ [Attachment Section - if required]                               │
│ Supporting Document                                              │
│ ┌───────────────────────────────────────────────────────────┐   │
│ │ [Drop files here or click to upload]                       │   │
│ │ Max 10MB. Allowed: PDF, JPG, PNG                           │   │
│ └───────────────────────────────────────────────────────────┘   │
├─────────────────────────────────────────────────────────────────┤
│ [Form Actions]                                                   │
│ [← Back to Dashboard]                    [Save Draft] [Submit]   │
└─────────────────────────────────────────────────────────────────┘
```

#### D.2.3 Approval Queue (Manager View)
```
┌─────────────────────────────────────────────────────────────────┐
│ [Header: Gradient Background]                                    │
│  Leave Approvals                    Pending: 5  Today: 2         │
│  Review and approve team requests                                │
├─────────────────────────────────────────────────────────────────┤
│ [Controls Row]                                                   │
│ [🔍 Search staff...]  [Status ▼]  [Type ▼]  [Reset]             │
├─────────────────────────────────────────────────────────────────┤
│ [Requests Table]                                                 │
│ # | Staff          | Type       | Dates       | Days | Actions  │
│ 1 | John Doe       | Annual     | 15-20 Feb   | 4    | [✓] [✗] │
│   | Academic Dept  |            |             |      | [View]   │
│ 2 | Jane Smith     | Sick       | 22 Feb      | 1    | [✓] [✗] │
│   | Admin Office   | (cert att) |             |      | [View]   │
├─────────────────────────────────────────────────────────────────┤
│ [Pagination]                                                     │
│ Showing 1 to 5 of 5 requests        [◀ Prev] [1] [2] [Next ▶]   │
└─────────────────────────────────────────────────────────────────┘
```

#### D.2.4 Settings Page (Tabbed Layout)
```
┌─────────────────────────────────────────────────────────────────┐
│ [Page Header]                                                    │
│  Leave Management Settings                                       │
├─────────────────────────────────────────────────────────────────┤
│ [Tabs]                                                           │
│ [Leave Types] [Policies] [Holidays] [General]                    │
├─────────────────────────────────────────────────────────────────┤
│ [Tab Content: Leave Types]                                       │
│ ┌───────────────────────────────────────────────────────────┐   │
│ │ [+ Add Leave Type]                                         │   │
│ │                                                            │   │
│ │ Code  | Name           | Days | Requires Doc | Status     │   │
│ │ ANN   | Annual Leave   | 21   | No           | Active     │   │
│ │ SICK  | Sick Leave     | 12   | Yes (>2d)    | Active     │   │
│ │ COMP  | Compassionate  | 5    | Yes          | Active     │   │
│ │ [Edit] [Deactivate]                                        │   │
│ └───────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
```

### D.3 Component Reference

| Component | Reference File | Usage |
|-----------|----------------|-------|
| List container | `staff/index.blade.php` | Leave requests list, approval queue |
| Form container | `students/edit.blade.php` | Request form, settings |
| Gradient header | `staff/index.blade.php` | Dashboard, list headers |
| Tab navigation | `students/edit.blade.php` | Settings page, request details |
| Data table | `staff/index.blade.php` | All list views |
| Action buttons | `staff/index.blade.php` | Row actions, form actions |
| Status badges | `students/edit.blade.php` | Request status display |
| Form grid | `students/edit.blade.php` | Multi-column forms |
| Modal dialogs | `staff/index.blade.php` | Confirmations, quick actions |

---

## Appendix E: Testing Specifications

### E.1 Unit Tests

#### LeaveCalculationService Tests
```php
/** @test */
public function it_calculates_leave_days_excluding_weekends(){
    // Given: Request from Monday 2025-02-17 to Friday 2025-02-21
    // When: Calculate leave days
    // Then: Should return 5 days (no weekends in range)
}

/** @test */
public function it_calculates_leave_days_excluding_weekends_and_holidays(){
    // Given: Request includes a public holiday
    // When: Calculate leave days
    // Then: Should exclude the holiday from count
}

/** @test */
public function it_handles_half_day_requests_correctly(){
    // Given: Single day request with start_half_day = 'pm'
    // When: Calculate leave days
    // Then: Should return 0.5 days
}

/** @test */
public function it_handles_half_day_start_and_end(){
    // Given: Request from Mon PM to Fri AM
    // When: Calculate leave days
    // Then: Should return 4 days (0.5 + 3 + 0.5)
}
```

#### LeaveBalanceService Tests
```php
/** @test */
public function it_calculates_available_balance_correctly(){
    // Given: entitled=21, carried=3, used=5, pending=2, adjusted=0
    // When: Get available balance
    // Then: Should return 17
}

/** @test */
public function it_prevents_overdraw_when_not_allowed(){
    // Given: available=3, requested=5, allow_negative=false
    // When: Attempt to deduct
    // Then: Should throw InsufficientBalanceException
}

/** @test */
public function it_allows_overdraw_for_unpaid_leave(){
    // Given: Unpaid leave type with allow_negative=true
    // When: Request exceeds balance
    // Then: Should allow the request
}

/** @test */
public function it_accrues_balance_monthly(){
    // Given: entitlement=24, accrual_rate=2, current_month=June
    // When: Calculate accrued balance
    // Then: Should return 12 days accrued
}

/** @test */
public function it_enforces_carry_over_limits(){
    // Given: unused=10, carry_over_limit=5
    // When: Process year-end carry over
    // Then: Should carry only 5 days
}
```

### E.2 Feature Tests

#### Leave Request Submission Tests
```php
/** @test */
public function staff_can_submit_leave_request(){
    // Given: Authenticated staff with balance
    // When: POST /leave/requests with valid data
    // Then: Request created, status=pending, balance.pending updated
}

/** @test */
public function staff_cannot_submit_overlapping_requests(){
    // Given: Existing approved request for Feb 15-20
    // When: Submit request for Feb 18-25
    // Then: Should return 422 with overlap error
}

/** @test */
public function staff_cannot_submit_without_sufficient_balance(){
    // Given: 3 days available, requesting 5 days
    // When: Submit request
    // Then: Should return 422 with balance error
}

/** @test */
public function staff_cannot_submit_past_date_requests(){
    // Given: allow_past_date_requests=false
    // When: Submit request for yesterday
    // Then: Should return 422 with date error
}

/** @test */
public function attachment_required_for_sick_leave_over_2_days(){
    // Given: Sick leave request for 3 days, no attachment
    // When: Submit request
    // Then: Should return 422 requiring attachment
}
```

#### Leave Approval Tests
```php
/** @test */
public function manager_can_approve_team_member_request(){
    // Given: Manager with staff reporting_to them
    // When: POST /leave/requests/{id}/approve
    // Then: Status=approved, balance.used updated, pending reduced
}

/** @test */
public function manager_cannot_approve_non_team_request(){
    // Given: Request from staff in different department
    // When: Attempt to approve
    // Then: Should return 403 Forbidden
}

/** @test */
public function manager_cannot_approve_own_request(){
    // Given: Manager submits own leave request
    // When: Attempt to self-approve
    // Then: Should return 403 Forbidden
}

/** @test */
public function hr_can_approve_any_request(){
    // Given: HR Admin role
    // When: Approve request from any department
    // Then: Should succeed
}

/** @test */
public function rejection_restores_pending_balance(){
    // Given: Pending request with balance.pending=5
    // When: Reject the request
    // Then: balance.pending should decrease by 5
}
```

### E.3 Concurrency Tests

```php
/** @test */
public function concurrent_submissions_handled_via_idempotency(){
    // Given: Same idempotency key submitted twice
    // When: Process both requests
    // Then: Only one request created, second returns same result
}

/** @test */
public function concurrent_approvals_prevented_via_locking(){
    // Given: Two managers attempt to approve same request
    // When: Process concurrently
    // Then: Only one approval succeeds, other gets error
}

/** @test */
public function balance_updates_are_atomic(){
    // Given: Two requests submitted simultaneously
    // When: Both attempt to deduct from same balance
    // Then: Total deducted equals sum of both, no race condition
}

/** @test */
public function year_end_carry_over_locks_balances(){
    // Given: Carry-over job running
    // When: User submits request during carry-over
    // Then: Request waits for lock, then processes correctly
}
```

### E.4 Authorization Tests

```php
/** @test */
public function guest_cannot_access_leave_endpoints(){
    // Given: Unauthenticated user
    // When: Access any leave endpoint
    // Then: Return 401 Unauthorized
}

/** @test */
public function staff_cannot_view_others_requests(){
    // Given: Staff user
    // When: GET /leave/requests/{other_user_request}
    // Then: Return 403 Forbidden
}

/** @test */
public function staff_cannot_access_admin_settings(){
    // Given: Regular staff user
    // When: Access /leave/settings
    // Then: Return 403 Forbidden
}

/** @test */
public function hr_admin_can_adjust_any_balance(){
    // Given: HR Admin role
    // When: POST /leave/balances/{any}/adjust
    // Then: Should succeed
}

/** @test */
public function staff_cannot_adjust_own_balance(){
    // Given: Staff user
    // When: Attempt to adjust own balance
    // Then: Return 403 Forbidden
}
```

### E.5 Notification Tests

```php
/** @test */
public function manager_notified_on_new_request(){
    // Given: Staff submits leave request
    // When: Request created
    // Then: Manager receives notification (email + database)
}

/** @test */
public function staff_notified_on_approval(){
    // Given: Manager approves request
    // When: Approval processed
    // Then: Staff receives approval notification
}

/** @test */
public function staff_notified_on_rejection(){
    // Given: Manager rejects request
    // When: Rejection processed
    // Then: Staff receives rejection notification with reason
}

/** @test */
public function reminder_sent_before_leave_starts(){
    // Given: Approved leave starting tomorrow
    // When: Reminder job runs
    // Then: Staff receives reminder notification
}
```

### E.6 Report Tests

```php
/** @test */
public function leave_utilization_report_calculates_correctly(){
    // Given: Department with 5 staff, various leave taken
    // When: Generate utilization report
    // Then: Percentages and totals are accurate
}

/** @test */
public function leave_report_exports_to_excel(){
    // Given: Leave data for date range
    // When: Export report
    // Then: Valid Excel file with correct data
}

/** @test */
public function manager_sees_only_team_in_reports(){
    // Given: Manager with 3 direct reports
    // When: Generate team report
    // Then: Only shows those 3 staff members
}
```

### E.7 Test Coverage Requirements

| Category | Minimum Coverage |
|----------|------------------|
| Models | 90% |
| Services | 95% |
| Controllers | 85% |
| Policies | 100% |
| FormRequests | 100% |
| Jobs | 90% |
| Overall | 85% |

### E.8 Test Data Factories

```php
// LeaveTypeFactory
LeaveType::factory()->annual()->create();      // Standard annual leave
LeaveType::factory()->sick()->create();        // Sick leave with attachment required
LeaveType::factory()->unpaid()->create();      // Unpaid, no balance tracking

// LeaveRequestFactory
LeaveRequest::factory()->pending()->create();  // Pending approval
LeaveRequest::factory()->approved()->create(); // Already approved
LeaveRequest::factory()->rejected()->create(); // Rejected with reason

// LeaveBalanceFactory
LeaveBalance::factory()->full()->create();     // Full entitlement
LeaveBalance::factory()->low()->create();      // Only 2 days remaining
LeaveBalance::factory()->exhausted()->create(); // 0 days available
```
