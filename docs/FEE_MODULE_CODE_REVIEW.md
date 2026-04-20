# Fee Administration Module - Comprehensive Code Review

**Review Date:** February 2026
**Reviewer:** Claude Code
**Module Version:** Current Production

---

## Executive Summary

**Overall Assessment: GOOD with CRITICAL race condition vulnerabilities**

| Category | Rating | Notes |
|----------|--------|-------|
| **Architecture** | 8/10 | Well-structured, proper separation of concerns |
| **Security** | 9/10 | Excellent authorization, validation, CSRF protection |
| **Race Condition Safety** | 4/10 | CRITICAL - Missing locks on financial operations |
| **Data Integrity** | 6/10 | Denormalized amounts without sync guarantee |
| **Code Quality** | 8/10 | Consistent patterns, good error handling |

---

## Module Overview

### Architecture

The Fee Administration module consists of:

- **15 Eloquent Models** - Complete fee lifecycle coverage
- **9 Service Classes** - Business logic layer
- **6 Controllers** - HTTP request handling
- **13 FormRequest Classes** - Input validation
- **3 Policies** - Authorization enforcement
- **1 Custom Middleware** - Historical data protection

### Data Flow

```
FeeType → FeeStructure → StudentInvoice → StudentInvoiceItem
                              ↓
                         FeePayment ←→ PaymentPlanInstallment
                              ↓
                         FeeRefund / LateFeeCharge
```

### Key Models

| Model | Purpose |
|-------|---------|
| `StudentInvoice` | Annual fee invoice per student per year |
| `StudentInvoiceItem` | Line items (fees, carryovers, adjustments, late fees) |
| `FeePayment` | Payment records with receipt numbers |
| `PaymentPlan` | Installment payment arrangements |
| `PaymentPlanInstallment` | Individual installments within a plan |
| `FeeRefund` | Refund/credit note workflow (pending→approved→processed) |
| `LateFeeCharge` | Late fees with waiver capability |
| `FeeBalanceCarryover` | Outstanding balances carried to next year |
| `FeePaymentSequence` | Atomic sequence generation with locking |
| `FeeAuditLog` | Polymorphic audit trail |

---

## CRITICAL ISSUES

### Issue #1: Invoice Balance Race Condition

**Location:** `app/Services/Fee/PaymentService.php` lines 59-142

**Vulnerability:**
```php
// Line 73: Read invoice balance OUTSIDE of invoice lock
$balance = (string) $invoice->balance;

// Line 75: Validation based on potentially stale data
if (bccomp($amount, $balance, 2) > 0) {
    throw new \Exception('Payment amount cannot exceed balance');
}

// Lines 122-124: Update without fresh read
$newAmountPaid = bcadd((string) $invoice->amount_paid, $amount, 2);
$invoice->amount_paid = $newAmountPaid;
```

**Race Condition Scenario:**
1. Thread A reads invoice balance = 5000.00
2. Thread B reads invoice balance = 5000.00
3. Thread A validates amount 4000.00 ✓ (4000 < 5000)
4. Thread B validates amount 4500.00 ✓ (4500 < 5000)
5. Thread A records payment 4000.00 → balance becomes 1000.00
6. Thread B records payment 4500.00 → balance becomes 500.00
7. **Result:** Total payments (8500) exceed original invoice (5000)

**Impact:** Double payments, overpayment, balance corruption

**Fix Required:**
```php
public function recordPayment(StudentInvoice $invoice, ...)
{
    return DB::transaction(function () use ($invoice, ...) {
        // LOCK the invoice first
        $invoice = StudentInvoice::lockForUpdate()->find($invoice->id);

        // Now validate with fresh data
        $balance = (string) $invoice->balance;
        // ... rest of logic
    });
}
```

---

### Issue #2: Installment Payment Race Condition

**Location:** `app/Models/Fee/PaymentPlanInstallment.php` lines 89-104

**Vulnerability:**
```php
public function recordPayment(string $amount): void
{
    // Reads amount_paid without locking
    $this->amount_paid = bcadd((string) $this->amount_paid, $amount, 2);
    $this->save();
}
```

**Impact:** Concurrent payments can overwrite each other, losing payment records.

**Fix Required:** Add `lockForUpdate()` before reading `amount_paid`.

---

### Issue #3: Refund Processing Race Condition

**Location:** `app/Services/Fee/RefundService.php` lines 268-330

**Vulnerability:**
```php
public function processRefund(FeeRefund $refund, ...)
{
    return DB::transaction(function () use ($refund, ...) {
        $invoice = $refund->invoice;  // No lock
        $newAmountPaid = bcsub((string) $invoice->amount_paid, $amount, 2);
        $invoice->amount_paid = $newAmountPaid;
    });
}
```

**Impact:** Concurrent refund processing can corrupt invoice balance.

---

### Issue #4: Balance Carryover Duplication

**Location:** `app/Services/Fee/InvoiceService.php` lines 227-261

**Vulnerability:** Check-then-act pattern without locking:
```php
if (FeeBalanceCarryover::existsForStudentYearRange($studentId, $fromYear, $year)) {
    continue;  // But another thread may be creating right now!
}
$this->balanceService->carryForwardBalance(...);
```

**Impact:** Duplicate carryovers, inflated balances.

---

### Issue #5: No Double-Payment Prevention

**Location:** `app/Services/Fee/PaymentService.php`

**Missing:**
- No idempotency keys/request deduplication
- No receipt number uniqueness checking before recording
- No optimistic locking (version columns)

**Impact:** Network retries can create duplicate payments.

---

## HIGH PRIORITY ISSUES

### Issue #6: Dual Late Fee Tracking

**Location:** `StudentInvoiceItem` (TYPE_LATE_FEE) AND `LateFeeCharge` model

**Problem:** Late fees appear to be tracked in two places:
- `StudentInvoiceItem` with `item_type = 'late_fee'`
- Separate `LateFeeCharge` model

**Risk:** Data inconsistency, late fees counted twice.

**Recommendation:** Clarify which is the source of truth.

---

### Issue #7: Broken Discount Calculation

**Location:** `app/Models/Fee/StudentDiscount.php` `calculateAmount()` method

**Problem:** References non-existent attributes:
```php
if ($this->discountType->is_percentage) {  // UNDEFINED!
    return round(($subtotal * $this->discountType->value) / 100, 2);
}
return $this->discountType->value;  // UNDEFINED!
```

**Fix:** Should use `$this->discountType->percentage` (actual column name).

---

### Issue #8: Multiple Active Payment Plans Possible

**Location:** `payment_plans` table

**Problem:** No unique constraint on `(student_invoice_id, status)` where `status = 'active'`.

**Impact:** Multiple active plans per invoice can cause payment misallocation.

---

### Issue #9: Float Arithmetic in Balance Calculation

**Location:** `app/Models/Fee/StudentInvoice.php` line 199

**Problem:**
```php
$this->balance = $this->total_amount - $this->amount_paid;  // Float arithmetic!
```

**Should be:**
```php
$this->balance = bcsub((string) $this->total_amount, (string) $this->amount_paid, 2);
```

---

### Issue #10: Missing Student Model Relationships

**Location:** `app/Models/Student.php`

**Missing relationships:**
- `feeRefunds()`
- `paymentPlans()`
- `lateFeeCharges()`
- `feeBalanceCarryovers()`
- `feeClearances()`

---

## MEDIUM PRIORITY ISSUES

### Issue #11: N+1 Query Vulnerabilities

**Location:** `app/Models/Fee/StudentInvoice.php` accessors

```php
// Runs separate query PER invoice
$this->refunds()->where('status', 'processed')->sum('amount');
$this->lateFeeCharges()->where('waived', false)->sum('amount');
```

**Impact:** Loading 100 invoices = 200+ extra queries.

**Fix:** Use eager loading or add to select with subqueries.

---

### Issue #12: Missing Database Indexes

| Table | Missing Index |
|-------|---------------|
| `fee_payments` | `student_invoice_id` (FK) |
| `fee_refunds` | `fee_payment_id`, `student_invoice_id` |
| `student_invoice_items` | `fee_structure_id` |
| `late_fee_charges` | `waived` |
| `student_discounts` | `discount_type_id` |

---

### Issue #13: FormRequest Authorization Gaps

**Location:** `app/Http/Requests/Fee/StoreFeeStructureRequest.php`, `StoreStudentDiscountRequest.php`

```php
public function authorize(): bool
{
    return true;  // Should check Gate::allows('manage-fee-setup')
}
```

---

### Issue #14: Hardcoded Configuration

**Location:** `app/Http/Controllers/Fee/FeeSetupController.php` lines 71-105

Currency symbols, prefixes, and defaults are hardcoded. Should use config files.

---

## POSITIVE FINDINGS

### Transaction Usage

All services properly wrap multi-step operations in `DB::transaction()`:
- PaymentService: `recordPayment()`, `voidPayment()`
- InvoiceService: `generateInvoice()`, `cancelInvoice()`, `applyLateFee()`
- PaymentPlanService: `createPaymentPlan()`, `cancelPaymentPlan()`
- RefundService: All workflow methods
- BalanceService: `carryForwardBalance()`, `grantClearanceOverride()`

### Proper Locking in Sequence Generation

`FeePaymentSequence.php` correctly uses `lockForUpdate()`:
```php
public static function getNextInvoiceNumber(int $year): string
{
    return DB::transaction(function () use ($year) {
        $sequence = self::lockForUpdate()->find($year);
        $sequence->last_invoice_sequence++;
        $sequence->save();
        return sprintf('INV-%d-%04d', $year, $sequence->last_invoice_sequence);
    });
}
```

### bcmath for Monetary Calculations

All services use `bcadd()`, `bcsub()`, `bcmul()`, `bcdiv()`, `bccomp()` with proper 2-decimal precision.

### Comprehensive Audit Logging

`FeeAuditLog` provides polymorphic audit trail capturing:
- User, IP address, timestamp
- Old/new values for all changes
- Action types (create, update, delete, void, cancel, issue, carryover)

### Excellent Authorization

- Gate-based authorization: `manage-fee-setup`, `collect-fees`, `void-payments`, `approve-refunds`
- Three policies: `StudentInvoicePolicy`, `FeePaymentPolicy`, `FeeRefundPolicy`
- Historical data lock middleware

### Strong Input Validation

13 FormRequest classes with:
- Required field validation
- Foreign key existence checks
- Numeric bounds validation
- Date range validation
- Custom business logic validation

### No SQL Injection Risks

All queries use parameter binding. Only safe aggregate functions in raw queries.

---

## HOW THE MODULE WORKS

### Invoice Generation Flow

1. **Trigger:** Manual or bulk generation via `FeeCollectionController`
2. **Service:** `InvoiceService::generateInvoice()`
3. **Steps:**
   - Check for existing invoice (unique per student/year)
   - Get applicable fee structures for student's grade
   - Calculate discounts from `StudentDiscount`
   - Check for carryovers from previous years
   - Generate invoice number via `FeePaymentSequence` (atomic)
   - Create `StudentInvoice` with items
   - Log to `FeeAuditLog`

### Payment Recording Flow

1. **Trigger:** User submits payment via `FeeCollectionController::storePayment()`
2. **Validation:** `StorePaymentRequest` validates amount ≤ balance
3. **Service:** `PaymentService::recordPayment()`
4. **Steps:**
   - Generate receipt number (atomic via sequence)
   - If invoice has active payment plan, auto-allocate to next due installment
   - Create `FeePayment` record
   - Update `PaymentPlanInstallment` if applicable
   - Update `StudentInvoice.amount_paid` and recalculate balance
   - Send payment confirmation email (if enabled)
   - Log to `FeeAuditLog`

### Payment Plan Flow

1. **Creation:** From invoice show page → `PaymentPlanController::store()`
2. **Service:** `PaymentPlanService::createPaymentPlan()`
3. **Steps:**
   - Validate invoice has balance and no existing active plan
   - Create `PaymentPlan` record
   - Generate installments based on frequency (monthly/termly/custom)
   - Each installment gets calculated amount and due date
4. **Payment Allocation:**
   - When payment recorded, `PaymentService` checks for active plan
   - Allocates to oldest unpaid/partial installment first
   - Updates installment status (pending → partial → paid)
   - Checks plan completion when all installments paid

### Refund Workflow

1. **Request:** `RefundController::store()` creates pending refund
2. **Approval:** Manager calls `RefundController::approve()`
3. **Processing:** `RefundController::process()` finalizes
4. **Service:** `RefundService` handles each step
5. **Impact:** Reduces `invoice.amount_paid` and recalculates balance

### Notifications & Reminders

Scheduled commands in `app/Console/Kernel.php`:

| Command | Schedule | Purpose |
|---------|----------|---------|
| `fee:apply-late-fees` | Daily 00:05 | Apply late fees after grace period |
| `fee:send-reminders` | Daily 08:00 | Remind about upcoming due dates |
| `fee:send-overdue-notifications` | Daily 09:00 | Notify overdue invoices |

**Email Types:**
- `PaymentReminderMail` - Upcoming due date
- `PaymentOverdueMail` - Past due notice
- `PaymentConfirmationMail` - Payment received

**Admin CC:** All notifications CC'd to `admin_notification_email` if configured.

---

## VERIFICATION STEPS

After implementing fixes:

1. **Race Condition Testing:**
   - Use Apache JMeter or similar to send concurrent payment requests
   - Verify total payments never exceed invoice balance
   - Verify installment allocations are correct under load

2. **Balance Integrity Check:**
   ```sql
   SELECT id, invoice_number, total_amount, amount_paid, balance,
          (total_amount - amount_paid) as calculated_balance
   FROM student_invoices
   WHERE balance != (total_amount - amount_paid);
   ```

3. **Payment Plan Verification:**
   ```sql
   SELECT pp.id, pp.total_amount,
          SUM(ppi.amount_paid) as installments_paid,
          si.amount_paid as invoice_paid
   FROM payment_plans pp
   JOIN payment_plan_installments ppi ON pp.id = ppi.payment_plan_id
   JOIN student_invoices si ON pp.student_invoice_id = si.id
   GROUP BY pp.id, pp.total_amount, si.amount_paid
   HAVING SUM(ppi.amount_paid) != si.amount_paid;
   ```

4. **Late Fee Consistency:**
   - Verify late fees in `late_fee_charges` match `student_invoice_items` with type='late_fee'

---

## FIXES APPLIED (February 2026)

The following issues from this review have been fixed:

### Critical Race Condition Fixes

| Issue | File | Fix Applied |
|-------|------|-------------|
| #1 Invoice Balance Race | `PaymentService.php` | Added `lockForUpdate()` at start of `recordPayment()` |
| #2 Installment Payment Race | `PaymentService.php` | Added `lockForUpdate()` on installment before `recordPayment()` |
| #3 Refund Processing Race | `RefundService.php` | Added `lockForUpdate()` on refund and invoice in `processRefund()` |
| #4 Carryover Duplication | `InvoiceService.php` | Added `lockForUpdate()` on student in `checkAndAddAllCarryovers()` |
| Void Payment Race | `PaymentService.php` | Added `lockForUpdate()` on payment and invoice in `voidPayment()` |

### High Priority Fixes

| Issue | File | Fix Applied |
|-------|------|-------------|
| #7 Broken Discount Calculation | `StudentDiscount.php` | Fixed `calculateAmount()` to use `percentage` attribute |
| #8 Multiple Active Plans | `PaymentPlanService.php` | Added `lockForUpdate()` before active plan check |
| #9 Float Arithmetic | `StudentInvoice.php` | Changed `recalculateBalance()` to use bcmath |

### Medium Priority Fixes

| Issue | File | Fix Applied |
|-------|------|-------------|
| #12 Missing Indexes | New migration | Added indexes on FK columns and carryover unique constraint |
| #13 FormRequest Auth | `StoreFeeStructureRequest.php`, `StoreStudentDiscountRequest.php` | Added `Gate::allows('manage-fee-setup')` |

### Migration Required

Run the following to apply database indexes:
```bash
php artisan migrate
```

---

## CONCLUSION

The Fee Administration module is well-architected with strong security practices. The critical race condition vulnerabilities identified in this review have been fixed by adding `lockForUpdate()` calls to all financial operations that read-then-write invoice balances or payment allocations.

---

## OPERATIONAL DEPENDENCIES

The Fee Administration module requires the following services to be running for full functionality.

### 1. Queue Worker (Required for Email Notifications)

The module uses Laravel's queue system for sending email notifications. Without a running queue worker, the following features will not work:

- Payment confirmation emails
- Payment reminder emails
- Overdue payment notifications
- Bulk invoice generation (async mode)

**Start the queue worker:**
```bash
# Development
php artisan queue:work

# Production (with automatic restart on failure)
php artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

**Production Setup (Supervisor recommended):**

Create `/etc/supervisor/conf.d/laravel-worker.conf`:
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/project/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/project/storage/logs/worker.log
stopwaitsecs=3600
```

Then run:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

---

### 2. Task Scheduler (Required for Automated Processes)

The module relies on Laravel's task scheduler for automated fee management. Without the scheduler, the following features will not work:

| Command | Schedule | Purpose |
|---------|----------|---------|
| `fee:apply-late-fees` | Daily 00:05 | Automatically apply late fees after grace period |
| `fee:send-reminders` | Daily 08:00 | Send payment due date reminders |
| `fee:send-overdue-notifications` | Daily 09:00 | Send overdue payment notifications |

**Enable the scheduler:**

Add the following cron entry on your server:
```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

**Verify scheduler is working:**
```bash
php artisan schedule:list
```

---

### 3. Configuration Settings

The following settings must be configured in Fee Settings for notifications to work:

| Setting | Location | Purpose |
|---------|----------|---------|
| `fee.notify_on_payment` | Fee Settings > General | Enable payment confirmation emails |
| `fee.admin_notification_email` | Fee Settings > General | Admin CC for all fee notifications |
| `fee.enable_late_fees` | Fee Settings > General | Enable automatic late fee application |
| `fee.late_fee_grace_period` | Fee Settings > General | Days before late fee is applied |
| `fee.late_fee_type` | Fee Settings > General | Fixed amount or percentage |
| `fee.late_fee_amount` | Fee Settings > General | Late fee value |

---

### 4. Mail Configuration

Ensure your `.env` file has proper mail settings:

```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@school.com
MAIL_FROM_NAME="${APP_NAME}"
```

**Test mail configuration:**
```bash
php artisan tinker
>>> Mail::raw('Test email', function($m) { $m->to('test@example.com')->subject('Test'); });
```

---

### 5. Quick Setup Checklist

```bash
# 1. Run migrations
php artisan migrate

# 2. Clear caches
php artisan config:clear
php artisan cache:clear

# 3. Start queue worker (development)
php artisan queue:work

# 4. Verify scheduler commands
php artisan schedule:list

# 5. Test late fee command manually
php artisan fee:apply-late-fees --dry-run

# 6. Test reminder command manually
php artisan fee:send-reminders --dry-run
```

---

### 6. Monitoring

**Check queue status:**
```bash
php artisan queue:monitor redis:default --max=100
```

**View failed jobs:**
```bash
php artisan queue:failed
```

**Retry failed jobs:**
```bash
php artisan queue:retry all
```

**Check logs for fee operations:**
```bash
tail -f storage/logs/laravel.log | grep -i "fee\|payment\|invoice"
```

---

### 7. Laravel Forge Setup

If using Laravel Forge, you only need to create **one scheduled job** that runs Laravel's scheduler every minute. Laravel then handles all the individual fee commands internally.

**Create a new scheduled job with these settings:**

| Field | Value |
|-------|-------|
| **Name** | Laravel Scheduler |
| **Command** | `php /home/forge/demo.juniorschool.co/artisan schedule:run` |
| **User** | forge |
| **Frequency** | Every Minute |

The "Every Minute" frequency is important - Laravel's scheduler checks every minute which commands are due to run based on their individual schedules defined in the code.

You don't need to create separate jobs for each fee command. This single scheduler job will automatically run:
- `fee:apply-late-fees` at 00:05 daily
- `fee:send-reminders` at 08:00 daily
- `fee:send-overdue-notifications` at 09:00 daily

**Queue Worker in Forge:**

Forge automatically manages queue workers. Go to your site's **Queue** tab and enable the queue worker with these recommended settings:
- **Connection:** redis (or database)
- **Queue:** default
- **Processes:** 2
- **Maximum Seconds:** 60
- **Maximum Tries:** 3
