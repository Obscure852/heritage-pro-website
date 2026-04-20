<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ClassListAnalysisExport implements FromArray, WithStyles, WithTitle, WithColumnWidths{
    protected $data;
    protected $rowCounter;
    protected $subjectsWithScores;

    protected $marks = [
        'jce_title_row'              => null,
        'jce_header_row'             => null,
        'jce_data_row'               => null,

        'subject_title_row'          => null,
        'subject_header_row'         => null,
        'subject_data_row'           => null,

        'students_title_row'         => null,
        'students_header_top_row'    => null,
        'students_header_bottom_row' => null,
        'students_first_data_row'    => null,
    ];

    private int $spacerCols = 1;

    public function __construct($data){
        $this->data = $data;
        $this->rowCounter = 1;
        $this->subjectsWithScores = $this->getSubjectsWithScores();
    }

    private function rowWithSpacer(array $cells): array{
        return array_merge(array_fill(0, $this->spacerCols, ''), $cells);
    }

    private function getSubjectsWithScores(): array{
        $subjectsWithScores = [];
        $allSubjects = $this->data['allSubjects'] ?? [];
        $students = $this->data['students'] ?? [];

        foreach ($allSubjects as $subject) {
            $hasScores = false;
            foreach ($students as $student) {
                if (($student['subjects'][$subject]['score'] ?? '-') !== '-') {
                    $hasScores = true;
                    break;
                }
            }
            if ($hasScores) {
                $subjectsWithScores[] = $subject;
            }
        }

        return $subjectsWithScores;
    }

    public function array(): array{
        $rows = [];

        $className       = $this->data['className'] ?? '';
        $type            = $this->data['type'] ?? 'CA';
        $test            = $this->data['test'] ?? null;
        $jceAnalysis     = $this->data['jceAnalysis'] ?? [];
        $subjectAnalysis = $this->data['subjectAnalysis'] ?? [];
        $students        = $this->data['students'] ?? [];

        $rows[] = $this->rowWithSpacer(["{$className} - JCE Subjects Grade Analysis"]);
        $this->marks['jce_title_row'] = $this->rowCounter++;

        $gradesJce = ['A','B','C','D','E','F'];
        $headerRow = [];
        foreach ($gradesJce as $g) { $headerRow[] = $g; $headerRow[] = $g.'%'; }
        $headerRow[] = 'AB%';
        $headerRow[] = 'ABC%';
        $headerRow[] = 'Total';
        $headerRow[] = '100%';
        $rows[] = $this->rowWithSpacer($headerRow);
        $this->marks['jce_header_row'] = $this->rowCounter++;

        $dataRow = [];
        foreach ($gradesJce as $g) {
            $dataRow[] = $jceAnalysis[$g]['Total'] ?? 0;
            $dataRow[] = isset($jceAnalysis[$g]['%']) ? round($jceAnalysis[$g]['%']) / 100 : 0;
        }

        $dataRow[] = isset($jceAnalysis['AB%']) ? round($jceAnalysis['AB%']) / 100 : 0;
        $dataRow[] = isset($jceAnalysis['ABC%']) ? round($jceAnalysis['ABC%']) / 100 : 0;
        $dataRow[] = $jceAnalysis['Total'] ?? 0;
        $dataRow[] = 1;
        $rows[] = $this->rowWithSpacer($dataRow);
        $this->marks['jce_data_row'] = $this->rowCounter++;

        $rows[] = $this->rowWithSpacer([]);
        $this->rowCounter++;

        $subjectTitle = ($type === 'CA')
            ? "{$className} - End of " . (($test->name ?? null) ?: 'Month') . " Class Subjects Grade Analysis"
            : "{$className} - End of Term Class Subjects Grade Analysis";
        $rows[] = $this->rowWithSpacer([$subjectTitle]);
        $this->marks['subject_title_row'] = $this->rowCounter++;

        $gradesSubj = ['A*','A','B','C','D','E','F','G','U'];
        $headerRow = [];
        foreach ($gradesSubj as $g) { $headerRow[] = $g; $headerRow[] = $g.'%'; }
        $headerRow[] = 'A*AB%';
        $headerRow[] = 'A*ABC%';
        $headerRow[] = 'Total';
        $headerRow[] = '100%';
        $rows[] = $this->rowWithSpacer($headerRow);
        $this->marks['subject_header_row'] = $this->rowCounter++;

        $dataRow = [];
        foreach ($gradesSubj as $g) {
            $dataRow[] = $subjectAnalysis[$g]['Total'] ?? 0;
            $dataRow[] = isset($subjectAnalysis[$g]['%']) ? round($subjectAnalysis[$g]['%']) / 100 : 0;
        }
        $dataRow[] = isset($subjectAnalysis['A*AB%']) ? round($subjectAnalysis['A*AB%']) / 100 : 0;
        $dataRow[] = isset($subjectAnalysis['A*ABC%']) ? round($subjectAnalysis['A*ABC%']) / 100 : 0;
        $dataRow[] = $subjectAnalysis['Total'] ?? 0;
        $dataRow[] = 1; 
        $rows[] = $this->rowWithSpacer($dataRow);
        $this->marks['subject_data_row'] = $this->rowCounter++;

        $rows[] = $this->rowWithSpacer([]);
        $this->rowCounter++;


        $studentsTitle = ($type === 'CA')
            ? "{$className} - End of " . (($test->name ?? null) ?: 'Month') . " Class Subjects Grade Analysis"
            : "{$className} - End of Term Exam Class Subjects Analysis";
        $rows[] = $this->rowWithSpacer([$studentsTitle]);
        $this->marks['students_title_row'] = $this->rowCounter++;

        $top = ['#','Name','Class','Gender','JCE'];
        foreach ($this->subjectsWithScores as $subject) {
            $top[] = substr($subject, 0, 3);
            $top[] = '';
        }
        $top[] = 'CRE'; $top[] = 'TP'; $top[] = 'Pos';
        $rows[] = $this->rowWithSpacer($top);
        $this->marks['students_header_top_row'] = $this->rowCounter++;

        $bottom = ['','','','',''];
        foreach ($this->subjectsWithScores as $_) {
            $bottom[] = 'Score';
            $bottom[] = 'Grade';
        }
        $bottom[] = ''; $bottom[] = ''; $bottom[] = '';
        $rows[] = $this->rowWithSpacer($bottom);
        $this->marks['students_header_bottom_row'] = $this->rowCounter++;

        $this->marks['students_first_data_row'] = $this->marks['students_header_bottom_row'] + 1;
        $i = 1;
        foreach ($students as $student) {
            $row = [
                $i++,
                $student['name']   ?? '',
                $student['class']  ?? '',
                $student['gender'] ?? '',
                $student['jce']    ?? '',
            ];
            foreach ($this->subjectsWithScores as $subject) {
                $row[] = $student['subjects'][$subject]['score']  ?? '-';
                $row[] = $student['subjects'][$subject]['display_grade']
                    ?? $student['subjects'][$subject]['grade']
                    ?? '-';
            }
            $row[] = $student['credits']     ?? 0;
            $row[] = $student['totalPoints'] ?? 0;
            $row[] = $student['position']    ?? '';

            $rows[] = $this->rowWithSpacer($row);
            $this->rowCounter++;
        }

        return $rows;
    }

    public function columnWidths(): array{
        $w = [];
        $col = 1;

        for ($i = 0; $i < $this->spacerCols; $i++, $col++) {
            $w[Coordinate::stringFromColumnIndex($col)] = 3;
        }

        $w[Coordinate::stringFromColumnIndex($col++)] = 5;
        $w[Coordinate::stringFromColumnIndex($col++)] = 28;
        $w[Coordinate::stringFromColumnIndex($col++)] = 10;
        $w[Coordinate::stringFromColumnIndex($col++)] = 8;
        $w[Coordinate::stringFromColumnIndex($col++)] = 8;

        foreach ($this->subjectsWithScores as $_) {
            $w[Coordinate::stringFromColumnIndex($col++)] = 9;
            $w[Coordinate::stringFromColumnIndex($col++)] = 9;
        }

        $w[Coordinate::stringFromColumnIndex($col++)] = 8;
        $w[Coordinate::stringFromColumnIndex($col++)] = 8;
        $w[Coordinate::stringFromColumnIndex($col++)] = 8;

        return $w;
    }

    public function styles(Worksheet $sheet){
        $hc = $sheet->getHighestColumn();
        $hr = $sheet->getHighestRow();

        foreach (['jce_title_row','subject_title_row','students_title_row'] as $key) {
            $r = $this->marks[$key];
            if ($r) {
                $sheet->getStyle("A{$r}:{$hc}{$r}")
                    ->getFont()->setBold(true)->setSize(14);
                $sheet->getRowDimension($r)->setRowHeight(20);
            }
        }

        foreach ([
            'jce_header_row',
            'subject_header_row',
            'students_header_top_row',
            'students_header_bottom_row'
        ] as $key) {
            $r = $this->marks[$key];
            if ($r) {
                $sheet->getStyle("A{$r}:{$hc}{$r}")
                    ->getFont()->setBold(true);
                $sheet->getStyle("A{$r}:{$hc}{$r}")
                    ->getAlignment()->setHorizontal('center')->setVertical('center');
            }
        }

        $sheet->getStyle("A1:{$hc}{$hr}")->getAlignment()->setHorizontal('center')->setVertical('center');
        $nameColIndex = $this->spacerCols + 2;
        $nameCol = Coordinate::stringFromColumnIndex($nameColIndex);
        $sheet->getStyle("{$nameCol}1:{$nameCol}{$hr}")->getAlignment()->setHorizontal('left')->setWrapText(true);

        $jceHeader = $this->marks['jce_header_row'];
        $jceData   = $this->marks['jce_data_row'];
        if ($jceHeader && $jceData) {
            $sheet->getStyle("A{$jceHeader}:{$hc}{$jceData}")
                ->applyFromArray(['borders'=>['allBorders'=>['borderStyle'=>\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]]);
        }

        $subHeader = $this->marks['subject_header_row'];
        $subData   = $this->marks['subject_data_row'];
        if ($subHeader && $subData) {
            $sheet->getStyle("A{$subHeader}:{$hc}{$subData}")
                ->applyFromArray(['borders'=>['allBorders'=>['borderStyle'=>\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]]);
        }

        $studTop = $this->marks['students_header_top_row'];
        if ($studTop) {
            $sheet->getStyle("A{$studTop}:{$hc}{$hr}")->applyFromArray(['borders'=>['allBorders'=>['borderStyle'=>\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]]);
        }

        $spaceRows = [];
        if (!empty($this->marks['jce_data_row']))     $spaceRows[] = $this->marks['jce_data_row'] + 1;
        if (!empty($this->marks['subject_data_row'])) $spaceRows[] = $this->marks['subject_data_row'] + 1;
        foreach ($spaceRows as $blankRow) {
            if ($blankRow <= $hr) $sheet->getRowDimension($blankRow)->setRowHeight(10);
        }

        $firstCol = 1 + $this->spacerCols;
        $jceWidth      = (6 * 2) + 2 + 2;
        $subjectWidth  = (9 * 2) + 2 + 2;
        $studentsWidth = 5 + (count($this->subjectsWithScores) * 2) + 3;

        $mergeTitle = function(int $row, int $startIdx, int $width) use ($sheet) {
            if (!$row) return;
            $startCol = Coordinate::stringFromColumnIndex($startIdx);
            $endCol   = Coordinate::stringFromColumnIndex($startIdx + $width - 1);
            $range    = "{$startCol}{$row}:{$endCol}{$row}";

            foreach ($sheet->getMergeCells() as $mergedRange => $_) {
                [$tl, $br] = explode(':', $mergedRange) + [null, null];
                if (!$tl || !$br) continue;
                $tlRow = (int)preg_replace('/\D+/', '', $tl);
                $brRow = (int)preg_replace('/\D+/', '', $br);
                if ($row >= $tlRow && $row <= $brRow) {
                    $tlColIdx = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString(
                        preg_replace('/\d+/', '', $tl)
                    );
                    $brColIdx = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString(
                        preg_replace('/\d+/', '', $br)
                    );
                    $ourStart = $startIdx;
                    $ourEnd   = $startIdx + $width - 1;
                    $overlap  = !($brColIdx < $ourStart || $tlColIdx > $ourEnd);
                    if ($overlap) {
                        $sheet->unmergeCells($mergedRange);
                    }
                }
            }

            $sheet->mergeCells($range);
            $sheet->getStyle("{$startCol}{$row}")->getAlignment()->setHorizontal('left')->setVertical('center');
        };

        $mergeTitle($this->marks['jce_title_row']      ?? 0, $firstCol, $jceWidth);
        $mergeTitle($this->marks['subject_title_row']  ?? 0, $firstCol, $subjectWidth);
        $mergeTitle($this->marks['students_title_row'] ?? 0, $firstCol, $studentsWidth);

        if ($hRow = $this->marks['jce_header_row']) {
            $col = $firstCol;
            foreach (['A','B','C','D','E','F'] as $_) {
                $sheet->mergeCells(
                    Coordinate::stringFromColumnIndex($col) . $hRow . ':' .
                    Coordinate::stringFromColumnIndex($col+1) . $hRow
                );
                $col += 2;
            }
            $totalStart = $col + 2;
            $sheet->mergeCells(
                Coordinate::stringFromColumnIndex($totalStart) . $hRow . ':' .
                Coordinate::stringFromColumnIndex($totalStart+1) . $hRow
            );
        }

        if ($hRow = $this->marks['subject_header_row']) {
            $col = $firstCol;
            foreach (['A*','A','B','C','D','E','F','G','U'] as $_) {
                $sheet->mergeCells(
                    Coordinate::stringFromColumnIndex($col) . $hRow . ':' .
                    Coordinate::stringFromColumnIndex($col+1) . $hRow
                );
                $col += 2;
            }
            $totalStart = $col + 2;
            $sheet->mergeCells(
                Coordinate::stringFromColumnIndex($totalStart) . $hRow . ':' .
                Coordinate::stringFromColumnIndex($totalStart+1) . $hRow
            );
        }

        if ($studTop = $this->marks['students_header_top_row']) {
            $col = $firstCol + 5;
            foreach ($this->subjectsWithScores as $_) {
                $sheet->mergeCells(
                    Coordinate::stringFromColumnIndex($col) . $studTop . ':' .
                    Coordinate::stringFromColumnIndex($col+1) . $studTop
                );
                $col += 2;
            }
        }

        if (($firstData = $this->marks['students_first_data_row']) && $firstData <= $hr) {
            $sheet->getStyle("A{$firstData}:{$hc}{$firstData}")
                ->applyFromArray([
                    'fill'=>[
                        'fillType'=>\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor'=>['rgb'=>'E6FFE6']
                    ]
                ]);
        }

        if ($this->marks['jce_data_row']) {
            $jcePctCols = [];
            for ($off = 1; $off <= 11; $off += 2) $jcePctCols[] = $firstCol + $off;
            $jcePctCols[] = $firstCol + 12;
            $jcePctCols[] = $firstCol + 13;
            $jcePctCols[] = $firstCol + 15;
            foreach ($jcePctCols as $cIdx) {
                $c = Coordinate::stringFromColumnIndex($cIdx);
                $sheet->getStyle($c . $this->marks['jce_data_row'])->getNumberFormat()->setFormatCode('0%');
            }
        }

        if ($this->marks['subject_data_row']) {
            $subPctCols = [];
            for ($off = 1; $off <= 17; $off += 2) $subPctCols[] = $firstCol + $off;
            $subPctCols[] = $firstCol + 18;
            $subPctCols[] = $firstCol + 19;
            $subPctCols[] = $firstCol + 21;
            foreach ($subPctCols as $cIdx) {
                $c = Coordinate::stringFromColumnIndex($cIdx);
                $sheet->getStyle($c . $this->marks['subject_data_row'])->getNumberFormat()->setFormatCode('0%');
            }
        }

        $sheet->getStyle("A1:{$hc}{$hr}")->getAlignment()->setVertical('center');
        return [];
    }



    public function title(): string{
        return 'Class List Analysis';
    }
}
