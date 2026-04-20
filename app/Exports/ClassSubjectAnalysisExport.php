<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ClassSubjectAnalysisExport implements FromArray, WithStyles, WithTitle, WithColumnWidths{
    protected $data;
    protected $rowCounter;

    protected ?int $titleRow = null;
    protected ?int $headerTopRow = null;
    protected ?int $headerBottomRow = null;
    protected ?int $firstDataRow = null;
    protected ?int $totalsRow = null;

    public function __construct($data){
        $this->data = $data;
        $this->rowCounter = 1;
    }

    public function array(): array{
        $rows = [];
        $className = $this->data['className'] ?? '';
        $type      = $this->data['type'] ?? 'CA';
        $report    = $this->data['report'] ?? [];
        $totals    = $this->data['totals'] ?? [];
        $test      = $this->data['test'] ?? null;

        $title = $type === 'CA'
            ? "{$className} - End of " . (($test->name ?? null) ?: 'Month') . " Subjects Analysis Report by Grade & Gender"
            : "{$className} - End of Term Subjects Analysis Report by Grade & Gender";
        $rows[] = [$title];
        $this->titleRow = $this->rowCounter++;

        $headerRow1 = [
            'SUBJECT',
            'A*','',
            'A','',
            'B','',
            'C','',
            'CREDIT %',
            'D','',
            'E','',
            'PASS %',
            'F','',
            'G','',
            'U','',
            'TOTAL','',
            'POSITION',
        ];
        $rows[] = $headerRow1;
        $this->headerTopRow = $this->rowCounter++;

        $headerRow2 = [
            '',
            'M','F',
            'M','F',
            'M','F',
            'M','F',
            '',
            'M','F',
            'M','F',
            '',
            'M','F',
            'M','F',
            'M','F',
            'M','F',
            '',
        ];
        $rows[] = $headerRow2;
        $this->headerBottomRow = $this->rowCounter++;

        $this->firstDataRow = $this->rowCounter;
        foreach ($report as $subject) {
            $creditPct = $subject['CREDIT %'] ?? 0;
            $passPct   = $subject['PASS %']   ?? 0;

            $creditPct = is_numeric($creditPct) ? ($creditPct / 100) : 0;
            $passPct   = is_numeric($passPct)   ? ($passPct / 100)   : 0;

            $row = [
                $subject['SUBJECT'] ?? '',
                $subject['A*']['M'] ?? 0,
                $subject['A*']['F'] ?? 0,
                $subject['A']['M']  ?? 0,
                $subject['A']['F']  ?? 0,
                $subject['B']['M']  ?? 0,
                $subject['B']['F']  ?? 0,
                $subject['C']['M']  ?? 0,
                $subject['C']['F']  ?? 0,
                $creditPct,
                $subject['D']['M']  ?? 0,
                $subject['D']['F']  ?? 0,
                $subject['E']['M']  ?? 0,
                $subject['E']['F']  ?? 0,
                $passPct,
                $subject['F']['M']  ?? 0,
                $subject['F']['F']  ?? 0,
                $subject['G']['M']  ?? 0,
                $subject['G']['F']  ?? 0,
                $subject['U']['M']  ?? 0,
                $subject['U']['F']  ?? 0,
                $subject['TOTAL']['M'] ?? 0,
                $subject['TOTAL']['F'] ?? 0,
                $subject['POSITION']   ?? '',
            ];
            $rows[] = $row;
            $this->rowCounter++;
        }

        $tCredit = $totals['CREDIT %'] ?? 0;
        $tPass   = $totals['PASS %'] ?? 0;
        $tCredit = is_numeric($tCredit) ? ($tCredit / 100) : 0;
        $tPass   = is_numeric($tPass)   ? ($tPass / 100)   : 0;

        $totalsRow = [
            'TOTAL',
            $totals['A*']['M'] ?? 0,
            $totals['A*']['F'] ?? 0,
            $totals['A']['M']  ?? 0,
            $totals['A']['F']  ?? 0,
            $totals['B']['M']  ?? 0,
            $totals['B']['F']  ?? 0,
            $totals['C']['M']  ?? 0,
            $totals['C']['F']  ?? 0,
            $tCredit,
            $totals['D']['M']  ?? 0,
            $totals['D']['F']  ?? 0,
            $totals['E']['M']  ?? 0,
            $totals['E']['F']  ?? 0,
            $tPass,
            $totals['F']['M']  ?? 0,
            $totals['F']['F']  ?? 0,
            $totals['G']['M']  ?? 0,
            $totals['G']['F']  ?? 0,
            $totals['U']['M']  ?? 0,
            $totals['U']['F']  ?? 0,
            $totals['TOTAL']['M'] ?? 0,
            $totals['TOTAL']['F'] ?? 0,
            '',
        ];
        $rows[] = $totalsRow;
        $this->totalsRow = $this->rowCounter++;

        return $rows;
    }

    public function columnWidths(): array{
        return [
            'A' => 25,
            'B' => 10, 'C' => 10,
            'D' => 10, 'E' => 10,
            'F' => 10, 'G' => 10,
            'H' => 10, 'I' => 10,
            'J' => 12,
            'K' => 10, 'L' => 10,
            'M' => 10, 'N' => 10,
            'O' => 12,
            'P' => 10, 'Q' => 10,
            'R' => 10, 'S' => 10,
            'T' => 10, 'U' => 10,
            'V' => 10, 'W' => 10,
            'X' => 12,
        ];
    }

    public function styles(Worksheet $sheet){
        $lastCol = 'X';
        $lastRow = $this->rowCounter;

        $sheet->mergeCells("A{$this->titleRow}:{$lastCol}{$this->titleRow}");
        $sheet->getStyle("A{$this->titleRow}:{$lastCol}{$this->titleRow}")->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle("A{$this->titleRow}:{$lastCol}{$this->titleRow}")->getAlignment()->setHorizontal('left')->setVertical('center');
        $sheet->getRowDimension($this->titleRow)->setRowHeight(20);

        $sheet->getStyle("A{$this->headerTopRow}:{$lastCol}{$this->headerBottomRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$this->headerTopRow}:{$lastCol}{$this->headerBottomRow}")->getAlignment()->setHorizontal('center')->setVertical('center');

        $sheet->mergeCells("A{$this->headerTopRow}:A{$this->headerBottomRow}");
        $sheet->mergeCells("J{$this->headerTopRow}:J{$this->headerBottomRow}");
        $sheet->mergeCells("O{$this->headerTopRow}:O{$this->headerBottomRow}");
        $sheet->mergeCells("X{$this->headerTopRow}:X{$this->headerBottomRow}");

        $pairs = [
            ['B','C'], ['D','E'], ['F','G'], ['H','I'],
            ['K','L'], ['M','N'],
            ['P','Q'], ['R','S'], ['T','U'], ['V','W'],
        ];
        foreach ($pairs as [$c1, $c2]) {
            $sheet->mergeCells("{$c1}{$this->headerTopRow}:{$c2}{$this->headerTopRow}");
        }

        $sheet->getStyle("A{$this->headerTopRow}:{$lastCol}{$lastRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ]);

        $sheet->getStyle("A{$this->headerTopRow}:{$lastCol}{$lastRow}")->getAlignment()->setHorizontal('center')->setVertical('center');
        if ($this->firstDataRow) {
            $sheet->getStyle("A{$this->firstDataRow}:A{$lastRow}")
                ->getAlignment()->setHorizontal('left');
        }

        if ($this->totalsRow) {
            $sheet->getStyle("A{$this->totalsRow}:{$lastCol}{$this->totalsRow}")
                ->getFont()->setBold(true)->setItalic(true);
            $sheet->getStyle("A{$this->totalsRow}:{$lastCol}{$this->totalsRow}")
                ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE0E0E0');
        }

        if ($this->firstDataRow) {
            $sheet->getStyle("J{$this->firstDataRow}:J{$lastRow}")->getNumberFormat()->setFormatCode('0%');
            $sheet->getStyle("O{$this->firstDataRow}:O{$lastRow}")->getNumberFormat()->setFormatCode('0%');
        }

        return [];
    }


    public function title(): string{
        return 'Subject Analysis Report';
    }
}
