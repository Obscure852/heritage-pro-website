<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class TeacherValueAdditionExport implements FromArray, WithStyles, WithTitle {
    protected $subjectGroups;
    protected $test;
    protected $rowCounter;
    protected $subjectHeaderRows = [];
    protected $totalRows = [];

    public function __construct($subjectGroups, $test = null) {
        $this->subjectGroups = $subjectGroups;
        $this->test = $test;
        $this->rowCounter = 1;
    }

    public function array(): array {
        $rows = [];

        if ($this->test) {
            $gradeName = optional($this->test->grade)->name ?? 'Grade';
            if (($this->test->type ?? '') === 'CA') {
                $title = sprintf('TEACHER BY TEACHER %s, %s %s', strtoupper($gradeName), strtoupper($this->test->name ?? 'MONTH'), $this->test->year ?? date('Y'));
            } else {
                $title = sprintf('TEACHER BY TEACHER %s, END OF TERM (EXAM) %s', strtoupper($gradeName), $this->test->year ?? date('Y'));
            }
        } else {
            $title = 'Teacher Value Addition Analysis';
        }

        $rows[] = [$title];
        $this->rowCounter++;

        $headers = ['Teacher', 'Class', 'A*', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'U', 'X', 'Total', 'ABC%', '%(A-E)', '% JC [ABC]', 'VA'];
        $rows[] = $headers;
        $this->rowCounter++;

        foreach ($this->subjectGroups as $group) {
            // Subject header row
            $rows[] = [$group['name']];
            $this->subjectHeaderRows[] = $this->rowCounter;
            $this->rowCounter++;

            // Data rows
            foreach ($group['rows'] as $row) {
                $rows[] = [
                    $row['teacher'],
                    $row['class'],
                    $row['A*'], $row['A'], $row['B'], $row['C'],
                    $row['D'], $row['E'], $row['F'], $row['G'],
                    $row['U'], $row['X'], $row['total'],
                    $row['abcPercent'], $row['aePercent'], $row['jcAbcPercent'],
                    ($row['va'] > 0 ? '+' : '') . $row['va'],
                ];
                $this->rowCounter++;
            }

            // Department Overall row
            $tot = $group['total'];
            $rows[] = [
                'Department Overall', '',
                $tot['A*'], $tot['A'], $tot['B'], $tot['C'],
                $tot['D'], $tot['E'], $tot['F'], $tot['G'],
                $tot['U'], $tot['X'], $tot['total'],
                $tot['abcPercent'], $tot['aePercent'], $tot['jcAbcPercent'],
                ($tot['va'] > 0 ? '+' : '') . $tot['va'],
            ];
            $this->totalRows[] = $this->rowCounter;
            $this->rowCounter++;
        }

        return $rows;
    }

    public function styles(Worksheet $sheet) {
        $lastCol = 'Q';

        // Title row
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle("A1:{$lastCol}1")->getAlignment()->setHorizontal('left');

        // Header row
        $headerRow = 2;
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->getAlignment()->setHorizontal('center');
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")
            ->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD3D3D3');

        // Borders for data area
        $lastRow = $this->rowCounter - 1;
        if ($lastRow >= $headerRow) {
            $sheet->getStyle("A{$headerRow}:{$lastCol}{$lastRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                ],
            ]);
        }

        // Subject header rows (blue)
        foreach ($this->subjectHeaderRows as $row) {
            $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                ->getFill()->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF4E73DF');
        }

        // Total rows (grey)
        foreach ($this->totalRows as $row) {
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                ->getFill()->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE6E6E6');
        }

        // Column widths
        $columnWidths = [
            'A' => 22, 'B' => 18, 'C' => 6, 'D' => 6, 'E' => 6, 'F' => 6,
            'G' => 6, 'H' => 6, 'I' => 6, 'J' => 6, 'K' => 6, 'L' => 6,
            'M' => 8, 'N' => 8, 'O' => 8, 'P' => 12, 'Q' => 8,
        ];
        foreach ($columnWidths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        $sheet->getStyle("A1:{$lastCol}{$lastRow}")->getAlignment()->setVertical('center')->setWrapText(true);
    }

    public function title(): string {
        return 'Teacher Value Addition';
    }
}
