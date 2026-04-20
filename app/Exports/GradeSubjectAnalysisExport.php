<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GradeSubjectAnalysisExport implements FromArray, WithStyles, WithTitle{
    protected $data;
    protected $rowCounter;

    public function __construct($data){
        $this->data = $data;
        $this->rowCounter = 1; 
    }

    public function array(): array{
        $rows = [];
        $gradeName = $this->data['gradeName'] ?? '';
        $testName = $this->data['testName']->name ?? '';
        $type = $this->data['type'];
        $report = $this->data['report'];
        $totals = $this->data['totals'];

        if ($type === 'CA') {
            $title = "End of {$testName} {$gradeName} Subjects Analysis";
        } else {
            $title = "End of Term {$gradeName} Subjects Analysis";
        }
        $rows[] = [$title];
        $this->rowCounter++;

        $headerRow1 = [
            'SUBJECT',
            'A*', '', '', 
            'A', '', '',  
            'B', '', '',  
            'C', '', '',  
            'CREDIT %',
            'D', '', '',  
            'E', '', '',  
            'PASS %',
            'F', '', '',  
            'G', '', '',  
            'U', '', '',  
            'TOTAL', '', '',
            'POSITION'
        ];
        $rows[] = $headerRow1;
        $this->rowCounter++;

        $headerRow2 = [
            '', 
            'M', 'F', 'T', 
            'M', 'F', 'T', 
            'M', 'F', 'T', 
            'M', 'F', 'T', 
            '', 
            'M', 'F', 'T', 
            'M', 'F', 'T', 
            '', 
            'M', 'F', 'T', 
            'M', 'F', 'T', 
            'M', 'F', 'T', 
            'M', 'F', 'T', 
            '' 
        ];
        $rows[] = $headerRow2;
        $this->rowCounter++;

        foreach ($report as $subject) {
            $row = [
                $subject['SUBJECT'],
                $subject['A*']['M'] ?? 0,
                $subject['A*']['F'] ?? 0,
                ($subject['A*']['M'] ?? 0) + ($subject['A*']['F'] ?? 0),
                $subject['A']['M'] ?? 0,
                $subject['A']['F'] ?? 0,
                ($subject['A']['M'] ?? 0) + ($subject['A']['F'] ?? 0),
                $subject['B']['M'] ?? 0,
                $subject['B']['F'] ?? 0,
                ($subject['B']['M'] ?? 0) + ($subject['B']['F'] ?? 0),
                $subject['C']['M'] ?? 0,
                $subject['C']['F'] ?? 0,
                ($subject['C']['M'] ?? 0) + ($subject['C']['F'] ?? 0),
                $subject['CREDIT %'],
                $subject['D']['M'] ?? 0,
                $subject['D']['F'] ?? 0,
                ($subject['D']['M'] ?? 0) + ($subject['D']['F'] ?? 0),
                $subject['E']['M'] ?? 0,
                $subject['E']['F'] ?? 0,
                ($subject['E']['M'] ?? 0) + ($subject['E']['F'] ?? 0),
                $subject['PASS %'],
                $subject['F']['M'] ?? 0,
                $subject['F']['F'] ?? 0,
                ($subject['F']['M'] ?? 0) + ($subject['F']['F'] ?? 0),
                $subject['G']['M'] ?? 0,
                $subject['G']['F'] ?? 0,
                ($subject['G']['M'] ?? 0) + ($subject['G']['F'] ?? 0),
                $subject['U']['M'] ?? 0,
                $subject['U']['F'] ?? 0,
                ($subject['U']['M'] ?? 0) + ($subject['U']['F'] ?? 0),
                $subject['TOTAL']['M'] ?? 0,
                $subject['TOTAL']['F'] ?? 0,
                ($subject['TOTAL']['M'] ?? 0) + ($subject['TOTAL']['F'] ?? 0),
                $subject['POSITION'],
            ];
            $rows[] = $row;
            $this->rowCounter++;
        }

        $totalsRow = [
            'TOTAL',
            $totals['A*']['M'] ?? 0,
            $totals['A*']['F'] ?? 0,
            ($totals['A*']['M'] ?? 0) + ($totals['A*']['F'] ?? 0),
            $totals['A']['M'] ?? 0,
            $totals['A']['F'] ?? 0,
            ($totals['A']['M'] ?? 0) + ($totals['A']['F'] ?? 0),
            $totals['B']['M'] ?? 0,
            $totals['B']['F'] ?? 0,
            ($totals['B']['M'] ?? 0) + ($totals['B']['F'] ?? 0),
            $totals['C']['M'] ?? 0,
            $totals['C']['F'] ?? 0,
            ($totals['C']['M'] ?? 0) + ($totals['C']['F'] ?? 0),
            $totals['CREDIT %'],
            $totals['D']['M'] ?? 0,
            $totals['D']['F'] ?? 0,
            ($totals['D']['M'] ?? 0) + ($totals['D']['F'] ?? 0),
            $totals['E']['M'] ?? 0,
            $totals['E']['F'] ?? 0,
            ($totals['E']['M'] ?? 0) + ($totals['E']['F'] ?? 0),
            $totals['PASS %'],
            $totals['F']['M'] ?? 0,
            $totals['F']['F'] ?? 0,
            ($totals['F']['M'] ?? 0) + ($totals['F']['F'] ?? 0),
            $totals['G']['M'] ?? 0,
            $totals['G']['F'] ?? 0,
            ($totals['G']['M'] ?? 0) + ($totals['G']['F'] ?? 0),
            $totals['U']['M'] ?? 0,
            $totals['U']['F'] ?? 0,
            ($totals['U']['M'] ?? 0) + ($totals['U']['F'] ?? 0),
            $totals['TOTAL']['M'] ?? 0,
            $totals['TOTAL']['F'] ?? 0,
            ($totals['TOTAL']['M'] ?? 0) + ($totals['TOTAL']['F'] ?? 0),
            '',
        ];
        $rows[] = $totalsRow;
        $this->rowCounter++;

        return $rows;
    }

    public function styles(Worksheet $sheet){
        $sheet->mergeCells('A1:AH1');
        $sheet->getStyle('A1:AH1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1:AH1')->getAlignment()->setHorizontal('left')->setVertical('center');
        $sheet->getRowDimension(1)->setRowHeight(20);

        $sheet->getStyle('A2:AH3')->getFont()->setBold(true);
        $sheet->getStyle('A2:AH3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
              ->getStartColor()->setARGB('FFD3D3D3');

        $sheet->getStyle('A2:AH' . $this->rowCounter)->getAlignment()->setHorizontal('center')->setVertical('center');
        $sheet->getStyle('A4:A' . $this->rowCounter)->getAlignment()->setHorizontal('left');

        $columnWidths = [
            'A' => 25,
            'B' => 8,  'C' => 8,  'D' => 8,
            'E' => 8,  'F' => 8,  'G' => 8,
            'H' => 8,  'I' => 8,  'J' => 8,
            'K' => 8,  'L' => 8,  'M' => 8,
            'N' => 12,
            'O' => 8,  'P' => 8,  'Q' => 8,
            'R' => 8,  'S' => 8,  'T' => 8,
            'U' => 12,
            'V' => 8,  'W' => 8,  'X' => 8,
            'Y' => 8,  'Z' => 8,  'AA' => 8,
            'AB' => 8, 'AC' => 8, 'AD' => 8,
            'AE' => 10, 'AF' => 10, 'AG' => 10,
            'AH' => 12,
        ];
        foreach ($columnWidths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        $sheet->getStyle('A2:AH' . $this->rowCounter)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ]);

        $sheet->getStyle('A' . ($this->rowCounter) . ':AH' . ($this->rowCounter))->getFont()->setBold(true)->setItalic(true);
        $sheet->getStyle('A' . ($this->rowCounter) . ':AH' . ($this->rowCounter))->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
              ->getStartColor()->setARGB('FFE0E0E0');

        $mergeRanges = [
            'B2:D2','E2:G2','H2:J2','K2:M2',
            'O2:Q2','R2:T2','V2:X2','Y2:AA2','AB2:AD2','AE2:AG2',
        ];
        foreach ($mergeRanges as $range) {
            $sheet->mergeCells($range);
        }
        $sheet->mergeCells('A2:A3');
        $sheet->mergeCells('N2:N3');
        $sheet->mergeCells('U2:U3');
        $sheet->mergeCells('AH2:AH3');

        $sheet->freezePane('A4');
        $sheet->setAutoFilter('A3:AH3');

        for ($row = 1; $row <= $this->rowCounter; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(-1);
        }

        $conditionalStyles = [];
        for ($i = 1; $i <= 3; $i++) {
            $cs = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
            $cs->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS)
               ->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_EQUAL)
               ->addCondition($i);
            $cs->getStyle()->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
            $cs->getStyle()->getFont()->setBold(true);
            $conditionalStyles[] = $cs;
        }
        $sheet->getStyle('AH4:AH' . ($this->rowCounter - 1))->setConditionalStyles($conditionalStyles);

        $totalColumns = ['D','G','J','M','Q','T','X','AA','AD','AG'];
        foreach ($totalColumns as $col) {
            $sheet->getStyle($col . '4:' . $col . $this->rowCounter)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFF0F0F0');
        }
    }

    public function title(): string{
        return 'Grade Subject Analysis';
    }
}
