<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClassGroupingExport implements FromArray, WithStyles{
    protected $groupedData;
    protected $overallTotals;
    protected $rowCounter;
    protected $test;

    public function __construct($groupedData, $overallTotals, $test = null){
        $this->groupedData   = $groupedData;
        $this->overallTotals = $overallTotals;
        $this->test          = $test;
        $this->rowCounter    = 1;
    }

    public function array(): array{
        $rows = [];
        if ($this->test && ($this->test->type ?? null) === 'CA') {
            $namePart = $this->test->name ?? 'Month';
            $title = "End of {$namePart} House Credits Performance Analysis";
        } else {
            $title = 'End of Term House Credits Performance Analysis';
        }
        $rows[] = [$title];
        $this->rowCounter++;


        $subtitle = 'Overview of credits and JCE grades for each house';
        $rows[] = [$subtitle];
        $this->rowCounter++;

        $rows[] = [''];
        $this->rowCounter++;

        $headerRow1 = [
            'House Name',
            'Total Students',
            'Class Name',
            'Grade',
            'No. of Students',
            '>=6 Credits',
            '',
            '',
            '% with >=6 Credits',
            'Students with >=6 JCE Credits',
            '% with >=6 JCE Credits',
            'Value Addition',
        ];
        $headerRow2 = [
            '',
            '',
            '',
            '',
            '',
            'M',
            'F',
            'Total',
            '',
            '',
            '',
            '',
        ];
        $rows[] = $headerRow1; $this->rowCounter++;
        $rows[] = $headerRow2; $this->rowCounter++;

        foreach ($this->groupedData as $data) {
            $houseNameCell = $data['house']->name;
            $firstRow = true;

            foreach ($data['classes'] as $class) {
                $row = [];

                if ($firstRow) {
                    $row[] = $houseNameCell;
                    $row[] = $data['total_students'];
                    $firstRow = false;
                    $houseNameCell = null;
                } else {
                    $row[] = null;
                    $row[] = null;
                }

                $row[] = $class['name'];
                $row[] = $class['grade_name'] ?? 'N/A';
                $row[] = $class['count'];
                $row[] = $class['male_with_more_than_6_credits'];
                $row[] = $class['female_with_more_than_6_credits'];
                $row[] = $class['students_with_more_than_6_credits'];
                $row[] = ($class['percentage_with_more_than_6_credits'] ?? 0) . '%';
                $row[] = $class['students_with_more_than_6_jce_credits'];
                $row[] = ($class['percentage_of_jce_grades'] ?? 0) . '%';
                $row[] = ($class['value_addition'] ?? 0) . '%';

                $rows[] = $row;
                $this->rowCounter++;
            }

            $rows[] = [
                'Total for ' . $data['house']->name,
                null,
                null,
                null,
                null,
                $data['male_with_more_than_6_credits'],
                $data['female_with_more_than_6_credits'],
                $data['students_with_more_than_6_credits'],
                ($data['percentage_with_more_than_6_credits'] ?? 0) . '%',
                $data['students_with_more_than_6_jce_credits'],
                ($data['percentage_of_jce_grades'] ?? 0) . '%',
                ($data['value_addition'] ?? 0) . '%',
            ];
            $this->rowCounter++;
            $rows[] = [''];
            $this->rowCounter++;
        }

        $rows[] = [
            'Overall Total',
            null,
            null,
            null,
            null,
            $this->overallTotals['overallMaleWithMoreThan6Credits'],
            $this->overallTotals['overallFemaleWithMoreThan6Credits'],
            $this->overallTotals['overallStudentsWithMoreThan6Credits'],
            ($this->overallTotals['overallPercentageWithMoreThan6Credits'] ?? 0) . '%',
            $this->overallTotals['overallStudentsWithMoreThan6JceCredits'],
            ($this->overallTotals['overallPercentageOfJceGrades'] ?? 0) . '%',
            ($this->overallTotals['overallValueAddition'] ?? 0) . '%',
        ];
        $this->rowCounter++;

        return $rows;
    }

    public function styles(Worksheet $sheet){
        $sheet->mergeCells('A1:L1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('left');

        $sheet->mergeCells('A2:L2');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(12);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('left');

        $sheet->getStyle('A4:L5')->getFont()->setBold(true);
        $sheet->getStyle('A4:L5')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A4:L5')->getFill()
              ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
              ->getStartColor()->setARGB('FFD3D3D3');

        $sheet->getStyle('A4:L' . $this->rowCounter)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ]);

        $widths = [
            'A' => 20,
            'B' => 15,
            'C' => 20,
            'D' => 12,
            'E' => 15,
            'F' => 8,
            'G' => 8,
            'H' => 10,
            'I' => 18,
            'J' => 22,
            'K' => 22,
            'L' => 16,
        ];
        foreach ($widths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        $sheet->getStyle('A1:L' . $this->rowCounter)->getAlignment()->setVertical('center');
        $sheet->getStyle('B4:L' . $this->rowCounter)->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A4:A' . $this->rowCounter)->getAlignment()->setHorizontal('left');
        $sheet->getStyle('C4:C' . $this->rowCounter)->getAlignment()->setHorizontal('left');

        $currentRow = 6;
        foreach ($this->groupedData as $data) {
            $classCount = $data['classes']->count();
            if ($classCount > 0) {
                $start = $currentRow;
                $end   = $currentRow + $classCount - 1;
                $sheet->mergeCells("A{$start}:A{$end}");
                $sheet->mergeCells("B{$start}:B{$end}");
                $currentRow = $end + 2;
            } else {
                $currentRow += 2;
            }
        }

        for ($r = 6; $r <= $this->rowCounter; $r++) {
            $val = (string) $sheet->getCell("A{$r}")->getValue();
            if (strpos($val, 'Total for') === 0 || strpos($val, 'Overall Total') === 0) {
                $sheet->getStyle("A{$r}:L{$r}")->getFill()
                      ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                      ->getStartColor()->setARGB('FFE6E6E6');
                $sheet->getStyle("A{$r}:L{$r}")->getFont()->setBold(true);
            }
        }
    }
}
