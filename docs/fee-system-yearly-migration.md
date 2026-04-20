# Fee System Migration: Term-Based to Year-Based Schedule

**Document Version:** 1.0
**Date:** January 2026
**Status:** Planning

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Business Rationale](#2-business-rationale)
3. [Current System Analysis](#3-current-system-analysis)
4. [Target System Design](#4-target-system-design)
5. [Implementation Phases](#5-implementation-phases)
   - [Phase 1: Database Foundation](#phase-1-database-foundation)
   - [Phase 2: Model Layer Updates](#phase-2-model-layer-updates)
   - [Phase 3: Service Layer Updates](#phase-3-service-layer-updates)
   - [Phase 4: Controller & API Updates](#phase-4-controller--api-updates)
   - [Phase 5: User Interface Updates](#phase-5-user-interface-updates)
   - [Phase 6: Data Migration & Testing](#phase-6-data-migration--testing)
6. [Backward Compatibility Strategy](#6-backward-compatibility-strategy)
7. [Risk Assessment](#7-risk-assessment)
8. [Verification Checklist](#8-verification-checklist)

---

## 1. Executive Summary

### The Problem

The current Fee Administration system assigns fees on a **term-by-term basis** (3 terms per academic year). However, the school's actual operational model works differently:

- **Fee schedules are released annually** - not per term
- **Fees are fixed per grade per year** - students pay the same annual fee regardless of which term they're in
- The current system requires administrators to create identical fee structures for each term, leading to:
  - Redundant data entry
  - Risk of inconsistencies between terms
  - Confusion when generating invoices

### The Solution

Migrate to a **year-based fee schedule** system where:
- Fee structures are defined once per grade per year
- Students receive **one annual invoice** containing the full year's fees
- Multiple payments can be recorded against the single annual invoice throughout the year
- Historical term-based data remains intact for audit purposes

---

## 2. Business Rationale

### Why This Change Is Necessary

#### 2.1 Alignment with School Operations

Schools in Botswana typically release fee schedules at the beginning of each academic year. These schedules specify:
- Tuition fees per grade
- Levies (sports, activities, etc.)
- Optional fees (if applicable)

These amounts are **fixed for the entire year**, not variable by term. The current term-based system forces a workflow that doesn't match this reality.

**Current Workflow (Problematic):**
```
Admin receives annual fee schedule
    ↓
Must create fee structures for Term 1
    ↓
Must duplicate for Term 2
    ↓
Must duplicate for Term 3
    ↓
Risk of human error in data entry
```

**New Workflow (Improved):**
```
Admin receives annual fee schedule
    ↓
Creates fee structures once for the year
    ↓
System handles invoicing automatically
```

#### 2.2 Simplified Invoice Management

**Current State:**
- 3 invoices per student per year (one per term)
- Complex tracking of which payments apply to which term
- Balance carryover logic between terms
- Confusion about "clearing" a student (per term vs per year?)

**New State:**
- 1 invoice per student per year
- Clear balance: annual fee minus total payments
- Simple clearance: is the annual balance zero?

#### 2.3 Accurate Financial Reporting

Annual reporting becomes straightforward:
- Total fees expected = sum of all annual invoices
- Total collected = sum of all payments
- Outstanding = the difference

No need to aggregate across terms or handle edge cases where students have partial term payments.

#### 2.4 Reduced Administrative Burden

- No more creating 3x the fee structures
- No more "copy term to term" operations
- No confusion about which term a payment applies to
- Clearer communication with parents about outstanding amounts

---

## 3. Current System Analysis

### 3.1 Database Schema (Current Term-Based)

#### fee_structures table
```sql
CREATE TABLE fee_structures (
    id BIGINT PRIMARY KEY,
    fee_type_id BIGINT NOT NULL,      -- What fee (Tuition, Levy, etc.)
    grade_id BIGINT NOT NULL,          -- Which grade
    term_id BIGINT NOT NULL,           -- Which term (the problem!)
    year YEAR NOT NULL,                -- Academic year
    amount DECIMAL(10,2) NOT NULL,     -- Fee amount
    created_by BIGINT,

    UNIQUE (fee_type_id, grade_id, term_id)  -- Term-centric constraint
);
```

**Problem:** The unique constraint forces one record per fee type per grade per TERM. To set up 2026 fees for Grade 8 Tuition, admin must create 3 records (one for each term).

#### student_invoices table
```sql
CREATE TABLE student_invoices (
    id BIGINT PRIMARY KEY,
    invoice_number VARCHAR(20),
    student_id BIGINT NOT NULL,
    term_id BIGINT NOT NULL,           -- Tied to a term
    year INT NOT NULL,
    total_amount DECIMAL(12,2),
    amount_paid DECIMAL(12,2),
    balance DECIMAL(12,2),
    status ENUM('pending', 'partial', 'paid', 'cancelled'),
    ...
);
```

**Problem:** One invoice per student per term creates complexity in tracking annual balances.

#### student_discounts table
```sql
CREATE TABLE student_discounts (
    id BIGINT PRIMARY KEY,
    student_id BIGINT NOT NULL,
    discount_type_id BIGINT NOT NULL,
    term_id BIGINT NOT NULL,           -- Discount per term
    year INT NOT NULL,
    ...

    UNIQUE (student_id, discount_type_id, term_id)
);
```

**Problem:** Sibling discounts must be created 3 times per year (once per term).

### 3.2 Service Layer (Current)

**FeeStructureService::getFeeStructuresForGrade()**
```php
// Currently requires term_id
public function getFeeStructuresForGrade(int $gradeId, int $termId): Collection
{
    return FeeStructure::with(['feeType', 'grade', 'term'])
        ->where('grade_id', $gradeId)
        ->where('term_id', $termId)  // Term-centric
        ->whereHas('feeType', fn($q) => $q->where('is_active', true))
        ->get();
}
```

**InvoiceService::generateInvoice()**
```php
// Creates term-specific invoice
public function generateInvoice(Student $student, int $termId, User $user, ...): StudentInvoice
{
    // Checks for existing invoice for student/TERM
    // Gets fee structures for grade/TERM
    // Creates invoice tied to TERM
}
```

### 3.3 Key Pain Points

| Issue | Impact |
|-------|--------|
| Triple data entry for fee structures | Time waste, error-prone |
| Term-by-term invoice generation | Complex payment tracking |
| Term-based balance calculations | Confusing "which term am I paying for?" |
| Discount duplication per term | Administrative overhead |
| Balance carryover between terms | Edge cases, bugs |

---

## 4. Target System Design

### 4.1 Core Design Principles

1. **Fee structures are annual** - defined once per grade per year
2. **One invoice per student per year** - contains full annual fee
3. **Payments are recorded against the annual invoice** - no term association needed
4. **Historical data preserved** - term-based records remain for audit
5. **Dual-mode support** - system handles both modes during transition

### 4.2 Database Schema (New Year-Based)

#### fee_structures table (modified)
```sql
ALTER TABLE fee_structures
    MODIFY term_id BIGINT NULL,        -- Nullable for year-based
    ADD schedule_mode ENUM('term_based', 'year_based') DEFAULT 'term_based',
    ADD INDEX (fee_type_id, grade_id, year, schedule_mode);

-- Year-based uniqueness: one fee per type per grade per year
-- Application-level validation for: UNIQUE (fee_type_id, grade_id, year) WHERE schedule_mode = 'year_based'
```

#### student_invoices table (modified)
```sql
ALTER TABLE student_invoices
    MODIFY term_id BIGINT NULL,        -- Nullable for year-based
    ADD schedule_mode ENUM('term_based', 'year_based') DEFAULT 'term_based',
    ADD UNIQUE (student_id, year, schedule_mode);  -- One annual invoice per student
```

#### student_discounts table (modified)
```sql
ALTER TABLE student_discounts
    MODIFY term_id BIGINT NULL,        -- Nullable for year-based
    ADD schedule_mode ENUM('term_based', 'year_based') DEFAULT 'term_based';
```

### 4.3 Invoice Model (New)

**Annual Invoice Lifecycle:**

```
[Student Enrolled for Year]
         ↓
[Admin Generates Annual Invoice]
         ↓
    Invoice Created
    - Total: P 45,000 (full annual fee)
    - Paid: P 0
    - Balance: P 45,000
    - Status: PENDING
         ↓
[Parent Makes Payment: P 15,000]
         ↓
    Invoice Updated
    - Total: P 45,000
    - Paid: P 15,000
    - Balance: P 30,000
    - Status: PARTIAL
         ↓
[More Payments Throughout Year...]
         ↓
[Final Payment Clears Balance]
         ↓
    Invoice Updated
    - Total: P 45,000
    - Paid: P 45,000
    - Balance: P 0
    - Status: PAID
```

### 4.4 Fee Structure Workflow (New)

**Admin Setup Process:**

```
1. Navigate to Fee Setup → Fee Structures
2. Click "Add Year-Based Structure"
3. Select:
   - Year: 2026
   - Grade: Form 1
   - Fee Type: Tuition
   - Amount: P 25,000
4. Submit - ONE record created for entire year
5. Repeat for other fee types (Levy, etc.)
```

**Invoice Generation:**

```
1. Navigate to Invoices → Generate Invoice
2. Select Student
3. Select Year: 2026
4. System pulls all year-based fee structures for student's grade
5. Creates single annual invoice with all fees
```

---

## 5. Implementation Phases

### Phase 1: Database Foundation

**Objective:** Modify database schema to support year-based records while preserving existing data.

**Why This Phase First:**
- Database changes are foundational - all other changes depend on them
- Must be done carefully to avoid data loss
- Migrations can be rolled back if issues arise

#### Changes

**1.1 Migration: Add Year-Based Support to fee_structures**

**File:** `database/migrations/2026_01_XX_001_add_year_based_to_fee_structures.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            // Make term_id nullable for year-based entries
            $table->unsignedBigInteger('term_id')->nullable()->change();

            // Add schedule mode column
            $table->enum('schedule_mode', ['term_based', 'year_based'])
                  ->default('term_based')
                  ->after('year');

            // Add index for year-based lookups
            $table->index(
                ['fee_type_id', 'grade_id', 'year', 'schedule_mode'],
                'fee_structures_year_based_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->dropIndex('fee_structures_year_based_idx');
            $table->dropColumn('schedule_mode');

            // Restore term_id as required (may fail if nulls exist)
            $table->unsignedBigInteger('term_id')->nullable(false)->change();
        });
    }
};
```

**Why These Changes:**
- `term_id` becomes nullable because year-based structures don't reference a specific term
- `schedule_mode` column distinguishes old records from new year-based records
- New index optimizes queries that filter by year instead of term
- Default value `term_based` ensures existing records are unaffected

**1.2 Migration: Add Year-Based Support to student_invoices**

**File:** `database/migrations/2026_01_XX_002_add_year_based_to_student_invoices.php`

```php
<?php

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_invoices', function (Blueprint $table) {
            // Make term_id nullable for year-based invoices
            $table->unsignedBigInteger('term_id')->nullable()->change();

            // Add schedule mode column
            $table->enum('schedule_mode', ['term_based', 'year_based'])
                  ->default('term_based')
                  ->after('year');

            // Ensure one annual invoice per student per year
            $table->unique(
                ['student_id', 'year', 'schedule_mode'],
                'student_invoices_year_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('student_invoices', function (Blueprint $table) {
            $table->dropUnique('student_invoices_year_unique');
            $table->dropColumn('schedule_mode');
            $table->unsignedBigInteger('term_id')->nullable(false)->change();
        });
    }
};
```

**Why These Changes:**
- `term_id` nullable allows annual invoices that aren't tied to a specific term
- `schedule_mode` identifies which invoices are annual vs term-based
- Unique constraint prevents duplicate annual invoices for the same student/year

**1.3 Migration: Add Year-Based Support to student_discounts**

**File:** `database/migrations/2026_01_XX_003_add_year_based_to_student_discounts.php`

```php
<?php

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_discounts', function (Blueprint $table) {
            $table->unsignedBigInteger('term_id')->nullable()->change();
            $table->enum('schedule_mode', ['term_based', 'year_based'])
                  ->default('term_based')
                  ->after('year');
        });
    }

    public function down(): void
    {
        Schema::table('student_discounts', function (Blueprint $table) {
            $table->dropColumn('schedule_mode');
            $table->unsignedBigInteger('term_id')->nullable(false)->change();
        });
    }
};
```

**Why These Changes:**
- Discounts can now be assigned for an entire year instead of per term
- Reduces administrative burden of assigning the same discount 3 times

**1.4 Migration: Add Year-Based Support to student_clearances**

**File:** `database/migrations/2026_01_XX_004_add_year_based_to_student_clearances.php`

```php
<?php

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_clearances', function (Blueprint $table) {
            $table->year('year')->nullable()->after('term_id');
            $table->unsignedBigInteger('term_id')->nullable()->change();
            $table->enum('schedule_mode', ['term_based', 'year_based'])
                  ->default('term_based');
        });
    }

    public function down(): void
    {
        Schema::table('student_clearances', function (Blueprint $table) {
            $table->dropColumn(['year', 'schedule_mode']);
            $table->unsignedBigInteger('term_id')->nullable(false)->change();
        });
    }
};
```

**Why These Changes:**
- Clearance can be granted at the year level (cleared for entire year)
- `year` column explicitly stores which academic year the clearance applies to

**1.5 Migration: Add Year-Based Support to fee_balance_carryovers**

**File:** `database/migrations/2026_01_XX_005_add_year_based_to_fee_balance_carryovers.php`

```php
<?php

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_balance_carryovers', function (Blueprint $table) {
            $table->year('from_year')->nullable()->after('to_term_id');
            $table->year('to_year')->nullable()->after('from_year');
            $table->unsignedBigInteger('from_term_id')->nullable()->change();
            $table->unsignedBigInteger('to_term_id')->nullable()->change();
            $table->enum('schedule_mode', ['term_based', 'year_based'])
                  ->default('term_based');
        });
    }

    public function down(): void
    {
        Schema::table('fee_balance_carryovers', function (Blueprint $table) {
            $table->dropColumn(['from_year', 'to_year', 'schedule_mode']);
            $table->unsignedBigInteger('from_term_id')->nullable(false)->change();
            $table->unsignedBigInteger('to_term_id')->nullable(false)->change();
        });
    }
};
```

**Why These Changes:**
- Balance carryover can now work year-to-year instead of term-to-term
- Outstanding balance from 2025 carries to 2026 directly

---

### Phase 2: Model Layer Updates

**Objective:** Update Eloquent models to support the new schema and provide year-based query methods.

**Why This Phase Second:**
- Models are the data access layer - services depend on them
- Adding scopes and methods enables clean service layer code
- New functionality added without breaking existing code

#### Changes

**2.1 FeeStructure Model**

**File:** `app/Models/Fee/FeeStructure.php`

```php
<?php

namespace App\Models\Fee;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class FeeStructure extends Model
{
    use SoftDeletes;

    // Schedule mode constants
    const MODE_TERM_BASED = 'term_based';
    const MODE_YEAR_BASED = 'year_based';

    protected $fillable = [
        'fee_type_id',
        'grade_id',
        'term_id',
        'year',
        'schedule_mode',  // NEW
        'amount',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'year' => 'integer',
    ];

    // Existing relationships...
    public function feeType(): BelongsTo { /* ... */ }
    public function grade(): BelongsTo { /* ... */ }
    public function term(): BelongsTo { /* ... */ }
    public function createdBy(): BelongsTo { /* ... */ }

    // NEW SCOPES

    /**
     * Filter to year-based fee structures only.
     */
    public function scopeYearBased(Builder $query): Builder
    {
        return $query->where('schedule_mode', self::MODE_YEAR_BASED);
    }

    /**
     * Filter to term-based fee structures only.
     */
    public function scopeTermBased(Builder $query): Builder
    {
        return $query->where('schedule_mode', self::MODE_TERM_BASED);
    }

    /**
     * Get fee structures for a specific grade and year (year-based mode).
     */
    public function scopeForYearAndGrade(Builder $query, int $year, int $gradeId): Builder
    {
        return $query->where('year', $year)
                     ->where('grade_id', $gradeId)
                     ->where('schedule_mode', self::MODE_YEAR_BASED);
    }

    // NEW METHODS

    /**
     * Check if this fee structure is year-based.
     */
    public function isYearBased(): bool
    {
        return $this->schedule_mode === self::MODE_YEAR_BASED;
    }

    /**
     * Check if this fee structure is term-based.
     */
    public function isTermBased(): bool
    {
        return $this->schedule_mode === self::MODE_TERM_BASED;
    }
}
```

**Why These Changes:**
- Constants provide clear, reusable values for schedule mode
- `scopeYearBased()` enables queries like `FeeStructure::yearBased()->get()`
- `scopeForYearAndGrade()` provides the primary query pattern for annual invoicing
- Helper methods `isYearBased()` and `isTermBased()` make conditional logic cleaner

**2.2 StudentInvoice Model**

**File:** `app/Models/Fee/StudentInvoice.php`

```php
// Add to existing model:

const MODE_TERM_BASED = 'term_based';
const MODE_YEAR_BASED = 'year_based';

protected $fillable = [
    // ... existing fields ...
    'schedule_mode',  // NEW
];

/**
 * Filter to year-based invoices only.
 */
public function scopeYearBased(Builder $query): Builder
{
    return $query->where('schedule_mode', self::MODE_YEAR_BASED);
}

/**
 * Get year-based invoices for a specific year.
 */
public function scopeForYearOnly(Builder $query, int $year): Builder
{
    return $query->where('year', $year)
                 ->where('schedule_mode', self::MODE_YEAR_BASED);
}

/**
 * Check if this is a year-based invoice.
 */
public function isYearBased(): bool
{
    return $this->schedule_mode === self::MODE_YEAR_BASED;
}
```

**Why These Changes:**
- Enables querying annual invoices separately from term invoices
- `forYearOnly()` scope gets the single annual invoice for a student/year

**2.3 StudentDiscount Model**

**File:** `app/Models/Fee/StudentDiscount.php`

```php
// Add to existing model:

protected $fillable = [
    // ... existing fields ...
    'schedule_mode',  // NEW
];

/**
 * Filter to year-based discounts only.
 */
public function scopeYearBased(Builder $query): Builder
{
    return $query->where('schedule_mode', 'year_based');
}

/**
 * Get discounts for a specific year only (year-based).
 */
public function scopeForYearOnly(Builder $query, int $year): Builder
{
    return $query->where('year', $year)
                 ->where('schedule_mode', 'year_based');
}
```

**2.4 StudentClearance Model**

**File:** `app/Models/Fee/StudentClearance.php`

```php
// Add to existing model:

protected $fillable = [
    // ... existing fields ...
    'year',           // NEW
    'schedule_mode',  // NEW
];

/**
 * Filter to year-based clearances.
 */
public function scopeYearBased(Builder $query): Builder
{
    return $query->where('schedule_mode', 'year_based');
}

/**
 * Get clearance for a specific year.
 */
public function scopeForYear(Builder $query, int $year): Builder
{
    return $query->where('year', $year)
                 ->where('schedule_mode', 'year_based');
}
```

**2.5 FeeBalanceCarryover Model**

**File:** `app/Models/Fee/FeeBalanceCarryover.php`

```php
// Add to existing model:

protected $fillable = [
    // ... existing fields ...
    'from_year',      // NEW
    'to_year',        // NEW
    'schedule_mode',  // NEW
];

/**
 * Filter to year-based carryovers.
 */
public function scopeYearBased(Builder $query): Builder
{
    return $query->where('schedule_mode', 'year_based');
}

/**
 * Filter by source year.
 */
public function scopeFromYear(Builder $query, int $year): Builder
{
    return $query->where('from_year', $year);
}

/**
 * Filter by destination year.
 */
public function scopeToYear(Builder $query, int $year): Builder
{
    return $query->where('to_year', $year);
}
```

---

### Phase 3: Service Layer Updates

**Objective:** Add business logic for year-based fee operations while maintaining backward compatibility.

**Why This Phase Third:**
- Services contain business logic that controllers depend on
- Adding new methods doesn't break existing functionality
- Each new method can be tested independently

#### Changes

**3.1 FeeStructureService**

**File:** `app/Services/Fee/FeeStructureService.php`

```php
<?php

namespace App\Services\Fee;

use App\Models\Fee\FeeStructure;
use App\Models\Fee\FeeAuditLog;
use App\Models\Term;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FeeStructureService
{
    // ... existing methods remain unchanged ...

    /**
     * Get fee structures using automatic mode detection.
     *
     * Checks for year-based structures first. If found, returns those.
     * Otherwise falls back to term-based for backward compatibility.
     *
     * WHY: This method enables gradual migration. New years use year-based,
     * historical years continue to work with term-based structures.
     */
    public function getFeeStructuresForGradeAuto(int $gradeId, int $termId): Collection
    {
        $term = Term::findOrFail($termId);
        $year = $term->year;

        // Check for year-based structures first
        $yearBasedStructures = FeeStructure::with(['feeType', 'grade'])
            ->forYearAndGrade($year, $gradeId)
            ->active()
            ->get();

        if ($yearBasedStructures->isNotEmpty()) {
            return $yearBasedStructures;
        }

        // Fall back to term-based for historical data
        return $this->getFeeStructuresForGrade($gradeId, $termId);
    }

    /**
     * Get fee structures for a grade and year (year-based mode).
     *
     * WHY: Direct method when you know you want year-based structures.
     */
    public function getFeeStructuresForGradeYear(int $gradeId, int $year): Collection
    {
        return FeeStructure::with(['feeType', 'grade'])
            ->forYearAndGrade($year, $gradeId)
            ->whereHas('feeType', fn($q) => $q->where('is_active', true))
            ->get();
    }

    /**
     * Create a year-based fee structure.
     *
     * WHY: Year-based structures don't reference term_id. This method ensures
     * the correct schedule_mode is set and validates uniqueness.
     */
    public function createYearBasedFeeStructure(array $data, User $user): FeeStructure
    {
        return DB::transaction(function () use ($data, $user) {
            // Check for existing year-based structure
            $exists = FeeStructure::where('fee_type_id', $data['fee_type_id'])
                ->where('grade_id', $data['grade_id'])
                ->where('year', $data['year'])
                ->where('schedule_mode', FeeStructure::MODE_YEAR_BASED)
                ->exists();

            if ($exists) {
                throw new \Exception(
                    'A year-based fee structure already exists for this fee type, grade, and year.'
                );
            }

            $feeStructure = FeeStructure::create([
                'fee_type_id' => $data['fee_type_id'],
                'grade_id' => $data['grade_id'],
                'term_id' => null,  // Not used for year-based
                'year' => $data['year'],
                'schedule_mode' => FeeStructure::MODE_YEAR_BASED,
                'amount' => $data['amount'],
                'created_by' => $user->id,
            ]);

            FeeAuditLog::log(
                $feeStructure,
                FeeAuditLog::ACTION_CREATE,
                null,
                $feeStructure->toArray(),
                'Year-based fee structure created'
            );

            return $feeStructure;
        });
    }

    /**
     * Copy fee structures from one year to another (year-based).
     *
     * WHY: When a new academic year starts, administrators want to copy
     * last year's fee structures as a starting point, then adjust amounts.
     */
    public function copyStructuresToYear(int $fromYear, int $toYear, User $user): int
    {
        return DB::transaction(function () use ($fromYear, $toYear, $user) {
            $sourceStructures = FeeStructure::where('year', $fromYear)
                ->where('schedule_mode', FeeStructure::MODE_YEAR_BASED)
                ->get();

            $copiedCount = 0;

            foreach ($sourceStructures as $source) {
                $exists = FeeStructure::where('fee_type_id', $source->fee_type_id)
                    ->where('grade_id', $source->grade_id)
                    ->where('year', $toYear)
                    ->where('schedule_mode', FeeStructure::MODE_YEAR_BASED)
                    ->exists();

                if (!$exists) {
                    FeeStructure::create([
                        'fee_type_id' => $source->fee_type_id,
                        'grade_id' => $source->grade_id,
                        'term_id' => null,
                        'year' => $toYear,
                        'schedule_mode' => FeeStructure::MODE_YEAR_BASED,
                        'amount' => $source->amount,
                        'created_by' => $user->id,
                    ]);

                    $copiedCount++;
                }
            }

            if ($copiedCount > 0) {
                FeeAuditLog::log(
                    null,
                    FeeAuditLog::ACTION_CREATE,
                    null,
                    ['from_year' => $fromYear, 'to_year' => $toYear, 'count' => $copiedCount],
                    "Copied {$copiedCount} year-based fee structures from {$fromYear} to {$toYear}"
                );
            }

            return $copiedCount;
        });
    }

    /**
     * Calculate total fees for a grade and year.
     *
     * WHY: Provides a summary of all fees a student in this grade
     * will be charged for the entire year.
     */
    public function getTotalFeesForGradeYear(int $gradeId, int $year): array
    {
        $structures = FeeStructure::with('feeType')
            ->forYearAndGrade($year, $gradeId)
            ->whereHas('feeType', fn($q) => $q->where('is_active', true))
            ->get();

        $total = '0.00';
        $mandatory = '0.00';
        $optional = '0.00';
        $byCategory = [
            'tuition' => '0.00',
            'levy' => '0.00',
            'optional' => '0.00',
        ];

        foreach ($structures as $structure) {
            $amount = (string) $structure->amount;
            $total = bcadd($total, $amount, 2);

            $category = $structure->feeType->category ?? 'other';
            if (isset($byCategory[$category])) {
                $byCategory[$category] = bcadd($byCategory[$category], $amount, 2);
            }

            if ($structure->feeType->is_optional) {
                $optional = bcadd($optional, $amount, 2);
            } else {
                $mandatory = bcadd($mandatory, $amount, 2);
            }
        }

        return [
            'total' => $total,
            'mandatory' => $mandatory,
            'optional' => $optional,
            'by_category' => $byCategory,
        ];
    }
}
```

**3.2 InvoiceService**

**File:** `app/Services/Fee/InvoiceService.php`

```php
<?php

namespace App\Services\Fee;

use App\Models\Fee\StudentInvoice;
use App\Models\Fee\StudentInvoiceItem;
use App\Models\Fee\StudentDiscount;
use App\Models\Fee\FeeAuditLog;
use App\Models\Student;
use App\Models\StudentTerm;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    // ... existing methods remain unchanged ...

    /**
     * Generate an annual invoice for a student.
     *
     * WHY: This is the primary invoice generation method for the year-based system.
     * Creates a single invoice containing all annual fees for the student's grade.
     *
     * @param Student $student The student to invoice
     * @param int $year The academic year
     * @param User $user The user generating the invoice
     * @param string|null $dueDate Optional due date
     * @param string|null $notes Optional notes
     *
     * @return StudentInvoice The generated invoice
     * @throws \Exception If invoice already exists or no fee structures found
     */
    public function generateYearlyInvoice(
        Student $student,
        int $year,
        User $user,
        ?string $dueDate = null,
        ?string $notes = null
    ): StudentInvoice {
        return DB::transaction(function () use ($student, $year, $user, $dueDate, $notes) {
            // Check for existing annual invoice
            $existingInvoice = StudentInvoice::forStudent($student->id)
                ->forYearOnly($year)
                ->first();

            if ($existingInvoice) {
                throw new \Exception(
                    "An annual invoice already exists for this student for {$year}. " .
                    "Invoice #: {$existingInvoice->invoice_number}"
                );
            }

            // Get student's current grade
            // Note: Using the most recent StudentTerm record to determine grade
            $studentTerm = StudentTerm::where('student_id', $student->id)
                ->whereHas('term', fn($q) => $q->where('year', $year))
                ->where('status', 'Current')
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$studentTerm) {
                throw new \Exception(
                    'Student is not enrolled in any grade for this year.'
                );
            }

            $gradeId = $studentTerm->grade_id;

            // Get year-based fee structures
            $feeStructures = $this->feeStructureService->getFeeStructuresForGradeYear(
                $gradeId,
                $year
            );

            if ($feeStructures->isEmpty()) {
                throw new \Exception(
                    'No year-based fee structures found for this grade and year. ' .
                    'Please set up fee structures first.'
                );
            }

            // Get year-based discounts for the student
            $discounts = $this->getYearlyDiscountsForStudent($student->id, $year);

            // Calculate invoice items
            $invoiceItems = [];
            $subtotal = '0.00';
            $totalDiscount = '0.00';

            foreach ($feeStructures as $feeStructure) {
                $amount = (string) $feeStructure->amount;
                $itemDiscount = $this->calculateItemDiscount($feeStructure, $discounts);
                $netAmount = bcsub($amount, $itemDiscount, 2);

                $invoiceItems[] = [
                    'fee_structure_id' => $feeStructure->id,
                    'description' => $feeStructure->feeType->name,
                    'amount' => $amount,
                    'discount_amount' => $itemDiscount,
                    'net_amount' => $netAmount,
                ];

                $subtotal = bcadd($subtotal, $amount, 2);
                $totalDiscount = bcadd($totalDiscount, $itemDiscount, 2);
            }

            $totalAmount = bcsub($subtotal, $totalDiscount, 2);

            // Generate invoice number
            $invoiceNumber = $this->generateInvoiceNumber();

            // Create invoice
            $invoice = StudentInvoice::create([
                'invoice_number' => $invoiceNumber,
                'student_id' => $student->id,
                'term_id' => null,  // Year-based invoices don't reference a term
                'year' => $year,
                'schedule_mode' => StudentInvoice::MODE_YEAR_BASED,
                'subtotal_amount' => $subtotal,
                'discount_amount' => $totalDiscount,
                'total_amount' => $totalAmount,
                'amount_paid' => '0.00',
                'balance' => $totalAmount,
                'status' => StudentInvoice::STATUS_PENDING,
                'issued_at' => now(),
                'due_date' => $dueDate ?? now()->addDays(30),
                'notes' => $notes,
                'created_by' => $user->id,
            ]);

            // Create invoice items
            foreach ($invoiceItems as $item) {
                StudentInvoiceItem::create([
                    'student_invoice_id' => $invoice->id,
                    'fee_structure_id' => $item['fee_structure_id'],
                    'description' => $item['description'],
                    'amount' => $item['amount'],
                    'discount_amount' => $item['discount_amount'],
                    'net_amount' => $item['net_amount'],
                ]);
            }

            // Check for and add any carryover balance from previous year
            $this->checkAndAddYearlyCarryover($invoice, $student, $year);

            // Log audit trail
            FeeAuditLog::log(
                $invoice,
                FeeAuditLog::ACTION_CREATE,
                null,
                $invoice->toArray(),
                "Annual invoice generated for {$year}"
            );

            return $invoice->fresh(['items', 'student']);
        });
    }

    /**
     * Get year-based discounts for a student.
     *
     * WHY: Year-based discounts are applied to the entire annual invoice
     * rather than term-by-term.
     */
    private function getYearlyDiscountsForStudent(int $studentId, int $year): Collection
    {
        return StudentDiscount::forStudent($studentId)
            ->forYearOnly($year)
            ->active()
            ->with('discountType')
            ->get();
    }

    /**
     * Check for and add carryover balance from previous year.
     *
     * WHY: If a student has an outstanding balance from the previous year,
     * it should be added to their current year's invoice.
     */
    private function checkAndAddYearlyCarryover(
        StudentInvoice $invoice,
        Student $student,
        int $year
    ): void {
        $previousYear = $year - 1;

        // Get previous year's outstanding balance
        $previousBalance = $this->balanceService->getStudentBalanceForYear(
            $student->id,
            $previousYear
        );

        if (bccomp($previousBalance, '0.00', 2) > 0) {
            // Create carryover item on invoice
            StudentInvoiceItem::create([
                'student_invoice_id' => $invoice->id,
                'fee_structure_id' => null,
                'description' => "Balance carried forward from {$previousYear}",
                'amount' => $previousBalance,
                'discount_amount' => '0.00',
                'net_amount' => $previousBalance,
            ]);

            // Update invoice totals
            $invoice->subtotal_amount = bcadd($invoice->subtotal_amount, $previousBalance, 2);
            $invoice->total_amount = bcadd($invoice->total_amount, $previousBalance, 2);
            $invoice->balance = bcadd($invoice->balance, $previousBalance, 2);
            $invoice->save();
        }
    }
}
```

**3.3 BalanceService**

**File:** `app/Services/Fee/BalanceService.php`

```php
// Add these methods to existing service:

/**
 * Get student's balance for a specific year.
 *
 * WHY: Year-based balance is simpler - just look at the annual invoice.
 */
public function getStudentBalanceForYear(int $studentId, int $year): string
{
    $invoice = StudentInvoice::forStudent($studentId)
        ->forYearOnly($year)
        ->active()
        ->first();

    if (!$invoice) {
        return '0.00';
    }

    return (string) $invoice->balance;
}

/**
 * Check clearance status for a year.
 *
 * WHY: Clearance is now at the year level - is the annual invoice paid?
 */
public function checkYearClearance(int $studentId, int $year): array
{
    $balance = $this->getStudentBalanceForYear($studentId, $year);

    // Check for manual override
    $clearance = StudentClearance::forStudent($studentId)
        ->forYear($year)
        ->overrideGranted()
        ->first();

    $hasOverride = $clearance !== null;
    $overrideReason = $hasOverride ? $clearance->reason : null;

    // Cleared if balance is zero OR override granted
    $cleared = bccomp($balance, '0.00', 2) === 0 || $hasOverride;

    return [
        'cleared' => $cleared,
        'balance' => $balance,
        'has_override' => $hasOverride,
        'override_reason' => $overrideReason,
    ];
}

/**
 * Grant clearance override for a year.
 *
 * WHY: Sometimes students need clearance despite outstanding balance
 * (e.g., payment plan arrangements).
 */
public function grantYearlyClearanceOverride(
    int $studentId,
    int $year,
    User $grantedBy,
    string $reason,
    ?string $notes = null
): StudentClearance {
    return DB::transaction(function () use ($studentId, $year, $grantedBy, $reason, $notes) {
        // Check if override already exists
        $existing = StudentClearance::forStudent($studentId)
            ->forYear($year)
            ->first();

        if ($existing) {
            // Update existing record
            $existing->update([
                'override_granted' => true,
                'granted_by' => $grantedBy->id,
                'granted_at' => now(),
                'reason' => $reason,
                'notes' => $notes,
            ]);

            return $existing;
        }

        // Create new clearance record
        return StudentClearance::create([
            'student_id' => $studentId,
            'term_id' => null,  // Year-based
            'year' => $year,
            'schedule_mode' => 'year_based',
            'override_granted' => true,
            'granted_by' => $grantedBy->id,
            'granted_at' => now(),
            'reason' => $reason,
            'notes' => $notes,
        ]);
    });
}
```

**3.4 DiscountService**

**File:** `app/Services/Fee/DiscountService.php`

```php
// Add this method to existing service:

/**
 * Assign a discount to a student for the entire year.
 *
 * WHY: Year-based discounts apply to the annual invoice rather than
 * requiring separate discount assignments for each term.
 */
public function assignYearlyDiscountToStudent(array $data, User $user): StudentDiscount
{
    return DB::transaction(function () use ($data, $user) {
        // Check for existing year-based discount
        $exists = StudentDiscount::where('student_id', $data['student_id'])
            ->where('discount_type_id', $data['discount_type_id'])
            ->where('year', $data['year'])
            ->where('schedule_mode', 'year_based')
            ->exists();

        if ($exists) {
            throw new \Exception(
                'This discount is already assigned to the student for this year.'
            );
        }

        $studentDiscount = StudentDiscount::create([
            'student_id' => $data['student_id'],
            'discount_type_id' => $data['discount_type_id'],
            'term_id' => null,  // Year-based
            'year' => $data['year'],
            'schedule_mode' => 'year_based',
            'assigned_by' => $user->id,
            'notes' => $data['notes'] ?? null,
        ]);

        FeeAuditLog::log(
            $studentDiscount,
            FeeAuditLog::ACTION_CREATE,
            null,
            $studentDiscount->toArray(),
            'Year-based discount assigned to student'
        );

        return $studentDiscount;
    });
}
```

---

### Phase 4: Controller & API Updates

**Objective:** Expose year-based functionality through controllers and update routes.

**Why This Phase Fourth:**
- Controllers are the interface between views and services
- Adding new endpoints doesn't break existing functionality
- Can be tested via HTTP requests

#### Changes

**4.1 FeeSetupController**

**File:** `app/Http/Controllers/Fee/FeeSetupController.php`

```php
// Add these methods:

/**
 * Display year-based fee structures.
 */
public function yearStructures(Request $request): View
{
    Gate::authorize('manage-fee-setup');

    $year = $request->get('year', (int) date('Y'));

    $structures = FeeStructure::with(['feeType', 'grade', 'createdBy'])
        ->yearBased()
        ->where('year', $year)
        ->orderBy('grade_id')
        ->orderBy('fee_type_id')
        ->get();

    $years = FeeStructure::yearBased()
        ->select('year')
        ->distinct()
        ->orderBy('year', 'desc')
        ->pluck('year');

    // Add current and next year if not present
    $currentYear = (int) date('Y');
    if (!$years->contains($currentYear)) {
        $years->prepend($currentYear);
    }
    if (!$years->contains($currentYear + 1)) {
        $years->prepend($currentYear + 1);
    }

    return view('fees.setup.year-structures', [
        'structures' => $structures,
        'years' => $years->unique()->sortDesc()->values(),
        'selectedYear' => $year,
        'grades' => Grade::where('active', true)->orderBy('name')->get(),
        'feeTypes' => FeeType::active()->orderBy('name')->get(),
    ]);
}

/**
 * Store a new year-based fee structure.
 */
public function storeYearStructure(StoreYearFeeStructureRequest $request): RedirectResponse
{
    Gate::authorize('manage-fee-setup');

    try {
        $this->feeStructureService->createYearBasedFeeStructure(
            $request->validated(),
            $request->user()
        );

        return redirect()
            ->route('fees.setup.year-structures', ['year' => $request->year])
            ->with('success', 'Year-based fee structure created successfully.');
    } catch (\Exception $e) {
        return redirect()
            ->back()
            ->withInput()
            ->with('error', $e->getMessage());
    }
}

/**
 * Copy year-based structures to a new year.
 */
public function copyYearStructures(Request $request): RedirectResponse
{
    Gate::authorize('manage-fee-setup');

    $request->validate([
        'from_year' => ['required', 'integer', 'min:2020'],
        'to_year' => ['required', 'integer', 'min:2020', 'different:from_year'],
    ]);

    try {
        $count = $this->feeStructureService->copyStructuresToYear(
            $request->from_year,
            $request->to_year,
            $request->user()
        );

        return redirect()
            ->route('fees.setup.year-structures', ['year' => $request->to_year])
            ->with('success', "Copied {$count} fee structures from {$request->from_year} to {$request->to_year}.");
    } catch (\Exception $e) {
        return redirect()
            ->back()
            ->with('error', $e->getMessage());
    }
}
```

**4.2 FeeCollectionController**

**File:** `app/Http/Controllers/Fee/FeeCollectionController.php`

```php
// Add these methods:

/**
 * Show form for creating an annual invoice.
 */
public function createYearlyInvoice(Request $request): View
{
    $this->authorize('create', StudentInvoice::class);

    $student = null;
    if ($request->filled('student_id')) {
        $student = Student::with('currentGrade')->find($request->student_id);
    }

    $currentYear = (int) date('Y');
    $years = collect([$currentYear, $currentYear + 1]);

    return view('fees.collection.invoices.create-yearly', [
        'student' => $student,
        'years' => $years,
        'selectedYear' => $request->get('year', $currentYear),
    ]);
}

/**
 * Store a new annual invoice.
 */
public function storeYearlyInvoice(GenerateYearlyInvoiceRequest $request): RedirectResponse
{
    try {
        $student = Student::findOrFail($request->student_id);

        $invoice = $this->invoiceService->generateYearlyInvoice(
            $student,
            $request->year,
            $request->user(),
            $request->due_date,
            $request->notes
        );

        return redirect()
            ->route('fees.collection.invoices.show', $invoice)
            ->with('success', "Annual invoice #{$invoice->invoice_number} generated successfully.");
    } catch (\Exception $e) {
        return redirect()
            ->back()
            ->withInput()
            ->with('error', $e->getMessage());
    }
}

/**
 * Generate bulk annual invoices for a grade.
 */
public function storeBulkYearlyInvoices(GenerateBulkYearlyInvoicesRequest $request): RedirectResponse
{
    $validated = $request->validated();

    // Get all current students in the grade
    $students = Student::where('status', Student::STATUS_CURRENT)
        ->whereHas('currentEnrollment', fn($q) => $q->where('grade_id', $validated['grade_id']))
        ->get();

    $results = ['generated' => 0, 'skipped' => 0, 'errors' => 0];

    foreach ($students as $student) {
        try {
            // Check if invoice already exists
            $exists = StudentInvoice::forStudent($student->id)
                ->forYearOnly($validated['year'])
                ->exists();

            if ($exists) {
                $results['skipped']++;
                continue;
            }

            $this->invoiceService->generateYearlyInvoice(
                $student,
                $validated['year'],
                auth()->user(),
                $validated['due_date'] ?? null
            );

            $results['generated']++;
        } catch (\Exception $e) {
            $results['errors']++;
        }
    }

    $message = "Bulk annual invoices: {$results['generated']} created";
    if ($results['skipped'] > 0) {
        $message .= ", {$results['skipped']} skipped (existing)";
    }
    if ($results['errors'] > 0) {
        $message .= ", {$results['errors']} errors";
    }

    return redirect()
        ->route('fees.collection.invoices.index', ['year' => $validated['year']])
        ->with($results['errors'] > 0 ? 'warning' : 'success', $message);
}
```

**4.3 New Routes**

**File:** `routes/fees/setup.php`

```php
// Add these routes:

// Year-based fee structures
Route::get('/fees/setup/year-structures', [FeeSetupController::class, 'yearStructures'])
    ->name('fees.setup.year-structures');
Route::post('/fees/setup/year-structures', [FeeSetupController::class, 'storeYearStructure'])
    ->name('fees.setup.year-structures.store');
Route::post('/fees/setup/year-structures/copy', [FeeSetupController::class, 'copyYearStructures'])
    ->name('fees.setup.year-structures.copy');
```

**File:** `routes/fees/collection.php`

```php
// Add these routes:

// Annual invoices
Route::get('/fees/invoices/create-yearly', [FeeCollectionController::class, 'createYearlyInvoice'])
    ->name('fees.collection.invoices.create-yearly');
Route::post('/fees/invoices/yearly', [FeeCollectionController::class, 'storeYearlyInvoice'])
    ->name('fees.collection.invoices.store-yearly');
Route::post('/fees/invoices/bulk-yearly', [FeeCollectionController::class, 'storeBulkYearlyInvoices'])
    ->name('fees.collection.invoices.bulk-yearly');
```

---

### Phase 5: User Interface Updates

**Objective:** Create views for year-based fee management.

**Why This Phase Fifth:**
- Views depend on controller methods and data
- Can be developed incrementally alongside controllers
- User testing can begin once views are ready

#### Changes

**5.1 Year-Based Fee Structures Page**

**File:** `resources/views/fees/setup/year-structures.blade.php`

This view should include:
- Year selector dropdown
- Table of fee structures grouped by grade
- Add new structure form
- Edit/delete actions
- Copy structures between years functionality

**5.2 Annual Invoice Creation Page**

**File:** `resources/views/fees/collection/invoices/create-yearly.blade.php`

This view should include:
- Student search/selection
- Year selector
- Preview of fees for student's grade
- Due date picker
- Notes field
- Generate button

**5.3 Bulk Annual Invoice Generation**

**File:** `resources/views/fees/collection/invoices/bulk-yearly.blade.php`

This view should include:
- Grade selector
- Year selector
- Due date picker
- Generate button with confirmation

---

### Phase 6: Data Migration & Testing

**Objective:** Migrate existing data and verify system integrity.

**Why This Phase Last:**
- All code changes must be complete before migration
- Testing validates the entire system
- Migration can be rolled back if issues arise

#### Changes

**6.1 Migration Command**

**File:** `app/Console/Commands/MigrateToYearBasedFees.php`

```php
<?php

namespace App\Console\Commands;

use App\Models\Fee\FeeStructure;
use App\Models\Term;
use Illuminate\Console\Command;

class MigrateToYearBasedFees extends Command
{
    protected $signature = 'fee:migrate-to-year-based {year : The year to migrate}';
    protected $description = 'Create year-based fee structures from existing term-based ones';

    public function handle(): int
    {
        $year = (int) $this->argument('year');

        $this->info("Migrating fee structures for {$year}...");

        // Get Term 1 for this year as the source
        $term1 = Term::where('year', $year)->where('term', 1)->first();

        if (!$term1) {
            $this->error("Term 1 for {$year} not found.");
            return 1;
        }

        $termStructures = FeeStructure::where('term_id', $term1->id)
            ->where('schedule_mode', 'term_based')
            ->get();

        if ($termStructures->isEmpty()) {
            $this->warn("No term-based structures found for Term 1, {$year}.");
            return 0;
        }

        $created = 0;
        $skipped = 0;

        foreach ($termStructures as $structure) {
            // Check if year-based already exists
            $exists = FeeStructure::where('fee_type_id', $structure->fee_type_id)
                ->where('grade_id', $structure->grade_id)
                ->where('year', $year)
                ->where('schedule_mode', 'year_based')
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            FeeStructure::create([
                'fee_type_id' => $structure->fee_type_id,
                'grade_id' => $structure->grade_id,
                'term_id' => null,
                'year' => $year,
                'schedule_mode' => 'year_based',
                'amount' => $structure->amount,
                'created_by' => $structure->created_by,
            ]);

            $created++;
            $this->line("Created: {$structure->feeType->name} - Grade {$structure->grade->name}");
        }

        $this->info("Migration complete. Created: {$created}, Skipped: {$skipped}");

        return 0;
    }
}
```

**6.2 Testing Checklist**

See [Section 8: Verification Checklist](#8-verification-checklist) for complete testing requirements.

---

## 6. Backward Compatibility Strategy

### Dual-Mode Operation

The system supports both term-based and year-based modes simultaneously:

| Data Type | Old Records | New Records |
|-----------|-------------|-------------|
| Fee Structures | `schedule_mode = 'term_based'` | `schedule_mode = 'year_based'` |
| Invoices | `schedule_mode = 'term_based'` | `schedule_mode = 'year_based'` |
| Discounts | `schedule_mode = 'term_based'` | `schedule_mode = 'year_based'` |

### Auto-Detection Logic

When generating invoices or looking up fees, the system:
1. First checks for year-based records
2. If found, uses year-based logic
3. If not found, falls back to term-based logic

This allows:
- Historical data (2025 and earlier) to continue working
- New years (2026+) to use year-based approach
- Gradual migration without data loss

### No Historical Data Modification

- All existing term-based records remain unchanged
- Default value `term_based` ensures existing records are correctly identified
- Reports can show both types of data with appropriate labels

---

## 7. Risk Assessment

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| Data loss during migration | Low | High | No historical data modified; new columns only |
| Invoice calculation errors | Medium | High | Extensive testing; audit logging |
| User confusion (two modes) | Medium | Medium | Clear UI labels; training documentation |
| Performance degradation | Low | Medium | New indexes added; query optimization |
| Rollback complexity | Low | High | Feature flag allows reverting to term-based |

### Rollback Plan

If critical issues arise:
1. Set `FEE_SCHEDULE_MODE=term_based` in `.env`
2. System reverts to term-based logic
3. Year-based records remain but are not used
4. Investigate and fix issues
5. Re-enable year-based mode when ready

---

## 8. Verification Checklist

### 8.1 Fee Structure Setup

- [ ] Can create year-based fee structure for Grade X, Year 2026
- [ ] Year-based structures display correctly in setup UI
- [ ] Can edit year-based fee structure amount
- [ ] Can delete year-based fee structure
- [ ] Can copy year-based structures from 2026 to 2027
- [ ] Duplicate prevention works (same fee type/grade/year rejected)

### 8.2 Annual Invoice Generation

- [ ] Can generate annual invoice for a student
- [ ] Invoice contains correct fee items from year-based structures
- [ ] Invoice total matches sum of all fee items
- [ ] Discounts applied correctly
- [ ] Cannot create duplicate invoice for same student/year
- [ ] Historical term-based invoices still accessible

### 8.3 Payments on Annual Invoice

- [ ] Can record payment against annual invoice
- [ ] Invoice balance updates correctly
- [ ] Invoice status changes: Pending → Partial → Paid
- [ ] Multiple payments can be recorded
- [ ] Payment receipt shows correct details

### 8.4 Balance & Clearance

- [ ] Year-based balance calculation returns correct amount
- [ ] Clearance check returns `cleared: true` when balance is zero
- [ ] Clearance override can be granted for a year
- [ ] Carryover from previous year works correctly

### 8.5 Reports

- [ ] Dashboard shows year-based statistics when filtered by year
- [ ] Outstanding by grade report works with year filter
- [ ] Debtors list shows students with outstanding annual invoices
- [ ] Export to Excel includes year-based data

### 8.6 Backward Compatibility

- [ ] Existing term-based fee structures still visible
- [ ] Existing term-based invoices still accessible
- [ ] Existing payments still linked correctly
- [ ] Term-based reports still work for historical years

---

## Document History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | Jan 2026 | System | Initial document |

