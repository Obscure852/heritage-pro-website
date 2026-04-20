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

class AwardTypeAnalysisSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths, WithEvents {
    protected array $data;
    protected array $activeSubjects;

    public function __construct(array $data) {
        $this->data = $data;
        $this->activeSubjects = $this->getSubjectsWithScores();
    }

    private function getSubjectsWithScores(): array {
        $cols = [];
        foreach ($this->data['allSubjects'] as $subject) {
            foreach ($this->data['students'] as $student) {
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
        foreach ($this->data['students'] as $student) {
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

            $rows[] = $row;
            $i++;
        }

        return $rows;
    }

    public function title(): string {
        return ($this->data['awardLabel'] ?? 'Award') . ' Analysis';
    }

    public function registerEvents(): array {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->insertNewRowBefore(1, 2);

                $gradeName = $this->data['gradeName'] ?? 'Grade';
                $awardLabel = $this->data['awardLabel'] ?? 'Award';
                $test = $this->data['test'] ?? null;

                if (($this->data['type'] ?? '') === 'CA') {
                    $title = "{$gradeName} - {$awardLabel} End of " . (($test->name ?? null) ?: 'Month') . " Analysis";
                } else {
                    $title = "{$gradeName} - {$awardLabel} End of Term Analysis";
                }
                $sheet->setCellValue('A1', $title);

                $subjectColCount = count($this->activeSubjects);
                // 6 fixed cols (#, Class, Surname, Firstname, Gender, JCE) + subjects + Overall Pts + Best 6
                $lastColIndex = 5 + $subjectColCount + 2;
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

                $sheet->getStyle("A2:{$lastCol}2")->getFont()->setBold(true);
                $sheet->getStyle("A2:{$lastCol}2")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $dataStart = 3;
                $dataEnd = $dataStart + count($this->data['students']) - 1;

                if ($dataEnd >= $dataStart) {
                    $sheet->getStyle("A{$dataStart}:{$lastCol}{$dataEnd}")
                        ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                }
                $sheet->getStyle("A2:{$lastCol}2")
                    ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                // Alignment
                $sheet->getStyle("A{$dataStart}:A{$dataEnd}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C{$dataStart}:D{$dataEnd}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("B{$dataStart}:B{$dataEnd}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("E{$dataStart}:{$lastCol}{$dataEnd}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Highlight top student row
                if ($dataEnd >= $dataStart) {
                    $sheet->getStyle("A{$dataStart}:{$lastCol}{$dataStart}")
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
