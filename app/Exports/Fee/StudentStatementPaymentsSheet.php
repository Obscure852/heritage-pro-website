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

class StudentStatementPaymentsSheet implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    public function __construct(
        protected mixed $payments
    ) {}

    public function collection(): Collection
    {
        $rows = [];

        // Handle both Collection and array
        $paymentList = $this->payments instanceof \Illuminate\Support\Collection
            ? $this->payments
            : collect($this->payments);

        foreach ($paymentList as $payment) {
            $rows[] = [
                $payment->receipt_number ?? '-',
                $payment->payment_date ? $payment->payment_date->format('Y-m-d') : '-',
                ucfirst(str_replace('_', ' ', $payment->payment_method ?? '-')),
                'P ' . number_format((float) ($payment->amount ?? 0), 2),
                $payment->reference_number ?? '-',
            ];
        }

        return collect($rows);
    }

    public function headings(): array
    {
        return [
            'Receipt #',
            'Date',
            'Method',
            'Amount',
            'Reference'
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
            'A' => 18,
            'B' => 15,
            'C' => 18,
            'D' => 18,
            'E' => 20
        ];
    }

    public function title(): string
    {
        return 'Payments';
    }
}
