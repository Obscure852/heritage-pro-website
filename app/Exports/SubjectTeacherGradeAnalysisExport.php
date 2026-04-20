<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SubjectTeacherGradeAnalysisExport implements FromArray, WithStyles, WithTitle{
    protected $reportData;
    protected $test1;
    protected $rowCounter;

    public function __construct($reportData, $test1 = null){
        $this->reportData = $reportData;
        $this->test1 = $test1;
        $this->rowCounter = 1;
    }

    public function array(): array{
        $rows = [];
        if ($this->test1) {
            $gradeName = optional($this->test1->grade)->name ?? 'Grade';
            if (($this->test1->type ?? '') === 'CA') {
                $title = sprintf('%s - End of %s Teachers Analysis', $gradeName, ($this->test1->name ?? 'Month'));
            } else {
                $title = sprintf('%s - End of Term Teachers Analysis', $gradeName);
            }
        } else {
            $title = 'Subject Teacher Grade Analysis Report';
        }

        $rows[] = [$title];
        $this->rowCounter++;

        $headers = [
            'TEACHER','CLASS','SUBJECT','A*','A','B','C','% CREDIT','D','E','% PASS','F','G','U','TOTAL',
        ];

        $rows[] = $headers;
        $this->rowCounter++;

        foreach ($this->reportData as $row) {
            $rows[] = [
                $row['TEACHER'],
                $row['CLASS'],
                $row['SUBJECT'],
                $row['A*'],
                $row['A'],
                $row['B'],
                $row['C'],
                $row['% CREDIT'],
                $row['D'],
                $row['E'],
                $row['% PASS'],
                $row['F'],
                $row['G'],
                $row['U'],
                $row['TOTAL'],
            ];
            $this->rowCounter++;
        }

        return $rows;
    }

    public function styles(Worksheet $sheet){
        $sheet->mergeCells('A1:O1');
        $sheet->getStyle('A1:O1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1:O1')->getAlignment()->setHorizontal('left');
        $sheet->getRowDimension(1)->setRowHeight(-1);

        $headerRow = 2;
        $sheet->getStyle('A' . $headerRow . ':O' . $headerRow)->getFont()->setBold(true);
        $sheet->getStyle('A' . $headerRow . ':O' . $headerRow)->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A' . $headerRow . ':O' . $headerRow)
            ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD3D3D3');

        $sheet->getStyle('A' . $headerRow . ':O' . $this->rowCounter)->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
            ],
        ]);

        $columnWidths = [
            'A' => 20, 'B' => 18, 'C' => 22, 'D' => 8, 'E' => 8, 'F' => 8, 'G' => 8,
            'H' => 10, 'I' => 8, 'J' => 8, 'K' => 10, 'L' => 8, 'M' => 8, 'N' => 8, 'O' => 10,
        ];
        foreach ($columnWidths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        $sheet->getStyle('A1:O' . $this->rowCounter)->getAlignment()->setVertical('center')->setWrapText(true);
        if ($this->rowCounter > 2) {
            $lastRow = $this->rowCounter - 1;
            $sheet->getStyle('A' . $lastRow . ':O' . $lastRow)->getFont()->setBold(true);
            $sheet->getStyle('A' . $lastRow . ':O' . $lastRow)
                ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFF9F9F9');
        }
    }

    public function title(): string{
        return 'Teacher Grade Analysis';
    }
}
