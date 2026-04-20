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

class GradeAnalysisStudentsSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths, WithEvents{
    protected array $data;
    protected array $hasScoresSubjects;

    public function __construct(array $data){
        $this->data = $data;
        $this->hasScoresSubjects = $this->getSubjectsWithScores();
    }

    private function getSubjectsWithScores(): array{
        $cols = [];
        foreach ($this->data['allSubjects'] as $subject) {
            foreach ($this->data['students'] as $student) {
                if (isset($student['subjects'][$subject]) && ($student['subjects'][$subject]['score'] ?? '-') !== '-') {
                    $cols[] = $subject;
                    break;
                }
            }
        }
        return $cols;
    }

    public function array(): array{
        $rows = [];
        $i = 1;
        foreach ($this->data['students'] as $student) {
            $row = [
                $i,
                $student['name'] ?? '',
                $student['class'] ?? '',
                $student['gender'] ?? '',
                $student['jce'] ?? '',
            ];

            foreach ($this->hasScoresSubjects as $subject) {
                $score = $student['subjects'][$subject]['score'] ?? null;
                $grade = $student['subjects'][$subject]['display_grade']
                    ?? $student['subjects'][$subject]['grade']
                    ?? '';
                $row[] = ($score === '-' || $score === null || $score === '') ? null : (is_numeric($score) ? 0 + $score : $score);
                $row[] = $grade;
            }

            $row[] = (int)($student['credits'] ?? 0);
            $row[] = (int)($student['totalPoints'] ?? 0);
            $row[] = $student['position'] ?? '';

            $rows[] = $row;
            $i++;
        }

        return $rows;
    }

    public function title(): string{
        return 'Student Analysis';
    }

    public function registerEvents(): array{
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->insertNewRowBefore(1, 2);

                $gradeName = $this->data['gradeName'] ?? 'Grade';
                $test = $this->data['test'] ?? null;
                if (($this->data['type'] ?? '') === 'CA') {
                    $title = "{$gradeName} - End of " . (($test->name ?? null) ?: 'Month') . " Grade Analysis";
                } else {
                    $title = "{$gradeName} - End of Term Exam Grade Analysis";
                }
                $sheet->setCellValue('A1', $title);

                $subjectColCount = count($this->hasScoresSubjects) * 2;
                $lastColIndex = 4 + $subjectColCount + 3;
                $lastCol = $this->col($lastColIndex);

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle("A1:{$lastCol}1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getRowDimension(1)->setRowHeight(20);

                $sheet->setCellValue('A2', '#');
                $sheet->setCellValue('B2', 'Name');
                $sheet->setCellValue('C2', 'Class');
                $sheet->setCellValue('D2', 'Gender');
                $sheet->setCellValue('E2', 'JCE');

                $colIdx = 5;
                foreach ($this->hasScoresSubjects as $subject) {
                    $abbr = strtoupper(mb_substr($subject, 0, 3));
                    $start = $this->col($colIdx);
                    $end = $this->col($colIdx + 1);
                    $sheet->setCellValue("{$start}2", $abbr);
                    $sheet->mergeCells("{$start}2:{$end}2");
                    $sheet->getStyle("{$start}2:{$end}2")
                          ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)
                          ->setVertical(Alignment::VERTICAL_CENTER);
                    $colIdx += 2;
                }

                $sheet->setCellValue($this->col($colIdx) . '2', 'CRE'); $colIdx++;
                $sheet->setCellValue($this->col($colIdx) . '2', 'TP');  $colIdx++;
                $sheet->setCellValue($this->col($colIdx) . '2', 'Pos');

                $sheet->getStyle("A2:{$lastCol}2")->getFont()->setBold(true);
                $sheet->getStyle("A2:{$lastCol}2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

                $dataStart = 3;
                $dataEnd = $dataStart + count($this->data['students']) - 1;

                if ($dataEnd >= $dataStart) {
                    $sheet->getStyle("A{$dataStart}:{$lastCol}{$dataEnd}")
                          ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                }
                $sheet->getStyle("A2:{$lastCol}2")
                      ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                $sheet->getStyle("B{$dataStart}:B{$dataEnd}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("A{$dataStart}:A{$dataEnd}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C{$dataStart}:{$lastCol}{$dataEnd}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                if ($dataEnd >= $dataStart) {
                    $sheet->getStyle("A{$dataStart}:{$lastCol}{$dataStart}")
                          ->getFill()->setFillType(Fill::FILL_SOLID)
                          ->getStartColor()->setARGB('E6FFE6');
                    $sheet->getStyle("A{$dataStart}:{$lastCol}{$dataStart}")
                          ->getFont()->setBold(false);
                }

                $sheet->getStyle("A{$dataStart}:{$lastCol}{$dataEnd}")
                      ->getFont()->setBold(false);

                $sheet->freezePane('A3');

                $sheet->setAutoFilter('');
            },
        ];
    }

    public function styles(Worksheet $sheet){
        return [
            1 => ['font' => ['bold' => true]],
            2 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array{
        $widths = [
            'A' => 5,
            'B' => 30,
            'C' => 10,
            'D' => 8,
            'E' => 8,
        ];

        $colIdx = 5;
        foreach ($this->hasScoresSubjects as $_) {
            $widths[$this->col($colIdx)] = 8;     $colIdx++;
            $widths[$this->col($colIdx)] = 8;     $colIdx++;
        }

        $widths[$this->col($colIdx)] = 8; $colIdx++;
        $widths[$this->col($colIdx)] = 8; $colIdx++;
        $widths[$this->col($colIdx)] = 8;

        return $widths;
    }

    private function col(int $zeroBasedIndex): string{
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
