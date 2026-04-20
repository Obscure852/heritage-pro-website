# Heritage Junior Secondary School Management System
## Comprehensive System Overview & Development Investment Analysis

**Document Version:** 1.0
**Last Updated:** February 2026
**System Type:** Enterprise School Management Platform

---

## Executive Summary

The Heritage Junior Secondary School Management System is a comprehensive Laravel 9.x web application designed to manage all aspects of school operations for multi-level educational institutions (Senior, Junior/CJSS, Primary, and Reception/Pre-school).

### System Scale
| Metric | Count |
|--------|-------|
| Eloquent Models | 254 |
| HTTP Controllers | 63 |
| Service Classes | 30+ |
| Route Files | 33 |
| View Directories | 37 |
| Excel Export Classes | 57+ |

### Total Estimated Development Investment
| Metric | Value |
|--------|-------|
| Total Development Time | 42-58 person-months |
| Total Development Cost | $571,200 - $788,800 USD |
| Average Cost | ~$680,000 USD |

---

## Technology Stack

| Component | Technology | Purpose |
|-----------|------------|---------|
| Backend Framework | Laravel 9.x (PHP 8.0+) | Core application logic |
| Frontend | Blade, Bootstrap 5, Vite | User interface |
| Database | MySQL/PostgreSQL | Data persistence |
| PDF Generation | DomPDF | Report cards, invoices |
| Excel Processing | Maatwebsite/Excel | Import/export operations |
| Charts | ApexCharts | Data visualization |
| Tables | DataTables | Interactive data tables |
| Calendar | FullCalendar | Schedule views |
| Editor | CKEditor 5 | Rich text editing |
| File Upload | Dropzone | Document uploads |
| Biometric | Hikvision/ZKTeco SDK | Attendance integration |
| Queue System | Laravel Queues | Background processing |

---

## Core Modules

### 1. Fee Management Module

**Purpose:** Complete financial management for student fees, invoicing, payments, discounts, refunds, and financial reporting.

#### Features
- Fee type and structure configuration per grade/year
- Student invoice generation with line items
- Payment recording with multiple payment methods
- Payment plan/installment management
- Discount application (percentage/fixed)
- Refund processing
- Fee balance carryover between academic years
- Student clearance management
- Comprehensive audit trail
- Financial reporting and analytics

#### Technical Metrics
| Component | Count |
|-----------|-------|
| Services | 9 |
| Models | 17 |
| Controllers | 8 |
| Migrations | 15+ |

#### Key Files
```
app/Services/Fee/
├── FeeStructureService.php
├── PaymentService.php
├── InvoiceService.php
├── BalanceService.php
├── RefundService.php
├── DiscountService.php
├── PaymentPlanService.php
├── ReportingService.php
└── FeeAuditService.php
```

#### Development Estimates
| Metric | Value |
|--------|-------|
| Complexity | Complex |
| Development Time | 5-7 person-months |
| Development Cost | $68,000 - $95,200 USD |

**Justification:** Financial modules require precise decimal arithmetic, audit compliance, transaction safety, multiple payment workflows, and extensive reporting. Integration with external accounting concepts adds complexity.

---

### 2. Leave Management Module

**Purpose:** Staff leave requests, approvals, balance tracking, policy management, and leave analytics.

#### Features
- Leave request submission and tracking
- Multi-level approval workflows (manager/HR)
- Leave balance management
- Leave type configuration (annual, sick, maternity, etc.)
- Leave policy rules and eligibility
- Public holiday calendar integration
- Team leave calendar visibility
- Working days calculation (excluding holidays)
- Leave audit trails
- Leave reports and analytics

#### Technical Metrics
| Component | Count |
|-----------|-------|
| Services | 10 |
| Models | 11 |
| Controllers | 7 |
| Events | 3 |

#### Key Files
```
app/Services/Leave/
├── LeaveRequestService.php
├── LeaveApprovalService.php
├── LeaveBalanceService.php
├── LeaveCalculationService.php
├── LeavePolicyService.php
├── LeaveTypeService.php
├── LeaveReportService.php
├── LeaveNotificationService.php
├── LeaveAuditService.php
└── PublicHolidayService.php
```

#### Development Estimates
| Metric | Value |
|--------|-------|
| Complexity | Medium-Complex |
| Development Time | 3-4 person-months |
| Development Cost | $40,800 - $54,400 USD |

**Justification:** Workflow-based system with approval chains, balance calculations, date logic (working days), and policy engine. Requires integration with notifications and attendance systems.

---

### 3. Staff Attendance Module

**Purpose:** Track staff attendance via biometric devices and manual entry, with comprehensive reporting.

#### Features
- Biometric device integration (Hikvision/ZKTeco)
- Raw biometric event processing pipeline
- Employee-to-device ID mapping
- Clock in/out recording
- Self-service attendance entry
- Manual attendance correction
- Leave-attendance correlation
- Public holiday handling
- Manager team views
- Attendance reports and dashboards
- Device synchronization and logging

#### Technical Metrics
| Component | Count |
|-----------|-------|
| Services | 15 |
| Models | 9 |
| Controllers | 3 |

#### Key Files
```
app/Services/StaffAttendance/
├── AttendanceRecordService.php
├── AttendanceProcessingService.php
├── BiometricEventService.php
├── DeviceSyncService.php
├── ManualAttendanceService.php
├── SelfServiceClockService.php
├── StaffMappingService.php
├── AttendanceDashboardService.php
├── ReportService.php
├── StaffAttendanceAuditService.php
├── LeaveAttendanceCorrelationService.php
└── PublicHolidayAttendanceService.php
```

#### Development Estimates
| Metric | Value |
|--------|-------|
| Complexity | Complex |
| Development Time | 4-6 person-months |
| Development Cost | $54,400 - $81,600 USD |

**Justification:** Hardware integration with biometric devices, real-time event processing pipeline, device protocol handling, race condition management for concurrent punches, and complex correlation logic with leave system.

---

### 4. Welfare & Student Support Module

**Purpose:** Manage student welfare cases, safeguarding concerns, discipline, counseling, and health incidents.

#### Features
- Welfare case management
- Safeguarding concern tracking (child protection)
- Disciplinary record management
- Counseling session scheduling
- Health incident reporting
- Intervention plan creation and tracking
- Parent communication logging
- Case notes and attachments
- Welfare audit trails
- Reporting and analytics

#### Technical Metrics
| Component | Count |
|-----------|-------|
| Services | 7 |
| Models | 14 |
| Controllers | 7 |

#### Key Files
```
app/Services/Welfare/
├── WelfareCaseService.php
├── DisciplinaryService.php
├── CounselingService.php
├── SafeguardingService.php
├── HealthIncidentService.php
├── WelfareReportingService.php
└── WelfareAuditService.php
```

#### Development Estimates
| Metric | Value |
|--------|-------|
| Complexity | Medium |
| Development Time | 3-4 person-months |
| Development Cost | $40,800 - $54,400 USD |

**Justification:** Case management system with multiple case types, attachment handling, sensitive data protection requirements, and compliance audit trails. Relatively straightforward CRUD with workflow elements.

---

### 5. Learning Management System (LMS) Module

**Purpose:** Comprehensive online learning platform with courses, assignments, quizzes, gradebook, and learning analytics.

#### Features
- Course and module management
- Assignment creation and submission
- Quiz engine with multiple question types
- Gradebook with category weighting
- Rubric-based grading
- Discussion forums
- Learning content management
- Video content with progress tracking
- H5P interactive content support
- LTI tool integration
- Certificate generation
- Learning analytics
- Student enrollment management
- Course prerequisites

#### Technical Metrics
| Component | Count |
|-----------|-------|
| Models | 107 |
| Controllers | 21 |
| Services | 2+ |

#### Key Model Categories
```
app/Models/Lms/
├── Course, Module, Content          # Course structure
├── Assignment, AssignmentSubmission # Assignments
├── Quiz, Question, Answer           # Quizzes
├── Grade, GradeItem, GradeCategory  # Grading
├── Discussion, DiscussionReply      # Forums
├── Video, VideoProgress             # Media
├── H5pContent                       # Interactive
├── LtiConnection                    # Integrations
└── Certificate                      # Credentials
```

#### Development Estimates
| Metric | Value |
|--------|-------|
| Complexity | Enterprise |
| Development Time | 10-14 person-months |
| Development Cost | $136,000 - $190,400 USD |

**Justification:** Full learning management system is an enterprise-scale application in itself. Includes quiz engine, gradebook calculations, content management, video handling, discussion forums, LTI integration, and analytics. 107 models indicate significant domain complexity.

---

### 6. Academic Assessment Module

**Purpose:** Manage academic assessments, grades, report cards, and term/year progression across multiple school types.

#### Features
- School-type specific assessment handling (Senior/Junior/Primary/Reception)
- Test and exam management
- Score entry and validation
- Criteria-based assessments
- Grade calculations with scales
- Report card generation (PDF)
- Score comments (automatic and manual)
- External exam integration (JCE, PSLE)
- Term rollover operations
- Year rollover operations
- Finals management
- House performance tracking

#### Technical Metrics
| Component | Count |
|-----------|-------|
| Services | 6 |
| Models | 15+ |
| Controllers | 5 |

#### Key Services
```
app/Services/
├── GradebookService.php
├── TermRolloverService.php
├── YearRolloverService.php
├── FinalsModuleRolloverService.php
├── YearRolloverReverseService.php
└── TermRolloverReverseService.php
```

#### Development Estimates
| Metric | Value |
|--------|-------|
| Complexity | Complex |
| Development Time | 5-7 person-months |
| Development Cost | $68,000 - $95,200 USD |

**Justification:** Multi-school-type support requires parallel implementations. Complex grade calculation logic, rollover operations with data migration, external exam integration, and PDF report generation add significant complexity.

---

### 7. Student Management Module

**Purpose:** Core student records, profiles, enrollment, and lifecycle management.

#### Features
- Student profile management
- Personal and demographic information
- Health information tracking
- Academic history
- Student status lifecycle (active, departed, transferred)
- Bulk import/export via Excel
- Student search and filtering
- Class/grade assignment
- House assignment
- Student departure documentation
- Student transfer workflows

#### Technical Metrics
| Component | Count |
|-----------|-------|
| Models | 10+ |
| Controllers | 3+ |
| Services | 2+ |

#### Development Estimates
| Metric | Value |
|--------|-------|
| Complexity | Medium |
| Development Time | 2-3 person-months |
| Development Cost | $27,200 - $40,800 USD |

**Justification:** Core CRUD operations with moderate workflow complexity for status changes, bulk operations for import/export, and search/filter functionality. Foundational module that other modules depend on.

---

### 8. Admissions Module

**Purpose:** Manage prospective student applications, admission workflows, and enrollment processing.

#### Features
- Online application form
- Application status tracking
- Application review workflow
- Health information capture
- Academic history capture
- Application-to-enrollment conversion
- Admission analytics
- Communication with applicants

#### Technical Metrics
| Component | Count |
|-----------|-------|
| Models | 5+ |
| Controllers | 2+ |

#### Development Estimates
| Metric | Value |
|--------|-------|
| Complexity | Medium |
| Development Time | 2-3 person-months |
| Development Cost | $27,200 - $40,800 USD |

**Justification:** Workflow-based application processing with form handling, status management, and conversion logic. Moderate complexity with clear bounded scope.

---

### 9. Communications Module

**Purpose:** Bulk messaging via SMS and email, notification management, and communication tracking.

#### Features
- Bulk SMS messaging
- Email communications
- Notification templates
- SMS template management
- Report card distribution
- Communication history tracking
- SMS balance management
- SMS cost calculation
- Delivery tracking
- Recipient group management

#### Technical Metrics
| Component | Count |
|-----------|-------|
| Services | 6+ |
| Models | 5+ |
| Controllers | 3+ |

#### Key Services
```
app/Services/
├── NotificationService.php
├── EmailService.php
├── SmsService.php
├── SmsBalanceService.php
├── SmsCostCalculator.php
├── SmsJobService.php
└── SmsPlaceholderService.php
```

#### Development Estimates
| Metric | Value |
|--------|-------|
| Complexity | Medium |
| Development Time | 2-3 person-months |
| Development Cost | $27,200 - $40,800 USD |

**Justification:** External API integration with SMS providers, template engine, queue-based processing for bulk operations. Well-defined scope with standard patterns.

---

### 10. Asset Management Module

**Purpose:** Track school assets, equipment, assignments, maintenance, and disposal.

#### Features
- Asset registration and categorization
- Asset assignment to users/departments
- Asset location tracking
- Maintenance scheduling
- Maintenance history
- Asset disposal workflows
- Depreciation calculations
- Asset audit trails
- Asset condition monitoring
- Asset reporting

#### Technical Metrics
| Component | Count |
|-----------|-------|
| Models | 8+ |
| Controllers | 3+ |

#### Key Models
```
app/Models/
├── Asset.php
├── AssetCategory.php
├── AssetAssignment.php
├── AssetMaintenance.php
├── AssetDisposal.php
└── AssetAudit.php
```

#### Development Estimates
| Metric | Value |
|--------|-------|
| Complexity | Simple-Medium |
| Development Time | 2-3 person-months |
| Development Cost | $27,200 - $40,800 USD |

**Justification:** Standard CRUD with workflow elements for assignments and disposals. Depreciation calculations add some complexity. Well-bounded scope.

---

## Optional Modules (Module Settings)

The system provides 7 configurable modules that can be enabled/disabled via the admin interface at `/setup/modules`.

### Module Visibility Architecture

**Controller:** `ModuleSettingsController.php`
**Service:** `ModuleVisibilityService.php`
**Storage:** `s_m_s_api_settings` table
**Cache TTL:** 3600 seconds (1 hour)

### Configuration Summary

| Module | Key | Icon | Associated Roles |
|--------|-----|------|------------------|
| Student Welfare | `welfare` | `fas fa-hospital-user` | School Counsellor, Welfare Admin, Welfare View, Nurse |
| Communications | `communications` | `bx bxs-chat` | Communications Admin, Communications Edit, Communications View, SMS Admin |
| Assets | `assets` | `bx bxs-package` | Asset Management Admin, Asset Management Edit, Asset Management View |
| LMS | `lms` | `fas fa-graduation-cap` | LMS Admin, LMS Instructor, LMS Student |
| Leave Management | `leave` | `bx bx-calendar-check` | Leave Admin, Leave View |
| Fee Administration | `fees` | `bx bxs-wallet` | Fee Admin, Fee Collection, Fee Reports, Bursar |
| Staff Attendance | `staff_attendance` | `bx bx-fingerprint` | HR Admin, Leave Admin |

### How Module Visibility Works

1. **Sidebar Navigation:** Menu items for disabled modules are hidden
2. **Role Management:** Roles associated with disabled modules are filtered from assignment UI
3. **Cache Management:** Visibility settings are cached for performance
4. **Database Storage:** Settings stored as key-value pairs in `s_m_s_api_settings`

### Key Service Methods

```php
class ModuleVisibilityService
{
    public function isModuleVisible(string $moduleKey): bool;
    public function getVisibleModules(): array;
    public function getHiddenRoles(): array;
    public function updateModuleVisibility(string $moduleKey, bool $visible): bool;
    public function clearCache(): void;
}
```

---

## Infrastructure & Shared Components

### Helper Services
| Service | Purpose |
|---------|---------|
| SettingsHelper | System settings access |
| CurrencyHelper | Currency formatting |
| CacheHelper | Caching utilities |
| LogActivityHelper | Activity logging |
| SMSHelper | SMS utilities |

### Cross-Cutting Concerns
| Concern | Implementation |
|---------|----------------|
| Authentication | Laravel built-in |
| Authorization | Policies + Gates |
| Audit Logging | Polymorphic audit models |
| Caching | Laravel Cache with Redis/file |
| Queues | Laravel Queues for async |
| Transactions | `DB::transaction()` |

### Infrastructure Development Estimate
| Metric | Value |
|--------|-------|
| Development Time | 3-4 person-months |
| Development Cost | $40,800 - $54,400 USD |

**Includes:** Authentication, authorization framework, shared services, caching layer, queue setup, logging infrastructure, database architecture.

---

## Development Investment Summary

### Module-by-Module Breakdown

| Module | Complexity | Time (months) | Cost (USD) |
|--------|------------|---------------|------------|
| Fee Management | Complex | 5-7 | $68,000 - $95,200 |
| Leave Management | Medium-Complex | 3-4 | $40,800 - $54,400 |
| Staff Attendance | Complex | 4-6 | $54,400 - $81,600 |
| Welfare & Support | Medium | 3-4 | $40,800 - $54,400 |
| LMS | Enterprise | 10-14 | $136,000 - $190,400 |
| Academic Assessment | Complex | 5-7 | $68,000 - $95,200 |
| Student Management | Medium | 2-3 | $27,200 - $40,800 |
| Admissions | Medium | 2-3 | $27,200 - $40,800 |
| Communications | Medium | 2-3 | $27,200 - $40,800 |
| Asset Management | Simple-Medium | 2-3 | $27,200 - $40,800 |
| Infrastructure | N/A | 3-4 | $40,800 - $54,400 |

### Total System Investment

| Metric | Low Estimate | High Estimate | Average |
|--------|--------------|---------------|---------|
| Development Time | 42 person-months | 58 person-months | 50 person-months |
| Development Cost | $571,200 USD | $788,800 USD | $680,000 USD |

### Team Composition Recommendation

For a system of this scale, the recommended team:

| Role | Count | Duration |
|------|-------|----------|
| Senior Laravel Developer | 1-2 | Full project |
| Mid-Level Developer | 2-3 | Full project |
| Junior Developer | 1-2 | Full project |
| UI/UX Designer | 1 | First 50% of project |
| QA Engineer | 1 | Last 60% of project |
| Project Manager | 1 | Full project |

---

## Appendix: Estimation Methodology

### Industry Standard Rates Used

| Role | Hourly Rate (USD) | Monthly Rate (USD) |
|------|-------------------|-------------------|
| Senior Laravel Developer | $75-150 | $12,000-24,000 |
| Mid-Level Developer | $50-75 | $8,000-12,000 |
| Junior Developer | $30-50 | $4,800-8,000 |
| UI/UX Designer | $60-100 | $9,600-16,000 |
| QA Engineer | $40-70 | $6,400-11,200 |

**Blended Team Rate:** $85/hr or $13,600/month

### Complexity Classification

| Level | Characteristics | Time Range |
|-------|-----------------|------------|
| Simple | Basic CRUD, minimal logic | 1-2 months |
| Medium | Workflows, integrations, reporting | 2-4 months |
| Complex | Financial calculations, external integrations, compliance | 4-8 months |
| Enterprise | Full platform (LMS-scale), extensive features | 8-14 months |

### Assumptions

1. **Team Experience:** Mid-to-senior level Laravel expertise
2. **Methodology:** Agile development with 2-week sprints
3. **Quality Standards:** Production-ready code with testing
4. **Documentation:** Inline documentation, API documentation
5. **Deployment:** Standard Laravel deployment practices
6. **Geographic Rate:** US/UK/Western Europe market rates

### Factors Not Included

- Project management overhead (add 10-15%)
- Code review and quality assurance (included in estimates)
- Infrastructure/hosting costs (operational, not development)
- Third-party license costs (SMS, biometric SDKs)
- Training and handover (add 5-10%)
- Ongoing maintenance (separate budget)

---

## Document History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | February 2026 | System Analysis | Initial document |
