<?php

namespace App\Exports\Fee;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DailyCollectionsExport implements FromArray, WithHeadings, WithStyles, WithTitle
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function title(): string
    {
        return 'Daily Collections';
    }

    public function headings(): array
    {
        return [
            'Receipt #',
            'Student Name',
            'Student #',
            'Amount (P)',
            'Method',
            'Reference',
            'Received By',
            'Time',
        ];
    }

    public function array(): array
    {
        $rows = [];

        foreach ($this->data['payments'] as $payment) {
            $rows[] = [
                $payment['receipt_number'],
                $payment['student_name'],
                $payment['student_number'],
                number_format((float) $payment['amount'], 2),
                ucfirst($payment['payment_method']),
                $payment['reference_number'] ?? '-',
                $payment['received_by'],
                $payment['created_at'],
            ];
        }

        // Add summary rows
        $rows[] = [];
        $rows[] = ['', '', 'TOTAL:', number_format((float) $this->data['total_collected'], 2), '', '', '', ''];

        // Add breakdown by method
        $rows[] = [];
        $rows[] = ['Breakdown by Payment Method'];
        foreach ($this->data['by_method'] as $method => $info) {
            $rows[] = [ucfirst($method), '', '', number_format((float) $info['total'], 2), $info['count'] . ' payments', '', '', ''];
        }

        // Add breakdown by collector
        $rows[] = [];
        $rows[] = ['Breakdown by Collector'];
        foreach ($this->data['by_collector'] as $collector) {
            $rows[] = [$collector['name'], '', '', number_format((float) $collector['total'], 2), $collector['count'] . ' payments', '', '', ''];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 11]],
        ];
    }
}
