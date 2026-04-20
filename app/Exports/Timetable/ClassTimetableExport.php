<?php

namespace App\Exports\Timetable;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClassTimetableExport implements FromArray, WithStyles, WithTitle, WithColumnWidths {
    public function __construct(
        protected array $gridData,
        protected array $daySchedule,
        protected string $className,
        protected string $timetableName
    ) {}

    public function array(): array {
        $rows = [];

        // Row 1: Title
        $headerCols = $this->getHeaderColumns();
        $titleRow = [$this->className . ' - Class Timetable'];
        for ($i = 1; $i < count($headerCols); $i++) {
            $titleRow[] = '';
        }
        $rows[] = $titleRow;

        // Row 2: Subtitle
        $subtitleRow = [$this->timetableName . ' | ' . now()->format('d M Y')];
        for ($i = 1; $i < count($headerCols); $i++) {
            $subtitleRow[] = '';
        }
        $rows[] = $subtitleRow;

        // Row 3: Empty separator
        $rows[] = array_fill(0, count($headerCols), '');

        // Row 4: Headers
        $rows[] = $headerCols;

        // Rows 5-10: Day 1-6
        for ($day = 1; $day <= 6; $day++) {
            $row = ['Day ' . $day];
            $skipUntil = 0;

            foreach ($this->daySchedule as $schedItem) {
                if ($schedItem['type'] === 'break') {
                    $row[] = '';
                    continue;
                }

                $period = (int) $schedItem['period'];
                if ($period < $skipUntil) {
                    $row[] = '';
                    continue;
                }

                $slot = $this->gridData[$day][$period] ?? null;
                if ($slot) {
                    $duration = $slot['duration'] ?? 1;
                    $skipUntil = $period + $duration;
                    $subjectName = $slot['subject_name'] ?? '?';
                    $detail = $this->getDetailText($slot);
                    $row[] = $subjectName . ($detail ? ' / ' . $detail : '');
                } else {
                    $row[] = '';
                }
            }

            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Get the detail text for a slot cell (override in subclass).
     */
    protected function getDetailText(array $slot): string {
        $teacherName = $slot['teacher_name'] ?? '';
        if (!$teacherName) return '';
        $parts = preg_split('/\s+/', trim($teacherName));
        if (empty($parts) || $parts[0] === '') return '?';
        $first = mb_strtoupper(mb_substr($parts[0], 0, 1));
        $last = count($parts) > 1 ? mb_strtoupper(mb_substr(end($parts), 0, 1)) : '';
        return $first . $last;
    }

    public function styles(Worksheet $sheet): void {
        $colCount = count($this->getHeaderColumns());
        $lastCol = $this->colLetter($colCount);
        $lastDataRow = 10; // 4 header rows + 6 day rows

        // Title row: bold, large
        $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true)->setSize(14);
        $sheet->mergeCells("A1:{$lastCol}1");

        // Subtitle row
        $sheet->getStyle("A2:{$lastCol}2")->getFont()->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF666666'));
        $sheet->mergeCells("A2:{$lastCol}2");

        // Header row: dark background, white text, bold, center
        $sheet->getStyle("A4:{$lastCol}4")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '343A40']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Data rows: borders, center-aligned
        $sheet->getStyle("A4:{$lastCol}{$lastDataRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DEE2E6']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // Day labels: left-aligned, bold
        $sheet->getStyle("A5:A{$lastDataRow}")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8F9FA']],
        ]);

        // Break columns: yellow background
        $colIdx = 1; // 0-based, col A = 0 (Day label)
        foreach ($this->daySchedule as $schedItem) {
            $colIdx++;
            if ($schedItem['type'] === 'break') {
                $colLetter = $this->colLetter($colIdx);
                $sheet->getStyle("{$colLetter}4:{$colLetter}{$lastDataRow}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FEF3C7');
            }
        }
    }

    public function title(): string {
        return mb_substr($this->className, 0, 31);
    }

    public function columnWidths(): array {
        $widths = ['A' => 10];
        $colIdx = 1;
        foreach ($this->daySchedule as $schedItem) {
            $colIdx++;
            $letter = $this->colLetter($colIdx);
            $widths[$letter] = $schedItem['type'] === 'break' ? 6 : 14;
        }
        return $widths;
    }

    protected function getHeaderColumns(): array {
        $headers = ['Day'];
        foreach ($this->daySchedule as $item) {
            if ($item['type'] === 'period') {
                $headers[] = 'P' . $item['period'];
            } elseif ($item['type'] === 'break') {
                $headers[] = $item['label'] ?? 'Break';
            }
        }
        return $headers;
    }

    protected function colLetter(int $colNum): string {
        $letter = '';
        while ($colNum > 0) {
            $colNum--;
            $letter = chr(65 + ($colNum % 26)) . $letter;
            $colNum = intdiv($colNum, 26);
        }
        return $letter;
    }
}
