# Fee Administration Year-End Procedures

This document describes the procedures for transitioning fee data from one academic year to the next.

---

## Overview

The fee administration module operates on an annual basis. At the end of each academic year, several tasks must be completed to prepare for the new year:

1. **Review outstanding balances** - Identify students with unpaid fees
2. **Close the current year** - Lock modifications to historical data
3. **Copy fee structures** - Set up fees for the new year
4. **Carry forward balances** - Transfer outstanding balances to new invoices
5. **Initialize sequences** - Reset invoice/receipt numbering for the new year

---

## Pre-Year-End Checklist

Before starting year-end procedures, verify:

- [ ] All payments for the current year have been recorded
- [ ] Voided payments have been properly documented
- [ ] Outstanding balance reports have been generated
- [ ] Collection rate statistics have been reviewed
- [ ] Backup of the database has been created

---

## Procedure 1: Review Outstanding Balances

### Purpose
Identify all students with unpaid fees before closing the year.

### Steps

1. Navigate to **Fee Reports → Debtors List**
2. Select the current year in the filter
3. Export the debtors list for record-keeping
4. Review students with significant balances
5. Contact parents/guardians for payment arrangements

### Related Code
```php
// Get all students with outstanding balance
$balanceService = app(BalanceService::class);
$debtors = $balanceService->getOutstandingStudentsForYear($year);
```

### Reports to Generate
- **Debtors List** - All students with outstanding balances
- **Aging Report** - Breakdown by 30/60/90 day buckets
- **Grade Comparison** - Outstanding balances by grade
- **Collection Summary** - Year-end collection statistics

---

## Procedure 2: Close the Current Year

### Purpose
Lock the year to prevent accidental modifications to historical data.

### Steps

1. Navigate to **Fee Setup → General Settings**
2. Scroll to the "Year Locking" section
3. Select the year to lock in "Lock Years Up To"
4. Optionally enable "Auto-lock Past Years"
5. Click "Save Settings"

### Important Notes
- Once a year is locked, fee structures, invoices, and payments cannot be modified
- Only administrators with the `override-historical-year-lock` permission can make changes
- This is a reversible action (the year can be unlocked if needed)

### Settings Involved
| Setting | Key | Description |
|---------|-----|-------------|
| Auto-lock Past Years | `fee.auto_lock_past_years` | Automatically lock all past years |
| Locked Until Year | `fee.locked_until_year` | Specific year up to which data is locked |

---

## Procedure 3: Copy Fee Structures

### Purpose
Set up fee amounts for the new academic year based on the previous year.

### Steps

1. Navigate to **Fee Setup → Fee Structures**
2. Click "Copy Structures" button
3. Select source year (previous year)
4. Select destination year (new year)
5. Click "Copy Structures"
6. Review and adjust amounts as needed

### Related Code
```php
// Copy fee structures from one year to another
$feeStructureService = app(FeeStructureService::class);
$copiedCount = $feeStructureService->copyStructuresToYear($fromYear, $toYear, $user);
```

### Post-Copy Tasks
- Review each fee structure for the new year
- Adjust amounts for any fee increases
- Verify mandatory/optional fee settings
- Check grade assignments are correct

### Important Notes
- Copying does NOT overwrite existing structures for the destination year
- Only structures that don't exist in the destination are created
- The original amounts are copied (adjust manually if fees changed)

---

## Procedure 4: Carry Forward Balances

### Purpose
Transfer outstanding balances from the previous year to new invoices.

### How It Works
When generating invoices for the new year, the system:
1. Checks for outstanding balances from previous years
2. Creates carryover line items on the new invoice
3. Records the carryover in `fee_balance_carryovers` table

### Configuration

1. Navigate to **Fee Setup → General Settings**
2. Set "Carryover Lookback Years" (default: 3)
3. This determines how many years back to check for balances

### During Invoice Generation
- Carryover balances are **automatically included** when generating invoices
- Carryover items appear as separate line items with type `carryover`
- The source year is tracked in the `source_year` column

### Manual Carryover
```php
// Manually carry forward a balance
$balanceService = app(BalanceService::class);
$carryover = $balanceService->carryForwardBalance($student, $fromYear, $toYear, $user);
```

### Carryover Item Format
On invoices, carryover items appear as:
```
Balance brought forward from 2025    P 1,500.00
```

### Important Notes
- Carryovers are only created if there's an actual outstanding balance
- Duplicate carryovers are prevented (checked before creation)
- Discounts do NOT apply to carryover amounts
- Carryover amounts cannot be modified (original balance is preserved)

---

## Procedure 5: Initialize Sequences

### Purpose
Ensure invoice and receipt numbers continue properly in the new year.

### How It Works
- Sequences are tracked per year in `fee_payment_sequences` table
- Each year starts fresh with sequence 1
- Numbers are formatted as: `PREFIX-YYYY-NNNNN` (e.g., INV-2027-00001)

### Automatic Initialization
Sequences are automatically created when the first invoice/receipt is generated for a new year. No manual action required.

### Manual Verification
```sql
-- Check sequence numbers for a year
SELECT * FROM fee_payment_sequences WHERE year = 2027;
```

### Sequence Fields
| Field | Description |
|-------|-------------|
| `last_invoice_sequence` | Last used invoice number |
| `last_receipt_sequence` | Last used receipt number |
| `last_refund_sequence` | Last used refund number |

---

## Year-End Timeline

### Recommended Schedule

| When | Task |
|------|------|
| 4 weeks before year end | Generate outstanding balance reports |
| 3 weeks before year end | Send payment reminders to debtors |
| 2 weeks before year end | Final payment collection push |
| 1 week before year end | Generate year-end reports |
| Year end | Lock the current year |
| New year start | Copy fee structures |
| New year start | Adjust fee amounts |
| As needed | Generate invoices (carryovers included automatically) |

---

## Troubleshooting

### Issue: Cannot modify invoices from previous year
**Cause:** Year is locked
**Solution:**
1. Check if auto-lock is enabled
2. Administrator can override using the `override-historical-year-lock` permission
3. Or temporarily unlock the year in settings (not recommended)

### Issue: Carryover not appearing on invoice
**Cause:**
- No outstanding balance for previous years
- Carryover lookback years setting too low
- Carryover already exists for this student/year

**Solution:**
1. Verify balance exists: `SELECT * FROM student_invoices WHERE student_id = X AND balance > 0`
2. Check carryover lookback setting
3. Check for existing carryover: `SELECT * FROM fee_balance_carryovers WHERE student_id = X`

### Issue: Duplicate invoice numbers
**Cause:** Rare race condition
**Solution:** The system uses pessimistic locking to prevent this. If it occurs, manually update the sequence in `fee_payment_sequences`.

---

## Database Tables Involved

| Table | Purpose |
|-------|---------|
| `fee_structures` | Fee amounts per grade/year |
| `student_invoices` | Annual invoices |
| `student_invoice_items` | Line items (fees and carryovers) |
| `fee_payments` | Payment records |
| `fee_balance_carryovers` | Carryover tracking |
| `fee_payment_sequences` | Invoice/receipt numbering |
| `sms_api_settings` | Configuration settings |

---

## Related Documentation

- [Fee Database Schema](./FEE_DATABASE_SCHEMA.md) - Complete database documentation
- [Fee Administration Improvements](./FEE_ADMINISTRATION_IMPROVEMENTS.md) - Module analysis and improvements
