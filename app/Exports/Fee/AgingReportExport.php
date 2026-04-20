<?php

namespace App\Exports\Fee;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AgingReportExport implements WithMultipleSheets
{
    public function __construct(
        protected array $data,
        protected ?string $termName = null
    ) {}

    public function sheets(): array
    {
        return [
            'Summary' => new AgingReportSummarySheet($this->data['summary'] ?? []),
            'Details' => new AgingReportDetailsSheet($this->data['details'] ?? []),
        ];
    }
}
