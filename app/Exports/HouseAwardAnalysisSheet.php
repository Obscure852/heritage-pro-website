<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class HouseAwardAnalysisSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths, WithEvents {
    protected array $data;
    protected array $activeSubjects;
    protected array $classHeaderRows = [];
    protected int $firstStudentRow = 0;
    protected int $totalRows = 0;

    public function __construct(array $data) {
        $this->data = $data;
        $this->activeSubjects = $this->getSubjectsWithScores();
    }

    private function getSubjectsWithScores(): array {
        $cols = [];
        $allStudents = collect($this->data['classesData'])->flatten(1)->all();
        foreach ($this->data['allSubjects'] as $subject) {
            foreach ($allStudents as $student) {
                if (isset($student['subjects'][$subject]) && $student['subjects'][$subject]['grade'] !== '-') {
                    $cols[] = $subject;
                    break;
                }
            }
        }
        return $cols;
    }

    public function array(): array {
        $rows = [];
        $i = 1;
        $colCount = 6 + count($this->activeSubjects) + 3;

        foreach ($this->data['classesData'] as $className => $students) {
            // Class header row
            $headerRow = array_fill(0, $colCount, '');
            $headerRow[0] = $className . ' (' . count($students) . ' students)';
            $rows[] = $headerRow;
            $this->classHeaderRows[] = count($rows);

            foreach ($students as $student) {
                if ($this->firstStudentRow === 0) {
                    $this->firstStudentRow = count($rows) + 1;
                }

                $row = [
                    $i,
                    $student['class'] ?? '',
                    $student['surname'] ?? '',
                    $student['firstname'] ?? '',
                    $student['gender'] ?? '',
                    $student['jce'] ?? '',
                ];

                foreach ($this->activeSubjects as $subject) {
                    $row[] = $student['subjects'][$subject]['grade'] ?? '-';
                }

                $row[] = (int) ($student['overallPoints'] ?? 0);
                $row[] = (int) ($student['best6Points'] ?? 0);
                $row[] = (int) ($student['credits'] ?? 0);

                $rows[] = $row;
                $i++;
            }
        }

        $this->totalRows = count($rows);
        return $rows;
    }

    public function title(): string {
        return $this->data['houseName'] ?? 'House';
    }

    public function registerEvents(): array {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->insertNewRowBefore(1, 2);

                $gradeName = $this->data['gradeName'] ?? 'Grade';
                $houseName = $this->data['houseName'] ?? 'House';
                $test = $this->data['test'] ?? null;

                if (($this->data['type'] ?? '') === 'CA') {
                    $title = "{$gradeName} - {$houseName} House Award Analysis - End of " . (($test->name ?? null) ?: 'Month');
                } else {
                    $title = "{$gradeName} - {$houseName} House Award Analysis - End of Term";
                }
                $sheet->setCellValue('A1', $title);

                $subjectColCount = count($this->activeSubjects);
                $lastColIndex = 5 + $subjectColCount + 3;
                $lastCol = $this->col($lastColIndex);

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle("A1:{$lastCol}1")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getRowDimension(1)->setRowHeight(20);

                // Header row
                $sheet->setCellValue('A2', '#');
                $sheet->setCellValue('B2', 'Class');
                $sheet->setCellValue('C2', 'Surname');
                $sheet->setCellValue('D2', 'Firstname');
                $sheet->setCellValue('E2', 'Gender');
                $sheet->setCellValue('F2', 'JCE');

                $colIdx = 6;
                foreach ($this->activeSubjects as $subject) {
                    $abbr = strtoupper(mb_substr($subject, 0, 3));
                    $sheet->setCellValue($this->col($colIdx) . '2', $abbr);
                    $sheet->getStyle($this->col($colIdx) . '2')
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $colIdx++;
                }

                $sheet->setCellValue($this->col($colIdx) . '2', 'Overall Pts');
                $colIdx++;
                $sheet->setCellValue($this->col($colIdx) . '2', 'Best 6');
                $colIdx++;
                $sheet->setCellValue($this->col($colIdx) . '2', 'Credits');

                $sheet->getStyle("A2:{$lastCol}2")->getFont()->setBold(true);
                $sheet->getStyle("A2:{$lastCol}2")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $dataStart = 3;
                $dataEnd = $dataStart + $this->totalRows - 1;

                if ($dataEnd >= $dataStart) {
                    $sheet->getStyle("A{$dataStart}:{$lastCol}{$dataEnd}")
                        ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                }
                $sheet->getStyle("A2:{$lastCol}2")
                    ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                // Alignment for data rows
                $sheet->getStyle("A{$dataStart}:A{$dataEnd}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C{$dataStart}:D{$dataEnd}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("B{$dataStart}:B{$dataEnd}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("E{$dataStart}:{$lastCol}{$dataEnd}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Style class header rows with grey background
                foreach ($this->classHeaderRows as $headerIdx) {
                    $excelRow = $dataStart + $headerIdx - 1;
                    $sheet->mergeCells("A{$excelRow}:{$lastCol}{$excelRow}");
                    $sheet->getStyle("A{$excelRow}:{$lastCol}{$excelRow}")
                        ->getFill()->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('E9ECEF');
                    $sheet->getStyle("A{$excelRow}:{$lastCol}{$excelRow}")
                        ->getFont()->setBold(true);
                    $sheet->getStyle("A{$excelRow}:{$lastCol}{$excelRow}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                // Highlight top student row (first data row)
                if ($this->firstStudentRow > 0) {
                    $topRow = $dataStart + $this->firstStudentRow - 1;
                    $sheet->getStyle("A{$topRow}:{$lastCol}{$topRow}")
                        ->getFill()->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('E6FFE6');
                }

                $sheet->freezePane('A3');
            },
        ];
    }

    public function styles(Worksheet $sheet) {
        return [
            1 => ['font' => ['bold' => true]],
            2 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array {
        $widths = [
            'A' => 5,
            'B' => 12,
            'C' => 18,
            'D' => 18,
            'E' => 8,
            'F' => 8,
        ];

        $colIdx = 6;
        foreach ($this->activeSubjects as $_) {
            $widths[$this->col($colIdx)] = 8;
            $colIdx++;
        }

        $widths[$this->col($colIdx)] = 12;
        $colIdx++;
        $widths[$this->col($colIdx)] = 10;
        $colIdx++;
        $widths[$this->col($colIdx)] = 10;

        return $widths;
    }

    private function col(int $zeroBasedIndex): string {
        $i = $zeroBasedIndex + 1;
        $name = '';
        while ($i > 0) {
            $rem = ($i - 1) % 26;
            $name = chr(65 + $rem) . $name;
            $i = intdiv($i - 1, 26);
        }
        return $name;
    }
}
