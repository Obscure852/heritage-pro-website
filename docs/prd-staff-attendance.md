# PRD: Staff Attendance Module

**Module:** `attendance`
**Version:** 1.0
**Date:** 2026-04-22
**Status:** Draft

---

## 1. Overview

The Staff Attendance module adds time-and-attendance tracking to Heritage Pro CRM. It enables staff to clock in/out (manually or via biometric devices), gives managers a two-week rolling attendance grid for their teams, and gives administrators full control over attendance codes, shift schedules, and public holidays. The module integrates with the existing CRM user, department, and permission infrastructure.

> **Note:** Leave management (quotas, accrual, balances, carry-over) is planned as a **separate module** that will integrate with attendance codes categorised as `leave`. This PRD covers attendance tracking only.

---

## 2. Goals

| # | Goal | Success Metric |
|---|------|----------------|
| G1 | Eliminate paper-based attendance registers | 100% of daily attendance captured digitally within 30 days of rollout |
| G2 | Support biometric clock-in/out alongside manual entry | At least one biometric integration active before go-live |
| G3 | Give managers real-time visibility into team attendance | Managers can view a 2-week attendance grid filtered by department |
| G4 | Configurable attendance codes and holidays | Admin can CRUD codes and holidays without developer involvement |
| G5 | Accurate overtime and late-arrival tracking | System auto-flags late arrivals and overtime based on shift rules |

---

## 3. User Roles & Permissions

Follows the existing `heritage_crm.modules` permission pattern (`view`, `edit`, `admin`).

| Capability | Admin | Manager | Finance | Rep |
|---|---|---|---|---|
| View own attendance | Yes | Yes | Yes | Yes |
| Clock in / Clock out | Yes | Yes | Yes | Yes |
| View team attendance grid | Yes | Yes (own dept) | Yes (read-only) | No |
| Override / correct a clock record | Yes | Yes (own dept) | No | No |
| Approve corrections | Yes | Yes (own dept) | No | No |
| Manage attendance codes | Yes | No | No | No |
| Manage holidays | Yes | No | No | No |
| Manage shift schedules | Yes | No | No | No |
| Manage biometric devices | Yes | No | No | No |
| Export attendance reports | Yes | Yes | Yes | No |

### Module Config Entry (`heritage_crm.modules.attendance`)

```php
'attendance' => [
    'label' => 'Attendance',
    'caption' => 'Clock-in and schedules',
    'icon' => 'bx bx-fingerprint',
    'route' => 'crm.attendance.my',
    'match' => ['crm.attendance.*'],
    'default_permissions' => [
        'admin' => 'admin',
        'finance' => 'view',
        'manager' => 'edit',
        'rep' => 'view',
    ],
    'children' => [
        ['label' => 'My Attendance', 'route' => 'crm.attendance.my', 'match' => ['crm.attendance.my']],
        ['label' => 'Team Grid', 'route' => 'crm.attendance.grid', 'match' => ['crm.attendance.grid*']],
        ['label' => 'Reports', 'route' => 'crm.attendance.reports', 'match' => ['crm.attendance.reports*']],
    ],
],
```

---

## 4. Feature Specifications

### 4.1 Clock In / Clock Out

#### 4.1.1 Manual Clock

- A prominent **Clock In** / **Clock Out** button appears in the CRM top navbar (next to the presence indicator) for all authenticated users.
- Clicking **Clock In** records `clocked_in_at = now()` and sets `source = 'manual'`.
- Clicking **Clock Out** closes the open record with `clocked_out_at = now()`.
- If the user has an open (un-clocked-out) record from a previous day, the system auto-closes it at `23:59:59` of that day and flags it as `auto_closed = true` with a warning badge.
- Users can optionally attach a **note** when clocking in or out (e.g., "Working from home", "Left early - appointment").
- Guard rails:
  - Cannot clock in if already clocked in (button shows "Clock Out" state).
  - Cannot clock in outside the allowed window if the admin has configured `earliest_clock_in` / `latest_clock_in` on the shift.
  - Duplicate clock-in within 60 seconds is rejected (debounce).

#### 4.1.2 Biometric Clock

- Biometric devices push clock events to a dedicated API endpoint: `POST /api/crm/attendance/biometric-event`.
- Authentication via device API token (stored in `crm_attendance_devices` table, scoped to Laravel Sanctum ability `attendance:biometric-push`).
- Payload:

```json
{
    "device_id": "BIO-FRONT-01",
    "employee_identifier": "EMP-0042",
    "event_type": "clock_in|clock_out",
    "captured_at": "2026-04-22T07:58:12+02:00",
    "verification_method": "fingerprint|face|card|pin",
    "confidence_score": 0.97
}
```

- The `employee_identifier` maps to `users.personal_payroll_number` (existing field).
- Events are queued (`crm-attendance` queue) and processed by `ProcessBiometricEventJob`:
  1. Resolve user by `personal_payroll_number`.
  2. If `clock_in`: create a new attendance record with `source = 'biometric'`.
  3. If `clock_out`: close the open record.
  4. If user not found or duplicate event: log to `crm_attendance_device_logs` with `status = 'unmatched'|'duplicate'`.
- Minimum confidence threshold is configurable per device (`min_confidence`). Events below threshold are logged but not processed.

#### 4.1.3 Biometric Device Management

Admin UI under **Settings > Attendance > Devices**:

| Field | Type | Required |
|---|---|---|
| `name` | string | Yes |
| `device_identifier` | string (unique) | Yes |
| `location` | string | No |
| `api_token` | auto-generated | - |
| `min_confidence` | decimal (0.00-1.00, default 0.80) | Yes |
| `is_active` | boolean | Yes |
| `last_heartbeat_at` | datetime (updated via API ping) | - |

Admin can regenerate the API token and deactivate a device (rejects all future events from it).

---

### 4.2 Daily Attendance Grid (2-Week View)

The primary attendance view is a **two-week rolling grid** showing all staff attendance at a glance.

#### Layout

```
                 Mon 14  Tue 15  Wed 16  Thu 17  Fri 18  Sat 19  Sun 20  Mon 21  Tue 22  ...  Sun 27
 ┌──────────────┬───────┬───────┬───────┬───────┬───────┬───────┬───────┬───────┬───────┬────┬───────┐
 │ Jane Doe     │  P    │  P    │  A    │  P    │  P    │  --   │  --   │  P    │  ?    │    │       │
 │ John Smith   │  P    │  LA   │  P    │  P    │  P    │  --   │  --   │  WFH  │  ?    │    │       │
 │ Alice K.     │  P    │  P    │  P    │  P    │  P    │  --   │  H    │  P    │  ?    │    │       │
 └──────────────┴───────┴───────┴───────┴───────┴───────┴───────┴───────┴───────┴───────┴────┴───────┘
```

- **Rows:** One row per user, grouped by department with collapsible department headers.
- **Columns:** 14 calendar days. Default range: current week + next week. Navigation arrows shift the window by one week in either direction.
- **Cells:** Display the attendance code badge for that user-day (color-coded). Hovering shows a tooltip with clock-in/out times and notes.
- **Today column** is visually highlighted.
- **Weekend / holiday columns** have a muted background.
- `?` indicates today's date where the user has not yet clocked in.
- `--` indicates a non-working day per the user's assigned shift schedule.

#### Filters & Controls

| Filter | Type | Default |
|---|---|---|
| Department | Dropdown (multi-select) | All (managers see own dept only) |
| User search | Text input | - |
| Date range | Week picker | Current 2-week window |
| Attendance code | Dropdown (multi-select) | All |
| Show weekends | Toggle | On |

#### Cell Interactions

- **Click a cell** (edit permission): Opens a slide-over panel to view/edit that day's record.
  - Shows: clock-in time, clock-out time, total hours, source (manual/biometric), notes.
  - Allows: change attendance code, manually set clock times (correction), add admin note.
  - Corrections are audit-logged with `corrected_by`, `corrected_at`, and `original_values` JSON snapshot.

---

### 4.3 My Attendance (Personal View)

Available to all users. Shows:

1. **Today's status card**: Current clock state, time clocked in, running duration counter.
2. **This week summary**: Hours worked per day (bar chart), total hours vs. expected hours.
3. **2-week personal grid**: Same 14-day layout but single-row (own data only) with full detail.
4. **Monthly calendar heatmap**: Color-coded days for the current month.
5. **Stats**: Total days present, absent, late for current month.

---

### 4.4 Attendance Codes (Configurable)

Administrators can create, edit, reorder, and deactivate attendance codes under **Settings > Attendance > Codes**.

#### Default Seed Codes

| Code | Label | Color | Category | Counts as Working | Is System |
|---|---|---|---|---|---|
| `P` | Present | `#0ab39c` (green) | presence | Yes | Yes |
| `A` | Absent | `#f06548` (red) | absence | No | Yes |
| `LA` | Late Arrival | `#f7b84b` (amber) | presence | Yes | Yes |
| `EO` | Early Out | `#f7b84b` (amber) | presence | Yes | No |
| `H` | Holiday | `#6559cc` (purple) | holiday | No | Yes |
| `WFH` | Work From Home | `#0ab39c` (green) | presence | Yes | No |
| `HD` | Half Day | `#299cdb` (blue) | presence | Partial (0.5) | No |
| `T` | Training | `#405189` (indigo) | duty | Yes | No |
| `OT` | Overtime | `#0d6efd` (blue) | presence | Yes | No |
| `SU` | Suspended | `#343a40` (dark) | absence | No | No |
| `L` | Leave | `#299cdb` (blue) | leave | No | Yes |

> **Note:** Leave-category codes (`L` and any custom leave codes) exist here as attendance markers only. The future Leave Management module will own leave types, quotas, balances, and approval workflows. For now, managers can manually apply leave codes to attendance records.

#### Code Properties

| Field | Type | Description |
|---|---|---|
| `code` | string(8), unique | Short code displayed in grid cells |
| `label` | string | Human-readable name |
| `color` | hex string | Badge background color |
| `category` | enum: `presence`, `absence`, `leave`, `holiday`, `duty` | Groups codes for filtering and reporting |
| `counts_as_working` | decimal (0.00-1.00) | 1.0 = full day, 0.5 = half day, 0 = non-working. Used in hours/days calculations |
| `is_system` | boolean | System codes cannot be deleted (but can be renamed/recolored) |
| `is_active` | boolean | Inactive codes cannot be assigned to new records |
| `sort_order` | integer | Display order in dropdowns and grid legend |

---

### 4.5 Shift Schedules

Shifts define expected working hours and days. Users are assigned to a shift; the system uses the shift to determine:

- Whether a user is **expected** to work on a given day.
- Whether a clock-in is **late** (auto-applies `LA` code).
- Whether a clock-out is **early** (auto-applies `EO` code).
- Expected **total hours** for reporting.

#### Shift Model

| Field | Type | Description |
|---|---|---|
| `name` | string | e.g., "Standard Office", "Early Shift" |
| `is_default` | boolean | Auto-assigned to new users |
| `grace_minutes` | integer | Minutes after `start_time` before clock-in counts as late (default: 15) |
| `early_out_minutes` | integer | Minutes before `end_time` that counts as early departure (default: 15) |
| `overtime_after_minutes` | integer | Minutes past `end_time` before overtime accrues (default: 30) |
| `earliest_clock_in` | time | Earliest allowed clock-in (e.g., 06:00). Null = no restriction |
| `latest_clock_in` | time | Latest allowed manual clock-in (e.g., 12:00). Null = no restriction |
| `is_active` | boolean | - |

#### Shift Days (pivot: `crm_attendance_shift_days`)

| Field | Type |
|---|---|
| `shift_id` | FK |
| `day_of_week` | integer (0=Mon ... 6=Sun) |
| `start_time` | time |
| `end_time` | time |
| `is_working_day` | boolean |

Example: "Standard Office" shift has Mon-Fri 08:00-17:00 as working days, Sat-Sun as non-working.

#### User-Shift Assignment

- Each user has a `shift_id` FK on the `users` table (nullable, defaults to the default shift).
- Admin can bulk-assign shifts by department.
- A user's shift can be overridden for a specific date range via `crm_attendance_shift_overrides` (e.g., temporary night shift assignment).

---

### 4.6 Holidays

Public holidays and company-wide days off. When a holiday falls on a working day, the system auto-applies the `H` attendance code and excludes the day from "expected hours" calculations.

#### Holiday Model

| Field | Type | Description |
|---|---|---|
| `name` | string | e.g., "President's Day", "Christmas Day" |
| `date` | date | Specific date |
| `is_recurring` | boolean | If true, applies every year on the same month/day |
| `applies_to` | enum: `all`, `department`, `shift` | Scope of the holiday |
| `scope_id` | integer, nullable | Department ID or Shift ID when scoped |
| `is_active` | boolean | - |

#### Holiday Management UI (Settings > Attendance > Holidays)

- Calendar year view showing all holidays as colored pins.
- CRUD form for adding/editing holidays.
- When a holiday is added retroactively, a background job backfills `H` codes for affected records.

---

### 4.7 Automatic Code Assignment

The system runs logic on each clock event and at end-of-day to assign the correct attendance code:

| Condition | Assigned Code | Trigger |
|---|---|---|
| Clocked in within grace period | `P` (Present) | On clock-in |
| Clocked in after grace period | `LA` (Late Arrival) | On clock-in |
| Clocked out before `end_time - early_out_minutes` | `EO` (Early Out) | On clock-out |
| Worked past `end_time + overtime_after_minutes` | `OT` (Overtime) flag added | On clock-out |
| Working day, no clock-in by end of day | `A` (Absent) | End-of-day job |
| Holiday on a working day | `H` (Holiday) | Holiday sync job |
| Non-working day per shift | No record created | - |

Manually applied codes (WFH, Training, etc.) always take precedence over auto-assigned codes. Managers and admins can override any auto-assigned code.

---

### 4.8 Corrections & Audit Trail

#### Correction Request (by user)

1. User navigates to **My Attendance** and clicks on a day.
2. User submits a correction request: proposed clock-in/out times + reason.
3. Record enters `pending_correction` state.
4. Manager/admin receives a notification (in-app discussion message) and can approve or reject.
5. On approval: record is updated, audit log entry created.
6. On rejection: record stays unchanged, user is notified with the rejection reason.

---

### 4.9 Reports & Exports

Available to admin, manager, and finance roles.

#### Built-in Reports

| Report | Description |
|---|---|
| **Daily Attendance Summary** | All staff attendance for a selected date with clock times and codes |
| **Monthly Attendance Register** | Full month grid per department, printable A3 landscape (PDF) |
| **Hours Worked Summary** | Per-user total hours, expected hours, overtime, deficit for a date range |
| **Late Arrivals Report** | All late-arrival records for a period with frequency count per user |
| **Absenteeism Report** | Absence frequency and patterns per user/department for a period |
| **Biometric Audit Log** | All biometric events including unmatched/failed for a device/date range |

#### Export Formats

- **Excel** (via Maatwebsite/Excel, consistent with existing CRM exports)
- **PDF** (via barryvdh/laravel-dompdf, consistent with existing commercial document PDFs)

---

### 4.10 Notifications

| Event | Channel | Recipients |
|---|---|---|
| Auto-closed overnight record | In-app | Affected user |
| Late arrival flagged | In-app | User + their manager |
| Absent (no clock-in by EOD) | In-app | User's manager |
| Correction request submitted | In-app | User's manager |
| Correction approved/rejected | In-app | Requesting user |
| Biometric device offline (no heartbeat > 30 min) | In-app | All attendance admins |

Notifications use the existing CRM discussion/messaging infrastructure.

---

## 5. Data Model

### 5.1 New Tables

#### `crm_attendance_codes`

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `code` | varchar(8) | Unique, uppercase |
| `label` | varchar(100) | |
| `color` | varchar(7) | Hex color |
| `category` | enum(`presence`,`absence`,`leave`,`holiday`,`duty`) | |
| `counts_as_working` | decimal(3,2) | 0.00 - 1.00 |
| `is_system` | boolean | Default false |
| `is_active` | boolean | Default true |
| `sort_order` | integer | Default 0 |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

#### `crm_attendance_shifts`

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `name` | varchar(100) | |
| `is_default` | boolean | Only one row can be true |
| `grace_minutes` | integer | Default 15 |
| `early_out_minutes` | integer | Default 15 |
| `overtime_after_minutes` | integer | Default 30 |
| `earliest_clock_in` | time, nullable | |
| `latest_clock_in` | time, nullable | |
| `is_active` | boolean | Default true |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

#### `crm_attendance_shift_days`

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `shift_id` | FK -> `crm_attendance_shifts` | Cascade delete |
| `day_of_week` | tinyint | 0=Mon, 6=Sun |
| `start_time` | time | |
| `end_time` | time | |
| `is_working_day` | boolean | |

Unique constraint: `(shift_id, day_of_week)`.

#### `crm_attendance_shift_overrides`

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `user_id` | FK -> `users` | |
| `shift_id` | FK -> `crm_attendance_shifts` | |
| `start_date` | date | |
| `end_date` | date | |
| `reason` | text, nullable | |
| `created_by` | FK -> `users` | |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

#### `crm_attendance_records`

The core table. One row per user per day.

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `user_id` | FK -> `users` | Indexed |
| `date` | date | Indexed |
| `attendance_code_id` | FK -> `crm_attendance_codes` | |
| `clocked_in_at` | datetime, nullable | |
| `clocked_out_at` | datetime, nullable | |
| `source` | enum(`manual`,`biometric`,`system`,`import`) | |
| `clock_in_note` | text, nullable | |
| `clock_out_note` | text, nullable | |
| `total_minutes` | integer, nullable | Computed on clock-out |
| `overtime_minutes` | integer | Default 0 |
| `is_late` | boolean | Default false |
| `is_early_out` | boolean | Default false |
| `auto_closed` | boolean | Default false |
| `status` | enum(`active`,`pending_correction`) | Default `active` |
| `approved_by` | FK -> `users`, nullable | |
| `approved_at` | datetime, nullable | |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

Unique constraint: `(user_id, date)`.

#### `crm_attendance_corrections`

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `attendance_record_id` | FK -> `crm_attendance_records` | |
| `requested_by` | FK -> `users` | |
| `original_values` | json | Snapshot of fields before correction |
| `proposed_clock_in` | datetime, nullable | |
| `proposed_clock_out` | datetime, nullable | |
| `proposed_code_id` | FK -> `crm_attendance_codes`, nullable | |
| `reason` | text | |
| `status` | enum(`pending`,`approved`,`rejected`) | Default `pending` |
| `reviewed_by` | FK -> `users`, nullable | |
| `reviewed_at` | datetime, nullable | |
| `rejection_reason` | text, nullable | |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

#### `crm_attendance_holidays`

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `name` | varchar(150) | |
| `date` | date | |
| `is_recurring` | boolean | Default false |
| `applies_to` | enum(`all`,`department`,`shift`) | Default `all` |
| `scope_id` | integer, nullable | Department or shift ID |
| `is_active` | boolean | Default true |
| `created_by` | FK -> `users` | |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

#### `crm_attendance_devices`

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `name` | varchar(100) | |
| `device_identifier` | varchar(50), unique | |
| `location` | varchar(200), nullable | |
| `api_token_id` | FK -> `personal_access_tokens`, nullable | Sanctum token reference |
| `min_confidence` | decimal(3,2) | Default 0.80 |
| `is_active` | boolean | Default true |
| `last_heartbeat_at` | datetime, nullable | |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

#### `crm_attendance_device_logs`

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `device_id` | FK -> `crm_attendance_devices` | |
| `employee_identifier` | varchar(50) | Raw value from device |
| `event_type` | varchar(20) | |
| `captured_at` | datetime | |
| `verification_method` | varchar(20), nullable | |
| `confidence_score` | decimal(4,3), nullable | |
| `status` | enum(`processed`,`unmatched`,`duplicate`,`below_confidence`,`device_inactive`) | |
| `matched_user_id` | FK -> `users`, nullable | |
| `attendance_record_id` | FK -> `crm_attendance_records`, nullable | |
| `error_message` | text, nullable | |
| `created_at` | timestamp | |

### 5.2 Modified Tables

#### `users`

| New Column | Type | Notes |
|---|---|---|
| `shift_id` | FK -> `crm_attendance_shifts`, nullable | Defaults to the default shift |

---

## 6. API Endpoints

### 6.1 Biometric Integration API (Sanctum token auth, ability: `attendance:biometric-push`)

| Method | URI | Description |
|---|---|---|
| `POST` | `/api/crm/attendance/biometric-event` | Push a clock event from a biometric device |
| `POST` | `/api/crm/attendance/biometric-heartbeat` | Device heartbeat/status ping |

### 6.2 Web Routes (auth + crm.access + crm.onboarding middleware)

All prefixed with `/crm/attendance`, named `crm.attendance.*`.

| Method | URI | Name | Description |
|---|---|---|---|
| `GET` | `/my` | `my` | Personal attendance dashboard |
| `GET` | `/grid` | `grid` | Team 2-week attendance grid |
| `POST` | `/clock` | `clock` | Clock in or clock out (toggle) |
| `GET` | `/records/{record}` | `records.show` | View single record detail |
| `PUT` | `/records/{record}` | `records.update` | Update/correct a record (manager+) |
| `POST` | `/records/{record}/correction` | `records.correction` | Submit correction request |
| `PUT` | `/corrections/{correction}/review` | `corrections.review` | Approve/reject correction |
| `GET` | `/reports` | `reports` | Reports index |
| `GET` | `/reports/{type}` | `reports.show` | Generate specific report |
| `GET` | `/reports/{type}/export` | `reports.export` | Export report as Excel/PDF |

### 6.3 Settings Routes (admin only, under `/crm/settings/attendance`)

| Method | URI | Name | Description |
|---|---|---|---|
| `GET` | `/codes` | `settings.attendance.codes` | List attendance codes |
| `POST` | `/codes` | `settings.attendance.codes.store` | Create code |
| `PUT` | `/codes/{code}` | `settings.attendance.codes.update` | Update code |
| `DELETE` | `/codes/{code}` | `settings.attendance.codes.destroy` | Deactivate code |
| `GET` | `/shifts` | `settings.attendance.shifts` | List shifts |
| `POST` | `/shifts` | `settings.attendance.shifts.store` | Create shift |
| `PUT` | `/shifts/{shift}` | `settings.attendance.shifts.update` | Update shift |
| `DELETE` | `/shifts/{shift}` | `settings.attendance.shifts.destroy` | Deactivate shift |
| `GET` | `/holidays` | `settings.attendance.holidays` | List holidays |
| `POST` | `/holidays` | `settings.attendance.holidays.store` | Create holiday |
| `PUT` | `/holidays/{holiday}` | `settings.attendance.holidays.update` | Update holiday |
| `DELETE` | `/holidays/{holiday}` | `settings.attendance.holidays.destroy` | Delete holiday |
| `GET` | `/devices` | `settings.attendance.devices` | List biometric devices |
| `POST` | `/devices` | `settings.attendance.devices.store` | Register device |
| `PUT` | `/devices/{device}` | `settings.attendance.devices.update` | Update device |
| `POST` | `/devices/{device}/regenerate-token` | `settings.attendance.devices.regenerate-token` | Regenerate API token |

---

## 7. Scheduled Jobs

Register in `app/Console/Kernel.php`:

| Job | Schedule | Description |
|---|---|---|
| `CloseOvernightRecordsJob` | Daily at `00:05` | Finds open records from previous days, closes them at `23:59:59`, sets `auto_closed = true` |
| `MarkAbsenteesJob` | Daily at configurable time (default `17:30`) | For each user with a working day and no clock-in, creates a record with `A` (Absent) code |
| `SyncHolidayAttendanceJob` | On-demand (dispatched when a holiday is created/updated) | Backfills `H` codes for affected users on the holiday date |

---

## 8. Configuration Additions

Add to `config/heritage_crm.php`:

```php
'attendance' => [
    'queue' => [
        'connection' => env('CRM_ATTENDANCE_QUEUE_CONNECTION', env('QUEUE_CONNECTION', 'database')),
        'queue' => env('CRM_ATTENDANCE_QUEUE_NAME', 'crm-attendance'),
    ],
    'auto_close_time' => '23:59:59',
    'mark_absent_at' => '17:30',
    'clock_debounce_seconds' => 60,
    'max_daily_hours' => 24,
    'biometric_heartbeat_timeout_minutes' => 30,
],
```

---

## 9. Edge Cases & Business Rules

| # | Scenario | Handling |
|---|---|---|
| 1 | User clocks in twice in one day (e.g., leaves for lunch) | v1: Single clock-in/out per day. Multiple clock pairs are a v2 feature. First clock-in and last clock-out define the day. |
| 2 | User forgets to clock out | `CloseOvernightRecordsJob` auto-closes at `23:59:59`, flags `auto_closed`. User sees a warning banner next day. |
| 3 | Biometric device sends event for deactivated user | Logged as `unmatched` in device logs. Not processed. |
| 4 | Holiday added for a date that already has attendance records | `SyncHolidayAttendanceJob` overwrites the code to `H` only if current code is `A` (absent). Existing `P`, etc. are preserved. |
| 5 | User changes department mid-period | Grid groups by current department. Historical records are not re-grouped. |
| 6 | Shift assignment changes mid-day | The shift active at clock-in time is used for that day's calculations. |
| 7 | Two biometric events within debounce window | Second event logged as `duplicate`, not processed. |
| 8 | Correction approved after month-end export | Export snapshots are point-in-time. Corrected records appear in the next export. |
| 9 | Clock-in before `earliest_clock_in` | Rejected with a validation message. User must wait. |

---

## 10. Future Enhancements (Out of Scope for v1)

| Feature | Notes |
|---|---|
| **Leave Management Module** | Separate module: leave types, annual quotas, accrual rules, carry-over, balance enforcement, approval workflows. Will consume attendance codes with `category = 'leave'`. |
| Multiple clock-in/out pairs per day | Support lunch breaks, split shifts |
| Geofenced clock-in (mobile) | Clock-in only when within GPS radius of office |
| Overtime approval workflow | Require manager approval before overtime is officially recorded |
| Integration with payroll export | Map attendance data to payroll system formats |
| Country-specific holiday packs | Pre-built holiday lists by country |
| Shift rotation schedules | Auto-rotate staff through shifts on a weekly/monthly cycle |
| Attendance analytics dashboard | Trend charts, department comparisons, predictive absenteeism alerts |
| Bulk attendance import | CSV/Excel import for historical attendance data (follows existing CRM import pattern) |

---

## 11. Dependencies

| Dependency | Status | Notes |
|---|---|---|
| Existing CRM user model & auth | Available | `User` model, Sanctum tokens, role system |
| Department & position system | Available | `CrmUserDepartment`, `CrmUserPosition` |
| Discussion/notification system | Available | Used for correction notifications |
| `personal_payroll_number` on users | Available | Used as biometric identifier |
| Maatwebsite/Excel | Available | Excel exports |
| barryvdh/laravel-dompdf | Referenced in CLAUDE.md | PDF exports |
| Queue worker | Required | For biometric event processing and scheduled jobs |

---

## 12. Acceptance Criteria

| # | Criterion |
|---|---|
| AC1 | A user can clock in and clock out via the navbar button and see their status update in real time |
| AC2 | The 2-week grid displays all users grouped by department with correct attendance code badges |
| AC3 | Clicking a grid cell opens a detail panel showing clock times, notes, and allows code changes (for authorized users) |
| AC4 | Admin can create, edit, and deactivate attendance codes, and the grid reflects the changes immediately |
| AC5 | Admin can create shifts with per-day schedules and assign users to shifts |
| AC6 | Late arrivals are auto-detected based on shift grace period and flagged with the `LA` code |
| AC7 | Admin can manage public holidays and the `H` code is auto-applied on holiday dates |
| AC8 | A registered biometric device can push clock events via the API and records are created correctly |
| AC9 | Unmatched or low-confidence biometric events are logged but not processed |
| AC10 | Users can submit correction requests and managers can approve/reject them |
| AC11 | The monthly attendance register can be exported as Excel and PDF |
| AC12 | Overnight unclosed records are auto-closed by the scheduled job |
| AC13 | Absent users are auto-marked by the end-of-day scheduled job |
| AC14 | All attendance actions are scoped by the existing CRM module permission system |

---

---

## 13. Design System Alignment

All attendance views **must** use the existing Heritage Pro CRM design system. No new CSS paradigms, component shapes, or color palettes should be introduced. The following documents exactly which existing patterns map to each attendance UI element.

### 13.1 Global CSS Conventions

| Convention | Rule |
|---|---|
| Class prefix | All custom classes use `.crm-` prefix (e.g., `.crm-attendance-grid`, `.crm-attendance-cell`) |
| Font | `'IBM Plex Sans', sans-serif` inherited from layout |
| Primary accent | `#2563eb` for active states, focus rings, selected items |
| Text hierarchy | Dark `#0f172a` for headings, `#334155` for body, `#64748b` for muted |
| Borders | `#e5e7eb` standard, `#cbd5e1` for input borders |
| Border radius | `3px` for cards (not rounded corners) |
| Spacing | 24px card padding, 16-20px standard gaps, 10px field gaps |
| Icons | Boxicons (`.bx` prefix) for navigation/chrome, FontAwesome (`.fas`/`.far`) for action buttons |

### 13.2 Page Layout Patterns

#### My Attendance Page (`my.blade.php`)

Follows the **Dashboard** pattern:

```
┌──────────────────────────────────────────────────────────────────────┐
│  .crm-summary-hero                                                   │
│  ┌─────────────────────────────┐  ┌────┐ ┌────┐ ┌────┐ ┌────┐       │
│  │ .crm-summary-hero-copy      │  │Stat│ │Stat│ │Stat│ │Stat│       │
│  │ h1: "My Attendance"         │  │Days│ │Hrs │ │Late│ │Abs │       │
│  │ p: "Tuesday, 22 April 2026" │  │Pres│ │Wrkd│ │Days│ │Days│       │
│  └─────────────────────────────┘  └────┘ └────┘ └────┘ └────┘       │
└──────────────────────────────────────────────────────────────────────┘

┌─── .crm-grid.cols-2 ────────────────────────────────────────────────┐
│                                                                      │
│  LEFT COLUMN (.crm-stack)              RIGHT COLUMN (aside)          │
│                                                                      │
│  ┌─ .crm-card ──────────────┐          ┌─ .crm-card ─────────────┐  │
│  │ .crm-kicker: "Today"     │          │ .crm-kicker: "Month"    │  │
│  │ h2: "Clock Status"       │          │ h2: "April 2026"        │  │
│  │                           │          │                         │  │
│  │ [Clock In/Out widget]     │          │ [Calendar heatmap]      │  │
│  │ Running timer + note      │          │ Color-coded day cells   │  │
│  └───────────────────────────┘          └─────────────────────────┘  │
│                                                                      │
│  ┌─ .crm-card ──────────────┐          ┌─ .crm-card ─────────────┐  │
│  │ .crm-kicker: "This Week" │          │ .crm-kicker: "Summary"  │  │
│  │ h2: "Hours Worked"       │          │ h2: "Monthly Stats"     │  │
│  │                           │          │                         │  │
│  │ [ApexChart bar chart]     │          │ .crm-meta-list rows     │  │
│  │ Expected vs actual hrs    │          │ Present: 18 / Late: 2   │  │
│  └───────────────────────────┘          └─────────────────────────┘  │
│                                                                      │
│  ┌─ .crm-card (full width, grid-column: 1 / -1) ────────────────┐   │
│  │ .crm-kicker: "Last 14 days"                                   │   │
│  │ h2: "Personal Attendance"                                     │   │
│  │ [Single-row 2-week grid with code badges]                     │   │
│  └───────────────────────────────────────────────────────────────┘   │
└──────────────────────────────────────────────────────────────────────┘
```

- **Hero stats** use `@include('crm.partials.header-stat', ['label' => '...', 'value' => ...])`.
- **Bar chart** uses ApexCharts (already loaded globally in CRM layout).
- **Meta list** uses `.crm-meta-list` > `.crm-meta-row` > `<span>` + `<strong>`.
- **Heatmap** is a custom `.crm-mini-month` grid (same pattern as the calendar sidebar mini-month).

#### Team Grid Page (`grid.blade.php`)

Follows the **Index/Listing** pattern with a custom table body:

```
┌─ .crm-card.crm-filter-card ─────────────────────────────────────────┐
│  .crm-kicker: "Filters"                                             │
│  h2: "Team Attendance"                                               │
│                                                                      │
│  .crm-filter-grid (4-column):                                       │
│  ┌────────────┐ ┌────────────┐ ┌────────────┐ ┌────────────┐        │
│  │ Department  │ │ Search     │ │ Code       │ │ Weekends   │        │
│  │ <select>   │ │ <input>    │ │ <select>   │ │ [toggle]   │        │
│  └────────────┘ └────────────┘ └────────────┘ └────────────┘        │
│                                                                      │
│  .form-actions:  [Reset]  [< Prev Week]  [Today]  [Next Week >]     │
└──────────────────────────────────────────────────────────────────────┘

┌─ .crm-card ──────────────────────────────────────────────────────────┐
│  .crm-card-title:                                                    │
│    .crm-kicker: "14 Apr – 27 Apr 2026"                               │
│    h2: "Attendance Grid"                                             │
│                                                                      │
│  .crm-attendance-legend (.crm-inline):                               │
│    [P badge] [A badge] [LA badge] [H badge] ...                      │
│                                                                      │
│  .crm-table-wrap:                                                    │
│  ┌──────────────────────────────────────────────────────────────┐     │
│  │ .crm-table                                                   │     │
│  │                                                               │     │
│  │  thead: Employee | Mon 14 | Tue 15 | ... | Sun 27            │     │
│  │                                                               │     │
│  │  Department header row (colspan, muted bg):                   │     │
│  │  "Engineering (4 staff)"                                      │     │
│  │                                                               │     │
│  │  tbody rows:                                                  │     │
│  │  ┌─────────────┬───────┬───────┬───────┬─── ...              │     │
│  │  │ [avatar] Name│ .crm-pill │ .crm-pill │ .crm-pill │        │     │
│  │  └─────────────┴───────┴───────┴───────┴─── ...              │     │
│  │                                                               │     │
│  │  Today column: background rgba(37,99,235,0.06)               │     │
│  │  Weekend columns: background #f8fafc                          │     │
│  │  Holiday columns: background rgba(101,89,204,0.06)           │     │
│  └──────────────────────────────────────────────────────────────┘     │
│                                                                      │
│  @include('crm.partials.pager')                                      │
└──────────────────────────────────────────────────────────────────────┘
```

- **Grid cells** contain `.crm-pill` badges using the attendance code's configured `color`.
- **Cell pill CSS**: `display: inline-flex; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 600; cursor: pointer;` with the code's color as background (at 15% opacity) and the code's color as text.
- **Tooltip** on hover (native `title` attribute or a lightweight JS tooltip) showing: "Clocked in: 07:52 | Clocked out: 17:05 | 9h 13m".
- **Click** on an editable cell opens a **slide-over panel** (right-side drawer, 420px width, `.crm-attendance-panel`), not a modal. The panel uses `.crm-card` internals with `.crm-meta-list` for record details and `.crm-form` for the edit form.
- **Department group rows** use `<tr>` with a single `<td colspan="15">` styled as: `background: #f8fafc; font-weight: 600; font-size: 13px; padding: 10px 14px; color: #334155; border-bottom: 1px solid #e5e7eb;`.

#### Settings Pages

Follow the existing **Settings tab** pattern:

```
┌─ .crm-tabs.crm-tabs-top ────────────────────────────────────────────┐
│  [Overview] [Sales stages] [Imports] [Commercial] [Attendance]       │
└──────────────────────────────────────────────────────────────────────┘

┌─ .crm-tabs.crm-tabs-top (sub-tabs for attendance) ──────────────────┐
│  [Codes] [Shifts] [Holidays] [Devices]                               │
└──────────────────────────────────────────────────────────────────────┘

Content below uses standard .crm-card with .crm-table for listing,
.crm-form with .crm-field-grid for create/edit forms.
```

- **Attendance Codes list**: `.crm-table` with columns: Color swatch (20x20 circle), Code, Label, Category (`.crm-pill`), Working %, System (lock icon), Active (toggle), Actions (edit/deactivate).
- **Shifts list**: `.crm-table` with columns: Name, Default (star icon), Schedule summary ("Mon-Fri 08:00-17:00"), Grace, Active, Actions.
- **Holidays list**: `.crm-table` with columns: Name, Date, Recurring (`.crm-pill`), Scope, Active, Actions.
- **Devices list**: `.crm-table` with columns: Name, Identifier, Location, Status (`.crm-pill` green/red), Last Heartbeat (relative time), Actions.

#### Reports Page

Follows the **Index** pattern:

```
┌─ .crm-summary-hero ─────────────────────────────────────────────────┐
│  h1: "Attendance Reports"                                            │
│  p: "Generate and export attendance data"                            │
│  Stats: [Total Records] [Present Today] [Late Today] [Absent Today]  │
└──────────────────────────────────────────────────────────────────────┘

┌─ .crm-grid.cols-3 ──────────────────────────────────────────────────┐
│  ┌─ .crm-card (report card) ────┐  (repeat for each report type)    │
│  │  <i class="bx bx-calendar    │                                    │
│  │      bx-lg"></i>              │                                    │
│  │  h3: "Monthly Register"      │                                    │
│  │  p.crm-muted-copy:           │                                    │
│  │    "Full month grid..."      │                                    │
│  │  a.btn.btn-primary: "Generate"│                                    │
│  └───────────────────────────────┘                                    │
└──────────────────────────────────────────────────────────────────────┘
```

### 13.3 Navbar Clock Widget

The clock-in/out button sits in the CRM topbar, following the same pattern as the existing presence/search icons:

```html
<div class="crm-attendance-clock" id="crm-clock-widget">
    <!-- Clocked out state -->
    <button class="btn crm-topbar-action crm-clock-btn is-out"
            data-crm-clock-action="in"
            title="Clock In">
        <i class="bx bx-log-in-circle"></i>
        <span class="crm-clock-label">Clock In</span>
    </button>

    <!-- Clocked in state (shown instead) -->
    <button class="btn crm-topbar-action crm-clock-btn is-in"
            data-crm-clock-action="out"
            title="Clock Out — since 07:52">
        <i class="bx bx-log-out-circle"></i>
        <span class="crm-clock-label">Clock Out</span>
        <span class="crm-clock-timer">4h 12m</span>
    </button>
</div>
```

- Uses the same `.crm-topbar-action` sizing as other navbar icons (36px height, 12px horizontal padding).
- `.is-in` state: green accent (`#0ab39c`) on the icon and timer.
- `.is-out` state: muted icon (`#64748b`).
- Timer updates every 60 seconds via the existing presence polling interval.
- On click: POST to `/crm/attendance/clock` via fetch, update widget state, show `.crm-toast` confirmation.

### 13.4 Component Reference Map

| Attendance Element | CRM Pattern to Use | Reference File |
|---|---|---|
| Page header with stats | `.crm-summary-hero` + `header-stat` partial | `crm/dashboard.blade.php` |
| Filter bar | `.crm-card.crm-filter-card` + `.crm-filter-grid` | `crm/users/index.blade.php` |
| Data tables | `.crm-table-wrap` > `.crm-table` | `crm/customers/index.blade.php` |
| Attendance code badges | `.crm-pill` with dynamic background color | `crm/requests/index.blade.php` |
| Two-column detail layout | `.crm-grid.cols-2` > `.crm-stack` + `<aside>` | `crm/requests/show.blade.php` |
| Key-value detail rows | `.crm-meta-list` > `.crm-meta-row` | `crm/requests/show.blade.php` |
| Form layout | `.crm-form` > `.crm-field-grid` > `.crm-field` | `crm/users/create.blade.php` |
| Save button with spinner | `.btn.btn-primary.btn-loading` | `crm/contacts/create.blade.php` |
| Cancel/back button | `.btn.btn-light.crm-btn-light` | `crm/users/create.blade.php` |
| Settings tabs | `.crm-tabs.crm-tabs-top` > `.crm-tab` | `crm/settings/` views |
| Pagination | `crm.partials.pager` include | `crm/customers/index.blade.php` |
| Empty states | `.crm-empty` | `crm/requests/index.blade.php` |
| Toast feedback | `crm.partials.flash` (session-driven) | Layout `crm.blade.php` |
| Slide-over panel | New component, but styled with `.crm-card` internals | n/a (new, see spec below) |
| Mini calendar heatmap | `.crm-mini-month` grid | `crm/calendar/index.blade.php` |
| Charts | ApexCharts (bar, donut) | `crm/dashboard.blade.php` |
| Delete confirmation | `onsubmit="return confirm('...')"` pattern | `crm/partials/delete-button.blade.php` |
| Help/info box | `crm.partials.helper-text` include | Various settings pages |

### 13.5 Slide-Over Panel Spec (New Component)

The grid cell click opens a right-side drawer. This is the only new UI primitive; it must match the existing visual language:

```css
.crm-slide-panel {
    position: fixed;
    top: 0;
    right: 0;
    width: 420px;
    height: 100vh;
    background: #ffffff;
    border-left: 1px solid #e5e7eb;
    box-shadow: -4px 0 24px rgba(0, 0, 0, 0.08);
    z-index: 1050;
    transform: translateX(100%);
    transition: transform 0.25s ease;
    overflow-y: auto;
    padding: 24px;
}

.crm-slide-panel.is-open {
    transform: translateX(0);
}

.crm-slide-panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid #e5e7eb;
}

.crm-slide-panel-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.2);
    z-index: 1049;
    opacity: 0;
    transition: opacity 0.25s ease;
    pointer-events: none;
}

.crm-slide-panel-backdrop.is-visible {
    opacity: 1;
    pointer-events: auto;
}
```

Internal content uses standard `.crm-meta-list`, `.crm-form`, `.crm-field`, `.form-actions`, and `.crm-pill` classes.

---

---

## 14. Development Phases

### Phase 1: Foundation — Database, Models & Module Registration

**Goal:** Establish the data layer and wire the attendance module into the CRM shell so subsequent phases have a stable base to build on.

**Estimated scope:** Backend only, no UI.

#### Tasks

| # | Task | Files | Details |
|---|------|-------|---------|
| 1.1 | Create migration: `crm_attendance_codes` | `database/migrations/2026_04_23_100000_create_crm_attendance_codes_table.php` | All columns per data model. Seed the 11 default codes in migration or a seeder. |
| 1.2 | Create migration: `crm_attendance_shifts` + `crm_attendance_shift_days` | `database/migrations/2026_04_23_100100_create_crm_attendance_shifts_tables.php` | Include unique constraint on `(shift_id, day_of_week)`. Seed "Standard Office" shift (Mon-Fri 08:00-17:00). |
| 1.3 | Create migration: `crm_attendance_shift_overrides` | `database/migrations/2026_04_23_100200_create_crm_attendance_shift_overrides_table.php` | FKs to `users` and `crm_attendance_shifts`. |
| 1.4 | Create migration: `crm_attendance_records` | `database/migrations/2026_04_23_100300_create_crm_attendance_records_table.php` | Unique constraint on `(user_id, date)`. Indexes on `user_id`, `date`, `attendance_code_id`. |
| 1.5 | Create migration: `crm_attendance_corrections` | `database/migrations/2026_04_23_100400_create_crm_attendance_corrections_table.php` | FK to records and users. |
| 1.6 | Create migration: `crm_attendance_holidays` | `database/migrations/2026_04_23_100500_create_crm_attendance_holidays_table.php` | All columns per data model. |
| 1.7 | Create migration: `add_shift_id_to_users` | `database/migrations/2026_04_23_100600_add_shift_id_to_users_table.php` | Nullable FK to `crm_attendance_shifts`. |
| 1.8 | Create Eloquent models | `app/Models/CrmAttendanceCode.php`, `CrmAttendanceShift.php`, `CrmAttendanceShiftDay.php`, `CrmAttendanceShiftOverride.php`, `CrmAttendanceRecord.php`, `CrmAttendanceCorrection.php`, `CrmAttendanceHoliday.php` | Define `$fillable`, `$casts`, relationships. `CrmAttendanceRecord` has `belongsTo` for user, code; scopes for date ranges. `User` model gets `shift()` belongsTo and `attendanceRecords()` hasMany. |
| 1.9 | Register attendance module in config | `config/heritage_crm.php` | Add `attendance` to `modules` array (as per section 3). Add `attendance` config block (as per section 8). |
| 1.10 | Add `AttendanceShiftResolver` service | `app/Services/Crm/AttendanceShiftResolver.php` | `resolveForUserAndDate(User, Carbon): CrmAttendanceShift` — checks overrides first, falls back to user's shift, then default shift. Returns shift + the matching `CrmAttendanceShiftDay` for the given day-of-week. |
| 1.11 | Create route file (empty group) | `routes/crm/attendance.php` | Register middleware group, require in `routes/web.php`. Routes will be populated in later phases. |
| 1.12 | Write tests | `tests/Feature/Crm/CrmAttendanceFoundationTest.php` | Test: migrations run, models create/relate correctly, shift resolver returns correct shift for override vs. default, config is registered. |

#### Acceptance Criteria for Phase 1

- `php artisan migrate` succeeds with all new tables created.
- Default attendance codes and "Standard Office" shift are seeded.
- `User` model has `shift()` relationship.
- Attendance module appears in sidebar for users with appropriate permissions.
- `AttendanceShiftResolver` correctly prioritises: override > user shift > default shift.

---

### Phase 2: Clock In/Out Engine & My Attendance Page

**Goal:** Users can clock in and out via the navbar and view their personal attendance dashboard.

**Depends on:** Phase 1.

#### Tasks

| # | Task | Files | Details |
|---|------|-------|---------|
| 2.1 | Create `AttendanceClockService` | `app/Services/Crm/AttendanceClockService.php` | Methods: `clockIn(User, ?string $note): CrmAttendanceRecord`, `clockOut(User, ?string $note): CrmAttendanceRecord`, `currentStatus(User): array`. Handles: debounce check (60s), shift window validation, auto-code assignment (P vs LA), total_minutes + overtime calculation on clock-out, early-out detection. All writes wrapped in `DB::transaction` with `lockForUpdate`. |
| 2.2 | Create `AttendanceController` | `app/Http/Controllers/Crm/AttendanceController.php` | Extends `CrmController`. Methods: `my()`, `clock()`. `clock()` is a POST that toggles clock-in/out, returns JSON `{status, record, toast_message}`. |
| 2.3 | Create `AttendanceClockRequest` | `app/Http/Requests/Crm/AttendanceClockRequest.php` | Validates optional `note` (string, max 500). |
| 2.4 | Register clock route | `routes/crm/attendance.php` | `POST /clock` -> `AttendanceController@clock`. |
| 2.5 | Build navbar clock widget | `resources/views/crm/attendance/partials/clock-button.blade.php` | HTML per section 13.3. Include in `resources/views/layouts/crm.blade.php` topbar. JS: fetch POST on click, update state, show toast, update timer. |
| 2.6 | Build My Attendance page | `resources/views/crm/attendance/my.blade.php` | Layout per section 13.2. Components: hero stats (`.crm-summary-hero` with days present / hours worked / late / absent), clock status card (`.crm-card`), weekly hours bar chart (ApexCharts), personal 2-week grid row (`.crm-table` single-row), monthly heatmap (`.crm-mini-month`), monthly stats (`.crm-meta-list`). |
| 2.7 | Register my route | `routes/crm/attendance.php` | `GET /my` -> `AttendanceController@my`. |
| 2.8 | Create `AttendanceGridService` | `app/Services/Crm/AttendanceGridService.php` | Method: `buildPersonalGrid(User, Carbon $start, Carbon $end): array`. Returns array of 14 day-cells with: date, day_of_week, is_working_day, is_holiday, is_today, record (if exists), code, clock times. Used by My Attendance and later by Team Grid. |
| 2.9 | Write tests | `tests/Feature/Crm/CrmAttendanceClockTest.php` | Test: clock-in creates record with P code, clock-in after grace creates LA, clock-out computes total_minutes, debounce rejects duplicate, early-out detected, overtime calculated, clock-out before clock-in rejected, shift window enforced. |

#### Acceptance Criteria for Phase 2

- AC1: Navbar clock button toggles between "Clock In" and "Clock Out" states, timer runs.
- Clock-in at 08:05 with 15min grace = `P`. Clock-in at 08:20 = `LA`.
- Clock-out computes correct `total_minutes` and flags `overtime_minutes` when applicable.
- My Attendance page shows today's status, weekly chart, personal grid, monthly heatmap, and stats.
- All data uses `.crm-summary-hero`, `.crm-card`, `.crm-meta-list`, `.crm-pill`, `.crm-mini-month` patterns.

---

### Phase 3: Team Attendance Grid (2-Week View)

**Goal:** Managers and admins can view the full team attendance grid with department grouping, filtering, and cell interaction.

**Depends on:** Phase 2.

#### Tasks

| # | Task | Files | Details |
|---|------|-------|---------|
| 3.1 | Extend `AttendanceGridService` | `app/Services/Crm/AttendanceGridService.php` | Add method: `buildTeamGrid(User $viewer, Carbon $start, Carbon $end, array $filters): array`. Returns departments → users → day-cells. Applies department scoping for managers (own dept only). Eager-loads `attendanceRecords.code` for the date range. |
| 3.2 | Add `grid()` method to controller | `app/Http/Controllers/Crm/AttendanceController.php` | Passes grid data, filters, departments, codes to view. Permission check: `authorizeModuleAccess('attendance', 'view')` + managers scoped to own department. |
| 3.3 | Register grid route | `routes/crm/attendance.php` | `GET /grid` -> `AttendanceController@grid`. |
| 3.4 | Build grid page | `resources/views/crm/attendance/grid.blade.php` | Layout per section 13.2 (Team Grid). Filter card (`.crm-filter-card` with `.crm-filter-grid`), legend row (`.crm-inline` of `.crm-pill` badges), attendance table (`.crm-table` with department group rows, code pill cells, today highlight, weekend/holiday column shading), pagination. |
| 3.5 | Build grid cell partial | `resources/views/crm/attendance/partials/grid-cell.blade.php` | Renders a single `<td>` with the `.crm-pill` badge. Applies `title` tooltip with clock times. Adds `data-record-id`, `data-user-id`, `data-date` attributes for JS interaction. |
| 3.6 | Build legend partial | `resources/views/crm/attendance/partials/legend.blade.php` | Renders all active attendance codes as a horizontal `.crm-inline` row of `.crm-pill` mini-badges. |
| 3.7 | Build slide-over panel | `resources/views/crm/attendance/partials/record-panel.blade.php` | Right-side drawer (`.crm-slide-panel`) per section 13.5. Shows record detail (`.crm-meta-list`), edit form (`.crm-form` with code dropdown, datetime inputs, note textarea), save button (`.btn-loading`). Loaded via fetch when a cell is clicked. |
| 3.8 | Add record show/update routes | `routes/crm/attendance.php` | `GET /records/{record}` (JSON for panel), `PUT /records/{record}` (update with audit log). |
| 3.9 | Add record update logic | `app/Http/Controllers/Crm/AttendanceController.php` | `recordShow()` returns JSON with record + code + user. `recordUpdate()` validates, saves `original_values` snapshot to corrections table, updates record, returns JSON. Permission: `edit` level + department scope for managers. |
| 3.10 | Write tests | `tests/Feature/Crm/CrmAttendanceGridTest.php` | Test: grid returns correct 14-day structure, department grouping works, manager sees only own dept, admin sees all, weekend days marked correctly, holidays marked, cell click returns record JSON, record update creates audit entry. |

#### Acceptance Criteria for Phase 3

- AC2: Grid shows all users grouped by department with code badges.
- AC3: Clicking a cell opens slide-over with record details and edit form.
- Managers see only their department; admins see all.
- Today column highlighted, weekends and holidays have muted background.
- Navigation arrows shift the 2-week window correctly.
- Record updates are audit-logged.

---

### Phase 4: Settings — Attendance Codes, Shifts & Holidays

**Goal:** Admin can fully configure attendance codes, shift schedules, and holidays through the UI.

**Depends on:** Phase 1 (models exist).

#### Tasks

| # | Task | Files | Details |
|---|------|-------|---------|
| 4.1 | Create `AttendanceSettingController` | `app/Http/Controllers/Crm/AttendanceSettingController.php` | Extends `CrmController`. Methods for codes (list, store, update, destroy), shifts (list, store, update, destroy), holidays (list, store, update, destroy). All guarded by `authorizeAdminSettings()`. |
| 4.2 | Create form requests | `app/Http/Requests/Crm/AttendanceCodeUpsertRequest.php`, `AttendanceShiftUpsertRequest.php`, `AttendanceHolidayUpsertRequest.php` | Validate all fields per data model. Code: unique code validation (case-insensitive). Shift: validate shift_days array (7 entries, valid times). Holiday: date validation, scope_id required when applies_to != 'all'. |
| 4.3 | Register settings routes | `routes/crm/attendance.php` (or `routes/crm/settings.php`) | All settings routes under `/crm/settings/attendance/` prefix. |
| 4.4 | Add "Attendance" tab to settings | `resources/views/crm/settings/` | Add a tab link to the existing `.crm-tabs.crm-tabs-top` navigation that appears on settings pages. |
| 4.5 | Build attendance codes settings page | `resources/views/crm/settings/attendance-codes.blade.php` | Sub-tabs: [Codes] [Shifts] [Holidays] [Devices]. Codes tab: `.crm-table` listing all codes with color swatch, code, label, category pill, working %, system lock icon, active state. Inline add/edit form below table (`.crm-form` with `.crm-field-grid`). Color picker for the hex color field. |
| 4.6 | Build shifts settings page | `resources/views/crm/settings/attendance-shifts.blade.php` | `.crm-table` listing shifts. Create/edit form with: name, grace_minutes, early_out_minutes, overtime_after_minutes, earliest/latest clock-in, and a 7-day schedule grid (Mon-Sun rows with start_time, end_time, is_working_day checkbox). Bulk assign shift to department action. |
| 4.7 | Build holidays settings page | `resources/views/crm/settings/attendance-holidays.blade.php` | `.crm-table` listing holidays with name, date, recurring badge, scope, active state. Create/edit form. When a holiday is created/updated, dispatch `SyncHolidayAttendanceJob`. |
| 4.8 | Create `SyncHolidayAttendanceJob` | `app/Jobs/SyncHolidayAttendanceJob.php` | Receives holiday record. For each affected user (based on scope), upserts an attendance record with `H` code for the holiday date. Only overwrites if current code is `A` or no record exists. |
| 4.9 | Write tests | `tests/Feature/Crm/CrmAttendanceCodeTest.php`, `CrmAttendanceShiftTest.php`, `CrmAttendanceHolidayTest.php` | Test: CRUD for codes (system codes can't be deleted), CRUD for shifts (validate shift days), CRUD for holidays, holiday sync job backfills H codes, only admin can access settings. |

#### Acceptance Criteria for Phase 4

- AC4: Admin can CRUD attendance codes, changes reflected in grid immediately.
- AC5: Admin can create shifts with per-day schedules and assign to users.
- AC7: Admin can manage holidays, `H` code auto-applied on holiday dates.
- System codes cannot be deleted.
- Shift bulk-assign by department works.
- All settings pages use `.crm-tabs`, `.crm-table`, `.crm-form` patterns.

---

### Phase 5: Corrections & Approval Workflow

**Goal:** Users can request corrections to their attendance records, and managers can approve or reject them.

**Depends on:** Phase 2 + Phase 3.

#### Tasks

| # | Task | Files | Details |
|---|------|-------|---------|
| 5.1 | Add correction submission | `app/Http/Controllers/Crm/AttendanceController.php` | `submitCorrection()` method: creates a `CrmAttendanceCorrection` record with `original_values` snapshot, sets attendance record status to `pending_correction`. |
| 5.2 | Create `AttendanceCorrectionRequest` | `app/Http/Requests/Crm/AttendanceCorrectionRequest.php` | Validates: proposed_clock_in (nullable datetime), proposed_clock_out (nullable datetime), proposed_code_id (nullable, exists in codes), reason (required string). At least one proposed field must be provided. |
| 5.3 | Add correction review | `app/Http/Controllers/Crm/AttendanceController.php` | `reviewCorrection()` method: approve (applies proposed values to record, sets correction status, records reviewed_by/at) or reject (sets rejection_reason, notifies user). Permission: `edit` level + department scope. |
| 5.4 | Create `AttendanceCorrectionReviewRequest` | `app/Http/Requests/Crm/AttendanceCorrectionReviewRequest.php` | Validates: action (required, in: approve/reject), rejection_reason (required if reject). |
| 5.5 | Register correction routes | `routes/crm/attendance.php` | `POST /records/{record}/correction`, `PUT /corrections/{correction}/review`. |
| 5.6 | Add correction UI to slide-over panel | `resources/views/crm/attendance/partials/record-panel.blade.php` | For users viewing their own record: "Request Correction" section with proposed times, code, and reason fields. For managers viewing pending corrections: approval/rejection interface with the original vs. proposed values side-by-side and approve/reject buttons. |
| 5.7 | Add pending correction indicators | Grid view and My Attendance | Pending corrections show a pulsing dot overlay on the grid cell `.crm-pill`. My Attendance page shows a banner listing pending corrections. |
| 5.8 | Notification integration | `app/Http/Controllers/Crm/AttendanceController.php` | On correction submitted: send in-app notification to user's manager (via existing discussion system). On approved/rejected: notify the requesting user. |
| 5.9 | Write tests | `tests/Feature/Crm/CrmAttendanceCorrectionTest.php` | Test: user submits correction, record enters pending state, manager approves (values updated, audit logged), manager rejects (record unchanged, reason saved), user can't approve own correction, rep can't approve corrections, notifications sent. |

#### Acceptance Criteria for Phase 5

- AC10: Users can submit corrections, managers can approve/reject.
- Approved corrections update the record and log the audit trail.
- Rejected corrections preserve the original record and notify the user.
- Pending corrections are visually indicated on the grid.
- Notifications fire on submission and review.

---

### Phase 6: Scheduled Jobs & Automation

**Goal:** Overnight auto-close, end-of-day absentee marking, and holiday sync run reliably on schedule.

**Depends on:** Phase 2 + Phase 4.

#### Tasks

| # | Task | Files | Details |
|---|------|-------|---------|
| 6.1 | Create `CloseOvernightRecordsJob` | `app/Jobs/CloseOvernightRecordsJob.php` | Finds all `crm_attendance_records` where `clocked_out_at IS NULL` and `date < today`. Sets `clocked_out_at` to `{date} 23:59:59`, `auto_closed = true`, computes `total_minutes`. Sends in-app notification to affected users. |
| 6.2 | Create `MarkAbsenteesJob` | `app/Jobs/MarkAbsenteesJob.php` | For today's date: find all active users whose shift says today is a working day AND who have no attendance record for today AND today is not a holiday. Create a record with `A` code, `source = 'system'`. Notify each user's manager. |
| 6.3 | Register in Kernel | `app/Console/Kernel.php` | `CloseOvernightRecordsJob` daily at `00:05`. `MarkAbsenteesJob` daily at configured time (default `17:30`). |
| 6.4 | Add auto-close warning banner | `resources/views/crm/attendance/my.blade.php` | If user has any auto-closed records in the past 7 days, show a `.crm-help` warning box at the top: "Your clock-out was automatically recorded on {dates}. Please submit a correction if needed." |
| 6.5 | Write tests | `tests/Feature/Crm/CrmAttendanceJobsTest.php` | Test: overnight job closes open records and sets auto_closed, absentee job creates A records for unclocked users, absentee job skips holidays and non-working days, absentee job skips users who already have a record. |

#### Acceptance Criteria for Phase 6

- AC12: Overnight records auto-closed by scheduled job.
- AC13: Absent users marked by end-of-day job.
- Auto-closed records show warning to user on My Attendance page.
- Jobs skip holidays and non-working days correctly.

---

### Phase 7: Biometric Integration

**Goal:** External biometric devices can push clock events via API, with full device management UI.

**Depends on:** Phase 2 (clock service exists).

#### Tasks

| # | Task | Files | Details |
|---|------|-------|---------|
| 7.1 | Create migration: `crm_attendance_devices` + `crm_attendance_device_logs` | `database/migrations/2026_04_23_100700_create_crm_attendance_device_tables.php` | All columns per data model. |
| 7.2 | Create models | `app/Models/CrmAttendanceDevice.php`, `app/Models/CrmAttendanceDeviceLog.php` | Relationships, fillable, casts. Device has `hasMany` logs. |
| 7.3 | Create `BiometricEventProcessor` service | `app/Services/Crm/BiometricEventProcessor.php` | Method: `process(CrmAttendanceDevice, array $payload): CrmAttendanceDeviceLog`. Resolves user by `personal_payroll_number`, validates confidence, checks duplicates (debounce window), delegates to `AttendanceClockService`, logs outcome. |
| 7.4 | Create `ProcessBiometricEventJob` | `app/Jobs/ProcessBiometricEventJob.php` | Queued job that calls `BiometricEventProcessor`. Queue: `crm-attendance`. |
| 7.5 | Create API controller | `app/Http/Controllers/Api/Crm/BiometricController.php` | `event()`: validate payload via `BiometricEventRequest`, dispatch job, return 202 Accepted. `heartbeat()`: update `last_heartbeat_at` on device, return 200. Auth: Sanctum token with `attendance:biometric-push` ability. |
| 7.6 | Create `BiometricEventRequest` | `app/Http/Requests/Crm/BiometricEventRequest.php` | Validates: device_id (required string), employee_identifier (required string), event_type (required, in: clock_in/clock_out), captured_at (required datetime), verification_method (nullable string), confidence_score (nullable numeric 0-1). |
| 7.7 | Register API routes | `routes/api.php` | `POST /crm/attendance/biometric-event`, `POST /crm/attendance/biometric-heartbeat`. Sanctum middleware. |
| 7.8 | Build device management settings UI | `resources/views/crm/settings/attendance-devices.blade.php` | `.crm-table` listing devices with status pill (green if heartbeat < 30 min, red otherwise), last heartbeat relative time. Create/edit form (`.crm-form`). Token display (shown once on create, masked after). Regenerate token button with confirmation. |
| 7.9 | Add device routes to settings | `routes/crm/attendance.php` | CRUD routes for devices + regenerate-token POST. |
| 7.10 | Device offline notification | `app/Jobs/MarkAbsenteesJob.php` (or separate scheduled check) | During the absentee job (or a separate 15-min scheduled check): find devices where `is_active = true` AND `last_heartbeat_at < now - 30 min`. Notify attendance admins once (avoid repeated alerts — track in cache). |
| 7.11 | Write tests | `tests/Feature/Crm/CrmAttendanceBiometricTest.php` | Test: valid biometric event creates attendance record, unmatched employee logged, below-confidence logged, duplicate within debounce logged, inactive device rejected, heartbeat updates timestamp, device CRUD by admin only, token regeneration. |

#### Acceptance Criteria for Phase 7

- AC8: Biometric device can push events and records are created.
- AC9: Unmatched/low-confidence events logged, not processed.
- Device management UI follows `.crm-table` + `.crm-form` patterns.
- Offline device alerts fire after 30 min of no heartbeat.

---

### Phase 8: Reports & Exports

**Goal:** Managers and admins can generate attendance reports and export them as Excel/PDF.

**Depends on:** Phase 3 (grid data), Phase 6 (jobs populate records).

#### Tasks

| # | Task | Files | Details |
|---|------|-------|---------|
| 8.1 | Create `AttendanceReportService` | `app/Services/Crm/AttendanceReportService.php` | Methods for each report type: `dailySummary(Carbon $date, array $filters)`, `monthlyRegister(Carbon $month, ?int $departmentId)`, `hoursWorked(Carbon $from, Carbon $to, array $filters)`, `lateArrivals(Carbon $from, Carbon $to, array $filters)`, `absenteeism(Carbon $from, Carbon $to, array $filters)`, `biometricAudit(Carbon $from, Carbon $to, ?int $deviceId)`. Each returns a structured array/collection suitable for both view rendering and export. |
| 8.2 | Create `AttendanceReportController` | `app/Http/Controllers/Crm/AttendanceReportController.php` | Methods: `index()` (report picker with today's stats), `show(string $type)` (render report with filters), `export(string $type)` (download Excel or PDF). Permission: `view` level minimum. |
| 8.3 | Register report routes | `routes/crm/attendance.php` | `GET /reports`, `GET /reports/{type}`, `GET /reports/{type}/export`. |
| 8.4 | Build report index page | `resources/views/crm/attendance/reports/index.blade.php` | Layout per section 13.2 (Reports). Hero stats + `.crm-grid.cols-3` of report cards. Each card has icon, title, description, and "Generate" link. |
| 8.5 | Build individual report views | `resources/views/crm/attendance/reports/daily-summary.blade.php`, `monthly-register.blade.php`, `hours-worked.blade.php`, `late-arrivals.blade.php`, `absenteeism.blade.php`, `biometric-audit.blade.php` | Each has a filter card (`.crm-filter-card`) at top and a results table (`.crm-table`) below. Export button (`.btn.btn-primary`) in the card title area. |
| 8.6 | Create Excel exports | `app/Exports/AttendanceMonthlyExport.php`, `AttendanceHoursExport.php`, `AttendanceLateArrivalsExport.php`, `AttendanceAbsenteeismExport.php` | Via Maatwebsite/Excel. Each implements `FromCollection`, `WithHeadings`, `WithStyles`. |
| 8.7 | Create PDF export for monthly register | `resources/views/crm/attendance/reports/monthly-register-pdf.blade.php` | A3 landscape layout via dompdf. Department-grouped month grid matching the on-screen grid style. |
| 8.8 | Write tests | `tests/Feature/Crm/CrmAttendanceReportTest.php` | Test: each report returns correct data, filters work, Excel downloads as valid file, PDF generates without error, permission enforcement (rep cannot access reports). |

#### Acceptance Criteria for Phase 8

- AC11: Monthly register exports as Excel and PDF.
- All 6 reports render correctly with filter controls.
- Export buttons trigger file downloads.
- Report pages use `.crm-summary-hero`, `.crm-filter-card`, `.crm-table`, `.crm-grid.cols-3` patterns.

---

### Phase 9: Polish, Notifications & Integration Testing

**Goal:** Wire up all notification events, add final UI polish, ensure end-to-end flows work correctly.

**Depends on:** All previous phases.

#### Tasks

| # | Task | Files | Details |
|---|------|-------|---------|
| 9.1 | Wire all notification events | `app/Http/Controllers/Crm/AttendanceController.php`, jobs | Connect all events from section 4.10 to the existing CRM discussion/messaging system. Each notification creates an in-app message via the discussion infrastructure. |
| 9.2 | Add auto-close banner to My Attendance | `resources/views/crm/attendance/my.blade.php` | `.crm-help` warning for recent auto-closed records (already outlined in Phase 6, finalize styling). |
| 9.3 | Empty states | All attendance views | Add `.crm-empty` states for: no records in grid date range, no pending corrections, no reports generated yet, no devices registered. |
| 9.4 | Loading states | Grid page, report pages | Add skeleton loading (`.crm-card` with muted placeholder content) for slow queries. Ensure all forms have `.btn-loading` spinner behavior. |
| 9.5 | Responsive adjustments | All attendance views | Grid: horizontal scroll on mobile (`overflow-x: auto` already via `.crm-table-wrap`). Slide-over: full-width on screens < 768px. Filter grid: collapse to 1-2 columns. |
| 9.6 | Helper text boxes | Settings pages, My Attendance | Add `@include('crm.partials.helper-text')` where needed: "What are attendance codes?", "How shifts work", clock widget instructions for first-time users. |
| 9.7 | Full integration test suite | `tests/Feature/Crm/CrmAttendanceIntegrationTest.php` | End-to-end scenarios: (1) User clocks in, appears on grid, clocks out, hours calculated. (2) User forgets to clock out, overnight job closes, warning shown next day. (3) Manager overrides a record, audit logged. (4) Biometric event arrives, record created, appears on grid. (5) Holiday created, H codes backfilled. (6) Admin creates code, it appears in grid legend and dropdowns. |
| 9.8 | Permission integration tests | `tests/Feature/Crm/CrmAttendancePermissionTest.php` | Test every route against each role: rep can only see own attendance, manager scoped to department, finance is read-only, admin has full access. |

#### Acceptance Criteria for Phase 9

- AC14: All actions scoped by CRM module permission system.
- All notification events fire correctly.
- Empty states, loading states, and responsive layouts work.
- Full integration test suite passes.
- All pages consistent with CRM design system.

---

### Phase Summary

| Phase | Name | Key Deliverable | Depends On |
|---|---|---|---|
| **1** | Foundation | Database, models, config, shift resolver | - |
| **2** | Clock Engine & My Attendance | Navbar clock widget + personal dashboard | Phase 1 |
| **3** | Team Grid | 2-week attendance grid with slide-over | Phase 2 |
| **4** | Settings | Codes, shifts, holidays admin UI | Phase 1 |
| **5** | Corrections | Correction requests + approval workflow | Phase 2, 3 |
| **6** | Scheduled Jobs | Overnight close, absentee marking | Phase 2, 4 |
| **7** | Biometric Integration | API + device management | Phase 2 |
| **8** | Reports & Exports | 6 reports + Excel/PDF export | Phase 3, 6 |
| **9** | Polish & Integration | Notifications, empty states, full test suite | All |

> **Parallelism note:** Phases 4, 5, 6, and 7 can be developed in parallel after Phase 2 completes, as they have independent concerns. Phase 3 can start as soon as Phase 2's `AttendanceGridService` is functional. Phase 8 requires Phase 3 and Phase 6. Phase 9 is always last.
