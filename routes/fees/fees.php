<?php

/*
|--------------------------------------------------------------------------
| Fees Routes
|--------------------------------------------------------------------------
|
| This file includes all fee-related route files.
| Each sub-module is organized into its own file for maintainability.
|
| Sub-modules:
| - setup.php: Fee types and fee structures management
| - collection.php: Payment collection and processing
| - refunds.php: Refunds and credit notes
| - reports.php: Fee reports and analytics
| - discounts.php: Discount management
| - balance.php: Balance and clearance management
| - audit.php: Audit trail viewing
|
*/

require __DIR__.'/setup.php';
require __DIR__.'/discounts.php';
require __DIR__.'/collection.php';
require __DIR__.'/payment-plans.php';
require __DIR__.'/refunds.php';
require __DIR__.'/balance.php';
require __DIR__.'/reports.php';
require __DIR__.'/audit.php';
