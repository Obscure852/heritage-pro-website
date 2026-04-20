# Staff Attendance System - Product Requirements Document (PRD)

## 1. Executive Summary

### 1.1 Purpose
Implement a comprehensive Staff Attendance Management System for Heritage Pro College Management System to track staff clock-in/out times, daily attendance status, and integrate with the existing Leave Management system.

### 1.2 Goals
- Enable staff to self-record attendance via clock-in/clock-out
- Provide HR with oversight and correction capabilities
- Auto-integrate with Leave system (approved leave = "On Leave" status)
- Generate attendance reports and analytics
- Support standard weekday schedules (Monday-Friday)

### 1.3 Key Features
1. **Clock In/Out System** - Capture exact arrival/departure times
2. **Daily Status Summary** - Present, Absent, Late, On Leave, etc.
3. **Self-Service Portal** - Staff clock themselves in/out
4. **HR Override** - HR can edit, correct, or manually enter attendance
5. **Leave Integration** - Approved leave auto-populates attendance
6. **Reporting & Analytics** - Attendance summaries, trends, punctuality reports

---

## 2. User Roles & Permissions

| Role | Capabilities |
|------|-------------|
| **Staff (Self)** | Clock in/out, view own attendance history, view own reports |
| **Department Head** | View department attendance, generate department reports |
| **HR Administrator** | Full CRUD on all attendance, override entries, manage settings, bulk operations |
| **System Admin** | All HR capabilities + system configuration |

### 2.1 New Permissions Required
```
staff_attendance.view          - View attendance records
staff_attendance.clock         - Clock in/out (self)
staff_attendance.edit          - Edit attendance (HR)
staff_attendance.delete        - Delete attendance records (HR)
staff_attendance.reports       - Access attendance reports
staff_attendance.settings      - Manage attendance settings
```

---

## 3. Data Model

### 3.1 New Tables

#### `staff_attendances` (Main attendance records)
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PK, auto-increment | Primary key |
| user_id | bigint | FK → users, indexed | Staff member |
| date | date | indexed | Attendance date |
| clock_in | timestamp | nullable | Clock-in time |
| clock_out | timestamp | nullable | Clock-out time |
| status | enum | default: 'present' | Daily status |
| status_code_id | bigint | FK → staff_attendance_codes, nullable | Detailed status code |
| hours_worked | decimal(5,2) | nullable | Calculated hours |
| overtime_hours | decimal(5,2) | nullable, default: 0 | Overtime if applicable |
| is_late | boolean | default: false | Late arrival flag |
| late_minutes | integer | nullable | Minutes late |
| leave_request_id | bigint | FK → leave_requests, nullable | Link to leave |
| remarks | text | nullable | Notes/comments |
| recorded_by | bigint | FK → users, nullable | Who recorded (self or HR) |
| ip_address | string(45) | nullable | IP for audit |
| created_at | timestamp | | |
| updated_at | timestamp | | |

**Indexes:**
- `UNIQUE (user_id, date)` - One record per staff per day
- `INDEX (date, status)` - For daily reports
- `INDEX (user_id, date)` - For individual history

**Status Enum Values:**
- `present` - At work
- `absent` - Not at work (unexcused)
- `late` - Arrived late
- `half_day` - Worked partial day
- `on_leave` - Approved leave
- `holiday` - Public holiday
- `weekend` - Weekend (if tracked)

#### `staff_attendance_codes` (Detailed status codes)
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PK | Primary key |
| code | string(10) | unique | Short code (e.g., "P", "A", "L") |
| name | string(50) | | Display name |
| description | string(255) | nullable | Full description |
| color | string(7) | default: '#10b981' | Hex color for UI |
| counts_as_present | boolean | default: true | For attendance % calculation |
| is_active | boolean | default: true | Soft disable |
| order | integer | default: 0 | Display order |
| created_at | timestamp | | |
| updated_at | timestamp | | |

**Default Codes:**
| Code | Name | Color | Counts as Present |
|------|------|-------|-------------------|
| P | Present | #10b981 (green) | Yes |
| A | Absent | #ef4444 (red) | No |
| L | Late | #f59e0b (amber) | Yes |
| HD | Half Day | #8b5cf6 (purple) | Yes (0.5) |
| OL | On Leave | #3b82f6 (blue) | N/A (excluded) |
| SL | Sick Leave | #06b6d4 (cyan) | N/A (excluded) |
| WFH | Work From Home | #10b981 (green) | Yes |
| H | Holiday | #6366f1 (indigo) | N/A (excluded) |

#### `staff_attendance_settings` (System configuration)
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PK | Primary key |
| key | string(50) | unique | Setting key |
| value | text | | Setting value |
| type | enum | | Type: string, integer, boolean, json |
| description | string(255) | nullable | Setting description |
| created_at | timestamp | | |
| updated_at | timestamp | | |

**Default Settings:**
| Key | Value | Description |
|-----|-------|-------------|
| work_start_time | 08:00 | Standard work start time |
| work_end_time | 17:00 | Standard work end time |
| late_threshold_minutes | 15 | Minutes after start to be marked late |
| half_day_hours | 4 | Minimum hours for half-day |
| full_day_hours | 8 | Standard full day hours |
| overtime_threshold | 8 | Hours after which overtime starts |
| allow_self_clockin | true | Staff can clock themselves |
| require_clockout | true | Must clock out to complete day |
| auto_clockout_time | 23:59 | Auto clock-out if forgotten |
| geofencing_enabled | false | Require location for clock-in |

#### `staff_attendance_logs` (Audit trail)
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PK | Primary key |
| staff_attendance_id | bigint | FK, indexed | Related attendance record |
| action | enum | | create, update, delete, clock_in, clock_out |
| old_values | json | nullable | Previous values |
| new_values | json | nullable | New values |
| performed_by | bigint | FK → users | Who made the change |
| ip_address | string(45) | nullable | IP address |
| user_agent | text | nullable | Browser/device info |
| created_at | timestamp | | |

---

## 4. Integration with Existing Systems

### 4.1 Leave System Integration
When a leave request is **approved**:
1. System auto-creates `staff_attendances` records for each leave day
2. Sets `status = 'on_leave'`
3. Links via `leave_request_id`
4. These records are read-only (cannot be edited)

**Implementation:**
- Add listener to `LeaveRequestApproved` event
- Create attendance records for date range
- Exclude weekends and public holidays

### 4.2 Public Holidays Integration
- Reuse existing `PublicHoliday` model from Leave system
- Auto-mark holidays as `status = 'holiday'`
- Exclude from attendance requirements

### 4.3 User Model Integration
Add relationship to User model:
```php
public function staffAttendances(): HasMany
{
    return $this->hasMany(StaffAttendance::class);
}
```

---

## 5. Core Features

### 5.1 Clock In/Out
**Staff Self-Service:**
- Dashboard widget showing clock status
- One-click clock in/out
- View today's status
- Optional: Capture IP address for audit

**Clock In Rules:**
- Can only clock in once per day
- Clock in creates attendance record with `clock_in` timestamp
- If after `work_start_time + late_threshold`, mark `is_late = true`

**Clock Out Rules:**
- Can only clock out if clocked in
- Calculates `hours_worked` on clock out
- If `hours_worked < half_day_hours`, mark as `half_day`

### 5.2 HR Management Console
**Attendance Register View:**
- Calendar/grid view (similar to student attendance)
- Filter by department, date range, status
- Bulk marking capabilities
- Quick status toggle

**Individual Staff View:**
- Monthly attendance summary
- Clock in/out history
- Edit capabilities with audit log

**Bulk Operations:**
- Mark all present for a date
- Import attendance from CSV
- Correct multiple records

### 5.3 Reporting & Analytics

**Standard Reports:**
1. **Daily Attendance Report** - All staff status for a date
2. **Monthly Summary** - Per staff attendance percentage
3. **Department Report** - Attendance by department
4. **Punctuality Report** - Late arrivals analysis
5. **Absenteeism Report** - Absence patterns
6. **Hours Worked Report** - Total hours per staff

**Dashboard Widgets:**
- Today's attendance overview (present/absent/late counts)
- Monthly attendance trend chart
- Top punctual staff
- Attendance alerts (consecutive absences)

---

## 6. User Interface

### 6.1 Design System (Match Student Edit Page Theme)

All views must follow the existing design patterns from `/resources/views/students/edit.blade.php`:

#### Layout Structure
```blade
@extends('layouts.master')
@section('title') Staff Attendance @endsection
@section('css')
    <!-- Custom styles matching student edit page -->
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1') <a href="#">Human Resources</a> @endslot
        @slot('title') Staff Attendance @endslot
    @endcomponent

    <div class="form-container">
        <!-- Page Header -->
        <!-- Tabs -->
        <!-- Content -->
        <!-- Form Actions -->
    </div>
@endsection
```

#### Core CSS Classes to Use
| Component | Classes | Description |
|-----------|---------|-------------|
| Container | `.form-container` | White box with shadow, 32px padding, 3px radius |
| Page Header | `.page-header`, `.page-title` | Flex layout, border-bottom |
| Tabs | `.form-tabs`, `.nav-tabs`, `.nav-link` | Blue active border (#3b82f6) |
| Form Grid | `.form-grid` | 3-col desktop, 2-col tablet, 1-col mobile |
| Form Group | `.form-group`, `.form-label`, `.form-control` | Standard form styling |
| Section Title | `.section-title` | 16px bold, #1f2937 |
| Info Card | `.info-card`, `.info-item` | Read-only display boxes |
| Status Badge | `.status-badge`, `.status-active` | Colored pills |
| Buttons | `.btn`, `.btn-primary`, `.btn-secondary` | Gradient primary, icon support |
| Form Actions | `.form-actions`, `.form-actions-left/right` | Footer button layout |
| Meta Info | `.meta-info`, `.meta-info-row` | Gray footer with timestamps |
| Help Text | `.help-text`, `.help-title`, `.help-content` | Blue left-border info box |

#### Color Scheme
```css
/* Primary */
--primary-blue: #3b82f6;
--primary-dark: #2563eb;

/* Status Colors */
--success: #10b981;  /* Present */
--danger: #dc2626;   /* Absent */
--warning: #f59e0b;  /* Late */
--info: #3b82f6;     /* On Leave */
--purple: #8b5cf6;   /* Half Day */

/* Text */
--text-primary: #1f2937;
--text-secondary: #6b7280;

/* Backgrounds */
--bg-light: #f9fafb;
--border: #e5e7eb;
```

#### Status Badges for Attendance
```blade
<span class="status-badge status-present">Present</span>   <!-- Green -->
<span class="status-badge status-absent">Absent</span>     <!-- Red -->
<span class="status-badge status-late">Late</span>         <!-- Amber -->
<span class="status-badge status-leave">On Leave</span>    <!-- Blue -->
<span class="status-badge status-halfday">Half Day</span>  <!-- Purple -->
```

### 6.2 View Templates

#### A. Main Register View (index.blade.php)
**Tabbed Interface:**
1. **Attendance Register** (fa-calendar-check) - Grid view
2. **Manual Entry** (fa-edit) - HR manual entry form
3. **Reports** (fa-chart-bar) - Quick reports access
4. **Settings** (fa-cog) - Attendance codes & settings

**Register Grid Structure:**
```blade
<div class="form-container">
    <div class="page-header">
        <h4 class="page-title">Staff Attendance Register</h4>
        <div class="d-flex gap-2">
            <!-- Date picker, Department filter -->
        </div>
    </div>

    <!-- Stats Cards Row -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="info-card" style="background: linear-gradient(135deg, #10b981, #059669);">
                <div class="credit-amount">{{ $presentCount }}</div>
                <div class="credit-label">Present Today</div>
            </div>
        </div>
        <!-- Absent, Late, On Leave cards -->
    </div>

    <!-- Attendance Grid Table -->
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Staff Member</th>
                    <th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th>
                </tr>
            </thead>
            <tbody>
                <!-- Clickable cells with status codes -->
            </tbody>
        </table>
    </div>

    <div class="form-actions">
        <div class="form-actions-left">
            <button class="btn btn-secondary">Previous Week</button>
        </div>
        <div class="form-actions-right">
            <button class="btn btn-primary">Save Changes</button>
        </div>
    </div>
</div>
```

#### B. Clock Widget (Partial)
```blade
<div class="card">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="card-title mb-0">
                <i class="fas fa-clock text-primary me-2"></i>Today's Attendance
            </h5>
            <span class="status-badge status-{{ $status }}">{{ ucfirst($status) }}</span>
        </div>

        <div class="info-card mb-3">
            <div class="info-item">
                <strong>Work Hours:</strong> 08:00 - 17:00
            </div>
            @if($clockedIn)
            <div class="info-item">
                <strong>Clock In:</strong> {{ $clockInTime }}
            </div>
            <div class="info-item">
                <strong>Hours Worked:</strong> {{ $hoursWorked }}
            </div>
            @endif
        </div>

        @if(!$clockedIn)
            <button class="btn btn-success w-100" onclick="clockIn()">
                <i class="fas fa-sign-in-alt me-2"></i>Clock In
            </button>
        @else
            <button class="btn btn-danger w-100" onclick="clockOut()">
                <i class="fas fa-sign-out-alt me-2"></i>Clock Out
            </button>
        @endif
    </div>
</div>
```

#### C. Staff Attendance Detail View
Uses accordion pattern for historical records:
```blade
<div class="accordion" id="attendanceHistory">
    @foreach($months as $month)
    <div class="accordion-item mb-2">
        <h2 class="accordion-header">
            <button class="accordion-button" data-bs-toggle="collapse" data-bs-target="#month{{ $loop->index }}">
                {{ $month->format('F Y') }}
                <span class="badge bg-success ms-2">{{ $presentDays }} days</span>
            </button>
        </h2>
        <div id="month{{ $loop->index }}" class="accordion-collapse collapse">
            <div class="accordion-body">
                <!-- Daily attendance records -->
            </div>
        </div>
    </div>
    @endforeach
</div>
```

### 6.3 Navigation
Add to sidebar under "Human Resources":
```
Human Resources
  ├── Staff Directory
  ├── Staff Attendance    ← NEW
  │     ├── Register
  │     ├── My Attendance (self-service)
  │     ├── Reports
  │     └── Settings
  ├── Leave Manager
  └── ...
```

---

## 7. API Endpoints

### 7.1 Staff Self-Service
```
POST   /staff-attendance/clock-in          Clock in
POST   /staff-attendance/clock-out         Clock out
GET    /staff-attendance/today             Get today's status
GET    /staff-attendance/my-history        Get own history
```

### 7.2 HR Management
```
GET    /staff-attendance                   List all (with filters)
GET    /staff-attendance/{id}              Get single record
POST   /staff-attendance                   Create record (HR)
PUT    /staff-attendance/{id}              Update record (HR)
DELETE /staff-attendance/{id}              Delete record (HR)
GET    /staff-attendance/register          Attendance register view
POST   /staff-attendance/bulk-update       Bulk update records
```

### 7.3 Reports
```
GET    /staff-attendance/reports/daily     Daily report
GET    /staff-attendance/reports/monthly   Monthly summary
GET    /staff-attendance/reports/department Department report
GET    /staff-attendance/reports/export    Export to Excel/PDF
```

### 7.4 Settings
```
GET    /staff-attendance/settings          Get settings
PUT    /staff-attendance/settings          Update settings
GET    /staff-attendance/codes             List status codes
POST   /staff-attendance/codes             Create status code
PUT    /staff-attendance/codes/{id}        Update status code
```

---

## 8. Technical Implementation

### 8.1 Files to Create

**Models:**
- `app/Models/StaffAttendance.php`
- `app/Models/StaffAttendanceCode.php`
- `app/Models/StaffAttendanceSetting.php`
- `app/Models/StaffAttendanceLog.php`

**Controllers:**
- `app/Http/Controllers/StaffAttendance/StaffAttendanceController.php`
- `app/Http/Controllers/StaffAttendance/StaffAttendanceClockController.php`
- `app/Http/Controllers/StaffAttendance/StaffAttendanceReportController.php`
- `app/Http/Controllers/StaffAttendance/StaffAttendanceSettingsController.php`

**Services:**
- `app/Services/StaffAttendance/StaffAttendanceService.php`
- `app/Services/StaffAttendance/StaffAttendanceReportService.php`

**Policies:**
- `app/Policies/StaffAttendancePolicy.php`

**Form Requests:**
- `app/Http/Requests/StaffAttendance/ClockInRequest.php`
- `app/Http/Requests/StaffAttendance/StoreAttendanceRequest.php`
- `app/Http/Requests/StaffAttendance/UpdateAttendanceRequest.php`

**Events & Listeners:**
- `app/Listeners/CreateLeaveAttendanceRecords.php` (listens to LeaveRequestApproved)

**Migrations:**
- `database/migrations/xxxx_create_staff_attendance_codes_table.php`
- `database/migrations/xxxx_create_staff_attendances_table.php`
- `database/migrations/xxxx_create_staff_attendance_settings_table.php`
- `database/migrations/xxxx_create_staff_attendance_logs_table.php`

**Seeders:**
- `database/seeders/StaffAttendanceCodeSeeder.php`
- `database/seeders/StaffAttendanceSettingSeeder.php`

**Views:**
- `resources/views/staff-attendance/index.blade.php` (register)
- `resources/views/staff-attendance/clock-widget.blade.php`
- `resources/views/staff-attendance/reports/` (report views)
- `resources/views/staff-attendance/settings.blade.php`

**Routes:**
- `routes/staff-attendance/staff-attendance.php`

### 8.2 Follow Existing Patterns
- Use `upsert()` for bulk attendance updates (like student attendance)
- Follow thin controller / fat service pattern
- Use policies for authorization
- Add activity logging for audit trail
- Use transactions for multi-step operations

---

## 9. Security & Compliance

### 9.1 Data Protection
- Attendance data is PII - apply appropriate access controls
- IP addresses logged for audit purposes
- All changes logged in `staff_attendance_logs`

### 9.2 Audit Requirements
- Track who created/modified each record
- Maintain full change history
- Cannot hard-delete attendance records (soft delete or archive)

### 9.3 Access Control
- Staff can only view/edit own attendance
- Department heads can view department only
- HR has full access
- Use existing Spatie Permission system

---

## 10. Success Metrics

| Metric | Target |
|--------|--------|
| Clock-in adoption rate | >90% of staff using self clock-in |
| Data accuracy | <2% HR corrections needed |
| Report generation time | <5 seconds |
| Leave integration accuracy | 100% approved leaves reflected |

---

## 11. Implementation Phases

### Phase 1: Core Foundation
- Database migrations and models
- Basic CRUD operations
- HR attendance register view
- Manual attendance entry

### Phase 2: Self-Service
- Staff clock in/out
- Dashboard widget
- Personal attendance history

### Phase 3: Integration
- Leave system integration
- Public holiday handling
- Auto-population features

### Phase 4: Reporting
- Standard reports
- Export functionality
- Dashboard analytics

### Phase 5: Polish & Testing
- UI refinements
- Performance optimization
- Comprehensive testing
- Documentation

---

## 12. Dependencies

### Existing Systems Used:
- User model and authentication
- Leave Management system (for integration)
- Public Holidays (from Leave system)
- Department model
- Spatie Permission package
- Activity logging

### No External Dependencies Required
- Uses existing Laravel stack
- No new packages needed

---

## 13. Device Integration (Clocking Machines)

### 13.1 Overview
The Staff Attendance System can integrate with external clocking devices for automated attendance capture. This provides more reliable attendance data and reduces manual entry.

### 13.2 Supported Device Types

#### A. Biometric Devices (Fingerprint/Face Recognition)
**Popular Brands:**
- ZKTeco (ZK Series) - Most common in Africa
- Hikvision
- Suprema
- FingerTec

**Integration Methods:**
1. **Push API** - Device pushes data to our webhook endpoint
2. **Pull API** - System polls device for new records
3. **SDK Integration** - Direct library integration

**Database Addition for Device Records:**
```sql
CREATE TABLE biometric_devices (
    id BIGINT PRIMARY KEY,
    name VARCHAR(100),
    serial_number VARCHAR(100) UNIQUE,
    ip_address VARCHAR(45),
    port INT DEFAULT 4370,
    device_type ENUM('fingerprint', 'face', 'card', 'hybrid'),
    location VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    last_sync_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE biometric_enrollments (
    id BIGINT PRIMARY KEY,
    user_id BIGINT REFERENCES users(id),
    device_id BIGINT REFERENCES biometric_devices(id),
    template_data BLOB,          -- Fingerprint/face template
    enrollment_date TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE device_attendance_logs (
    id BIGINT PRIMARY KEY,
    device_id BIGINT REFERENCES biometric_devices(id),
    user_id BIGINT REFERENCES users(id),
    punch_time TIMESTAMP,
    punch_type ENUM('in', 'out', 'break_start', 'break_end'),
    verification_method ENUM('fingerprint', 'face', 'card', 'password'),
    raw_data JSON,               -- Original device payload
    synced_to_attendance BOOLEAN DEFAULT FALSE,
    staff_attendance_id BIGINT REFERENCES staff_attendances(id),
    created_at TIMESTAMP
);
```

#### B. RFID/Proximity Card Readers
**How it works:**
- Staff are issued RFID cards/tags
- Tap card on reader to clock in/out
- Reader sends card ID to system
- System matches card to staff member

**Database Addition:**
```sql
ALTER TABLE users ADD COLUMN rfid_card_number VARCHAR(50) UNIQUE;
```

#### C. Mobile Device (GPS-based)
**Features:**
- Clock in/out via mobile app or web
- GPS location capture for verification
- Geofencing to restrict to campus area
- Photo capture for verification (optional)

**Database Addition:**
```sql
ALTER TABLE staff_attendances ADD COLUMN (
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    location_accuracy DECIMAL(6, 2),
    photo_path VARCHAR(255)
);
```

### 13.3 ZKTeco Integration (Recommended)

ZKTeco devices are widely used and have good API support.

#### Integration Architecture
```
┌─────────────────┐     ┌──────────────────┐     ┌─────────────────┐
│  ZKTeco Device  │────▶│  Webhook/Sync    │────▶│  Staff          │
│  (Fingerprint)  │     │  Service         │     │  Attendance     │
└─────────────────┘     └──────────────────┘     └─────────────────┘
                              │
                              ▼
                        ┌──────────────┐
                        │  Device Logs │
                        │  (Raw Data)  │
                        └──────────────┘
```

#### Webhook Endpoint
```php
// routes/api.php
Route::post('/webhook/biometric/{device_serial}', [BiometricWebhookController::class, 'receive'])
    ->middleware('verify.device.signature');

// Controller
class BiometricWebhookController extends Controller
{
    public function receive(Request $request, string $deviceSerial)
    {
        // 1. Validate device exists
        $device = BiometricDevice::where('serial_number', $deviceSerial)->firstOrFail();

        // 2. Log raw data
        $log = DeviceAttendanceLog::create([
            'device_id' => $device->id,
            'user_id' => $this->matchUser($request->user_id),
            'punch_time' => Carbon::parse($request->timestamp),
            'punch_type' => $request->type,
            'verification_method' => $request->method,
            'raw_data' => $request->all(),
        ]);

        // 3. Queue job to process into staff attendance
        ProcessDeviceAttendance::dispatch($log);

        return response()->json(['status' => 'received']);
    }
}
```

#### Sync Service (for Pull-based devices)
```php
class BiometricSyncService
{
    public function syncDevice(BiometricDevice $device): int
    {
        // Connect to device via SDK/API
        $zk = new ZKTeco($device->ip_address, $device->port);
        $zk->connect();

        // Get attendance logs since last sync
        $logs = $zk->getAttendance();

        $processed = 0;
        foreach ($logs as $log) {
            // Create device attendance log
            // Queue processing job
            $processed++;
        }

        $device->update(['last_sync_at' => now()]);

        return $processed;
    }
}
```

#### Processing Device Logs into Attendance
```php
class ProcessDeviceAttendance implements ShouldQueue
{
    public function handle(DeviceAttendanceLog $log): void
    {
        $date = $log->punch_time->toDateString();

        // Find or create attendance record for the day
        $attendance = StaffAttendance::firstOrCreate(
            ['user_id' => $log->user_id, 'date' => $date],
            ['status' => 'present', 'recorded_by' => null] // Device recorded
        );

        // Update clock in/out based on punch type
        if ($log->punch_type === 'in' && !$attendance->clock_in) {
            $attendance->clock_in = $log->punch_time;
            $attendance->checkLateness();
        } elseif ($log->punch_type === 'out') {
            $attendance->clock_out = $log->punch_time;
            $attendance->calculateHoursWorked();
        }

        $attendance->save();

        // Mark log as synced
        $log->update([
            'synced_to_attendance' => true,
            'staff_attendance_id' => $attendance->id,
        ]);
    }
}
```

### 13.4 Settings for Device Integration

Add to `staff_attendance_settings`:
| Key | Value | Description |
|-----|-------|-------------|
| device_integration_enabled | false | Enable device integration |
| device_sync_interval | 5 | Minutes between sync attempts |
| device_timezone | Africa/Gaborone | Device timezone |
| allow_manual_override | true | HR can override device records |
| require_both_punches | true | Require both in and out punches |
| auto_match_punches | true | Auto-pair in/out punches |
| max_work_hours | 12 | Flag if hours exceed this |

### 13.5 Device Management UI

**Admin Settings Tab:**
```
Settings → Devices
  ├── Add Device (IP, Port, Type, Location)
  ├── Test Connection
  ├── Sync Now (manual trigger)
  ├── View Sync History
  └── Device Status Dashboard
```

**Staff Enrollment:**
```
Staff Edit Page → Biometric Tab
  ├── Enroll Fingerprint (requires device connection)
  ├── Assign RFID Card
  ├── View Enrollment Status
  └── Re-enroll / Remove
```

### 13.6 API Endpoints for Devices

```
# Device Management (Admin)
GET    /staff-attendance/devices              List all devices
POST   /staff-attendance/devices              Add new device
PUT    /staff-attendance/devices/{id}         Update device
DELETE /staff-attendance/devices/{id}         Remove device
POST   /staff-attendance/devices/{id}/sync    Manual sync trigger
GET    /staff-attendance/devices/{id}/logs    View device logs

# Webhook (Device Push)
POST   /api/webhook/biometric/{serial}        Receive device data

# Staff Enrollment
POST   /staff/{id}/biometric/enroll           Enroll staff
DELETE /staff/{id}/biometric/enrollment       Remove enrollment
GET    /staff/{id}/biometric/status           Check enrollment status
```

### 13.7 Implementation Phases for Device Integration

**Phase 1: Foundation**
- Database migrations for device tables
- Basic device management CRUD
- Manual sync capability

**Phase 2: Webhook Integration**
- Webhook endpoint for push-based devices
- Queue processing for attendance records
- Conflict resolution logic

**Phase 3: SDK Integration**
- ZKTeco SDK integration
- Fingerprint enrollment from web
- Real-time sync

**Phase 4: Advanced Features**
- Multi-device support
- Failover handling
- Offline sync queue
- Analytics dashboard

---

## 14. Open Questions

1. ~~**Geofencing** - Should staff only be able to clock in from campus?~~ (Addressed in Mobile Device section)
2. ~~**Biometric Integration** - Future integration with fingerprint/face recognition?~~ (Addressed in Section 13)
3. **Overtime Approval** - Should overtime require manager approval?
4. **Remote Work** - How to handle work-from-home attendance?
5. **Device Budget** - What is the budget for clocking devices?
6. **Device Locations** - How many entry points need devices?
7. **Existing Devices** - Are there any existing clocking devices to integrate with?

---

## 15. Appendix

### A. Existing Codebase References

**Student Attendance (Reference Pattern):**
- Model: `/app/Models/Attendance.php`
- Controller: `/app/Http/Controllers/AttendanceController.php`
- Routes: `/routes/attendance/attendance.php`

**User/Staff System:**
- Model: `/app/Models/User.php`
- Controller: `/app/Http/Controllers/UserController.php`
- Routes: `/routes/staff/users.php`

**Leave System:**
- Models: `/app/Models/Leave/`
- Controllers: `/app/Http/Controllers/Leave/`
- Routes: `/routes/leave/leave.php`

---

## 16. Setup & Configuration Guide

This section details the steps administrators must take to enable and configure the Staff Attendance System features.

### 16.1 Queue Worker Setup (Required)

The Leave Integration and Biometric Webhook features use **queued jobs** to process data in the background, preventing slow response times and avoiding traffic bottlenecks.

#### Queue Configuration

**1. Configure Queue Driver (`.env`):**
```env
QUEUE_CONNECTION=database   # or redis for production
```

**2. Create Queue Tables (if using database driver):**
```bash
php artisan queue:table
php artisan migrate
```

**3. Start Queue Worker:**
```bash
# Development
php artisan queue:work --queue=default,attendance

# Production (with Supervisor)
php artisan queue:work --queue=default,attendance --sleep=3 --tries=3 --max-time=3600
```

#### Supervisor Configuration (Production)

Create `/etc/supervisor/conf.d/attendance-worker.conf`:
```ini
[program:attendance-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/college/artisan queue:work --queue=default,attendance --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/college/storage/logs/worker.log
stopwaitsecs=3600
```

Then run:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start attendance-worker:*
```

#### Laravel Horizon (Recommended for Production)

If using Horizon for queue management:

```php
// config/horizon.php
'environments' => [
    'production' => [
        'supervisor-attendance' => [
            'connection' => 'redis',
            'queue' => ['attendance', 'default'],
            'balance' => 'auto',
            'processes' => 3,
            'tries' => 3,
            'timeout' => 300,
        ],
    ],
],
```

### 16.2 Leave Integration Setup

The Leave Integration automatically creates attendance records when leave is approved.

#### How It Works
1. HR/Manager approves a leave request
2. System fires `LeaveRequestApproved` event
3. `CreateLeaveAttendanceRecords` listener (queued) catches the event
4. Creates attendance records for each leave day (excluding weekends/holidays)

#### Files Involved
| File | Purpose |
|------|---------|
| `app/Events/Leave/LeaveRequestApproved.php` | Event fired on leave approval |
| `app/Listeners/StaffAttendance/CreateLeaveAttendanceRecords.php` | Listener that creates records |
| `app/Providers/EventServiceProvider.php` | Event-listener registration |

#### Verification Steps
1. Ensure the listener is registered in `EventServiceProvider.php`:
```php
protected $listen = [
    \App\Events\Leave\LeaveRequestApproved::class => [
        \App\Listeners\StaffAttendance\CreateLeaveAttendanceRecords::class,
    ],
];
```

2. Ensure queue worker is running (see 16.1)

3. Test by approving a leave request and checking:
   - Queue job processes successfully: `php artisan queue:work --once`
   - Attendance records created in `staff_attendances` table

#### Troubleshooting
| Issue | Solution |
|-------|----------|
| Records not created | Check queue worker is running |
| Wrong leave code | Verify `staff_attendance_codes` has 'OL' and 'SL' codes |
| Weekends included | Check `isWeekend()` logic in listener |
| Holidays included | Verify `PublicHoliday` model exists with active holidays |

### 16.3 Biometric Webhook Setup

The webhook endpoints allow biometric devices to push attendance data directly to the system.

#### Webhook Endpoints
| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/api/webhook/biometric/{deviceSerial}` | POST | Receive punch data (queued processing) |
| `/api/webhook/biometric/{deviceSerial}/process` | POST | Receive AND immediately process |
| `/api/webhook/biometric/{deviceSerial}/heartbeat` | POST | Device health check |

#### Device Configuration Steps

**1. Add Device in System:**
- Navigate to: Staff Attendance → Settings → Biometric Devices
- Click "Add Device"
- Enter:
  - **Name:** e.g., "Main Entrance Scanner"
  - **Serial Number:** Device serial (used in webhook URL)
  - **IP Address:** Device IP for SDK sync
  - **Port:** Default 4370 for ZKTeco
  - **Device Type:** fingerprint, face, card, or hybrid
  - **Location:** e.g., "Admin Building Entrance"

**2. Configure Device to Push Data:**

On the biometric device admin panel (e.g., ZKTeco web interface):
```
Settings → Communication → Push Settings
  Server URL: https://your-domain.com/api/webhook/biometric/DEVICE_SERIAL
  Method: POST
  Content-Type: application/json
```

**3. Enroll Staff on Device:**
- Go to: Staff → Edit → Biometric Tab
- Add enrollment with:
  - **Device:** Select the registered device
  - **Device User ID:** The ID assigned on the physical device
  - **Type:** fingerprint, face, card
  - **Finger:** (for fingerprint) which finger enrolled

#### Payload Formats Supported

The webhook accepts multiple payload formats:

**Format 1: Single Punch**
```json
{
  "user_id": "42",
  "timestamp": "2025-12-27T08:05:23",
  "type": 0,
  "verify": 1
}
```

**Format 2: Array of Records**
```json
{
  "records": [
    {"uid": "42", "time": "08:05:23", "status": 0},
    {"uid": "43", "time": "08:10:15", "status": 0}
  ]
}
```

**Format 3: ZKTeco Format**
```json
{
  "log": [
    {"pin": "42", "punch_time": "2025-12-27 08:05:23", "verify": 1}
  ]
}
```

#### Punch Type Codes
| Code | Meaning |
|------|---------|
| 0 or 255 | Clock In |
| 1 | Clock Out |
| 2 | Break Start |
| 3 | Break End |
| 4 | Overtime Start |
| 5 | Overtime End |

#### Verification Method Codes
| Code | Method |
|------|--------|
| 0 | Password |
| 1 | Fingerprint |
| 2 | Card/RFID |
| 15 | Face |

### 16.4 Scheduled Jobs Setup

Add the following to your scheduler for automated processing:

**`app/Console/Kernel.php`:**
```php
protected function schedule(Schedule $schedule): void
{
    // Process pending device logs every 5 minutes
    $schedule->job(new \App\Jobs\StaffAttendance\ProcessPendingDeviceLogs)
        ->everyFiveMinutes()
        ->withoutOverlapping();

    // Mark holidays in attendance at 6 AM daily
    $schedule->job(new \App\Jobs\StaffAttendance\MarkHolidayAttendanceJob)
        ->dailyAt('06:00')
        ->withoutOverlapping();

    // Auto clock-out forgotten punches at midnight
    $schedule->job(new \App\Jobs\StaffAttendance\AutoClockOutJob)
        ->dailyAt('23:59')
        ->withoutOverlapping();

    // Sync pull-based devices every 5 minutes
    $schedule->job(new \App\Jobs\Biometric\SyncBiometricDevicesJob)
        ->everyFiveMinutes()
        ->withoutOverlapping();
}
```

**Ensure cron is configured:**
```bash
* * * * * cd /var/www/college && php artisan schedule:run >> /dev/null 2>&1
```

### 16.5 Admin Configuration Checklist

| Step | Action | Location |
|------|--------|----------|
| 1 | Run migrations | `php artisan migrate` |
| 2 | Seed attendance codes | `php artisan db:seed --class=StaffAttendanceCodeSeeder` |
| 3 | Seed default settings | `php artisan db:seed --class=StaffAttendanceSettingSeeder` |
| 4 | Add permissions to roles | Admin → Roles → Add `staff_attendance.*` permissions |
| 5 | Configure work hours | Staff Attendance → Settings → Work Hours |
| 6 | Configure late threshold | Staff Attendance → Settings → Late Threshold |
| 7 | Add biometric devices | Staff Attendance → Settings → Devices → Add |
| 8 | Enroll staff on devices | Staff → Edit → Biometric Tab |
| 9 | Start queue worker | `php artisan queue:work` or Supervisor |
| 10 | Verify cron running | `crontab -l` |

### 16.6 Testing the Setup

**Test Leave Integration:**
```bash
# Create test leave request and approve it
php artisan tinker
>>> $leave = \App\Models\Leave\LeaveRequest::find(1);
>>> event(new \App\Events\Leave\LeaveRequestApproved($leave));
>>> \App\Models\StaffAttendance\StaffAttendance::where('leave_request_id', 1)->get();
```

**Test Webhook Endpoint:**
```bash
# Test with curl
curl -X POST https://your-domain.com/api/webhook/biometric/ABC123456 \
  -H "Content-Type: application/json" \
  -d '{"user_id": "1", "timestamp": "2025-12-27T08:00:00", "type": 0}'
```

**Check Queue Processing:**
```bash
# View failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Monitor queue in real-time
php artisan queue:listen --queue=attendance
```

### 16.7 Troubleshooting Common Issues

| Issue | Cause | Solution |
|-------|-------|----------|
| Webhook returns 404 | Device not registered | Add device in Settings → Devices |
| Webhook returns 404 | Wrong serial number | Check serial matches exactly |
| Punches not creating attendance | Queue not running | Start queue worker |
| User not matched | Enrollment missing | Add enrollment in Staff → Biometric Tab |
| Duplicate records | Device sending same punch | System auto-skips duplicates (check logs) |
| Leave records not created | Event not firing | Verify `LeaveRequestApproved` event is dispatched |
| Late detection wrong | Timezone mismatch | Check `APP_TIMEZONE` in `.env` |

### 16.8 Log Files

Monitor these logs for issues:

| Log | Location | Purpose |
|-----|----------|---------|
| Laravel Log | `storage/logs/laravel.log` | General application errors |
| Queue Worker | `storage/logs/worker.log` | Queue processing issues |
| Failed Jobs | `failed_jobs` table | Jobs that failed after retries |

**Enable detailed logging for debugging:**
```php
// .env
LOG_LEVEL=debug
```
