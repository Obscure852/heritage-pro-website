# Fee Administration Database Schema

This document describes the database schema for the Fee Administration module.

## Migration Order

Migrations must be run in the following order:

| # | Migration File | Description |
|---|----------------|-------------|
| 1 | `2026_01_25_000001_create_fee_types_table.php` | Creates `fee_types` - fee categories (tuition, transport, meals, etc.) |
| 2 | `2026_01_25_000002_create_fee_structures_table.php` | Creates `fee_structures` - links fee types to grades/years with amounts |
| 3 | `2026_01_25_000005_create_fee_payment_sequences_table.php` | Creates `fee_payment_sequences` - invoice/receipt number generation |
| 4 | `2026_01_25_000006_create_student_invoices_table.php` | Creates `student_invoices` - annual invoices per student |
| 5 | `2026_01_25_000007_create_student_invoice_items_table.php` | Creates `student_invoice_items` - line items on invoices |
| 6 | `2026_01_25_000008_create_fee_payments_table.php` | Creates `fee_payments` - payment records |
| 7 | `2026_01_25_000009_create_fee_balance_carryovers_table.php` | Creates `fee_balance_carryovers` - year-to-year balance tracking |
| 8 | `2026_01_25_000010_create_fee_audit_logs_table.php` | Creates `fee_audit_logs` - audit trail |
| 9 | `2026_01_26_200000_convert_fee_tables_to_year_based.php` | Adds year-based filtering |
| 10 | `2026_01_27_000001_add_item_type_to_student_invoice_items.php` | Adds `item_type` column for carryover distinction |
| 11 | `2026_01_27_000002_add_fee_carryover_settings.php` | Adds carryover configuration settings |
| 12 | `2026_02_01_114636_make_fee_structure_id_nullable_in_student_invoice_items.php` | Allows carryover items without fee structure |
| 13 | `2026_02_02_061350_add_refund_support_to_fees.php` | Creates `fee_refunds` table, adds `credit_balance` to invoices |
| 14 | `2026_02_02_061440_add_refund_sequence_to_fee_payment_sequences.php` | Adds refund number sequence |

---

## Core Tables

### fee_types
Defines categories of fees charged by the school.

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `code` | varchar(10) | Unique code (e.g., TUI, TRN, MEL) |
| `name` | varchar(100) | Display name |
| `category` | varchar(50) | Category: tuition, transport, boarding, meals, activities, other |
| `description` | text | Optional description |
| `is_active` | boolean | Whether fee type is active |
| `is_mandatory` | boolean | Whether fee applies to all students |
| `is_recurring` | boolean | Whether fee recurs annually |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |
| `deleted_at` | timestamp | Soft delete |

### fee_structures
Links fee types to grades and years with specific amounts.

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `fee_type_id` | bigint | FK to fee_types |
| `grade_id` | bigint | FK to grades (nullable for all-grade fees) |
| `year` | int | Academic year |
| `amount` | decimal(12,2) | Fee amount |
| `effective_from` | date | When this rate takes effect |
| `effective_until` | date | When this rate expires |
| `notes` | text | Optional notes |
| `created_by` | bigint | FK to users |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |
| `deleted_at` | timestamp | Soft delete |

**Unique constraint**: (fee_type_id, grade_id, year)

### student_invoices
Annual invoices for each student.

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `invoice_number` | varchar(20) | Unique invoice number (e.g., INV-2026-00001) |
| `student_id` | bigint | FK to students |
| `year` | int | Academic year |
| `subtotal_amount` | decimal(12,2) | Total before discounts |
| `discount_amount` | decimal(12,2) | Total discounts applied |
| `total_amount` | decimal(12,2) | Final amount due |
| `amount_paid` | decimal(12,2) | Total payments received |
| `balance` | decimal(12,2) | Outstanding balance |
| `credit_balance` | decimal(12,2) | Overpayment credit |
| `status` | varchar(20) | draft, issued, partial, paid, overdue, cancelled |
| `issued_at` | datetime | When invoice was issued |
| `due_date` | date | Payment due date |
| `notes` | text | Optional notes |
| `created_by` | bigint | FK to users |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |
| `deleted_at` | timestamp | Soft delete |

**Unique constraint**: (student_id, year)

### student_invoice_items
Line items on invoices.

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `student_invoice_id` | bigint | FK to student_invoices |
| `fee_structure_id` | bigint | FK to fee_structures (nullable for carryovers) |
| `item_type` | varchar(20) | Type: fee, carryover, adjustment, credit_note |
| `description` | varchar(255) | Item description |
| `amount` | decimal(12,2) | Original amount |
| `discount_amount` | decimal(12,2) | Discount applied |
| `net_amount` | decimal(12,2) | Final amount (amount - discount) |
| `source_year` | int | For carryovers: the year the balance originated |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

### fee_payments
Payment records against invoices.

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `student_invoice_id` | bigint | FK to student_invoices |
| `receipt_number` | varchar(20) | Unique receipt number |
| `amount` | decimal(12,2) | Payment amount |
| `payment_method` | varchar(20) | cash, bank_transfer, mobile_money, card, other |
| `payment_date` | date | Date payment was made |
| `reference_number` | varchar(100) | External reference (cheque #, transfer ID) |
| `notes` | text | Optional notes |
| `received_by` | bigint | FK to users (who processed payment) |
| `voided` | boolean | Whether payment was voided |
| `voided_at` | datetime | When voided |
| `voided_by` | bigint | FK to users |
| `void_reason` | text | Reason for voiding |
| `year` | int | Academic year (for reporting) |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |
| `deleted_at` | timestamp | Soft delete |

### fee_refunds
Refund and credit note records.

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `refund_number` | varchar(20) | Unique refund/credit note number |
| `student_invoice_id` | bigint | FK to student_invoices |
| `fee_payment_id` | bigint | FK to fee_payments (nullable for credit notes) |
| `student_id` | bigint | FK to students |
| `type` | varchar(20) | full_refund, partial_refund, credit_note |
| `amount` | decimal(12,2) | Refund/credit amount |
| `reason` | text | Reason for refund |
| `method` | varchar(20) | cash, bank_transfer, mobile_money, cheque, credit_to_account |
| `status` | varchar(20) | pending, approved, processed, rejected |
| `requested_by` | bigint | FK to users |
| `approved_by` | bigint | FK to users |
| `approved_at` | datetime | |
| `processed_by` | bigint | FK to users |
| `processed_at` | datetime | |
| `rejection_reason` | text | Reason if rejected |
| `notes` | text | Additional notes |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

### fee_balance_carryovers
Tracks balance carried over between years.

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `student_id` | bigint | FK to students |
| `from_year` | int | Source year |
| `to_year` | int | Destination year |
| `amount` | decimal(12,2) | Balance carried over |
| `from_invoice_id` | bigint | FK to student_invoices (source) |
| `to_invoice_id` | bigint | FK to student_invoices (destination) |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

### fee_payment_sequences
Manages sequence numbers for invoices, receipts, and refunds.

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `year` | int | Academic year |
| `last_invoice_sequence` | int | Last used invoice number |
| `last_receipt_sequence` | int | Last used receipt number |
| `last_refund_sequence` | int | Last used refund number |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

**Unique constraint**: (year)

---

## Entity Relationship Diagram

```
                                ┌──────────────┐
                                │  fee_types   │
                                └──────┬───────┘
                                       │
                                       │ 1:M
                                       ▼
┌──────────┐                   ┌───────────────────┐
│  grades  │──────────────────▶│  fee_structures   │
└──────────┘      1:M          └─────────┬─────────┘
                                         │
                                         │ 1:M
                                         ▼
┌──────────────┐              ┌──────────────────────────┐
│   students   │◀─────────────│  student_invoice_items   │
└──────┬───────┘              └────────────┬─────────────┘
       │                                   │
       │ 1:M                               │ M:1
       ▼                                   │
┌──────────────────┐                       │
│ student_invoices │◀──────────────────────┘
└────────┬─────────┘
         │
         │ 1:M
         ▼
┌────────────────┐
│  fee_payments  │
└────────┬───────┘
         │
         │ 1:M
         ▼
┌────────────────┐
│  fee_refunds   │
└────────────────┘
```

---

## Important Constraints

1. **One Invoice Per Student Per Year**: `student_invoices` has a unique constraint on (student_id, year)

2. **Sequence Number Locking**: `fee_payment_sequences` uses `lockForUpdate()` to prevent duplicate numbers

3. **Soft Deletes**: Most tables use soft deletes to preserve historical data

4. **Balance Integrity**: The `StudentInvoiceObserver` automatically recalculates balance when amounts change

5. **Year Locking**: The `PreventHistoricalFeeModification` middleware blocks modifications to locked years

---

## Settings (stored in sms_api_settings)

| Key | Description |
|-----|-------------|
| `fee.currency_symbol` | Currency symbol (default: P) |
| `fee.currency_code` | ISO currency code (default: BWP) |
| `fee.currency_position` | before or after |
| `fee.receipt_prefix` | Receipt number prefix (default: RCP) |
| `fee.invoice_prefix` | Invoice number prefix (default: INV) |
| `fee.carryover_lookback_years` | Years to check for carryovers |
| `fee.auto_lock_past_years` | Auto-lock historical years |
| `fee.locked_until_year` | Manually locked year threshold |
