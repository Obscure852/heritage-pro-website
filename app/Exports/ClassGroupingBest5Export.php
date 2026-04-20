<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClassGroupingBest5Export implements FromArray, WithStyles{
    protected $groupedData;
    protected $overallTotals;
    protected $rowCounter;
    protected $test;

    public function __construct($groupedData, $overallTotals, $test = null)
    {
        $this->groupedData   = $groupedData;
        $this->overallTotals = $overallTotals;
        $this->test          = $test;
        $this->rowCounter    = 1;
    }

    public function array(): array
    {
        $rows = [];

        // Title
        if ($this->test && ($this->test->type ?? null) === 'CA') {
            $namePart = $this->test->name ?? 'Month';
            $title = "End of {$namePart} House Credits Performance Analysis (Best 5)";
        } else {
            $title = 'End of Term House Credits Performance Analysis (Best 5)';
        }
        $rows[] = [$title];               $this->rowCounter++;

        // Subtitle
        $subtitle = 'Overview of credits (Best 5) and JCE grades for each house';
        $rows[] = [$subtitle];            $this->rowCounter++;

        // Spacer
        $rows[] = [''];                   $this->rowCounter++;

        // Headers
        $headerRow1 = [
            'House Name',
            'Total Students',
            'Class Name',
            'Grade',
            'No. of Students',
            '>=5 Credits',
            '',
            '',
            '% with >=5 Credits',
            'Students with >=5 JCE Credits',
            '% with >=5 JCE Credits',
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
        $rows[] = $headerRow1;            $this->rowCounter++;
        $rows[] = $headerRow2;            $this->rowCounter++;

        // Body
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
                $row[] = $class['male_with_more_than_5_credits'];
                $row[] = $class['female_with_more_than_5_credits'];
                $row[] = $class['students_with_more_than_5_credits'];
                $row[] = ($class['percentage_with_more_than_5_credits'] ?? 0) . '%';
                $row[] = $class['students_with_more_than_5_jce_credits'];
                $row[] = ($class['percentage_of_jce_grades'] ?? 0) . '%';
                $row[] = ($class['value_addition'] ?? 0) . '%';

                $rows[] = $row;
                $this->rowCounter++;
            }

            // House total row
            $rows[] = [
                'Total for ' . $data['house']->name,
                null,
                null,
                null,
                null,
                $data['male_with_more_than_5_credits'],
                $data['female_with_more_than_5_credits'],
                $data['students_with_more_than_5_credits'],
                ($data['percentage_with_more_than_5_credits'] ?? 0) . '%',
                $data['students_with_more_than_5_jce_credits'],
                ($data['percentage_of_jce_grades'] ?? 0) . '%',
                ($data['value_addition'] ?? 0) . '%',
            ];
            $this->rowCounter++;

            // Spacer between houses
            $rows[] = [''];
            $this->rowCounter++;
        }

        // Overall totals
        $rows[] = [
            'Overall Total',
            null,
            null,
            null,
            null,
            $this->overallTotals['overallMaleWithMoreThan5Credits'],
            $this->overallTotals['overallFemaleWithMoreThan5Credits'],
            $this->overallTotals['overallStudentsWithMoreThan5Credits'],
            ($this->overallTotals['overallPercentageWithMoreThan5Credits'] ?? 0) . '%',
            $this->overallTotals['overallStudentsWithMoreThan5JceCredits'],
            ($this->overallTotals['overallPercentageOfJceGrades'] ?? 0) . '%',
            ($this->overallTotals['overallValueAddition'] ?? 0) . '%',
        ];
        $this->rowCounter++;

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        // Title & subtitle merge
        $sheet->mergeCells('A1:L1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('left');

        $sheet->mergeCells('A2:L2');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(12);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('left');

        // Header band
        $sheet->getStyle('A4:L5')->getFont()->setBold(true);
        $sheet->getStyle('A4:L5')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A4:L5')->getFill()
              ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
              ->getStartColor()->setARGB('FFD3D3D3');

        // Borders
        $sheet->getStyle('A4:L' . $this->rowCounter)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ]);

        // Column widths
        $widths = [
            'A' => 20, // House
            'B' => 15, // Total Students
            'C' => 20, // Class
            'D' => 12, // Grade
            'E' => 15, // No. of Students
            'F' => 8,  // M
            'G' => 8,  // F
            'H' => 10, // Total (>=5)
            'I' => 18, // % >=5
            'J' => 22, // Students >=5 JCE
            'K' => 22, // % >=5 JCE
            'L' => 16, // Value add
        ];
        foreach ($widths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        // Alignment
        $sheet->getStyle('A1:L' . $this->rowCounter)->getAlignment()->setVertical('center');
        $sheet->getStyle('B4:L' . $this->rowCounter)->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A4:A' . $this->rowCounter)->getAlignment()->setHorizontal('left'); // House
        $sheet->getStyle('C4:C' . $this->rowCounter)->getAlignment()->setHorizontal('left'); // Class

        // Merge house name + total-students down per block
        $currentRow = 6;
        foreach ($this->groupedData as $data) {
            $classCount = $data['classes']->count();
            if ($classCount > 0) {
                $start = $currentRow;
                $end   = $currentRow + $classCount - 1;
                $sheet->mergeCells("A{$start}:A{$end}");
                $sheet->mergeCells("B{$start}:B{$end}");
                // +1 total row +1 blank spacer
                $currentRow = $end + 2;
            } else {
                // no classes: one row for house, +1 spacer
                $currentRow += 2;
            }
        }

        // Shade "Total for ..." and "Overall Total" rows
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
