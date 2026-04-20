<?php

namespace App\Exports\Fee;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class StudentStatementExport implements WithMultipleSheets
{
    public function __construct(
        protected array $statement,
        protected string $studentName
    ) {}

    public function sheets(): array
    {
        return [
            'Summary' => new StudentStatementSummarySheet($this->statement, $this->studentName),
            'Invoices' => new StudentStatementInvoicesSheet($this->statement['invoices'] ?? collect([])),
            'Payments' => new StudentStatementPaymentsSheet($this->statement['payments'] ?? collect([])),
            'Transactions' => new StudentStatementTransactionsSheet($this->statement['balance_history'] ?? []),
        ];
    }
}
