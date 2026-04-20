# Fee Administration Module - Product Requirements Document

## Overview

A comprehensive fee administration module for basic education (Grades 1-12) with 3 terms per year. Handles tuition fees, levies, optional charges, partial payments, discounts, and detailed financial reporting.

---

## Requirements Summary

| Requirement | Details |
|-------------|---------|
| Fee Types | Tuition, levies, optional items (transport, meals, uniforms) |
| Fee Structure | Same fees per grade |
| Payments | Term-based with partial payments allowed |
| Balance Handling | Carry forward across terms |
| Payment Methods | Cash, bank transfer, mobile money, cheque (gateway ready) |
| Discounts | Sibling, staff, percentage-based |
| User Roles | Fee Setup, Fee Collection, Fee Reports (separated) |
| Reports | Statements, summaries, outstanding, aging, trends |

---

## UI/UX Design Standards

**CRITICAL:** All Fee Administration views MUST follow the theming from `admissions/index.blade.php`, `admissions/admission-new.blade.php`, and `docs/create.blade.php`.

### Index/List Pages Theme (Reference: `admissions/index.blade.php`)

```css
/* Main container */
.fee-container {
    background: white;
    border-radius: 3px;
    padding: 0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

/* Header with gradient */
.fee-header {
    background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
    color: white;
    padding: 28px;
    border-radius: 3px 3px 0 0;
}

/* Body section */
.fee-body {
    padding: 24px;
}

/* Help text box */
.help-text {
    background: #f8f9fa;
    padding: 12px;
    border-left: 4px solid #3b82f6;
    border-radius: 0 3px 3px 0;
    margin-bottom: 20px;
}

.help-text .help-title {
    font-weight: 600;
    color: #374151;
    margin-bottom: 4px;
}

.help-text .help-content {
    color: #6b7280;
    font-size: 13px;
    line-height: 1.4;
}

/* Primary button */
.btn-primary {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    border: none;
    color: white;
    font-weight: 500;
    padding: 10px 16px;
    border-radius: 3px;
    transition: all 0.2s ease;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    color: white;
}

/* Table styling */
.table thead th {
    background: #f9fafb;
    border-bottom: 2px solid #e5e7eb;
    font-weight: 600;
    color: #374151;
    font-size: 13px;
}

.table tbody tr:hover {
    background-color: #f9fafb;
}

/* Status badges */
.status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.status-paid { background: #d1fae5; color: #065f46; }
.status-partial { background: #fef3c7; color: #92400e; }
.status-outstanding { background: #fee2e2; color: #991b1b; }
.status-overdue { background: #fecaca; color: #7f1d1d; }
```

### Form Pages Theme (Reference: `admissions/admission-new.blade.php`)

```css
/* Form container */
.form-container {
    background: white;
    border-radius: 3px;
    padding: 32px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

/* Page header */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    padding-bottom: 12px;
    border-bottom: 1px solid #e5e7eb;
}

.page-title {
    font-size: 22px;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

/* Section title */
.section-title {
    font-size: 16px;
    font-weight: 600;
    margin: 24px 0 16px 0;
    color: #1f2937;
    padding-bottom: 8px;
    border-bottom: 1px solid #e5e7eb;
}

/* Form grid - 3 columns */
.form-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}

@media (max-width: 992px) {
    .form-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 576px) {
    .form-grid { grid-template-columns: 1fr; }
}

/* Form controls */
.form-control, .form-select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 3px;
    font-size: 14px;
    transition: all 0.2s;
}

.form-control:focus, .form-select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Form actions */
.form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    padding-top: 24px;
    border-top: 1px solid #f3f4f6;
    margin-top: 32px;
}
```

### Save Button with Loading Animation (MANDATORY)

**Every save/submit button MUST use this pattern:**

```html
<button type="submit" class="btn btn-primary btn-loading">
    <span class="btn-text"><i class="fas fa-save"></i> Save Changes</span>
    <span class="btn-spinner d-none">
        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
        Saving...
    </span>
</button>
```

**Required CSS:**
```css
.btn-loading.loading .btn-text {
    display: none;
}

.btn-loading.loading .btn-spinner {
    display: inline-flex !important;
    align-items: center;
}

.btn-loading:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}
```

**Required JavaScript:**
```javascript
// Add to form submit handler
const submitBtn = form.querySelector('button[type="submit"].btn-loading');
if (submitBtn) {
    submitBtn.classList.add('loading');
    submitBtn.disabled = true;
}
```

### Button Text Variations

| Action | Button Text | Loading Text |
|--------|-------------|--------------|
| Create | `<i class="fas fa-save"></i> Create [Entity]` | `Creating...` |
| Update | `<i class="fas fa-save"></i> Save Changes` | `Saving...` |
| Record Payment | `<i class="fas fa-save"></i> Record Payment` | `Processing...` |
| Generate Invoice | `<i class="fas fa-file-invoice"></i> Generate Invoice` | `Generating...` |
| Generate Bulk | `<i class="fas fa-file-invoice"></i> Generate Invoices` | `Generating...` |
| Void Payment | `<i class="fas fa-ban"></i> Void Payment` | `Voiding...` |
| Export | `<i class="fas fa-download"></i> Export` | `Exporting...` |

### View Templates Structure

**Index Page Template:**
```blade
<div class="fee-container">
    <div class="fee-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h3 style="margin:0;">[Page Title]</h3>
                <p style="margin:6px 0 0 0; opacity:.9;">[Subtitle]</p>
            </div>
            <div class="col-md-6">
                <!-- Stats -->
            </div>
        </div>
    </div>
    <div class="fee-body">
        <div class="help-text">
            <div class="help-title">[Help Title]</div>
            <div class="help-content">[Help Content]</div>
        </div>
        <!-- Filters and table -->
    </div>
</div>
```

**Form Page Template:**
```blade
<div class="form-container">
    <div class="page-header">
        <h1 class="page-title">[Form Title]</h1>
    </div>

    <div class="help-text">
        <div class="help-title">[Help Title]</div>
        <div class="help-content">[Help Content]</div>
    </div>

    <form method="post" action="[route]">
        @csrf

        <h3 class="section-title">[Section Name]</h3>
        <div class="form-grid">
            <!-- Form fields -->
        </div>

        <div class="form-actions">
            <a class="btn btn-secondary" href="[back-route]">
                <i class="bx bx-x"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary btn-loading">
                <span class="btn-text"><i class="fas fa-save"></i> [Action]</span>
                <span class="btn-spinner d-none">
                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                    [Loading Text]...
                </span>
            </button>
        </div>
    </form>
</div>
```

---

## Database Schema

### Table: `fee_types`
```sql
- id (bigint, PK)
- code (varchar 20, unique) -- e.g., 'TUITION', 'TRANSPORT'
- name (varchar 100)
- category (enum: 'tuition', 'levy', 'optional')
- description (text, nullable)
- is_optional (boolean, default: false)
- is_active (boolean, default: true)
- created_at, updated_at, deleted_at
```

### Table: `fee_structures`
```sql
- id (bigint, PK)
- fee_type_id (FK -> fee_types)
- grade_id (FK -> grades)
- term_id (FK -> terms)
- year (year)
- amount (decimal 10,2)
- created_by (FK -> users)
- created_at, updated_at, deleted_at
- UNIQUE(fee_type_id, grade_id, term_id)
```

### Table: `discount_types`
```sql
- id (bigint, PK)
- code (varchar 20, unique) -- e.g., 'SIBLING', 'STAFF'
- name (varchar 100)
- percentage (decimal 5,2) -- e.g., 10.00 for 10%
- description (text, nullable)
- applies_to (enum: 'all', 'tuition_only')
- is_active (boolean, default: true)
- created_at, updated_at, deleted_at
```

### Table: `student_discounts`
```sql
- id (bigint, PK)
- student_id (FK -> students)
- discount_type_id (FK -> discount_types)
- term_id (FK -> terms)
- year (year)
- assigned_by (FK -> users)
- notes (text, nullable)
- created_at, updated_at, deleted_at
- UNIQUE(student_id, discount_type_id, term_id)
```

### Table: `student_invoices`
```sql
- id (bigint, PK)
- invoice_number (varchar 20, unique) -- e.g., 'INV-2026-0001'
- student_id (FK -> students)
- term_id (FK -> terms)
- year (year)
- subtotal_amount (decimal 12,2)
- discount_amount (decimal 12,2, default: 0)
- total_amount (decimal 12,2)
- amount_paid (decimal 12,2, default: 0)
- balance (decimal 12,2)
- status (enum: 'draft', 'issued', 'partial', 'paid', 'overdue', 'cancelled')
- issued_at (datetime, nullable)
- due_date (date, nullable)
- notes (text, nullable)
- created_by (FK -> users)
- created_at, updated_at, deleted_at
```

### Table: `student_invoice_items`
```sql
- id (bigint, PK)
- student_invoice_id (FK -> student_invoices)
- fee_structure_id (FK -> fee_structures)
- description (varchar 255)
- amount (decimal 10,2)
- discount_amount (decimal 10,2, default: 0)
- net_amount (decimal 10,2)
- created_at, updated_at
```

### Table: `fee_payments`
```sql
- id (bigint, PK)
- receipt_number (varchar 20, unique) -- e.g., 'RCP-2026-0001'
- student_invoice_id (FK -> student_invoices)
- student_id (FK -> students)
- term_id (FK -> terms)
- amount (decimal 12,2)
- payment_method (enum: 'cash', 'bank_transfer', 'mobile_money', 'cheque')
- payment_date (date)
- reference_number (varchar 100, nullable)
- cheque_number (varchar 50, nullable)
- bank_name (varchar 100, nullable)
- notes (text, nullable)
- received_by (FK -> users)
- voided (boolean, default: false)
- voided_at (datetime, nullable)
- voided_by (FK -> users, nullable)
- void_reason (text, nullable)
- created_at, updated_at, deleted_at
```

### Table: `fee_payment_sequences`
```sql
- year (year, PK)
- last_invoice_sequence (int, default: 0)
- last_receipt_sequence (int, default: 0)
- updated_at (datetime)
```

### Table: `fee_balance_carryovers`
```sql
- id (bigint, PK)
- student_id (FK -> students)
- from_term_id (FK -> terms)
- to_term_id (FK -> terms)
- balance_amount (decimal 12,2)
- carried_at (datetime)
- carried_by (FK -> users)
- created_at, updated_at
```

### Table: `fee_audit_logs`
```sql
- id (bigint, PK)
- auditable_type (varchar 255)
- auditable_id (bigint)
- action (varchar 50)
- user_id (FK -> users)
- old_values (json, nullable)
- new_values (json, nullable)
- notes (text, nullable)
- ip_address (varchar 45)
- created_at
```

---

## File Structure

```
app/
├── Models/Fee/
│   ├── FeeType.php
│   ├── FeeStructure.php
│   ├── DiscountType.php
│   ├── StudentDiscount.php
│   ├── StudentInvoice.php
│   ├── StudentInvoiceItem.php
│   ├── FeePayment.php
│   ├── FeePaymentSequence.php
│   ├── FeeBalanceCarryover.php
│   └── FeeAuditLog.php
├── Services/Fee/
│   ├── FeeStructureService.php
│   ├── InvoiceService.php
│   ├── PaymentService.php
│   ├── DiscountService.php
│   ├── FeeReportingService.php
│   └── FeeAuditService.php
├── Http/
│   ├── Controllers/Fee/
│   │   ├── FeeSetupController.php
│   │   ├── FeeCollectionController.php
│   │   ├── FeeReportController.php
│   │   └── StudentDiscountController.php
│   └── Requests/Fee/
│       ├── StoreFeeTypeRequest.php
│       ├── UpdateFeeTypeRequest.php
│       ├── StoreFeeStructureRequest.php
│       ├── StoreDiscountTypeRequest.php
│       ├── AssignDiscountRequest.php
│       ├── GenerateInvoiceRequest.php
│       ├── StorePaymentRequest.php
│       └── VoidPaymentRequest.php
├── Policies/Fee/
│   ├── StudentInvoicePolicy.php
│   └── FeePaymentPolicy.php
└── Exports/Fee/
    ├── OutstandingBalancesExport.php
    ├── DebtorsListExport.php
    └── CollectionSummaryExport.php

routes/fees/
├── fees.php
├── setup.php
├── collection.php
├── reports.php
└── discounts.php

resources/views/fees/
├── setup/
│   ├── fee-types/
│   ├── fee-structures/
│   └── discount-types/
├── collection/
│   ├── invoices/
│   ├── payments/
│   └── daily/
├── reports/
│   ├── dashboard.blade.php
│   ├── statements/
│   ├── collection/
│   ├── outstanding/
│   └── analytics/
└── discounts/

database/migrations/
├── 2026_01_25_000001_create_fee_types_table.php
├── 2026_01_25_000002_create_fee_structures_table.php
├── 2026_01_25_000003_create_discount_types_table.php
├── 2026_01_25_000004_create_student_discounts_table.php
├── 2026_01_25_000005_create_fee_payment_sequences_table.php
├── 2026_01_25_000006_create_student_invoices_table.php
├── 2026_01_25_000007_create_student_invoice_items_table.php
├── 2026_01_25_000008_create_fee_payments_table.php
├── 2026_01_25_000009_create_fee_balance_carryovers_table.php
└── 2026_01_25_000010_create_fee_audit_logs_table.php
```

---

## Authorization

### New Roles
- `Fee Setup` - Define fees, structures, discounts
- `Fee Collection` - Record payments, generate invoices
- `Fee Reports` - View-only access to reports

### Gates (AuthServiceProvider.php)
```php
'manage-fee-setup'     -> ['Administrator', 'Fee Setup', 'Bursar']
'collect-fees'         -> ['Administrator', 'Fee Collection', 'Bursar']
'void-payments'        -> ['Administrator', 'Bursar']
'view-fee-reports'     -> ['Administrator', 'Fee Setup', 'Fee Collection', 'Fee Reports', 'Bursar']
'export-fee-reports'   -> ['Administrator', 'Bursar', 'Fee Reports']
```

---

# Implementation Phases - Granular Tasks

## Phase 1: Database Foundation

### Task 1.1: Create fee_types migration
**File:** `database/migrations/2026_01_25_000001_create_fee_types_table.php`
- Create migration with all columns as specified
- Add soft deletes
- Add index on `code` and `is_active`

### Task 1.2: Create fee_structures migration
**File:** `database/migrations/2026_01_25_000002_create_fee_structures_table.php`
- Create migration with foreign keys to fee_types, grades, terms
- Add unique constraint on (fee_type_id, grade_id, term_id)
- Add indexes on grade_id, term_id, year

### Task 1.3: Create discount_types migration
**File:** `database/migrations/2026_01_25_000003_create_discount_types_table.php`
- Create migration with all columns
- Add soft deletes

### Task 1.4: Create student_discounts migration
**File:** `database/migrations/2026_01_25_000004_create_student_discounts_table.php`
- Create migration with foreign keys
- Add unique constraint on (student_id, discount_type_id, term_id)

### Task 1.5: Create fee_payment_sequences migration
**File:** `database/migrations/2026_01_25_000005_create_fee_payment_sequences_table.php`
- Create migration for race-condition-proof numbering
- Primary key on year

### Task 1.6: Create student_invoices migration
**File:** `database/migrations/2026_01_25_000006_create_student_invoices_table.php`
- Create migration with all columns
- Add indexes on student_id, term_id, status, invoice_number

### Task 1.7: Create student_invoice_items migration
**File:** `database/migrations/2026_01_25_000007_create_student_invoice_items_table.php`
- Create migration with foreign key to student_invoices

### Task 1.8: Create fee_payments migration
**File:** `database/migrations/2026_01_25_000008_create_fee_payments_table.php`
- Create migration with all columns
- Add indexes on student_id, term_id, payment_date, receipt_number

### Task 1.9: Create fee_balance_carryovers migration
**File:** `database/migrations/2026_01_25_000009_create_fee_balance_carryovers_table.php`
- Create migration for balance tracking

### Task 1.10: Create fee_audit_logs migration
**File:** `database/migrations/2026_01_25_000010_create_fee_audit_logs_table.php`
- Create migration for audit trail
- Add indexes on auditable_type, auditable_id, user_id

### Task 1.11: Run migrations and verify
- Run `php artisan migrate`
- Verify all tables created correctly
- Test foreign key constraints

---

## Phase 2: Core Models

### Task 2.1: Create FeeType model
**File:** `app/Models/Fee/FeeType.php`
- Define fillable, casts, constants for categories
- Add `feeStructures()` relationship
- Add `scopeActive()` and `scopeOptional()` scopes

### Task 2.2: Create FeeStructure model
**File:** `app/Models/Fee/FeeStructure.php`
- Define fillable, casts
- Add relationships: `feeType()`, `grade()`, `term()`, `createdBy()`
- Add `scopeForGrade()` and `scopeForTerm()` scopes

### Task 2.3: Create DiscountType model
**File:** `app/Models/Fee/DiscountType.php`
- Define fillable, casts, constants
- Add `studentDiscounts()` relationship
- Add `scopeActive()` scope

### Task 2.4: Create StudentDiscount model
**File:** `app/Models/Fee/StudentDiscount.php`
- Define fillable
- Add relationships: `student()`, `discountType()`, `term()`, `assignedBy()`

### Task 2.5: Create FeePaymentSequence model
**File:** `app/Models/Fee/FeePaymentSequence.php`
- Define table, primary key, fillable
- No relationships needed

### Task 2.6: Create StudentInvoice model
**File:** `app/Models/Fee/StudentInvoice.php`
- Define fillable, casts, constants for statuses
- Add relationships: `student()`, `term()`, `items()`, `payments()`, `createdBy()`
- Add `scopeForStudent()`, `scopeForTerm()`, `scopeOutstanding()`, `scopePaid()`
- Add `recalculateBalance()`, `isPaid()`, `isOverdue()`, `getStatusColorAttribute()`
- Add `generateInvoiceNumber()` static method with lock

### Task 2.7: Create StudentInvoiceItem model
**File:** `app/Models/Fee/StudentInvoiceItem.php`
- Define fillable, casts
- Add relationships: `invoice()`, `feeStructure()`

### Task 2.8: Create FeePayment model
**File:** `app/Models/Fee/FeePayment.php`
- Define fillable, casts, constants for payment methods
- Add relationships: `invoice()`, `student()`, `term()`, `receivedBy()`, `voidedBy()`
- Add `scopeNotVoided()`, `scopeForDateRange()`
- Add `void()` method, `isVoided()` helper
- Add `generateReceiptNumber()` static method with lock

### Task 2.9: Create FeeBalanceCarryover model
**File:** `app/Models/Fee/FeeBalanceCarryover.php`
- Define fillable, casts
- Add relationships: `student()`, `fromTerm()`, `toTerm()`, `carriedBy()`

### Task 2.10: Create FeeAuditLog model
**File:** `app/Models/Fee/FeeAuditLog.php`
- Define fillable, casts
- Add relationships: `user()`, `auditable()` (morphTo)

### Task 2.11: Update Student model
**File:** `app/Models/Student.php`
- Add `invoices()` hasMany relationship
- Add `feePayments()` hasMany relationship
- Add `discounts()` hasMany relationship
- Add `getCurrentBalanceAttribute()` accessor
- Add `hasOutstandingFees()` method

---

## Phase 3: Services - Audit and Structure

### Task 3.1: Create FeeAuditService
**File:** `app/Services/Fee/FeeAuditService.php`
- Implement `log(Model $model, string $action, ?array $oldValues, ?array $newValues, ?string $notes = null)`
- Implement `getAuditTrail(string $modelType, int $modelId)`
- Implement `getRecentActivity(int $limit = 20)`

### Task 3.2: Create FeeStructureService - Part 1
**File:** `app/Services/Fee/FeeStructureService.php`
- Implement `createFeeType(array $data): FeeType`
- Implement `updateFeeType(FeeType $type, array $data): FeeType`
- Implement `deleteFeeType(FeeType $type): bool`

### Task 3.3: Create FeeStructureService - Part 2
**File:** `app/Services/Fee/FeeStructureService.php`
- Implement `createFeeStructure(array $data, User $user): FeeStructure`
- Implement `updateFeeStructure(FeeStructure $structure, array $data): FeeStructure`
- Implement `deleteFeeStructure(FeeStructure $structure): bool`

### Task 3.4: Create FeeStructureService - Part 3
**File:** `app/Services/Fee/FeeStructureService.php`
- Implement `copyStructureToTerm(int $fromTermId, int $toTermId, User $user): int`
- Implement `getFeeStructuresForGrade(int $gradeId, int $termId): Collection`
- Implement `getTotalFeesForGrade(int $gradeId, int $termId): array`

### Task 3.5: Create DiscountService
**File:** `app/Services/Fee/DiscountService.php`
- Implement `createDiscountType(array $data): DiscountType`
- Implement `updateDiscountType(DiscountType $type, array $data): DiscountType`
- Implement `assignDiscount(Student $student, DiscountType $discount, int $termId, User $user): StudentDiscount`
- Implement `removeDiscount(StudentDiscount $discount): bool`
- Implement `calculateStudentDiscount(Student $student, int $termId): float`
- Implement `getStudentDiscounts(int $studentId, ?int $termId = null): Collection`

---

## Phase 4: Authorization

### Task 4.1: Add fee gates to AuthServiceProvider
**File:** `app/Providers/AuthServiceProvider.php`
- Add `access-fee-administration` gate
- Add `manage-fee-setup` gate
- Add `collect-fees` gate
- Add `void-payments` gate
- Add `view-fee-reports` gate
- Add `export-fee-reports` gate

### Task 4.2: Create StudentInvoicePolicy
**File:** `app/Policies/Fee/StudentInvoicePolicy.php`
- Implement `viewAny()`, `view()`, `create()`, `update()`, `delete()`, `cancel()`, `generateBulk()`

### Task 4.3: Create FeePaymentPolicy
**File:** `app/Policies/Fee/FeePaymentPolicy.php`
- Implement `viewAny()`, `view()`, `create()`, `void()`, `printReceipt()`

### Task 4.4: Register policies in AuthServiceProvider
- Register StudentInvoicePolicy
- Register FeePaymentPolicy

### Task 4.5: Create role seeder or update existing
- Add 'Fee Setup' role
- Add 'Fee Collection' role
- Add 'Fee Reports' role

---

## Phase 5: Fee Setup Controller and Views

### Task 5.1: Create form request validators
**Files:**
- `app/Http/Requests/Fee/StoreFeeTypeRequest.php`
- `app/Http/Requests/Fee/UpdateFeeTypeRequest.php`
- `app/Http/Requests/Fee/StoreFeeStructureRequest.php`
- `app/Http/Requests/Fee/UpdateFeeStructureRequest.php`
- `app/Http/Requests/Fee/StoreDiscountTypeRequest.php`
- `app/Http/Requests/Fee/UpdateDiscountTypeRequest.php`

### Task 5.2: Create FeeSetupController - Fee Types
**File:** `app/Http/Controllers/Fee/FeeSetupController.php`
- Implement `indexTypes()`, `createType()`, `storeType()`, `editType()`, `updateType()`, `destroyType()`

### Task 5.3: Create FeeSetupController - Fee Structures
**File:** `app/Http/Controllers/Fee/FeeSetupController.php`
- Implement `indexStructures()`, `createStructure()`, `storeStructure()`, `editStructure()`, `updateStructure()`, `destroyStructure()`, `copyStructures()`

### Task 5.4: Create FeeSetupController - Discount Types
**File:** `app/Http/Controllers/Fee/FeeSetupController.php`
- Implement `indexDiscountTypes()`, `storeDiscountType()`, `updateDiscountType()`

### Task 5.5: Create setup routes
**File:** `routes/fees/setup.php`
- Define all fee type routes
- Define all fee structure routes
- Define discount type routes

### Task 5.6: Create fee types views
**Files:**
- `resources/views/fees/setup/fee-types/index.blade.php` - Use `fee-container` theme from admissions/index
- `resources/views/fees/setup/fee-types/create.blade.php` - Use `form-container` theme from admissions/admission-new
- `resources/views/fees/setup/fee-types/edit.blade.php` - Use `form-container` theme

**IMPORTANT:** Follow UI/UX Design Standards section. All save buttons MUST use `btn-loading` pattern with spinner animation.

### Task 5.7: Create fee structures views
**Files:**
- `resources/views/fees/setup/fee-structures/index.blade.php` - Use `fee-container` theme
- `resources/views/fees/setup/fee-structures/create.blade.php` - Use `form-container` theme
- `resources/views/fees/setup/fee-structures/edit.blade.php` - Use `form-container` theme

**IMPORTANT:** Follow UI/UX Design Standards section. All save buttons MUST use `btn-loading` pattern with spinner animation.

### Task 5.8: Create discount types views
**Files:**
- `resources/views/fees/setup/discount-types/index.blade.php` - Use `fee-container` theme
- `resources/views/fees/setup/discount-types/_form.blade.php` (modal) - Modal form with `btn-loading` save button

**IMPORTANT:** Follow UI/UX Design Standards section. Save buttons MUST use spinner animation.

---

## Phase 6: Invoice Service and Generation

### Task 6.1: Create InvoiceService - Core
**File:** `app/Services/Fee/InvoiceService.php`
- Implement constructor with dependencies
- Implement `getInvoice(int $invoiceId): StudentInvoice`
- Implement `getStudentInvoiceForTerm(int $studentId, int $termId): ?StudentInvoice`

### Task 6.2: Create InvoiceService - Generation
**File:** `app/Services/Fee/InvoiceService.php`
- Implement `generateInvoice(Student $student, int $termId, User $user): StudentInvoice`
- Include: get fee structures, calculate discounts, create invoice and items

### Task 6.3: Create InvoiceService - Bulk Generation
**File:** `app/Services/Fee/InvoiceService.php`
- Implement `generateBulkInvoices(int $gradeId, int $termId, User $user): array`
- Return stats: generated, skipped, errors

### Task 6.4: Create InvoiceService - Discount Application
**File:** `app/Services/Fee/InvoiceService.php`
- Implement `applyDiscounts(StudentInvoice $invoice): void`
- Calculate and apply student discounts to invoice items

### Task 6.5: Create InvoiceService - Cancel and Carryover
**File:** `app/Services/Fee/InvoiceService.php`
- Implement `cancelInvoice(StudentInvoice $invoice, User $user, string $reason): bool`
- Implement `carryForwardBalance(Student $student, int $fromTermId, int $toTermId, User $user): float`

### Task 6.6: Create invoice request validators
**Files:**
- `app/Http/Requests/Fee/GenerateInvoiceRequest.php`
- `app/Http/Requests/Fee/GenerateBulkInvoicesRequest.php`

---

## Phase 7: Fee Collection Controller and Views - Invoices

### Task 7.1: Create FeeCollectionController - Invoice Methods
**File:** `app/Http/Controllers/Fee/FeeCollectionController.php`
- Implement `indexInvoices()`, `showInvoice()`, `generateInvoice()`, `generateBulkInvoices()`

### Task 7.2: Create FeeCollectionController - Student Lookup
**File:** `app/Http/Controllers/Fee/FeeCollectionController.php`
- Implement `searchStudent()` - AJAX search for students
- Implement `studentAccount()` - show student's fee account

### Task 7.3: Create collection routes - Part 1
**File:** `routes/fees/collection.php`
- Define invoice routes
- Define student search routes

### Task 7.4: Create invoice views
**Files:**
- `resources/views/fees/collection/invoices/index.blade.php` - Use `fee-container` theme with gradient header
- `resources/views/fees/collection/invoices/show.blade.php` - Use `form-container` theme
- `resources/views/fees/collection/invoices/generate.blade.php` - Use `form-container` theme
- `resources/views/fees/collection/invoices/bulk-generate.blade.php` - Use `form-container` theme

**IMPORTANT:** Follow UI/UX Design Standards. Generate buttons MUST use `btn-loading` pattern:
```html
<button type="submit" class="btn btn-primary btn-loading">
    <span class="btn-text"><i class="fas fa-file-invoice"></i> Generate Invoice</span>
    <span class="btn-spinner d-none">
        <span class="spinner-border spinner-border-sm me-2"></span>
        Generating...
    </span>
</button>
```

### Task 7.5: Create invoice PDF template
**File:** `resources/views/fees/collection/invoices/pdf.blade.php`
- Design printable invoice format

---

## Phase 8: Payment Service

### Task 8.1: Create PaymentService - Core
**File:** `app/Services/Fee/PaymentService.php`
- Implement constructor with dependencies
- Implement `getPaymentsForInvoice(int $invoiceId): Collection`
- Implement `getPaymentsForStudent(int $studentId, ?int $termId = null): Collection`

### Task 8.2: Create PaymentService - Record Payment
**File:** `app/Services/Fee/PaymentService.php`
- Implement `recordPayment(array $data, User $collector): FeePayment`
- Include: validate amount, create payment, update invoice balance
- Use database transaction

### Task 8.3: Create PaymentService - Void Payment
**File:** `app/Services/Fee/PaymentService.php`
- Implement `voidPayment(FeePayment $payment, User $user, string $reason): bool`
- Include: mark as voided, recalculate invoice balance
- Use database transaction

### Task 8.4: Create PaymentService - Daily Collections
**File:** `app/Services/Fee/PaymentService.php`
- Implement `getDailyCollections(string $date, ?int $collectorId = null): Collection`
- Implement `allocatePaymentToInvoice(FeePayment $payment): void`

### Task 8.5: Create payment request validators
**Files:**
- `app/Http/Requests/Fee/StorePaymentRequest.php`
- `app/Http/Requests/Fee/VoidPaymentRequest.php`

---

## Phase 9: Fee Collection Controller and Views - Payments

### Task 9.1: Create FeeCollectionController - Payment Methods
**File:** `app/Http/Controllers/Fee/FeeCollectionController.php`
- Implement `createPayment()`, `storePayment()`, `showPayment()`

### Task 9.2: Create FeeCollectionController - Void and Print
**File:** `app/Http/Controllers/Fee/FeeCollectionController.php`
- Implement `voidPayment()`, `printReceipt()`

### Task 9.3: Create FeeCollectionController - Daily Operations
**File:** `app/Http/Controllers/Fee/FeeCollectionController.php`
- Implement `dailyCollection()`, `endOfDaySummary()`

### Task 9.4: Create collection routes - Part 2
**File:** `routes/fees/collection.php`
- Add payment routes
- Add daily operation routes

### Task 9.5: Create payment views
**Files:**
- `resources/views/fees/collection/payments/create.blade.php` - Use `form-container` theme
- `resources/views/fees/collection/payments/show.blade.php` - Use `form-container` theme
- `resources/views/fees/collection/payments/void-modal.blade.php` - Modal with `btn-loading` pattern

**IMPORTANT:** Record Payment button MUST use:
```html
<button type="submit" class="btn btn-primary btn-loading">
    <span class="btn-text"><i class="fas fa-save"></i> Record Payment</span>
    <span class="btn-spinner d-none">
        <span class="spinner-border spinner-border-sm me-2"></span>
        Processing...
    </span>
</button>
```

### Task 9.6: Create receipt PDF template
**File:** `resources/views/fees/collection/payments/receipt-pdf.blade.php`
- Design printable receipt format

### Task 9.7: Create daily collection views
**Files:**
- `resources/views/fees/collection/daily/index.blade.php` - Use `fee-container` theme with stats header
- `resources/views/fees/collection/daily/end-of-day.blade.php` - Use `fee-container` theme

**IMPORTANT:** Follow admissions/index.blade.php layout for list views.

---

## Phase 10: Student Discount Management

### Task 10.1: Create discount request validator
**File:** `app/Http/Requests/Fee/AssignDiscountRequest.php`

### Task 10.2: Create StudentDiscountController
**File:** `app/Http/Controllers/Fee/StudentDiscountController.php`
- Implement `index()`, `assign()`, `remove()`
- Implement `autoAssignSiblingDiscounts()`

### Task 10.3: Create discount routes
**File:** `routes/fees/discounts.php`
- Define all discount routes

### Task 10.4: Create discount views
**Files:**
- `resources/views/fees/discounts/index.blade.php` - Use `fee-container` theme
- `resources/views/fees/discounts/assign-modal.blade.php` - Modal with `btn-loading` pattern

**IMPORTANT:** Assign button MUST use spinner animation on submit.

---

## Phase 11: Reporting Service

### Task 11.1: Create FeeReportingService - Student Reports
**File:** `app/Services/Fee/FeeReportingService.php`
- Implement `getStudentStatement(int $studentId, ?int $termId = null): array`
- Implement `getStudentPaymentHistory(int $studentId): Collection`

### Task 11.2: Create FeeReportingService - Collection Reports
**File:** `app/Services/Fee/FeeReportingService.php`
- Implement `getCollectionSummary(int $termId, ?array $filters = []): array`
- Implement `getDailyCollectionReport(string $date): array`
- Implement `getCollectionByMethod(int $termId): array`
- Implement `getCollectorPerformance(int $termId): array`

### Task 11.3: Create FeeReportingService - Outstanding Reports
**File:** `app/Services/Fee/FeeReportingService.php`
- Implement `getOutstandingBalances(int $termId, ?int $gradeId = null): Collection`
- Implement `getAgingReport(int $termId): array`
- Implement `getDebtorsList(int $termId, float $minBalance = 0): Collection`

### Task 11.4: Create FeeReportingService - Analytics
**File:** `app/Services/Fee/FeeReportingService.php`
- Implement `getPaymentTrends(int $year): array`
- Implement `getCollectionRates(int $termId): array`
- Implement `getGradeComparison(int $termId): array`

### Task 11.5: Create FeeReportingService - Dashboard
**File:** `app/Services/Fee/FeeReportingService.php`
- Implement `getDashboardStats(): array`
- Return: total collected, outstanding, collection rate, recent payments

---

## Phase 12: Report Controller and Views

### Task 12.1: Create FeeReportController - Dashboard
**File:** `app/Http/Controllers/Fee/FeeReportController.php`
- Implement `dashboard()`

### Task 12.2: Create FeeReportController - Student Reports
**File:** `app/Http/Controllers/Fee/FeeReportController.php`
- Implement `studentStatement()`, `studentPaymentHistory()`, `printStatement()`

### Task 12.3: Create FeeReportController - Collection Reports
**File:** `app/Http/Controllers/Fee/FeeReportController.php`
- Implement `collectionSummary()`, `dailyCollection()`, `collectionByMethod()`

### Task 12.4: Create FeeReportController - Outstanding Reports
**File:** `app/Http/Controllers/Fee/FeeReportController.php`
- Implement `outstandingBalances()`, `agingReport()`, `debtorsList()`

### Task 12.5: Create FeeReportController - Analytics
**File:** `app/Http/Controllers/Fee/FeeReportController.php`
- Implement `paymentTrends()`, `collectionRates()`, `gradeComparison()`

### Task 12.6: Create report routes
**File:** `routes/fees/reports.php`
- Define all report routes

### Task 12.7: Create report dashboard view
**File:** `resources/views/fees/reports/dashboard.blade.php`
- Use `fee-container` theme with gradient header showing key stats
- Summary cards, charts (ApexCharts), recent activity table
- Follow admissions/index.blade.php layout pattern

### Task 12.8: Create student report views
**Files:**
- `resources/views/fees/reports/statements/show.blade.php`
- `resources/views/fees/reports/statements/print.blade.php`
- `resources/views/fees/reports/statements/history.blade.php`

### Task 12.9: Create collection report views
**Files:**
- `resources/views/fees/reports/collection/summary.blade.php`
- `resources/views/fees/reports/collection/daily.blade.php`
- `resources/views/fees/reports/collection/by-method.blade.php`

### Task 12.10: Create outstanding report views
**Files:**
- `resources/views/fees/reports/outstanding/index.blade.php`
- `resources/views/fees/reports/outstanding/aging.blade.php`
- `resources/views/fees/reports/outstanding/debtors.blade.php`

### Task 12.11: Create analytics views
**Files:**
- `resources/views/fees/reports/analytics/trends.blade.php`
- `resources/views/fees/reports/analytics/rates.blade.php`
- `resources/views/fees/reports/analytics/grade-comparison.blade.php`

---

## Phase 13: Excel Exports

### Task 13.1: Create OutstandingBalancesExport
**File:** `app/Exports/Fee/OutstandingBalancesExport.php`
- Implement export with filters

### Task 13.2: Create DebtorsListExport
**File:** `app/Exports/Fee/DebtorsListExport.php`
- Implement export with filters

### Task 13.3: Create CollectionSummaryExport
**File:** `app/Exports/Fee/CollectionSummaryExport.php`
- Implement export with date range

### Task 13.4: Add export methods to FeeReportController
**File:** `app/Http/Controllers/Fee/FeeReportController.php`
- Implement `exportOutstandingBalances()`, `exportDebtorsList()`, `exportCollectionSummary()`

### Task 13.5: Add export routes
**File:** `routes/fees/reports.php`
- Add export routes

---

## Phase 14: Route Integration and Navigation

### Task 14.1: Create main fees route file
**File:** `routes/fees/fees.php`
- Include setup.php, collection.php, reports.php, discounts.php
- Apply auth and throttle middleware

### Task 14.2: Update web.php
**File:** `routes/web.php`
- Include fees/fees.php

### Task 14.3: Add navigation menu items
**File:** Update main navigation view
- Add Fee Administration menu with sub-items
- Show/hide based on user permissions

---

## Phase 15: Testing and Verification

### Task 15.1: Test fee setup workflow
- Login as Fee Setup role
- Create fee types (Tuition, Registration, Transport, Meals)
- Create fee structures for each grade for current term
- Create discount types (Sibling 10%, Staff 25%)

### Task 15.2: Test invoice generation
- Generate invoice for single student
- Verify invoice items and totals
- Generate bulk invoices for a grade
- Print invoice PDF

### Task 15.3: Test payment recording
- Record cash payment (partial)
- Record bank transfer payment
- Verify balance updates
- Print receipt
- Test void payment

### Task 15.4: Test discount application
- Assign sibling discount to student
- Regenerate invoice to verify discount applied
- Run auto-assign sibling discounts

### Task 15.5: Test reports
- View student statement
- Generate collection summary
- View outstanding balances
- Generate aging report
- Export to Excel

### Task 15.6: Test authorization
- Verify Fee Setup role can only access setup
- Verify Fee Collection role can only access collection
- Verify Fee Reports role is view-only
- Verify void-payments is restricted

---

## Phase 16: Balance Carryover Integration

### Task 16.1: Implement balance carryover on term change
- Add carryover logic to InvoiceService
- Track carryover in fee_balance_carryovers table
- Show carried balance on new term invoice

### Task 16.2: Integrate with term rollover
**File:** `app/Services/TermRolloverService.php`
- Add hook to carry forward fee balances
- Update rollover history tracking

---

## Verification Checklist

### After Phase 2 (Models)
- [ ] All models created in app/Models/Fee/
- [ ] Relationships work correctly
- [ ] Student model has fee relationships

### After Phase 5 (Fee Setup)
- [ ] Can create/edit/delete fee types
- [ ] Can create/edit/delete fee structures
- [ ] Can create/edit discount types
- [ ] Authorization works for Fee Setup role

### After Phase 9 (Payments)
- [ ] Can generate invoices (single and bulk)
- [ ] Can record payments (all methods)
- [ ] Balance calculates correctly
- [ ] Can void payments
- [ ] Receipts print correctly

### After Phase 12 (Reports)
- [ ] Dashboard shows correct stats
- [ ] Student statements accurate
- [ ] Collection reports work
- [ ] Outstanding reports work
- [ ] Exports work

### Final Verification
- [ ] All roles have correct access
- [ ] Audit logs capture all actions
- [ ] PDF templates render correctly
- [ ] No N+1 queries in listings
- [ ] Balance carryover works

### UI/UX Compliance Checklist
- [ ] All index pages use `fee-container` theme with gradient header
- [ ] All form pages use `form-container` theme
- [ ] All pages have `.help-text` info box
- [ ] All save/submit buttons use `btn-loading` class with spinner animation
- [ ] Save button shows `<i class="fas fa-save"></i>` icon
- [ ] Loading state shows spinner with appropriate text (Creating.../Saving.../Processing...)
- [ ] Form grids use 3-column responsive layout
- [ ] Status badges use correct color classes
- [ ] Tables have hover effect on rows
- [ ] Buttons have hover transform and shadow effects
