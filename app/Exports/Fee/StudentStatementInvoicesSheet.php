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

class StudentStatementInvoicesSheet implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    public function __construct(
        protected mixed $invoices
    ) {}

    public function collection(): Collection
    {
        $rows = [];

        // Handle both Collection and array
        $invoiceList = $this->invoices instanceof \Illuminate\Support\Collection
            ? $this->invoices
            : collect($this->invoices);

        foreach ($invoiceList as $invoice) {
            // Build description from invoice items
            $description = '';
            if (isset($invoice->items) && $invoice->items->count() > 0) {
                $itemDescriptions = $invoice->items->pluck('description')->filter()->toArray();
                $description = implode(', ', array_slice($itemDescriptions, 0, 3));
                if ($invoice->items->count() > 3) {
                    $description .= '...';
                }
            }

            // Determine status
            $status = 'Outstanding';
            $balance = (float) ($invoice->balance ?? 0);
            $totalAmount = (float) ($invoice->total_amount ?? 0);
            if ($balance <= 0) {
                $status = 'Paid';
            } elseif ($balance < $totalAmount) {
                $status = 'Partial';
            }

            $rows[] = [
                $invoice->invoice_number ?? '-',
                $invoice->issued_at ? $invoice->issued_at->format('Y-m-d') : '-',
                $description ?: 'Fee Invoice',
                'P ' . number_format($totalAmount, 2),
                $status,
            ];
        }

        return collect($rows);
    }

    public function headings(): array
    {
        return [
            'Invoice #',
            'Date',
            'Description',
            'Amount',
            'Status'
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
            'C' => 40,
            'D' => 18,
            'E' => 15
        ];
    }

    public function title(): string
    {
        return 'Invoices';
    }
}
