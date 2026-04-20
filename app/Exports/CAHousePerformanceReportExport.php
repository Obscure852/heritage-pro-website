<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CAHousePerformanceReportExport implements FromArray, WithStyles, WithTitle{
    protected $housePerformance;
    protected $schoolData;
    protected $type;
    protected $rowCounter;

    public function __construct($housePerformance, $schoolData, $type){
        $this->housePerformance = $housePerformance;
        $this->schoolData = $schoolData;
        $this->type = $type;
        $this->rowCounter = 1;
    }

    public function array(): array{
        $rows = [];

        $title = 'House Performance Analysis - ' . ucfirst($this->type);
        $rows[] = [$title];
        $this->rowCounter++;

        $schoolInfo = [
            [$this->schoolData->school_name ?? 'School Name'],
            [$this->schoolData->physical_address ?? 'Physical Address'],
            [$this->schoolData->postal_address ?? 'Postal Address'],
            ['Tel: ' . ($this->schoolData->telephone ?? 'N/A') . ' Fax: ' . ($this->schoolData->fax ?? 'N/A')],
        ];
        foreach ($schoolInfo as $info) {
            $rows[] = $info;
            $this->rowCounter++;
        }

        $rows[] = [''];
        $this->rowCounter++;

        $headers = [
            'House',
            'A*',
            'A',
            'B',
            'C',
            '% CREDIT',
            'D',
            'E',
            '% PASS',
            'F',
            'G',
            'U',
            'TOTAL',
        ];
        $rows[] = $headers;
        $this->rowCounter++;

        foreach ($this->housePerformance as $houseName => $data) {
            $row = [
                $houseName,
                $data['A*'] ?? 0,
                $data['A'] ?? 0,
                $data['B'] ?? 0,
                $data['C'] ?? 0,
                ($data['% CREDIT'] ?? 0) . '%',
                $data['D'] ?? 0,
                $data['E'] ?? 0,
                ($data['% PASS'] ?? 0) . '%',
                $data['F'] ?? 0,
                $data['G'] ?? 0,
                $data['U'] ?? 0,
                $data['TOTAL'] ?? 0,
            ];
            $rows[] = $row;
            $this->rowCounter++;
        }

        return $rows;
    }

    public function styles(Worksheet $sheet){
        $sheet->mergeCells('A1:M1');
        $sheet->getStyle('A1:M1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1:M1')->getAlignment()->setHorizontal('center');

        $sheet->getStyle('A2:A5')->getFont()->setItalic(true)->setSize(12);
        $sheet->getStyle('A2:A5')->getAlignment()->setHorizontal('left');

        $headerRow = 7;
        $sheet->getStyle('A' . $headerRow . ':M' . $headerRow)->getFont()->setBold(true);
        $sheet->getStyle('A' . $headerRow . ':M' . $headerRow)->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A' . $headerRow . ':M' . $headerRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
              ->getStartColor()->setARGB('FFD3D3D3'); // Light gray

        $sheet->getStyle('A' . $headerRow . ':M' . $this->rowCounter)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ]);

        $columnWidths = [
            'A' => 20, // House
            'B' => 8,  // A*
            'C' => 8,  // A
            'D' => 8,  // B
            'E' => 8,  // C
            'F' => 10, // % CREDIT
            'G' => 8,  // D
            'H' => 8,  // E
            'I' => 10, // % PASS
            'J' => 8,  // F
            'K' => 8,  // G
            'L' => 8,  // U
            'M' => 8,  // TOTAL
        ];
        foreach ($columnWidths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        $sheet->getStyle('A1:M' . $this->rowCounter)->getAlignment()->setHorizontal('center')->setVertical('center');
        for ($row = 1; $row <= $this->rowCounter; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(-1); // Auto height
        }
    }

    public function title(): string{
        return 'House Performance Analysis';
    }
}
