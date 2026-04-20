<?php

namespace App\Exports\Fee;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class StudentStatementTransactionsSheet implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    public function __construct(
        protected array $transactions
    ) {}

    public function collection(): Collection
    {
        $rows = [];

        foreach ($this->transactions as $transaction) {
            $debit = (float) ($transaction['debit'] ?? 0);
            $credit = (float) ($transaction['credit'] ?? 0);
            $balance = (float) ($transaction['balance'] ?? 0);

            $rows[] = [
                $transaction['date'] ?? '-',
                $transaction['description'] ?? '-',
                $debit > 0 ? 'P ' . number_format($debit, 2) : '-',
                $credit > 0 ? 'P ' . number_format($credit, 2) : '-',
                'P ' . number_format($balance, 2),
            ];
        }

        return collect($rows);
    }

    public function headings(): array
    {
        return [
            'Date',
            'Description',
            'Debit',
            'Credit',
            'Balance'
        ];
    }

    public function styles(Worksheet $sheet): void
    {
        // Header row styling
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);
        $sheet->getStyle('A1:E1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F2F2F2');
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 35,
            'C' => 18,
            'D' => 18,
            'E' => 18
        ];
    }

    public function title(): string
    {
        return 'Transactions';
    }
}
